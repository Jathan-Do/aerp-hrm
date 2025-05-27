<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

$id = absint($_GET['edit'] ?? 0);
$table = $wpdb->prefix . 'aerp_hrm_kpi_settings';

// Lấy dữ liệu KPI hiện tại
$kpi = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
if (!$kpi) {
    echo '<div class="notice notice-error"><p>❌ Không tìm thấy mốc KPI.</p></div>';
    return;
}

// Xử lý cập nhật
if (
    isset($_POST['aerp_update_kpi']) &&
    check_admin_referer('aerp_edit_kpi_action', 'aerp_edit_kpi_nonce')
) {
    $wpdb->update($table, [
        'min_score'     => absint($_POST['min_score']),
        'reward_amount' => floatval($_POST['reward_amount']),
        'note'          => sanitize_text_field($_POST['note']),
        'sort_order'    => absint($_POST['sort_order']),
    ], ['id' => $id]);

    aerp_js_redirect(admin_url('admin.php?page=aerp_kpi_settings&edit_noti=success'));
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">✏️ Sửa mốc thưởng KPI</h1>
    <a href="<?= admin_url('admin.php?page=aerp_kpi_settings') ?>" class="page-title-action">← Quay lại danh sách</a>

    <form method="post" style="max-width: 600px; margin-top: 20px;">
        <?php wp_nonce_field('aerp_edit_kpi_action', 'aerp_edit_kpi_nonce'); ?>
        <input type="hidden" name="id" value="<?= esc_attr($kpi->id) ?>">
        <table class="form-table">
            <tr>
                <th><label for="min_score">Từ điểm</label></th>
                <td><input type="number" name="min_score" value="<?= esc_attr($kpi->min_score) ?>" required></td>
            </tr>
            <tr>
                <th><label for="reward_amount">Tiền thưởng</label></th>
                <td><input type="number" name="reward_amount" value="<?= esc_attr($kpi->reward_amount) ?>" required></td>
            </tr>
            <tr>
                <th><label for="note">Ghi chú</label></th>
                <td><input type="text" name="note" value="<?= esc_attr($kpi->note) ?>"></td>
            </tr>
            <tr>
                <th><label for="sort_order">Thứ tự</label></th>
                <td><input type="number" name="sort_order" value="<?= esc_attr($kpi->sort_order) ?>"></td>
            </tr>
        </table>

        <p><button type="submit" name="aerp_update_kpi" class="button button-primary">💾 Cập nhật</button></p>
    </form>
</div>
