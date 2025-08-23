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
    aerp_user_has_permission($user_id, 'reward_edit'),
    aerp_user_has_permission($user_id, 'disciplinary_edit'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
$id = absint($_GET['adjustment_id'] ?? 0);
$adjustment = AERP_Frontend_Adjustment_Manager::get_by_id($id);
if (!$adjustment) {
    echo '<div class="alert alert-danger">❌ Không tìm thấy bản ghi điều chỉnh.</div>';
    return;
}
$employee_id = $adjustment->employee_id;
$employee = AERP_Frontend_Employee_Manager::get_by_id($employee_id);
if (!$employee) {
    echo '<div class="alert alert-danger">Nhân viên không tồn tại.</div>';
    return;
}
ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Cập nhật tùy chỉnh cho: <?= esc_html($employee->full_name) ?></h2>
    <div class="user-info text-end">
        Xin chào, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>
<?php
if (function_exists('aerp_render_breadcrumb')) {
    aerp_render_breadcrumb([
        ['label' => 'Trang chủ', 'url' => home_url('/aerp-dashboard'), 'icon' => 'fas fa-home'],
        ['label' => 'Quản lý nhân viên', 'url' => home_url('/aerp-hrm-employees')],
        ['label' => 'Chi tiết nhân viên', 'url' => home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=adjustment')],
        ['label' => 'Cập nhật tùy chỉnh']
    ]);
}
?>
<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_adjustment_action', 'aerp_save_adjustment_nonce'); ?>
            <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">
            <input type="hidden" name="id" value="<?= esc_attr($id) ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lí do</label>
                    <input type="text" name="reason" value="<?= esc_attr($adjustment->reason) ?>" required class="form-control shadow-sm">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ngày áp dụng</label>
                    <input type="date" name="date_effective" value="<?= esc_attr($adjustment->date_effective) ?>" required class="form-control shadow-sm bg-body">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Loại</label>
                    <select name="type" required class="form-control shadow-sm">
                        <option value="reward" <?= selected($adjustment->type, 'reward') ?>>🎁 Thưởng</option>
                        <option value="fine" <?= selected($adjustment->type, 'fine') ?>>⚠️ Phạt</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Số tiền</label>
                    <input type="number" name="amount" value="<?= esc_attr($adjustment->amount) ?>" required class="form-control shadow-sm">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="description" class="form-control shadow-sm" rows="3"><?= esc_textarea($adjustment->description) ?></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="aerp_save_adjustment" class="btn btn-primary">Lưu thay đổi</button>
                    <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=adjustment') ?>" class="btn btn-secondary">Quay lại</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Cập nhật tùy chỉnh cho nhân viên';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');