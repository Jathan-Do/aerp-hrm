<?php

// ============================
// COMMON FUNCTIONS FOR CORE MODULE
// ============================

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
function aerp_safe_select_options($items, $selected = '', $key = 'id', $label = 'name', $show_all_option = false)
{
    if ($show_all_option) {
        echo '<option value="">-- Tất cả --</option>';
    }
    foreach ((array)$items as $item) {
        if (!is_object($item) || !isset($item->$key) || !isset($item->$label)) continue;
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($item->$key),
            selected($selected, $item->$key, false),
            esc_html($item->$label)
        );
    }
}

// Render thông báo nâng cấp lên Pro
// Thông báo này sẽ hiển thị trong các module khác nhau
function aerp_render_pro_block($feature_name = 'tính năng này', $module_name = 'AERP') {
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
        $employee_id, $month_start
    ));

    foreach ($employee_rewards as $reward) {
        $bonus_total += floatval($reward->amount);
    }

    return $bonus_total;
}
add_filter('aerp_hrm_auto_bonus', 'aerp_hrm_bonus_hook', 10, 3);

//Hook chuyển hướng
function aerp_js_redirect($url) {
    echo '<script>window.location.href="' . esc_url_raw($url) . '";</script>';
    exit;
}
