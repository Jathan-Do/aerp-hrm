<?php
// Shortcode: [aerp_login]
function aerp_custom_login_form()
{
    ob_start();

    // Hiển thị thông báo lỗi
    $error = '';
    if (isset($_GET['login'])) {
        $error = match ($_GET['login']) {
            'failed' => '<div id="aerp-hrm-toast" class="aerp-hrm-toast error"><span>Tên đăng nhập hoặc mật khẩu không đúng!</span><button onclick="closeToast()">X</button></div>',
            'empty' => '<div id="aerp-hrm-toast" class="aerp-hrm-toast warning"><span>Vui lòng nhập đầy đủ thông tin!</span><button onclick="closeToast()">X</button></div>',
            default => ''
        };
    }
?>

    <div class="aerp-hrm-dashboard">
        <div class="aerp-card login-card">
            <div class="card-header">
                <img src="<?php echo AERP_HRM_URL . 'assets/images/logo.png'; ?>" alt="Logo" class="logo" style="width: 50px; margin-bottom: 10px;">
                <h2>Đăng nhập hệ thống</h2>
            </div>

            <?php echo $error; ?>

            <form action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post" class="aerp-form">
                <div class="form-group">
                    <label for="user_login">
                        <span class="dashicons dashicons-admin-users"></span>
                        Tên đăng nhập
                    </label>
                    <input type="text" name="log" id="user_login" required autocomplete="username" placeholder="Nhập tên đăng nhập">
                </div>

                <div class="form-group">
                    <label for="user_pass">
                        <span class="dashicons dashicons-unlock"></span>
                        Mật khẩu
                    </label>
                    <input type="password" name="pwd" id="user_pass" required autocomplete="current-password" placeholder="Nhập mật khẩu">
                </div>

                <div class="form-group remember-me">
                    <label>
                        <input type="checkbox" name="rememberme" value="forever">
                        <span>Ghi nhớ đăng nhập</span>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="aerp-login-btn">
                        <span class="dashicons dashicons-admin-users"></span>
                        Đăng nhập
                    </button>
                </div>

                <?php
                // Lấy URL redirect dựa vào role
                $redirect_url = aerp_get_login_redirect_url();
                ?>
                <input type="hidden" name="redirect_to" value="<?php echo esc_url($redirect_url); ?>">
                <?php wp_nonce_field('aerp_login_nonce', 'aerp_login_nonce'); ?>
            </form>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('aerp_login', 'aerp_custom_login_form');

// Hàm helper để lấy URL redirect
function aerp_get_login_redirect_url()
{
    // Nếu đã đăng nhập, lấy role hiện tại
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        // Ưu tiên: admin -> hr_manager -> department_lead -> accountant -> employee
        if (aerp_user_has_role($user_id, 'admin')) {
            return home_url('/aerp-dashboard');
        }
        if (aerp_user_has_role($user_id, 'hr_manager')) {
            return home_url('/aerp-quan-ly');
        }
        if (aerp_user_has_role($user_id, 'department_lead')) {
            return home_url('/aerp-quan-ly');
        }
        if (aerp_user_has_role($user_id, 'accountant')) {
            return home_url('/aerp-ke-toan');
        }
        if (aerp_user_has_role($user_id, 'employee')) {
            return home_url('/aerp-ho-so-nhan-vien');
        }
    }

    // Mặc định về trang dashboard
    return home_url('/aerp-ho-so-nhan-vien');
}

// Thêm filter để xử lý redirect sau khi đăng nhập
add_filter('login_redirect', function ($redirect_to, $requested_redirect_to, $user) {
    if (!is_wp_error($user) && $user) {
        $user_id = $user->ID;
        // Ưu tiên: admin -> hr_manager -> department_lead -> accountant -> employee
        if (aerp_user_has_role($user_id, 'admin')) {
            return home_url('/aerp-dashboard');
        }
        if (aerp_user_has_role($user_id, 'hr_manager')) {
            return home_url('/aerp-quan-ly');
        }
        if (aerp_user_has_role($user_id, 'department_lead')) {
            return home_url('/aerp-quan-ly');
        }
        if (aerp_user_has_role($user_id, 'accountant')) {
            return home_url('/aerp-ke-toan');
        }
        if (aerp_user_has_role($user_id, 'employee')) {
            return home_url('/aerp-ho-so-nhan-vien');
        }
    }
    return $redirect_to;
}, 10, 3);
