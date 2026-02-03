    <body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
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
                    <a href="<?php echo base_url('analysis'); ?>" class="nav-link">
                        <i class="fas fa-chart-bar mr-1"></i> Analysis
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="<?php echo base_url('account/requests'); ?>" class="nav-link">
                        <i class="fas fa-terminal mr-1"></i> Commands
                    </a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">3</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <span class="dropdown-item dropdown-header">
                                <i class="fas fa-bell mr-2"></i> 3 Notifications
                            </span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-sync-alt mr-2 text-info"></i> Device sync completed
                            <span class="float-right text-muted text-sm">5 mins ago</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-shield-alt mr-2 text-success"></i> Security check passed
                            <span class="float-right text-muted text-sm">2 hours ago</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-exclamation-triangle mr-2 text-warning"></i> New data available
                            <span class="float-right text-muted text-sm">1 day ago</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">
                            <i class="fas fa-eye mr-1"></i> View All Notifications
                        </a>
                    </div>
                </li>

                <!-- Fullscreen -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="Fullscreen">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>

                <!-- User Menu -->
                <?php
                $avatar = null;
                $username = isset($user_info['username']) ? htmlspecialchars(ucwords($user_info['username'])) : 'User';
                ?>
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <img src="<?php echo $avatar ? base_url('uploads/profiles/' . $avatar) : base_url('assets/img/avatar2.png'); ?>"
                             class="user-image img-circle elevation-2"
                             alt="User Image"
                             style="width: 32px; height: 32px; object-fit: cover;">
                        <span class="d-none d-md-inline ml-1">
                                <?php echo $username; ?>
                            </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <li class="user-header bg-light">
                            <img src="<?php echo $avatar ? base_url('uploads/profiles/' . $avatar) : base_url('assets/img/avatar2.png'); ?>"
                                 class="img-circle elevation-2"
                                 alt="User Image">
                            <p class="mt-2">
                                <?php echo $username; ?>
                                <small>Member</small>
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
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="<?php echo base_url('home'); ?>" class="brand-link">
                <img src="<?php echo base_url('assets/img/logo.png'); ?>"
                     class="brand-image img-circle elevation-3"
                     alt="Logo"
                     style="opacity: .8">
                <span class="brand-text font-weight-light">Prj Monitor</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="<?php echo $avatar ? base_url('uploads/profiles/' . $avatar) : base_url('assets/img/avatar2.png'); ?>"
                             class="img-circle elevation-2"
                             alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">
                            <?php echo $username; ?>
                        </a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="<?php echo base_url('home'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'home') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <!-- Data Section -->
                        <li class="nav-header">DATA</li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('apps'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'apps') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-mobile-alt"></i>
                                <p>Apps</p>
                                <span class="badge badge-info float-right"><?php echo isset($total_apps) ? $total_apps : 0; ?></span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('call_logs'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'call_logs') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-phone-alt"></i>
                                <p>Call Logs</p>
                                <span class="badge badge-success float-right"><?php echo isset($total_calls) ? $total_calls : 0; ?></span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('contacts'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'contacts') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-id-card"></i>
                                <p>Contacts</p>
                                <span class="badge badge-warning float-right"><?php echo isset($total_contacts) ? $total_contacts : 0; ?></span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('sms'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'sms') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-sms"></i>
                                <p>SMS</p>
                                <span class="badge badge-danger float-right"><?php echo isset($total_sms) ? $total_sms : 0; ?></span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('files'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'files') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-file"></i>
                                <p>Files</p>
                                <span class="badge badge-secondary float-right"><?php echo isset($total_files) ? $total_files : 0; ?></span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('location'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'location') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-map-marker-alt"></i>
                                <p>Locations</p>
                                <span class="badge badge-info float-right"><?php echo isset($total_locations) ? $total_locations : 0; ?></span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('activities'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'activities') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-walking"></i>
                                <p>Activities</p>
                                <span class="badge badge-primary float-right"><?php echo isset($total_activities) ? $total_activities : 0; ?></span>
                            </a>
                        </li>

                        <!-- Intelligence Section -->
                        <li class="nav-header">INTELLIGENCE</li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('analysis'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'analysis') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Analysis</p>
                                <span class="badge badge-info float-right">AI</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('account/requests'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'requests') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-terminal"></i>
                                <p>Commands</p>
                                <span class="badge badge-warning float-right">Live</span>
                            </a>
                        </li>

                        <!-- Account Section -->
                        <li class="nav-header">ACCOUNT</li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('account/profile'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'account_profile') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-user-alt"></i>
                                <p>Profile</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('account/setting'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'account_setting') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>Settings</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('account/logs'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'account_logs') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-history"></i>
                                <p>Access Logs</p>
                            </a>
                        </li>

                        <!-- Help Section -->
                        <li class="nav-header">HELP</li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('faqs'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'faqs') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-info-circle"></i>
                                <p>FAQs</p>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->

                <!-- Sidebar Footer -->
                <div class="sidebar-footer mt-4 pt-3 border-top text-center">
                    <small class="text-muted d-block mb-1">Device Status</small>
                    <?php if (isset($user_token["Token_Status"]) && $user_token["Token_Status"] == "00"): ?>
                        <span class="badge badge-danger">
                                <i class="fas fa-times-circle mr-1"></i> Disconnected
                            </span>
                    <?php else: ?>
                        <span class="badge badge-success">
                                <i class="fas fa-check-circle mr-1"></i> Connected
                            </span>
                    <?php endif; ?>
                </div>
            </div>
            <!-- /.sidebar -->
        </aside>