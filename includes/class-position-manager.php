<?php

class AERP_Position_Manager {

    public static function handle_form_submit() {
        if (
            ! isset($_POST['aerp_save_position']) || 
            ! check_admin_referer('aerp_save_position_action', 'aerp_save_position_nonce')
        ) {
            return;
        }

        if ( ! current_user_can('manage_options') ) return;

        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_positions';

        $name = sanitize_text_field($_POST['position_name']);
        $desc = sanitize_textarea_field($_POST['position_desc']);
        $id   = isset($_POST['position_id']) ? absint($_POST['position_id']) : 0;

        if ($id) {
            $wpdb->update($table, [
                'name' => $name,
                'description' => $desc
            ], ['id' => $id]);
            $msg = 'Đã cập nhật chức vụ!';
        } else {
            $wpdb->insert($table, [
                'name'        => $name,
                'description' => $desc,
                'created_at'  => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
            ]);
            $msg = 'Đã thêm chức vụ!';
        }

        add_action('admin_notices', function () use ($msg) {
            echo '<div class="updated"><p>' . esc_html($msg) . '</p></div>';
        });
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

