<?php
if (!defined('ABSPATH')) exit;
if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}
// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

if (!is_user_logged_in()) {
    wp_die(__('You must be logged in to access this page.'));
}

// Danh sách điều kiện, chỉ cần 1 cái đúng là qua
$access_conditions = [
    aerp_user_has_role($user_id, 'admin'),
    aerp_user_has_role($user_id, 'department_lead'),
    aerp_user_has_permission($user_id,'salary_view'),
];
if (!in_array(true, $access_conditions, true)) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Hàm kiểm tra user có role bất kỳ trong mảng không
if (!function_exists('aerp_user_has_any_role')) {
    function aerp_user_has_any_role($user_id, $roles = []) {
        foreach ($roles as $role) {
            if (aerp_user_has_role($user_id, $role)) return true;
        }
        return false;
    }
}
// Định nghĩa tất cả menu HRM
$all_hrm_menu = [
    [
        'icon' => 'fa-building',
        'title' => 'Thông tin công ty',
        'desc' => 'Quản lý thông tin doanh nghiệp và các thông tin cơ bản',
        'url' => home_url('/aerp-company'),
        'color' => 'primary',
        'show_for' => ['admin'],
    ],
    [
        'icon' => 'fa-map-marker-alt',
        'title' => 'Chi nhánh',
        'desc' => 'Quản lý các chi nhánh và vị trí làm việc trong công ty',
        'url' => home_url('/aerp-work-location'),
        'color' => 'info',
        'show_for' => ['admin'],
    ],
    [
        'icon' => 'fa-sitemap',
        'title' => 'Phòng ban',
        'desc' => 'Thiết lập cơ cấu tổ chức và quản lý các phòng ban trong công ty',
        'url' => home_url('/aerp-departments'),
        'color' => 'success',
        'show_for' => ['admin'],
    ],
    [
        'icon' => 'fa-user-tie',
        'title' => 'Chức vụ',
        'desc' => 'Quản lý các vị trí, chức vụ và cấp bậc trong tổ chức',
        'url' => home_url('/aerp-position'),
        'color' => 'secondary',
        'show_for' => ['admin', 'department_lead'],
    ],
    [
        'icon' => 'fa-exclamation-circle',
        'title' => 'Quản lý vi phạm',
        'desc' => 'Thiết lập các quy định, mức phạt và quản lý vi phạm',
        'url' => home_url('/aerp-discipline-rule'),
        'color' => 'danger',
        'show_for' => ['admin', 'department_lead'],
    ],
    [
        'icon' => 'fa-star',
        'title' => 'Xếp loại nhân sự',
        'desc' => 'Thiết lập tiêu chí và quản lý xếp loại đánh giá nhân viên',
        'url' => home_url('/aerp-ranking-settings'),
        'color' => 'warning',
        'show_for' => ['admin', 'department_lead'],
    ],
    [
        'icon' => 'fa-gift',
        'title' => 'Thưởng tự động',
        'desc' => 'Cấu hình các quy định thưởng và mức thưởng tự động',
        'url' => home_url('/aerp-reward-settings'),
        'color' => 'success',
        'show_for' => ['admin', 'department_lead'],
    ],
    [
        'icon' => 'fa-chart-line',
        'title' => 'KPI Bonus Settings',
        'desc' => 'Thiết lập các chỉ tiêu KPI và mức thưởng theo hiệu suất',
        'url' => home_url('/aerp-kpi-settings'),
        'color' => 'info',
        'show_for' => ['admin', 'department_lead'],
    ],
    [
        'icon' => 'fa-money-bill-wave',
        'title' => 'Lương tổng hợp',
        'desc' => 'Tổng hợp lương của tất cả nhân viên',
        'url' => home_url('/aerp-salary-summary'),
        'color' => 'primary',
        'permission' => 'salary_view',
    ],
    [
        'icon' => 'fa-users-cog',
        'title' => 'Nhóm quyền',
        'desc' => 'Quản lý các nhóm quyền và quyền hạn của nhân viên',
        'url' => home_url('/aerp-role'),
        'color' => 'primary',
        'show_for' => ['admin'],
    ],
    [
        'icon' => 'fa-user-shield',
        'title' => 'Quyền',
        'desc' => 'Quản lý các quyền và quyền hạn của nhân viên',
        'url' => home_url('/aerp-permission'),
        'color' => 'secondary',
        'show_for' => ['admin'],
    ],
];

