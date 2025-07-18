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
    <h2>Thêm thưởng động mới</h2>
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
            <?php wp_nonce_field('aerp_save_reward_action', 'aerp_save_reward_nonce'); ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Tên thưởng</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="amount" class="form-label">Số tiền (VNĐ)</label>
                    <input type="number" class="form-control" id="amount" name="amount" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="trigger_type" class="form-label">Loại kích hoạt</label>
                    <select name="trigger_type" id="trigger_type" class="form-select" onchange="toggleCustomTrigger(this.value)">
                        <option value="">-- Chọn loại --</option>
                        <option value="birthday">🎂 Sinh nhật</option>
                        <option value="holiday">🎉 Lễ/Tết</option>
                        <option value="seniority">🏆 Thâm niên</option>
                        <option value="manual">✍️ Khác...</option>
                    </select>
                    <div id="custom_trigger_wrapper" style="margin-top:8px; display:none;">
                        <input type="text" name="custom_trigger_type" class="form-control" placeholder="Nhập loại tùy chỉnh">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="day_trigger" class="form-label">Ngày áp dụng (nếu có)</label>
                    <input type="date" class="form-control" id="day_trigger" name="day_trigger">
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_reward" class="btn btn-primary">Thêm mới</button>
                <a href="<?php echo esc_url(home_url('/aerp-reward-settings/')); ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<script>
    function toggleCustomTrigger(val) {
        document.getElementById('custom_trigger_wrapper').style.display = (val === 'manual') ? 'block' : 'none';
    }
</script>
<?php
$content = ob_get_clean();
$title = 'Thêm thưởng động mới';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
