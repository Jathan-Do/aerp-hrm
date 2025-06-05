<?php
/**
 * Shortcode: [aerp_manager_dashboard]
 * Dashboard cho quản lý
 */

// Helper functions
function aerp_count_team_members() {
    $user_id = get_current_user_id();
    global $wpdb;
    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_hrm_departments WHERE manager_id = %d",
        $user_id
    ));
    if (empty($departments)) return 0;
    $in = implode(',', array_map('intval', $departments));
    $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_employees WHERE department_id IN ($in) AND status = 'active'");
    return $count ?: 0;
}

function aerp_count_today_tasks() {
    $user_id = get_current_user_id();
    global $wpdb;
    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_hrm_departments WHERE manager_id = %d",
        $user_id
    ));
    if (empty($departments)) return 0;
    $in = implode(',', array_map('intval', $departments));
    $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_tasks t
        INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON t.employee_id = e.id
        WHERE e.department_id IN ($in) AND DATE(t.created_at) = CURDATE()");
    return $count ?: 0;
}

function aerp_count_attendance_today() {
    $user_id = get_current_user_id();
    global $wpdb;
    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_hrm_departments WHERE manager_id = %d",
        $user_id
    ));
    if (empty($departments)) return 0;
    $in = implode(',', array_map('intval', $departments));
    $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aerp_hrm_attendance a
        INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON a.employee_id = e.id
        WHERE e.department_id IN ($in) AND DATE(a.work_date) = CURDATE()");
    return $count ?: 0;
}

function aerp_render_recent_tasks() {
    $user_id = get_current_user_id();
    global $wpdb;
    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_hrm_departments WHERE manager_id = %d",
        $user_id
    ));
    if (empty($departments)) {
        echo '<p class="aerp-no-data">Chưa có task nào</p>';
        return;
    }
    $in = implode(',', array_map('intval', $departments));
    $tasks = $wpdb->get_results("
        SELECT t.*, e.full_name 
        FROM {$wpdb->prefix}aerp_hrm_tasks t
        INNER JOIN {$wpdb->prefix}aerp_hrm_employees e ON t.employee_id = e.id
        WHERE e.department_id IN ($in)
        ORDER BY t.created_at DESC LIMIT 5
    ");
    if (empty($tasks)) {
        echo '<p class="aerp-no-data">Chưa có task nào</p>';
        return;
    }
    echo '<div class="aerp-task-list">';
    foreach ($tasks as $task) {
        $status_class = match($task->status) {
            'done' => 'success',
            'in_progress' => 'warning',
            'assigned' => 'info',
            default => 'secondary'
        };
        ?>
        <div class="aerp-task-item">
            <div class="task-header">
                <span class="task-title"><?php echo esc_html($task->task_title); ?></span>
                <span class="task-status <?php echo $status_class; ?>">
                    <?php echo esc_html($task->status); ?>
                </span>
            </div>
            <div class="task-meta">
                <span class="task-assignee">
                    <i class="dashicons dashicons-admin-users"></i>
                    <?php echo esc_html($task->full_name); ?>
                </span>
                <span class="task-date">
                    <i class="dashicons dashicons-calendar-alt"></i>
                    <?php echo date('d/m/Y', strtotime($task->created_at)); ?>
                </span>
            </div>
        </div>
        <?php
    }
    echo '</div>';
}

function aerp_render_team_members() {
    $user_id = get_current_user_id();
    $department_id = get_user_meta($user_id, 'department_id', true);
    
    global $wpdb;
    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}aerp_hrm_departments WHERE manager_id = %d",
        $user_id
    ));
    
    if (!empty($departments)) {
        $in = implode(',', array_map('intval', $departments));
        $members = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_employees WHERE department_id IN ($in) AND status = 'active'");
    }
    
    if (empty($members)) {
        echo '<p class="aerp-no-data">Chưa có nhân viên nào</p>';
        return;
    }
    
    echo '<div class="aerp-team-grid">';
    foreach ($members as $member) {
        ?>
        <div class="aerp-team-member">
            <div class="member-avatar">
                <?php 
                $avatar = get_user_meta($member->user_id, 'avatar', true);
                if ($avatar) {
                    echo '<img src="' . esc_url($avatar) . '" alt="' . esc_attr($member->full_name) . '">';
                } else {
                    echo '<span class="dashicons dashicons-admin-users"></span>';
                }
                ?>
            </div>
            <div class="member-info">
                <h4><?php echo esc_html($member->full_name); ?></h4>
                <p class="member-position"><?php echo esc_html($member->position); ?></p>
            </div>
        </div>
        <?php
    }
    echo '</div>';
}

// Main shortcode function
function aerp_manager_dashboard() {
    // Kiểm tra quyền
    if (!aerp_user_can(get_current_user_id(), 'task')) {
        return '<div class="aerp-error">Bạn không có quyền truy cập trang này.</div>';
    }

    ob_start();
    ?>
    <div class="aerp-hrm-dashboard">
        <div class="aerp-card">
            <div class="card-header">
                <h2>Dashboard Quản lý</h2>
            </div>
            
            <!-- Thống kê nhanh -->
            <div class="aerp-stats-grid">
                <div class="stat-card">
                    <span class="dashicons dashicons-groups"></span>
                    <h3>Nhân viên</h3>
                    <p class="stat-number"><?php echo aerp_count_team_members(); ?></p>
                </div>
                <div class="stat-card">
                    <span class="dashicons dashicons-clipboard"></span>
                    <h3>Task hôm nay</h3>
                    <p class="stat-number"><?php echo aerp_count_today_tasks(); ?></p>
                </div>
                <div class="stat-card">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <h3>Chấm công</h3>
                    <p class="stat-number"><?php echo aerp_count_attendance_today(); ?></p>
                </div>
            </div>

            <!-- Danh sách task gần đây -->
            <div class="aerp-recent-tasks">
                <h3>Task gần đây</h3>
                <?php aerp_render_recent_tasks(); ?>
            </div>

            <!-- Danh sách nhân viên -->
            <div class="aerp-team-members">
                <h3>Nhân viên trong team</h3>
                <?php aerp_render_team_members(); ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aerp_manager_dashboard', 'aerp_manager_dashboard'); 