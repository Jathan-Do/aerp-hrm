<?php
if (!defined('ABSPATH')) exit;
global $wpdb;
require_once AERP_HRM_PATH . 'includes/table/table-discipline-rules.php';
$table = new AERP_Discipline_Rules_Table();

// Xử lý thêm mới
if (
    isset($_POST['aerp_add_rule']) &&
    check_admin_referer('aerp_add_rule_action', 'aerp_add_rule_nonce')
) {
    $wpdb->insert($wpdb->prefix . 'aerp_hrm_disciplinary_rules', [
        'rule_name'     => sanitize_text_field($_POST['rule_name']),
        'penalty_point' => absint($_POST['penalty_point']),
        'fine_amount'   => floatval($_POST['fine_amount']),
        'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
    ]);
    echo '<div class="updated"><p>✅ Đã thêm lý do vi phạm.</p></div>';
}

// Xử lý xóa
$table->process_bulk_action();
$table->prepare_items();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Cấu hình vi phạm</h1>

    <form method="post" style="margin: 20px 0; max-width: 600px;">
        <?php wp_nonce_field('aerp_add_rule_action', 'aerp_add_rule_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="rule_name">Lý do</label></th>
                <td><input type="text" name="rule_name" required></td>
            </tr>
            <tr>
                <th><label for="penalty_point">Điểm trừ</label></th>
                <td><input type="number" name="penalty_point" value="0"></td>
            </tr>
            <tr>
                <th><label for="fine_amount">Tiền phạt (VNĐ)</label></th>
                <td><input type="number" name="fine_amount" value="0"></td>
            </tr>
        </table>
        <p><button type="submit" name="aerp_add_rule" class="button button-primary">Thêm lý do</button></p>
    </form>

    <hr>

    <form method="post">
        <?php $table->search_box('Tìm kiếm', 'search_rule'); ?>
        <input type="hidden" name="page" value="<?= esc_attr($_REQUEST['page']) ?>">
        <?php $table->display(); ?>
    </form>
</div>
