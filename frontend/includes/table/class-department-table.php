<?php
if (!defined('ABSPATH')) {
    exit;
}
class AERP_Frontend_Department_Table extends AERP_Frontend_Table
{
    public function __construct()
    {
        parent::__construct([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_departments',
            'columns' => [
                'id' => 'ID',
                'name' => 'Tên phòng ban',
                'description' => 'Mô tả',
                'created_at' => 'Ngày tạo'
            ],
            'sortable_columns' => ['id', 'name', 'created_at'],
            'searchable_columns' => ['name', 'description'],
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => ['edit', 'delete'],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-departments'),
            'delete_item_callback' => ['AERP_Frontend_Department_Manager', 'delete_department_by_id'],
            'nonce_action_prefix' => 'delete_department_',
            'message_transient_key' => 'aerp_department_message',
            'hidden_columns_option_key' => 'aerp_hrm_department_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_department',
            'table_wrapper' => '#aerp-department-table-wrapper',
        ]);
    }
}
