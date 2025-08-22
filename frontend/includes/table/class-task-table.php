<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Task_Table extends AERP_Frontend_Table
{
    protected $employee_id;

    public function __construct($args = [])
    {
        $this->employee_id = $args['employee_id'] ?? 0;

        $columns = [
            'task_title' => 'Tên công việc',
            'task_desc'  => 'Mô tả',
            'deadline'   => 'Hạn chót',
            'score'      => 'Điểm KPI',
            'status'     => 'Trạng thái',
            'comments'   => 'Phản hồi',
            'actions'    => 'Thao tác',
        ];

        $sortable = ['id', 'deadline', 'score'];
        $searchable = ['task_title', 'task_desc', 'status', 'score'];

        parent::__construct(array_merge([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_tasks',
            'columns' => $columns,
            'sortable_columns' => $sortable,
            'searchable_columns' => $searchable,
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => [],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-hrm-employees/?action=view&id=' . $this->employee_id . '&section=task'),
            'delete_item_callback' => ['AERP_Frontend_Task_Manager', 'delete_task_by_id'],
            'nonce_action_prefix' => 'delete_task_',
            'message_transient_key' => 'aerp_task_message',
            'hidden_columns_option_key' => 'aerp_hrm_task_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_task',
            'table_wrapper' => '#aerp-task-table-wrapper',
        ], $args));
    }

    protected function column_actions($item)
    {
        $edit_url = home_url("/aerp-hrm-employees/?action=view&id={$this->employee_id}&section=task&sub_action=edit&task_id={$item->id}");

        $delete_url = wp_nonce_url(
            home_url("/aerp-hrm-employees/?action=view&id={$this->employee_id}&section=task&sub_action=delete&task_id={$item->id}&employee_id={$this->employee_id}"),
            'delete_task_' . $item->id
        );

        return sprintf(
            '<a data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Chỉnh sửa" href="%s" class="btn btn-sm btn-success mb-2 mb-md-0"><i class="fas fa-edit"></i></a> 
         <a data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Xóa" href="%s" class="btn btn-sm btn-danger" onclick="return confirm(\'Bạn có chắc muốn xóa?\')"><i class="fas fa-trash"></i></a>',
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
        if (!empty($this->filters['status'])) {
            $filters[] = "status = %s";
            $params[] = $this->filters['status'];
        }
        if (!empty($this->filters['month'])) {
            $filters[] = "MONTH(deadline) = %d";
            $params[] = (int)$this->filters['month'];
        }
        if (!empty($this->filters['year'])) {
            $filters[] = "YEAR(deadline) = %d";
            $params[] = (int)$this->filters['year'];
        }
        return [$filters, $params];
    }
    public function get_tasks_by_month($month, $year)
    {
        return AERP_Task_Manager::get_tasks_by_month($this->employee_id, $month, $year);
    }
    protected function column_comments($item)
    {
        $count = AERP_Task_Manager::count_comments($item->id);
        return $count ? "$count phản hồi" : '—';
    }
    protected function column_status($item)
    {
        $status_badges = [
            'done' => '<span class="badge bg-success">Hoàn thành</span>',
            'assigned' => '<span class="badge bg-primary">Đã giao</span>',
            'failed' => '<span class="badge bg-danger">Thất bại</span>'
        ];

        return $status_badges[$item->status] ?? esc_html($item->status);
    }
}
