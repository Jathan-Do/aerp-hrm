<?php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Check if user is logged in and has admin capabilities
if (!is_user_logged_in() || !aerp_user_has_role($user_id, 'admin')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
$month = sanitize_text_field($_GET['salary_month'] ?? '');
$table = new AERP_Frontend_Salary_Summary_Table();
$table->set_filters(['salary_month' => $month]);
$table->process_bulk_action();

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Bảng lương tổng hợp</h2>
    <div class="user-info">
        Welcome, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(site_url('/aerp-dang-nhap')); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form id="aerp-customer-filter-form" class="row g-2 mb-3 aerp-table-ajax-form" data-table-wrapper="#aerp-salary-summary-table-wrapper" data-ajax-action="aerp_hrm_filter_salary_summary">
            <div class="col-12 col-md-2 mb-2">
                <input type="hidden" name="aerp_salary_summary" value="1">
                <label class="orm-label mb-1" for="salary_month">Tháng:</label>
                <input class="form-control" type="month" name="salary_month" value="<?= esc_attr($month) ?>">
            </div>
            <div class="col-12 col-md-1 d-flex align-items-end mb-2">
                <button type="submit" class="btn btn-primary w-100">Lọc</button>
            </div>
        </form>
        <div id="aerp-salary-summary-table-wrapper">
            <?php $table->render(); ?>
        </div>
        <form method="post" action="<?= admin_url('admin-post.php') ?>">
            <?php wp_nonce_field('aerp_export_excel', 'aerp_export_nonce'); ?>
            <input type="hidden" name="action" value="aerp_export_excel_common">
            <input type="hidden" name="callback" value="salary_summary_export">
            <input type="hidden" name="salary_month" value="<?= esc_attr($month) ?>">
            <button type="submit" name="aerp_export_excel" class="btn btn-success">📥 Xuất Excel</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Bảng lương tổng hợp';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
