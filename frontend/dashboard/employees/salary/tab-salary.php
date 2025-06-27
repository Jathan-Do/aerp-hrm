<?php
if (!defined('ABSPATH')) exit;
if (!isset($employee_id)) {
    $employee_id = absint($_POST['id'] ?? $_GET['id'] ?? 0);
}
if (!$employee_id) {
    echo '<div class="alert alert-danger">Không tìm thấy nhân viên.</div>';
    return;
}

$selected_month = $_POST['salary_month'] ?? null;

// Nếu bấm nút tính lương thì tính lương cho tháng đó
if (
    isset($_POST['aerp_generate_salary']) &&
    check_admin_referer('aerp_salary_action', 'aerp_salary_nonce') &&
    !empty($selected_month)
) {
    AERP_Frontend_Salary_Manager::calculate_salary($employee_id, $selected_month);
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Đã tính lương cho nhân viên tháng ' . esc_html(date('m/Y', strtotime($selected_month))) . '.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
}

// Nếu vừa tính lương thì chỉ hiển thị tháng đó, còn lại hiển thị toàn bộ lịch sử
if (!empty($selected_month) && isset($_POST['aerp_generate_salary'])) {
    $filters = ['employee_id' => $employee_id, 'salary_month' => $selected_month];
} else {
    $filters = ['employee_id' => $employee_id];
}

$table = new AERP_Frontend_Salary_Table([
    'employee_id' => $employee_id,
]);

$table->process_bulk_action();

?>
<div class="card">
    <div class="card-body">
        <div class="mb-3">
            <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=salary_config') ?>" class="btn btn-primary">Cấu hình lương</a>
            <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=advance') ?>" class="btn btn-secondary">+ Tạm ứng</a>


        </div>
        <form method="post" class="mb-3 d-flex gap-2 flex-md-row flex-column">
            <?php wp_nonce_field('aerp_salary_action', 'aerp_salary_nonce'); ?>
            <input class="form-control w-auto" type="month" name="salary_month" value="<?= esc_attr($selected_month ?: date('Y-m')) ?>" required>
            <button type="submit" name="aerp_generate_salary" class="btn btn-success">Tính lương tháng này</button>
        </form>
        <div id="aerp-salary-table-wrapper" data-employee-id="<?= esc_attr($employee_id) ?>">
            <?php $table->render(); ?>
        </div>
        <form method="post" action="<?= home_url('/aerp-salary/export') ?>" class="mt-3 d-flex gap-2">
            <?php wp_nonce_field('aerp_export_excel', 'aerp_export_nonce'); ?>
            <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">
            <input class="form-control w-auto" type="month" name="salary_month" value="<?= esc_attr($selected_month ?: date('Y-m')) ?>">
            <button type="submit" name="aerp_export_excel" class="btn btn-outline-primary">📥 Xuất Excel</button>
        </form>
    </div>
</div>