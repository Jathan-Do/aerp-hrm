<?php
if (!defined('ABSPATH')) exit;

$employee_id = absint($_GET['employee_id'] ?? 0);
if (!$employee_id) {
    echo '<div class="notice notice-error"><p>Thiếu mã nhân viên.</p></div>';
    return;
}

$employee = AERP_Employee_Manager::get_by_id($employee_id);
if (!$employee) {
    echo '<div class="notice notice-error"><p>Nhân viên không tồn tại.</p></div>';
    return;
}

?>

<div class="wrap">
    <h1 class="wp-heading-inline">Giao việc cho: <?= esc_html($employee->full_name) ?></h1>
    <a href="<?= admin_url('admin.php?page=aerp_employees&view=' . $employee_id . '#tasks') ?>" class="page-title-action">← Quay lại nhân viên</a>
    <hr class="wp-header-end">

    <form method="post">
        <?php wp_nonce_field('aerp_add_task_action', 'aerp_add_task_nonce'); ?>

        <table class="form-table">
            <tr><th>Tiêu đề công việc</th><td><input type="text" name="task_title" required class="regular-text"></td></tr>
            <tr><th>Mô tả</th><td><textarea name="task_desc" class="large-text" rows="3"></textarea></td></tr>
            <tr><th>Hạn chót</th><td><input type="datetime-local" name="deadline" required></td></tr>
            <tr><th>Điểm KPI</th><td><input type="number" name="score" min="0" max="10"></td></tr>
            <tr><th>Trạng thái</th>
                <td>
                    <select name="status">
                        <option value="assigned">Đã giao</option>
                        <option value="done">Hoàn thành</option>
                        <option value="failed">Thất bại</option>
                    </select>
                </td>
            </tr>
        </table>

        <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">
        <p><input type="submit" name="aerp_add_task" class="button button-primary" value="Lưu công việc"></p>
    </form>
</div>
