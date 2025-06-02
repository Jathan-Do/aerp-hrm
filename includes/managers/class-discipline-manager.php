<?php
if (!defined('ABSPATH')) exit;

class AERP_Discipline_Manager
{
    public static function handle_submit()
    {
        if (!current_user_can('manage_options')) return;

        if (
            isset($_POST['aerp_add_discipline']) &&
            check_admin_referer('aerp_add_discipline_action', 'aerp_add_discipline_nonce')
        ) {
            global $wpdb;

            $employee_id = absint($_POST['employee_id']);
            $rule_id     = absint($_POST['rule_id']);
            $date        = sanitize_text_field($_POST['date_violation']);

            if ($employee_id && $rule_id && $date) {
                $wpdb->insert($wpdb->prefix . 'aerp_hrm_disciplinary_logs', [
                    'employee_id'     => $employee_id,
                    'rule_id'         => $rule_id,
                    'date_violation'  => $date,
                    'created_at'      => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
                ]);

                add_action('admin_notices', function () {
                    echo '<div class="updated"><p>✅ Đã ghi nhận vi phạm.</p></div>';
                });
            }
        }
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
