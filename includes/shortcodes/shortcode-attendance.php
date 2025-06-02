<?php
// File: includes/shortcodes.php
if (!defined('ABSPATH')) exit;
add_shortcode('aerp_attendance', 'aerp_shortcode_attendance');

function aerp_shortcode_attendance()
{
    if (!is_user_logged_in()) {
        $login_url = site_url('/aerp-dang-nhap');
        return '<p>Bạn cần <a href="' . esc_url($login_url) . '">đăng nhập</a> để chấm công.</p>';
    }

    $user_id = get_current_user_id();
    $employee = aerp_get_employee_by_user_id($user_id);
    if (!$employee) return '<p>Không tìm thấy nhân viên tương ứng.</p>';

    ob_start();
    include_once AERP_HRM_PATH . 'frontend/attendance.php';
    return ob_get_clean();
}
