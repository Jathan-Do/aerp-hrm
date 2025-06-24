<?php
if (!defined('ABSPATH')) {
    exit;
}

class AERP_Frontend_Work_Location_Manager
{
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_work_location'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['aerp_save_work_location_nonce'], 'aerp_save_work_location_action')) {
            wp_die('Invalid nonce for position save.');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_work_locations';

        $name = sanitize_text_field($_POST['work_location_name']);
        $desc = sanitize_textarea_field($_POST['work_location_desc']);
        $id = isset($_POST['work_location_id']) ? absint($_POST['work_location_id']) : 0;

        if ($id) {
            $wpdb->update(
                $table,
                ['name' => $name, 'description' => $desc],
                ['id' => $id]
            );
            $msg = 'Đã cập nhật chi nhánh!';
        } else {
            $wpdb->insert($table, [
                'name' => $name,
                'description' => $desc,
                'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
            ]);
            $msg = 'Đã thêm chi nhánh!';
        }
        aerp_clear_table_cache();
        set_transient('aerp_work_location_message', $msg, 10);
        wp_redirect(home_url('/aerp-work-location'));
        exit;
    }

    public static function handle_single_delete()
    {
        $id = absint($_GET['id'] ?? 0);
        $nonce_action = 'delete_work_location_' . $id;

        if ($id && check_admin_referer($nonce_action)) {
            if (self::delete_work_location_by_id($id)) {
                $message = 'Đã xóa chi nhánh thành công!';
            } else {
                $message = 'Không thể xóa chi nhánh.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_work_location_message', $message, 10);
            wp_redirect(home_url('/aerp-work-location'));
            exit;
        } else {
            error_log('AERP_HRM: Single delete - Nonce verification failed or ID missing.');
        }
        wp_die('Invalid request or nonce.');
    }

    public static function delete_work_location_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_work_locations', ['id' => absint($id)]);
        aerp_clear_table_cache();
        
        return (bool) $deleted;
    }
    public static function get_work_locations() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_work_locations ORDER BY id DESC");
    }

    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_work_locations WHERE id = %d", $id));
    }
}
