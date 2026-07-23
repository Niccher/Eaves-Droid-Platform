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
            <div class="row">
                <div class="col-lg-2 col-4"><div class="small-box bg-info"><div class="inner"><h3><?= $total ?></h3><p>Total</p></div></div></div>
                <div class="col-lg-2 col-4"><div class="small-box bg-success"><div class="inner"><h3><?= $active ?></h3><p>Active</p></div></div></div>
                <div class="col-lg-2 col-4"><div class="small-box bg-warning"><div class="inner"><h3><?= $used ?></h3><p>Used</p></div></div></div>
                <div class="col-lg-2 col-4"><div class="small-box bg-danger"><div class="inner"><h3><?= $expired ?></h3><p>Expired</p></div></div></div>
                <div class="col-lg-2 col-4"><div class="small-box bg-secondary"><div class="inner"><h3><?= $deleted ?></h3><p>Deleted</p></div></div></div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Tokens per User</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <thead><tr><th>User</th><th>Tokens</th></tr></thead>
                                <tbody>
                                    <?php foreach ($per_user as $pu): ?>
                                    <tr><td><?= htmlspecialchars($pu['username'] ?? 'Unknown') ?></td><td><?= $pu['token_count'] ?></td></tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Token Creation (30 days)</h3></div>
                        <div class="card-body"><canvas id="tokenChart" height="200"></canvas></div>
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
