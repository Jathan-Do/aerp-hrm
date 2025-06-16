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
        ];

        $args = wp_parse_args($args, $defaults);

        foreach ($args as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        // Set default sort column to first sortable column if available
        $default_sort_column = !empty($this->sortable_columns) ? $this->sortable_columns[0] : 'id';
        $this->sort_column = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : $default_sort_column;
        $this->sort_order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'asc';
        $this->search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
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
        return add_query_arg($query_params, $base);
    }

    public function get_items()
    {
        global $wpdb;

        // Build where clause for search
        $where = [];
        $params = [];

        if ($this->search_term && !empty($this->searchable_columns)) {
            $search_conditions = [];
            foreach ($this->searchable_columns as $column) {
                $search_conditions[] = "$column LIKE %s";
                $params[] = '%' . $wpdb->esc_like($this->search_term) . '%';
            }
            $where[] = '(' . implode(' OR ', $search_conditions) . ')';
        }

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total items
        $total_query = "SELECT COUNT(*) FROM {$this->table_name} $where_clause";
        if (!empty($params)) {
            $total_query = $wpdb->prepare($total_query, $params);
        }
        $this->total_items = $wpdb->get_var($total_query);

        // Get items with pagination
        $offset = ($this->current_page - 1) * $this->per_page;

        // Validate sort column
        if (!in_array($this->sort_column, $this->sortable_columns)) {
            $this->sort_column = 'name';
        }

        $query = "SELECT * FROM {$this->table_name} $where_clause ORDER BY {$this->sort_column} {$this->sort_order} LIMIT %d OFFSET %d";
        $params[] = $this->per_page;
        $params[] = $offset;

        $this->items = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);

        return $this->items;
    }

    public function render()
    {
        $items = $this->get_items();
?>
        <div class="aerp-table-wrapper">
            <!-- Search form -->
            <form method="get" class="mb-4">
                <div class="input-group" style="justify-self: end;">
                    <input type="search" name="s" class="form-control" placeholder="Tìm kiếm..."
                        value="<?php echo esc_attr($this->search_term); ?>">
                    <button type="submit" class="btn btn-outline-secondary">Tìm kiếm</button>
                </div>
            </form>

            <!-- Bulk actions form and Table -->
            <?php if (!empty($this->bulk_actions)): ?>
                <form method="post" class="mb-3">
                    <?php wp_nonce_field($this->bulk_action_nonce_key, 'aerp_bulk_nonce'); ?>
                    <div class="d-flex gap-2 align-items-center mb-3">
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
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">
                                        <input type="checkbox" id="select-all" class="form-check-input border-dark-subtle">
                                    </th>
                                    <?php foreach ($this->columns as $key => $label): ?>
                                        <th>
                                            <?php if (in_array($key, $this->sortable_columns)): ?>
                                                <a class="text-decoration-none" href="<?php echo esc_url($this->get_base_url(['orderby' => $key, 'order' => ($this->sort_column === $key && strtoupper($this->sort_order) === 'ASC') ? 'DESC' : 'ASC'])); ?>">
                                                    <?php echo esc_html($label); ?>
                                                </a>
                                            <?php else: ?>
                                                <?php echo esc_html($label); ?>
                                            <?php endif; ?>
                                        </th>
                                    <?php endforeach; ?>
                                    <?php if (!empty($this->actions)): ?>
                                        <th class="text-center">Thao tác</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="ids[]" value="<?php echo esc_attr($item[$this->primary_key]); ?>" class="form-check-input border-dark-subtle">
                                        </td>
                                        <?php foreach ($this->columns as $key => $label): ?>
                                            <td><?php echo esc_html($item[$key]); ?></td>
                                        <?php endforeach; ?>
                                        <?php if (!empty($this->actions)): ?>
                                            <td class="text-center">
                                                <?php $this->render_row_actions($item); ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            <?php else: // Nếu không có bulk actions, vẫn hiển thị bảng 
            ?>
                <!-- Table (without bulk action form) -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <?php foreach ($this->columns as $key => $label): ?>
                                    <th>
                                        <?php if (in_array($key, $this->sortable_columns)): ?>
                                            <a class="text-decoration-none" href="<?php echo esc_url($this->get_base_url(['orderby' => $key, 'order' => ($this->sort_column === $key && strtoupper($this->sort_order) === 'ASC') ? 'DESC' : 'ASC'])); ?>">
                                                <?php echo esc_html($label); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo esc_html($label); ?>
                                        <?php endif; ?>
                                    </th>
                                <?php endforeach; ?>
                                <?php if (!empty($this->actions)): ?>
                                    <th>Thao tác</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <?php foreach ($this->columns as $key => $label): ?>
                                        <td><?php echo esc_html($item[$key]); ?></td>
                                    <?php endforeach; ?>
                                    <?php if (!empty($this->actions)): ?>
                                        <td>
                                            <?php $this->render_row_actions($item); ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php $this->render_pagination(); ?>
        </div>
<?php
    }

    protected function render_row_actions($item)
    {
        $actions = [];

        if (in_array('edit', $this->actions)) {
            $actions['edit'] = sprintf(
                '<a href="%s" class="btn btn-sm btn-success"><i class="fas fa-edit"></i></a>',
                esc_url($this->get_base_url(['action' => 'edit', 'id' => $item[$this->primary_key]]))
            );
        }

        if (in_array('delete', $this->actions)) {
            $actions['delete'] = sprintf(
                '<a href="%s" class="btn btn-sm btn-danger" onclick="return confirm(\'Bạn có chắc muốn xóa?\')"><i class="fas fa-trash"></i></a>',
                esc_url($this->get_base_url(['action' => 'delete', 'id' => $item[$this->primary_key], '_wpnonce' => wp_create_nonce($this->nonce_action_prefix . $item[$this->primary_key])]))
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

    protected function render_pagination()
    {
        $total_items = $this->total_items;
        $total_pages = $this->get_total_pages();
        $current_page = $this->current_page;

        if ($total_pages <= 1 && $total_items === 0) {
            return;
        }

        echo '<div class="tablenav-pages d-flex align-items-center justify-content-end">';

        // Display total items
        if ($total_items > 0) {
            echo '<span class="displaying-num">' . sprintf(_n('%s mục', '%s mục', $total_items, 'aerp-hrm'), number_format_i18n($total_items)) . '</span>';
        }

        echo '<span class="pagination-links aerp-pagination">';

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
        if (!isset($_POST['bulk_action']) || empty($_POST['ids'])) {
            error_log('AERP_Frontend_Table: Bulk action - No action or no IDs. Exiting.');
            return;
        }

        if (!wp_verify_nonce($_POST['aerp_bulk_nonce'], $this->bulk_action_nonce_key)) {
            error_log('AERP_Frontend_Table: Bulk action - Nonce verification failed.');
            wp_die('Invalid nonce for bulk action.');
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $ids = array_map('intval', $_POST['ids']);

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
}
