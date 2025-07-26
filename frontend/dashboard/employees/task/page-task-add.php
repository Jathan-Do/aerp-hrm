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
    aerp_user_has_permission($user_id, 'task_create'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

$employee_id = absint($_GET['id'] ?? 0);
$employee = AERP_Frontend_Employee_Manager::get_by_id($employee_id);
if (!$employee) {
    echo '<div class="alert alert-danger">Nhân viên không tồn tại.</div>';
    return;
}

ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Giao việc cho: <?= esc_html($employee->full_name) ?></h2>
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
            <?php wp_nonce_field('aerp_task_action', 'aerp_task_nonce'); ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tiêu đề công việc</label>
                    <input type="text" name="task_title" required class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Hạn chót</label>
                    <input type="datetime-local" name="deadline" required class="form-control">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Điểm KPI</label>
                    <input type="number" name="score" min="0" max="10" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="assigned">Đã giao</option>
                        <option value="done">Hoàn thành</option>
                        <option value="failed">Thất bại</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea name="task_desc" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_task" class="btn btn-primary">Lưu công việc</button>
                <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=task') ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Giao việc cho nhân viên';
include AERP_HRM_PATH . 'frontend/dashboard/layout.php';
