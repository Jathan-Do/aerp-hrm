<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class AERP_Task_Table extends AERP_Base_Table
{

    protected $employee_id;

    public function __construct($employee_id)
    {
        $this->employee_id = absint($employee_id);
        parent::__construct([
            'singular' => 'task',
            'plural'   => 'tasks',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'         => '<input type="checkbox" />',
            'task_title' => 'Tên công việc',
            'task_desc'  => 'Mô tả',
            'deadline'   => 'Hạn chót',
            'score'      => 'Điểm KPI',
            'status'     => 'Trạng thái',
            'comments' => 'Phản hồi'
        ];
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%d" />', esc_attr($item['id']));
    }

    public function column_task_title($item)
    {
        $edit_url = admin_url('admin.php?page=aerp_task_edit&id=' . $item['id']);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=aerp_employees&view=' . $item['employee_id'] . '&delete_task=' . $item['id']),
            'aerp_delete_task_' . $item['id']
        );

        $actions = [
            'edit' => '<a href="' . esc_url($edit_url) . '">Sửa</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Bạn có chắc muốn xoá công việc này?\')">Xoá</a>',
        ];

        return '<strong>' . esc_html($item['task_title']) . '</strong> ' . $this->row_actions($actions);
    }


    public function column_default($item, $column_name)
    {
        if ($column_name === 'deadline') {
            return date('d/m/Y H:i ', strtotime($item['deadline']));
        }
        if ($column_name === 'comments') {
            $count = AERP_Task_Manager::count_comments($item['id']);
            return $count ? "$count phản hồi" : '—';
        }
        return esc_html($item[$column_name] ?? '');
    }

    public function get_sortable_columns()
    {
        return [
            'deadline' => ['deadline', true],
            'score'    => ['score', false],
        ];
    }

    public function get_searchable_columns()
    {
        return ['task_title', 'task_desc', 'status', 'score'];
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
                "DELETE FROM {$wpdb->prefix}aerp_hrm_tasks WHERE id IN ($placeholders)",
                ...$ids
            ));

            add_action('admin_notices', function () {
                echo '<div class="updated"><p>Đã xóa các công việc được chọn.</p></div>';
            });
        }
    }

    public function get_tasks_by_month($month, $year)
    {
        return AERP_Task_Manager::get_tasks_by_month($this->employee_id, $month, $year);
    }

    public function prepare_items()
    {
        global $wpdb;

        $table         = $wpdb->prefix . 'aerp_hrm_tasks';
        $per_page      = 10;
        $current_page  = $this->get_pagenum();
        $offset        = ($current_page - 1) * $per_page;

        $orderby       = $_REQUEST['orderby'] ?? 'deadline';
        $order         = $_REQUEST['order'] ?? 'desc';
        $search        = $_REQUEST['s'] ?? '';
        $status_filter = $_GET['status'] ?? '';
        $month         = isset($_GET['month']) ? intval($_GET['month']) : date('n');
        $year          = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

        $where_clauses = [$wpdb->prepare("employee_id = %d", $this->employee_id)];
        $searchable    = $this->get_searchable_columns();

        // Tìm kiếm
        if (!empty($search) && !empty($searchable)) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $search_parts = [];
            foreach ($searchable as $col) {
                $search_parts[] = $wpdb->prepare("$col LIKE %s", $like);
            }
            $where_clauses[] = '(' . implode(' OR ', $search_parts) . ')';
        }

        // Lọc theo status nếu có
        if (!empty($status_filter)) {
            $where_clauses[] = $wpdb->prepare("status = %s", $status_filter);
        }

        // Lọc theo tháng và năm
        $start_date = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
        $end_date = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
        $where_clauses[] = $wpdb->prepare("deadline BETWEEN %s AND %s", $start_date, $end_date);

        $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

        // Sắp xếp
        $sortable = $this->get_sortable_columns();
        $order_by = isset($sortable[$orderby])
            ? sanitize_sql_orderby("{$orderby} " . (strtoupper($order) === 'ASC' ? 'ASC' : 'DESC'))
            : "deadline DESC";

        // Truy vấn dữ liệu
        $sql_data = $wpdb->prepare("SELECT * FROM $table $where_sql ORDER BY $order_by LIMIT %d OFFSET %d", $per_page, $offset);
        $items = $wpdb->get_results($sql_data, ARRAY_A);

        // Đếm tổng dòng
        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table $where_sql");

        // Gán dữ liệu
        $this->items = $items;
        $this->set_pagination_args([
            'total_items' => $total,
            'per_page'    => $per_page,
        ]);

        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
            $this->get_primary_column_name()
        ];
    }
}
