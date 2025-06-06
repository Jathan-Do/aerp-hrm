<div class="wrap">
    <h1 class="wp-heading-inline">Thêm nhóm quyền</h1>
    <form method="post">
        <?php wp_nonce_field('aerp_save_role_action', 'aerp_save_role_nonce'); ?>
        <table class="form-table">
            <tr>
                <th>Tên nhóm quyền</th>
                <td><input type="text" name="role_name" class="regular-text" required></td>
            </tr>
            <tr>
                <th>Mô tả</th>
                <td><textarea name="role_desc" class="large-text"></textarea></td>
            </tr>
            <?php $all_permissions = function_exists('AERP_Permission_Manager::get_permissions') ? AERP_Permission_Manager::get_permissions() : (class_exists('AERP_Permission_Manager') ? AERP_Permission_Manager::get_permissions() : []); ?>
            <tr>
                <th>Quyền</th>
                <td>
                    <?php foreach ($all_permissions as $perm): ?>
                        <label style="display:block;margin-bottom:4px;">
                            <input type="checkbox" name="role_permissions[]" value="<?= esc_attr($perm->id) ?>">
                            <?= esc_html($perm->name) ?><?php if ($perm->description) echo ' - ' . esc_html($perm->description); ?>
                        </label>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>
        <p>
            <input type="submit" name="aerp_save_role" class="button button-primary" value="Lưu thông tin">
            <a href="<?= admin_url('admin.php?page=aerp_roles') ?>" class="button">Huỷ</a>
        </p>
    </form>
</div> 