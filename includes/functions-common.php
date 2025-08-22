<?php

// ============================
// COMMON FUNCTIONS FOR CORE MODULE
// ============================

function aerp_get_work_locations()
{
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_work_locations ORDER BY name ASC");
}

function aerp_get_departments()
{
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_departments ORDER BY name ASC");
}

function aerp_get_positions()
{
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_positions ORDER BY name ASC");
}

function aerp_get_company_info()
{
    if (class_exists('AERP_Company_Manager')) {
        return AERP_Company_Manager::get_info();
    }
    return null;
}
/**
 * Kiểm tra module hiện tại có đang ở bản Pro không
 *
 * @param string $module_slug Ví dụ: 'hrm', 'crm', 'stock'
 * @return bool
 */
function aerp_is_pro_module($module_slug)
{
    $licenses = get_option('aerp_license_keys', []);
    $data = $licenses[$module_slug] ?? [];

    return !empty($data['license_key']) && $data['status'] === 'active';
}

// Lấy nhân viên theo user_id
function aerp_get_employee_by_user_id($user_id)
{
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}aerp_hrm_employees WHERE user_id = %d",
        $user_id
    ));
}
// Lấy nhân viên theo id
function aerp_get_employee_by_id($id)
{
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}aerp_hrm_employees WHERE id = %d",
        $id
    ));
}
// Lấy tên chi nhánh theo ID
function aerp_get_work_location_name($id)
{
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare(
        "SELECT name FROM {$wpdb->prefix}aerp_hrm_work_locations WHERE id = %d",
        $id
    ));
}
// Lấy tên phòng ban theo ID
function aerp_get_department_name($id)
{
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare(
        "SELECT name FROM {$wpdb->prefix}aerp_hrm_departments WHERE id = %d",
        $id
    ));
}

// Lấy tên chức vụ theo ID
function aerp_get_position_name($id)
{
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare(
        "SELECT name FROM {$wpdb->prefix}aerp_hrm_positions WHERE id = %d",
        $id
    ));
}

// Lấy tên nhân viên theo ID
function aerp_get_employee_name($id)
{
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare(
        "SELECT full_name FROM {$wpdb->prefix}aerp_hrm_employees WHERE id = %d",
        $id
    ));
}

/**
 * Lấy danh sách tất cả nhân viên
 */
function aerp_get_employees_with_location()
{
    global $wpdb;
    return $wpdb->get_results(
        "SELECT e.*, wl.name AS work_location_name
         FROM {$wpdb->prefix}aerp_hrm_employees e
         LEFT JOIN {$wpdb->prefix}aerp_hrm_work_locations wl ON e.work_location_id = wl.id
         ORDER BY e.full_name ASC"
    );
}
function aerp_get_employees_with_location_select2($q = '')
{
    global $wpdb;
    $where = '';
    $where = '';
    if ($q !== '') {
        $q_like = '%' . $wpdb->esc_like($q) . '%';
        $where = $wpdb->prepare(
            " AND (e.full_name LIKE %s OR wl.name LIKE %s)",
            $q_like,
            $q_like
        );
    }
    return $wpdb->get_results(
        "SELECT e.id, e.full_name, wl.name AS work_location_name
         FROM {$wpdb->prefix}aerp_hrm_employees e
         LEFT JOIN {$wpdb->prefix}aerp_hrm_work_locations wl ON e.work_location_id = wl.id
         WHERE 1=1 AND e.status = 'active' $where
         ORDER BY e.full_name ASC"
    );
}
function aerp_safe_select_options($items, $selected = '', $key = 'id', $label = 'name', $show_all_option = false)
{
    if ($show_all_option) {
        echo '<option value="">-- Tất cả --</option>';
    }
    foreach ((array)$items as $item) {
        $option_value = '';
        $option_label = '';

        if (is_object($item) && isset($item->$key) && isset($item->$label)) {
            // Xử lý mảng object (từ get_results)
            $option_value = $item->$key;
            $option_label = $item->$label;
        } elseif (!is_object($item) && !is_array($item)) {
            // Xử lý mảng giá trị đơn (từ get_col)
            $option_value = $item;
            $option_label = $item;
        } else {
            continue; // Bỏ qua nếu định dạng không nhận ra
        }

        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($option_value),
            selected($selected, $option_value, false),
            esc_html($option_label)
        );
    }
}

// Render thông báo nâng cấp lên Pro
// Thông báo này sẽ hiển thị trong các module khác nhau
function aerp_render_pro_block($feature_name = 'tính năng này', $module_name = 'AERP')
{
    echo '<div class="aerp-pro-warning" style="border:1px solid #ccd0d4; background:#fff3cd; padding:20px; margin-top:10px;">';
    echo '<h3 style="margin-top:0;">🔒 Tính năng Pro</h3>';
    echo '<p>' . sprintf('Chức năng <strong>%s</strong> chỉ khả dụng khi nâng cấp lên bản <strong>%s</strong>.', esc_html($feature_name), esc_html($module_name)) . '</p>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=aerp_license')) . '" class="button button-primary">Nâng cấp ngay</a></p>';
    echo '</div>';
}

// Allow plugin modules to apply filters
add_filter('aerp_get_departments', 'aerp_get_departments');
add_filter('aerp_get_positions', 'aerp_get_positions');
add_filter('aerp_get_company_info', 'aerp_get_company_info');
add_filter('aerp_get_work_locations', 'aerp_get_work_locations');
add_filter('aerp_get_employees_with_location', 'aerp_get_employees_with_location');

