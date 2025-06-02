<?php
if (!defined('ABSPATH')) exit;

require_once AERP_HRM_PATH . 'includes/table/table-salary.php';

$month = $_POST['salary_month'] ?? null;

// Xử lý tính lương nếu admin bấm nút
if (
    isset($_POST['aerp_generate_salary']) &&
    check_admin_referer('aerp_salary_action', 'aerp_salary_nonce')
) {
    $month = sanitize_text_field($_POST['salary_month']);
    AERP_Salary_Manager::calculate_salary($employee_id, $month);
    echo '<div class="notice notice-success"><p>✅ Đã tính lương cho nhân viên tháng ' . esc_html(date('m/Y', strtotime($month))) . '.</p></div>';
}

// Giao diện form
?>

<p>
    <a href="<?= admin_url('admin.php?page=aerp_salary_add&employee_id=' . $employee_id) ?>" class="button button-primary">
        Thêm cấu hình lương
    </a>
    <a href="<?= admin_url('admin.php?page=aerp_advance_add&employee_id=' . $employee->id) ?>" class="button button-secondary">
        + Tạm ứng lương
    </a>
</p>

<form method="post" style="margin-bottom: 20px;">
    <?php wp_nonce_field('aerp_salary_action', 'aerp_salary_nonce'); ?>
    <input type="month" name="salary_month" value="<?= esc_attr($month ?: date('Y-m')) ?>" required>
    <input type="submit" name="aerp_generate_salary" class="button" value="Tính lương tháng này">
</form>

<?php
$table = new AERP_Salary_Table($employee_id);
$table->process_bulk_action();
$table->prepare_items($month); // hỗ trợ truyền tháng lọc
?>
<form method="get">
    <input type="hidden" name="page" value="aerp_employees">
    <input type="hidden" name="view" value="<?= esc_attr($employee_id) ?>">
    <input type="hidden" name="tab" value="salary">
    <input type="hidden" name="paged_salary" value="<?= $table->get_pagenum() ?>">
    <?php $table->search_box('Tìm kiếm', 'search_salary'); ?>
</form>
<form method="post">
    <?php $table->display(); ?>
</form>
<form method="post" action="<?= admin_url('admin-post.php') ?>" style="margin-top: 20px;">
    <?php wp_nonce_field('aerp_export_excel', 'aerp_export_nonce'); ?>
    <input type="hidden" name="action" value="aerp_export_excel_common">
    <input type="hidden" name="callback" value="salary_employee_export">
    <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">

    <!-- ✅ Lấy lại từ bảng nếu form không còn giá trị -->
    <input type="month" name="salary_month" value="<?= esc_attr($month ?: date('Y-m')) ?>">

    <button type="submit" name="aerp_export_excel" class="button">📥 Xuất Excel</button>
</form>