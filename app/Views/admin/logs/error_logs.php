<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Error Logs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/logs') ?>">Logs</a></li>
                        <li class="breadcrumb-item active">Errors</li>
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
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="btn-group">
                                <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-outline-primary">All</a>
                                <a href="<?= base_url('admin/logs/access') ?>" class="btn btn-sm btn-outline-primary">Access</a>
                                <a href="<?= base_url('admin/logs/errors') ?>" class="btn btn-sm btn-danger">Errors</a>
                                <a href="<?= base_url('admin/logs/api') ?>" class="btn btn-sm btn-outline-info">API</a>
                                <a href="<?= base_url('admin/logs/fcm') ?>" class="btn btn-sm btn-outline-info">FCM</a>
                                <a href="<?= base_url('admin/logs/maintenance') ?>" class="btn btn-sm btn-outline-warning">Maintenance</a>
                            </div>
                            <div class="card-tools">
                                <span class="badge badge-danger"><?= $total ?> failed actions</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Category</th>
                                        <th>Error</th>
                                        <th>IP</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($logs)): ?>
                                    <tr><td colspan="7" class="text-center text-muted">No errors found.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= $log['id'] ?></td>
                                        <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                                        <td><span class="badge badge-warning"><?= htmlspecialchars($log['action_type'] ?? '-') ?></span></td>
                                        <td><?= htmlspecialchars($log['action_category'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($log['error_message']): ?>
                                            <span class="text-danger" title="<?= htmlspecialchars($log['error_message']) ?>">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <?= htmlspecialchars(substr($log['error_message'], 0, 60)) ?>
                                            </span>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($log['created_at'] ?? '-') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-file-alt mr-1"></i> PHP Error Logs</h3>
                            <div class="card-tools">
                                <form method="post" action="<?= base_url('admin/logs/clear-error-files') ?>" style="display:inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete all PHP log files?')"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>File</th>
                                        <th>Size</th>
                                        <th>Lines</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($error_files)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">No log files.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($error_files as $f): ?>
                                    <tr>
                                        <td><small><?= htmlspecialchars($f['filename']) ?></small></td>
                                        <td><?= number_format($f['size'] / 1024, 1) ?> KB</td>
                                        <td><?= $f['lines'] ?></td>
                                        <td>
                                            <a href="<?= base_url('admin/logs/view-error-file/' . $f['filename']) ?>" class="btn btn-xs btn-info" title="View"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (!empty($error_files)): ?>
                        <div class="card-footer">
                            <small class="text-muted">Last modified: <?= htmlspecialchars($error_files[0]['modified'] ?? '-') ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
