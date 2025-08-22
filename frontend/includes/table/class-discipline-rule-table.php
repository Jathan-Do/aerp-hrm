<?php
if (!defined('ABSPATH')) {
    exit;
}
class AERP_Frontend_Discipline_Rule_Table extends AERP_Frontend_Table
{
    public function __construct()
    {
        parent::__construct([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_disciplinary_rules',
            'columns' => [
                // 'id' => 'ID',
                'rule_name' => 'Lý do vi phạm',
                'penalty_point' => 'Điểm trừ',
                'fine_amount' => 'Tiền phạt',
                'created_at' => 'Ngày tạo',
            ],
            'sortable_columns' => ['id', 'rule_name', 'penalty_point', 'fine_amount'],
            'searchable_columns' => ['rule_name', 'penalty_point', 'fine_amount'],
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => ['edit', 'delete'],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-discipline-rule'),
            'delete_item_callback' => ['AERP_Frontend_Discipline_Rule_Manager', 'delete_discipline_rule_by_id'],
            'nonce_action_prefix' => 'delete_discipline_rule_',
            'message_transient_key' => 'aerp_discipline_rule_message',
            'hidden_columns_option_key' => 'aerp_hrm_discipline_rule_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_discipline_rule',
            'table_wrapper' => '#aerp-discipline-rule-table-wrapper',
        ]);
    }
    public function column_penalty_point($item)
    {
        return '<strong style="color:red"> -' . esc_html($item->penalty_point) . '</strong>';
    }
    protected function column_fine_amount($item)
    {
        return sprintf('%s %s', number_format($item->fine_amount, 0), 'đ');
    }
}
