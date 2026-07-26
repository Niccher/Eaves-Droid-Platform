<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-robot mr-1"></i> Anomaly Engine Logs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/logs') ?>">Logs</a></li>
                        <li class="breadcrumb-item active">Engine Logs</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">Anomaly Detection Engine Runs</h5>
                        <p class="mb-0 small text-muted">Anomaly detection engine runs triggered by any user (admin or ordinary). Shows the engine type, algorithms selected, status, duration, and who requested it.</p>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header">
                        <div class="btn-group">
                            <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-list mr-1"></i>All</a>
                            <a href="<?= base_url('admin/logs/access') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-sign-in-alt mr-1"></i>Access</a>
                            <a href="<?= base_url('admin/logs/errors') ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Errors</a>
                            <a href="<?= base_url('admin/logs/php-errors') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-alt mr-1"></i>PHP Errors</a>
                            <a href="<?= base_url('admin/logs/api') ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-code mr-1"></i>API</a>
                            <a href="<?= base_url('admin/logs/fcm') ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-fire mr-1"></i>FCM</a>
                            <a href="<?= base_url('admin/logs/maintenance') ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-shield-alt mr-1"></i>Maintenance</a>
                            <a href="<?= base_url('admin/logs/engine') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-robot mr-1"></i>Engine</a>
                        </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Timestamp</th>
                                    <th>Owner</th>
                                    <th>Engine</th>
                                    <th>Algorithms</th>
                                    <th>Scope</th>
                                    <th>Status</th>
                                    <th>Time Taken</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history)): ?>
                                <tr><td colspan="8" class="text-center text-muted py-4">No anomaly engine runs recorded yet.</td></tr>
                                <?php else: ?>
                                <?php $i = 1; ?>
                                <?php foreach ($history as $j): ?>
                                <tr>
                                    <td class="text-muted"><?= $i++ ?></td>
                                    <td class="text-nowrap"><small><?= date('M j, Y g:i A', strtotime($j['created_at'])) ?></small></td>
                                    <td>
                                        <i class="fas fa-user-circle text-muted mr-1"></i>
                                        <?= esc($j['owner_name'] ?? 'Unknown') ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $j['engine'] === 'python' ? 'warning' : ($j['engine'] === 'php' ? 'success' : 'primary') ?>">
                                            <i class="fas fa-<?= $j['engine'] === 'python' ? 'robot' : ($j['engine'] === 'php' ? 'code' : 'project-diagram') ?> mr-1"></i>
                                            <?= ucfirst($j['engine'] ?? '?') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary badge-pill mr-1"><?= $j['algorithm_count'] ?></span>
                                        <?php if (!empty($j['algorithm_names'])): ?>
                                            <?php foreach (array_slice($j['algorithm_names'], 0, 3) as $nm): ?>
                                            <span class="badge badge-light border mr-1"><?= esc($nm) ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($j['algorithm_names']) > 3): ?>
                                            <span class="badge badge-light border">+<?= count($j['algorithm_names']) - 3 ?></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($j['scope'] === 'incremental'): ?>
                                        <span class="badge badge-info">New Data</span>
                                        <?php else: ?>
                                        <span class="badge badge-secondary">Full Scan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $statusBadge = match($j['status']) {
                                            'completed' => 'success',
                                            'running' => 'primary',
                                            'failed' => 'danger',
                                            'pending' => 'secondary',
                                            default => 'secondary',
                                        }; ?>
                                        <span class="badge badge-<?= $statusBadge ?>">
                                            <i class="fas fa-<?= $j['status'] === 'completed' ? 'check-circle' : ($j['status'] === 'running' ? 'spinner' : ($j['status'] === 'failed' ? 'times-circle' : 'clock')) ?> mr-1"></i>
                                            <?= ucfirst($j['status'] ?? '?') ?>
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <?php if ($j['completed_at'] && $j['time_taken'] > 0): ?>
                                        <i class="far fa-clock mr-1"></i>
                                        <?php
                                            $t = (int)$j['time_taken'];
                                            if ($t >= 60) {
                                                echo floor($t / 60) . 'm ' . ($t % 60) . 's';
                                            } else {
                                                echo $t . 's';
                                            }
                                        ?>
                                        <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
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
                    Showing the most recent 100 engine runs. Runs created by both admin and ordinary users are included.
                    <span class="float-right">
                        <a href="<?= base_url('admin/anomalies') ?>" class="text-warning"><i class="fas fa-tools mr-1"></i>Anomaly Engine Settings</a>
                    </span>
                </div>
            </div>
        </div>
    </section>
</div>
