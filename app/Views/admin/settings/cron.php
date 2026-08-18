<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cron Jobs & Scheduled Tasks</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/settings') ?>">Settings</a></li>
                        <li class="breadcrumb-item active">Cron Jobs</li>
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
                        <h5 class="text-info font-weight-bold mb-1">Cron Jobs & Scheduled Tasks</h5>
                        <p class="mb-0 small text-muted">Manage automated background tasks — schedule commands, monitor execution history, and configure job parameters for system maintenance and data processing.</p>
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
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage') ?>" class="nav-link"><i class="fas fa-hdd mr-1"></i> Storage Monitor</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage-cleanup') ?>" class="nav-link"><i class="fas fa-broom mr-1"></i> Storage Cleanup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/email-triggers') ?>" class="nav-link"><i class="fas fa-envelope mr-1"></i> Email Triggers</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/backup') ?>" class="nav-link"><i class="fas fa-hdd mr-1"></i> Backup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/cron') ?>" class="nav-link active"><i class="fas fa-clock mr-1"></i> Cron Jobs</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Scheduled Commands</h3>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#cronModal">
                                <i class="fas fa-plus mr-1"></i> Add Cron Job
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Command</th>
                                            <th>Schedule</th>
                                            <th>Description</th>
                                            <th>Last Run</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cronJobs as $job): ?>
                                        <tr>
                                            <td><code><?= esc($job['command']) ?></code></td>
                                            <td><span class="badge badge-info"><?= esc($job['schedule']) ?></span></td>
                                            <td><?= esc($job['description']) ?></td>
                                            <td><?= !empty($job['last_run']) ? date('M j, Y H:i', strtotime($job['last_run'])) : '<span class="text-muted">Never</span>' ?></td>
                                            <td>
                                                <?php if ($job['enabled']): ?>
                                                    <span class="badge badge-success">Enabled</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Disabled</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" onclick="editCron(<?= $job['id'] ?>)" title="Edit"><i class="fas fa-edit"></i></button>
                                                    <button type="button" class="btn btn-outline-<?= $job['enabled'] ? 'warning' : 'success' ?>" onclick="toggleCron(<?= $job['id'] ?>, <?= $job['enabled'] ? 0 : 1 ?>)" title="<?= $job['enabled'] ? 'Disable' : 'Enable' ?>"><i class="fas fa-<?= $job['enabled'] ? 'pause' : 'play' ?>"></i></button>
                                                    <button type="button" class="btn btn-outline-info" onclick="runCronNow(<?= $job['id'] ?>)" title="Run Now"><i class="fas fa-bolt"></i></button>
                                                    <button type="button" class="btn btn-outline-danger" onclick="deleteCron(<?= $job['id'] ?>)" title="Delete"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($cronJobs)): ?>
                                        <tr><td colspan="6" class="text-center text-muted py-4">No cron jobs configured. Click "Add Cron Job" to create one.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Cron Execution Logs -->
                    <div class="card mt-4">
                        <div class="card-header"><h3 class="card-title">Recent Executions</h3></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Command</th>
                                            <th>Started</th>
                                            <th>Finished</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                            <th>Output</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($executionLogs as $log): ?>
                                        <tr>
                                            <td><code><?= esc($log['command']) ?></code></td>
                                            <td><?= date('M j, Y H:i:s', strtotime($log['started_at'])) ?></td>
                                            <td><?= $log['finished_at'] ? date('M j, Y H:i:s', strtotime($log['finished_at'])) : '<span class="text-muted">Running...</span>' ?></td>
                                            <td><?= $log['duration_ms'] ? number_format($log['duration_ms'] / 1000, 2) . 's' : '-' ?></td>
                                            <td>
                                                <?php if ($log['status'] === 'success'): ?>
                                                    <span class="badge badge-success">Success</span>
                                                <?php elseif ($log['status'] === 'failed'): ?>
                                                    <span class="badge badge-danger">Failed</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Running</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($log['output'])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick='showOutput(<?= json_encode($log["output"]) ?>)'><i class="fas fa-terminal"></i> View</button>
                                                <?php else: ?>
                                                    <span class="text-muted">No output</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($executionLogs)): ?>
                                        <tr><td colspan="6" class="text-center text-muted py-4">No execution logs yet.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Add/Edit Cron Modal -->
