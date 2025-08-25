<?php
if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html($title); ?></title>
    <?php wp_head(); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card label {
            font-weight: 500;
        }

        .breadcrumb-item.active {
            font-weight: 500;
            color: #182433;
        }

        .breadcrumb-item>a {
            text-decoration: none;
            color: rgb(100, 104, 107);
        }

        .breadcrumb-item>a:hover {
            text-decoration: revert;
        }

        .collapsible-menu-content {
            display: none;
            border-left: 4px solid rgb(52, 152, 219);
        }

        .dashboard-sidebar {
            min-height: 100vh;
            background: #2c3e50;
            color: white;
            transition: width 0.3s ease;
        }

        .dashboard-content {
            padding: 20px;
            transition: all 0.3s ease;
        }

        .nav-link {
            color: white;
            padding: 10px 20px;
        }

        .nav-link:hover {
            background: #34495e;
            color: white;
        }

        .nav-link.active {
            background: rgb(61, 85, 109);
        }

        .menu-text {
            display: inline-block;
            vertical-align: middle;
            opacity: 1;
            max-width: 150px;
            transition: all 0.1s linear;
            white-space: nowrap;
            overflow: hidden;
        }

        .dashboard-sidebar:not(.collapsed) .menu-text {
            transition-delay: 0.1s;
        }

        .category-card {
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            transition: box-shadow 0.2s;
        }

        .category-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .category-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        /* Sidebar collapse styles */
        .dashboard-sidebar.collapsed {
            width: 80px;
        }

        /* .dashboard-sidebar.collapsed .collapsible-menu-header, */
        .dashboard-sidebar.collapsed .menu-text {
            opacity: 0;
            max-width: 0;
        }

        .dashboard-sidebar.collapsed .nav-link {
            text-align: center;
            padding-left: 0;
            padding-right: 0;
        }

        .dashboard-sidebar.collapsed .nav-link i {
            margin-right: 0 !important;
        }

        .dashboard-sidebar.collapsed .logo {
            margin-bottom: 10px;
        }

        @media (min-width: 768px) {
            .dashboard-sidebar.collapsed~.dashboard-content {
                width: calc(100% - 80px);
            }


        }

        @media (max-width: 768px) {
            .dashboard-sidebar {
                height: 100vh;
                min-height: 0;
            }
        }

        /* Loading overlay for tables */
        .aerp-table-loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.7);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: .375rem;
        }

        .aerp-table-spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-top-color: #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php
            include(AERP_HRM_PATH . 'frontend/dashboard/sidebar.php');

            ?>
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 dashboard-content">
                <button style="justify-self: end;" class="d-flex align-items-center btn btn-primary d-md-none my-2 ms-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#aerpSidebar" aria-controls="aerpSidebar">
                    <i class="fas fa-bars me-2"></i> Menu
                </button>

                <?php echo $content; ?>
            </div>
        </div>
    </div>
    <?php wp_footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <script>
        jQuery(function($) {
            var $sidebar = $('.dashboard-sidebar.d-none.d-md-block');
            var $menu_content = $('.collapsible-menu-content>a>span');
            var $btn = $('#sidebarCollapseBtn');
            if ($btn.length && $sidebar.length) {
                $btn.on('click', function() {
                    $sidebar.toggleClass('collapsed');
                    var $icon = $btn.find('i');
                    if ($sidebar.hasClass('collapsed')) {
                        $icon.removeClass('fa-angle-double-left').addClass('fa-angle-double-right');
                        $menu_content.removeClass('ms-4');
                    } else {
                        $icon.removeClass('fa-angle-double-right').addClass('fa-angle-double-left');
                        $menu_content.addClass('ms-4');
                    }
                });
            }

            // Enable Bootstrap 5 tooltips globally for elements with data-bs-toggle="tooltip"
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.forEach(function(el) {
                    new bootstrap.Tooltip(el);
                });
            }
        });
    </script>
</body>

</html>