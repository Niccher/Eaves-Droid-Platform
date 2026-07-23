<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>User Data: <?= htmlspecialchars($view_user['username']) ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/users') ?>">Users</a></li>
                        <li class="breadcrumb-item active">Data</li>
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
            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= session()->getFlashdata('error') ?>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Account Info</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr><td style="width:200px">User ID</td><td><?= $view_user['id'] ?></td></tr>
                                <tr><td>Username</td><td><?= htmlspecialchars($view_user['username']) ?></td></tr>
                                <tr><td>Email</td><td><?= htmlspecialchars($view_user['email'] ?? '-') ?></td></tr>
                                <tr><td>Status</td><td><?= ($view_user['active'] ?? 1) ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Suspended</span>' ?></td></tr>
                                <tr><td>Created</td><td><?= htmlspecialchars($view_user['created_at'] ?? '-') ?></td></tr>
                                <tr><td>Last Active</td><td><?= htmlspecialchars($view_user['last_active'] ?? '-') ?></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Uploaded Data</h3>
                            <div class="card-tools">
                                <?php if ($view_user['id'] !== $user_info['id']): ?>
                                <form method="post" action="<?= base_url('admin/users/clearData/' . $view_user['id']) ?>" style="display:inline" id="clearAllForm">
                                    <?= csrf_field() ?>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmClearAll()">
                                        <i class="fas fa-trash-alt"></i> Clear All Data
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Data Type</th>
                                        <th>Record Count</th>
                                        <th>Table</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data_counts as $dc): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($dc['label']) ?></strong></td>
                                        <td><span class="badge badge-<?= $dc['count'] > 0 ? 'primary' : 'secondary' ?>"><?= number_format($dc['count']) ?></span></td>
                                        <td><code><?= htmlspecialchars($dc['table']) ?></code></td>
                                        <td>
                                            <?php if ($dc['count'] > 0 && $view_user['id'] !== $user_info['id']): ?>
                                            <a href="<?= base_url('admin/users/deleteDataType/' . $view_user['id'] . '/' . $dc['key']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete all <?= $dc['count'] ?> <?= htmlspecialchars($dc['label']) ?> records?')">
                                                <i class="fas fa-trash"></i> Clear
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function confirmClearAll() {
    Swal.fire({
        title: 'Clear All Data?',
        text: 'This will permanently delete ALL uploaded data for this user. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, clear everything',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('clearAllForm').submit();
        }
    });
}
</script>
