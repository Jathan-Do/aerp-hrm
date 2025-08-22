<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
// Check if user is logged in and has admin capabilities
if (!is_user_logged_in() || !aerp_user_has_role($user_id, 'admin')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}


ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Thêm chi nhánh mới</h2>
    <div class="user-info">
        Welcome, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(site_url('/aerp-dang-nhap')); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
<?php
if (function_exists('aerp_render_breadcrumb')) {
    aerp_render_breadcrumb([
        ['label' => 'Trang chủ', 'url' => home_url('/aerp-dashboard'), 'icon' => 'fas fa-home'],
        ['label' => 'Danh mục', 'url' => home_url('/aerp-categories')],
        ['label' => 'Quản lý chi nhánh', 'url' => home_url('/aerp-work-location')],
        ['label' => 'Thêm chi nhánh mới']
    ]);
}
?>
<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_work_location_action', 'aerp_save_work_location_nonce'); ?>
            <div class="mb-3">
                <label for="work_location_name" class="form-label">Tên chi nhánh</label>
                <input type="text" class="form-control" id="work_location_name" name="work_location_name" required>
            </div>
            <div class="mb-3">
                <label for="work_location_desc" class="form-label">Mô tả</label>
                <textarea class="form-control" id="work_location_desc" name="work_location_desc" rows="3"></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_work_location" class="btn btn-primary">Thêm mới</button>
                <a href="?page=aerp_work_location" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Thêm chi nhánh mới';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');