<?php
class AERP_Attachment_Table extends AERP_Base_Table
{
    protected $employee_id;

    public function __construct($employee_id)
    {
        $this->employee_id = absint($employee_id);
        parent::__construct([
            'singular' => 'attachment',
            'plural'   => 'attachments',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'              => '<input type="checkbox" />',
            'file_name'       => 'Tên file',
            'attachment_type' => 'Loại hồ sơ',
            'file_type'       => 'Định dạng',
            'storage_type'    => 'Nơi lưu trữ',
            'uploaded_at'     => 'Ngày tải',
        ];
    }
    public function get_bulk_actions()
    {
        return ['delete' => 'Xoá'];
    }
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%d" />', esc_attr($item['id']));
    }

    public function column_file_name($item)
    {
        $edit_url = admin_url('admin.php?page=aerp_attachment_edit&id=' . $item['id']);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=aerp_employees&view=' . $item['employee_id'] . '&tab=attachments&delete_attachment=' . $item['id']),
            'aerp_delete_attachment_' . $item['id']
        );

        $actions = [
            'edit' => '<a href="' . esc_url($edit_url) . '">Sửa</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Bạn có chắc muốn xoá hồ sơ này?\')">Xoá</a>',
        ];

        return sprintf(
            '<strong><a href="%s" target="_blank">%s</a></strong> %s',
            esc_url($item['file_url']),
            esc_html($item['file_name']),
            $this->row_actions($actions)
        );
    }

    public function column_storage_type($item)
    {
        $types = [
            'local' => '<span class="dashicons dashicons-admin-home" title="Lưu trên máy chủ"></span> Máy chủ',
            'drive' => '<span class="dashicons dashicons-google" title="Lưu trên Google Drive"></span> Google Drive',
            'manual' => '<span class="dashicons dashicons-admin-links" title="Link thủ công"></span> Link thủ công'
        ];
        return $types[$item['storage_type']] ?? '—';
    }

    public function column_uploaded_at($item)
    {
        return date('d/m/Y H:i', strtotime($item['uploaded_at']));
    }

    public function column_default($item, $column_name)
    {
        return esc_html($item[$column_name] ?? '—');
    }
    public function process_bulk_action()
    {
        if ($this->current_action() === 'delete' && !empty($_POST['id'])) {
            global $wpdb;
            $ids = array_map('absint', $_POST['id']);
            $placeholders = implode(',', array_fill(0, count($ids), '%d'));

            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}aerp_hrm_attachments WHERE id IN ($placeholders)",
                ...$ids
            ));

            add_action('admin_notices', function () {
                echo '<div class="updated"><p>Đã xóa các hồ sơ được chọn.</p></div>';
            });
        }
    }

    public function prepare_items()
    {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}aerp_hrm_attachments WHERE employee_id = %d",
            $this->employee_id
        ), ARRAY_A);

        $this->set_data($results);  // <-- đây
        parent::prepare_items();    // <-- gọi lại hàm cha
    }


    public function get_sortable_columns()
    {
        return [
            'file_name'   => ['file_name', true],
            'file_type'   => ['file_type', false],
            'uploaded_at' => ['uploaded_at', true],
        ];
    }
    public function get_searchable_columns()
    {
        return ['file_name', 'file_type', 'attachment_type'];
    }
}
