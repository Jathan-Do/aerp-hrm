<?php
if (!defined('ABSPATH')) exit;

require_once AERP_HRM_PATH . 'includes/table/table-attendance.php';

$month = sanitize_text_field($_GET['att_month'] ?? date('Y-m'));
$table = new AERP_Attendance_Table($employee_id);
$table->process_bulk_action();
$table->prepare_items();
?>

<p>
    <a href="<?= admin_url('admin.php?page=aerp_attendance_add&employee_id=' . $employee_id) ?>" class="button button-primary">
        + Thêm chấm công
    </a>
</p>

<form method="get" style="margin-bottom: 15px;">
    <input type="hidden" name="page" value="aerp_employees">
    <input type="hidden" name="view" value="<?= esc_attr($employee_id) ?>">
    <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">
    <label>Tháng:
        <input type="month" name="att_month" value="<?= esc_attr($month) ?>">
    </label>
    <input type="submit" class="button" value="Lọc">
</form>
<form method="post">
    <?php $table->search_box('Tìm kiếm', 'search_attendance'); ?>
    <?php $table->display(); ?>
</form>
