<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Backup & Restore</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/settings') ?>">Settings</a></li>
                        <li class="breadcrumb-item active">Backup</li>
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
                        <h5 class="text-info font-weight-bold mb-1">Backup Settings</h5>
                        <p class="mb-0 small text-muted">Automated and manual backup configuration — backup schedule, retention policy, storage location, and one-click backup execution.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card card-outline card-primary shadow-sm mb-3">
                        <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-sliders-h mr-1 text-primary"></i> Settings Hub</h3></div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item"><a href="<?= base_url('admin/settings') ?>" class="nav-link"><i class="fas fa-cog mr-2 text-primary"></i> General</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/api') ?>" class="nav-link"><i class="fas fa-plug mr-2 text-info"></i> API</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/security') ?>" class="nav-link"><i class="fas fa-shield-alt mr-2 text-danger"></i> Security</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/notifications') ?>" class="nav-link"><i class="fas fa-envelope mr-2 text-primary"></i> Emails & SMTP</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/fcm') ?>" class="nav-link"><i class="fas fa-fire mr-2 text-warning"></i> Firebase (FCM)</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/maintenance') ?>" class="nav-link"><i class="fas fa-tools mr-2 text-secondary"></i> Maintenance</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage') ?>" class="nav-link"><i class="fas fa-chart-pie mr-2 text-success"></i> Storage Monitor</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage-cleanup') ?>" class="nav-link"><i class="fas fa-broom mr-2 text-warning"></i> Storage Cleanup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/email-triggers') ?>" class="nav-link"><i class="fas fa-bell mr-2 text-info"></i> Email Triggers</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/backup') ?>" class="nav-link active"><i class="fas fa-database mr-2 text-info"></i> Backup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/cron') ?>" class="nav-link"><i class="fas fa-clock mr-2 text-purple"></i> Cron Jobs</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <form method="post" action="<?= base_url('admin/settings/update') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="section" value="backup">

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Backup Schedule & Retention</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="schedule_cron"><strong>Schedule (Cron Expression)</strong></label>
                                            <input type="text" class="form-control" name="schedule_cron" id="schedule_cron" value="<?= esc($settings['schedule_cron'] ?? '0 2 * * *') ?>" placeholder="0 2 * * * (daily at 2 AM)">
                                            <small class="form-text text-muted">Standard cron expression. Examples: <code>0 2 * * *</code> = daily 2 AM, <code>0 */6 * * *</code> = every 6 hours, <code>0 3 * * 0</code> = weekly Sunday 3 AM.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="retention_days"><strong>Retention (Days)</strong></label>
                                            <input type="number" class="form-control" name="retention_days" id="retention_days" min="0" max="365" value="<?= esc($settings['retention_days'] ?? 30) ?>">
                                            <small class="form-text text-muted">Delete backups older than this many days. Set to 0 to keep forever.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="storage_path"><strong>Backup Storage Path</strong></label>
                                            <input type="text" class="form-control" name="storage_path" id="storage_path" value="<?= esc($settings['storage_path'] ?? WRITEPATH . 'backups') ?>">
                                            <small class="form-text text-muted">Directory where backup files are stored. Must be writable by web server.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" name="compress" id="compress" value="1" <?= !empty($settings['compress']) ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="compress">Compress backups (gzip)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">Notification Settings</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="custom-control custom-switch mb-3">
                                            <input type="checkbox" class="custom-control-input" name="notify_on_success" id="notify_on_success" value="1" <?= !empty($settings['notify_on_success']) ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="notify_on_success">Email admins on successful backup</label>
                                        </div>
                                        <div class="custom-control custom-switch mb-3">
                                            <input type="checkbox" class="custom-control-input" name="notify_on_failure" id="notify_on_failure" value="1" <?= !empty($settings['notify_on_failure']) ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="notify_on_failure">Email admins on backup failure</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Settings</button>
                        </div>
                    </form>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Manual Backup</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="<?= base_url('admin/settings/backup/create') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Create Backup Now</button>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Database Backups</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Filename</th>
                                        <th>Size</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($backups)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">No backups yet.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($backups as $b): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($b['filename']) ?></code></td>
                                        <td><?= number_format($b['size'] / 1048576, 2) ?> MB</td>
                                        <td><?= htmlspecialchars($b['modified']) ?></td>
                                        <td>
                                            <a href="<?= base_url('admin/settings/backup/download/' . $b['filename']) ?>" class="btn btn-sm btn-success" title="Download"><i class="fas fa-download"></i></a>
                                            <form method="post" action="<?= base_url('admin/settings/backup/restore') ?>" style="display:inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="filename" value="<?= htmlspecialchars($b['filename']) ?>">
                                                <button type="submit" class="btn btn-sm btn-warning" title="Restore" onclick="return confirm('Restore this backup? This will overwrite current data.')"><i class="fas fa-undo"></i></button>
                                            </form>
                                            <a href="<?= base_url('admin/settings/backup/delete/' . $b['filename']) ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this backup?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>