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

// Lấy role đang sửa
$edit_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
$editing = AERP_Frontend_Role_Manager::get_by_id($edit_id);
if (!$editing) {
    wp_die(__('Role not found.'));
}

// Lấy danh sách quyền đã phân
$role_permissions = AERP_Frontend_Role_Manager::get_permissions_of_role($edit_id);

// Nhóm quyền hệ thống (readonly)
$system_slugs = ['admin', 'department_lead', 'accountant', 'employee'];
$is_system_role = in_array($editing->slug, $system_slugs);

// Lấy danh sách tất cả quyền
if (class_exists('AERP_Frontend_Permission_Manager') && method_exists('AERP_Frontend_Permission_Manager', 'get_permissions')) {
    $all_permissions = AERP_Frontend_Permission_Manager::get_permissions();
} else {
    $all_permissions = [];
}

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Cập nhật nhóm quyền</h2>
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
            <input type="hidden" name="role_id" value="<?php echo esc_attr($editing->id); ?>">

            <div class="mb-3">
                <label for="role_name" class="form-label">Tên nhóm quyền</label>
                <input type="text" class="form-control" id="role_name" name="role_name"
                       value="<?php echo esc_attr($editing->name); ?>"
                       required <?php echo $is_system_role ? 'readonly' : ''; ?>>
            </div>

            <div class="mb-3">
                <label for="role_desc" class="form-label">Mô tả</label>
                <textarea class="form-control" id="role_desc" name="role_desc" rows="3"
                          <?php echo $is_system_role ? 'readonly' : ''; ?>><?php echo esc_textarea($editing->description); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Phân quyền</label>
                <?php if (!empty($all_permissions)): ?>
                    <?php foreach ($all_permissions as $perm): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox"
                                   name="role_permissions[]"
                                   id="perm_<?php echo esc_attr($perm->id); ?>"
                                   value="<?php echo esc_attr($perm->id); ?>"
                                   <?php echo in_array($perm->id, $role_permissions) ? 'checked' : ''; ?>
                                   <?php echo $is_system_role ? 'disabled' : ''; ?>>
                            <label class="form-check-label" for="perm_<?php echo esc_attr($perm->id); ?>">
                                <?php echo esc_html($perm->name); ?>
                                <?php if (!empty($perm->description)) echo ' - ' . esc_html($perm->description); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-danger">Không có quyền nào được cấu hình.</p>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_role" class="btn btn-primary" <?php echo $is_system_role ? 'disabled' : ''; ?>>
                    Cập nhật nhóm quyền
                </button>
                <a href="<?php echo esc_url(home_url('/aerp-role')); ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Cập nhật nhóm quyền';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
