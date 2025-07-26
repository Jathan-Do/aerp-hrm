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
    aerp_user_has_permission($user_id, 'salary_advance_add'),
    aerp_user_has_permission($user_id, 'salary_advance_edit'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
$employee_id = absint($_GET['id'] ?? 0);
$employee = AERP_Employee_Manager::get_by_id($employee_id);
if (!$employee) {
    wp_die(__('Nhân viên không tồn tại.'));
}

$edit_id = absint($_GET['advance_id'] ?? 0);
$advance = $edit_id ? AERP_Frontend_Advance_Manager::get_by_id($edit_id) : null;

ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2><?= $edit_id ? 'Sửa tạm ứng' : 'Thêm tạm ứng' ?></h2>
    <div class="user-info text-end">
        Chào, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Thoát
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_advance_action', 'aerp_save_advance_nonce'); ?>
            <input type="hidden" name="employee_id" value="<?= esc_attr($employee->id) ?>">
            <?php if ($edit_id): ?>
                <input type="hidden" name="advance_id" value="<?= esc_attr($edit_id) ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Nhân viên</label>
                <input type="text" class="form-control" value="<?= esc_html($employee->full_name) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Số tiền tạm ứng (VND)</label>
                <input type="number" class="form-control" name="amount" value="<?= esc_attr($advance->amount ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ngày ứng</label>
                <input type="date" class="form-control bg-body" name="advance_date" value="<?= esc_attr($advance->advance_date ?? date('Y-m-d')) ?>" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_advance" class="btn btn-primary">
                    <?= $edit_id ? 'Cập nhật' : 'Thêm mới' ?>
                </button>
                <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=advance') ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = $edit_id ? 'Sửa tạm ứng' : 'Thêm tạm ứng';
include AERP_HRM_PATH . 'frontend/dashboard/layout.php';
