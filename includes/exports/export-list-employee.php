<?php
if (!defined('ABSPATH')) exit;

function employee_list_export()
{
    global $wpdb;

    $results = $wpdb->get_results("
        SELECT e.employee_code, e.full_name, e.gender, e.birthday,
               e.phone_number, e.email, e.join_date, e.status,
               e.current_points,
               d.name AS department_name,
               p.name AS position_name
        FROM {$wpdb->prefix}aerp_hrm_employees e
        LEFT JOIN {$wpdb->prefix}aerp_hrm_departments d ON e.department_id = d.id
        LEFT JOIN {$wpdb->prefix}aerp_hrm_positions p ON e.position_id = p.id
        ORDER BY e.full_name ASC
    ", ARRAY_A);

    // if (empty($results)) {
    //     wp_die('⚠️ Không có dữ liệu nhân sự để xuất.');
    // }

    // Tiêu đề cột
    $headers = [
        'employee_code'     => 'Mã NV',
        'full_name'         => 'Họ tên',
        'gender'            => 'Giới tính',
        'birthday'          => 'Ngày sinh',
        'phone_number'      => 'SĐT',
        'email'             => 'Email',
        'join_date'         => 'Ngày vào',
        'status'            => 'Trạng thái',
        'current_points'    => 'Điểm',
        'department_name'   => 'Phòng ban',
        'position_name'     => 'Chức vụ'
    ];

    // Gọi hàm export tái sử dụng
    if (!class_exists('AERP_Excel_Export_Helper')) {
        require_once AERP_HRM_PATH . 'includes/class-excel-export-helper.php';
    }

    AERP_Excel_Export_Helper::export($headers, $results, 'danh-sach-nhan-su', 'Danh sách nhân sự');
}
