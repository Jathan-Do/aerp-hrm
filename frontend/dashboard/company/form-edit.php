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
$editing = AERP_Frontend_Company_Manager::get_by_id($edit_id);

if (!$editing) {
    wp_die(__('Company not found.'));
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Cập nhật thông tin công ty</h2>
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
            <input type="hidden" name="company_id" value="<?php echo esc_attr($edit_id); ?>">
            <div class="mb-3">
                <label for="company_name" class="form-label">Tên công ty</label>
                <input type="text" class="form-control" id="company_name" name="company_name"
                    value="<?php echo esc_attr($editing->company_name); ?>" required>
            </div>
            <div class="mb-3">
                <label for="tax_code" class="form-label">Mã số thuế</label>
                <input type="text" class="form-control" id="tax_code" name="tax_code"
                    value="<?php echo esc_attr($editing->tax_code); ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Số điện thoại</label>
                <input type="text" class="form-control" id="phone" name="phone"
                    value="<?php echo esc_attr($editing->phone); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="<?php echo esc_attr($editing->email); ?>" required>
            </div>
            <div class="mb-3">
                <label for="website" class="form-label">Website</label>
                <input type="url" class="form-control" id="website" name="website"
                    value="<?php echo esc_attr($editing->website); ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Địa chỉ</label>
                <input type="text" class="form-control" id="address" name="address"
                    value="<?php echo esc_attr($editing->address); ?>" required>
            </div>
            <div class="mb-3">
                <label for="logo_url" class="form-label">Logo URL</label>
                <input type="url" class="form-control" id="logo_url" name="logo_url"
                    value="<?php echo esc_attr($editing->logo_url); ?>" required>
            </div>
            <div class="mb-3">
                <label for="work_saturday" class="form-label">Làm việc thứ 7</label>
                <select class="form-select" name="work_saturday" id="work_saturday">
                    <option value="off" <?= ($editing->work_saturday ?? 'off') === 'off' ? 'selected' : '' ?>>Nghỉ thứ 7</option>
                    <option value="full" <?= ($editing->work_saturday ?? 'off') === 'full' ? 'selected' : '' ?>>Làm cả ngày thứ 7</option>
                    <option value="half" <?= ($editing->work_saturday ?? 'off') === 'half' ? 'selected' : '' ?>>Làm nửa ngày thứ 7</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_company" class="btn btn-primary">Cập nhật</button>
                <a href="?page=aerp_company" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Cập nhật phòng ban';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
