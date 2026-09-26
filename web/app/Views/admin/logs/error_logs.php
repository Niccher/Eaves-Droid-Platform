<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-exclamation-triangle mr-1"></i> Error Logs</h1>
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

            <div class="callout callout-info shadow-sm mb-4">
                <h5><i class="fas fa-info-circle mr-2"></i>Error Logs</h5>
                <p class="mb-0">Failed system actions — errors triggered by user actions, API calls, and system operations. Each entry shows what went wrong and who triggered it.</p>
            </div>

            <div class="card card-outline card-danger shadow-sm">
                <div class="card-header">
                    <div class="btn-group">
                        <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-list mr-1"></i>All</a>
                        <a href="<?= base_url('admin/logs/access') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-sign-in-alt mr-1"></i>Access</a>
                        <a href="<?= base_url('admin/logs/errors') ?>" class="btn btn-sm btn-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Errors</a>
                        <a href="<?= base_url('admin/logs/php-errors') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-alt mr-1"></i>PHP Errors</a>
                        <a href="<?= base_url('admin/logs/api') ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-code mr-1"></i>API</a>
                        <a href="<?= base_url('admin/logs/fcm') ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-fire mr-1"></i>FCM</a>
                        <a href="<?= base_url('admin/logs/maintenance') ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-shield-alt mr-1"></i>Maintenance</a>
                        <a href="<?= base_url('admin/logs/engine') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-robot mr-1"></i>Engine</a>
                    </div>
                    <div class="card-tools">
                        <span class="badge badge-danger"><?= $total ?> failed actions</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="errorTable">
                            <thead class="thead-light">
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
                                <tr><td colspan="7" class="text-center text-muted py-4">No errors found.</td></tr>
                                <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= $log['id'] ?></td>
                                    <td>
                                        <i class="fas fa-user-circle text-muted mr-1"></i>
                                        <?= htmlspecialchars($log['username'] ?? 'System') ?>
                                    </td>
                                    <td><span class="badge badge-warning"><?= htmlspecialchars($log['action_type'] ?? '-') ?></span></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($log['action_category'] ?? '-') ?></span></td>
                                    <td>
                                        <?php if ($log['error_message']): ?>
                                        <span class="text-danger" title="<?= htmlspecialchars($log['error_message']) ?>">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            <?= htmlspecialchars(substr($log['error_message'], 0, 60)) ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted"><i class="fas fa-minus mr-1"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td>
                                    <td class="text-nowrap"><small><?= htmlspecialchars($log['created_at'] ?? '-') ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#errorTable').DataTable({
        order: [[0, 'desc']],
        searching: false,
        paging: false,
        responsive: true,
    });
});
</script>
