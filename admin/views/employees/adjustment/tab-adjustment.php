<?php
require_once AERP_HRM_PATH . 'includes/table/table-adjustment.php';
$table = new AERP_Adjustment_Table($employee_id);
$table->process_bulk_action();
$table->prepare_items();
?>

<p>
    <a href="<?= admin_url('admin.php?page=aerp_adjustment_add&employee_id=' . $employee_id) ?>" class="button button-primary">
        + Điều chỉnh
    </a>
</p>

<form method="get">
    <input type="hidden" name="page" value="aerp_employees">
    <input type="hidden" name="view" value="<?= esc_attr($employee_id) ?>">
    <?php $table->search_box('Tìm điều chỉnh', 'search_adjustment'); ?>
</form>

<form method="post">
    <?php $table->display(); ?>
</form>
