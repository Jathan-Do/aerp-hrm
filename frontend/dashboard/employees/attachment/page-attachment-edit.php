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
    aerp_user_has_permission($user_id, 'attachment_edit'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
$attachment_id = absint($_GET['attachment_id'] ?? 0);
$record = AERP_Frontend_Attachment_Manager::get_by_id($attachment_id);
if (!$record) {
    echo '<div class="alert alert-danger">Không tìm thấy hồ sơ.</div>';
    return;
}
$employee = AERP_Frontend_Employee_Manager::get_by_id($record->employee_id);
if (!$employee) {
    echo '<div class="alert alert-danger">Nhân viên không tồn tại.</div>';
    return;
}
$employee_id = $record->employee_id;
ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Cập nhật hồ sơ đính kèm cho: <?= esc_html($employee->full_name) ?></h2>
    <div class="user-info text-end">
        Xin chào, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>
<?php
if (function_exists('aerp_render_breadcrumb')) {
    aerp_render_breadcrumb([
        ['label' => 'Trang chủ', 'url' => home_url('/aerp-dashboard'), 'icon' => 'fas fa-home'],
        ['label' => 'Quản lý nhân viên', 'url' => home_url('/aerp-hrm-employees')],
        ['label' => 'Chi tiết nhân viên', 'url' => home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=attachment')],
        ['label' => 'Cập nhật hồ sơ']
    ]);
}
?>
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
                <?php wp_nonce_field('aerp_edit_attachment_' . $record->id, 'aerp_edit_attachment_nonce'); ?>
                <input type="hidden" name="id" value="<?= esc_attr($record->id) ?>">
                <input type="hidden" name="employee_id" value="<?= esc_attr($record->employee_id) ?>">
                <input type="hidden" name="edit_mode" value="upload">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Loại hồ sơ</label>
                        <select name="attachment_type" required class="form-select shadow-sm">
                            <option value="contract" <?= selected($record->attachment_type, 'contract') ?>>Hợp đồng</option>
                            <option value="cccd" <?= selected($record->attachment_type, 'cccd') ?>>CCCD</option>
                            <option value="degree" <?= selected($record->attachment_type, 'degree') ?>>Bằng cấp</option>
                            <option value="other" <?= selected($record->attachment_type, 'other') ?>>Khác</option>
                        </select>
                    </div>
                    <!-- <div class="col-md-6 mb-3">
                        <label class="form-label">Nơi lưu trữ: </label> -->
                    <input type="hidden" name="storage_type" value="local">
                    <!-- <span>Lưu trên máy chủ</span>
                    </div> -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Chọn file mới</label>
                        <input class="form-control shadow-sm" type="file" name="attachment_file">
                        <p class="description">
                            File hiện tại:
                            <a href="<?= esc_url($record->file_url) ?>" target="_blank"><?= esc_html($record->file_name) ?></a>
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="aerp_edit_attachment_submit" class="btn btn-primary">Cập nhật hồ sơ</button>
                    <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=attachment') ?>" class="btn btn-secondary">Quay lại</a>
                </div>
            </form>
        </div>

        <!-- Nhập thủ công -->
        <div id="tab-manual" class="attachment-tab" style="display:none;">
            <form method="post">
                <?php wp_nonce_field('aerp_edit_attachment_' . $record->id, 'aerp_edit_attachment_nonce'); ?>
                <input type="hidden" name="id" value="<?= esc_attr($record->id) ?>">
                <input type="hidden" name="employee_id" value="<?= esc_attr($record->employee_id) ?>">
                <input type="hidden" name="edit_mode" value="manual">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Loại hồ sơ</label>
                        <select name="attachment_type" required class="form-select shadow-sm">
                            <option value="contract" <?= selected($record->attachment_type, 'contract') ?>>Hợp đồng</option>
                            <option value="cccd" <?= selected($record->attachment_type, 'cccd') ?>>CCCD</option>
                            <option value="degree" <?= selected($record->attachment_type, 'degree') ?>>Bằng cấp</option>
                            <option value="other" <?= selected($record->attachment_type, 'other') ?>>Khác</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tên file</label>
                        <input id="file_name" type="text" name="file_name" class="form-control shadow-sm" value="<?= esc_attr($record->file_name) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">URL file</label>
                        <input id="file_url" type="url" name="file_url" class="form-control shadow-sm" value="<?= esc_url($record->file_url) ?>" required>
                        <button type="button" class="mt-2 btn btn-outline-primary" id="select_file">Chọn từ thư viện</button>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Định dạng</label>
                        <input id="file_type" type="text" name="file_type" class="form-control shadow-sm" value="<?= esc_attr($record->file_type) ?>">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="aerp_edit_attachment_submit" class="btn btn-primary">Cập nhật hồ sơ</button>
                    <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=attachment') ?>" class="btn btn-secondary">Quay lại</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Cập nhật hồ sơ đính kèm';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
