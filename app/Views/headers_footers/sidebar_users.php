    <body class="hold-transition sidebar-mini layout-fixed">
    <?php if (session()->get('impersonated_by') !== null): ?>
    <div class="alert alert-warning mb-0 text-center" style="border-radius:0; margin-bottom:0 !important;">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        You are acting as <strong><?php echo htmlspecialchars(session()->get('impersonated_username') ?? ($user_info['username'] ?? 'User')); ?></strong>
        <form action="<?php echo base_url('superadmin/impersonate/stop'); ?>" method="post" class="d-inline">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-sm btn-danger ml-2">
                <i class="fas fa-sign-out-alt mr-1"></i>Exit Impersonation
            </button>
        </form>
    </div>
    <?php endif; ?>
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
                        <a class="dropdown-item <?= empty($active_device_id) ? 'active' : '' ?>" href="<?= base_url('account/switch-device/all') ?>">
                            <i class="fas fa-layer-group mr-2"></i> All Devices
                        </a>
                        <div class="dropdown-divider"></div>
                        <?php foreach ($sidebar_user_devices as $d):
                            $did = $d['device_id'] ?? '';
                            $parts = array_filter([$d['device_manufacturer'] ?? '', $d['device_model'] ?? '']);
                            $name = !empty($parts) ? implode(' ', $parts) : substr($did, 0, 20);
                            $isActive = ($did === $active_device_id);
                            // Format device added date
                            $addedDate = '';
                            if (!empty($d['created_at'])) {
                                try {
                                    $dt = new DateTime($d['created_at']);
                                    $addedDate = ' <span class="text-muted small ml-1">(added ' . $dt->format('D, M j, Y g:i A') . ')</span>';
                                } catch (Exception $e) {
                                    $addedDate = '';
                                }
                            }
                        ?>
                        <a class="dropdown-item <?= $isActive ? 'active' : '' ?>" href="<?= base_url('account/switch-device/' . urlencode($did)) ?>">
                            <i class="fas fa-mobile-alt mr-2"></i> <?= htmlspecialchars($name) ?><?= $addedDate ?>
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

                <!-- Support Chat Dropdown/Badge -->
                <?php
                $db = \Config\Database::connect();
                $unreadSupportCount = 0;
                if (auth()->loggedIn()) {
                    $unreadSupportCount = $db->table('support_messages')
                        ->where('client_id', auth()->id())
                        ->where('sender_id !=', auth()->id())
                        ->where('is_read', 0)
                        ->countAllResults();
                }
                ?>
                <li class="nav-item dropdown">
                    <a class="nav-link" href="<?php echo base_url('support/chat'); ?>" title="Support Chat">
                        <i class="fas fa-comments"></i>
                        <?php if ($unreadSupportCount > 0): ?>
                            <span id="support-chat-badge" class="badge badge-danger navbar-badge"><?php echo $unreadSupportCount; ?></span>
                        <?php else: ?>
                            <span id="support-chat-badge" class="badge badge-danger navbar-badge d-none"></span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Fullscreen -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="Fullscreen">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>

                    <!-- User Menu -->
                <?php
                // Force fresh subscription data - bypass any potential caching
                if (auth()->loggedIn()) {
                    $user = auth()->user();
                    $userId = $user->id;
                    // Force fresh DB query for subscription
                    $db = \Config\Database::connect();
                    $subscription = $db->table('user_subscriptions')
                        ->select('plan, status, billing_cycle')
                        ->where('user_id', $userId)
                        ->where('status', 'active')
                        ->where('current_period_end >=', date('Y-m-d H:i:s'))
                        ->orderBy('current_period_end', 'DESC')
                        ->limit(1)
                        ->get()
                        ->getRowArray();
                    $subscription_plan = $subscription['plan'] ?? 'free';
                } else {
                    $subscription_plan = 'free';
                }
                
                $avatar = isset($user_info['profile_image']) ? $user_info['profile_image'] : null;
                $username = isset($user_info['username']) ? htmlspecialchars(ucwords($user_info['username'])) : 'User';
                $plan_class = ($subscription_plan === 'free') ? 'badge-secondary' : (($subscription_plan === 'gold') ? 'badge-warning' : 'badge-danger');
                $plan_icon = ($subscription_plan === 'gold') ? 'fa-crown' : (($subscription_plan === 'platinum') ? 'fa-gem' : 'fa-star');
                $plan_label = ucfirst($subscription_plan);

                // Prepare initials avatar variables
                $colors = ['#f56954', '#f39c12', '#0073b7', '#00c0ef', '#00a65a', '#3c8dbc', '#39cccc', '#605ca8', '#ff851b'];
                $colorIndex = abs(crc32($username)) % count($colors);
                $avatarColor = $colors[$colorIndex];
                $initials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $username), 0, 2));
                if (empty($initials)) {
                    $initials = 'UD';
                }
                ?>
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <?php if ($avatar): ?>
                            <img src="<?php echo base_url('uploads/profiles/' . $avatar); ?>"
                                 class="user-image img-circle elevation-2"
                                 alt="User Image"
                                 style="width: 32px; height: 32px; object-fit: cover;">
                        <?php else: ?>
                            <div class="user-image img-circle elevation-2 d-inline-flex align-items-center justify-content-center text-white font-weight-bold text-uppercase" 
                                 style="background-color: <?= $avatarColor ?>; width: 32px; height: 32px; font-size: 11px; display: inline-flex !important; vertical-align: middle;">
                                <?= $initials ?>
                            </div>
                        <?php endif; ?>
                        <span class="d-none d-md-inline ml-1">
                                <?php echo $username; ?>
                                <span class="badge badge-sm badge-<?php echo $plan_class; ?> ml-1">
                                    <i class="fas <?php echo $plan_icon; ?> fa-xs"></i> <?php echo $plan_label; ?>
                                </span>
                            </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <li class="user-header bg-light">
                            <?php if ($avatar): ?>
                                <img src="<?php echo base_url('uploads/profiles/' . $avatar); ?>"
                                     class="img-circle elevation-2"
                                     alt="User Image">
                            <?php else: ?>
                                <div class="img-circle elevation-2 mx-auto d-flex align-items-center justify-content-center text-white font-weight-bold text-uppercase" 
                                     style="background-color: <?= $avatarColor ?>; width: 90px; height: 90px; font-size: 28px; display: flex !important; margin-bottom: 10px;">
                                    <?= $initials ?>
                                </div>
                            <?php endif; ?>
                            <p class="mt-2">
                                <?php echo $username; ?>
                                <br>
                                <span class="badge badge-sm <?php echo $plan_class; ?>">
                                    <i class="fas <?php echo $plan_icon; ?> fa-xs"></i> <?php echo $plan_label; ?> Subscriber
                                </span>
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
                        <li class="nav-header">MAIN</li>

                        <?php if (!empty($is_impersonating)): ?>
                        <li class="nav-item">
                            <form action="<?php echo base_url('superadmin/impersonate/stop'); ?>" method="post" class="nav-link">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="w-100 text-left btn btn-block btn-danger">
                                    <i class="nav-icon fas fa-sign-out-alt"></i>
                                    <p>Stop Impersonation</p>
                                </button>
                            </form>
                        </li>
                        <?php endif; ?>

                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="<?php echo base_url('home'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'home') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <?php if (auth()->user()->can('admin.access')): ?>
                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/dashboard'); ?>"
                               class="nav-link <?php echo (isset($pag) && strpos($pag, 'admin-') === 0) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-shield-alt"></i>
                                <p>Admin Panel</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Data Section (Collapsible) -->
                        <?php 
                            $data_pages     = ['apps', 'call_logs', 'contacts', 'sms', 'files', 'location', 'activities', 'activity', 'sms_analyse'];
                            $data_sub_pages = [];
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
                                       class="nav-link <?php echo (isset($pag) && ($pag == 'contacts' || $pag == 'sms_analyse')) ? 'active' : ''; ?>">
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
                                        class="nav-link <?php echo (isset($pag) && ($pag == 'location' || $pag == 'activities')) ? 'active' : ''; ?>">
                                         <i class="fas fa-map-pin nav-icon"></i>
                                         <p>Location &amp; Activity</p>
                                         <span class="badge badge-success float-right"><?php echo isset($total_location_activity) ? $total_location_activity : (($total_locations ?? 0) + ($total_activities ?? 0)); ?></span>
                                     </a>
                                 </li>

 </ul>
                        </li>

