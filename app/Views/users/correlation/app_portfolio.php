<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-th-large text-primary mr-2"></i> App Portfolio & Categorization</h1>
                </div>
                <div class="col-sm-6 text-right">
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
                        <?php $_eng = (new \App\Models\AnomaliesModel())->getDefaultEngine(); $_engLabel = match($_eng){'python'=>'Python Engine','both'=>'Hybrid Engine',default=>'PHP Engine'}; ?>
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

    <section class="content">
        <div class="container-fluid">
            <!-- Category Distribution -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">Category Distribution</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="categoryChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <?php foreach ($categories as $cat => $count): if ($count == 0) continue; ?>
                        <div class="col-sm-6">
                            <div class="info-box shadow-sm border">
                                <span class="info-box-icon <?= $cat == 'Social' ? 'bg-info' : ($cat == 'Finance' ? 'bg-success' : 'bg-secondary') ?>">
                                    <i class="fas <?= $cat == 'Social' ? 'fa-share-alt' : ($cat == 'Finance' ? 'fa-wallet' : 'fa-box') ?>"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text"><?= $cat ?></span>
                                    <span class="info-box-number"><?= $count ?> Apps</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-light border shadow-sm">
                        <h5><i class="fas fa-lightbulb text-warning mr-2"></i> Portfolio Insight</h5>
                        <?php 
                        arsort($categories);
                        $dominant = key($categories);
                        ?>
                        The device appears to be primarily used for <b><?= $dominant ?></b> activity. 
                        This classification is based on automated analysis of 1,000+ known package name patterns.
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function () {
    var ctx = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'polarArea',
        data: {
            labels: <?= json_encode(array_keys($categories)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($categories)) ?>,
                backgroundColor: ['#17a2b8', '#28a745', '#ffc107', '#007bff', '#6c757d', '#343a40', '#adb5bd']
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
});
</script>
