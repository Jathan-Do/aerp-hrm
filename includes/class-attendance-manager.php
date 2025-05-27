<?php
if (!defined('ABSPATH')) exit;

class AERP_Attendance_Manager
{
    public static function handle_submit()
    {
        if (!current_user_can('manage_options')) return;

        if (isset($_POST['aerp_add_attendance']) && check_admin_referer('aerp_add_attendance_action', 'aerp_add_attendance_nonce')) {
            self::insert();
        }

        if (isset($_POST['aerp_update_attendance']) && check_admin_referer('aerp_edit_attendance_action', 'aerp_edit_attendance_nonce')) {
            self::update();
        }
    }

    public static function handle_delete()
    {
        if (
            isset($_GET['delete_attendance'], $_GET['_wpnonce']) &&
            wp_verify_nonce($_GET['_wpnonce'], 'aerp_delete_attendance_' . $_GET['delete_attendance'])
        ) {
            global $wpdb;
            $id = absint($_GET['delete_attendance']);
            $wpdb->delete($wpdb->prefix . 'aerp_hrm_attendance', ['id' => $id]);
        }
    }

    protected static function insert()
    {
        global $wpdb;

        $employee_id = absint($_POST['employee_id']);
        $work_date   = sanitize_text_field($_POST['work_date']);

        // ✅ Kiểm tra trùng
        if (self::check_duplicate($employee_id, $work_date)) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>⚠️ Nhân viên này đã được chấm công ngày đó.</p></div>';
            });
            return;
        }

        $wpdb->insert($wpdb->prefix . 'aerp_hrm_attendance', [
            'employee_id' => $employee_id,
            'work_date'   => $work_date,
            'shift'       => sanitize_text_field($_POST['shift']),
            'work_ratio'  => floatval($_POST['work_ratio']),
            'note'        => sanitize_text_field($_POST['note']),
            'created_at'  => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
        ]);

        add_action('admin_notices', function () {
            echo '<div class="updated"><p>✅ Đã thêm chấm công.</p></div>';
        });
    }


    protected static function update()
    {
        global $wpdb;

        $id          = absint($_POST['id']);
        $employee_id = absint($_POST['employee_id']);
        $work_date   = sanitize_text_field($_POST['work_date']);

        // ✅ Kiểm tra trùng – trừ chính bản ghi hiện tại
        $is_duplicate = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_attendance
        WHERE employee_id = %d AND work_date = %s AND id != %d
    ", $employee_id, $work_date, $id));

        if ($is_duplicate > 0) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>⚠️ Đã tồn tại chấm công khác của nhân viên này cho ngày đó.</p></div>';
            });
            return;
        }

        $wpdb->update($wpdb->prefix . 'aerp_hrm_attendance', [
            'work_date'   => $work_date,
            'shift'       => sanitize_text_field($_POST['shift']),
            'work_ratio'  => floatval($_POST['work_ratio']),
            'note'        => sanitize_text_field($_POST['note']),
        ], ['id' => $id]);

        add_action('admin_notices', function () {
            echo '<div class="updated"><p>✅ Đã cập nhật chấm công.</p></div>';
        });
    }


    public static function get_by_id($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}aerp_hrm_attendance WHERE id = %d",
            $id
        ));
    }

    public static function save_attendance($employee_id, $date, $shift, $ratio, $note = '')
    {
        global $wpdb;

        if (self::check_duplicate($employee_id, $date)) {
            return false;
        }

        return $wpdb->insert($wpdb->prefix . 'aerp_hrm_attendance', [
            'employee_id' => $employee_id,
            'work_date'   => $date,
            'shift'       => $shift,
            'work_ratio'  => $ratio,
            'note'        => $note,
            'created_at'  => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
        ]);
    }

    public static function check_duplicate($employee_id, $date)
    {
        global $wpdb;

        return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_attendance WHERE employee_id = %d AND work_date = %s", $employee_id, $date)) > 0;
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
