<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>System Performance</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/reports') ?>">Reports</a></li>
                        <li class="breadcrumb-item active">Performance</li>
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
                        <h5 class="text-info font-weight-bold mb-1">Performance Report</h5>
                        <p class="mb-0 small text-muted">System performance metrics — response times, query execution speeds, cache hit rates, memory usage, and PHP-FPM worker statistics.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Server Info</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <tr><td>PHP Version</td><td><strong><?= $php_version ?></strong></td></tr>
                                <tr><td>Server Software</td><td><?= htmlspecialchars($server_software) ?></td></tr>
                                <tr><td>Memory Limit</td><td><?= $memory_limit ?></td></tr>
                                <tr><td>Max Upload</td><td><?= $max_upload ?></td></tr>
                                <tr><td>Max POST</td><td><?= $max_post ?></td></tr>
                                <tr><td>Max Execution</td><td><?= $max_execution ?>s</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Error Rate</h3></div>
                        <div class="card-body">
                            <?php
                            $total = $error_rate->total ?? 0;
                            $failed = $error_rate->failed ?? 0;
                            $rate = $total > 0 ? round($failed / $total * 100, 2) : 0;
                            ?>
                            <h3><?= $rate ?>%</h3>
                            <small class="text-muted"><?= number_format($failed) ?> failed out of <?= number_format($total) ?> actions</small>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-<?= $rate > 10 ? 'danger' : ($rate > 5 ? 'warning' : 'success') ?>" style="width: <?= min($rate, 100) ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Database Overview</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <tr><td>Total Tables</td><td><strong><?= count($table_sizes) ?></strong></td></tr>
                                <tr><td>Total Records</td><td><strong><?= number_format($total_records) ?></strong></td></tr>
                                <tr><td>Total DB Size</td><td><strong><?= number_format($total_db_size / 1048576, 2) ?> MB</strong></td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Uploads (Last 7 Days)</h3></div>
                        <div class="card-body"><canvas id="uploadTrend" height="150"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3 class="card-title">Largest Tables</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm" id="tableSizes">
                        <thead><tr><th>Table</th><th>Rows</th><th>Size</th></tr></thead>
                        <tbody>
                            <?php foreach (array_slice($table_sizes, 0, 20) as $ts): ?>
                            <tr><td><code><?= $ts['name'] ?></code></td><td><?= number_format($ts['rows']) ?></td><td><?= number_format($ts['size'] / 1024, 1) ?> KB</td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('uploadTrend'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($uploads_by_day, 'date')) ?: '[]' ?>,
        datasets: [{
            label: 'Uploads',
            data: <?= json_encode(array_column($uploads_by_day, 'count')) ?: '[]' ?>,
            borderColor: '#28a745', fill: false, tension: 0.3
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
});
</script>
