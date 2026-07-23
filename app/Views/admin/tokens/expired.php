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
            <div class="card">
                <div class="card-header">
                    <span class="badge badge-warning"><?= $total ?> expired tokens</span>
                    <div class="card-tools">
                        <a href="<?= base_url('admin/tokens') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-arrow-left mr-1"></i> All Tokens</a>
                    </div>
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
