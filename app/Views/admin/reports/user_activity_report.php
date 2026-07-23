<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Activity: <?= htmlspecialchars($report_user['username']) ?></h1></div>
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
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Data Counts</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <?php foreach ($data_counts as $dc): ?>
                                <tr><td><?= $dc['label'] ?></td><td><span class="badge badge-primary float-right"><?= number_format($dc['count']) ?></span></td></tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Uploads (30 days)</h3></div>
                        <div class="card-body"><canvas id="uploadChart" height="150"></canvas></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Login History</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <thead><tr><th>Date</th><th>IP</th><th>Success</th></tr></thead>
                                <tbody>
                                    <?php foreach ($login_history as $lh): ?>
                                    <tr><td><?= htmlspecialchars($lh['date']) ?></td><td><?= htmlspecialchars($lh['ip_address']) ?></td><td><?= $lh['success'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>' ?></td></tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Recent Actions</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <thead><tr><th>Action</th><th>Timestamp</th></tr></thead>
                                <tbody>
                                    <?php foreach ($actions as $a): ?>
                                    <tr><td><span class="badge badge-info"><?= htmlspecialchars($a['action_type']) ?></span></td><td><?= htmlspecialchars($a['created_at']) ?></td></tr>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('uploadChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($uploads_per_day, 'date')) ?: '[]' ?>,
        datasets: [{
            label: 'Uploads',
            data: <?= json_encode(array_column($uploads_per_day, 'count')) ?: '[]' ?>,
            backgroundColor: '#17a2b8'
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
});
</script>
