<div class="wrap">
    <h1 class="wp-heading-inline">Thêm nhân viên</h1>

    <form method="post">
        <?php wp_nonce_field('aerp_save_employee_action', 'aerp_save_employee_nonce'); ?>

        <h2>1. Thông tin cá nhân</h2>
        <table class="form-table">
            <tr>
                <th>Mã nhân viên</th>
                <td><input type="text" name="employee_code" class="regular-text" required></td>
            </tr>
            <tr>
                <th>Họ tên</th>
                <td><input type="text" name="full_name" class="regular-text" required></td>
            </tr>
            <tr>
                <th>Giới tính</th>
                <td>
                    <select name="gender">
                        <option value="male">Nam</option>
                        <option value="female">Nữ</option>
                        <option value="other">Khác</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Ngày sinh</th>
                <td><input type="date" name="birthday" class="regular-text"></td>
            </tr>
            <tr>
                <th>Số CCCD</th>
                <td><input type="text" name="cccd_number" class="regular-text"></td>
            </tr>
            <tr>
                <th>Ngày cấp CCCD</th>
                <td><input type="date" name="cccd_issued_date" class="regular-text"></td>
            </tr>
        </table>

        <h2>2. Địa chỉ & liên hệ</h2>
        <table class="form-table">
            <tr>
                <th>Địa chỉ thường trú</th>
                <td><textarea name="address_permanent" class="large-text"></textarea></td>
            </tr>
            <tr>
                <th>Địa chỉ hiện tại</th>
                <td><textarea name="address_current" class="large-text"></textarea></td>
            </tr>
            <tr>
                <th>Số điện thoại</th>
                <td><input type="text" name="phone_number" class="regular-text"></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><input type="email" name="email" class="regular-text"></td>
            </tr>
        </table>

        <h2>3. Tài khoản ngân hàng</h2>
        <table class="form-table">
            <tr>
                <th>Số tài khoản</th>
                <td><input type="text" name="bank_account" class="regular-text"></td>
            </tr>
            <tr>
                <th>Tên ngân hàng</th>
                <td><input type="text" name="bank_name" class="regular-text"></td>
            </tr>
        </table>

        <h2>4. Thân nhân</h2>
        <table class="form-table">
            <tr>
                <th>Họ tên người thân</th>
                <td><input type="text" name="relative_name" class="regular-text"></td>
            </tr>
            <tr>
                <th>Số điện thoại</th>
                <td><input type="text" name="relative_phone" class="regular-text"></td>
            </tr>
            <tr>
                <th>Quan hệ</th>
                <td><input type="text" name="relative_relationship" class="regular-text"></td>
            </tr>
        </table>

        <h2>5. Công việc</h2>
        <table class="form-table">
            <tr>
                <th>Phòng ban</th>
                <td>
                    <!-- <select name="department_id">
                        <?php foreach (apply_filters('aerp_get_departments', []) as $dept): ?>
                            <option value="<?= esc_attr($dept->id) ?>"><?= esc_html($dept->name) ?></option>
                        <?php endforeach; ?>
                    </select> -->
                    <select name="department_id">
                        <?php aerp_safe_select_options(apply_filters('aerp_get_departments', []), $selected_position); ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Chức vụ</th>
                <td>
                    <!-- <select name="position_id">
                        <?php foreach (apply_filters('aerp_get_positions', []) as $pos): ?>
                            <option value="<?= esc_attr($pos->id) ?>"><?= esc_html($pos->name) ?></option>
                        <?php endforeach; ?>
                    </select> -->
                    <select name="position_id">
                        <?php aerp_safe_select_options(apply_filters('aerp_get_positions', []), $selected_position); ?>
                    </select>

                </td>
            </tr>
            <tr>
                <th>Chi nhánh</th>
                <td>
                    <select name="work_location_id">
                        <?php aerp_safe_select_options(apply_filters('aerp_get_work_locations', []), $selected_work_location); ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Ngày vào làm</th>
                <td><input type="date" name="join_date" id="start_date" class="regular-text"></td>
            </tr>
            <tr>
                <th>Ngày nghỉ việc</th>
                <td> <input type="date" name="off_date" id="end_date" class="regular-text"></td>
            </tr>
            <tr>
                <th>Trạng thái</th>
                <td>
                    <select name="status">
                        <option value="active">Đang làm</option>
                        <option value="inactive">Tạm nghỉ</option>
                        <option value="resigned">Đã nghỉ</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Ghi chú</th>
                <td><textarea name="note" class="large-text"></textarea></td>
            </tr>
            <tr>
                <th>User WordPress</th>
                <td>
                    <select name="user_id">
                        <option value="0">Không liên kết</option>
                        <?php
                        $users = get_users();
                        foreach ($users as $user) {
                            $user_roles = array_map(function ($role) {
                                return translate_user_role(wp_roles()->roles[$role]['name']);
                            }, $user->roles);
                            $role_display = !empty($user_roles) ? ' (' . implode(', ', $user_roles) . ')' : '';
                            echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name . ' - ' . $user->user_email . $role_display) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>

        <p>
            <input type="submit" name="aerp_save_employee" class="button button-primary" value="Lưu thông tin">
            <a href="<?= admin_url('admin.php?page=aerp_employees') ?>" class="button">Huỷ</a>
        </p>
    </form>
</div>