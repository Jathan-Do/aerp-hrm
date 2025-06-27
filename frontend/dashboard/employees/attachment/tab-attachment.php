<?php
if (!defined('ABSPATH')) exit;
if (!aerp_hrm_is_pro()) {
    aerp_render_pro_block('Hồ sơ đính kèm', 'AERP HRM Pro (Quản lý nhân sự)');
    return;
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