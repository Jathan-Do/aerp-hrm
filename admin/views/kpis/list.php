<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
require_once AERP_HRM_PATH . 'includes/table/table-kpi-settings.php';
$table = new AERP_KPI_Settings_Table();
$table->process_bulk_action();
$table->prepare_items();
// Xử lý thêm mới
if (
    isset($_POST['aerp_add_kpi']) &&
    check_admin_referer('aerp_add_kpi_action', 'aerp_add_kpi_nonce')
) {
    $wpdb->insert($wpdb->prefix . 'aerp_hrm_kpi_settings', [
        'min_score'  => sanitize_text_field($_POST['min_score']),
        'reward_amount'  => absint($_POST['reward_amount']),
        'note'       => sanitize_text_field($_POST['note']),
        'sort_order' => absint($_POST['sort_order']),
        'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
    ]);
    aerp_js_redirect(admin_url('admin.php?page=aerp_kpi_settings&add_noti=success'));
}
// Xử lý xoá
if (
    isset($_GET['delete_kpi']) &&
    wp_verify_nonce($_GET['_wpnonce'] ?? '', 'aerp_delete_kpi_' . $_GET['delete_kpi'])
) {
    $wpdb->delete($wpdb->prefix . 'aerp_hrm_kpi_settings', ['id' => absint($_GET['delete_kpi'])]);
    aerp_js_redirect(admin_url('admin.php?page=aerp_kpi_settings&delete_noti=success'));
}
?>

<div class="wrap">
    <?php
    if (isset($_GET['add_noti']) && $_GET['add_noti'] === 'success') {
        echo '<div class="notice notice-success is-dismissible"><p>Thêm mục kpi thành công!</p></div>';
    }
    ?>
    <?php
    if (isset($_GET['edit_noti']) && $_GET['edit_noti'] === 'success') {
        echo '<div class="notice notice-success is-dismissible"><p>Cập nhật mục kpi thành công!</p></div>';
    }
    ?>
    <?php if (isset($_GET['delete_noti'])): ?>
        <?php if ($_GET['delete_noti'] === 'success'): ?>
            <div class="notice notice-success is-dismissible">
                <p>Xóa mục kpi thành công.</p>
            </div>
        <?php else: ?>
            <div class="notice notice-error is-dismissible">
                <p>Xóa mục kpi thất bại.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <h1 class="wp-heading-inline">🎯 Cấu hình thưởng KPI</h1>
    <!-- <a href="<?= admin_url('admin.php?page=aerp_kpi_settings&add=1') ?>" class="button button-primary">Thêm mốc thưởng</a> -->
    <form method="post" style="max-width: 600px; margin-top: 20px;">
        <?php wp_nonce_field('aerp_add_kpi_action', 'aerp_add_kpi_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label>Từ điểm</label></th>
                <td><input type="number" name="min_score" required></td>
            </tr>
            <tr>
                <th><label>Tiền thưởng</label></th>
                <td><input type="number" name="reward_amount" required></td>
            </tr>
            <tr>
                <th><label>Ghi chú</label></th>
                <td><input type="text" name="note"></td>
            </tr>
            <tr>
                <th><label>Thứ tự</label></th>
                <td><input type="number" name="sort_order" value="0"></td>
            </tr>
        </table>
        <p><button type="submit" name="aerp_add_kpi" class="button button-primary">Thêm cấu hình</button></p>
    </form>

    <form method="get">
        <input type="hidden" name="page" value="aerp_kpi_settings">
        <?php $table->search_box('Tìm mốc thưởng', 'search_kpi'); ?>
    </form>
    <form method="post">
        <?php $table->display(); ?>
    </form>
</div>