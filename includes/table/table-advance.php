<?php
if (!defined('ABSPATH')) exit;

class AERP_Advance_Table extends AERP_Base_Table
{
    protected $employee_id;

    public function __construct($employee_id = 0)
    {
        $this->employee_id = absint($employee_id);
        parent::__construct([
            'singular' => 'advance',
            'plural'   => 'advances',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'           => '<input type="checkbox" />',
            'advance_date' => 'Ngày ứng',
            'amount'       => 'Số tiền',
            'actions'      => 'Thao tác',
        ];
    }

    public function column_cb($item)
    {
        return '<input type="checkbox" name="id[]" value="' . esc_attr($item['id']) . '" />';
    }

    public function column_amount($item)
    {
        return number_format($item['amount'], 0, ',', '.') . ' đ';
    }

    public function column_advance_date($item)
    {
        return date('d/m/Y', strtotime($item['advance_date']));
    }

    public function column_actions($item)
    {
        $edit_url = admin_url('admin.php?page=aerp_advance_add&employee_id=' . $this->employee_id . '&edit=' . $item['id']);
        $delete_url = admin_url('admin.php?page=aerp_advance_add&employee_id=' . $this->employee_id . '&delete=' . $item['id']);
        return '<a href="' . esc_url($edit_url) . '">Sửa</a> | <a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Xác nhận xóa?\')">Xóa</a>';
    }

    public function get_sortable_columns()
    {
        return [
            'advance_date' => ['advance_date', true],
            'amount'       => ['amount', false],
        ];
    }

    public function get_searchable_columns()
    {
        return ['amount'];
    }

    public function prepare_items()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_advance_salaries';

        $orderby = $_REQUEST['orderby'] ?? 'advance_date';
        $order   = $_REQUEST['order'] ?? 'desc';
        $search  = $_REQUEST['s'] ?? '';

        $where = '1=1';
        $args = [];

        if ($this->employee_id) {
            $where .= ' AND employee_id = %d';
            $args[] = $this->employee_id;
        }

        if (!empty($search)) {
            $where .= ' AND amount LIKE %s';
            $args[] = '%' . $wpdb->esc_like($search) . '%';
        }

        $order_by = sanitize_sql_orderby("$orderby " . ($order === 'asc' ? 'ASC' : 'DESC'));

        $sql = "SELECT * FROM $table WHERE $where ORDER BY $order_by";
        $results = $wpdb->get_results($wpdb->prepare($sql, ...$args), ARRAY_A);

        $this->set_data($results);
        parent::prepare_items();
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
                "DELETE FROM {$wpdb->prefix}aerp_hrm_advance_salaries WHERE id IN ($placeholders)",
                $ids
            ));
        }
    }
}