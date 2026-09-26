<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-user-circle text-primary mr-1"></i>
                        <?= htmlspecialchars($report_user['username']) ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/reports') ?>">Reports</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/reports/user-activity') ?>">User Activity</a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($report_user['username']) ?></li>
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
                        <h5 class="text-info font-weight-bold mb-1">User Activity Detail</h5>
                        <p class="mb-0 small text-muted">In-depth activity report for a single user — every action performed, session duration, feature accessed, and device information.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-database mr-1"></i> Data Counts</h3>
                            <div class="card-tools">
                                <span class="badge badge-primary"><?= array_sum(array_column($data_counts, 'count')) ?> total</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    <?php foreach ($data_counts as $dc): ?>
                                    <tr>
                                        <td>
                                            <i class="fas <?= $dc['icon'] ?? 'fa-circle' ?> text-muted mr-1" style="width: 18px;"></i>
                                            <?= $dc['label'] ?>
                                        </td>
                                        <td class="text-right">
                                            <span class="badge badge-<?= $dc['count'] > 0 ? 'primary' : 'light text-muted' ?>">
                                                <?= number_format($dc['count']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Uploads (30 days)</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="uploadChart" height="180"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-sign-in-alt mr-1"></i> Login History</h3>
                            <div class="card-tools">
                                <span class="badge badge-success"><?= count($login_history) ?> events</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>IP</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($login_history)): ?>
                                    <tr><td colspan="3" class="text-muted text-center">No login history.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($login_history as $lh): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($lh['date']) ?></td>
                                        <td><code><?= htmlspecialchars($lh['ip_address'] ?? '-') ?></code></td>
                                        <td>
                                            <?php if ($lh['success']): ?>
                                            <span class="badge badge-success">Success</span>
                                            <?php else: ?>
                                            <span class="badge badge-danger">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history mr-1"></i> Recent Actions</h3>
                            <div class="card-tools">
                                <span class="badge badge-warning"><?= count($actions) ?> actions</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($actions)): ?>
                                    <tr><td colspan="2" class="text-muted text-center">No recent actions.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($actions as $a): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-info"><?= htmlspecialchars($a['action_type'] ?? '-') ?></span>
                                        </td>
                                        <td><small><?= htmlspecialchars($a['created_at'] ?? '-') ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
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
new Chart(document.getElementById('uploadChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($uploads_per_day, 'date')) ?: '[]' ?>,
        datasets: [{
            label: 'Uploads',
            data: <?= json_encode(array_column($uploads_per_day, 'count')) ?: '[]' ?>,
            backgroundColor: '#17a2b8',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});
</script>