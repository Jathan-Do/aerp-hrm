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
    aerp_user_has_permission($user_id, 'disciplinary_add'),
    aerp_user_has_permission($user_id, 'disciplinary_edit'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
global $wpdb;
$rules = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_disciplinary_rules ORDER BY rule_name");
$employee_id = absint($_GET['id'] ?? 0);
$employee = AERP_Employee_Manager::get_by_id($employee_id);
$today = date('Y-m-d');
ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-5">
    <h2>Ghi nhận vi phạm</h2>
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
        ['label' => 'Chi tiết nhân viên', 'url' => home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=discipline')],
        ['label' => 'Ghi nhận vi phạm']
    ]);
}
?>
<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_discipline_log_action', 'aerp_save_discipline_log_nonce'); ?>
            <input name="employee_id" value="<?= esc_attr($employee_id) ?>" hidden>
            <div class="mb-3">
                <label class="form-label">Nhân viên</label>
                <input type="text" class="form-control shadow-sm" value="<?= esc_html($employee->full_name) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Lý do vi phạm</label>
                <select class="form-select shadow-sm" name="rule_id" required>
                    <option value="">-- Chọn lý do --</option>
                    <?php foreach ($rules as $r): ?>
                        <option value="<?= esc_attr($r->id) ?>">
                            <?= esc_html($r->rule_name) ?> (–<?= $r->penalty_point ?>đ, –<?= number_format($r->fine_amount, 0, ',', '.') ?>đ)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Ngày vi phạm</label>
                <input type="date" class="form-control shadow-sm bg-body" name="date_violation" value="<?= esc_attr($today) ?>" required>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_discipline_log" class="btn btn-primary">
                    Lưu vi phạm
                </button>
                <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '#discipline') ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Ghi nhận vi phạm';
include AERP_HRM_PATH . 'frontend/dashboard/layout.php';
