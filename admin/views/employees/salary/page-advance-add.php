<?php
if (!defined('ABSPATH')) exit;

require_once AERP_HRM_PATH . 'includes/table/table-advance.php';

global $wpdb;
$employees = $wpdb->get_results("SELECT id, full_name FROM {$wpdb->prefix}aerp_hrm_employees WHERE status = 'active' ORDER BY full_name ASC");
$today = date('Y-m-d');
$selected_id = absint($_GET['employee_id']);

// Xử lý xóa
if (isset($_GET['delete']) && $selected_id && current_user_can('manage_options')) {
    $delete_id = absint($_GET['delete']);
    $wpdb->delete($wpdb->prefix . 'aerp_hrm_advance_salaries', ['id' => $delete_id, 'employee_id' => $selected_id]);
    aerp_js_redirect(admin_url('admin.php?page=aerp_advance_add&employee_id=' . $selected_id));
}

// Xử lý lấy dữ liệu để sửa
$edit_data = null;
if (isset($_GET['edit']) && $selected_id && current_user_can('manage_options')) {
    $edit_id = absint($_GET['edit']);
    $edit_data = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}aerp_hrm_advance_salaries WHERE id = %d AND employee_id = %d",
        $edit_id,
        $selected_id
    ));
}
// Lấy tên nhân viên
$edit_employee_name = '';
if ($edit_data) {
    $edit_employee = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT full_name FROM {$wpdb->prefix}aerp_hrm_employees WHERE id = %d",
            $edit_data->employee_id
        )
    );
    if ($edit_employee) {
        $edit_employee_name = $edit_employee->full_name;
    }
}
// Xử lý lưu sửa
if (isset($_POST['aerp_edit_advance']) && $selected_id && current_user_can('manage_options')) {
    $edit_id = absint($_POST['edit_id']);
    $amount = floatval($_POST['amount']);
    $advance_date = sanitize_text_field($_POST['advance_date']);
    $wpdb->update(
        $wpdb->prefix . 'aerp_hrm_advance_salaries',
        ['amount' => $amount, 'advance_date' => $advance_date],
        ['id' => $edit_id, 'employee_id' => $selected_id]
    );
    aerp_js_redirect(admin_url('admin.php?page=aerp_advance_add&employee_id=' . $selected_id));
}

$table = new AERP_Advance_Table($selected_id);
$table->process_bulk_action();
$table->prepare_items();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Tạm ứng lương</h1>
    <a href="<?= admin_url('admin.php?page=aerp_employees&view=' . $selected_id . '#salary') ?>" class="page-title-action">← Quay lại bảng</a>

    <p>
        <a href="<?= admin_url('admin.php?page=aerp_advance_add&employee_id=' . $selected_id . '&addnew=1') ?>" class="button button-primary">Thêm mới tạm ứng</a>
    </p>
    <?php if (isset($_GET['addnew'])): ?>
        <h2>Thêm tạm ứng lương</h2>
        <form method="post" style="max-width: 500px; margin-top: 20px;">
            <?php wp_nonce_field('aerp_add_advance_action', 'aerp_add_advance_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="employee_id">Nhân viên</label></th>
                    <td>
                        <select name="employee_id" required>
                            <option value="">-- Chọn nhân viên --</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= esc_attr($emp->id) ?>" <?= selected($emp->id, $selected_id) ?>>
                                    <?= esc_html($emp->full_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="amount">Số tiền (VND)</label></th>
                    <td><input type="number" name="amount" required></td>
                </tr>
                <tr>
                    <th><label for="advance_date">Ngày ứng</label></th>
                    <td><input type="date" name="advance_date" value="<?= esc_attr($today) ?>" required></td>
                </tr>
            </table>
            <p><button type="submit" name="aerp_add_advance" class="button button-primary">Lưu</button>
                <a href="<?= admin_url('admin.php?page=aerp_advance_add&employee_id=' . $selected_id) ?>" class="button">Hủy</a>
            </p>
        </form>
    <?php endif; ?>
    <?php if ($edit_data): ?>
        <h2>Sửa tạm ứng lương</h2>
        <form method="post" style="max-width: 500px; margin-top: 20px;">
            <?php wp_nonce_field('aerp_add_advance_action', 'aerp_add_advance_nonce'); ?>
            <input type="hidden" name="edit_id" value="<?= esc_attr($edit_data->id) ?>">
            <table class="form-table">
                <tr>
                    <th>Nhân viên</th>
                    <td>
                        <input type="text" value="<?= esc_html($edit_employee_name) ?>" disabled>
                    </td>
                </tr>
                <tr>
                    <th>Số tiền (VND)</th>
                    <td><input type="number" name="amount" value="<?= esc_attr($edit_data->amount) ?>" required></td>
                </tr>
                <tr>
                    <th>Ngày ứng</th>
                    <td><input type="date" name="advance_date" value="<?= esc_attr($edit_data->advance_date) ?>" required></td>
                </tr>
            </table>
            <p><button type="submit" name="aerp_edit_advance" class="button button-primary">Cập nhật</button>
                <a href="<?= admin_url('admin.php?page=aerp_advance_add&employee_id=' . $selected_id) ?>" class="button">Hủy</a>
            </p>
        </form>
    <?php endif; ?>
    <?php if (empty($edit_data) && !isset($_GET['addnew'])): ?>
        <form method="get">
            <input type="hidden" name="page" value="aerp_advance_add">
            <input type="hidden" name="employee_id" value="<?= esc_attr($selected_id) ?>">
            <?php $table->search_box('Tìm kiếm', 'search_advance'); ?>
        </form>
        <form method="post">
            <?php $table->display(); ?>
        </form>
    <?php endif; ?>
</div>