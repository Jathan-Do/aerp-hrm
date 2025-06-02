<?php
function aerp_menu_active($slug)
{
    return strpos($_SERVER['REQUEST_URI'], $slug) !== false ? 'active' : '';
}
?>

<button class="aerp-mobile-toggle" aria-label="Menu">
    <span class="dashicons dashicons-menu"></span>
</button>
<nav class="aerp-main-menu">
    <a href="<?= esc_url(site_url('/aerp-ho-so-nhan-vien')) ?>" class="aerp-menu-item <?= aerp_menu_active('aerp-ho-so-nhan-vien') ?>">
        <span class="aerp-menu-icon bg-green"><i class="dashicons dashicons-portfolio"></i></span>
        <span class="aerp-menu-text">Hồ sơ & Bảng lương</span>
    </a>
    <a href="<?= esc_url(site_url('/aerp-danh-sach-cong-viec')) ?>" class="aerp-menu-item <?= aerp_menu_active('aerp-danh-sach-cong-viec') ?>">
        <span class="aerp-menu-icon bg-blue"><i class="dashicons dashicons-list-view"></i></span>
        <span class="aerp-menu-text">Danh sách công việc</span>
    </a>
    <a href="<?= esc_url(site_url('/aerp-cham-cong')) ?>" class="aerp-menu-item <?= aerp_menu_active('aerp-cham-cong') ?>">
        <span class="aerp-menu-icon bg-orange"><i class="dashicons dashicons-calendar-alt"></i></span>
        <span class="aerp-menu-text">Chấm công</span>
    </a>
    <a href="<?= esc_url(wp_logout_url(site_url('/aerp-dang-nhap'))) ?>" class="aerp-menu-item aerp-logout" id="aerp-logout-link">
        <span class="aerp-menu-icon bg-red"><i class="dashicons dashicons-migrate"></i></span>
        <span class="aerp-menu-text">Đăng xuất</span>
    </a>
</nav>

<style>
    
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.querySelector('.aerp-mobile-toggle');
        const menu = document.querySelector('.aerp-main-menu');

        if (toggleBtn && menu) {
            toggleBtn.addEventListener('click', function() {
                menu.classList.toggle('active');
            });
        }

        // Xác nhận khi đăng xuất
        const logoutLink = document.getElementById('aerp-logout-link');
        if (logoutLink) {
            logoutLink.addEventListener('click', function(e) {
                if (!confirm('Bạn có chắc chắn muốn đăng xuất không?')) {
                    e.preventDefault();
                }
            });
        }
    });
</script>