<?php
if (!defined('ABSPATH')) {
    exit;
}
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
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

$edit_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
$editing = AERP_Frontend_Discipline_Rule_Manager::get_by_id($edit_id);

if (!$editing) {
    wp_die(__('Discipline rule not found.'));
}

ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-5">
    <h2>Cập nhật quy tắc kỷ luật</h2>
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
        ['label' => 'Danh mục', 'url' => home_url('/aerp-categories')],
        ['label' => 'Quản lý quy tắc kỷ luật', 'url' => home_url('/aerp-discipline-rule')],
        ['label' => 'Cập nhật quy tắc kỷ luật']
    ]);
}
?>
<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_discipline_rule_action', 'aerp_save_discipline_rule_nonce'); ?>
            <input type="hidden" name="rule_id" value="<?php echo esc_attr($edit_id); ?>">
            <div class="mb-3">
                <label for="rule_name" class="form-label">Tên quy tắc</label>
                <input type="text" class="form-control shadow-sm" id="rule_name" name="rule_name" value="<?php echo esc_attr($editing->rule_name); ?>" required>
            </div>
            <div class="mb-3">
                <label for="penalty_point" class="form-label">Điểm phạt</label>
                <input type="number" class="form-control shadow-sm" id="penalty_point" name="penalty_point" min="0" value="<?php echo esc_attr($editing->penalty_point); ?>">
            </div>
            <div class="mb-3">
                <label for="fine_amount" class="form-label">Tiền phạt</label>
                <input type="number" class="form-control shadow-sm" id="fine_amount" name="fine_amount" min="0" value="<?php echo esc_attr($editing->fine_amount); ?>">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_discipline_rule" class="btn btn-primary">Cập nhật</button>
                <a href="<?php echo esc_url(home_url('/aerp-discipline-rule/')); ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Cập nhật quy tắc kỷ luật';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php'); 