<?php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$employee_id = absint($_GET['id'] ?? 0);
$attendance_id = absint($_GET['attendance_id'] ?? 0);
$attendance = AERP_Attendance_Manager::get_by_id($attendance_id);
if (!$employee_id || !$attendance) {
    echo '<div class="notice notice-error"><p>Thiếu dữ liệu hoặc không tìm thấy dòng chấm công.</p></div>';
    return;
}
$employee = AERP_Frontend_Employee_Manager::get_by_id($employee_id);
$today = date('Y-m-d');
ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Sửa chấm công cho: <?= esc_html($employee->full_name) ?></h2>
    <div class="user-info text-end">
        Xin chào, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_save_attendance_action', 'aerp_save_attendance_nonce'); ?>
            <input type="hidden" name="employee_id" value="<?= esc_attr($employee_id) ?>">
            <input type="hidden" name="attendance_id" value="<?= esc_attr($attendance->id) ?>">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Ngày áp dụng</label>
                    <input type="date" name="work_date" required class="form-control bg-body" value="<?= esc_attr(date('Y-m-d', strtotime($attendance->work_date))) ?>">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Loại chấm công</label>
                    <select class="form-select" name="shift_type" id="shift_type" onchange="onShiftTypeChange()" required>
                        <option value="off" <?= $attendance->shift === 'off' ? 'selected' : '' ?>>Nghỉ (OFF)</option>
                        <option value="ot" <?= $attendance->shift === 'ot' ? 'selected' : '' ?>>Tăng ca (OT)</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3" id="work_ratio_row">
                    <label class="form-label">Hệ số công</label>
                    <select class="form-select" name="work_ratio_select" id="work_ratio_select" onchange="onWorkRatioSelectChange()">
                        <option value="1" <?= ($attendance->work_ratio == 1) ? 'selected' : '' ?>>1.0</option>
                        <option value="1.5" <?= ($attendance->work_ratio == 1.5) ? 'selected' : '' ?>>1.5</option>
                        <option value="custom" <?= ($attendance->work_ratio != 1 && $attendance->work_ratio != 1.5 && $attendance->work_ratio != 0) ? 'selected' : '' ?>>Tự nhập</option>
                    </select>
                    <input type="number" step="0.1" min="0" class="form-control mt-2" name="work_ratio" id="work_ratio" value="<?= esc_attr($attendance->work_ratio) ?>" style="width:80px;display:none;">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Ghi chú</label>
                    <input class="form-control" type="text" name="note" value="<?= esc_attr($attendance->note) ?>" placeholder="(Tùy chọn)">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="aerp_save_attendance" class="btn btn-primary">Cập nhật chấm công</button>
                    <a href="<?= home_url('/aerp-hrm-employees/?action=view&id=' . $employee_id . '&section=attendance') ?>" class="btn btn-secondary">Quay lại</a>
                </div>
            </div>
        </form>
    </div>
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
    if(document.getElementById('work_ratio_select').value === 'custom') {
        document.getElementById('work_ratio').style.display = '';
    }
});
</script>
<?php
$content = ob_get_clean();
$title = 'Sửa chấm công cho nhân viên';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');