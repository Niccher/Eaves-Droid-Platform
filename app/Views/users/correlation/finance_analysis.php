    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-8 col-md-6">
                        <div class="d-flex align-items-center">
                            <h1 class="h2 mb-0">
                                <i class="fas fa-money-bill-wave text-success mr-2"></i>
                                Financial Intelligence
                            </h1>
                            <div class="ml-3">
                                <span class="badge badge-light border p-2 text-danger">
                                    Total Spending: <b>Ksh <?= number_format($financial_data['totalSpending'] ?? 0, 2) ?></b>
                                </span>
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0">Automated spending analysis and transaction tracking from your mobile wallet</p>
                    </div>
                    <div class="col-lg-4 col-md-6 text-right">
                        <a class="btn btn-outline-info btn-sm" href="<?= base_url('analysis') ?>"><i class="fas fa-arrow-left mr-1"></i> Back to Analysis</a>
                    </div>
                </div>
            </div>
        </section>

<?php if (isset($ml_insight) && !empty($ml_insight['insights'])): ?>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header">
                        <?php $_eng = (new \App\Models\Mod_Anomalies())->getDefaultEngine(); $_engLabel = match($_eng){'python'=>'Python Engine','both'=>'Hybrid Engine',default=>'PHP Engine'}; ?>
                        <h3 class="card-title"><i class="fas fa-brain mr-2"></i> <?= $_engLabel ?> Intelligence</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <span class="badge badge-info p-2"><?= $ml_insight['algorithm'] ?></span>
                                <p class="text-muted mt-2 mb-0"><small><?= $ml_insight['data_source'] ?></small></p>
                            </div>
                            <div class="col-md-8">
                                <p><?= $ml_insight['description'] ?></p>
                                <ul class="mb-0">
                                    <?php foreach ($ml_insight['insights'] as $insight): ?>
                                    <li><?= $insight ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?= view('analysis/anomaly_alert_card', ['anomaly_alerts' => $anomaly_alerts ?? []]) ?>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                
                <!-- Charts Row -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-outline card-success shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-area mr-1"></i> Monthly Cash Flow</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="cashFlowChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-outline card-info shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Spending by Category</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="spendingTypeChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="card card-outline card-secondary shadow-sm mt-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-2"></i>
                            Recent Transactions
                            <small class="text-muted ml-2">Showing <?= count($financial_data['transactions']) ?> of <?= $total ?></small>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Sender/Provider</th>
                                        <th>Description</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($financial_data['transactions'] as $tx): ?>
                                    <tr>
                                        <td class="text-muted"><?= date('M d, Y H:i', $tx['date'] / 1000) ?></td>
                                        <td class="font-weight-bold"><?= esc($tx['sender']) ?></td>
                                        <td style="max-width: 300px;" class="text-sm"><?= esc($tx['description']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $tx['type'] == 'income' ? 'success' : ($tx['type'] == 'utility' ? 'warning' : 'info') ?>">
                                                <?= ucfirst($tx['type']) ?>
                                            </span>
                                        </td>
                                        <td class="font-weight-bold text-<?= $tx['type'] == 'income' ? 'success' : 'danger' ?>">
                                            <?= $tx['type'] == 'income' ? '+' : '-' ?> Ksh <?= number_format($tx['amount'], 2) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="entry-info">
                                Showing <?= (($currentPage-1)*$perPage+1) ?> to <?= min($currentPage*$perPage, $total) ?> of <?= $total ?> entries
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="float-right">
                                <?= $pager_links ?? '' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>
        </section>
    </div>

    <!-- Scripts for Charts -->
    <script>
        $(document).ready(function() {
            // Cash Flow Chart (Spending vs Income)
            const cashFlowCtx = document.getElementById('cashFlowChart').getContext('2d');
            const months = <?= json_encode(array_reverse(array_keys($financial_data['spendingByMonth']))) ?>;
            const spendingData = <?= json_encode(array_reverse(array_values($financial_data['spendingByMonth']))) ?>;
            const incomeData = <?= json_encode(array_reverse(array_values($financial_data['incomeByMonth']))) ?>;

            new Chart(cashFlowCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Spending',
                            data: spendingData,
                            borderColor: '#dc3545',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Income',
                            data: incomeData,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Spending Type Chart
            const typeCtx = document.getElementById('spendingTypeChart').getContext('2d');
            const types = <?= json_encode(array_keys($financial_data['spendingByType'])) ?>;
            const typeValues = <?= json_encode(array_values($financial_data['spendingByType'])) ?>;

            new Chart(typeCtx, {
                type: 'doughnut',
                data: {
                    labels: types.map(t => t.charAt(0).toUpperCase() + t.slice(1)),
                    datasets: [{
                        data: typeValues,
                        backgroundColor: ['#ffc107', '#17a2b8', '#007bff', '#6c757d', '#343a40'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    </script>
