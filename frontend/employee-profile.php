<?php
if (!defined('ABSPATH')) {
    exit();
}

$user_id = get_current_user_id();
$employee = aerp_get_employee_by_user_id($user_id);

if (!$employee && !current_user_can('manage_options')) {
    echo '<div class="notice notice-warning"><p>Chưa liên kết nhân viên với tài khoản WordPress.</p></div>';
    return;
}

$employee_id = $employee ? $employee->id : 0;

// Xử lý thêm thưởng/phạt
if (
    isset($_POST['aerp_add_adjustment']) &&
    check_admin_referer('aerp_add_adjustment_action', 'aerp_add_adjustment_nonce')
) {
    AERP_Adjustment_Manager::add([
        'employee_id' => $employee_id,
        'type' => sanitize_text_field($_POST['type']),
        'amount' => floatval($_POST['amount']),
        'reason' => sanitize_text_field($_POST['reason']),
        'date_effective' => sanitize_text_field($_POST['date_effective']),
        'description' => sanitize_textarea_field($_POST['description'])
    ]);
    aerp_js_redirect(add_query_arg('adjustment_added', '1', remove_query_arg('adjustment_added')));
    exit;
}

// Thông báo sau redirect
$notification = '';
if (isset($_GET['adjustment_added'])) {
    $notification = 'Thêm thưởng/phạt thành công';
}

// Lấy bản ghi lương mới nhất
global $wpdb;
$salary = $wpdb->get_row($wpdb->prepare("
    SELECT * FROM {$wpdb->prefix}aerp_hrm_salaries 
    WHERE employee_id = %d 
    ORDER BY salary_month DESC, created_at DESC 
    LIMIT 1
", $employee_id));

$month = $salary ? date('Y-m', strtotime($salary->salary_month)) : '';
$month_start = $month . '-01';
$month_end = date('Y-m-t', strtotime($month_start));
//Lấy config lương
$config = $wpdb->get_row(
    $wpdb->prepare(
        "
        SELECT * FROM {$wpdb->prefix}aerp_hrm_salary_config
        WHERE employee_id = %d AND start_date <= %s AND end_date >= %s
        ORDER BY start_date DESC LIMIT 1
    ",
        $employee_id,
        $month_start,
        $month_start,
    ),
);

// Tính toán chi tiết
$work_days = intval($salary->work_days ?? 0);
$total = $salary ? $salary->final_salary ?? 0 : 0;

$adjustments = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_adjustments WHERE employee_id = %d AND date_effective BETWEEN %s AND %s ORDER BY date_effective DESC", $employee_id, $month_start, $month_end));
$rewards = [];
$fines = [];

foreach ($adjustments as $a) {
    if ($a->type === 'reward') {
        $rewards[] = $a;
    } elseif ($a->type === 'fine') {
        $fines[] = $a;
    }
}

// Tính tổng điểm KPI các công việc trong tháng lương
$total_kpi = 0;
$kpi_bonus = 0;
if ($salary) {
    $tasks_in_month = AERP_Task_Manager::get_tasks_by_month($employee_id, date('n', strtotime($salary->salary_month)), date('Y', strtotime($salary->salary_month)));
    foreach ($tasks_in_month as $task) {
        $total_kpi += (int)($task->score ?? 0);
    }
    // Lấy mức thưởng theo tổng điểm KPI (chuẩn bảng aerp_hrm_kpi_settings)
    $kpi_setting = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}aerp_hrm_kpi_settings WHERE min_score <= %d ORDER BY min_score DESC LIMIT 1",
        $total_kpi
    ));
    if ($kpi_setting) {
        $kpi_bonus = $kpi_setting->reward_amount;
    }
}

