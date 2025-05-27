<?php
$rows = AERP_Employee_Manager::get_all();

$table = new AERP_Employee_Table([
    'plural'   => 'employees',
    'singular' => 'employee',
]);

$table->process_bulk_action(); // xử lý bulk delete
$table->set_data(array_map('get_object_vars', $rows));
$table->prepare_items();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Danh sách nhân viên</h1>
    <a href="<?= admin_url('admin.php?page=aerp_employees&add=1') ?>" class="page-title-action">Thêm mới</a>

    <!-- ✅ Form Lọc Riêng -->
    <form id="aerp-filter-form" method="get">
        <?php $table->render_filter_form(); ?>
    </form>
    <!-- ✅ Bảng chính -->
    <form method="get" id="aerp-table-form">
        <input type="hidden" name="page" value="aerp_employees">
        <?php $table->search_box('Tìm kiếm', 'search_employee'); ?>
        <?php $table->display(); ?>
    </form>
</div>
<h3>Xuất dữ liệu</h3>
<form method="post" action="<?= admin_url('admin-post.php') ?>">
    <?php wp_nonce_field('aerp_export_excel', 'aerp_export_nonce'); ?>
    <input type="hidden" name="action" value="aerp_export_excel_common">
    <input type="hidden" name="callback" value="employee_list_export">
    <button type="submit" name="aerp_export_excel" class="button">📥 Xuất danh sách nhân viên</button>
</form>