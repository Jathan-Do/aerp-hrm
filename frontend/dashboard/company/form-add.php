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
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Thêm công ty mới</h2>
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
            <?php wp_nonce_field('aerp_save_company_action', 'aerp_save_company_nonce'); ?>
            <div class="mb-3">
                <label for="company_name" class="form-label">Tên công ty</label>
                <input type="text" class="form-control" id="company_name" name="company_name" required>
            </div>
            <div class="mb-3">
                <label for="tax_code" class="form-label">Mã số thuế</label>
                <input type="text" class="form-control" id="tax_code" name="tax_code" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Số điện thoại</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                    required>
            </div>
            <div class="mb-3">
                <label for="website" class="form-label">Website</label>
                <input type="url" class="form-control" id="website" name="website"
                    required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Địa chỉ</label>
                <input type="text" class="form-control" id="address" name="address"
                    required>
            </div>
            <div class="mb-3">
                <label for="logo_url" class="form-label">Logo URL</label>
                <input type="url" class="form-control" id="logo_url" name="logo_url"
                    required>
            </div>
            <div class="mb-3">
                <label for="work_saturday" class="form-label">Làm việc thứ 7</label>
                <select class="form-select" name="work_saturday" id="work_saturday">
                    <option value="off">Nghỉ thứ 7</option>
                    <option value="full">Làm cả ngày thứ 7</option>
                    <option value="half">Làm nửa ngày thứ 7</option>
                </select>
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