<div class="modal fade" id="cronModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('admin/settings/cron/save') ?>" id="cronForm" onsubmit="return saveCron(event)">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="cronId">
                <input type="hidden" name="<?= csrf_token() ?>" id="csrfHash" value="<?= csrf_hash() ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Cron Job</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Command <span class="text-danger">*</span></label>
                                    <select class="form-control" name="command" id="cronCommand" required>
                                        <option value="">Select a command...</option>
                                        <optgroup label="System">
                                        <option value="maintenance:check">maintenance:check — Auto-activate/deactivate scheduled maintenance</option>
                                        </optgroup>
                                        <optgroup label="Backup & Storage">
                                        <option value="backup:create">backup:create — Create full database backup with gzip</option>
                                        <option value="storage:check">storage:check — Check disk usage and send alerts</option>
                                        </optgroup>
                                        <optgroup label="Queue">
                                        <option value="queue:process">queue:process — Process pending upload queue items</option>
                                        </optgroup>
                                        <optgroup label="AnomaliesController & ML">
                                        <option value="anomalies:run-job">anomalies:run-job — Run ML anomaly detection job</option>
                                        </optgroup>
                                        <optgroup label="Housekeeping">
                                        <option value="logs:clear">logs:clear — Clear old log files</option>
                                        <option value="cache:clear">cache:clear — Clear system cache files</option>
                                        <option value="queue:cleanup">queue:cleanup — Reset stuck upload queue items</option>
                                        <option value="tokens:cleanup">tokens:cleanup — Purge expired tokens</option>
                                        <option value="ml:cleanup">ml:cleanup — Mark stale ML jobs as failed</option>
                                        <option value="storage:pollution">storage:pollution — Remove orphaned file records</option>
                                        <option value="notifications:digest">notifications:digest — Send batch notification digest</option>
                                        </optgroup>
                                        <optgroup label="Database">
                                        <option value="migrate">migrate — Run all new migrations</option>
                                        <option value="db:seed">db:seed — Run database seeder</option>
                                        </optgroup>
                                        <optgroup label="Custom">
                                        <option value="custom">Custom command...</option>
                                        </optgroup>
                                    </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Custom Command</label>
                                <input type="text" class="form-control" name="custom_command" id="customCommand" placeholder="e.g., my:custom:command --arg=value" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Schedule (Cron Expression) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="schedule" id="cronSchedule" required placeholder="e.g., */15 * * * * (every 15 minutes)">
                        <small class="form-text text-muted">
                            <a href="https://crontab.guru/" target="_blank">Cron expression reference</a> |
                            Common: <code>* * * * *</code> (every min) | <code>*/15 * * * *</code> (15 min) | <code>0 * * * *</code> (hourly) | <code>0 2 * * *</code> (daily 2AM) | <code>0 2 * * 0</code> (weekly Sun 2AM)
                        </small>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="What does this cron job do?"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" name="enabled" id="cronEnabled" value="1" checked>
                            <label class="custom-control-label" for="cronEnabled">Enabled</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Arguments (JSON)</label>
                        <textarea class="form-control" name="arguments" rows="2" placeholder='{"job_id": 123}'></textarea>
                        <small class="form-text text-muted">Optional JSON arguments passed to the command.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Cron Job</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Output Modal -->
<div class="modal fade" id="outputModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Command Output</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <pre id="outputContent" style="background:#1e1e1e;color:#d4d4d4;padding:16px;border-radius:4px;max-height:60vh;overflow:auto;font-family:monospace;font-size:12px;"></pre>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('cronCommand').addEventListener('change', function() {
    document.getElementById('customCommand').disabled = this.value !== 'custom';
});

function showToast(message, type) {
    const iconMap = {success: 'check-circle', error: 'times-circle', warning: 'exclamation-circle', info: 'info-circle'};
    Swal.fire({toast: true, position: 'top-end', icon: type || 'success', title: message, showConfirmButton: false, timer: 3000, timerProgressBar: true});
}

