<?php
$table = new AERP_Position_Table([
    'plural'   => 'positions',
    'singular' => 'position',
    'ajax'     => false
]);

$table->process_bulk_action();
$rows = AERP_Position_Manager::get_positions();

$table->set_data(array_map('get_object_vars', $rows));
$table->prepare_items();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Danh sách chức vụ</h1>
    <a href="<?= admin_url('admin.php?page=aerp_positions&add=1') ?>" class="page-title-action">Thêm mới</a>
    <hr class="wp-header-end">

    <form method="post">
        <?php $table->search_box('Tìm kiếm', 'search_position'); ?>
        <?php $table->display(); ?>
    </form>
</div>
