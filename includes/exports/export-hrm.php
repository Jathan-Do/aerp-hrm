<?php
function hrm_summary_report_export()
{
    $month = sanitize_text_field($_POST['report_month'] ?? date('Y-m'));
    $month = $month . '-01';
    if (!is_string($month) || empty($month)) {
        wp_die('❌ Tháng không hợp lệ.');
    }

    // Lấy dữ liệu
    $summary     = AERP_Report_Manager::get_summary($month);
    $performance = AERP_Report_Manager::get_performance_data($month);
    $tenure      = AERP_Report_Manager::get_tenure_data();
    $departments = AERP_Report_Manager::get_department_data();
    $salary = AERP_Report_Manager::get_salary_data($month);


    // Bắt đầu mảng Excel
    $rows = [];

    $rows[] = ['📊 BÁO CÁO NHÂN SỰ THÁNG ' . date('m/Y', strtotime($month))];
    $rows[] = [];
    $rows[] = ['Tổng nhân sự', intval($summary['total'])];
    $rows[] = ['Vào làm', intval($summary['joined'])];
    $rows[] = ['Nghỉ việc', intval($summary['resigned'])];

    // Hiệu suất
    if (!empty($performance)) {
        $rows[] = [];
        $rows[] = ['Hiệu suất theo phòng ban'];
        $rows[] = ['Phòng ban ID', 'Số task', 'Điểm TB'];
        foreach ($performance as $p) {
            $rows[] = [
                intval($p['department_id']),
                intval($p['total_tasks']),
                round(floatval($p['avg_score']), 1)
            ];
        }
    }

    // Thâm niên
    if (!empty($tenure)) {
        $rows[] = [];
        $rows[] = ['Phân bố thâm niên'];
        $rows[] = ['Số năm', 'Số lượng'];
        foreach ($tenure as $t) {
            $rows[] = [$t['years'] . ' năm', intval($t['count'])];
        }
    }

    // Phân bố phòng ban
    if (!empty($departments)) {
        $rows[] = [];
        $rows[] = ['Phân bố phòng ban'];
        $rows[] = ['Phòng ban ID', 'Số lượng'];
        foreach ($departments as $d) {
            $rows[] = [intval($d['department_id']), intval($d['employee_count'])];
        }
    }

    // Chi phí lương
    if (!empty($salary)) {
        $rows[] = [];
        $rows[] = ['Chi phí lương'];
        $rows[] = ['Phòng ban ID', 'Tổng lương', 'Lương TB'];

        foreach ($salary as $s) {
            $rows[] = [
                intval($s['department_id']),
                round(floatval($s['total_cost'])),
                round(floatval($s['avg_salary']))
            ];
        }
    }

    // Gọi export
    if (!class_exists('AERP_Excel_Export_Helper')) {
        require_once AERP_HRM_PATH . 'includes/class-excel-export-helper.php';
    }

    AERP_Excel_Export_Helper::export_raw($rows, 'bao-cao-nhan-su', 'Báo cáo nhân sự');
}
