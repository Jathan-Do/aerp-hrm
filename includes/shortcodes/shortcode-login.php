<?php
// Shortcode: [aerp_login]
function aerp_custom_login_form($atts)
{
    $atts = shortcode_atts([
        'redirect' => home_url('/aerp-ho-so-nhan-vien'),
    ], $atts);

    $redirect_url = isset($_GET['redirect_to']) ? esc_url($_GET['redirect_to']) : esc_url($atts['redirect']);

    // Nếu đã đăng nhập thì chuyển hướng
    if (is_user_logged_in()) {
        wp_redirect($redirect_url);
        exit;
    }

    // Hiển thị thông báo lỗi nếu có
    $error = '';
    if (isset($_GET['login']) && $_GET['login'] === 'failed') {
        $error = '<div id="aerp-hrm-toast" class="aerp-hrm-toast error"><span>Tên đăng nhập hoặc mật khẩu không đúng.</span><button onclick="closeToast()">X</button></div>';
    } elseif (isset($_GET['login']) && $_GET['login'] === 'empty') {
        $error = '<div id="aerp-hrm-toast" class="aerp-hrm-toast warning"><span>Vui lòng nhập đầy đủ thông tin.</span><button onclick="closeToast()">X</button></div>';
    }

    // Custom action xử lý đăng nhập
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aerp_login_nonce']) && wp_verify_nonce($_POST['aerp_login_nonce'], 'aerp_login')) {
        $creds = [
            'user_login'    => sanitize_user($_POST['log']),
            'user_password' => $_POST['pwd'],
            'remember'      => !empty($_POST['rememberme']),
        ];
        if (empty($creds['user_login']) || empty($creds['user_password'])) {
            wp_redirect(add_query_arg('login', 'empty'));
            exit;
        }
        $user = wp_signon($creds, false);
        if (is_wp_error($user)) {
            wp_redirect(add_query_arg('login', 'failed'));
            exit;
        } else {
            wp_redirect($redirect_url);
            exit;
        }
    }

    ob_start();
?>
    <div class="aerp-hrm-dashboard">
        <div class="aerp-card login-card">
            <div class="card-header">
                <h2>Đăng nhập hệ thống</h2>
            </div>

            <?php echo $error; ?>

            <form method="post" class="aerp-form">
                <?php wp_nonce_field('aerp_login', 'aerp_login_nonce'); ?>

                <div class="form-group">
                    <label for="aerp_user_login"><span class="dashicons dashicons-admin-users"></span> Tên đăng nhập</label>
                    <input type="text" id="aerp_user_login" name="log" required autocomplete="username" placeholder="Nhập tên đăng nhập">
                </div>

                <div class="form-group">
                    <label for="aerp_user_pass"><span class="dashicons dashicons-unlock"></span> Mật khẩu</label>
                    <input type="password" id="aerp_user_pass" name="pwd" required autocomplete="current-password" placeholder="Nhập mật khẩu">
                </div>

                <div class="form-group remember-me">
                    <label>
                        <input type="checkbox" name="rememberme" value="forever">
                        <span>Ghi nhớ đăng nhập</span>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="aerp-login-btn">
                        Đăng nhập
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .aerp-card.login-card {
            max-width: 420px;
            margin: 60px auto 0 auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 32px rgba(52, 152, 219, 0.10), 0 1.5px 6px rgba(44, 62, 80, 0.06);
            padding: 36px 36px 28px 36px;
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            border: 1px solid #eaf1fb;
            transition: box-shadow 0.2s;
        }

        .aerp-card.login-card:hover {
            box-shadow: 0 8px 40px rgba(52, 152, 219, 0.18), 0 2px 8px rgba(44, 62, 80, 0.10);
        }

        .aerp-card.login-card .card-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .aerp-card.login-card .card-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #2179c4;
            margin: 0;
            letter-spacing: 1px;
        }

        .aerp-card.login-card .aerp-form {
            padding: 0;
        }

        .aerp-card.login-card .form-group {
            margin-bottom: 22px;
        }

        .aerp-card.login-card label {
            display: block;
            margin-bottom: 7px;
            font-weight: 500;
            color: #2d3a4a;
            font-size: 1rem;
        }

        .aerp-card.login-card label .dashicons {
            font-size: 1.1em;
            color: #2179c4;
            margin-right: 5px;
            vertical-align: -2px;
        }

        .aerp-card.login-card input[type="text"],
        .aerp-card.login-card input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #dbeafe;
            border-radius: 8px;
            font-size: 1.08rem;
            background: #fafdff;
            transition: border-color 0.2s, box-shadow 0.2s;
            color: #2d3a4a;
            box-sizing: border-box;
        }

        .aerp-card.login-card input[type="text"]:focus,
        .aerp-card.login-card input[type="password"]:focus {
            border-color: #2179c4;
            outline: none;
            box-shadow: 0 0 0 2px #e3f0fc;
            background: #fff;
        }

        .aerp-card.login-card .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
        }

        .aerp-card.login-card .remember-me label {
            display: flex;
            align-items: center;
            cursor: pointer;
            margin-bottom: 0;
            font-weight: 400;
            color: #6b7a90;
            font-size: 0.98rem;
        }

        .aerp-card.login-card .remember-me input {
            margin-right: 8px;
        }

        .aerp-card.login-card .form-actions {
            margin-top: 10px;
        }

        .aerp-card.login-card .aerp-login-btn {
            width: 100%;
            background: linear-gradient(90deg, #2179c4 0%, #3498db 100%);
            color: #fff;
            border: none;
            padding: 13px 0;
            border-radius: 8px;
            font-size: 1.13rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }

        .aerp-card.login-card .aerp-login-btn:hover {
            background: linear-gradient(90deg, #3498db 0%, #2179c4 100%);
            box-shadow: 0 4px 16px rgba(52, 152, 219, 0.13);
        }

        .aerp-card.login-card .aerp-login-btn .dashicons {
            font-size: 1.2em;
            margin-right: 2px;
            vertical-align: -2px;
        }
    </style>
<?php
    return ob_get_clean();
}
add_shortcode('aerp_login', 'aerp_custom_login_form');
