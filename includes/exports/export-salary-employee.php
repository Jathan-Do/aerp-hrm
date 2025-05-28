<?php
if (!defined('ABSPATH')) exit;

function salary_employee_export()
{
    global $wpdb;
    $employee_id = absint($_REQUEST['employee_id'] ?? 0);
    $month_raw = sanitize_text_field($_REQUEST['salary_month'] ?? date('Y-m'));


    $start = $month_raw . '-01';
    // var_dump("Export lương: employee_id={$employee_id}, salary_month={$start}");

    $query = "
        SELECT 
            e.employee_code, e.full_name, e.bank_account, e.bank_name,
            s.salary_month, s.base_salary, s.bonus, s.deduction, s.auto_bonus, 
            s.final_salary, s.advance_paid, s.points_total,
            s.work_days, s.off_days, s.ot_days
        FROM {$wpdb->prefix}aerp_hrm_salaries s
        LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON s.employee_id = e.id
        WHERE s.employee_id = %d AND s.salary_month = %s
    ";

    $row = $wpdb->get_row($wpdb->prepare($query, $employee_id, $start), ARRAY_A);

    $headers = [
        'employee_code'  => 'Mã NV',
        'full_name'      => 'Họ tên',
        'bank_account'   => 'Số TK',
        'bank_name'      => 'Ngân hàng',
        'salary_month'   => 'Tháng',
        'base_salary'    => 'Lương cơ bản',
        'bonus'          => 'Thưởng',
        'deduction'      => 'Phạt',
        'auto_bonus'     => 'Thưởng tự động',
        'work_days'      => 'Ngày công',
        'off_days'       => 'Ngày nghỉ',
        'ot_days'        => 'Tăng ca',
        'advance_paid'   => 'Tạm ứng',
        'final_salary'   => 'Lương cuối',
        'points_total'   => 'Điểm tổng',
    ];

    if (!class_exists('AERP_Excel_Export_Helper')) {
        require_once AERP_HRM_PATH . 'includes/class-excel-export-helper.php';
    }

    AERP_Excel_Export_Helper::export($headers, [$row], 'bang-luong-nhan-vien', 'Bảng lương');
}
