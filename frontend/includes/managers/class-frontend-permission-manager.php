<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Permission_Manager
{
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_permission']) || !check_admin_referer('aerp_save_permission_action', 'aerp_save_permission_nonce')) {
            return;
        }

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
$employee = aerp_get_employee_by_user_id($user_id);
$user_fullname = $employee ? $employee->full_name : '';
        if (!is_user_logged_in() || !aerp_user_has_role($user_id, 'admin')) {
            wp_die(__('Bạn không có quyền thực hiện thao tác này.'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'aerp_permissions';
        $name  = sanitize_text_field($_POST['permission_name']);
        $desc  = sanitize_textarea_field($_POST['permission_desc']);
        $id    = isset($_POST['permission_id']) ? absint($_POST['permission_id']) : 0;

        if ($id) {
            // Check trùng tên (ngoại trừ chính nó)
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE name = %s AND id != %d", $name, $id));
            if ($exists) {
                set_transient('aerp_permission_message', 'Tên quyền đã tồn tại!', 10);
                wp_redirect(home_url('/aerp-permission'));
                exit;
            }

            $wpdb->update($table, ['name' => $name, 'description' => $desc], ['id' => $id]);
            $msg = 'Đã cập nhật quyền!';
        } else {
            // Check trùng tên khi thêm mới
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE name = %s", $name));
            if ($exists) {
                set_transient('aerp_permission_message', 'Tên quyền đã tồn tại!', 10);
                wp_redirect(home_url('/aerp-permission'));
                exit;
            }

            $wpdb->insert($table, ['name' => $name, 'description' => $desc]);
            $msg = 'Đã thêm quyền!';
        }

        aerp_clear_table_cache();
        set_transient('aerp_permission_message', $msg, 10);
        wp_redirect(home_url('/aerp-permission'));
        exit;
    }

    public static function handle_single_delete()
    {
        $id = absint($_GET['id'] ?? 0);
        $nonce_action = 'delete_permission_' . $id;

        if ($id && check_admin_referer($nonce_action)) {
            if (self::delete_permission_by_id($id)) {
                $message = 'Đã xóa quyền thành công!';
            } else {
                $message = 'Không thể xóa quyền.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_permission_message', $message, 10);
            wp_redirect(home_url('/aerp-permission'));
            exit;
        } else {
            error_log('AERP_HRM: Single delete - Nonce verification failed or ID missing.');
        }
        wp_die('Invalid request or nonce.');
    }
    public static function delete_permission_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_permissions', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool) $deleted;
    }
    public static function get_by_id($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_permissions';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public static function get_permissions()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_permissions ORDER BY id DESC");
    }

    public static function get_permissions_of_user($user_id)
    {
        global $wpdb;
        return $wpdb->get_col($wpdb->prepare(
            "SELECT permission_id FROM {$wpdb->prefix}aerp_user_permission WHERE user_id = %d",
            $user_id
        ));
    }
}
