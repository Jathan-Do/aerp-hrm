<?php
// Shortcode: [aerp_login]
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
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập hệ thống</title>
    <?php wp_head(); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
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
<!-- 
                <div class="form-group remember-me">
                    <label>
                        <input type="checkbox" name="rememberme" value="forever">
                        <span>Ghi nhớ đăng nhập</span>
                    </label>
                </div> -->

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
    <?php wp_footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>