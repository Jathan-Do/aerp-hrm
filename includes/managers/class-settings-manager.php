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

        // Menu chính: Danh mục
        add_submenu_page(
            'aerp_employees',
            'Danh mục',
            'Danh mục',
            'manage_options',
            'aerp_categories',
            [__CLASS__, 'categories_page']
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

        // Menu chính: Báo cáo
        add_submenu_page(
            'aerp_employees',
            'Báo cáo nhân sự',
            'Báo cáo',
            'manage_options',
            'aerp_hrm_reports',
            [__CLASS__, 'aerp_hrm_reports_page']
        );

        // Menu chính: Bản quyền
        add_submenu_page(
            'aerp_employees',
            'Bản quyền module',
            'Bản quyền',
            'manage_options',
            'aerp_license',
            ['AERP_HRM_Settings_Manager', 'license_page']
        );

        // Menu chính: Cài đặt
        add_submenu_page(
            'aerp_employees',
            'Cài đặt',
            'Cài đặt',
            'manage_options',
            'aerp_hrm_settings',
            [__CLASS__, 'settings_page']
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

    public static function aerp_hrm_reports_page()
    {
        $report_month = sanitize_text_field($_GET['report_month'] ?? date('Y-m'));
        $report_month = $report_month . '-01'; // ✅ fix về đúng định dạng DATE

        // Lấy dữ liệu thống kê
        $summary        = AERP_Report_Manager::get_summary($report_month);
        $performance    = AERP_Report_Manager::get_performance_data($report_month);
        $tenure         = AERP_Report_Manager::get_tenure_data();
        $departments    = AERP_Report_Manager::get_department_data();
        $salary_stats   = AERP_Report_Manager::get_salary_data($report_month);

        // Load script mới
        wp_enqueue_script('aerp-admin-charts', AERP_HRM_URL . 'assets/js/admin-charts.js', ['jquery', 'chartjs'], time(), true);

        // Gửi sang JS
        wp_localize_script('aerp-admin-charts', 'performanceData', $performance);
        wp_localize_script('aerp-admin-charts', 'tenureData', $tenure);
        wp_localize_script('aerp-admin-charts', 'departmentData', $departments);
        wp_localize_script('aerp-admin-charts', 'salaryData', $salary_stats);

        // Biến tắt gọn cho template
        $total_employees = $summary['total'];
        $joined          = $summary['joined'];
        $resigned        = $summary['resigned'];

        // Đẩy sang file hiển thị
        include AERP_HRM_PATH . 'admin/views/reports/reports.php';
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

    public static function license_page()
    {
        if (!aerp_user_has_permission(get_current_user_id(), 'license_manage')) {
            wp_die('Bạn không có quyền truy cập trang này!');
        }
        if (isset($_POST['aerp_license_update']) && check_admin_referer('aerp_license_action', 'aerp_license_nonce')) {
            $data = [];

            foreach ($_POST['module_license'] as $slug => $key) {
                $license_key = sanitize_text_field($key);

                // ✅ Tạm thời nếu có key → active luôn
                // $status = !empty($license_key) ? 'active' : 'invalid';
                $status = ($license_key === 'demo-hrm-key') ? 'active' : 'invalid';


                // 🔒 Khi có API thật, bạn sẽ thay phần này bằng gọi wp_remote_get()

                $data[$slug] = [
                    'license_key' => $license_key,
                    'status'      => $status
                ];
            }



            update_option('aerp_license_keys', $data);

            echo '<div class="updated"><p>Đã lưu thông tin bản quyền.</p></div>';
        }

        $licenses = get_option('aerp_license_keys', []);
        $modules = [
            'hrm'  => 'Quản lý nhân sự',
            'crm'  => 'Khách hàng',
            'order' => 'Đơn hàng',
            'stock' => 'Kho hàng',
            'finance' => 'Tài chính',
        ];
?>
        <div class="wrap">
            <h1>Quản lý bản quyền AERP</h1>
            <p style="color:#888;">* Nếu bạn đã mua bản quyền, vui lòng nhập key vào ô bên dưới. VD: demo-hrm-key</p>
            <p>Nhập mã license bạn nhận được khi mua plugin. Nếu chưa có, bạn có thể <a href="https://yourdomain.com/mua-ban-quyen" target="_blank">mua license tại đây</a>.</p>

            <form method="post">
                <?php wp_nonce_field('aerp_license_action', 'aerp_license_nonce'); ?>
                <table class="form-table">
                    <?php foreach ($modules as $slug => $label): ?>
                        <tr>
                            <th><label for="module_<?= esc_attr($slug) ?>"><?= esc_html($label) ?></label></th>
                            <td>
                                <input type="text" name="module_license[<?= esc_attr($slug) ?>]" class="regular-text"
                                    value="<?= esc_attr($licenses[$slug]['license_key'] ?? '') ?>">
                                <span class="description"><?= isset($licenses[$slug]['status']) && $licenses[$slug]['status'] === 'active'
                                                                ? '<span style="color:green;">(Đã kích hoạt)</span>'
                                                                : '<span style="color:red;">(Chưa kích hoạt)</span>' ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <p>
                    <input type="submit" name="aerp_license_update" class="button button-primary" value="Lưu thông tin">
                </p>
            </form>
        </div>
    <?php
    }

    public static function settings_page()
    {
        if (isset($_POST['aerp_hrm_save_settings']) && check_admin_referer('aerp_hrm_settings_action', 'aerp_hrm_settings_nonce')) {
            $delete_data = isset($_POST['aerp_hrm_delete_data_on_uninstall']) ? 1 : 0;
            update_option('aerp_hrm_delete_data_on_uninstall', $delete_data);
            echo '<div class="updated"><p>Đã lưu cài đặt.</p></div>';
        }
        $delete_data = get_option('aerp_hrm_delete_data_on_uninstall', 0);
    ?>
        <div class="wrap">
            <h1>Cài đặt AERP HRM</h1>
            <form method="post">
                <?php wp_nonce_field('aerp_hrm_settings_action', 'aerp_hrm_settings_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Xóa dữ liệu khi gỡ plugin?</th>
                        <td>
                            <input type="checkbox" name="aerp_hrm_delete_data_on_uninstall" value="1" <?php checked($delete_data, 1); ?> />
                            <label for="aerp_hrm_delete_data_on_uninstall">Xóa toàn bộ dữ liệu khi gỡ plugin</label>
                        </td>
                    </tr>
                </table>
                <p><button type="submit" name="aerp_hrm_save_settings" class="button button-primary">Lưu cài đặt</button></p>
            </form>
        </div>
<?php
    }

    // Thêm hàm hiển thị trang Danh mục
    public static function categories_page()
    {
        include_once AERP_HRM_PATH . 'admin/views/categories/list.php';
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
