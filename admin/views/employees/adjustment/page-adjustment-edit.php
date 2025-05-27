<?php
if (!defined('ABSPATH')) exit;

$id = absint($_GET['id'] ?? 0);
$adjustment = AERP_Adjustment_Manager::get_by_id($id);

if (!$adjustment) {
    echo '<div class="notice notice-error"><p>❌ Không tìm thấy bản ghi điều chỉnh.</p></div>';
    return;
}

$employee_id = $adjustment->employee_id; // ✅ Lấy trực tiếp từ bản ghi

?>

<div class="wrap">
    <h1 class="wp-heading-inline">✏️ Sửa điều chỉnh</h1>
    <a href="<?= admin_url('admin.php?page=aerp_employees&view=' . $employee_id . '#adjustment') ?>" class="page-title-action">← Quay lại nhân viên</a>

    <form method="post">
        <?php wp_nonce_field('aerp_edit_adjustment_action', 'aerp_edit_adjustment_nonce'); ?>
        <input type="hidden" name="id" value="<?= esc_attr($adjustment->id) ?>">
        <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">

        <table class="form-table">
            <tr>
                <th><label>Lí do</label></th>
                <td><input type="text" name="reason" value="<?= esc_attr($adjustment->reason) ?>" required></td>
            </tr>
            <tr>
                <th><label>Ngày áp dụng</label></th>
                <td><input type="date" name="date_effective" value="<?= esc_attr($adjustment->date_effective) ?>" required></td>
            </tr>
            <tr>
                <th><label>Loại</label></th>
                <td>
                    <select name="type" required>
                        <option value="reward" <?= selected($adjustment->type, 'reward') ?>>🎁 Thưởng</option>
                        <option value="fine" <?= selected($adjustment->type, 'fine') ?>>⚠️ Phạt</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label>Số tiền</label></th>
                <td><input type="number" name="amount" value="<?= esc_attr($adjustment->amount) ?>" required></td>
            </tr>
            <tr>
                <th><label>Ghi chú</label></th>
                <td><input type="text" name="description" value="<?= esc_attr($adjustment->description) ?>"></td>
            </tr>
        </table>
        <p><button type="submit" name="aerp_update_adjustment" class="button button-primary">Lưu thay đổi</button></p>
    </form>
</div>