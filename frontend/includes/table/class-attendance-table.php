<?php
if (!defined('ABSPATH')) {
    exit;
}
class AERP_Frontend_Attendance_Table extends AERP_Frontend_Table
{
    protected $employee_id;
    public function __construct($args = [])
    {
        $this->employee_id = $args['employee_id'] ?? 0;
        parent::__construct([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_attendance',
            'columns' => [
                'id' => 'ID',
                'work_date'   => 'Ngày chấm công',
                'shift'       => 'Loại chấm công',
                'work_ratio'  => 'Hệ số',
                'note'        => 'Ghi chú',
                'actions'     => 'Hành động',
            ],
            'sortable_columns' => ['work_date', 'work_ratio'],
            'searchable_columns' => ['note', 'shift'],
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => [],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-hrm-employees/?action=view&id=' . $this->employee_id . '&section=attendance'),
            'delete_item_callback' => ['AERP_Frontend_Attendance_Manager', 'delete_by_id'],
            'nonce_action_prefix' => 'delete_attendance_',
            'message_transient_key' => 'aerp_attendance_message',
            'hidden_columns_option_key' => 'aerp_hrm_attendance_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_attendance',
            'table_wrapper' => '#aerp-attendance-table-wrapper',
        ]);
    }
    protected function column_actions($item)
    {
        $edit_url = home_url("/aerp-hrm-employees/?action=view&id={$this->employee_id}&section=attendance&sub_action=edit&attendance_id={$item->id}");
        $delete_url = wp_nonce_url(
            home_url("/aerp-hrm-employees/?action=view&id={$this->employee_id}&section=attendance&sub_action=delete&attendance_id={$item->id}&employee_id={$this->employee_id}"),
            'delete_attendance_' . $item->id
        );
        return sprintf(
            '<a href="%s" class="btn btn-sm btn-success mb-2 mb-md-0"><i class="fas fa-edit"></i></a> 
         <a href="%s" class="btn btn-sm btn-danger" onclick="return confirm(\'Bạn có chắc muốn xóa?\')"><i class="fas fa-trash"></i></a>',
            esc_url($edit_url),
            esc_url($delete_url)
        );
    }
    public function set_filters($filters = [])
    {
        parent::set_filters($filters);
        if (!empty($filters['employee_id'])) {
            $this->employee_id = absint($filters['employee_id']);
        }
    }
    protected function get_extra_filters()
    {
        $filters = [];
        $params = [];

        if (!empty($this->employee_id)) {
            $filters[] = "employee_id = %d";
            $params[] = $this->employee_id;
        }
        if (!empty($this->filters['work_date'])) {
            $filters[] = "DATE_FORMAT(work_date, '%Y-%m') = %s";
            $params[] = $this->filters['work_date'];
        }
        if (!empty($this->filters['shift'])) {
            $filters[] = "shift = %s";
            $params[] = $this->filters['shift'];
        }
        return [$filters, $params];
    }
    protected function column_work_date($item)
    {
        return date('d/m/Y', strtotime($item->work_date));
    }
    protected function column_shift($item)
    {
        $shift = $item->shift;
        $badge = '';
        
        if ($shift === 'off') {
            $badge = '<span class="badge bg-secondary">OFF</span>';
        } else if ($shift === 'ot') {
            $badge = '<span class="badge bg-warning">OT</span>';
        } else {
            $badge = $shift;
        }
        
        return $badge;
    }
}
