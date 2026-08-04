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
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="<?php echo base_url('superadmin/home'); ?>" class="nav-link">
                        <i class="fas fa-shield-alt mr-1 text-danger"></i> Super Admin
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
                        <li class="user-header bg-danger">
                            <img src="<?php echo $avatar ? base_url('uploads/profiles/' . $avatar) : base_url('assets/img/avatar2.png'); ?>"
                                 class="img-circle elevation-2" alt="User Image">
                            <p class="mt-2">
                                <?php echo $username; ?>
                                <small>Super Administrator</small>
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
        <aside class="main-sidebar sidebar-dark-danger elevation-4">
            <a href="<?php echo base_url('superadmin/home'); ?>" class="brand-link">
                <img src="<?= base_url('assets/img/logo.png') ?>" alt="Eaves Droid Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Eaves Droid</span>
            </a>

            <div class="sidebar" style="overflow-y: auto; overflow-x: hidden; height: calc(100vh - 57px);">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                        <li class="nav-header">MAIN</li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('superadmin/home'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'superadmin-home') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-shield-alt"></i>
                                <p>Super Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/dashboard'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'admin-dashboard') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Admin Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-header">SUPERADMIN</li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('superadmin/users'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'superadmin-users') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-user-cog"></i>
                                <p>Role Matrix</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('superadmin/audit'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'superadmin-audit') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-clipboard-check"></i>
                                <p>Audit Trail</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('superadmin/omni-search'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'superadmin-omni-search') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-search"></i>
                                <p>Omni Search</p>
                            </a>
                        </li>

                        <li class="nav-header">ADMINISTRATION</li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/users'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'admin-users') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p>Users</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/remote-device'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'admin-remote-device') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-mobile-alt"></i>
                                <p>Remote Devices</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/defaults'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'admin-defaults') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>App Defaults</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/db_info'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'admin-db-info') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-database"></i>
                                <p>DB Info</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/reports'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'admin-reports') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Reports</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/tokens'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'admin-tokens') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-key"></i>
                                <p>API Tokens</p>
                            </a>
                        </li>

                        <li class="nav-header">SYSTEM</li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/logs'); ?>"
                               class="nav-link <?php echo (isset($pag) && str_starts_with($pag, 'admin-log')) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>System Logs</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/ml'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'admin-ml') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-brain"></i>
                                <p>ML / AI</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/anomalies'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'admin-anomalies') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-exclamation-triangle text-warning"></i>
                                <p>Anomaly Engine</p>
                            </a>
                        </li>

                        <?php
                            $settingsPages = ['admin-settings', 'admin-settings-api', 'admin-settings-security', 'admin-settings-notifications', 'admin-maintenance', 'admin-backup', 'admin-settings-storage', 'admin-settings-email-triggers', 'admin-settings-cron'];
                            $isSettingsActive = isset($pag) && in_array($pag, $settingsPages);
                        ?>
                        <li class="nav-item <?php echo $isSettingsActive ? 'active' : ''; ?>">
                            <a href="<?php echo base_url('admin/settings'); ?>"
                               class="nav-link <?php echo $isSettingsActive ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>Settings</p>
                            </a>
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
