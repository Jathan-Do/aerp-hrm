<?php
$report_month = sanitize_text_field($_GET['report_month'] ?? date('Y-m'));

if (
    isset($_POST['aerp_export_excel']) &&
    check_admin_referer('aerp_export_excel', 'aerp_export_nonce')
)
?>
<div class="wrap aerp-hrm-reports">
    <h1 class="wp-heading-inline">📊 Báo cáo nhân sự</h1>

    <form method="get" class="report-filters">
        <input type="hidden" name="page" value="aerp_hrm_reports">
        <label>Tháng:
            <input type="month" name="report_month" value="<?= esc_attr($report_month) ?>">
        </label>
        <button class="button button-primary">Lọc</button>
    </form>

    <div class="report-summary">
        <div class="summary-card">
            <h3>Tổng nhân sự</h3>
            <div class="number"><?= number_format($total_employees) ?></div>
        </div>
        <div class="summary-card">
            <h3>Vào làm</h3>
            <div class="number"><?= number_format($joined) ?></div>
        </div>
        <div class="summary-card">
            <h3>Nghỉ việc</h3>
            <div class="number"><?= number_format($resigned) ?></div>
        </div>
    </div>

    <div class="report-charts">
        <div class="chart-container">
            <h3>Hiệu suất theo phòng ban</h3>
            <canvas id="performanceChart" height="200"></canvas>
        </div>

        <div class="chart-container">
            <h3>Phân bố thâm niên</h3>
            <canvas id="tenureChart" height="200"></canvas>
        </div>

        <div class="chart-container">
            <h3>Phân bố phòng ban</h3>
            <canvas id="departmentChart" height="200"></canvas>
        </div>

        <div class="chart-container">
            <h3>Chi phí lương</h3>
            <canvas id="salaryChart" height="200"></canvas>
        </div>
    </div>

    <div class="report-exports">
        <h3>Xuất dữ liệu</h3>
        <form method="post" action="<?= admin_url('admin-post.php') ?>">
            <?php wp_nonce_field('aerp_export_excel', 'aerp_export_nonce'); ?>
            <input type="hidden" name="action" value="aerp_export_excel_common">
            <input type="hidden" name="callback" value="hrm_summary_report_export">
            <input type="hidden" name="report_month" value="<?= esc_attr($report_month) ?>">
            <button type="submit" name="aerp_export_excel" class="button">📥 Xuất Excel</button>
        </form>

    </div>

</div>