<?php
if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}
$hrm_active = function_exists('aerp_hrm_init') || is_plugin_active('aerp-hrm/aerp-hrm.php');
$crm_active = function_exists('aerp_crm_init') || is_plugin_active('aerp-crm/aerp-crm.php');
$order_active = function_exists('aerp_order_init') || is_plugin_active('aerp-order/aerp-order.php');

function aerp_menu_active($slug)
{
    return strpos($_SERVER['REQUEST_URI'], $slug) !== false ? 'active' : '';
}
?>
<div class="offcanvas offcanvas-start d-md-none p-0" tabindex="-1" id="aerpSidebar">
    <div class="offcanvas-header bg-dark text-white">
        <h5 class="offcanvas-title">Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body dashboard-sidebar p-0">
        <?php include(AERP_HRM_PATH . 'frontend/dashboard/sidebar-content.php'); ?>
    </div>
</div>
<div class="col-md-3 col-lg-2 d-none d-md-block dashboard-sidebar p-0 position-relative">
    <?php include(AERP_HRM_PATH . 'frontend/dashboard/sidebar-content.php'); ?>
    <div class="position-fixed" style="bottom: 20px; z-index: 10; width: inherit;">
        <button id="sidebarCollapseBtn" class="btn btn-sm btn-secondary d-block m-auto" type="button">
            <i class="fas fa-angle-double-left"></i>
        </button>
    </div>

</div>