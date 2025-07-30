<?php
if (!defined('ABSPATH')) exit;

class AERP_Report_Manager
{
    public static function get_summary($month, $work_location_id = 0)
    {
        global $wpdb;
        $end   = date('Y-m-t', strtotime($month));
        $where = '';
        $params = [];
        if ($work_location_id) {
            $where = ' AND work_location_id = %d';
            $params[] = $work_location_id;
        }
        // Tổng nhân sự: đã vào làm trước hoặc trong tháng (bất kể trạng thái)
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_employees
         WHERE join_date <= %s $where",
            $end,
            ...$params
        ));
        // Đang làm: đã vào làm trước hoặc trong tháng và còn active
        $joined = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_employees
         WHERE join_date <= %s AND status = 'active' $where",
            $end,
            ...$params
        ));
        // Nghỉ việc: đã vào làm trước hoặc trong tháng và đã nghỉ
        $resigned = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_employees
         WHERE join_date <= %s AND status = 'resigned' $where",
            $end,
            ...$params
        ));
        return compact('total', 'joined', 'resigned');
    }

    public static function get_performance_data($month, $work_location_id = 0)
    {
        global $wpdb;
        $start = date('Y-m-01', strtotime($month));
        $end   = date('Y-m-t', strtotime($month));
        $where = '';
        $params = [];
        if ($work_location_id) {
            $where = ' AND e.work_location_id = %d';
            $params[] = $work_location_id;
        }
        return $wpdb->get_results($wpdb->prepare(
            "SELECT e.department_id, COUNT(t.id) as total_tasks, AVG(t.score) as avg_score
            FROM {$wpdb->prefix}aerp_hrm_tasks t
            LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON t.employee_id = e.id
            WHERE t.status = 'done' AND t.deadline BETWEEN %s AND %s $where
            GROUP BY e.department_id",
            $start,
            $end,
            ...$params
        ), ARRAY_A);
    }

    public static function get_tenure_data($work_location_id = 0)
    {
        global $wpdb;
        $where = '';
        $params = [];
        if ($work_location_id) {
            $where = ' AND work_location_id = %d';
            $params[] = $work_location_id;
        }
        return $wpdb->get_results($wpdb->prepare(
            "SELECT FLOOR(TIMESTAMPDIFF(MONTH, join_date, CURDATE())/12) AS years, COUNT(*) as count
            FROM {$wpdb->prefix}aerp_hrm_employees
            WHERE status = 'active' $where
            GROUP BY years",
            ...$params
        ), ARRAY_A);
    }

    public static function get_department_data($work_location_id = 0)
    {
        global $wpdb;
        $where = '';
        $params = [];
        if ($work_location_id) {
            $where = ' AND e.work_location_id = %d';
            $params[] = $work_location_id;
        }
        return $wpdb->get_results($wpdb->prepare(
            "SELECT e.department_id, d.name as department_name, COUNT(*) as employee_count
            FROM {$wpdb->prefix}aerp_hrm_employees e
            LEFT JOIN {$wpdb->prefix}aerp_hrm_departments d ON e.department_id = d.id
            WHERE e.status = 'active' $where
            GROUP BY e.department_id, d.name",
            ...$params
        ), ARRAY_A);
    }

    public static function get_salary_data($month, $work_location_id = 0)
    {
        global $wpdb;
        $where = '';
        $params = [];
        if ($work_location_id) {
            $where = ' AND e.work_location_id = %d';
            $params[] = $work_location_id;
        }
        // Chuẩn hóa $month về dạng YYYY-MM-01 nếu chỉ có YYYY-MM
        if (strlen($month) == 7) {
            $month = $month . '-01';
        }
        return $wpdb->get_results($wpdb->prepare(
            "SELECT e.department_id, d.name as department_name, SUM(s.final_salary) as total_cost, AVG(s.final_salary) as avg_salary
             FROM {$wpdb->prefix}aerp_hrm_salaries s
             LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON s.employee_id = e.id
             LEFT JOIN {$wpdb->prefix}aerp_hrm_departments d ON e.department_id = d.id
             WHERE s.salary_month = %s $where
             GROUP BY e.department_id, d.name",
            $month,
            ...$params
        ), ARRAY_A);
    }
}
