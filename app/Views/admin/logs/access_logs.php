<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Access Logs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/logs') ?>">Logs</a></li>
                        <li class="breadcrumb-item active">Access</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="btn-group">
                        <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-outline-primary">All</a>
                        <a href="<?= base_url('admin/logs/access') ?>" class="btn btn-sm btn-primary">Access</a>
                        <a href="<?= base_url('admin/logs/errors') ?>" class="btn btn-sm btn-outline-danger">Errors</a>
                        <a href="<?= base_url('admin/logs/api') ?>" class="btn btn-sm btn-outline-info">API</a>
                    </div>
                    <div class="card-tools">
                        <span class="badge badge-primary"><?= $actionTotal ?> actions</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover" id="accessTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>IP</th>
                                <th>User Agent</th>
                                <th>Success</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($actions)): ?>
                            <tr><td colspan="7" class="text-center text-muted">No access logs found.</td></tr>
                            <?php else: ?>
                            <?php foreach ($actions as $log): ?>
                            <tr>
                                <td><?= $log['id'] ?></td>
                                <td><?= htmlspecialchars($log['date'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($log['username'] ?? 'Guest') ?></td>
                                <td><span class="badge badge-info"><?= htmlspecialchars($log['action_type'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
                                <td><small title="<?= htmlspecialchars($log['user_agent'] ?? '') ?>"><?= htmlspecialchars(substr($log['user_agent'] ?? '', 0, 50)) ?></small></td>
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
    $('#accessTable').DataTable({
        order: [[0, 'desc']],
        searching: false,
        paging: false,
        responsive: true,
    });
});
</script>
