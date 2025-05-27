<?php
if (!defined('ABSPATH')) exit;

class AERP_Reward_Table extends AERP_Base_Table
{

    public function __construct()
    {
        parent::__construct([
            'singular' => 'reward',
            'plural'   => 'rewards',
            'ajax'     => false,
        ]);
    }
    public function get_columns()
    {
        return [
            'cb'           => '<input type="checkbox" />',
            'name'         => 'Tên thưởng',
            'trigger_type' => 'Loại',
            'day_trigger'  => 'Ngày áp dụng',
            'amount'       => 'Số tiền',
        ];
    }
    public function column_cb($item)
    {
        return '<input type="checkbox" name="id[]" value="' . esc_attr($item['id']) . '" />';
    }

    public function get_sortable_columns()
    {
        return [
            'name'         => ['name', true],
            'trigger_type' => ['trigger_type', false],
            'amount'       => ['amount', false],
        ];
    }

    public function get_searchable_columns()
    {
        return ['name', 'trigger_type'];
    }

    public function column_default($item, $column_name)
    {
        if ($column_name === 'day_trigger') {
            return $item[$column_name] ? date('d/m/Y', strtotime($item[$column_name])) : '--';
        }
        if ($column_name === 'amount') {
            return number_format($item[$column_name], 0, ',', '.') . ' đ';
        }
        return esc_html($item[$column_name] ?? '');
    }
    public function column_name($item)
    {
        $edit_url = admin_url('admin.php?page=aerp_reward_settings&edit=' . $item['id']);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=aerp_reward_settings&delete_reward=' . $item['id']),
            'aerp_delete_reward_' . $item['id']
        );

        $actions = [
            'edit' => '<a href="' . esc_url($edit_url) . '">Sửa</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Bạn có chắc muốn xoá mục thưởng này?\')">Xoá</a>',
        ];

        return '<strong>' . esc_html($item['name']) . '</strong>' . $this->row_actions($actions);
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
            $wpdb->query("DELETE FROM {$wpdb->prefix}aerp_hrm_reward_definitions WHERE id IN (" . implode(',', $ids) . ")");
            add_action('admin_notices', function () {
                echo '<div class="updated"><p>✅ Đã xoá các bản ghi thưởng đã chọn.</p></div>';
            });
        }
    }
    public function prepare_items()
    {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_reward_definitions ORDER BY created_at DESC", ARRAY_A);

        $this->set_data($results);
        parent::prepare_items();
    }
}
