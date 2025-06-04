<?php
require_once AERP_HRM_PATH . 'includes/table/table-permission.php';
AERP_Permission_Manager::handle_delete();
$table = new AERP_Permission_Table([
    'plural'   => 'permissions',
    'singular' => 'permission',
    'ajax'     => false
]);
$table->process_bulk_action();
$table->set_data(array_map('get_object_vars', AERP_Permission_Manager::get_permissions()));
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Danh sách quyền</h1>
    <a href="<?= admin_url('admin.php?page=aerp_permissions&add=1') ?>" class="page-title-action">Thêm mới</a>
    <form method="post"><?php $table->search_box('Tìm kiếm', 'search_permission'); ?>
        <?php $table->prepare_items(); $table->display(); ?>
    </form>
</div> 