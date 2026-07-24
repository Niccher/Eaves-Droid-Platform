<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-shield-alt mr-1"></i> Maintenance Block Logs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/logs') ?>">Logs</a></li>
                        <li class="breadcrumb-item active">Maintenance Blocks</li>
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

            <div class="callout callout-warning bg-light shadow-sm border-left-warning mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle text-warning fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-warning font-weight-bold mb-1">Maintenance Mode Access Blocks</h5>
                        <p class="mb-0 small text-muted">
                            Every time a non-admin user is blocked from accessing the system during maintenance mode, the attempt is logged here.
                            Total blocked attempts recorded: <strong><?= number_format($total) ?></strong>.
                        </p>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header">
                    <div class="btn-group">
                        <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-outline-primary">All</a>
                        <a href="<?= base_url('admin/logs/access') ?>" class="btn btn-sm btn-outline-primary">Access</a>
                        <a href="<?= base_url('admin/logs/errors') ?>" class="btn btn-sm btn-outline-danger">Errors</a>
                        <a href="<?= base_url('admin/logs/api') ?>" class="btn btn-sm btn-outline-info">API</a>
                        <a href="<?= base_url('admin/logs/fcm') ?>" class="btn btn-sm btn-outline-info">FCM</a>
                        <a href="<?= base_url('admin/logs/maintenance') ?>" class="btn btn-sm btn-warning">Maintenance Blocks</a>
                    </div>
                    <div class="card-tools">
                        <form method="post" action="<?= base_url('admin/logs/clear') ?>" style="display:inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Clear all logs? This removes ALL logs, not just maintenance blocks.')"><i class="fas fa-trash"></i> Clear All</button>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>IP Address</th>
                                    <th>Request Method</th>
                                    <th>Request URL</th>
                                    <th>User Agent / Device</th>
                                    <th>Authenticated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">No maintenance blocks recorded.</td></tr>
                                <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                <?php
                                    $newVals = !empty($log['new_values']) ? json_decode($log['new_values'], true) : [];
                                    $authStatus = $newVals['user_authenticated'] ?? false;
                                ?>
                                <tr>
                                    <td class="text-nowrap"><small><?= htmlspecialchars($log['created_at'] ?? '-') ?></small></td>
                                    <td>
                                        <?php if ($log['user_id'] && $log['username']): ?>
                                        <i class="fas fa-user-circle text-muted mr-1"></i>
                                        <?= htmlspecialchars($log['username']) ?>
                                        <?php else: ?>
                                        <span class="text-muted"><i class="fas fa-user-slash mr-1"></i> Guest</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td>
                                    <td><span class="badge badge-secondary"><?= strtoupper(htmlspecialchars($log['request_method'] ?? '-')) ?></span></td>
                                    <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($log['request_url'] ?? '') ?>">
                                        <small><?= htmlspecialchars($log['request_url'] ?? '-') ?></small>
                                    </td>
                                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($log['user_agent'] ?? '') ?>">
                                        <small class="text-muted">
                                            <?php if ($log['device_type']): ?>
                                            <i class="fas fa-<?= $log['device_type'] === 'mobile' ? 'mobile-alt' : ($log['device_type'] === 'bot' ? 'robot' : 'desktop') ?> mr-1"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($log['operating_system'] ?? '') ?>
                                            <?php if ($log['browser']): ?> &mdash; <?= htmlspecialchars($log['browser']) ?><?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($authStatus): ?>
                                        <span class="badge badge-warning"><i class="fas fa-check-circle mr-1"></i> Logged In</span>
                                        <?php else: ?>
                                        <span class="badge badge-secondary"><i class="fas fa-times-circle mr-1"></i> Guest</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer small text-muted">
                    <i class="fas fa-info-circle mr-1"></i>
                    Showing the most recent 100 blocked attempts. Older entries are not displayed but remain in the database.
                    <span class="float-right">
                        <a href="<?= base_url('admin/settings/maintenance') ?>" class="text-warning"><i class="fas fa-tools mr-1"></i>Go to Maintenance Settings</a>
                    </span>
                </div>
            </div>
        </div>
    </section>
</div>

