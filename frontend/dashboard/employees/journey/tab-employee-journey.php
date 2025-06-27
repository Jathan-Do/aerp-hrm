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

?>
<style>
    .aerp-timeline {
        list-style: none;
        padding-left: 0;
        border-left: 4px solid #37B34A;
        margin-left: 30px;
        position: relative;
    }

    .aerp-timeline li {
        position: relative;
        padding: 0 0 30px 40px;
        min-height: 60px;
    }

    .aerp-timeline li:last-of-type {
        padding-bottom: 0;
    }
    .timeline-icon {
        position: absolute;
        left: -22px;
        top: 0;
        width: 40px;
        height: 40px;
        background: #fff;
        color: #37B34A;
        border: 3px solid #37B34A;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: bold;
        box-shadow: 0 2px 8px rgba(55, 179, 74, 0.08);
        transition: box-shadow 0.2s;
        z-index: 2;
    }

    .aerp-timeline li:hover .timeline-icon {
        box-shadow: 0 4px 16px rgba(55, 179, 74, 0.18);
        background: #eafff0;
    }

    .timeline-content {
        background: #fff;
        padding: 16px 18px 12px 18px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        margin-bottom: 0;
        position: relative;
        transition: box-shadow 0.2s, border 0.2s;
    }

    .aerp-timeline li:hover .timeline-content {
        box-shadow: 0 6px 24px rgba(55, 179, 74, 0.10);
        border: 1.5px solid #37B34A;
    }

    .timeline-date {
        font-size: 13px;
        color: #37B34A;
        margin-top: 8px;
        font-weight: 500;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .timeline-date:before {
        content: '\1F552';
        /* clock emoji */
        font-size: 14px;
        margin-right: 2px;
        opacity: 0.7;
    }

    @media (max-width: 600px) {
        .aerp-timeline {
            margin-left: 10px;
            border-left-width: 2px;
        }

        .timeline-icon {
            left: -15px;
            width: 28px;
            height: 28px;
            font-size: 15px;
        }

        .timeline-content {
            padding: 10px 8px 8px 10px;
            border-radius: 6px;
        }

        .aerp-timeline li {
            position: relative;
            padding: 0 0 30px 20px;
            min-height: 60px;
        }
    }
</style>