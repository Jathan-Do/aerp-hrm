<?php
if (!defined('ABSPATH')) exit;

function aerp_dashboard_export()
{
    $month = sanitize_text_field($_POST['report_month'] ?? date('Y-m'));
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    // Lấy thông tin employee và work_location_id
    $employee = function_exists('aerp_get_employee_by_user_id') ? aerp_get_employee_by_user_id($user_id) : null;
    $work_location_id = $employee ? $employee->work_location_id : 0;

    // Lấy warehouses user quản lý
    $warehouses = class_exists('AERP_Warehouse_Manager') ? AERP_Warehouse_Manager::aerp_get_warehouses_by_user($user_id) : [];
    $user_warehouse_ids = array_map(function ($w) {
        return $w->id;
    }, $warehouses);

    global $wpdb;
    $start = date('Y-m-01', strtotime($month));
    $end = date('Y-m-t', strtotime($month));

    // Bắt đầu mảng Excel
    $rows = [];
    $rows[] = ['📊 BÁO CÁO TỔNG HỢP DASHBOARD - THÁNG ' . date('m/Y', strtotime($month))];
    $rows[] = ['Người xuất báo cáo: ' . $current_user->display_name];
    $rows[] = ['Thời gian xuất: ' . (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('d/m/Y H:i:s')];
    $rows[] = [];

    // === BÁO CÁO NHÂN SỰ ===
    if (class_exists('AERP_Report_Manager')) {
        $summary = AERP_Report_Manager::get_summary($month, $work_location_id);
        $performance = AERP_Report_Manager::get_performance_data($month, $work_location_id);
        $tenure = AERP_Report_Manager::get_tenure_data($work_location_id);
        $department = AERP_Report_Manager::get_department_data($work_location_id);
        $salary = AERP_Report_Manager::get_salary_data($month, $work_location_id);

        $rows[] = ['👥 BÁO CÁO NHÂN SỰ'];
        $rows[] = ['Tổng nhân sự', intval($summary['total'])];
        $rows[] = ['Đang làm', intval($summary['joined'])];
        $rows[] = ['Nghỉ việc', intval($summary['resigned'])];
        $rows[] = [];

        // Hiệu suất theo phòng ban
        if (!empty($performance)) {
            $rows[] = ['Hiệu suất theo phòng ban'];
            $rows[] = ['Phòng ban ID', 'Số task', 'Điểm TB'];
            foreach ($performance as $p) {
                $rows[] = [
                    intval($p['department_id']),
                    intval($p['total_tasks']),
                    round(floatval($p['avg_score']), 1)
                ];
            }
            $rows[] = [];
        }

        // Thâm niên
        if (!empty($tenure)) {
            $rows[] = ['Phân bố thâm niên'];
            $rows[] = ['Số năm', 'Số lượng'];
            foreach ($tenure as $t) {
                $rows[] = [$t['years'] . ' năm', intval($t['count'])];
            }
            $rows[] = [];
        }

        // Phân bố phòng ban
        if (!empty($department)) {
            $rows[] = ['Phân bố phòng ban'];
            $rows[] = ['Phòng ban ID', 'Số lượng'];
            foreach ($department as $d) {
                $rows[] = [intval($d['department_id']), intval($d['employee_count'])];
            }
            $rows[] = [];
        }

        // Chi phí lương
        if (!empty($salary)) {
            $rows[] = ['Chi phí lương'];
            $rows[] = ['Phòng ban ID', 'Tổng lương', 'Lương TB'];
            foreach ($salary as $s) {
                $rows[] = [
                    intval($s['department_id']),
                    round(floatval($s['total_cost'])),
                    round(floatval($s['avg_salary']))
                ];
            }
            $rows[] = [];
        }
    }

    // === BÁO CÁO ĐƠN HÀNG ===
    if (function_exists('aerp_order_init') || is_plugin_active('aerp-order/aerp-order.php')) {
        if ($work_location_id) {
            $total_orders = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) FROM {$wpdb->prefix}aerp_order_orders o
                LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                WHERE e.work_location_id = %d
                  AND o.order_date BETWEEN %s AND %s
            ", $work_location_id, $start, $end));

            $total_revenue = $wpdb->get_var($wpdb->prepare("
                SELECT SUM(o.total_amount) FROM {$wpdb->prefix}aerp_order_orders o
                LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                WHERE e.work_location_id = %d
                  AND o.order_date BETWEEN %s AND %s
            ", $work_location_id, $start, $end));

            $orders_by_month = $wpdb->get_results($wpdb->prepare("
                SELECT DATE_FORMAT(o.order_date, '%Y-%m') as ym, COUNT(*) as total, SUM(o.total_amount) as revenue
                FROM {$wpdb->prefix}aerp_order_orders o
                LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                WHERE e.work_location_id = %d
                  AND o.order_date BETWEEN %s AND %s
                GROUP BY ym ORDER BY ym DESC
            ", $work_location_id, $start, $end), ARRAY_A);

            $rows[] = ['🛒 BÁO CÁO ĐƠN HÀNG'];
            $rows[] = ['Tổng đơn hàng', intval($total_orders)];
            $rows[] = ['Tổng doanh thu', number_format($total_revenue, 0, ',', '.') . ' VNĐ'];
            $rows[] = [];

            if (!empty($orders_by_month)) {
                $rows[] = ['Đơn hàng & Doanh thu theo tháng'];
                $rows[] = ['Tháng', 'Số đơn', 'Doanh thu (VNĐ)'];
                foreach ($orders_by_month as $order) {
                    $rows[] = [
                        $order['ym'],
                        intval($order['total']),
                        number_format($order['revenue'], 0, ',', '.')
                    ];
                }
                $rows[] = [];
            }
        }
    }

    // === BÁO CÁO KHO ===
    if (function_exists('aerp_order_init') || is_plugin_active('aerp-order/aerp-order.php')) {
        if (!empty($user_warehouse_ids)) {
            $warehouse_ids_sql = implode(',', array_map('intval', $user_warehouse_ids));
            $total_warehouses = count($user_warehouse_ids);
            $total_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_products WHERE id IN (SELECT product_id FROM {$wpdb->prefix}aerp_product_stocks WHERE warehouse_id IN ($warehouse_ids_sql))");

            // Lấy ngưỡng tồn kho thấp từ setting
            $low_stock_threshold = get_option('aerp_low_stock_threshold', 10);
            $low_stock = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_product_stocks WHERE quantity <= %d AND warehouse_id IN ($warehouse_ids_sql)", $low_stock_threshold));

            $stock_by_warehouse = $wpdb->get_results("SELECT w.name, SUM(ps.quantity) as total FROM {$wpdb->prefix}aerp_product_stocks ps JOIN {$wpdb->prefix}aerp_warehouses w ON ps.warehouse_id = w.id WHERE ps.warehouse_id IN ($warehouse_ids_sql) GROUP BY w.id, w.name", ARRAY_A);

            $rows[] = ['🏭 BÁO CÁO KHO'];
            $rows[] = ['Tổng kho', intval($total_warehouses)];
            $rows[] = ['Tổng sản phẩm', intval($total_products)];
            $rows[] = ['Sản phẩm tồn kho thấp (≤' . $low_stock_threshold . ')', intval($low_stock)];
            $rows[] = [];

            if (!empty($stock_by_warehouse)) {
                $rows[] = ['Phân bố tồn kho theo kho'];
                $rows[] = ['Tên kho', 'Tổng tồn kho'];
                foreach ($stock_by_warehouse as $warehouse) {
                    $rows[] = [
                        $warehouse['name'],
                        intval($warehouse['total'])
                    ];
                }
                $rows[] = [];
            }
        }
    }

    // === BÁO CÁO KHÁCH HÀNG ===
    if (function_exists('aerp_crm_init') || is_plugin_active('aerp-crm/aerp-crm.php')) {
        $total_customers = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_crm_customers");
        $new_customers = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_crm_customers WHERE DATE(created_at) >= CURDATE() - INTERVAL 30 DAY");
        $customers_by_month = $wpdb->get_results("SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total FROM {$wpdb->prefix}aerp_crm_customers GROUP BY ym ORDER BY ym DESC LIMIT 12", ARRAY_A);

        $rows[] = ['👥 BÁO CÁO KHÁCH HÀNG'];
        $rows[] = ['Tổng khách hàng', intval($total_customers)];
        $rows[] = ['Khách hàng mới 30 ngày', intval($new_customers)];
        $rows[] = [];

        if (!empty($customers_by_month)) {
            $rows[] = ['Khách hàng mới theo tháng'];
            $rows[] = ['Tháng', 'Số khách hàng mới'];
            foreach ($customers_by_month as $customer) {
                $rows[] = [
                    $customer['ym'],
                    intval($customer['total'])
                ];
            }
        }
    }

    // Gọi export
    if (!class_exists('AERP_Excel_Export_Helper')) {
        require_once AERP_HRM_PATH . 'includes/class-excel-export-helper.php';
    }

    AERP_Excel_Export_Helper::export_raw($rows, 'bao-cao-dashboard', 'Báo cáo Dashboard');
}