/**
 * Hook tính thưởng động: chỉ cộng các mục thưởng động đã gán cho nhân viên trong tháng
 */
function aerp_hrm_bonus_hook($bonus, $employee_id, $month)
{
    global $wpdb;
    $bonus_total = 0;
    $month_start = date('Y-m-01', strtotime($month));

    // Lấy các reward_id mà nhân viên được gán trong tháng này
    $employee_rewards = $wpdb->get_results($wpdb->prepare(
        "SELECT r.*, er.note
         FROM {$wpdb->prefix}aerp_hrm_employee_rewards er
         INNER JOIN {$wpdb->prefix}aerp_hrm_reward_definitions r ON r.id = er.reward_id
         WHERE er.employee_id = %d AND er.month = %s",
        $employee_id,
        $month_start
    ));

    foreach ($employee_rewards as $reward) {
        $bonus_total += floatval($reward->amount);
    }

    return $bonus_total;
}
add_filter('aerp_hrm_auto_bonus', 'aerp_hrm_bonus_hook', 10, 3);

//Hook chuyển hướng
function aerp_js_redirect($url)
{
    echo '<script>window.location.href="' . esc_url_raw($url) . '";</script>';
    exit;
}

//Breadcrumb
function aerp_render_breadcrumb($items = [])
{
    if (empty($items) || !is_array($items)) {
        return;
    }

    // Normalize: allow [ 'Title' => 'url', 'Current' => '' ] style
    $normalized = [];
    $isAssocSimple = array_values($items) !== $items; // associative (simple map)
    if ($isAssocSimple) {
        foreach ($items as $label => $url) {
            $normalized[] = [
                'label' => $label,
                'url' => $url,
            ];
        }
    } else {
        $normalized = $items;
    }

    echo '<nav aria-label="breadcrumb" class="mb-3">';
    echo '<div class="card">';
    echo '<div class="card-body">';
    echo '<ol class="breadcrumb mb-0">';
    $lastIndex = count($normalized) - 1;
    foreach ($normalized as $index => $item) {
        $label = isset($item['label']) ? $item['label'] : '';
        $url = isset($item['url']) ? $item['url'] : '';
        $icon = isset($item['icon']) ? $item['icon'] : '';
        $isActive = empty($url) || $index === $lastIndex;

        if ($isActive) {
            echo '<li class="breadcrumb-item active" aria-current="page">';
            if (!empty($icon)) {
                echo '<i class="' . esc_attr($icon) . ' me-1"></i>';
            }
            echo esc_html($label) . '</li>';
        } else {
            echo '<li class="breadcrumb-item">';
            echo '<a href="' . esc_url($url) . '">';
            if (!empty($icon)) {
                echo '<i class="' . esc_attr($icon) . ' me-1"></i>';
            }
            echo esc_html($label) . '</a></li>';
        }
    }
    echo '</ol>';
    echo '</div>';
    echo '</div>';
    echo '</nav>';
}

/**
 * Kiểm tra user có quyền (permission) không (từ role hoặc quyền đặc biệt)
 * @param int $user_id
 * @param string $permission_name (tên quyền, cột name trong bảng aerp_permissions)
 * @return bool
 */
function aerp_user_has_permission($user_id, $permission_name)
{
    global $wpdb;
    // Lấy permission_id từ tên quyền
    $permission_id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_permissions WHERE name = %s",
        $permission_name
    ));
    if (!$permission_id) return false;

    // Kiểm tra quyền đặc biệt
    $has_special = $wpdb->get_var($wpdb->prepare(
        "SELECT 1 FROM {$wpdb->prefix}aerp_user_permission WHERE user_id = %d AND permission_id = %d",
        $user_id,
        $permission_id
    ));
    if ($has_special) return true;

    // Kiểm tra quyền từ nhóm quyền
    $has_role = $wpdb->get_var($wpdb->prepare(
        "SELECT 1 FROM {$wpdb->prefix}aerp_user_role ur
         JOIN {$wpdb->prefix}aerp_role_permission rp ON ur.role_id = rp.role_id
         WHERE ur.user_id = %d AND rp.permission_id = %d",
        $user_id,
        $permission_id
    ));
    return (bool)$has_role;
}


/**
 * Kiểm tra user có thuộc nhóm quyền (role) nào đó không
 * @param int $user_id
 * @param string $role_name (tên nhóm quyền, ví dụ: 'admin', 'department_lead')
 * @return bool
 */
function aerp_user_has_role($user_id, $role_name)
{
    global $wpdb;
    $role_id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_roles WHERE name = %s",
        $role_name
    ));
    if (!$role_id) return false;
    return (bool) $wpdb->get_var($wpdb->prepare(
        "SELECT 1 FROM {$wpdb->prefix}aerp_user_role WHERE user_id = %d AND role_id = %d",
        $user_id,
        $role_id
    ));
}


/**
 * Xóa toàn bộ cache transient bảng (prefix aerp_table_)
 */
function aerp_clear_table_cache()
{
    global $wpdb;
    $transients = $wpdb->get_col(
        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_aerp_table_%'"
    );
    foreach ($transients as $transient) {
        $key = str_replace('_transient_', '', $transient);
        delete_transient($key);
    }
}
