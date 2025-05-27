<?php
require_once AERP_HRM_PATH . 'includes/table/table-task.php';

$table = new AERP_Task_Table($employee_id);
$table->process_bulk_action();
$table->prepare_items();

// Get current month and year if not set
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Calculate total KPI points for the selected month
$total_kpi = 0;
$tasks = $table->get_tasks_by_month($current_month, $current_year);
foreach ($tasks as $task) {
    $total_kpi += floatval($task->score);
}

// Vietnamese month names
$vietnamese_months = [
    1 => 'Tháng 1',
    2 => 'Tháng 2',
    3 => 'Tháng 3',
    4 => 'Tháng 4',
    5 => 'Tháng 5',
    6 => 'Tháng 6',
    7 => 'Tháng 7',
    8 => 'Tháng 8',
    9 => 'Tháng 9',
    10 => 'Tháng 10',
    11 => 'Tháng 11',
    12 => 'Tháng 12'
];
?>

<p>
    <a href="<?= admin_url('admin.php?page=aerp_task_add&employee_id=' . $employee_id) ?>" class="button button-primary">Giao việc mới</a>
</p>

<div class="kpi-summary" style="margin: 15px 0; padding: 10px; background: #f8f9fa; border: 1px solid #ddd;">
    <h3>Tổng điểm KPI <?= $vietnamese_months[$current_month] . ' ' . $current_year ?>: <strong><?= number_format($total_kpi) ?></strong></h3>
</div>

<form method="get">
    <input type="hidden" name="page" value="aerp_employees">
    <input type="hidden" name="view" value="<?= esc_attr($_GET['view']) ?>">
    <select name="status">
        <option value="">-- Tất cả trạng thái --</option>
        <option value="assigned" <?= selected($_GET['status'] ?? '', 'assigned') ?>>Đã giao</option>
        <option value="done" <?= selected($_GET['status'] ?? '', 'done') ?>>Hoàn thành</option>
        <option value="failed" <?= selected($_GET['status'] ?? '', 'failed') ?>>Thất bại</option>
    </select>
    
    <select name="month">
        <?php foreach ($vietnamese_months as $num => $name): ?>
            <option value="<?= $num ?>" <?= selected($current_month, $num, false) ?>><?= $name ?></option>
        <?php endforeach; ?>
    </select>
    
    <select name="year">
        <?php
        $current_year = date('Y');
        for ($i = $current_year - 2; $i <= $current_year + 1; $i++) {
            printf(
                '<option value="%d" %s>%d</option>',
                $i,
                selected($current_year, $i, false),
                $i
            );
        }
        ?>
    </select>
    
    <button class="button">Lọc</button>
</form>

<form method="post">
    <?php $table->search_box('Tìm công việc', 'search_task'); ?>
    <?php $table->display(); ?>
</form>