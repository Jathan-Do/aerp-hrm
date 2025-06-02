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

// Xử lý xóa task của chính mình
if (
    isset($_POST['aerp_delete_own_task']) &&
    check_admin_referer('aerp_delete_own_task_action', 'aerp_delete_own_task_nonce')
) {
    $task_id = absint($_POST['delete_task_id']);
    $task = AERP_Task_Manager::get_by_id($task_id);
    if ($task && $task->created_by == $user_id && $task->employee_id == $employee_id) {
        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'aerp_hrm_tasks', ['id' => $task_id]);
        aerp_js_redirect($clean_url);
        exit;
    }
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
<!-- Quick Links -->
<?php include(AERP_HRM_PATH . 'frontend/quick-links.php'); ?>
<div class="aerp-hrm-dashboard">
    <!-- Header và Filter -->
    <div class="aerp-card aerp-task-header">
        <div class="aerp-card-header">
            <h2><i class="dashicons dashicons-list-view"></i> Quản lý công việc</h2>
            <button type="button" class="aerp-btn aerp-btn-primary" data-open-aerp-hrm-task-popup>
                <i class="dashicons dashicons-plus"></i> Thêm công việc
            </button>
        </div>

        <form method="get" class="aerp-hrm-task-filter-form">
            <input type="hidden" name="page_id" value="<?= esc_attr(get_the_ID()) ?>">
            <div class="form-group">
                <input type="text" name="keyword" placeholder="Tìm kiếm công việc..." value="<?= esc_attr($keyword) ?>">
            </div>
            <div class="form-group">
                <select name="status" class="aerp-hrm-custom-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="assigned" <?= selected($status, 'assigned') ?>>Đã giao</option>
                    <option value="done" <?= selected($status, 'done') ?>>Hoàn thành</option>
                    <option value="failed" <?= selected($status, 'failed') ?>>Thất bại</option>
                </select>
            </div>
            <button type="submit" class="aerp-btn aerp-btn-primary">
                <i class="dashicons dashicons-filter"></i> Lọc
            </button>
        </form>
    </div>

    <?php if (!empty($notification)): ?>
        <div id="aerp-hrm-toast" class="aerp-hrm-toast">
            <span><?= esc_html($notification) ?></span>
            <button onclick="closeToast()">X</button>
        </div>
    <?php endif; ?>

    <!-- Danh sách công việc -->
    <div class="aerp-card aerp-task-list">
        <?php if ($tasks): ?>
            <?php foreach ($tasks as $task): ?>
                <div class="aerp-task-item <?= $task->status ?>">
                    <div class="aerp-task-header">
                        <div class="aerp-task-title">
                            <h3><?= esc_html($task->task_title) ?></h3>
                            <div class="aerp-task-meta">
                                <span class="task-deadline">
                                    <i class="dashicons dashicons-calendar-alt"></i> Deadline: <?= date('H:i d/m/Y', strtotime($task->deadline)) ?>
                                </span>
                                <?php if ($task->score): ?>
                                    <span class="task-score">
                                        <i class="dashicons dashicons-star-filled"></i> Điểm KPI: <?= esc_html($task->score) ?> điểm
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="aerp-task-actions">

                            <!-- Bình luận -->
                            <?php $comment_count = AERP_Task_Manager::count_comments($task->id); ?>
                            <button class="aerp-btn aerp-btn-secondary aerp-task-comment-btn" data-task-id="<?= $task->id ?>" data-task-title="<?= esc_attr($task->task_title) ?>">
                                <i class="dashicons dashicons-format-status"></i> Bình luận (<?= $comment_count ?>)
                            </button>
                            <?php if ($task->created_by == $user_id && $task->status != 'done'): ?>
                                <button class="aerp-btn aerp-btn-primary" onclick='openEditTaskPopup(<?= json_encode([
                                                                                                            "id" => $task->id,
                                                                                                            "task_title" => $task->task_title,
                                                                                                            "task_desc" => $task->task_desc,
                                                                                                            "deadline" => $task->deadline,
                                                                                                            "score" => $task->score
                                                                                                        ]) ?>)'>
                                    <i class="dashicons dashicons-edit"></i>
                                    Sửa công việc
                                </button>
                            <?php endif; ?>
                            <?php if ($task->created_by == $user_id): ?>
                                <form method="post" style="display:inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa công việc này?');">
                                    <?php wp_nonce_field('aerp_delete_own_task_action', 'aerp_delete_own_task_nonce'); ?>
                                    <input type="hidden" name="delete_task_id" value="<?= esc_attr($task->id) ?>">
                                    <button type="submit" name="aerp_delete_own_task" class="aerp-btn aerp-btn-danger">
                                        <i class="dashicons dashicons-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="aerp-task-content aerp-comment-form">
                        <?php if ($task->task_desc): ?>
                            <textarea rows="4" readonly><?= esc_html($task->task_desc) ?></textarea>
                        <?php endif; ?>

                        <div class="aerp-task-status-form">
                            <form method="post" class="aerp-status-form">
                                <?php wp_nonce_field('aerp_update_task_action', 'aerp_update_task_nonce'); ?>
                                <input type="hidden" name="task_id" value="<?= esc_attr($task->id) ?>">
                                <select name="status" class="aerp-status-select aerp-hrm-custom-select">
                                    <option value="assigned" <?= selected($task->status, 'assigned') ?>>
                                        <i class="dashicons dashicons-yes"></i> Đã giao
                                    </option>
                                    <option value="done" <?= selected($task->status, 'done') ?>>
                                        <i class="dashicons dashicons-yes"></i> Hoàn thành
                                    </option>
                                    <option value="failed" <?= selected($task->status, 'failed') ?>>
                                        <i class="dashicons dashicons-no"></i> Thất bại
                                    </option>
                                </select>
                                <button type="submit" name="aerp_update_task_status" class="aerp-btn aerp-task-update-status-btn">
                                    <i class="dashicons dashicons-yes"></i> Cập nhật
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Phân trang -->
            <?php if ($total_pages > 1): ?>
                <div class="aerp-pagination">
                    <?php
                    $big = 999999999;
                    echo paginate_links(array(
                        'base'    => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                        'format'  => '?paged=%#%',
                        'current' => max(1, $paged),
                        'total'   => $total_pages,
                        'prev_text' => '<i class="dashicons dashicons-arrow-left-alt2"></i>',
                        'next_text' => '<i class="dashicons dashicons-arrow-right-alt2"></i>',
                    ));
                    ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="aerp-no-tasks">
                <i class="dashicons dashicons-folder-open"></i>
                <p>Không có công việc nào được giao</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Popup thêm công việc -->
