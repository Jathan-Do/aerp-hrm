<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class AERP_Salary_Table extends AERP_Base_Table
{

    protected $employee_id;

    public function __construct($employee_id = 0)
    {
        $this->employee_id = absint($employee_id);
        parent::__construct([
            'singular' => 'salary',
            'plural'   => 'salaries',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'          => '<input type="checkbox" />',
            'salary_month'     => 'Tháng',
            'base_salary'      => 'Lương cơ bản',
            'bonus'            => 'Thưởng',
            'auto_bonus'       => 'Thưởng động',
            'deduction'        => 'Phạt',
            'advance_paid'     => 'Ứng lương',
            'final_salary'     => 'Thực lãnh',
            'work_days'        => 'Ngày công',
            'off_days'         => 'Ngày nghỉ',
            'ot_days'          => 'Tăng ca',
            'ranking'          => 'Xếp loại',
            'points_total'     => 'Điểm',
            'note'             => 'Ghi chú',
        ];
    }
    public function column_cb($item)
    {
        return '<input type="checkbox" name="id[]" value="' . esc_attr($item['id']) . '" />';
    }

    public function column_default($item, $column_name)
    {
        $currency_fields = [
            'base_salary',
            'bonus',
            'auto_bonus',
            'deduction',
            'advance_paid',
            'final_salary'
        ];

        if (in_array($column_name, $currency_fields)) {
            return number_format((float)$item[$column_name], 0, ',', '.') . ' đ';
        }

        if ($column_name === 'salary_month') {
            return date('m/Y', strtotime($item[$column_name]));
        }
        if ($column_name === 'work_days') {
            // Tính lại số ngày làm việc chuẩn trong tháng
            $month = $item['salary_month'];
            $start = new DateTime($month);
            $end = new DateTime($month);
            $end->modify('last day of this month');
            $work_days_standard = 0;
            for ($d = clone $start; $d <= $end; $d->modify('+1 day')) {
                $w = (int)$d->format('N');
                if ($w < 6) $work_days_standard++;
            }
            return $work_days_standard;
        }
        if (in_array($column_name, ['work_days', 'ot_days'])) {
            return number_format((float)$item[$column_name], 1, ',', '');
        }
        if ($column_name === 'off_days') {
            return (int)$item[$column_name];
        }

        return esc_html($item[$column_name] ?? '');
    }

    public function prepare_items($month = null)
    {
        global $wpdb;

        $per_page     = 10;
        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;
        $base         = $wpdb->prefix . 'aerp_hrm_salaries';

        $orderby = $_REQUEST['orderby'] ?? '';
        $order   = $_REQUEST['order'] ?? 'desc';
        $search  = $_POST['s'] ?? '';

        // ---- WHERE mặc định ----
        $where_clauses = [];

        if (!empty($this->employee_id)) {
            $where_clauses[] = $wpdb->prepare("employee_id = %d", $this->employee_id);
        }

        if (!empty($month)) {
            $month_start = date('Y-m-01', strtotime($month));
            $where_clauses[] = $wpdb->prepare("salary_month = %s", $month_start);
        }

        // ---- Search nhiều cột ----
        $searchable = $this->get_searchable_columns();
        if (!empty($search) && !empty($searchable)) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $search_clauses = [];

            foreach ($searchable as $column) {
                $search_clauses[] = $wpdb->prepare("$column LIKE %s", $like);
            }

            if (!empty($search_clauses)) {
                $where_clauses[] = '(' . implode(' OR ', $search_clauses) . ')';
            }
        }

        // Gộp tất cả WHERE
        $where = count($where_clauses) ? implode(' AND ', $where_clauses) : '1=1';

        // ---- ORDER BY ----
        $order_by = "salary_month DESC";
        $sortable = $this->get_sortable_columns();
        if ($orderby && isset($sortable[$orderby])) {
            $order_by = sanitize_sql_orderby("{$orderby} " . ($order === 'asc' ? 'ASC' : 'DESC'));
        }

        // ---- TRUY VẤN CHÍNH ----
        $query = "SELECT * FROM $base WHERE $where ORDER BY $order_by LIMIT %d OFFSET %d";

        $this->items = $wpdb->get_results(
            $wpdb->prepare($query, $per_page, $offset),
            ARRAY_A
        );

        // Tổng số bản ghi
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $base WHERE $where");

        // Fallback tính final_salary nếu thiếu
        foreach ($this->items as &$row) {
            if (!isset($row['final_salary'])) {
                $row['final_salary'] = round(
                    floatval($row['base_salary']) +
                    floatval($row['bonus']) +
                    floatval($row['auto_bonus'] ?? 0) -
                    floatval($row['deduction']) -
                    floatval($row['advance_paid'] ?? 0)
                );
            }
        }

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);

        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];
    }


    public function get_sortable_columns()
    {
        return [
            'salary_month' => ['salary_month', true],
            'final_salary' => ['final_salary', false],
            'base_salary'  => ['base_salary', false],
            'deduction'    => ['deduction', false],
        ];
    }

    public function get_searchable_columns()
    {
        return [
            'note',
            'ranking',
            'salary_month'
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
                "DELETE FROM {$wpdb->prefix}aerp_hrm_salaries WHERE id IN ($placeholders)",
                $ids
            ));
        }
    }
}
