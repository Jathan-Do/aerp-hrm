<?php
if (!defined('ABSPATH')) exit;

class AERP_Task_Manager
{
    // ========================
    // ADMIN HANDLE
    // ========================
    public static function handle_submit()
    {
        if (!current_user_can('manage_options')) return;

        if (isset($_POST['aerp_add_task']) && check_admin_referer('aerp_add_task_action', 'aerp_add_task_nonce')) {
            self::insert_task();
        }

        if (isset($_POST['aerp_update_task']) && check_admin_referer('aerp_edit_task_action', 'aerp_edit_task_nonce')) {
            self::update_task();
        }

        if (isset($_POST['aerp_add_task_comment']) && check_admin_referer('aerp_comment_task_action', 'aerp_comment_task_nonce')) {
            self::add_comment([
                'task_id' => absint($_POST['task_id']),
                'user_id' => get_current_user_id(),
                'comment' => sanitize_textarea_field($_POST['comment']),
            ]);
        }
    }

    protected static function insert_task()
    {
        global $wpdb;

        $wpdb->insert($wpdb->prefix . 'aerp_hrm_tasks', [
            'employee_id' => absint($_POST['employee_id']),
            'task_title' => sanitize_text_field($_POST['task_title']),
            'task_desc' => sanitize_textarea_field($_POST['task_desc']),
            'deadline' => sanitize_text_field($_POST['deadline']),
            'score' => absint($_POST['score']),
            'status' => sanitize_text_field($_POST['status']),
            'created_by' => get_current_user_id(),
            'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
        ]);

        add_action('admin_notices', function () {
            echo '<div class="updated"><p>Đã giao việc thành công.</p></div>';
        });
    }

    protected static function update_task()
    {
        global $wpdb;

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        if (!$id) return;

        $wpdb->update(
            $wpdb->prefix . 'aerp_hrm_tasks',
            [
                'task_title' => sanitize_text_field($_POST['task_title']),
                'task_desc' => sanitize_textarea_field($_POST['task_desc']),
                'deadline' => sanitize_text_field($_POST['deadline']),
                'score' => absint($_POST['score']),
                'status' => sanitize_text_field($_POST['status']),
            ],
            ['id' => $id]
        );
        // ✅ Ghi phản hồi nếu có
        if (!empty($_POST['comment'])) {
            self::add_comment([
                'task_id' => $id,
                'user_id' => get_current_user_id(),
                'comment' => sanitize_textarea_field($_POST['comment']),
            ]);
        }
        add_action('admin_notices', function () {
            echo '<div class="updated"><p>Đã cập nhật công việc.</p></div>';
        });
    }

    public static function handle_delete()
    {
        if (
            isset($_GET['page'], $_GET['delete_task'], $_GET['_wpnonce']) &&
            $_GET['page'] === 'aerp_employees' &&
            wp_verify_nonce($_GET['_wpnonce'], 'aerp_delete_task_' . $_GET['delete_task'])
        ) {
            global $wpdb;
            $id = absint($_GET['delete_task']);
            $wpdb->delete($wpdb->prefix . 'aerp_hrm_tasks', ['id' => $id]);

            $employee_id = absint($_GET['view'] ?? 0);
            wp_redirect(admin_url('admin.php?page=aerp_employees&view=' . $employee_id . '&deleted=1'));
            exit;
        }
    }

    // ========================
    // FRONTEND
    // ========================

    public static function add($data)
    {
        global $wpdb;
        return $wpdb->insert($wpdb->prefix . 'aerp_hrm_tasks', [
            'employee_id' => absint($data['employee_id']),
            'task_title' => sanitize_text_field($data['task_title']),
            'task_desc' => sanitize_textarea_field($data['task_desc']),
            'deadline' => sanitize_text_field($data['deadline']),
            'score' => isset($data['score']) ? absint($data['score']) : 0,
            'status' => 'assigned',
            'created_by' => absint($data['created_by']),
            'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
        ]);
    }

    public static function update_status($task_id, $employee_id, $new_status)
    {
        global $wpdb;

        // Chỉ update nếu task thuộc nhân viên đang login
        $task = self::get_by_id($task_id);
        if (!$task || $task->employee_id != $employee_id) return false;

        return $wpdb->update(
            $wpdb->prefix . 'aerp_hrm_tasks',
            ['status' => sanitize_text_field($new_status)],
            ['id' => $task_id]
        );
    }

    public static function add_comment($data)
    {
        global $wpdb;

        if (!$data['task_id'] || !$data['user_id'] || empty($data['comment'])) return false;

        return $wpdb->insert(
            $wpdb->prefix . 'aerp_hrm_task_comments',
            [
                'task_id' => absint($data['task_id']),
                'user_id' => absint($data['user_id']),
                'comment' => sanitize_textarea_field($data['comment']),
                'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
            ]
        );
    }

    // ========================
    // GETTERS
    // ========================

    public static function get_by_employee($employee_id)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}aerp_hrm_tasks WHERE employee_id = %d ORDER BY deadline DESC",
            $employee_id
        ));
    }
    public static function get_tasks_by_employee($employee_id)
    {
        return self::get_by_employee($employee_id);
    }

    public static function get_by_id($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}aerp_hrm_tasks WHERE id = %d",
            $id
        ));
    }

    public static function get_open_tasks($employee_id)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}aerp_hrm_tasks WHERE employee_id = %d AND status != 'done' ORDER BY deadline ASC",
            $employee_id
        ));
    }

    public static function get_comments($task_id)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, u.display_name FROM {$wpdb->prefix}aerp_hrm_task_comments c
             LEFT JOIN {$wpdb->prefix}users u ON c.user_id = u.ID
             WHERE task_id = %d ORDER BY created_at ASC",
            $task_id
        ));
    }

    public static function count_comments($task_id)
    {
        global $wpdb;
        return (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_task_comments WHERE task_id = %d",
            $task_id
        ));
    }

    public static function search_tasks_by_employee($employee_id, $args = [])
    {
        global $wpdb;

        $where = ["employee_id = %d"];
        $params = [$employee_id];

        if (!empty($args['keyword'])) {
            $where[] = "task_title LIKE %s";
            $params[] = '%' . $wpdb->esc_like($args['keyword']) . '%';
        }

        if (!empty($args['status'])) {
            $where[] = "status = %s";
            $params[] = $args['status'];
        }

        $sql = "SELECT * FROM {$wpdb->prefix}aerp_hrm_tasks WHERE " . implode(' AND ', $where) . " ORDER BY deadline DESC";

        // Phân trang
        if (isset($args['limit']) && isset($args['offset'])) {
            $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }

        return $wpdb->get_results($wpdb->prepare($sql, ...$params));
    }

    public static function count_tasks_by_employee($employee_id, $args = [])
    {
        global $wpdb;

        $where = ["employee_id = %d"];
        $params = [$employee_id];

        if (!empty($args['keyword'])) {
            $where[] = "task_title LIKE %s";
            $params[] = '%' . $wpdb->esc_like($args['keyword']) . '%';
        }

        if (!empty($args['status'])) {
            $where[] = "status = %s";
            $params[] = $args['status'];
        }

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_tasks WHERE " . implode(' AND ', $where);
        return (int) $wpdb->get_var($wpdb->prepare($sql, ...$params));
    }

    public static function get_tasks_by_month($employee_id, $month, $year)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_tasks';
        
        $start_date = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
        $end_date = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table 
            WHERE employee_id = %d 
            AND deadline BETWEEN %s AND %s
            ORDER BY deadline ASC",
            $employee_id,
            $start_date,
            $end_date
        ));
    }
}
