<?php
if (!defined('ABSPATH')) exit;
class AERP_Frontend_Reward_Table extends AERP_Frontend_Table
{
    public function __construct()
    {
        parent::__construct([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_reward_definitions',
            'columns' => [
                'id' => 'ID',
                'name' => 'Tên thưởng',
                'amount' => 'Số tiền',
                'trigger_type' => 'Loại kích hoạt',
                'day_trigger' => 'Ngày áp dụng',
                'created_at' => 'Ngày tạo',
            ],
            'sortable_columns' => ['id', 'name', 'amount', 'trigger_type', 'day_trigger'],
            'searchable_columns' => ['name', 'trigger_type'],
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => ['edit', 'delete'],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-reward-settings'),
            'delete_item_callback' => ['AERP_Frontend_Reward_Manager', 'delete_by_id'],
            'nonce_action_prefix' => 'delete_reward_',
            'message_transient_key' => 'aerp_reward_message',
            'hidden_columns_option_key' => 'aerp_hrm_reward_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_reward',
            'table_wrapper' => '#aerp-reward-table-wrapper',
        ]);
    }
} 