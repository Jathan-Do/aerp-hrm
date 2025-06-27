<?php
if (!defined('ABSPATH')) exit;

class AERP_Frontend_Salary_Config_Table extends AERP_Frontend_Table
{
    protected $employee_id;

    public function __construct($args = [])
    {
        $this->employee_id = $args['employee_id'] ?? 0;

        $columns = [
            'start_date'  => 'Từ ngày',
            'end_date'    => 'Đến ngày',
            'base_salary' => 'Lương cơ bản',
            'allowance'   => 'Phụ cấp',
            'created_at'  => 'Ngày tạo',
            'actions'     => 'Thao tác',
        ];

        $sortable = ['start_date', 'end_date', 'base_salary', 'allowance', 'created_at'];
        $searchable = ['base_salary', 'allowance'];

        parent::__construct(array_merge([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_salary_config',
            'columns' => $columns,
            'sortable_columns' => $sortable,
            'searchable_columns' => $searchable,
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => [],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-hrm-employees/?action=view&id=' . $this->employee_id . '&section=salary_config'),
            'delete_item_callback' => ['AERP_Frontend_Salary_Config_Manager', 'delete_salary_config_by_id'],
            'nonce_action_prefix' => 'delete_salary_config_',
            'message_transient_key' => 'aerp_salary_config_message',
            'hidden_columns_option_key' => 'aerp_hrm_salary_config_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_salary_config',
            'table_wrapper' => '#aerp-salary-config-table-wrapper',

        ], $args));
    }

    protected function column_start_date($item)
    {
        return esc_html(date('d/m/Y', strtotime($item->start_date)));
    }

    protected function column_end_date($item)
    {
        return esc_html(date('d/m/Y', strtotime($item->end_date)));
    }

    protected function column_base_salary($item)
    {
        return number_format((float)$item->base_salary, 0, ',', '.') . ' đ';
    }

    protected function column_allowance($item)
    {
        return number_format((float)$item->allowance, 0, ',', '.') . ' đ';
    }

    protected function column_created_at($item)
    {
        return date('d/m/Y H:i', strtotime($item->created_at));
    }

    protected function column_actions($item)
    {
        $edit_url = home_url("/aerp-hrm-employees/?action=view&id={$this->employee_id}&section=advance&sub_action=edit&advance_id={$item->id}");

        $delete_url = wp_nonce_url(
            home_url("/aerp-hrm-employees/?action=view&id={$this->employee_id}&section=salary_config&sub_action=delete&config_id={$item->id}&employee_id={$this->employee_id}"),
            'delete_salary_config_' . $item->id
        );

        return sprintf(
            '<a href="%s" class="btn btn-sm btn-success mb-2 mb-md-0"><i class="fas fa-edit"></i></a> 
         <a href="%s" class="btn btn-sm btn-danger" onclick="return confirm(\'Bạn có chắc muốn xóa?\')"><i class="fas fa-trash"></i></a>',
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

        return [$filters, $params];
    }
}
