<?php

/**
 * Shortcode: [aerp_manager_dashboard]
 * Professional Manager Dashboard with Enhanced UI
 */

function aerp_manager_dashboard()
{
    if (!aerp_user_has_role(get_current_user_id(), 'department_lead')) {
        return '<div class="aerp-error">Bạn không có quyền truy cập trang này.</div>';
    }

    ob_start();
?>
    <div class="aerp-manager-dashboard">
        <!-- Header with Quick Stats -->
        <div class="dashboard-header">
            <div class="header-stats">
                <div class="stat-card primary">
                    <div class="stat-icon"><i class="dashicons dashicons-groups"></i></div>
                    <div class="stat-info">
                        <span class="stat-label">Nhân viên</span>
                        <span class="stat-value"><?php echo aerp_count_team_members(); ?></span>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon"><i class="dashicons dashicons-yes"></i></div>
                    <div class="stat-info">
                        <span class="stat-label">Hoàn thành task</span>
                        <span class="stat-value"><?php echo aerp_count_completed_tasks(); ?></span>
                    </div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon"><i class="dashicons dashicons-clock"></i></div>
                    <div class="stat-info">
                        <span class="stat-label">Đang làm việc</span>
                        <span class="stat-value"><?php echo aerp_count_working_employees(); ?></span>
                    </div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-icon"><i class="dashicons dashicons-no-alt"></i></div>
                    <div class="stat-info">
                        <span class="stat-label">Task trễ hạn</span>
                        <span class="stat-value"><?php echo aerp_count_overdue_tasks(); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="dashboard-content">
            <!-- Left Column -->
            <div class="content-left">
                <!-- Team Performance Chart -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Hiệu suất team (7 ngày)</h3>
                        <div class="card-actions">
                            <select class="time-filter">
                                <option value="7">7 ngày</option>
                                <option value="30">30 ngày</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="performance-chart">
                            <!-- Chart will be rendered by JavaScript -->
                            <canvas id="teamPerformanceChart"></canvas>


                        </div>
                    </div>
                </div>

                <!-- Task Status Overview -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Phân bổ công việc</h3>
                    </div>
                    <div class="card-body">
                        <div class="task-status-grid">
                            <?php aerp_render_task_status_overview(); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="content-right">
                <!-- Team Members List -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Thành viên team</h3>
                        <div class="card-actions">
                            <input type="text" class="search-input" placeholder="Tìm nhân viên...">
                        </div>
                    </div>
                    <div class="card-body team-list">
                        <div class="team-members-list">
                            <?php aerp_render_enhanced_team_members(); ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Hoạt động gần đây</h3>
                    </div>
                    <div class="card-body activity">
                        <div class="activity-feed">
                            <?php aerp_render_recent_activities(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full-width Bottom Section -->
        <div class="dashboard-bottom">
            <!-- Detailed Task List -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Danh sách công việc</h3>
                    <div class="card-actions">
                        <select class="task-filter">
                            <option value="all">Tất cả</option>
                            <option value="today">Hôm nay</option>
                            <option value="overdue">Trễ hạn</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="task-table-wrapper">
                        <?php aerp_render_task_table(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .aerp-manager-dashboard {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #2d3748;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-header {
            margin-bottom: 24px;
        }

        .header-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-card.primary {
            border-left: 4px solid #3490dc;
        }

        .stat-card.success {
            border-left: 4px solid #38a169;
        }

        .stat-card.warning {
            border-left: 4px solid #d69e2e;
        }

        .stat-card.danger {
            border-left: 4px solid #e53e3e;
        }

        .stat-icon {
            font-size: 24px;
            margin-right: 16px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(52, 144, 220, 0.1);
            color: #3490dc;
        }

        .stat-card.success .stat-icon {
            background: rgba(56, 161, 105, 0.1);
            color: #38a169;
        }

        .stat-card.warning .stat-icon {
            background: rgba(214, 158, 46, 0.1);
            color: #d69e2e;
        }

        .stat-card.danger .stat-icon {
            background: rgba(229, 62, 62, 0.1);
            color: #e53e3e;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 14px;
            color: #718096;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
        }

        .dashboard-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 24px;
            margin-bottom: 24px;
        }

        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            height: 400px;
            overflow-y: hidden;
            /* padding-bottom: 15px; */
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
            height: 100%;
        }

        .card-body.team-list,
        .card-body.activity {
            max-height: 300px;
            overflow-y: auto;
        }

        .performance-chart {
            height: 300px;
        }

        .task-status-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .task-status-item {
            background: #f7fafc;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }

        .task-status-count {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .task-status-label {
            font-size: 14px;
            color: #718096;
        }

        .team-members-list {
            display: grid;
            gap: 12px;
        }

        .team-member-card {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            background: #f7fafc;
            transition: background 0.2s;
        }

        .team-member-card:hover {
            background: #ebf8ff;
        }

        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 12px;
            background: #bee3f8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3182ce;
        }

        .member-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .member-info {
            flex: 1;
        }

        .member-name {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .member-position {
            font-size: 12px;
            color: #718096;
        }

        .member-status {
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .status-online {
            background: #c6f6d5;
            color: #38a169;
        }

        .status-offline {
            background: #fed7d7;
            color: #e53e3e;
        }

        .activity-feed {
            display: grid;
            gap: 12px;
        }

        .activity-item {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .activity-icon {
            margin-right: 12px;
            color: #718096;
        }

        .activity-content {
            flex: 1;
            font-size: 14px;
        }

        .activity-time {
            font-size: 12px;
            color: #a0aec0;
        }

        .task-table-wrapper {
            overflow-x: auto;
            height: 350px
        }

        .task-table {
            width: 100%;
            border-collapse: collapse;
        }

        .task-table th {
            text-align: left;
            padding: 12px 16px;
            background: #f7fafc;
            font-weight: 600;
            font-size: 14px;
        }

        .task-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #edf2f7;
        }

        .task-table tbody {
            max-height: 300px;


            overflow-y: auto;
        }

        .task-priority {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 500;
        }

        .priority-high {
            background: #fed7d7;
            color: #e53e3e;
        }

        .priority-medium {
            background: #feebcb;
            color: #dd6b20;
        }

        .priority-low {
            background: #c6f6d5;
            color: #38a169;
        }

        .task-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-completed {
            background: #c6f6d5;
            color: #38a169;
        }

        .status-in-progress {
            background: #feebcb;
            color: #dd6b20;
        }

        .status-overdue {
            background: #fed7d7;
            color: #e53e3e;
        }

        .status-pending {
            background: #ebf8ff;
            color: #3182ce;
        }

        @media (max-width: 1024px) {
            .dashboard-content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .header-stats {
                grid-template-columns: 1fr 1fr;
            }

            .task-status-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .header-stats {
                grid-template-columns: 1fr;
            }

            .task-status-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <?php if (!is_admin()): ?>
        <script>
            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        </script>
    <?php endif; ?>

    <script>
        jQuery(document).ready(function($) {
            var chart;
            if (typeof Chart !== 'undefined') {
                var ctx = document.getElementById('teamPerformanceChart').getContext('2d');
                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: [{
                                label: 'Task hoàn thành',
                                data: [],
                                backgroundColor: 'rgba(56, 161, 105, 0.7)',
                                borderColor: 'rgba(56, 161, 105, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Task trễ hạn',
                                data: [],
                                backgroundColor: 'rgba(229, 62, 62, 0.7)',
                                borderColor: 'rgba(229, 62, 62, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Task đã giao',
                                data: [],
                                backgroundColor: 'rgba(49, 130, 206, 0.7)',
                                borderColor: 'rgba(49, 130, 206, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                ticks: {
                                    autoSkip: false,
                                    callback: function(value, index, values) {
                                        var label = this.getLabelForValue(value);
                                        if (label && label.length === 10) {
                                            return label.substr(8,2) + '/' + label.substr(5,2);
                                        }
                                        return label;
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });

                // Hàm load dữ liệu
                function loadChartData(days) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'aerp_get_team_performance',
                            days: days
                        },
                        success: function(response) {
                            if (response.success) {
                                chart.data.labels = response.data.labels;
                                chart.data.datasets[0].data = response.data.done;
                                chart.data.datasets[1].data = response.data.failed;
                                chart.data.datasets[2].data = response.data.assigned;
                                chart.update();
                            }
                        }
                    });
                }

                // Load dữ liệu ban đầu
                loadChartData(7);

                // Lắng nghe sự kiện thay đổi filter
                $('.time-filter').change(function() {
                    var days = $(this).val();
                    loadChartData(days);
                });
            }

            // Search functionality
            $('.search-input').on('input', function() {
                var searchTerm = $(this).val().toLowerCase();
                $('.team-member-card').each(function() {
                    var memberName = $(this).find('.member-name').text().toLowerCase();
                    $(this).toggle(memberName.includes(searchTerm));
                });
            });

            // Filter tasks
            $('.task-filter').change(function() {
                var filter = $(this).val();
                $('.task-table tbody tr').each(function() {
                    var $row = $(this);
                    var status = $row.find('.task-status').text().toLowerCase();
                    var deadline = $row.find('td:nth-child(4)').text(); // lấy hạn chót

                    var show = true;
                    if (filter === 'today') {
                        var today = new Date();
                        var d = today.getDate().toString().padStart(2, '0') + '/' +
                            (today.getMonth() + 1).toString().padStart(2, '0') + '/' +
                            today.getFullYear();
                        show = deadline === d;
                    } else if (filter === 'overdue') {
                        show = status.includes('trễ hạn');
                    }
                    $row.toggle(show || filter === 'all');
                });
            });
        });
    </script>
<?php
    return ob_get_clean();
}
add_shortcode('aerp_manager_dashboard', 'aerp_manager_dashboard');

// ======================
// HELPER FUNCTIONS
// ======================

function aerp_count_team_members()
{
    $user_id = get_current_user_id();
    global $wpdb;
    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_hrm_departments WHERE manager_id = %d",
        $user_id
    ));
    if (empty($departments)) return 0;
    $in = implode(',', array_map('intval', $departments));
    return $wpdb->get_var("
        SELECT COUNT(*) 
        FROM {$wpdb->prefix}aerp_hrm_employees 
        WHERE department_id IN ($in) AND status = 'active'
    ") ?: 0;
}

