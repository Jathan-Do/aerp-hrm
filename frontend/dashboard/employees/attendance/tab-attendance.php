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
    aerp_user_has_permission($user_id, 'attendance_view'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
if (!isset($employee_id)) {
    $employee_id = absint($_POST['id'] ?? $_GET['id'] ?? 0);
}
$work_date = isset($_GET['work_date']) ? sanitize_text_field($_GET['work_date']) : '';
$filters = [
    'employee_id' => $employee_id,
];
if ($work_date) {
    $filters['work_date'] = $work_date;
}
$table = new AERP_Frontend_Attendance_Table([
    'employee_id' => $employee_id,
]);
$table->set_filters($filters);
$table->process_bulk_action();
?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-md-center flex-column flex-md-row">
            <h3>Ghi nhận chấm công</h3>
            <a href="<?= home_url("/aerp-hrm-employees/?action=view&id={$employee_id}&section=attendance&sub_action=add") ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ghi nhận chấm công
            </a>
        </div>
        <form id="aerp-attendance-filter-form" class="row g-2 mb-3 aerp-table-ajax-form" data-table-wrapper="#aerp-attendance-table-wrapper" data-ajax-action="aerp_hrm_filter_attendance">
            <div class="col-12 col-md-2 mb-2">
                <label for="filter-work-date" class="form-label mb-1">Tháng</label>
                <input class="form-control" id="filter-work-date" type="month" name="work_date" value="<?= esc_attr($work_date) ?>">
            </div>
            <div class="col-12 col-md-2 mb-2">
                <label for="filter-shift-type" class="form-label mb-1">Loại chấm công</label>
                <select class="form-select" id="filter-shift-type" name="shift">
                    <option value="">Tất cả</option>
                    <option value="off">Nghỉ (OFF)</option>
                    <option value="ot">Tăng ca (OT)</option>
                </select>
            </div>
            <div class="col-12 col-md-1 d-flex align-items-end mb-2">
                <button type="submit" class="btn btn-primary w-100">Lọc</button>
            </div>
        </form>
        <?php // Display messages if any (using Transients API)
        $message = get_transient('aerp_attendance_message');
        if ($message) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    ' . esc_html($message) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            delete_transient('aerp_attendance_message'); // Xóa transient sau khi hiển thị
        }
        ?>
        <div id="aerp-attendance-table-wrapper" data-employee-id="<?= esc_attr($employee_id) ?>">
            <?php $table->render(); ?>
        </div>
        <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=attendance') ?>" class="btn btn-secondary" style="width: fit-content;">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>
