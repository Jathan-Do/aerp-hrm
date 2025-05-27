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
        <div class="aerp-hrm-title"><span class="icon">💰</span> Lương tháng gần nhất</div>
        <?php if ($salary): ?>
            <div class="salary-table">
                <div><span>Tháng:</span> <strong><?= date('m/Y', strtotime($salary->salary_month)) ?></strong></div>
                <div><span>Lương cơ bản:</span> <strong class="text-primary"><?= number_format($salary->base_salary, 0, ',', '.') ?> đ</strong></div>
                <div><span>Phụ cấp:</span> <strong><?= number_format($config->allowance, 0, ',', '.') ?> đ</strong></div>
                <div><span>Thưởng KPI / động:</span> <strong><?= number_format($salary->auto_bonus, 0, ',', '.') ?> đ</strong></div>
                <div><span>Tổng ngày công:</span> <strong><?= $work_days ?></strong></div>
                <div><span>Thưởng:</span> <strong class="text-success">+<?= number_format($salary->bonus, 0, ',', '.') ?> đ</strong></div>
                <div><span>Phạt:</span> <strong class="text-danger">-<?= number_format($salary->deduction, 0, ',', '.') ?> đ</strong></div>
                <div><span>Tạm ứng:</span> <strong><?= number_format($salary->advance_paid, 0, ',', '.') ?> đ</strong></div>
                <div><span>Xếp loại:</span> <strong><?= esc_html($salary->ranking ?: '--') ?></strong></div>
                <div><span>Điểm tháng:</span> <strong><?= esc_html($salary->points_total) ?></strong></div>
                <div><span>Tổng điểm KPI:</span> <strong><?= esc_html($total_kpi) ?> (<?= number_format($kpi_bonus, 0, ',', '.') ?> đ)</strong></div>
                <div class="salary-total"><span><strong>Tổng nhận:</strong></span> <strong class="text-total"><?= number_format($total, 0, ',', '.') ?> đ</strong></div>
            </div>
        <?php else: ?>
            <p><em>Chưa có dữ liệu lương.</em></p>
        <?php endif; ?>
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

        <?php
        $rewards = array_filter($adjustments, fn($a) => $a->type === 'reward');
        $fines = array_filter($adjustments, fn($a) => $a->type === 'fine');
        ?>

        <div class="aerp-accordion-group">
            <div class="aerp-accordion-item">
                <button class="aerp-hrm-accordion-header" type="button">
                    🎁 Thưởng (<?= count($rewards) ?> mục)
                    <span class="aerp-hrm-accordion-icon">▼</span>
                </button>
                <div class="aerp-hrm-accordion-body bg-reward">
                    <?php if (empty($rewards)): ?>
                        <p><em>Không có mục thưởng.</em></p>
                    <?php else: ?>
                        <?php foreach ($rewards as $r): ?>
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
                    ⚠️ Phạt (<?= count($fines) ?> mục)
                    <span class="aerp-hrm-accordion-icon">▼</span>
                </button>
                <div class="aerp-hrm-accordion-body bg-fine">
                    <?php if (empty($fines)): ?>
                        <p><em>Không có mục phạt.</em></p>
                    <?php else: ?>
                        <?php foreach ($fines as $f): ?>
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