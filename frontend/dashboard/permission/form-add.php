<?php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

if (!is_user_logged_in() || !aerp_user_has_role($user_id, 'admin')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Thêm quyền mới</h2>
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
            <?php wp_nonce_field('aerp_save_permission_action', 'aerp_save_permission_nonce'); ?>
            <div class="mb-3">
                <label for="permission_name" class="form-label">Tên quyền</label>
                <input type="text" class="form-control shadow-sm" id="permission_name" name="permission_name" required>
            </div>
            <div class="mb-3">
                <label for="permission_desc" class="form-label">Mô tả</label>
                <textarea class="form-control shadow-sm" id="permission_desc" name="permission_desc" rows="3"></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_permission" class="btn btn-primary">Thêm mới</button>
                <a href="<?php echo esc_url(home_url('/aerp-permission')) ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Thêm quyền mới';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
