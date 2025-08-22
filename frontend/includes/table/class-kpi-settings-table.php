<?php
if (!defined('ABSPATH')) exit;
class AERP_Frontend_KPI_Settings_Table extends AERP_Frontend_Table
{
    public function __construct()
    {
        parent::__construct([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_kpi_settings',
            'columns' => [
                // 'id' => 'ID',
                'min_score' => 'Từ điểm',
                'reward_amount' => 'Tiền thưởng',
                'note' => 'Ghi chú',
                'sort_order' => 'Thứ tự',
                'created_at' => 'Ngày tạo',
            ],
            'sortable_columns' => ['id', 'min_score', 'reward_amount', 'sort_order'],
            'searchable_columns' => ['note'],
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => ['edit', 'delete'],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-kpi-settings'),
            'delete_item_callback' => ['AERP_Frontend_KPI_Settings_Manager', 'delete_by_id'],
            'nonce_action_prefix' => 'delete_kpi_',
            'message_transient_key' => 'aerp_kpi_message',
            'hidden_columns_option_key' => 'aerp_hrm_kpi_settings_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_kpi_settings',
            'table_wrapper' => '#aerp-kpi-settings-table-wrapper',
        ]);
    }
    protected function column_reward_amount($item)
    {
        return sprintf('%s %s', number_format($item->reward_amount, 0), 'đ');
    }
} 