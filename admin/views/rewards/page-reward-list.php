<?php
if (!defined('ABSPATH')) exit;
global $wpdb;

require_once AERP_HRM_PATH . 'includes/table/table-reward.php';
$table = new AERP_Reward_Table();
$table->process_bulk_action();
$table->prepare_items();
// Xử lý xoá
if (
    isset($_GET['delete_reward']) &&
    wp_verify_nonce($_GET['_wpnonce'] ?? '', 'aerp_delete_reward_' . $_GET['delete_reward'])
) {
    $wpdb->delete($wpdb->prefix . 'aerp_hrm_reward_definitions', ['id' => absint($_GET['delete_reward'])]);

    aerp_js_redirect(admin_url('admin.php?page=aerp_reward_settings&delete_noti=success'));
}
?>
<div class="wrap">
    <?php
    if (isset($_GET['add_noti']) && $_GET['add_noti'] === 'success') {
        echo '<div class="notice notice-success is-dismissible"><p>Thêm mục thưởng thành công!</p></div>';
    }
    ?>
    <?php
    if (isset($_GET['edit_noti']) && $_GET['edit_noti'] === 'success') {
        echo '<div class="notice notice-success is-dismissible"><p>Cập nhật mục thưởng thành công!</p></div>';
    }
    ?>
    <?php if (isset($_GET['delete_noti'])): ?>
        <?php if ($_GET['delete_noti'] === 'success'): ?>
            <div class="notice notice-success is-dismissible">
                <p>Xóa mục thưởng thành công.</p>
            </div>
        <?php else: ?>
            <div class="notice notice-error is-dismissible">
                <p>Xóa mục thưởng thất bại.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <h1 class="wp-heading-inline">Cấu hình Thưởng động</h1>
    <a href="<?= admin_url('admin.php?page=aerp_reward_settings&add=1') ?>" class="button button-secondary" style="margin-top: 10px;">+ Thêm thưởng</a>

    <form method="post">
        <?php $table->search_box('Tìm thưởng', 'search_reward'); ?>
        <?php $table->display(); ?>
    </form>
</div>