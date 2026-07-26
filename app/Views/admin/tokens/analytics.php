<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Token Analytics</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/tokens') ?>">Tokens</a></li>
                        <li class="breadcrumb-item active">Analytics</li>
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
                        <h5 class="text-info font-weight-bold mb-1">Token Analytics</h5>
                        <p class="mb-0 small text-muted">Usage analytics for API tokens — request counts, last-used timestamps, endpoint access patterns, and rate-limit consumption.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="btn-group">
                        <a href="<?= base_url('admin/tokens') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-list mr-1"></i>All</a>
                        <a href="<?= base_url('admin/tokens/expired') ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-clock mr-1"></i>Expired</a>
                        <a href="<?= base_url('admin/tokens/analytics') ?>" class="btn btn-sm btn-info"><i class="fas fa-chart-bar mr-1"></i>Analytics</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-2 col-4">
                    <div class="small-box bg-info animated pulse">
                        <div class="inner"><h3><i class="fas fa-database mr-1"></i> <?= $total ?></h3><p>Total Tokens</p></div>
                        <div class="icon"><i class="fas fa-key"></i></div>
                    </div>
                </div>
                <div class="col-lg-2 col-4">
                    <div class="small-box bg-success">
                        <div class="inner"><h3><i class="fas fa-check-circle mr-1"></i> <?= $active ?></h3><p>Active</p></div>
                        <div class="icon"><i class="fas fa-shield-alt"></i></div>
                    </div>
                </div>
                <div class="col-lg-2 col-4">
                    <div class="small-box bg-warning">
                        <div class="inner"><h3><i class="fas fa-history mr-1"></i> <?= $used ?></h3><p>Used</p></div>
                        <div class="icon"><i class="fas fa-check-double"></i></div>
                    </div>
                </div>
                <div class="col-lg-2 col-4">
                    <div class="small-box bg-danger">
                        <div class="inner"><h3><i class="fas fa-clock mr-1"></i> <?= $expired ?></h3><p>Expired</p></div>
                        <div class="icon"><i class="fas fa-hourglass-end"></i></div>
                    </div>
                </div>
                <div class="col-lg-2 col-4">
                    <div class="small-box bg-secondary">
                        <div class="inner"><h3><i class="fas fa-trash-alt mr-1"></i> <?= $deleted ?></h3><p>Deleted</p></div>
                        <div class="icon"><i class="fas fa-times-circle"></i></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-info shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-users mr-1 text-info"></i> Tokens per User</h3>
                            <div class="card-tools"><span class="badge badge-info"><?= count($per_user) ?> users</span></div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <thead><tr><th>User</th><th class="text-center">Tokens</th></tr></thead>
                                <tbody>
                                    <?php foreach ($per_user as $pu): ?>
                                    <tr>
                                        <td><i class="fas fa-user-circle text-muted mr-1"></i><?= htmlspecialchars($pu['username'] ?? 'Unknown') ?></td>
                                        <td class="text-center"><span class="badge badge-info badge-pill"><?= $pu['token_count'] ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-outline card-info shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-line mr-1 text-info"></i> Token Creation (30 days)</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="tokenChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('tokenChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($usage_by_day, 'date')) ?: '[]' ?>,
        datasets: [{
            label: 'Tokens Created',
            data: <?= json_encode(array_column($usage_by_day, 'count')) ?: '[]' ?>,
            backgroundColor: '#17a2b8'
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
});
</script>
