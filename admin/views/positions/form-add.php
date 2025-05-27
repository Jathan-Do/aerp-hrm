<div class="wrap">
    <h1 class="wp-heading-inline">Thêm chức vụ</h1>
    <form method="post">
        <?php wp_nonce_field('aerp_save_position_action', 'aerp_save_position_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="position_name">Tên chức vụ</label></th>
                <td><input type="text" name="position_name" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="position_desc">Mô tả</label></th>
                <td><textarea name="position_desc" class="large-text"></textarea></td>
            </tr>
        </table>
        <p><input type="submit" name="aerp_save_position" class="button button-primary" value="Thêm mới"></p>
        <a href="<?= admin_url('admin.php?page=aerp_positions') ?>" class="button">Quay lại danh sách</a>
    </form>
</div>
