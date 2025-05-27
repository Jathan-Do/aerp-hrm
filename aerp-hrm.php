<?php
/**
 * Plugin Name: AERP Core – Quản trị doanh nghiệp
 * Description: Plugin tổng hợp gồm nền tảng (framework) và nhân sự (HRM) của hệ thống AERP.
 * Version: 1.0.0
 * Author: Truong Thinh Group
 * Text Domain: aerp-core
 */


if (!defined('ABSPATH')) exit;

// Constants
define('AERP_HRM_PATH', plugin_dir_path(__FILE__));
define('AERP_HRM_URL', plugin_dir_url(__FILE__));
define('AERP_HRM_VERSION', '1.0.0');

// Kiểm tra bản Pro
if (!function_exists('aerp_hrm_is_pro')) {
    function aerp_hrm_is_pro()
    {
        return function_exists('aerp_is_pro_module') && aerp_is_pro_module('hrm');
    }
}

// Khởi tạo plugin
function aerp_hrm_init()
{
    // Cảnh báo nếu thiếu framework
    // if (!function_exists('aerp_get_departments')) {
    //     add_action('admin_notices', function () {
    //         echo '<div class="error"><p><strong>AERP HRM</strong> yêu cầu plugin nền <strong>AERP Framework</strong> để hoạt động.</p></div>';
    //     });
    //     return;
    // }
    //load func dùng chung
    require_once AERP_HRM_PATH . 'includes/functions-common.php';

    // Table 
    require_once AERP_HRM_PATH . 'includes/table/class-base-table.php';
    require_once AERP_HRM_PATH . 'includes/table/table-employee.php';
    require_once AERP_HRM_PATH . 'includes/table/table-department.php';
    require_once AERP_HRM_PATH . 'includes/table/table-position.php';
    require_once AERP_HRM_PATH . 'includes/table/table-company.php';
    // Load các class cần thiết
    $includes = [
        'class-department-manager.php',
        'class-position-manager.php',
        'class-company-manager.php',
        'class-employee-manager.php',
        'class-settings-manager.php',
        'class-salary-manager.php',
        'class-task-manager.php',
        'class-attachment-manager.php',
        'class-attendance-manager.php',
        'class-advance-manager.php',
        'class-discipline-manager.php',
        'class-adjustment-manager.php',
        'class-report-manager.php',
        'class-excel-export-helper.php',
        'class-export-controller.php',
    ];
    foreach ($includes as $file) {
        require_once AERP_HRM_PATH . 'includes/' . $file;
    }


    // Shortcodes frontend
    require_once AERP_HRM_PATH . 'includes/shortcodes/shortcode-hr-profile.php';
    require_once AERP_HRM_PATH . 'includes/shortcodes/shortcode-task-list.php';
    require_once AERP_HRM_PATH . 'includes/shortcodes/shortcode-attendance.php';

    // Xử lý form và logic
    $managers = [
        'AERP_Position_Manager',
        'AERP_Department_Manager',
        'AERP_Company_Manager',
        'AERP_Employee_Manager',
        'AERP_Task_Manager',
        'AERP_Attachment_Manager',
        'AERP_Attendance_Manager',
        'AERP_Advance_Manager',
        'AERP_Discipline_Manager',
        'AERP_Adjustment_Manager',
    ];
    foreach ($managers as $manager) {
        if (method_exists($manager, 'handle_submit')) {
            add_action('admin_init', [$manager, 'handle_submit']);
        }
        if (method_exists($manager, 'handle_form_submit')) {
            add_action('admin_init', [$manager, 'handle_form_submit']);
        }
        if (method_exists($manager, 'handle_delete')) {
            add_action('admin_init', [$manager, 'handle_delete']);
        }
    }

    // Admin menu
    if (is_admin()) {
        add_action('admin_menu', ['AERP_HRM_Settings_Manager', 'register_admin_menu']);
    }

    // Tải asset frontend
    add_action('wp_enqueue_scripts', function () {
        if (!is_admin()) {
            wp_enqueue_style('aerp-hrm-frontend', AERP_HRM_URL . 'assets/css/frontend.css', [], '1.0');
            wp_enqueue_script('aerp-hrm-frontend', AERP_HRM_URL . 'assets/js/frontend.js', ['jquery'], '1.0', true);
        }
    });

    // Lên lịch check công việc trễ
    if (!wp_next_scheduled('aerp_check_late_tasks_daily')) {
        wp_schedule_event(time(), 'daily', 'aerp_check_late_tasks_daily');
    }
    add_action('aerp_check_late_tasks_daily', ['AERP_Discipline_Manager', 'handle_late_tasks']);
}
add_action('plugins_loaded', 'aerp_hrm_init');

// Đăng ký database và page kèm shortcode khi kích hoạt
register_activation_hook(__FILE__, function () {
    require_once AERP_HRM_PATH . 'install-schema.php';
    aerp_hrm_install_schema();

    // Tạo các trang mặc định với shortcode
    $pages = [
        [
            'title'   => 'Hồ sơ nhân viên',
            'slug'    => 'ho-so-nhan-vien',
            'content' => '[aerp_hr_profile]'
        ],
        [
            'title'   => 'Chấm công',
            'slug'    => 'cham-cong',
            'content' => '[aerp_attendance]'
        ],
        [
            'title'   => 'Danh sách công việc',
            'slug'    => 'danh-sach-cong-viec',
            'content' => '[aerp_task_list]'
        ],
    ];

    foreach ($pages as $page) {
        // Kiểm tra theo slug
        $existing = get_page_by_path($page['slug']);
        if (!$existing) {
            wp_insert_post([
                'post_title'   => $page['title'],
                'post_name'    => $page['slug'],
                'post_content' => $page['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page'
            ]);
        }
    }
});

// Tải asset admin
add_action('admin_enqueue_scripts', function () {
    $version = time(); // Thêm version để tránh cache
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
    wp_enqueue_script('aerp-admin-employee', AERP_HRM_URL . 'assets/js/admin-employee.js', ['jquery', 'chartjs'], $version, true);
    wp_enqueue_media();
    wp_enqueue_style('aerp-admin-employee', AERP_HRM_URL . 'assets/css/reports.css', [], $version);
});

// Export Excel dùng chung (dạng POST)
add_action('admin_post_aerp_export_excel_common', 'aerp_handle_common_export_excel');

function aerp_handle_common_export_excel()
{
    if (
        !isset($_POST['aerp_export_excel']) ||
        !wp_verify_nonce($_POST['aerp_export_nonce'], 'aerp_export_excel')
    ) {
        wp_die('⛔ Lỗi xác thực.');
    }

    $callback = sanitize_text_field($_POST['callback'] ?? '');

    // ✅ Ánh xạ callback → file
    $export_map = [
        'hrm_summary_report_export' => 'export-hrm.php',
        'employee_list_export'      => 'export-list-employee.php',
        'salary_employee_export'    => 'export-salary-employee.php',
        'salary_summary_export' => 'export-salary-summary.php',
        // thêm các callback khác tại đây
    ];

    if (!isset($export_map[$callback])) {
        wp_die("⛔ Không tìm thấy file export cho callback: $callback");
    }

    $callback_file = AERP_HRM_PATH . 'includes/exports/' . $export_map[$callback];
    if (file_exists($callback_file)) {
        require_once $callback_file;
    }

    if (function_exists($callback)) {
        call_user_func($callback);
    } else {
        wp_die("⛔ Không tìm thấy hàm xử lý export: $callback");
    }
}