function aerp_count_completed_tasks()
{
    $user_id = get_current_user_id();
    global $wpdb;
    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_hrm_departments WHERE manager_id = %d",
        $user_id
    ));
    if (empty($departments)) return 0;
    $in = implode(',', array_map('intval', $departments));
    return $wpdb->get_var("
        SELECT COUNT(*) 
        FROM {$wpdb->prefix}aerp_hrm_tasks t
        INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON t.employee_id = e.id
        WHERE e.department_id IN ($in) AND t.status = 'done'
    ") ?: 0;
}

function aerp_count_working_employees()
{
    $user_id = get_current_user_id();
    global $wpdb;
    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_hrm_departments WHERE manager_id = %d",
        $user_id
    ));
    if (empty($departments)) return 0;
    $in = implode(',', array_map('intval', $departments));
    return $wpdb->get_var("
        SELECT COUNT(DISTINCT a.employee_id)
        FROM {$wpdb->prefix}aerp_hrm_attendance a
        INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON a.employee_id = e.id
        WHERE e.department_id IN ($in) AND DATE(a.work_date) = CURDATE()
    ") ?: 0;
}

function aerp_count_overdue_tasks()
{
    $user_id = get_current_user_id();
    global $wpdb;
    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_hrm_departments WHERE manager_id = %d",
        $user_id
    ));
    if (empty($departments)) return 0;
    $in = implode(',', array_map('intval', $departments));
    return $wpdb->get_var("
        SELECT COUNT(*) 
        FROM {$wpdb->prefix}aerp_hrm_tasks t
        INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON t.employee_id = e.id
        WHERE e.department_id IN ($in) AND t.status != 'done' AND t.deadline < NOW()
    ") ?: 0;
}

