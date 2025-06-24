<?php
if (!defined('ABSPATH')) exit;
class AERP_Frontend_Salary_Summary_Table extends AERP_Frontend_Table
{
    public function __construct()
    {
        parent::__construct([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_salaries',
            'columns' => [
                'employee_code' => 'Mã NV',
                'full_name'     => 'Họ tên',
                'email'         => 'Email',
                'bank_name'     => 'Ngân hàng',
                'bank_account'  => 'Số TK',
                'salary_month'  => 'Tháng',
                'base_salary'   => 'Lương cơ bản',
                'bonus'         => 'Thưởng',
                'deduction'     => 'Phạt',
                'advance_paid'  => 'Tạm ứng',
                'final_salary'  => 'Lương cuối',
                'points_total'  => 'Điểm',
            ],
            'sortable_columns' => [
                'full_name',
                'employee_code',
                'bank_account',
                'email',
                'salary_month',
                'bonus',
                'deduction',
                'advance_paid',
                'final_salary',
                'points_total'
            ],
            'searchable_columns' => ['employee_code', 'full_name', 'bank_account', 'email'],
            'primary_key' => 'id',
            'per_page' => 20,
            'actions' => [],
            'bulk_actions' => [],
            'base_url' => home_url('/aerp-salary-summary'),
            'hidden_columns_option_key' => 'aerp_salary_summary_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_salary_summary',
            'table_wrapper' => '#aerp-salary-summary-table-wrapper',

        ]);
    }

    protected function get_extra_filters()
    {
        $filters = [];
        $params = [];

        if (!empty($this->filters['salary_month'])) {
            $filters[] = "DATE_FORMAT(s.salary_month, '%Y-%m') = %s";
            $params[] = $this->filters['salary_month'];
        }

        return [$filters, $params];
    }

    public function get_items()
    {
        global $wpdb;

        $where = [];
        $params = [];

        // Lọc theo tháng lương
        if (!empty($this->filters['salary_month'])) {
            $where[] = "DATE_FORMAT(s.salary_month, '%Y-%m') = %s";
            $params[] = $this->filters['salary_month'];
        }

        // Tìm kiếm
        if (!empty($this->search_term)) {
            $where[] = "(e.full_name LIKE %s OR e.email LIKE %s)";
            $params[] = '%' . $wpdb->esc_like($this->search_term) . '%';
            $params[] = '%' . $wpdb->esc_like($this->search_term) . '%';
        }

        $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Sắp xếp
        $allowed_sorts = ['salary_month', 'base_salary', 'bonus', 'deduction', 'final_salary', 'full_name'];
        $orderby = in_array($this->sort_column, $allowed_sorts) ? $this->sort_column : 'full_name';
        $order = strtoupper($this->sort_order) === 'DESC' ? 'DESC' : 'ASC';
        $orderby_sql = in_array($orderby, ['full_name']) ? "e.$orderby" : "s.$orderby";

        // Giới hạn
        $limit = intval($this->per_page);
        $offset = ($this->current_page - 1) * $limit;

        // Query tổng số
        $sql_count = "
            SELECT COUNT(*)
            FROM {$wpdb->prefix}aerp_hrm_salaries s
            LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON s.employee_id = e.id
            $where_sql
        ";
        $this->total_items = !empty($params) ? $wpdb->get_var($wpdb->prepare($sql_count, ...$params)) : $wpdb->get_var($sql_count);

        // Query dữ liệu
        $sql = "
            SELECT 
                s.*, 
                e.employee_code, e.full_name, e.email, e.bank_account, e.bank_name
            FROM {$wpdb->prefix}aerp_hrm_salaries s
            LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON s.employee_id = e.id
            $where_sql
            ORDER BY $orderby_sql $order
            LIMIT %d OFFSET %d
        ";

        $params2 = array_merge($params, [$limit, $offset]);
        $this->items = $wpdb->get_results($wpdb->prepare($sql, ...$params2));

        return $this->items;
    }


    public function column_salary_month($item)
    {
        return date('m/Y', strtotime($item->salary_month));
    }
    public function column_base_salary($item)
    {
        return number_format($item->base_salary, 0, ',', '.') . ' đ';
    }
    public function column_bonus($item)
    {
        return number_format($item->bonus, 0, ',', '.') . ' đ';
    }
    public function column_deduction($item)
    {
        return number_format($item->deduction, 0, ',', '.') . ' đ';
    }
    public function column_advance_paid($item)
    {
        return number_format($item->advance_paid, 0, ',', '.') . ' đ';
    }
    public function column_final_salary($item)
    {
        return '<strong>' . number_format($item->final_salary, 0, ',', '.') . ' đ</strong>';
    }
    public function column_full_name($item)
    {
        $view_url = home_url('/aerp-salary-summary/?action=view&id=' . $item->id);
        return '<a href="' . esc_url($view_url) . '"><strong>' . esc_html($item->full_name) . '</strong></a>';
    }
}
