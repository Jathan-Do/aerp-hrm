<?php
if (!defined('ABSPATH')) exit;
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
    aerp_user_has_role($user_id, 'accountant'),
    aerp_user_has_permission($user_id, 'salary_add'),
    aerp_user_has_permission($user_id, 'salary_edit'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
$employee_id = absint($_GET['id'] ?? 0);
$employee = AERP_Frontend_Employee_Manager::get_by_id($employee_id);
if (!$employee) {
    wp_die(__('Nhân viên không tồn tại.'));
}
$table = new AERP_Frontend_Salary_Config_Table([
    'employee_id' => $employee_id
]);
$table->process_bulk_action();
ob_start();
?>

<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Cấu hình lương – <?= esc_html($employee->full_name) ?></h2>
    <div class="user-info text-end">
        Welcome, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(site_url('/aerp-dang-nhap')); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách cấu hình lương</h5>
        <a href="<?= home_url("/aerp-hrm-employees/?action=view&id={$employee_id}&section=salary_config&sub_action=add") ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm cấu hình lương
        </a>
    </div>
    <div class="card-body">
        <?php // Display messages if any (using Transients API)
        $message = get_transient('aerp_salary_config_message');
        if ($message) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    ' . esc_html($message) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            delete_transient('aerp_salary_config_message'); // Xóa transient sau khi hiển thị
        }
        ?>
        <div id="aerp-salary-config-table-wrapper" data-employee-id="<?= esc_attr($employee_id) ?>">
            <?php $table->render(); ?>
        </div>
        <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=salary') ?>" class="btn btn-secondary" style="width: fit-content;">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>




<?php
$content = ob_get_clean();
$title = 'Danh sách cấu hình lương';
include AERP_HRM_PATH . 'frontend/dashboard/layout.php';