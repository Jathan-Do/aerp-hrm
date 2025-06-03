<?php
$table = new AERP_Work_Location_Table([
    'plural'   => 'work_locations',
    'singular' => 'work_location',
    'ajax'     => false
]);

$table->process_bulk_action(); // xử lý xoá trước
AERP_Work_Location_Manager::handle_delete();
$table->set_data(array_map('get_object_vars', AERP_Work_Location_Manager::get_work_locations()));
$table->prepare_items();

?>
<div class="wrap">
    <h1 class="wp-heading-inline">Danh sách chi nhánh</h1>
    <a href="<?= admin_url('admin.php?page=aerp_work_locations&add=1') ?>" class="page-title-action">Thêm mới</a>
    <form method="post"><?php $table->search_box('Tìm kiếm', 'search_work_location'); ?>
        <?php $table->display(); ?>
    </form>
</div> 