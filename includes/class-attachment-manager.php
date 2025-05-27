<?php
require_once dirname(__FILE__) . '/class-google-drive-manager.php';

class AERP_Attachment_Manager
{
    // =============================
    // HANDLE ACTION
    // =============================
    public static function handle_submit()
    {
        if (!current_user_can('manage_options')) return;

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

    public static function handle_delete()
    {
        if (
            isset($_GET['delete_attachment'], $_GET['_wpnonce']) &&
            wp_verify_nonce($_GET['_wpnonce'], 'aerp_delete_attachment_' . $_GET['delete_attachment'])
        ) {
            global $wpdb;
            $attachment = self::get_by_id(absint($_GET['delete_attachment']));
            
            if ($attachment) {
                // Xóa file trên Drive nếu có
                if ($attachment->storage_type === 'drive' && !empty($attachment->drive_file_id)) {
                    $drive = AERP_Google_Drive_Manager::get_instance();
                    $drive->delete_file($attachment->drive_file_id);
                }
                
                // Xóa record trong DB
                $wpdb->delete($wpdb->prefix . 'aerp_hrm_attachments', ['id' => absint($_GET['delete_attachment'])]);
            }
        }
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
            // Upload lên Google Drive
            $drive = AERP_Google_Drive_Manager::get_instance();
            $result = $drive->upload_file(
                $file['tmp_name'],
                $file['name'],
                $file['type']
            );
            
            if (!$result) {
                wp_die('Lỗi upload lên Google Drive');
            }
            
            $file_url = $result['file_url'];
            $drive_file_id = $result['file_id'];
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
            'uploaded_at'     => current_time('mysql'),
            'storage_type'    => $storage_type,
            'drive_file_id'   => $drive_file_id
        ]);
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
            'uploaded_at'     => current_time('mysql'),
            'storage_type'    => 'manual',
            'drive_file_id'   => null
        ]);

        aerp_js_redirect(admin_url("admin.php?page=aerp_employees&view={$data['employee_id']}#attachments"));
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
                
                if ($storage_type === 'drive') {
                    // Upload lên Google Drive
                    $drive = AERP_Google_Drive_Manager::get_instance();
                    $result = $drive->upload_file(
                        $file['tmp_name'],
                        $file['name'],
                        $file['type']
                    );
                    
                    if (!$result) {
                        wp_die('Lỗi upload lên Google Drive');
                    }
                    
                    $file_url = $result['file_url'];
                    $drive_file_id = $result['file_id'];
                } else {
                    // Upload local như cũ
                    $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));
                    if ($upload['error']) {
                        wp_die('Lỗi upload: ' . $upload['error']);
                    }
                    $file_url = $upload['url'];
                    $drive_file_id = null;
                }

                // Cập nhật DB
                $wpdb->update($wpdb->prefix . 'aerp_hrm_attachments', [
                    'file_url'        => esc_url_raw($file_url),
                    'file_name'       => sanitize_file_name($file['name']),
                    'file_type'       => $ext,
                    'attachment_type' => $attachment_type,
                    'uploaded_by'     => get_current_user_id(),
                    'uploaded_at'     => current_time('mysql'),
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

                // Chuyển từ Drive sang Local
                if ($old_storage_type === 'drive' && $new_storage_type === 'local' && $old_drive_file_id) {
                    $drive = AERP_Google_Drive_Manager::get_instance();
                    // Tải file từ Google Drive về
                    $response = $drive->get_client()->getHttpClient()->request(
                        'GET',
                        'https://www.googleapis.com/drive/v3/files/' . $old_drive_file_id . '?alt=media',
                        [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $drive->get_client()->getAccessToken()['access_token']
                            ]
                        ]
                    );
                    $file_content = $response->getBody()->getContents();
                    $upload = wp_upload_bits($file_name, null, $file_content);
                    if ($upload['error']) {
                        wp_die('Lỗi tải file từ Google Drive về máy chủ: ' . $upload['error']);
                    }
                    $file_url = $upload['url'];
                    $drive_file_id = null;
                    $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    // (Tùy chọn) Xóa file trên Drive
                    $drive->delete_file($old_drive_file_id);
                }
                // Chuyển từ Local sang Drive
                elseif ($old_storage_type === 'local' && $new_storage_type === 'drive') {
                    $drive = AERP_Google_Drive_Manager::get_instance();
                    // Lấy đường dẫn file local
                    $file_path = str_replace(wp_get_upload_dir()['baseurl'], wp_get_upload_dir()['basedir'], $old_file_url);
                    $mime_type = wp_check_filetype($file_path)['type'] ?: 'application/octet-stream';
                    $result = $drive->upload_file($file_path, $file_name, $mime_type);
                    if (!$result) {
                        wp_die('Lỗi upload file từ máy chủ lên Google Drive');
                    }
                    $file_url = $result['file_url'];
                    $drive_file_id = $result['file_id'];
                    $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    // (Tùy chọn) Xóa file local
                    $file_path = str_replace(wp_get_upload_dir()['baseurl'], wp_get_upload_dir()['basedir'], $old_file_url);
                    @unlink($file_path);
                }

                $wpdb->update($wpdb->prefix . 'aerp_hrm_attachments', [
                    'file_url'        => esc_url_raw($file_url),
                    'file_name'       => sanitize_file_name($file_name),
                    'file_type'       => $file_type,
                    'attachment_type' => $attachment_type,
                    'uploaded_by'     => get_current_user_id(),
                    'uploaded_at'     => current_time('mysql'),
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

        // Redirect lại về tab hồ sơ
        aerp_js_redirect(admin_url("admin.php?page=aerp_employees&view={$employee_id}#attachments"));
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
