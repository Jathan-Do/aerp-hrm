<?php

class AERP_HRM_Settings_Manager
{
    public static function register_admin_menu()
    {
        add_menu_page(
            'AERP Nhân sự',
            'AERP Nhân sự',
            'manage_options',
            'aerp_employees',
            [__CLASS__, 'employees_page'],
            'dashicons-groups',
            6
        );

        // Menu chính: Lương tổng hợp
        add_submenu_page(
            'aerp_categories',
            'Lương tổng hợp',
            'Lương tổng hợp',
            'manage_options',
            'aerp_salary_summary',
            [__CLASS__, 'salary_page']
        );

        // Menu chính: Google Drive
        // add_submenu_page(
        //     'aerp_employees',
        //     'Cấu hình Google Drive',
        //     'Cấu hình Google Drive',
        //     'manage_options',
        //     'aerp_google_drive_settings',
        //     ['AERP_HRM_Settings_Manager', 'google_drive_settings_page']
        // );

        // === Các menu con của Danh mục ===
        // Submenu: Quản lý công ty
        add_submenu_page(
            'aerp_categories',
            'Thông tin công ty',
            'Thông tin công ty',
            'manage_options',
            'aerp_companies',
            [__CLASS__, 'company_page']
        );

        // Submenu: Quản lý phòng ban
        add_submenu_page(
            'aerp_categories',
            'Phòng ban',
            'Phòng ban',
            'manage_options',
            'aerp_departments',
            [__CLASS__, 'departments_page']
        );

        // Submenu: Quản lý chức vụ
        add_submenu_page(
            'aerp_categories',
            'Chức vụ',
            'Chức vụ',
            'manage_options',
            'aerp_positions',
            [__CLASS__, 'positions_page']
        );

        // Submenu: Quản lý vi phạm
        add_submenu_page(
            'aerp_categories',
            'Quản lý vi phạm',
            'Quản lý vi phạm',
            'manage_options',
            'aerp_discipline',
            [__CLASS__, 'discipline_page']
        );

        // Submenu: Quản lý xếp hạng
        add_submenu_page(
            'aerp_categories',
            'Cấu hình xếp loại',
            'Xếp loại nhân sự',
            'manage_options',
            'aerp_ranking_settings',
            [__CLASS__, 'ranking_settings_page']
        );

        // Submenu: Quản lý thưởng tự động
        add_submenu_page(
            'aerp_categories',
            'Cấu hình thưởng tự động',
            'Thưởng tự động',
            'manage_options',
            'aerp_reward_settings',
            [__CLASS__, 'reward_settings_page']
        );

        // Submenu: Quản lý KPI
        add_submenu_page(
            'aerp_categories',
            'Thưởng KPI',
            'KPI Bonus Settings',
            'manage_options',
            'aerp_kpi_settings',
            [__CLASS__, 'kpi_settings_page']
        );

        // Submenu: Quản lý chi nhánh
        add_submenu_page(
            'aerp_categories',
            'Chi nhánh',
            'Chi nhánh',
            'manage_options',
            'aerp_work_locations',
            [__CLASS__, 'work_locations_page']
        );

        // Submenu: Quản lý nhóm quyền
        add_submenu_page(
            'aerp_categories',
            'Nhóm quyền',
            'Nhóm quyền',
            'manage_options',
            'aerp_roles',
            [__CLASS__, 'roles_page']
        );

        // Submenu: Quản lý quyền
        add_submenu_page(
            'aerp_categories',
            'Quyền',
            'Quyền',
            'manage_options',
            'aerp_permissions',
            [__CLASS__, 'permissions_page']
        );


        // === Các menu ẩn ===
        // Submenu ẩn: Thêm lương
        add_submenu_page(
            null,
            'Thêm lương',
            'Thêm lương',
            'manage_options',
            'aerp_salary_add',
            function () {
                include AERP_HRM_PATH . 'admin/views/employees/salary/page-salary-add.php';
            }
        );

        // Submenu ẩn: Thêm công việc
        add_submenu_page(
            null,
            'Thêm công việc',
            'Thêm công việc',
            'manage_options',
            'aerp_task_add',
            function () {
                include AERP_HRM_PATH . 'admin/views/employees/task/page-task-add.php';
            }
        );

        // Submenu sửa công việc
        add_submenu_page(
            'aerp_employees',
            'Sửa công việc',
            'Sửa công việc',
            'manage_options',
            'aerp_task_edit',
            function () {
                include AERP_HRM_PATH . 'admin/views/employees/task/page-task-edit.php';
            }
        );

        // Submenu ẩn: Thêm hồ sơ
        add_submenu_page(
            null,
            'Thêm hồ sơ đính kèm',
            'Thêm hồ sơ',
            'manage_options',
            'aerp_attachment_add',
            function () {
                include AERP_HRM_PATH . 'admin/views/employees/attachment/page-attachment-add.php';
            }
        );

        // Submenu ẩn: Sửa hồ sơ
        add_submenu_page(
            'aerp_employees',
            'Sửa hồ sơ đính kèm',
            'Sửa hồ sơ',
            'manage_options',
            'aerp_attachment_edit',
            function () {
                include AERP_HRM_PATH . 'admin/views/employees/attachment/page-attachment-edit.php';
            }
        );

        // Submenu ẩn: Thêm chấm công
        add_submenu_page(
            null,
            'Thêm chấm công',
            'Thêm chấm công',
            'manage_options',
            'aerp_attendance_add',
            function () {
                include AERP_HRM_PATH . 'admin/views/employees/attendance/page-attendance-add.php';
            }
        );

        // Submenu ẩn: Sửa hồ sơ
        add_submenu_page(
            'aerp_employees',
            'Sửa chấm công',
            'Sửa chấm công',
            'manage_options',
            'aerp_attendance_edit',
            function () {
                include AERP_HRM_PATH . 'admin/views/employees/attendance/page-attendance-edit.php';
            }
        );

        add_submenu_page(
            null,
            'Tạm ứng lương',
            'Tạm ứng lương',
            'manage_options',
            'aerp_advance_add',
            function () {
                include AERP_HRM_PATH . 'admin/views/employees/salary/page-advance-add.php';
            }
        );

        add_submenu_page(
            null,
            'Ghi nhận vi phạm',
            'Ghi nhận vi phạm',
            'manage_options',
            'aerp_discipline_add',
            function () {
                include AERP_HRM_PATH . 'admin/views/employees/discipline/page-discipline-add.php';
            }
        );

        add_submenu_page(
            null,
            'Thêm thuởng',
            'Thêm thưởng',
            'manage_options',
            'aerp_employee_reward_add',
            function () {
                include AERP_HRM_PATH . 'admin/views/employees/reward/page-employee-reward-add.php';
            }
        );

        add_submenu_page(
            'aerp_employees',
            'Sửa thuởng',
            'Sửa thưởng',
            'manage_options',
            'aerp_employee_reward_edit',
            function () {
                include AERP_HRM_PATH . 'admin/views/employees/reward/page-employee-reward-edit.php';
            }
        );

        add_submenu_page(
            null,
            'Thêm điều chỉnh',
            'Thêm điều chỉnh',
            'manage_options',
            'aerp_adjustment_add',
            function () {
                include AERP_HRM_PATH . 'admin/views/employees/adjustment/page-adjustment-add.php';
            }
        );

        add_submenu_page(
            null,
            'Sửa điều chỉnh',
            'Sửa điều chỉnh',
            'manage_options',
            'aerp_adjustment_edit',
            function () {
                include AERP_HRM_PATH . 'admin/views/employees/adjustment/page-adjustment-edit.php';
            }
        );

        // Ẩn các submenu không nên hiển thị
        add_action('admin_head', function () {
            remove_submenu_page('aerp_employees', 'aerp_task_edit');
            remove_submenu_page('aerp_employees', 'aerp_attachment_edit');
            remove_submenu_page('aerp_employees', 'aerp_attendance_edit');
            remove_submenu_page('aerp_employees', 'aerp_employee_reward_edit');
            remove_submenu_page('aerp_employees', 'aerp_adjustment_edit');
        });

        // Menu cấu hình mapping động phân quyền chức năng
        // add_submenu_page(
        //     'aerp_categories',
        //     'Cấu hình phân quyền chức năng',
        //     'Phân quyền chức năng',
        //     'manage_options',
        //     'aerp_feature_permission_map',
        //     function () {
        //         include AERP_HRM_PATH . 'admin/views/settings/feature-permission-map.php';
        //     }
        // );
    }

