<style>
    .aerp-profile-box {
        background: #fff;
        padding: 30px;
        border: 1px solid #ccd0d4;
        border-radius: 8px;
        max-width: 900px;
        margin-top: 20px;
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

    .tab-content {
        display: none;
        margin-top: 20px;
    }

    .tab-content.active {
        display: block;
    }
</style>

<div class="wrap">

    <h2 class="nav-tab-wrapper">
        <a href="#detail-view" class="nav-tab">Chi tiết nhân viên</a>
        <a href="#salary" class="nav-tab nav-tab-active">Lương</a>
        <a href="#tasks" class="nav-tab">Công việc</a>
        <a href="#disciplines" class="nav-tab">Vi phạm</a>
        <a href="#rewards" class="nav-tab">Thưởng</a>
        <a href="#adjustment" class="nav-tab">Tùy chỉnh Thưởng/ Phạt</a>
        <a href="#attachments" class="nav-tab">Hồ sơ</a>
        <a href="#attendance" class="nav-tab">Chấm công</a>
        <a href="#journey" class="nav-tab">Hành trình</a>
    </h2>
    <div id="detail-view" class="tab-content active">
        <?php include AERP_HRM_PATH . 'admin/views/employees/employee/tab-view-detail.php'; ?>
    </div>
    <div id="salary" class="tab-content">
        <?php include AERP_HRM_PATH . 'admin/views/employees/salary/tab-salary.php'; ?>
    </div>
    <div id="tasks" class="tab-content">
        <?php include AERP_HRM_PATH . 'admin/views/employees/task/tab-task.php'; ?>
    </div>
    <div id="disciplines" class="tab-content">
        <?php include AERP_HRM_PATH . 'admin/views/employees/discipline/tab-discipline.php'; ?>
    </div>
    <div id="rewards" class="tab-content">
        <?php include AERP_HRM_PATH . 'admin/views/employees/reward/tab-reward.php'; ?>
    </div>
    <div id="adjustment" class="tab-content">
        <?php include AERP_HRM_PATH . 'admin/views/employees/adjustment/tab-adjustment.php'; ?>
    </div>
    <div id="attachments" class="tab-content">
        <?php include AERP_HRM_PATH . 'admin/views/employees/attachment/tab-attachments.php'; ?>
    </div>
    <div id="attendance" class="tab-content">
        <?php include AERP_HRM_PATH . 'admin/views/employees/attendance/tab-attendance.php'; ?>
    </div>
    <div id="journey" class="tab-content">
        <?php include AERP_HRM_PATH . 'admin/views/employees/journey/tab-employee-journey.php'; ?>
    </div>
</div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.nav-tab');
        const contents = document.querySelectorAll('.tab-content');

        function activateTab(tabId) {
            tabs.forEach(tab => {
                tab.classList.remove('nav-tab-active');
                if (tab.getAttribute('href') === `#${tabId}`) {
                    tab.classList.add('nav-tab-active');
                }
            });

            contents.forEach(content => {
                content.classList.remove('active');
                if (content.id === tabId) {
                    content.classList.add('active');
                }
            });
        }

        // Khi click tab
        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('href').substring(1);
                activateTab(id);
                history.replaceState(null, '', '#' + id);
            });
        });

        // Khi load lại trang → lấy từ hash URL
        const currentHash = window.location.hash.replace('#', '');
        if (currentHash) {
            activateTab(currentHash);
        } else {
            activateTab('detail-view'); // fallback tab mặc định
        }
    });
</script>