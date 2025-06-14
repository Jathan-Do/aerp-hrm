<?php
if (!defined('ABSPATH')) {
    exit;
}

// Check if user is logged in and has admin capabilities
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Get current user
$current_user = wp_get_current_user();

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Thêm phòng ban mới</h2>
    <div class="user-info">
        Welcome, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_department_action', 'aerp_save_department_nonce'); ?>
            <div class="mb-3">
                <label for="department_name" class="form-label">Tên phòng ban</label>
                <input type="text" class="form-control" id="department_name" name="department_name" required>
            </div>
            <div class="mb-3">
                <label for="department_desc" class="form-label">Mô tả</label>
                <textarea class="form-control" id="department_desc" name="department_desc" rows="3"></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_department" class="btn btn-primary">Thêm mới</button>
                <a href="?page=aerp_departments" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Thêm phòng ban mới';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');