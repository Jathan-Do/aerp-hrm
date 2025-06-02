<?php
if (!defined('ABSPATH')) exit;

class AERP_Report_Manager
{
    public static function get_summary($month)
    {
        global $wpdb;

        $start = date('Y-m-01', strtotime($month));
        $end   = date('Y-m-t', strtotime($month));

        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_employees WHERE status = 'active'");
        $joined = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_employees
            WHERE join_date BETWEEN %s AND %s
        ", $start, $end));
        $resigned = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_employees
            WHERE off_date BETWEEN %s AND %s AND status = 'resigned'
        ", $start, $end));

        return compact('total', 'joined', 'resigned');
    }

    public static function get_performance_data($month)
    {
        global $wpdb;

        $start = date('Y-m-01', strtotime($month));
        $end   = date('Y-m-t', strtotime($month));

        return $wpdb->get_results("
            SELECT e.department_id, COUNT(t.id) as total_tasks, AVG(t.score) as avg_score
            FROM {$wpdb->prefix}aerp_hrm_tasks t
            LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON t.employee_id = e.id
            WHERE t.status = 'done' AND t.deadline BETWEEN '$start' AND '$end'
            GROUP BY e.department_id
        ", ARRAY_A);
    }

    public static function get_tenure_data()
    {
        global $wpdb;

        return $wpdb->get_results("
            SELECT FLOOR(TIMESTAMPDIFF(MONTH, join_date, CURDATE())/12) AS years, COUNT(*) as count
            FROM {$wpdb->prefix}aerp_hrm_employees
            WHERE status = 'active'
            GROUP BY years
        ", ARRAY_A);
    }

    public static function get_department_data()
    {
        global $wpdb;

        return $wpdb->get_results("
            SELECT department_id, COUNT(*) as employee_count
            FROM {$wpdb->prefix}aerp_hrm_employees
            WHERE status = 'active'
            GROUP BY department_id
        ", ARRAY_A);
    }

    public static function get_salary_data($month)
    {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT e.department_id, SUM(s.final_salary) as total_cost, AVG(s.final_salary) as avg_salary
            FROM {$wpdb->prefix}aerp_hrm_salaries s
            LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON s.employee_id = e.id
            WHERE s.salary_month = %s
            GROUP BY e.department_id
        ", $month), ARRAY_A);
    }
}
