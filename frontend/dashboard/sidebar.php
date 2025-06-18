<?php
if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}
$hrm_active = function_exists('aerp_hrm_init') || is_plugin_active('aerp-hrm/aerp-hrm.php');
$crm_active = function_exists('aerp_crm_init') || is_plugin_active('aerp-crm/aerp-crm.php');

function aerp_menu_active($slug)
{
    return strpos($_SERVER['REQUEST_URI'], $slug) !== false ? 'active' : '';
}
?>
<div class="col-md-3 col-lg-2 dashboard-sidebar p-0">
    <div class="p-3 text-center d-flex flex-wrap align-items-center gap-2 justify-content-center">
        <img src="<?php echo AERP_HRM_URL . 'assets/images/logo.png'; ?>" alt="Logo" class="logo" style="width: 50px; margin-bottom: 10px;">
        <h4>Dashboard</h4>
    </div>
    <nav class="nav flex-column">
        <?php if ($hrm_active): ?>
        <!-- HRM Menu -->
        <div class="px-3 py-2 text-white-50 text-uppercase collapsible-menu-header">
            <i class="fas fa-users me-2"></i> Nhân sự <i class="fas fa-chevron-down float-end"></i>
        </div>
        <div class="collapsible-menu-content">
            <a class="nav-link <?php echo aerp_menu_active('aerp-dashboard'); ?>" href="<?php echo home_url('/aerp-dashboard'); ?>">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
            <a class="nav-link <?php echo aerp_menu_active('aerp-categories'); ?>" href="<?php echo home_url('/aerp-categories'); ?>">
                <i class="fas fa-th-large me-2"></i> Danh Mục
            </a>
            <a class="nav-link" href="#employees">
                <i class="fas fa-user me-2"></i> Nhân sự
            </a>
        </div>
        <?php endif; ?>

        <?php if ($crm_active): ?>
        <!-- CRM Menu -->
        <div class="px-3 py-2 text-white-50 text-uppercase mt-3 collapsible-menu-header">
            <i class="fas fa-address-book me-2"></i> Khách hàng <i class="fas fa-chevron-down float-end"></i>
        </div>
        <div class="collapsible-menu-content">
            <a class="nav-link <?php echo aerp_menu_active('aerp-crm-dashboard'); ?>" href="<?php echo home_url('/aerp-crm-dashboard'); ?>">
                <i class="fas fa-tachometer-alt me-2"></i> CRM Dashboard
            </a>
            <a class="nav-link <?php echo aerp_menu_active('aerp-crm-customers'); ?>" href="<?php echo home_url('/aerp-crm-customers'); ?>">
                <i class="fas fa-user me-2"></i> Khách Hàng
            </a>
        </div>
        <?php endif; ?>
        <!-- Setting Menu -->
        <div class="px-3 py-2 text-white-50 text-uppercase collapsible-menu-header">
            <i class="fas fa-cogs me-2"></i> Cài đặt <i class="fas fa-chevron-down float-end"></i>
        </div>
        <div class="collapsible-menu-content">
            <a class="nav-link <?php echo aerp_menu_active('aerp-setting'); ?>" href="<?php echo home_url('/aerp-setting'); ?>">
                <i class="fas fa-cog me-2"></i> Cài đặt
            </a>
        </div>
    </nav>
</div>