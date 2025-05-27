<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$rules = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_disciplinary_rules ORDER BY rule_name");
$employees = $wpdb->get_results("SELECT id, full_name FROM {$wpdb->prefix}aerp_hrm_employees WHERE status = 'active'");
$selected_id = absint($_GET['employee_id'] ?? 0);
$today = date('Y-m-d');
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Ghi nhận vi phạm</h1>
    <a href="<?= admin_url('admin.php?page=aerp_employees&view=' . $selected_id . '#disciplines') ?>" class="page-title-action">← Quay lại bảng</a>
    <form method="post" style="max-width: 600px; margin-top: 20px;">
        <?php wp_nonce_field('aerp_add_discipline_action', 'aerp_add_discipline_nonce'); ?>

        <table class="form-table">
            <tr>
                <th><label for="employee_id">Nhân viên</label></th>
                <td>
                    <select name="employee_id" required>
                        <option value="">-- Chọn nhân viên --</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= esc_attr($emp->id) ?>" <?= selected($emp->id, $selected_id) ?>>
                                <?= esc_html($emp->full_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="rule_id">Lý do vi phạm</label></th>
                <td>
                    <select name="rule_id" required>
                        <option value="">-- Chọn lý do --</option>
                        <?php foreach ($rules as $r): ?>
                            <option value="<?= esc_attr($r->id) ?>">
                                <?= esc_html($r->rule_name) ?> (–<?= $r->penalty_point ?>đ, –<?= number_format($r->fine_amount, 0, ',', '.') ?>đ)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="date_violation">Ngày vi phạm</label></th>
                <td><input type="date" name="date_violation" value="<?= esc_attr($today) ?>" required></td>
            </tr>
        </table>

        <p><button type="submit" name="aerp_add_discipline" class="button button-primary">Lưu vi phạm</button></p>
    </form>
</div>