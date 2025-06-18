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

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($actions) {
    if (function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    if (is_plugin_active('aerp-crm/aerp-crm.php')) {
        // Disable deactivate link
        unset($actions['deactivate']);
        $actions['deactivate'] = '<span style="color:#aaa;">Vui lòng deactivate AERP CRM trước</span>';
    }
    return $actions;
});

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
    require_once AERP_HRM_PATH . 'includes/table/table-work-location.php';

    // Load frontend table classes
    require_once AERP_HRM_PATH . 'frontend/includes/table/class-frontend-table.php';
    require_once AERP_HRM_PATH . 'frontend/includes/table/class-department-table.php';
    require_once AERP_HRM_PATH . 'frontend/includes/table/class-company-table.php';
    require_once AERP_HRM_PATH . 'frontend/includes/table/class-position-table.php';
    require_once AERP_HRM_PATH . 'frontend/includes/table/class-work-location-table.php';

    // Load các class cần thiết khác
    require_once AERP_HRM_PATH . 'includes/class-excel-export-helper.php';

    // Load các class cần thiết manager
    $includes = [
        'class-work-location-manager.php',
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
        'class-role-manager.php',
        'class-permission-manager.php',
    ];
    foreach ($includes as $file) {
        require_once AERP_HRM_PATH . 'includes/managers/' . $file;
    }

    // Load frontend manager classes
    $includes = [
        'class-frontend-department-manager.php',
        'class-frontend-company-manager.php',
        'class-frontend-position-manager.php',
        'class-frontend-work-location-manager.php',
    ];
    foreach ($includes as $file) {
        require_once AERP_HRM_PATH . 'frontend/includes/managers/' . $file;
    }


    // Shortcodes frontend
    require_once AERP_HRM_PATH . 'includes/shortcodes/shortcode-hr-profile.php';
    require_once AERP_HRM_PATH . 'includes/shortcodes/shortcode-task-list.php';
    require_once AERP_HRM_PATH . 'includes/shortcodes/shortcode-attendance.php';
    require_once AERP_HRM_PATH . 'includes/shortcodes/shortcode-login.php';
    require_once AERP_HRM_PATH . 'includes/shortcodes/shortcode-manager-dashboard.php';

    // Xử lý form và logic
    $managers = [
        'AERP_Work_Location_Manager',
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
        'AERP_Role_Manager',
        'AERP_Permission_Manager',
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
    // Xử lý form và logic frontend
    $managers = [
        'AERP_Frontend_Department_Manager',
        'AERP_Frontend_Company_Manager',
        'AERP_Frontend_Position_Manager',
        'AERP_Frontend_Work_Location_Manager',
    ];
    foreach ($managers as $manager) {
        if (method_exists($manager, 'handle_submit')) {
            add_action('init', [$manager, 'handle_submit']);
        }
        if (method_exists($manager, 'handle_form_submit')) {
            add_action('init', [$manager, 'handle_form_submit']);
        }
        if (method_exists($manager, 'handle_delete')) {
            add_action('init', [$manager, 'handle_delete']);
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
            wp_enqueue_style('aerp-hrm-manager-dashboard', AERP_HRM_URL . 'assets/css/manager-dashboard.css', [], '1.0');
            wp_enqueue_style('dashicons');
            wp_enqueue_style('aerp-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css');
            wp_enqueue_script('aerp-hrm-frontend', AERP_HRM_URL . 'assets/js/frontend.js', ['jquery', 'chartjs'], '1.0', true);
            wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script('aerp-frontend-table', AERP_HRM_URL . 'assets/js/frontend-table.js', ['jquery', 'jquery-ui-dialog'], '1.0', true);

            // Prepare data for wp_localize_script
            $dummy_table_instance = new AERP_Frontend_Table();
            $all_column_keys = $dummy_table_instance->get_column_keys();
            $hidden_columns_option_key = $dummy_table_instance->get_hidden_columns_option_key();

            wp_localize_script('aerp-frontend-table', 'aerp_table_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('aerp_save_column_preferences'),
            ));
        }
    }, 1);

    // Lên lịch check công việc trễ
    if (!wp_next_scheduled('aerp_check_late_tasks_daily')) {
        wp_schedule_event(time(), 'daily', 'aerp_check_late_tasks_daily');
    }
    add_action('aerp_check_late_tasks_daily', ['AERP_Discipline_Manager', 'handle_late_tasks']);
    add_action('wp_ajax_aerp_save_column_preferences', ['AERP_Frontend_Table', 'handle_save_column_preferences']);
    add_action('wp_ajax_nopriv_aerp_save_column_preferences', ['AERP_Frontend_Table', 'handle_save_column_preferences']);
}
add_action('plugins_loaded', 'aerp_hrm_init');

