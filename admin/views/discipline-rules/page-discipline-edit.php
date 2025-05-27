<?php
if (!defined('ABSPATH')) exit;
global $wpdb;

$id = absint($_GET['edit'] ?? 0);
$rule = $wpdb->get_row($wpdb->prepare("
    SELECT * FROM {$wpdb->prefix}aerp_hrm_disciplinary_rules WHERE id = %d
", $id));

if (!$rule) {
    echo '<div class="notice notice-error"><p>Không tìm thấy lý do vi phạm.</p></div>';
    return;
}

// Xử lý cập nhật
if (
    isset($_POST['aerp_update_rule']) &&
    check_admin_referer('aerp_edit_rule_action', 'aerp_edit_rule_nonce')
) {
    $wpdb->update($wpdb->prefix . 'aerp_hrm_disciplinary_rules', [
        'rule_name'     => sanitize_text_field($_POST['rule_name']),
        'penalty_point' => absint($_POST['penalty_point']),
        'fine_amount'   => floatval($_POST['fine_amount']),
    ], ['id' => $id]);

    echo '<div class="notice notice-success"><p>✅ Đã cập nhật lý do vi phạm.</p></div>';
    $rule = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_disciplinary_rules WHERE id = %d", $id));
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">✏️ Sửa lý do vi phạm</h1>

    <form method="post" style="max-width: 600px; margin-top: 20px;">
        <?php wp_nonce_field('aerp_edit_rule_action', 'aerp_edit_rule_nonce'); ?>
        <input type="hidden" name="id" value="<?= esc_attr($rule->id) ?>">

        <table class="form-table">
            <tr>
                <th><label for="rule_name">Lý do</label></th>
                <td><input type="text" name="rule_name" value="<?= esc_attr($rule->rule_name) ?>" required></td>
            </tr>
            <tr>
                <th><label for="penalty_point">Điểm trừ</label></th>
                <td><input type="number" name="penalty_point" value="<?= esc_attr($rule->penalty_point) ?>"></td>
            </tr>
            <tr>
                <th><label for="fine_amount">Tiền phạt (VNĐ)</label></th>
                <td><input type="number" name="fine_amount" value="<?= esc_attr($rule->fine_amount) ?>"></td>
            </tr>
        </table>

        <p>
            <button type="submit" name="aerp_update_rule" class="button button-primary">Cập nhật</button>
            <a href="<?= admin_url('admin.php?page=aerp_discipline') ?>" class="button">← Quay lại danh sách</a>
        </p>
    </form>
</div>
