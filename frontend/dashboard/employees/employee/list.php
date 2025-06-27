<?php
// Lấy user hiện tại (nếu cần)
$current_user = wp_get_current_user();

// Khởi tạo table
$table = new AERP_Frontend_Employee_Table();
$table->process_bulk_action(); // Xử lý bulk nếu có

ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Quản lý nhân viên</h2>
    <div class="user-info text-end">
        Xin chào, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <h5 class="mb-0">Danh sách nhân viên</h5>
        <div class="d-flex gap-2 flex-column flex-md-row">
            <a href="<?php echo esc_url(home_url('/aerp-hrm-employees/?action=add')); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm mới nhân viên
            </a>
        </div>
    </div>
    <div class="card-body">

        <!-- Filter Form -->
        <form id="aerp-employee-filter-form" class="g-2 mb-3 aerp-table-ajax-form" data-table-wrapper="#aerp-employee-table-wrapper" data-ajax-action="aerp_hrm_filter_employees">
            <div class="row">
                <div class="col-12 col-md-2 mb-2">
                    <label for="filter-department" class="form-label mb-1">Phòng ban</label>
                    <select id="filter-department" name="department_id" class="form-select">
                        <?php
                        $departments = apply_filters('aerp_get_departments', []);
                        aerp_safe_select_options($departments, '', 'id', 'name', true);
                        ?>
                    </select>
                </div>
                <div class="col-12 col-md-2 mb-2">
                    <label for="filter-position" class="form-label mb-1">Chức vụ</label>
                    <select id="filter-position" name="position_id" class="form-select">
                        <?php
                        $positions = apply_filters('aerp_get_positions', []);
                        aerp_safe_select_options($positions, '', 'id', 'name', true);
                        ?>
                    </select>
                </div>
                <div class="col-12 col-md-2 mb-2">
                    <label for="filter-work-location" class="form-label mb-1">Chi nhánh</label>
                    <select id="filter-work-location" name="work_location_id" class="form-select">
                        <?php
                        $work_locations = apply_filters('aerp_get_work_locations', []);
                        aerp_safe_select_options($work_locations, '', 'id', 'name', true);
                        ?>
                    </select>
                </div>
                <div class="col-12 col-md-2 mb-2">
                    <label for="filter-birthday-month" class="form-label mb-1">Sinh nhật</label>
                    <select id="filter-birthday-month" name="birthday_month" class="form-select">
                        <option value="">Tất cả</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>">Tháng <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <label class="form-label mb-1">Ngày vào làm</label>
                    <div class="d-flex gap-1">
                        <input type="date" name="join_date_from" class="form-control" placeholder="Từ">
                        <input type="date" name="join_date_to" class="form-control" placeholder="Đến">
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <label class="form-label mb-1">Ngày nghỉ</label>
                    <div class="d-flex gap-1">
                        <input type="date" name="off_date_from" class="form-control" placeholder="Từ">
                        <input type="date" name="off_date_to" class="form-control" placeholder="Đến">
                    </div>
                </div>
                <div class="col-12 col-md-2 mb-2">
                    <label for="filter-status" class="form-label mb-1">Trạng thái</label>
                    <select id="filter-status" name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="active">Đang làm</option>
                        <option value="inactive">Tạm nghỉ</option>
                        <option value="resigned">Đã nghỉ</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 d-flex align-items-end mb-2">
                    <button type="submit" class="btn btn-primary w-100">Lọc</button>
                </div>
            </div>

        </form>
        <?php
        // Hiển thị thông báo nếu có
        $message = get_transient('aerp_employee_message');
        if ($message) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    ' . esc_html($message) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            delete_transient('aerp_employee_message');
        }
        ?>
        <div id="aerp-employee-table-wrapper">
            <?php $table->render(); ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Quản lý nhân viên';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
