<?php
if (!defined('ABSPATH')) exit;
// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

if (!is_user_logged_in()) {
    wp_die(__('You must be logged in to access this page.'));
}

// Danh sách điều kiện, chỉ cần 1 cái đúng là qua
$access_conditions = [
    aerp_user_has_role($user_id, 'admin'),
    aerp_user_has_role($user_id, 'department_lead'),
    aerp_user_has_permission($user_id, 'employee_edit'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

$edit_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
$employee = AERP_Frontend_Employee_Manager::get_by_id($edit_id);

if (!$employee) {
    echo '<div class="alert alert-danger">Không tìm thấy nhân viên.</div>';
    return;
}
$data = get_object_vars($employee);
$all_roles = class_exists('AERP_Frontend_Role_Manager') ? AERP_Frontend_Role_Manager::get_roles() : [];
$user_roles = isset($data['user_id']) ? AERP_Frontend_Role_Manager::get_roles_of_user($data['user_id']) : [];
$all_permissions = class_exists('AERP_Frontend_Permission_Manager') ? AERP_Frontend_Permission_Manager::get_permissions() : [];
$user_permissions = isset($data['user_id']) ? AERP_Frontend_Permission_Manager::get_permissions_of_user($data['user_id']) : [];

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
        $role_permissions_map[$role->id] = AERP_Frontend_Role_Manager::get_permissions_of_role($role->id);
    }
}

$department_lead_role_id = null;
foreach ($all_roles as $role) {
    if ($role->name === 'department_lead') {
        $department_lead_role_id = $role->id;
        break;
    }
}
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
    <h2>Cập nhật nhân viên</h2>
    <div class="user-info text-end">
        Xin chào, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>
