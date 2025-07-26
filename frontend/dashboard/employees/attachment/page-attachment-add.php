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
    aerp_user_has_role($user_id, 'department_lead'),
    aerp_user_has_permission($user_id, 'attachment_add'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
$employee_id = absint($_GET['id'] ?? 0);
$employee = AERP_Frontend_Employee_Manager::get_by_id($employee_id);
if (!$employee) {
    echo '<div class="alert alert-danger">Nhân viên không tồn tại.</div>';
    return;
}
ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Thêm hồ sơ đính kèm cho: <?= esc_html($employee->full_name) ?></h2>
    <div class="user-info text-end">
        Xin chào, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <!-- Tab Switch -->
        <div class="tab-switcher mb-2">
            <a href="#" class="btn btn-outline-primary tab-upload active" data-target="upload">📁 Tải từ máy</a>
            <a href="#" class="btn btn-outline-primary tab-manual" data-target="manual">📝 Nhập thủ công</a>
        </div>

        <!-- Upload từ máy -->
        <div id="tab-upload" class="attachment-tab" style="display:block;">
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('aerp_upload_attachment_' . $employee_id, 'aerp_attachment_nonce'); ?>
                <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Loại hồ sơ</label>
                        <select name="attachment_type" required class="form-select">
                            <option value="">-- Chọn loại --</option>
                            <option value="contract">Hợp đồng</option>
                            <option value="cccd">CCCD</option>
                            <option value="degree">Bằng cấp</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Chọn file</label>
                        <input class="form-control" type="file" name="attachment_file" required>
                    </div>
                </div>
                <!-- <div class="col-md-6 mb-3">
                    <label class="form-label">Nơi lưu trữ: </label> -->
                <input type="hidden" name="storage_type" value="local">
                <!-- <span>Lưu trên máy chủ</span>
                </div> -->
                <div class="d-flex gap-2">
                    <button type="submit" name="aerp_upload_attachment" class="btn btn-primary">Tải lên</button>
                    <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=attachment') ?>" class="btn btn-secondary">Quay lại</a>
                </div>
            </form>
        </div>

        <!-- Nhập thủ công -->
        <div id="tab-manual" class="attachment-tab" style="display:none;">
            <form method="post">
                <?php wp_nonce_field('aerp_manual_attachment_' . $employee_id, 'aerp_manual_attachment_nonce'); ?>
                <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Loại hồ sơ</label>
                        <select name="attachment_type" required class="form-select">
                            <option value="">-- Chọn loại --</option>
                            <option value="contract">Hợp đồng</option>
                            <option value="cccd">CCCD</option>
                            <option value="degree">Bằng cấp</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tên file</label>
                        <input id="file_name" type="text" name="file_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">URL file</label>
                        <input id="file_url" type="url" name="file_url" class="form-control" required><button type="button" class="mt-2 btn btn-outline-primary" id="select_file">Chọn từ thư viện</button>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Định dạng</label>
                        <input id="file_type" type="text" name="file_type" class="form-control" required>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="aerp_manual_attachment_submit" class="btn btn-primary">Lưu hồ sơ</button>
                    <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=attachment') ?>" class="btn btn-secondary">Quay lại</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Thêm hồ sơ đính kèm';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
