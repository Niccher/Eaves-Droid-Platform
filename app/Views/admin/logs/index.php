<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>System Logs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item active">Logs</li>
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

            <div class="card">
                <div class="card-header">
                    <div class="btn-group">
                        <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-primary">All</a>
                        <a href="<?= base_url('admin/logs/access') ?>" class="btn btn-sm btn-outline-primary">Access</a>
                        <a href="<?= base_url('admin/logs/errors') ?>" class="btn btn-sm btn-outline-danger">Errors</a>
                        <a href="<?= base_url('admin/logs/api') ?>" class="btn btn-sm btn-outline-info">API</a>
                    </div>
                    <div class="card-tools">
                        <form method="post" action="<?= base_url('admin/logs/clear') ?>" style="display:inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Clear all logs?')"><i class="fas fa-trash"></i> Clear</button>
                        </form>
                        <form method="post" action="<?= base_url('admin/logs/export') ?>" style="display:inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-download"></i> Export CSV</button>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover" id="logsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action Type</th>
                                <th>Category</th>
                                <th>Severity</th>
                                <th>IP</th>
                                <th>Success</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                            <tr><td colspan="8" class="text-center text-muted">No logs found.</td></tr>
                            <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= $log['id'] ?></td>
                                <td><?= htmlspecialchars($log['date'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                                <td><span class="badge badge-info"><?= htmlspecialchars($log['action_type'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($log['category'] ?? '-') ?></td>
                                <td>
                                    <span class="badge badge-<?= $log['severity'] === 'critical' ? 'danger' : ($log['severity'] === 'high' ? 'warning' : ($log['severity'] === 'medium' ? 'info' : 'secondary')) ?>">
                                        <?= htmlspecialchars($log['severity'] ?? 'low') ?>
                                    </span>
                                </td>
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
    $('#logsTable').DataTable({
        order: [[0, 'desc']],
        searching: false,
        paging: false,
        responsive: true,
    });
});
</script>
