<?php
if (!defined('ABSPATH')) exit;

class AERP_Employee_Reward_Table extends AERP_Base_Table
{
    protected $employee_id;

    public function __construct($employee_id)
    {
        $this->employee_id = $employee_id;
        parent::__construct([
            'singular' => 'employee_reward',
            'plural'   => 'employee_rewards',
            'ajax'     => false
        ]);
    }

    public function column_cb($item)
    {
        return '<input type="checkbox" name="employee_reward[]" value="' . esc_attr($item['id']) . '" />';
    }
    public function get_columns()
    {
        return [
            'cb'        => '<input type="checkbox" />',
            'month'     => 'Tháng',
            'reward'    => 'Tên thưởng',
            'amount'    => 'Số tiền',
            'note'      => 'Ghi chú'
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'month'  => ['month', true],
            'reward' => ['reward', false],
            'amount' => ['amount', false]
        ];
    }

    public function get_bulk_actions()
    {
        return [
            'delete' => 'Xóa'
        ];
    }
    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            $ids = isset($_POST['employee_reward']) ? array_map('absint', $_POST['employee_reward']) : [];
            if (!empty($ids)) {
                global $wpdb;
                foreach ($ids as $id) {
                    $wpdb->delete("{$wpdb->prefix}aerp_hrm_employee_rewards", ['id' => $id]);
                }
                add_settings_error('aerp_hrm', 'employee_reward_deleted', 'Đã xóa thưởng thành công.', 'updated');
            }
        }
    }
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'month':
                return date('m/Y', strtotime($item['month']));
            case 'amount':
                return number_format($item['amount'], 0, ',', '.') . ' đ';
            default:
                return esc_html($item[$column_name] ?? '');
        }
    }

    public function prepare_items()
    {
        global $wpdb;
        $query = $wpdb->prepare("
            SELECT r.name as reward, r.amount, er.*
            FROM {$wpdb->prefix}aerp_hrm_employee_rewards er
            LEFT JOIN {$wpdb->prefix}aerp_hrm_reward_definitions r ON r.id = er.reward_id
            WHERE er.employee_id = %d
            ORDER BY er.month DESC
        ", $this->employee_id);

        $data = $wpdb->get_results($query, ARRAY_A);
        $this->set_data($data);
        parent::prepare_items();
    }

    public function get_searchable_columns()
    {
        return ['reward', 'note'];
    }
    public function column_reward($item)
    {
        $edit_url = admin_url('admin.php?page=aerp_employee_reward_edit&edit=' . $item['id']);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=aerp_employees&view=' . $item['employee_id'] . '&delete_employee_reward=' . $item['id']),
            'aerp_delete_employee_reward_' . $item['id']
        );

        $actions = [
            'edit'   => '<a href="' . esc_url($edit_url) . '">Sửa</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Bạn có chắc muốn xoá mục thưởng này?\')">Xoá</a>',
        ];

        return '<strong>' . esc_html($item['reward']) . '</strong> ' . $this->row_actions($actions);
    }
}
