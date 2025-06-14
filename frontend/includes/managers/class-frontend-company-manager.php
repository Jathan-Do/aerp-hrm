<?php
if (!defined('ABSPATH')) {
    exit;
}

class AERP_Frontend_Company_Manager
{
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_company'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['aerp_save_company_nonce'], 'aerp_save_company_action')) {
            wp_die('Invalid nonce for department save.');
        }

        if (!current_user_can('manage_options')) {
            wp_die('Permission denied.');
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
        $id = isset($_POST['company_id']) ? absint($_POST['company_id']) : 0;
        if ($id) {
            $wpdb->update($table, $data, ['id' => $id]);
            $msg = 'Đã cập nhật thông tin công ty!';
        } else {
            $data['created_at'] = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s');
            $wpdb->insert($table, $data);
            $msg = 'Đã thêm thông tin công ty!';
        }

        set_transient('aerp_company_message', $msg, 10);
        wp_redirect(home_url('/aerp-company'));
        exit;
    }

    public static function handle_single_delete()
    {
        $id = absint($_GET['id'] ?? 0);
        $nonce_action = 'delete_company_' . $id;

        if ($id && check_admin_referer($nonce_action)) {
            if (self::delete_company_by_id($id)) {
                $message = 'Đã xóa công ty thành công!';
            } else {
                $message = 'Không thể xóa công ty.';
            }
            set_transient('aerp_company_message', $message, 10);
            wp_redirect(home_url('/aerp-company'));
            exit;
        } else {
            error_log('AERP_HRM: Single delete - Nonce verification failed or ID missing.');
        }
        wp_die('Invalid request or nonce.');
    }

    public static function delete_company_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_company_info', ['id' => absint($id)]);
        return (bool) $deleted;
    }
    public static function get_by_id($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_company_info';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
}
