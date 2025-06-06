<?php
if (!defined('ABSPATH')) exit;

// Thêm CSS inline cho giao diện
wp_add_inline_style('aerp-admin-employee', '
    .wrap { max-width: 1600px; }
    .aerp-category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        margin: 16px 0;
    }
    .aerp-category-card {
        position: relative;
        background: #fff;
        border: 1px solid #e2e4e7;
        border-radius: 4px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        min-height: 140px;
        transition: all 0.2s ease;
    }
    .aerp-category-card:hover {
        border-color: #2271b1;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .aerp-category-card h3 {
        font-size: 14px;
        margin: 0 0 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #1d2327;
        font-weight: 600;
    }
    .aerp-category-card .dashicons {
        color: #2271b1;
        font-size: 18px;
        width: 18px;
        height: 18px;
    }
    .aerp-category-card p {
        color: #50575e;
        margin: 0 0 16px;
        font-size: 13px;
        line-height: 1.4;
        flex-grow: 1;
    }
    .aerp-category-card .button {
        width: 100%;
        text-align: center;
        padding: 4px 12px;
        height: auto;
        font-size: 13px;
        font-weight: 500;
    }
    .aerp-category-card .button-primary {
        background: #2271b1;
        border-color: #2271b1;
    }
    .aerp-category-card .button-primary:hover {
        background: #135e96;
        border-color: #135e96;
    }
    @media screen and (min-width: 1200px) {
        .aerp-category-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
');
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Danh mục quản lý</h1>
    <hr class="wp-header-end">

    <div class="aerp-category-grid">
        <div class="aerp-category-card">
            <h3><span class="dashicons dashicons-building"></span> Thông tin công ty</h3>
            <p>Quản lý thông tin doanh nghiệp, chi nhánh và các thông tin cơ bản</p>
            <a href="<?php echo admin_url('admin.php?page=aerp_companies'); ?>" class="button button-primary">Quản lý</a>
        </div>
        <div class="aerp-category-card">
            <h3><span class="dashicons dashicons-building"></span> Chi nhánh</h3>
            <p>Quản lý các chi nhánh và vị trí làm việc trong công ty</p>
            <a href="<?php echo admin_url('admin.php?page=aerp_work_locations'); ?>" class="button button-primary">Quản lý</a>
        </div>

        <div class="aerp-category-card">
            <h3><span class="dashicons dashicons-groups"></span> Phòng ban</h3>
            <p>Thiết lập cơ cấu tổ chức và quản lý các phòng ban trong công ty</p>
            <a href="<?php echo admin_url('admin.php?page=aerp_departments'); ?>" class="button button-primary">Quản lý</a>
        </div>

        <div class="aerp-category-card">
            <h3><span class="dashicons dashicons-businessman"></span> Chức vụ</h3>
            <p>Quản lý các vị trí, chức vụ và cấp bậc trong tổ chức</p>
            <a href="<?php echo admin_url('admin.php?page=aerp_positions'); ?>" class="button button-primary">Quản lý</a>
        </div>

        <div class="aerp-category-card">
            <h3><span class="dashicons dashicons-warning"></span> Quản lý vi phạm</h3>
            <p>Thiết lập các quy định, mức phạt và quản lý vi phạm</p>
            <a href="<?php echo admin_url('admin.php?page=aerp_discipline'); ?>" class="button button-primary">Quản lý</a>
        </div>

        <div class="aerp-category-card">
            <h3><span class="dashicons dashicons-awards"></span> Xếp loại nhân sự</h3>
            <p>Thiết lập tiêu chí và quản lý xếp loại đánh giá nhân viên</p>
            <a href="<?php echo admin_url('admin.php?page=aerp_ranking_settings'); ?>" class="button button-primary">Quản lý</a>
        </div>

        <div class="aerp-category-card">
            <h3><span class="dashicons dashicons-money-alt"></span> Thưởng tự động</h3>
            <p>Cấu hình các quy định thưởng và mức thưởng tự động</p>
            <a href="<?php echo admin_url('admin.php?page=aerp_reward_settings'); ?>" class="button button-primary">Quản lý</a>
        </div>

        <div class="aerp-category-card">
            <h3><span class="dashicons dashicons-chart-area"></span> KPI Bonus Settings</h3>
            <p>Thiết lập các chỉ tiêu KPI và mức thưởng theo hiệu suất</p>
            <a href="<?php echo admin_url('admin.php?page=aerp_kpi_settings'); ?>" class="button button-primary">Quản lý</a>
        </div>

        <div class="aerp-category-card">
            <h3><span class="dashicons dashicons-money"></span> Lương tổng hợp</h3>
            <p>Tổng hợp lương của tất cả nhân viên</p>
            <a href="<?php echo admin_url('admin.php?page=aerp_salary_summary'); ?>" class="button button-primary">Quản lý</a>
        </div>
        <div class="aerp-category-card">
            <h3><span class="dashicons dashicons-groups"></span> Nhóm quyền</h3>
            <p>Quản lý các nhóm quyền và quyền hạn của nhân viên</p>
            <a href="<?php echo admin_url('admin.php?page=aerp_roles'); ?>" class="button button-primary">Quản lý</a>
        </div>
        <div class="aerp-category-card">
            <h3><span class="dashicons dashicons-groups"></span> Quyền</h3>
            <p>Quản lý các quyền và quyền hạn của nhân viên</p>
            <a href="<?php echo admin_url('admin.php?page=aerp_permissions'); ?>" class="button button-primary">Quản lý</a>
        </div>
    </div>
</div>