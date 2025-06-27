<?php
if (!defined('ABSPATH')) exit;
class AERP_Frontend_Adjustment_Manager
{
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_adjustment'])) return;
        if (!wp_verify_nonce($_POST['aerp_save_adjustment_nonce'], 'aerp_save_adjustment_action')) {
            wp_die('Invalid nonce for adjustment save.');
        }
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_adjustments';
        $employee_id = absint($_POST['employee_id']);
        $reason      = sanitize_text_field($_POST['reason']);
        $amount      = floatval($_POST['amount']);
        $type        = sanitize_text_field($_POST['type']);
        $description = sanitize_textarea_field($_POST['description']);
        $date        = sanitize_text_field($_POST['date_effective']);
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        if ($id) {
            $wpdb->update($table, [
                'reason' => $reason,
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
                'date_effective' => $date
            ], ['id' => $id]);
            $msg = 'Đã cập nhật mục điều chỉnh!';
            $row = $wpdb->get_row($wpdb->prepare("SELECT employee_id FROM $table WHERE id = %d", $id));
            if ($row) {
                $employee_id = $row->employee_id;
            }
        } else {
            $wpdb->insert($table, [
                'employee_id' => $employee_id,
                'reason' => $reason,
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
                'date_effective' => $date,
                'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
            ]);
            $msg = 'Đã thêm mục điều chỉnh!';
        }
        aerp_clear_table_cache();
        set_transient('aerp_adjustment_message', $msg, 10);
        wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=adjustment'));
        exit;
    }
    public static function handle_single_delete()
    {
        $id = absint($_GET['adjustment_id'] ?? 0);
        $nonce_action = 'delete_adjustment_' . $id;
        if ($id && check_admin_referer($nonce_action)) {
            if (self::delete_by_id($id)) {
                $message = 'Đã xóa mục điều chỉnh thành công!';
            } else {
                $message = 'Không thể xóa mục điều chỉnh.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_adjustment_message', $message, 10);
            wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $_GET['employee_id'] . '&section=adjustment'));
            exit;
        } else {
            error_log('AERP_HRM: Single delete reward - Nonce verification failed or ID missing.');
        }
        wp_die('Invalid request or nonce.');
    }
    public static function delete_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_adjustments', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool) $deleted;
    }
    public static function get_all()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_adjustments ORDER BY id DESC");
    }
    public static function get_by_id($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_adjustments WHERE id = %d", $id));
    }
} 