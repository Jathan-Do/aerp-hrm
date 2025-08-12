<?php

/**
 * Frontend Dashboard Template
 */

if (!defined('ABSPATH')) {
    exit;
}
// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

if (!is_user_logged_in()) {
    wp_die(__('You must be logged in to access this page.'));
}

// Danh sách điều kiện, chỉ cần 1 cái đúng là qua
$access_conditions = [
    aerp_user_has_role($user_id, 'admin'),
    aerp_user_has_role($user_id, 'department_lead'),

];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}
$hrm_active = function_exists('aerp_hrm_init') || is_plugin_active('aerp-hrm/aerp-hrm.php');
$order_active = function_exists('aerp_order_init') || is_plugin_active('aerp-order/aerp-order.php');
$crm_active = function_exists('aerp_crm_init') || is_plugin_active('aerp-crm/aerp-crm.php');
$warehouse_active = $order_active; // kho nằm trong order

global $wpdb;
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$employee = function_exists('aerp_get_employee_by_user_id') ? aerp_get_employee_by_user_id($user_id) : null;
$work_location_id = $employee ? $employee->work_location_id : 0;
$warehouses = class_exists('AERP_Warehouse_Manager') ? AERP_Warehouse_Manager::aerp_get_warehouses_by_user($user_id) : [];
$user_warehouse_ids = array_map(function ($w) {
    return $w->id;
}, $warehouses);
ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Báo cáo Tổng</h2>
    <div class="user-info text-end">
        Welcome, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(site_url('/aerp-dang-nhap')); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
