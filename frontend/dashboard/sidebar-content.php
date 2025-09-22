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
        <a class="nav-link <?php echo aerp_menu_active('aerp-dashboard'); ?>" href="<?php echo home_url('/aerp-dashboard'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Dashboard">
            <i class="fas fa-tachometer-alt me-2"></i> <span class="menu-text">Dashboard</span>
        </a>
        <a class="nav-link <?php echo aerp_menu_active('aerp-categories'); ?>" href="<?php echo home_url('/aerp-categories'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Danh Mục">
            <i class="fas fa-th-large me-2"></i> <span class="menu-text">Danh Mục</span>
        </a>
        <div class="px-3 py-2 collapsible-menu-header fw-bold" style="cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Nhân sự">
            <i class="fas fa-users me-2"></i> <span class="menu-text">Nhân sự</span> <i class="fas fa-chevron-down float-end"></i>
        </div>
        <div class="collapsible-menu-content">
            <a class="nav-link light <?php echo aerp_menu_active('aerp-hrm-employees'); ?>" href="<?php echo home_url('/aerp-hrm-employees'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Danh sách">
                <span class="ms-4"><i class="fas fa-list me-2"></i> <span class="menu-text">Danh sách</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-hrm-employees/?action=add'); ?>" href="<?php echo home_url('/aerp-hrm-employees/?action=add'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Thêm mới">
                <span class="ms-4"><i class="fas fa-plus me-2"></i> <span class="menu-text">Thêm mới</span></span>
            </a>
        </div>
        <!-- </div> -->
    <?php endif; ?>

    <?php if ($crm_active): ?>
        <!-- CRM Menu -->

        <div class="px-3 py-2 collapsible-menu-header fw-bold" style="cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Khách Hàng">
            <i class="fas fa-address-book me-2"></i> <span class="menu-text">Khách Hàng</span> <i class="fas fa-chevron-down float-end"></i>
        </div>
        <div class="collapsible-menu-content">
            <a class="nav-link light <?php echo aerp_menu_active('aerp-crm-dashboard'); ?>" href="<?php echo home_url('/aerp-crm-dashboard'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Dashboard">
                <span class="ms-4"><i class="fas fa-tachometer-alt me-2"></i> <span class="menu-text">Dashboard</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-crm-customers'); ?>" href="<?php echo home_url('/aerp-crm-customers'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Danh sách">
                <span class="ms-4">
                    <i class="fas fa-list me-2"></i>
                    <span class="menu-text">Danh sách</span>
                    <?php
                        // Đếm số lượng đơn hàng có status là 'new'
                        global $wpdb;
                        $customer_new_count = (int) $wpdb->get_var(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}aerp_crm_customers WHERE assigned_to = 0 AND status = 'active'"
                        );
                    ?>
                    <span class="badge text-bg-secondary ms-2 rounded-pill bg-danger"><?php echo $customer_new_count; ?></span>
                </span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-crm-customers/?action=add'); ?>" href="<?php echo home_url('/aerp-crm-customers/?action=add'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Thêm mới">
                <span class="ms-4"><i class="fas fa-plus me-2"></i> <span class="menu-text">Thêm mới</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-crm-customer-types'); ?>" href="<?php echo home_url('/aerp-crm-customer-types'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Loại khách hàng">
                <span class="ms-4"><i class="fas fa-tags me-2"></i> <span class="menu-text">Loại khách hàng</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-crm-customer-sources'); ?>" href="<?php echo home_url('/aerp-crm-customer-sources'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Nguồn khách hàng">
                <span class="ms-4"><i class="fas fa-globe me-2"></i> <span class="menu-text">Nguồn khách hàng</span></span>
            </a>
        </div>
    <?php endif; ?>
    <?php if ($order_active): ?>
        <div class="px-3 py-2 collapsible-menu-header fw-bold" style="cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Đơn hàng">
            <i class="fas fa-file-invoice me-2"></i> <span class="menu-text">Đơn hàng</span> <i class="fas fa-chevron-down float-end"></i>
        </div>
        <div class="collapsible-menu-content">
            <a class="nav-link light <?php echo aerp_menu_active('aerp-report-order'); ?>" href="<?php echo home_url('/aerp-report-order'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Dashboard">
                <span class="ms-4"><i class="fas fa-tachometer-alt me-2"></i> <span class="menu-text">Dashboard</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-order-orders'); ?>" href="<?php echo home_url('/aerp-order-orders'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Danh sách">
                <span class="ms-4">
                    <i class="fas fa-list me-2"></i>
                    <span class="menu-text">Danh sách</span>
                    <?php
                        // Đếm số lượng đơn hàng có status là 'new'
                        global $wpdb;
                        $current_user_id = get_current_user_id();
                        $current_user_employee = $wpdb->get_row($wpdb->prepare(
                            "SELECT id, work_location_id FROM {$wpdb->prefix}aerp_hrm_employees WHERE user_id = %d",
                            $current_user_id
                        ));
                        $employee_current_id = $current_user_employee->id;
                        $order_new_count = (int) $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT COUNT(*) FROM {$wpdb->prefix}aerp_order_orders WHERE status = 'new' AND (employee_id = %d OR created_by = %d)",
                                $employee_current_id,
                                $employee_current_id
                            )
                        );
                    ?>
                    <span class="badge text-bg-secondary ms-2 rounded-pill bg-danger"><?php echo $order_new_count; ?></span>
                </span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-order-orders/?action=add'); ?>" href="<?php echo home_url('/aerp-order-orders/?action=add'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Thêm mới">
                <span class="ms-4"><i class="fas fa-plus me-2"></i> <span class="menu-text">Thêm mới</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-order-statuses'); ?>" href="<?php echo home_url('/aerp-order-statuses'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Trạng thái đơn hàng">
                <span class="ms-4"><i class="fas fa-tags me-2"></i> <span class="menu-text">Trạng thái đơn hàng</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-devices'); ?>" href="<?php echo home_url('/aerp-devices'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Quản lý thiết bị">
                <span class="ms-4"><i class="fas fa-laptop me-2"></i> <span class="menu-text">Quản lý thiết bị</span><?php
                        // Đếm số lượng đơn hàng có status là 'new'
                        global $wpdb;
                        $current_user_id = get_current_user_id();
                        $current_user_employee = $wpdb->get_row($wpdb->prepare(
                            "SELECT id, work_location_id FROM {$wpdb->prefix}aerp_hrm_employees WHERE user_id = %d",
                            $current_user_id
                        ));
                        $employee_current_id = $current_user_employee->id;
                        $device_count = (int) $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT COUNT(*) 
                                 FROM {$wpdb->prefix}aerp_order_devices d
                                 INNER JOIN {$wpdb->prefix}aerp_order_orders o ON d.order_id = o.id
                                 WHERE d.device_status = 'received' AND (o.employee_id = %d OR o.created_by = %d)",
                                 $employee_current_id,
                                 $employee_current_id
                            )
                        );
                    ?>
                    <span class="badge text-bg-secondary ms-2 rounded-pill bg-danger"><?php echo $device_count; ?></span></span>
            </a>
        </div>
        <div class="px-3 py-2 collapsible-menu-header fw-bold" style="cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Quản lý kho">
            <i class="fas fa-warehouse me-2"></i> <span class="menu-text">Quản lý kho</span> <i class="fas fa-chevron-down float-end"></i>
        </div>
        <div class="collapsible-menu-content">
            <a class="nav-link light <?php echo aerp_menu_active('aerp-stock-timeline'); ?>" href="<?php echo home_url('/aerp-stock-timeline'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Dashboard">
                <span class="ms-4"><i class="fas fa-tachometer-alt me-2"></i> <span class="menu-text">Dashboard</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-warehouses'); ?>" href="<?php echo home_url('/aerp-warehouses'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Danh sách">
                <span class="ms-4"><i class="fas fa-list me-2"></i> <span class="menu-text">Danh sách</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-warehouses/?action=add'); ?>" href="<?php echo home_url('/aerp-warehouses/?action=add'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Thêm mới">
                <span class="ms-4"><i class="fas fa-plus me-2"></i> <span class="menu-text">Thêm mới</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-warehouses/?action=stock'); ?>" href="<?php echo home_url('/aerp-warehouses/?action=stock'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Tồn kho">
                <span class="ms-4"><i class="fas fa-boxes me-2"></i> <span class="menu-text">Tồn kho</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-inventory-transfers'); ?>" href="<?php echo home_url('/aerp-inventory-transfers'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Chuyển kho">
                <span class="ms-4"><i class="fas fa-exchange-alt me-2"></i> <span class="menu-text">Chuyển kho</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-inventory-logs'); ?>" href="<?php echo home_url('/aerp-inventory-logs'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Nhập/ Xuất kho">
                <span class="ms-4"><i class="fas fa-dolly me-2"></i> <span class="menu-text">Nhập/ Xuất kho</span></span>
            </a>
        </div>
        <div class="px-3 py-2 collapsible-menu-header fw-bold" style="cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Kế toán">
            <i class="fas fa-calculator me-2"></i> <span class="menu-text">Kế toán</span> <i class="fas fa-chevron-down float-end"></i>
        </div>
        <div class="collapsible-menu-content">
            <a class="nav-link light <?php echo aerp_menu_active('aerp-acc-reports'); ?>" href="<?php echo home_url('/aerp-acc-reports'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Dashboard">
                <span class="ms-4"><i class="fas fa-tachometer-alt me-2"></i> <span class="menu-text">Dashboard</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-acc-receipts'); ?>" href="<?php echo home_url('/aerp-acc-receipts'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Phiếu thu">
                <span class="ms-4"><i class="fas fa-list me-2"></i> <span class="menu-text">Phiếu thu</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-acc-payments'); ?>" href="<?php echo home_url('/aerp-acc-payments'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Phiếu chi">
                <span class="ms-4"><i class="fas fa-list me-2"></i> <span class="menu-text">Phiếu chi</span></span>
            </a>
            <a class="nav-link light <?php echo aerp_menu_active('aerp-acc-deposits'); ?>" href="<?php echo home_url('/aerp-acc-deposits'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Phiếu nộp tiền">
                <span class="ms-4"><i class="fas fa-list me-2"></i> <span class="menu-text">Phiếu nộp tiền</span></span>
            </a>
        </div>
    <?php endif; ?>
    <!-- Setting Menu -->
    <div class="">
        <a class="nav-link  <?php echo aerp_menu_active('aerp-setting'); ?>" href="<?php echo home_url('/aerp-setting'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Cài đặt">
            <i class="fas fa-cog me-2"></i> <span class="menu-text">Cài đặt</span>
        </a>
    </div>
</nav>