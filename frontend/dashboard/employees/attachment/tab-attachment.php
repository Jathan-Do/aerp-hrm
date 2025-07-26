<?php
if (!defined('ABSPATH')) exit;
if (!aerp_hrm_is_pro()) {
    aerp_render_pro_block('Hồ sơ đính kèm', 'AERP HRM Pro (Quản lý nhân sự)');
    return;
}
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
    aerp_user_has_permission($user_id, 'attachment_view'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
if (!isset($employee_id)) {
    $employee_id = absint($_POST['id'] ?? $_GET['id'] ?? 0);
}
$filters = [
    'employee_id' => $employee_id,
];
$table = new AERP_Frontend_Attachment_Table([
    'employee_id' => $employee_id,
]);
$table->set_filters($filters);
$table->process_bulk_action();
?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-md-center flex-column flex-md-row mb-3">
            <h3>Hồ sơ đính kèm</h3>
            <a href="<?= home_url("/aerp-hrm-employees/?action=view&id={$employee_id}&section=attachment&sub_action=add") ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm hồ sơ
            </a>
        </div>
        <?php // Display messages if any (using Transients API)
        $message = get_transient('aerp_attachment_message');
        if ($message) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    ' . esc_html($message) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            delete_transient('aerp_attachment_message'); // Xóa transient sau khi hiển thị
        }
        ?>
        <div id="aerp-attachment-table-wrapper" data-employee-id="<?= esc_attr($employee_id) ?>">
            <?php $table->render(); ?>
        </div>
        <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=attachment') ?>" class="btn btn-secondary" style="width: fit-content;">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>