function aerp_render_task_status_overview()
{
    $user_id = get_current_user_id();
    global $wpdb;
    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_hrm_departments WHERE manager_id = %d",
        $user_id
    ));
    if (empty($departments)) {
        echo '<div class="task-status-item">
            <div class="task-status-count">0</div>
            <div class="task-status-label">Không có dữ liệu</div>
        </div>';
        return;
    }
    $in = implode(',', array_map('intval', $departments));

    $statuses = [
        'done' => ['label' => 'Hoàn thành', 'color' => 'success'],
        // 'in_progress' => ['label' => 'Đang làm', 'color' => 'warning'],
        'overdue' => ['label' => 'Trễ hạn', 'color' => 'danger'],
        'assigned' => ['label' => 'Đã giao', 'color' => 'info']
    ];

    foreach ($statuses as $status => $info) {
        $count = $wpdb->get_var($wpdb->prepare(
            "
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}aerp_hrm_tasks t
            INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON t.employee_id = e.id
            WHERE e.department_id IN ($in) AND t.status = %s",
            $status === 'overdue' ? 'assigned' : $status
        ));

        if ($status === 'overdue') {
            $count = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM {$wpdb->prefix}aerp_hrm_tasks t
                INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON t.employee_id = e.id
                WHERE e.department_id IN ($in) AND t.status != 'done' AND t.deadline < NOW()
            ");
        }

        echo '<div class="task-status-item">
            <div class="task-status-count">' . ($count ?: 0) . '</div>
            <div class="task-status-label">' . esc_html($info['label']) . '</div>
        </div>';
    }
}

