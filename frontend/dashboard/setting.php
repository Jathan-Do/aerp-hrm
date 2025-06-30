<?php
// Get current user
$current_user = wp_get_current_user();
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aerp_save_settings'])) {
    // Verify nonce for security
    if (!isset($_POST['aerp_settings_nonce']) || !wp_verify_nonce($_POST['aerp_settings_nonce'], 'aerp_save_settings_action')) {
        wp_die('Security check failed');
    }

    // Save HRM setting
    update_option('aerp_hrm_delete_data_on_uninstall', isset($_POST['aerp_hrm_delete_data_on_uninstall']) ? 1 : 0);

    // Save CRM setting
    update_option('aerp_crm_delete_data_on_uninstall', isset($_POST['aerp_crm_delete_data_on_uninstall']) ? 1 : 0);

    // Save Order setting
    update_option('aerp_order_delete_data_on_uninstall', isset($_POST['aerp_order_delete_data_on_uninstall']) ? 1 : 0);
}

// Get current settings
$hrm_checked = get_option('aerp_hrm_delete_data_on_uninstall', 0) ? 'checked' : '';
$crm_checked = get_option('aerp_crm_delete_data_on_uninstall', 0) ? 'checked' : '';
$order_checked = get_option('aerp_order_delete_data_on_uninstall', 0) ? 'checked' : '';
$hrm_active = function_exists('aerp_hrm_init') || is_plugin_active('aerp-hrm/aerp-hrm.php');
$crm_active = function_exists('aerp_crm_init') || is_plugin_active('aerp-crm/aerp-crm.php');
$order_active = function_exists('aerp_order_init') || is_plugin_active('aerp-order/aerp-order.php');

ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Cài đặt AERP</h2>
    <div class="user-info text-end">
        Welcome, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(site_url('/aerp-dang-nhap')); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
<div class="card mt-4 shadow aerp-settings">
    <div class="card-header bg-light p-3">
        <h4 class="card-title mb-0">Tùy chọn xóa dữ liệu</h4>
    </div>

    <div class="card-body">
        <form method="post" class="aerp-settings-form">
            <?php wp_nonce_field('aerp_save_settings_action', 'aerp_settings_nonce'); ?>

            <div class="alert alert-warning mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Cảnh báo:</strong> Các tùy chọn này sẽ xóa vĩnh viễn dữ liệu khi bạn gỡ plugin. Hãy chắc chắn trước khi lưu cài đặt.
            </div>
            <?php
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Đã lưu cài đặt thành công!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            ?>
            <?php if ($hrm_active): ?>
                <div class="form-group mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                            name="aerp_hrm_delete_data_on_uninstall"
                            id="aerp_hrm_delete_data_on_uninstall"
                            value="1" <?php echo $hrm_checked; ?>>
                        <label class="form-check-label" for="aerp_hrm_delete_data_on_uninstall">
                            <strong>Xóa toàn bộ dữ liệu Plugin HRM</strong>
                            <p class="text-muted small mb-0">Tất cả dữ liệu nhân sự, phòng ban, chức vụ sẽ bị xóa khi gỡ plugin</p>
                        </label>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($crm_active): ?>
                <div class="form-group mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                            name="aerp_crm_delete_data_on_uninstall"
                            id="aerp_crm_delete_data_on_uninstall"
                            value="1" <?php echo $crm_checked; ?>>
                        <label class="form-check-label" for="aerp_crm_delete_data_on_uninstall">
                            <strong>Xóa toàn bộ dữ liệu Plugin CRM</strong>
                            <p class="text-muted small mb-0">Tất cả dữ liệu khách hàng, lịch sử tương tác sẽ bị xóa khi gỡ plugin</p>
                        </label>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($order_active): ?>
                <div class="form-group mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                            name="aerp_order_delete_data_on_uninstall"
                            id="aerp_order_delete_data_on_uninstall"
                            value="1" <?php echo $order_checked; ?>>
                        <label class="form-check-label" for="aerp_order_delete_data_on_uninstall">
                            <strong>Xóa toàn bộ dữ liệu Plugin Order</strong>
                            <p class="text-muted small mb-0">Tất cả dữ liệu đơn hàng, lịch sử đơn hàng sẽ bị xóa khi gỡ plugin</p>
                        </label>
                    </div>
                </div>
            <?php endif; ?>
            <div class="form-footer pt-3 border-top">
                <button type="submit" name="aerp_save_settings" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i>Lưu cài đặt
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .aerp-settings .form-check-input {
        width: 3em;
        height: 1.5em;
        margin-right: 10px;
    }

    .aerp-settings .form-check-label {
        display: flex;
        flex-direction: column;
    }
</style>

<?php
$content = ob_get_clean();
$title = 'Cài đặt xóa dữ liệu';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
