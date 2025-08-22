<?php

/**
 * Install schema for AERP HRM Module – full reset version
 */

function aerp_hrm_get_table_names()
{
    global $wpdb;
    return [
        $wpdb->prefix . 'aerp_hrm_employees',
        $wpdb->prefix . 'aerp_hrm_departments',
        $wpdb->prefix . 'aerp_hrm_positions',
        $wpdb->prefix . 'aerp_hrm_salaries',
        $wpdb->prefix . 'aerp_hrm_adjustments',
        $wpdb->prefix . 'aerp_hrm_advance_salaries',
        $wpdb->prefix . 'aerp_hrm_ranking_settings',
        $wpdb->prefix . 'aerp_hrm_tasks',
        $wpdb->prefix . 'aerp_hrm_employee_rewards',
        $wpdb->prefix . 'aerp_hrm_reward_definitions',
        $wpdb->prefix . 'aerp_hrm_attendance',
        $wpdb->prefix . 'aerp_hrm_salary_config',
        $wpdb->prefix . 'aerp_hrm_task_comments',
        $wpdb->prefix . 'aerp_hrm_attachments',
        $wpdb->prefix . 'aerp_hrm_disciplinary_rules',
        $wpdb->prefix . 'aerp_hrm_disciplinary_logs',
        $wpdb->prefix . 'aerp_hrm_kpi_settings',
        $wpdb->prefix . 'aerp_hrm_employee_journey',
        $wpdb->prefix . 'aerp_hrm_company_info',
        $wpdb->prefix . 'aerp_hrm_work_locations',
        $wpdb->prefix . 'aerp_roles',
        $wpdb->prefix . 'aerp_permissions',
        $wpdb->prefix . 'aerp_role_permission',
        $wpdb->prefix . 'aerp_user_role',
        $wpdb->prefix . 'aerp_user_permission',

        // ... thêm bảng khác nếu có
    ];
}

