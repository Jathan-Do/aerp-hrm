<?php
if (!defined('ABSPATH')) exit;
if (!isset($employee_id)) {
    $employee_id = absint($_POST['id'] ?? $_GET['id'] ?? 0);
}
$month = isset($_GET['violation_month']) ? sanitize_text_field($_GET['violation_month']) : '';
$filters = [
    'employee_id' => $employee_id,
];
if ($month) {
    $filters['violation_month'] = $month;
}
$table = new AERP_Frontend_Discipline_Log_Table([
    'employee_id' => $employee_id,
]);
$table->set_filters($filters);

$table->process_bulk_action();
?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-md-center flex-column flex-md-row">
            <h3>Ghi nhận vi phạm</h3>
            <a href="<?= home_url("/aerp-hrm-employees/?action=view&id={$employee_id}&section=discipline&sub_action=add") ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ghi nhận vi phạm
            </a>
        </div>
        <form id="aerp-discipline-log-filter-form" class="row g-2 mb-3 aerp-table-ajax-form" data-table-wrapper="#aerp-discipline-log-table-wrapper" data-ajax-action="aerp_hrm_filter_discipline_log">
            <div class="col-12 col-md-2 mb-2">
                <label for="filter-month" class="form-label mb-1">Tháng</label>
                <input class="form-control" id="filter-month" type="month" name="violation_month" value="<?= esc_attr($month) ?>">
            </div>
            <div class="col-12 col-md-1 d-flex align-items-end mb-2">
                <button type="submit" class="btn btn-primary w-100">Lọc</button>
            </div>
        </form>
        <?php // Display messages if any (using Transients API)
        $message = get_transient('aerp_discipline_message');
        if ($message) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    ' . esc_html($message) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            delete_transient('aerp_discipline_message'); // Xóa transient sau khi hiển thị
        }
        ?>
        <div id="aerp-discipline-log-table-wrapper" data-employee-id="<?= esc_attr($employee_id) ?>">
            <?php $table->render(); ?>
        </div>
        <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '#discipline') ?>" class="btn btn-secondary" style="width: fit-content;">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>