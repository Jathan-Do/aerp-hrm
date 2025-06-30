<?php
// require_once dirname(__FILE__) . '/class-google-drive-manager.php';

class AERP_Frontend_Attachment_Manager
{
    // =============================
    // HANDLE ACTION
    // =============================
    public static function handle_submit()
    {
        // Upload file
        if (
            isset($_POST['aerp_upload_attachment']) &&
            wp_verify_nonce($_POST['aerp_attachment_nonce'], 'aerp_upload_attachment_' . $_POST['employee_id'])
        ) {
            self::insert_from_upload($_FILES['attachment_file'], $_POST);
        }

        // Nhập thủ công
        if (
            isset($_POST['aerp_manual_attachment_submit']) &&
            check_admin_referer('aerp_manual_attachment_' . $_POST['employee_id'], 'aerp_manual_attachment_nonce')
        ) {
            self::insert_manual($_POST);
        }

        // Cập nhật
        if (
            isset($_POST['aerp_edit_attachment_submit']) &&
            check_admin_referer('aerp_edit_attachment_' . $_POST['id'], 'aerp_edit_attachment_nonce')
        ) {
            self::update($_POST);
        }
    }

    // public static function handle_delete()
    // {
    //     if (
    //         isset($_GET['delete_attachment'], $_GET['_wpnonce']) &&
    //         wp_verify_nonce($_GET['_wpnonce'], 'aerp_delete_attachment_' . $_GET['delete_attachment'])
    //     ) {
    //         global $wpdb;
    //         $attachment = self::get_by_id(absint($_GET['delete_attachment']));
            
    //         if ($attachment) {
    //             // Xóa file trên Drive nếu có
    //             // if ($attachment->storage_type === 'drive' && !empty($attachment->drive_file_id)) {
    //             //     $drive = AERP_Google_Drive_Manager::get_instance();
    //             //     $drive->delete_file($attachment->drive_file_id);
    //             // }
                
