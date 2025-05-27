<?php
if (!defined('ABSPATH')) exit;

class AERP_Adjustment_Manager
{
    public static function handle_submit()
    {
        if (!current_user_can('manage_options')) return;

        if (isset($_POST['aerp_add_adjustment']) && check_admin_referer('aerp_add_adjustment_action', 'aerp_add_adjustment_nonce')) {
            self::insert();
        }

        if (isset($_POST['aerp_update_adjustment']) && check_admin_referer('aerp_edit_adjustment_action', 'aerp_edit_adjustment_nonce')) {
            self::update();
        }
    }

    public static function handle_delete()
    {
        if (
            isset($_GET['delete_adjustment'], $_GET['_wpnonce']) &&
            wp_verify_nonce($_GET['_wpnonce'], 'aerp_delete_adjustment_' . $_GET['delete_adjustment'])
        ) {
            global $wpdb;
            $id = absint($_GET['delete_adjustment']);
            $wpdb->delete($wpdb->prefix . 'aerp_hrm_adjustments', ['id' => $id]);
        }
    }

    protected static function insert()
    {
        global $wpdb;

        $employee_id = absint($_POST['employee_id']);
        $reason      = sanitize_text_field($_POST['reason']);
        $amount      = floatval($_POST['amount']);
        $type        = sanitize_text_field($_POST['type']);
        $description = sanitize_textarea_field($_POST['description']);
        $date        = sanitize_text_field($_POST['date_effective']);

        $wpdb->insert($wpdb->prefix . 'aerp_hrm_adjustments', [
            'employee_id'     => $employee_id,
            'reason'          => $reason,
            'amount'          => $amount,
            'type'            => $type,
            'description'     => $description,
            'date_effective'  => $date,
            'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
        ]);

        add_action('admin_notices', function () {
            echo '<div class="updated"><p>✅ Đã thêm điều chỉnh.</p></div>';
        });
    }

    protected static function update()
    {
        global $wpdb;

        $id          = absint($_POST['id']);
        $employee_id = absint($_POST['employee_id']);
        $reason        = sanitize_text_field($_POST['reason']);
        $amount      = floatval($_POST['amount']);
        $type        = sanitize_text_field($_POST['type']);
        $description = sanitize_text_field($_POST['description']);
        $date        = sanitize_text_field($_POST['date_effective']);

        $wpdb->update($wpdb->prefix . 'aerp_hrm_adjustments', [
            'reason'         => $reason,
            'amount'         => $amount,
            'type'           => $type,
            'description'    => $description,
            'date_effective' => $date,
        ], ['id' => $id]);

        add_action('admin_notices', function () {
            echo '<div class="updated"><p>✅ Đã cập nhật điều chỉnh.</p></div>';
        });
    }
    public static function get_employee_id($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}aerp_hrm_adjustments WHERE employee_id = %d
        ", $id));
    }
    public static function get_by_id($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}aerp_hrm_adjustments WHERE id = %d
        ", $id));
    }

    public static function get_adjustments_by_employee($employee_id, $month = null)
    {
        global $wpdb;

        $where = "employee_id = %d";
        $args = [$employee_id];

        if ($month) {
            $start = date('Y-m-01', strtotime($month));
            $end   = date('Y-m-t', strtotime($month));
            $where .= " AND date_effective BETWEEN %s AND %s";
            $args[] = $start;
            $args[] = $end;
        }

        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}aerp_hrm_adjustments WHERE $where ORDER BY date_effective DESC
        ", ...$args));
    }

    public static function add($data)
    {
        global $wpdb;

        return $wpdb->insert(
            $wpdb->prefix . 'aerp_hrm_adjustments',
            [
                'employee_id'     => absint($data['employee_id']),
                'type'           => sanitize_text_field($data['type']),
                'amount'         => floatval($data['amount']),
                'reason'         => sanitize_text_field($data['reason']),
                'date_effective' => sanitize_text_field($data['date_effective']),
                'description'    => sanitize_textarea_field($data['description']),
                'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
            ]
        );
    }
}
