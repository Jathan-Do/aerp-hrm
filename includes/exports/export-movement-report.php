<?php
if (!defined('ABSPATH')) exit;

function movement_report_export()
{
    global $wpdb;
    $warehouse_id = absint($_POST['warehouse_id'] ?? 0);
    $product_id = absint($_POST['product_id'] ?? 0);
    $type = sanitize_text_field($_POST['type'] ?? '');
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
    $where = ["il.status = 'confirmed'"];
    $args = [];
    $where[] = 'il.warehouse_id IN (' . implode(',', array_map('intval', $user_warehouse_ids)) . ')';
    if ($warehouse_id) {
        $where[] = 'il.warehouse_id = %d';
        $args[] = $warehouse_id;
    }
    if ($product_id) {
        $where[] = 'il.product_id = %d';
        $args[] = $product_id;
    }
    if ($type) {
        $where[] = 'il.type = %s';
        $args[] = $type;
    }
    if ($start_date) {
        $where[] = 'DATE(il.created_at) >= %s';
        $args[] = $start_date;
    }
    if ($end_date) {
        $where[] = 'DATE(il.created_at) <= %s';
        $args[] = $end_date;
    }
    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $sql = "
        SELECT 
            il.type,
            il.quantity,
            il.note,
            il.created_at,
            p.name as product_name,
            p.sku,
            w.name as warehouse_name,
            u.display_name as created_by,
            s.name as supplier_name
        FROM {$wpdb->prefix}aerp_inventory_logs il
        LEFT JOIN {$wpdb->prefix}aerp_products p ON il.product_id = p.id
        LEFT JOIN {$wpdb->prefix}aerp_warehouses w ON il.warehouse_id = w.id
        LEFT JOIN {$wpdb->prefix}users u ON il.created_by = u.ID
        LEFT JOIN {$wpdb->prefix}aerp_suppliers s ON il.supplier_id = s.id
        $where_sql
        ORDER BY il.created_at DESC
    ";
    $data = $wpdb->get_results($wpdb->prepare($sql, ...$args), ARRAY_A);
    $headers = [
        'product_name' => 'Sản phẩm',
        'sku' => 'Mã sản phẩm',
        'type' => 'Loại phiếu',
        'quantity' => 'Số lượng',
        'note' => 'Ghi chú',
        'warehouse_name' => 'Kho',
        'supplier_name' => 'Nhà cung cấp',
        'created_by' => 'Người tạo',
        'created_at' => 'Ngày tạo',
    ];
    if (!class_exists('AERP_Excel_Export_Helper')) {
        require_once AERP_HRM_PATH . 'includes/class-excel-export-helper.php';
    }
    AERP_Excel_Export_Helper::export($headers, $data, 'bao-cao-chuyen-dong-kho', 'Báo cáo chuyển động kho');
} 