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

                <!-- Device Selector -->
                <?php if (!empty($sidebar_user_devices)): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-mobile-alt mr-1"></i>
                        <?php
                        $deviceLabel = 'All Devices';
                        if (!empty($active_device_id)) {
                            foreach ($sidebar_user_devices as $d) {
                                if (($d['device_id'] ?? '') === $active_device_id) {
                                    $parts = array_filter([$d['device_manufacturer'] ?? '', $d['device_model'] ?? '']);
                                    $deviceLabel = !empty($parts) ? implode(' ', $parts) : substr($d['device_id'], 0, 16);
                                    break;
                                }
                            }
                        }
                        ?>
                        <span class="d-none d-md-inline ml-1"><?= htmlspecialchars($deviceLabel) ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg">
                        <span class="dropdown-item-text"><strong>Select Device</strong></span>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item <?= empty($active_device_id) ? 'active' : '' ?>" href="<?= base_url('switch-device/all') ?>">
                            <i class="fas fa-layer-group mr-2"></i> All Devices
                        </a>
                        <div class="dropdown-divider"></div>
                        <?php foreach ($sidebar_user_devices as $d):
                            $did = $d['device_id'] ?? '';
                            $parts = array_filter([$d['device_manufacturer'] ?? '', $d['device_model'] ?? '']);
                            $name = !empty($parts) ? implode(' ', $parts) : substr($did, 0, 20);
                            $isActive = ($did === $active_device_id);
                        ?>
                        <a class="dropdown-item <?= $isActive ? 'active' : '' ?>" href="<?= base_url('switch-device/' . urlencode($did)) ?>">
                            <i class="fas fa-mobile-alt mr-2"></i> <?= htmlspecialchars($name) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </li>
                <?php endif; ?>
            </ul>

            <!-- SEARCH FORM -->
            <form class="form-inline ml-3 d-none d-md-flex" action="<?php echo base_url('globalsearch'); ?>" method="GET">
                <div class="input-group input-group-sm border rounded-pill bg-light px-2" style="width: 300px;">
                    <input class="form-control form-control-navbar border-0 bg-transparent" type="search" name="q" placeholder="Search SMS, Calls, Contacts..." aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-navbar text-muted" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">

                <!-- Fullscreen -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="Fullscreen">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>

                <!-- User Menu -->
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
                <img src="<?= base_url('assets/img/logo.png') ?>" alt="Eaves Droid Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Eaves Droid</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar" style="overflow-y: auto; overflow-x: hidden; height: calc(100vh - 57px);">

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

                        <!-- Data Section (Collapsible) -->
                        <?php 
                            $data_pages     = ['apps', 'call_logs', 'contacts', 'sms', 'files', 'location', 'activities', 'activity', 'advanced', 'remote_device', 'sim_configs'];
                            $data_sub_pages = ['sim_configs'];
                            $is_data_open   = (isset($pag) && in_array($pag, $data_pages))
                                           || (isset($sub_pag) && in_array($sub_pag, $data_sub_pages));
                        ?>
                        <li class="nav-item has-treeview <?php echo $is_data_open ? 'menu-open' : ''; ?>">
                            <a href="#" class="nav-link <?php echo $is_data_open ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-database"></i>
                                <p>
                                    DATA
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo base_url('apps'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'apps') ? 'active' : ''; ?>">
                                        <i class="fas fa-th nav-icon"></i>
                                        <p>Apps</p>
                                        <span class="badge badge-success float-right"><?php echo isset($total_apps) ? $total_apps : 0; ?></span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?php echo base_url('call_logs'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'call_logs') ? 'active' : ''; ?>">
                                        <i class="fas fa-phone nav-icon"></i>
                                        <p>Call Logs</p>
                                        <span class="badge badge-success float-right"><?php echo isset($total_calls) ? $total_calls : 0; ?></span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?php echo base_url('contacts'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'contacts') ? 'active' : ''; ?>">
                                        <i class="fas fa-address-book nav-icon"></i>
                                        <p>Contacts</p>
                                        <span class="badge badge-success float-right"><?php echo isset($total_contacts) ? $total_contacts : 0; ?></span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?php echo base_url('sms'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'sms') ? 'active' : ''; ?>">
                                        <i class="fas fa-comment-dots nav-icon"></i>
                                        <p>SMS</p>
                                        <span class="badge badge-success float-right"><?php echo isset($total_sms) ? $total_sms : 0; ?></span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?php echo base_url('files'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'files') ? 'active' : ''; ?>">
                                        <i class="fas fa-folder nav-icon"></i>
                                        <p>Files</p>
                                        <span class="badge badge-success float-right"><?php echo isset($total_files) ? $total_files : 0; ?></span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?php echo base_url('location'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'location') ? 'active' : ''; ?>">
                                        <i class="fas fa-map-pin nav-icon"></i>
                                        <p>Locations</p>
                                        <span class="badge badge-success float-right"><?php echo isset($total_locations) ? $total_locations : 0; ?></span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?php echo base_url('activities'); ?>"
                                       class="nav-link <?php echo (isset($pag) && ($pag == 'activities' || $pag == 'activity')) ? 'active' : ''; ?>">
                                        <i class="fas fa-running nav-icon"></i>
                                        <p>Activities</p>
                                        <span class="badge badge-success float-right"><?php echo isset($total_activities) ? $total_activities : 0; ?></span>
                                    </a>
                                </li>

                                <?php 
                                    $adv_total = ($total_device ?? 0) + ($total_network ?? 0) + ($total_accounts ?? 0) + 
                                                 ($total_calendar ?? 0) + ($total_app_usage ?? 0) + ($total_notifications ?? 0) + 
                                                 ($total_bluetooth ?? 0) + ($total_sensors ?? 0) + ($total_media ?? 0);
                                ?>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('advanced/device'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'advanced') ? 'active' : ''; ?>">
                                        <i class="fas fa-microchip nav-icon"></i>
                                        <p>
                                            Device Metrics
                                            <span class="badge badge-success float-right"><?php echo $adv_total; ?></span>
                                        </p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('remote-device'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'remote_device') ? 'active' : ''; ?>">
                                        <i class="fas fa-desktop nav-icon"></i>
                                        <p>Remote Device</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo base_url('sim-configs'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'sim_configs') ? 'active' : ''; ?>">
                                        <i class="fas fa-sim-card nav-icon"></i>
                                        <p>
                                            SIM Configs
                                            <span class="badge badge-success float-right"><?php echo $total_sim_configs ?? 0; ?></span>
                                        </p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Intelligence Section (Collapsible) -->
                        <?php 
                            $intel_pages = ['analysis', 'timeline'];
            $is_intel_open = (isset($pag) && in_array($pag, $intel_pages)) 
                          || (isset($sub_pag) && in_array($sub_pag, ['timeline', 'wellbeing', 'anomalies']));
                        ?>
                        <li class="nav-item has-treeview <?php echo $is_intel_open ? 'menu-open' : ''; ?>">
                            <a href="#" class="nav-link <?php echo $is_intel_open ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-brain"></i>
                                <p>
                                    INTELLIGENCE
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo base_url('analysis'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'analysis' && (!isset($sub_pag) || $sub_pag != 'timeline')) ? 'active' : ''; ?>">
                                        <i class="fas fa-chart-bar nav-icon"></i>
                                        <p>Analysis</p>
                                        <span class="badge badge-info float-right">AI</span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?php echo base_url('analysis/timeline'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'timeline') || (isset($sub_pag) && $sub_pag == 'timeline') ? 'active' : ''; ?>">
                                        <i class="fas fa-clock nav-icon"></i>
                                        <p>Timeline</p>
                                        <span class="badge badge-info float-right">New</span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?php echo base_url('analysis/wellbeing'); ?>"
                                       class="nav-link <?php echo (isset($sub_pag) && $sub_pag == 'wellbeing') ? 'active' : ''; ?>">
                                        <i class="fas fa-heartbeat nav-icon"></i>
                                        <p>Digital Wellbeing</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?php echo base_url('analysis/anomalies'); ?>"
                                       class="nav-link <?php echo (isset($sub_pag) && $sub_pag == 'anomalies') ? 'active' : ''; ?>">
                                        <i class="fas fa-exclamation-triangle nav-icon"></i>
                                        <p>Anomalies</p>
                                        <span class="badge badge-info float-right"><i class="fas fa-exclamation"></i></span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Account Section (Collapsible) -->
                        <?php 
                            $account_pages = ['account_profile', 'account_setting', 'account_devices', 'account_logs'];
                            $is_account_open = (isset($pag) && in_array($pag, $account_pages)) || (isset($sub_pag) && $sub_pag == 'blocklist');
                        ?>
                        <li class="nav-item has-treeview <?php echo $is_account_open ? 'menu-open' : ''; ?>">
                            <a href="#" class="nav-link <?php echo $is_account_open ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-user-shield"></i>
                                <p>
                                    ACCOUNT
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo base_url('account/profile'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'account_profile') ? 'active' : ''; ?>">
                                        <i class="fas fa-user-circle nav-icon"></i>
                                        <p>Profile</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?php echo base_url('account/setting'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'account_setting') ? 'active' : ''; ?>">
                                        <i class="fas fa-cog nav-icon"></i>
                                        <p>Settings</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?php echo base_url('account/devices'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'account_devices') ? 'active' : ''; ?>">
                                        <i class="fas fa-mobile-alt nav-icon"></i>
                                        <p>Device</p>
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a href="<?php echo base_url('analysis/blocklist'); ?>"
                                       class="nav-link <?php echo (isset($sub_pag) && $sub_pag == 'blocklist') ? 'active' : ''; ?>">
                                        <i class="fas fa-ban nav-icon"></i>
                                        <p>Blocklist</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?php echo base_url('account/logs'); ?>"
                                       class="nav-link <?php echo (isset($pag) && $pag == 'account_logs') ? 'active' : ''; ?>">
                                        <i class="fas fa-history nav-icon"></i>
                                        <p>Access Logs</p>
                                    </a>
                                </li>
                            </ul>
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