// Lọc menu theo role/permission
$management_hrm_menu = array_filter($all_hrm_menu, function($item) use ($user_id) {
    // Nếu có trường 'permission' thì phải có permission đó
    if (isset($item['permission']) && !aerp_user_has_permission($user_id, $item['permission'])) {
        return false;
    }
    // Nếu có trường 'show_for' thì phải có ít nhất 1 role trong đó
    if (isset($item['show_for']) && !aerp_user_has_any_role($user_id, $item['show_for'])) {
        return false;
    }
    return true;
});
$management_hrm_menu = array_values($management_hrm_menu); // reset key

$order_active = function_exists('aerp_order_init') || is_plugin_active('aerp-order/aerp-order.php');

// Định nghĩa tất cả menu Order
$all_order_menu = [
    [
        'icon' => 'fa-box',
        'title' => 'Sản phẩm kho',
        'desc' => 'Quản lý sản phẩm kho',
        'url' => home_url('/aerp-products'),
        'color' => 'primary',
        'show_for' => ['admin', 'department_lead'],
    ],
    [
        'icon' => 'fa-weight-scale',
        'title' => 'Đơn vị tính sản phẩm',
        'desc' => 'Quản lý đơn vị tính',
        'url' => home_url('/aerp-units'),
        'color' => 'info',
        'show_for' => ['admin', 'department_lead'],
    ],
    [
        'icon' => 'fa-tags',
        'title' => 'Danh mục sản phẩm',
        'desc' => 'Quản lý danh mục sản phẩm',
        'url' => home_url('/aerp-product-categories'),
        'color' => 'warning',
        'show_for' => ['admin', 'department_lead'],
    ],
    // [
    //     'icon' => 'fa-warehouse',
    //     'title' => 'Kho',
    //     'desc' => 'Quản lý kho',
    //     'url' => home_url('/aerp-warehouses'),
    //     'color' => 'success',
    //     'show_for' => ['admin', 'department_lead'],
    // ],
    [
        'icon' => 'fa-history',
        'title' => 'Ghi nhận nhập/ xuất kho',
        'desc' => 'Quản lý lịch sử nhập/xuất kho',
        'url' => home_url('/aerp-inventory-logs'),
        'color' => 'warning',
        'show_for' => ['admin', 'department_lead'],
    ],
    [
        'icon' => 'fa-users',
        'title' => 'Nhà cung cấp',
        'desc' => 'Quản lý nhà cung cấp',
        'url' => home_url('/aerp-suppliers'),
        'color' => 'danger',
        'show_for' => ['admin', 'department_lead'],
    ],
];

$management_order_menu = [];
if ($order_active) {
    $management_order_menu = array_filter($all_order_menu, function($item) use ($user_id) {
        if (isset($item['permission']) && !aerp_user_has_permission($user_id, $item['permission'])) {
            return false;
        }
        if (isset($item['show_for']) && !aerp_user_has_any_role($user_id, $item['show_for'])) {
            return false;
        }
        return true;
    });
    $management_order_menu = array_values($management_order_menu);
}

ob_start();
?>
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-th-large me-2"></i> Danh mục quản lý</h5>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <h5 class="mb-0">1. Danh mục plugin HRM</h5>
            <?php foreach ($management_hrm_menu  as $item): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card category-card h-100">
                        <div class="card-body text-center d-flex flex-column">
                            <i class="fas <?php echo $item['icon']; ?> category-icon text-<?php echo $item['color']; ?>"></i>
                            <h6 class="text-uppercase mt-2"><?php echo $item['title']; ?></h6>
                            <p class="fs-6 text-muted flex-grow-1"><?php echo $item['desc']; ?></p>
                            <a href="<?php echo esc_url($item['url']); ?>" class="btn btn-sm btn-outline-<?php echo $item['color']; ?>">Quản lý</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($management_order_menu)) : ?>
            <div class="row g-3">
                <h5 class="mb-0">2. Danh mục plugin Order</h5>
                <?php foreach ($management_order_menu  as $item): ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card category-card h-100">
                            <div class="card-body text-center d-flex flex-column">
                                <i class="fas <?php echo $item['icon']; ?> category-icon text-<?php echo $item['color']; ?>"></i>
                                <h6 class="text-uppercase mt-2"><?php echo $item['title']; ?></h6>
                                <p class="fs-6 text-muted flex-grow-1"><?php echo $item['desc']; ?></p>
                                <a href="<?php echo esc_url($item['url']); ?>" class="btn btn-sm btn-outline-<?php echo $item['color']; ?>">Quản lý</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Danh mục quản lý';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