    public static function reward_settings_page()
    {
        if (isset($_GET['edit'])) {
            include_once AERP_HRM_PATH . 'admin/views/rewards/page-reward-edit.php';
            return;
        }
        if (isset($_GET['add'])) {
            include_once AERP_HRM_PATH . 'admin/views/rewards/page-reward-add.php';
            return;
        }
        include_once AERP_HRM_PATH . 'admin/views/rewards/page-reward-list.php';
    }

    public static function discipline_page()
    {
        if (isset($_GET['edit'])) {
            include_once AERP_HRM_PATH . 'admin/views/discipline-rules/page-discipline-edit.php';
            return;
        }
        include_once AERP_HRM_PATH . 'admin/views/discipline-rules/page-discipline-rules.php';
    }

    public static function ranking_settings_page()
    {
        if (isset($_GET['edit'])) {
            include_once AERP_HRM_PATH . 'admin/views/rankings/ranking-edit.php';
            return;
        }
        include_once AERP_HRM_PATH . 'admin/views/rankings/ranking-settings.php';
    }

    public static function kpi_settings_page()
    {
        if (isset($_GET['edit'])) {
            include_once AERP_HRM_PATH . 'admin/views/kpis/form.php';
            return;
        }
        include_once AERP_HRM_PATH . 'admin/views/kpis/list.php';
    }

