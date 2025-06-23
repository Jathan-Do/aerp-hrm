<div class="p-3 text-center collapsible-menu-content" style="max-height: 80px;">
    <img src="<?php echo AERP_HRM_URL . 'assets/images/logo.png'; ?>" alt="Logo" class="logo me-2" style="width: 50px; margin-bottom: 10px;">
    <h4 class="menu-text">Dashboard</h4>
</div>
<nav class="nav flex-column">
    <?php if ($hrm_active): ?>
        <!-- HRM Menu -->
        <!-- <div class="px-3 py-2 text-white-50 text-uppercase collapsible-menu-header">
            <i class="fas fa-users me-2"></i> Nhân sự <i class="fas fa-chevron-down float-end"></i>
        </div> -->
        <div class="collapsible-menu-content">
            <a class="nav-link <?php echo aerp_menu_active('aerp-dashboard'); ?>" href="<?php echo home_url('/aerp-dashboard'); ?>">
                <i class="fas fa-tachometer-alt me-2"></i> <span class="menu-text">Dashboard</span>
            </a>
            <a class="nav-link <?php echo aerp_menu_active('aerp-categories'); ?>" href="<?php echo home_url('/aerp-categories'); ?>">
                <i class="fas fa-th-large me-2"></i> <span class="menu-text">Danh Mục</span>
            </a>
            <a class="nav-link" href="#employees">
                <i class="fas fa-users me-2"></i> <span class="menu-text">Nhân sự</span>
            </a>
        </div>
    <?php endif; ?>

    <?php if ($crm_active): ?>
        <!-- CRM Menu -->
        <!-- <div class="px-3 py-2 text-white-50 text-uppercase mt-3 collapsible-menu-header">
            <i class="fas fa-address-book me-2"></i> Khách hàng <i class="fas fa-chevron-down float-end"></i>
        </div> -->
        <div class="collapsible-menu-content">
            <!-- <a class="nav-link <?php echo aerp_menu_active('aerp-crm-dashboard'); ?>" href="<?php echo home_url('/aerp-crm-dashboard'); ?>">
                <i class="fas fa-tachometer-alt me-2"></i> CRM Dashboard
            </a> -->
            <a class="nav-link <?php echo aerp_menu_active('aerp-crm-customers'); ?>" href="<?php echo home_url('/aerp-crm-customers'); ?>">
                <i class="fas fa-address-book me-2"></i> <span class="menu-text">Khách Hàng</span>
            </a>
            <!-- <a class="nav-link <?php echo aerp_menu_active('aerp-crm-customer-types'); ?>" href="<?php echo home_url('/aerp-crm-customer-types'); ?>">
                <i class="fas fa-tags me-2"></i> Loại khách hàng
            </a> -->
        </div>
    <?php endif; ?>
    <?php if ($order_active): ?>
        <div class="collapsible-menu-content">
            <a class="nav-link <?php echo aerp_menu_active('aerp-order-orders'); ?>" href="<?php echo home_url('/aerp-order-orders'); ?>">
                <i class="fas fa-file-invoice me-2"></i> <span class="menu-text">Đơn hàng</span>
            </a>
        </div>
    <?php endif; ?>
    <!-- Setting Menu -->
    <!-- <div class="px-3 py-2 text-white-50 text-uppercase collapsible-menu-header">
        <i class="fas fa-cogs me-2"></i> Cài đặt <i class="fas fa-chevron-down float-end"></i>
    </div> -->
    <div class="collapsible-menu-content">
        <a class="nav-link <?php echo aerp_menu_active('aerp-setting'); ?>" href="<?php echo home_url('/aerp-setting'); ?>">
            <i class="fas fa-cog me-2"></i> <span class="menu-text">Cài đặt</span>
        </a>
    </div>
</nav>