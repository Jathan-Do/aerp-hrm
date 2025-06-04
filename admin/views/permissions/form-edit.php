<?php
$permission_id = absint($_GET['edit'] ?? 0);
$permission = AERP_Permission_Manager::get_by_id($permission_id);
if (!$permission) {
    echo '<div class="notice notice-error"><p>Không tìm thấy quyền.</p></div>';
    return;
}
$data = get_object_vars($permission);
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Chỉnh sửa quyền</h1>
    <form method="post">
        <?php wp_nonce_field('aerp_save_permission_action', 'aerp_save_permission_nonce'); ?>
        <input type="hidden" name="permission_id" value="<?= esc_attr($data['id']) ?>">
        <table class="form-table">
            <tr>
                <th>Tên quyền</th>
                <td><input type="text" name="permission_name" value="<?= esc_attr($data['name']) ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th>Mô tả</th>
                <td><textarea name="permission_desc" class="large-text"><?= esc_textarea($data['description']) ?></textarea></td>
            </tr>
        </table>
        <p>
            <input type="submit" name="aerp_save_permission" class="button button-primary" value="Cập nhật quyền">
            <a href="<?= admin_url('admin.php?page=aerp_permissions') ?>" class="button">Huỷ</a>
        </p>
    </form>
</div> 