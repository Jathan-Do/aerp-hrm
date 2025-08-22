<?php
// File: includes/table/table-role.php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Role_Table extends AERP_Frontend_Table
{
    public function __construct()
    {
        parent::__construct([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_roles',
            'columns' => [
                // 'id' => 'ID',
                'name' => 'Tên nhóm quyền',
                'description' => 'Mô tả'
            ],
            'sortable_columns' => ['id', 'name'],
            'searchable_columns' => ['name', 'description'],
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => ['delete'],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-role'),
            'delete_item_callback' => ['AERP_Frontend_Role_Manager', 'delete_role_by_id'],
            'nonce_action_prefix' => 'delete_role_',
            'message_transient_key' => 'aerp_role_message',
            'hidden_columns_option_key' => 'aerp_hrm_role_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_role',
            'table_wrapper' => '#aerp-role-table-wrapper',
        ]);
    }
}
