<?php
$role_id = absint($_GET['edit'] ?? 0);
$role = AERP_Role_Manager::get_by_id($role_id);
if (!$role) {
    echo '<div class="notice notice-error"><p>Không tìm thấy nhóm quyền.</p></div>';
    return;
}
$data = get_object_vars($role);
$all_permissions = function_exists('AERP_Permission_Manager::get_permissions') ? AERP_Permission_Manager::get_permissions() : (class_exists('AERP_Permission_Manager') ? AERP_Permission_Manager::get_permissions() : []);
$role_permissions = AERP_Role_Manager::get_permissions_of_role($role_id);
$system_slugs = ['admin', 'department_lead', 'accountant', 'employee'];
$is_system_role = in_array($data['slug'] ?? '', $system_slugs);
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Chỉnh sửa nhóm quyền</h1>
    <form method="post">
        <?php wp_nonce_field('aerp_save_role_action', 'aerp_save_role_nonce'); ?>
        <input type="hidden" name="role_id" value="<?= esc_attr($data['id']) ?>">
        <table class="form-table">
            <tr>
                <th>Tên nhóm quyền</th>
                <td><input type="text" name="role_name" value="<?= esc_attr($data['name']) ?>" class="regular-text" required <?= $is_system_role ? 'readonly' : '' ?>></td>
            </tr>
            <tr>
                <th>Mô tả</th>
                <td><textarea name="role_desc" class="large-text" <?= $is_system_role ? 'readonly' : '' ?>><?= esc_textarea($data['description']) ?></textarea></td>
            </tr>
            <tr>
                <th>Quyền</th>
                <td>
                    <?php foreach ($all_permissions as $perm): ?>
                        <label style="display:block;margin-bottom:4px;">
                            <input type="checkbox" name="role_permissions[]" value="<?= esc_attr($perm->id) ?>" <?= in_array($perm->id, $role_permissions) ? 'checked' : '' ?>>
                            <?= esc_html($perm->name) ?><?php if ($perm->description) echo ' - ' . esc_html($perm->description); ?>
                        </label>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>
        <p>
            <input type="submit" name="aerp_save_role" class="button button-primary" value="Cập nhật nhóm quyền">
            <a href="<?= admin_url('admin.php?page=aerp_roles') ?>" class="button">Huỷ</a>
        </p>
    </form>
</div> 