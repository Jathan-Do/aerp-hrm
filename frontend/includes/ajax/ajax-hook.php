<?php
add_action('wp_ajax_aerp_hrm_employee_tab_content', function() {
    $employee_id = absint($_POST['id'] ?? 0);
    $section = sanitize_text_field($_POST['section'] ?? 'detail-view');
    ob_start();
    switch ($section) {
        case 'salary':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/salary/tab-salary.php';
            break;
        case 'task':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/task/tab-task.php';
            break;
        case 'discipline':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/discipline/tab-discipline.php';
            break;
        case 'reward':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/reward/tab-reward.php';
            break;
        case 'adjustment':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/adjustment/tab-adjustment.php';
            break;
        case 'attachment':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/attachment/tab-attachment.php';
            break;
        case 'attendance':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/attendance/tab-attendance.php';
            break;
        case 'journey':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/journey/tab-employee-journey.php';
            break;
        default:
            include AERP_HRM_PATH . 'frontend/dashboard/employees/employee/tab-view-detail.php';
            break;
    }
    $html = ob_get_clean();
    echo $html;
    wp_die();
});

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


add_action('wp_ajax_aerp_hrm_filter_employees', 'aerp_hrm_filter_employees_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_employees', 'aerp_hrm_filter_employees_callback');
function aerp_hrm_filter_employees_callback()
{
    $filters = [
        'department_id' => sanitize_text_field($_POST['department_id'] ?? ''),
        'position_id' => sanitize_text_field($_POST['position_id'] ?? ''),
        'work_location_id' => sanitize_text_field($_POST['work_location_id'] ?? ''),
        'status' => sanitize_text_field($_POST['status'] ?? ''),
        'birthday_month' => sanitize_text_field($_POST['birthday_month'] ?? ''),
        'join_date_from' => sanitize_text_field($_POST['join_date_from'] ?? ''),
        'join_date_to' => sanitize_text_field($_POST['join_date_to'] ?? ''),
        'off_date_from' => sanitize_text_field($_POST['off_date_from'] ?? ''),
        'off_date_to' => sanitize_text_field($_POST['off_date_to'] ?? ''),
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Employee_Table();
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_salary', 'aerp_hrm_filter_salary_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_salary', 'aerp_hrm_filter_salary_callback');
function aerp_hrm_filter_salary_callback()
{
    $filters = [
        'employee_id' => sanitize_text_field($_POST['employee_id'] ?? ''),
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Salary_Table([
        'employee_id' => $filters['employee_id'],
    ]);
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_advance', 'aerp_hrm_filter_advance_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_advance', 'aerp_hrm_filter_advance_callback');
function aerp_hrm_filter_advance_callback()
{
    $filters = [
        'employee_id' => sanitize_text_field($_POST['employee_id'] ?? ''),
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Advance_Table([
        'employee_id' => $filters['employee_id'],
    ]);
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_salary_config', 'aerp_hrm_filter_salary_config_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_salary_config', 'aerp_hrm_filter_salary_config_callback');
function aerp_hrm_filter_salary_config_callback()
{
    $filters = [
        'employee_id' => sanitize_text_field($_POST['employee_id'] ?? ''),
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Salary_Config_Table([
        'employee_id' => $filters['employee_id'],
    ]);
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_task', 'aerp_hrm_filter_task_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_task', 'aerp_hrm_filter_task_callback');
function aerp_hrm_filter_task_callback()
{
    $filters = [
        'employee_id' => sanitize_text_field($_POST['employee_id'] ?? ''),
        'status' => sanitize_text_field($_POST['status'] ?? ''),
        'month' => sanitize_text_field($_POST['month'] ?? ''),
        'year' => sanitize_text_field($_POST['year'] ?? ''),
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Task_Table([
        'employee_id' => $filters['employee_id'],
    ]);
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_discipline_log', 'aerp_hrm_filter_discipline_log_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_discipline_log', 'aerp_hrm_filter_discipline_log_callback');
function aerp_hrm_filter_discipline_log_callback()
{
    $filters = [
        'employee_id' => sanitize_text_field($_POST['employee_id'] ?? ''),
        'violation_month' => sanitize_text_field($_POST['violation_month'] ?? ''),
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Discipline_Log_Table([
        'employee_id' => $filters['employee_id'],
    ]);
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_aerp_hrm_filter_employee_reward', 'aerp_hrm_filter_employee_reward_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_employee_reward', 'aerp_hrm_filter_employee_reward_callback');
function aerp_hrm_filter_employee_reward_callback()
{
    $filters = [
        'employee_id' => sanitize_text_field($_POST['employee_id'] ?? ''),
        'month' => sanitize_text_field($_POST['month'] ?? ''),
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Employee_Reward_Table([
        'employee_id' => $filters['employee_id'],
    ]);
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_adjustment', 'aerp_hrm_filter_adjustment_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_adjustment', 'aerp_hrm_filter_adjustment_callback');
function aerp_hrm_filter_adjustment_callback()
{
    $filters = [
        'employee_id' => sanitize_text_field($_POST['employee_id'] ?? ''),
        'month' => sanitize_text_field($_POST['month'] ?? ''),
        'type' => sanitize_text_field($_POST['type'] ?? ''),
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Adjustment_Table([
        'employee_id' => $filters['employee_id'],
    ]);
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_aerp_hrm_filter_attachment', 'aerp_hrm_filter_attachment_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_attachment', 'aerp_hrm_filter_attachment_callback');
function aerp_hrm_filter_attachment_callback()
{
    $filters = [
        'employee_id' => sanitize_text_field($_POST['employee_id'] ?? ''),
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Attachment_Table([
        'employee_id' => $filters['employee_id'],
    ]);
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_hrm_filter_attendance', 'aerp_hrm_filter_attendance_callback');
add_action('wp_ajax_nopriv_aerp_hrm_filter_attendance', 'aerp_hrm_filter_attendance_callback');
function aerp_hrm_filter_attendance_callback()
{
    $filters = [
        'employee_id' => sanitize_text_field($_POST['employee_id'] ?? ''),
        'work_date' => sanitize_text_field($_POST['work_date'] ?? ''),
        'shift' => sanitize_text_field($_POST['shift'] ?? ''),
        'search_term' => sanitize_text_field($_POST['s'] ?? ''),
        'paged' => intval($_POST['paged'] ?? 1),
        'orderby' => sanitize_text_field($_POST['orderby'] ?? ''),
        'order' => sanitize_text_field($_POST['order'] ?? ''),
    ];
    $table = new AERP_Frontend_Attendance_Table([
        'employee_id' => $filters['employee_id'],
    ]);
    $table->set_filters($filters);
    ob_start();
    $table->render();
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('wp_ajax_aerp_order_search_employees', function() {
    $q = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
    $employees = function_exists('aerp_get_employees_with_location_select2') ? aerp_get_employees_with_location_select2($q) : [];
    $results = [];
    $count = 0;
    foreach ($employees as $employee) {
        $display_name = $employee->full_name;
        if (!empty($employee->work_location_name)) {
            $display_name .= ' - ' . $employee->work_location_name;
        }
        $results[] = [
            'id' => $employee->id,
            'text' => $display_name,
        ];
        if (!$q && ++$count >= 20) break;
    }
    wp_send_json($results);
});