// Lấy thêm phạt từ bảng vi phạm
$discipline_fines = $wpdb->get_results($wpdb->prepare("
    SELECT dr.fine_amount AS amount, dr.rule_name AS reason, dl.date_violation AS date, '' AS description
    FROM {$wpdb->prefix}aerp_hrm_disciplinary_logs dl
    INNER JOIN {$wpdb->prefix}aerp_hrm_disciplinary_rules dr ON dr.id = dl.rule_id
    WHERE dl.employee_id = %d AND dl.date_violation BETWEEN %s AND %s
", $employee_id, $month_start, $month_end));

// Lấy thêm thưởng từ bảng tự động
$auto_rewards = $wpdb->get_results($wpdb->prepare("
    SELECT rd.amount AS amount, rd.name AS reason, er.month AS date, er.note AS description
    FROM {$wpdb->prefix}aerp_hrm_employee_rewards er
    INNER JOIN {$wpdb->prefix}aerp_hrm_reward_definitions rd ON rd.id = er.reward_id
    WHERE er.employee_id = %d AND er.month BETWEEN %s AND %s
", $employee_id, $month_start, $month_end));

// Phân loại adjustments
$rewards = array_filter($adjustments, fn($a) => $a->type === 'reward');
$fines = array_filter($adjustments, fn($a) => $a->type === 'fine');

// Gộp tất cả lại
$all_rewards = array_merge(
    array_map(function ($r) {
        $r->type = 'reward';
        return $r;
    }, $rewards),
    array_map(function ($r) {
        $r->type = 'reward';
        return $r;
    }, $auto_rewards)
);
$all_fines = array_merge(
    array_map(function ($f) {
        $f->type = 'fine';
        return $f;
    }, $fines),
    array_map(function ($f) {
        $f->type = 'fine';
        return $f;
    }, $discipline_fines)
);

$calc_month = isset($_GET['calc_month']) ? $_GET['calc_month'] : date('Y-m');
$calc_data = null;
if (isset($_GET['calc_month'])) {
    $month_start = date('Y-m-01 00:00:00', strtotime($calc_month));
    $month_end   = date('Y-m-t 23:59:59', strtotime($calc_month));
    // Lấy cấu hình lương
    $config = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}aerp_hrm_salary_config
        WHERE employee_id = %d AND start_date <= %s AND end_date >= %s
        ORDER BY start_date DESC LIMIT 1
    ", $employee_id, $month_start, $month_start));
    $base      = $config ? floatval($config->base_salary) : 0;
    $allowance = $config ? floatval($config->allowance) : 0;
    // Số ngày làm việc chuẩn
    $start = new DateTime($month_start);
    $end = new DateTime($month_end);
    $work_days_standard = 0;
    for ($d = clone $start; $d <= $end; $d->modify('+1 day')) {
        $w = (int)$d->format('N');
        if ($w < 6) $work_days_standard++;
    }
    // Chấm công
    $attendance = $wpdb->get_results($wpdb->prepare("
        SELECT shift, work_ratio FROM {$wpdb->prefix}aerp_hrm_attendance
        WHERE employee_id = %d AND work_date BETWEEN %s AND %s
    ", $employee_id, $month_start, $month_end));
    $off_days = 0;
    $ot_total = 0;
    foreach ($attendance as $row) {
        if ($row->shift === 'off' && floatval($row->work_ratio) == 0) {
            $off_days++;
        } elseif ($row->shift === 'ot' && floatval($row->work_ratio) > 0) {
            $ot_total += floatval($row->work_ratio);
        }
    }
    $actual_work_days = $work_days_standard - $off_days;
    $salary_per_day = ($base + $allowance) / ($work_days_standard ?: 1);
    $total_salary = $actual_work_days * $salary_per_day + $ot_total * $salary_per_day;
    // Thưởng & phạt thủ công
    $adjustments = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}aerp_hrm_adjustments
        WHERE employee_id = %d AND date_effective BETWEEN %s AND %s
    ", $employee_id, $month_start, $month_end));
    $bonus = 0;
    $deduction = 0;
    $cost_items = [];
    foreach ($adjustments as $a) {
        if ($a->type === 'reward') {
            $bonus += floatval($a->amount);
        } elseif ($a->type === 'fine') {
            $deduction += floatval($a->amount);
        }
    }

    // Thêm chi phí tăng ca
    if ($ot_total > 0) {
        $ot_amount = $ot_total * $salary_per_day;
        $cost_items[] = ['type' => 'plus', 'label' => 'Tăng ca (' . $ot_total . ' ngày)', 'amount' => $ot_amount];
    }

    // Thêm chi phí nghỉ không lương
    if ($off_days > 0) {
        $off_amount = $off_days * $salary_per_day;
        $cost_items[] = ['type' => 'minus', 'label' => 'Nghỉ không lương (' . $off_days . ' ngày)', 'amount' => -$off_amount];
    }

    // Thưởng KPI theo task
    $total_kpi = (int)$wpdb->get_var($wpdb->prepare("
        SELECT SUM(score) FROM {$wpdb->prefix}aerp_hrm_tasks
        WHERE employee_id = %d AND status = 'done' AND deadline BETWEEN %s AND %s
    ", $employee_id, $month_start, $month_end));
    $kpi_bonus = 0;
    $kpi_levels  = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_kpi_settings ORDER BY min_score DESC");
    foreach ($kpi_levels  as $level) {
        if ($total_kpi >= $level->min_score) {
            $kpi_bonus = floatval($level->reward_amount);
            break;
        }
    }
    $bonus += $kpi_bonus;

    // Thưởng động từ hook (tết, sinh nhật...)
    $auto_bonus = apply_filters('aerp_hrm_auto_bonus', 0, $employee_id, $calc_month);
    $bonus += $auto_bonus;

    // Phạt vi phạm
    $disciplines = $wpdb->get_results($wpdb->prepare("
        SELECT dr.penalty_point, dr.fine_amount FROM {$wpdb->prefix}aerp_hrm_disciplinary_logs dl
        INNER JOIN {$wpdb->prefix}aerp_hrm_disciplinary_rules dr ON dr.id = dl.rule_id
        WHERE dl.employee_id = %d AND dl.date_violation BETWEEN %s AND %s
    ", $employee_id, $month_start, $month_end));
    $total_points = 100;
    $violation_deduction = 0;
    foreach ($disciplines as $v) {
        $total_points -= intval($v->penalty_point);
        $violation_deduction += floatval($v->fine_amount);
    }
    $total_points = max(0, $total_points);
    $deduction += $violation_deduction;

    // Ứng lương
    $advance = floatval($wpdb->get_var($wpdb->prepare("
        SELECT SUM(amount) FROM {$wpdb->prefix}aerp_hrm_advance_salaries
        WHERE employee_id = %d AND advance_date BETWEEN %s AND %s
    ", $employee_id, $month_start, $month_end))) ?: 0;
    if ($advance) $cost_items[] = ['type' => 'minus', 'label' => 'Ứng lương', 'amount' => -$advance];
    // Xếp loại
    $ranking = '--';
    $ranks = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}aerp_hrm_ranking_settings
        ORDER BY min_point DESC
    ");
    foreach ($ranks as $r) {
        if ($total_points >= $r->min_point) {
            $ranking = $r->rank_code;
            break;
        }
    }
    // Thưởng thủ công
    $bonus_thu_cong = $bonus - $kpi_bonus - $auto_bonus;
    $tong_nhan = $base + $allowance + ($ot_total * $salary_per_day) + $auto_bonus + $kpi_bonus + $bonus_thu_cong;
    // Tổng lương
    $final_salary = $total_salary + $bonus - $deduction - $advance;
    $calc_data = compact('base', 'allowance', 'work_days_standard', 'off_days', 'ot_total', 'actual_work_days', 'salary_per_day', 'total_salary', 'bonus', 'deduction', 'advance', 'final_salary', 'cost_items', 'total_kpi', 'kpi_bonus', 'auto_bonus', 'total_points', 'ranking', 'tong_nhan');
}

?>

<div class="aerp-hrm-profile-container modern">
    <div class="aerp-hrm-card profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php $initial = mb_strtoupper(mb_substr($employee->full_name, 0, 1)); ?>
                <span><?= esc_html($initial) ?></span>
            </div>
            <div class="profile-info">
                <div class="profile-name"><?= esc_html($employee->full_name) ?></div>
                <div class="profile-meta">Mã NV: <?= esc_html($employee->employee_code) ?> · <?= esc_html(aerp_get_position_name($employee->position_id)) ?> · <?= esc_html(aerp_get_department_name($employee->department_id)) ?></div>
            </div>
        </div>
        <div class="profile-details-grid">
            <div><span class="icon">📧</span> <a href="mailto:<?= esc_attr($employee->email) ?>"><?= esc_html($employee->email) ?></a></div>
            <div><span class="icon">📅</span> Ngày vào làm: <?= $employee->join_date ? date('d/m/Y', strtotime($employee->join_date)) : '—' ?></div>
            <?php if ($employee->relative_name): ?>
                <div><span class="icon">👨‍👩‍👧‍👦</span> Người thân: <?= esc_html($employee->relative_name) ?> (<?= esc_html($employee->relative_relationship) ?> – <?= esc_html($employee->relative_phone) ?>)</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="aerp-hrm-card salary-card">
        <div class="aerp-hrm-title"><span class="icon">💰</span> Lương tháng hiện tại</div>
        <?php if ($salary): ?>
            <div class="salary-table">
                <div><span>Tháng:</span> <strong><?= date('m/Y', strtotime($salary->salary_month)) ?></strong></div>
                <div><span>Lương cơ bản:</span> <strong class="text-primary"><?= number_format($salary->base_salary, 0, ',', '.') ?> đ</strong></div>
                <div><span>Phụ cấp:</span> <strong><?= number_format($config->allowance, 0, ',', '.') ?> đ</strong></div>
                <div><span>Công/ngày:</span> <strong><?= isset($salary->salary_per_day) ? number_format($salary->salary_per_day, 0, ',', '.') . ' đ' : '' ?></strong></div>
                <div><span>Thưởng động:</span> <strong class="text-success"><?= number_format($salary->auto_bonus, 0, ',', '.') ?> đ</strong></div>
                <div><span>Tổng ngày công:</span> <strong><?= $work_days ?></strong></div>
                <div><span>Thưởng:</span> <strong class="text-success">+<?= number_format($salary->bonus, 0, ',', '.') ?> đ</strong></div>
                <div><span>Phạt:</span> <strong class="text-danger">-<?= number_format($salary->deduction, 0, ',', '.') ?> đ</strong></div>
                <div><span>Điểm chuyên cần:</span> <strong><?= esc_html($salary->points_total) ?></strong></div>
                <div><span>Xếp loại:</span> <strong><?= esc_html($salary->ranking ?: '--') ?></strong></div>
                <div><span>Thưởng KPI:</span> <strong><?= esc_html($total_kpi) ?> (<?= number_format($kpi_bonus, 0, ',', '.') ?> đ)</strong></div>
                <div><span>Ứng lương:</span> <strong><?= number_format($salary->advance_paid, 0, ',', '.') ?> đ</strong></div>
                <?php
                $tong_nhan = ($salary->base_salary ?? 0)
                    + ($salary->auto_bonus ?? 0)
                    + ($salary->bonus ?? 0)
                    + (isset($salary->allowance) ? $salary->allowance : ($config->allowance ?? 0))
                    + ($salary->salary_per_day * $salary->ot_days);
                ?>
                <div class="salary-total"><span><strong>Tổng nhận:</strong></span> <strong><?= number_format($tong_nhan, 0, ',', '.') ?> đ</strong></div>
                <div class="salary-total"><span><strong>Tổng thực lãnh:</strong></span> <strong class="text-total"><?= number_format($total, 0, ',', '.') ?> đ</strong></div>
            </div>
        <?php else: ?>
            <p><em>Chưa có dữ liệu lương.</em></p>
        <?php endif; ?>
        <form method="get" class="aerp-hrm-task-form" style="display:flex;gap:12px;align-items:center;justify-content: end;">
            <input type="hidden" name="page" value="aerp_employee_profile">
            <input type="month" style="margin-top: 0 !important;" name="calc_month" value="<?= esc_attr($calc_month) ?>">
            <button type="submit" style="margin-top: 0 !important;" class="aerp-hrm-btn">Tính lương</button>
        </form>
    </div>

    <!-- Form chọn tháng và nút tính lương -->
    <?php if ($calc_data): ?>
        <div class="aerp-hrm-card salary-card">
            <div class="aerp-hrm-title"><span class="icon">💰</span> Lương/thưởng/phạt tháng <?= date('m/Y', strtotime($calc_month)) ?></div>
            <div class="salary-table">
                <div><span>Lương cơ bản:</span> <strong class="text-primary"><?= number_format($calc_data['base'], 0, ',', '.') ?> đ</strong></div>
                <div><span>Phụ cấp:</span> <strong><?= number_format($calc_data['allowance'], 0, ',', '.') ?> đ</strong></div>
                <div><span>Tổng ngày công:</span> <strong><?= $calc_data['work_days_standard'] ?></strong></div>
                <div><span>Công/ngày:</span> <strong><?= isset($calc_data['salary_per_day']) ? number_format($calc_data['salary_per_day'], 0, ',', '.') . ' đ' : '' ?></strong></div>
                <div><span>Ngày nghỉ:</span> <strong><?= $calc_data['off_days'] ?></strong></div>
                <div><span>Tăng ca:</span> <strong><?= $calc_data['ot_total'] ?></strong></div>
                <div><span>Thưởng:</span> <strong class="text-success">+<?= number_format($calc_data['bonus'], 0, ',', '.') ?> đ</strong></div>
                <div><span>Phạt:</span> <strong class="text-danger">-<?= number_format($calc_data['deduction'], 0, ',', '.') ?> đ</strong></div>
                <div><span>Thưởng KPI:</span> <strong><?= esc_html($calc_data['total_kpi']) ?> (<?= number_format($calc_data['kpi_bonus'], 0, ',', '.') ?> đ)</strong></div>
                <div><span>Ứng lương:</span> <strong>-<?= number_format($calc_data['advance'], 0, ',', '.') ?> đ</strong></div>
                <div class="salary-total"><span><strong>Tổng nhận:</strong></span> <strong><?= number_format($calc_data['tong_nhan'], 0, ',', '.') ?> đ</strong></div>
                <div class="salary-total"><span><strong>Tổng thực lãnh:</strong></span> <strong class="text-total"><?= number_format($calc_data['final_salary'], 0, ',', '.') ?> đ</strong></div>
            </div>
        </div>
        <div class="aerp-hrm-card">
            <div class="aerp-hrm-title"><span class="icon">📊</span> Chi phí tăng/giảm</div>
            <div class="cost-table">
                <?php foreach ($calc_data['cost_items'] as $item): ?>
                    <div class="cost-row <?= $item['type'] ?>">
                        <span><?= esc_html($item['label']) ?></span>
                        <span><?= ($item['amount'] > 0 ? '+' : '') . number_format($item['amount'], 0, ',', '.') ?> đ</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="aerp-hrm-card">
        <div class="aerp-hrm-title">
            <span class="icon">🎁</span> Chi tiết thưởng / phạt
            <button type="button" class="aerp-hrm-task-form button" data-open-adjustment-popup>+ Thêm</button>
        </div>
        <?php if (!empty($notification)): ?>
            <div id="aerp-hrm-toast" class="aerp-hrm-toast">
                <span><?= esc_html($notification) ?></span>
                <button onclick="closeToast()">X</button>
            </div>
        <?php endif; ?>

        <div class="aerp-accordion-group">
            <div class="aerp-accordion-item">
                <button class="aerp-hrm-accordion-header" type="button">
                    🎁 Thưởng (<?= count($all_rewards) ?> mục)
                    <span class="aerp-hrm-accordion-icon">▼</span>
                </button>
                <div class="aerp-hrm-accordion-body bg-reward">
                    <?php if (empty($all_rewards)): ?>
                        <p><em>Không có mục thưởng.</em></p>
                    <?php else: ?>
                        <?php foreach ($all_rewards as $r): ?>
                            <div class="aerp-hrm-item-row">
                                <strong><?= number_format($r->amount, 0, ',', '.') ?> đ</strong>
                                <em>(<?= esc_html($r->reason) ?>)</em>
                                <div><?= esc_html($r->description) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="aerp-accordion-item">
                <button class="aerp-hrm-accordion-header" type="button">
                    ⚠️ Phạt (<?= count($all_fines) ?> mục)
                    <span class="aerp-hrm-accordion-icon">▼</span>
                </button>
                <div class="aerp-hrm-accordion-body bg-fine">
                    <?php if (empty($all_fines)): ?>
                        <p><em>Không có mục phạt.</em></p>
                    <?php else: ?>
                        <?php foreach ($all_fines as $f): ?>
                            <div class="aerp-hrm-item-row">
                                <strong><?= number_format($f->amount, 0, ',', '.') ?> đ</strong>
                                <em>(<?= esc_html($f->reason) ?>)</em>
                                <div><?= esc_html($f->description) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    $configs = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}aerp_hrm_salary_config
        WHERE employee_id = %d ORDER BY start_date DESC
    ", $employee_id));
    if ($configs):
    ?>
        <div class="aerp-hrm-card">
            <div class="aerp-hrm-title"><i>📈</i> Lộ trình lương</div>
            <div class="aerp-hrm-salary-timeline">
                <?php foreach ($configs as $config): ?>
                    <div class="aerp-hrm-timeline-item">
                        <div class="dot"><?= date('d/m/Y', strtotime($config->start_date)) ?> - <?= date('d/m/Y', strtotime($config->end_date)) ?></div>
                        <div class="info">
                            💰 <?= number_format($config->base_salary, 0, ',', '.') ?> đ
                            <?php if ($config->allowance >= 0): ?>
                                <small>+ <?= number_format($config->allowance, 0, ',', '.') ?> đ phụ cấp</small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="aerp-hrm-card">
        <div class="aerp-hrm-title"><i>📋</i> Công việc</div>
        <a href="<?= esc_url(site_url('/danh-sach-cong-viec')) ?>" class="aerp-hrm-btn">Xem danh sách công việc</a>
    </div>
</div>

<!-- Popup form thêm thưởng/phạt -->
<div class="aerp-hrm-task-popup" id="aerp-hrm-adjustmentPopup">
    <div class="aerp-hrm-task-popup-inner">
        <div class="aerp-hrm-task-popup-close">×</div>
        <h3>➕ Thêm thưởng/phạt</h3>
        <form method="post" class="aerp-hrm-task-form">
            <?php wp_nonce_field('aerp_add_adjustment_action', 'aerp_add_adjustment_nonce'); ?>
            <select name="type" required>
                <option value="">-- Chọn loại --</option>
                <option value="reward">Thưởng</option>
                <option value="fine">Phạt</option>
            </select>
            <input type="number" name="amount" placeholder="Số tiền" required>
            <input type="text" name="reason" placeholder="Lý do" required>
            <input type="date" name="date_effective" required>
            <textarea name="description" rows="3" placeholder="Ghi chú..."></textarea>
            <button type="submit" name="aerp_add_adjustment">Thêm</button>
        </form>
    </div>
</div>


<style>
    .cost-table {
        display: grid;
        gap: 8px;
        margin-top: 10px;
    }

    .cost-row {
        display: flex;
        justify-content: space-between;
        font-size: 15px;
    }

    .cost-row.plus {
        color: #16a34a;
    }

    .cost-row.minus {
        color: #dc2626;
    }
</style>

<script>
    jQuery(function($) {
        $('.aerp-hrm-accordion-header').on('click', function() {
            $(this).toggleClass('active')
                .next('.aerp-hrm-accordion-body')
                .stop(true, true)
                .slideToggle(250);
        });
    });
</script>