<?php
class AERP_Position_Table extends AERP_Base_Table {

    public function get_columns() {
        return [
            'cb'          => '<input type="checkbox" />',
            'id'          => 'ID',
            'name'        => 'Tên chức vụ',
            'description' => 'Mô tả',
            'created_at'  => 'Ngày tạo',
        ];
    }

    public function get_sortable_columns() {
        return [
            'id'         => ['id', true],
            'name'       => ['name', true],
            'created_at' => ['created_at', false],
        ];
    }

    public function get_bulk_actions() {
        return ['delete' => 'Xoá'];
    }

    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="id[]" value="%s" />', esc_attr($item['id']));
    }

    public function column_name($item) {
        $edit_url = admin_url('admin.php?page=aerp_positions&edit=' . $item['id']);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=aerp_positions&delete=' . $item['id']),
            'aerp_delete_position_' . $item['id']
        );

        $actions = [
            'edit'   => '<a href="' . esc_url($edit_url) . '">Sửa</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Bạn có chắc muốn xoá chức vụ này?\')">Xoá</a>',
        ];

        return sprintf('<strong>%s</strong> %s', esc_html($item['name']), $this->row_actions($actions));
    }

    public function get_searchable_columns() {
        return ['name', 'description'];
    }

    public function process_bulk_action() {
        if ($this->current_action() === 'delete' && !empty($_POST['id'])) {
            global $wpdb;
            $ids = array_map('absint', $_POST['id']);
            $placeholders = implode(',', array_fill(0, count($ids), '%d'));
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}aerp_hrm_positions WHERE id IN ($placeholders)",
                ...$ids // đúng cú pháp
            ));
            add_action('admin_notices', function () {
                echo '<div class="updated"><p>Đã xóa các chức vụ được chọn!</p></div>';
            });
        }
    }
}

