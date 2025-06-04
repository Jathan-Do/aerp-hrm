<?php
$employee_id = absint($_GET['edit'] ?? 0);
$employee = AERP_Employee_Manager::get_by_id($employee_id);

if (!$employee) {
    echo '<div class="notice notice-error"><p>Không tìm thấy nhân viên.</p></div>';
    return;
}
$data = get_object_vars($employee);
$all_roles = class_exists('AERP_Role_Manager') ? AERP_Role_Manager::get_roles() : [];
$user_roles = isset($data['user_id']) ? AERP_Role_Manager::get_roles_of_user($data['user_id']) : [];
$all_permissions = class_exists('AERP_Permission_Manager') ? AERP_Permission_Manager::get_permissions() : [];
$user_permissions = isset($data['user_id']) ? AERP_Permission_Manager::get_permissions_of_user($data['user_id']) : [];

// Lấy tất cả permission đã có qua role
$user_role_permissions = [];
if (!empty($user_roles)) {
    global $wpdb;
    $role_ids = implode(',', array_map('intval', $user_roles));
    if ($role_ids) {
        $user_role_permissions = $wpdb->get_col("SELECT DISTINCT permission_id FROM {$wpdb->prefix}aerp_role_permission WHERE role_id IN ($role_ids)");
    }
}

