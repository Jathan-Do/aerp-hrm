<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Task_Manager {

    public static function handle_form_submit() {
        if (!isset($_POST['aerp_save_task']) && !isset($_POST['aerp_edit_task'])) return;

        if (!wp_verify_nonce($_POST['aerp_task_nonce'], 'aerp_task_action')) {
            wp_die('Invalid nonce.');
        }

        global $wpdb;
        $employee_id = absint($_POST['employee_id'] ?? 0);
        if (!$employee_id) return;

        $task_title = sanitize_text_field($_POST['task_title']);
        $task_desc = sanitize_textarea_field($_POST['task_desc']);
        $deadline = sanitize_text_field($_POST['deadline']);
        $score = absint($_POST['score'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? 'assigned');

        if (isset($_POST['aerp_edit_task'])) {
            $edit_id = absint($_POST['edit_id']);
            $wpdb->update(
                $wpdb->prefix . 'aerp_hrm_tasks',
                [
                    'task_title' => $task_title,
                    'task_desc'  => $task_desc,
                    'deadline'   => $deadline,
                    'score'      => $score,
                    'status'     => $status,
                ],
                ['id' => $edit_id, 'employee_id' => $employee_id]
            );

            if (!empty($_POST['comment'])) {
                self::add_comment([
                    'task_id' => $edit_id,
                    'user_id' => get_current_user_id(),
                    'comment' => sanitize_textarea_field($_POST['comment']),
                ]);
            }
            $message = 'Cập nhật công việc thành công!';
        } else {
            $wpdb->insert(
                $wpdb->prefix . 'aerp_hrm_tasks',
                [
                    'employee_id' => $employee_id,
                    'task_title'  => $task_title,
                    'task_desc'   => $task_desc,
                    'deadline'    => $deadline,
                    'score'       => $score,
                    'status'      => $status,
                    'created_by'  => get_current_user_id(),
                    'created_at'  => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
                ]
            );
            $message = 'Thêm công việc thành công!';
        }

        aerp_clear_table_cache();
        set_transient('aerp_task_message', $message, 10);
        wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=task'));
        exit;
    }

    public static function handle_single_delete() {
        $task_id = absint($_GET['task_id'] ?? 0);
        $nonce_action = 'delete_task_' . $task_id;
        if ($task_id && check_admin_referer($nonce_action)) {
            if (self::delete_task_by_id($task_id)) {
                $message = 'Đã xóa công việc thành công!';
            } else {
                $message = 'Không thể xóa công việc.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_task_message', $message, 10);
            wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $_GET['employee_id'] . '&section=task'));
            exit;
        }
        wp_die('Invalid request or nonce.');
    }

    public static function delete_task_by_id($id) {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_tasks', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool)$deleted;
    }

    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}aerp_hrm_tasks WHERE id = %d",
            $id
        ));
    }

    public static function get_comments($task_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, u.display_name FROM {$wpdb->prefix}aerp_hrm_task_comments c
             LEFT JOIN {$wpdb->prefix}users u ON c.user_id = u.ID
             WHERE task_id = %d ORDER BY created_at ASC",
            $task_id
        ));
    }

    public static function add_comment($data) {
        global $wpdb;

        if (!$data['task_id'] || !$data['user_id'] || empty($data['comment'])) return false;

        return $wpdb->insert(
            $wpdb->prefix . 'aerp_hrm_task_comments',
            [
                'task_id'    => absint($data['task_id']),
                'user_id'    => absint($data['user_id']),
                'comment'    => sanitize_textarea_field($data['comment']),
                'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
            ]
        );
    }
}
