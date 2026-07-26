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
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Sections</h3></div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item"><a href="<?= base_url('admin/settings') ?>" class="nav-link"><i class="fas fa-cog mr-1"></i> General</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/api') ?>" class="nav-link"><i class="fas fa-plug mr-1"></i> API</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/security') ?>" class="nav-link"><i class="fas fa-shield-alt mr-1"></i> Security</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/notifications') ?>" class="nav-link"><i class="fas fa-bell mr-1"></i> Notifications</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/maintenance') ?>" class="nav-link"><i class="fas fa-tools mr-1"></i> Maintenance</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/backup') ?>" class="nav-link active"><i class="fas fa-hdd mr-1"></i> Backup</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Database Backups</h3>
                            <div class="card-tools">
                                <form method="post" action="<?= base_url('admin/settings/backup/create') ?>" style="display:inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i> Create Backup</button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
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