$role_permissions_map = [];
if (!empty($all_roles)) {
    foreach ($all_roles as $role) {
        $role_permissions_map[$role->id] = AERP_Role_Manager::get_permissions_of_role($role->id);
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Chỉnh sửa nhân viên</h1>

    <form method="post">
        <?php wp_nonce_field('aerp_save_employee_action', 'aerp_save_employee_nonce'); ?>
        <input type="hidden" name="employee_id" value="<?= esc_attr($data['id']) ?>">

        <h2>1. Thông tin cá nhân</h2>
        <table class="form-table">
            <tr>
                <th>Mã nhân viên</th>
                <td><input type="text" name="employee_code" value="<?= esc_attr($data['employee_code']) ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th>Họ tên</th>
                <td><input type="text" name="full_name" value="<?= esc_attr($data['full_name']) ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th>Giới tính</th>
                <td>
                    <select name="gender">
                        <option value="male" <?= selected($data['gender'], 'male') ?>>Nam</option>
                        <option value="female" <?= selected($data['gender'], 'female') ?>>Nữ</option>
                        <option value="other" <?= selected($data['gender'], 'other') ?>>Khác</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Ngày sinh</th>
                <td><input type="date" name="birthday" value="<?= esc_attr($data['birthday']) ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th>Số CCCD</th>
                <td><input type="text" name="cccd_number" value="<?= esc_attr($data['cccd_number']) ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th>Ngày cấp CCCD</th>
                <td><input type="date" name="cccd_issued_date" value="<?= esc_attr($data['cccd_issued_date']) ?>" class="regular-text"></td>
            </tr>
        </table>

        <h2>2. Địa chỉ & liên hệ</h2>
        <table class="form-table">
            <tr>
                <th>Địa chỉ thường trú</th>
                <td><textarea name="address_permanent" class="large-text"><?= esc_textarea($data['address_permanent']) ?></textarea></td>
            </tr>
            <tr>
                <th>Địa chỉ hiện tại</th>
                <td><textarea name="address_current" class="large-text"><?= esc_textarea($data['address_current']) ?></textarea></td>
            </tr>
            <tr>
                <th>Số điện thoại</th>
                <td><input type="text" name="phone_number" value="<?= esc_attr($data['phone_number']) ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><input type="email" name="email" value="<?= esc_attr($data['email']) ?>" class="regular-text"></td>
            </tr>
        </table>

        <h2>3. Tài khoản ngân hàng</h2>
        <table class="form-table">
            <tr>
                <th>Số tài khoản</th>
                <td><input type="text" name="bank_account" value="<?= esc_attr($data['bank_account']) ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th>Tên ngân hàng</th>
                <td><input type="text" name="bank_name" value="<?= esc_attr($data['bank_name']) ?>" class="regular-text"></td>
            </tr>
        </table>

        <h2>4. Thân nhân</h2>
        <table class="form-table">
            <tr>
                <th>Họ tên người thân</th>
                <td><input type="text" name="relative_name" value="<?= esc_attr($data['relative_name']) ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th>Số điện thoại</th>
                <td><input type="text" name="relative_phone" value="<?= esc_attr($data['relative_phone']) ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th>Quan hệ</th>
                <td><input type="text" name="relative_relationship" value="<?= esc_attr($data['relative_relationship']) ?>" class="regular-text"></td>
            </tr>
        </table>

        <h2>5. Công việc</h2>
        <table class="form-table">
            <tr>
                <th>Phòng ban</th>
                <td>
                    <select name="department_id">
                        <?php foreach (apply_filters('aerp_get_departments', []) as $dept): ?>
                            <option value="<?= esc_attr($dept->id) ?>" <?= selected($data['department_id'], $dept->id) ?>>
                                <?= esc_html($dept->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Chức vụ</th>
                <td>
                    <select name="position_id">
                        <?php foreach (apply_filters('aerp_get_positions', []) as $pos): ?>
                            <option value="<?= esc_attr($pos->id) ?>" <?= selected($data['position_id'], $pos->id) ?>>
                                <?= esc_html($pos->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Chi nhánh</th>
                <td>
                    <select name="work_location_id">
                        <?php foreach (apply_filters('aerp_get_work_locations', []) as $loc): ?>
                            <option value="<?= esc_attr($loc->id) ?>" <?= selected($data['work_location_id'], $loc->id) ?>>
                                <?= esc_html($loc->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Ngày vào làm</th>
                <td><input type="date" name="join_date" value="<?= esc_attr($data['join_date']) ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th>Ngày nghỉ việc</th>
                <td><input type="date" name="off_date" value="<?= esc_attr($data['off_date']) ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th>Trạng thái</th>
                <td>
                    <select name="status">
                        <option value="active" <?= selected($data['status'], 'active') ?>>Đang làm</option>
                        <option value="inactive" <?= selected($data['status'], 'inactive') ?>>Tạm nghỉ</option>
                        <option value="resigned" <?= selected($data['status'], 'resigned') ?>>Đã nghỉ</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Ghi chú</th>
                <td><textarea name="note" class="large-text"><?= esc_textarea($data['note']) ?></textarea></td>
            </tr>
            <tr>
                <th>User WordPress</th>
                <td>
                    <select name="user_id">
                        <option value="0">Không liên kết</option>
                        <?php
                        $users = get_users();
                        foreach ($users as $user) {
                            $wp_user_roles = array_map(function ($role) {
                                return translate_user_role(wp_roles()->roles[$role]['name']);
                            }, $user->roles);
                            $role_display = !empty($wp_user_roles) ? ' (' . implode(', ', $wp_user_roles) . ')' : '';
                            echo '<option value="' . esc_attr($user->ID) . '"' . selected($data['user_id'], $user->ID, false) . '>' . esc_html($user->display_name . ' - ' . $user->user_email . $role_display) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Nhóm quyền</th>
                <td>
                    <div class="aerp-perm-group">
                    <?php foreach ($all_roles as $role): ?>
                        <?php $is_checked = in_array((string)$role->id, array_map('strval', $user_roles)); ?>
                        <label style="display:block;margin-bottom:4px;">
                            <input type="checkbox" class="role-checkbox" data-role-id="<?= esc_attr($role->id) ?>" name="user_roles[]" value="<?= esc_attr($role->id) ?>" <?= $is_checked ? 'checked' : '' ?>>
                            <?= esc_html($role->name) ?><?php if ($role->description) echo ' - ' . esc_html($role->description); ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Quyền đặc biệt</th>
                <td>
                    <?php
                    // Group quyền theo feature
                    $grouped_permissions = [];
                    foreach ($all_permissions as $perm) {
                        if (preg_match('/^([a-zA-Z0-9_]+)_/', $perm->name, $matches)) {
                            $feature = $matches[1];
                            $grouped_permissions[$feature][] = $perm;
                        } else {
                            $grouped_permissions['Khác'][] = $perm;
                        }
                    }
                    ?>
                    <div class="aerp-perm-grid">
                        <?php foreach ($grouped_permissions as $feature => $perms): ?>
                            <div class="aerp-perm-group">
                                <div class="aerp-perm-group-title"><?= esc_html($feature) ?></div>
                                <div class="aerp-perm-checkbox">
                                    <?php foreach ($perms as $perm): ?>
                                        <?php
                                            $has_via_role = in_array($perm->id, $user_role_permissions);
                                            $is_checked = in_array((string)$perm->id, array_map('strval', $user_permissions));
                                        ?>
                                        <label title="<?= esc_attr($perm->description ?: $perm->name) ?>" style="color:<?= $has_via_role ? '#888' : 'inherit' ?>">
                                            <input type="checkbox"
                                                class="perm-checkbox"
                                                data-perm-id="<?= esc_attr($perm->id) ?>"
                                                name="user_permissions[]"
                                                value="<?= esc_attr($perm->id) ?>"
                                                <?= $is_checked && !$has_via_role ? 'checked' : '' ?>
                                                <?= $has_via_role ? 'disabled' : '' ?>>
                                            <?= esc_html($perm->name) ?><?php if ($perm->description) echo ' - ' . esc_html($perm->description); ?>
                                            <?php if ($has_via_role): ?>
                                                <span class="perm-via-role" style="color:#888; font-size:11px;">(Đã có qua nhóm quyền)</span>
                                            <?php endif; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </td>
            </tr>
        </table>

        <p>
            <input type="submit" name="aerp_save_employee" class="button button-primary" value="Cập nhật nhân viên">
            <a href="<?= admin_url('admin.php?page=aerp_employees') ?>" class="button">Huỷ</a>
        </p>
    </form>
</div>

<script>
    var rolePermissionsMap = <?= json_encode($role_permissions_map) ?>;
</script>
