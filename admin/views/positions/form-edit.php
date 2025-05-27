<?php
$edit_id = absint($_GET['edit']);
$editing = AERP_Position_Manager::get_by_id($edit_id);
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Cập nhật chức vụ</h1>
    <form method="post">
        <?php wp_nonce_field('aerp_save_position_action', 'aerp_save_position_nonce'); ?>
        <input type="hidden" name="position_id" value="<?= esc_attr($edit_id) ?>">
        <table class="form-table">
            <tr>
                <th><label for="position_name">Tên chức vụ</label></th>
                <td><input type="text" name="position_name" class="regular-text" required value="<?= esc_attr($editing->name) ?>"></td>
            </tr>
            <tr>
                <th><label for="position_desc">Mô tả</label></th>
                <td><textarea name="position_desc" class="large-text"><?= esc_textarea($editing->description) ?></textarea></td>
            </tr>
        </table>
        <p>
            <input type="submit" name="aerp_save_position" class="button button-primary" value="Cập nhật">
            <a href="<?= admin_url('admin.php?page=aerp_positions') ?>" class="button">Huỷ</a>
        </p>
    </form>
</div>
