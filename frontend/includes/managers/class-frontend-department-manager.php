<?php
if (!defined('ABSPATH')) {
    exit;
}

class AERP_Frontend_Department_Manager
{
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_department'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['aerp_save_department_nonce'], 'aerp_save_department_action')) {
            wp_die('Invalid nonce for department save.');
        }

        if (!current_user_can('manage_options')) {
            wp_die('Permission denied.');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_departments';

        $name = sanitize_text_field($_POST['department_name']);
        $desc = sanitize_textarea_field($_POST['department_desc']);
        $id = isset($_POST['department_id']) ? absint($_POST['department_id']) : 0;

        if ($id) {
            $wpdb->update(
                $table,
                ['name' => $name, 'description' => $desc],
                ['id' => $id]
            );
            $msg = 'Đã cập nhật phòng ban!';
        } else {
            $wpdb->insert($table, [
                'name' => $name,
                'description' => $desc,
                'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
            ]);
            $msg = 'Đã thêm phòng ban!';
        }

        set_transient('aerp_department_message', $msg, 10);
        wp_redirect(home_url('/aerp-departments'));
        exit;
    }

    public static function handle_single_delete()
    {
        $id = absint($_GET['id'] ?? 0);
        $nonce_action = 'delete_department_' . $id;

        if ($id && check_admin_referer($nonce_action)) {
            if (self::delete_department_by_id($id)) {
                $message = 'Đã xóa phòng ban thành công!';
            } else {
                $message = 'Không thể xóa phòng ban.';
            }
            set_transient('aerp_department_message', $message, 10);
            wp_redirect(home_url('/aerp-departments'));
            exit;
        } else {
            error_log('AERP_HRM: Single delete - Nonce verification failed or ID missing.');
        }
        wp_die('Invalid request or nonce.');
    }

    /**
     * Xóa phòng ban theo ID
     * @param int $id ID của phòng ban cần xóa
     * @return bool True nếu xóa thành công, false nếu thất bại
     */
    public static function delete_department_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_departments', ['id' => absint($id)]);
        return (bool) $deleted;
    }
    public static function get_by_id($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_departments';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
}
