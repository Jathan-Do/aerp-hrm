<?php
if (!defined('ABSPATH')) {
    exit;
}

class AERP_Frontend_Discipline_Log_Manager
{
    /**
     * Thêm hoặc cập nhật quy tắc kỷ luật
     */
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_discipline_log'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['aerp_save_discipline_log_nonce'], 'aerp_save_discipline_log_action')) {
            wp_die('Invalid nonce for discipline log save.');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_disciplinary_logs';

        $employee_id = isset($_POST['employee_id']) ? absint($_POST['employee_id']) : 0;
        $rule_id = isset($_POST['rule_id']) ? absint($_POST['rule_id']) : 0;
        $date_violation = isset($_POST['date_violation']) ? sanitize_text_field($_POST['date_violation']) : '';

        // Chỉ insert, không update
        $wpdb->insert($table, [
            'employee_id'    => $employee_id,
            'rule_id'        => $rule_id,
            'date_violation' => $date_violation,
            'created_at'     => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
        ]);
        $msg = 'Đã thêm vi phạm!';

        aerp_clear_table_cache();
        set_transient('aerp_discipline_log_message', $msg, 10);
        wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=discipline'));
        exit;
    }

    /**
     * Xóa một quy tắc kỷ luật
     */
    public static function handle_single_delete()
    {
        $id = absint($_GET['id'] ?? 0);
        $nonce_action = 'delete_discipline_log_' . $id;

        if ($id && check_admin_referer($nonce_action)) {
            if (self::delete_discipline_log_by_id($id)) {
                $message = 'Đã xóa vi phạm thành công!';
            } else {
                $message = 'Không thể xóa vi phạm.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_discipline_log_message', $message, 10);
            wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $_GET['employee_id'] . '&section=discipline'));
            exit;
        } else {
            error_log('AERP_HRM: Single delete discipline log - Nonce verification failed or ID missing.');
        }
        wp_die('Invalid request or nonce.');
    }

    /**
     * Xóa quy tắc kỷ luật theo ID
     */
    public static function delete_discipline_log_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_disciplinary_logs', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool) $deleted;
    }

    /**
     * Lấy tất cả quy tắc kỷ luật
     */
    public static function get_logs()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_disciplinary_logs ORDER BY id DESC");
    }

    /**
     * Lấy quy tắc kỷ luật theo ID
     */
    public static function get_by_id($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_disciplinary_logs WHERE id = %d", $id));
    }
}
