<?php
if (!defined('ABSPATH')) {
    exit;
}

class AERP_Frontend_Table
{
    protected $items = [];
    protected $total_items = 0;
    protected $per_page = 10;
    protected $current_page = 1;
    protected $sort_column = 'name';
    protected $sort_order = 'asc';
    protected $search_term = '';
    protected $table_name = '';
    protected $columns = [];
    protected $sortable_columns = [];
    protected $searchable_columns = [];
    protected $primary_key = 'id';
    protected $actions = [];
    protected $bulk_actions = [];
    protected $base_url = '';
    protected $delete_item_callback = null;
    protected $nonce_action_prefix = '';
    protected $message_transient_key = '';
    protected $bulk_action_nonce_key = 'aerp_bulk_action';
    protected $hidden_columns_option_key = '';
    protected $visible_columns = [];
    protected $show_cb = true;
    protected $ajax_action = '';
    protected $table_wrapper = '';
    protected $filters = [];

    public function __construct($args = [])
    {
        $this->current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

        // Merge default args with provided args
        $defaults = [
            'table_name' => '',
            'columns' => [],
            'sortable_columns' => [],
            'searchable_columns' => ['name'],
            'primary_key' => 'id',
            'per_page' => 10,
            'actions' => ['edit', 'delete'],
            'bulk_actions' => ['delete'],
            'base_url' => '', // default, class con có thể override
            'delete_item_callback' => null,
            'nonce_action_prefix' => '',
            'message_transient_key' => '',
            'bulk_action_nonce_key' => 'aerp_bulk_action',
            'hidden_columns_option_key' => '',
            'show_cb' => true,
            'ajax_action' => '',
            'table_wrapper' => '',
        ];

        $args = wp_parse_args($args, $defaults);

        foreach ($args as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        // Load user column preferences
        $this->load_column_preferences();

        // Set default sort column to first sortable column if available
        $default_sort_column = !empty($this->sortable_columns) ? $this->sortable_columns[0] : 'id';
        $this->sort_column = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : $default_sort_column;
        $this->sort_order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'asc';
        $this->search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    }

    /**
     * Load user-specific column display preferences.
     */
    protected function load_column_preferences()
    {
        if (!empty($this->hidden_columns_option_key) && is_user_logged_in()) {
            $hidden_cols = get_user_option($this->hidden_columns_option_key, get_current_user_id());
            if (false === $hidden_cols) {
                // No saved preference, all columns are visible by default
                $this->visible_columns = array_keys($this->columns);
            } else {
                $this->visible_columns = array_diff(array_keys($this->columns), (array) $hidden_cols);
            }
        } else {
            // If no option key is set or user not logged in, all columns are visible
            $this->visible_columns = array_keys($this->columns);
        }
    }

    /**
     * Save user-specific column display preferences.
     * @param array $hidden_cols Array of column keys to hide.
     */
    public function save_column_preferences($hidden_cols)
    {
        if (!empty($this->hidden_columns_option_key) && is_user_logged_in()) {
            update_user_option(get_current_user_id(), $this->hidden_columns_option_key, (array) $hidden_cols);
            return true;
        }
        return false;
    }

    protected function get_base_url($args = [])
    {
        $base = $this->base_url;

        // Start with relevant query parameters from $_GET
        $query_params = [];
        if (isset($_GET['s'])) {
            $query_params['s'] = sanitize_text_field($_GET['s']);
        }
        if (isset($_GET['orderby'])) {
            $query_params['orderby'] = sanitize_text_field($_GET['orderby']);
        }
        if (isset($_GET['order'])) {
            $query_params['order'] = sanitize_text_field($_GET['order']);
        }
        if (isset($_GET['paged'])) {
            $query_params['paged'] = intval($_GET['paged']);
        }

        // Merge with provided arguments. New arguments will override.
        $query_params = array_merge($query_params, $args);

        // Remove 'page' query var (WordPress admin specific)
        unset($query_params['page']);

        // Specific logic for resetting paged on 'delete' action
        if (isset($query_params['action']) && $query_params['action'] === 'delete') {
            unset($query_params['paged']);
        }

        // Remove 'paged' if it's 0 (meaning first page or no pagination for internal logic)
        if (isset($query_params['paged']) && $query_params['paged'] == 0) {
            unset($query_params['paged']);
        }

        // Return the raw URL string. esc_url will be applied at the point of output.
        $final_url = add_query_arg($query_params, $base);
        return $final_url;
    }

    public function set_filters($filters = [])
    {
        $this->filters = $filters;
        if (!empty($filters['search_term'])) $this->search_term = $filters['search_term'];
        if (!empty($filters['paged'])) $this->current_page = $filters['paged'];
        if (!empty($filters['orderby'])) $this->sort_column = $filters['orderby'];
        if (!empty($filters['order'])) $this->sort_order = $filters['order'];
    }

    /**
     * Cho phép class con mở rộng điều kiện search liên bảng
     * @return array [conditions[], params[]]
     */
    protected function get_extra_search_conditions($search_term)
    {
        return [[], []];
    }

    /**
     * Cho phép class con mở rộng filter đặc thù
     * @return array [conditions[], params[]]
     */
    protected function get_extra_filters()
    {
        return [[], []];
    }

    public function get_items()
    {
        global $wpdb;
        $where = [];
        $params = [];

        // Search
        if ($this->search_term && !empty($this->searchable_columns)) {
            $search_conditions = [];
            foreach ($this->searchable_columns as $column) {
                $search_conditions[] = "$column LIKE %s";
                $params[] = '%' . $wpdb->esc_like($this->search_term) . '%';
            }
            // Thêm điều kiện search mở rộng từ class con
            list($extra_search, $extra_params) = $this->get_extra_search_conditions($this->search_term);
            $search_conditions = array_merge($search_conditions, $extra_search);
            $params = array_merge($params, $extra_params);
            $where[] = '(' . implode(' OR ', $search_conditions) . ')';
        }

        // Filter mở rộng từ class con
        list($extra_filters, $extra_filter_params) = $this->get_extra_filters();
        $where = array_merge($where, $extra_filters);
        $params = array_merge($params, $extra_filter_params);

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($this->current_page - 1) * $this->per_page;

        // Tạo cache key duy nhất cho truy vấn này
        $cache_key = 'aerp_table_' . md5(
            $this->table_name . '|' .
                $where_clause . '|' .
                serialize($params) . '|' .
                $this->sort_column . '|' .
                $this->sort_order . '|' .
                $this->per_page . '|' .
                $offset . '|' .
                get_current_user_id()
        );

        // Thử lấy từ cache
        $cached = get_transient($cache_key);
        if ($cached !== false && isset($cached['items'], $cached['total_items'])) {
            $this->items = $cached['items'];
            $this->total_items = $cached['total_items'];
            return $this->items;
        }

        // Nếu không có cache, truy vấn như cũ
        $total_query = "SELECT COUNT(*) FROM {$this->table_name} $where_clause";
        if (!empty($params)) {
            $total_query = $wpdb->prepare($total_query, $params);
        }
        $this->total_items = $wpdb->get_var($total_query);

        $query = "SELECT * FROM {$this->table_name} $where_clause ORDER BY {$this->sort_column} {$this->sort_order} LIMIT %d OFFSET %d";
        $params2 = array_merge($params, [$this->per_page, $offset]);
        $this->items = $wpdb->get_results($wpdb->prepare($query, $params2));

        // Lưu cache
        set_transient($cache_key, [
            'items' => $this->items,
            'total_items' => $this->total_items
        ], 3600); // 1 hour cache

        return $this->items;
    }

    public function get_column_keys()
    {
        return array_keys($this->columns);
    }

    public function get_hidden_columns_option_key()
    {
        return $this->hidden_columns_option_key;
    }

    public function render()
    {
        $items = $this->get_items();
?>
        <div class="aerp-table-wrapper">
            <!-- Search form -->
            <?php if (!empty($this->searchable_columns)) : ?>
                <form method="get" class="mb-4 aerp-table-search-form aerp-table-ajax-form"
                    data-table-wrapper="<?php echo esc_attr($this->table_wrapper); ?>"
                    data-ajax-action="<?php echo esc_attr($this->ajax_action); ?>"
                    onsubmit="return false;">
                    <?php
                    // Giữ lại các tham số filter từ form chính để đảm bảo chúng không bị mất khi tìm kiếm, phân trang, sort
                    if (!empty($this->filters)) {
                        foreach ($this->filters as $key => $value) {
                            // Bỏ qua các tham số đã có sẵn trong form tìm kiếm hoặc do table tự quản lý
                            if (in_array($key, ['s', 'orderby', 'order', 'paged', 'search_term']) || empty($value)) {
                                continue;
                            }
                            echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr(stripslashes($value)) . '">';
                        }
                    }
                    ?>
                    <div class="input-group" style="justify-self: end;">
                        <input type="search" name="s" class="form-control aerp-table-search-input" placeholder="Tìm kiếm..." value="<?php echo esc_attr($this->search_term); ?>">
                    </div>
                </form>
            <?php endif; ?>

            <div class="d-flex justify-content-end position-relative mb-3">
                <a href="#" id="aerp-column-options-button" class="btn btn-secondary action">Tùy chọn cột</a>
                <div id="aerp-column-options-dropdown" class="dropdown-menu position-absolute bg-white border-1 border-secondary-subtle card-body" style="display:none; top: 50px">
                    <form id="aerp-column-options-form">
                        <?php wp_nonce_field('aerp_save_column_preferences', 'aerp_column_prefs_nonce'); ?>
                        <input type="hidden" name="option_key" value="<?php echo esc_attr($this->hidden_columns_option_key); ?>" />
                        <?php foreach ($this->columns as $key => $label): ?>
                            <p>
                                <label>
                                    <input class="form-check-input border-secondary" type="checkbox" name="aerp_visible_columns[]" value="<?php echo esc_attr($key); ?>" <?php checked(in_array($key, $this->visible_columns)); ?> />
                                    <?php echo esc_html($label); ?>
                                </label>
                            </p>
                        <?php endforeach; ?>
                        <p class="text-end mb-0">
                            <button type="submit" class="btn btn-primary w-100">Lưu thay đổi</button>
                        </p>
                    </form>
                </div>
            </div>

            <!-- Bulk actions form and Table -->
            <?php if (!empty($this->bulk_actions)): ?>
                <form method="post" class="mb-3">
                    <?php wp_nonce_field($this->bulk_action_nonce_key, 'aerp_bulk_nonce'); ?>
                    <div class="d-flex gap-2 align-items-center mb-3 justify-content-md-start justify-content-between">
                        <select name="bulk_action" class="form-select" style="width: auto;">
                            <option value="">Hành động hàng loạt</option>
                            <?php foreach ($this->bulk_actions as $action): ?>
                                <option value="<?php echo esc_attr($action); ?>"><?php echo esc_html(ucfirst($action)); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-secondary">Áp dụng</button>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <?php if ($this->show_cb): ?>
                                        <th scope="col" class="text-center" style="width: 40px;">
                                            <input id="cb-select-all-1" type="checkbox" class="form-check-input border-secondary" />
                                        </th>
                                    <?php endif; ?>
                                    <?php foreach ($this->columns as $key => $label): ?>
                                        <?php if (in_array($key, $this->visible_columns)): ?>
                                            <th scope="col" class="<?php echo in_array($key, $this->sortable_columns) ? 'sortable' : ''; ?>">
                                                <?php if (in_array($key, $this->sortable_columns)): ?>
                                                    <a href="<?php echo esc_url($this->get_base_url(['orderby' => $key, 'order' => ($this->sort_column === $key && strtolower($this->sort_order) === 'asc') ? 'desc' : 'asc'])); ?>"
                                                        class="text-decoration-none aerp-table-sort"
                                                        data-orderby="<?php echo esc_attr($key); ?>"
                                                        data-order="<?php echo esc_attr(($this->sort_column === $key && strtolower($this->sort_order) === 'asc') ? 'desc' : 'asc'); ?>">
                                                        <?php echo esc_html($label); ?>
                                                        <?php if ($this->sort_column === $key): ?>
                                                            <i class="fas fa-sort-<?php echo strtolower($this->sort_order); ?> ms-1"></i>
                                                        <?php else: ?>
                                                            <i class="fas fa-sort ms-1"></i>
                                                        <?php endif; ?>
                                                    </a>
                                                <?php else: ?>
                                                    <?php echo esc_html($label); ?>
                                                <?php endif; ?>
                                            </th>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if (!empty($this->actions)): ?>
                                        <th class="text-center" style="width: 100px;">Thao tác</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <tr>
                                        <td colspan="<?php echo count($this->columns) + ($this->show_cb ? 1 : 0); ?>" class="text-center py-4">
                                            <div class="text-muted">Không tìm thấy dữ liệu.</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <?php if ($this->show_cb): ?>
                                                <td class="text-center">
                                                    <input type="checkbox" name="bulk_items[]" value="<?php echo esc_attr($item->{$this->primary_key}); ?>" class="form-check-input border-secondary" />
                                                </td>
                                            <?php endif; ?>
                                            <?php foreach ($this->columns as $key => $label): ?>
                                                <?php if (in_array($key, $this->visible_columns)): ?>
                                                    <td>
                                                        <?php
                                                        $method_name = 'column_' . $key;
                                                        if (method_exists($this, $method_name)) {
                                                            echo $this->$method_name($item);
                                                        } elseif (isset($item->$key)) {
                                                            echo esc_html($item->$key);
                                                        } else {
                                                            echo '--';
                                                        }
                                                        ?>
                                                    </td>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                            <?php if (!empty($this->actions)): ?>
                                                <td class="text-center ">
                                                    <div class="d-flex gap-2 justify-content-center">
                                                        <?php $this->render_row_actions($item); ?>
                                                    </div>

                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            <?php else: ?>
                <!-- Table (without bulk action form) -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <?php foreach ($this->columns as $key => $label): ?>
                                    <?php if (in_array($key, $this->visible_columns)): ?>
                                        <th scope="col" class="<?php echo in_array($key, $this->sortable_columns) ? 'sortable' : ''; ?>">
                                            <?php if (in_array($key, $this->sortable_columns)): ?>
                                                <a href="<?php echo esc_url($this->get_base_url(['orderby' => $key, 'order' => ($this->sort_column === $key && strtolower($this->sort_order) === 'asc') ? 'desc' : 'asc'])); ?>"
                                                    class="text-decoration-none aerp-table-sort"
                                                    data-orderby="<?php echo esc_attr($key); ?>"
                                                    data-order="<?php echo esc_attr(($this->sort_column === $key && strtolower($this->sort_order) === 'asc') ? 'desc' : 'asc'); ?>">
                                                    <?php echo esc_html($label); ?>
                                                    <?php if ($this->sort_column === $key): ?>
                                                        <i class="fas fa-sort-<?php echo strtolower($this->sort_order); ?> ms-1"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-sort ms-1"></i>
                                                    <?php endif; ?>
                                                </a>
                                            <?php else: ?>
                                                <?php echo esc_html($label); ?>
                                            <?php endif; ?>
                                        </th>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if (!empty($this->actions)): ?>
                                    <th class="text-center" style="width: 100px;">Thao tác</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="<?php echo count($this->columns); ?>" class="text-center py-4">
                                        <div class="text-muted">Không tìm thấy dữ liệu.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <?php foreach ($this->columns as $key => $label): ?>
                                            <?php if (in_array($key, $this->visible_columns)): ?>
                                                <td>
                                                    <?php
                                                    $method_name = 'column_' . $key;
                                                    if (method_exists($this, $method_name)) {
                                                        echo $this->$method_name($item);
                                                    } elseif (isset($item->$key)) {
                                                        echo esc_html($item->$key);
                                                    } else {
                                                        echo '--';
                                                    }
                                                    ?>
                                                </td>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <?php if (!empty($this->actions)): ?>
                                            <td class="text-center">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <?php $this->render_row_actions($item); ?>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php $this->render_pagination(); ?>
        </div>
        <?php wp_nonce_field($this->nonce_action_prefix . '_bulk_action', $this->bulk_action_nonce_key); ?>

<?php
    }

    protected function render_row_actions($item)
    {
        $actions = [];

        if (in_array('edit', $this->actions)) {
            $actions['edit'] = sprintf(
                '<a href="%s" class="btn btn-sm btn-success"><i class="fas fa-edit"></i></a>',
                esc_url($this->get_base_url(['action' => 'edit', 'id' => $item->{$this->primary_key}]))
            );
        }

        if (in_array('delete', $this->actions)) {
            $actions['delete'] = sprintf(
                '<a href="%s" class="btn btn-sm btn-danger" onclick="return confirm(\'Bạn có chắc muốn xóa?\')"><i class="fas fa-trash"></i></a>',
                esc_url($this->get_base_url(['action' => 'delete', 'id' => $item->{$this->primary_key}, '_wpnonce' => wp_create_nonce($this->nonce_action_prefix . $item->{$this->primary_key})]))
            );
        }

        echo implode(' ', $actions);
    }

    // Helper to get total pages
    protected function get_total_pages()
    {
        if ($this->per_page === 0) {
            return 0; // Avoid division by zero
        }
        return ceil($this->total_items / $this->per_page);
    }

    protected function render_pagination($location = 'top')
    {
        $total_items = $this->total_items;
        $total_pages = $this->get_total_pages();
        $current_page = $this->current_page;

        if ($total_pages <= 1 && $total_items === 0) {
            return;
        }

        echo '<div class="tablenav-pages d-flex align-items-center justify-content-md-end justify-content-center">';

        // Display total items
        if ($total_items > 0) {
            echo '<span class="displaying-num">' . sprintf(_n('%s mục', '%s mục', $total_items, 'aerp-hrm'), number_format_i18n($total_items)) . '</span>';
        }
        // if ($total_pages > 10) {
        echo '<span class="pagination-links aerp-pagination">';
        // }

        $big = 999999999; // need a large number to represent the page number placeholder

        $base_url_for_pagination = $this->get_base_url(['paged' => $big]);

        $pagination_args = array(
            'base'      => str_replace($big, '%#%', $base_url_for_pagination),
            'format'    => '?paged=%#%',
            'current'   => $current_page,
            'total'     => $total_pages,
            'prev_text' => '<i class="dashicons dashicons-arrow-left-alt2"></i>',
            'next_text' => '<i class="dashicons dashicons-arrow-right-alt2"></i>',
        );

        echo paginate_links($pagination_args);

        echo '</span>'; // .pagination-links
        echo '</div>'; // .tablenav-pages
    }

    public function process_bulk_action()
    {
        if (!isset($_POST['bulk_action']) || empty($_POST['bulk_items'])) {
            error_log('AERP_Frontend_Table: Bulk action - No action or no IDs. Exiting.');
            return;
        }

        if (!wp_verify_nonce($_POST['aerp_bulk_nonce'], $this->bulk_action_nonce_key)) {
            error_log('AERP_Frontend_Table: Bulk action - Nonce verification failed.');
            wp_die('Invalid nonce for bulk action.');
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $ids = array_map('intval', $_POST['bulk_items']);

        if (!in_array($action, $this->bulk_actions)) {
            error_log('AERP_Frontend_Table: Bulk action - Invalid action: ' . $action);
            return;
        }

        $success_message = '';

        switch ($action) {
            case 'delete':
                error_log('AERP_Frontend_Table: Bulk action - Deleting items.');
                $deleted_count = 0;
                if (is_callable($this->delete_item_callback)) {
                    foreach ($ids as $id) {
                        if (call_user_func($this->delete_item_callback, $id)) {
                            $deleted_count++;
                        }
                    }
                } else {
                    error_log('AERP_Frontend_Table: Bulk action - Delete callback not set or not callable.');
                }

                if ($deleted_count > 0) {
                    $success_message = sprintf(__('Đã xóa %d mục được chọn!', 'aerp-hrm'), $deleted_count);
                    error_log('AERP_Frontend_Table: Bulk action - Successfully deleted ' . $deleted_count . ' items.');
                } else {
                    $success_message = 'Không có mục nào được xóa.';
                    error_log('AERP_Frontend_Table: Bulk action - No items were deleted.');
                }
                break;
        }

        if ($success_message) {
            set_transient($this->message_transient_key, $success_message, 10);
            wp_redirect($this->get_base_url());
            exit;
        }
    }

    /**
     * AJAX handler to save user column preferences.
     */
    public static function handle_save_column_preferences()
    {
        if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'aerp_save_column_preferences')) {
            wp_send_json_error('Nonce verification failed.');
        }

        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in.');
        }

        $hidden_columns = isset($_POST['hidden_columns']) ? array_map('sanitize_text_field', (array) $_POST['hidden_columns']) : [];
        $option_key = isset($_POST['option_key']) ? sanitize_text_field($_POST['option_key']) : '';

        if (empty($option_key)) {
            wp_send_json_error('Option key is missing.');
        }

        // Create a dummy instance of the table class to access the save_column_preferences method
        // This is a workaround as save_column_preferences is not static.
        // In a real scenario, you might refactor save_column_preferences to be static or use a different approach.
        $dummy_table = new self(['hidden_columns_option_key' => $option_key, 'columns' => []]); // Pass a dummy columns array

        if ($dummy_table->save_column_preferences($hidden_columns)) {
            wp_send_json_success('Column preferences saved successfully.');
        } else {
            wp_send_json_error('Failed to save column preferences.');
        }
        wp_die(); // Always die to terminate the AJAX request properly
    }
}
