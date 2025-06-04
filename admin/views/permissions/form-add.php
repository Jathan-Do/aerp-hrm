<div class="wrap">
    <h1 class="wp-heading-inline">Thêm quyền</h1>
    <form method="post">
        <?php wp_nonce_field('aerp_save_permission_action', 'aerp_save_permission_nonce'); ?>
        <table class="form-table">
            <tr>
                <th>Tên quyền</th>
                <td><input type="text" name="permission_name" class="regular-text" required></td>
            </tr>
            <tr>
                <th>Mô tả</th>
                <td><textarea name="permission_desc" class="large-text"></textarea></td>
            </tr>
        </table>
        <p>
            <input type="submit" name="aerp_save_permission" class="button button-primary" value="Lưu thông tin">
            <a href="<?= admin_url('admin.php?page=aerp_permissions') ?>" class="button">Huỷ</a>
        </p>
    </form>
</div> 