<?php
if (!defined('ABSPATH')) exit;

require_once AERP_HRM_PATH . 'includes/class-attendance-manager.php';

$employee_id = absint($_GET['employee_id'] ?? 0);
$id = absint($_GET['id'] ?? 0);
$attendance = AERP_Attendance_Manager::get_by_id($id);

if (!$employee_id || !$attendance) {
    echo '<div class="notice notice-error"><p>Thiếu dữ liệu hoặc không tìm thấy dòng chấm công.</p></div>';
    return;
}

// Danh sách ca mặc định
$default_shifts = [
    'full'      => ['label' => 'Cả ngày', 'ratio' => 1.0],
    'morning'   => ['label' => 'Ca sáng', 'ratio' => 1.0],
    'afternoon' => ['label' => 'Ca chiều', 'ratio' => 1.0],
    'overtime'  => ['label' => 'Tăng ca', 'ratio' => 1.5],
    'holiday'   => ['label' => 'Lễ/Tết', 'ratio' => 2.0],
];
$shifts = get_option('aerp_hrm_shift_definitions', $default_shifts);
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Sửa chấm công</h1>
    <a href="<?= admin_url('admin.php?page=aerp_employees&view=' . $attendance->employee_id . '#attendance') ?>" class="page-title-action">← Quay lại nhân viên</a>

    <form method="post">
        <?php wp_nonce_field('aerp_edit_attendance_action', 'aerp_edit_attendance_nonce'); ?>
        <input type="hidden" name="id" value="<?= esc_attr($attendance->id) ?>">
        <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">

        <table class="form-table">
            <tr>
                <th><label for="work_date">Ngày làm việc</label></th>
                <td><input type="datetime-local" name="work_date" value="<?= esc_attr($attendance->work_date) ?>" required></td>
            </tr>
            <tr>
                <th><label for="shift">Ca làm</label></th>
                <td>
                    <select name="shift" onchange="document.getElementById('work_ratio').value=this.selectedOptions[0].dataset.ratio" required>
                        <?php foreach ($shifts as $key => $s): ?>
                            <option value="<?= esc_attr($key) ?>" data-ratio="<?= esc_attr($s['ratio']) ?>" <?= selected($attendance->shift, $key) ?>>
                                <?= esc_html($s['label']) ?> (x<?= esc_html($s['ratio']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="work_ratio">Hệ số công</label></th>
                <td><input type="number" step="0.1" name="work_ratio" id="work_ratio" value="<?= esc_attr($attendance->work_ratio) ?>" required></td>
            </tr>
            <tr>
                <th><label for="note">Ghi chú</label></th>
                <td><input type="text" name="note" value="<?= esc_attr($attendance->note) ?>"></td>
            </tr>
        </table>

        <p><input type="submit" name="aerp_update_attendance" class="button button-primary" value="Cập nhật chấm công"></p>
    </form>
</div>