function aerp_render_enhanced_team_members()
{
    $user_id = get_current_user_id();
    global $wpdb;
    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_hrm_departments WHERE manager_id = %d",
        $user_id
    ));
    if (empty($departments)) {
        echo '<p class="aerp-no-data">Chưa có nhân viên nào</p>';
        return;
    }
    $in = implode(',', array_map('intval', $departments));

    $members = $wpdb->get_results("
        SELECT e.*, 
               p.name as position_name,
               d.name as department_name,
               (SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_tasks t 
                WHERE t.employee_id = e.id AND t.status = 'done') as completed_tasks,
               (SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_tasks t 
                WHERE t.employee_id = e.id AND t.status != 'done' AND t.deadline < NOW()) as overdue_tasks,
               (SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_attendance a 
                WHERE a.employee_id = e.id AND DATE(a.work_date) = CURDATE()) as today_attendance
        FROM {$wpdb->prefix}aerp_hrm_employees e
        LEFT JOIN {$wpdb->prefix}aerp_hrm_positions p ON e.position_id = p.id
        LEFT JOIN {$wpdb->prefix}aerp_hrm_departments d ON e.department_id = d.id
        WHERE e.department_id IN ($in) AND e.status = 'active'
        ORDER BY e.full_name ASC
    ");

    if (empty($members)) {
        echo '<p class="aerp-no-data">Chưa có nhân viên nào</p>';
        return;
    }

    foreach ($members as $member) {
        $avatar = get_avatar_url($member->user_id, ['size' => 80]);
        $status_class = $member->today_attendance > 0 ? 'status-online' : 'status-offline';
        $status_text = $member->today_attendance > 0 ? 'Đang làm việc' : 'Nghỉ hôm nay';

        echo '<div class="team-member-card">
            <div class="member-avatar">
                <img src="' . esc_url($avatar) . '" alt="' . esc_attr($member->full_name) . '">
            </div>
            <div class="member-info">
                <div class="member-name">' . esc_html($member->full_name) . '</div>
                <div class="member-position">' . esc_html($member->position_name) . '</div>
                <div class="member-department">' . esc_html($member->department_name) . '</div>
            </div>
            <div class="member-stats">
                <div class="stat-item" title="Task hoàn thành">
                    <i class="dashicons dashicons-yes"></i> ' . $member->completed_tasks . '
                </div>
                <div class="stat-item" title="Task trễ hạn">
                    <i class="dashicons dashicons-warning"></i> ' . $member->overdue_tasks . '
                </div>
                <div class="member-status ' . $status_class . '">
                    ' . $status_text . '
                </div>
            </div>
        </div>';
    }
}

