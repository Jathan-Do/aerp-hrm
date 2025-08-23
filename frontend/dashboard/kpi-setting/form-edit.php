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
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
$edit_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
$editing = AERP_Frontend_KPI_Settings_Manager::get_by_id($edit_id);
if (!$editing) {
    wp_die(__('KPI setting not found.'));
}
ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Cập nhật mốc thưởng KPI</h2>
    <div class="user-info text-end">
        Welcome, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
<?php
if (function_exists('aerp_render_breadcrumb')) {
    aerp_render_breadcrumb([
        ['label' => 'Trang chủ', 'url' => home_url('/aerp-dashboard'), 'icon' => 'fas fa-home'],
        ['label' => 'Danh mục', 'url' => home_url('/aerp-categories')],
        ['label' => 'Quản lý mốc thưởng KPI', 'url' => home_url('/aerp-kpi-settings')],
        ['label' => 'Cập nhật mốc thưởng KPI']
    ]);
}
?>
<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_kpi_setting_action', 'aerp_save_kpi_setting_nonce'); ?>
            <input type="hidden" name="kpi_id" value="<?php echo esc_attr($edit_id); ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="min_score" class="form-label">Từ điểm</label>
                    <input type="number" class="form-control shadow-sm" id="min_score" name="min_score" value="<?php echo esc_attr($editing->min_score); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="reward_amount" class="form-label">Tiền thưởng</label>
                    <input type="number" class="form-control shadow-sm" id="reward_amount" name="reward_amount" value="<?php echo esc_attr($editing->reward_amount); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="note" class="form-label">Ghi chú</label>
                    <input type="text" class="form-control shadow-sm" id="note" name="note" value="<?php echo esc_attr($editing->note); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="sort_order" class="form-label">Thứ tự</label>
                    <input type="number" class="form-control shadow-sm" id="sort_order" name="sort_order" value="<?php echo esc_attr($editing->sort_order); ?>">
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_kpi_setting" class="btn btn-primary">Cập nhật</button>
                <a href="<?php echo esc_url(home_url('/aerp-kpi-settings/')); ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Cập nhật mốc thưởng KPI';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
