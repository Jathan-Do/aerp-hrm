<?php
if (!defined('ABSPATH')) {
    exit;
}
class AERP_Frontend_Work_Location_Table extends AERP_Frontend_Table
{
    public function __construct()
    {
        parent::__construct([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_work_locations',
            'columns' => [
                // 'id' => 'ID',
                'name' => 'Tên chi nhánh',
                'description' => 'Mô tả',
                'created_at' => 'Ngày tạo'
            ],
            'sortable_columns' => ['id', 'name', 'created_at'],
            'searchable_columns' => ['name', 'description'],
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => ['edit', 'delete'],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-work-location'),
            'delete_item_callback' => ['AERP_Frontend_Work_Location_Manager', 'delete_work_location_by_id'],
            'nonce_action_prefix' => 'delete_work_location_',
            'message_transient_key' => 'aerp_work_location_message',
            'hidden_columns_option_key' => 'aerp_hrm_work_location_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_work_location',
            'table_wrapper' => '#aerp-work_location-table-wrapper',
        ]);
    }
}
