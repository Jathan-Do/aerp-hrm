<?php
if (!defined('ABSPATH')) exit;

// Auth
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
if (!is_user_logged_in()) wp_die(__('You must be logged in to access this page.'));
$access_conditions = [
    aerp_user_has_role($user_id, 'admin'),
    aerp_user_has_role($user_id, 'department_lead'),
    aerp_user_has_role($user_id, 'accountant'),
];
if (!in_array(true, $access_conditions, true)) wp_die(__('You do not have sufficient permissions to access this page.'));



$table = new AERP_Frontend_Commission_Table();
$table->set_filters($filters);
ob_start();
$table->render();
$html = ob_get_clean();


ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Danh mục % lợi nhuận</h2>
    <div class="user-info text-end">
        Welcome, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
<?php
if (function_exists('aerp_render_breadcrumb')) {
    aerp_render_breadcrumb([
        ['label' => 'Trang chủ', 'url' => home_url('/aerp-dashboard'), 'icon' => 'fas fa-home'],
        ['label' => 'Nhân sự', 'url' => home_url('/aerp-hrm-employees')],
        ['label' => 'Danh mục % lợi nhuận']
    ]);
}
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách danh mục % lợi nhuận</h5>
        <a href="<?= home_url("/aerp-hrm-commission-schemes?action=add") ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm danh mục
        </a>
    </div>
    <div class="card-body">
        <?php // Display messages if any (using Transients API)
        $message = get_transient('aerp_commission_scheme_message');
        if ($message) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    ' . esc_html($message) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            delete_transient('aerp_commission_scheme_message'); // Xóa transient sau khi hiển thị
        }
        ?>
        <div id="aerp-commission-scheme-table-wrapper">
            <?php $table->render(); ?>
        </div>
        <a href="<?= home_url('/aerp-categories') ?>" class="btn btn-secondary" style="width: fit-content;">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Danh mục % lợi nhuận';
include AERP_HRM_PATH . 'frontend/dashboard/layout.php';
?>

