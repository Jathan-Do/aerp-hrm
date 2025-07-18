<?php
if (!defined('ABSPATH')) {
    exit;
}
// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

if (!is_user_logged_in()) {
    wp_die(__('You must be logged in to access this page.'));
}

// Danh sách điều kiện, chỉ cần 1 cái đúng là qua
$access_conditions = [
    aerp_user_has_role($user_id, 'admin'),
    aerp_user_has_role($user_id, 'department_lead'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}


ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Thêm chức vụ mới</h2>
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
            <?php wp_nonce_field('aerp_save_position_action', 'aerp_save_position_nonce'); ?>
            <div class="mb-3">
                <label for="position_name" class="form-label">Tên chức vụ</label>
                <input type="text" class="form-control" id="position_name" name="position_name" required>
            </div>
            <div class="mb-3">
                <label for="position_desc" class="form-label">Mô tả</label>
                <textarea class="form-control" id="position_desc" name="position_desc" rows="3"></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_position" class="btn btn-primary">Thêm mới</button>
                <a href="?page=aerp_position" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Thêm chức vụ mới';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');