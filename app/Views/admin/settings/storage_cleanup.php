<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Storage Cleanup Settings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/settings') ?>">Settings</a></li>
                        <li class="breadcrumb-item active">Storage Cleanup</li>
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
                    <i class="fas fa-recycle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">Storage Cleanup</h5>
                        <p class="mb-0 small text-muted">Monitor storage usage and configure automated cleanup thresholds for logs, cache, backups, and more.</p>
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
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage-cleanup') ?>" class="nav-link active"><i class="fas fa-broom mr-1"></i> Storage Cleanup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/email-triggers') ?>" class="nav-link"><i class="fas fa-envelope mr-1"></i> Email Triggers</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/backup') ?>" class="nav-link"><i class="fas fa-hdd mr-1"></i> Backup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/cron') ?>" class="nav-link"><i class="fas fa-clock mr-1"></i> Cron Jobs</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Storage Usage Breakdown</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-info"><i class="fas fa-file mr-1"></i> All FilesController</h6>
                                            <div class="h4 mb-0"><?= esc($all_files_size_formatted ?? '0 B') ?></div>
                                            <small class="text-muted">Uploaded files on disk</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-warning"><i class="fas fa-database mr-1"></i> Database</h6>
                                            <div class="h4 mb-0"><?= esc($db_size_formatted ?? '0 B') ?></div>
                                            <small class="text-muted">All tables combined</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-primary"><i class="fas fa-hdd mr-1"></i> Total Used</h6>
                                            <div class="h4 mb-0"><?= esc($total_used_size_formatted ?? '0 B') ?></div>
                                            <small class="text-muted">FilesController + Database</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header"><h3 class="card-title">Cleanup Settings</h3></div>
                        <div class="card-body">
                            <form method="post" action="<?= base_url('admin/settings/update') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="section" value="storage">
                                <input type="hidden" name="return_url" value="<?= base_url('admin/settings/storage-cleanup') ?>">

                                <p class="small text-muted">Configure how long to retain data in each category. Cron jobs will clean up items older than these thresholds.</p>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="logs_retention_days"><strong>Logs Retention (days)</strong></label>
                                            <input type="number" class="form-control" name="logs_retention_days" id="logs_retention_days" min="1" max="365" value="<?= esc($settings['logs_retention_days'] ?? 30) ?>">
                                            <small class="form-text text-muted">Delete log files older than this. Run <code>logs:clear</code> cron.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="queue_cleanup_timeout_hours"><strong>Queue Stuck Timeout (hours)</strong></label>
                                            <input type="number" class="form-control" name="queue_cleanup_timeout_hours" id="queue_cleanup_timeout_hours" min="1" max="168" value="<?= esc($settings['queue_cleanup_timeout_hours'] ?? 2) ?>">
                                            <small class="form-text text-muted">Reset queue items stuck in processing state. Run <code>queue:cleanup</code> cron.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ml_cleanup_timeout_hours"><strong>ML Job Timeout (hours)</strong></label>
                                            <input type="number" class="form-control" name="ml_cleanup_timeout_hours" id="ml_cleanup_timeout_hours" min="1" max="72" value="<?= esc($settings['ml_cleanup_timeout_hours'] ?? 1) ?>">
                                            <small class="form-text text-muted">Mark ML jobs as failed after this timeout. Run <code>ml:cleanup</code> cron.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="cleanup_backups_days"><strong>Backup Retention (days)</strong></label>
                                            <input type="number" class="form-control" name="cleanup_backups_days" id="cleanup_backups_days" min="0" max="365" value="<?= esc($settings['cleanup_backups_days'] ?? 0) ?>">
                                            <small class="form-text text-muted">Delete backups older than this. 0 = keep forever.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="cleanup_cache_days"><strong>Cache Retention (days)</strong></label>
                                            <input type="number" class="form-control" name="cleanup_cache_days" id="cleanup_cache_days" min="0" max="30" value="<?= esc($settings['cleanup_cache_days'] ?? 7) ?>">
                                            <small class="form-text text-muted">Clear cache files older than this.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="cleanup_exports_days"><strong>Exports Retention (days)</strong></label>
                                            <input type="number" class="form-control" name="cleanup_exports_days" id="cleanup_exports_days" min="0" max="365" value="<?= esc($settings['cleanup_exports_days'] ?? 30) ?>">
                                            <small class="form-text text-muted">Delete exported reports older than this.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Cleanup Settings</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>