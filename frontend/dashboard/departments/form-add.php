<?php
if (!defined('ABSPATH')) {
    exit;
}
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

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-5">
    <h2>Thêm phòng ban mới</h2>
    <div class="user-info">
        Hi, <?php echo esc_html($user_fullname); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>
<?php
if (function_exists('aerp_render_breadcrumb')) {
    aerp_render_breadcrumb([
        ['label' => 'Trang chủ', 'url' => home_url('/aerp-dashboard'), 'icon' => 'fas fa-home'],
        ['label' => 'Danh mục', 'url' => home_url('/aerp-categories')],
        ['label' => 'Quản lý phòng ban', 'url' => home_url('/aerp-departments')],
        ['label' => 'Thêm phòng ban mới']
    ]);
}
?>
<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_department_action', 'aerp_save_department_nonce'); ?>
            <div class="mb-3">
                <label for="department_name" class="form-label">Tên phòng ban</label>
                <input type="text" class="form-control shadow-sm" id="department_name" name="department_name" required>
            </div>
            <div class="mb-3">
                <label for="department_desc" class="form-label">Mô tả</label>
                <textarea class="form-control shadow-sm" id="department_desc" name="department_desc" rows="3"></textarea>
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