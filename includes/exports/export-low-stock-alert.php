<?php
if (!defined('ABSPATH')) exit;

function low_stock_alert_export()
{
    global $wpdb;
    $threshold = absint($_POST['threshold'] ?? 10);
    $warehouse_id = absint($_POST['warehouse_id'] ?? 0);
    $user_id = get_current_user_id();
    $user_warehouse_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT warehouse_id FROM {$wpdb->prefix}aerp_warehouse_managers WHERE user_id = %d",
        $user_id
    ));
    if (empty($user_warehouse_ids)) {
        $user_warehouse_ids = [0]; // Không có kho nào
    }
    $where = 'WHERE ps.quantity <= %d AND ps.warehouse_id IN (' . implode(',', array_map('intval', $user_warehouse_ids)) . ')';
    $args = [$threshold];
    if ($warehouse_id) {
        $where .= ' AND ps.warehouse_id = %d';
        $args[] = $warehouse_id;
    }
    $sql = "
        SELECT 
            p.name, p.sku, 
            u.name as unit, 
            c.name as category, 
            ps.quantity, w.name as warehouse_name, ps.updated_at
        FROM {$wpdb->prefix}aerp_product_stocks ps
        LEFT JOIN {$wpdb->prefix}aerp_products p ON ps.product_id = p.id
        LEFT JOIN {$wpdb->prefix}aerp_warehouses w ON ps.warehouse_id = w.id
        LEFT JOIN {$wpdb->prefix}aerp_units u ON p.unit_id = u.id
        LEFT JOIN {$wpdb->prefix}aerp_product_categories c ON p.category_id = c.id
        $where
        ORDER BY ps.updated_at DESC
    ";
    $data = $wpdb->get_results($wpdb->prepare($sql, ...$args), ARRAY_A);
    $headers = [
        'name' => 'Tên sản phẩm',
        'sku' => 'Mã sản phẩm',
        'unit' => 'Đơn vị',
        'category' => 'Danh mục',
        'warehouse_name' => 'Kho',
        'quantity' => 'Số lượng',
        'updated_at' => 'Cập nhật gần nhất',
    ];
    if (!class_exists('AERP_Excel_Export_Helper')) {
        require_once AERP_HRM_PATH . 'includes/class-excel-export-helper.php';
    }
    AERP_Excel_Export_Helper::export($headers, $data, 'canh-bao-ton-kho-thap', 'Cảnh báo tồn kho thấp');
}
