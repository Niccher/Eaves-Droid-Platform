<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-shield-alt text-danger mr-2"></i> Security Settings
                    </h1>
                    <p class="text-muted mt-1 mb-0">Password policy rules, account lockout thresholds, session security parameters, CSRF protection, and Content Security Policy configuration.</p>
                </div>
                <div class="col-sm-4">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/settings') ?>">Settings</a></li>
                        <li class="breadcrumb-item active">Security</li>
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

            <div class="row">
                <div class="col-md-3">
                    <div class="card card-outline card-primary shadow-sm mb-3">
                        <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-sliders-h mr-1 text-primary"></i> Settings Hub</h3></div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item"><a href="<?= base_url('admin/settings') ?>" class="nav-link"><i class="fas fa-cog mr-2 text-primary"></i> General</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/api') ?>" class="nav-link"><i class="fas fa-plug mr-2 text-info"></i> API</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/security') ?>" class="nav-link active"><i class="fas fa-shield-alt mr-2 text-danger"></i> Security</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/notifications') ?>" class="nav-link"><i class="fas fa-envelope mr-2 text-primary"></i> Emails & SMTP</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/fcm') ?>" class="nav-link"><i class="fas fa-fire mr-2 text-warning"></i> Firebase (FCM)</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/maintenance') ?>" class="nav-link"><i class="fas fa-tools mr-2 text-secondary"></i> Maintenance</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage') ?>" class="nav-link"><i class="fas fa-chart-pie mr-2 text-success"></i> Storage Monitor</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage-cleanup') ?>" class="nav-link"><i class="fas fa-broom mr-2 text-warning"></i> Storage Cleanup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/email-triggers') ?>" class="nav-link"><i class="fas fa-bell mr-2 text-info"></i> Email Triggers</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/backup') ?>" class="nav-link"><i class="fas fa-database mr-2 text-info"></i> Backup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/cron') ?>" class="nav-link"><i class="fas fa-clock mr-2 text-purple"></i> Cron Jobs</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Security Configuration</h3></div>
                        <form action="<?= base_url('admin/settings/update') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="section" value="security">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Minimum Password Length</label>
                                    <input type="number" name="min_password_length" class="form-control" value="<?= htmlspecialchars($settings['min_password_length'] ?? '8') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Session TTL (minutes)</label>
                                    <input type="number" name="session_ttl" class="form-control" value="<?= htmlspecialchars($settings['session_ttl'] ?? '120') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Max Login Attempts Before Lockout</label>
                                    <input type="number" name="max_login_attempts" class="form-control" value="<?= htmlspecialchars($settings['max_login_attempts'] ?? '5') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Lockout Duration (minutes)</label>
                                    <input type="number" name="lockout_duration" class="form-control" value="<?= htmlspecialchars($settings['lockout_duration'] ?? '15') ?>">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
