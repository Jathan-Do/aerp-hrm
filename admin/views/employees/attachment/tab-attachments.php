<?php
if (!aerp_hrm_is_pro()) {
    aerp_render_pro_block('Hồ sơ đính kèm', 'AERP HRM Pro (Quản lý nhân sự)');
    return;
}

require_once AERP_HRM_PATH . 'includes/table/table-attachment.php';

$table = new AERP_Attachment_Table($employee_id);
$table->process_bulk_action();
$table->prepare_items();
?>
<p>
    <a href="<?= admin_url('admin.php?page=aerp_attachment_add&employee_id=' . $employee_id) ?>" class="button button-primary">Tạo hồ sơ mới</a>
</p>
<form method="post">
    <?php $table->search_box('Tìm hồ sơ', 'search_attachment'); ?>
    <?php $table->display(); ?>
</form>