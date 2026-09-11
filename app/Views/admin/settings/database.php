<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-database text-primary mr-2"></i> Database Info
                    </h1>
                    <p class="text-muted mt-1 mb-0">Database connection parameters, query logging options, backup configuration, and maintenance routines for the application database.</p>
                </div>
                <div class="col-sm-4">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item active">DB Info</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header p-0">
                    <ul class="nav nav-pills ml-3 mt-2 mb-2" id="dbTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-overview-link" data-toggle="pill" href="#tab-overview" role="tab">
                                <i class="fas fa-chart-pie mr-2"></i>Database
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-tables-link" data-toggle="pill" href="#tab-tables" role="tab">
                                <i class="fas fa-table mr-2"></i>Tables
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="dbTabsContent">

                        <!-- ==================== DATABASE OVERVIEW TAB ==================== -->
                        <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                            <div class="alert alert-info border-0">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>About these metrics:</strong> The database stores all collected device data, system logs, and application state. The figures below show the current storage footprint.
                            </div>

                            <div class="row">
                                <div class="col-lg-4 col-md-6">
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h3><?= $total_tables ?></h3>
                                            <p>Database Tables</p>
                                        </div>
                                        <div class="icon"><i class="fas fa-table"></i></div>
                                    </div>
                                    <p class="text-muted small px-2">Total number of tables across the database. Each table stores a specific data type — user accounts, device profiles, uploaded files, logs, and more.</p>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h3><?= number_format($total_db_size / 1048576, 2) ?> <small>MB</small></h3>
                                            <p>Total Database Size</p>
                                        </div>
                                        <div class="icon"><i class="fas fa-database"></i></div>
                                    </div>
                                    <p class="text-muted small px-2">Combined size of all table data and indexes on disk. Calculated from <code>SHOW TABLE STATUS</code> as <code>Data Length + Index Length</code>.</p>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="small-box bg-warning">
                                        <div class="inner">
                                            <h3><?= $log_count + $cache_count ?></h3>
                                            <p>Logs &amp; Cache FilesController</p>
                                        </div>
                                        <div class="icon"><i class="fas fa-archive"></i></div>
                                    </div>
                                    <p class="text-muted small px-2">FilesController written by the application at runtime. Logs record system events and errors; cache files speed up page loads. These can be cleared safely.</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-outline card-danger">
                                        <div class="card-header"><h3 class="card-title"><i class="fas fa-file-alt mr-2"></i>Log FilesController</h3></div>
                                        <div class="card-body">
                                            <div class="alert alert-light border-0 p-2 mb-2 small">
                                                <i class="fas fa-info-circle mr-1 text-info"></i>
                                                Log files contain request traces, errors, and debug output. Clearing them frees disk space but permanently removes historical logs.
                                            </div>
                                            <table class="table table-sm">
                                                <tr><td>FilesController</td><td><strong><?= $log_count ?></strong></td></tr>
                                                <tr><td>Total Size</td><td><strong><?= number_format($log_size / 1024, 1) ?> KB</strong></td></tr>
                                            </table>
                                            <form method="post" action="<?= base_url('admin/settings/maintenance/run') ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="logs">
                                                <button type="submit" class="btn btn-outline-danger btn-sm btn-block" onclick="return confirm('Clear all log files?')">
                                                    <i class="fas fa-trash mr-1"></i> Clear Log FilesController
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-outline card-warning">
                                        <div class="card-header"><h3 class="card-title"><i class="fas fa-eraser mr-2"></i>Cache</h3></div>
                                        <div class="card-body">
                                            <div class="alert alert-light border-0 p-2 mb-2 small">
                                                <i class="fas fa-info-circle mr-1 text-info"></i>
                                                Cache files store temporary data to improve response times. Clearing them may slow down initial page loads until the cache rebuilds.
                                            </div>
                                            <table class="table table-sm">
                                                <tr><td>FilesController</td><td><strong><?= $cache_count ?></strong></td></tr>
                                                <tr><td>Total Size</td><td><strong><?= number_format($cache_size / 1024, 1) ?> KB</strong></td></tr>
                                            </table>
                                            <form method="post" action="<?= base_url('admin/settings/maintenance/run') ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="cache">
                                                <button type="submit" class="btn btn-outline-warning btn-sm btn-block" onclick="return confirm('Clear all cache files?')">
                                                    <i class="fas fa-eraser mr-1"></i> Clear Cache
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== TABLES TAB ==================== -->
                        <div class="tab-pane fade" id="tab-tables" role="tabpanel">
                            <div class="alert alert-info border-0">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Size Estimation:</strong> Each table's total size is <code>Data Length + Index Length</code> from MySQL's <code>SHOW TABLE STATUS</code>. Data length is the space used by the actual rows; index length is the space used by indexes for faster lookups. Values are displayed in KB (1024 bytes).
                            </div>
                            <table class="table table-hover table-striped mb-0" id="tableStats" style="width:100%;">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Table Name</th>
                                        <th>Engine</th>
                                        <th>Rows</th>
                                        <th>Data Size</th>
                                        <th>Index Size</th>
                                        <th>Total Size</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($table_stats as $ts): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($ts['name']) ?></code></td>
                                        <td><span class="badge badge-secondary"><?= htmlspecialchars($ts['engine']) ?></span></td>
                                        <td><?= number_format($ts['rows']) ?></td>
                                        <td><?= number_format(($ts['data_size'] ?? $ts['size'] ?? 0) / 1024, 1) ?> KB</td>
                                        <td><?= number_format(($ts['index_size'] ?? 0) / 1024, 1) ?> KB</td>
                                        <td><strong><?= number_format($ts['size'] / 1024, 1) ?> KB</strong></td>
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
        order: [[5, 'desc']],
        searching: false,
        paging: false,
        responsive: true,
        autoWidth: false,
    });
});
</script>
