<?php
// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$employee = aerp_get_employee_by_user_id($user_id);
$user_fullname = $employee ? $employee->full_name : '';

// Check if user is logged in and has admin capabilities
if (!is_user_logged_in() || !aerp_user_has_role($user_id, 'admin')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
// Process bulk actions
$table = new AERP_Frontend_Work_Location_Table();
$table->process_bulk_action();

ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-5">
    <h2>Quản lý chi nhánh</h2>
    <div class="user-info text-end">
        Hi, <?php echo esc_html($user_fullname); ?>
        <a href="<?php echo wp_logout_url(site_url('/aerp-dang-nhap')); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>
<?php
if (function_exists('aerp_render_breadcrumb')) {
    aerp_render_breadcrumb([
        ['label' => 'Trang chủ', 'url' => home_url('/aerp-dashboard'), 'icon' => 'fas fa-home'],
        ['label' => 'Danh mục', 'url' => home_url('/aerp-categories')],
        ['label' => 'Quản lý chi nhánh']
    ]);
}
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách chi nhánh</h5>
        <a href="<?php echo esc_url(home_url('/aerp-work-location/?action=add')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm mới
        </a>
    </div>
    <div class="card-body">
        <?php // Display messages if any (using Transients API)
        $message = get_transient('aerp_work_location_message');
        if ($message) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    ' . esc_html($message) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            delete_transient('aerp_work_location_message'); // Xóa transient sau khi hiển thị
        } ?>
        <div id="aerp-work-location-table-wrapper">
            <?php $table->render(); ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Quản lý chi nhánh';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
