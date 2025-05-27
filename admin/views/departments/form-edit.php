<?php
$edit_id = isset($_GET['edit']) ? absint($_GET['edit']) : 0;
$editing = AERP_Department_Manager::get_by_id($edit_id);
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Cập nhật phòng ban</h1>
    <form method="post">
        <?php wp_nonce_field('aerp_save_department_action', 'aerp_save_department_nonce'); ?>
        <input type="hidden" name="department_id" value="<?= esc_attr($edit_id) ?>">
        <table class="form-table">
            <tr>
                <th><label for="department_name">Tên phòng ban</label></th>
                <td><input type="text" name="department_name" class="regular-text" required value="<?= esc_attr($editing->name) ?>"></td>
            </tr>
            <tr>
                <th><label for="department_desc">Mô tả</label></th>
                <td><textarea name="department_desc" class="large-text"><?= esc_textarea($editing->description) ?></textarea></td>
            </tr>
        </table>
        <p>
            <input type="submit" name="aerp_save_department" class="button button-primary" value="Cập nhật">
            <a href="<?= admin_url('admin.php?page=aerp_departments') ?>" class="button">Huỷ</a>
        </p>
    </form>
    <hr class="wp-header-end">
    <a href="<?= admin_url('admin.php?page=aerp_departments') ?>" class="button">Quay lại danh sách</a>
</div>