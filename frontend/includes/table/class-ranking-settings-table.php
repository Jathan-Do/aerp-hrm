<?php
if (!defined('ABSPATH')) exit;
class AERP_Frontend_Ranking_Settings_Table extends AERP_Frontend_Table
{
    public function __construct()
    {
        parent::__construct([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_ranking_settings',
            'columns' => [
                // 'id' => 'ID',
                'rank_code' => 'Xếp loại',
                'min_point' => 'Từ điểm',
                'note' => 'Ghi chú',
                'sort_order' => 'Thứ tự',
                'created_at' => 'Ngày tạo',
            ],
            'sortable_columns' => ['id', 'rank_code', 'min_point', 'sort_order'],
            'searchable_columns' => ['rank_code', 'note'],
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => ['edit', 'delete'],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-ranking-settings'),
            'delete_item_callback' => ['AERP_Frontend_Ranking_Settings_Manager', 'delete_by_id'],
            'nonce_action_prefix' => 'delete_ranking_',
            'message_transient_key' => 'aerp_ranking_settings_message',
            'hidden_columns_option_key' => 'aerp_hrm_ranking_settings_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_ranking_settings',
            'table_wrapper' => '#aerp-ranking-settings-table-wrapper',
        ]);
    }
} 