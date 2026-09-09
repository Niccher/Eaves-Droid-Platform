<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>General Settings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item active">Settings</li>
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

            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">General Settings</h5>
                        <p class="mb-0 small text-muted">Configure core application settings — site name, timezone, default language, session lifetime, and global feature toggles that affect the entire platform.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card card-outline card-primary shadow-sm mb-3">
                        <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-sliders-h mr-1 text-primary"></i> Settings Hub</h3></div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item"><a href="<?= base_url('admin/settings') ?>" class="nav-link active"><i class="fas fa-cog mr-2 text-primary"></i> General</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/api') ?>" class="nav-link"><i class="fas fa-plug mr-2 text-info"></i> API</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/security') ?>" class="nav-link"><i class="fas fa-shield-alt mr-2 text-danger"></i> Security</a></li>
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
                        <div class="card-header"><h3 class="card-title">General Settings</h3></div>
                        <form action="<?= base_url('admin/settings/update') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="section" value="app">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>App Name</label>
                                    <input type="text" name="app_name" class="form-control" value="<?= htmlspecialchars($settings['app_name'] ?? 'Eaves Droid') ?>">
                                </div>
                                <div class="form-group">
                                    <label>App Description</label>
                                    <textarea name="app_description" class="form-control" rows="3"><?= htmlspecialchars($settings['app_description'] ?? 'Advanced Mobile Forensic & Data Intelligence Platform') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Timezone</label>
                                    <select name="timezone" class="form-control">
                                        <?php $tz = $settings['timezone'] ?? date_default_timezone_get(); ?>
                                        <option value="UTC" <?= $tz === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                        <option value="Africa/Nairobi" <?= $tz === 'Africa/Nairobi' ? 'selected' : '' ?>>Africa/Nairobi (EAT)</option>
                                        <option value="America/New_York" <?= $tz === 'America/New_York' ? 'selected' : '' ?>>America/New_York</option>
                                        <option value="Europe/London" <?= $tz === 'Europe/London' ? 'selected' : '' ?>>Europe/London</option>
                                        <option value="Asia/Dubai" <?= $tz === 'Asia/Dubai' ? 'selected' : '' ?>>Asia/Dubai</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Language</label>
                                    <select name="language" class="form-control">
                                        <?php $lang = $settings['language'] ?? 'en'; ?>
                                        <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>English</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="maintenance_mode" value="0">
                                        <input type="checkbox" class="custom-control-input" id="maintenance_mode" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="maintenance_mode">Maintenance Mode</label>
                                        <small class="form-text text-muted">When enabled, only admins can access the site. Configure <a href="<?= base_url('admin/settings/maintenance') ?>">scheduling and advanced options here</a>.</small>
                                    </div>
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
