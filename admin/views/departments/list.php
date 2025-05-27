<?php
$table = new AERP_Department_Table([
    'plural'   => 'departments',
    'singular' => 'department',
    'ajax'     => false
]);

$table->process_bulk_action(); // xử lý xoá trước

$all = AERP_Department_Manager::get_departments();
$table->set_data(array_map('get_object_vars', $all)); // dùng lại dữ liệu mới
$table->prepare_items();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Danh sách phòng ban</h1>
    <a href="<?= admin_url('admin.php?page=aerp_departments&add=1') ?>" class="page-title-action">Thêm mới</a>
    <hr class="wp-header-end">

    <form method="post">
        <?php $table->search_box('Tìm kiếm', 'search_department'); ?>
        <?php $table->display(); ?>
    </form>
</div>