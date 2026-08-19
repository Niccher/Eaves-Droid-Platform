<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header pt-3 pb-2">
        <div class="container-fluid">
            <div class="row align-items-center mb-2">
                <div class="col-sm-6">
                    <h1 class="h3 mb-0 text-dark font-weight-bold">
                        <i class="fas fa-th-large text-primary mr-2"></i> App Portfolio, Bandwidth &amp; Crash Analytics
                    </h1>
                    <p class="text-muted mb-0 small">Wi-Fi vs Cellular data exfiltration, ANR crash log analytics, and unused bloatware detection.</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-outline-secondary btn-sm shadow-sm" href="<?= base_url('analysis') ?>"><i class="fas fa-arrow-left mr-1"></i> Back to Analysis</a>
                </div>
            </div>
        </div>
    </section>

<?php if (isset($ml_insight) && !empty($ml_insight['insights'])): ?>
<?php $_eng = (new \App\Models\AnomaliesModel())->getDefaultEngine(); $_engLabel = match($_eng){'python'=>'Python Engine','both'=>'Hybrid Engine',default=>'PHP Engine'}; ?>
<section class="content mb-4">
    <div class="container-fluid">
        <div class="card bg-secondary text-white shadow-sm border-0" style="border-radius: 8px;">
            <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0 d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <div class="rounded-circle p-3 mr-3 shadow" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%);">
                        <i class="fas fa-cubes fa-lg text-white"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-weight-bold text-white"><?= $_engLabel ?> App Portfolio &amp; Bandwidth Synthesis</h4>
                        <small class="text-light opacity-75">Package classification, background upload exfiltration, &amp; ANR instability profiling</small>
                    </div>
                </div>
                <div>
                    <span class="badge badge-pill badge-info px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
                        <i class="fas fa-microchip mr-1"></i> Algorithm: <?= esc($ml_insight['algorithm'] ?? 'Regex Pattern + KMeans') ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-5 mb-3 mb-md-0 border-right-md border-info pr-md-4">
                        <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                            <h6 class="text-info font-weight-bold mb-2" style="color: #38bdf8;"><i class="fas fa-database mr-2"></i> What Is Happening</h6>
                            <p class="mb-0 text-light opacity-90" style="font-size: 0.9rem; line-height: 1.5;">
                                <?= esc($ml_insight['description'] ?? 'Matches installed app package names against 1,000+ regex patterns to profile usage, monitors background network upload ratios for Trojan activity, and counts crash ANRs.') ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-7 pl-md-4">
                        <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-lightbulb mr-2"></i> App Portfolio Intelligence Findings</h6>
                        <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
                            <?php foreach ($ml_insight['insights'] as $insight): ?>
                            <li class="mb-2 d-flex align-items-start">
                                <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                <span><?= $insight ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
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
                <div class="col-md-5">
                    <div class="card card-outline card-primary shadow-sm h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-pie text-primary mr-2"></i> Category Distribution</h3>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <canvas id="categoryChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="row">
                        <?php 
                        $catMeta = [
                            'Social & Communication' => ['color' => 'bg-info', 'icon' => 'fa-share-alt'],
                            'Social' => ['color' => 'bg-info', 'icon' => 'fa-share-alt'],
                            'Finance & Banking' => ['color' => 'bg-success', 'icon' => 'fa-wallet'],
                            'Finance' => ['color' => 'bg-success', 'icon' => 'fa-wallet'],
                            'Entertainment & Media' => ['color' => 'bg-danger', 'icon' => 'fa-film'],
                            'Entertainment' => ['color' => 'bg-danger', 'icon' => 'fa-film'],
                            'Productivity & Work' => ['color' => 'bg-primary', 'icon' => 'fa-briefcase'],
                            'Productivity' => ['color' => 'bg-primary', 'icon' => 'fa-briefcase'],
                            'Tools & Utilities' => ['color' => 'bg-warning', 'icon' => 'fa-tools'],
                            'Tools' => ['color' => 'bg-warning', 'icon' => 'fa-tools'],
                            'System & Core' => ['color' => 'bg-secondary', 'icon' => 'fa-microchip'],
                            'System' => ['color' => 'bg-secondary', 'icon' => 'fa-microchip'],
                            'Shopping & Lifestyle' => ['color' => 'bg-teal', 'icon' => 'fa-shopping-bag'],
                            'Shopping' => ['color' => 'bg-teal', 'icon' => 'fa-shopping-bag'],
                            'Lifestyle' => ['color' => 'bg-teal', 'icon' => 'fa-shopping-bag'],
                            'Other' => ['color' => 'bg-dark', 'icon' => 'fa-cubes']
                        ];
                        foreach ($categories as $cat => $count): if ($count == 0) continue;
                            $meta = $catMeta[$cat] ?? ['color' => 'bg-info', 'icon' => 'fa-th-large'];
                        ?>
                        <div class="col-sm-6">
                            <div class="info-box shadow-sm border">
                                <span class="info-box-icon <?= $meta['color'] ?> text-white elevation-1">
                                    <i class="fas <?= $meta['icon'] ?>"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text font-weight-bold text-dark"><?= $cat ?></span>
                                    <span class="info-box-number text-muted"><?= number_format($count) ?> Apps</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
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

            <!-- 📡 Bandwidth Exfiltration & Data Usage Split by Network -->
            <?php
            $formatBandwidthMB = function($mb) {
                $val = (float)$mb;
                if ($val >= 1048576) {
                    return number_format($val / 1048576, 2) . ' TBs';
                } else if ($val >= 1024) {
                    return number_format($val / 1024, 2) . ' GBs';
                } else {
                    return number_format($val, 2) . ' MBs';
                }
            };

            // Partition by network type & pick top 10
            $wifiList = [];
            $cellList = [];
            foreach ($bandwidth_usage as $bw) {
                if (!empty($bw['is_wifi'])) {
                    $wifiList[] = $bw;
                } else {
                    $cellList[] = $bw;
                }
            }
            usort($wifiList, fn($a, $b) => ($b['total_mb'] ?? 0) <=> ($a['total_mb'] ?? 0));
            usort($cellList, fn($a, $b) => ($b['total_mb'] ?? 0) <=> ($a['total_mb'] ?? 0));

            $top10Wifi = array_slice($wifiList, 0, 10);
            $top10Cellular = array_slice($cellList, 0, 10);
            ?>

            <div class="row mt-3">
                <!-- Top 10 Wi-Fi Data Apps -->
                <div class="col-md-6">
                    <div class="card card-outline card-info shadow-sm h-100">
                        <div class="card-header border-0 bg-transparent">
                            <h3 class="card-title font-weight-bold mb-0">
                                <i class="fas fa-wifi text-info mr-2"></i> Top 10 Wi-Fi Data Exfiltration
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Application / Package</th>
                                            <th>Total Wi-Fi</th>
                                            <th>Risk</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($top10Wifi)): ?>
                                            <?php foreach ($top10Wifi as $bw): ?>
                                            <?php 
                                                $appName = $bw['app_name'] ?? '';
                                                if (empty($appName)) {
                                                    $parts = explode('.', $bw['package_name']);
                                                    $appName = ucfirst(end($parts));
                                                }
                                            ?>
                                            <tr>
                                                <td>
                                                    <div><b><?= esc($appName) ?></b></div>
                                                    <small class="text-muted"><code><?= esc($bw['package_name']) ?></code></small>
                                                </td>
                                                <td>
                                                    <div><b><?= $formatBandwidthMB($bw['total_mb']) ?></b></div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-arrow-down text-success mr-1"></i><?= $formatBandwidthMB($bw['rx_mb']) ?> in &bull; <i class="fas fa-arrow-up text-danger mr-1"></i><?= $formatBandwidthMB($bw['tx_mb']) ?> out
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= str_contains($bw['exfiltration_risk'], 'HIGH') ? 'danger' : 'success' ?>">
                                                        <?= esc($bw['exfiltration_risk']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center text-muted py-3">No Wi-Fi bandwidth exfiltration recorded.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top 10 Cellular Data Apps -->
                <div class="col-md-6">
                    <div class="card card-outline card-warning shadow-sm h-100">
                        <div class="card-header border-0 bg-transparent">
                            <h3 class="card-title font-weight-bold mb-0">
                                <i class="fas fa-broadcast-tower text-warning mr-2"></i> Top 10 Cellular Data Exfiltration
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Application / Package</th>
                                            <th>Total Cellular</th>
                                            <th>Risk</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($top10Cellular)): ?>
                                            <?php foreach ($top10Cellular as $bw): ?>
                                            <?php 
                                                $appName = $bw['app_name'] ?? '';
                                                if (empty($appName)) {
                                                    $parts = explode('.', $bw['package_name']);
                                                    $appName = ucfirst(end($parts));
                                                }
                                            ?>
                                            <tr>
                                                <td>
                                                    <div><b><?= esc($appName) ?></b></div>
                                                    <small class="text-muted"><code><?= esc($bw['package_name']) ?></code></small>
                                                </td>
                                                <td>
                                                    <div><b><?= $formatBandwidthMB($bw['total_mb']) ?></b></div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-arrow-down text-success mr-1"></i><?= $formatBandwidthMB($bw['rx_mb']) ?> in &bull; <i class="fas fa-arrow-up text-danger mr-1"></i><?= $formatBandwidthMB($bw['tx_mb']) ?> out
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= str_contains($bw['exfiltration_risk'], 'HIGH') ? 'danger' : 'success' ?>">
                                                        <?= esc($bw['exfiltration_risk']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center text-muted py-3">No cellular bandwidth exfiltration recorded.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 💥 App Instability & Crash Log + 🗑️ Unused App / Bloatware Detector -->
            <?php 
            $hasCrash = !empty($crash_analytics);
            $hasBloat = !empty($bloatware_apps);
            ?>
            <?php if ($hasCrash || $hasBloat): ?>
            <div class="row mt-3">
                <?php if ($hasCrash): ?>
                <div class="col-md-<?= $hasBloat ? '6' : '12' ?>">
                    <div class="card card-outline card-warning shadow-sm h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-bug text-warning mr-2"></i> App Instability &amp; ANR Crash Log</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Application</th>
                                        <th>Exception Type</th>
                                        <th>Crashes</th>
                                        <th>Instability Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($crash_analytics as $crash): ?>
                                    <tr>
                                        <td><code><?= esc($crash['package_name']) ?></code></td>
                                        <td><small><?= esc($crash['exception']) ?></small></td>
                                        <td><span class="badge badge-secondary"><?= $crash['count'] ?></span></td>
                                        <td>
                                            <span class="badge badge-<?= $crash['instability_score'] > 6 ? 'danger' : 'warning' ?>">
                                                Score: <?= $crash['instability_score'] ?> (<?= esc($crash['status']) ?>)
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($hasBloat): ?>
                <div class="col-md-<?= $hasCrash ? '6' : '12' ?>">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-broom text-secondary mr-2"></i> Unused Bloatware Detector (&gt;30 Days Inactive)</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Application / Package</th>
                                        <th>Age (Days)</th>
                                        <th>Usage Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bloatware_apps as $bloat): ?>
                                    <tr>
                                        <td>
                                            <div><b><?= esc($bloat['app_name']) ?></b></div>
                                            <small class="text-muted"><code><?= esc($bloat['package_name']) ?></code></small>
                                        </td>
                                        <td><?= $bloat['installed_days_ago'] ?>d</td>
                                        <td><span class="badge badge-light border text-muted"><?= esc($bloat['usage_status']) ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
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
