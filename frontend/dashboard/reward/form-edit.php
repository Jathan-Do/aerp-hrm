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

$edit_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
$editing = AERP_Frontend_Reward_Manager::get_by_id($edit_id);

if (!$editing) {
    wp_die(__('Reward not found.'));
}

$is_custom = !in_array($editing->trigger_type, ['birthday', 'holiday', 'seniority']);

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Cập nhật thưởng động</h2>
    <div class="user-info">
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
            <input type="hidden" name="reward_id" value="<?php echo esc_attr($edit_id); ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Tên thưởng</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo esc_attr($editing->name); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="amount" class="form-label">Số tiền (VNĐ)</label>
                    <input type="number" class="form-control" id="amount" name="amount" value="<?php echo esc_attr($editing->amount); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="trigger_type" class="form-label">Loại kích hoạt</label>
                    <select name="trigger_type" id="trigger_type" class="form-select" onchange="toggleCustomTrigger(this.value)">
                        <option value="">-- Chọn loại --</option>
                        <option value="birthday" <?php selected($editing->trigger_type, 'birthday'); ?>>🎂 Sinh nhật</option>
                        <option value="holiday" <?php selected($editing->trigger_type, 'holiday'); ?>>🎉 Lễ/Tết</option>
                        <option value="seniority" <?php selected($editing->trigger_type, 'seniority'); ?>>🏆 Thâm niên</option>
                        <option value="manual" <?php echo $is_custom ? 'selected' : ''; ?>>✍️ Khác...</option>
                    </select>
                    <div id="custom_trigger_wrapper" style="margin-top:8px; <?php echo $is_custom ? '' : 'display:none;'; ?>">
                        <input type="text" name="custom_trigger_type" class="form-control" value="<?php echo $is_custom ? esc_attr($editing->trigger_type) : ''; ?>" placeholder="Nhập loại tùy chỉnh">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="day_trigger" class="form-label">Ngày áp dụng (nếu có)</label>
                    <input type="date" class="form-control" id="day_trigger" name="day_trigger" value="<?php echo esc_attr($editing->day_trigger); ?>">
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="aerp_save_reward" class="btn btn-primary">Cập nhật</button>
                <a href="<?php echo esc_url(home_url('/aerp-reward-settings/')); ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
<script>
    function toggleCustomTrigger(val) {
        document.getElementById('custom_trigger_wrapper').style.display = (val === 'manual') ? 'block' : 'none';
    }
    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomTrigger(document.getElementById('trigger_type').value);
    });
</script>
<?php
$content = ob_get_clean();
$title = 'Cập nhật thưởng động';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
