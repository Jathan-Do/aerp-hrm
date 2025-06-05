<?php

class AERP_Company_Manager {

    public static function handle_form_submit() {
        if (
            ! isset($_POST['aerp_save_company']) ||
            ! check_admin_referer('aerp_save_company_action', 'aerp_save_company_nonce') ||
            ! current_user_can('manage_options')
        ) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_company_info';

        $data = [
            'company_name' => sanitize_text_field($_POST['company_name']),
            'tax_code'     => sanitize_text_field($_POST['tax_code']),
            'phone'        => sanitize_text_field($_POST['phone']),
            'email'        => sanitize_email($_POST['email']),
            'address'      => sanitize_text_field($_POST['address']),
            'website'      => esc_url_raw($_POST['website']),
            'logo_url'     => esc_url_raw($_POST['logo_url']),
            'work_saturday' => sanitize_text_field($_POST['work_saturday'] ?? 'off'),
        ];

        $info = self::get_info();

        if ($info) {
            $wpdb->update($table, $data, ['id' => $info->id]);
        } else {
            $data['created_at'] = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s');
            $wpdb->insert($table, $data);
        }

        add_action('admin_notices', function () {
            echo '<div class="updated"><p>Thông tin công ty đã được lưu.</p></div>';
        });
    }

    public static function get_info() {
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM {$wpdb->prefix}aerp_hrm_company_info LIMIT 1");
    }
}
