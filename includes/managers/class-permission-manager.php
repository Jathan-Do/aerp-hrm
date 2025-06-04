<?php

class AERP_Permission_Manager
{
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_permission']) || !check_admin_referer('aerp_save_permission_action', 'aerp_save_permission_nonce')) {
            return;
        }
        if (!current_user_can('manage_options')) return;
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_permissions';
        $name = sanitize_text_field($_POST['permission_name']);
        $desc = sanitize_textarea_field($_POST['permission_desc']);
        $id   = isset($_POST['permission_id']) ? absint($_POST['permission_id']) : 0;
        if ($id) {
            $wpdb->update($table, ['name' => $name, 'description' => $desc], ['id' => $id]);
            $msg = 'Đã cập nhật quyền!';
        } else {
            $wpdb->insert($table, ['name' => $name, 'description' => $desc]);
            $msg = 'Đã thêm quyền!';
        }
        add_action('admin_notices', function () use ($msg) {
            echo '<div class="updated"><p>' . esc_html($msg) . '</p></div>';
        });
    }
    public static function handle_delete()
    {
        if (
            isset($_GET['page'], $_GET['delete']) &&
            $_GET['page'] === 'aerp_permissions' &&
            is_numeric($_GET['delete']) &&
            check_admin_referer('aerp_delete_permission_' . $_GET['delete'])
        ) {
            global $wpdb;
            $id = absint($_GET['delete']);
            $wpdb->delete($wpdb->prefix . 'aerp_permissions', ['id' => $id]);
            wp_redirect(admin_url('admin.php?page=aerp_permissions&deleted=1'));
            exit;
        }
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
