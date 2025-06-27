<?php

$current_user = wp_get_current_user();
$section = $_GET['section'] ?? 'detail-view';
$employee_id = absint($_GET['id'] ?? 0);
ob_start();
?>
<style>
    .aerp-profile-box {
        background: #fff;
        padding: 30px;
        padding-top: 0;
        border: 1px solid #ccd0d4;
        border-radius: 8px;
        /* max-width: 900px; */
        margin: 20px 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .aerp-profile-box h2 {
        font-size: 16px;
        text-transform: uppercase;
        color: #0073aa;
        margin-top: 30px;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }

    .aerp-profile-table {
        border: 0;
        width: 100%;
        border-collapse: collapse;
    }

    .aerp-profile-table th {
        text-align: left;
        width: 180px;
        color: #444;
        font-weight: 600;
        padding: 8px 0;
    }

    .aerp-profile-table td {
        padding: 8px 0;
    }

    .aerp-tabs {
        display: flex;
        gap: 8px;
        border-bottom: 2px solid #e5e5e5;
        margin-bottom: 20px;
        background: rgb(244, 245, 245);
        padding: 0;
        overflow-y: scroll;
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
    }

    .aerp-tabs::-webkit-scrollbar {
        display: none;
        /* Chrome, Safari, Opera*/
    }


    .aerp-tab {
        display: inline-block;
        padding: 10px 18px;
        color: #0073aa;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: color 0.2s, border-color 0.2s;
        min-width: fit-content
    }

    .aerp-tab:hover,
    .aerp-tab.active {
        color: #005177;
        border-bottom: 2px solid #0073aa;
        background: #fff;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }
</style>
<div class="d-flex flex-column-reverse flex-md-row justify-content-between align-items-md-center mb-4">
    <h2>Chi tiết nhân viên</h2>
    <div class="user-info text-end">
        Xin chào, <?php echo esc_html($current_user->display_name); ?>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-sm btn-outline-danger ms-2">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>
<div class="aerp-tabs" id="aerp-tabs">
    <a href="<?= home_url("/aerp-hrm-employees/?action=view&id=$employee_id&section=detail-view") ?>" class="aerp-tab<?= $section === 'detail-view' ? ' active' : '' ?>" data-section="detail-view">Chi tiết nhân viên</a>
    <a href="<?= home_url("/aerp-hrm-employees/?action=view&id=$employee_id&section=salary") ?>" class="aerp-tab<?= $section === 'salary' ? ' active' : '' ?>" data-section="salary">Lương</a>
    <a href="<?= home_url("/aerp-hrm-employees/?action=view&id=$employee_id&section=task") ?>" class="aerp-tab<?= $section === 'task' ? ' active' : '' ?>" data-section="task">Công việc</a>
    <a href="<?= home_url("/aerp-hrm-employees/?action=view&id=$employee_id&section=discipline") ?>" class="aerp-tab<?= $section === 'discipline' ? ' active' : '' ?>" data-section="discipline">Vi phạm</a>
    <a href="<?= home_url("/aerp-hrm-employees/?action=view&id=$employee_id&section=reward") ?>" class="aerp-tab<?= $section === 'reward' ? ' active' : '' ?>" data-section="reward">Thưởng</a>
    <a href="<?= home_url("/aerp-hrm-employees/?action=view&id=$employee_id&section=adjustment") ?>" class="aerp-tab<?= $section === 'adjustment' ? ' active' : '' ?>" data-section="adjustment">Tùy chỉnh Thưởng/ Phạt</a>
    <a href="<?= home_url("/aerp-hrm-employees/?action=view&id=$employee_id&section=attachment") ?>" class="aerp-tab<?= $section === 'attachment' ? ' active' : '' ?>" data-section="attachment">Hồ sơ</a>
    <a href="<?= home_url("/aerp-hrm-employees/?action=view&id=$employee_id&section=attendance") ?>" class="aerp-tab<?= $section === 'attendance' ? ' active' : '' ?>" data-section="attendance">Chấm công</a>
    <a href="<?= home_url("/aerp-hrm-employees/?action=view&id=$employee_id&section=journey") ?>" class="aerp-tab<?= $section === 'journey' ? ' active' : '' ?>" data-section="journey">Hành trình</a>
</div>
<div class="tab-content active" id="aerp-tab-content">
    <?php
    switch ($section) {
        case 'salary':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/salary/tab-salary.php';
            break;
        case 'task':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/task/tab-task.php';
            break;
        case 'discipline':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/discipline/tab-discipline.php';
            break;
        case 'reward':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/reward/tab-reward.php';
            break;
        case 'adjustment':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/adjustment/tab-adjustment.php';
            break;
        case 'attachment':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/attachment/tab-attachment.php';
            break;
        case 'attendance':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/attendance/tab-attendance.php';
            break;
        case 'journey':
            include AERP_HRM_PATH . 'frontend/dashboard/employees/journey/tab-employee-journey.php';
            break;
        default:
            include AERP_HRM_PATH . 'frontend/dashboard/employees/employee/tab-view-detail.php';
            break;
    }
    ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('#aerp-tabs .aerp-tab');
    const tabContent = document.getElementById('aerp-tab-content');
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            if (this.classList.contains('active')) return;
            const section = this.getAttribute('data-section');
            const employeeId = <?= (int)$employee_id ?>;
            // Hiển thị loading
            tabContent.innerHTML = '<div style="padding:40px;text-align:center"><span class="spinner-border text-primary"></span></div>';
            // Cập nhật active tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            // Gọi ajax lấy nội dung tab
            fetch('<?= admin_url('admin-ajax.php') ?>?action=aerp_hrm_employee_tab_content', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + employeeId + '&section=' + section
            })
            .then(res => res.text())
            .then(html => {
                tabContent.innerHTML = html;
                // Cập nhật url trên trình duyệt
                window.history.replaceState({}, '', '?action=view&id=' + employeeId + '&section=' + section);
            });
        });
    });
});
</script>
<?php
$content = ob_get_clean();
$title = 'Chi tiết nhân viên';
include(AERP_HRM_PATH . 'frontend/dashboard/layout.php');
