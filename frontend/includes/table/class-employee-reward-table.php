<?php
if (!defined('ABSPATH')) {
    exit;
}
class AERP_Frontend_Employee_Reward_Table extends AERP_Frontend_Table
{
    protected $employee_id;
    public function __construct($args = [])
    {
        $this->employee_id = $args['employee_id'] ?? 0;
        parent::__construct([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_employee_rewards',
            'columns' => [
                'id' => 'ID',
                'month'     => 'Ngày thưởng',
                'reward'    => 'Tên thưởng',
                'amount'    => 'Số tiền',
                'note'      => 'Ghi chú',
                'actions'    => 'Thao tác',
            ],
            'sortable_columns' => ['month', 'reward', 'amount'],
            'searchable_columns' => ['reward', 'amount', 'note'],
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => [],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-hrm-employees/?action=view&id=' . $this->employee_id . '&section=reward'),
            'delete_item_callback' => ['AERP_Frontend_Employee_Reward_Manager', 'delete_by_id'],
            'nonce_action_prefix' => 'delete_employee_reward_',
            'message_transient_key' => 'aerp_employee_reward_message',
            'hidden_columns_option_key' => 'aerp_hrm_employee_reward_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_employee_reward',
            'table_wrapper' => '#aerp-employee-reward-table-wrapper',
        ]);
    }
    protected function column_actions($item)
    {
        $edit_url = home_url("/aerp-hrm-employees/?action=view&id={$this->employee_id}&section=reward&sub_action=edit&employee_reward_id={$item->id}");
        $delete_url = wp_nonce_url(
            home_url("/aerp-hrm-employees/?action=view&id={$this->employee_id}&section=reward&sub_action=delete&employee_reward_id={$item->id}&employee_id={$this->employee_id}"),
            'delete_employee_reward_' . $item->id
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
        if (!empty($this->filters['month'])) {
            $filters[] = "DATE_FORMAT(month, '%Y-%m') = %s";
            $params[] = $this->filters['month'];
        }
        return [$filters, $params];
    }
    public function column_amount($item)
    {
        return '<strong style="color:green"> +' . number_format($item->amount, 0, ',', '.') . ' đ</strong>';
    }
    protected function get_extra_search_conditions($search_term)
    {
        $search_conditions = [];
        $params = [];
        foreach ($this->searchable_columns as $column) {
            if ($column === 'reward') {
                $search_conditions[] = "rd.name LIKE %s";
            } elseif ($column === 'amount') {
                $search_conditions[] = "rd.amount LIKE %s";
            } elseif ($column === 'note') {
                $search_conditions[] = "er.note LIKE %s";
            }
            $params[] = '%' . esc_sql($search_term) . '%';
        }
        return [$search_conditions, $params];
    }
    public function get_items()
    {
        global $wpdb;
        $where = [];
        $params = [];

        // Search liên bảng
        if ($this->search_term && !empty($this->searchable_columns)) {
            list($search_conditions, $search_params) = $this->get_extra_search_conditions($this->search_term);
            if (!empty($search_conditions)) {
                $where[] = '(' . implode(' OR ', $search_conditions) . ')';
                $params = array_merge($params, $search_params);
            }
        }

        list($extra_filters, $extra_filter_params) = $this->get_extra_filters();
        $where = array_merge($where, $extra_filters);
        $params = array_merge($params, $extra_filter_params);

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($this->current_page - 1) * $this->per_page;

        // Đếm tổng số bản ghi
        $total_query = "SELECT COUNT(*) 
                        FROM {$wpdb->prefix}aerp_hrm_employee_rewards er
                        LEFT JOIN {$wpdb->prefix}aerp_hrm_reward_definitions rd ON er.reward_id = rd.id
                        $where_clause";
        if (!empty($params)) {
            $total_query = $wpdb->prepare($total_query, $params);
        }
        $this->total_items = $wpdb->get_var($total_query);

        // Lấy dữ liệu có join sang bảng reward_definitions
        $query = "
            SELECT er.id, er.month, rd.name as reward, rd.amount, er.note, er.created_at
            FROM {$wpdb->prefix}aerp_hrm_employee_rewards er
            LEFT JOIN {$wpdb->prefix}aerp_hrm_reward_definitions rd ON er.reward_id = rd.id
            $where_clause
            ORDER BY {$this->sort_column} {$this->sort_order}
            LIMIT %d OFFSET %d
        ";
        $params2 = array_merge($params, [$this->per_page, $offset]);
        $this->items = $wpdb->get_results($wpdb->prepare($query, $params2));

        return $this->items;
    }
}
