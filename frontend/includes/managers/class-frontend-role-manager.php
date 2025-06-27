<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Role_Manager
{
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_role']) || !wp_verify_nonce($_POST['aerp_save_role_nonce'], 'aerp_save_role_action')) {
            return;
        }

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        if (!aerp_user_has_role($user_id, 'admin')) {
            wp_die(__('Bạn không có quyền thực hiện thao tác này.'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'aerp_roles';
        $system_roles = ['admin', 'department_lead', 'accountant', 'employee'];

        $name = sanitize_text_field($_POST['role_name']);
        $desc = sanitize_textarea_field($_POST['role_desc']);
        $id = isset($_POST['role_id']) ? absint($_POST['role_id']) : 0;

        if ($id) {
            $role = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
            if (in_array($role->name, $system_roles)) {
                set_transient('aerp_role_message', 'Không thể sửa nhóm quyền hệ thống!', 10);
                wp_redirect(home_url('/aerp-role'));
                exit;
            }
            // Kiểm tra trùng tên với nhóm quyền khác
            $existing_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE name = %s AND id != %d", $name, $id));
            if ($existing_id) {
                set_transient('aerp_role_message', 'Tên nhóm quyền đã tồn tại!', 10);
                wp_redirect(home_url('/aerp-role'));
                exit;
            }

            $wpdb->update($table, ['name' => $name, 'description' => $desc], ['id' => $id]);

            $role_id = $id;
            $msg = 'Đã cập nhật nhóm quyền!';
        } else {
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE name = %s", $name));
            if ($exists) {
                set_transient('aerp_role_message', 'Tên nhóm quyền đã tồn tại!', 10);
                wp_redirect(home_url('/aerp-role'));
                exit;
            }
            $wpdb->insert($table, ['name' => $name, 'description' => $desc]);
            $role_id = $wpdb->insert_id;
            $msg = 'Đã thêm nhóm quyền!';
        }

        // Save permissions
        $permissions = isset($_POST['role_permissions']) ? array_map('intval', (array)$_POST['role_permissions']) : [];
        $wpdb->delete($wpdb->prefix . 'aerp_role_permission', ['role_id' => $role_id]);
        foreach ($permissions as $pid) {
            $wpdb->insert($wpdb->prefix . 'aerp_role_permission', [
                'role_id' => $role_id,
                'permission_id' => $pid
            ]);
        }

        aerp_clear_table_cache();
        set_transient('aerp_role_message', $msg, 10);
        wp_redirect(home_url('/aerp-role'));
        exit;
    }

    public static function handle_single_delete()
    {
        $id = absint($_GET['id'] ?? 0);
        $nonce_action = 'delete_role_' . $id;

        if ($id && check_admin_referer($nonce_action)) {
            global $wpdb;
            $role = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_roles WHERE id = %d", $id));
            $system_roles = ['admin', 'department_lead', 'accountant', 'employee'];
            if (in_array($role->name, $system_roles)) {
                set_transient('aerp_role_message', 'Không thể xoá nhóm quyền hệ thống!', 10);
            } else {
                $wpdb->delete($wpdb->prefix . 'aerp_roles', ['id' => $id]);
                set_transient('aerp_role_message', 'Đã xoá nhóm quyền thành công!', 10);
            }
            aerp_clear_table_cache();
            wp_redirect(home_url('/aerp-role'));
            exit;
        } else {
            wp_die('Yêu cầu không hợp lệ.');
        }
    }

    public static function delete_role_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_roles', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool) $deleted;
    }

    public static function get_by_id($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_roles WHERE id = %d", $id));
    }

    public static function get_roles()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_roles ORDER BY id DESC");
    }

    public static function get_permissions_of_role($role_id)
    {
        global $wpdb;
        return $wpdb->get_col($wpdb->prepare("SELECT permission_id FROM {$wpdb->prefix}aerp_role_permission WHERE role_id = %d", $role_id));
    }
    public static function get_roles_of_user($user_id)
    {
        global $wpdb;
        return $wpdb->get_col($wpdb->prepare(
            "SELECT role_id FROM {$wpdb->prefix}aerp_user_role WHERE user_id = %d", $user_id
        ));
    }
}
