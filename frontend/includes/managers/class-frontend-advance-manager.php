<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Advance_Manager
{
    public static function handle_form_submit() {
        if (
            !isset($_POST['aerp_save_advance']) ||
            !wp_verify_nonce($_POST['aerp_save_advance_nonce'], 'aerp_save_advance_action')
        ) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_advance_salaries';

        $data = [
            'employee_id'  => absint($_POST['employee_id']),
            'amount'       => floatval($_POST['amount']),
            'advance_date' => sanitize_text_field($_POST['advance_date']),
            'created_at'   => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s'),
        ];

        $id = isset($_POST['advance_id']) ? absint($_POST['advance_id']) : 0;
        if ($id) {
            $wpdb->update($table, $data, ['id' => $id]);
            $msg = 'Đã cập nhật tạm ứng!';
        } else {
            $wpdb->insert($table, $data);
            $id = $wpdb->insert_id;
            $msg = 'Đã thêm tạm ứng!';
        }
        aerp_clear_table_cache();
        set_transient('aerp_advance_message', $msg, 10);
        wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $_POST['employee_id'] . '&section=advance'));
        exit;
    }

    public static function get_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_advance_salaries';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public static function get_filtered($args) {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_advance_salaries';
        $where = "1=1";
        $params = [];

        if (!empty($args['employee_id'])) {
            $where .= $wpdb->prepare(" AND employee_id = %d", $args['employee_id']);
        }
        if (!empty($args['advance_date'])) {
            $where .= $wpdb->prepare(" AND advance_date = %s", $args['advance_date']);
        }
        if (!empty($args['search'])) {
            $s = esc_sql($args['search']);
            $where .= " AND (amount LIKE '%$s%')";
        }

        $limit  = absint($args['per_page'] ?? 10);
        $offset = (($args['paged'] ?? 1) - 1) * $limit;
        $orderby = esc_sql($args['orderby'] ?? 'advance_date');
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
        $deleted = $wpdb->delete($wpdb->prefix . 'aerp_hrm_advance_salaries', ['id' => absint($id)]);
        aerp_clear_table_cache();
        return (bool) $deleted;
    }
    public static function handle_single_delete() {
        $advance_id = absint($_GET['advance_id'] ?? 0);
        $nonce_action = 'delete_advance_' . $advance_id;
        if ($advance_id && check_admin_referer($nonce_action)) {
            if (self::delete_by_id($advance_id)) {
                $message = 'Đã xóa tạm ứng thành công!';
            } else {
                $message = 'Không thể xóa tạm ứng.';
            }
            aerp_clear_table_cache();
            set_transient('aerp_advance_message', $message, 10);
            wp_redirect(home_url('/aerp-hrm-employees/?action=view&id=' . $_GET['employee_id'] . '&section=advance'));
            exit;
        }
        wp_die('Invalid request or nonce.');
    }
} 