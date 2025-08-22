<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Discipline_Log_Table extends AERP_Frontend_Table
{
    protected $employee_id;

    public function __construct($args = [])
    {
        $this->employee_id = absint($args['employee_id'] ?? 0);

        $columns = [
            // 'id' => 'ID',
            'date_violation' => 'Ngày vi phạm',
            'rule_name'      => 'Lý do',
            'penalty_point'  => 'Điểm trừ',
            'fine_amount'    => 'Tiền phạt (VNĐ)',
        ];

        $sortable = ['id', 'date_violation', 'penalty_point', 'fine_amount'];
        $searchable = ['rule_name'];

        parent::__construct(array_merge([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_disciplinary_logs',
            'columns' => $columns,
            'sortable_columns' => $sortable,
            'searchable_columns' => $searchable,
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => [],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-hrm-employees/?action=view&id=' . $this->employee_id . '&section=discipline'),
            'delete_item_callback' => ['AERP_Frontend_Discipline_Log_Manager', 'delete_discipline_log_by_id'],
            'message_transient_key' => 'aerp_discipline_message',
            'ajax_action' => 'aerp_hrm_filter_discipline_log',
            'table_wrapper' => '#aerp-discipline-log-table-wrapper',
            'hidden_columns_option_key' => 'aerp_hrm_discipline_log_table_hidden_columns',
            'nonce_action_prefix' => 'delete_discipline_',

        ], $args));
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
        $filters = ["employee_id = %d"];
        $params = [$this->employee_id];

        if (!empty($this->filters['violation_month'])) {
            $start = date('Y-m-01', strtotime($this->filters['violation_month']));
            $end = date('Y-m-t', strtotime($this->filters['violation_month']));
            $filters[] = "date_violation BETWEEN %s AND %s";
            $params[] = $start;
            $params[] = $end;
        }

        return [$filters, $params];
    }
    public function get_items()
    {
        global $wpdb;
        $where = [];
        $params = [];

        // Lấy điều kiện filter từ cha
        if ($this->search_term && !empty($this->searchable_columns)) {
            $search_conditions = [];
            foreach ($this->searchable_columns as $column) {
                $search_conditions[] = "r.$column LIKE %s";
                $params[] = '%' . $wpdb->esc_like($this->search_term) . '%';
            }
            $where[] = '(' . implode(' OR ', $search_conditions) . ')';
        }

        list($extra_filters, $extra_filter_params) = $this->get_extra_filters();
        $where = array_merge($where, $extra_filters);
        $params = array_merge($params, $extra_filter_params);

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($this->current_page - 1) * $this->per_page;

        // Đếm tổng số bản ghi
        $total_query = "SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_disciplinary_logs l
                        LEFT JOIN {$wpdb->prefix}aerp_hrm_disciplinary_rules r ON l.rule_id = r.id
                        $where_clause";
        if (!empty($params)) {
            $total_query = $wpdb->prepare($total_query, $params);
        }
        $this->total_items = $wpdb->get_var($total_query);

        // Lấy dữ liệu có join sang bảng rules
        $query = "
            SELECT l.*, r.rule_name, r.penalty_point, r.fine_amount
            FROM {$wpdb->prefix}aerp_hrm_disciplinary_logs l
            LEFT JOIN {$wpdb->prefix}aerp_hrm_disciplinary_rules r ON l.rule_id = r.id
            $where_clause
            ORDER BY {$this->sort_column} {$this->sort_order}
            LIMIT %d OFFSET %d
        ";
        $params2 = array_merge($params, [$this->per_page, $offset]);
        $this->items = $wpdb->get_results($wpdb->prepare($query, $params2));

        return $this->items;
    }
    public function delete_discipline($id)
    {
        global $wpdb;
        return $wpdb->delete($wpdb->prefix . 'aerp_hrm_disciplinary_logs', ['id' => $id]);
    }

    protected function column_penalty_point($item)
    {
        return intval($item->penalty_point) . ' điểm';
    }
    public function column_fine_amount($item)
    {
        return '<strong style="color:red"> -' . number_format($item->fine_amount, 0, ',', '.') . ' đ</strong>';
    }
}
