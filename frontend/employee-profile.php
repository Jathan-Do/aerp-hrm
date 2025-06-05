<?php
if (!defined('ABSPATH')) {
    exit();
}

$user_id = get_current_user_id();
$employee = aerp_get_employee_by_user_id($user_id);

if (!$employee && !current_user_can('manage_options')) {
    echo '<div class="aerp-notice notice-warning"><p>Chưa liên kết nhân viên với tài khoản WordPress.</p></div>';
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
$current_month = date('Y-m');
$current_month_start = $current_month . '-01';
$salary = $wpdb->get_row($wpdb->prepare("
    SELECT * FROM {$wpdb->prefix}aerp_hrm_salaries 
    WHERE employee_id = %d 
      AND salary_month = %s
    ORDER BY created_at DESC 
    LIMIT 1
", $employee_id, $current_month_start));

$month = $salary ? date('Y-m', strtotime($salary->salary_month)) : '';
$month_start = $month . '-01';
$month_end = date('Y-m-t', strtotime($month_start));
//Lấy config lương
$config_init = $wpdb->get_row(
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

// Tính toán chi tiết cho tháng lương mới nhất (chỉ tăng ca, nghỉ không lương, ứng lương)
$latest_cost_items = [];
$latest_off_days = intval($salary->off_days ?? 0);
$latest_ot_days = floatval($salary->ot_days ?? 0);
$latest_salary_per_day = floatval($salary->salary_per_day ?? 0);
$latest_advance = floatval($salary->advance_paid ?? 0);

// Thêm chi phí tăng ca cho tháng mới nhất
if ($latest_ot_days > 0 && $latest_salary_per_day > 0) {
    $latest_ot_amount = $latest_ot_days * $latest_salary_per_day;
    $latest_cost_items[] = ['type' => 'plus', 'label' => 'Tăng ca (' . $latest_ot_days . ' ngày)', 'amount' => $latest_ot_amount];
}

// Thêm chi phí nghỉ không lương cho tháng mới nhất
if ($latest_off_days > 0 && $latest_salary_per_day > 0) {
    $latest_off_amount = $latest_off_days * $latest_salary_per_day; // Assuming unpaid leave deduction logic
    $latest_cost_items[] = ['type' => 'minus', 'label' => 'Nghỉ không lương (' . $latest_off_days . ' ngày)', 'amount' => -$latest_off_amount];
}

// Thêm ứng lương cho tháng mới nhất
if ($latest_advance > 0) {
    $latest_cost_items[] = ['type' => 'minus', 'label' => 'Ứng lương', 'amount' => -$latest_advance];
}

// Lấy thưởng/phạt thủ công cho tháng mới nhất - KHÔNG thêm vào latest_cost_items
$latest_manual_adjustments = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_adjustments WHERE employee_id = %d AND date_effective BETWEEN %s AND %s ORDER BY date_effective DESC", $employee_id, $month_start, $month_end));

// Lấy phạt từ bảng vi phạm cho tháng mới nhất - KHÔNG thêm vào latest_cost_items
$latest_discipline_fines = $wpdb->get_results($wpdb->prepare("
    SELECT dr.fine_amount AS amount, dr.rule_name AS reason, dl.date_violation AS date, '' AS description
    FROM {$wpdb->prefix}aerp_hrm_disciplinary_logs dl
    INNER JOIN {$wpdb->prefix}aerp_hrm_disciplinary_rules dr ON dr.id = dl.rule_id
    WHERE dl.employee_id = %d AND dl.date_violation BETWEEN %s AND %s
", $employee_id, $month_start, $month_end));

// Lấy thưởng từ bảng tự động cho tháng mới nhất - KHÔNG thêm vào latest_cost_items
$latest_auto_rewards = $wpdb->get_results($wpdb->prepare("
    SELECT rd.amount AS amount, rd.name AS reason, er.month AS date, er.note AS description
    FROM {$wpdb->prefix}aerp_hrm_employee_rewards er
    INNER JOIN {$wpdb->prefix}aerp_hrm_reward_definitions rd ON rd.id = er.reward_id
    WHERE er.employee_id = %d AND er.month BETWEEN %s AND %s
", $employee_id, $month_start, $month_end));

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
$total_kpi_init = 0;
$kpi_bonus_init = 0;
if ($salary) {
    $tasks_in_month = AERP_Task_Manager::get_tasks_by_month($employee_id, date('n', strtotime($salary->salary_month)), date('Y', strtotime($salary->salary_month)));
    foreach ($tasks_in_month as $task) {
        if ($task->status === 'done') {
            $total_kpi_init += (int)($task->score ?? 0);
        }
    }
    // Lấy mức thưởng theo tổng điểm KPI (chuẩn bảng aerp_hrm_kpi_settings)
    $kpi_setting = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}aerp_hrm_kpi_settings WHERE min_score <= %d ORDER BY min_score DESC LIMIT 1",
        $total_kpi_init
    ));
    if ($kpi_setting) {
        $kpi_bonus_init = $kpi_setting->reward_amount;
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

// Phân loại adjustments (bổ sung trường date)
$rewards = array_map(function ($a) {
    $a->date = $a->date_effective ?? '';
    return $a;
}, array_filter($adjustments, fn($a) => $a->type === 'reward'));
$fines = array_map(function ($a) {
    $a->date = $a->date_effective ?? '';
    return $a;
}, array_filter($adjustments, fn($a) => $a->type === 'fine'));

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

// Lọc lại chỉ lấy các mục trong tháng hiện tại
$all_rewards = array_filter($all_rewards, function ($item) use ($month_start, $month_end) {
    $date = isset($item->date) ? strtotime($item->date) : false;
    return $date && $date >= strtotime($month_start) && $date <= strtotime($month_end);
});
$all_fines = array_filter($all_fines, function ($item) use ($month_start, $month_end) {
    $date = isset($item->date) ? strtotime($item->date) : false;
    return $date && $date >= strtotime($month_start) && $date <= strtotime($month_end);
});

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
    $today = new DateTime();
    $start = new DateTime($month_start);
    $end = new DateTime($month_end);
    $now_month = $today->format('Y-m');
    $target_month = (new DateTime($month_start))->format('Y-m');
    // Lấy cấu hình làm việc thứ 7 từ công ty
    $company_info = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}aerp_hrm_company_info LIMIT 1");
    $work_saturday = $company_info->work_saturday ?? 'off';
    // Số ngày công chuẩn của cả tháng (dùng để chia lương/ngày)
    $work_days_standard_full_month = 0;
    for ($d = clone $start; $d <= $end; $d->modify('+1 day')) {
        $w = (int)$d->format('N');
        if ($w < 6) {
            $work_days_standard_full_month++;
        } elseif ($w == 6) {
            if ($work_saturday === 'full') {
                $work_days_standard_full_month++;
            } elseif ($work_saturday === 'half') {
                $work_days_standard_full_month += 0.5;
            }
        }
        // Chủ nhật (w==7) luôn nghỉ
    }
    // Số ngày công chuẩn tính đến hiện tại (dùng để tính số ngày công thực tế)
    if ($target_month > $now_month) {
        // Tháng tương lai
        $work_days_standard = 0;
    } elseif ($target_month == $now_month) {
        // Tháng hiện tại: chỉ tính đến hôm nay
        $end_cur = $today;
        $work_days_standard = 0;
        for ($d = clone $start; $d <= $end_cur; $d->modify('+1 day')) {
            $w = (int)$d->format('N');
            if ($w < 6) $work_days_standard++;
        }
    } else {
        // Tháng quá khứ: đủ tháng
        $work_days_standard = $work_days_standard_full_month;
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
    $salary_per_day = ($base + $allowance) / ($work_days_standard_full_month ?: 1);
    $total_salary = $actual_work_days * $salary_per_day + $ot_total * $salary_per_day;
    // Thưởng & phạt thủ công
    $adjustments = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}aerp_hrm_adjustments
        WHERE employee_id = %d AND date_effective BETWEEN %s AND %s
    ", $employee_id, $month_start, $month_end));
    $bonus = 0;
    $deduction = 0;
    $cost_items = []; // This will be used for the calculation breakdown
    foreach ($adjustments as $a) {
        if ($a->type === 'reward') {
            $bonus += floatval($a->amount);
        } elseif ($a->type === 'fine') {
            $deduction += floatval($a->amount);
        }
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
    $calc_data = compact('base', 'allowance', 'work_days_standard_full_month', 'work_days_standard', 'off_days', 'ot_total', 'actual_work_days', 'salary_per_day', 'total_salary', 'bonus', 'deduction', 'advance', 'final_salary', 'cost_items', 'total_kpi', 'kpi_bonus', 'auto_bonus', 'total_points', 'ranking', 'tong_nhan');
}

?>
<!-- Quick Links -->
<?php include(AERP_HRM_PATH . 'frontend/quick-links.php'); ?>

<div class="aerp-hrm-dashboard">
    <!-- Header Profile -->
    <div class="aerp-profile-header">
        <div class="aerp-profile-avatar">
            <?php $initial = mb_strtoupper(mb_substr($employee->full_name, 0, 1)); ?>
            <div class="aerp-avatar-circle"><?= esc_html($initial) ?></div>
        </div>
        <div class="aerp-profile-info">
            <h2><?= esc_html($employee->full_name) ?></h2>
            <div class="aerp-profile-meta">
                <span><i class="dashicons dashicons-id"></i> Mã NV: <?= esc_html($employee->employee_code) ?></span>
                <span><i class="dashicons dashicons-businessman"></i> <?= esc_html(aerp_get_position_name($employee->position_id)) ?></span>
                <span><i class="dashicons dashicons-building"></i> <?= esc_html(aerp_get_department_name($employee->department_id)) ?></span>
            </div>
            <div class="aerp-profile-contact">
                <span><i class="dashicons dashicons-email"></i> <a href="mailto:<?= esc_attr($employee->email) ?>"><?= esc_html($employee->email) ?></a></span>
                <span><i class="dashicons dashicons-calendar"></i> Ngày vào làm: <?= $employee->join_date ? date('d/m/Y', strtotime($employee->join_date)) : '—' ?></span>
                <?php if ($employee->relative_name): ?>
                    <span><i class="dashicons dashicons-groups"></i> Người thân: <?= esc_html($employee->relative_name) ?> (<?= esc_html($employee->relative_relationship) ?> – <?= esc_html($employee->relative_phone) ?>)</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Salary Overview Cards -->
    <div class="aerp-salary-overview" id="aerp-salary-overview">
        <div class="aerp-card aerp-salary-summary">
            <div class="aerp-card-header">
                <h2><i class="dashicons dashicons-money"></i> Tổng quan lương tháng hiện tại (<?= date('m/Y') ?>)</h2>
            </div>
            <?php if ($salary): ?>
                <div class="aerp-salary-stats">
                    <div class="aerp-stat-card">
                        <div class="aerp-stat-icon bg-blue">
                            <i class="dashicons dashicons-money"></i>
                        </div>
                        <div class="aerp-stat-info">
                            <span class="aerp-stat-label">Lương cơ bản</span>
                            <span class="aerp-stat-value"><?= number_format($salary->base_salary, 0, ',', '.') ?> đ</span>
                        </div>
                    </div>

                    <div class="aerp-stat-card">
                        <div class="aerp-stat-icon bg-green">
                            <i class="dashicons dashicons-money-alt"></i>
                        </div>
                        <div class="aerp-stat-info">
                            <span class="aerp-stat-label">Phụ cấp</span>
                            <span class="aerp-stat-value"><?= number_format($config_init->allowance, 0, ',', '.') ?> đ</span>
                        </div>
                    </div>

                    <div class="aerp-stat-card">
                        <div class="aerp-stat-icon bg-orange">
                            <i class="dashicons dashicons-calendar-alt"></i>
                        </div>
                        <div class="aerp-stat-info">
                            <span class="aerp-stat-label">Ngày công chuẩn</span>
                            <span class="aerp-stat-value"><?= $salary->work_days ?></span>
                        </div>
                    </div>
                    <div class="aerp-stat-card">
                        <div class="aerp-stat-icon bg-teal">
                            <i class="dashicons dashicons-calendar-alt"></i>
                        </div>
                        <div class="aerp-stat-info">
                            <span class="aerp-stat-label">Ngày thực tế</span>
                            <span class="aerp-stat-value"><?= $salary->actual_work_days ?></span>
                        </div>
                    </div>

                    <div class="aerp-stat-card">
                        <div class="aerp-stat-icon bg-purple">
                            <i class="dashicons dashicons-star-filled"></i>
                        </div>
                        <div class="aerp-stat-info">
                            <span class="aerp-stat-label">Điểm KPI</span>
                            <span class="aerp-stat-value"><?= esc_html($total_kpi_init) ?></span>
                        </div>
                    </div>
                </div>

                <div class="aerp-salary-total-card">
                    <div class="aerp-total-item">
                        <span>Tổng nhận:</span>
                        <span class="aerp-total-value positive">
                            <?php
                            $tong_nhan = ($salary->base_salary ?? 0)
                                + ($salary->auto_bonus ?? 0)
                                + ($salary->bonus ?? 0)
                                + ($config_init->allowance ?? 0)
                                + ($salary->salary_per_day * $salary->ot_days);
                            echo number_format($tong_nhan, 0, ',', '.') . ' đ';
                            ?>
                        </span>
                    </div>
                    <div class="aerp-total-item">
                        <span>Thực lãnh:</span>
                        <span class="aerp-total-value highlight"><?= number_format($total, 0, ',', '.') ?> đ</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="aerp-no-data">
                    <i class="dashicons dashicons-folder-open"></i>
                    <p>Chưa có dữ liệu lương</p>
                </div>
            <?php endif; ?>
            <form method="get" class="aerp-salary-month-form">
                <input type="hidden" name="page" value="aerp_employee_profile">
                <div class="form-group">
                    <input type="month" id="calc_month" name="calc_month" value="<?= esc_attr($calc_month) ?>">
                    <button type="submit" class="aerp-btn aerp-btn-primary"><i class="dashicons dashicons-calculator"></i> Tính lương</button>
                </div>
            </form>
        </div>
        <?php if ($salary): ?>
            <div class="aerp-card aerp-salary-details">
                <div class="aerp-card-header">
                    <h2><i class="dashicons dashicons-portfolio"></i> Chi tiết lương (<?= date('m/Y') ?>)</h2>
                </div>

                <div class="aerp-detail-sections">
                    <div class="aerp-detail-section">
                        <h3><i class="dashicons dashicons-plus"></i> Các khoản cộng</h3>
                        <ul class="aerp-detail-list">
                            <li>
                                <span>Lương cơ bản</span>
                                <span><?= number_format($salary->base_salary ?? 0, 0, ',', '.') ?> đ</span>
                            </li>
                            <li>
                                <span>Phụ cấp</span>
                                <span><?= number_format($config_init->allowance, 0, ',', '.') ?> đ</span>
                            </li>
                            <li>
                                <span>Thưởng KPI</span>
                                <span class="aerp-text-success">+<?= number_format($kpi_bonus_init, 0, ',', '.') ?> đ</span>
                            </li>
                            <li>
                                <span>Thưởng động</span>
                                <span class="aerp-text-success">+<?= number_format($salary->auto_bonus ?? 0, 0, ',', '.') ?> đ</span>
                            </li>
                            <li>
                                <span>Thưởng khác</span>
                                <span class="aerp-text-success">+<?= number_format($salary->bonus - $kpi_bonus_init ?? 0, 0, ',', '.') ?> đ</span>
                            </li>
                        </ul>
                    </div>

                    <div class="aerp-detail-section">
                        <h3><i class="dashicons dashicons-minus"></i> Các khoản trừ</h3>
                        <ul class="aerp-detail-list">
                            <li>
                                <span>Phạt</span>
                                <span class="aerp-text-danger">-<?= number_format($salary->deduction ?? 0, 0, ',', '.') ?> đ</span>
                            </li>
                            <li>
                                <span>Ứng lương</span>
                                <span class="aerp-text-danger">-<?= number_format($salary->advance_paid ?? 0, 0, ',', '.') ?> đ</span>
                            </li>
                        </ul>
                    </div>

                    <div class="aerp-detail-section">
                        <h3><i class="dashicons dashicons-chart-area"></i> Thông tin khác</h3>
                        <ul class="aerp-detail-list">
                            <li>
                                <span>Xếp loại</span>
                                <span class="badge badge-info"><?= esc_html($salary->ranking ?: '--') ?></span>
                            </li>
                            <li>
                                <span>Điểm chuyên cần</span>
                                <span><?= esc_html($salary->points_total) ?></span>
                            </li>
                            <li>
                                <span>Công/ngày</span>
                                <span><?= isset($salary->salary_per_day) ? number_format($salary->salary_per_day, 0, ',', '.') . ' đ' : '' ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <!-- Cost Breakdown -->
    <div class="aerp-card aerp-cost-breakdown">
        <div class="aerp-card-header">
            <h2><i class="dashicons dashicons-list-view"></i> Chi tiết tăng/giảm tháng hiện tại (<?= date('m/Y') ?>)</h2>
        </div>

        <div class="aerp-cost-items">
            <?php if (!empty($latest_cost_items)): ?>
                <?php foreach ($latest_cost_items as $item): ?>
                    <div class="aerp-cost-item <?= $item['type'] === 'plus' ? 'positive' : 'negative' ?>">
                        <div class="aerp-cost-icon">
                            <?php if ($item['type'] === 'plus'): ?>
                                <i class="dashicons dashicons-plus"></i>
                            <?php else: ?>
                                <i class="dashicons dashicons-minus"></i>
                            <?php endif; ?>
                        </div>
                        <div class="aerp-cost-details">
                            <span><?= esc_html($item['label']) ?></span>
                            <strong><?= ($item['amount'] > 0 ? '+' : '') . number_format($item['amount'], 0, ',', '.') ?> đ</strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="aerp-no-data">
                    <i class="dashicons dashicons-folder-open"></i>
                    <p>Không có chi tiết tăng/giảm trong tháng này.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($calc_data): ?>
        <!-- Salary Calculation Results -->
        <div class="aerp-card aerp-salary-calculation">
            <div class="aerp-card-header">
                <h2><i class="dashicons dashicons-calculator"></i> Tính lương tháng <?= date('m/Y', strtotime($calc_month)) ?></h2>
            </div>

            <div class="aerp-calculation-results">
                <div class="aerp-result-section">
                    <h3><i class="dashicons dashicons-products"></i> Thông tin cơ bản</h3>
                    <div class="aerp-result-grid">
                        <div class="aerp-result-item">
                            <span>Lương cơ bản</span>
                            <strong><?= number_format($calc_data['base'], 0, ',', '.') ?> đ</strong>
                        </div>
                        <div class="aerp-result-item">
                            <span>Phụ cấp</span>
                            <strong><?= number_format($calc_data['allowance'], 0, ',', '.') ?> đ</strong>
                        </div>
                        <div class="aerp-result-item">
                            <span>Tổng ngày công</span>
                            <strong><?= $calc_data['work_days_standard_full_month'] ?></strong>
                        </div>
                        <div class="aerp-result-item">
                            <span>Công/ngày</span>
                            <strong><?= number_format($calc_data['salary_per_day'], 0, ',', '.') ?> đ</strong>
                        </div>
                        <div class="aerp-result-item">
                            <span>Ứng lương</span>
                            <strong class="aerp-text-danger">-<?= number_format($calc_data['advance'], 0, ',', '.') ?> đ</strong>
                        </div>
                    </div>
                </div>

                <div class="aerp-result-section">
                    <h3><i class="dashicons dashicons-calendar"></i> Chấm công</h3>
                    <div class="aerp-result-grid">
                        <div class="aerp-result-item">
                            <span>Ngày nghỉ</span>
                            <strong><?= $calc_data['off_days'] ?></strong>
                        </div>
                        <div class="aerp-result-item">
                            <span>Tăng ca</span>
                            <strong><?= $calc_data['ot_total'] ?></strong>
                        </div>
                        <div class="aerp-result-item">
                            <span>Ngày làm thực tế</span>
                            <strong><?= $calc_data['actual_work_days'] ?></strong>
                        </div>
                    </div>
                </div>

                <div class="aerp-result-section">
                    <h3><i class="dashicons dashicons-tickets-alt"></i> Thưởng & phạt</h3>
                    <div class="aerp-result-grid">
                        <div class="aerp-result-item">
                            <span>Thưởng KPI</span>
                            <strong class="aerp-text-success">+<?= number_format($calc_data['kpi_bonus'], 0, ',', '.') ?> đ</strong>
                        </div>
                        <div class="aerp-result-item">
                            <span>Thưởng khác</span>
                            <strong class="aerp-text-success">+<?= number_format($calc_data['bonus'] - $calc_data['kpi_bonus'] - $calc_data['auto_bonus'], 0, ',', '.') ?> đ</strong>
                        </div>
                        <div class="aerp-result-item">
                            <span>Phạt</span>
                            <strong class="aerp-text-danger">-<?= number_format($calc_data['deduction'], 0, ',', '.') ?> đ</strong>
                        </div>
                    </div>
                </div>

                <div class="aerp-result-section">
                    <h3><i class="dashicons dashicons-media-spreadsheet"></i> Tổng hợp</h3>
                    <div class="aerp-result-totals">
                        <div class="aerp-total-item">
                            <span>Tổng nhận:</span>
                            <strong class="aerp-total-value positive"><?= number_format($calc_data['tong_nhan'], 0, ',', '.') ?> đ</strong>
                        </div>
                        <div class="aerp-total-item">
                            <span>Thực lãnh:</span>
                            <strong class="aerp-total-value highlight"><?= number_format($calc_data['final_salary'], 0, ',', '.') ?> đ</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- Rewards & Fines -->
    <div class="aerp-card aerp-rewards-fines">
        <div class="aerp-card-header">
            <h2><i class="dashicons dashicons-awards"></i> Thưởng & Phạt tháng hiện tại (<?= date('m/Y') ?>)</h2>
            <button type="button" class="aerp-btn aerp-btn-primary" data-open-adjustment-popup>
                <i class="dashicons dashicons-plus"></i> Thêm mới
            </button>
        </div>

        <?php if (!empty($notification)): ?>
            <div id="aerp-hrm-toast" class="aerp-hrm-toast">
                <span><?= esc_html($notification) ?></span>
                <button onclick="closeToast()">X</button>
            </div>
        <?php endif; ?>

        <div class="aerp-rf-tabs">
            <div class="aerp-rf-tab active" data-tab="rewards">
                <i class="dashicons dashicons-awards"></i> Thưởng (<?= count($all_rewards) ?>)
            </div>
            <div class="aerp-rf-tab" data-tab="fines">
                <i class="dashicons dashicons-warning"></i> Phạt (<?= count($all_fines) ?>)
            </div>
        </div>

        <div class="aerp-rf-content active" id="rewards">
            <?php if (empty($all_rewards)): ?>
                <div class="aerp-no-data">
                    <i class="dashicons dashicons-folder-open"></i>
                    <p>Không có mục thưởng</p>
                </div>
            <?php else: ?>
                <div class="aerp-rf-items">
                    <?php foreach ($all_rewards as $r): ?>
                        <div class="aerp-rf-item positive">
                            <div class="aerp-rf-icon">
                                <i class="dashicons dashicons-awards"></i>
                            </div>
                            <div class="aerp-rf-details">
                                <div class="aerp-rf-amount">+<?= number_format($r->amount, 0, ',', '.') ?> đ</div>
                                <div class="aerp-rf-reason"><?= esc_html($r->reason) ?></div>
                                <div class="aerp-rf-meta">
                                    <?php if (!empty($r->date)): ?>
                                        <span class="rf-date"><i class="dashicons dashicons-calendar"></i> <?= date('d/m/Y', strtotime($r->date)) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($r->description)): ?>
                                        <span class="rf-desc"><i class="dashicons dashicons-format-status"></i> <?= esc_html($r->description) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="aerp-rf-content" id="fines">
            <?php if (empty($all_fines)): ?>
                <div class="aerp-no-data">
                    <i class="dashicons dashicons-folder-open"></i>
                    <p>Không có mục phạt</p>
                </div>
            <?php else: ?>
                <div class="aerp-rf-items">
                    <?php foreach ($all_fines as $f): ?>
                        <div class="aerp-rf-item negative">
                            <div class="aerp-rf-icon">
                                <i class="dashicons dashicons-warning"></i>
                            </div>
                            <div class="aerp-rf-details">
                                <div class="aerp-rf-amount">-<?= number_format($f->amount, 0, ',', '.') ?> đ</div>
                                <div class="aerp-rf-reason"><?= esc_html($f->reason) ?></div>
                                <div class="aerp-rf-meta">
                                    <?php if (!empty($f->date)): ?>
                                        <span class="rf-date"><i class="dashicons dashicons-calendar"></i> <?= date('d/m/Y', strtotime($f->date)) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($f->description)): ?>
                                        <span class="rf-desc"><i class="dashicons dashicons-format-status"></i> <?= esc_html($f->description) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Salary Timeline -->
    <?php
    $configs = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}aerp_hrm_salary_config
        WHERE employee_id = %d ORDER BY start_date DESC
    ", $employee_id));
    if ($configs):
    ?>
        <div class="aerp-card aerp-salary-timeline">
            <div class="aerp-card-header">
                <h2><i class="dashicons dashicons-chart-area"></i> Lộ trình lương</h2>
            </div>

            <div class="aerp-timeline-container">
                <?php foreach ($configs as $config): ?>
                    <div class="aerp-timeline-item">
                        <div class="aerp-timeline-date">
                            <?= date('d/m/Y', strtotime($config->start_date)) ?> - <?= date('d/m/Y', strtotime($config->end_date)) ?>
                        </div>
                        <div class="aerp-timeline-content">
                            <div class="aerp-timeline-dot"></div>
                            <div class="aerp-timeline-info">
                                <div class="aerp-timeline-salary">
                                    <i class="dashicons dashicons-money"></i>
                                    <?= number_format($config->base_salary, 0, ',', '.') ?> đ
                                    <?php if ($config->allowance > 0): ?>
                                        <span class="aerp-timeline-allowance">
                                            + <?= number_format($config->allowance, 0, ',', '.') ?> đ phụ cấp
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Popup form thêm thưởng/phạt -->
<div class="aerp-popup" id="aerp-adjustment-popup">
    <div class="aerp-popup-overlay"></div>
    <div class="aerp-popup-content">
        <div class="aerp-popup-header">
            <h3><i class="dashicons dashicons-plus"></i> Thêm thưởng/phạt</h3>
            <button class="aerp-popup-close">&times;</button>
        </div>
        <div class="aerp-popup-body">
            <form method="post" class="aerp-form">
                <?php wp_nonce_field('aerp_add_adjustment_action', 'aerp_add_adjustment_nonce'); ?>

                <div class="form-group">
                    <label for="adjustment-type"><i class="dashicons dashicons-tag"></i> Loại</label>
                    <select class="aerp-hrm-custom-select" id="adjustment-type" name="type" required>
                        <option value="">-- Chọn loại --</option>
                        <option value="reward">Thưởng</option>
                        <option value="fine">Phạt</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="adjustment-amount"><i class="dashicons dashicons-money"></i> Số tiền</label>
                    <input type="number" id="adjustment-amount" name="amount" placeholder="Nhập số tiền" required>
                </div>

                <div class="form-group">
                    <label for="adjustment-reason"><i class="dashicons dashicons-format-status"></i> Lý do</label>
                    <input type="text" id="adjustment-reason" name="reason" placeholder="Nhập lý do" required>
                </div>

                <div class="form-group">
                    <label for="adjustment-date"><i class="dashicons dashicons-calendar"></i> Ngày hiệu lực</label>
                    <input type="date" id="adjustment-date" name="date_effective" required>
                </div>

                <div class="form-group">
                    <label for="adjustment-description"><i class="dashicons dashicons-edit"></i> Ghi chú</label>
                    <textarea id="adjustment-description" name="description" rows="3" placeholder="Nhập ghi chú (nếu có)"></textarea>
                </div>

                <div class="aerp-form-actions">
                    <button type="submit" name="aerp_add_adjustment" class="aerp-btn aerp-btn-primary">
                        <i class="dashicons dashicons-yes"></i> Lưu lại
                    </button>
                    <button type="button" class="aerp-btn aerp-btn-secondary aerp-popup-close">
                        <i class="dashicons dashicons-no"></i> Hủy bỏ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    jQuery(document).ready(function($) {
        // Toggle tabs
        $('.aerp-rf-tab').on('click', function() {
            $('.aerp-rf-tab').removeClass('active');
            $(this).addClass('active');

            $('.aerp-rf-content').removeClass('active');
            $('#' + $(this).data('tab')).addClass('active');
        });
    });
</script>