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
    $shift = sanitize_text_field($_POST['shift'] ?? '');
    $note  = sanitize_text_field($_POST['note'] ?? '');

    $ratio = $shifts[$shift]['ratio'] ?? 1.0;

    if ($date && $shift) {
        if (AERP_Attendance_Manager::check_duplicate($employee_id, $date)) {
            aerp_js_redirect(add_query_arg('attendance_status', 'duplicate'));
            exit;
        } else {
            $saved = AERP_Attendance_Manager::save_attendance($employee_id, $date, $shift, $ratio, $note);
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
    $employee_id, $limit, $offset
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
                <label for="work_date">Ngày làm việc:</label>
                <input type="datetime-local" name="work_date" value="<?= esc_attr($today) ?>" required>
            </div>

            <div class="aerp-hrm-task-form-row">
                <label for="shift">Ca làm:</label>
                <select name="shift" required class="aerp-hrm-aerp-hrm-aerp-hrm-aerp-hrm-custom-select">
                    <?php foreach ($shifts as $key => $s): ?>
                        <option value="<?= esc_attr($key) ?>">
                            <?= esc_html($s['label']) ?> (x<?= esc_html($s['ratio']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
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
    </div>
</div>