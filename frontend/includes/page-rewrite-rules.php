<?php
// === REWRITE RULES FOR FRONTEND DASHBOARD ===
add_action('init', function () {
    add_rewrite_rule('^aerp-dashboard/?$', 'index.php?aerp_dashboard=1', 'top');
    add_rewrite_rule('^aerp-categories/?$', 'index.php?aerp_categories=1', 'top');
    add_rewrite_rule('^aerp-departments/?$', 'index.php?aerp_departments=1', 'top');
    add_rewrite_rule('^aerp-company/?$', 'index.php?aerp_company=1', 'top');
    add_rewrite_rule('^aerp-position/?$', 'index.php?aerp_position=1', 'top');
    add_rewrite_rule('^aerp-work-location/?$', 'index.php?aerp_work_location=1', 'top');
    add_rewrite_rule('^aerp-setting/?$', 'index.php?aerp_setting=1', 'top');
    add_rewrite_rule('^aerp-discipline-rule/?$', 'index.php?aerp_discipline_rule=1', 'top');
    add_rewrite_rule('^aerp-ranking-settings/?$', 'index.php?aerp_ranking_settings=1', 'top');
    add_rewrite_rule('^aerp-reward-settings/?$', 'index.php?aerp_reward_settings=1', 'top');
    add_rewrite_rule('^aerp-kpi-settings/?$', 'index.php?aerp_kpi_settings=1', 'top');
    add_rewrite_rule('^aerp-salary-summary/?$', 'index.php?aerp_salary_summary=1', 'top');
    add_rewrite_rule('^aerp-salary-summary/view/([0-9]+)/?$', 'index.php?aerp_salary_summary=1&action=view&id=$matches[1]', 'top');
    add_rewrite_rule('^aerp-role/?$', 'index.php?aerp_role=1', 'top');
    add_rewrite_rule('^aerp-permission/?$', 'index.php?aerp_permission=1', 'top');
    add_rewrite_rule('^aerp-hrm-employees/?$', 'index.php?aerp_hrm_employees=1', 'top');
    add_rewrite_rule('^aerp-hrm-employees/view/([0-9]+)/?$', 'index.php?aerp_hrm_employees=1&action=view&id=$matches[1]', 'top');
    add_rewrite_rule('^aerp-hrm-employees/view/([0-9]+)/section/([a-zA-Z0-9_-]+)/?$', 'index.php?aerp_hrm_employees=1&action=view&id=$matches[1]&section=$matches[2]', 'top');



    $rules = get_option('rewrite_rules');
    if ($rules && (!isset($rules['^aerp-dashboard/?$']))) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-categories/?$'])) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-departments/?$'])) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-company/?$'])) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-position/?$'])) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-work-location/?$'])) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-setting/?$'])) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-discipline-rule/?$'])) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-ranking-settings/?$'])) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-reward-settings/?$'])) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-kpi-settings/?$'])) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-salary-summary/?$'])) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-role/?$'])) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-permission/?$'])) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-hrm-employees/?$'])) {
        flush_rewrite_rules();
    }
    if ($rules && !isset($rules['^aerp-hrm-employees/view/([0-9]+)/section/([a-zA-Z0-9_-]+)/?$'])) {
        flush_rewrite_rules();
    }
});

add_filter('query_vars', function ($vars) {
    $vars[] = 'aerp_dashboard';
    $vars[] = 'aerp_categories';
    $vars[] = 'aerp_departments';
    $vars[] = 'aerp_company';
    $vars[] = 'aerp_position';
    $vars[] = 'aerp_work_location';
    $vars[] = 'paged';
    $vars[] = 'orderby';
    $vars[] = 'order';
    $vars[] = 'aerp_setting';
    $vars[] = 'aerp_discipline_rule';
    $vars[] = 'aerp_ranking_settings';
    $vars[] = 'aerp_reward_settings';
    $vars[] = 'aerp_kpi_settings';
    $vars[] = 'aerp_salary_summary';
    $vars[] = 'id';
    $vars[] = 'aerp_role';
    $vars[] = 'aerp_permission';
    $vars[] = 'aerp_hrm_employees';
    $vars[] = 'section';
    return $vars;
});

