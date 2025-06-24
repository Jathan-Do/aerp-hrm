<?php
if (!defined('ABSPATH')) {
    exit;
}
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
// Check if user is logged in and has admin capabilities
if (!is_user_logged_in() || !aerp_user_has_role($user_id, 'admin')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Thêm quy tắc kỷ luật mới</h2>
    <div class="user-info text-end">
        Welcome, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_discipline_rule_action', 'aerp_save_discipline_rule_nonce'); ?>
            <div class="mb-3">
                <label for="rule_name" class="form-label">Tên quy tắc</label>
                <input type="text" class="form-control" id="rule_name" name="rule_name" required>
            </div>
            <div class="mb-3">
                <label for="penalty_point" class="form-label">Điểm phạt</label>
                <input type="number" class="form-control" id="penalty_point" name="penalty_point" min="0" value="0">
            </div>
            <div class="mb-3">
                <label for="fine_amount" class="form-label">Tiền phạt</label>
                <input type="number" class="form-control" id="fine_amount" name="fine_amount" min="0" value="0">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_discipline_rule" class="btn btn-primary">Thêm mới</button>
                <a href="<?php echo esc_url(home_url('/aerp-discipline-rule/')); ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Thêm quy tắc kỷ luật mới';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php'); 