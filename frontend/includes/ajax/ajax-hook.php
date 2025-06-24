<?php
add_action('wp_ajax_aerp_hrm_filter_company', 'aerp_hrm_filter_company_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_company', 'aerp_hrm_filter_company_callback');
function aerp_hrm_filter_company_callback()
{
    $filters = [
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Company_Table();
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_department', 'aerp_hrm_filter_department_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_department', 'aerp_hrm_filter_department_callback');
function aerp_hrm_filter_department_callback()
{
    $filters = [
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Department_Table();
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_position', 'aerp_hrm_filter_position_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_position', 'aerp_hrm_filter_position_callback');
function aerp_hrm_filter_position_callback()
{
    $filters = [
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Position_Table();
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_work_location', 'aerp_hrm_filter_work_location_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_work_location', 'aerp_hrm_filter_work_location_callback');
function aerp_hrm_filter_work_location_callback()
{
    $filters = [
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Work_Location_Table();
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_discipline_rule', 'aerp_hrm_filter_discipline_rule_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_discipline_rule', 'aerp_hrm_filter_discipline_rule_callback');
function aerp_hrm_filter_discipline_rule_callback()
{
    $filters = [
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Discipline_Rule_Table();
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_ranking_settings', 'aerp_hrm_filter_ranking_settings_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_ranking_settings', 'aerp_hrm_filter_ranking_settings_callback');
function aerp_hrm_filter_ranking_settings_callback()
{
    $filters = [
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Ranking_Settings_Table();
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_reward', 'aerp_hrm_filter_reward_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_reward', 'aerp_hrm_filter_reward_callback');
function aerp_hrm_filter_reward_callback()
{
    $filters = [
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Reward_Table();
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_kpi_settings', 'aerp_hrm_filter_kpi_settings_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_kpi_settings', 'aerp_hrm_filter_kpi_settings_callback');
function aerp_hrm_filter_kpi_settings_callback()
{
    $filters = [
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_KPI_Settings_Table();
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_salary_summary', 'aerp_hrm_filter_salary_summary_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_salary_summary', 'aerp_hrm_filter_salary_summary_callback');
function aerp_hrm_filter_salary_summary_callback()
{
    $filters = [
        'salary_month' => sanitize_text_field($_POST['salary_month'] ?? ''),
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Salary_Summary_Table();
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_role', 'aerp_hrm_filter_role_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_role', 'aerp_hrm_filter_role_callback');
function aerp_hrm_filter_role_callback()
{
    $filters = [
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Role_Table();
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_permission', 'aerp_hrm_filter_permission_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_permission', 'aerp_hrm_filter_permission_callback');
function aerp_hrm_filter_permission_callback()
{
    $filters = [
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Permission_Table();
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}