<?php
if (!defined('ABSPATH')) exit;

$employee_id = absint($_GET['employee_id'] ?? 0);
if (!$employee_id) {
    echo '<div class="notice notice-error"><p>Thiếu employee_id</p></div>';
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
    <h1 class="wp-heading-inline">Thêm chấm công</h1>
    <a href="<?= admin_url('admin.php?page=aerp_employees&view=' . $employee_id . '#attendance') ?>" class="page-title-action">← Quay lại bảng công</a>

    <form method="post">
        <?php wp_nonce_field('aerp_add_attendance_action', 'aerp_add_attendance_nonce'); ?>
        <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">

        <table class="form-table">
            <tr>
                <th><label for="work_date">Ngày áp dụng</label></th>
                <td><input type="date" name="work_date" required></td>
            </tr>
            <tr>
                <th><label for="shift_type">Loại chấm công</label></th>
                <td>
                    <select name="shift_type" id="shift_type" onchange="onShiftTypeChange()" required>
                        <option value="off">Nghỉ (OFF)</option>
                        <option value="ot">Tăng ca (OT)</option>
                    </select>
                </td>
            </tr>
            <tr id="work_ratio_row">
                <th><label for="work_ratio">Hệ số công</label></th>
                <td>
                    <select name="work_ratio_select" id="work_ratio_select" onchange="onWorkRatioSelectChange()">
                        <option value="1">1.0</option>
                        <option value="1.5">1.5</option>
                        <option value="custom">Tự nhập</option>
                    </select>
                    <input type="number" step="0.1" min="0" name="work_ratio" id="work_ratio" value="1" style="width:80px;display:none;">
                </td>
            </tr>
            <tr>
                <th><label for="note">Ghi chú</label></th>
                <td><input type="text" name="note" placeholder="(Tùy chọn)"></td>
            </tr>
        </table>

        <p><input type="submit" name="aerp_add_attendance" class="button button-primary" value="Lưu chấm công"></p>
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
        ratioSelect.value = '1';
        ratioInput.value = 1;
        ratioInput.style.display = 'none';
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
});
</script>