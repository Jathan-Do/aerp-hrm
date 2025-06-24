<?php
if (!defined('ABSPATH')) exit;
class AERP_Frontend_Reward_Manager
{
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_reward'])) return;
        if (!wp_verify_nonce($_POST['aerp_save_reward_nonce'], 'aerp_save_reward_action')) {
            wp_die('Invalid nonce for reward save.');
        }
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_reward_definitions';
        $name = sanitize_text_field($_POST['name']);
        $amount = floatval($_POST['amount']);
        $type = sanitize_text_field($_POST['trigger_type']);
        $custom = sanitize_text_field($_POST['custom_trigger_type']);
        $trigger_type = ($type === 'manual' && $custom) ? $custom : $type;
        $day_trigger = !empty($_POST['day_trigger']) ? sanitize_text_field($_POST['day_trigger']) : null;
        $id = isset($_POST['reward_id']) ? absint($_POST['reward_id']) : 0;
        if ($id) {
            $wpdb->update($table, [
                'name' => $name,
                'amount' => $amount,
                'trigger_type' => $trigger_type,
                'day_trigger' => $day_trigger
            ], ['id' => $id]);
            $msg = 'Đã cập nhật mục thưởng!';
        } else {
            $wpdb->insert($table, [
                'name' => $name,
                'amount' => $amount,
                'trigger_type' => $trigger_type,
                'day_trigger' => $day_trigger,
                'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
            ]);
            $msg = 'Đã thêm mục thưởng!';
        }
        aerp_clear_table_cache();
        set_transient('aerp_reward_message', $msg, 10);
        wp_redirect(home_url('/aerp-reward-settings'));
        exit;
    }
    public static function handle_single_delete()
    {
        $id = absint($_GET['id'] ?? 0);
        $nonce_action = 'delete_reward_' . $id;
        if ($id && check_admin_referer($nonce_action)) {
            if (self::delete_by_id($id)) {
                $message = 'Đã xóa mục thưởng thành công!';
            } else {
                $message = 'Không thể xóa mục thưởng.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_reward_message', $message, 10);
            wp_redirect(home_url('/aerp-reward-settings'));
            exit;
        } else {
            error_log('AERP_HRM: Single delete reward - Nonce verification failed or ID missing.');
        }
        wp_die('Invalid request or nonce.');
    }
    public static function delete_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_reward_definitions', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool) $deleted;
    }
    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_reward_definitions ORDER BY id DESC");
    }
    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_reward_definitions WHERE id = %d", $id));
    }
} 