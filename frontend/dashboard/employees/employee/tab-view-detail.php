<?php
if (!isset($employee_id)) {
    $employee_id = absint($_POST['id'] ?? $_GET['id'] ?? 0);
}
$employee = AERP_Frontend_Employee_Manager::get_by_id($employee_id);
$current_user = wp_get_current_user();
if (!$employee) {
    echo '<div class="notice notice-error"><p>Không tìm thấy nhân viên.</p></div>';
    return;
}

$deps = apply_filters('aerp_get_departments', []);
$positions = apply_filters('aerp_get_positions', []);
$dept_map = wp_list_pluck($deps, 'name', 'id');
$pos_map = wp_list_pluck($positions, 'name', 'id');


?>
<div class="aerp-profile-box">
    <div class="row">
        <div class="col-md-6">
            <h2>Thông tin cá nhân</h2>
            <table class="aerp-profile-table">
                <tr>
                    <th>Mã nhân viên:</th>
                    <td><?= esc_html($employee->employee_code) ?></td>
                </tr>
                <tr>
                    <th>Họ tên:</th>
                    <td><strong><?= esc_html($employee->full_name) ?></strong></td>
                </tr>
                <tr>
                    <th>Giới tính:</th>
                    <td><?= esc_html($employee->gender) ?></td>
                </tr>
                <tr>
                    <th>Ngày sinh:</th>
                    <td><?= esc_html($employee->birthday) ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><a href="mailto:<?= esc_attr($employee->email) ?>"><?= esc_html($employee->email) ?></a></td>
                </tr>
                <tr>
                    <th>Điện thoại:</th>
                    <td><a href="tel:<?= esc_attr($employee->phone_number) ?>"><?= esc_html($employee->phone_number) ?></a></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h2>Thông tin công việc</h2>
            <table class="aerp-profile-table">
                <tr>
                    <th>Phòng ban:</th>
                    <td><?= esc_html($dept_map[$employee->department_id] ?? '—') ?></td>
                </tr>
                <tr>
                    <th>Chức vụ:</th>
                    <td><?= esc_html($pos_map[$employee->position_id] ?? '—') ?></td>
                </tr>
                <tr>
                    <th>Ngày vào làm:</th>
                    <td><?= esc_html($employee->join_date) ?></td>
                </tr>
                <tr>
                    <th>Trạng thái:</th>
                    <td><?= esc_html($employee->status) ?></td>
                </tr>
                <tr>
                    <th>Điểm hiện tại:</th>
                    <td><?= esc_html($employee->current_points) ?></td>
            </table>

        </div>
    </div>



    <h2>Ghi chú</h2>
    <div style="background:#f9f9f9; padding:10px; border-left: 3px solid #0073aa;">
        <?= nl2br(esc_html($employee->note)) ?: '<em>Không có ghi chú</em>' ?>
    </div>
</div>
<a href="<?php echo home_url('/aerp-hrm-employees'); ?>" class="btn btn-outline-primary">← Quay lại</a>