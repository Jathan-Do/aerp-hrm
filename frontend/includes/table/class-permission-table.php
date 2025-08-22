<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Permission_Table extends AERP_Frontend_Table
{
    public function __construct()
    {
        parent::__construct([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_permissions',
            'columns' => [
                // 'id' => 'ID',
                'name' => 'Tên quyền',
                'description' => 'Mô tả'
            ],
            'sortable_columns' => ['id', 'name'],
            'searchable_columns' => ['name', 'description'],
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => ['delete'],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-permission'),
            'delete_item_callback' => ['AERP_Frontend_Permission_Manager', 'delete_permission_by_id'],
            'nonce_action_prefix' => 'delete_permission_',
            'message_transient_key' => 'aerp_permission_message',
            'hidden_columns_option_key' => 'aerp_hrm_permission_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_permission',
            'table_wrapper' => '#aerp-permission-table-wrapper',
        ]);
    }
}
