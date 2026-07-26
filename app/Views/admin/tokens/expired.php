<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Expired Tokens</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/tokens') ?>">Tokens</a></li>
                        <li class="breadcrumb-item active">Expired</li>
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
                        <h5 class="text-info font-weight-bold mb-1">Expired Tokens</h5>
                        <p class="mb-0 small text-muted">View and clean up expired or revoked API tokens. Tokens beyond their validity period are listed here for audit and purging.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="btn-group">
                        <a href="<?= base_url('admin/tokens') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-list mr-1"></i>All</a>
                        <a href="<?= base_url('admin/tokens/expired') ?>" class="btn btn-sm btn-warning"><i class="fas fa-clock mr-1"></i>Expired</a>
                        <a href="<?= base_url('admin/tokens/analytics') ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-chart-bar mr-1"></i>Analytics</a>
                    </div>
                    <div class="card-tools"><span class="badge badge-warning"><?= $total ?> expired tokens</span></div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped" id="expiredTable">
                        <thead>
                            <tr><th>ID</th><th>Owner</th><th>Device</th><th>Created</th><th>Expired</th><th>Last Used</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tokens)): ?>
                            <tr><td colspan="7" class="text-center text-muted">No expired tokens.</td></tr>
                            <?php else: ?>
                            <?php foreach ($tokens as $t): ?>
                            <tr>
                                <td><?= $t['counter'] ?></td>
                                <td><?= htmlspecialchars($t['username'] ?? 'Unknown') ?></td>
                                <td><small><?= htmlspecialchars($t['device_name'] ?? '-') ?></small></td>
                                <td><?= htmlspecialchars($t['created_at'] ?? '-') ?></td>
                                <td><span class="badge badge-danger"><?= htmlspecialchars($t['expires_at'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($t['last_used_at'] ?? 'Never') ?></td>
                                <td>
                                    <form method="post" action="<?= base_url('admin/tokens/delete/' . $t['counter']) ?>" style="display:inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this token?')"><i class="fas fa-trash"></i> Delete</button>
                                    </form>
                                </td>
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
    $('#expiredTable').DataTable({ order: [[4, 'desc']], searching: false, paging: false, responsive: true });
});
</script>
