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
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    foreach ($sqls as $sql) {
        dbDelta($sql);
    }
}
