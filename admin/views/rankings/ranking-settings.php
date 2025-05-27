<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
require_once AERP_HRM_PATH . 'includes/table/table-ranking-settings.php';

$table = new AERP_Ranking_Settings_Table();
$table->process_bulk_action();
$table->prepare_items();

// Xử lý thêm mới
if (
    isset($_POST['aerp_add_rank']) &&
    check_admin_referer('aerp_add_rank_action', 'aerp_add_rank_nonce')
) {
    $wpdb->insert($wpdb->prefix . 'aerp_hrm_ranking_settings', [
        'rank_code'  => sanitize_text_field($_POST['rank_code']),
        'min_point'  => absint($_POST['min_point']),
        'note'       => sanitize_text_field($_POST['note']),
        'sort_order' => absint($_POST['sort_order']),
        'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
    ]);
    aerp_js_redirect(admin_url('admin.php?page=aerp_ranking_settings&added=success'));
}

// Xử lý xoá
if (
    isset($_GET['delete_ranking']) &&
    wp_verify_nonce($_GET['_wpnonce'] ?? '', 'aerp_delete_ranking_' . $_GET['delete_ranking'])
) {
    $wpdb->delete($wpdb->prefix . 'aerp_hrm_ranking_settings', ['id' => absint($_GET['delete_ranking'])]);
    aerp_js_redirect(admin_url('admin.php?page=aerp_ranking_settings&deleted=success'));
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Cấu hình xếp loại nhân sự</h1>

    <?php if (isset($_GET['added'])): ?>
        <div class="notice notice-success"><p>✅ Đã thêm cấu hình.</p></div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <div class="notice notice-success"><p>🗑️ Đã xoá cấu hình.</p></div>
    <?php endif; ?>

    <form method="post" style="max-width: 600px; margin-top: 20px;">
        <?php wp_nonce_field('aerp_add_rank_action', 'aerp_add_rank_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label>Xếp loại</label></th>
                <td><input type="text" name="rank_code" required></td>
            </tr>
            <tr>
                <th><label>Từ điểm</label></th>
                <td><input type="number" name="min_point" required></td>
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
        <p><button type="submit" name="aerp_add_rank" class="button button-primary">Thêm cấu hình</button></p>
    </form>

    <hr>
    <form method="get">
        <input type="hidden" name="page" value="aerp_ranking_settings">
        <?php $table->search_box('Tìm kiếm', 'search_ranking'); ?>
    </form>

    <form method="post">
        <?php $table->display(); ?>
    </form>
</div>
