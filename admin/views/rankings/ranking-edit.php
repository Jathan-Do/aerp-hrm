<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$id = absint($_GET['edit'] ?? 0);
$table = $wpdb->prefix . 'aerp_hrm_ranking_settings';

// Lấy dữ liệu hiện tại
$rank = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
if (!$rank) {
    echo '<div class="notice notice-error"><p>Không tìm thấy dữ liệu.</p></div>';
    return;
}

// Cập nhật
if (
    isset($_POST['aerp_update_rank']) &&
    check_admin_referer('aerp_edit_rank_action', 'aerp_edit_rank_nonce')
) {
    $wpdb->update($table, [
        'rank_code'  => sanitize_text_field($_POST['rank_code']),
        'min_point'  => absint($_POST['min_point']),
        'note'       => sanitize_text_field($_POST['note']),
        'sort_order' => absint($_POST['sort_order']),
    ], ['id' => $id]);

    echo '<div class="notice notice-success"><p>✅ Đã cập nhật xếp loại.</p></div>';
    $rank = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_ranking_settings WHERE id = %d", $id));
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Sửa cấu hình xếp loại</h1>
    <a href="<?= admin_url('admin.php?page=aerp_ranking_settings') ?>" class="page-title-action">← Quay lại danh sách</a>

    <form method="post" style="max-width: 600px; margin-top: 20px;">
        <?php wp_nonce_field('aerp_edit_rank_action', 'aerp_edit_rank_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="rank_code">Xếp loại</label></th>
                <td><input type="text" name="rank_code" value="<?= esc_attr($rank->rank_code) ?>" required></td>
            </tr>
            <tr>
                <th><label for="min_point">Từ điểm</label></th>
                <td><input type="number" name="min_point" value="<?= esc_attr($rank->min_point) ?>" required></td>
            </tr>
            <tr>
                <th><label for="note">Ghi chú</label></th>
                <td><input type="text" name="note" value="<?= esc_attr($rank->note) ?>"></td>
            </tr>
            <tr>
                <th><label for="sort_order">Thứ tự</label></th>
                <td><input type="number" name="sort_order" value="<?= esc_attr($rank->sort_order) ?>"></td>
            </tr>
        </table>

        <p><button type="submit" name="aerp_update_rank" class="button button-primary">💾 Cập nhật</button></p>
    </form>
</div>