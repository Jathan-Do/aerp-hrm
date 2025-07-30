<?php
if (!defined('ABSPATH')) exit;

function stock_timeline_export()
{
    global $wpdb;
    $warehouse_id = absint($_POST['warehouse_id'] ?? 0);
    $product_id = absint($_POST['product_id'] ?? 0);
    $start_date = sanitize_text_field($_POST['start_date'] ?? '');
    $end_date = sanitize_text_field($_POST['end_date'] ?? '');
    $user_id = get_current_user_id();
    $user_warehouse_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT warehouse_id FROM {$wpdb->prefix}aerp_warehouse_managers WHERE user_id = %d",
        $user_id
    ));
    if (empty($user_warehouse_ids)) {
        $user_warehouse_ids = [0];
    }
    $where = [];
    $args = [];
    $where[] = 'ps.warehouse_id IN (' . implode(',', array_map('intval', $user_warehouse_ids)) . ')';
    if ($warehouse_id) {
        $where[] = 'ps.warehouse_id = %d';
        $args[] = $warehouse_id;
    }
    if ($product_id) {
        $where[] = 'ps.product_id = %d';
        $args[] = $product_id;
    }
    if ($start_date) {
        $where[] = 'DATE(ps.updated_at) >= %s';
        $args[] = $start_date;
    }
    if ($end_date) {
        $where[] = 'DATE(ps.updated_at) <= %s';
        $args[] = $end_date;
    }
    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
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
        $where_sql
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
    AERP_Excel_Export_Helper::export($headers, $data, 'bao-cao-ton-kho-theo-thoi-gian', 'Báo cáo tồn kho theo thời gian');
}
