<?php
if (!current_user_can('manage_options')) return;

$salary_id = absint($_GET['view'] ?? 0);
if (!$salary_id) {
    echo '<div class="notice notice-error"><p>Thiếu ID bảng lương.</p></div>';
    return;
}

global $wpdb;

$row = $wpdb->get_row("
    SELECT s.*, e.full_name, e.employee_code, e.email, e.bank_name, e.bank_account
    FROM {$wpdb->prefix}aerp_hrm_salaries s
    LEFT JOIN {$wpdb->prefix}aerp_hrm_employees e ON s.employee_id = e.id
    WHERE s.id = {$salary_id}
");

if (!$row) {
    echo '<div class="notice notice-warning"><p>Không tìm thấy bản ghi lương.</p></div>';
    return;
}

$total = $row->base_salary + $row->bonus + $row->auto_bonus - $row->deduction - $row->advance_paid;
?>

<div class="wrap">
    <h1>Lương chi tiết: <?= esc_html($row->full_name) ?> – <?= esc_html($row->salary_month) ?></h1>
    <table class="widefat striped">
        <tbody>
            <tr>
                <th>Mã NV</th>
                <td><?= esc_html($row->employee_code) ?></td>
            </tr>
            <tr>
                <th>Tháng</th>
                <td><?= date('m/Y', strtotime($row->salary_month)) ?></td>
            </tr>
            <tr>
                <th>Ngân hàng</th>
                <td><?= esc_html($row->bank_name ?: '—') ?></td>
            </tr>
            <tr>
                <th>Số tài khoản</th>
                <td><?= esc_html($row->bank_account ?: '—') ?></td>
            </tr>

            <tr>
                <th>Lương cơ bản</th>
                <td><?= number_format($row->base_salary, 0, ',', '.') ?> đ</td>
            </tr>
            <tr>
                <th>Thưởng</th>
                <td><?= number_format($row->bonus + $row->auto_bonus, 0, ',', '.') ?> đ</td>
            </tr>
            <tr>
                <th>Phạt</th>
                <td><?= number_format($row->deduction, 0, ',', '.') ?> đ</td>
            </tr>
            <tr>
                <th>Tạm ứng</th>
                <td><?= number_format($row->advance_paid, 0, ',', '.') ?> đ</td>
            </tr>
            <tr>
                <th>Điểm tháng</th>
                <td><?= esc_html($row->points_total) ?></td>
            </tr>
            <tr>
                <th>Xếp loại</th>
                <td><?= esc_html($row->ranking ?: '--') ?></td>
            </tr>
            <tr>
                <th><strong>Tổng nhận</strong></th>
                <td><strong><?= number_format($total, 0, ',', '.') ?> đ</strong></td>
            </tr>
        </tbody>
    </table>

    <p>
        <a href="<?= admin_url('admin.php?page=aerp_salary_summary') ?>" class="button">← Quay lại bảng lương</a>
    </p>
</div>