<!-- Remote Device Button -->
                        <li class="nav-item">
                            <a href="<?php echo base_url('remote-device'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'remote_device') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-mobile-alt"></i>
                                <p>Remote Device</p>
                            </a>
                        </li>

<!-- Hardware Button -->
                        <li class="nav-item">
                            <a href="<?php echo base_url('advanced/hardware'); ?>"
                               class="nav-link <?php echo (isset($active_tab) && in_array($active_tab, ['device_context', 'network_info', 'bluetooth', 'sensors', 'camera_info', 'battery_stats', 'proc_info', 'sim_configs', 'cell_towers', 'display_info', 'storage', 'nfc', 'hardware_graphics', 'hardware_network', 'hardware_landing', 'audio_devices', 'biometric', 'gnss_hardware', 'usb_devices', 'vibration', 'network_connectivity', 'display_graphics', 'sensors_location', 'media_hardware', 'storage_peripherals', 'shortrange_auth', 'device_fingerprint', 'hardware_dashboard'])) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-microchip"></i>
                                <p>Hardware</p>
                            </a>
                        </li>

                        <!-- Software Button -->
                        <li class="nav-item">
                            <a href="<?php echo base_url('advanced/software'); ?>"
                               class="nav-link <?php echo (isset($active_tab) && in_array($active_tab, ['accounts', 'calendar', 'app_usage', 'notifications', 'security_audit', 'remote_media', 'accessibility', 'input_methods', 'data_usage', 'saved_wifi', 'default_apps', 'alarms', 'app_security', 'network_security', 'telephony_network', 'system_locale', 'software_landing', 'app_permissions', 'clipboard', 'content_providers', 'crash_logs', 'digital_wellbeing', 'email', 'health_data', 'keyguard', 'screenshots', 'vpn_config'])) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-laptop-code"></i>
                                <p>Software</p>
                            </a>
                        </li>

                        <!-- Intelligence Section (Collapsible) -->
                        <?php 
                            $intel_pages = ['analysis', 'timeline'];
                            $intel_subs = ['timeline', 'wellbeing', 'anomalies', 'behavioral', 'correlation_engine', 'risk_care_plan'];
                            $is_intel_open = (isset($pag) && in_array($pag, $intel_pages)) 
                          || (isset($sub_pag) && in_array($sub_pag, $intel_subs));
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
                                    <a href="<?php echo base_url('analysis/anomalies/results'); ?>"
                                       class="nav-link <?php echo (isset($sub_pag) && $sub_pag == 'anomalies') ? 'active' : ''; ?>">
                                        <i class="fas fa-bug nav-icon"></i>
                                        <p>Anomaly Scanner</p>
                                        <span class="badge badge-danger float-right">Live</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                        <a href="<?php echo base_url('analysis/behavioral-anomalies'); ?>"
                                        class="nav-link <?php echo (isset($sub_pag) && $sub_pag == 'behavioral') ? 'active' : ''; ?>">
                                         <i class="fas fa-brain nav-icon"></i>
                                         <p>Behavioral Analysis</p>
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

                        <!-- Subscriptions & Help Section -->
                        <li class="nav-header">SUBSCRIPTIONS &amp; HELP</li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('billing'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'billing' && (!isset($sub_pag) || $sub_pag !== 'payments')) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-credit-card text-info"></i>
                                <p>Billing / Upgrade</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('account/payments'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'billing' && isset($sub_pag) && $sub_pag == 'payments') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-file-invoice-dollar text-success"></i>
                                <p>Payment History</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo base_url('support/chat'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag == 'support_chat') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-comments text-primary"></i>
                                <p>
                                    Support Chat
                                    <?php if (isset($unreadSupportCount) && $unreadSupportCount > 0): ?>
                                        <span class="badge badge-danger right"><?php echo $unreadSupportCount; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>

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

<?php if (auth()->loggedIn()): ?>
<script>
(function() {
    // Global navbar badge poller — runs on every page every 5 seconds
    // On the chat page itself, the chat poller handles badge updates via its own poll response
    if (document.querySelector('.nav-link[href*="support/chat"]')) {
        var supportBadge = document.getElementById('support-chat-badge');
        function updateSupportBadge(count) {
            if (!supportBadge) return;
            if (count > 0) {
                supportBadge.textContent = count;
                supportBadge.classList.remove('d-none');
            } else {
                supportBadge.textContent = '';
                supportBadge.classList.add('d-none');
            }
        }
        // Only run the poller if we're NOT on the chat page (chat page has its own poller)
        if (!document.getElementById('chatMessages')) {
            setInterval(function() {
                fetch('<?= base_url('api/v1/support/unread-count') ?>', { credentials: 'same-origin' })
                    .then(function(r) { return r.json(); })
                    .then(function(data) { updateSupportBadge(data.unread_count || 0); })
                    .catch(function() {});
            }, 5000);
        }
    }
})();
</script>
<?php endif; ?>