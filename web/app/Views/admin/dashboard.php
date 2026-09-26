<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Admin Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Admin Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= number_format($total_users) ?></h3>
                            <p>Total Users</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                        <a href="<?= base_url('admin/users') ?>" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= number_format($total_devices) ?></h3>
                            <p>Total Devices</p>
                        </div>
                        <div class="icon"><i class="fas fa-mobile-alt"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= number_format($total_uploads) ?></h3>
                            <p>Total Uploads</p>
                        </div>
                        <div class="icon"><i class="fas fa-upload"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= number_format($storage_used / 1048576, 1) ?> MB</h3>
                            <p>Storage Used</p>
                        </div>
                        <div class="icon"><i class="fas fa-database"></i></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-area mr-1"></i> Upload Volume (30 days)</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="uploadChart" style="height: 250px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> User Signups (30 days)</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="signupChart" style="height: 250px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-clock mr-1"></i> Recent Activity</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>IP</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_activity)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">No recent activity</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($recent_activity as $act): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($act['username'] ?? 'Unknown') ?></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($act['action_type'] ?? '-') ?></span></td>
                                        <td><?= htmlspecialchars($act['ip_address'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($act['created_at'] ?? '-') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-heartbeat mr-1"></i> System Health</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <td>PHP Version</td>
                                    <td><strong><?= PHP_VERSION ?></strong></td>
                                </tr>
                                <tr>
                                    <td>Database</td>
                                    <td><span class="badge badge-success">Connected</span></td>
                                </tr>
                                <tr>
                                    <td>Server Time</td>
                                    <td><strong><?= date('Y-m-d H:i:s') ?></strong></td>
                                </tr>
                                <tr>
                                    <td>Timezone</td>
                                    <td><strong><?= date_default_timezone_get() ?></strong></td>
                                </tr>
                                <tr>
                                    <td>Users with Data</td>
                                    <td><strong><?= number_format($total_users_with_data) ?></strong></td>
                                </tr>
                                <tr>
                                    <td>Last Backup</td>
                                    <td>
                                        <?php if ($latest_backup): ?>
                                        <strong><?= htmlspecialchars($latest_backup['created_at']) ?></strong>
                                        <?php else: ?>
                                        <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Recent Registrations</td>
                                    <td>
                                        <?php if (!empty($recent_registrations)): ?>
                                        <ul class="list-unstyled mb-0">
                                            <?php foreach ($recent_registrations as $reg): ?>
                                            <li><small><?= htmlspecialchars($reg['username']) ?> &mdash; <?= htmlspecialchars($reg['created_at']) ?></small></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php else: ?>
                                        <span class="text-muted">None</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
var uploadCtx = document.getElementById('uploadChart').getContext('2d');
var signupCtx = document.getElementById('signupChart').getContext('2d');

var uploadLabels = <?= json_encode(array_column($uploads_per_day, 'date')) ?>;
var uploadData = <?= json_encode(array_column($uploads_per_day, 'count')) ?>;

new Chart(uploadCtx, {
    type: 'bar',
    data: {
        labels: uploadLabels.length ? uploadLabels : ['No data'],
        datasets: [{
            label: 'Uploads',
            data: uploadData.length ? uploadData : [0],
            backgroundColor: 'rgba(60,141,188,0.9)',
            borderColor: 'rgba(60,141,188,0.8)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
    }
});

var signupLabels = <?= json_encode(array_column($users_per_day, 'date')) ?>;
var signupData = <?= json_encode(array_column($users_per_day, 'count')) ?>;

new Chart(signupCtx, {
    type: 'line',
    data: {
        labels: signupLabels.length ? signupLabels : ['No data'],
        datasets: [{
            label: 'New Users',
            data: signupData.length ? signupData : [0],
            backgroundColor: 'rgba(40,167,69,0.2)',
            borderColor: 'rgba(40,167,69,1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
    }
});
</script>
