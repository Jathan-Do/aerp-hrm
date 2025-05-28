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
                <th><label for="work_date">Ngày áp dụng</label></th>
                <td><input type="date" name="work_date" value="<?= esc_attr(date('Y-m-d', strtotime($attendance->work_date))) ?>" required></td>
            </tr>
            <tr>
                <th><label for="shift_type">Loại chấm công</label></th>
                <td>
                    <select name="shift_type" id="shift_type" onchange="onShiftTypeChange()" required>
                        <option value="off" <?= $attendance->shift === 'off' ? 'selected' : '' ?>>Nghỉ (OFF)</option>
                        <option value="ot" <?= $attendance->shift === 'ot' ? 'selected' : '' ?>>Tăng ca (OT)</option>
                    </select>
                </td>
            </tr>
            <tr id="work_ratio_row">
                <th><label for="work_ratio">Hệ số công</label></th>
                <td>
                    <select name="work_ratio_select" id="work_ratio_select" onchange="onWorkRatioSelectChange()">
                        <option value="1" <?= ($attendance->work_ratio == 1) ? 'selected' : '' ?>>1.0</option>
                        <option value="1.5" <?= ($attendance->work_ratio == 1.5) ? 'selected' : '' ?>>1.5</option>
                        <option value="custom" <?= ($attendance->work_ratio != 1 && $attendance->work_ratio != 1.5 && $attendance->work_ratio != 0) ? 'selected' : '' ?>>Tự nhập</option>
                    </select>
                    <input type="number" step="0.1" min="0" name="work_ratio" id="work_ratio" value="<?= esc_attr($attendance->work_ratio) ?>" style="width:80px;display:none;">
                </td>
            </tr>
            <tr>
                <th><label for="note">Ghi chú</label></th>
                <td><input type="text" name="note" value="<?= esc_attr($attendance->note) ?>"></td>
            </tr>
        </table>

        <p><input type="submit" name="aerp_update_attendance" class="button button-primary" value="Cập nhật chấm công"></p>
    </form>
</div>
<script>
function onShiftTypeChange() {
    var type = document.getElementById('shift_type').value;
    var ratioRow = document.getElementById('work_ratio_row');
    var ratioSelect = document.getElementById('work_ratio_select');
    var ratioInput = document.getElementById('work_ratio');
    if (type === 'off') {
        ratioRow.style.display = 'none';
        ratioInput.value = 0;
    } else {
        ratioRow.style.display = '';
        // Nếu là tăng ca, tự động chọn lại hệ số phù hợp
        if (ratioSelect.value !== 'custom') {
            ratioInput.value = ratioSelect.value;
            ratioInput.style.display = 'none';
        }
    }
}
function onWorkRatioSelectChange() {
    var ratioSelect = document.getElementById('work_ratio_select');
    var ratioInput = document.getElementById('work_ratio');
    if (ratioSelect.value === 'custom') {
        ratioInput.style.display = '';
        ratioInput.value = '';
        ratioInput.focus();
    } else {
        ratioInput.style.display = 'none';
        ratioInput.value = ratioSelect.value;
    }
}
document.addEventListener('DOMContentLoaded', function() {
    onShiftTypeChange();
    document.getElementById('shift_type').addEventListener('change', onShiftTypeChange);
    document.getElementById('work_ratio_select').addEventListener('change', onWorkRatioSelectChange);
    // Hiển thị input hệ số nếu là custom
    if(document.getElementById('work_ratio_select').value === 'custom') {
        document.getElementById('work_ratio').style.display = '';
    }
});
</script>