<div class="aerp-hrm-task-popup" id="taskPopup">
    <div class="aerp-hrm-task-popup-inner">
        <div class="aerp-hrm-task-popup-close">×</div>
        <h3>➕ Thêm công việc mới</h3>
        <form method="post" class="aerp-hrm-task-form">
            <?php wp_nonce_field('aerp_add_task_action', 'aerp_add_task_nonce'); ?>

            <div class="form-group">
                <label style="font-weight: 600; font-size: 16px;" for="task_title">Tiêu đề</label>
                <input type="text" id="task_title" name="task_title" placeholder="Nhập tiêu đề công việc" required>
            </div>

            <div class="form-group">
                <label style="font-weight: 600; font-size: 16px;" for="task_desc"> Mô tả</label>
                <textarea id="task_desc" name="task_desc" rows="3" placeholder="Nhập mô tả chi tiết"></textarea>
            </div>

            <div class="form-group">
                <label style="font-weight: 600; font-size: 16px;" for="deadline">Deadline</label>
                <input type="datetime-local" id="deadline" name="deadline" required>
            </div>

            <div class="form-group">
                <label style="font-weight: 600; font-size: 16px;" for="score">Điểm KPI</label>
                <input type="number" id="score" name="score" min="0" max="100" placeholder="0-100" required>
            </div>

            <div class="aerp-form-actions">
                <button type="submit" name="aerp_add_task" class="aerp-btn aerp-btn-primary">
                    <i class="dashicons dashicons-yes"></i> Lưu công việc
                </button>
                <button type="button" class="aerp-btn aerp-btn-secondary aerp-popup-close">
                    <i class="dashicons dashicons-no"></i> Hủy bỏ
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Popup sửa công việc -->
<div class="aerp-hrm-task-popup" id="editTaskPopup">
    <div class="aerp-hrm-task-popup-inner">
        <div class="aerp-hrm-task-popup-close">×</div>
        <h3>✏️ Chỉnh sửa công việc</h3>
        <form method="post" class="aerp-hrm-task-form">
            <?php wp_nonce_field('aerp_edit_own_task_action', 'aerp_edit_own_task_nonce'); ?>
            <input type="hidden" name="edit_task_id" id="edit_task_id">

            <div class="form-group">
                <label style="font-weight: 600; font-size: 16px;" for="edit_task_title"> Tiêu đề</label>
                <input type="text" id="edit_task_title" name="edit_task_title" placeholder="Nhập tiêu đề công việc" required>
            </div>

            <div class="form-group">
                <label style="font-weight: 600; font-size: 16px;" for="edit_task_desc"> Mô tả</label>
                <textarea id="edit_task_desc" name="edit_task_desc" rows="3" placeholder="Nhập mô tả chi tiết"></textarea>
            </div>

            <div class="form-group">
                <label style="font-weight: 600; font-size: 16px;" for="edit_task_deadline"> Deadline</label>
                <input type="datetime-local" id="edit_task_deadline" name="edit_task_deadline" required>
            </div>

            <div class="form-group">
                <label style="font-weight: 600; font-size: 16px;" for="edit_task_score"> Điểm KPI</label>
                <input type="number" id="edit_task_score" name="edit_task_score" min="0" max="100" placeholder="0-100" required>
            </div>

            <div class="aerp-form-actions">
                <button type="submit" name="aerp_update_own_task" class="aerp-btn aerp-btn-primary">
                    <i class="dashicons dashicons-yes"></i> Lưu thay đổi
                </button>
                <button type="button" class="aerp-btn aerp-btn-secondary aerp-popup-close">
                    <i class="dashicons dashicons-no"></i> Hủy bỏ
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Popup bình luận task -->
<div class="aerp-hrm-task-popup" id="taskCommentPopup">
    <div class="aerp-hrm-task-popup-inner">
        <div class="aerp-hrm-task-popup-close">×</div>
        <h3 id="taskCommentPopupTitle">Bình luận công việc</h3>
        <div id="taskCommentPopupContent"></div>
    </div>
</div>

<script>
    window.aerpFrontend = {
        ajaxurl: "<?= admin_url('admin-ajax.php') ?>"
    };
</script>