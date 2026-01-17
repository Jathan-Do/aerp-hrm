<?php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$employee = aerp_get_employee_by_user_id($user_id);
$user_fullname = $employee ? $employee->full_name : '';

if (!is_user_logged_in() || !aerp_user_has_role($user_id, 'admin')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

$edit_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
$editing = AERP_Frontend_Permission_Manager::get_by_id($edit_id);
if (!$editing) {
    wp_die(__('Permission not found.'));
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-5">
    <h2>Cập nhật quyền truy cập</h2>
    <div class="user-info">
        Hi, <?php echo esc_html($user_fullname); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_permission_action', 'aerp_save_permission_nonce'); ?>
            <input type="hidden" name="permission_id" value="<?php echo esc_attr($editing->id); ?>">
            <div class="mb-3">
                <label for="permission_name" class="form-label">Tên quyền</label>
                <input type="text" class="form-control shadow-sm" id="permission_name" name="permission_name" value="<?php echo esc_attr($editing->name); ?>" required>
            </div>
            <div class="mb-3">
                <label for="permission_desc" class="form-label">Mô tả</label>
                <textarea class="form-control shadow-sm" id="permission_desc" name="permission_desc" rows="3"><?php echo esc_textarea($editing->description); ?></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_permission" class="btn btn-primary">Cập nhật quyền</button>
                <a href="<?php echo esc_url(home_url('/aerp-permission')) ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Cập nhật quyền';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
