<?php
if (!defined('ABSPATH')) exit;

require_once AERP_HRM_PATH . 'includes/table/table-discipline-log.php';

$month = sanitize_text_field($_GET['violation_month'] ?? date('Y-m'));
$table = new AERP_Discipline_Log_Table($employee_id);
$table->process_bulk_action();
$table->prepare_items();
?>

<div class="">
    <p>
        <a href="<?= admin_url('admin.php?page=aerp_discipline_add&employee_id=' . $employee_id) ?>" class="button button-primary">
            + Ghi nhận vi phạm
        </a>
    </p>
    <form method="get" style="margin-bottom: 15px;">
        <input type="hidden" name="page" value="aerp_employees">
        <input type="hidden" name="view" value="<?= esc_attr($employee_id) ?>">
        <input type="hidden" name="tab" value="disciplines">
        <label>Tháng:
            <input type="month" name="violation_month" value="<?= esc_attr($month) ?>">
        </label>
        <input type="submit" class="button" value="Lọc">
    </form>

    <form method="post">
        <?php $table->search_box('Tìm vi phạm', 'search_violation'); ?>
        <?php $table->display(); ?>
    </form>
</div>