<div class="wrap">
    <h1 class="wp-heading-inline">Thêm chi nhánh</h1>
    <form method="post">
        <?php wp_nonce_field('aerp_save_work_location_action', 'aerp_save_work_location_nonce'); ?>
        <table class="form-table">
            <tr>
                <th>Tên chi nhánh</th>
                <td><input type="text" name="work_location_name" class="regular-text" required></td>
            </tr>
            <tr>
                <th>Mô tả</th>
                <td><textarea name="work_location_desc" class="large-text"></textarea></td>
            </tr>
        </table>
        <p>
            <input type="submit" name="aerp_save_work_location" class="button button-primary" value="Lưu thông tin">
            <a href="<?= admin_url('admin.php?page=aerp_work_locations') ?>" class="button">Huỷ</a>
        </p>
    </form>
</div> 