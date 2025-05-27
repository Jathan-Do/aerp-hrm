<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class AERP_Discipline_Log_Table extends AERP_Base_Table
{
    protected $employee_id;

    public function __construct($employee_id)
    {
        $this->employee_id = absint($employee_id);
        parent::__construct([
            'singular' => 'discipline',
            'plural'   => 'disciplines',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'             => '<input type="checkbox" />',
            'date_violation' => 'Ngày vi phạm',
            'rule_name'      => 'Lý do',
            'penalty_point'  => 'Điểm trừ',
            'fine_amount'    => 'Tiền phạt (VNĐ)',
        ];
    }
    public function column_cb($item)
    {
        return '<input type="checkbox" name="id[]" value="' . esc_attr($item['id']) . '" />';
    }


    public function get_sortable_columns()
    {
        return [
            'date_violation' => ['date_violation', true],
            'penalty_point'  => ['penalty_point', false],
            'fine_amount'    => ['fine_amount', false],
        ];
    }

    public function get_searchable_columns()
    {
        return ['rule_name'];
    }

    public function get_primary_column_name()
    {
        return 'rule_name';
    }

    public function prepare_items()
    {
        global $wpdb;

        $month = $_GET['violation_month'] ?? '';
        $orderby = $_REQUEST['orderby'] ?? '';
        $order   = $_REQUEST['order'] ?? 'desc';
        $search  = $_REQUEST['s'] ?? '';

        $where_clauses = ["l.employee_id = {$this->employee_id}"];

        if (!empty($month)) {
            $start = date('Y-m-01', strtotime($month));
            $end   = date('Y-m-t', strtotime($month));
            $where_clauses[] = $wpdb->prepare("l.date_violation BETWEEN %s AND %s", $start, $end);
        }

        if (!empty($search)) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where_clauses[] = $wpdb->prepare("r.rule_name LIKE %s", $like);
        }

        $where = implode(' AND ', $where_clauses);
        $order_by = "l.date_violation DESC";
        if ($orderby && in_array($orderby, ['date_violation', 'penalty_point', 'fine_amount'])) {
            $order_by = sanitize_sql_orderby("{$orderby} " . ($order === 'asc' ? 'ASC' : 'DESC'));
        }

        $sql = "
            SELECT l.*, r.rule_name, r.penalty_point, r.fine_amount
            FROM {$wpdb->prefix}aerp_hrm_disciplinary_logs l
            LEFT JOIN {$wpdb->prefix}aerp_hrm_disciplinary_rules r ON l.rule_id = r.id
            WHERE $where
            ORDER BY $order_by
        ";

        $results = $wpdb->get_results($sql, ARRAY_A);
        $this->set_data($results);
        parent::prepare_items();
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'date_violation':
                return date('d/m/Y', strtotime($item[$column_name]));
            case 'penalty_point':
                return intval($item[$column_name]) . ' điểm';
            case 'fine_amount':
                return number_format((float)$item[$column_name], 0, ',', '.') . ' đ';
            default:
                return parent::column_default($item, $column_name);
        }
    }
    public function get_bulk_actions()
    {
        return ['delete' => 'Xoá'];
    }
    public function process_bulk_action()
    {
        if ($this->current_action() === 'delete' && !empty($_POST['id'])) {
            global $wpdb;
            $ids = array_map('absint', $_POST['id']);
            $placeholders = implode(',', array_fill(0, count($ids), '%d'));
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}aerp_hrm_disciplinary_logs WHERE id IN ($placeholders)",
                $ids
            ));
        }
    }
    
}
