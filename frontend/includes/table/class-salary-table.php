<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Salary_Table extends AERP_Frontend_Table
{
    protected $employee_id;

    public function __construct($args = [])
    {
        $this->employee_id = $args['employee_id'] ?? 0;
        $columns = [
            'salary_month'     => 'Tháng',
            'base_salary'      => 'Lương cơ bản',
            'bonus'            => 'Thưởng',
            'auto_bonus'       => 'Thưởng động',
            'deduction'        => 'Phạt',
            'advance_paid'     => 'Ứng lương',
            'salary_per_day'   => 'Công/ngày',
            'work_days'        => 'Ngày công',
            'actual_work_days' => 'Ngày thực tế',
            'off_days'         => 'Ngày nghỉ',
            'ot_days'          => 'Tăng ca',
            'final_salary'     => 'Thực lãnh',
            'ranking'          => 'Xếp loại',
            'points_total'     => 'Điểm',
            'note'             => 'Ghi chú',
            'created_at'       => 'Ngày tạo',
        ];
        $sortable = [
            'id',
            'salary_month',
            'final_salary',
            'base_salary',
            'deduction',
            'created_at',
        ];
        $searchable = ['note', 'salary_month', 'ranking'];
        parent::__construct(array_merge([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_salaries',
            'columns' => $columns,
            'sortable_columns' => $sortable,
            'searchable_columns' => $searchable,
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => [],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-hrm-employees/?action=view&id=' . $this->employee_id . '&section=salary'),
            'delete_item_callback' => ['AERP_Frontend_Salary_Manager', 'delete_by_id'],
            'nonce_action_prefix' => 'delete_salary_',
            'message_transient_key' => 'aerp_salary_message',
            'hidden_columns_option_key' => 'aerp_hrm_salary_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_salary',
            'table_wrapper' => '#aerp-salary-table-wrapper',
        ], $args));
    }

    protected function column_salary_month($item)
    {
        return date('m/Y', strtotime($item->salary_month));
    }

    protected function column_base_salary($item)
    {
        return number_format((float)$item->base_salary, 0, ',', '.') . ' đ';
    }
    protected function column_final_salary($item)
    {
        return number_format((float)$item->final_salary, 0, ',', '.') . ' đ';
    }
    protected function column_auto_bonus($item)
    {
        return number_format((float)$item->auto_bonus, 0, ',', '.') . ' đ';
    }
    protected function column_bonus($item)
    {
        return number_format((float)$item->bonus, 0, ',', '.') . ' đ';
    }
    protected function column_deduction($item)
    {
        return number_format((float)$item->deduction, 0, ',', '.') . ' đ';
    }
    protected function column_advance_paid($item)
    {
        return number_format((float)$item->advance_paid, 0, ',', '.') . ' đ';
    }
    protected function column_salary_per_day($item)
    {
        return number_format((float)$item->salary_per_day, 0, ',', '.') . ' đ';
    }
    protected function column_ot_days($item)
    {
        return number_format((float)$item->ot_days, 1, ',', '.');
    }
    protected function column_points_total($item)
    {
        return esc_html($item->points_total) . ' điểm';
    }
    protected function column_ranking($item)
    {
        return esc_html($item->ranking);
    }

    public function set_filters($filters = [])
    {
        parent::set_filters($filters);
        if (!empty($filters['employee_id'])) {
            $this->employee_id = $filters['employee_id'];
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
        // Nếu muốn lọc thêm theo tháng lương:
        if (!empty($this->filters['salary_month'])) {
            $filters[] = "DATE_FORMAT(salary_month, '%Y-%m') = %s";
            $params[] = $this->filters['salary_month'];
        }
        return [$filters, $params];
    }
}
