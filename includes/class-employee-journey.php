<?php

if (!defined('ABSPATH')) exit;

/**
 * Quản lý Hành trình Nhân sự (Employee Journey)
 */
class AERP_HRM_Employee_Journey
{
    private $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'aerp_hrm_employee_journey';
    }

    /**
     * Ghi lại một sự kiện vào hành trình nhân sự
     */
    public function add_event($employee_id, $event_type, $old_value = '', $new_value = '', $note = '')
    {
        global $wpdb;

        $wpdb->insert($this->table, [
            'employee_id' => $employee_id,
            'event_type'  => sanitize_text_field($event_type),
            'old_value'   => maybe_serialize($old_value),
            'new_value'   => maybe_serialize($new_value),
            'note'        => sanitize_textarea_field($note),
            'created_at'  => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Lấy danh sách hành trình theo thời gian của nhân viên
     */
    public function get_timeline($employee_id)
    {
        global $wpdb;

        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE employee_id = %d ORDER BY created_at ASC", $employee_id),
            OBJECT // ✅ trả về object thay vì ARRAY_A
        );

        // Giải nén old/new nếu cần
        foreach ($rows as &$row) {
            $row->old_value = maybe_unserialize($row->old_value);
            $row->new_value = maybe_unserialize($row->new_value);
        }

        return $rows;
    }


    /**
     * Xoá toàn bộ hành trình của nhân viên (nếu cần reset)
     */
    public function delete_all($employee_id)
    {
        global $wpdb;
        return $wpdb->delete($this->table, ['employee_id' => absint($employee_id)]);
    }
}
