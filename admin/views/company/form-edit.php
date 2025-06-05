<?php $info = AERP_Company_Manager::get_info(); ?>

<div class="wrap">
    <h1 class="wp-heading-inline">Cập nhật thông tin công ty</h1>

    <form method="post">
        <?php wp_nonce_field('aerp_save_company_action', 'aerp_save_company_nonce'); ?>

        <table class="form-table">
            <tr>
                <th><label for="company_name">Tên công ty</label></th>
                <td><input type="text" name="company_name" class="regular-text" value="<?= esc_attr($info->company_name ?? '') ?>"></td>
            </tr>
            <tr>
                <th><label for="tax_code">Mã số thuế</label></th>
                <td><input type="text" name="tax_code" class="regular-text" value="<?= esc_attr($info->tax_code ?? '') ?>"></td>
            </tr>
            <tr>
                <th><label for="phone">Số điện thoại</label></th>
                <td><input type="text" name="phone" class="regular-text" value="<?= esc_attr($info->phone ?? '') ?>"></td>
            </tr>
            <tr>
                <th><label for="email">Email</label></th>
                <td><input type="email" name="email" class="regular-text" value="<?= esc_attr($info->email ?? '') ?>"></td>
            </tr>
            <tr>
                <th><label for="website">Website</label></th>
                <td><input type="url" name="website" class="regular-text" value="<?= esc_attr($info->website ?? '') ?>"></td>
            </tr>
            <tr>
                <th><label for="address">Địa chỉ</label></th>
                <td><input type="text" name="address" class="regular-text" value="<?= esc_attr($info->address ?? '') ?>"></td>
            </tr>
            <tr>
                <th><label for="logo_url">Logo URL</label></th>
                <td><input type="url" name="logo_url" class="regular-text" value="<?= esc_attr($info->logo_url ?? '') ?>"></td>
            </tr>
            <tr>
                <th><label for="work_saturday">Làm việc thứ 7</label></th>
                <td>
                    <select name="work_saturday" id="work_saturday">
                        <option value="off" <?= ($info->work_saturday ?? 'off') === 'off' ? 'selected' : '' ?>>Nghỉ thứ 7</option>
                        <option value="full" <?= ($info->work_saturday ?? 'off') === 'full' ? 'selected' : '' ?>>Làm cả ngày thứ 7</option>
                        <option value="half" <?= ($info->work_saturday ?? 'off') === 'half' ? 'selected' : '' ?>>Làm nửa ngày thứ 7</option>
                    </select>
                </td>
            </tr>
        </table>

        <p>
            <input type="submit" name="aerp_save_company" class="button button-primary" value="Lưu thông tin">
        </p>
    </form>
</div>