add_action('template_redirect', function () {
    if (get_query_var('aerp_dashboard')) {
        include AERP_HRM_PATH . 'frontend/dashboard/dashboard.php';
        exit;
    }
    if (get_query_var('aerp_categories')) {
        include AERP_HRM_PATH . 'frontend/dashboard/categories.php';
        exit;
    }
    if (get_query_var('aerp_departments')) {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'add':
                include AERP_HRM_PATH . 'frontend/dashboard/departments/form-add.php';
                break;
            case 'edit':
                include AERP_HRM_PATH . 'frontend/dashboard/departments/form-edit.php';
                break;
            case 'delete':
                AERP_Frontend_Department_Manager::handle_single_delete();
                break;
            default:
                include AERP_HRM_PATH . 'frontend/dashboard/departments/list.php';
                break;
        }
        exit;
    }
    if (get_query_var('aerp_company')) {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'add':
                include AERP_HRM_PATH . 'frontend/dashboard/company/form-add.php';
                break;
            case 'edit':
                include AERP_HRM_PATH . 'frontend/dashboard/company/form-edit.php';
                break;
            case 'delete':
                AERP_Frontend_Company_Manager::handle_single_delete();
                break;
            default:
                include AERP_HRM_PATH . 'frontend/dashboard/company/list.php';
                break;
        }
        exit;
    }
    if (get_query_var('aerp_position')) {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'add':
                include AERP_HRM_PATH . 'frontend/dashboard/position/form-add.php';
                break;
            case 'edit':
                include AERP_HRM_PATH . 'frontend/dashboard/position/form-edit.php';
                break;
            case 'delete':
                AERP_Frontend_Position_Manager::handle_single_delete();
                break;
            default:
                include AERP_HRM_PATH . 'frontend/dashboard/position/list.php';
                break;
        }
        exit;
    }
    if (get_query_var('aerp_work_location')) {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'add':
                include AERP_HRM_PATH . 'frontend/dashboard/work-location/form-add.php';
                break;
            case 'edit':
                include AERP_HRM_PATH . 'frontend/dashboard/work-location/form-edit.php';
                break;
            case 'delete':
                AERP_Frontend_Work_Location_Manager::handle_single_delete();
                break;
            default:
                include AERP_HRM_PATH . 'frontend/dashboard/work-location/list.php';
                break;
        }
        exit;
    }
    if (get_query_var('aerp_setting')) {
        include AERP_HRM_PATH . 'frontend/dashboard/setting.php';
        exit;
    }
    if (get_query_var('aerp_discipline_rule')) {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'add':
                include AERP_HRM_PATH . 'frontend/dashboard/discipline-rule/form-add.php';
                break;
            case 'edit':
                include AERP_HRM_PATH . 'frontend/dashboard/discipline-rule/form-edit.php';
                break;
            case 'delete':
                AERP_Frontend_Discipline_Rule_Manager::handle_single_delete();
                break;
            default:
                include AERP_HRM_PATH . 'frontend/dashboard/discipline-rule/list.php';
                break;
        }
        exit;
    }
    if (get_query_var('aerp_ranking_settings')) {
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'add':
                include AERP_HRM_PATH . 'frontend/dashboard/ranking-setting/form-add.php';
                break;
            case 'edit':
                include AERP_HRM_PATH . 'frontend/dashboard/ranking-setting/form-edit.php';
                break;
            case 'delete':
                AERP_Frontend_Ranking_Settings_Manager::handle_single_delete();
                break;
            default:
                include AERP_HRM_PATH . 'frontend/dashboard/ranking-setting/list.php';
                break;
        }
        exit;
    }
    if (get_query_var('aerp_reward_settings')) {
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'add':
                include AERP_HRM_PATH . 'frontend/dashboard/reward/form-add.php';
                break;
            case 'edit':
                include AERP_HRM_PATH . 'frontend/dashboard/reward/form-edit.php';
                break;
            case 'delete':
                AERP_Frontend_Reward_Manager::handle_single_delete();
                break;
            default:
                include AERP_HRM_PATH . 'frontend/dashboard/reward/list.php';
                break;
        }
        exit;
    }
    if (get_query_var('aerp_kpi_settings')) {
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'add':
                include AERP_HRM_PATH . 'frontend/dashboard/kpi-setting/form-add.php';
                break;
            case 'edit':
                include AERP_HRM_PATH . 'frontend/dashboard/kpi-setting/form-edit.php';
                break;
            case 'delete':
                AERP_Frontend_KPI_Settings_Manager::handle_single_delete();
                break;
            default:
                include AERP_HRM_PATH . 'frontend/dashboard/kpi-setting/list.php';
                break;
        }
        exit;
    }
    if (get_query_var('aerp_salary_summary')) {
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'view':
                include AERP_HRM_PATH . 'frontend/dashboard/salary-summary/view-detail.php';
                break;
            default:
                include AERP_HRM_PATH . 'frontend/dashboard/salary-summary/list.php';
                break;
        }
        exit;
    }
    if (get_query_var('aerp_role')) {
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'add':
                include AERP_HRM_PATH . 'frontend/dashboard/role/form-add.php';
                break;
            case 'edit':
                include AERP_HRM_PATH . 'frontend/dashboard/role/form-edit.php';
                break;
            case 'delete':
                AERP_Frontend_Role_Manager::handle_single_delete();
                break;
            default:
                include AERP_HRM_PATH . 'frontend/dashboard/role/list.php';
                break;
        }
        exit;
    }
    if (get_query_var('aerp_permission')) {
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'add':
                include AERP_HRM_PATH . 'frontend/dashboard/permission/form-add.php';
                break;
            case 'edit':
                include AERP_HRM_PATH . 'frontend/dashboard/permission/form-edit.php';
                break;
            case 'delete':
                AERP_Frontend_Role_Manager::handle_single_delete();
                break;
            default:
                include AERP_HRM_PATH . 'frontend/dashboard/permission/list.php';
                break;
        }
        exit;
    }
    if (get_query_var('aerp_hrm_employees')) {
        $action = $_GET['action'] ?? '';
        $section = $_GET['section'] ?? get_query_var('section') ?? '';
        switch ($action) {
            case 'add':
                include AERP_HRM_PATH . 'frontend/dashboard/employees/employee/form-add.php';
                break;
            case 'edit':
                include AERP_HRM_PATH . 'frontend/dashboard/employees/employee/form-edit.php';
                break;
            case 'delete':
                AERP_Frontend_Employee_Manager::handle_single_delete();
                break;
            case 'view':
                if ($section === 'advance') {
                    $sub_action = $_GET['sub_action'] ?? '';
                    switch ($sub_action) {
                        case 'add':
                            include AERP_HRM_PATH . 'frontend/dashboard/employees/salary/form-advance.php';
                            break;
                        case 'edit':
                            include AERP_HRM_PATH . 'frontend/dashboard/employees/salary/form-advance.php';
                            break;
                        case 'delete':
                            AERP_Frontend_Advance_Manager::handle_single_delete();
                            break;
                        default:
                            include AERP_HRM_PATH . 'frontend/dashboard/employees/salary/page-advance.php';
                            break;
                    }
                } elseif ($section === 'salary_config') {
                    $sub_action = $_GET['sub_action'] ?? '';
                    switch ($sub_action) {
                        case 'add':
                            include AERP_HRM_PATH . 'frontend/dashboard/employees/salary/form-salary-config.php';
                            break;
                        case 'edit':
                            include AERP_HRM_PATH . 'frontend/dashboard/employees/salary/form-salary-config.php';
                            break;
                        case 'delete':
                            AERP_Frontend_Salary_Config_Manager::handle_single_delete();
                            break;
                        default:
                            include AERP_HRM_PATH . 'frontend/dashboard/employees/salary/page-salary-config.php';
                            break;
                    }
                } elseif ($section === 'task' && isset($_GET['sub_action'])) {
                    $sub_action = $_GET['sub_action'];
                    if ($sub_action === 'add') {
                        include AERP_HRM_PATH . 'frontend/dashboard/employees/task/page-task-add.php';
                        exit;
                    } elseif ($sub_action === 'edit') {
                        include AERP_HRM_PATH . 'frontend/dashboard/employees/task/page-task-edit.php';
                        exit;
                    } elseif ($sub_action === 'delete') {
                        AERP_Frontend_Task_Manager::handle_single_delete();
                        exit;
                    }
                } elseif ($section === 'discipline' && isset($_GET['sub_action'])) {
                    $sub_action = $_GET['sub_action'];
                    if ($sub_action === 'add') {
                        include AERP_HRM_PATH . 'frontend/dashboard/employees/discipline/page-discipline-add.php';
                        exit;
                    }
                } elseif ($section === 'reward' && isset($_GET['sub_action'])) {
                    $sub_action = $_GET['sub_action'];
                    if ($sub_action === 'add') {
                        include AERP_HRM_PATH . 'frontend/dashboard/employees/reward/page-employee-reward-add.php';
                        exit;
                    } elseif ($sub_action === 'edit') {
                        include AERP_HRM_PATH . 'frontend/dashboard/employees/reward/page-employee-reward-edit.php';
                        exit;
                    } elseif ($sub_action === 'delete') {
                        AERP_Frontend_Employee_Reward_Manager::handle_single_delete();
                        exit;
                    }
                } elseif ($section === 'adjustment' && isset($_GET['sub_action'])) {
                    $sub_action = $_GET['sub_action'];
                    if ($sub_action === 'add') {
                        include AERP_HRM_PATH . 'frontend/dashboard/employees/adjustment/page-adjustment-add.php';
                        exit;
                    } elseif ($sub_action === 'edit') {
                        include AERP_HRM_PATH . 'frontend/dashboard/employees/adjustment/page-adjustment-edit.php';
                        exit;
                    } elseif ($sub_action === 'delete') {
                        AERP_Frontend_Adjustment_Manager::handle_single_delete();
                        exit;
                    }
                } elseif ($section === 'attachment' && isset($_GET['sub_action'])) {
                    $sub_action = $_GET['sub_action'];
                    if ($sub_action === 'add') {
                        include AERP_HRM_PATH . 'frontend/dashboard/employees/attachment/page-attachment-add.php';
                        exit;
                    } elseif ($sub_action === 'edit') {
                        include AERP_HRM_PATH . 'frontend/dashboard/employees/attachment/page-attachment-edit.php';
                        exit;
                    } elseif ($sub_action === 'delete') {
                        AERP_Frontend_Attachment_Manager::handle_single_delete();
                        exit;
                    }
                } elseif ($section === 'attendance' && isset($_GET['sub_action'])) {
                    $sub_action = $_GET['sub_action'];
                    if ($sub_action === 'add') {
                        include AERP_HRM_PATH . 'frontend/dashboard/employees/attendance/page-attendance-add.php';
                        exit;
                    } elseif ($sub_action === 'edit') {
                        include AERP_HRM_PATH . 'frontend/dashboard/employees/attendance/page-attendance-edit.php';
                        exit;
                    } elseif ($sub_action === 'delete') {
                        AERP_Frontend_Attendance_Manager::handle_single_delete();
                        exit;
                    }
                } else {
                    include AERP_HRM_PATH . 'frontend/dashboard/employees/form-view.php';
                }
                break;

            default:
                include AERP_HRM_PATH . 'frontend/dashboard/employees/employee/list.php';
                break;
        }
        exit;
    }
});

// Tắt canonical redirect cho các trang HRM ảo (refactor)
add_action('template_redirect', function () {
    $virtual_routes = ['aerp_company', 'aerp_departments', 'aerp_position', 'aerp_work_location', 'aerp_discipline_rule', 'aerp_reward_settings', 'aerp_ranking_settings', 'aerp_kpi_settings', 'aerp_role', 'aerp_permission', 'aerp_hrm_employees'];
    foreach ($virtual_routes as $route) {
        if (get_query_var($route)) {
            remove_filter('template_redirect', 'redirect_canonical');
            break;
        }
    }
}, 0);
