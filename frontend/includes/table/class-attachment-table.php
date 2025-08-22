<?php
if (!defined('ABSPATH')) {
    exit;
}
class AERP_Frontend_Attachment_Table extends AERP_Frontend_Table
{
    protected $employee_id;
    public function __construct($args = [])
    {
        $this->employee_id = $args['employee_id'] ?? 0;
        parent::__construct([
            'table_name' => $GLOBALS['wpdb']->prefix . 'aerp_hrm_attachments',
            'columns' => [
                // 'id' => 'ID',
                'file_name'       => 'Tên file',
                'attachment_type' => 'Loại hồ sơ',
                'file_type'       => 'Định dạng',
                'storage_type'    => 'Nơi lưu trữ',
                'uploaded_at'     => 'Ngày tải',
                'actions'         => 'Thao tác',
            ],
            'sortable_columns' => ['id', 'file_name', 'attachment_type', 'file_type', 'storage_type', 'uploaded_at'],
            'searchable_columns' => ['file_name', 'attachment_type', 'file_type', 'storage_type', 'uploaded_at'],
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => [],
            'bulk_actions' => ['delete'],
            'base_url' => home_url('/aerp-hrm-employees/?action=view&id=' . $this->employee_id . '&section=attachment'),
            'delete_item_callback' => ['AERP_Frontend_Attachment_Manager', 'delete_attachment_by_id'],
            'nonce_action_prefix' => 'delete_attachment_',
            'message_transient_key' => 'aerp_attachment_message',
            'hidden_columns_option_key' => 'aerp_hrm_attachment_table_hidden_columns',
            'ajax_action' => 'aerp_hrm_filter_attachment',
            'table_wrapper' => '#aerp-attachment-table-wrapper',
        ]);
    }
    protected function column_actions($item)
    {
        $edit_url = home_url("/aerp-hrm-employees/?action=view&id={$this->employee_id}&section=attachment&sub_action=edit&attachment_id={$item->id}");
        $delete_url = wp_nonce_url(
            home_url("/aerp-hrm-employees/?action=view&id={$this->employee_id}&section=attachment&sub_action=delete&attachment_id={$item->id}&employee_id={$this->employee_id}"),
            'delete_attachment_' . $item->id
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
        return [$filters, $params];
    }
    public function column_storage_type($item)
    {
        $types = [
            'local' => '<span class="dashicons dashicons-admin-home" title="Lưu trên máy chủ"></span> Máy chủ',
            'drive' => '<span class="dashicons dashicons-google" title="Lưu trên Google Drive"></span> Google Drive',
            'manual' => '<span class="dashicons dashicons-admin-links" title="Link thủ công"></span> Link thủ công'
        ];
        return $types[$item->storage_type] ?? '—';
    }
    public function column_file_name($item)
    {
        return sprintf(
            '<strong><a class="text-decoration-none" href="%s" target="_blank">%s</a></strong>',
            esc_url($item->file_url),
            esc_html($item->file_name)
        );
    }
}
