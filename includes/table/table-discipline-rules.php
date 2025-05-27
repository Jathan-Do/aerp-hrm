<?php
if (!defined('ABSPATH')) exit;

class AERP_Discipline_Rules_Table extends AERP_Base_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'discipline_rule',
            'plural'   => 'discipline_rules',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'            => '<input type="checkbox" />',
            'rule_name'      => 'Lý do vi phạm',
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
            'rule_name'     => ['rule_name', true],
            'penalty_point' => ['penalty_point', false],
            'fine_amount'   => ['fine_amount', false],
        ];
    }
    public function get_searchable_columns()
    {
        return ['rule_name', 'penalty_point', 'fine_amount'];
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
                "DELETE FROM {$wpdb->prefix}aerp_hrm_disciplinary_rules WHERE id IN ($placeholders)",
                $ids
            ));
        }
    }
    public function prepare_items()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_disciplinary_rules';

        // Xử lý xóa
        if (
            isset($_GET['delete_rule'], $_GET['_wpnonce']) &&
            wp_verify_nonce($_GET['_wpnonce'], 'aerp_delete_rule_' . $_GET['delete_rule'])
        ) {
            $wpdb->delete($table, ['id' => absint($_GET['delete_rule'])]);
        }

        // Lấy dữ liệu
        $data = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC", ARRAY_A);

        // Format tiền
        foreach ($data as &$item) {
            $item['fine_amount'] = number_format((float)$item['fine_amount'], 0, ',', '.');
        }

        $this->set_data($data);
        parent::prepare_items();
    }

    public function column_rule_name($item)
    {
        $edit_url = admin_url('admin.php?page=aerp_discipline&edit=' . $item['id']);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=aerp_discipline&delete_rule=' . $item['id']),
            'aerp_delete_rule_' . $item['id']
        );

        $actions = [
            'edit'   => '<a href="' . esc_url($edit_url) . '">Sửa</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Xoá mục này?\')">Xoá</a>',
        ];

        return '<strong>' . esc_html($item['rule_name']) . '</strong> ' . $this->row_actions($actions);
    }
    public function column_penalty_point($item)
    {
        return '<strong style="color:red"> -' . esc_html($item['penalty_point']) . '</strong>';
    }
}