function aerp_render_recent_activities()
{
    $user_id = get_current_user_id();
    global $wpdb;
    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_hrm_departments WHERE manager_id = %d",
        $user_id
    ));
    if (empty($departments)) {
        echo '<p class="aerp-no-data">Chưa có hoạt động nào</p>';
        return;
    }
    $in = implode(',', array_map('intval', $departments));

    $activities = $wpdb->get_results("
        SELECT 
            'task' as type,
            t.task_title as title,
            t.created_at,
            e.full_name as employee_name,
            t.status,
            NULL as adjustment_type
        FROM {$wpdb->prefix}aerp_hrm_tasks t
        INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON t.employee_id = e.id
        WHERE e.department_id IN ($in)
        UNION ALL
        SELECT 
            'attendance' as type,
            CONCAT('Chấm công ngày ', DATE_FORMAT(a.work_date, '%d/%m/%Y')) as title,
            a.created_at,
            e.full_name as employee_name,
            NULL as status,
            NULL as adjustment_type
        FROM {$wpdb->prefix}aerp_hrm_attendance a
        INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON a.employee_id = e.id
        WHERE e.department_id IN ($in)
        UNION ALL
        SELECT 
            'adjustment' as type,
            CONCAT('Điều chỉnh: ', a.reason) as title,
            a.created_at,
            e.full_name as employee_name,
            NULL as status,
            a.type as adjustment_type
        FROM {$wpdb->prefix}aerp_hrm_adjustments a
        INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON a.employee_id = e.id
        WHERE e.department_id IN ($in)
        ORDER BY created_at DESC
        LIMIT 10
    ");

    if (empty($activities)) {
        echo '<p class="aerp-no-data">Chưa có hoạt động nào</p>';
        return;
    }

    foreach ($activities as $activity) {
        $icon = '';
        $color = '';

        if ($activity->type === 'task') {
            $icon = 'dashicons-clipboard';
            $color = '#3182ce';
        } elseif ($activity->type === 'attendance') {
            $icon = 'dashicons-calendar-alt';
            $color = '#38a169';
        } elseif ($activity->type === 'adjustment') {
            $icon = 'dashicons-money';
            $color = $activity->adjustment_type === 'reward' ? '#38a169' : '#e53e3e';
        }
        echo '<div class="activity-item">
            <div class="activity-icon">
                <span class="dashicons ' . $icon . '" style="color: ' . $color . '"></span>
            </div>
            <div class="activity-content">
                <div>' . esc_html($activity->title) . '</div>
                <div class="activity-meta">
                    <span>' . esc_html($activity->employee_name) . '</span>
                </div>
            </div>
            <div class="activity-time">' .
            human_time_diff((new DateTime($activity->created_at, new DateTimeZone('Asia/Ho_Chi_Minh')))->getTimestamp(),
                current_time('timestamp')
            ) . ' trước
            </div>
        </div>';
    }
}

function aerp_render_task_table()
{
    $user_id = get_current_user_id();
    global $wpdb;
    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_hrm_departments WHERE manager_id = %d",
        $user_id
    ));
    if (empty($departments)) {
        echo '<p class="aerp-no-data">Chưa có công việc nào</p>';
        return;
    }
    $in = implode(',', array_map('intval', $departments));

    $tasks = $wpdb->get_results("
        SELECT 
            t.*,
            e.full_name as employee_name,
            p.name as position_name,
            d.name as department_name,
            CASE 
                WHEN t.status = 'done' THEN 'completed'
                WHEN t.deadline < NOW() THEN 'overdue'
                WHEN t.status = 'in_progress' THEN 'in-progress'
                ELSE 'pending'
            END as display_status
        FROM {$wpdb->prefix}aerp_hrm_tasks t
        INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON t.employee_id = e.id
        LEFT JOIN {$wpdb->prefix}aerp_hrm_positions p ON e.position_id = p.id
        LEFT JOIN {$wpdb->prefix}aerp_hrm_departments d ON e.department_id = d.id
        WHERE e.department_id IN ($in)
        ORDER BY t.deadline ASC
        LIMIT 20
    ");

    if (empty($tasks)) {
        echo '<p class="aerp-no-data">Chưa có công việc nào</p>';
        return;
    }

    echo '<table class="task-table">
        <thead>
            <tr>
                <th>Tiêu đề</th>
                <th>Nhân viên</th>
                <th>Phòng ban</th>
                <th>Hạn chót</th>
                <th>Trạng thái</th>
                <th>Điểm</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($tasks as $task) {
        $status_class = 'status-' . $task->display_status;
        $status_text = '';

        switch ($task->display_status) {
            case 'completed':
                $status_text = 'Hoàn thành';
                break;
            case 'overdue':
                $status_text = 'Trễ hạn';
                break;
            case 'in-progress':
                $status_text = 'Đang làm';
                break;
            default:
                $status_text = 'Đã giao';
        }

        echo '<tr>
            <td>
                <div class="task-title">' . esc_html($task->task_title) . '</div>
                <div class="task-desc">' . esc_html(wp_trim_words($task->task_desc, 10)) . '</div>
            </td>
            <td>
                <div class="employee-name">' . esc_html($task->employee_name) . '</div>
                <div class="employee-position">' . esc_html($task->position_name) . '</div>
            </td>
            <td>' . esc_html($task->department_name) . '</td>
            <td>' . date('d/m/Y', strtotime($task->deadline)) . '</td>
            <td><span class="task-status ' . $status_class . '">' . $status_text . '</span></td>
            <td>' . ($task->score ?: '-') . '</td>
        </tr>';
    }

    echo '</tbody></table>';
}

