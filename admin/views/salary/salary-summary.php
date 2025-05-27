<?php
if (!defined('ABSPATH')) exit;

require_once AERP_HRM_PATH . 'includes/table/table-salary-summary.php';

$month = sanitize_text_field($_GET['salary_month'] ?? '');
$table = new AERP_Salary_Summary_Table();
$table->process_bulk_action();
$table->prepare_items($month);
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Bảng lương tổng hợp</h1>

    <form method="get" style="margin: 15px 0;">
        <input type="hidden" name="page" value="aerp_salary_summary">
        <label for="salary_month">Tháng:</label>
        <input type="month" name="salary_month" value="<?= esc_attr($month) ?>">
        <button class="button">Lọc</button>
        <?php $table->search_box('Tìm kiếm', 'search_salary'); ?>
    </form>

    <form method="post">
        <?php $table->display(); ?>
    </form>

    <form method="post" action="<?= admin_url('admin-post.php') ?>">
        <?php wp_nonce_field('aerp_export_excel', 'aerp_export_nonce'); ?>
        <input type="hidden" name="action" value="aerp_export_excel_common">
        <input type="hidden" name="callback" value="salary_summary_export">
        <input type="hidden" name="salary_month" value="<?= esc_attr($month) ?>">
        <button type="submit" name="aerp_export_excel" class="button button-primary">📥 Xuất Excel</button>
    </form>
</div>
