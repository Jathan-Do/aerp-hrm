<?php
if (!defined('ABSPATH')) exit;

$journey = new AERP_HRM_Employee_Journey();
$events = $journey->get_timeline($employee_id);

if (empty($events)) {
    echo '<p>⚠️ Chưa có dữ liệu hành trình.</p>';
    return;
}

// Icon & nhãn sự kiện
$icons = [
    'join'          => '👋',
    'promotion'     => '🏆',
    'transfer'      => '🏢',
    'salary_change' => '💸',
    'resign'        => '📤',
];

$labels = [
    'join'          => 'Vào làm',
    'promotion'     => 'Thay đổi chức vụ',
    'transfer'      => 'Chuyển phòng ban',
    'salary_change' => 'Điều chỉnh lương',
    'resign'        => 'Nghỉ việc',
];

// Lấy sẵn danh sách phòng ban và chức vụ
$departments = wp_list_pluck(apply_filters('aerp_get_departments', []), 'name', 'id');
$positions   = wp_list_pluck(apply_filters('aerp_get_positions', []), 'name', 'id');

echo '<ul class="aerp-timeline">';
foreach ($events as $e) {
    $event_type = $e->event_type;
    $icon  = $icons[$event_type] ?? '🔹';
    $label = $labels[$event_type] ?? ucfirst($event_type);

    $old = maybe_unserialize($e->old_value);
    $new = maybe_unserialize($e->new_value);

    // Format lại dữ liệu hiển thị
    if ($event_type === 'transfer') {
        $old = $departments[$old] ?? $old;
        $new = $departments[$new] ?? $new;
    }

    if ($event_type === 'promotion') {
        $old = $positions[$old] ?? $old;
        $new = $positions[$new] ?? $new;
    }

    if ($event_type === 'salary_change') {
        $old = number_format(floatval($old), 0, ',', '.') . ' đ';
        $new = number_format(floatval($new), 0, ',', '.') . ' đ';
    }

    $change = '';
    if (!empty($old) || !empty($new)) {
        $change = sprintf(
            '<br><small><strong>Từ:</strong> %s → <strong>Đến:</strong> %s</small>',
            esc_html($old),
            esc_html($new)
        );
    }

    echo '<li>';
    echo "<div class='timeline-icon'>{$icon}</div>";
    echo "<div class='timeline-content'>";
    echo "<strong>" . esc_html($e->note ?: $label) . "</strong>";
    echo $change;
    echo '<div class="timeline-date">' . date('d/m/Y H:i', strtotime($e->created_at)) . '</div>';
    echo "</div>";
    echo '</li>';
}

echo '</ul>';
