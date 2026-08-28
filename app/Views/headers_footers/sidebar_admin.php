    <body class="hold-transition sidebar-mini layout-fixed">
    <?php if (session()->get('impersonated_by') !== null): ?>
    <div class="alert alert-warning mb-0 text-center" style="border-radius:0; margin-bottom:0 !important;">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        You are acting as <strong><?php echo htmlspecialchars(session()->get('impersonated_username') ?? 'User'); ?></strong>
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
            </ul>

            <ul class="navbar-nav ml-auto">
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
                        <li class="user-header bg-light">
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

                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/support'); ?>"
                               class="nav-link <?php echo (isset($pag) && $pag === 'admin-support') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-comments text-info"></i>
                                <p>
                                    Support Chat
                                    <?php if (isset($unreadAdminChatsCount) && $unreadAdminChatsCount > 0): ?>
                                        <span class="badge badge-danger right"><?php echo $unreadAdminChatsCount; ?></span>
                                    <?php endif; ?>
                                </p>
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
})();
</script>
