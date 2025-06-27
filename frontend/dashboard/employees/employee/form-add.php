<?php
if (!defined('ABSPATH')) exit;
$current_user = wp_get_current_user();

ob_start();
?>
<style>
    .aerp-perm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px 32px;
        margin-top: 8px;
    }

    .aerp-perm-group {
        background: #f8fafd;
        border: 1px solid #e2e4e7;
        border-radius: 6px;
        padding: 10px 14px 8px 14px;
        min-width: 200px;
    }

    .aerp-perm-group-title {
        font-weight: 600;
        color: #2271b1;
        margin-bottom: 6px;
        text-transform: capitalize;
        font-size: 15px;
        letter-spacing: 0.5px;
    }

    .aerp-perm-checkbox label {
        display: flex;
        align-items: center;
        margin-bottom: 4px;
        font-size: 13px;
        cursor: pointer;
    }

    .aerp-perm-checkbox input[type=checkbox] {
        margin-right: 6px;
    }
</style>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Thêm nhân viên mới</h2>
    <div class="user-info text-end">
        Xin chào, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_employee_action', 'aerp_save_employee_nonce'); ?>
            <div class="row">
                <h5>1. Thông tin cá nhân</h5>
                <div class="col-md-3 mb-3">
                    <label for="employee_code" class="form-label">Mã nhân viên</label>
                    <input type="text" class="form-control" id="employee_code" name="employee_code">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="full_name" class="form-label">Họ và tên</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="gender" class="form-label">Giới tính</label>
                    <select class="form-select" id="gender" name="gender">
                        <option value="male">Nam</option>
                        <option value="female">Nữ</option>
                        <option value="other">Khác</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="birthday" class="form-label">Ngày sinh</label>
                    <input type="date" class="form-control bg-body" id="birthday" name="birthday">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="cccd_number" class="form-label">Số CCCD</label>
                    <input type="text" class="form-control" id="cccd_number" name="cccd_number">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="cccd_issued_date" class="form-label">Ngày cấp CCCD</label>
                    <input type="date" class="form-control bg-body" id="cccd_issued_date" name="cccd_issued_date">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="bank_account" class="form-label">Số tài khoản</label>
                    <input type="text" class="form-control" id="bank_account" name="bank_account">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="bank_name" class="form-label">Tên ngân hàng</label>
                    <input type="text" class="form-control" id="bank_name" name="bank_name">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="phone_number" class="form-label">Số điện thoại</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="address_permanent" class="form-label">Địa chỉ thường trú</label>
                    <textarea rows="1" class="form-control" id="address_permanent" name="address_permanent"></textarea>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="address_current" class="form-label">Địa chỉ hiện tại</label>
                    <textarea rows="1" class="form-control" id="address_current" name="address_current"></textarea>
                </div>
                <h5>2. Thân nhân</h5>
                <div class="col-md-3 mb-3">
                    <label for="relative_name" class="form-label">Họ tên người thân</label>
                    <input type="text" class="form-control" id="relative_name" name="relative_name">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="relative_phone" class="form-label">Số điện thoại người thân</label>
                    <input type="text" class="form-control" id="relative_phone" name="relative_phone">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="relative_relationship" class="form-label">Quan hệ</label>
                    <input type="text" class="form-control" id="relative_relationship" name="relative_relationship">
                </div>
                <h5>3. Công việc</h5>
                <div class="col-md-3 mb-3">
                    <label for="department_id" class="form-label">Phòng ban</label>
                    <select class="form-select" id="department_id" name="department_id">
                        <?php
                        $departments = apply_filters('aerp_get_departments', []);
                        aerp_safe_select_options($departments, '', 'id', 'name', true);
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="position_id" class="form-label">Chức vụ</label>
                    <select class="form-select" id="position_id" name="position_id">
                        <?php
                        $positions = apply_filters('aerp_get_positions', []);
                        aerp_safe_select_options($positions, '', 'id', 'name', true);
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="work_location_id" class="form-label">Chi nhánh</label>
                    <select class="form-select" id="work_location_id" name="work_location_id">
                        <?php
                        $work_locations = apply_filters('aerp_get_work_locations', []);
                        aerp_safe_select_options($work_locations, '', 'id', 'name', true);
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active">Đang làm</option>
                        <option value="inactive">Tạm nghỉ</option>
                        <option value="resigned">Đã nghỉ</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="join_date" class="form-label">Ngày vào làm</label>
                    <input type="date" class="form-control bg-body" id="join_date" name="join_date">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="off_date" class="form-label">Ngày nghỉ việc</label>
                    <input type="date" class="form-control bg-body" id="off_date" name="off_date">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="user_id" class="form-label">User WordPress</label>
                    <select class="form-select" id="user_id" name="user_id">
                        <option value="0">Không liên kết</option>
                        <?php
                        $users = get_users();
                        foreach ($users as $user) {
                            $wp_user_roles = array_map(function ($role) {
                                return translate_user_role(wp_roles()->roles[$role]['name']);
                            }, $user->roles);
                            $role_display = !empty($wp_user_roles) ? ' (' . implode(', ', $wp_user_roles) . ')' : '';
                            echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name . ' - ' . $user->user_email . $role_display) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <?php
                $all_roles = class_exists('AERP_Frontend_Role_Manager') ? AERP_Frontend_Role_Manager::get_roles() : [];
                $all_permissions = class_exists('AERP_Frontend_Permission_Manager') ? AERP_Frontend_Permission_Manager::get_permissions() : [];
                $role_permissions_map = [];
                if (!empty($all_roles)) {
                    foreach ($all_roles as $role) {
                        $role_permissions_map[$role->id] = AERP_Frontend_Role_Manager::get_permissions_of_role($role->id);
                    }
                }
                ?>
                <script>
                    var rolePermissionsMap = <?= json_encode($role_permissions_map) ?>;
                </script>
                <h5>4. Phân quyền</h5>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Nhóm quyền</label>
                    <div class="aerp-perm-group">
                        <?php foreach ($all_roles as $role): ?>
                            <label style="display:block;margin-bottom:4px;">
                                <input type="checkbox" class="role-checkbox" data-role-id="<?= esc_attr($role->id) ?>" name="user_roles[]" value="<?= esc_attr($role->id) ?>" id="role-<?= esc_attr($role->name) ?>">
                                <?= esc_html($role->name) ?><?php if ($role->description) echo ' - ' . esc_html($role->description); ?>
                            </label>
                            <?php if ($role->name === 'department_lead'): ?>
                                <div id="select-department-lead" style="display:none; margin: 8px 0 0 24px;">
                                    <label>Chọn phòng ban quản lý:</label>
                                    <select name="department_lead_department_id">
                                        <option value="">-- Chọn phòng ban --</option>
                                        <?php foreach (apply_filters('aerp_get_departments', []) as $dept): ?>
                                            <option value="<?= esc_attr($dept->id) ?>"><?= esc_html($dept->name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var leadCheckbox = document.getElementById('role-department_lead');
                            var selectDiv = document.getElementById('select-department-lead');
                            if (leadCheckbox && selectDiv) {
                                leadCheckbox.addEventListener('change', function() {
                                    selectDiv.style.display = this.checked ? 'block' : 'none';
                                });
                            }
                        });
                    </script>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Quyền đặc biệt</label>
                    <div class="aerp-perm-grid ">
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
                        <?php foreach ($grouped_permissions as $feature => $perms): ?>
                            <div class="aerp-perm-group">
                                <div class="aerp-perm-group-title"><?= esc_html($feature) ?></div>
                                <div class="aerp-perm-checkbox">
                                    <?php foreach ($perms as $perm): ?>
                                        <label title="<?= esc_attr($perm->description ?: $perm->name) ?>">
                                            <input type="checkbox" class="perm-checkbox" data-perm-id="<?= esc_attr($perm->id) ?>" name="user_permissions[]" value="<?= esc_attr($perm->id) ?>">
                                            <?= esc_html($perm->name) ?><?php if ($perm->description) echo ' - ' . esc_html($perm->description); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label for="note" class="form-label">Ghi chú</label>
                    <textarea class="form-control" id="note" name="note" rows="2"></textarea>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_employee" class="btn btn-primary">Thêm mới</button>
                <a href="<?php echo home_url('/aerp-hrm-employees'); ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Thêm nhân viên mới';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
