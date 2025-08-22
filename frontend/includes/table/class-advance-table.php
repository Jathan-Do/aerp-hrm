<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Advance_Table extends AERP_Frontend_Table
{
    protected $employee_id;

    public function __construct($args = [])
    {
        $this->employee_id = $args['employee_id'] ?? 0;

        $columns = [
            'advance_date' => 'Ngày ứng',
            'amount'       => 'Số tiền',
            'note'         => 'Ghi chú',
            'created_at'   => 'Ngày tạo',
            'actions'      => 'Thao tác',
        ];

        $sortable = ['id', 'advance_date', 'amount', 'created_at'];
        $searchable = ['amount'];

        parent::__construct(array_merge([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_advance_salaries',
            'columns' => $columns,
            'sortable_columns' => $sortable,
            'searchable_columns' => $searchable,
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => [],
            'bulk_actions' => ['delete'],
            'delete_item_callback' => ['AERP_Frontend_Advance_Manager', 'delete_by_id'],
            'nonce_action_prefix' => 'delete_advance_',
            'message_transient_key' => 'aerp_advance_message',
            'hidden_columns_option_key' => 'aerp_advance_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_advance',
            'table_wrapper' => '#aerp-advance-table-wrapper',
            'base_url' => home_url('/aerp-hrm-employees/?action=view&id=' . $this->employee_id . '&section=advance'),
        ], $args));
    }
    protected function column_actions($item)
    {
        $edit_url = home_url("/aerp-hrm-employees/?action=view&id={$this->employee_id}&section=advance&sub_action=edit&advance_id={$item->id}");

        $delete_url = wp_nonce_url(
            home_url("/aerp-hrm-employees/?action=view&id={$this->employee_id}&section=advance&sub_action=delete&advance_id={$item->id}&employee_id={$this->employee_id}"),
            'delete_advance_' . $item->id
        );

        return sprintf(
            '<a data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Chỉnh sửa" href="%s" class="btn btn-sm btn-success mb-2 mb-md-0"><i class="fas fa-edit"></i></a> 
         <a data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Xóa" href="%s" class="btn btn-sm btn-danger" onclick="return confirm(\'Bạn có chắc muốn xóa?\')"><i class="fas fa-trash"></i></a>',
            esc_url($edit_url),
            esc_url($delete_url)
        );
    }



    protected function column_amount($item)
    {
        return number_format((float)$item->amount, 0, ',', '.') . ' đ';
    }

    protected function column_advance_date($item)
    {
        return date('d/m/Y', strtotime($item->advance_date));
    }

    protected function column_created_at($item)
    {
        return date('d/m/Y H:i', strtotime($item->created_at));
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

        return [$filters, $params];
    }
}
