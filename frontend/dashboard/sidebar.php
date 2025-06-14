<?php
function aerp_menu_active($slug) {
    return strpos($_SERVER['REQUEST_URI'], $slug) !== false ? 'active' : '';
}
?>
<div class="col-md-3 col-lg-2 dashboard-sidebar p-0">
    <div class="p-3 text-center d-flex flex-wrap align-items-center gap-2 justify-content-center">
        <img src="<?php echo AERP_HRM_URL . 'assets/images/logo.png'; ?>" alt="Logo" class="logo" style="width: 50px; margin-bottom: 10px;">
        <h4>Dashboard</h4>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link <?php echo aerp_menu_active('aerp-dashboard'); ?>" href="<?php echo home_url('/aerp-dashboard'); ?>">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>
        <a class="nav-link <?php echo aerp_menu_active('aerp-categories'); ?>" href="<?php echo home_url('/aerp-categories'); ?>">
            <i class="fas fa-th-large me-2"></i> Danh Mục
        </a>
        <a class="nav-link" href="#employees">
            <i class="fas fa-users me-2"></i> Employees
        </a>
    </nav>
</div>