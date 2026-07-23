<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Maintenance</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/settings') ?>">Settings</a></li>
                        <li class="breadcrumb-item active">Maintenance</li>
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

            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Sections</h3></div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item"><a href="<?= base_url('admin/settings') ?>" class="nav-link"><i class="fas fa-cog mr-1"></i> General</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/api') ?>" class="nav-link"><i class="fas fa-plug mr-1"></i> API</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/security') ?>" class="nav-link"><i class="fas fa-shield-alt mr-1"></i> Security</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/notifications') ?>" class="nav-link"><i class="fas fa-bell mr-1"></i> Notifications</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/maintenance') ?>" class="nav-link active"><i class="fas fa-tools mr-1"></i> Maintenance</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/backup') ?>" class="nav-link"><i class="fas fa-hdd mr-1"></i> Backup</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title">Database Status</h3></div>
                                <div class="card-body p-0">
                                    <table class="table table-sm">
                                        <tr><td>Total Tables</td><td><strong><?= $total_tables ?></strong></td></tr>
                                        <tr><td>Total DB Size</td><td><strong><?= number_format($total_db_size / 1048576, 2) ?> MB</strong></td></tr>
                                    </table>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header"><h3 class="card-title">Cache & Logs</h3></div>
                                <div class="card-body p-0">
                                    <table class="table table-sm">
                                        <tr><td>Log Files</td><td><strong><?= $log_count ?></strong> (<?= number_format($log_size / 1024, 1) ?> KB)</td></tr>
                                        <tr><td>Cache Files</td><td><strong><?= $cache_count ?></strong> (<?= number_format($cache_size / 1024, 1) ?> KB)</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title">Actions</h3></div>
                                <div class="card-body">
                                    <form method="post" action="<?= base_url('admin/settings/maintenance/run') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="all">
                                        <button type="submit" class="btn btn-warning btn-block mb-2" onclick="return confirm('Run all maintenance tasks?')">
                                            <i class="fas fa-play mr-1"></i> Run All Maintenance
                                        </button>
                                    </form>
                                    <form method="post" action="<?= base_url('admin/settings/maintenance/run') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="optimize">
                                        <button type="submit" class="btn btn-outline-primary btn-block mb-2">
                                            <i class="fas fa-database mr-1"></i> Optimize Tables
                                        </button>
                                    </form>
                                    <form method="post" action="<?= base_url('admin/settings/maintenance/run') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="logs">
                                        <button type="submit" class="btn btn-outline-danger btn-block mb-2">
                                            <i class="fas fa-trash mr-1"></i> Clear Log Files
                                        </button>
                                    </form>
                                    <form method="post" action="<?= base_url('admin/settings/maintenance/run') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="cache">
                                        <button type="submit" class="btn btn-outline-secondary btn-block">
                                            <i class="fas fa-eraser mr-1"></i> Clear Cache
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Database Tables</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-striped" id="tableStats">
                                <thead>
                                    <tr>
                                        <th>Table</th>
                                        <th>Engine</th>
                                        <th>Rows</th>
                                        <th>Size</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($table_stats as $ts): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($ts['name']) ?></code></td>
                                        <td><?= htmlspecialchars($ts['engine']) ?></td>
                                        <td><?= number_format($ts['rows']) ?></td>
                                        <td><?= number_format($ts['size'] / 1024, 1) ?> KB</td>
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
$(document).ready(function() {
    $('#tableStats').DataTable({
        order: [[2, 'desc']],
        searching: false,
        paging: false,
        responsive: true,
    });
});
</script>
