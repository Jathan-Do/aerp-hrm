<?php
if (!defined('ABSPATH')) exit;
$current_user = wp_get_current_user();
$employee_id = absint($_GET['id'] ?? 0);
$employee = AERP_Frontend_Employee_Manager::get_by_id($employee_id);
if (!$employee) {
    echo '<div class="alert alert-danger">Nhân viên không tồn tại.</div>';
    return;
}
$today = date('Y-m-d');
$adjustments = AERP_Frontend_Adjustment_Manager::get_all();
ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Thêm tùy chỉnh cho: <?= esc_html($employee->full_name) ?></h2>
    <div class="user-info text-end">
        Xin chào, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_adjustment_action', 'aerp_save_adjustment_nonce'); ?>
            <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lí do</label>
                    <input type="text" name="reason" required class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ngày áp dụng</label>
                    <input type="date" name="date_effective" value="<?= esc_attr($today) ?>" required class="form-control bg-body">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Loại</label>
                    <select name="type" required class="form-control">
                        <option value="reward">🎁 Thưởng</option>
                        <option value="fine">⚠️ Phạt</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Số tiền</label>
                    <input type="number" name="amount" required class="form-control">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="aerp_save_adjustment" class="btn btn-primary">Lưu</button>
                    <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=adjustment') ?>" class="btn btn-secondary">Quay lại</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Thêm tùy chỉnh cho nhân viên';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
