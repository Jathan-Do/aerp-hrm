<div class="p-3 text-center" style="max-height: 80px;">
    <img src="<?php echo AERP_HRM_URL . 'assets/images/logo.png'; ?>" alt="Logo" class="logo me-2" style="width: 50px; margin-bottom: 10px;">
    <h4 class="menu-text">Dashboard</h4>
</div>
<nav class="nav flex-column">
    <?php if ($hrm_active): ?>
        <!-- HRM Menu -->
        <!-- <div class="px-3 py-2 text-white-50 text-uppercase collapsible-menu-header">
            <i class="fas fa-users me-2"></i> Nhân sự <i class="fas fa-chevron-down float-end"></i>
        </div> -->
        <!-- <div class="collapsible-menu-content"> -->
        <a class="nav-link <?php echo aerp_menu_active('aerp-dashboard'); ?>" href="<?php echo home_url('/aerp-dashboard'); ?>">
            <i class="fas fa-tachometer-alt me-2"></i> <span class="menu-text">Dashboard</span>
        </a>
        <a class="nav-link <?php echo aerp_menu_active('aerp-categories'); ?>" href="<?php echo home_url('/aerp-categories'); ?>">
            <i class="fas fa-th-large me-2"></i> <span class="menu-text">Danh Mục</span>
        </a>
        <div class="px-3 py-2 collapsible-menu-header">
            <i class="fas fa-users me-2"></i> <span class="menu-text">Nhân sự</span> <i class="fas fa-chevron-down float-end"></i>
        </div>
        <div class="collapsible-menu-content">
            <a class="nav-link <?php echo aerp_menu_active('aerp-hrm-employees'); ?>" href="<?php echo home_url('/aerp-hrm-employees'); ?>">
                <span class="ms-4"><i class="fas fa-list me-2"></i> <span class="menu-text">Danh sách</span></span>
            </a>
            <a class="nav-link <?php echo aerp_menu_active('aerp-hrm-employees/?action=add'); ?>" href="<?php echo home_url('/aerp-hrm-employees/?action=add'); ?>">
                <span class="ms-4"><i class="fas fa-plus me-2"></i> <span class="menu-text">Thêm mới</span></span>
            </a>
        </div>
        <!-- </div> -->
    <?php endif; ?>

    <?php if ($crm_active): ?>
        <!-- CRM Menu -->

        <div class="px-3 py-2 collapsible-menu-header">
            <i class="fas fa-address-book me-2"></i> <span class="menu-text">Khách Hàng</span> <i class="fas fa-chevron-down float-end"></i>
        </div>
        <div class="collapsible-menu-content">
            <a class="nav-link <?php echo aerp_menu_active('aerp-crm-dashboard'); ?>" href="<?php echo home_url('/aerp-crm-dashboard'); ?>">
                <span class="ms-4"><i class="fas fa-tachometer-alt me-2"></i> <span class="menu-text">Dashboard</span></span>
            </a>
            <a class="nav-link <?php echo aerp_menu_active('aerp-crm-customers'); ?>" href="<?php echo home_url('/aerp-crm-customers'); ?>">
                <span class="ms-4"><i class="fas fa-list me-2"></i> <span class="menu-text">Danh sách</span></span>
            </a>
            <a class="nav-link <?php echo aerp_menu_active('aerp-crm-customers/?action=add'); ?>" href="<?php echo home_url('/aerp-crm-customers/?action=add'); ?>">
                <span class="ms-4"><i class="fas fa-plus me-2"></i> <span class="menu-text">Thêm mới</span></span>
            </a>
            <a class="nav-link <?php echo aerp_menu_active('aerp-crm-customer-types'); ?>" href="<?php echo home_url('/aerp-crm-customer-types'); ?>">
                <span class="ms-4"><i class="fas fa-tags me-2"></i> <span class="menu-text">Loại khách hàng</span></span>
            </a>
            <a class="nav-link <?php echo aerp_menu_active('aerp-crm-customer-sources'); ?>" href="<?php echo home_url('/aerp-crm-customer-sources'); ?>">
                <span class="ms-4"><i class="fas fa-globe me-2"></i> <span class="menu-text">Nguồn khách hàng</span></span>
            </a>
        </div>
    <?php endif; ?>
    <?php if ($order_active): ?>
        <div class="px-3 py-2 collapsible-menu-header">
            <i class="fas fa-file-invoice me-2"></i> <span class="menu-text">Đơn hàng</span> <i class="fas fa-chevron-down float-end"></i>
        </div>
        <div class="collapsible-menu-content">
            <a class="nav-link <?php echo aerp_menu_active('aerp-order-orders'); ?>" href="<?php echo home_url('/aerp-order-orders'); ?>">
                <span class="ms-4"><i class="fas fa-list me-2"></i> <span class="menu-text">Danh sách</span></span>
            </a>
            <a class="nav-link <?php echo aerp_menu_active('aerp-order-orders/?action=add'); ?>" href="<?php echo home_url('/aerp-order-orders/?action=add'); ?>">
                <span class="ms-4"><i class="fas fa-plus me-2"></i> <span class="menu-text">Thêm mới</span></span>
            </a>
            <a class="nav-link <?php echo aerp_menu_active('aerp-order-statuses'); ?>" href="<?php echo home_url('/aerp-order-statuses'); ?>">
                <span class="ms-4"><i class="fas fa-tags me-2"></i> <span class="menu-text">Trạng thái đơn hàng</span></span>
            </a>
        </div>
        <div class="px-3 py-2 collapsible-menu-header">
            <i class="fas fa-warehouse me-2"></i> <span class="menu-text">Quản lý kho</span> <i class="fas fa-chevron-down float-end"></i>
        </div>
        <div class="collapsible-menu-content">
            <a class="nav-link <?php echo aerp_menu_active('aerp-warehouses'); ?>" href="<?php echo home_url('/aerp-warehouses'); ?>">
                <span class="ms-4"><i class="fas fa-list me-2"></i> <span class="menu-text">Danh sách</span></span>
            </a>
            <a class="nav-link <?php echo aerp_menu_active('aerp-warehouses/?action=add'); ?>" href="<?php echo home_url('/aerp-warehouses/?action=add'); ?>">
                <span class="ms-4"><i class="fas fa-plus me-2"></i> <span class="menu-text">Thêm mới</span></span>
            </a>
            <a class="nav-link <?php echo aerp_menu_active('aerp-warehouses/?action=stock'); ?>" href="<?php echo home_url('/aerp-warehouses/?action=stock'); ?>">
                <span class="ms-4"><i class="fas fa-boxes me-2"></i> <span class="menu-text">Tồn kho</span></span>
            </a>
            <a class="nav-link <?php echo aerp_menu_active('aerp-inventory-transfers'); ?>" href="<?php echo home_url('/aerp-inventory-transfers'); ?>">
                <span class="ms-4"><i class="fas fa-exchange-alt me-2"></i> <span class="menu-text">Chuyển kho</span></span>
            </a>
            <a class="nav-link <?php echo aerp_menu_active('aerp-inventory-logs'); ?>" href="<?php echo home_url('/aerp-inventory-logs'); ?>">
                <span class="ms-4"><i class="fas fa-dolly me-2"></i> <span class="menu-text">Nhập/ Xuất kho</span></span>
            </a>
        </div>
    <?php endif; ?>
    <!-- Setting Menu -->
    <div class="">
        <a class="nav-link <?php echo aerp_menu_active('aerp-setting'); ?>" href="<?php echo home_url('/aerp-setting'); ?>">
            <i class="fas fa-cog me-2"></i> <span class="menu-text">Cài đặt</span>
        </a>
    </div>
</nav>