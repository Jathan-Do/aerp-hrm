<?php
if (!defined('ABSPATH')) exit;
global $wpdb;

$employee_reward_id = absint($_GET['employee_reward_id'] ?? 0);
$row = AERP_Frontend_Employee_Reward_Manager::get_by_id($employee_reward_id);

if (!$row) {
    echo '<div class="alert alert-danger">❌ Không tìm thấy dữ liệu thưởng nhân viên.</div>';
    return;
}
$employee = AERP_Frontend_Employee_Manager::get_by_id($row->employee_id);
$rewards = AERP_Frontend_Reward_Manager::get_all();
$employee_id = $row->employee_id; 
$month = $row->month ? date('Y-m', strtotime($row->month)) : date('Y-m');
$current_user = wp_get_current_user();

ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Cập nhật thưởng cho: <?= esc_html($employee->full_name) ?></h2>
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
            <?php wp_nonce_field('aerp_save_employee_reward_action', 'aerp_save_employee_reward_nonce'); ?>
            <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">
            <input type="hidden" name="id" value="<?= esc_attr($employee_reward_id) ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ngày thưởng</label>
                    <input type="date" name="month" value="<?= esc_attr($month) ?>" required class="form-control bg-body">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ngày thưởng</label>
                    <select class="form-select" name="reward_id" required>
                        <?php foreach ($rewards as $r): ?>
                            <option value="<?= esc_attr($r->id) ?>" <?= selected($r->id, $row->reward_id) ?>>
                                <?= esc_html($r->name) ?> – <?= number_format($r->amount, 0, ',', '.') ?> đ
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="3"><?= esc_textarea($row->note) ?></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="aerp_save_employee_reward" class="btn btn-primary">Lưu thay đổi</button>
                    <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=reward') ?>" class="btn btn-secondary">Quay lại</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Cập nhật thưởng cho nhân viên';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
