<?php
if (!defined('ABSPATH')) exit;
require_once AERP_HRM_PATH . 'includes/table/table-employee-reward.php';

$table = new AERP_Employee_Reward_Table($employee_id);
$table->process_bulk_action();
$table->prepare_items();
if (
    isset($_GET['delete_employee_reward'], $_GET['_wpnonce']) &&
    wp_verify_nonce($_GET['_wpnonce'], 'aerp_delete_employee_reward_' . $_GET['delete_employee_reward'])
) {
    global $wpdb;
    $id = absint($_GET['delete_employee_reward']);
    $wpdb->delete("{$wpdb->prefix}aerp_hrm_employee_rewards", ['id' => $id]);

    // 👉 Chuyển hướng lại đúng tab, không còn tham số rác
    $redirect_url = admin_url('admin.php?page=aerp_employees&view=' . $employee_id . '&#rewards');
    aerp_js_redirect($redirect_url);
}

?>
<p>
    <a href="<?= admin_url('admin.php?page=aerp_employee_reward_add&employee_id=' . $employee_id) ?>" class="button button-primary">
        + Thêm thưởng
    </a>
</p>

<form method="post">
    <?php $table->search_box('Tìm thưởng', 'search_reward'); ?>
    <?php $table->display(); ?>
</form>