<?php
if (!defined('ABSPATH')) exit;
if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}
// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Check if user is logged in and has admin capabilities
if (!is_user_logged_in() || !aerp_user_has_role($user_id, 'admin')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
$order_active = function_exists('aerp_order_init') || is_plugin_active('aerp-order/aerp-order.php');
$current_user = wp_get_current_user();
$management_hrm_menu  = [
    [
        'icon' => 'fa-building',
        'title' => 'Thông tin công ty',
        'desc' => 'Quản lý thông tin doanh nghiệp và các thông tin cơ bản',
        'url' => home_url('/aerp-company'),
        'color' => 'primary',
    ],
    [
        'icon' => 'fa-map-marker-alt',
        'title' => 'Chi nhánh',
        'desc' => 'Quản lý các chi nhánh và vị trí làm việc trong công ty',
        'url' => home_url('/aerp-work-location'),
        'color' => 'info',
    ],
    [
        'icon' => 'fa-sitemap',
        'title' => 'Phòng ban',
        'desc' => 'Thiết lập cơ cấu tổ chức và quản lý các phòng ban trong công ty',
        'url' => home_url('/aerp-departments'),
        'color' => 'success',
    ],
    [
        'icon' => 'fa-user-tie',
        'title' => 'Chức vụ',
        'desc' => 'Quản lý các vị trí, chức vụ và cấp bậc trong tổ chức',
        'url' => home_url('/aerp-position'),
        'color' => 'secondary',
    ],
    [
        'icon' => 'fa-exclamation-circle',
        'title' => 'Quản lý vi phạm',
        'desc' => 'Thiết lập các quy định, mức phạt và quản lý vi phạm',
        'url' => home_url('/aerp-discipline-rule'),
        'color' => 'danger',
    ],
    [
        'icon' => 'fa-star',
        'title' => 'Xếp loại nhân sự',
        'desc' => 'Thiết lập tiêu chí và quản lý xếp loại đánh giá nhân viên',
        'url' => home_url('/aerp-ranking-settings'),
        'color' => 'warning',
    ],
    [
        'icon' => 'fa-gift',
        'title' => 'Thưởng tự động',
        'desc' => 'Cấu hình các quy định thưởng và mức thưởng tự động',
        'url' => home_url('/aerp-reward-settings'),
        'color' => 'success',
    ],
    [
        'icon' => 'fa-chart-line',
        'title' => 'KPI Bonus Settings',
        'desc' => 'Thiết lập các chỉ tiêu KPI và mức thưởng theo hiệu suất',
        'url' => home_url('/aerp-kpi-settings'),
        'color' => 'info',
    ],
    [
        'icon' => 'fa-money-bill-wave',
        'title' => 'Lương tổng hợp',
        'desc' => 'Tổng hợp lương của tất cả nhân viên',
        'url' => home_url('/aerp-salary-summary'),
        'color' => 'primary',
    ],
    [
        'icon' => 'fa-users-cog',
        'title' => 'Nhóm quyền',
        'desc' => 'Quản lý các nhóm quyền và quyền hạn của nhân viên',
        'url' => home_url('/aerp-role'),
        'color' => 'primary',
    ],
    [
        'icon' => 'fa-user-shield',
        'title' => 'Quyền',
        'desc' => 'Quản lý các quyền và quyền hạn của nhân viên',
        'url' => home_url('/aerp-permission'),
        'color' => 'secondary',
    ],
];
$management_order_menu = [];
if ($order_active) {
    $management_order_menu  = [
        [
            'icon' => 'fa-box',
            'title' => 'Sản phẩm kho',
            'desc' => 'Quản lý sản phẩm kho',
            'url' => home_url('/aerp-products'),
            'color' => 'primary',
        ],
        [
            'icon' => 'fa-weight-scale',
            'title' => 'Đơn vị tính sản phẩm',
            'desc' => 'Quản lý đơn vị tính',
            'url' => home_url('/aerp-units'),
            'color' => 'info',
        ],
        [
            'icon' => 'fa-tags',
            'title' => 'Danh mục sản phẩm',
            'desc' => 'Quản lý danh mục sản phẩm',
            'url' => home_url('/aerp-product-categories'),
            'color' => 'warning',
        ],
        [
            'icon' => 'fa-warehouse',
            'title' => 'Kho',
            'desc' => 'Quản lý kho',
            'url' => home_url('/aerp-warehouses'),
            'color' => 'success',
        ],
        [
            'icon' => 'fa-history',
            'title' => 'Ghi nhận nhập/ xuất kho',
            'desc' => 'Quản lý lịch sử nhập/xuất kho',
            'url' => home_url('/aerp-inventory-logs'),
            'color' => 'warning',
        ],

    ];
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
