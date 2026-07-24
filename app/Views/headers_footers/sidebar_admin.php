    <body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="<?php echo base_url('home'); ?>" class="nav-link">
                        <i class="fas fa-home mr-1"></i> Home
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="<?php echo base_url('admin/dashboard'); ?>" class="nav-link">
                        <i class="fas fa-tachometer-alt mr-1"></i> Admin
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="Fullscreen">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>

                <?php
                $avatar = isset($user_info['profile_image']) ? $user_info['profile_image'] : null;
                $username = isset($user_info['username']) ? htmlspecialchars(ucwords($user_info['username'])) : 'User';
                ?>
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <img src="<?php echo $avatar ? base_url('uploads/profiles/' . $avatar) : base_url('assets/img/avatar2.png'); ?>"
                             class="user-image img-circle elevation-2"
                             alt="User Image"
                             style="width: 32px; height: 32px; object-fit: cover;">
                        <span class="d-none d-md-inline ml-1"><?php echo $username; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <li class="user-header bg-light">
                            <img src="<?php echo $avatar ? base_url('uploads/profiles/' . $avatar) : base_url('assets/img/avatar2.png'); ?>"
                                 class="img-circle elevation-2" alt="User Image">
                            <p class="mt-2">
                                <?php echo $username; ?>
                                <small>Administrator</small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <a href="<?php echo base_url('account/profile'); ?>" class="btn btn-default btn-sm">
                                <i class="fas fa-user mr-1"></i> Profile
                            </a>
                            <a href="<?php echo base_url('logout'); ?>" class="btn btn-default btn-sm float-right">
                                <i class="fas fa-sign-out-alt mr-1"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="<?php echo base_url('admin/dashboard'); ?>" class="brand-link">
                <img src="<?= base_url('assets/img/logo.png') ?>" alt="Eaves Droid Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Eaves Droid</span>
            </a>

            <div class="sidebar" style="overflow-y: auto; overflow-x: hidden; height: calc(100vh - 57px);">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                        <li class="nav-header">MAIN</li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/dashboard'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'admin-dashboard') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-header">MANAGEMENT</li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/users'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'admin-users') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p>Users</p>
                            </a>
                        </li>

                        <?php
                            $reportPages = ['admin-reports', 'admin-reports-user-activity', 'admin-reports-data-usage', 'admin-reports-performance', 'admin-reports-generate'];
                            $isReportOpen = isset($pag) && in_array($pag, $reportPages);
                        ?>
                        <li class="nav-item has-treeview <?php echo $isReportOpen ? 'menu-open' : ''; ?>">
                            <a href="#" class="nav-link <?php echo $isReportOpen ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>
                                    Reports
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/reports'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-reports') ? 'active' : ''; ?>">
                                        <i class="fas fa-tachometer-alt nav-icon"></i>
                                        <p>Dashboard</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/reports/user-activity'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-reports-user-activity') ? 'active' : ''; ?>">
                                        <i class="fas fa-user nav-icon"></i>
                                        <p>User Activity</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/reports/data-usage'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-reports-data-usage') ? 'active' : ''; ?>">
                                        <i class="fas fa-database nav-icon"></i>
                                        <p>Data Usage</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/reports/performance'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-reports-performance') ? 'active' : ''; ?>">
                                        <i class="fas fa-tachometer-alt nav-icon"></i>
                                        <p>Performance</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/reports/generate'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-reports-generate') ? 'active' : ''; ?>">
                                        <i class="fas fa-file-export nav-icon"></i>
                                        <p>Generate Report</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <?php
                            $tokenPages = ['admin-tokens', 'admin-tokens-expired', 'admin-tokens-analytics'];
                            $isTokenOpen = isset($pag) && in_array($pag, $tokenPages);
                        ?>
                        <li class="nav-item has-treeview <?php echo $isTokenOpen ? 'menu-open' : ''; ?>">
                            <a href="#" class="nav-link <?php echo $isTokenOpen ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-key"></i>
                                <p>
                                    Tokens
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/tokens'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-tokens') ? 'active' : ''; ?>">
                                        <i class="fas fa-list nav-icon"></i>
                                        <p>All Tokens</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/tokens/expired'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-tokens-expired') ? 'active' : ''; ?>">
                                        <i class="fas fa-clock nav-icon"></i>
                                        <p>Expired</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/tokens/analytics'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-tokens-analytics') ? 'active' : ''; ?>">
                                        <i class="fas fa-chart-pie nav-icon"></i>
                                        <p>Analytics</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-header">SYSTEM</li>

                        <?php
                            $logPages = ['admin-logs', 'admin-access-logs', 'admin-error-logs', 'admin-api-logs'];
                            $isLogOpen = isset($pag) && in_array($pag, $logPages);
                        ?>
                        <li class="nav-item has-treeview <?php echo $isLogOpen ? 'menu-open' : ''; ?>">
                            <a href="#" class="nav-link <?php echo $isLogOpen ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>
                                    System Logs
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/logs'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-logs') ? 'active' : ''; ?>">
                                        <i class="fas fa-list nav-icon"></i>
                                        <p>All Logs</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/logs/access'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-access-logs') ? 'active' : ''; ?>">
                                        <i class="fas fa-sign-in-alt nav-icon"></i>
                                        <p>Access Logs</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/logs/errors'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-error-logs') ? 'active' : ''; ?>">
                                        <i class="fas fa-exclamation-triangle nav-icon"></i>
                                        <p>Error Logs</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/logs/api'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-api-logs') ? 'active' : ''; ?>">
                                        <i class="fas fa-code nav-icon"></i>
                                        <p>API Logs</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/ml'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'admin-ml') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-brain"></i>
                                <p>ML / AI</p>
                            </a>
                        </li>

                        <?php
                            $settingsPages = ['admin-settings', 'admin-settings-api', 'admin-settings-security', 'admin-settings-notifications', 'admin-maintenance', 'admin-backup'];
                            $isSettingsOpen = isset($pag) && in_array($pag, $settingsPages);
                        ?>
                        <li class="nav-item has-treeview <?php echo $isSettingsOpen ? 'menu-open' : ''; ?>">
                            <a href="#" class="nav-link <?php echo $isSettingsOpen ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>
                                    Settings
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/settings'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-settings') ? 'active' : ''; ?>">
                                        <i class="fas fa-cog nav-icon"></i>
                                        <p>General</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/settings/api'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-settings-api') ? 'active' : ''; ?>">
                                        <i class="fas fa-plug nav-icon"></i>
                                        <p>API</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/settings/security'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-settings-security') ? 'active' : ''; ?>">
                                        <i class="fas fa-shield-alt nav-icon"></i>
                                        <p>Security</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/settings/notifications'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-settings-notifications') ? 'active' : ''; ?>">
                                        <i class="fas fa-bell nav-icon"></i>
                                        <p>Notifications</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/settings/maintenance'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-maintenance') ? 'active' : ''; ?>">
                                        <i class="fas fa-tools nav-icon"></i>
                                        <p>Maintenance</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('admin/settings/backup'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag === 'admin-backup') ? 'active' : ''; ?>">
                                        <i class="fas fa-hdd nav-icon"></i>
                                        <p>Backup</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-header">LINKS</li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('home'); ?>" class="nav-link">
                                <i class="nav-icon fas fa-arrow-left"></i>
                                <p>Back to App</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
