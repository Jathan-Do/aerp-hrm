<div class="wrap">
    <h1 class="wp-heading-inline">Thêm phòng ban</h1>
    <form method="post">
        <?php wp_nonce_field('aerp_save_department_action', 'aerp_save_department_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="department_name">Tên phòng ban</label></th>
                <td><input type="text" name="department_name" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="department_desc">Mô tả</label></th>
                <td><textarea name="department_desc" class="large-text"></textarea></td>
            </tr>
        </table>
        <p><input type="submit" name="aerp_save_department" class="button button-primary" value="Thêm mới"></p>
    </form>
    <hr class="wp-header-end">
    <a href="<?= admin_url('admin.php?page=aerp_departments') ?>" class="button">Quay lại danh sách</a>
</div>
