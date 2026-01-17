<?php
if (!defined('ABSPATH')) exit;

// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$employee = aerp_get_employee_by_user_id($user_id);
$user_fullname = $employee ? $employee->full_name : '';

if (!is_user_logged_in()) {
    wp_die(__('You must be logged in to access this page.'));
}

// Danh sách điều kiện, chỉ cần 1 cái đúng là qua
$access_conditions = [
    aerp_user_has_role($user_id, 'admin'),
    aerp_user_has_role($user_id, 'department_lead'),
    aerp_user_has_role($user_id, 'accountant'),
    aerp_user_has_permission($user_id, 'salary_edit'),
    aerp_user_has_permission($user_id, 'salary_add'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

$employee_id = absint($_GET['id'] ?? 0);
$employee = AERP_Employee_Manager::get_by_id($employee_id);
if (!$employee) {
    wp_die(__('Nhân viên không tồn tại.'));
}

$edit_id = absint($_GET['config_id'] ?? 0);
$config = $edit_id ? AERP_Frontend_Salary_Config_Manager::get_by_id($edit_id) : null;

ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-5">
    <h2><?= $edit_id ? 'Sửa cấu hình lương' : 'Thêm cấu hình lương' ?></h2>
    <div class="user-info text-end">
        Hi, <?php echo esc_html($user_fullname); ?>
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
        ['label' => 'Chi tiết nhân viên', 'url' => home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=salary')],
        ['label' => 'Cấu hình lương', 'url' => home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=salary_config')],
        ['label' => ($edit_id ? 'Sửa cấu hình lương' : 'Thêm cấu hình lương')],
    ]);
}
?>
<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_salary_config_action', 'aerp_salary_nonce'); ?>
            <input type="hidden" name="employee_id" value="<?= esc_attr($employee->id) ?>">
            <?php if ($edit_id): ?>
                <input type="hidden" name="config_id" value="<?= esc_attr($edit_id) ?>">
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label">Nhân viên</label>
                <input type="text" class="form-control shadow-sm" value="<?= esc_html($employee->full_name) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label" for="salary_mode">Kiểu tính lương</label>
                <select class="form-select shadow-sm" name="salary_mode" id="salary_mode">
                    <?php
                    $mode = $config->salary_mode ?? 'fixed';
                    $options = [
                        'fixed' => 'Lương cứng',
                        'piecework' => 'Lương khoán (hoa hồng lợi nhuận)',
                        'both' => 'Cả hai (cứng + khoán)'
                    ];
                    foreach ($options as $val => $label) {
                        printf('<option value="%s" %s>%s</option>', esc_attr($val), selected($mode, $val, false), esc_html($label));
                    }
                    ?>
                </select>
            </div>
            <?php $mode_current = $config->salary_mode ?? 'fixed'; $show_fixed_group = in_array($mode_current, ['fixed','both'], true); ?>
            <div id="fixed-fields-group" style="display:<?= $show_fixed_group ? 'block' : 'none' ?>;">
                <div class="mb-3">
                    <label class="form-label" for="start_date">Từ ngày</label>
                    <input type="date" class="form-control shadow-sm bg-body" name="start_date" id="start_date" value="<?= esc_attr($config->start_date ?? '') ?>" <?= $show_fixed_group ? 'required' : '' ?>>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="end_date">Đến ngày</label>
                    <input type="date" class="form-control shadow-sm bg-body" name="end_date" id="end_date" value="<?= esc_attr($config->end_date ?? '') ?>" <?= $show_fixed_group ? 'required' : '' ?>>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="base_salary">Lương cơ bản</label>
                    <input type="number" class="form-control shadow-sm" name="base_salary" step="1000" value="<?= esc_attr($config->base_salary ?? '') ?>" <?= $show_fixed_group ? 'required' : '' ?>>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="allowance">Phụ cấp</label>
                    <input type="number" class="form-control shadow-sm" name="allowance" step="1000" value="<?= esc_attr($config->allowance ?? '') ?>" <?= $show_fixed_group ? 'required' : '' ?>>
                </div>
            </div>
            <?php $show_commission = in_array(($config->salary_mode ?? 'fixed'), ['piecework','both'], true); ?>
            <div class="mb-3" id="commission-settings-group" style="display:<?= $show_commission ? 'block' : 'none' ?>;">
                <label class="form-label" for="commission_scheme_id">Danh mục % lợi nhuận</label>
                <select class="form-select shadow-sm" name="commission_scheme_id" id="commission_scheme_id">
                    <option value="">-- Chọn danh mục --</option>
                    <?php
                    global $wpdb;
                    $schemes = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}aerp_hrm_commission_schemes ORDER BY id DESC");
                    $selected_scheme = intval($config->commission_scheme_id ?? 0);
                    foreach ($schemes as $s) {
                        printf('<option value="%d" %s>%s</option>', $s->id, selected($selected_scheme, $s->id, false), esc_html($s->name));
                    }
                    ?>
                </select>
                <small class="text-muted">Áp dụng khi chọn lương khoán hoặc cả hai.</small>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_salary_config" class="btn btn-primary">
                    <?= $edit_id ? 'Cập nhật' : 'Thêm mới' ?>
                </button>
                <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=salary') ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<script>
    (function(){
        var modeSelect = document.getElementById('salary_mode');
        var fixedGroup = document.getElementById('fixed-fields-group');
        var commissionGroup = document.getElementById('commission-settings-group');
        function setRequired(group, enable){
            if (!group) return;
            var inputs = group.querySelectorAll('input, select, textarea');
            inputs.forEach(function(el){
                if (enable) { el.setAttribute('required','required'); }
                else { el.removeAttribute('required'); }
            });
        }
        function updateVisibility(){
            var v = modeSelect ? modeSelect.value : 'fixed';
            if (v === 'piecework') {
                if (fixedGroup) fixedGroup.style.display = 'none';
                if (commissionGroup) commissionGroup.style.display = 'block';
                setRequired(fixedGroup, false);
            } else if (v === 'fixed') {
                if (fixedGroup) fixedGroup.style.display = 'block';
                if (commissionGroup) commissionGroup.style.display = 'none';
                setRequired(fixedGroup, true);
                var sel = document.getElementById('commission_scheme_id');
                if (sel) sel.value = '';
            } else { // both
                if (fixedGroup) fixedGroup.style.display = 'block';
                if (commissionGroup) commissionGroup.style.display = 'block';
                setRequired(fixedGroup, true);
            }
        }
        if (modeSelect) {
            modeSelect.addEventListener('change', updateVisibility);
            updateVisibility();
        }
    })();
</script>
<?php
$content = ob_get_clean();
$title = $edit_id ? 'Sửa cấu hình lương' : 'Thêm cấu hình lương';
include AERP_HRM_PATH . 'frontend/dashboard/layout.php';
