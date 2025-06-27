<?php
if (!defined('ABSPATH')) exit;
if (!isset($employee_id)) {
    $employee_id = absint($_POST['id'] ?? $_GET['id'] ?? 0);
}
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Tên tháng tiếng Việt
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
// Dữ liệu bảng
$table = new AERP_Frontend_Task_Table(['employee_id' => $employee_id]);
$table->set_filters([
    'employee_id' => $employee_id,
    'month' => $current_month,
    'year' => $current_year,
    'status' => $_GET['status'] ?? '',
    's' => $_GET['s'] ?? '',
]);

// Tính tổng KPI
$total_kpi = 0;
$all_tasks = $table->get_tasks_by_month($current_month, $current_year);
foreach ($all_tasks as $task) {
    if ($task->status === 'done') {
        $total_kpi += floatval($task->score);
    }
}
$table->process_bulk_action();

?>
<div class="card">
    <div class="card-body">
        <div class="kpi-summary d-flex justify-content-between align-items-md-center flex-column flex-md-row" style="margin: 15px 0; padding: 10px; background: #f8f9fa; border: 1px solid #ddd;">
            <h3>Tổng điểm KPI <?= $vietnamese_months[$current_month] . ' ' . $current_year ?>: <strong><?= number_format($total_kpi) ?></strong></h3>
            <a href="<?= home_url("/aerp-hrm-employees/?action=view&id={$employee_id}&section=task&sub_action=add") ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm công việc
            </a>
        </div>
        <form id="aerp-task-filter-form" class="row g-2 mb-3 aerp-table-ajax-form" data-table-wrapper="#aerp-task-table-wrapper" data-ajax-action="aerp_hrm_filter_task">
            <input type="hidden" name="action" value="view">
            <input type="hidden" name="id" value="<?= esc_attr($employee_id) ?>">
            <input type="hidden" name="section" value="task">
            <div class="col-12 col-md-2 mb-2">
                <label for="filter-status" class="form-label mb-1">Trạng thái</label>
                <select id="filter-status" name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="assigned" <?= selected($_GET['status'] ?? '', 'assigned') ?>>Đã giao</option>
                    <option value="done" <?= selected($_GET['status'] ?? '', 'done') ?>>Hoàn thành</option>
                    <option value="failed" <?= selected($_GET['status'] ?? '', 'failed') ?>>Thất bại</option>
                </select>
            </div>
            <div class="col-12 col-md-2 mb-2">
                <label for="filter-month" class="form-label mb-1">Tháng</label>
                <select id="filter-month" name="month" class="form-select">
                    <?php foreach ($vietnamese_months as $num => $name): ?>
                        <option value="<?= $num ?>" <?= selected($current_month, $num) ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2 mb-2">
                <label for="filter-year" class="form-label mb-1">Năm</label>
                <select id="filter-year" name="year" class="form-select">
                    <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                        <option value="<?= $y ?>" <?= selected($current_year, $y) ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-12 col-md-1 d-flex align-items-end mb-2">
                <button type="submit" class="btn btn-primary w-100">Lọc</button>
            </div>
        </form>
        <?php // Display messages if any (using Transients API)
        $message = get_transient('aerp_task_message');
        if ($message) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    ' . esc_html($message) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            delete_transient('aerp_task_message'); // Xóa transient sau khi hiển thị
        }
        ?>
        <div id="aerp-task-table-wrapper" data-employee-id="<?= esc_attr($employee_id) ?>">
            <?php $table->render(); ?>
        </div>
        <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '#task') ?>" class="btn btn-secondary" style="width: fit-content;">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>

    </div>
</div>