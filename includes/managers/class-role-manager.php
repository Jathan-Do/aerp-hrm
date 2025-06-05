<?php

class AERP_Role_Manager
{
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_role']) || !check_admin_referer('aerp_save_role_action', 'aerp_save_role_nonce')) {
            return;
        }
        if (!current_user_can('manage_options')) return;
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_roles';
        $system_slugs = ['admin', 'department_lead', 'accountant', 'employee'];
        $name = sanitize_text_field($_POST['role_name']);
        $slug = isset($_POST['role_slug']) ? sanitize_text_field($_POST['role_slug']) : '';
        if (!$slug) {
            // Nếu không nhập slug, tự động sinh từ tên
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]+/', '_', $name)));
        }
        $desc = sanitize_textarea_field($_POST['role_desc']);
        $id   = isset($_POST['role_id']) ? absint($_POST['role_id']) : 0;
        if ($id) {
            $role = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
            if (in_array($role->slug, $system_slugs)) {
                // Không cho sửa slug, tên, mô tả
                $msg = 'Không thể sửa nhóm quyền hệ thống!';
                add_action('admin_notices', function () use ($msg) {
                    echo '<div class="notice notice-error"><p>' . esc_html($msg) . '</p></div>';
                });
                return;
            }
            $wpdb->update($table, ['name' => $name, 'description' => $desc], ['id' => $id]);
            $role_id = $id;
            $msg = 'Đã cập nhật nhóm quyền!';
        } else {
            // Kiểm tra trùng slug
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE slug = %s", $slug));
            if ($exists) {
                $msg = 'Slug đã tồn tại, vui lòng chọn tên khác!';
                add_action('admin_notices', function () use ($msg) {
                    echo '<div class="notice notice-error"><p>' . esc_html($msg) . '</p></div>';
                });
                return;
            }
            if (in_array($slug, $system_slugs)) {
                $wpdb->insert($table, ['slug' => $slug, 'name' => $name, 'description' => $desc]);
            } else {
                $wpdb->insert($table, ['slug' => $slug, 'name' => $name, 'description' => $desc]);
            }
            $role_id = $wpdb->insert_id;
            $msg = 'Đã thêm nhóm quyền!';
        }
        // Xử lý lưu quyền cho role
        $permissions = isset($_POST['role_permissions']) ? array_map('intval', (array)$_POST['role_permissions']) : [];
        $wpdb->delete($wpdb->prefix . 'aerp_role_permission', ['role_id' => $role_id]);
        foreach ($permissions as $pid) {
            $wpdb->insert($wpdb->prefix . 'aerp_role_permission', [
                'role_id' => $role_id,
                'permission_id' => $pid
            ]);
        }
        add_action('admin_notices', function () use ($msg) {
            echo '<div class="updated"><p>' . esc_html($msg) . '</p></div>';
        });
    }
    public static function handle_delete()
    {
        if (
            isset($_GET['page'], $_GET['delete']) &&
            $_GET['page'] === 'aerp_roles' &&
            is_numeric($_GET['delete']) &&
            check_admin_referer('aerp_delete_role_' . $_GET['delete'])
        ) {
            global $wpdb;
            $id = absint($_GET['delete']);
            $role = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_roles WHERE id = %d", $id));
            $system_slugs = ['admin', 'department_lead', 'accountant', 'employee'];
            if (in_array($role->slug, $system_slugs)) {
                wp_redirect(admin_url('admin.php?page=aerp_roles&error=system_role'));
                exit;
            }
            $wpdb->delete($wpdb->prefix . 'aerp_roles', ['id' => $id]);
            wp_redirect(admin_url('admin.php?page=aerp_roles&deleted=1'));
            exit;
        }
    }
    public static function get_by_id($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_roles';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    public static function get_roles()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_roles ORDER BY id DESC");
    }
    public static function get_permissions_of_role($role_id)
    {
        global $wpdb;
        return $wpdb->get_col($wpdb->prepare(
            "SELECT permission_id FROM {$wpdb->prefix}aerp_role_permission WHERE role_id = %d", $role_id
        ));
    }
    public static function get_roles_of_user($user_id)
    {
        global $wpdb;
        return $wpdb->get_col($wpdb->prepare(
            "SELECT role_id FROM {$wpdb->prefix}aerp_user_role WHERE user_id = %d", $user_id
        ));
    }
} 