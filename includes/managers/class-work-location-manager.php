<?php

class AERP_Work_Location_Manager
{
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_work_location']) || !check_admin_referer('aerp_save_work_location_action', 'aerp_save_work_location_nonce')) {
            return;
        }

        if (!current_user_can('manage_options')) return;

        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_work_locations';

        $name = sanitize_text_field($_POST['work_location_name']);
        $desc = sanitize_textarea_field($_POST['work_location_desc']);
        $id   = isset($_POST['work_location_id']) ? absint($_POST['work_location_id']) : 0;

        if ($id) {
            $wpdb->update($table, ['name' => $name, 'description' => $desc], ['id' => $id]);
            $msg = 'Đã cập nhật chi nhánh!';
        } else {
            $wpdb->insert($table, ['name' => $name, 'description' => $desc, 'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')]);
            $msg = 'Đã thêm chi nhánh!';
        }

        add_action('admin_notices', function () use ($msg) {
            echo '<div class="updated"><p>' . esc_html($msg) . '</p></div>';
        });
    }

    public static function handle_delete()
    {
        if (
            isset($_GET['page'], $_GET['delete']) &&
            $_GET['page'] === 'aerp_work_locations' &&
            is_numeric($_GET['delete']) &&
            check_admin_referer('aerp_delete_work_location_' . $_GET['delete'])
        ) {
            global $wpdb;
            $id = absint($_GET['delete']);
            $wpdb->delete($wpdb->prefix . 'aerp_hrm_work_locations', ['id' => $id]);

            wp_redirect(admin_url('admin.php?page=aerp_work_locations&deleted=1'));
            exit;
        }
    }

    public static function get_by_id($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_work_locations';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public static function get_work_locations()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_work_locations ORDER BY id DESC");
    }
} 