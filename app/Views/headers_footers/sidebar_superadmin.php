    <body class="hold-transition sidebar-mini layout-fixed">
    <?php if (!empty($is_impersonating)): ?>
    <div class="alert alert-warning mb-0 text-center" style="border-radius:0; margin-bottom:0 !important;">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        You are acting as <strong><?php echo htmlspecialchars(session()->get('impersonated_username') ?? 'Unknown'); ?></strong>
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
                <!-- ML Engine Telemetry Live Badge -->
                <li class="nav-item dropdown" id="ml-telemetry-container">
                    <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#" title="ML Engine Status & Telemetry" style="cursor: pointer;">
                        <span id="ml-heartbeat-dot" class="badge badge-secondary mr-1" style="width: 9px; height: 9px; border-radius: 50%; padding: 0; display: inline-block; vertical-align: middle;"></span>
                        <span class="d-none d-md-inline small font-weight-bold" id="ml-heartbeat-text"><i class="fas fa-brain mr-1"></i> ML Engine</span>
                        <span class="badge badge-light ml-1 small d-none d-lg-inline" id="ml-heartbeat-latency" style="font-size: 10px;">--</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow-lg p-0" style="min-width: 320px;">
                        <div class="dropdown-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                            <span class="font-weight-bold"><i class="fas fa-microchip mr-1 text-info"></i> ML Telemetry</span>
                            <span class="badge badge-pill badge-secondary" id="ml-dropdown-status-badge">Checking...</span>
                        </div>
                        <div class="p-3 bg-light">
                            <div class="row text-center mb-2">
                                <div class="col-4 border-right">
                                    <div class="text-muted small">Latency</div>
                                    <div class="font-weight-bold text-dark" id="ml-dropdown-latency">--</div>
                                </div>
                                <div class="col-4 border-right">
                                    <div class="text-muted small">RAM / CPU</div>
                                    <div class="font-weight-bold text-dark small" id="ml-dropdown-load">--</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted small">Detectors</div>
                                    <div class="font-weight-bold text-dark" id="ml-dropdown-models">--</div>
                                </div>
                            </div>
                            <div class="border-top pt-2 mt-2">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span><i class="fas fa-database mr-1"></i> MySQL Link:</span>
                                    <span class="font-weight-bold" id="ml-dropdown-db">Checking...</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span><i class="fas fa-shield-alt mr-1"></i> Failover Mode:</span>
                                    <span class="text-success font-weight-bold" id="ml-dropdown-failover">Self-Healing Armed</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span><i class="fas fa-network-wired mr-1"></i> Backend URL:</span>
                                    <span class="text-truncate font-weight-bold" style="max-width: 160px;" id="ml-dropdown-url">--</span>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider m-0"></div>
                        <div class="p-2 bg-white d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="if(window.pollMlHeartbeat) window.pollMlHeartbeat(true)">
                                <i class="fas fa-sync-alt mr-1"></i> Ping Now
                            </button>
                            <a href="<?= base_url('admin/ml') ?>" class="btn btn-xs btn-primary">
                                <i class="fas fa-cog mr-1"></i> ML Settings
                            </a>
                        </div>
                    </div>
                </li>

                <!-- Global Omni Search Modal Trigger -->
                <li class="nav-item">
                    <a class="nav-link btn btn-sm btn-outline-light rounded-pill px-2 py-1 mx-1 text-dark d-flex align-items-center" href="#" data-toggle="modal" data-target="#globalOmniSearchModal" title="Global Omni Search (Ctrl+K)" style="border: 1px solid #ced4da; background: #f8f9fa;">
                        <i class="fas fa-search text-muted mr-1"></i>
                        <span class="d-none d-md-inline text-muted small mr-2">Search...</span>
                        <kbd class="small text-muted d-none d-sm-inline" style="background:#e9ecef; font-size:10px; padding: 2px 4px; border-radius: 3px;">Ctrl+K</kbd>
                    </a>
                </li>

                <!-- Quick Impersonate User Trigger -->
                <li class="nav-item">
                    <a class="nav-link text-warning" href="#" data-toggle="modal" data-target="#globalImpersonateModal" title="Quick Impersonate User">
                        <i class="fas fa-user-secret"></i>
                    </a>
                </li>

                <!-- Admin Support Chat Dropdown/Badge -->
                <?php
                $db = \Config\Database::connect();
                $unreadAdminChatsCount = $db->table('support_messages')
                    ->where('is_read', 0)
                    ->whereNotIn('sender_id', function (\CodeIgniter\Database\BaseBuilder $b) {
                        return $b->select('user_id')->from('auth_groups_users')->whereIn('group', ['admin', 'superadmin']);
                    })
                    ->countAllResults();
                ?>
                <li class="nav-item dropdown">
                    <a class="nav-link" href="<?php echo base_url('admin/support'); ?>" title="Client Support Messages">
                        <i class="fas fa-headset"></i>
                        <?php if ($unreadAdminChatsCount > 0): ?>
                            <span id="admin-chat-badge" class="badge badge-danger navbar-badge"><?php echo $unreadAdminChatsCount; ?></span>
                        <?php else: ?>
                            <span id="admin-chat-badge" class="badge badge-danger navbar-badge d-none"></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="Fullscreen">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>

                <?php
                $avatar = isset($user_info['profile_image']) ? $user_info['profile_image'] : null;
                $username = isset($user_info['username']) ? htmlspecialchars(ucwords($user_info['username'])) : 'User';

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
                        <span class="d-none d-md-inline ml-1"><?php echo $username; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <li class="user-header bg-danger">
                            <?php if ($avatar): ?>
                                <img src="<?php echo base_url('uploads/profiles/' . $avatar); ?>"
                                     class="img-circle elevation-2" alt="User Image">
                            <?php else: ?>
                                <div class="img-circle elevation-2 mx-auto d-flex align-items-center justify-content-center text-white font-weight-bold text-uppercase" 
                                     style="background-color: <?= $avatarColor ?>; width: 90px; height: 90px; font-size: 28px; display: flex !important; margin-bottom: 10px;">
                                    <?= $initials ?>
                                </div>
                            <?php endif; ?>
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

                        <li class="nav-item">
                            <a href="<?php echo base_url('superadmin/home'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'superadmin-home') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-shield-alt text-danger"></i>
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

                        <li class="nav-header">PLATFORM HUBS</li>

                        <!-- 1. Identity & Access Hub -->
                        <?php
                            $accessPages = ['admin-users', 'superadmin-users', 'admin-user-create', 'admin-user-edit', 'admin-tokens', 'superadmin-impersonate'];
                            $isAccessActive = isset($pag) && in_array($pag, $accessPages);
                        ?>
                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/users'); ?>"
                               class="nav-link <?php echo $isAccessActive ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-users-cog text-primary"></i>
                                <p>Identity &amp; Access</p>
                            </a>
                        </li>

                        <!-- 2. Fleet & Devices Hub -->
                        <?php
                            $fleetPages = ['superadmin-fleet', 'superadmin-fleet-home', 'superadmin-fleet-device', 'superadmin-fleet-tab', 'admin-remote-device', 'admin-defaults'];
                            $isFleetActive = isset($pag) && (in_array($pag, $fleetPages) || str_starts_with($pag, 'superadmin-fleet'));
                        ?>
                        <li class="nav-item">
                            <a href="<?php echo base_url('superadmin/fleet'); ?>"
                               class="nav-link <?php echo $isFleetActive ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-mobile-alt text-info"></i>
                                <p>Fleet &amp; Devices</p>
                            </a>
                        </li>

                        <!-- 3. Billing & Monetization Hub -->
                        <?php
                            $billingPages = ['superadmin-subscriptions', 'superadmin-subscription-detail', 'superadmin-payments', 'superadmin-plans', 'superadmin-plans-edit', 'superadmin-plans-history', 'superadmin-plans-definitions', 'superadmin-billing'];
                            $isBillingActive = isset($pag) && in_array($pag, $billingPages);
                        ?>
                        <li class="nav-item">
                            <a href="<?php echo base_url('superadmin/subscriptions'); ?>"
                               class="nav-link <?php echo $isBillingActive ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-credit-card text-success"></i>
                                <p>Billing &amp; Subscriptions</p>
                            </a>
                        </li>

                        <!-- 4. AI & Anomaly Engine Hub -->
                        <?php
                            $aiPages = ['admin-ml', 'admin-anomalies', 'superadmin-forensics', 'superadmin-forensics-export'];
                            $isAiActive = isset($pag) && in_array($pag, $aiPages);
                        ?>
                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/ml'); ?>"
                               class="nav-link <?php echo $isAiActive ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-brain text-warning"></i>
                                <p>AI &amp; Anomaly Engine</p>
                            </a>
                        </li>

                        <!-- 5. Infrastructure & Container Telemetry Hub -->
                        <?php
                            $infraPages = ['superadmin-infrastructure', 'admin-db-info', 'admin-retention'];
                            $isInfraActive = isset($pag) && in_array($pag, $infraPages);
                        ?>
                        <li class="nav-item">
                            <a href="<?php echo base_url('superadmin/infrastructure'); ?>"
                               class="nav-link <?php echo $isInfraActive ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-server text-info"></i>
                                <p>Infrastructure &amp; Health</p>
                            </a>
                        </li>

                        <!-- 6. Security Audit & Logs Hub -->
                        <?php
                            $auditPages = ['superadmin-audit', 'admin-reports'];
                            $isAuditActive = isset($pag) && (in_array($pag, $auditPages) || str_starts_with($pag, 'admin-log'));
                        ?>
                        <li class="nav-item">
                            <a href="<?php echo base_url('superadmin/audit'); ?>"
                               class="nav-link <?php echo $isAuditActive ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-clipboard-check text-danger"></i>
                                <p>Audit Trail &amp; Logs</p>
                            </a>
                        </li>

                        <!-- 7. Platform Settings Hub -->
                        <?php
                            $settingsPages = ['admin-settings', 'admin-settings-api', 'admin-settings-security', 'admin-settings-notifications', 'admin-maintenance', 'admin-backup', 'admin-settings-storage', 'admin-settings-email-triggers', 'admin-settings-cron'];
                            $isSettingsActive = isset($pag) && in_array($pag, $settingsPages);
                        ?>
                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/settings'); ?>"
                               class="nav-link <?php echo $isSettingsActive ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>Platform Settings</p>
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

        <!-- Global Omni Search Modal (Ctrl+K) -->
        <div class="modal fade" id="globalOmniSearchModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-header bg-dark text-white py-3 px-4 border-0">
                        <div class="input-group w-100">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent border-0 text-white"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="omniSearchModalInput" class="form-control bg-transparent text-white border-0 font-weight-bold" placeholder="Search users, SMS, calls, contacts, files, locations... (Press Esc to close)" autocomplete="off" style="box-shadow: none; font-size: 1.05rem;">
                            <div class="input-group-append">
                                <button type="button" class="close text-white pr-2" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body p-0" style="max-height: 480px; overflow-y: auto;" id="omniSearchModalResults">
                        <div class="text-center py-5 text-muted" id="omniSearchPlaceholder">
                            <i class="fas fa-search fa-2x mb-2 text-secondary"></i>
                            <p class="mb-0 small">Start typing to search across the entire forensic ecosystem...</p>
                            <p class="text-muted small mt-1"><kbd>Esc</kbd> to close &bull; <kbd>&uarr;</kbd><kbd>&darr;</kbd> to navigate</p>
                        </div>
                        <div id="omniSearchResultsContent" class="p-3 d-none"></div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-3 justify-content-between">
                        <small class="text-muted"><i class="fas fa-bolt text-warning mr-1"></i> Indexed live search</small>
                        <a href="<?= base_url('superadmin/omni-search') ?>" id="omniFullPageLink" class="btn btn-xs btn-outline-primary">
                            <i class="fas fa-external-link-alt mr-1"></i> Full Search Console
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Impersonate Modal -->
        <div class="modal fade" id="globalImpersonateModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content shadow-lg" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-header bg-danger text-white py-2 px-3">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-user-secret mr-2"></i>Quick Impersonate User</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-3">Impersonate any standard user to audit their telemetry and dashboard view directly.</p>
                        <div class="text-center py-2">
                            <a href="<?= base_url('superadmin/impersonate') ?>" class="btn btn-danger btn-block font-weight-bold">
                                <i class="fas fa-user-secret mr-1"></i> Launch Impersonation Manager
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<script>
(function() {
    var adminBadge = document.getElementById('admin-chat-badge');
    function updateAdminBadge(count) {
        if (!adminBadge) return;
        if (count > 0) {
            adminBadge.textContent = count;
            adminBadge.classList.remove('d-none');
        } else {
            adminBadge.textContent = '';
            adminBadge.classList.add('d-none');
        }
    }
    // Only run on non-admin-chat pages (chat page has its own poller)
    if (!document.getElementById('chatMessages')) {
        setInterval(function() {
            fetch('<?= base_url('api/v1/admin/support/unread-count') ?>', { credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(data) { updateAdminBadge(data.unread_count || 0); })
                .catch(function() {});
        }, 5000);
    }

    // ML Engine Live Telemetry Poller
    function updateMlTelemetry(data) {
        const dot = document.getElementById('ml-heartbeat-dot');
        const latency = document.getElementById('ml-heartbeat-latency');
        const dropStatus = document.getElementById('ml-dropdown-status-badge');
        const dropLatency = document.getElementById('ml-dropdown-latency');
        const dropLoad = document.getElementById('ml-dropdown-load');
        const dropModels = document.getElementById('ml-dropdown-models');
        const dropDb = document.getElementById('ml-dropdown-db');
        const dropUrl = document.getElementById('ml-dropdown-url');

        if (!dot) return;

        if (data && data.online) {
            dot.className = 'badge badge-success mr-1';
            dot.style.backgroundColor = '#28a745';
            if (latency) {
                latency.textContent = data.latency_ms + 'ms';
                latency.className = data.latency_ms < 200 ? 'badge badge-success ml-1 small' : 'badge badge-warning ml-1 small';
            }
            if (dropStatus) {
                dropStatus.className = 'badge badge-pill badge-success';
                dropStatus.textContent = 'Healthy (v' + (data.version || '2.5.0') + ')';
            }
            if (dropLatency) dropLatency.textContent = data.latency_ms + ' ms';
            if (dropLoad) {
                const mem = (data.memory && data.memory.used) ? Math.round(data.memory.used) + 'MB' : 'Active';
                const cpu = (data.cpu_percent !== undefined) ? ' / ' + data.cpu_percent + '%' : '';
                dropLoad.textContent = mem + cpu;
            }
            if (dropModels) dropModels.textContent = (data.models_count || 7) + ' Active';
            if (dropDb) {
                if (data.database_status === 'connected') {
                    dropDb.innerHTML = '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>' + (data.database_latency_ms ? data.database_latency_ms + 'ms' : 'Connected') + ' (' + (data.database_tables_verified || 10) + '/' + (data.database_total_tables || 10) + ' tbls)</span>';
                } else {
                    dropDb.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle mr-1"></i>' + (data.database_status || 'Disconnected') + '</span>';
                }
            }
            if (dropUrl) dropUrl.textContent = data.tested_url || 'FastAPI Container';
        } else {
            dot.className = 'badge badge-danger mr-1';
            dot.style.backgroundColor = '#dc3545';
            if (latency) {
                latency.textContent = 'Fallback';
                latency.className = 'badge badge-warning ml-1 small';
            }
            if (dropStatus) {
                dropStatus.className = 'badge badge-pill badge-warning';
                dropStatus.textContent = 'Offline (PHP-ML Fallback Active)';
            }
            if (dropLatency) dropLatency.textContent = 'N/A';
            if (dropLoad) dropLoad.textContent = 'Local PHP-ML';
            if (dropModels) dropModels.textContent = '15 Built-in';
            if (dropDb) dropDb.innerHTML = '<span class="text-info"><i class="fas fa-database mr-1"></i>Direct PHP DB</span>';
            if (dropUrl) dropUrl.textContent = 'Unreachable';
        }
    }

    window.pollMlHeartbeat = function(manual) {
        const dot = document.getElementById('ml-heartbeat-dot');
        if (manual && dot) dot.style.opacity = '0.5';
        fetch('<?= base_url('admin/ml/heartbeat') ?>', { credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (dot) dot.style.opacity = '1';
                updateMlTelemetry(data);
            })
            .catch(function() {
                if (dot) dot.style.opacity = '1';
                updateMlTelemetry({ online: false });
            });
    };

    // Global Omni Search Modal (Ctrl+K)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            $('#globalOmniSearchModal').modal('show');
        }
    });

    $('#globalOmniSearchModal').on('shown.bs.modal', function() {
        $('#omniSearchModalInput').focus().select();
    });

    let omniSearchTimeout = null;
    const omniInput = document.getElementById('omniSearchModalInput');
    if (omniInput) {
        omniInput.addEventListener('input', function() {
            const query = this.value.trim();
            const placeholder = document.getElementById('omniSearchPlaceholder');
            const content = document.getElementById('omniSearchResultsContent');
            const fullLink = document.getElementById('omniFullPageLink');

            if (fullLink) {
                fullLink.href = '<?= base_url('superadmin/omni-search') ?>?q=' + encodeURIComponent(query);
            }

            if (query.length < 2) {
                if (placeholder) placeholder.classList.remove('d-none');
                if (content) { content.classList.add('d-none'); content.innerHTML = ''; }
                return;
            }

            clearTimeout(omniSearchTimeout);
            omniSearchTimeout = setTimeout(function() {
                if (content) {
                    content.classList.remove('d-none');
                    content.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Searching across ecosystem...</div>';
                }
                if (placeholder) placeholder.classList.add('d-none');

                fetch('<?= base_url('superadmin/omni-search/ajax') ?>?q=' + encodeURIComponent(query), { credentials: 'same-origin' })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (!data.success || !data.results || data.total === 0) {
                            content.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-search-minus mr-1"></i> No matching records found for "<strong>' + escModalHtml(query) + '</strong>".</div>';
                            return;
                        }

                        let html = '<div class="small text-muted mb-2 font-weight-bold">Found ' + data.total + ' matches:</div>';
                        for (const catKey in data.results) {
                            const cat = data.results[catKey];
                            html += '<div class="card card-outline card-' + cat.color + ' mb-2 shadow-none border">';
                            html += '<div class="card-header py-1 px-2 bg-light d-flex justify-content-between align-items-center">';
                            html += '<span class="font-weight-bold text-' + cat.color + ' small"><i class="fas ' + cat.icon + ' mr-1"></i> ' + escModalHtml(cat.label) + '</span>';
                            html += '<span class="badge badge-' + cat.color + '" style="font-size:10px;">' + cat.items.length + '</span>';
                            html += '</div><div class="list-group list-group-flush">';
                            cat.items.forEach(function(item) {
                                html += '<div class="list-group-item list-group-item-action py-2 px-3">';
                                html += '<div class="d-flex justify-content-between align-items-start">';
                                html += '<div class="text-truncate mr-2" style="max-width: 80%;">';
                                html += '<div class="font-weight-bold text-dark small text-truncate">' + escModalHtml(item.preview || 'Untitled') + '</div>';
                                if (item.detail) {
                                    html += '<div class="text-muted small text-truncate" style="font-size:11px;">' + escModalHtml(item.detail) + '</div>';
                                }
                                html += '</div>';
                                if (item.username) {
                                    html += '<span class="badge badge-light border small text-muted"><i class="fas fa-user mr-1"></i>' + escModalHtml(item.username) + '</span>';
                                }
                                html += '</div></div>';
                            });
                            html += '</div></div>';
                        }
                        content.innerHTML = html;
                    })
                    .catch(function() {
                        if (content) content.innerHTML = '<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Search request failed.</div>';
                    });
            }, 250);
        });
    }

    function escModalHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }
})();
</script>
