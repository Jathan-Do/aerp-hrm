<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Commission_Table extends AERP_Frontend_Table
{
    protected $employee_id;

    public function __construct($args = [])
    {
        $this->employee_id = $args['employee_id'] ?? 0;

        $columns = [
            'name'  => 'Tên danh mục',
            'note'    => 'Ghi chú',
            'min_profit'  => 'Lợi nhuận tối thiểu',
            'max_profit'  => 'Lợi nhuận tối đa',
            'percent'  => 'Phần trăm',
            'created_at'  => 'Ngày tạo',
        ];

        $sortable = ['id', 'name', 'min_profit', 'max_profit', 'percent', 'created_at'];
        $searchable = ['name', 'min_profit', 'max_profit', 'percent'];

        parent::__construct(array_merge([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_commission_schemes',
            'columns' => $columns,
            'sortable_columns' => $sortable,
            'searchable_columns' => $searchable,
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => ['edit', 'delete'],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-hrm-commission-schemes'),
            'delete_item_callback' => ['AERP_Frontend_Commission_Manager', 'delete_scheme_by_id'],
            'nonce_action_prefix' => 'delete_commission_scheme_',
            'message_transient_key' => 'aerp_commission_scheme_message',
            'hidden_columns_option_key' => 'aerp_hrm_commission_scheme_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_commission_scheme',
            'table_wrapper' => '#aerp-commission-scheme-table-wrapper',

        ], $args));
    }

}
