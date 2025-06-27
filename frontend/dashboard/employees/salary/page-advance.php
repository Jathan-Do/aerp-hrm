<?php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

if (!is_user_logged_in() || !aerp_user_has_role($user_id, 'admin')) {
    wp_die(__('Bạn không có quyền truy cập.'));
}

$employee_id = absint($_GET['id'] ?? 0);
$employee = AERP_Frontend_Employee_Manager::get_by_id($employee_id);
if (!$employee) {
    wp_die(__('Nhân viên không tồn tại.'));
}

$table = new AERP_Frontend_Advance_Table([
    'employee_id' => $employee_id
]);
$table->process_bulk_action(); // Xử lý bulk + single action (delete)

ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Tạm ứng lương – <?= esc_html($employee->full_name) ?></h2>
    <div class="user-info text-end">
        Welcome, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(site_url('/aerp-dang-nhap')); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách tạm ứng</h5>
        <a href="<?= home_url("/aerp-hrm-employees/?action=view&id={$employee_id}&section=advance&sub_action=add") ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm tạm ứng
        </a>
    </div>
    <div class="card-body">
        <?php // Display messages if any (using Transients API)
        $message = get_transient('aerp_advance_message');
        if ($message) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    ' . esc_html($message) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            delete_transient('aerp_advance_message'); // Xóa transient sau khi hiển thị
        }
        ?>
        <div id="aerp-advance-table-wrapper" data-employee-id="<?= esc_attr($employee_id) ?>">
            <?php $table->render(); ?>
        </div>
        <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=salary') ?>" class="btn btn-secondary" style="width: fit-content;">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>




<?php
$content = ob_get_clean();
$title = 'Danh sách tạm ứng';
include AERP_HRM_PATH . 'frontend/dashboard/layout.php';
