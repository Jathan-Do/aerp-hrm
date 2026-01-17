<?php
// File: frontend/dashboard/roles/list.php
if (!defined('ABSPATH')) exit;

// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$employee = aerp_get_employee_by_user_id($user_id);
$user_fullname = $employee ? $employee->full_name : '';

if (!is_user_logged_in()) {
    wp_die(__('You must be logged in to access this page.'));
}

// Danh sách điều kiện, chỉ cần 1 cái đúng là qua
$access_conditions = [
    aerp_user_has_role($user_id, 'admin'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
$table = new AERP_Frontend_Role_Table();
$table->process_bulk_action();

ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-5">
    <h2>Quản lý nhóm quyền</h2>
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
        ['label' => 'Quản lý nhóm quyền']
    ]);
}
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách nhóm quyền</h5>
        <!-- <a href="<?php echo esc_url(home_url('/aerp-role/?action=add')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm mới
        </a> -->
    </div>
    <div class="card-body">
        <?php
        $message = get_transient('aerp_role_message');
        if ($message) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'
                . esc_html($message) .
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
            delete_transient('aerp_role_message');
        }
        ?>
        <div id="aerp-role-table-wrapper">
            <?php $table->render(); ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Quản lý nhóm quyền';
include AERP_HRM_PATH . 'frontend/dashboard/layout.php';
