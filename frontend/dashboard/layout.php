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
        .dashboard-sidebar {
            min-height: 100vh;
            background: #2c3e50;
            color: white;
        }

        .dashboard-content {
            padding: 20px;
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
            background: #3498db;
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
            width: 60px !important;
            min-width: 60px !important;
            max-width: 60px !important;
            transition: width 0.2s;
        }

        .dashboard-sidebar.collapsed .nav-link,
        .dashboard-sidebar.collapsed h4,
        .dashboard-sidebar.collapsed .collapsible-menu-header,
        .dashboard-sidebar.collapsed .collapsible-menu-content {
            display: none !important;
        }

        .dashboard-sidebar.collapsed .logo {
            margin-bottom: 0;
        }

        .dashboard-sidebar .logo {
            transition: width 0.2s;
        }

        @media (min-width: 768px) {
            .dashboard-sidebar.collapsed~.dashboard-content {
                width: calc(100% - 60px);
                transition: margin-left 0.2s, width 0.2s;
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        jQuery(function($) {
            var $sidebar = $('.dashboard-sidebar.d-none.d-md-block');
            var $btn = $('#sidebarCollapseBtn');
            if ($btn.length && $sidebar.length) {
                $btn.on('click', function() {
                    $sidebar.toggleClass('collapsed');
                    var $icon = $btn.find('i');
                    if ($sidebar.hasClass('collapsed')) {
                        $icon.removeClass('fa-angle-double-left').addClass('fa-angle-double-right');
                    } else {
                        $icon.removeClass('fa-angle-double-right').addClass('fa-angle-double-left');
                    }
                });
            }
        });
    </script>
</body>
</html>