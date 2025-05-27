<?php
if (!defined('ABSPATH')) exit;

class AERP_Ranking_Settings_Table extends AERP_Base_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'ranking',
            'plural'   => 'rankings',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'          => '<input type="checkbox" />',
            'rank_code'   => 'Xếp loại',
            'min_point'   => 'Từ điểm',
            'note'        => 'Ghi chú',
            'sort_order'  => 'Thứ tự',
        ];
    }
    public function column_cb($item)
    {
        return '<input type="checkbox" name="id[]" value="' . esc_attr($item['id']) . '" />';
    }
    public function get_sortable_columns()
    {
        return [
            'min_point'  => ['min_point', true],
            'sort_order' => ['sort_order', false],
        ];
    }

    public function get_searchable_columns()
    {
        return ['rank_code', 'note'];
    }

    public function get_primary_column_name()
    {
        return 'rank_code';
    }

    public function column_default($item, $column_name)
    {
        return esc_html($item[$column_name] ?? '');
    }

    public function column_rank_code($item)
    {
        $edit_url = admin_url('admin.php?page=aerp_ranking_settings&edit=' . $item['id']);

        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=aerp_ranking_settings&delete_ranking=' . $item['id']),
            'aerp_delete_ranking_' . $item['id']
        );

        $actions = [
            'edit' => '<a href="' . esc_url($edit_url) . '">Sửa</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Bạn có chắc muốn xoá cấu hình này?\')">Xoá</a>',
        ];

        return '<strong>' . esc_html($item['rank_code']) . '</strong>' . $this->row_actions($actions);
    }

    public function prepare_items()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'aerp_hrm_ranking_settings';

        $orderby = $_REQUEST['orderby'] ?? '';
        $order   = $_REQUEST['order'] ?? 'desc';
        $search  = $_REQUEST['s'] ?? '';

        $where = '1=1';
        if (!empty($search)) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where .= $wpdb->prepare(" AND (rank_code LIKE %s OR note LIKE %s)", $like, $like);
        }

        $order_by = 'sort_order ASC, min_point DESC';
        if ($orderby && in_array($orderby, ['min_point', 'sort_order'])) {
            $order_by = sanitize_sql_orderby("{$orderby} " . ($order === 'asc' ? 'ASC' : 'DESC'));
        }

        $results = $wpdb->get_results("SELECT * FROM $table WHERE $where ORDER BY $order_by", ARRAY_A);
        $this->set_data($results);
        parent::prepare_items();
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
                "DELETE FROM {$wpdb->prefix}aerp_hrm_ranking_settings WHERE id IN ($placeholders)",
                $ids
            ));
        }
    }
}
