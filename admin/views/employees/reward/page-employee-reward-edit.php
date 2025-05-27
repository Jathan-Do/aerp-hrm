<?php
if (!defined('ABSPATH')) exit;
global $wpdb;

$id = absint($_GET['edit'] ?? 0);
$row = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}aerp_hrm_employee_rewards WHERE id = %d",
    $id
));

if (!$row) {
    echo '<div class="notice notice-error"><p>❌ Không tìm thấy dữ liệu thưởng nhân viên.</p></div>';
    return;
}

$employee_id = $row->employee_id;
$rewards = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_reward_definitions ORDER BY name ASC");

if (
    isset($_POST['aerp_update_employee_reward']) &&
    check_admin_referer('aerp_edit_employee_reward_action', 'aerp_edit_employee_reward_nonce')
) {
    $wpdb->update(
        $wpdb->prefix . 'aerp_hrm_employee_rewards',
        [
            'reward_id' => absint($_POST['reward_id']),
            'month'     => sanitize_text_field($_POST['month']) . '-01',
            'note'      => sanitize_text_field($_POST['note']),
        ],
        ['id' => $id]
    );

    echo '<div class="notice notice-success"><p>✅ Đã cập nhật thưởng cho nhân viên.</p></div>';

    // Reload row
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}aerp_hrm_employee_rewards WHERE id = %d",
        $id
    ));
}

?>

<div class="wrap">
    <h1 class="wp-heading-inline">✏️ Cập nhật Thưởng cho nhân viên</h1>
    <a href="<?= admin_url('admin.php?page=aerp_employees&view=' . $employee_id . '#rewards') ?>" class="page-title-action">← Quay lại bảng</a>
    <form method="post">
        <?php wp_nonce_field('aerp_edit_employee_reward_action', 'aerp_edit_employee_reward_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="month">Tháng áp dụng</label></th>
                <td>
                    <?php
                    $month = $row->month ? date('Y-m', strtotime($row->month)) : date('Y-m');
                    ?>
                    <input type="month" name="month" value="<?= esc_attr($month) ?>" required>
                </td>
            </tr>

            <tr>
                <th><label for="reward_id">Chọn thưởng</label></th>
                <td>
                    <select name="reward_id" required>
                        <?php foreach ($rewards as $r): ?>
                            <option value="<?= esc_attr($r->id) ?>" <?= selected($r->id, $row->reward_id) ?>>
                                <?= esc_html($r->name) ?> – <?= number_format($r->amount, 0, ',', '.') ?> đ
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th><label for="note">Ghi chú</label></th>
                <td><input type="text" name="note" value="<?= esc_attr($row->note) ?>"></td>
            </tr>
        </table>

        <p><button type="submit" name="aerp_update_employee_reward" class="button button-primary">Lưu thay đổi</button></p>
    </form>
</div>