    //             // Xóa record trong DB
    //             $wpdb->delete($wpdb->prefix . 'aerp_hrm_attachments', ['id' => absint($_GET['delete_attachment'])]);
    //         }
    //     }
    // }
    public static function handle_single_delete()
    {
        $id = absint($_GET['attachment_id'] ?? 0);
        $nonce_action = 'delete_attachment_' . $id;

        if ($id && check_admin_referer($nonce_action)) {
            if (self::delete_attachment_by_id($id)) {
                $message = 'Đã xóa hồ sơ thành công!';
            } else {
                $message = 'Không thể xóa hồ sơ.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_attachment_message', $message, 10);
            wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $_GET['employee_id'] . '&section=attachment'));
            exit;
        } else {
            error_log('AERP_HRM: Single delete attachment - Nonce verification failed or ID missing.');
        }
        wp_die('Invalid request or nonce.');
    }

    /**
     * Xóa quy tắc kỷ luật theo ID
     */
    public static function delete_attachment_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_attachments', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool) $deleted;
    }
    // =============================
    // INSERT METHODS
    // =============================
    protected static function insert_from_upload($file, $data)
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            wp_die('File upload không hợp lệ');
        }

        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'png'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            wp_die('Chỉ cho phép các file: pdf, doc, jpg, png');
        }

        // Xác định storage type
        $storage_type = isset($data['storage_type']) ? $data['storage_type'] : 'local';
        
        if ($storage_type === 'drive') {
            // Upload lên Google Drive (TẠM THỜI TẮT)
            // $drive = AERP_Google_Drive_Manager::get_instance();
            // $result = $drive->upload_file(
            //     $file['tmp_name'],
            //     $file['name'],
            //     $file['type']
            // );
            // if (!$result) {
            //     wp_die('Lỗi upload lên Google Drive');
            // }
            // $file_url = $result['file_url'];
            // $drive_file_id = $result['file_id'];
            // Tạm thời chuyển sang upload local
            $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));
            if ($upload['error']) {
                wp_die('Lỗi upload: ' . $upload['error']);
            }
            $file_url = $upload['url'];
            $drive_file_id = null;
        } else {
            // Upload local như cũ
            $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));
            if ($upload['error']) {
                wp_die('Lỗi upload: ' . $upload['error']);
            }
            $file_url = $upload['url'];
            $drive_file_id = null;
        }

        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'aerp_hrm_attachments', [
            'employee_id'     => absint($data['employee_id']),
            'file_url'        => esc_url_raw($file_url),
            'file_name'       => sanitize_file_name($file['name']),
            'file_type'       => $ext,
            'attachment_type' => sanitize_text_field($data['attachment_type']),
            'uploaded_by'     => get_current_user_id(),
            'uploaded_at'     => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s'),
            'storage_type'    => $storage_type,
            'drive_file_id'   => $drive_file_id
        ]);
        $msg = 'Đã thêm hồ sơ!';
        aerp_clear_table_cache();
        set_transient('aerp_attachment_message', $msg, 10);
        wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $data['employee_id'] . '&section=attachment'));
        exit;
    }

    protected static function insert_manual($data)
    {
        $file_type = sanitize_text_field($data['file_type']);
        if (empty($file_type)) {
            $file_type = strtolower(pathinfo($data['file_url'], PATHINFO_EXTENSION));
        }

        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'aerp_hrm_attachments', [
            'employee_id'     => absint($data['employee_id']),
            'file_url'        => esc_url_raw($data['file_url']),
            'file_name'       => sanitize_text_field($data['file_name']),
            'file_type'       => $file_type,
            'attachment_type' => sanitize_text_field($data['attachment_type']),
            'uploaded_by'     => get_current_user_id(),
            'uploaded_at'     => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s'),
            'storage_type'    => 'manual',
            'drive_file_id'   => null
        ]);

        $msg = 'Đã thêm hồ sơ!';
        aerp_clear_table_cache();
        set_transient('aerp_attachment_message', $msg, 10);
        wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $data['employee_id'] . '&section=attachment'));
        exit;
    }

    protected static function update($data)
    {
        global $wpdb;

        $id = absint($data['id']);
        $employee_id = absint($data['employee_id']);
        $attachment_type = sanitize_text_field($data['attachment_type']);

        // Lấy thông tin file cũ
        $old = self::get_by_id($id);
        $old_storage_type = $old->storage_type;
        $old_file_url = $old->file_url;
        $old_file_name = $old->file_name;
        $old_file_type = $old->file_type;
        $old_drive_file_id = $old->drive_file_id;

        // Nếu là cập nhật từ upload file mới
        if ($data['edit_mode'] === 'upload') {
            if (!empty($_FILES['attachment_file']['name'])) {
                $file = $_FILES['attachment_file'];

                // Kiểm tra hợp lệ
                $allowed = ['pdf', 'doc', 'docx', 'jpg', 'png'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed)) {
                    wp_die('Chỉ cho phép các file: pdf, doc, docx, jpg, png');
                }

                // Xác định storage type
                $storage_type = isset($data['storage_type']) ? $data['storage_type'] : 'local';
                
                // Upload local như cũ
                $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));
                if ($upload['error']) {
                    wp_die('Lỗi upload: ' . $upload['error']);
                }
                $file_url = $upload['url'];
                $drive_file_id = null;

                // Cập nhật DB với file mới
                $wpdb->update($wpdb->prefix . 'aerp_hrm_attachments', [
                    'file_url'        => esc_url_raw($file_url),
                    'file_name'       => sanitize_file_name($file['name']),
                    'file_type'       => $ext,
                    'attachment_type' => $attachment_type,
                    'uploaded_by'     => get_current_user_id(),
                    'uploaded_at'     => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s'),
                    'storage_type'    => $storage_type,
                    'drive_file_id'   => $drive_file_id
                ], ['id' => $id]);
            } else {
                // Không upload mới → chỉ đổi loại hồ sơ hoặc chuyển đổi nơi lưu trữ
                $new_storage_type = isset($data['storage_type']) ? $data['storage_type'] : $old_storage_type;
                $file_url = $old_file_url;
                $drive_file_id = $old_drive_file_id;
                $file_name = $old_file_name;
                $file_type = $old_file_type;

                $wpdb->update($wpdb->prefix . 'aerp_hrm_attachments', [
                    'file_url'        => esc_url_raw($file_url),
                    'file_name'       => sanitize_file_name($file_name),
                    'file_type'       => $file_type,
                    'attachment_type' => $attachment_type,
                    'uploaded_by'     => get_current_user_id(),
                    'uploaded_at'     => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s'),
                    'storage_type'    => $new_storage_type,
                    'drive_file_id'   => $drive_file_id
                ], ['id' => $id]);
            }
        }
        // Nếu là sửa thủ công
        elseif ($data['edit_mode'] === 'manual') {
            $file_name = sanitize_text_field($data['file_name']);
            $file_url = esc_url_raw($data['file_url']);
            $file_type = sanitize_text_field($data['file_type']) ?: strtolower(pathinfo($file_url, PATHINFO_EXTENSION));

            $wpdb->update($wpdb->prefix . 'aerp_hrm_attachments', [
                'file_url'        => $file_url,
                'file_name'       => $file_name,
                'file_type'       => $file_type,
                'attachment_type' => $attachment_type,
                'storage_type'    => 'manual',
                'drive_file_id'   => null
            ], ['id' => $id]);
        }
        $msg = 'Đã cập nhật hồ sơ!';
        // Redirect lại về tab hồ sơ
        aerp_clear_table_cache();
        set_transient('aerp_attachment_message', $msg, 10);
        wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=attachment'));
        exit;
    }

    // =============================
    // GETTERS
    // =============================
    public static function get_by_employee($employee_id)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}aerp_hrm_attachments WHERE employee_id = %d ORDER BY uploaded_at DESC",
            $employee_id
        ));
    }
    
    public static function get_by_id($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}aerp_hrm_attachments WHERE id = %d",
            $id
        ));
    }
}
