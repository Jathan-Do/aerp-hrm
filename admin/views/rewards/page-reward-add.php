<?php
if (!defined('ABSPATH')) exit;
global $wpdb;

if (
    isset($_POST['aerp_add_reward']) &&
    check_admin_referer('aerp_add_reward_action', 'aerp_add_reward_nonce')
) {
    global $wpdb;

    $name   = sanitize_text_field($_POST['name']);
    $amount = floatval($_POST['amount']);
    $type   = sanitize_text_field($_POST['trigger_type']);
    $custom = sanitize_text_field($_POST['custom_trigger_type']);
    $trigger_type = ($type === 'manual' && $custom) ? $custom : $type;

    $day_trigger = !empty($_POST['day_trigger']) ? sanitize_text_field($_POST['day_trigger']) : null;

    $wpdb->insert($wpdb->prefix . 'aerp_hrm_reward_definitions', [
        'name'         => $name,
        'amount'       => $amount,
        'trigger_type' => $trigger_type,
        'day_trigger'  => $day_trigger,
        'created_at'   => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
    ]);

    aerp_js_redirect(admin_url('admin.php?page=aerp_reward_settings&add_noti=success'));
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Thêm Mục Thưởng</h1>
    <a href="<?= admin_url('admin.php?page=aerp_reward_settings') ?>" class="page-title-action">← Quay lại danh sách</a>
    <form method="post">
        <?php wp_nonce_field('aerp_add_reward_action', 'aerp_add_reward_nonce'); ?>

        <table class="form-table">
            <tr>
                <th><label for="name">Tên thưởng</label></th>
                <td><input type="text" name="name" required></td>
            </tr>

            <tr>
                <th><label for="amount">Số tiền (VNĐ)</label></th>
                <td><input type="number" name="amount" required></td>
            </tr>

            <tr>
                <th><label for="trigger_type">Loại kích hoạt</label></th>
                <td>
                    <select name="trigger_type" id="trigger_type" onchange="toggleCustomTrigger(this.value)">
                        <option value="">-- Chọn loại --</option>
                        <option value="birthday">🎂 Sinh nhật</option>
                        <option value="holiday">🎉 Lễ/Tết</option>
                        <option value="seniority">🏆 Thâm niên</option>
                        <option value="manual">✍️ Khác...</option>
                    </select>
                    <div id="custom_trigger_wrapper" style="margin-top: 8px; display: none;">
                        <input type="text" name="custom_trigger_type" placeholder="Nhập loại tùy chỉnh">
                    </div>
                </td>
            </tr>

            <tr>
                <th><label for="day_trigger">Ngày áp dụng (nếu có)</label></th>
                <td><input type="date" name="day_trigger"></td>
            </tr>
        </table>

        <p><button type="submit" name="aerp_add_reward" class="button button-primary">Lưu</button></p>
    </form>
</div>

<script>
    function toggleCustomTrigger(val) {
        document.getElementById('custom_trigger_wrapper').style.display = (val === 'manual') ? 'block' : 'none';
    }
</script>