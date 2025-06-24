<?php
if (!defined('ABSPATH')) {
    exit;
}

class AERP_Frontend_Discipline_Rule_Manager
{
    /**
     * Thêm hoặc cập nhật quy tắc kỷ luật
     */
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_discipline_rule'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['aerp_save_discipline_rule_nonce'], 'aerp_save_discipline_rule_action')) {
            wp_die('Invalid nonce for discipline rule save.');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_disciplinary_rules';

        $name = sanitize_text_field($_POST['rule_name']);
        $penalty_point = isset($_POST['penalty_point']) ? intval($_POST['penalty_point']) : 0;
        $fine_amount = isset($_POST['fine_amount']) ? floatval($_POST['fine_amount']) : 0;
        $id = isset($_POST['rule_id']) ? absint($_POST['rule_id']) : 0;

        if ($id) {
            $wpdb->update(
                $table,
                [
                    'rule_name'     => $name,
                    'penalty_point' => $penalty_point,
                    'fine_amount'   => $fine_amount
                ],
                ['id' => $id]
            );
            $msg = 'Đã cập nhật quy tắc kỷ luật!';
        } else {
            $wpdb->insert($table, [
                'rule_name'     => $name,
                'penalty_point' => $penalty_point,
                'fine_amount'   => $fine_amount,
                'created_at'    => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
            ]);
            $msg = 'Đã thêm quy tắc kỷ luật!';
        }
        aerp_clear_table_cache();
        set_transient('aerp_discipline_rule_message', $msg, 10);
        wp_redirect(home_url('/aerp-discipline-rule'));
        exit;
    }

    /**
     * Xóa một quy tắc kỷ luật
     */
    public static function handle_single_delete()
    {
        $id = absint($_GET['id'] ?? 0);
        $nonce_action = 'delete_discipline_rule_' . $id;

        if ($id && check_admin_referer($nonce_action)) {
            if (self::delete_discipline_rule_by_id($id)) {
                $message = 'Đã xóa quy tắc kỷ luật thành công!';
            } else {
                $message = 'Không thể xóa quy tắc kỷ luật.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_discipline_rule_message', $message, 10);
            wp_redirect(home_url('/aerp-discipline-rule'));
            exit;
        } else {
            error_log('AERP_HRM: Single delete discipline rule - Nonce verification failed or ID missing.');
        }
        wp_die('Invalid request or nonce.');
    }

    /**
     * Xóa quy tắc kỷ luật theo ID
     */
    public static function delete_discipline_rule_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_disciplinary_rules', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool) $deleted;
    }

    /**
     * Lấy tất cả quy tắc kỷ luật
     */
    public static function get_rules() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_disciplinary_rules ORDER BY id DESC");
    }

    /**
     * Lấy quy tắc kỷ luật theo ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_disciplinary_rules WHERE id = %d", $id));
    }
    public static function handle_late_tasks()
    {
        global $wpdb;

        $today = current_time('Y-m-d');

        $rules_table = $wpdb->prefix . 'aerp_hrm_disciplinary_rules';
        $logs_table  = $wpdb->prefix . 'aerp_hrm_disciplinary_logs';
        $tasks_table = $wpdb->prefix . 'aerp_hrm_tasks';

        // 1️⃣ Kiểm tra rule theo system_key
        $rule = $wpdb->get_row("
        SELECT * FROM $rules_table 
        WHERE system_key = 'late_task_auto'
        LIMIT 1
    ");

        if (!$rule) {
            // Nếu chưa có thì tạo mới
            $wpdb->insert($rules_table, [
                'rule_name'     => 'Tự động: Trễ deadline công việc',
                'penalty_point' => 3,
                'fine_amount'   => 100000,
                'system_key'    => 'late_task_auto',
                'created_at'    => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
            ]);
            $rule_id = $wpdb->insert_id;
        } else {
            $rule_id = $rule->id;
        }

        // 2️⃣ Lấy danh sách task trễ hạn chưa hoàn thành
        $tasks = $wpdb->get_results($wpdb->prepare("
        SELECT id, employee_id, deadline FROM $tasks_table
        WHERE status != 'done' AND deadline < %s
    ", $today));

        $added = 0;

        foreach ($tasks as $task) {
            // 3️⃣ Kiểm tra đã ghi log chưa
            $already_logged = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $logs_table
            WHERE employee_id = %d AND rule_id = %d AND date_violation = %s
        ", $task->employee_id, $rule_id, $task->deadline));

            if (!$already_logged) {
                // 4️⃣ Ghi nhận vi phạm
                $wpdb->insert($logs_table, [
                    'employee_id'    => $task->employee_id,
                    'rule_id'        => $rule_id,
                    'date_violation' => $task->deadline,
                    'created_at'     => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
                ]);
                $added++;
            }
        }

        return $added;
    }
}
