<?php
if (!defined('ABSPATH')) {
    exit;
}
class AERP_Frontend_Adjustment_Table extends AERP_Frontend_Table
{
    protected $employee_id;
    public function __construct($args = [])
    {
        $this->employee_id = $args['employee_id'] ?? 0;
        parent::__construct([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_adjustments',
            'columns' => [
                // 'id' => 'ID',
                'reason'         => 'Lý do',
                'date_effective' => 'Ngày áp dụng',
                'type'           => 'Loại',
                'amount'         => 'Số tiền',
                'description'    => 'Ghi chú',
                'actions'        => 'Thao tác',
            ],
            'sortable_columns' => ['id', 'date_effective', 'type', 'amount'],
            'searchable_columns' => ['reason', 'amount', 'description'],
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => [],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-hrm-employees/?action=view&id=' . $this->employee_id . '&section=adjustment'),
            'delete_item_callback' => ['AERP_Frontend_Adjustment_Manager', 'delete_by_id'],
            'nonce_action_prefix' => 'delete_adjustment_',
            'message_transient_key' => 'aerp_adjustment_message',
            'hidden_columns_option_key' => 'aerp_hrm_adjustment_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_adjustment',
            'table_wrapper' => '#aerp-adjustment-table-wrapper',
        ]);
    }
    protected function column_actions($item)
    {
        $edit_url = home_url("/aerp-hrm-employees/?action=view&id={$this->employee_id}&section=adjustment&sub_action=edit&adjustment_id={$item->id}");
        $delete_url = wp_nonce_url(
            home_url("/aerp-hrm-employees/?action=view&id={$this->employee_id}&section=adjustment&sub_action=delete&adjustment_id={$item->id}&employee_id={$this->employee_id}"),
            'delete_adjustment_' . $item->id
        );
        return sprintf(
            '<a data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Chỉnh sửa" href="%s" class="btn btn-sm btn-success mb-2 mb-md-0"><i class="fas fa-edit"></i></a> 
         <a data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Xóa" href="%s" class="btn btn-sm btn-danger" onclick="return confirm(\'Bạn có chắc muốn xóa?\')"><i class="fas fa-trash"></i></a>',
            esc_url($edit_url),
            esc_url($delete_url)
        );
    }
    public function set_filters($filters = [])
    {
        parent::set_filters($filters);
        if (!empty($filters['employee_id'])) {
            $this->employee_id = absint($filters['employee_id']);
        }
    }
    protected function get_extra_filters()
    {
        $filters = [];
        $params = [];

        if (!empty($this->employee_id)) {
            $filters[] = "employee_id = %d";
            $params[] = $this->employee_id;
        }
        if (!empty($this->filters['month'])) {
            $filters[] = "DATE_FORMAT(date_effective, '%Y-%m') = %s";
            $params[] = $this->filters['month'];
        }
        if (!empty($this->filters['type'])) {
            $filters[] = "type = %s";
            $params[] = $this->filters['type'];
        }
        return [$filters, $params];
    }
    public function column_amount($item)
    {
        return '<strong style="color:' . ($item->type === 'reward' ? 'green' : 'red') . '">' . number_format($item->amount, 0, ',', '.') . ' đ</strong>';
    }
    public function column_type($item)
    {
        $badge_class = $item->type === 'reward' ? 'bg-success' : 'bg-danger';
        $label = $item->type === 'reward' ? 'Thưởng' : 'Phạt';
        return '<span class="badge ' . $badge_class . '">' . $label . '</span>';
    }
}
