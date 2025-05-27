<?php
if (!defined('ABSPATH')) exit;
$task_id = absint($_GET['id'] ?? 0);
$record = AERP_Attachment_Manager::get_by_id($task_id);

if (!$record) {
    echo '<div class="notice notice-error"><p>Không tìm thấy hồ sơ.</p></div>';
    return;
}
$employee = AERP_Employee_Manager::get_by_id($record->employee_id);
if (!$employee) {
    echo '<div class="notice notice-error"><p>Nhân viên không tồn tại.</p></div>';
    return;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Sửa hồ sơ đính kèm</h1>
    <a href="<?= admin_url('admin.php?page=aerp_employees&view=' . $record->employee_id . '#attachments') ?>" class="page-title-action">← Quay lại nhân viên</a>
    <hr class="wp-header-end">

    <!-- Tab Switch -->
    <div class="tab-switcher" style="margin-top: 20px;">
        <a href="#" class="button button-secondary tab-upload active" data-target="upload">📁 Upload file mới</a>
        <a href="#" class="button button-secondary tab-manual" data-target="manual">✏️ Sửa thủ công</a>
    </div>

    <!-- Upload từ máy -->
    <div id="tab-upload" class="attachment-tab" style="display:block;">
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('aerp_edit_attachment_' . $record->id, 'aerp_edit_attachment_nonce'); ?>
            <input type="hidden" name="id" value="<?= esc_attr($record->id) ?>">
            <input type="hidden" name="employee_id" value="<?= esc_attr($record->employee_id) ?>">
            <input type="hidden" name="edit_mode" value="upload">

            <table class="form-table">
                <tr>
                    <th scope="row">Loại hồ sơ</th>
                    <td>
                        <select name="attachment_type" required>
                            <option value="contract" <?= selected($record->attachment_type, 'contract') ?>>Hợp đồng</option>
                            <option value="cccd" <?= selected($record->attachment_type, 'cccd') ?>>CCCD</option>
                            <option value="degree" <?= selected($record->attachment_type, 'degree') ?>>Bằng cấp</option>
                            <option value="other" <?= selected($record->attachment_type, 'other') ?>>Khác</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Nơi lưu trữ</th>
                    <td>
                        <!-- <select name="storage_type" required>
                            <option value="local" <?= selected($record->storage_type, 'local') ?>>Lưu trên máy chủ</option>
                            <option value="drive" <?= selected($record->storage_type, 'drive') ?>>Lưu trên Google Drive</option>
                        </select> -->
                        <input type="hidden" name="storage_type" value="local">
                        <span>Lưu trên máy chủ</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Chọn file mới</th>
                    <td>
                        <input type="file" name="attachment_file">
                        <p class="description">
                            File hiện tại:
                            <a href="<?= esc_url($record->file_url) ?>" target="_blank"><?= esc_html($record->file_name) ?></a>
                            <!-- <?php if ($record->storage_type === 'drive'): ?>
                                <span class="dashicons dashicons-google" title="Lưu trên Google Drive"></span>
                            <?php endif; ?> -->
                        </p>
                    </td>
                </tr>
            </table>

            <p><input type="submit" name="aerp_edit_attachment_submit" class="button button-primary" value="Cập nhật hồ sơ"></p>
        </form>
    </div>

    <!-- Nhập thủ công -->
    <div id="tab-manual" class="attachment-tab" style="display:none;">
        <form method="post">
            <?php wp_nonce_field('aerp_edit_attachment_' . $record->id, 'aerp_edit_attachment_nonce'); ?>
            <input type="hidden" name="id" value="<?= esc_attr($record->id) ?>">
            <input type="hidden" name="employee_id" value="<?= esc_attr($record->employee_id) ?>">
            <input type="hidden" name="edit_mode" value="manual">

            <table class="form-table">
                <tr>
                    <th scope="row">Loại hồ sơ</th>
                    <td>
                        <select name="attachment_type" required>
                            <option value="contract" <?= selected($record->attachment_type, 'contract') ?>>Hợp đồng</option>
                            <option value="cccd" <?= selected($record->attachment_type, 'cccd') ?>>CCCD</option>
                            <option value="degree" <?= selected($record->attachment_type, 'degree') ?>>Bằng cấp</option>
                            <option value="other" <?= selected($record->attachment_type, 'other') ?>>Khác</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Tên file</th>
                    <td><input type="text" name="file_name" class="regular-text" value="<?= esc_attr($record->file_name) ?>" required></td>
                </tr>
                <tr>
                    <th scope="row">URL file</th>
                    <td>
                        <input type="url" name="file_url" class="regular-text" id="file_url" value="<?= esc_url($record->file_url) ?>" required>
                        <button type="button" class="button" id="select_file">Chọn từ thư viện</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Định dạng</th>
                    <td><input type="text" name="file_type" class="small-text" id="file_type" value="<?= esc_attr($record->file_type) ?>"></td>
                </tr>
            </table>

            <p><input type="submit" name="aerp_edit_attachment_submit" class="button button-primary" value="Cập nhật hồ sơ"></p>
        </form>
    </div>
</div>