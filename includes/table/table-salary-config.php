<?php
if (!defined('ABSPATH')) exit;

class AERP_Salary_Config_Table extends AERP_Base_Table
{
    protected $employee_id;

    public function __construct($employee_id)
    {
        $this->employee_id = absint($employee_id);
        parent::__construct([
            'singular' => 'salary_config',
            'plural'   => 'salary_configs',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'          => '<input type="checkbox" />',
            'start_date'  => 'Từ ngày',
            'end_date'    => 'Đến ngày',
            'base_salary' => 'Lương cơ bản',
            'allowance'   => 'Phụ cấp',
            'actions'     => 'Thao tác',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'start_date'  => ['start_date', true],
            'end_date'    => ['end_date', false],
            'base_salary' => ['base_salary', false],
            'allowance'   => ['allowance', false],
        ];
    }

    public function get_searchable_columns()
    {
        return ['base_salary', 'allowance'];
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%d" />', esc_attr($item['id']));
    }

    public function column_default($item, $column_name)
    {
        $currency_fields = ['base_salary', 'allowance'];

        if (in_array($column_name, $currency_fields)) {
            return number_format((float)$item[$column_name], 0, ',', '.') . ' đ';
        }

        return esc_html($item[$column_name] ?? '');
    }

    public function column_actions($item)
    {
        $edit_url = add_query_arg([
            'page' => 'aerp_salary_add',
            'employee_id' => $this->employee_id,
            'edit' => $item['id']
        ], admin_url('admin.php'));
        $delete_url = wp_nonce_url(add_query_arg([
            'page' => 'aerp_salary_add',
            'employee_id' => $this->employee_id,
            'delete' => $item['id']
        ], admin_url('admin.php')), 'aerp_salary_delete_' . $item['id']);
        return '<a href="' . esc_url($edit_url) . '">Sửa</a> | <a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Bạn có chắc chắn muốn xóa cấu hình lương này?\')">Xóa</a>';
    }

    public function prepare_items()
    {
        global $wpdb;

        $orderby = $_REQUEST['orderby'] ?? 'start_date';
        $order   = $_REQUEST['order'] ?? 'desc';
        $search  = $_REQUEST['s'] ?? '';

        $where_clauses = ["employee_id = {$this->employee_id}"];
        $args = [];

        if (!empty($search)) {
            $where_clauses[] = "base_salary LIKE %s";
            $args[] = '%' . $wpdb->esc_like($search) . '%';
        }

        $where = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

        $sql = "
            SELECT * FROM {$wpdb->prefix}aerp_hrm_salary_config
            $where
            ORDER BY $orderby $order
        ";

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
                "DELETE FROM {$wpdb->prefix}aerp_hrm_salary_config WHERE id IN ($placeholders)",
                $ids
            ));
        }
    }
} 