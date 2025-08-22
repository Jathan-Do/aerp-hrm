<?php
if (!defined('ABSPATH')) {
    exit;
}
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

ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Thêm xếp loại mới</h2>
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
        ['label' => 'Quản lý xếp loại nhân sự', 'url' => home_url('/aerp-ranking-settings')],
        ['label' => 'Thêm xếp loại mới']
    ]);
}
?>
<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_ranking_setting_action', 'aerp_save_ranking_setting_nonce'); ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="rank_code" class="form-label">Xếp loại</label>
                    <input type="text" class="form-control" id="rank_code" name="rank_code" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="min_point" class="form-label">Từ điểm</label>
                    <input type="number" class="form-control" id="min_point" name="min_point" min="0" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="sort_order" class="form-label">Thứ tự</label>
                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="note" class="form-label">Ghi chú</label>
                    <input type="text" class="form-control" id="note" name="note">
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_ranking_setting" class="btn btn-primary">Thêm mới</button>
                <a href="<?php echo esc_url(home_url('/aerp-ranking-settings/')); ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Thêm xếp loại mới';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
