<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Salary_Config_Manager {
    public static function handle_form_submit() {
        if (!isset($_POST['aerp_save_salary_config']) && !isset($_POST['aerp_edit_salary_config'])) return;

        if (!wp_verify_nonce($_POST['aerp_salary_nonce'], 'aerp_salary_config_action')) {
            wp_die('Invalid nonce.');
        }

        global $wpdb;
        $employee_id = absint($_POST['employee_id'] ?? 0);
        if (!$employee_id) return;

        $start_date  = sanitize_text_field($_POST['start_date']);
        $end_date    = sanitize_text_field($_POST['end_date']);
        $base_salary = floatval($_POST['base_salary']);
        $allowance   = floatval($_POST['allowance']);
        $salary_mode = sanitize_text_field($_POST['salary_mode'] ?? 'fixed');
        $commission_scheme_id = isset($_POST['commission_scheme_id']) ? absint($_POST['commission_scheme_id']) : null;

        $edit_id = absint($_POST['config_id'] ?? 0);
        if ($edit_id) {
            $wpdb->update(
                $wpdb->prefix . 'aerp_hrm_salary_config',
                [
                    'start_date'   => $start_date,
                    'end_date'     => $end_date,
                    'base_salary'  => $base_salary,
                    'allowance'    => $allowance,
                    'salary_mode'  => $salary_mode,
                    'commission_scheme_id' => $commission_scheme_id
                ],
                ['id' => $edit_id, 'employee_id' => $employee_id]
            );
        } else {
            $wpdb->insert(
                $wpdb->prefix . 'aerp_hrm_salary_config',
                [
                    'employee_id' => $employee_id,
                    'start_date'  => $start_date,
                    'end_date'    => $end_date,
                    'base_salary' => $base_salary,
                    'allowance'   => $allowance,
                    'salary_mode' => $salary_mode,
                    'commission_scheme_id' => $commission_scheme_id,
                    'created_at'  => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
                ]
            );

            $last_config = $wpdb->get_row($wpdb->prepare(
                "SELECT base_salary FROM {$wpdb->prefix}aerp_hrm_salary_config WHERE employee_id = %d ORDER BY start_date DESC LIMIT 1 OFFSET 1",
                $employee_id
            ));

            if ($last_config && floatval($last_config->base_salary) !== $base_salary) {
                $journey = new AERP_HRM_Employee_Journey();
                $journey->add_event(
                    $employee_id,
                    'salary_change',
                    floatval($last_config->base_salary),
                    $base_salary,
                    'Thay đổi lương cơ bản'
                );
            }
        }

        aerp_clear_table_cache();
        set_transient('aerp_salary_config_message', 'Lưu cấu hình lương thành công!', 10);
        wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $_POST['employee_id'] . '&section=salary_config'));
        exit;
    }

    public static function handle_single_delete() {
        $config_id = absint($_GET['config_id'] ?? 0);
        $nonce_action = 'delete_salary_config_' . $config_id;
        if ($config_id && check_admin_referer($nonce_action)) {
            if (self::delete_salary_config_by_id($config_id)) {
                $message = 'Đã xóa cấu hình lương thành công!';
            } else {
                $message = 'Không thể xóa cấu hình lương.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_salary_config_message', $message, 10);
            wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $_GET['employee_id'] . '&section=salary_config'));
            exit;
        }
        wp_die('Invalid request or nonce.');
    }
    public static function delete_salary_config_by_id($id)
    {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_salary_config', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool) $deleted;
    }

    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}aerp_hrm_salary_config WHERE id = %d",
            $id
        ));
    }
}
