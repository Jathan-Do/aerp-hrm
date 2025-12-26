<?php
if (!defined('ABSPATH')) exit;

/**
 * Export danh sách khách hàng.
 */
function customer_list_export()
{
    global $wpdb;

    $customers_table  = $wpdb->prefix . 'aerp_crm_customers';
    $phones_table     = $wpdb->prefix . 'aerp_crm_customer_phones';
    $employees_table  = $wpdb->prefix . 'aerp_hrm_employees';
    $sources_table    = $wpdb->prefix . 'aerp_crm_customer_sources';

    $results = $wpdb->get_results("
        SELECT 
            c.customer_code,
            c.full_name,
            c.email,
            c.address,
            c.status,
            c.created_at,
            cs.name AS source_name,
            e.full_name AS assigned_employee,
            cp.phones
        FROM {$customers_table} c
        LEFT JOIN {$sources_table} cs ON c.customer_source_id = cs.id
        LEFT JOIN {$employees_table} e ON c.assigned_to = e.id
        LEFT JOIN (
            SELECT customer_id, GROUP_CONCAT(phone_number SEPARATOR ', ') AS phones
            FROM {$phones_table}
            GROUP BY customer_id
        ) cp ON cp.customer_id = c.id
        ORDER BY c.created_at DESC
    ", ARRAY_A);

    $headers = [
        'customer_code'     => 'Mã KH',
        'full_name'         => 'Họ tên',
        'phones'            => 'SĐT',
        'email'             => 'Email',
        'address'           => 'Địa chỉ',
        'status'            => 'Trạng thái',
        'assigned_employee' => 'Nhân viên phụ trách',
        'source_name'       => 'Nguồn KH',
        'created_at'        => 'Ngày tạo',
    ];

    if (!class_exists('AERP_Excel_Export_Helper')) {
        require_once AERP_HRM_PATH . 'includes/class-excel-export-helper.php';
    }

    AERP_Excel_Export_Helper::export($headers, $results, 'danh-sach-khach-hang', 'Danh sách khách hàng');
}


