<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>API Logs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/logs') ?>">Logs</a></li>
                        <li class="breadcrumb-item active">API</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="callout callout-info shadow-sm mb-4">
                <h5><i class="fas fa-info-circle mr-2"></i>API Logs</h5>
                <p class="mb-0">API request logs — file uploads, data syncs, external service calls, and system-level operations routed through the API.</p>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="btn-group">
                        <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-list mr-1"></i>All</a>
                        <a href="<?= base_url('admin/logs/access') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-sign-in-alt mr-1"></i>Access</a>
                        <a href="<?= base_url('admin/logs/errors') ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Errors</a>
                        <a href="<?= base_url('admin/logs/php-errors') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-alt mr-1"></i>PHP Errors</a>
                        <a href="<?= base_url('admin/logs/api') ?>" class="btn btn-sm btn-info"><i class="fas fa-code mr-1"></i>API</a>
                        <a href="<?= base_url('admin/logs/fcm') ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-fire mr-1"></i>FCM</a>
                        <a href="<?= base_url('admin/logs/maintenance') ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-shield-alt mr-1"></i>Maintenance</a>
                        <a href="<?= base_url('admin/logs/engine') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-robot mr-1"></i>Engine</a>
                    </div>
                    <div class="card-tools">
                        <span class="badge badge-info"><?= $total ?> API requests</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover" id="apiTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Method</th>
                                <th>Endpoint</th>
                                <th>Response Code</th>
                                <th>Duration</th>
                                <th>IP</th>
                                <th>Success</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                            <tr><td colspan="9" class="text-center text-muted">No API logs found.</td></tr>
                            <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= $log['id'] ?></td>
                                <td><?= htmlspecialchars($log['created_at'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($log['username'] ?? 'Unknown') ?></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($log['request_method'] ?? '-') ?></span></td>
                                <td><small title="<?= htmlspecialchars($log['request_url'] ?? '') ?>"><?= htmlspecialchars(substr($log['request_url'] ?? '', 0, 60)) ?></small></td>
                                <td>
                                    <?php if ($log['response_code']): ?>
                                    <span class="badge badge-<?= $log['response_code'] >= 500 ? 'danger' : ($log['response_code'] >= 400 ? 'warning' : ($log['response_code'] >= 300 ? 'info' : 'success')) ?>">
                                        <?= $log['response_code'] ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $log['execution_time_ms'] ? number_format($log['execution_time_ms']) . ' ms' : '<span class="text-muted">-</span>' ?></td>
                                <td><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
                                <td><?= $log['success'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>' ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#apiTable').DataTable({
        order: [[0, 'desc']],
        searching: false,
        paging: false,
        responsive: true,
    });
});
</script>
