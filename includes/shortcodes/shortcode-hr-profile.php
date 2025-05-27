<?php
// File: includes/shortcodes.php
if (!defined('ABSPATH')) exit;

add_shortcode('aerp_hr_profile', 'aerp_shortcode_hr_profile');

function aerp_shortcode_hr_profile() {
    if (!is_user_logged_in()) {
        $login_url = wp_login_url();
        return '<p>Bạn cần <a href="' . esc_url($login_url) . '">đăng nhập</a> để xem hồ sơ.</p>';
    }

    $user_id = get_current_user_id();
    $employee = aerp_get_employee_by_user_id($user_id);

    if (!$employee) return '<p>Không tìm thấy hồ sơ nhân viên.</p>';

    // Include file giao diện
    ob_start();
    include AERP_HRM_PATH . 'frontend/employee-profile.php';
    return ob_get_clean();
}
