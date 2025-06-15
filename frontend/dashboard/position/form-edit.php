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


$edit_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
$editing = AERP_Frontend_Position_Manager::get_by_id($edit_id);

if (!$editing) {
    wp_die(__('Position not found.'));
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Cập nhật chức vụ</h2>
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
            <input type="hidden" name="position_id" value="<?php echo esc_attr($edit_id); ?>">
            <div class="mb-3">
                <label for="position_name" class="form-label">Tên chức vụ</label>
                <input type="text" class="form-control" id="position_name" name="position_name" 
                       value="<?php echo esc_attr($editing->name); ?>" required>
            </div>
            <div class="mb-3">
                <label for="position_desc" class="form-label">Mô tả</label>
                <textarea class="form-control" id="position_desc" name="position_desc" 
                          rows="3"><?php echo esc_textarea($editing->description); ?></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_position" class="btn btn-primary">Cập nhật</button>
                <a href="?page=aerp_position" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Cập nhật phòng ban';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');