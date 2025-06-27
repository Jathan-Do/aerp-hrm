<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Attendance_Manager
{
    public static function handle_form_submit()
    {
        if (!isset($_POST['aerp_save_attendance'])) return;
        if (!wp_verify_nonce($_POST['aerp_save_attendance_nonce'], 'aerp_save_attendance_action')) {
            wp_die('Invalid nonce for attendance save.');
        }
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_attendance';
        $employee_id = absint($_POST['employee_id']);
        $work_date   = sanitize_text_field($_POST['work_date']);
        $shift_type  = sanitize_text_field($_POST['shift_type']);
        $work_ratio  = floatval($_POST['work_ratio']);
        $note        = sanitize_text_field($_POST['note']);
        $id = isset($_POST['attendance_id']) ? absint($_POST['attendance_id']) : 0;
        // Kiểm tra trùng (trừ chính bản ghi khi update)
        $is_duplicate = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE employee_id = %d AND work_date = %s" . ($id ? " AND id != %d" : ""),
            $id ? [$employee_id, $work_date, $id] : [$employee_id, $work_date]
        ));
        if ($is_duplicate > 0) {
            set_transient('aerp_attendance_message', '⚠️ Đã tồn tại chấm công khác của nhân viên này cho ngày đó.', 10);
            wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=attendance'));
            exit;
        }
        $shift = ($shift_type === 'off') ? 'off' : 'ot';
        if ($id) {
            $wpdb->update($table, [
                'work_date'   => $work_date,
                'shift'       => $shift,
                'work_ratio'  => $work_ratio,
                'note'        => $note,
            ], ['id' => $id]);
            $msg = 'Đã cập nhật chấm công!';
        } else {
            $wpdb->insert($table, [
                'employee_id' => $employee_id,
                'work_date'   => $work_date,
                'shift'       => $shift,
                'work_ratio'  => $work_ratio,
                'note'        => $note,
                'created_at'  => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
            ]);
            $msg = 'Đã thêm chấm công!';
        }
        aerp_clear_table_cache();
        set_transient('aerp_attendance_message', $msg, 10);
        wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=attendance'));
        exit;
    }

    public static function handle_single_delete()
    {
        $id = absint($_GET['attendance_id'] ?? 0);
        $nonce_action = 'delete_attendance_' . $id;
        if ($id && check_admin_referer($nonce_action)) {
            if (self::delete_by_id($id)) {
                $message = 'Đã xóa chấm công thành công!';
            } else {
                $message = 'Không thể xóa chấm công.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_attendance_message', $message, 10);
            wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $_GET['employee_id'] . '&section=attendance'));
            exit;
        } else {
            error_log('AERP_HRM: Single delete attendance - Nonce verification failed or ID missing.');
        }
        wp_die('Invalid request or nonce.');
    }

    public static function delete_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_attendance', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool) $deleted;
    }

    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_attendance ORDER BY id DESC");
    }
    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_attendance WHERE id = %d", $id));
    }

    public static function get_attendance_by_employee($employee_id, $month = null)
    {
        global $wpdb;

        $where = "employee_id = %d";
        $args  = [$employee_id];

        if ($month) {
            $start = date('Y-m-01', strtotime($month));
            $end   = date('Y-m-t', strtotime($month));
            $where .= " AND work_date BETWEEN %s AND %s";
            $args[] = $start;
            $args[] = $end;
        }

        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_attendance WHERE $where ORDER BY work_date DESC", ...$args));
    }
}
