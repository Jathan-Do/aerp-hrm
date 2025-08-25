<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
if (!is_user_logged_in()) {
    $login_url = site_url('/aerp-dang-nhap');
    return '<p>Bạn cần <a href="' . esc_url($login_url) . '">đăng nhập</a> để chấm công.</p>';
}

$user_id = get_current_user_id();
$employee = aerp_get_employee_by_user_id($user_id);
if (!$employee) return '<p>Không tìm thấy nhân viên tương ứng.</p>';

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
            aerp_clear_table_cache();
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
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chấm công</title>
    <?php wp_head(); ?>
</head>

<body>
    <!-- Quick Links -->
    <?php include(AERP_HRM_PATH . 'frontend/quick-links.php'); ?>
    <div class="aerp-hrm-dashboard">
        <div class="aerp-card attendance-card">
            <div class="aerp-card-header">
                <h2><span class="dashicons dashicons-clock"></span> Chấm công hôm nay</h2>
            </div>

            <?= $status_msg ?>

            <form method="post" class="aerp-form">
                <?php wp_nonce_field('aerp_frontend_attendance'); ?>

                <div class="form-group" style="padding: 0 15px;">
                    <label for="work_date"><span class="dashicons dashicons-calendar"></span> Ngày chấm công</label>
                    <input type="date" id="work_date" name="work_date" value="<?= esc_attr($today) ?>" required>
                </div>

                <div class="form-group" style="padding: 0 15px;">
                    <label for="shift_type"><span class="dashicons dashicons-businessman"></span> Loại chấm công</label>
                    <select class="aerp-hrm-custom-select" id="shift_type" name="shift_type" onchange="onShiftTypeChange()" required>
                        <option value="off">Nghỉ (OFF)</option>
                        <option value="ot">Tăng ca (OT)</option>
                    </select>
                </div>

                <div class="form-group" style="padding: 0 15px;" id="work_ratio_row">
                    <label for="work_ratio"><span class="dashicons dashicons-calculator"></span> Hệ số công</label>
                    <div class="aerp-ratio-selector">
                        <select id="work_ratio_select" name="work_ratio_select" onchange="onWorkRatioSelectChange()">
                            <option value="1">1.0</option>
                            <option value="1.5">1.5</option>
                            <option value="custom">Tự nhập</option>
                        </select>
                        <input type="number" step="0.1" min="0" id="work_ratio" name="work_ratio" value="1" style="display:none;">
                    </div>
                </div>

                <div class="form-group" style="padding: 0 15px;">
                    <label for="note"><span class="dashicons dashicons-format-status"></span> Ghi chú</label>
                    <input type="text" id="note" name="note" placeholder="Nhập ghi chú (nếu có)">
                </div>

                <div class="aerp-form-actions" style="padding: 15px;">
                    <button type="submit" name="aerp_frontend_attendance" class="aerp-btn aerp-btn-primary">
                        <span class="dashicons dashicons-yes"></span> Chấm công
                    </button>
                </div>
            </form>
        </div>

        <div class="aerp-card aerp-attendance-history">
            <div class="aerp-card-header">
                <h2><span class="dashicons dashicons-backup"></span> Lịch sử chấm công</h2>
            </div>

            <?php if ($recent_attendance): ?>
                <div class="aerp-attendance-list">
                    <?php foreach ($recent_attendance as $row): ?>
                        <?php
                        $shift_label = isset($shifts[$row->shift]['label']) ? $shifts[$row->shift]['label'] : ucfirst($row->shift);
                        $is_off = $row->shift === 'off';
                        ?>
                        <div class="aerp-attendance-item <?= $is_off ? 'off-day' : '' ?>">
                            <div class="aerp-attendance-date">
                                <div class="aerp-attendance-day"><?= date('d', strtotime($row->work_date)) ?></div>
                                <div class="aerp-attendance-month"><?= date('m/Y', strtotime($row->work_date)) ?></div>
                            </div>
                            <div class="aerp-attendance-info">
                                <div class="aerp-attendance-type">
                                    <span class="badge <?= $is_off ? 'badge-danger' : 'badge-success' ?>">
                                        <?= esc_html($shift_label) ?>
                                    </span>
                                    <?php if (!$is_off): ?>
                                        <span class="aerp-attendance-ratio">x<?= $row->work_ratio ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($row->note)): ?>
                                    <div class="aerp-attendance-note">
                                        <span class="dashicons dashicons-format-chat"></span> <?= esc_html($row->note) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="aerp-attendance-time">
                                <?= date('H:i', strtotime($row->work_date)) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="aerp-pagination">
                        <?php
                        $big = 999999999;
                        echo paginate_links(array(
                            'base'    => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                            'format'  => '?paged=%#%',
                            'current' => max(1, $paged),
                            'total'   => $total_pages,
                            'prev_text' => '<i class="dashicons dashicons-arrow-left-alt2"></i>',
                            'next_text' => '<i class="dashicons dashicons-arrow-right-alt2"></i>',
                        ));
                        ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="aerp-no-data">
                    <span class="dashicons dashicons-archive"></span>
                    <p>Bạn chưa chấm công gần đây</p>
                </div>
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
                ratioRow.style.display = 'block';
                ratioSelect.value = '1';
                ratioInput.value = 1;
                ratioInput.style.display = 'none';
            }
        }

        function onWorkRatioSelectChange() {
            var ratioSelect = document.getElementById('work_ratio_select');
            var ratioInput = document.getElementById('work_ratio');

            if (ratioSelect.value === 'custom') {
                ratioInput.style.display = 'block';
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
    <?php wp_footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>