<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Reports Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item active">Reports</li>
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
                        <h5 class="text-info font-weight-bold mb-1">Reports Dashboard</h5>
                        <p class="mb-0 small text-muted">Generate and view system reports — data usage summaries, user activity logs, performance metrics, and custom report exports.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info"><div class="inner"><h3><?= number_format($total_users) ?></h3><p>Total Users</p></div><div class="icon"><i class="fas fa-users"></i></div></div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success"><div class="inner"><h3><?= number_format($users_with_data) ?></h3><p>Users With Data</p></div><div class="icon"><i class="fas fa-database"></i></div></div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning"><div class="inner"><h3><?= number_format($total_uploads) ?></h3><p>Total Uploads</p></div><div class="icon"><i class="fas fa-upload"></i></div></div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger"><div class="inner"><h3><?= number_format($total_storage / 1048576, 1) ?> MB</h3><p>Storage Used</p></div><div class="icon"><i class="fas fa-hdd"></i></div></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Data Type Distribution</h3></div>
                        <div class="card-body"><canvas id="dataTypeChart" height="250"></canvas></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Quick Links</h3></div>
                        <div class="card-body">
                            <a href="<?= base_url('admin/reports/user-activity') ?>" class="btn btn-outline-primary btn-block mb-2"><i class="fas fa-user mr-1"></i> User Activity Reports</a>
                            <a href="<?= base_url('admin/reports/data-usage') ?>" class="btn btn-outline-info btn-block mb-2"><i class="fas fa-chart-pie mr-1"></i> Data Usage Reports</a>
                            <a href="<?= base_url('admin/reports/performance') ?>" class="btn btn-outline-warning btn-block mb-2"><i class="fas fa-tachometer-alt mr-1"></i> System Performance</a>
                            <a href="<?= base_url('admin/reports/generate') ?>" class="btn btn-outline-success btn-block"><i class="fas fa-file-export mr-1"></i> Generate Custom Report</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
var ctx = document.getElementById('dataTypeChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($data_type_counts, 'label')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($data_type_counts, 'count')) ?>,
            backgroundColor: ['#17a2b8','#28a745','#ffc107','#dc3545','#6610f2','#e83e8c','#20c997','#fd7e14']
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});
</script>