    public static function salary_page()
    {
        if (isset($_GET['view'])) {
            include_once AERP_HRM_PATH . 'admin/views/salary/salary-view-detail.php';
            return;
        }
        include_once AERP_HRM_PATH . 'admin/views/salary/salary-summary.php';
    }

    public static function employees_page()
    {
        if (isset($_GET['view'])) {
            include_once AERP_HRM_PATH . 'admin/views/employees/form-view.php';
            return;
        }

        if (isset($_GET['edit'])) {
            include_once AERP_HRM_PATH . 'admin/views/employees/employee/form-edit.php';
            return;
        }

        if (isset($_GET['add'])) {
            include_once AERP_HRM_PATH . 'admin/views/employees/employee/form-add.php';
            return;
        }

        include_once AERP_HRM_PATH . 'admin/views/employees/employee/list.php';
    }

    public static function company_page()
    {
        include_once AERP_HRM_PATH . 'admin/views/company/form-edit.php';
    }

    public static function departments_page()
    {
        if (isset($_GET['edit'])) {
            include_once AERP_HRM_PATH . 'admin/views/departments/form-edit.php';
            return;
        }

        if (isset($_GET['add'])) {
            include_once AERP_HRM_PATH . 'admin/views/departments/form-add.php';
            return;
        }

        include_once AERP_HRM_PATH . 'admin/views/departments/list.php';
    }

    public static function positions_page()
    {
        if (isset($_GET['edit'])) {
            include_once AERP_HRM_PATH . 'admin/views/positions/form-edit.php';
            return;
        }

        if (isset($_GET['add'])) {
            include_once AERP_HRM_PATH . 'admin/views/positions/form-add.php';
            return;
        }

        include_once AERP_HRM_PATH . 'admin/views/positions/list.php';
    }

    public static function work_locations_page()
    {
        if (isset($_GET['edit'])) {
            include_once AERP_HRM_PATH . 'admin/views/work-locations/form-edit.php';
            return;
        }
        if (isset($_GET['add'])) {
            include_once AERP_HRM_PATH . 'admin/views/work-locations/form-add.php';
            return;
        }
        include_once AERP_HRM_PATH . 'admin/views/work-locations/list.php';
    }

