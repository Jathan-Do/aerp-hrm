<?php

class AERP_Department_Manager
{

    public static function handle_form_submit()
    {
        if (! isset($_POST['aerp_save_department']) || ! check_admin_referer('aerp_save_department_action', 'aerp_save_department_nonce')) {
            return;
        }

        if (! current_user_can('manage_options')) return;

        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_departments';

        $name = sanitize_text_field($_POST['department_name']);
        $desc = sanitize_textarea_field($_POST['department_desc']);
        $id   = isset($_POST['department_id']) ? absint($_POST['department_id']) : 0;

        if ($id) {
            $wpdb->update($table, ['name' => $name, 'description' => $desc], ['id' => $id]);
            $msg = 'Đã cập nhật phòng ban!';
        } else {
            $wpdb->insert($table, ['name' => $name, 'description' => $desc, 'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')]);
            $msg = 'Đã thêm phòng ban!';
        }

        add_action('admin_notices', function () use ($msg) {
            echo '<div class="updated"><p>' . esc_html($msg) . '</p></div>';
        });
    }
    public static function handle_delete()
    {
        if (
            isset($_GET['page'], $_GET['delete']) &&
            $_GET['page'] === 'aerp_departments' &&
            is_numeric($_GET['delete']) &&
            check_admin_referer('aerp_delete_department_' . $_GET['delete'])
        ) {
            global $wpdb;
            $id = absint($_GET['delete']);
            $wpdb->delete($wpdb->prefix . 'aerp_hrm_departments', ['id' => $id]);

            wp_redirect(admin_url('admin.php?page=aerp_departments&deleted=1'));
            exit;
        }
    }


    public static function get_by_id($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_departments';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public static function get_departments()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_departments ORDER BY id DESC");
    }
}
