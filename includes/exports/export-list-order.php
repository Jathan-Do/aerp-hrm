<?php
if (!defined('ABSPATH')) exit;

/**
 * Export danh sách đơn hàng.
 */
function order_list_export()
{
    global $wpdb;

    $orders_table    = $wpdb->prefix . 'aerp_order_orders';
    $customers_table = $wpdb->prefix . 'aerp_crm_customers';
    $phones_table    = $wpdb->prefix . 'aerp_crm_customer_phones';
    $employees_table = $wpdb->prefix . 'aerp_hrm_employees';
    $items_table     = $wpdb->prefix . 'aerp_order_items';
    $contents_table  = $wpdb->prefix . 'aerp_order_content_lines';

    // Lấy dữ liệu đơn hàng kèm lợi nhuận tính sẵn
    $results = $wpdb->get_results("
        SELECT 
            o.order_code,
            c.full_name AS customer_name,
            c.address AS customer_address,
            cp.phones   AS customer_phones,
            o.order_date,
            o.created_at,
            o.total_amount,
            o.cost,
            o.status,
            o.order_type,
            e.full_name AS employee_name,
            (
                CASE 
                    WHEN o.order_type = 'all' THEN
                        (COALESCE((SELECT SUM(total_price) FROM {$contents_table} c2 WHERE c2.order_id = o.id), 0)
                         - COALESCE(o.cost, 0)
                         - COALESCE((SELECT SUM(quantity * unit_price) FROM {$items_table} i WHERE i.order_id = o.id), 0)
                         - COALESCE((SELECT SUM(external_cost) FROM {$items_table} i2 WHERE i2.order_id = o.id AND i2.purchase_type = 'external'), 0))
                    WHEN o.order_type <> 'all' AND o.status = 'paid' THEN
                        (COALESCE(o.cost, 0)
                         + COALESCE((SELECT SUM(quantity * unit_price) FROM {$items_table} i3 WHERE i3.order_id = o.id), 0)
                         - COALESCE((SELECT SUM(external_cost) FROM {$items_table} i4 WHERE i4.order_id = o.id AND i4.purchase_type = 'external'), 0))
                    ELSE
                        (COALESCE((SELECT SUM(total_price) FROM {$contents_table} c3 WHERE c3.order_id = o.id), 0)
                         - COALESCE(o.cost, 0)
                         - COALESCE((SELECT SUM(quantity * unit_price) FROM {$items_table} i5 WHERE i5.order_id = o.id), 0)
                         - COALESCE((SELECT SUM(external_cost) FROM {$items_table} i6 WHERE i6.order_id = o.id AND i6.purchase_type = 'external'), 0))
                END
            ) AS profit_value
        FROM {$orders_table} o
        LEFT JOIN {$customers_table} c ON o.customer_id = c.id
        LEFT JOIN (
            SELECT customer_id, GROUP_CONCAT(phone_number SEPARATOR ', ') AS phones
            FROM {$phones_table}
            GROUP BY customer_id
        ) cp ON cp.customer_id = c.id
        LEFT JOIN {$employees_table} e ON o.employee_id = e.id
        ORDER BY o.created_at DESC
    ", ARRAY_A);

    $headers = [
        'order_code'    => 'Mã đơn',
        'customer_name' => 'Khách hàng',
        'customer_address' => 'Địa chỉ',
        'customer_phones'  => 'SĐT',
        'employee_name' => 'Nhân viên phụ trách',
        'order_date'    => 'Ngày lập hóa đơn',
        'created_at'    => 'Ngày tạo đơn',
        'status'        => 'Tình trạng',
        'order_type'    => 'Loại đơn',
        'total_amount'  => 'Doanh thu',
        'cost'          => 'Chi phí',
        'profit_value'  => 'Lợi nhuận'
    ];

    if (!class_exists('AERP_Excel_Export_Helper')) {
        require_once AERP_HRM_PATH . 'includes/class-excel-export-helper.php';
    }

    AERP_Excel_Export_Helper::export($headers, $results, 'danh-sach-don-hang', 'Danh sách đơn hàng');
}