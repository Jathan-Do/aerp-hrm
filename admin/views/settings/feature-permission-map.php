<?php
function aerp_render_feature_permission_map_page()
{
    // Lấy danh sách quyền từ DB
    $permissions = class_exists('AERP_Permission_Manager') ? AERP_Permission_Manager::get_permissions() : [];

    // Group quyền theo feature (prefix trước dấu _)
    $feature_permissions = [];
    foreach ($permissions as $perm) {
        // Giả sử $perm->name là dạng: employee_view, salary_edit, ...
        if (preg_match('/^([a-zA-Z0-9_]+)_([a-zA-Z0-9_]+)$/', $perm->name, $matches)) {
            $feature = $matches[1];
            $action = $matches[2];
            $feature_permissions[$feature][$action] = $perm;
        }
    }

    // Lấy map đã lưu (nếu có)
    $map = get_option('aerp_feature_permission_map', []);

    // Xử lý lưu map khi submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('aerp_save_feature_permission_map')) {
        $new_map = [];
        foreach ($feature_permissions as $feature_key => $actions) {
            $new_map[$feature_key] = isset($_POST['feature_permission'][$feature_key]) ? $_POST['feature_permission'][$feature_key] : [];
        }
        update_option('aerp_feature_permission_map', $new_map);
        echo '<div class="updated"><p>Lưu thành công!</p></div>';
        $map = $new_map;
    }

    // Lấy tất cả action unique
    $all_actions = [];
    foreach ($feature_permissions as $feature => $actions) {
        foreach ($actions as $action => $perm) {
            $all_actions[$action] = true;
        }
    }
?>
    <div class="wrap">
        <h1>Cấu hình phân quyền chức năng</h1>
        <form method="post">
            <?php wp_nonce_field('aerp_save_feature_permission_map'); ?>
            <table class="form-table widefat striped aerp-matrix-permission">
                <thead>
                    <tr>
                        <th rowspan="2" style="vertical-align: middle; background: #f6f7f7; border-right: 1px solid #e2e4e7;">Chức năng</th>
                        <th colspan="<?= count($all_actions) ?>" style="text-align: center; background: #f6f7f7;">Hành động</th>
                    </tr>
                    <tr>
                        <?php foreach ($all_actions as $action => $_): ?>
                            <th style="text-align: center; background: #f6f7f7;"><?= esc_html($action) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feature_permissions as $feature_key => $actions): ?>
                        <tr>
                            <td style="border-right: 1px solid #e2e4e7;"><strong><?= esc_html($feature_key) ?></strong></td>
                            <?php foreach ($all_actions as $action_key => $_): ?>
                                <td>
                                    <?php if (isset($actions[$action_key])):
                                        $perm = $actions[$action_key];
                                    ?>
                                        <input type="checkbox"
                                            name="feature_permission[<?= esc_attr($feature_key) ?>][]"
                                            value="<?= esc_attr($perm->name) ?>"
                                            title="<?= esc_attr($perm->description ?: $perm->name) ?>"
                                            <?= (isset($map[$feature_key]) && in_array($perm->name, $map[$feature_key])) ? 'checked' : '' ?>>
                                    <?php else: ?>
                                        <span class="aerp-no-permission">×</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><input type="submit" class="button button-primary" value="Lưu cấu hình"></p>
        </form>
    </div>
<?php
}
aerp_render_feature_permission_map_page();
