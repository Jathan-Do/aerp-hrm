<?php
if (!defined('ABSPATH')) exit;
?>

<div class="aerp-filter-wrap" style="display: flex; flex-wrap: wrap; gap: 16px; margin-top: 20px;">
    <input type="hidden" name="page" value="aerp_employees">

    <div>
        <label for="filter_status"><strong>Trạng thái</strong></label><br>
        <select name="status" id="filter_status">
            <option value="">-- Tất cả --</option>
            <option value="active" <?= selected($status, 'active') ?>>Đang làm</option>
            <option value="off" <?= selected($status, 'off') ?>>Nghỉ việc</option>
        </select>
    </div>

    <div>
        <label for="filter_work_location"><strong>Chi nhánh</strong></label><br>
        <select name="work_location" id="filter_work_location">
            <?php aerp_safe_select_options($work_locations, $work_location, 'id', 'name', true); ?>
        </select>
    </div>

    <div>
        <label for="filter_department"><strong>Phòng ban</strong></label><br>
        <select name="department" id="filter_department">
            <?php aerp_safe_select_options($departments, $department, 'id', 'name', true); ?>
        </select>
    </div>

    <div>
        <label for="filter_position"><strong>Chức vụ</strong></label><br>
        <select name="position" id="filter_position">
            <?php aerp_safe_select_options($positions, $position, 'id', 'name', true); ?>
        </select>
    </div>

    <div>
        <label for="filter_birthday_month"><strong>Sinh nhật</strong></label><br>
        <select name="birthday_month" id="filter_birthday_month">
            <option value="">-- Tất cả --</option>
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= selected((int)$birthday, $i) ?>>Tháng <?= $i ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <div>
        <label><strong>Ngày vào làm</strong></label><br>
        <input type="date" name="join_date_from" value="<?= esc_attr($join_from) ?>"> →
        <input type="date" name="join_date_to" value="<?= esc_attr($join_to) ?>">
    </div>

    <div>
        <label><strong>Ngày nghỉ</strong></label><br>
        <input type="date" name="off_date_from" value="<?= esc_attr($off_from) ?>"> →
        <input type="date" name="off_date_to" value="<?= esc_attr($off_to) ?>">
    </div>


    <div style="display: flex; align-items: end; gap: 8px;">
        <?php submit_button('Lọc', '', 'filter_action', false); ?>
        <a href="<?= esc_url(remove_query_arg([
                        'status',
                        'work_location',
                        'department',
                        'position',
                        'birthday_month',
                        'join_date_from',
                        'join_date_to',
                        'off_date_from',
                        'off_date_to',
                        'filter_action',
                        'paged'
                    ])) ?>" class="button delete" id="aerp-reset-filter">Xoá bộ lọc</a>
    </div>

</div>