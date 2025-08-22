<?php
if (!defined('ABSPATH')) {
    exit;
}
class AERP_Frontend_Company_Table extends AERP_Frontend_Table {
    public function __construct() {
        parent::__construct([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_company_info',
            'columns' => [
                // 'id' => 'ID',
                'company_name' => 'Tên công ty',
                'tax_code' => 'Mã số thuế',
                'phone' => 'Số điện thoại',
                'email' => 'Email',
                'address' => 'Địa chỉ',
                'website' => 'Website',
                'work_saturday' => 'Làm việc thứ 7',
                'created_at' => 'Ngày tạo'
            ],
            'sortable_columns' => ['id', 'company_name', 'created_at'],
            'searchable_columns' => ['company_name', 'address'],
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => ['edit', 'delete'],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-company'),
            'delete_item_callback' => ['AERP_Frontend_Company_Manager', 'delete_company_by_id'],
            'nonce_action_prefix' => 'delete_company_',
            'message_transient_key' => 'aerp_company_message',
            'hidden_columns_option_key' => 'aerp_hrm_company_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_company',
            'table_wrapper' => '#aerp-company-table-wrapper',
        ]);
    }

    /**
     * Hiển thị cột work_saturday thân thiện hơn
     */
    protected function column_work_saturday($item)
    {
        $work_saturday_map = [
            'off' => 'Nghỉ thứ 7',
            'full' => 'Làm cả ngày thứ 7',
            'half' => 'Làm nửa ngày thứ 7',
        ];
        return $work_saturday_map[$item->work_saturday] ?? esc_html($item->work_saturday);
    }
}
