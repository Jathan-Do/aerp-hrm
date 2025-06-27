<?php
if (!defined('ABSPATH')) exit;
class AERP_Frontend_Employee_Reward_Manager
{
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_employee_reward'])) return;
        if (!wp_verify_nonce($_POST['aerp_save_employee_reward_nonce'], 'aerp_save_employee_reward_action')) {
            wp_die('Invalid nonce for reward save.');
        }
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_employee_rewards';
        $employee_id = isset($_POST['employee_id']) ? absint($_POST['employee_id']) : 0;
        $reward_id = isset($_POST['reward_id']) ? absint($_POST['reward_id']) : 0;
        $month = isset($_POST['month']) ? sanitize_text_field($_POST['month']) : '';
        $note = isset($_POST['note']) ? sanitize_text_field($_POST['note']) : '';
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        if ($id) {
            $wpdb->update($table, [
                'reward_id' => $reward_id,
                'month' => $month,
                'note' => $note
            ], ['id' => $id]);
            $msg = 'Đã cập nhật mục thưởng!';
            $row = $wpdb->get_row($wpdb->prepare("SELECT employee_id FROM $table WHERE id = %d", $id));
            if ($row) {
                $employee_id = $row->employee_id;
            }
        } else {
            $wpdb->insert($table, [
                'employee_id' => $employee_id,
                'reward_id' => $reward_id,
                'month' => $month,
                'note' => $note,
                'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
            ]);
            $msg = 'Đã thêm mục thưởng!';
        }
        aerp_clear_table_cache();
        set_transient('aerp_employee_reward_message', $msg, 10);
        wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=reward'));
        exit;
    }
    public static function handle_single_delete()
    {
        $id = absint($_GET['employee_reward_id'] ?? 0);
        $nonce_action = 'delete_employee_reward_' . $id;
        if ($id && check_admin_referer($nonce_action)) {
            if (self::delete_by_id($id)) {
                $message = 'Đã xóa mục thưởng thành công!';
            } else {
                $message = 'Không thể xóa mục thưởng.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_employee_reward_message', $message, 10);
            wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $_GET['employee_id'] . '&section=reward'));
            exit;
        } else {
            error_log('AERP_HRM: Single delete reward - Nonce verification failed or ID missing.');
        }
        wp_die('Invalid request or nonce.');
    }
    public static function delete_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_employee_rewards', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool) $deleted;
    }
    public static function get_all()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_employee_rewards ORDER BY id DESC");
    }
    public static function get_by_id($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_employee_rewards WHERE id = %d", $id));
    }
} 