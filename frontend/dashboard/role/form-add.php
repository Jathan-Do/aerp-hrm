<?php
if (!defined('ABSPATH')) exit;

// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

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
$all_permissions = function_exists('AERP_Frontend_Permission_Manager::get_permissions') ? AERP_Frontend_Permission_Manager::get_permissions() : (class_exists('AERP_Frontend_Permission_Manager') ? AERP_Frontend_Permission_Manager::get_permissions() : []);

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Thêm nhóm quyền mới</h2>
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
            <?php wp_nonce_field('aerp_save_role_action', 'aerp_save_role_nonce'); ?>
            <div class="mb-3">
                <label for="role_name" class="form-label">Tên nhóm quyền</label>
                <input type="text" class="form-control shadow-sm" id="role_name" name="role_name" required>
            </div>
            <div class="mb-3">
                <label for="role_desc" class="form-label">Mô tả</label>
                <textarea class="form-control shadow-sm" id="role_desc" name="role_desc" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Quyền</label>
                <div class="form-check">
                    <?php foreach ($all_permissions as $perm): ?>
                        <div class="mb-2">
                            <input class="form-check-input" type="checkbox" name="role_permissions[]" value="<?php echo esc_attr($perm->id); ?>" id="perm_<?php echo $perm->id; ?>">
                            <label class="form-check-label" for="perm_<?php echo $perm->id; ?>">
                                <?php echo esc_html($perm->name); ?><?php if ($perm->description) echo ' - ' . esc_html($perm->description); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_role" class="btn btn-primary">Thêm mới</button>
                <a href="<?php echo esc_url(home_url('/aerp-role')); ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Thêm nhóm quyền mới';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