<?php
if (function_exists('aerp_render_breadcrumb')) {
    aerp_render_breadcrumb([
        ['label' => 'Trang chủ', 'url' => home_url('/aerp-dashboard'), 'icon' => 'fas fa-home'],
        ['label' => 'Quản lý nhân viên', 'url' => home_url('/aerp-hrm-employees')],
        ['label' => 'Cập nhật nhân viên']
    ]);
}
?>
<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_employee_action', 'aerp_save_employee_nonce'); ?>
            <input type="hidden" name="employee_id" value="<?php echo esc_attr($employee->id); ?>">
            <div class="row">
                <h5>1. Thông tin cá nhân</h5>
                <div class="col-md-3 mb-3">
                    <label for="employee_code" class="form-label">Mã nhân viên</label>
                    <input type="text" class="form-control shadow-sm" id="employee_code" name="employee_code" value="<?php echo esc_attr($employee->employee_code); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="full_name" class="form-label">Họ và tên</label>
                    <input type="text" class="form-control shadow-sm" id="full_name" name="full_name" value="<?php echo esc_attr($employee->full_name); ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="gender" class="form-label">Giới tính</label>
                    <select class="form-select shadow-sm" id="gender" name="gender">
                        <option value="male" <?php selected($employee->gender, 'male'); ?>>Nam</option>
                        <option value="female" <?php selected($employee->gender, 'female'); ?>>Nữ</option>
                        <option value="other" <?php selected($employee->gender, 'other'); ?>>Khác</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="birthday" class="form-label">Ngày sinh</label>
                    <input type="date" class="form-control shadow-sm bg-body" id="birthday" name="birthday" value="<?php echo esc_attr($employee->birthday); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="cccd_number" class="form-label">Số CCCD</label>
                    <input type="text" class="form-control shadow-sm" id="cccd_number" name="cccd_number" value="<?php echo esc_attr($employee->cccd_number); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="cccd_issued_date" class="form-label">Ngày cấp CCCD</label>
                    <input type="date" class="form-control shadow-sm bg-body" id="cccd_issued_date" name="cccd_issued_date" value="<?php echo esc_attr($employee->cccd_issued_date); ?>">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="bank_account" class="form-label">Số tài khoản</label>
                    <input type="text" class="form-control shadow-sm" id="bank_account" name="bank_account" value="<?php echo esc_attr($employee->bank_account); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="bank_name" class="form-label">Tên ngân hàng</label>
                    <input type="text" class="form-control shadow-sm" id="bank_name" name="bank_name" value="<?php echo esc_attr($employee->bank_name); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="phone_number" class="form-label">Số điện thoại</label>
                    <input type="text" class="form-control shadow-sm" id="phone_number" name="phone_number" value="<?php echo esc_attr($employee->phone_number); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control shadow-sm" id="email" name="email" value="<?php echo esc_attr($employee->email); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="address_permanent" class="form-label">Địa chỉ thường trú</label>
                    <textarea rows="1" class="form-control shadow-sm" id="address_permanent" name="address_permanent"><?php echo esc_textarea($employee->address_permanent); ?></textarea>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="address_current" class="form-label">Địa chỉ hiện tại</label>
                    <textarea rows="1" class="form-control shadow-sm" id="address_current" name="address_current"><?php echo esc_textarea($employee->address_current); ?></textarea>
                </div>
                <h5>2. Thân nhân</h5>
                <div class="col-md-3 mb-3">
                    <label for="relative_name" class="form-label">Họ tên người thân</label>
                    <input type="text" class="form-control shadow-sm" id="relative_name" name="relative_name" value="<?php echo esc_attr($employee->relative_name); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="relative_phone" class="form-label">Số điện thoại người thân</label>
                    <input type="text" class="form-control shadow-sm" id="relative_phone" name="relative_phone" value="<?php echo esc_attr($employee->relative_phone); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="relative_relationship" class="form-label">Quan hệ</label>
                    <input type="text" class="form-control shadow-sm" id="relative_relationship" name="relative_relationship" value="<?php echo esc_attr($employee->relative_relationship); ?>">
                </div>
                <h5>3. Công việc</h5>
                <div class="col-md-3 mb-3">
                    <label for="department_id" class="form-label">Phòng ban</label>
                    <select class="form-select shadow-sm" id="department_id" name="department_id">
                        <?php
                        $departments = apply_filters('aerp_get_departments', []);
                        aerp_safe_select_options($departments, $employee->department_id, 'id', 'name', true);
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="position_id" class="form-label">Chức vụ</label>
                    <select class="form-select shadow-sm" id="position_id" name="position_id">
                        <?php
                        $positions = apply_filters('aerp_get_positions', []);
                        aerp_safe_select_options($positions, $employee->position_id, 'id', 'name', true);
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="work_location_id" class="form-label">Chi nhánh</label>
                    <select class="form-select shadow-sm" id="work_location_id" name="work_location_id">
                        <?php
                        $work_locations = apply_filters('aerp_get_work_locations', []);
                        aerp_safe_select_options($work_locations, $employee->work_location_id, 'id', 'name', true);
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select shadow-sm" id="status" name="status">
                        <option value="active" <?php selected($employee->status, 'active'); ?>>Đang làm</option>
                        <option value="inactive" <?php selected($employee->status, 'inactive'); ?>>Tạm nghỉ</option>
                        <option value="resigned" <?php selected($employee->status, 'resigned'); ?>>Đã nghỉ</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="join_date" class="form-label">Ngày vào làm</label>
                    <input type="date" class="form-control shadow-sm bg-body" id="join_date" name="join_date" value="<?php echo esc_attr($employee->join_date); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="off_date" class="form-label">Ngày nghỉ việc</label>
                    <input type="date" class="form-control shadow-sm bg-body" id="off_date" name="off_date" value="<?php echo esc_attr($employee->off_date); ?>">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="user_id" class="form-label">User WordPress</label>
                    <select class="form-select shadow-sm" id="user_id" name="user_id">
                        <option value="0">Không liên kết</option>
                        <?php
                        $users = get_users();
                        foreach ($users as $user) {
                            $wp_user_roles = array_map(function ($role) {
                                return translate_user_role(wp_roles()->roles[$role]['name']);
                            }, $user->roles);
                            $role_display = !empty($wp_user_roles) ? ' (' . implode(', ', $wp_user_roles) . ')' : '';
                            $selected = $employee->user_id == $user->ID ? 'selected' : '';
                            echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name . ' - ' . $user->user_email . $role_display) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <h5>4. Phân quyền</h5>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Nhóm quyền</label>
                    <div class="aerp-perm-group">
                        <?php foreach ($all_roles as $role): ?>
                            <?php $is_checked = in_array((string)$role->id, array_map('strval', $user_roles)); ?>
                            <label style="display:block;margin-bottom:4px;">
                                <input type="checkbox" class="role-checkbox" data-role-id="<?= esc_attr($role->id) ?>" name="user_roles[]" value="<?= esc_attr($role->id) ?>" id="role-<?= esc_attr($role->name) ?>" <?= $is_checked ? 'checked' : '' ?>>
                                <?= esc_html($role->name) ?><?php if ($role->description) echo ' - ' . esc_html($role->description); ?>
                            </label>
                            <?php if ($role->name === 'department_lead'): ?>
                                <div id="select-department-lead" style="display: <?= $is_checked ? 'block' : 'none' ?>; margin: 8px 0 0 24px;">
                                    <label>Chọn phòng ban quản lý:</label>
                                    <select name="department_lead_department_id">
                                        <option value="">-- Chọn phòng ban --</option>
                                        <?php foreach (apply_filters('aerp_get_departments', []) as $dep): ?>
                                            <option value="<?= esc_attr($dep->id) ?>" <?= ($dep->manager_id == $data['user_id']) ? 'selected' : '' ?>>
                                                <?= esc_html($dep->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
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
                                                <span class="perm-via-role" style="color:#888;font-weight:bold;  font-size:12px;">(Đã có qua nhóm quyền)</span>
                                            <?php endif; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label for="note" class="form-label">Ghi chú</label>
                    <textarea class="form-control shadow-sm" id="note" name="note" rows="2"><?php echo esc_textarea($employee->note); ?></textarea>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_employee" class="btn btn-primary">Cập nhật</button>
                <a href="<?php echo home_url('/aerp-hrm-employees'); ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<script>
    var rolePermissionsMap = <?= json_encode($role_permissions_map) ?>;
    document.addEventListener('DOMContentLoaded', function() {
        var leadCheckbox = document.getElementById('role-department_lead');
        var selectDiv = document.getElementById('select-department-lead');
        if (leadCheckbox && selectDiv) {
            selectDiv.style.display = leadCheckbox.checked ? 'block' : 'none';
            leadCheckbox.addEventListener('change', function() {
                selectDiv.style.display = this.checked ? 'block' : 'none';
            });
        }
    });
</script>
<?php
$content = ob_get_clean();
$title = 'Cập nhật nhân viên';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
