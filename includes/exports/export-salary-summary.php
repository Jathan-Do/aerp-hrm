<?php
if (!defined('ABSPATH')) exit;

function salary_summary_export()
{
    global $wpdb;
    $month = sanitize_text_field($_POST['salary_month'] ?? '');

    $where = '';
    $args = [];

    if ($month) {
        $where = 'WHERE s.salary_month = %s';
        $args[] = $month . '-01';
    }

    $sql = "
        SELECT 
            e.employee_code, e.full_name, e.email, e.bank_name, e.bank_account,
            s.salary_month, s.base_salary, s.bonus, s.deduction, s.adjustment, 
            s.advance_paid, s.final_salary, s.points_total
        FROM {$wpdb->prefix}aerp_hrm_salaries s
        LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON s.employee_id = e.id
        $where
        ORDER BY s.salary_month DESC
    ";

    $data = $wpdb->get_results($wpdb->prepare($sql, ...$args), ARRAY_A);

    $headers = [
        'employee_code' => 'Mã NV',
        'full_name'     => 'Họ tên',
        'email'         => 'Email',
        'bank_name'     => 'Ngân hàng',
        'bank_account'  => 'Số TK',
        'salary_month'  => 'Tháng',
        'base_salary'   => 'Lương cơ bản',
        'bonus'         => 'Thưởng',
        'deduction'     => 'Phạt',
        'adjustment'    => 'Điều chỉnh',
        'advance_paid'  => 'Tạm ứng',
        'final_salary'  => 'Lương cuối',
        'points_total'  => 'Điểm',
    ];

    if (!class_exists('AERP_Excel_Export_Helper')) {
        require_once AERP_HRM_PATH . 'includes/class-excel-export-helper.php';
    }

    AERP_Excel_Export_Helper::export($headers, $data, 'tong-hop-luong', 'Lương tổng hợp');
}
