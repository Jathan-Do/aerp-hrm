<?php
if (!defined('ABSPATH')) exit;

class AERP_Advance_Manager
{
    public static function handle_submit()
    {
        if (!current_user_can('manage_options')) return;

        if (
            isset($_POST['aerp_add_advance']) &&
            check_admin_referer('aerp_add_advance_action', 'aerp_add_advance_nonce')
        ) {
            global $wpdb;

            $employee_id = absint($_POST['employee_id']);
            $amount      = floatval($_POST['amount']);
            $date        = sanitize_text_field($_POST['advance_date']);

            if ($employee_id && $amount && $date) {
                $wpdb->insert($wpdb->prefix . 'aerp_hrm_advance_salaries', [
                    'employee_id'   => $employee_id,
                    'amount'        => $amount,
                    'advance_date'  => $date,
                    'created_at'    => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
                ]);

                add_action('admin_notices', function () {
                    echo '<div class="updated"><p>✅ Đã ghi nhận tạm ứng lương.</p></div>';
                });
            }
        }
    }
}
