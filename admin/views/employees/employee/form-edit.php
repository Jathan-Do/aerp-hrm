<?php
$employee_id = absint($_GET['edit'] ?? 0);
$employee = AERP_Employee_Manager::get_by_id($employee_id);

if (!$employee) {
    echo '<div class="notice notice-error"><p>Không tìm thấy nhân viên.</p></div>';
    return;
}

$data = get_object_vars($employee);
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Chỉnh sửa nhân viên</h1>

    <form method="post">
        <?php wp_nonce_field('aerp_save_employee_action', 'aerp_save_employee_nonce'); ?>
        <input type="hidden" name="employee_id" value="<?= esc_attr($data['id']) ?>">

        <h2>1. Thông tin cá nhân</h2>
        <table class="form-table">
            <tr><th>Mã nhân viên</th><td><input type="text" name="employee_code" value="<?= esc_attr($data['employee_code']) ?>" class="regular-text" required></td></tr>
            <tr><th>Họ tên</th><td><input type="text" name="full_name" value="<?= esc_attr($data['full_name']) ?>" class="regular-text" required></td></tr>
            <tr><th>Giới tính</th>
                <td>
                    <select name="gender">
                        <option value="male" <?= selected($data['gender'], 'male') ?>>Nam</option>
                        <option value="female" <?= selected($data['gender'], 'female') ?>>Nữ</option>
                        <option value="other" <?= selected($data['gender'], 'other') ?>>Khác</option>
                    </select>
                </td>
            </tr>
            <tr><th>Ngày sinh</th><td><input type="date" name="birthday" value="<?= esc_attr($data['birthday']) ?>" class="regular-text"></td></tr>
            <tr><th>Số CCCD</th><td><input type="text" name="cccd_number" value="<?= esc_attr($data['cccd_number']) ?>" class="regular-text"></td></tr>
            <tr><th>Ngày cấp CCCD</th><td><input type="date" name="cccd_issued_date" value="<?= esc_attr($data['cccd_issued_date']) ?>" class="regular-text"></td></tr>
        </table>

        <h2>2. Địa chỉ & liên hệ</h2>
        <table class="form-table">
            <tr><th>Địa chỉ thường trú</th><td><textarea name="address_permanent" class="large-text"><?= esc_textarea($data['address_permanent']) ?></textarea></td></tr>
            <tr><th>Địa chỉ hiện tại</th><td><textarea name="address_current" class="large-text"><?= esc_textarea($data['address_current']) ?></textarea></td></tr>
            <tr><th>Số điện thoại</th><td><input type="text" name="phone_number" value="<?= esc_attr($data['phone_number']) ?>" class="regular-text"></td></tr>
            <tr><th>Email</th><td><input type="email" name="email" value="<?= esc_attr($data['email']) ?>" class="regular-text"></td></tr>
        </table>

        <h2>3. Tài khoản ngân hàng</h2>
        <table class="form-table">
            <tr><th>Số tài khoản</th><td><input type="text" name="bank_account" value="<?= esc_attr($data['bank_account']) ?>" class="regular-text"></td></tr>
            <tr><th>Tên ngân hàng</th><td><input type="text" name="bank_name" value="<?= esc_attr($data['bank_name']) ?>" class="regular-text"></td></tr>
        </table>

        <h2>4. Thân nhân</h2>
        <table class="form-table">
            <tr><th>Họ tên người thân</th><td><input type="text" name="relative_name" value="<?= esc_attr($data['relative_name']) ?>" class="regular-text"></td></tr>
            <tr><th>Số điện thoại</th><td><input type="text" name="relative_phone" value="<?= esc_attr($data['relative_phone']) ?>" class="regular-text"></td></tr>
            <tr><th>Quan hệ</th><td><input type="text" name="relative_relationship" value="<?= esc_attr($data['relative_relationship']) ?>" class="regular-text"></td></tr>
        </table>

        <h2>5. Công việc</h2>
        <table class="form-table">
            <tr><th>Phòng ban</th>
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
            <tr><th>Chức vụ</th>
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
            <tr><th>Chi nhánh</th>
                <td>
                    <select name="work_location_id">
                        <?php foreach (apply_filters('aerp_get_work_locations', []) as $loc): ?>
                            <option value="<?= esc_attr($loc->id) ?>" <?= selected($data['work_location_id'], $loc->id) ?>>
                                <?= esc_html($loc->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td></tr>
            <tr><th>Ngày vào làm</th><td><input type="date" name="join_date" value="<?= esc_attr($data['join_date']) ?>" class="regular-text"></td></tr>
            <tr><th>Ngày nghỉ việc</th><td><input type="date" name="off_date" value="<?= esc_attr($data['off_date']) ?>" class="regular-text"></td></tr>
            <tr><th>Trạng thái</th>
                <td>
                    <select name="status">
                        <option value="active" <?= selected($data['status'], 'active') ?>>Đang làm</option>
                        <option value="inactive" <?= selected($data['status'], 'inactive') ?>>Tạm nghỉ</option>
                        <option value="resigned" <?= selected($data['status'], 'resigned') ?>>Đã nghỉ</option>
                    </select>
                </td>
            </tr>
            <tr><th>Ghi chú</th><td><textarea name="note" class="large-text"><?= esc_textarea($data['note']) ?></textarea></td></tr>
            <tr><th>User WordPress</th>
                <td>
                    <select name="user_id">
                        <option value="0">Không liên kết</option>
                        <?php
                        $users = get_users();
                        foreach ($users as $user) {
                            $user_roles = array_map(function($role) {
                                return translate_user_role(wp_roles()->roles[$role]['name']);
                            }, $user->roles);
                            $role_display = !empty($user_roles) ? ' (' . implode(', ', $user_roles) . ')' : '';
                            echo '<option value="' . esc_attr($user->ID) . '"' . selected($data['user_id'], $user->ID, false) . '>' . esc_html($user->display_name . ' - ' . $user->user_email . $role_display) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>

        <p>
            <input type="submit" name="aerp_save_employee" class="button button-primary" value="Cập nhật nhân viên">
            <a href="<?= admin_url('admin.php?page=aerp_employees') ?>" class="button">Huỷ</a>
        </p>
    </form>
</div>
