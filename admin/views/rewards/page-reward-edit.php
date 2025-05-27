<?php
if (!defined('ABSPATH')) exit;
global $wpdb;

$id = absint($_GET['edit'] ?? 0);
$reward = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_reward_definitions WHERE id = %d", $id));

if (!$reward) {
    echo '<div class="notice notice-error"><p>❌ Không tìm thấy bản ghi thưởng.</p></div>';
    return;
}

// Xử lý cập nhật
if (
    isset($_POST['aerp_update_reward']) &&
    check_admin_referer('aerp_edit_reward_action', 'aerp_edit_reward_nonce')
) {
    $name   = sanitize_text_field($_POST['name']);
    $amount = floatval($_POST['amount']);
    $type   = sanitize_text_field($_POST['trigger_type']);
    $custom = sanitize_text_field($_POST['custom_trigger_type']);
    $trigger_type = ($type === 'manual' && $custom) ? $custom : $type;

    $day_trigger = !empty($_POST['day_trigger']) ? sanitize_text_field($_POST['day_trigger']) : null;

    $wpdb->update($wpdb->prefix . 'aerp_hrm_reward_definitions', [
        'name'         => $name,
        'amount'       => $amount,
        'trigger_type' => $trigger_type,
        'day_trigger'  => $day_trigger,
    ], ['id' => $id]);

    aerp_js_redirect(admin_url('admin.php?page=aerp_reward_settings&edit_noti=success'));
}

// Detect nếu trigger là manual để show input
$is_custom = !in_array($reward->trigger_type, ['birthday', 'holiday', 'seniority']);
?>

<div class="wrap">
    <h1>Sửa Mục Thưởng: <?= esc_html($reward->name) ?></h1>

    <form method="post">
        <?php wp_nonce_field('aerp_edit_reward_action', 'aerp_edit_reward_nonce'); ?>

        <table class="form-table">
            <tr>
                <th><label for="name">Tên thưởng</label></th>
                <td><input type="text" name="name" value="<?= esc_attr($reward->name) ?>" required></td>
            </tr>

            <tr>
                <th><label for="amount">Số tiền (VNĐ)</label></th>
                <td><input type="number" name="amount" value="<?= esc_attr($reward->amount) ?>" required></td>
            </tr>

            <tr>
                <th><label for="trigger_type">Loại kích hoạt</label></th>
                <td>
                    <select name="trigger_type" id="trigger_type" onchange="toggleCustomTrigger(this.value)">
                        <option value="">-- Chọn loại --</option>
                        <option value="birthday" <?= selected($reward->trigger_type, 'birthday') ?>>🎂 Sinh nhật</option>
                        <option value="holiday" <?= selected($reward->trigger_type, 'holiday') ?>>🎉 Lễ/Tết</option>
                        <option value="seniority" <?= selected($reward->trigger_type, 'seniority') ?>>🏆 Thâm niên</option>
                        <option value="manual" <?= $is_custom ? 'selected' : '' ?>>✍️ Khác...</option>
                    </select>
                    <div id="custom_trigger_wrapper" style="margin-top:8px; <?= $is_custom ? '' : 'display:none;' ?>">
                        <input type="text" name="custom_trigger_type" value="<?= $is_custom ? esc_attr($reward->trigger_type) : '' ?>" placeholder="Nhập loại tùy chỉnh">
                    </div>
                </td>
            </tr>

            <tr>
                <th><label for="day_trigger">Ngày áp dụng</label></th>
                <td><input type="date" name="day_trigger" value="<?= esc_attr($reward->day_trigger) ?>"></td>
            </tr>
        </table>

        <p>
            <button type="submit" name="aerp_update_reward" class="button button-primary">💾 Lưu thay đổi</button>
            <a href="<?= admin_url('admin.php?page=aerp_reward_settings') ?>" class="button">← Quay lại</a>
        </p>
    </form>
</div>

<script>
    function toggleCustomTrigger(val) {
        document.getElementById('custom_trigger_wrapper').style.display = (val === 'manual') ? 'block' : 'none';
    }
    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomTrigger(document.getElementById('trigger_type').value);
    });
</script>