<?php
$current_user = wp_get_current_user();
$table = new AERP_Frontend_KPI_Settings_Table();
$table->process_bulk_action();
ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Quản lý mốc thưởng KPI</h2>
    <div class="user-info text-end">
        Welcome, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(site_url('/aerp-dang-nhap')); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách mốc thưởng KPI</h5>
        <a href="<?php echo esc_url(home_url('/aerp-kpi-settings/?action=add')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm mới
        </a>
    </div>
    <div class="card-body">
        <?php
        $message = get_transient('aerp_kpi_message');
        if ($message) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'
                . esc_html($message) .
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            delete_transient('aerp_kpi_message');
        }
        ?>
        <div id="aerp-kpi-settings-table-wrapper">
            <?php $table->render(); ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Quản lý mốc thưởng KPI';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php'); 