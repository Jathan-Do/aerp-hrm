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
    aerp_user_has_permission($user_id, 'task_edit'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

$task_id = absint($_GET['task_id'] ?? 0);
$task = AERP_Frontend_Task_Manager::get_by_id($task_id);
if (!$task) {
    echo '<div class="alert alert-danger">Không tìm thấy công việc.</div>';
    return;
}
$employee = AERP_Frontend_Employee_Manager::get_by_id($task->employee_id);
if (!$employee) {
    echo '<div class="alert alert-danger">Nhân viên không tồn tại.</div>';
    return;
}
ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Sửa công việc: <?= esc_html($task->task_title) ?></h2>
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
        ['label' => 'Chi tiết nhân viên', 'url' => home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=task')],
        ['label' => 'Sửa công việc']
    ]);
}
?>
<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_task_action', 'aerp_task_nonce'); ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tiêu đề công việc</label>
                    <input type="text" name="task_title" value="<?= esc_attr($task->task_title) ?>" required class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Hạn chót</label>
                    <input type="datetime-local" name="deadline" value="<?= esc_attr($task->deadline) ?>" required class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Điểm KPI</label>
                    <input type="number" name="score" value="<?= esc_attr($task->score) ?>" min="0" max="10" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="assigned" <?= selected($task->status, 'assigned', false) ?>>Đã giao</option>
                        <option value="done" <?= selected($task->status, 'done', false) ?>>Hoàn thành</option>
                        <option value="failed" <?= selected($task->status, 'failed', false) ?>>Thất bại</option>
                    </select>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea name="task_desc" class="form-control" rows="3"><?= esc_textarea($task->task_desc) ?></textarea>
                </div>
            </div>
            <input type="hidden" name="edit_id" value="<?= esc_attr($task_id) ?>">
            <input type="hidden" name="employee_id" value="<?= esc_attr($task->employee_id) ?>">
            <h5>Phản hồi nội bộ</h5>
            <?php $comments = AERP_Frontend_Task_Manager::get_comments($task->id); ?>
            <?php if ($comments): ?>
                <ul class="mb-3">
                    <?php foreach ($comments as $c): ?>
                        <li><strong><?= esc_html($c->display_name) ?>:</strong> <?= esc_html($c->comment) ?> <em>(<?= date('d/m/Y H:i', strtotime($c->created_at)) ?>)</em></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="mb-3"><em>Chưa có phản hồi.</em></p>
            <?php endif; ?>
            <div class="mb-3">
                <textarea name="comment" rows="3" class="form-control" placeholder="Phản hồi..."></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_edit_task" class="btn btn-primary">Cập nhật công việc & Gửi phản hồi (nếu có)</button>
                <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $task->employee_id . '&section=task') ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Sửa công việc';
include AERP_HRM_PATH . 'frontend/dashboard/layout.php';