<?php
// Load WP_List_Table nếu chưa tồn tại
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class AERP_Base_Table extends WP_List_Table
{
    protected $data = [];
    protected $total_items = 0;

    public function __construct($args = [])
    {
        $defaults = [
            'singular' => 'item',     // tên đơn
            'plural'   => 'items',    // tên số nhiều
            'ajax'     => false
        ];
        parent::__construct(array_merge($defaults, $args));
    }

    /**
     * Cung cấp dữ liệu và tổng số dòng
     */
    public function set_data($data, $total = null)
    {
        $this->data = is_array($data) ? $data : [];
        $this->total_items = $total ?? count($this->data);
    }

    /**
     * Các cột hiển thị mặc định – override ở class con
     */
    public function get_columns()
    {
        return [
            'cb'   => '<input type="checkbox" />',
            'id'   => 'ID',
            'name' => 'Tên'
        ];
    }

    public function get_sortable_columns()
    {
        return [];
    }

    public function get_bulk_actions()
    {
        return [];
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%s" />', esc_attr($item['id'] ?? ''));
    }

    public function column_default($item, $column_name)
    {
        if (!is_array($item) || !array_key_exists($column_name, $item)) {
            return '<span style="color:#aaa;">—</span>';
        }

        $value = $item[$column_name];

        if (is_array($value)) {
            return implode(', ', array_map('esc_html', $value));
        }

        return esc_html((string) $value);
    }

    public function extra_tablenav($which)
    {
        // Có thể override ở class con nếu cần
    }

    public function search_box($text, $input_id)
    {
        if (empty($_REQUEST['s']) && !$this->has_items()) {
            return;
        }

        $input_id = $input_id . '-search-input';

        echo '<p class="search-box">';
        echo '<label class="screen-reader-text" for="' . esc_attr($input_id) . '">' . esc_html($text) . ':</label>';
        echo '<input type="search" id="' . esc_attr($input_id) . '" name="s" value="' . esc_attr($_REQUEST['s'] ?? '') . '" />';
        submit_button($text, '', '', false, ['id' => 'search-submit']);
        echo '</p>';
    }

    public function prepare_items()
    {
        $columns  = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $hidden   = [];
        $primary  = $this->get_primary_column_name();

        $this->_column_headers = [$columns, $hidden, $sortable, $primary];

        $data = method_exists($this, 'filter_data') ? $this->filter_data($this->data, $_REQUEST) : $this->data;


        // Tìm kiếm
        $search = $_REQUEST['s'] ?? '';
        if (!empty($search)) {
            $searchable = $this->get_searchable_columns();
            $data = array_filter($data, function ($item) use ($search, $searchable) {
                foreach ($searchable as $column) {
                    if (isset($item[$column]) && stripos((string) $item[$column], $search) !== false) {
                        return true;
                    }
                }
                return false;
            });
        }

        // Trường hợp không có dữ liệu
        if (empty($data)) {
            $this->items = [];
            $this->set_pagination_args([
                'total_items' => 0,
                'per_page'    => $this->get_items_per_page($this->get_pagination_option_name(), 10),
            ]);
            return;
        }

        // Sắp xếp
        $orderby = $_REQUEST['orderby'] ?? '';
        $order   = $_REQUEST['order'] ?? 'asc';
        if ($orderby && isset($sortable[$orderby])) {
            usort($data, function ($a, $b) use ($orderby, $order) {
                $valA = $a[$orderby] ?? '';
                $valB = $b[$orderby] ?? '';
                return $order === 'asc' ? strnatcasecmp((string) $valA, (string) $valB) : strnatcasecmp((string) $valB, (string) $valA);
            });
        }

        // Phân trang
        $per_page     = $this->get_items_per_page($this->get_pagination_option_name(), 10);
        $current_page = $this->get_pagenum(); // đã override đúng ở con
        $offset       = ($current_page - 1) * $per_page;

        $this->items = array_slice($data, $offset, $per_page);

        $this->set_pagination_args([
            'total_items' => count($data),
            'per_page'    => $per_page,
        ]);
    }

    protected function get_pagination_option_name()
    {
        return $this->_args['plural'] . '_per_page';
    }

    public function get_searchable_columns()
    {
        return ['name'];
    }

    public function get_primary_column_name()
    {
        return 'name';
    }
}
