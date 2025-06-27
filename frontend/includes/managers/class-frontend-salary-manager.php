<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Salary_Manager
{
    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_salaries';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public static function get_filtered($args) {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_salaries';
        $where = "1=1";
        $params = [];

        if (!empty($args['employee_id'])) {
            $where .= $wpdb->prepare(" AND employee_id = %d", $args['employee_id']);
        }
        if (!empty($args['salary_month'])) {
            $where .= $wpdb->prepare(" AND salary_month = %s", $args['salary_month']);
        }
        if (!empty($args['search'])) {
            $s = esc_sql($args['search']);
            $where .= " AND (note LIKE '%$s%' OR ranking LIKE '%$s%')";
        }

        $limit  = absint($args['per_page'] ?? 10);
        $offset = (($args['paged'] ?? 1) - 1) * $limit;
        $orderby = esc_sql($args['orderby'] ?? 'salary_month');
        $order = esc_sql($args['order'] ?? 'DESC');

        $items = $wpdb->get_results("SELECT * FROM $table WHERE $where ORDER BY $orderby $order LIMIT $offset, $limit");
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where");

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    public static function delete_by_id($id) {
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_salaries', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool) $deleted;
    }
    public static function handle_single_delete() {
        $id = absint($_GET['id'] ?? 0);
        $nonce_action = 'delete_salary_' . $id;
        if ($id && check_admin_referer($nonce_action)) {
            if (self::delete_by_id($id)) {
                $message = 'Đã xóa lương thành công!';
            } else {
                $message = 'Không thể xóa lương.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_salary_message', $message, 10);
            wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $_GET['employee_id'] . '&section=salary'));
            exit;
        }
        wp_die('Invalid request or nonce.');
    }
    public static function calculate_salary($employee_id, $month) {
        // Copy logic từ class-salary-manager.php (backend) sang đây nếu cần
        aerp_clear_table_cache();
        if (!class_exists('AERP_Salary_Manager')) return false;
        return AERP_Salary_Manager::calculate_salary($employee_id, $month);
    }
} 