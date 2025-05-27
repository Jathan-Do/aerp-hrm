<?php
if (!defined('ABSPATH')) exit;
global $wpdb;
$employee_id = absint($_GET['employee_id'] ?? 0);

$rewards = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_reward_definitions ORDER BY name ASC");
$month   = date('Y-m');

if (
    isset($_POST['aerp_add_employee_reward']) &&
    check_admin_referer('aerp_add_employee_reward_action', 'aerp_add_employee_reward_nonce')
) {
    $wpdb->insert("{$wpdb->prefix}aerp_hrm_employee_rewards", [
        'employee_id' => $employee_id,
        'reward_id'   => absint($_POST['reward_id']),
        'month'       => sanitize_text_field($_POST['month']) . '-01',
        'note'        => sanitize_text_field($_POST['note']),
        'created_at'  => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
    ]);

    echo '<div class="notice notice-success"><p>✅ Đã thêm thưởng cho nhân viên.</p></div>';
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Thêm thưởng cho nhân viên</h1>
    <a href="<?= admin_url('admin.php?page=aerp_employees&view=' . $employee_id . '#rewards') ?>" class="page-title-action">← Quay lại bảng</a>
    <form method="post">
        <?php wp_nonce_field('aerp_add_employee_reward_action', 'aerp_add_employee_reward_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label>Tháng</label></th>
                <td><input type="month" name="month" value="<?= esc_attr($month) ?>" required></td>
            </tr>
            <tr>
                <th><label>Chọn thưởng</label></th>
                <td>
                    <select name="reward_id" required>
                        <?php foreach ($rewards as $r): ?>
                            <option value="<?= esc_attr($r->id) ?>">
                                <?= esc_html($r->name) ?> – <?= number_format($r->amount, 0, ',', '.') ?> đ
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label>Ghi chú</label></th>
                <td><input type="text" name="note"></td>
            </tr>
        </table>
        <p><button type="submit" name="aerp_add_employee_reward" class="button button-primary">Lưu</button></p>
    </form>
</div>