<?php
class AERP_Adjustment_Table extends AERP_Base_Table
{
    protected $employee_id;

    public function __construct($employee_id)
    {
        $this->employee_id = absint($employee_id);
        parent::__construct([
            'singular' => 'adjustment',
            'plural'   => 'adjustments',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'             => '<input type="checkbox" />',
            'reason'         => 'Lý do',
            'date_effective' => 'Ngày áp dụng',
            'type'           => 'Loại',
            'amount'         => 'Số tiền',
            'description'    => 'Ghi chú',
        ];
    }

    public function get_bulk_actions()
    {
        return ['delete' => 'Xoá'];
    }
    public function process_bulk_action()
    {
        if ($this->current_action() === 'delete' && !empty($_POST['id'])) {
            global $wpdb;
            $ids = array_map('absint', $_POST['id']);
            $placeholders = implode(',', array_fill(0, count($ids), '%d'));

            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}aerp_hrm_adjustments WHERE id IN ($placeholders)",
                ...$ids
            ));

            add_action('admin_notices', function () {
                echo '<div class="updated"><p>Đã xóa các điều chỉnh được chọn.</p></div>';
            });
        }
    }
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%d" />', esc_attr($item['id']));
    }
    public function get_sortable_columns()
    {
        return [
            'date_effective' => ['date_effective', true],
            'amount'         => ['amount', false],
        ];
    }

    public function get_searchable_columns()
    {
        return ['reason'];
    }

    public function column_type($item)
    {
        return $item['type'] === 'reward' ? '🎁 Thưởng' : '⚠️ Phạt';
    }

    public function column_amount($item)
    {
        return number_format($item['amount'], 0, ',', '.') . ' đ';
    }
    public function prepare_items()
    {
        global $wpdb;
        $data = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}aerp_hrm_adjustments
            WHERE employee_id = %d ORDER BY date_effective DESC
        ", $this->employee_id), ARRAY_A);

        $this->set_data($data);
        parent::prepare_items();
    }
    public function column_reason($item)
    {
        $edit_url = admin_url('admin.php?page=aerp_adjustment_edit&id=' . $item['id'] . '&employee_id=' . $item['employee_id']);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=aerp_employees&view=' . $item['employee_id'] . '&tab=adjustment&delete_adjustment=' . $item['id']),
            'aerp_delete_adjustment_' . $item['id']
        );

        $actions = [
            'edit' => '<a href="' . esc_url($edit_url) . '">Sửa</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Bạn có chắc muốn xoá điều chỉnh này?\')">Xoá</a>',
        ];

        return sprintf(
            '<strong>%s</strong> %s',
            esc_html($item['reason']),
            $this->row_actions($actions)
        );
    }
}
