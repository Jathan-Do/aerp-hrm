<?php
if (!defined('ABSPATH')) exit;

class AERP_Salary_Summary_Table extends AERP_Base_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'salary_summary',
            'plural'   => 'salary_summaries',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb' => '<input type="checkbox" />',
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
        ];
    }
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%d" />', $item['id']);
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
                "DELETE FROM {$wpdb->prefix}aerp_hrm_salaries WHERE id IN ($placeholders)",
                $ids
            ));
        }
    }
    public function get_searchable_columns()
    {
        return ['employee_code', 'full_name', 'bank_account', 'email'];
    }
    public function get_sortable_columns()
    {
        return [
            'full_name'  => ['full_name', true],
            'employee_code' => ['employee_code', false],
            'bank_account' => ['bank_account', false],
            'email' => ['email', false],
            'salary_month' => ['salary_month', false],
            'bonus' => ['bonus', false],
            'deduction' => ['deduction', false],
            'advance_paid' => ['advance_paid', false],
            'final_salary' => ['final_salary', false],
            'points_total' => ['points_total', false],
        ];
    }

    public function column_default($item, $column_name)
    {
        if (in_array($column_name, ['base_salary', 'bonus', 'deduction', 'adjustment', 'advance_paid', 'final_salary'])) {
            return number_format($item[$column_name], 0, ',', '.') . ' đ';
        }
        if ($column_name === 'salary_month') {
            return date('m/Y', strtotime($item[$column_name]));
        }
        return esc_html($item[$column_name] ?? '');
    }
    public function column_full_name($item)
    {
        $view_url = admin_url('admin.php?page=aerp_salary_summary&view=' . $item['id']);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=aerp_salary_summary&delete=' . $item['id']),
            'aerp_delete_salary_' . $item['id']
        );
        $actions = [
            'view'   => '<a href="' . esc_url($view_url) . '">Xem</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Bạn có chắc muốn xoá bảng lương này?\')">Xoá</a>',
        ];

        return sprintf('<strong>%s</strong> %s', esc_html($item['full_name']), $this->row_actions($actions));
    }

    public function prepare_items($month = '')
    {
        global $wpdb;
        $where = '';
        $args = [];

        if ($month) {
            $where = 'WHERE s.salary_month = %s';
            $args[] = $month . '-01';
        }

        $sql = "
            SELECT 
                s.id,  
                e.employee_code, e.full_name, e.email, e.bank_account, e.bank_name,
                s.salary_month, s.base_salary, s.bonus, s.deduction, s.adjustment, 
                s.final_salary, s.advance_paid, s.points_total
            FROM {$wpdb->prefix}aerp_hrm_salaries s
            LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON s.employee_id = e.id
            $where
            ORDER BY s.salary_month DESC
        ";
        $results = $wpdb->get_results($wpdb->prepare($sql, ...$args), ARRAY_A);
        $this->set_data($results);
        parent::prepare_items();
    }

}
