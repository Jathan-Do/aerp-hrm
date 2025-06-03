<?php
$work_location_id = absint($_GET['edit'] ?? 0);
$work_location = AERP_Work_Location_Manager::get_by_id($work_location_id);
if (!$work_location) {
    echo '<div class="notice notice-error"><p>Không tìm thấy chi nhánh.</p></div>';
    return;
}
$data = get_object_vars($work_location);
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Chỉnh sửa chi nhánh</h1>
    <form method="post">
        <?php wp_nonce_field('aerp_save_work_location_action', 'aerp_save_work_location_nonce'); ?>
        <input type="hidden" name="work_location_id" value="<?= esc_attr($data['id']) ?>">
        <table class="form-table">
            <tr>
                <th>Tên chi nhánh</th>
                <td><input type="text" name="work_location_name" value="<?= esc_attr($data['name']) ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th>Mô tả</th>
                <td><textarea name="work_location_desc" class="large-text"><?= esc_textarea($data['description']) ?></textarea></td>
            </tr>
        </table>
        <p>
            <input type="submit" name="aerp_save_work_location" class="button button-primary" value="Cập nhật chi nhánh">
            <a href="<?= admin_url('admin.php?page=aerp_work_locations') ?>" class="button">Huỷ</a>
        </p>
    </form>
</div> 