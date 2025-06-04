<?php
require_once AERP_HRM_PATH . 'includes/table/table-role.php';
AERP_Role_Manager::handle_delete();
$table = new AERP_Role_Table([
    'plural'   => 'roles',
    'singular' => 'role',
    'ajax'     => false
]);
$table->process_bulk_action();
$table->set_data(array_map('get_object_vars', AERP_Role_Manager::get_roles()));
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Danh sách nhóm quyền</h1>
    <a href="<?= admin_url('admin.php?page=aerp_roles&add=1') ?>" class="page-title-action">Thêm mới</a>
    <form method="post"><?php $table->search_box('Tìm kiếm', 'search_role'); ?>
        <?php $table->prepare_items(); $table->display(); ?>
    </form>
</div> 