<?php
if (!defined('ABSPATH')) exit;

$task_id = absint($_GET['id'] ?? 0);
$task = AERP_Task_Manager::get_by_id($task_id);

if (!$task) {
    echo '<div class="notice notice-error"><p>Không tìm thấy công việc.</p></div>';
    return;
}

$employee = AERP_Employee_Manager::get_by_id($task->employee_id);
if (!$employee) {
    echo '<div class="notice notice-error"><p>Nhân viên không tồn tại.</p></div>';
    return;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Sửa công việc: <?= esc_html($task->task_title) ?></h1>
    <a href="<?= admin_url('admin.php?page=aerp_employees&view=' . $task->employee_id . '#tasks') ?>" class="page-title-action">← Quay lại nhân viên</a>
    <hr class="wp-header-end">

    <form method="post">
        <?php wp_nonce_field('aerp_edit_task_action', 'aerp_edit_task_nonce'); ?>

        <table class="form-table">
            <tr>
                <th>Tiêu đề công việc</th>
                <td><input type="text" name="task_title" value="<?= esc_attr($task->task_title) ?>" required class="regular-text"></td>
            </tr>
            <tr>
                <th>Mô tả</th>
                <td><textarea name="task_desc" class="large-text" rows="3"><?= esc_textarea($task->task_desc) ?></textarea></td>
            </tr>
            <tr>
                <th>Hạn chót</th>
                <td><input type="datetime-local" name="deadline" value="<?= esc_attr($task->deadline) ?>" required></td>
            </tr>
            <tr>
                <th>Điểm KPI</th>
                <td><input type="number" name="score" value="<?= esc_attr($task->score) ?>" min="0" max="10"></td>
            </tr>
            <tr>
                <th>Trạng thái</th>
                <td>
                    <select name="status">
                        <option value="assigned" <?= selected($task->status, 'assigned', false) ?>>Đã giao</option>
                        <option value="done" <?= selected($task->status, 'done', false) ?>>Hoàn thành</option>
                        <option value="failed" <?= selected($task->status, 'failed', false) ?>>Thất bại</option>
                    </select>
                </td>
            </tr>
        </table>

        <input type="hidden" name="id" value="<?= esc_attr($task_id) ?>">
        <input type="hidden" name="employee_id" value="<?= esc_attr($task->employee_id) ?>">

        <h2>Phản hồi nội bộ</h2>
        <?php $comments = AERP_Task_Manager::get_comments($task->id); ?>
        <?php if ($comments): ?>
            <ul style="margin-left:20px;">
                <?php foreach ($comments as $c): ?>
                    <li><strong><?= esc_html($c->display_name) ?>:</strong> <?= esc_html($c->comment) ?> <em>(<?= date('d/m/Y H:i', strtotime($c->created_at)) ?>)</em></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><em>Chưa có phản hồi.</em></p>
        <?php endif; ?>

        <textarea name="comment" rows="3" style="width:100%;" placeholder="Phản hồi..."></textarea>

        <p><input type="submit" name="aerp_update_task" class="button button-primary" value="Cập nhật công việc & Gửi phản hồi (nếu có)"></p>
    </form>


</div>