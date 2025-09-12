<?php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
if (!is_user_logged_in()) wp_die(__('You must be logged in to access this page.'));
$access_conditions = [
    aerp_user_has_role($user_id, 'admin'),
    aerp_user_has_role($user_id, 'department_lead'),
    aerp_user_has_role($user_id, 'accountant'),
];
if (!in_array(true, $access_conditions, true)) wp_die(__('You do not have sufficient permissions to access this page.'));


$scheme_id = absint($_GET['id'] ?? 0);
$scheme = $scheme_id ? AERP_Frontend_Commission_Manager::get_scheme($scheme_id) : null;

ob_start();
?>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2><?= $scheme_id ? 'Sửa' : 'Thêm' ?> danh mục % lợi nhuận</h2>
    <div class="user-info text-end">
        Welcome, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
<?php
if (function_exists('aerp_render_breadcrumb')) {
    aerp_render_breadcrumb([
        ['label' => 'Trang chủ', 'url' => home_url('/aerp-dashboard'), 'icon' => 'fas fa-home'],
        ['label' => 'Danh mục % lợi nhuận', 'url' => home_url('/aerp-hrm-commission-schemes')],
        ['label' => $scheme_id ? 'Sửa' : 'Thêm']
    ]);
}
?>
<div class="card">
    <div class="card-body">
        <form method="post">
            <?php wp_nonce_field('aerp_commission_action', 'aerp_commission_nonce'); ?>
            <?php if ($scheme_id): ?><input type="hidden" name="scheme_id" value="<?= esc_attr($scheme_id) ?>"><?php endif; ?>
            <div class="mb-3">
                <label class="form-label">Tên danh mục</label>
                <input type="text" class="form-control shadow-sm" name="name" value="<?= esc_attr($scheme->name ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ghi chú</label>
                <textarea class="form-control shadow-sm" name="note" rows="2"><?= esc_textarea($scheme->note ?? '') ?></textarea>
            </div>

            <div class="row g-2 align-items-end mb-2">
                <div class="col-md-4">
                    <label class="form-label">Từ lợi nhuận</label>
                    <input type="number" class="form-control shadow-sm" name="min_profit" value="<?= esc_attr($scheme->min_profit ?? 0) ?>" step="1" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Đến lợi nhuận (bỏ trống = không giới hạn)</label>
                    <input type="number" class="form-control shadow-sm" name="max_profit" value="<?= isset($scheme->max_profit) ? esc_attr($scheme->max_profit) : '' ?>" step="1" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">% hưởng</label>
                    <input type="number" class="form-control shadow-sm" name="percent" value="<?= esc_attr($scheme->percent ?? 0) ?>" step="0.01" min="0" max="100">
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" name="aerp_save_commission_scheme" class="btn btn-primary">Lưu</button>
                <a href="<?= home_url('/aerp-hrm-commission-schemes') ?>" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>

<script></script>
<?php
$content = ob_get_clean();
$title = ($scheme_id ? 'Sửa' : 'Thêm') . ' danh mục % lợi nhuận';
include AERP_HRM_PATH . 'frontend/dashboard/layout.php';
?>

