<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Email Triggers</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/settings') ?>">Settings</a></li>
                        <li class="breadcrumb-item active">Email Triggers</li>
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
                        <h5 class="text-info font-weight-bold mb-1">Email Triggers</h5>
                        <p class="mb-0 small text-muted">Toggle individual email notifications on/off. All emails use the standardized template with security audit footer (Action, What This Does, Status, Initiated By, Browser, Browser IP, Executed At).</p>
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
                                <li class="nav-item"><a href="<?= base_url('admin/settings/email-triggers') ?>" class="nav-link active"><i class="fas fa-envelope mr-1"></i> Email Triggers</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/backup') ?>" class="nav-link"><i class="fas fa-hdd mr-1"></i> Backup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/cron') ?>" class="nav-link"><i class="fas fa-clock mr-1"></i> Cron Jobs</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Email Notification Triggers</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="<?= base_url('admin/settings/update') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="section" value="email_triggers">

                                <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                                        <div>
                                            <h5 class="text-info font-weight-bold mb-1">Email Triggers</h5>
                                            <p class="mb-0 small text-muted">Toggle individual email notifications on/off. All emails use the standardized template with security audit footer (Action, What This Does, Status, Initiated By, Browser, Browser IP, Executed At).</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- User-Facing Triggers -->
                                <h5 class="text-primary"><i class="fas fa-user mr-1"></i> User-Facing Transactional</h5>
                                <div class="row">
                                    <?php foreach ($userTriggers as $key => $info): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong><?= esc($info['label']) ?></strong>
                                                        <br><small class="text-muted"><?= esc($info['description']) ?></small>
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" name="<?= $key ?>" id="<?= $key ?>" value="1" <?= (isset($settings[$key]) && $settings[$key] === '0') ? '' : 'checked' ?>>
                                                        <label class="custom-control-label" for="<?= $key ?>">Enabled</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- User-Facing System -->
                                <h5 class="text-primary mt-4"><i class="fas fa-shield-alt mr-1"></i> User-Facing System (Admin Initiated)</h5>
                                <div class="row">
                                    <?php foreach ($userSystemTriggers as $key => $info): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong><?= esc($info['label']) ?></strong>
                                                        <br><small class="text-muted"><?= esc($info['description']) ?></small>
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" name="<?= $key ?>" id="<?= $key ?>" value="1" <?= (isset($settings[$key]) && $settings[$key] === '0') ? '' : 'checked' ?>>
                                                        <label class="custom-control-label" for="<?= $key ?>">Enabled</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Admin Notifications -->
                                <h5 class="text-warning mt-4"><i class="fas fa-user-shield mr-1"></i> Admin Notifications</h5>
                                <div class="row">
                                    <?php foreach ($adminTriggers as $key => $info): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-warning">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong class="text-warning"><?= esc($info['label']) ?></strong>
                                                        <br><small class="text-muted"><?= esc($info['description']) ?></small>
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" name="<?= $key ?>" id="<?= $key ?>" value="1" <?= (isset($settings[$key]) && $settings[$key] === '0') ? '' : 'checked' ?>>
                                                        <label class="custom-control-label" for="<?= $key ?>">Enabled</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- System/Background -->
                                <h5 class="text-secondary mt-4"><i class="fas fa-cogs mr-1"></i> System / Background</h5>
                                <div class="row">
                                    <?php foreach ($systemTriggers as $key => $info): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-secondary">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong class="text-secondary"><?= esc($info['label']) ?></strong>
                                                        <br><small class="text-muted"><?= esc($info['description']) ?></small>
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" name="<?= $key ?>" id="<?= $key ?>" value="1" <?= (isset($settings[$key]) && $settings[$key] === '0') ? '' : 'checked' ?>>
                                                        <label class="custom-control-label" for="<?= $key ?>">Enabled</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <hr class="my-4">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Email Triggers</button>
                                <a href="<?= base_url('admin/settings/notifications') ?>" class="btn btn-secondary ml-2"><i class="fas fa-cog mr-1"></i> SMTP Settings</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>