function aerp_get_team_performance_data()
{
    $user_id = get_current_user_id();
    global $wpdb;
    
    $days = isset($_POST['days']) ? intval($_POST['days']) : 7;
    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_hrm_departments WHERE manager_id = %d",
        $user_id
    ));
    
    if (empty($departments)) {
        wp_send_json_error('No departments found');
        return;
    }
    
    $in = implode(',', array_map('intval', $departments));
    
    // Lấy dữ liệu task hoàn thành (done) theo ngày tạo
    $done_tasks = $wpdb->get_results($wpdb->prepare("
        SELECT DATE(t.created_at) as date, COUNT(*) as count
        FROM {$wpdb->prefix}aerp_hrm_tasks t
        INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON t.employee_id = e.id
        WHERE e.department_id IN ($in) 
        AND t.status = 'done'
        AND t.created_at >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
        GROUP BY DATE(t.created_at)
        ORDER BY date ASC
    ", $days));
    
    // Lấy dữ liệu task trễ hạn (failed) theo ngày tạo
    $failed_tasks = $wpdb->get_results($wpdb->prepare("
        SELECT DATE(t.created_at) as date, COUNT(*) as count
        FROM {$wpdb->prefix}aerp_hrm_tasks t
        INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON t.employee_id = e.id
        WHERE e.department_id IN ($in) 
        AND t.status = 'failed'
        AND t.created_at >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
        GROUP BY DATE(t.created_at)
        ORDER BY date ASC
    ", $days));
    
    // Lấy dữ liệu task đã giao (assigned) theo ngày tạo
    $assigned_tasks = $wpdb->get_results($wpdb->prepare("
        SELECT DATE(t.created_at) as date, COUNT(*) as count
        FROM {$wpdb->prefix}aerp_hrm_tasks t
        INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON t.employee_id = e.id
        WHERE e.department_id IN ($in) 
        AND t.status = 'assigned'
        AND t.created_at >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
        GROUP BY DATE(t.created_at)
        ORDER BY date ASC
    ", $days));
    
    // Tạo mảng ngày
    $dates = array();
    $done_data = array();
    $failed_data = array();
    $assigned_data = array();
    
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dates[] = date('d/m', strtotime($date));
        $done_data[] = 0;
        $failed_data[] = 0;
        $assigned_data[] = 0;
    }
    
    // Điền dữ liệu task done
    foreach ($done_tasks as $task) {
        $index = array_search(date('d/m', strtotime($task->date)), $dates);
        if ($index !== false) {
            $done_data[$index] = intval($task->count);
        }
    }
    // Điền dữ liệu task failed
    foreach ($failed_tasks as $task) {
        $index = array_search(date('d/m', strtotime($task->date)), $dates);
        if ($index !== false) {
            $failed_data[$index] = intval($task->count);
        }
    }
    // Điền dữ liệu task assigned
    foreach ($assigned_tasks as $task) {
        $index = array_search(date('d/m', strtotime($task->date)), $dates);
        if ($index !== false) {
            $assigned_data[$index] = intval($task->count);
        }
    }
    
    wp_send_json_success(array(
        'labels' => $dates,
        'done' => $done_data,
        'failed' => $failed_data,
        'assigned' => $assigned_data
    ));
}
add_action('wp_ajax_aerp_get_team_performance', 'aerp_get_team_performance_data');
