<?php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$employee = aerp_get_employee_by_user_id($user_id);
if (!$employee) return;
$employee_id = $employee->id;

// Xóa các thông báo cũ
$clean_url = remove_query_arg(['task_added', 'task_updated', 'task_commented', 'task_edited']);

// Thêm task
if (
    isset($_POST['aerp_add_task']) &&
    check_admin_referer('aerp_add_task_action', 'aerp_add_task_nonce')
) {
    AERP_Task_Manager::add([
        'employee_id' => $employee_id,
        'task_title'  => sanitize_text_field($_POST['task_title']),
        'task_desc'   => sanitize_textarea_field($_POST['task_desc']),
        'deadline'    => sanitize_text_field($_POST['deadline']),
        'score'       => absint($_POST['score']),
        'status'      => 'assigned',
        'created_by'  => $user_id,
    ]);
    aerp_js_redirect(add_query_arg('task_added', '1', $clean_url));
    exit;
}

// Sửa task của chính mình
if (
    isset($_POST['aerp_update_own_task']) &&
    check_admin_referer('aerp_edit_own_task_action', 'aerp_edit_own_task_nonce')
) {
    $task_id = absint($_POST['edit_task_id']);
    $task = AERP_Task_Manager::get_by_id($task_id);
    if ($task && $task->created_by == $user_id && $task->employee_id == $employee_id && $task->status != 'done') {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'aerp_hrm_tasks',
            [
                'task_title' => sanitize_text_field($_POST['edit_task_title']),
                'task_desc'  => sanitize_textarea_field($_POST['edit_task_desc']),
                'deadline'   => sanitize_text_field($_POST['edit_task_deadline']),
                'score'      => absint($_POST['edit_task_score']),
            ],
            ['id' => $task_id]
        );
        aerp_js_redirect(add_query_arg('task_edited', '1', $clean_url));
        exit;
    }
}

// Cập nhật trạng thái
if (
    isset($_POST['aerp_update_task_status']) &&
    check_admin_referer('aerp_update_task_action', 'aerp_update_task_nonce')
) {
    $task_id = absint($_POST['task_id']);
    $status = sanitize_text_field($_POST['status']);
    AERP_Task_Manager::update_status($task_id, $employee_id, $status);
    aerp_js_redirect(add_query_arg('task_updated', '1', $clean_url));
    exit;
}

// Thêm comment
if (
    isset($_POST['aerp_add_task_comment']) &&
    check_admin_referer('aerp_comment_task_action', 'aerp_comment_task_nonce')
) {
    AERP_Task_Manager::add_comment([
        'task_id'  => absint($_POST['task_id']),
        'user_id'  => $user_id,
        'comment'  => sanitize_textarea_field($_POST['comment']),
    ]);
    aerp_js_redirect(add_query_arg('task_commented', '1', $clean_url));
    exit;
}


// Thông báo sau redirect
$notification = '';
foreach (['task_added', 'task_updated', 'task_commented', 'task_edited'] as $msg) {
    if (isset($_GET[$msg])) {
        $notification = ucwords(str_replace('_', ' ', $msg)) . ' thành công';
        break;
    }
}


// $tasks = AERP_Task_Manager::get_tasks_by_employee($employee_id);
$keyword = sanitize_text_field($_GET['keyword'] ?? '');
$status  = sanitize_text_field($_GET['status'] ?? '');
$paged = max(1, get_query_var('paged'));

$limit   = 2;
$offset  = ($paged - 1) * $limit;

$filter = [
    'keyword' => $keyword,
    'status' => $status,
    'limit' => $limit,
    'offset' => $offset,
];

$tasks = AERP_Task_Manager::search_tasks_by_employee($employee_id, $filter);
$total = AERP_Task_Manager::count_tasks_by_employee($employee_id, $filter);
$total_pages = ceil($total / $limit);

?>


