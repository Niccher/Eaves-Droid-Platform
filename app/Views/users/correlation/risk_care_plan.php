<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-shield-alt text-primary mr-2"></i>
                            Risk Score & Care Plan
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-<?= ($plan ?? 'free') === 'platinum' ? 'danger' : (($plan ?? 'free') === 'gold' ? 'warning' : 'secondary') ?> p-2">
                                <?= ucfirst($plan ?? 'free') ?>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Device risk assessment with actionable care plan recommendations</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="float-right mt-2">
                        <a href="<?= base_url('analysis') ?>" class="btn btn-info ml-2">
                            <i class="fas fa-microchip mr-1"></i> AdvancedController Analysis
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if ($risk === null): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            No risk score data available yet. Run the anomaly scanner to generate a risk assessment.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-tachometer-alt mr-2"></i>
                                    Risk Score
                                </h3>
                            </div>
                            <div class="card-body text-center">
                                <div class="progress progress-lg mb-3">
                                    <div class="progress-bar <?= $risk['score'] >= 70 ? 'bg-danger' : ($risk['score'] >= 40 ? 'bg-warning' : ($risk['score'] >= 20 ? 'bg-info' : 'bg-success')) ?>"
                                         style="width: <?= $risk['score'] ?>%">
                                        <?= $risk['score'] ?>/100
                                    </div>
                                </div>
                                <p class="text-muted">Computed at <?= $risk['computed_at'] ?></p>
                            </div>
                        </div>

                        <?php if ($percentile): ?>
                            <div class="card card-info mt-3">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-percentage mr-2"></i>
                                        Cohort Percentile
                                    </h3>
                                </div>
                                <div class="card-body text-center">
                                    <h2 class="display-4">
                                        <span class="badge badge-<?= $percentile['percentile'] >= 75 ? 'danger' : ($percentile['percentile'] >= 50 ? 'warning' : 'info') ?>">
                                            Top <?= $percentile['percentile'] ?>%
                                        </span>
                                    </h2>
                                    <p class="text-muted">vs <?= $percentile['cohort_size'] ?> other users</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-lg-8">
                        <?php if (!empty($trend) && ($is_gold || $is_platinum)): ?>
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-line mr-2"></i>
                                        Risk Trend
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="riskTrendChart" height="200"></canvas>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Severity Breakdown
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach (['critical' => 'Critical', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $sev => $label): ?>
                                        <div class="col-6 col-md-3 mb-2">
                                            <div class="small-box bg-<?= $sev === 'critical' ? 'danger' : ($sev === 'high' ? 'warning' : ($sev === 'medium' ? 'info' : 'secondary')) ?>">
                                                <div class="inner">
                                                    <h3><?= (int)($severityCounts[$sev] ?? 0) ?></h3>
                                                    <p><?= $label ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($actions) && $is_platinum): ?>
                            <div class="card card-danger">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-clipboard-list mr-2"></i>
                                        Priority Action Plan
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($actions as $action): ?>
                                        <div class="alert alert-<?= $action['priority'] === 'critical' ? 'danger' : ($action['priority'] === 'high' ? 'warning' : ($action['priority'] === 'medium' ? 'info' : 'secondary')) ?> mb-2">
                                            <div class="d-flex justify-content-between">
                                                <strong>
                                                    <span class="badge badge-<?= $action['priority'] === 'critical' ? 'danger' : ($action['priority'] === 'high' ? 'warning' : 'info') ?> mr-2">
                                                        <?= strtoupper($action['priority']) ?>
                                                    </span>
                                                    <?= esc($action['title']) ?>
                                                </strong>
                                                <small class="text-muted"><?= $action['evidence_count'] ?> evidence</small>
                                            </div>
                                            <p class="mb-0 mt-1"><small><?= esc($action['description']) ?></small></p>
                                            <span class="badge badge-light mt-1"><?= esc($action['category']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php if (!empty($trend) && ($is_gold || $is_platinum)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trendData = <?= json_encode($trend) ?>;
    const labels = trendData.map(d => d.computed_at);
    const scores = trendData.map(d => d.score);

    const ctx = document.getElementById('riskTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Risk Score',
                data: scores,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
            }],
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'Score' },
                },
                x: {
                    title: { display: true, text: 'Date' },
                },
            },
        },
    });
});
</script>
<?php endif; ?>