    public static function google_drive_settings_page()
    {
        // TẠM THỜI VÔ HIỆU HÓA CHỨC NĂNG GOOGLE DRIVE
        // // Đường dẫn credentials.json mới trong thư mục plugin
        // $credentials_path = AERP_HRM_PATH . 'credentials.json';
        // // Xử lý upload credentials.json
        // if (
        //     isset($_FILES['aerp_credentials_json']) &&
        //     isset($_POST['aerp_credentials_nonce']) &&
        //     wp_verify_nonce($_POST['aerp_credentials_nonce'], 'aerp_upload_credentials')
        // ) {
        //     $file = $_FILES['aerp_credentials_json'];
        //     if ($file['name'] !== 'credentials.json') {
        //         echo '<div class="error"><p>Tên file phải là <code>credentials.json</code>. Vui lòng đổi tên file trước khi upload.</p></div>';
        //     } elseif ($file['type'] === 'application/json' && $file['error'] === UPLOAD_ERR_OK) {
        //         move_uploaded_file($file['tmp_name'], $credentials_path);
        //         echo '<div class="updated"><p>Upload credentials.json thành công!</p></div>';
        //     } else {
        //         echo '<div class="error"><p>Lỗi upload file. Hãy chọn đúng file credentials.json.</p></div>';
        //     }
        // }
        // if (!file_exists($credentials_path)) {
        //     echo '<div class="wrap">';
        //     echo '<h2>Upload Google Drive json</h2>';
        //     echo '<p style="margin-top: 0px;">Vui lòng lưu tên file json từ Google Drive thành <code>credentials.json</code> trước khi upload lên.</p>';
        //     echo '<form method="post" enctype="multipart/form-data">';
        //     wp_nonce_field('aerp_upload_credentials', 'aerp_credentials_nonce');
        //     echo '<input type="file" name="aerp_credentials_json" accept=".json" required> ';
        //     echo '<button type="submit" class="button button-primary">Upload json</button>';
        //     echo '</form>';
        //     echo '</div>';
        //     return;
        // } else {
        //     echo '<p style="color:green;">Đã có file credentials.json. Bạn có thể upload lại nếu muốn thay đổi.</p>';
        //     echo '<form method="post" enctype="multipart/form-data" style="margin-bottom:20px;">';
        //     wp_nonce_field('aerp_upload_credentials', 'aerp_credentials_nonce');
        //     echo '<input type="file" name="aerp_credentials_json" accept=".json" required> ';
        //     echo '<button type="submit" class="button">Upload lại json</button>';
        //     echo '</form>';
        // }
        // require_once AERP_HRM_PATH . 'includes/class-google-drive-manager.php';
        // $drive = AERP_Google_Drive_Manager::get_instance();
        // $client = $drive->get_client();
        // if (isset($_GET['code'])) {
        //     $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        //     if (!isset($token['error'])) {
        //         update_option('aerp_google_drive_token', $token);
        //         echo '<div class="updated"><p>Kết nối Google Drive thành công!</p></div>';
        //     } else {
        //         echo '<div class="error"><p>Lỗi xác thực: ' . esc_html($token['error_description']) . '</p></div>';
        //     }
        // }
        // $token = get_option('aerp_google_drive_token');
        // if ($token) {
        //     $client->setAccessToken($token);
        //     if ($client->isAccessTokenExpired()) {
        //         if ($client->getRefreshToken()) {
        //             $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        //             update_option('aerp_google_drive_token', $client->getAccessToken());
        //             echo '<div class="updated"><p>Đã làm mới token Google Drive.</p></div>';
        //         } else {
        //             echo '<div class="error"><p>Token đã hết hạn, vui lòng kết nối lại.</p></div>';
        //         }
        //     } else {
        //         echo '<div class="updated"><p>Đã kết nối Google Drive!</p></div>';
        //     }
        // } else {
        //     $auth_url = $client->createAuthUrl();
        //     echo '<a href="' . esc_url($auth_url) . '" class="button button-primary">Kết nối Google Drive</a>';
        // }
    }

    public static function roles_page()
    {
        if (isset($_GET['edit'])) {
            include_once AERP_HRM_PATH . 'admin/views/roles/form-edit.php';
            return;
        }
        if (isset($_GET['add'])) {
            include_once AERP_HRM_PATH . 'admin/views/roles/form-add.php';
            return;
        }
        include_once AERP_HRM_PATH . 'admin/views/roles/list.php';
    }

    public static function permissions_page()
    {
        if (isset($_GET['edit'])) {
            include_once AERP_HRM_PATH . 'admin/views/permissions/form-edit.php';
            return;
        }
        if (isset($_GET['add'])) {
            include_once AERP_HRM_PATH . 'admin/views/permissions/form-add.php';
            return;
        }
        include_once AERP_HRM_PATH . 'admin/views/permissions/list.php';
    }
}
