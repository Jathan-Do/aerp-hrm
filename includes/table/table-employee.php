<?php

class AERP_Employee_Table extends AERP_Base_Table
{
    public function get_columns()
    {
        return [
            'cb'            => '<input type="checkbox" />',
            'id'            => 'ID',
            'employee_code' => 'Mã NV',
            'full_name'     => 'Họ tên',
            'phone_number'  => 'Số ĐT',
            'email'         => 'Email',
            'work_location' => 'Chi nhánh',
            'department'    => 'Phòng ban',
            'position'      => 'Chức vụ',
            'status'        => 'Trạng thái',
            'current_points' => 'Điểm hiện tại',
            'created_at'    => 'Ngày tạo',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'id'         => ['id', true],
            'full_name'  => ['full_name', true],
            'created_at' => ['created_at', false],
            'status'     => ['status', false],
            'employee_code' => ['employee_code', false],
        ];
    }

    public function get_searchable_columns()
    {
        return ['employee_code', 'full_name', 'phone_number', 'email'];
    }

    public function get_bulk_actions()
    {
        return ['delete' => 'Xoá'];
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%s" />', esc_attr($item['id']));
    }

    public function column_full_name($item)
    {
        $edit_url = admin_url('admin.php?page=aerp_employees&edit=' . $item['id']);
        $view_url = admin_url('admin.php?page=aerp_employees&view=' . $item['id']);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=aerp_employees&delete=' . $item['id']),
            'aerp_delete_employee_' . $item['id']
        );
        $actions = [
            'view' => '<a href="' . esc_url($view_url) . '">Xem</a>',
            'edit' => '<a href="' . esc_url($edit_url) . '">Sửa</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Bạn có chắc muốn xoá công việc này?\')">Xoá</a>',
        ];

        return sprintf('<strong>%s</strong> %s', esc_html($item['full_name']), $this->row_actions($actions));
    }

    public function column_default($item, $column_name)
    {
        if ($column_name === 'created_at') {
            return date('d/m/Y', strtotime($item['created_at']));
        }
        if ($column_name === 'status') {
            return $item['status'] === 'active' ? 'Đang làm' : 'Nghỉ việc';
        }
        if ($column_name === 'department') {
            return esc_html(aerp_get_department_name($item['department_id']));
        }
        if ($column_name === 'position') {
            return esc_html(aerp_get_position_name($item['position_id']));
        }
        if ($column_name === 'work_location') {
            return esc_html(aerp_get_work_location_name($item['work_location_id']));
        }
        if ($column_name === 'current_points') {
            return $item['current_points'] . ' điểm';
        }

        return esc_html($item[$column_name] ?? '');
    }

    public function process_bulk_action()
    {
        if ($this->current_action() === 'delete' && !empty($_POST['id'])) {
            global $wpdb;
            $ids = array_map('absint', $_POST['id']);
            $placeholders = implode(',', array_fill(0, count($ids), '%d'));
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}aerp_hrm_employees WHERE id IN ($placeholders)",
                ...$ids
            ));

            add_action('admin_notices', function () {
                echo '<div class="updated"><p>Đã xoá các nhân viên được chọn.</p></div>';
            });
        }
    }

    public function render_filter_form()
    {
        $status     = sanitize_text_field($_GET['status'] ?? '');
        $work_location = sanitize_text_field($_GET['work_location'] ?? '');
        $department = sanitize_text_field($_GET['department'] ?? '');
        $position   = sanitize_text_field($_GET['position'] ?? '');
        $birthday   = sanitize_text_field($_GET['birthday_month'] ?? '');
        $join_from  = sanitize_text_field($_GET['join_date_from'] ?? '');
        $join_to    = sanitize_text_field($_GET['join_date_to'] ?? '');
        $off_from   = sanitize_text_field($_GET['off_date_from'] ?? '');
        $off_to     = sanitize_text_field($_GET['off_date_to'] ?? '');

        $departments = apply_filters('aerp_get_departments', []);
        $positions   = apply_filters('aerp_get_positions', []);
        $work_locations = apply_filters('aerp_get_work_locations', []);

        include AERP_HRM_PATH . 'admin/views/employees/employee/employee-filter-form.php';
    }

    public function filter_data($data, $request)
    {
        return array_filter($data, function ($row) use ($request) {
            if (!empty($request['status']) && $row['status'] !== $request['status']) return false;
            if (!empty($request['work_location']) && $row['work_location_id'] != $request['work_location']) return false;
            if (!empty($request['department']) && $row['department_id'] != $request['department']) return false;
            if (!empty($request['position']) && $row['position_id'] != $request['position']) return false;

            if (!empty($request['birthday_month']) && !empty($row['birthday'])) {
                $month = (int) date('n', strtotime($row['birthday']));
                if ($month != (int)$request['birthday_month']) return false;
            }

            if (!empty($request['join_date_from']) && strtotime($row['join_date']) < strtotime($request['join_date_from'])) return false;
            if (!empty($request['join_date_to']) && strtotime($row['join_date']) > strtotime($request['join_date_to'])) return false;

            if (!empty($request['off_date_from']) && (!isset($row['off_date']) || strtotime($row['off_date']) < strtotime($request['off_date_from']))) return false;
            if (!empty($request['off_date_to']) && (!isset($row['off_date']) || strtotime($row['off_date']) > strtotime($request['off_date_to']))) return false;

            return true;
        });
    }
}
