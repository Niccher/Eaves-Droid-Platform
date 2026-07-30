<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Storage Monitor</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/settings') ?>">Settings</a></li>
                        <li class="breadcrumb-item active">Storage Monitor</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (session()->getFlashdata('message')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= session()->getFlashdata('message') ?>
            </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= session()->getFlashdata('error') ?>
            </div>
            <?php endif; ?>

            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">Storage Monitor</h5>
                        <p class="mb-0 small text-muted">Configure disk usage thresholds and automated checks. When thresholds are exceeded, admins receive email notifications.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Sections</h3></div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item"><a href="<?= base_url('admin/settings') ?>" class="nav-link"><i class="fas fa-cog mr-1"></i> General</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/api') ?>" class="nav-link"><i class="fas fa-plug mr-1"></i> API</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/security') ?>" class="nav-link"><i class="fas fa-shield-alt mr-1"></i> Security</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/notifications') ?>" class="nav-link"><i class="fas fa-bell mr-1"></i> Notifications</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/maintenance') ?>" class="nav-link"><i class="fas fa-tools mr-1"></i> Maintenance</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage') ?>" class="nav-link active"><i class="fas fa-hdd mr-1"></i> Storage Monitor</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/email-triggers') ?>" class="nav-link"><i class="fas fa-envelope mr-1"></i> Email Triggers</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/backup') ?>" class="nav-link"><i class="fas fa-hdd mr-1"></i> Backup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/cron') ?>" class="nav-link"><i class="fas fa-clock mr-1"></i> Cron Jobs</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Storage Monitor Settings</h3>
                            <button type="button" class="btn btn-primary btn-sm" onclick="runStorageCheckNow()">
                                <i class="fas fa-sync-alt mr-1"></i> Check Now
                            </button>
                        </div>
                        <div class="card-body">
                            <form method="post" action="<?= base_url('admin/settings/update') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="section" value="storage">

                                <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                                        <div>
                                            <h5 class="text-info font-weight-bold mb-1">Storage Monitor</h5>
                                            <p class="mb-0 small text-muted">Configure disk usage thresholds and automated checks. When thresholds are exceeded, admins receive email notifications.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="threshold_warning"><strong>Warning Threshold (%)</strong></label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="threshold_warning" id="threshold_warning" min="1" max="99" value="<?= esc($settings['threshold_warning'] ?? 80) ?>">
                                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                                            </div>
                                            <small class="form-text text-muted">Send warning email when disk usage exceeds this percentage.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="threshold_critical"><strong>Critical Threshold (%)</strong></label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="threshold_critical" id="threshold_critical" min="1" max="99" value="<?= esc($settings['threshold_critical'] ?? 90) ?>">
                                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                                            </div>
                                            <small class="form-text text-muted">Send critical alert email when disk usage exceeds this percentage.</small>
                                        </div>
                                    </div>
                                </div>

<div class="row">
                                     <div class="col-md-6">
                                         <div class="form-group">
                                             <label for="check_interval_minutes"><strong>Check Interval (minutes)</strong></label>
                                             <input type="number" class="form-control" name="check_interval_minutes" id="check_interval_minutes" min="1" max="1440" value="<?= esc($settings['check_interval_minutes'] ?? 15) ?>">
                                             <small class="form-text text-muted">How often to run the storage check (cron job). Minimum 1 minute.</small>
                                         </div>
                                     </div>
                                     <div class="col-md-6">
                                         <div class="form-group">
                                             <label for="notify_admins"><strong>Notify Admins</strong></label>
                                             <div class="custom-control custom-switch">
                                                 <input type="checkbox" class="custom-control-input" name="notify_admins" id="notify_admins" value="1" <?= !empty($settings['notify_admins']) ? 'checked' : '' ?>>
                                                 <label class="custom-control-label" for="notify_admins">Send email to all admins when thresholds are breached</label>
                                             </div>
                                         </div>
                                     </div>
                                 </div>

                                 <div class="row mt-3">
                                     <div class="col-12">
                                         <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Settings</button>
                                     </div>
                                 </div>

                                 <hr class="my-4">
                                <h5>Current Disk Usage</h5>
                                <div class="row">
                                    <?php foreach ($disks as $disk): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <h5 class="card-title text-muted"><?= esc($disk['mount'] ?? $disk['path'] ?? 'Disk') ?></h5>
                                                <?php 
                                                    $pct = $disk['usage_percent'] ?? 0;
                                                    $color = $pct >= ($settings['threshold_critical'] ?? 90) ? 'bg-danger' : ($pct >= ($settings['threshold_warning'] ?? 80) ? 'bg-warning' : 'bg-success');
                                                ?>
                                                <div class="progress progress-lg mb-2" style="height: 30px;">
                                                    <div class="progress-bar <?= $color ?> progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $pct ?>%;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?= $pct ?>%
                                                    </div>
                                                </div>
                                                <p class="mb-0 small">
                                                    <strong><?= esc($disk['used_formatted'] ?? '0 B') ?></strong> / <?= esc($disk['total_formatted'] ?? '0 B') ?>
                                                    <br><span class="text-muted"><?= esc($disk['free_formatted'] ?? '0 B') ?> free</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="storageCheckModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-hdd mr-2"></i>Storage Check Results</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="storageCheckBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                    <p class="mt-2">Checking storage...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function runStorageCheckNow() {
    const btn = event.target.closest('button');
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Checking...';
    btn.disabled = true;

    $('#storageCheckModal').modal('show');
    $('#storageCheckBody').html(`
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
            <p class="mt-2">Checking storage...</p>
        </div>
    `);

    fetch('<?= base_url('admin/settings/storage/check-now') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(r => r.json()).then(data => {
        btn.innerHTML = original;
        btn.disabled = false;
        if (data.success) {
            let html = '';
            if (data.disks && data.disks.length) {
                data.disks.forEach(d => {
                    const statusBadge = d.status === 'critical' ? 'danger' : (d.status === 'warning' ? 'warning' : 'success');
                    const statusIcon = d.status === 'critical' ? 'fa-exclamation-triangle' : (d.status === 'warning' ? 'fa-exclamation-circle' : 'fa-check-circle');
                    html += '<div class="card mb-2 border-' + statusBadge + '">';
                    html += '<div class="card-body py-2">';
                    html += '<div class="d-flex justify-content-between align-items-center">';
                    html += '<strong><i class="fas ' + statusIcon + ' text-' + statusBadge + ' mr-1"></i> ' + d.mount + '</strong>';
                    html += '<span class="badge badge-' + statusBadge + '">' + d.status.toUpperCase() + '</span>';
                    html += '</div>';
                    html += '<div class="progress progress-xs mt-2"><div class="progress-bar bg-' + statusBadge + '" style="width:' + d.usage_percent + '%"></div></div>';
                    html += '<small class="text-muted">' + d.usage_percent + '% used</small>';
                    html += '</div></div>';
                });
            } else {
                html = '<p class="text-muted">No disk data returned.</p>';
            }
            $('#storageCheckBody').html(html);
        } else {
            $('#storageCheckBody').html('<div class="alert alert-danger mb-0">' + data.message + '</div>');
        }
    }).catch(() => {
        btn.innerHTML = original;
        btn.disabled = false;
        $('#storageCheckBody').html('<div class="alert alert-danger mb-0">Request failed</div>');
    });
}
</script>