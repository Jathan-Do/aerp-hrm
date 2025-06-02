<?php
if (!defined('ABSPATH')) exit;
if (!current_user_can('manage_options')) return;

global $wpdb;

// ⚠️ Đảm bảo $employee_id được truyền từ file cha
$employee_id = absint($_GET['employee_id'] ?? 0);
if (!$employee_id) return;

$success = false;

// Xử lý lưu lương mới
if (isset($_POST['aerp_save_salary_config']) && check_admin_referer('aerp_salary_config_action', 'aerp_salary_nonce')) {
    $start_date = sanitize_text_field($_POST['start_date']);
    $end_date = sanitize_text_field($_POST['end_date']);
    $base_salary = floatval($_POST['base_salary']);
    $allowance = floatval($_POST['allowance']);

    $wpdb->insert("{$wpdb->prefix}aerp_hrm_salary_config", [
        'employee_id'  => $employee_id,
        'start_date'   => $start_date,
        'end_date'     => $end_date,
        'base_salary'  => $base_salary,
        'allowance'    => $allowance,
        'created_at' => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
    ]);
    // Lấy lương cũ gần nhất (nếu có)
    $last_config = $wpdb->get_row($wpdb->prepare("
        SELECT base_salary FROM {$wpdb->prefix}aerp_hrm_salary_config
        WHERE employee_id = %d
        ORDER BY start_date DESC
        LIMIT 1 OFFSET 1
    ", $employee_id));

    // Ghi nhận hành trình nếu có thay đổi
    if ($last_config && floatval($last_config->base_salary) != $base_salary) {
        $journey = new AERP_HRM_Employee_Journey();
        $journey->add_event(
            $employee_id,
            'salary_change',
            floatval($last_config->base_salary),
            $base_salary,
            'Thay đổi lương cơ bản'
        );
    }

    $success = true;
}

$edit_data = null;
if (isset($_GET['edit']) && $employee_id && current_user_can('manage_options')) {
    $edit_id = absint($_GET['edit']);
    $edit_data = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}aerp_hrm_salary_config WHERE id = %d AND employee_id = %d",
        $edit_id,
        $employee_id
    ));
}
// Xử lý xóa
if (isset($_GET['delete']) && $employee_id && current_user_can('manage_options') && isset($_GET['_wpnonce'])) {
    $delete_id = absint($_GET['delete']);
    if (wp_verify_nonce($_GET['_wpnonce'], 'aerp_salary_delete_' . $delete_id)) {
        $wpdb->delete($wpdb->prefix . 'aerp_hrm_salary_config', ['id' => $delete_id, 'employee_id' => $employee_id]);
        aerp_js_redirect(admin_url('admin.php?page=aerp_salary_add&employee_id=' . $employee_id));
    }
}
// Xử lý lưu sửa
if (isset($_POST['aerp_edit_salary_config']) && $employee_id && current_user_can('manage_options')) {
    $edit_id = absint($_POST['edit_id']);
    $start_date = sanitize_text_field($_POST['start_date']);
    $end_date = sanitize_text_field($_POST['end_date']);
    $base_salary = floatval($_POST['base_salary']);
    $allowance = floatval($_POST['allowance']);
    $wpdb->update(
        $wpdb->prefix . 'aerp_hrm_salary_config',
        [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'base_salary' => $base_salary,
            'allowance' => $allowance
        ],
        ['id' => $edit_id, 'employee_id' => $employee_id]
    );
    aerp_js_redirect(admin_url('admin.php?page=aerp_salary_add&employee_id=' . $employee_id));
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Cấu hình lương cho nhân viên</h1>
    <a href="<?= admin_url('admin.php?page=aerp_employees&view=' . $employee_id . '#salary') ?>" class="page-title-action">← Quay lại nhân viên</a>
    <?php if ($success): ?>
        <div class="notice notice-success">
            <p>✅ Đã lưu cấu hình lương.</p>
        </div>
    <?php endif; ?>
    <p> 
        <a href="<?= admin_url('admin.php?page=aerp_salary_add&employee_id=' . $employee_id . '&addnew=1') ?>" class="page-title-action button button-primary">Thêm mới cấu hình lương</a>
    </p>

    <?php if ($edit_data): ?>
        <h2>Sửa cấu hình lương</h2>
        <form method="post">
            <?php wp_nonce_field('aerp_salary_config_action', 'aerp_salary_nonce'); ?>
            <input type="hidden" name="edit_id" value="<?= esc_attr($edit_data->id) ?>">
            <table class="form-table">
                <tr>
                    <th><label for="start_date">Từ ngày</label></th>
                    <td><input type="date" name="start_date" id="start_date" value="<?= esc_attr($edit_data->start_date) ?>" required></td>
                </tr>
                <tr>
                    <th><label for="end_date">Đến ngày</label></th>
                    <td><input type="date" name="end_date" id="end_date" value="<?= esc_attr($edit_data->end_date) ?>" required></td>
                </tr>
                <tr>
                    <th><label for="base_salary">Lương cơ bản</label></th>
                    <td><input type="number" name="base_salary" step="1000" value="<?= esc_attr($edit_data->base_salary) ?>" required></td>
                </tr>
                <tr>
                    <th><label for="allowance">Phụ cấp</label></th>
                    <td><input type="number" name="allowance" step="1000" value="<?= esc_attr($edit_data->allowance) ?>" required></td>
                </tr>
            </table>
            <p><button type="submit" name="aerp_edit_salary_config" class="button button-primary">Cập nhật</button>
                <a href="<?= admin_url('admin.php?page=aerp_salary_add&employee_id=' . $employee_id) ?>" class="button">Hủy</a>
            </p>
        </form>
        <hr>
    <?php endif; ?>

    <?php if (isset($_GET['addnew'])): ?>
        <h2>Thêm cấu hình lương</h2>
        <form method="post">
            <?php wp_nonce_field('aerp_salary_config_action', 'aerp_salary_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="start_date">Từ ngày</label></th>
                    <td><input type="date" name="start_date" id="start_date" required></td>
                </tr>
                <tr>
                    <th><label for="end_date">Đến ngày</label></th>
                    <td><input type="date" name="end_date" id="end_date" required></td>
                </tr>
                <tr>
                    <th><label for="base_salary">Lương cơ bản</label></th>
                    <td><input type="number" name="base_salary" step="1000" required></td>
                </tr>
                <tr>
                    <th><label for="allowance">Phụ cấp</label></th>
                    <td><input type="number" name="allowance" step="1000" required value="0"></td>
                </tr>
            </table>

            <p><input type="submit" name="aerp_save_salary_config" class="button button-primary" value="Thêm cấu hình"></p>
        </form>
    <?php endif; ?>

    <hr>

    <?php
    // ⬇️ CHÈN SAU FORM LƯU CẤU HÌNH LƯƠNG
    require_once AERP_HRM_PATH . 'includes/table/table-salary-config.php';
    $salary_config_table = new AERP_Salary_Config_Table($employee_id);
    $salary_config_table->process_bulk_action();
    $salary_config_table->prepare_items();
    ?>

    <?php if (empty($edit_data) && !isset($_GET['addnew'])): ?>
        <h2>Lịch sử cấu hình lương</h2>
        <form method="get">
            <input type="hidden" name="page" value="aerp_salary_add">
            <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">
            <?php $salary_config_table->search_box('Tìm kiếm', 'search_salary'); ?>
        </form>
        <form method="post">
            <?php $salary_config_table->display(); ?>

        </form>
    <?php endif; ?>
</div>