<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Employee_Table extends AERP_Frontend_Table
{
    public function __construct($args = [])
    {
        $columns = [
            'id'            => 'ID',
            'employee_code' => 'Mã NV',
            'full_name'     => 'Họ tên',
            'phone_number'  => 'Số ĐT',
            'email'         => 'Email',
            'work_location' => 'Chi nhánh',
            'department'    => 'Phòng ban',
            'position'      => 'Chức vụ',
            'status'        => 'Trạng thái',
            'current_points' => 'Điểm hiện tại',
            'created_at'    => 'Ngày tạo',
        ];
        $sortable = [
            'id',
            'full_name',
            'created_at',
            'status',
            'employee_code'
        ];
        $searchable = ['employee_code', 'full_name', 'phone_number', 'email'];
        parent::__construct(array_merge([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_employees',
            'columns' => $columns,
            'sortable_columns' => $sortable,
            'searchable_columns' => $searchable,
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => ['edit', 'delete'],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-hrm-employees'),
            'delete_item_callback' => ['AERP_Frontend_Employee_Manager', 'delete_employee_by_id'],
            'nonce_action_prefix' => 'delete_employee_',
            'message_transient_key' => 'aerp_employee_message',
            'hidden_columns_option_key' => 'aerp_hrm_employee_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_employees',
            'table_wrapper' => '#aerp-employee-table-wrapper',
        ], $args));
    }

    public function set_filters($filters = [])
    {
        parent::set_filters($filters);
    }

    protected function get_extra_filters()
    {
        $filters = [];
        $params = [];
        if (!empty($this->filters['department_id'])) {
            $filters[] = "department_id = %d";
            $params[] = (int)$this->filters['department_id'];
        }
        if (!empty($this->filters['status'])) {
            $filters[] = "status = %s";
            $params[] = $this->filters['status'];
        }
        if (!empty($this->filters['position_id'])) {
            $filters[] = "position_id = %d";
            $params[] = (int)$this->filters['position_id'];
        }
        if (!empty($this->filters['work_location_id'])) {
            $filters[] = "work_location_id = %d";
            $params[] = (int)$this->filters['work_location_id'];
        }
        if (!empty($this->filters['birthday_month'])) {
            $filters[] = "MONTH(birthday) = %d";
            $params[] = (int)$this->filters['birthday_month'];
        }
        if (!empty($this->filters['join_date_from'])) {
            $filters[] = "join_date >= %s";
            $params[] = $this->filters['join_date_from'];
        }
        if (!empty($this->filters['join_date_to'])) {
            $filters[] = "join_date <= %s";
            $params[] = $this->filters['join_date_to'];
        }
        if (!empty($this->filters['off_date_from'])) {
            $filters[] = "(off_date IS NOT NULL AND off_date >= %s)";
            $params[] = $this->filters['off_date_from'];
        }
        if (!empty($this->filters['off_date_to'])) {
            $filters[] = "(off_date IS NOT NULL AND off_date <= %s)";
            $params[] = $this->filters['off_date_to'];
        }
        return [$filters, $params];
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="bulk_items[]" value="%s" />', esc_attr($item->id));
    }

    protected function column_full_name($item)
    {
        $detail_url = home_url('/aerp-hrm-employees/?action=view&id=' . $item->id);
        return sprintf('<a class="text-decoration-none" href="%s">%s</a>', esc_url($detail_url), esc_html($item->full_name));
    }

    protected function column_status($item)
    {
        $statuses = [
            'active' => '<span class="badge bg-success">Đang làm</span>',
            'inactive' => '<span class="badge bg-secondary">Tạm nghỉ</span>',
            'resigned' => '<span class="badge bg-danger">Đã nghỉ</span>',
        ];
        return $statuses[$item->status] ?? esc_html($item->status);
    }

    protected function column_department($item)
    {
        $name = aerp_get_department_name($item->department_id);
        return $name ? esc_html($name) : '<span class="text-muted">--</span>';
    }

    protected function column_position($item)
    {
        $name = aerp_get_position_name($item->position_id);
        return $name ? esc_html($name) : '<span class="text-muted">--</span>';
    }

    protected function column_work_location($item)
    {
        $name = aerp_get_work_location_name($item->work_location_id);
        return $name ? esc_html($name) : '<span class="text-muted">--</span>';
    }

    protected function column_current_points($item)
    {
        return esc_html($item->current_points) . ' điểm';
    }
}
