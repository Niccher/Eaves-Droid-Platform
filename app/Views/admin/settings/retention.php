<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <h1><i class="fas fa-clock text-danger mr-2"></i>Data Retention & Purge</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/settings') ?>">Settings</a></li>
                        <li class="breadcrumb-item active">Retention & Purge</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content pt-1">
        <div class="container-fluid">
            <!-- Retention/Purge Callout -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="callout callout-danger">
                        <h5><i class="fas fa-clock mr-2"></i>Data Retention & Purge</h5>
                        <p class="mb-1">Configure data retention policies per category and run GDPR-style purges. Data older than the retention period will be permanently deleted when purge is executed.</p>
                        <ul class="mb-0 small">
                            <li><strong>Retention Period:</strong> Set per-category retention in days. 0 or empty = never purge.</li>
                            <li><strong>Enabled:</strong> Toggle to enable/disable automatic purge for each category.</li>
                            <li><strong>Manual Purge:</strong> Run immediately with current settings. Deletes all records older than retention period.</li>
                            <li><strong>Automated:</strong> Add cron job <code>retention:purge</code> with schedule <code>0 2 * * *</code> (daily at 2 AM) for automated purges.</li>
                            <li><strong>Warning:</strong> Purged data is permanently deleted and cannot be recovered. Ensure backups exist.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-2">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= count($stats) ?></h3>
                            <p>Categories</p>
                        </div>
                        <div class="icon"><i class="fas fa-list"></i></div>
                        <div class="small-box-footer">
                            Configurable categories
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= count(array_filter($stats, fn($s) => $s['enabled'])) ?></h3>
                            <p>Enabled</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                        <div class="small-box-footer">
                            Auto-purge enabled
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= array_sum(array_column($stats, 'total')) ?></h3>
                            <p>Total Records</p>
                        </div>
                        <div class="icon"><i class="fas fa-database"></i></div>
                        <div class="small-box-footer">
                            Across all categories
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= count(array_filter($stats, fn($s) => $s['retention_days'] <= 30 && $s['enabled'])) ?></h3>
                            <p>Short Retention</p>
                        </div>
                        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="small-box-footer">
                            ≤ 30 days & enabled
                        </div>
                    </div>
                </div>
            </div>

            <!-- Retention Configuration -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="card">
                        <?= form_open('admin/settings/retention/save', ['id' => 'configForm', 'method' => 'post']) ?>
                        <?= form_open('admin/settings/retention/purge', ['id' => 'purgeForm', 'method' => 'post']) ?>
                            <div class="card-header d-flex align-items-center flex-wrap">
                                <h3 class="card-title mb-0"><i class="fas fa-cogs mr-2"></i>Retention Configuration</h3>
                                <div class="ml-auto d-flex">
                                    <button type="submit" form="configForm" class="btn btn-primary btn-sm mr-2" title="Save the retention days and auto-purge toggles for all categories">
                                        <i class="fas fa-save mr-1"></i> Save Configuration
                                    </button>
                                    <button type="submit" form="purgeForm" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt mr-1"></i> Run Purge Now
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 280px;">Datapoint Category</th>
                                                <th class="text-center" style="width: 130px;">Total Records</th>
                                                <th class="text-center" style="width: 160px;">Retention (Days)</th>
                                                <th class="text-center" style="width: 120px;">Auto Purge</th>
                                                <th class="text-center" style="width: 100px;">Purge?</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $categoryMeta = [
                                                    'apps'                  => ['icon' => 'fas fa-cubes text-primary', 'name' => 'Installed Apps'],
                                                    'calls'                 => ['icon' => 'fas fa-phone-alt text-success', 'name' => 'Call Logs'],
                                                    'sms'                   => ['icon' => 'fas fa-sms text-info', 'name' => 'SMS Messages'],
                                                    'contacts'              => ['icon' => 'fas fa-address-book text-warning', 'name' => 'Contacts'],
                                                    'files'                 => ['icon' => 'fas fa-folder-open text-danger', 'name' => 'Files & Media'],
                                                    'location_activities'   => ['icon' => 'fas fa-map-marked-alt text-purple', 'name' => 'Location & Activities'],
                                                    'misc_hardware_software'=> ['icon' => 'fas fa-microchip text-secondary', 'name' => 'Hardware & Software Misc'],
                                                    'app_usage'             => ['icon' => 'fas fa-chart-pie text-indigo', 'name' => 'App Usage Stats'],
                                                    'app_notifications'     => ['icon' => 'fas fa-bell text-dark', 'name' => 'App Notifications'],
                                                ];
                                            ?>
                                            <?php foreach ($stats as $label => $stat): ?>
                                                <?php 
                                                    $meta = $categoryMeta[$label] ?? ['icon' => 'fas fa-database text-muted', 'name' => ucfirst(str_replace('_', ' ', $label))];
                                                ?>
                                                <tr>
                                                    <td class="bg-white">
                                                        <i class="<?= $meta['icon'] ?> mr-2 fa-lg"></i>
                                                        <strong class="text-dark"><?= $meta['name'] ?></strong>
                                                        <small class="text-muted d-block font-mono" style="font-size:11px;"><?= $label ?></small>
                                                    </td>
                                                    <td class="text-center align-middle font-weight-bold"><?= number_format($stat['total']) ?></td>
                                                    <td class="text-center align-middle">
                                                        <div class="input-group input-group-sm mx-auto" style="max-width: 120px;">
                                                            <input type="number" form="configForm" name="retention_<?= $label ?>_days" class="form-control text-center font-weight-bold" value="<?= $stat['retention_days'] ?>" min="0">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">days</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <div class="custom-control custom-switch">
                                                            <input type="hidden" form="configForm" name="retention_<?= $label ?>_enabled" value="0">
                                                            <input type="checkbox" form="configForm" class="custom-control-input" id="ret_<?= $label ?>_enabled" name="retention_<?= $label ?>_enabled" value="1" <?= $stat['enabled'] ? 'checked' : '' ?>>
                                                            <label class="custom-control-label" for="ret_<?= $label ?>_enabled"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" form="purgeForm" class="custom-control-input" id="ret_<?= $label ?>_purge" name="categories[]" value="<?= $label ?>">
                                                            <label class="custom-control-label" for="ret_<?= $label ?>_purge"></label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($can_reset)): ?>
    <section class="content pt-1">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <div class="card card-outline card-danger shadow">
                        <div class="card-header bg-danger">
                            <h3 class="card-title mb-0 text-white font-weight-bold">
                                <i class="fas fa-bomb mr-2"></i>Danger Zone — Factory Reset
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="<?= base_url('admin/settings/retention/reset') ?>" id="factoryResetForm">
                                <?= csrf_field() ?>
                                <input type="hidden" name="confirm" id="factoryResetConfirm" value="">

                                <div class="row mb-3">
                                    <div class="col-md-7">
                                        <label class="font-weight-bold"><i class="fas fa-list-ul mr-1"></i> Reset Mode</label>
                                        <div class="custom-control custom-radio mb-2">
                                            <input type="radio" class="custom-control-input" id="mode_soft" name="reset_mode" value="soft" checked>
                                            <label class="custom-control-label" for="mode_soft">
                                                <strong>Option A: Soft Data Wipe (Default)</strong><br>
                                                <small class="text-muted">Keep user accounts & system configs intact. Wipe extracted device data (SMS, calls, locations, media, app stats).</small>
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio mb-2">
                                            <input type="radio" class="custom-control-input" id="mode_hard" name="reset_mode" value="hard">
                                            <label class="custom-control-label" for="mode_hard">
                                                <strong>Option B: Full Hard Wipe</strong><br>
                                                <small class="text-muted">Delete everything including non-admin user accounts, device pairings, and extracted data.</small>
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio mb-2">
                                            <input type="radio" class="custom-control-input" id="mode_logs" name="reset_mode" value="logs_only">
                                            <label class="custom-control-label" for="mode_logs">
                                                <strong>Option C: Logs & Temp Files Only</strong><br>
                                                <small class="text-muted">Wipe cache, reports, exports, and debug logs while leaving user accounts & device data intact.</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-5 border-left">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold"><i class="fas fa-shield-alt mr-1"></i> Safety Net & Security</label>
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="checkbox" class="custom-control-input" id="auto_backup" name="auto_backup" value="1" checked>
                                                <label class="custom-control-label font-weight-bold" for="auto_backup">
                                                    <i class="fas fa-database text-success mr-1"></i> Auto-Backup Before Reset
                                                </label>
                                                <br><small class="text-muted">Generates a compressed database backup before running the wipe operation.</small>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="admin_password" class="font-weight-bold text-danger"><i class="fas fa-key mr-1"></i> Confirm Admin Password</label>
                                            <input type="password" name="admin_password" id="admin_password" class="form-control" placeholder="Enter your login password" required>
                                            <small class="text-muted">Required to verify superadmin authorization.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-warning py-2 small mb-3">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Important:</strong> An immediate alert notification will be sent to <strong>all registered users</strong> notifying them of this system reset.
                                </div>

                                <button type="button" class="btn btn-danger btn-block font-weight-bold py-2" id="factoryResetBtn">
                                    <i class="fas fa-bomb mr-1"></i> Wipe & Reset System
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
$(function() {
    // Proper confirmation popup for the retention purge.
    $('#purgeForm').on('submit', function(e) {
        e.preventDefault();
        var $form = this;
        Swal.fire({
            title: 'Run Purge Now?',
            html: 'This will <strong>PERMANENTLY DELETE</strong> data older than the retention period for the selected categories.<br><br>This action <strong>cannot be undone</strong>.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, purge it',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then(function(result) {
            if (result.isConfirmed) {
                $form.submit();
            }
        });
        return false;
    });

    // Proper confirmation popup for the factory reset.
    $('#factoryResetBtn').on('click', function(e) {
        var pwd = $('#admin_password').val();
        if (!pwd) {
            Swal.fire({
                title: 'Password Required',
                text: 'Please enter your admin account password in the field provided to authorize this reset.',
                icon: 'error'
            });
            $('#admin_password').focus();
            return false;
        }

        var mode = $('input[name="reset_mode"]:checked').val();
        var modeDesc = 'Soft Data Wipe (Extracted data & media)';
        if (mode === 'hard') modeDesc = 'FULL HARD WIPE (User accounts & all data)';
        if (mode === 'logs_only') modeDesc = 'Logs & Temp Files Only';

        Swal.fire({
            title: 'System Factory Reset',
            html: 'Selected Mode: <strong>' + modeDesc + '</strong><br><br>' +
                  'This action is <strong>IRREVERSIBLE</strong>. An email notification will be dispatched to <strong>all registered users</strong>.<br><br>' +
                  'Type <strong>RESET</strong> in the box below to confirm.',
            icon: 'warning',
            input: 'text',
            inputPlaceholder: 'Type RESET to confirm',
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off'
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, execute reset',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            inputValidator: function(value) {
                if (!value || value.toUpperCase() !== 'RESET') {
                    return 'You must type "RESET" to confirm';
                }
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                $('#factoryResetConfirm').val('RESET');
                $('#factoryResetForm')[0].submit();
            }
        });
    });
});
</script>