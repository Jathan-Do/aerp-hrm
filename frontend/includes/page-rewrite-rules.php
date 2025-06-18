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
});

// Tắt canonical redirect cho các trang HRM ảo (refactor)
add_action('template_redirect', function() {
    $virtual_routes = ['aerp_company', 'aerp_departments', 'aerp_position', 'aerp_work_location'];
    foreach ($virtual_routes as $route) {
        if (get_query_var($route)) {
            remove_filter('template_redirect', 'redirect_canonical');
            break;
        }
    }
}, 0);