function saveCron(event) {
    event.preventDefault();
    var form = document.getElementById('cronForm');
    var data = new URLSearchParams(new FormData(form));
    data.set('<?= csrf_token() ?>', document.getElementById('csrfHash').value);
    Swal.fire({title: 'Saving...', allowOutsideClick: false, didOpen: () => {Swal.showLoading()}});
    fetch('<?= base_url('admin/settings/cron/save') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: data.toString()
    }).then(function(r) { return r.json(); }).then(function(result) {
        Swal.close();
        if (result.success) {
            $('#cronModal').modal('hide');
            showToast(result.message || 'Cron job saved successfully.', 'success');
            location.reload();
        } else {
            showToast(result.message || 'Failed to save cron job.', 'error');
        }
    }).catch(function() {
        Swal.close();
        showToast('Request failed. Please try again.', 'error');
    });
    return false;
}

function editCron(id) {
    fetch('<?= base_url('admin/settings/cron/get') ?>/' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalTitle').textContent = 'Edit Cron Job';
                document.getElementById('cronId').value = data.job.id;
                document.getElementById('cronCommand').value = data.job.command;
                document.getElementById('cronSchedule').value = data.job.schedule;
                document.getElementById('cronEnabled').checked = data.job.enabled == 1;
                document.querySelector('textarea[name="description"]').value = data.job.description;
                document.querySelector('textarea[name="arguments"]').value = data.job.arguments;
                if (data.job.custom_command) {
                    document.getElementById('cronCommand').value = 'custom';
                    document.getElementById('customCommand').value = data.job.custom_command;
                    document.getElementById('customCommand').disabled = false;
                }
                $('#cronModal').modal('show');
            }
        });
}

function toggleCron(id, enabled) {
    Swal.fire({title: 'Confirm', text: 'Are you sure you want to ' + (enabled ? 'enable' : 'disable') + ' this cron job?', icon: 'question', showCancelButton: true, confirmButtonText: 'Yes', cancelButtonText: 'Cancel'}).then((result) => {
        if (!result.isConfirmed) return;
        fetch('<?= base_url('admin/settings/cron/toggle') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
            body: 'id=' + id + '&enabled=' + enabled + '&<?= csrf_token() ?>=<?= csrf_hash() ?>'
        }).then(r => r.json()).then(data => {
            if (data.success) { showToast('Cron job ' + (enabled ? 'enabled' : 'disabled') + '.', 'success'); location.reload(); }
            else showToast(data.message || 'Failed to toggle cron job.', 'error');
        }).catch(function() { showToast('Request failed.', 'error'); });
    });
}

function runCronNow(id) {
    Swal.fire({title: 'Run now?', text: 'This will execute the cron job immediately.', icon: 'question', showCancelButton: true, confirmButtonText: 'Run', cancelButtonText: 'Cancel'}).then((result) => {
        if (!result.isConfirmed) return;
        Swal.fire({title: 'Running...', allowOutsideClick: false, didOpen: () => {Swal.showLoading()}});
        fetch('<?= base_url('admin/settings/cron/run') ?>/' + id, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).then(r => r.json()).then(data => {
            Swal.close();
            if (data.success) {
                showToast(data.message || 'Job executed successfully.', 'success');
                if (data.output) showOutput(data.output);
                location.reload();
            } else {
                showToast(data.message || 'Job failed.', 'error');
                if (data.output) showOutput(data.output);
            }
        }).catch(function() { Swal.close(); showToast('Request failed.', 'error'); });
    });
}

function deleteCron(id) {
    Swal.fire({title: 'Delete?', text: 'This cron job will be permanently removed.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete', cancelButtonText: 'Cancel', confirmButtonColor: '#dc3545'}).then((result) => {
        if (!result.isConfirmed) return;
        fetch('<?= base_url('admin/settings/cron/delete') ?>/' + id, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).then(r => r.json()).then(data => {
            if (data.success) { showToast('Cron job deleted.', 'success'); location.reload(); }
            else showToast(data.message || 'Failed to delete cron job.', 'error');
        }).catch(function() { showToast('Request failed.', 'error'); });
    });
}

function showOutput(output) {
    document.getElementById('outputContent').textContent = output;
    $('#outputModal').modal('show');
}

$('#cronModal').on('hidden.bs.modal', function() {
    document.getElementById('cronForm').reset();
    document.getElementById('cronId').value = '';
    document.getElementById('modalTitle').textContent = 'Add Cron Job';
    document.getElementById('customCommand').disabled = true;
});
</script>