<?php
if (!defined('ABSPATH')) {
    exit;
}

class AERP_Frontend_Position_Manager
{
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_position'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['aerp_save_position_nonce'], 'aerp_save_position_action')) {
            wp_die('Invalid nonce for position save.');
        }


        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_positions';

        $name = sanitize_text_field($_POST['position_name']);
        $desc = sanitize_textarea_field($_POST['position_desc']);
        $id = isset($_POST['position_id']) ? absint($_POST['position_id']) : 0;

        if ($id) {
            $wpdb->update(
                $table,
                ['name' => $name, 'description' => $desc],
                ['id' => $id]
            );
            $msg = 'Đã cập nhật chức vụ!';
        } else {
            $wpdb->insert($table, [
                'name' => $name,
                'description' => $desc,
                'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
            ]);
            $msg = 'Đã thêm chức vụ!';
        }

        set_transient('aerp_position_message', $msg, 10);
        wp_redirect(home_url('/aerp-position'));
        exit;
    }

    public static function handle_single_delete()
    {
        $id = absint($_GET['id'] ?? 0);
        $nonce_action = 'delete_position_' . $id;

        if ($id && check_admin_referer($nonce_action)) {
            if (self::delete_positon_by_id($id)) {
                $message = 'Đã xóa chức vụ thành công!';
            } else {
                $message = 'Không thể xóa chức vụ.';
            }
            set_transient('aerp_position_message', $message, 10);
            wp_redirect(home_url('/aerp-position'));
            exit;
        } else {
            error_log('AERP_HRM: Single delete - Nonce verification failed or ID missing.');
        }
        wp_die('Invalid request or nonce.');
    }

    public static function delete_positon_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_positions', ['id' => absint($id)]);
        return (bool) $deleted;
    }
    public static function get_positions() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_positions ORDER BY id DESC");
    }

    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_positions WHERE id = %d", $id));
    }
}
