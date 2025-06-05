<?php
if (!current_user_can('manage_options')) return;

$salary_id = absint($_GET['view'] ?? 0);
if (!$salary_id) {
    echo '<div class="notice notice-error"><p>Thiếu ID bảng lương.</p></div>';
    return;
}

global $wpdb;

// Lấy bản ghi lương và nhân viên
$row = $wpdb->get_row($wpdb->prepare("
    SELECT s.*, e.full_name, e.employee_code, e.email, e.bank_name, e.bank_account, e.position_id, e.department_id, e.join_date, e.relative_name, e.relative_relationship, e.relative_phone
    FROM {$wpdb->prefix}aerp_hrm_salaries s
    LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON s.employee_id = e.id
    WHERE s.id = %d
", $salary_id));

if (!$row) {
    echo '<div class="notice notice-warning"><p>Không tìm thấy bản ghi lương.</p></div>';
    return;
}

$employee_id = $row->employee_id;
$month = date('Y-m', strtotime($row->salary_month));
$month_start = $month . '-01';
$month_end = date('Y-m-t', strtotime($month_start));

// Lấy config lương
$config_init = $wpdb->get_row($wpdb->prepare("
    SELECT * FROM {$wpdb->prefix}aerp_hrm_salary_config
    WHERE employee_id = %d AND start_date <= %s AND end_date >= %s
    ORDER BY start_date DESC LIMIT 1
", $employee_id, $month_start, $month_start));

// Tính toán chi tiết tăng/giảm
$latest_cost_items = [];
$latest_off_days = intval($row->off_days ?? 0);
$latest_ot_days = floatval($row->ot_days ?? 0);
$latest_salary_per_day = floatval($row->salary_per_day ?? 0);
$latest_advance = floatval($row->advance_paid ?? 0);
if ($latest_ot_days > 0 && $latest_salary_per_day > 0) {
    $latest_ot_amount = $latest_ot_days * $latest_salary_per_day;
    $latest_cost_items[] = ['type' => 'plus', 'label' => 'Tăng ca (' . $latest_ot_days . ' ngày)', 'amount' => $latest_ot_amount];
}
if ($latest_off_days > 0 && $latest_salary_per_day > 0) {
    $latest_off_amount = $latest_off_days * $latest_salary_per_day;
    $latest_cost_items[] = ['type' => 'minus', 'label' => 'Nghỉ không lương (' . $latest_off_days . ' ngày)', 'amount' => -$latest_off_amount];
}
if ($latest_advance > 0) {
    $latest_cost_items[] = ['type' => 'minus', 'label' => 'Ứng lương', 'amount' => -$latest_advance];
}

// Lấy thưởng/phạt thủ công
$adjustments = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aerp_hrm_adjustments WHERE employee_id = %d AND date_effective BETWEEN %s AND %s ORDER BY date_effective DESC", $employee_id, $month_start, $month_end));
$rewards = [];
$fines = [];
foreach ($adjustments as $a) {
    if ($a->type === 'reward') {
        $a->date = $a->date_effective ?? '';
        $rewards[] = $a;
    } elseif ($a->type === 'fine') {
        $a->date = $a->date_effective ?? '';
        $fines[] = $a;
    }
}
// Lấy phạt từ bảng vi phạm
$discipline_fines = $wpdb->get_results($wpdb->prepare("
    SELECT dr.fine_amount AS amount, dr.rule_name AS reason, dl.date_violation AS date, '' AS description
    FROM {$wpdb->prefix}aerp_hrm_disciplinary_logs dl
    INNER JOIN {$wpdb->prefix}aerp_hrm_disciplinary_rules dr ON dr.id = dl.rule_id
    WHERE dl.employee_id = %d AND dl.date_violation BETWEEN %s AND %s
", $employee_id, $month_start, $month_end));
// Lấy thưởng tự động
$auto_rewards = $wpdb->get_results($wpdb->prepare("
    SELECT rd.amount AS amount, rd.name AS reason, er.month AS date, er.note AS description
    FROM {$wpdb->prefix}aerp_hrm_employee_rewards er
    INNER JOIN {$wpdb->prefix}aerp_hrm_reward_definitions rd ON rd.id = er.reward_id
    WHERE er.employee_id = %d AND er.month BETWEEN %s AND %s
", $employee_id, $month_start, $month_end));

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

// Tính tổng điểm KPI
$total_kpi = 0;
$kpi_bonus = 0;
$tasks_in_month = AERP_Task_Manager::get_tasks_by_month($employee_id, date('n', strtotime($row->salary_month)), date('Y', strtotime($row->salary_month)));
foreach ($tasks_in_month as $task) {
    if ($task->status === 'done') {
        $total_kpi += (int)($task->score ?? 0);
    }
}
$kpi_setting = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}aerp_hrm_kpi_settings WHERE min_score <= %d ORDER BY min_score DESC LIMIT 1",
    $total_kpi
));
if ($kpi_setting) {
    $kpi_bonus = $kpi_setting->reward_amount;
}

$total = $row->base_salary + $row->bonus + $row->auto_bonus + ($config_init->allowance ?? 0) + ($row->salary_per_day * $row->ot_days) - $row->deduction - $row->advance_paid;

// Lấy lộ trình lương
$configs = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM {$wpdb->prefix}aerp_hrm_salary_config
    WHERE employee_id = %d ORDER BY start_date DESC
", $employee_id));

?>
<style>
    @media print {

        body,
        html {
            background: #fff !important;
        }

        #adminmenumain,
        #wpadminbar,
        #adminmenuwrap,
        #wpfooter,
        .update-nag,
        .notice,
        .aerp-btn,
        .button,
        .aerp-print-hide,
        .aerp-card-header,
        .aerp-rf-tabs {
            display: none !important;
        }

        .aerp-salary-print-table {
            width: 100% !important;
            font-size: 13px;
        }

        .aerp-salary-print-table th,
        .aerp-salary-print-table td {
            border: 1px solid #333;
            padding: 4px 8px;
        }

        .aerp-salary-print-table th {
            background: #f0f0f0;
        }

        .aerp-salary-print-title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }

        .aerp-salary-print-logo {
            text-align: left;
            margin-bottom: 10px;
        }

        .aerp-salary-print-summary {
            margin-bottom: 10px;
        }

        .aerp-salary-print-section-title {
            font-weight: bold;
            background: #f8f8f8;
        }

        .aerp-salary-print-total {
            font-weight: bold;
            background: #e0ffe0;
        }

        .aerp-salary-print-final {
            font-weight: bold;
            background: #e0e7ff;
            color: #d90429;
        }
    }

    .aerp-salary-print-table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 20px;
    }

    .aerp-salary-print-table th,
    .aerp-salary-print-table td {
        border: 1px solid #333;
        padding: 4px 8px;
    }

    .aerp-salary-print-table th {
        background: #f0f0f0;
    }

    .aerp-salary-print-title {
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 10px;
    }

    .aerp-salary-print-logo {
        text-align: left;
        margin-bottom: 10px;
    }

    .aerp-salary-print-summary {
        margin-bottom: 10px;
    }

    .aerp-salary-print-section-title {
        font-weight: bold;
        background: #f8f8f8;
    }

    .aerp-salary-print-total {
        font-weight: bold;
        background: #e0ffe0;
    }

    .aerp-salary-print-final {
        font-weight: bold;
        background: #e0e7ff;
        color: #d90429;
    }

    .aerp-print-hide {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .aerp-print-hide>.button {
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>
<div class="aerp-print-hide">
    <button onclick="window.print()" class="button button-primary"><span class="dashicons dashicons-printer"></span> In PDF</button>
    <a href="<?= admin_url('admin.php?page=aerp_salary_summary') ?>" class="button">← Quay lại bảng lương</a>
</div>
<div class="aerp-salary-print-logo">
    <img src="<?= esc_url(get_option('aerp_company_logo') ?: AERP_HRM_URL . 'assets/images/logo.png') ?>" alt="Logo" style="height:40px;vertical-align:middle;">
</div>
<div class="aerp-salary-print-title">BẢNG LƯƠNG CHI TIẾT THÁNG <?= date('m/Y', strtotime($row->salary_month)) ?></div>
<table class="aerp-salary-print-table">
    <tr>
        <th colspan="4">Thông tin nhân viên</th>
    </tr>
    <tr>
        <td><b>Họ tên</b></td>
        <td><?= esc_html($row->full_name) ?></td>
        <td><b>Mã NV</b></td>
        <td><?= esc_html($row->employee_code) ?></td>
    </tr>
    <tr>
        <td><b>Phòng ban</b></td>
        <td><?= esc_html(aerp_get_department_name($row->department_id)) ?></td>
        <td><b>Chức vụ</b></td>
        <td><?= esc_html(aerp_get_position_name($row->position_id)) ?></td>
    </tr>
    <tr>
        <td><b>Ngày vào làm</b></td>
        <td><?= $row->join_date ? date('d/m/Y', strtotime($row->join_date)) : '—' ?></td>
        <td><b>Xếp loại</b></td>
        <td><?= esc_html($row->ranking ?: '--') ?></td>
    </tr>
    <tr>
        <td><b>Email</b></td>
        <td><?= esc_html($row->email) ?></td>
        <td><b>Ngân hàng</b></td>
        <td><?= esc_html($row->bank_name ?: '—') ?> (<?= esc_html($row->bank_account ?: '—') ?>)</td>
    </tr>
</table>

<table class="aerp-salary-print-table">
    <tr>
        <th colspan="4" class="aerp-salary-print-section-title">Các khoản cộng</th>
    </tr>
    <tr>
        <td>Lương cơ bản</td>
        <td colspan="3"><?= number_format($row->base_salary ?? 0, 0, ',', '.') ?> đ</td>
    </tr>
    <tr>
        <td>Phụ cấp</td>
        <td colspan="3"><?= number_format($config_init->allowance ?? 0, 0, ',', '.') ?> đ</td>
    </tr>
    <tr>
        <td>Thưởng KPI</td>
        <td colspan="3">+<?= number_format($kpi_bonus, 0, ',', '.') ?> đ</td>
    </tr>
    <tr>
        <td>Thưởng động</td>
        <td colspan="3">+<?= number_format($row->auto_bonus ?? 0, 0, ',', '.') ?> đ</td>
    </tr>
    <tr>
        <td>Thưởng khác</td>
        <td colspan="3">+<?= number_format(($row->bonus ?? 0) - $kpi_bonus, 0, ',', '.') ?> đ</td>
    </tr>
    <?php if (!empty($all_rewards)): ?>
        <?php foreach ($all_rewards as $r): ?>
            <tr>
                <td>Thưởng: <?= esc_html($r->reason) ?></td>
                <td><?= number_format($r->amount, 0, ',', '.') ?> đ</td>
                <td colspan="2"><?= !empty($r->description) ? esc_html($r->description) : '' ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<table class="aerp-salary-print-table">
    <tr>
        <th colspan="4" class="aerp-salary-print-section-title">Các khoản trừ</th>
    </tr>
    <tr>
        <td>Phạt</td>
        <td colspan="3">-<?= number_format($row->deduction ?? 0, 0, ',', '.') ?> đ</td>
    </tr>
    <?php if (!empty($all_fines)): ?>
        <?php foreach ($all_fines as $f): ?>
            <tr>
                <td>Phạt: <?= esc_html($f->reason) ?></td>
                <td>-<?= number_format($f->amount, 0, ',', '.') ?> đ</td>
                <td colspan="2"><?= !empty($f->description) ? esc_html($f->description) : '' ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<table class="aerp-salary-print-table">
    <tr>
        <th colspan="4" class="aerp-salary-print-section-title">Chi phí tăng/giảm</th>
    </tr>
    <tr>
        <th>Loại</th>
        <th>Lý do</th>
        <th>Số tiền</th>
        <th>Ghi chú</th>
    </tr>
    <?php if (!empty($latest_cost_items)): ?>
        <?php foreach ($latest_cost_items as $item): ?>
            <tr>
                <td style="color:<?= $item['type'] === 'plus' ? '#198754' : '#d90429' ?>;font-weight:bold;">
                    <?= $item['type'] === 'plus' ? 'Tăng (+)' : 'Giảm (-)' ?>
                </td>
                <td><?= esc_html($item['label']) ?></td>
                <td style="text-align:right;">
                    <?= ($item['amount'] > 0 ? '+' : '-') . number_format(abs($item['amount']), 0, ',', '.') ?> đ
                </td>
                <td></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="4" style="text-align:center;">Không có chi phí tăng/giảm trong tháng này.</td>
        </tr>
    <?php endif; ?>
</table>

<table class="aerp-salary-print-table">
    <tr>
        <th colspan="2" class="aerp-salary-print-section-title">Tổng kết</th>
    </tr>
    <tr class="aerp-salary-print-total">
        <td>Tổng nhận</td>
        <td><?= number_format(($row->base_salary ?? 0)
                + ($row->auto_bonus ?? 0)
                + ($row->bonus ?? 0)
                + ($config_init->allowance ?? 0)
                + ($row->salary_per_day * $row->ot_days), 0, ',', '.') ?> đ</td>
    </tr>
    <tr class="aerp-salary-print-final">
        <td>Thực lãnh</td>
        <td><?= number_format($total, 0, ',', '.') ?> đ</td>
    </tr>
</table>

<?php if (!empty($configs)): ?>
    <table class="aerp-salary-print-table">
        <tr>
            <th colspan="3" class="aerp-salary-print-section-title">Lộ trình lương</th>
        </tr>
        <tr>
            <th>Thời gian</th>
            <th>Lương cơ bản</th>
            <th>Phụ cấp</th>
        </tr>
        <?php foreach ($configs as $config): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($config->start_date)) ?> - <?= date('d/m/Y', strtotime($config->end_date)) ?></td>
                <td><?= number_format($config->base_salary, 0, ',', '.') ?> đ</td>
                <td><?= number_format($config->allowance, 0, ',', '.') ?> đ</td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

</rewritten_file>