<?php
if (!defined('ABSPATH')) exit;

class AERP_KPI_Settings_Table extends AERP_Base_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'kpi',
            'plural'   => 'kpis',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'           => '<input type="checkbox" />',
            'min_score'    => 'Từ điểm',
            'reward_amount' => 'Số tiền thưởng',
            'note'         => 'Ghi chú',
            'sort_order'   => 'Thứ tự',
        ];
    }

    public function get_primary_column_name()
    {
        return 'min_score';
    }
    public function get_sortable_columns()
    {
        return ['min_score' => ['min_score', true], 'reward_amount' => ['reward_amount', false]];
    }

    public function get_searchable_columns()
    {
        return ['note', 'min_score', 'reward_amount'];
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%d" />', $item['id']);
    }

    public function column_min_score($item)
    {
        $edit_url = admin_url('admin.php?page=aerp_kpi_settings&edit=' . $item['id']);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=aerp_kpi_settings&delete_kpi=' . $item['id']),
            'aerp_delete_kpi_' . $item['id']
        );

        $actions = [
            'edit' => '<a href="' . esc_url($edit_url) . '">Sửa</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Xóa mốc thưởng này?\')">Xóa</a>',
        ];

        return '<strong>' . esc_html($item['min_score']) . ' điểm</strong> ' . $this->row_actions($actions);
    }

    public function column_reward_amount($item)
    {
        return number_format($item['reward_amount'], 0, ',', '.') . ' đ';
    }

    public function prepare_items()
    {
        global $wpdb;
        $data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_kpi_settings ORDER BY sort_order ASC, min_score DESC", ARRAY_A);
        $this->set_data($data);
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
                "DELETE FROM {$wpdb->prefix}aerp_hrm_kpi_settings WHERE id IN ($placeholders)",
                $ids
            ));
        }
    }
}
