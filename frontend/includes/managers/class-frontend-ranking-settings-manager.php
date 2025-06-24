<?php
if (!defined('ABSPATH')) exit;
class AERP_Frontend_Ranking_Settings_Manager
{
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_ranking_setting'])) return;
        if (!wp_verify_nonce($_POST['aerp_save_ranking_setting_nonce'], 'aerp_save_ranking_setting_action')) {
            wp_die('Invalid nonce for ranking setting save.');
        }
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_ranking_settings';
        $rank_code = sanitize_text_field($_POST['rank_code']);
        $min_point = absint($_POST['min_point']);
        $note = sanitize_text_field($_POST['note']);
        $sort_order = absint($_POST['sort_order']);
        $id = isset($_POST['ranking_id']) ? absint($_POST['ranking_id']) : 0;
        if ($id) {
            $wpdb->update($table, [
                'rank_code' => $rank_code,
                'min_point' => $min_point,
                'note' => $note,
                'sort_order' => $sort_order
            ], ['id' => $id]);
            $msg = 'Đã cập nhật xếp loại!';
        } else {
            $wpdb->insert($table, [
                'rank_code' => $rank_code,
                'min_point' => $min_point,
                'note' => $note,
                'sort_order' => $sort_order,
                'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
            ]);
            $msg = 'Đã thêm xếp loại!';
        }
        aerp_clear_table_cache();
        set_transient('aerp_ranking_settings_message', $msg, 10);
        wp_redirect(home_url('/aerp-ranking-settings'));
        exit;
    }
    public static function handle_single_delete()
    {
        $id = absint($_GET['id'] ?? 0);
        $nonce_action = 'delete_ranking_' . $id;
        if ($id && check_admin_referer($nonce_action)) {
            if (self::delete_by_id($id)) {
                $message = 'Đã xóa xếp loại thành công!';
            } else {
                $message = 'Không thể xóa xếp loại.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_ranking_settings_message', $message, 10);
            wp_redirect(home_url('/aerp-ranking-settings'));
            exit;
        } else {
            error_log('AERP_HRM: Single delete ranking - Nonce verification failed or ID missing.');
        }
        wp_die('Invalid request or nonce.');
    }
    public static function delete_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_ranking_settings', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool) $deleted;
    }
    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_ranking_settings ORDER BY sort_order ASC, id DESC");
    }
    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_ranking_settings WHERE id = %d", $id));
    }
} 