// Đăng ký database và page kèm shortcode khi kích hoạt
register_activation_hook(__FILE__, function () {
    require_once AERP_HRM_PATH . 'install-schema.php';
    aerp_hrm_install_schema();

    // Tạo các trang mặc định với shortcode
    $pages = [
        [
            'title'   => 'AERP Hồ sơ nhân viên',
            'slug'    => 'aerp-ho-so-nhan-vien',
            'content' => '[aerp_hr_profile]'
        ],
        [
            'title'   => 'AERP Dashboard Quản lý',
            'slug'    => 'aerp-quan-ly',
            'content' => '[aerp_manager_dashboard]'
        ],
        [
            'title'   => 'AERP Chấm công',
            'slug'    => 'aerp-cham-cong',
            'content' => '[aerp_attendance]'
        ],
        [
            'title'   => 'AERP Danh sách công việc',
            'slug'    => 'aerp-danh-sach-cong-viec',
            'content' => '[aerp_task_list]'
        ],
        [
            'title'   => 'AERP Đăng nhập',
            'slug'    => 'aerp-dang-nhap',
            'content' => '[aerp_login]'
        ],
        [
            'title'   => 'AERP Công ty',
            'slug'    => 'aerp-company',
            'content' => ''
        ],
        [
            'title'   => 'AERP Chi nhánh',
            'slug'    => 'aerp-work-location',
            'content' => ''
        ],
        [
            'title'   => 'AERP Phòng ban',
            'slug'    => 'aerp-departments',
            'content' => ''
        ],
        [
            'title'   => 'AERP Chức vụ',
            'slug'    => 'aerp-position',
            'content' => ''
        ],
        [
            'title'   => 'AERP Dashboard',
            'slug'    => 'aerp-dashboard',
            'content' => ''
        ],
        [
            'title'   => 'AERP Danh mục',
            'slug'    => 'aerp-categories',
            'content' => ''
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
    flush_rewrite_rules();
});

// Xóa các trang khi deactivate plugin
register_deactivation_hook(__FILE__, function () {
    $slugs = [
        'aerp-ho-so-nhan-vien',
        'aerp-cham-cong',
        'aerp-danh-sach-cong-viec',
        'aerp-dang-nhap',
        'aerp-quan-ly',
        'aerp-dashboard',
        'aerp-categories',
        'aerp-company',
        'aerp-work-location',
        'aerp-departments',
        'aerp-position',
    ];
    foreach ($slugs as $slug) {
        $page = get_page_by_path($slug);
        if ($page) {
            wp_delete_post($page->ID, true); // true = force delete
        }
    }
    flush_rewrite_rules();
});

// Tải asset admin
add_action('admin_enqueue_scripts', function () {
    $version = time(); // Thêm version để tránh cache
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
    wp_enqueue_script('aerp-admin-employee', AERP_HRM_URL . 'assets/js/admin-employee.js', ['jquery', 'chartjs'], $version, true);
    wp_enqueue_media();
    wp_enqueue_style('aerp-admin-employee', AERP_HRM_URL . 'assets/css/reports.css', [], $version);
    wp_enqueue_style('aerp-backend', AERP_HRM_URL . 'assets/css/backend.css', [], $version);
}, 1);

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

// Redirect khi đăng nhập sai về trang custom login
function aerp_login_failed_redirect($username)
{
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    if (strpos($referrer, 'aerp-dang-nhap') !== false) {
        wp_redirect(home_url('/aerp-dang-nhap?login=failed'));
        exit;
    }
}
add_action('wp_login_failed', 'aerp_login_failed_redirect');

// AJAX: trả về HTML bình luận cho 1 task (thực tế, tách view)
function aerp_ajax_get_task_comments()
{
    $task_id = absint($_POST['task_id'] ?? 0);
    if (!$task_id) {
        wp_send_json_error('Thiếu task_id');
    }
    $comments_aerp = AERP_Task_Manager::get_comments($task_id);
    ob_start();
    include AERP_HRM_PATH . 'includes/ajax/ajax-task-comments.php';
    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_aerp_get_task_comments', 'aerp_ajax_get_task_comments');

// === REWRITE RULES FOR FRONTEND DASHBOARD ===
require_once AERP_HRM_PATH . 'frontend/includes/page-rewrite-rules.php';
