<?php

require_once AERP_HRM_PATH . 'includes/class-employee-journey.php';

class AERP_Employee_Manager
{

    /**
     * Lấy danh sách tất cả nhân viên
     */
    public static function get_all()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_employees ORDER BY id DESC");
    }

    /**
     * Lấy nhân viên theo ID
     */
    public static function get_by_id($id)
    {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_employees WHERE id = %d", $id)
        );
    }

    /**
     * Xử lý thêm / cập nhật nhân viên
     */
    public static function handle_form_submit()
    {
        if (
            ! isset($_POST['aerp_save_employee']) ||
            ! check_admin_referer('aerp_save_employee_action', 'aerp_save_employee_nonce') ||
            ! current_user_can('manage_options')
        ) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_employees';

        $data = [
            'employee_code'         => sanitize_text_field($_POST['employee_code']),
            'full_name'             => sanitize_text_field($_POST['full_name']),
            'gender'                => sanitize_text_field($_POST['gender']),
            'birthday'              => sanitize_text_field($_POST['birthday']),
            'cccd_number'           => sanitize_text_field($_POST['cccd_number']),
            'cccd_issued_date'      => sanitize_text_field($_POST['cccd_issued_date']),
            'address_permanent'     => sanitize_textarea_field($_POST['address_permanent']),
            'address_current'       => sanitize_textarea_field($_POST['address_current']),
            'phone_number'          => sanitize_text_field($_POST['phone_number']),
            'email'                 => sanitize_email($_POST['email']),
            'bank_account'          => sanitize_text_field($_POST['bank_account']),
            'bank_name'             => sanitize_text_field($_POST['bank_name']),
            'relative_name'         => sanitize_text_field($_POST['relative_name']),
            'relative_phone'        => sanitize_text_field($_POST['relative_phone']),
            'relative_relationship' => sanitize_text_field($_POST['relative_relationship']),
            'department_id'         => absint($_POST['department_id']),
            'position_id'           => absint($_POST['position_id']),
            'work_location_id'      => absint($_POST['work_location_id']),
            'join_date'             => sanitize_text_field($_POST['join_date']),
            'off_date'              => sanitize_text_field($_POST['off_date']),
            'status'                => sanitize_text_field($_POST['status']),
            'note'                  => sanitize_textarea_field($_POST['note']),
            'user_id'               => absint($_POST['user_id']),
        ];

        $id = isset($_POST['employee_id']) ? absint($_POST['employee_id']) : 0;

        if ($id) {
            // Lấy dữ liệu cũ
            $old = self::get_by_id($id);
            $wpdb->update($table, $data, ['id' => $id]);
            $msg = 'Đã cập nhật nhân viên!';

            // So sánh các trường quan trọng → log hành trình
            if ($old) {
                if ($old->department_id != $data['department_id']) {
                    self::log_journey($id, 'transfer', $old->department_id, $data['department_id'], 'Chuyển phòng ban');
                }

                if ($old->position_id != $data['position_id']) {
                    self::log_journey($id, 'promotion', $old->position_id, $data['position_id'], 'Thay đổi chức vụ');
                }

                if ($old->status != $data['status'] && $data['status'] === 'resigned') {
                    self::log_journey($id, 'resign', $old->status, $data['status'], 'Nghỉ việc');
                }
            }
        } else {
            $data['created_at'] = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s');
            $wpdb->insert($table, $data);
            $id = $wpdb->insert_id;
            $msg = 'Đã thêm nhân viên!';

            // Log vào làm
            self::log_journey($id, 'join', '', '', 'Vào làm');
        }

        // Lưu role cho user
        $user_id = absint($_POST['user_id']);
        // Luôn xóa hết roles cũ trước khi insert lại
        $wpdb->delete($wpdb->prefix . 'aerp_user_role', ['user_id' => $user_id]);
        if (isset($_POST['user_roles']) && $user_id) {
            $roles = array_map('intval', (array)$_POST['user_roles']);
            foreach ($roles as $rid) {
                $wpdb->insert($wpdb->prefix . 'aerp_user_role', [
                    'user_id' => $user_id,
                    'role_id' => $rid
                ]);
            }
        }

        // Lưu quyền đặc biệt cho user
        if ($user_id) {
            $permissions = isset($_POST['user_permissions']) ? array_map('intval', (array)$_POST['user_permissions']) : [];
            $wpdb->delete($wpdb->prefix . 'aerp_user_permission', ['user_id' => $user_id]);
            foreach ($permissions as $pid) {
                $wpdb->insert($wpdb->prefix . 'aerp_user_permission', [
                    'user_id' => $user_id,
                    'permission_id' => $pid
                ]);
            }
        }

        $user_roles = isset($_POST['user_roles']) ? array_map('intval', (array)$_POST['user_roles']) : [];
        $all_roles = AERP_Role_Manager::get_roles();
        $department_lead_role_id = null;
        if (!empty($all_roles)) {
            foreach ($all_roles as $role) {
                $role_name = is_array($role) ? $role['name'] : $role->name;
                $role_id = is_array($role) ? $role['id'] : $role->id;
                if ($role_name === 'department_lead') {
                    $department_lead_role_id = $role_id;
                    break;
                }
            }
        }
        $department_id = isset($_POST['department_lead_department_id']) ? intval($_POST['department_lead_department_id']) : 0;

        // Xử lý quyền trưởng phòng
        if ($user_id && $department_lead_role_id) {
            // Nếu user có role trưởng phòng nhưng không chọn phòng ban
            if (in_array($department_lead_role_id, $user_roles) && !$department_id) {
                // Xóa role trưởng phòng khỏi user
                $user_roles = array_diff($user_roles, [$department_lead_role_id]);
                // Xóa manager_id ở tất cả các phòng ban
                $wpdb->update(
                    $wpdb->prefix . 'aerp_hrm_departments',
                    ['manager_id' => null],
                    ['manager_id' => $user_id]
                );
                // Cập nhật lại roles cho user
                $wpdb->delete($wpdb->prefix . 'aerp_user_role', ['user_id' => $user_id]);
                foreach ($user_roles as $rid) {
                    $wpdb->insert($wpdb->prefix . 'aerp_user_role', [
                        'user_id' => $user_id,
                        'role_id' => $rid
                    ]);
                }
            }
            // Nếu user có role trưởng phòng và có chọn phòng ban
            else if (in_array($department_lead_role_id, $user_roles) && $department_id) {
                // 1. Xóa manager_id của user này ở tất cả các phòng ban khác
                $wpdb->update(
                    $wpdb->prefix . 'aerp_hrm_departments',
                    ['manager_id' => null],
                    ['manager_id' => $user_id]
                );
                // 2. Update manager_id cho phòng ban vừa chọn
                $wpdb->update(
                    $wpdb->prefix . 'aerp_hrm_departments',
                    ['manager_id' => $user_id],
                    ['id' => $department_id]
                );
            } 
            // Nếu user không còn role trưởng phòng
            else if (!in_array($department_lead_role_id, $user_roles)) {
                // Xóa manager_id của user này ở tất cả các phòng ban
                $wpdb->update(
                    $wpdb->prefix . 'aerp_hrm_departments',
                    ['manager_id' => null],
                    ['manager_id' => $user_id]
                );
            }
        }

        add_action('admin_notices', function () use ($msg) {
            echo '<div class="updated"><p>' . esc_html($msg) . '</p></div>';
        });
    }

    /**
     * Xử lý xoá nhân viên
     */
    public static function handle_delete()
    {
        if (
            isset($_GET['page'], $_GET['delete'], $_GET['_wpnonce']) &&
            $_GET['page'] === 'aerp_employees' &&
            wp_verify_nonce($_GET['_wpnonce'], 'aerp_delete_employee_' . $_GET['delete'])
        ) {
            global $wpdb;
            $id = absint($_GET['delete']);
            $wpdb->delete($wpdb->prefix . 'aerp_hrm_employees', ['id' => $id]);

            wp_redirect(admin_url('admin.php?page=aerp_employees&deleted=1'));
            exit;
        }
    }

    public static function get_filtered($args)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_employees';
        $where = "1=1";

        if (!empty($args['search'])) {
            $s = esc_sql($args['search']);
            $where .= " AND (full_name LIKE '%$s%' OR employee_code LIKE '%$s%')";
        }

        foreach ($args['filters'] as $key => $val) {
            $val = esc_sql($val);
            if ($val !== '') {
                $where .= $wpdb->prepare(" AND $key = %s", $val);
            }
        }

        $limit  = absint($args['per_page']);
        $offset = ($args['paged'] - 1) * $limit;

        $items = $wpdb->get_results("SELECT * FROM $table WHERE $where ORDER BY {$args['orderby']} {$args['order']} LIMIT $offset, $limit");
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where");

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    private static function log_journey($employee_id, $event_type, $old_value = '', $new_value = '', $note = '')
    {
        $journey = new AERP_HRM_Employee_Journey();
        $journey->add_event($employee_id, $event_type, $old_value, $new_value, $note);
    }
}
