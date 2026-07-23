<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Token Management</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item active">Tokens</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <?php if (session()->getFlashdata('message')): ?>
            <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?= session()->getFlashdata('message') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <div class="card">
                <div class="card-header">
                    <div class="btn-group">
                        <a href="<?= base_url('admin/tokens') ?>" class="btn btn-sm btn-primary">All</a>
                        <a href="<?= base_url('admin/tokens/expired') ?>" class="btn btn-sm btn-outline-warning">Expired</a>
                        <a href="<?= base_url('admin/tokens/analytics') ?>" class="btn btn-sm btn-outline-info">Analytics</a>
                    </div>
                    <div class="card-tools"><span class="badge badge-primary"><?= $total ?> tokens</span></div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped" id="tokenTable">
                        <thead>
                            <tr><th>ID</th><th>Owner</th><th>Token</th><th>Device</th><th>Status</th><th>Created</th><th>Expires</th><th>Last Used</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tokens)): ?>
                            <tr><td colspan="9" class="text-center text-muted">No tokens found.</td></tr>
                            <?php else: ?>
                            <?php foreach ($tokens as $t): ?>
                            <tr>
                                <td><?= $t['counter'] ?></td>
                                <td><?= htmlspecialchars($t['username'] ?? 'Unknown') ?></td>
                                <td><code title="<?= htmlspecialchars($t['token']) ?>"><?= htmlspecialchars(substr($t['token'], 0, 16)) ?>...</code></td>
                                <td><small><?= htmlspecialchars($t['device_name'] ?? '-') ?></small></td>
                                <td>
                                    <?php
                                    $statusMap = ['00' => ['Active', 'success'], '11' => ['Used', 'warning'], '99' => ['Deleted', 'danger']];
                                    $s = $statusMap[$t['status']] ?? ['Unknown', 'secondary'];
                                    ?>
                                    <span class="badge badge-<?= $s[1] ?>"><?= $s[0] ?></span>
                                </td>
                                <td><?= htmlspecialchars($t['created_at'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($t['expires_at'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($t['last_used_at'] ?? 'Never') ?></td>
                                <td>
                                    <?php if ($t['status'] === '00'): ?>
                                    <form method="post" action="<?= base_url('admin/tokens/revoke/' . $t['counter']) ?>" style="display:inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-warning" title="Revoke" onclick="return confirm('Revoke this token?')"><i class="fas fa-pause"></i></button>
                                    </form>
                                    <form method="post" action="<?= base_url('admin/tokens/regenerate/' . $t['counter']) ?>" style="display:inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-info" title="Regenerate" onclick="return confirm('Regenerate this token? The old token will be replaced.')"><i class="fas fa-sync"></i></button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="post" action="<?= base_url('admin/tokens/delete/' . $t['counter']) ?>" style="display:inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this token?')"><i class="fas fa-trash"></i></button>
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
    $('#tokenTable').DataTable({ order: [[0, 'desc']], searching: false, paging: false, responsive: true });
});
</script>
