<?php
if (!defined('ABSPATH')) exit;
/** @var int $task_id */
/** @var array $comments */
?>
<h4><i class="dashicons dashicons-format-status"></i> Bình luận</h4>
<?php if (!empty($comments)): ?>
    <div class="aerp-comment-list">
        <?php foreach ($comments as $c): ?>
            <?php
            $is_admin = user_can($c->user_id, 'manage_options');
            $badge_class = $is_admin ? 'aerp-badge-admin' : 'aerp-badge-user';
            ?>
            <div class="aerp-comment-item">
                <div class="aerp-comment-header">
                    <div class="aerp-comment-author <?= $badge_class ?>">
                        <?= esc_html($c->display_name) ?>
                    </div>
                    <div class="aerp-comment-date">
                        <?= date('d/m/Y H:i', strtotime($c->created_at)) ?>
                    </div>
                </div>
                <div class="aerp-comment-content">
                    <?= esc_html($c->comment) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="aerp-no-comments">Chưa có bình luận nào</div>
<?php endif; ?>
<form method="post" class="aerp-comment-form">
    <?php wp_nonce_field('aerp_comment_task_action', 'aerp_comment_task_nonce'); ?>
    <input type="hidden" name="task_id" value="<?= esc_attr($task_id) ?>">
    <textarea rows="2" name="comment" placeholder="Viết bình luận..."></textarea>
    <button type="submit" name="aerp_add_task_comment" class="aerp-btn aerp-btn-secondary">
        <i class="dashicons dashicons-email"></i> Gửi
    </button>
</form> 