<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>User Activity Reports</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/reports') ?>">Reports</a></li>
                        <li class="breadcrumb-item active">User Activity</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Select a User</h3></div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($users as $u): ?>
                        <div class="col-md-4 col-sm-6 mb-2">
                            <a href="<?= base_url('admin/reports/user-activity/' . urlencode($u['username'])) ?>" class="btn btn-outline-primary btn-block text-left">
                                <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($u['username']) ?>
                                <small class="float-right text-muted"><?= htmlspecialchars($u['email'] ?? '') ?></small>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
