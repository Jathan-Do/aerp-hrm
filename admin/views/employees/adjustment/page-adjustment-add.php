<?php
if (!defined('ABSPATH')) exit;
$employee_id = absint($_GET['employee_id'] ?? 0);
$today = date('Y-m-d');
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Điều chỉnh thưởng/phạt</h1>
    <a href="<?= admin_url('admin.php?page=aerp_employees&view=' . $employee_id . '#adjustment') ?>" class="page-title-action">← Quay lại nhân viên</a>

    <form method="post">
        <?php wp_nonce_field('aerp_add_adjustment_action', 'aerp_add_adjustment_nonce'); ?>
        <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">
        <table class="form-table">
            <tr>
                <th><label>Lí do</label></th>
                <td><input type="text" name="reason" required></td>
            </tr>
            <tr>
                <th><label>Ngày áp dụng</label></th>
                <td><input type="date" name="date_effective" value="<?= esc_attr($today) ?>" required></td>
            </tr>
            <tr>
                <th><label>Loại</label></th>
                <td>
                    <select name="type" required>
                        <option value="reward">🎁 Thưởng</option>
                        <option value="fine">⚠️ Phạt</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label>Số tiền</label></th>
                <td><input type="number" name="amount" required></td>
            </tr>
            <tr>
                <th><label>Ghi chú</label></th>
                <td><input type="text" name="description"></td>
            </tr>
        </table>
        <p><button type="submit" name="aerp_add_adjustment" class="button button-primary">Lưu</button></p>
    </form>
</div>