function aerp_hrm_install_schema()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $sqls = [];

    // 1. Nhân sự
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_employees (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        employee_code VARCHAR(50),
        full_name VARCHAR(255),
        gender ENUM('male','female','other'),
        birthday DATE,
        cccd_number VARCHAR(20),
        cccd_issued_date DATE,
        address_permanent TEXT,
        address_current TEXT,
        phone_number VARCHAR(20),
        email VARCHAR(255),
        bank_account VARCHAR(100),
        bank_name VARCHAR(255),
        relative_name VARCHAR(255),
        relative_phone VARCHAR(20),
        relative_relationship VARCHAR(50),
        department_id BIGINT,
        position_id BIGINT,
        work_location_id BIGINT,
        join_date DATE,
        off_date DATE NULL,
        status ENUM('active','inactive','resigned') DEFAULT 'active',
        note TEXT,
        user_id BIGINT,
        current_points INT DEFAULT 100,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 2. Lương
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_salaries (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        employee_id BIGINT NOT NULL,
        salary_month DATE NOT NULL,
        base_salary DOUBLE DEFAULT 0,
        salary_per_day DOUBLE DEFAULT 0,
        bonus DOUBLE DEFAULT 0,
        deduction DOUBLE DEFAULT 0,
        adjustment DOUBLE DEFAULT 0,
        actual_work_days INT DEFAULT 0,
        work_days INT DEFAULT 0,
        off_days INT DEFAULT 0,
        ot_days FLOAT DEFAULT 0,
        final_salary DECIMAL(15,0) DEFAULT 0,
        advance_paid DOUBLE DEFAULT 0,
        ranking VARCHAR(10),
        points_total INT DEFAULT 100,
        auto_bonus DOUBLE DEFAULT 0,
        finance_synced TINYINT(1) DEFAULT 0,
        finance_ref_id BIGINT,
        note TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 3. Công việc
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_tasks (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        employee_id BIGINT,
        task_title VARCHAR(255),
        task_desc TEXT,
        deadline DATETIME,
        score INT,
        status ENUM('assigned','done','failed') DEFAULT 'assigned',
        created_by BIGINT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 4. Feedback
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_task_comments (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        task_id BIGINT,
        user_id BIGINT,
        comment TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 5. Chấm công
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_attendance (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        employee_id BIGINT,
        work_date DATETIME,
        shift VARCHAR(20),
        work_ratio FLOAT,
        note TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 6. Điều chỉnh
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_adjustments (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        employee_id BIGINT,
        reason VARCHAR(255),
        amount DOUBLE,
        type ENUM('reward','fine','adjust'),
        description TEXT,
        date_effective DATE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 7. File đính kèm
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_attachments (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        employee_id BIGINT,
        file_url TEXT,
        file_name VARCHAR(255),
        file_type VARCHAR(50),
        attachment_type VARCHAR(50),
        storage_type VARCHAR(20) DEFAULT 'local',
        drive_file_id VARCHAR(255) DEFAULT NULL,
        uploaded_by BIGINT,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 8. Cấu hình lương động
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_salary_config (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        employee_id BIGINT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        base_salary DOUBLE DEFAULT 0,
        allowance DOUBLE DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 9. Cấu hình vi phạm
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_disciplinary_rules (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        rule_name VARCHAR(255) NOT NULL,
        system_key VARCHAR(100) DEFAULT NULL,
        penalty_point INT DEFAULT 0,
        fine_amount DOUBLE DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 10. Ghi nhận vi phạm
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_disciplinary_logs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        employee_id BIGINT NOT NULL,
        rule_id BIGINT NOT NULL,
        date_violation DATE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 11. Tạm ứng lương
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_advance_salaries (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        employee_id BIGINT NOT NULL,
        amount DOUBLE DEFAULT 0,
        advance_date DATE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 12. Danh mục thưởng tự động
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_reward_definitions (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        amount DOUBLE DEFAULT 0,
        trigger_type VARCHAR(255) DEFAULT NULL,
        day_trigger DATE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 13. Ghi nhận thưởng tự động
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_employee_rewards (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        employee_id BIGINT NOT NULL,
        reward_id BIGINT NOT NULL,
        month DATE NOT NULL,
        note TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 14. Cấu hình xếp hạng
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_ranking_settings (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        rank_code VARCHAR(10) NOT NULL, -- A, B, C...
        min_point INT NOT NULL,
        note TEXT,
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 15. Cấu hình KPI setting
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_kpi_settings (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        min_score INT NOT NULL,
        reward_amount DOUBLE DEFAULT 0,
        note TEXT,
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 16. Hành trình nhân sự (Employee Journey)
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_employee_journey (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        employee_id BIGINT NOT NULL,
        event_type VARCHAR(50) NOT NULL,
        old_value TEXT,
        new_value TEXT,
        note TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 17. Phòng ban
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_departments (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        manager_id INT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 18. Chức vụ
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_positions (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 19. Thông tin công ty
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_company_info (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        company_name VARCHAR(255),
        tax_code VARCHAR(50),
        phone VARCHAR(50),
        email VARCHAR(100),
        address TEXT,
        website VARCHAR(100),
        logo_url TEXT,
        work_saturday VARCHAR(10) DEFAULT 'off', -- off, full, half
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 20. Chi nhánh (Work Locations)
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_hrm_work_locations (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // 21. Roles (Nhóm quyền)
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_roles (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT
    ) $charset_collate;";

    // 22. Permissions (Quyền chi tiết)
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_permissions (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT
    ) $charset_collate;";

    // 23. Role-Permission mapping
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_role_permission (
        role_id BIGINT NOT NULL,
        permission_id BIGINT NOT NULL,
        PRIMARY KEY (role_id, permission_id)
    ) $charset_collate;";

    // 24. User-Role mapping
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_user_role (
        user_id BIGINT NOT NULL,
        role_id BIGINT NOT NULL,
        PRIMARY KEY (user_id, role_id)
    ) $charset_collate;";

    // 25. User-Permission mapping (quyền đặc biệt)
    $sqls[] = "CREATE TABLE {$wpdb->prefix}aerp_user_permission (
        user_id BIGINT NOT NULL,
        permission_id BIGINT NOT NULL,
        PRIMARY KEY (user_id, permission_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    foreach ($sqls as $sql) {
        dbDelta($sql);
    }
    // ✅ Seed data
    aerp_hrm_seed_data();
}

function aerp_hrm_seed_data()
{
    global $wpdb;

    // 1. Seed Roles
    $roles = [
        ['name' => 'admin', 'description' => 'Quản trị hệ thống'],
        ['name' => 'department_lead', 'description' => 'Trưởng phòng ban'],
        ['name' => 'accountant', 'description' => 'Kế toán'],
        ['name' => 'employee', 'description' => 'Nhân viên thông thường'],
    ];

    foreach ($roles as $role) {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}aerp_roles WHERE name = %s",
            $role['name']
        ));
        if (!$exists) {
            $wpdb->insert($wpdb->prefix . 'aerp_roles', $role);
        }
    }

    // 2. Seed Permissions
    $permissions = [
        // Plugin HRM
        // Salary
        ['name' => 'salary_calculate', 'description' => 'Tính lương'],
        ['name' => 'salary_add', 'description' => 'Thêm cấu hình lương'],
        ['name' => 'salary_edit', 'description' => 'Chỉnh sửa cấu hình lương'],
        ['name' => 'salary_view', 'description' => 'Xem lương'],
        ['name' => 'salary_advance_add', 'description' => 'Thêm tạm ứng lương'],
        ['name' => 'salary_advance_edit', 'description' => 'Chỉnh sửa tạm ứng lương'],
        // Employee
        ['name' => 'employee_add', 'description' => 'Thêm nhân viên'],
        ['name' => 'employee_edit', 'description' => 'Chỉnh sửa nhân viên'],
        ['name' => 'employee_view', 'description' => 'Xem thông tin nhân viên'],

        // Attendance
        ['name' => 'attendance_mark', 'description' => 'Chấm công'],
        ['name' => 'attendance_edit', 'description' => 'Chỉnh sửa chấm công'],
        ['name' => 'attendance_view', 'description' => 'Xem chấm công'],

        // Task
        ['name' => 'task_create', 'description' => 'Tạo công việc'],
        ['name' => 'task_edit', 'description' => 'Chỉnh sửa công việc'],
        ['name' => 'task_view', 'description' => 'Xem công việc'],

        // Reward
        ['name' => 'reward_add', 'description' => 'Thêm thưởng'],
        ['name' => 'reward_edit', 'description' => 'Chỉnh sửa thưởng'],
        ['name' => 'reward_view', 'description' => 'Xem thưởng'],

        // Disciplinary
        ['name' => 'disciplinary_add', 'description' => 'Thêm vi phạm'],
        ['name' => 'disciplinary_edit', 'description' => 'Chỉnh sửa vi phạm'],
        ['name' => 'disciplinary_view', 'description' => 'Xem vi phạm'],

        // Attachment
        ['name' => 'attachment_add', 'description' => 'Thêm hồ sơ đính kèm'],
        ['name' => 'attachment_edit', 'description' => 'Chỉnh sửa hồ sơ đính kèm'],
        ['name' => 'attachment_view', 'description' => 'Xem hồ sơ đính kèm'],

        // Plugin CRM
        // Customer
        ['name' => 'customer_add', 'description' => 'Thêm khách hàng'],
        ['name' => 'customer_edit', 'description' => 'Chỉnh sửa khách hàng'],
        ['name' => 'customer_view', 'description' => 'Xem khách hàng'],
        ['name' => 'customer_view_full', 'description' => 'Xem tất cả khách hàng'],


        // Customer Type
        ['name' => 'customer_type_add', 'description' => 'Thêm loại khách hàng'],
        ['name' => 'customer_type_edit', 'description' => 'Chỉnh sửa loại khách hàng'],
        ['name' => 'customer_type_view', 'description' => 'Xem loại khách hàng'],

        // Customer Source
        ['name' => 'customer_source_add', 'description' => 'Thêm nguồn khách hàng'],
        ['name' => 'customer_source_edit', 'description' => 'Chỉnh sửa nguồn khách hàng'],
        ['name' => 'customer_source_view', 'description' => 'Xem nguồn khách hàng'],

        // Plugin Order
        // Order
        ['name' => 'order_add', 'description' => 'Thêm đơn hàng'],
        ['name' => 'order_edit', 'description' => 'Chỉnh sửa đơn hàng'],
        ['name' => 'order_view', 'description' => 'Xem đơn hàng'],
        ['name' => 'order_view_full', 'description' => 'Xem tất cả đơn hàng'],

        // Order Status
        ['name' => 'order_status_add', 'description' => 'Thêm trạng thái đơn hàng'],
        ['name' => 'order_status_edit', 'description' => 'Chỉnh sửa trạng thái đơn hàng'],
        ['name' => 'order_status_view', 'description' => 'Xem trạng thái đơn hàng'],

        // Product
        ['name' => 'product_add', 'description' => 'Thêm sản phẩm'],
        ['name' => 'product_edit', 'description' => 'Chỉnh sửa sản phẩm'],
        ['name' => 'product_view', 'description' => 'Xem sản phẩm'],

        // Device
        // ['name' => 'device_add', 'description' => 'Thêm thiết bị'],
        ['name' => 'device_edit', 'description' => 'Chỉnh sửa thiết bị'],
        ['name' => 'device_view', 'description' => 'Xem thiết bị'],

        // Warehouse
        ['name' => 'warehouse_add', 'description' => 'Thêm kho'],
        ['name' => 'warehouse_view', 'description' => 'Xem kho'],
        ['name' => 'warehouse_edit', 'description' => 'Chỉnh sửa kho'],

        // Stock
        ['name' => 'stock_view', 'description' => 'Xem tồn kho'],
        ['name' => 'stock_adjustment', 'description' => 'Nhập/ Xuất kho'],
        ['name' => 'stock_transfer', 'description' => 'Chuyển kho'],
        ['name' => 'stocktake', 'description' => 'Kiểm kê tồn kho'],

        // Supplier
        ['name' => 'supplier_add', 'description' => 'Thêm nhà cung cấp'],
        ['name' => 'supplier_edit', 'description' => 'Chỉnh sửa nhà cung cấp'],
        ['name' => 'supplier_view', 'description' => 'Xem nhà cung cấp'],

    ];

    foreach ($permissions as $perm) {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}aerp_permissions WHERE name = %s",
            $perm['name']
        ));
        if (!$exists) {
            $wpdb->insert($wpdb->prefix . 'aerp_permissions', $perm);
        }
    }

    // 3. Seed Role-Permission mapping
    $role_permissions = [
        'admin' => [
            'salary_calculate',
            'salary_add',
            'salary_edit',
            'salary_view',
            'salary_advance_add', 
            'salary_advance_edit', 
            'employee_add',
            'employee_edit',
            'employee_view',
            'attendance_mark',
            'attendance_edit',
            'attendance_view',
            'task_create',
            'task_edit',
            'task_view',
            'reward_add', 
            'reward_edit', 
            'reward_view', 
            'disciplinary_add', 
            'disciplinary_edit', 
            'disciplinary_view',
            'attachment_add',
            'attachment_edit',
            'attachment_view',
            'customer_add',
            'customer_edit',
            'customer_view',
            'customer_view_full',
            'customer_type_add', 
            'customer_type_edit', 
            'customer_type_view',
            'order_add',
            'order_edit',
            'order_view',
            'order_view_full',
            'order_status_add',
            'order_status_edit', 
            'order_status_view', 
            'product_add',
            'product_edit',
            'product_view',
            // 'device_add',
            'device_edit',
            'device_view',
            'warehouse_add',
            'warehouse_edit',
            'warehouse_view',
            'stock_view',
            'stock_adjustment', 
            'stock_transfer',
            'stocktake',
            'supplier_add',
            'supplier_edit',
            'supplier_view'
        ],
        'department_lead' => [
            'salary_view',
            'employee_view',
            'employee_add', 
            'employee_edit', 
            'attendance_mark',
            'attendance_edit',
            'attendance_view',
            'task_create',
            'task_edit',
            'task_view',
            'reward_add', 
            'reward_edit', 
            'reward_view', 
            'disciplinary_add', 
            'disciplinary_edit', 
            'disciplinary_view',
            'attachment_add',
            'attachment_edit',
            'attachment_view',
            'customer_add', 
            'customer_edit', 
            'customer_view',
            'customer_view_full',
            'customer_type_add', 
            'customer_type_edit', 
            'customer_type_view',
            'order_add',
            'order_edit',
            'order_view', 
            'order_view_full',
            'order_status_add', 
            'order_status_edit', 
            'order_status_view', 
            'product_add',
            'product_edit',
            'product_view',
            // 'device_add',
            'device_edit',
            'device_view',
            'warehouse_add',
            'warehouse_view',
            'warehouse_edit', 
            'stock_view',
            'stock_adjustment', 
            'stock_transfer',
            'stocktake',
            'supplier_add',
            'supplier_edit',
            'supplier_view'
        ],
        'accountant' => [
            'salary_calculate',
            'salary_edit',
            'salary_view',
            'salary_add', 
            'salary_advance_add', 
            'salary_advance_edit', 
            'employee_view',
            'attendance_view',
            'order_edit',
            'order_view_full'
        ],
        'employee' => [
            'salary_view',
            'attendance_mark',
            'task_view'
        ]
    ];

    foreach ($role_permissions as $role_name => $permissions) {
        $role_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}aerp_roles WHERE name = %s",
            $role_name
        ));

        if ($role_id) {
            foreach ($permissions as $perm_name) {
                $perm_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}aerp_permissions WHERE name = %s",
                    $perm_name
                ));

                if ($perm_id) {
                    $exists = $wpdb->get_var($wpdb->prepare(
                        "SELECT 1 FROM {$wpdb->prefix}aerp_role_permission 
                        WHERE role_id = %d AND permission_id = %d",
                        $role_id,
                        $perm_id
                    ));

                    if (!$exists) {
                        $wpdb->insert($wpdb->prefix . 'aerp_role_permission', [
                            'role_id' => $role_id,
                            'permission_id' => $perm_id
                        ]);
                    }
                }
            }
        }
    }
}
