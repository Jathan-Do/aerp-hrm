<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Commission_Manager
{
    public static function get_schemes()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_commission_schemes ORDER BY id DESC");
    }

    public static function get_scheme($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_commission_schemes WHERE id = %d", absint($id)));
    }

    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_commission_scheme'])) return;
        if (!wp_verify_nonce($_POST['aerp_commission_nonce'] ?? '', 'aerp_commission_action')) wp_die('Invalid nonce');

        global $wpdb;
        $scheme_id = absint($_POST['scheme_id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $note = sanitize_textarea_field($_POST['note'] ?? '');
        $min_profit = isset($_POST['min_profit']) ? floatval($_POST['min_profit']) : 0;
        $max_profit = ($_POST['max_profit'] === '' || $_POST['max_profit'] === null) ? null : floatval($_POST['max_profit']);
        $percent = isset($_POST['percent']) ? floatval($_POST['percent']) : 0;

        if ($scheme_id) {
            $wpdb->update($wpdb->prefix . 'aerp_hrm_commission_schemes', [
                'name' => $name,
                'note' => $note,
                'min_profit' => $min_profit,
                'max_profit' => $max_profit,
                'percent' => $percent,
            ], ['id' => $scheme_id]);
        } else {
            $wpdb->insert($wpdb->prefix . 'aerp_hrm_commission_schemes', [
                'name' => $name,
                'note' => $note,
                'min_profit' => $min_profit,
                'max_profit' => $max_profit,
                'percent' => $percent,
                'created_at' => current_time('mysql')
            ]);
            $scheme_id = (int) $wpdb->insert_id;
        }
        aerp_clear_table_cache();
        set_transient('aerp_commission_scheme_message', 'Lưu danh mục hoa hồng thành công', 10);
        wp_redirect(home_url('/aerp-hrm-commission-schemes'));
        exit;
    }

    public static function handle_single_delete()
    {
        $scheme_id = absint($_GET['id'] ?? 0);
        $nonce_action = 'delete_commission_scheme_' . $scheme_id;
        if ($scheme_id && check_admin_referer($nonce_action)) {
            global $wpdb;
            $deleted = self::delete_scheme_by_id($scheme_id);
            if ($deleted) {
                $message = 'Đã xóa danh mục thành công!';
            } else {
                $message = 'Không thể xóa danh mục.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_commission_scheme_message', $message, 10);
            wp_redirect(home_url('/aerp-hrm-commission-schemes'));
            exit;
        }
        wp_die('Invalid request or nonce.');
    }
    public static function delete_scheme_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_commission_schemes', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool) $deleted;
    }
}
