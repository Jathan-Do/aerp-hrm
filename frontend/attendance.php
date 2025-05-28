<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$user_id = get_current_user_id();
$employee = aerp_get_employee_by_user_id($user_id);
if (!$employee) return;

$employee_id = $employee->id;
$today = date('Y-m-d');

$default_shifts = [
    'full'      => ['label' => 'Cả ngày', 'ratio' => 1.0],
    'morning'   => ['label' => 'Ca sáng', 'ratio' => 1.0],
    'afternoon' => ['label' => 'Ca chiều', 'ratio' => 1.0],
    'overtime'  => ['label' => 'Tăng ca', 'ratio' => 1.5],
    'holiday'   => ['label' => 'Lễ/Tết', 'ratio' => 2.0],
];
$shifts = get_option('aerp_hrm_shift_definitions', $default_shifts);

$success = false;
$error = '';

if (isset($_POST['aerp_frontend_attendance']) && wp_verify_nonce($_POST['_wpnonce'], 'aerp_frontend_attendance')) {
    $date  = sanitize_text_field($_POST['work_date'] ?? '');
    $shift_type = sanitize_text_field($_POST['shift_type'] ?? '');
    $work_ratio = floatval($_POST['work_ratio'] ?? 0);
    $note  = sanitize_text_field($_POST['note'] ?? '');

    $shift = ($shift_type === 'off') ? 'off' : 'ot';
    if ($shift_type === 'off') $work_ratio = 0;

    if ($date && $shift_type) {
        if (AERP_Attendance_Manager::check_duplicate($employee_id, $date)) {
            aerp_js_redirect(add_query_arg('attendance_status', 'duplicate'));
            exit;
        } else {
            $saved = AERP_Attendance_Manager::save_attendance($employee_id, $date, $shift, $work_ratio, $note);
            aerp_js_redirect(add_query_arg('attendance_status', $saved ? 'success' : 'error'));
            exit;
        }
    } else {
        aerp_js_redirect(add_query_arg('attendance_status', 'missing'));
        exit;
    }
}

$status_msg = '';
if (isset($_GET['attendance_status'])) {
    switch ($_GET['attendance_status']) {
        case 'success':
            $status_msg = '<div id="aerp-hrm-toast" class="aerp-hrm-toast"><span>Đã chấm công thành công.</span><button onclick="closeToast()">X</button></div>';
            break;
        case 'duplicate':
            $status_msg = '<div id="aerp-hrm-toast" class="aerp-hrm-toast warning"><span>Bạn đã chấm công ngày này rồi.</span><button onclick="closeToast()">X</button></div>';
            break;
        case 'error':
            $status_msg = '<div id="aerp-hrm-toast" class="aerp-hrm-toast warning"><span>Không thể lưu chấm công.</span><button onclick="closeToast()">X</button></div>';
            break;
        case 'missing':
            $status_msg = '<div id="aerp-hrm-toast" class="aerp-hrm-toast warning"><span>Vui lòng điền đầy đủ thông tin.</span><button onclick="closeToast()">X</button></div>';
            break;
    }
}

// Phân trang cho danh sách công đã chấm
$paged = max(1, get_query_var('paged'));
$limit   = 10;
$offset  = ($paged - 1) * $limit;

$total = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_attendance WHERE employee_id = %d",
    $employee_id
));

$recent_attendance = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}aerp_hrm_attendance WHERE employee_id = %d ORDER BY work_date DESC LIMIT %d OFFSET %d",
    $employee_id,
    $limit,
    $offset
));

$total_pages = ceil($total / $limit);
?>
<div class="aerp-hrm-profile-container">
    <div class="aerp-hrm-card">
        <div class="aerp-hrm-title"><i>🕒</i> Chấm công</div>

        <?= $status_msg ?>

        <form method="post" class="aerp-hrm-task-form">
            <?php wp_nonce_field('aerp_frontend_attendance'); ?>

            <div class="aerp-hrm-task-form-row">
                <label for="work_date">Ngày chấm công:</label>
                <input type="date" name="work_date" value="<?= esc_attr($today) ?>" required>
            </div>

            <div class="aerp-hrm-task-form-row">
                <label for="shift_type">Loại chấm công:</label>
                <select name="shift_type" id="shift_type" onchange="onShiftTypeChange()" required>
                    <option value="off">Nghỉ (OFF)</option>
                    <option value="ot">Tăng ca (OT)</option>
                </select>
            </div>
            <div class="aerp-hrm-task-form-row" id="work_ratio_row">
                <label for="work_ratio">Hệ số công:</label>
                <select name="work_ratio_select" id="work_ratio_select" onchange="onWorkRatioSelectChange()">
                    <option value="1">1.0</option>
                    <option value="1.5">1.5</option>
                    <option value="custom">Tự nhập</option>
                </select>
                <input type="number" step="0.1" min="0" name="work_ratio" id="work_ratio" value="1" style="width:80px;display:none;">
            </div>
            <div class="aerp-hrm-task-form-row">
                <label for="note">Ghi chú:</label>
                <input type="text" name="note" placeholder="(Tùy chọn)">
            </div>
            <div class="aerp-hrm-task-form-row">
                <button type="submit" name="aerp_frontend_attendance" class="aerp-hrm-task-form button button-submit">Chấm công</button>
            </div>
        </form>

        <div class="aerp-hrm-task-section-title">🔍 Công đã chấm gần đây</div>
        <?php if ($recent_attendance): ?>
            <ul class="aerp-hrm-task-comments">
                <?php foreach ($recent_attendance as $row): ?>
                    <?php
                    $shift_label = isset($shifts[$row->shift]['label']) ? $shifts[$row->shift]['label'] : ucfirst($row->shift);
                    ?>
                    <li>
                        <?= date('d/m/Y H:i', strtotime($row->work_date)) ?> — <?= esc_html($shift_label) ?> (x<?= $row->work_ratio ?>)
                        <?php if (!empty($row->note)) echo ' – ' . esc_html($row->note); ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($total_pages > 1): ?>
                <div class="aerp-hrm-task-pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a class="aerp-hrm-page-link <?= $i == $paged ? 'active' : '' ?>" href="<?= esc_url(add_query_arg(['paged' => $i])) ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Bạn chưa chấm công gần đây.</p>
        <?php endif; ?>
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
</div>