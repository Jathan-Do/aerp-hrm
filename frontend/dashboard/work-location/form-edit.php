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

$edit_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
$editing = AERP_Frontend_Work_Location_Manager::get_by_id($edit_id);

if (!$editing) {
    wp_die(__('Work location not found.'));
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Cập nhật chi nhánh</h2>
    <div class="user-info">
        Welcome, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(site_url('/aerp-dang-nhap')); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_work_location_action', 'aerp_save_work_location_nonce'); ?>
            <input type="hidden" name="work_location_id" value="<?php echo esc_attr($edit_id); ?>">
            <div class="mb-3">
                <label for="work_location_name" class="form-label">Tên chi nhánh</label>
                <input type="text" class="form-control" id="work_location_name" name="work_location_name" 
                       value="<?php echo esc_attr($editing->name); ?>" required>
            </div>
            <div class="mb-3">
                <label for="work_location_desc" class="form-label">Mô tả</label>
                <textarea class="form-control" id="work_location_desc" name="work_location_desc" 
                          rows="3"><?php echo esc_textarea($editing->description); ?></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_work_location" class="btn btn-primary">Cập nhật</button>
                <a href="?page=aerp_work_location" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Cập nhật chi nhánh';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');