<div class="aerp-hrm-task-container">
    <form method="get" class="aerp-hrm-task-filter-form" style="margin-bottom: 20px;">
        <input type="hidden" name="page_id" value="<?= esc_attr(get_the_ID()) ?>">
        <input type="text" name="keyword" placeholder="Tìm kiếm tiêu đề..." value="<?= esc_attr($_GET['keyword'] ?? '') ?>">
        <select name="status" class="aerp-hrm-custom-select">
            <option value="">-- Tất cả trạng thái --</option>
            <option value="assigned" <?= selected($_GET['status'] ?? '', 'assigned') ?>>Đã giao</option>
            <option value="done" <?= selected($_GET['status'] ?? '', 'done') ?>>Hoàn thành</option>
            <option value="failed" <?= selected($_GET['status'] ?? '', 'failed') ?>>Thất bại</option>
        </select>
        <button type="submit" class="button">Lọc</button>
    </form>
    <?php if (!empty($notification)): ?>
        <div id="aerp-hrm-toast" class="aerp-hrm-toast">
            <span><?= esc_html($notification) ?></span>
            <button onclick="closeToast()">X</button>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="aerp-hrm-task-section-title">📋 Danh sách công việc</h2>
        <button type="button" class="aerp-hrm-task-form button" data-open-aerp-hrm-task-popup>Thêm công việc</button>

    </div>

    <?php if ($tasks): foreach ($tasks as $task): ?>
            <div class="aerp-hrm-task-card">
                <div style="display: flex; justify-content: space-between;">
                    <div>
                        <div class="aerp-hrm-task-title"><?= esc_html($task->task_title) ?></div>
                        <div class="aerp-hrm-task-meta">
                            🗓 Deadline: <?= esc_html($task->deadline) ?>
                            <?php if ($task->score): ?>
                                | 📊 Điểm KPI: <?= esc_html($task->score) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($task->created_by == $user_id && $task->status != 'done'): ?>
                        <div>
                            <button class="aerp-hrm-task-form button"
                                onclick='openEditTaskPopup(<?= json_encode(
                                                                [
                                                                    "id" => $task->id,
                                                                    "task_title" => $task->task_title,
                                                                    "task_desc" => $task->task_desc,
                                                                    "deadline" => $task->deadline,
                                                                    "score" => $task->score
                                                                ]
                                                            ) ?>)'>Sửa
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <p><?= esc_html($task->task_desc) ?></p>
                <form method="post" class="aerp-hrm-task-form">
                    <?php wp_nonce_field('aerp_update_task_action', 'aerp_update_task_nonce'); ?>
                    <input type="hidden" name="task_id" value="<?= esc_attr($task->id) ?>">
                    <select name="status" class="aerp-hrm-custom-select">
                        <option value="assigned" <?= selected($task->status, 'assigned') ?>>🟡 Đã giao</option>
                        <option value="done" <?= selected($task->status, 'done') ?>>✅ Hoàn thành</option>
                        <option value="failed" <?= selected($task->status, 'failed') ?>>❌ Thất bại</option>
                    </select>
                    <button class="button-submit" type="submit" name="aerp_update_task_status">Cập nhật trạng thái</button>
                </form>

                <?php $comments = AERP_Task_Manager::get_comments($task->id); ?>
                <?php if (!empty($comments)): ?>
                    <h4 style="margin-top: 20px;">💬 Phản hồi</h4>
                    <ul class="aerp-hrm-task-comments">
                        <?php foreach ($comments as $c): ?>
                            <?php
                            $is_admin = user_can($c->user_id, 'manage_options');
                            $role_label = $is_admin ? 'Quản lý' : 'Nhân viên';
                            $badge_class = $is_admin ? 'aerp-hrm-badge-admin' : 'aerp-hrm-badge-user';
                            ?>
                            <li>
                                <span class="aerp-hrm-task-badge <?= $badge_class ?>"><?= esc_html($role_label) ?></span>
                                <strong><?= esc_html($c->display_name) ?>:</strong>
                                <?= esc_html($c->comment) ?>
                                <em>(<?= date('d/m/Y H:i', strtotime($c->created_at)) ?>)</em>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: #999; font-style: italic; font-size: 16px;">Không có phản hồi nào.</p>
                <?php endif; ?>

                <form method="post" class="aerp-hrm-task-form">
                    <?php wp_nonce_field('aerp_comment_task_action', 'aerp_comment_task_nonce'); ?>
                    <input type="hidden" name="task_id" value="<?= esc_attr($task->id) ?>">
                    <textarea name="comment" rows="2" placeholder="Nhập phản hồi..."></textarea>
                    <button type="submit" name="aerp_add_task_comment">Gửi phản hồi</button>
                </form>
            </div>
        <?php endforeach;
    else: ?>
        <p>Không có công việc nào được giao.</p>
    <?php endif; ?>
    <?php if ($total_pages > 1): ?>
        <div class="aerp-hrm-task-pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a class="aerp-hrm-page-link <?= $i == $paged ? 'active' : '' ?>"
                    href="<?= esc_url(add_query_arg(['paged' => $i])) ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <!-- Form popup thêm công việc -->
    <div class="aerp-hrm-task-popup" id="taskPopup">
        <div class="aerp-hrm-task-popup-inner">
            <div class="aerp-hrm-task-popup-close">×</div>
            <h3>➕ Thêm công việc mới</h3>
            <form method="post" class="aerp-hrm-task-form">
                <?php wp_nonce_field('aerp_add_task_action', 'aerp_add_task_nonce'); ?>
                <input type="text" name="task_title" placeholder="Tiêu đề công việc" required>
                <textarea name="task_desc" rows="3" placeholder="Mô tả chi tiết..."></textarea>
                <input type="datetime-local" name="deadline" required>
                <input type="number" name="score" min="0" max="100" placeholder="Điểm KPI (0-100)" required>
                <button class="button-submit" type="submit" name="aerp_add_task">Thêm công việc</button>
            </form>
        </div>
    </div>
    <!-- Popup sửa task -->
    <div class="aerp-hrm-task-popup" id="editTaskPopup">
        <div class="aerp-hrm-task-popup-inner">
            <div class="aerp-hrm-task-popup-close">×</div>
            <h3>✏️ Chỉnh sửa công việc</h3>
            <form method="post" class="aerp-hrm-task-form">
                <?php wp_nonce_field('aerp_edit_own_task_action', 'aerp_edit_own_task_nonce'); ?>
                <input type="hidden" name="edit_task_id" id="edit_task_id">
                <input type="text" name="edit_task_title" id="edit_task_title" required placeholder="Tiêu đề công việc">
                <textarea name="edit_task_desc" id="edit_task_desc" rows="3" placeholder="Mô tả công việc"></textarea>
                <input type="datetime-local" name="edit_task_deadline" id="edit_task_deadline" required>
                <input type="number" name="edit_task_score" id="edit_task_score" min="0" max="100" placeholder="Điểm KPI (0-100)" required>
                <button class="button-submit" type="submit" name="aerp_update_own_task">💾 Lưu chỉnh sửa</button>
            </form>
        </div>
    </div>

</div>