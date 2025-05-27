<?php
class AERP_Company_Table extends AERP_Base_Table {

    public function get_columns() {
        return [
            'company_name' => 'Tên công ty',
            'tax_code'     => 'Mã số thuế',
            'phone'        => 'Số điện thoại',
            'email'        => 'Email',
            'website'      => 'Website',
            'address'      => 'Địa chỉ',
        ];
    }

    public function get_sortable_columns() {
        return [];
    }

    public function get_bulk_actions() {
        return [];
    }

    public function get_searchable_columns() {
        return ['company_name', 'tax_code', 'phone', 'email'];
    }

    public function column_default($item, $column_name) {
        return isset($item[$column_name]) ? esc_html($item[$column_name]) : '<span style="color:#aaa">—</span>';
    }

    public function get_primary_column_name() {
        return 'company_name';
    }

    // Không cần checkbox hay bulk cho công ty (chỉ có 1 dòng)
}  