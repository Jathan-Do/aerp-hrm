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
        <div class="d-flex align-items-center gap-2">
            <label class="fw-bold" for="month">Tháng:</label>
            <input class="form-control w-auto" type="month" id="month" name="month" value="<?= esc_attr($month) ?>" max="<?= date('Y-m') ?>">
            
            <!-- Form xem báo cáo -->
            <form method="get" style="display: inline;">
                <input type="hidden" name="month" value="<?= esc_attr($month) ?>" id="month-hidden">
                <button type="submit" class="btn btn-primary">Xem</button>
            </form>
            
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
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="summary-card card">Tổng nhân sự<br><span><?= number_format($summary['total']) ?></span></div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card card">Đang làm<br><span><?= number_format($summary['joined']) ?></span></div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card card">Nghỉ việc<br><span><?= number_format($summary['resigned']) ?></span></div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="chart-container card">
                        <h5>Hiệu suất theo phòng ban</h5>
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="chart-container card">
                        <h5>Phân bố thâm niên</h5>
                        <canvas id="tenureChart"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="chart-container card">
                        <h5>Phân bố phòng ban</h5>
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="chart-container card">
                        <h5>Chi phí lương</h5>
                        <canvas id="salaryChart"></canvas>
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
            echo '<div class="alert alert-warning">Bạn chưa được gán chi nhánh, không thể xem báo cáo đơn hàng.</div>';
        } else {
            $start = date('Y-m-01', strtotime($month));
            $end   = date('Y-m-t', strtotime($month));
            $total_orders = $wpdb->get_var("
                SELECT COUNT(*) FROM {$wpdb->prefix}aerp_order_orders o
                LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                WHERE e.work_location_id = $work_location_id
                  AND o.order_date BETWEEN '$start' AND '$end'
            ");
            $total_revenue = $wpdb->get_var("
                SELECT SUM(o.total_amount) FROM {$wpdb->prefix}aerp_order_orders o
                LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                WHERE e.work_location_id = $work_location_id
                  AND o.order_date BETWEEN '$start' AND '$end'
            ");
            $orders_by_month = $wpdb->get_results("
                SELECT DATE_FORMAT(o.order_date, '%Y-%m') as ym, COUNT(*) as total, SUM(o.total_amount) as revenue
                FROM {$wpdb->prefix}aerp_order_orders o
                LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON o.employee_id = e.id
                WHERE e.work_location_id = $work_location_id
                  AND o.order_date BETWEEN '$start' AND '$end'
                GROUP BY ym ORDER BY ym DESC
            ", ARRAY_A);
        ?>
            <section class="dashboard-section mb-5">
                <h2><i class="fas fa-shopping-cart"></i> Báo cáo đơn hàng</h2>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="summary-card card">Tổng đơn hàng<br><span><?= number_format($total_orders) ?></span></div>
                    </div>
                    <div class="col-md-6">
                        <div class="summary-card card">Tổng doanh thu<br><span><?= number_format($total_revenue) ?></span></div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="chart-container card">
                            <h5>Đơn hàng & Doanh thu theo tháng</h5>
                            <canvas id="orderChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>
            <script>
                var orderChartData = {
                    labels: <?= json_encode(array_reverse(array_column($orders_by_month, 'ym'))) ?>,
                    orders: <?= json_encode(array_reverse(array_column($orders_by_month, 'total'))) ?>,
                    revenue: <?= json_encode(array_reverse(array_column($orders_by_month, 'revenue'))) ?>
                };
                jQuery(function($) {
                    if (typeof Chart !== 'undefined' && $('#orderChart').length) {
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
                                        pointRadius: 6, // tăng kích thước điểm
                                        pointBackgroundColor: 'rgba(255, 206, 86, 1)', // vàng đậm
                                        pointBorderColor: '#333', // viền đen
                                        pointBorderWidth: 3
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
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
                });
            </script>
        <?php } ?>
    <?php endif; ?>

    <?php if ($warehouse_active): ?>
        <?php
        // Lọc đơn hàng, kho theo kho user quản lý
        if (empty($user_warehouse_ids)) {
            echo '<div class="alert alert-warning">Bạn chưa được phân quyền quản lý kho nào, không thể xem báo cáo kho.</div>';
        } else {
            $warehouse_ids_sql = implode(',', array_map('intval', $user_warehouse_ids));
            $total_warehouses = count($user_warehouse_ids);
            $total_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_products WHERE id IN (SELECT product_id FROM {$wpdb->prefix}aerp_product_stocks WHERE warehouse_id IN ($warehouse_ids_sql))");

            // Lấy ngưỡng tồn kho thấp từ setting
            $low_stock_threshold = get_option('aerp_low_stock_threshold', 10);
            $low_stock = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_product_stocks WHERE quantity <= %d AND warehouse_id IN ($warehouse_ids_sql)", $low_stock_threshold));
            $stock_by_warehouse = $wpdb->get_results("SELECT w.name, SUM(ps.quantity) as total FROM {$wpdb->prefix}aerp_product_stocks ps JOIN {$wpdb->prefix}aerp_warehouses w ON ps.warehouse_id = w.id WHERE ps.warehouse_id IN ($warehouse_ids_sql) GROUP BY w.id, w.name", ARRAY_A);
        ?>
            <section class="dashboard-section mb-5">
                <h2><i class="fas fa-warehouse"></i> Báo cáo kho</h2>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="summary-card card">Tổng kho<br><span><?= number_format($total_warehouses) ?></span></div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card card">Tổng sản phẩm<br><span><?= number_format($total_products) ?></span></div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card card">Sản phẩm tồn kho thấp<br><span><?= number_format($low_stock) ?></span></div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="chart-container card">
                            <h5>Phân bố tồn kho theo kho</h5>
                            <canvas id="warehouseStockChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>
            <script>
                var warehouseStockData = {
                    labels: <?= json_encode(array_column($stock_by_warehouse, 'name')) ?>,
                    data: <?= json_encode(array_map('intval', array_column($stock_by_warehouse, 'total'))) ?>
                };
                jQuery(function($) {
                    if (typeof Chart !== 'undefined' && $('#warehouseStockChart').length) {
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
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.raw + ' sản phẩm';
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
    <?php endif; ?>

    <?php if ($crm_active): ?>
        <?php
        $total_customers = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_crm_customers");
        $new_customers = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_crm_customers WHERE DATE(created_at) >= CURDATE() - INTERVAL 30 DAY");
        $customers_by_month = $wpdb->get_results("SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total FROM {$wpdb->prefix}aerp_crm_customers GROUP BY ym ORDER BY ym DESC LIMIT 12", ARRAY_A);
        ?>
        <section class="dashboard-section mb-5">
            <h2><i class="fas fa-user-friends"></i> Báo cáo khách hàng</h2>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="summary-card card">Tổng khách hàng<br><span><?= number_format($total_customers) ?></span></div>
                </div>
                <div class="col-md-6">
                    <div class="summary-card card">Khách hàng mới 30 ngày<br><span><?= number_format($new_customers) ?></span></div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="chart-container card">
                        <h5>Khách hàng mới theo tháng</h5>
                        <canvas id="customerChart"></canvas>
                    </div>
                </div>
            </div>
        </section>
        <script>
            var customerChartData = {
                labels: <?= json_encode(array_reverse(array_column($customers_by_month, 'ym'))) ?>,
                data: <?= json_encode(array_reverse(array_column($customers_by_month, 'total'))) ?>
            };
            jQuery(function($) {
                if (typeof Chart !== 'undefined' && $('#customerChart').length) {
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
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
                    });
    </script>
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
        border-radius: 8px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.07);
        padding: 24px 0;
        text-align: center;
        font-size: 1.2rem;
        margin-bottom: 16px;
        font-weight: 500;
    }

    .summary-card span {
        display: block;
        font-size: 2.2rem;
        font-weight: bold;
        color: #007bff;
        margin-top: 8px;
    }

    .dashboard-section h2 {
        font-size: 1.5rem;
        margin-bottom: 24px;
    }

    .chart-container {
        min-height: 220px;
        max-width: 100%;
        margin-bottom: 24px;
        background: #fff;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.07);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .chart-container canvas {
        max-width: 100% !important;
        max-height: 220px !important;
    }
</style>
<?php
$content = ob_get_clean();
$title = 'AERP Dashboard';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
