<?php
if (!defined('ABSPATH')) exit;

class AERP_Attendance_Table extends AERP_Base_Table
{
    protected $employee_id;

    public function __construct($employee_id)
    {
        $this->employee_id = absint($employee_id);
        parent::__construct([
            'singular' => 'attendance',
            'plural'   => 'attendances',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'          => '<input type="checkbox" />',
            'work_date'   => 'Ngày chấm công',
            'shift'       => 'Loại chấm công',
            'work_ratio'  => 'Hệ số',
            'note'        => 'Ghi chú',
        ];
    }
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%d" />', esc_attr($item['id']));
    }
    public function get_sortable_columns()
    {
        return [
            'work_date'  => ['work_date', true],
            'work_ratio' => ['work_ratio', false],
        ];
    }

    public function get_searchable_columns()
    {
        return ['note', 'shift'];
    }

    public function get_primary_column_name()
    {
        return 'work_date';
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
                "DELETE FROM {$wpdb->prefix}aerp_hrm_attendance WHERE id IN ($placeholders)",
                ...$ids
            ));

            add_action('admin_notices', function () {
                echo '<div class="updated"><p>Đã xoá các ca được chọn.</p></div>';
            });
        }
    }
    public function prepare_items()
    {
        global $wpdb;

        $month = $_GET['att_month'] ?? '';
        $where_clauses = [
            $wpdb->prepare("employee_id = %d", $this->employee_id)
        ];

        if (!empty($month)) {
            $start = date('Y-m-01', strtotime($month));
            $end   = date('Y-m-t', strtotime($month));
            $where_clauses[] = $wpdb->prepare("work_date BETWEEN %s AND %s", $start, $end);
        }

        // Tìm kiếm
        $search = $_REQUEST['s'] ?? '';
        if (!empty($search)) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $search_clauses = [];
            foreach ($this->get_searchable_columns() as $column) {
                $search_clauses[] = $wpdb->prepare("$column LIKE %s", $like);
            }
            if ($search_clauses) {
                $where_clauses[] = '(' . implode(' OR ', $search_clauses) . ')';
            }
        }

        $where = implode(' AND ', $where_clauses);
        $query = "SELECT * FROM {$wpdb->prefix}aerp_hrm_attendance WHERE $where ORDER BY work_date DESC";
        $results = $wpdb->get_results($query, ARRAY_A);

        $this->set_data($results);
        parent::prepare_items();
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'work_date':
                return date('d/m/Y H:i', strtotime($item['work_date']));
            case 'work_ratio':
                return number_format($item['work_ratio'], 1);
            default:
                return parent::column_default($item, $column_name);
        }
    }
    public function column_shift($item)
    {
        $edit_url = admin_url('admin.php?page=aerp_attendance_edit&employee_id=' . $this->employee_id . '&id=' . $item['id']);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=aerp_employees&tab=attendance&employee_id=' . $this->employee_id . '&delete_attendance=' . $item['id']),
            'aerp_delete_attendance_' . $item['id']
        );

        $label = ucfirst($item['shift']);
        $actions = [
            'edit' => '<a href="' . esc_url($edit_url) . '">Sửa</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Bạn có chắc muốn xoá dòng chấm công này?\')">Xoá</a>',
        ];

        return '<strong>' . esc_html($label) . '</strong> ' . $this->row_actions($actions);
    }
}