<div class="dashboard-wrapper">
    <div class="mb-3">
        <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center">
            <div class="d-flex align-items-center gap-2">
                <label class="fw-bold" for="month">Tháng:</label>
                <input class="form-control w-auto" type="month" id="month" name="month" value="<?= esc_attr($month) ?>" max="<?= date('Y-m') ?>">

                <!-- Form xem báo cáo -->
                <form method="get" style="display: inline;">
                    <input type="hidden" name="month" value="<?= esc_attr($month) ?>" id="month-hidden">
                    <button type="submit" class="btn btn-primary">Xem</button>
                </form>
            </div>
            <div>
                <!-- Nút xuất Excel -->
                <form method="post" action="<?= admin_url('admin-post.php') ?>" style="display: inline;">
                    <?php wp_nonce_field('aerp_export_excel', 'aerp_export_nonce'); ?>
                    <input type="hidden" name="action" value="aerp_export_excel_common">
                    <input type="hidden" name="callback" value="aerp_dashboard_export">
                    <input type="hidden" name="report_month" value="<?= esc_attr($month) ?>" id="report-month-hidden">
                    <button type="submit" name="aerp_export_excel" class="btn btn-success">📥 Xuất Excel</button>
                </form>
            </div>

        </div>
    </div>

    <?php if ($hrm_active): ?>
        <?php
        if (class_exists('AERP_Report_Manager')) {
            $summary = AERP_Report_Manager::get_summary($month, $work_location_id);
            $performance = AERP_Report_Manager::get_performance_data($month, $work_location_id);
            $tenure = AERP_Report_Manager::get_tenure_data($work_location_id);
            $department = AERP_Report_Manager::get_department_data($work_location_id);
            $salary = AERP_Report_Manager::get_salary_data($month, $work_location_id);
        } else {
            $summary = ['total' => 0, 'joined' => 0, 'resigned' => 0];
            $performance = $tenure = $department = $salary = [];
        }
        ?>
        <section class="dashboard-section mb-5">
            <h2><i class="fas fa-users"></i> Báo cáo nhân sự</h2>

            <?php if (!$work_location_id): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Bạn chưa được gán chi nhánh, hiển thị dữ liệu toàn công ty.
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="summary-card card">
                        <div class="summary-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="summary-content">
                            <div class="summary-label">Tổng nhân sự</div>
                            <div class="summary-value"><?= number_format($summary['total']) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card card">
                        <div class="summary-icon active">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="summary-content">
                            <div class="summary-label">Đang làm</div>
                            <div class="summary-value"><?= number_format($summary['joined']) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card card">
                        <div class="summary-icon resigned">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <div class="summary-content">
                            <div class="summary-label">Nghỉ việc</div>
                            <div class="summary-value"><?= number_format($summary['resigned']) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card card">
                        <div class="summary-icon turnover">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="summary-content">
                            <div class="summary-label">Tỷ lệ nghỉ việc</div>
                            <div class="summary-value">
                                <?= $summary['total'] > 0 ? round(($summary['resigned'] / $summary['total']) * 100, 1) : 0 ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="chart-container card">
                        <h5><i class="fas fa-chart-bar"></i> Hiệu suất theo phòng ban</h5>
                        <?php if (empty($performance)): ?>
                            <div class="no-data">Không có dữ liệu hiệu suất</div>
                        <?php else: ?>
                            <canvas id="performanceChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container card">
                        <h5><i class="fas fa-chart-pie"></i> Phân bố thâm niên</h5>
                        <?php if (empty($tenure)): ?>
                            <div class="no-data">Không có dữ liệu thâm niên</div>
                        <?php else: ?>
                            <canvas id="tenureChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="chart-container card">
                        <h5><i class="fas fa-chart-doughnut"></i> Phân bố phòng ban</h5>
                        <?php if (empty($department)): ?>
                            <div class="no-data">Không có dữ liệu phòng ban</div>
                        <?php else: ?>
                            <canvas id="departmentChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container card">
                        <h5><i class="fas fa-money-bill-wave"></i> Chi phí lương</h5>
                        <?php if (empty($salary)): ?>
                            <div class="no-data">Không có dữ liệu lương</div>
                        <?php else: ?>
                            <canvas id="salaryChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <script src="<?= AERP_HRM_URL ?>assets/js/admin-charts.js"></script>
        <script>
            var performanceData = <?= json_encode($performance) ?>;
            var tenureData = <?= json_encode($tenure) ?>;
            var departmentData = <?= json_encode($department) ?>;
            var salaryData = <?= json_encode($salary) ?>;
        </script>
    <?php endif; ?>

    <?php if ($order_active): ?>
        <?php
        // Báo cáo đơn hàng theo chi nhánh user hiện tại
        if (!$work_location_id) {
            echo '<div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                Bạn chưa được gán chi nhánh, không thể xem báo cáo đơn hàng.
            </div>';
        } else {
            $start = date('Y-m-01', strtotime($month));
            $end   = date('Y-m-t', strtotime($month));

            // Tổng đơn hàng
            $total_orders = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) FROM {$wpdb->prefix}aerp_order_orders o
                LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                WHERE e.work_location_id = %d
                  AND o.order_date BETWEEN %s AND %s
            ", $work_location_id, $start, $end));

            // Tổng doanh thu
            $total_revenue = $wpdb->get_var($wpdb->prepare("
                SELECT SUM(o.total_amount) FROM {$wpdb->prefix}aerp_order_orders o
                LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                WHERE e.work_location_id = %d
                  AND o.order_date BETWEEN %s AND %s
            ", $work_location_id, $start, $end));

            // Tổng chi phí
            $total_cost = $wpdb->get_var($wpdb->prepare("
                SELECT SUM(o.cost) FROM {$wpdb->prefix}aerp_order_orders o
                LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                WHERE e.work_location_id = %d
                  AND o.order_date BETWEEN %s AND %s
                  AND o.cost IS NOT NULL
            ", $work_location_id, $start, $end));

            // Tổng lợi nhuận
            $total_profit = ($total_revenue ?? 0) - ($total_cost ?? 0);

            // Đơn hàng theo tháng
            $orders_by_month = $wpdb->get_results($wpdb->prepare("
                SELECT DATE_FORMAT(o.order_date, '%Y-%m') as ym, COUNT(*) as total, SUM(o.total_amount) as revenue
                FROM {$wpdb->prefix}aerp_order_orders o
                LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                WHERE e.work_location_id = %d
                  AND o.order_date BETWEEN %s AND %s
                GROUP BY ym ORDER BY ym DESC
            ", $work_location_id, $start, $end), ARRAY_A);

            // Đơn hàng theo trạng thái
            $orders_by_status = $wpdb->get_results($wpdb->prepare("
                SELECT o.status, COUNT(*) as count
                FROM {$wpdb->prefix}aerp_order_orders o
                LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                WHERE e.work_location_id = %d
                  AND o.order_date BETWEEN %s AND %s
                GROUP BY o.status
            ", $work_location_id, $start, $end), ARRAY_A);
        ?>
            <section class="dashboard-section mb-5">
                <h2><i class="fas fa-shopping-cart"></i> Báo cáo đơn hàng</h2>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="summary-card card">
                            <div class="summary-icon orders">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">Tổng đơn hàng</div>
                                <div class="summary-value"><?= number_format($total_orders) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card card">
                            <div class="summary-icon revenue">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">Tổng doanh thu</div>
                                <div class="summary-value"><?= number_format($total_revenue) ?> đ</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card card">
                            <div class="summary-icon cost">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">Tổng chi phí</div>
                                <div class="summary-value"><?= number_format($total_cost) ?> đ</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card card">
                            <div class="summary-icon <?= $total_profit >= 0 ? 'profit' : 'loss' ?>">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">Lợi nhuận</div>
                                <div class="summary-value <?= $total_profit >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($total_profit) ?> đ
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="chart-container card">
                            <h5><i class="fas fa-chart-bar"></i> Đơn hàng & Doanh thu theo tháng</h5>
                            <?php if (empty($orders_by_month)): ?>
                                <div class="no-data">Không có dữ liệu đơn hàng</div>
                            <?php else: ?>
                                <canvas id="orderChart"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="chart-container card">
                            <h5><i class="fas fa-chart-pie"></i> Đơn hàng theo trạng thái</h5>
                            <?php if (empty($orders_by_status)): ?>
                                <div class="no-data">Không có dữ liệu trạng thái</div>
                            <?php else: ?>
                                <canvas id="orderStatusChart"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <script>
                    var orderChartData = {
                        labels: <?= json_encode(array_reverse(array_column($orders_by_month, 'ym'))) ?>,
                        orders: <?= json_encode(array_reverse(array_column($orders_by_month, 'total'))) ?>,
                        revenue: <?= json_encode(array_reverse(array_column($orders_by_month, 'revenue'))) ?>
                    };

                    var orderStatusData = {
                        labels: <?= json_encode(array_column($orders_by_status, 'status')) ?>,
                        data: <?= json_encode(array_column($orders_by_status, 'count')) ?>
                    };

                    jQuery(function($) {
                        // Order Chart
                        if (typeof Chart !== 'undefined' && $('#orderChart').length && orderChartData.labels.length > 0) {
                            new Chart(document.getElementById('orderChart'), {
                                type: 'bar',
                                data: {
                                    labels: orderChartData.labels,
                                    datasets: [{
                                            label: 'Số đơn',
                                            data: orderChartData.orders,
                                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 1,
                                            yAxisID: 'y'
                                        },
                                        {
                                            label: 'Doanh thu',
                                            data: orderChartData.revenue,
                                            backgroundColor: 'rgba(255, 206, 86, 0.2)',
                                            borderColor: 'rgba(255, 206, 86, 1)',
                                            borderWidth: 3,
                                            type: 'line',
                                            yAxisID: 'y1',
                                            tension: 0.3,
                                            pointRadius: 6,
                                            pointBackgroundColor: 'rgba(255, 206, 86, 1)',
                                            pointBorderColor: '#333',
                                            pointBorderWidth: 3
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        },
                                        y1: {
                                            beginAtZero: true,
                                            position: 'right',
                                            grid: {
                                                drawOnChartArea: false
                                            },
                                            ticks: {
                                                callback: function(value) {
                                                    return new Intl.NumberFormat('vi-VN', {
                                                        style: 'currency',
                                                        currency: 'VND'
                                                    }).format(value);
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }

                        // Order Status Chart
                        if (typeof Chart !== 'undefined' && $('#orderStatusChart').length && orderStatusData.labels.length > 0) {
                            new Chart(document.getElementById('orderStatusChart'), {
                                type: 'doughnut',
                                data: {
                                    labels: orderStatusData.labels.map(function(status) {
                                        var statusMap = {
                                            'pending': 'Chờ xử lý',
                                            'confirmed': 'Đã xác nhận',
                                            'cancelled': 'Đã hủy',
                                            'completed': 'Hoàn thành'
                                        };
                                        return statusMap[status] || status;
                                    }),
                                    datasets: [{
                                        data: orderStatusData.data,
                                        backgroundColor: [
                                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                                            '#9966FF', '#FF9F40', '#C9CBCF', '#28a745'
                                        ]
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'bottom'
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    return context.label + ': ' + context.raw + ' đơn';
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    });
                </script>
            <?php } ?>
            </section>
        <?php endif; ?>

        <?php if ($warehouse_active): ?>
            <?php
            // Lọc đơn hàng, kho theo kho user quản lý
            if (empty($user_warehouse_ids)) {
                echo '<div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                Bạn chưa được phân quyền quản lý kho nào, không thể xem báo cáo kho.
            </div>';
            } else {
                $warehouse_ids_sql = implode(',', array_map('intval', $user_warehouse_ids));
                $total_warehouses = count($user_warehouse_ids);
                $total_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_products WHERE id IN (SELECT product_id FROM {$wpdb->prefix}aerp_product_stocks WHERE warehouse_id IN ($warehouse_ids_sql))");

                // Lấy ngưỡng tồn kho thấp từ setting
                $low_stock_threshold = get_option('aerp_low_stock_threshold', 10);
                $low_stock = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_product_stocks WHERE quantity <= %d AND warehouse_id IN ($warehouse_ids_sql)", $low_stock_threshold));

                // Tổng tồn kho
                $total_stock = $wpdb->get_var("SELECT SUM(ps.quantity) FROM {$wpdb->prefix}aerp_product_stocks ps WHERE ps.warehouse_id IN ($warehouse_ids_sql)");

                // Sản phẩm hết hàng
                $out_of_stock = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_product_stocks WHERE quantity = 0 AND warehouse_id IN ($warehouse_ids_sql)");

                $stock_by_warehouse = $wpdb->get_results("SELECT w.name, SUM(ps.quantity) as total FROM {$wpdb->prefix}aerp_product_stocks ps JOIN {$wpdb->prefix}aerp_warehouses w ON ps.warehouse_id = w.id WHERE ps.warehouse_id IN ($warehouse_ids_sql) GROUP BY w.id, w.name", ARRAY_A);

                // Top sản phẩm tồn kho thấp
                $low_stock_products = $wpdb->get_results($wpdb->prepare("
                SELECT p.name, ps.quantity, w.name as warehouse_name
                FROM {$wpdb->prefix}aerp_product_stocks ps 
                JOIN {$wpdb->prefix}aerp_products p ON ps.product_id = p.id
                JOIN {$wpdb->prefix}aerp_warehouses w ON ps.warehouse_id = w.id
                WHERE ps.quantity <= %d AND ps.warehouse_id IN ($warehouse_ids_sql)
                ORDER BY ps.quantity ASC
                LIMIT 10
            ", $low_stock_threshold), ARRAY_A);
            ?>
                <section class="dashboard-section mb-5">
                    <h2><i class="fas fa-warehouse"></i> Báo cáo kho</h2>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="summary-card card">
                                <div class="summary-icon warehouses">
                                    <i class="fas fa-warehouse"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-label">Tổng kho</div>
                                    <div class="summary-value"><?= number_format($total_warehouses) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card card">
                                <div class="summary-icon products">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-label">Tổng sản phẩm</div>
                                    <div class="summary-value"><?= number_format($total_products) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card card">
                                <div class="summary-icon low-stock">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-label">Tồn kho thấp (≤<?= $low_stock_threshold ?>)</div>
                                    <div class="summary-value"><?= number_format($low_stock) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card card">
                                <div class="summary-icon out-of-stock">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-label">Hết hàng</div>
                                    <div class="summary-value"><?= number_format($out_of_stock) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="chart-container card">
                                <h5><i class="fas fa-chart-doughnut"></i> Phân bố tồn kho theo kho</h5>
                                <?php if (empty($stock_by_warehouse)): ?>
                                    <div class="no-data">Không có dữ liệu tồn kho</div>
                                <?php else: ?>
                                    <canvas id="warehouseStockChart"></canvas>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="chart-container card">
                                <h5><i class="fas fa-exclamation-triangle"></i> Sản phẩm tồn kho thấp</h5>
                                <?php if (empty($low_stock_products)): ?>
                                    <div class="no-data">Không có sản phẩm tồn kho thấp</div>
                                <?php else: ?>
                                    <div class="low-stock-list">
                                        <?php foreach ($low_stock_products as $product): ?>
                                            <div class="low-stock-item">
                                                <div class="product-name"><?= esc_html($product['name']) ?></div>
                                                <div class="warehouse-name"><?= esc_html($product['warehouse_name']) ?></div>
                                                <div class="quantity <?= $product['quantity'] == 0 ? 'out-of-stock' : 'low-stock' ?>">
                                                    <?= number_format($product['quantity']) ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <script>
                        var warehouseStockData = {
                            labels: <?= json_encode(array_column($stock_by_warehouse, 'name')) ?>,
                            data: <?= json_encode(array_map('intval', array_column($stock_by_warehouse, 'total'))) ?>
                        };
                        jQuery(function($) {
                            if (typeof Chart !== 'undefined' && $('#warehouseStockChart').length && warehouseStockData.labels.length > 0) {
                                new Chart(document.getElementById('warehouseStockChart'), {
                                    type: 'doughnut',
                                    data: {
                                        labels: warehouseStockData.labels,
                                        datasets: [{
                                            data: warehouseStockData.data,
                                            backgroundColor: [
                                                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                                                '#FF9F40', '#C9CBCF', '#4BC0C0', '#FF6384', '#28a745'
                                            ]
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                position: 'bottom'
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        return context.label + ': ' + context.raw.toLocaleString('vi-VN') + ' sản phẩm';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                        });
                    </script>
                <?php } ?>
                </section>
            <?php endif; ?>

            <?php if ($crm_active): ?>
                <?php
                // Lấy danh sách nhân viên thuộc chi nhánh hiện tại
                $employee_ids = $wpdb->get_col($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}aerp_hrm_employees WHERE work_location_id = %d",
                    $work_location_id
                ));

                if (!empty($employee_ids)) {
                    $employee_ids_sql = implode(',', array_map('intval', $employee_ids));

                    $total_customers = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_crm_customers WHERE assigned_to IN ($employee_ids_sql)");
                    $start_date = date('Y-m-01', strtotime($month));
                    $end_date = date('Y-m-t', strtotime($month));
                    $new_customers = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->prefix}aerp_crm_customers WHERE assigned_to IN ($employee_ids_sql) AND created_at BETWEEN %s AND %s",
                        $start_date,
                        $end_date
                    ));
                    $active_customers = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_crm_customers WHERE assigned_to IN ($employee_ids_sql) AND status = 'active'");
                } else {
                    $total_customers = $new_customers = $active_customers = 0;
                }
                // Khách hàng theo tháng được chọn
                if (!empty($employee_ids)) {
                    $customers_by_month = $wpdb->get_results($wpdb->prepare("
                        SELECT DATE(created_at) as day, COUNT(*) as total 
                        FROM {$wpdb->prefix}aerp_crm_customers 
                        WHERE assigned_to IN ($employee_ids_sql) AND created_at BETWEEN %s AND %s
                        GROUP BY day 
                        ORDER BY day ASC
                    ", $start_date, $end_date), ARRAY_A);
                } else {
                    $customers_by_month = [];
                }

                // Khách hàng theo nguồn trong tháng được chọn
                if (!empty($employee_ids)) {
                    $customers_by_source = $wpdb->get_results($wpdb->prepare("
                        SELECT cs.name as source_name, cs.color as source_color, o.customer_source_id, COUNT(*) as count
                        FROM {$wpdb->prefix}aerp_order_orders o
                        LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                        LEFT JOIN {$wpdb->prefix}aerp_crm_customer_sources cs ON o.customer_source_id = cs.id
                        WHERE e.work_location_id = %d
                        AND o.customer_source_id IS NOT NULL AND o.customer_source_id != 0
                        AND o.order_date BETWEEN %s AND %s
                        GROUP BY o.customer_source_id
                        ORDER BY count DESC
                    ", $work_location_id, $start_date, $end_date), ARRAY_A);
                } else {
                    $customers_by_source = [];
                }

                // Thống kê khách hàng quay lại trong tháng
                if (!empty($employee_ids)) {
                    $returning_customers = $wpdb->get_var($wpdb->prepare("
                        SELECT COUNT(DISTINCT o.customer_id) 
                        FROM {$wpdb->prefix}aerp_order_orders o
                        LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                        WHERE e.work_location_id = %d
                          AND o.order_date BETWEEN %s AND %s
                          AND o.customer_id IN (
                            SELECT o2.customer_id 
                            FROM {$wpdb->prefix}aerp_order_orders o2
                            LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e2 ON o2.employee_id = e2.id
                            WHERE e2.work_location_id = %d
                              AND o2.order_date BETWEEN %s AND %s
                            GROUP BY o2.customer_id 
                            HAVING COUNT(*) > 1
                        )
                    ", $work_location_id, $start_date, $end_date, $work_location_id, $start_date, $end_date));

                    $new_customers_with_orders = $wpdb->get_var($wpdb->prepare("
                        SELECT COUNT(DISTINCT o.customer_id) 
                        FROM {$wpdb->prefix}aerp_order_orders o
                        LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                        WHERE e.work_location_id = %d
                          AND o.order_date BETWEEN %s AND %s
                          AND o.customer_id IN (
                            SELECT o2.customer_id 
                            FROM {$wpdb->prefix}aerp_order_orders o2
                            LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e2 ON o2.employee_id = e2.id
                            WHERE e2.work_location_id = %d
                              AND o2.order_date BETWEEN %s AND %s
                            GROUP BY o2.customer_id 
                            HAVING COUNT(*) = 1
                        )
                    ", $work_location_id, $start_date, $end_date, $work_location_id, $start_date, $end_date));

                    // Doanh thu trung bình mỗi đơn hàng trong tháng
                    $avg_order_revenue = $wpdb->get_var($wpdb->prepare("
                        SELECT AVG(o.total_amount) 
                        FROM {$wpdb->prefix}aerp_order_orders o
                        LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                        WHERE e.work_location_id = %d
                          AND o.total_amount > 0 AND o.order_date BETWEEN %s AND %s
                    ", $work_location_id, $start_date, $end_date));

                    // Số đơn hàng 0đ trong tháng
                    $zero_amount_orders = $wpdb->get_var($wpdb->prepare("
                        SELECT COUNT(*) 
                        FROM {$wpdb->prefix}aerp_order_orders o
                        LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                        WHERE e.work_location_id = %d
                          AND (o.total_amount = 0 OR o.total_amount IS NULL) 
                          AND o.order_date BETWEEN %s AND %s
                    ", $work_location_id, $start_date, $end_date));

                    // Số đơn hàng có lợi nhuận trong tháng
                    $profitable_orders = $wpdb->get_var($wpdb->prepare("
                        SELECT COUNT(*) 
                        FROM {$wpdb->prefix}aerp_order_orders o
                        LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                        WHERE e.work_location_id = %d
                          AND (o.total_amount - COALESCE(o.cost, 0)) > 0 
                          AND o.order_date BETWEEN %s AND %s
                    ", $work_location_id, $start_date, $end_date));
                } else {
                    $returning_customers = $new_customers_with_orders = $avg_order_revenue = $zero_amount_orders = $profitable_orders = 0;
                }
                ?>
                <section class="dashboard-section mb-5">
                    <h2><i class="fas fa-user-friends"></i> Báo cáo khách hàng</h2>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="summary-card card">
                                <div class="summary-icon customers">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-label">Tổng khách hàng</div>
                                    <div class="summary-value"><?= number_format($total_customers) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card card">
                                <div class="summary-icon new-customers">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-label">Khách hàng mới trong tháng</div>
                                    <div class="summary-value"><?= number_format($new_customers) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card card">
                                <div class="summary-icon active-customers">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-label">Khách hàng hoạt động</div>
                                    <div class="summary-value"><?= number_format($active_customers) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card card">
                                <div class="summary-icon growth">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-label">Tỷ lệ tăng trưởng</div>
                                    <div class="summary-value">
                                        <?php
                                        // Tính tỷ lệ tăng trưởng dựa trên tổng khách hàng của chi nhánh
                                        if (!empty($employee_ids)) {
                                            $last_month_total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_crm_customers WHERE assigned_to IN ($employee_ids_sql) AND DATE(created_at) < CURDATE() - INTERVAL 30 DAY"));
                                            $current_month_total = $total_customers;

                                            if ($last_month_total > 0) {
                                                $growth_rate = round((($current_month_total - $last_month_total) / $last_month_total) * 100, 2);
                                            } else {
                                                $growth_rate = $current_month_total > 0 ? 100 : 0;
                                            }
                                        } else {
                                            $growth_rate = 0;
                                        }

                                        $growth_class = $growth_rate >= 0 ? 'text-success' : 'text-danger';
                                        $growth_icon = $growth_rate >= 0 ? '↗' : '↘';
                                        ?>
                                        <span class="<?= $growth_class ?>"><?= $growth_icon ?> <?= abs($growth_rate) ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="chart-container card">
                                <h5><i class="fas fa-chart-bar"></i> Khách hàng mới (<?= date('m/Y', strtotime($month)) ?>)</h5>
                                <?php if (empty($customers_by_month)): ?>
                                    <div class="no-data">Không có dữ liệu khách hàng</div>
                                <?php else: ?>
                                    <canvas id="customerChart"></canvas>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="chart-container card">
                                <h5><i class="fas fa-chart-pie"></i> Khách hàng theo nguồn (<?= date('m/Y', strtotime($month)) ?>)</h5>
                                <?php if (empty($customers_by_source)): ?>
                                    <div class="no-data">Không có dữ liệu nguồn</div>
                                <?php else: ?>
                                    <canvas id="customerSourceChart"></canvas>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <script>
                        var customerChartData = {
                            labels: <?= json_encode(array_column($customers_by_month, 'day')) ?>,
                            data: <?= json_encode(array_column($customers_by_month, 'total')) ?>
                        };

                        var customerSourceData = {
                            labels: <?= json_encode(array_column($customers_by_source, 'source_name')) ?>,
                            data: <?= json_encode(array_column($customers_by_source, 'count')) ?>,
                            colors: <?= json_encode(array_column($customers_by_source, 'source_color')) ?>
                        };

                        jQuery(function($) {
                            // Customer Chart
                            if (typeof Chart !== 'undefined' && $('#customerChart').length && customerChartData.labels.length > 0) {
                                new Chart(document.getElementById('customerChart'), {
                                    type: 'bar',
                                    data: {
                                        labels: customerChartData.labels,
                                        datasets: [{
                                            label: 'Khách hàng mới',
                                            data: customerChartData.data,
                                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            y: {
                                                beginAtZero: true
                                            }
                                        }
                                    }
                                });
                            }

                            // Customer Source Chart
                            if (typeof Chart !== 'undefined' && $('#customerSourceChart').length && customerSourceData.labels.length > 0) {
                                new Chart(document.getElementById('customerSourceChart'), {
                                    type: 'doughnut',
                                    data: {
                                        labels: customerSourceData.labels.map(function(source) {
                                            var sourceMap = {
                                                // 'fb': 'Facebook',
                                                // 'zalo': 'Zalo',
                                                // 'tiktok': 'Tiktok',
                                                // 'youtube': 'Youtube',
                                                // 'web': 'Website',
                                                // 'referral': 'KH cũ giới thiệu',
                                                // 'other': 'Khác'
                                            };
                                            return sourceMap[source] || source;
                                        }),
                                        datasets: [{
                                            data: customerSourceData.data,
                                            backgroundColor: customerSourceData.colors
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                position: 'bottom'
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        return context.label + ': ' + context.raw + ' khách hàng';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                        });
                    </script>

                    <!-- Thông tin chi tiết -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="chart-container card">
                                <h5><i class="fas fa-chart-pie"></i> Phân bố khách hàng</h5>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="metric-item">
                                            <div class="metric-value text-primary"><?= number_format($returning_customers) ?></div>
                                            <div class="metric-label">Khách hàng quay lại</div>
                                            <small class="text-muted">(≥2 đơn)</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="metric-item">
                                            <div class="metric-value text-success"><?= number_format($new_customers_with_orders) ?></div>
                                            <div class="metric-label">Khách hàng mới</div>
                                            <small class="text-muted">(1 đơn)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-container card">
                                <h5><i class="fas fa-chart-bar"></i> Thống kê đơn hàng</h5>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="metric-item">
                                            <div class="metric-value text-warning"><?= number_format($zero_amount_orders) ?></div>
                                            <div class="metric-label">Đơn 0đ</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="metric-item">
                                            <div class="metric-value text-success"><?= number_format($profitable_orders) ?></div>
                                            <div class="metric-label">Đơn có lãi</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="metric-item">
                                            <div class="metric-value text-info"><?= number_format($avg_order_revenue, 0) ?>đ</div>
                                            <div class="metric-label">Dthu TB/đơn</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <script>
                // Cập nhật tháng khi user thay đổi
                document.getElementById('month').addEventListener('change', function() {
                    var selectedMonth = this.value;
                    document.getElementById('month-hidden').value = selectedMonth;
                    document.getElementById('report-month-hidden').value = selectedMonth;
                });
            </script>
</div>
<style>
    .dashboard-section {
        margin-bottom: 40px;
    }

    .summary-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solidrgb(205, 206, 207);
    }

    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    .summary-card .summary-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        font-size: 1.5rem;
        color: white;
    }

    .summary-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .summary-icon.active {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .summary-icon.resigned {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    }

    .summary-icon.turnover {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
    }

    .summary-icon.orders {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    .summary-icon.revenue {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .summary-icon.cost {
        background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
    }

    .summary-icon.profit {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .summary-icon.loss {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    }

    .summary-icon.warehouses {
        background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
    }

    .summary-icon.products {
        background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
    }

    .summary-icon.low-stock {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    }

    .summary-icon.out-of-stock {
        background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
    }

    .summary-icon.customers {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    .summary-icon.new-customers {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .summary-icon.active-customers {
        background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
    }

    .summary-icon.growth {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
    }

    .summary-content {
        text-align: center;
    }

    .summary-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .summary-value {
        font-size: 1.8rem;
        font-weight: bold;
        color: #212529;
    }

    .dashboard-section h2 {
        font-size: 1.5rem;
        margin-bottom: 24px;
        color: #495057;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 10px;
    }

    .dashboard-section h2 i {
        margin-right: 10px;
        color: #007bff;
    }

    .chart-container {
        min-height: 300px;
        max-width: 100%;
        margin-bottom: 24px;
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border: 1px solidrgb(205, 205, 206);
        position: relative;
    }

    .chart-container h5 {
        margin-bottom: 20px;
        color: #495057;
        font-weight: 600;
    }

    .chart-container h5 i {
        margin-right: 8px;
        color: #007bff;
    }

    .chart-container canvas {
        max-width: 100% !important;
        max-height: 250px !important;
    }

    .no-data {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 200px;
        color: #6c757d;
        font-style: italic;
        background: #f8f9fa;
        border-radius: 8px;
        border: 2px dashed #dee2e6;
    }

    .low-stock-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .low-stock-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.2s ease;
    }

    .low-stock-item:hover {
        background-color: #f8f9fa;
    }

    .low-stock-item:last-child {
        border-bottom: none;
    }

    .low-stock-item .product-name {
        font-weight: 500;
        color: #495057;
        flex: 1;
    }

    .low-stock-item .warehouse-name {
        font-size: 0.8rem;
        color: #6c757d;
        margin-right: 10px;
    }

    .low-stock-item .quantity {
        font-weight: bold;
        padding: 4px 8px;
        border-radius: 4px;
        min-width: 40px;
        text-align: center;
    }

    .low-stock-item .quantity.low-stock {
        background-color: #fff3cd;
        color: #856404;
    }

    .low-stock-item .quantity.out-of-stock {
        background-color: #f8d7da;
        color: #721c24;
    }

    .alert {
        border-radius: 8px;
        border: none;
        padding: 15px 20px;
    }

    .alert i {
        margin-right: 8px;
    }

    .metric-item {
        padding: 15px 10px;
        border-radius: 8px;
        background: #f8f9fa;
        margin-bottom: 10px;
        transition: all 0.3s ease;
        border: 1px solid rgb(205, 206, 207);
    }

    .metric-item:hover {
        background: #e9ecef;
        transform: translateY(-2px);
    }

    .metric-value {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .metric-label {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .summary-card {
            margin-bottom: 20px;
        }

        .chart-container {
            min-height: 250px;
        }

        .summary-value {
            font-size: 1.5rem;
        }
    }
</style>
<?php
$content = ob_get_clean();
$title = 'AERP Dashboard';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
