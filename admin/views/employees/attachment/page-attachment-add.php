<div class="wrap">
    <h1 class="wp-heading-inline">Thêm hồ sơ đính kèm</h1>
    <a href="<?= admin_url('admin.php?page=aerp_employees&view=' . absint($_GET['employee_id']).'#attachments') ?>" class="page-title-action">← Quay lại nhân viên</a>
    <hr class="wp-header-end">

    <!-- Tab Switch -->
    <div class="tab-switcher" style="margin-top: 20px;">
        <a href="#" class="button button-secondary tab-upload active" data-target="upload">📁 Tải từ máy</a>
        <a href="#" class="button button-secondary tab-manual" data-target="manual">📝 Nhập thủ công</a>
    </div>

    <!-- Upload từ máy -->
    <div id="tab-upload" class="attachment-tab" style="display:block;">
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('aerp_upload_attachment_' . $_GET['employee_id'], 'aerp_attachment_nonce'); ?>
            <input type="hidden" name="employee_id" value="<?= esc_attr($_GET['employee_id']) ?>">
            <table class="form-table">
                <tr>
                    <th scope="row">Loại hồ sơ</th>
                    <td>
                        <select name="attachment_type" required>
                            <option value="">-- Chọn loại --</option>
                            <option value="contract">Hợp đồng</option>
                            <option value="cccd">CCCD</option>
                            <option value="degree">Bằng cấp</option>
                            <option value="other">Khác</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Nơi lưu trữ</th>
                    <td>
                        <!-- <select name="storage_type" required>
                            <option value="local">Lưu trên máy chủ</option>
                            <option value="drive">Lưu trên Google Drive</option>
                        </select> -->
                        <input type="hidden" name="storage_type" value="local">
                        <span>Lưu trên máy chủ</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Chọn file</th>
                    <td><input type="file" name="attachment_file" required></td>
                </tr>
            </table>
            <p><input type="submit" name="aerp_upload_attachment" class="button button-primary" value="Tải lên"></p>
        </form>
    </div>

    <!-- Nhập thủ công -->
    <div id="tab-manual" class="attachment-tab" style="display:none;">
        <form method="post">
            <?php wp_nonce_field('aerp_manual_attachment_' . $_GET['employee_id'], 'aerp_manual_attachment_nonce'); ?>
            <input type="hidden" name="employee_id" value="<?= esc_attr($_GET['employee_id']) ?>">
            <table class="form-table">
                <tr>
                    <th scope="row">Loại hồ sơ</th>
                    <td>
                        <select name="attachment_type" required>
                            <option value="">-- Chọn loại --</option>
                            <option value="contract">Hợp đồng</option>
                            <option value="cccd">CCCD</option>
                            <option value="degree">Bằng cấp</option>
                            <option value="other">Khác</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Tên file</th>
                    <td><input type="text" name="file_name" class="regular-text" id="file_name" required></td>
                </tr>
                <tr>
                    <th scope="row">URL file</th>
                    <td>
                        <input type="url" name="file_url" class="regular-text" id="file_url" required>
                        <button type="button" class="button" id="select_file">Chọn từ thư viện</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Định dạng</th>
                    <td><input type="text" name="file_type" class="small-text" id="file_type"></td>
                </tr>
            </table>
            <p><input type="submit" name="aerp_manual_attachment_submit" class="button button-primary" value="Lưu hồ sơ"></p>
        </form>
    </div>


</div>
