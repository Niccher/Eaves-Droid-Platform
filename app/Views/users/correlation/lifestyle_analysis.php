<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header pt-3 pb-2">
        <div class="container-fluid">
            <div class="row align-items-center mb-2">
                <div class="col-sm-6">
                    <h1 class="h3 mb-0 text-dark font-weight-bold">
                        <i class="fas fa-walking text-success mr-2"></i> Lifestyle &amp; Mobility Profiling
                    </h1>
                    <p class="text-muted mb-0 small">Circadian rhythm sleep profiling, physical movement overlay, and daily travel mode breakdown.</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-outline-secondary btn-sm shadow-sm" href="<?= $back_url ?? base_url('analysis') ?>"><i class="fas fa-arrow-left mr-1"></i> <?= esc($back_label ?? 'Back to Analysis') ?></a>
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
                    <div class="rounded-circle p-3 mr-3 shadow" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);">
                        <i class="fas fa-bed fa-lg text-white"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-weight-bold text-white"><?= $_engLabel ?> Circadian &amp; Lifestyle Routine Synthesis</h4>
                        <small class="text-light opacity-75">Sleep-wake cycle estimation, physical movement vs screentime, &amp; travel mode profiling</small>
                    </div>
                </div>
                <div>
                    <span class="badge badge-pill badge-success px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
                        <i class="fas fa-microchip mr-1"></i> Algorithm: <?= esc($ml_insight['algorithm'] ?? 'Circadian Heuristic Profiler') ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-5 mb-3 mb-md-0 border-right-md border-success pr-md-4">
                        <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                            <h6 class="text-success font-weight-bold mb-2" style="color: #4ade80;"><i class="fas fa-database mr-2"></i> What Is Happening</h6>
                            <p class="mb-0 text-light opacity-90" style="font-size: 0.9rem; line-height: 1.5;">
                                <?= esc($ml_insight['description'] ?? 'Estimates daily sleep-wake routine by correlating screen-lock events, battery charging logs, and nighttime inactivity, while categorizing movement into walking, driving, and transit.') ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-7 pl-md-4">
                        <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-lightbulb mr-2"></i> Key Lifestyle Findings</h6>
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
            <div class="row">
                <!-- Activity Breakdown -->
                <div class="col-md-7">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Activity Breakdown</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="activityChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Screen Time Balance -->
                <div class="col-md-5">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Screen Time vs Interaction</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="screenChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Insights -->
                <div class="col-md-12">
                    <div class="card card-outline card-warning shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-lightbulb text-warning mr-2"></i> Mobility Insights</h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center py-2">
                                <div class="col-sm-4 border-right">
                                    <div class="description-block py-2">
                                        <div class="mb-2">
                                            <span class="rounded-circle bg-secondary text-white p-3 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                                                <i class="fas fa-couch fa-lg"></i>
                                            </span>
                                        </div>
                                        <h4 class="description-header text-secondary font-weight-bold"><?= number_format($mobility['STILL']) ?> pings</h4>
                                        <span class="description-text text-dark font-weight-bold d-block">SEDENTARY PERIODS</span>
                                        <small class="text-muted d-block mt-1">Stationary baseline &amp; resting state telemetry</small>
                                    </div>
                                </div>
                                <div class="col-sm-4 border-right">
                                    <div class="description-block py-2">
                                        <div class="mb-2">
                                            <span class="rounded-circle bg-success text-white p-3 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                                                <i class="fas fa-running fa-lg"></i>
                                            </span>
                                        </div>
                                        <h4 class="description-header text-success font-weight-bold"><?= number_format($mobility['WALKING'] + $mobility['RUNNING']) ?> pings</h4>
                                        <span class="description-text text-dark font-weight-bold d-block">ACTIVE PERIODS</span>
                                        <small class="text-muted d-block mt-1">Walking, running &amp; physical movement</small>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="description-block py-2">
                                        <div class="mb-2">
                                            <span class="rounded-circle bg-primary text-white p-3 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                                                <i class="fas fa-car fa-lg"></i>
                                            </span>
                                        </div>
                                        <h4 class="description-header text-primary font-weight-bold"><?= number_format($mobility['IN_VEHICLE']) ?> pings</h4>
                                        <span class="description-text text-dark font-weight-bold d-block">TRANSIT PERIODS</span>
                                        <small class="text-muted d-block mt-1">Vehicle commuting &amp; transit movement</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enriched Intelligence: Circadian Sleep & Daily Travel Distance -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card card-outline card-indigo shadow-sm h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-moon text-indigo mr-2"></i> Circadian &amp; Sleep Routine Profiler</h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center mb-3">
                                <div class="col-6 border-right">
                                    <h4 class="text-indigo mb-0"><?= esc($circadian['sleep_start'] ?? '23:30') ?> - <?= esc($circadian['wake_time'] ?? '07:15') ?></h4>
                                    <small class="text-muted">Estimated Sleep Window</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success mb-0"><?= esc($circadian['sleep_duration_hours'] ?? '7.75') ?> hrs</h4>
                                    <small class="text-muted">Est. Duration</small>
                                </div>
                            </div>
                            <div class="p-2 bg-light rounded border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-sm font-weight-bold">Night Owl Activity Index</span>
                                    <span class="badge badge-<?= ($circadian['night_owl_score'] ?? 0) > 50 ? 'warning' : 'info' ?>"><?= $circadian['night_owl_score'] ?? 25 ?> / 100</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-<?= ($circadian['night_owl_score'] ?? 0) > 50 ? 'warning' : 'indigo' ?>" role="progressbar" style="width: <?= $circadian['night_owl_score'] ?? 25 ?>%"></div>
                                </div>
                                <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle mr-1"></i> Status: <?= esc($circadian['quality_status'] ?? 'Normal Rest Baseline') ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-outline card-teal shadow-sm h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-route text-teal mr-2"></i> Daily Travel Distance Breakdown</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><i class="fas fa-walking text-success mr-2"></i> Walking &amp; Running</span>
                                <span class="font-weight-bold"><?= $travel_dist['walking_km'] ?? 3.2 ?> km</span>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: <?= min(100, ($travel_dist['walking_km'] ?? 3.2) * 10) ?>%"></div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><i class="fas fa-car text-primary mr-2"></i> Vehicle Transit</span>
                                <span class="font-weight-bold"><?= $travel_dist['driving_km'] ?? 14.5 ?> km</span>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: <?= min(100, ($travel_dist['driving_km'] ?? 14.5) * 4) ?>%"></div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><i class="fas fa-bicycle text-warning mr-2"></i> Cycling / Other</span>
                                <span class="font-weight-bold"><?= $travel_dist['transit_km'] ?? 1.8 ?> km</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: <?= min(100, ($travel_dist['transit_km'] ?? 1.8) * 15) ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- App Usage & Screentime on its own row -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-mobile-alt text-primary mr-2"></i> App Usage & Screentime</h3>
                        </div>
                        <div class="card-body">
                            <?php
                            $formatTime = function($ms) {
                                $secs = floor(($ms ?? 0) / 1000);
                                if ($secs <= 0) return '0 Secs';
                                $d = floor($secs / 86400);
                                $h = floor(($secs % 86400) / 3600);
                                $m = floor(($secs % 3600) / 60);
                                $s = $secs % 60;
                                $res = [];
                                if ($d > 0) $res[] = $d . ($d == 1 ? ' Day' : ' Days');
                                if ($h > 0) $res[] = $h . ($h == 1 ? ' Hour' : ' Hours');
                                if ($m > 0) $res[] = $m . ($m == 1 ? ' Min' : ' Mins');
                                if ($s > 0 || empty($res)) $res[] = $s . ($s == 1 ? ' Sec' : ' Secs');
                                return implode(', ', $res);
                            };
                            ?>
                            <div class="row mb-3">
                                <div class="col-12 text-center">
                                    <h4 class="text-primary font-weight-bold"><?= $formatTime($mobility['total_screentime_ms'] ?? 0) ?></h4>
                                    <span class="text-muted d-block">Total Foreground Screentime</span>
                                    <small class="text-muted font-italic mt-1 d-block">
                                        <i class="fas fa-history mr-1"></i> Recorded telemetry since <b><?= esc($mobility['first_record'] ?? date('M j, Y, g:i a', strtotime('-7 days'))) ?></b> until <b><?= esc($mobility['last_record'] ?? date('M j, Y, g:i a')) ?></b>
                                    </small>
                                </div>
                            </div>
                            <?php if (!empty($mobility['top_apps'])): ?>
                                <h5>Top 5 Apps by Usage</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>App Name</th>
                                                <th>App Type</th>
                                                <th>Time Spent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($mobility['top_apps'] as $app): ?>
                                            <?php 
                                                $pkg = strtolower($app['package_name'] ?? $app['name'] ?? '');
                                                $isSystem = (str_contains($pkg, 'android') || str_contains($pkg, 'google') || str_contains($pkg, 'systemui') || !empty($app['is_system']));
                                            ?>
                                            <tr>
                                                <td><b><?= esc($app['name']) ?></b></td>
                                                <td>
                                                    <span class="badge badge-<?= $isSystem ? 'secondary' : 'info' ?>">
                                                        <i class="fas fa-<?= $isSystem ? 'cogs' : 'user' ?> mr-1"></i>
                                                        <?= $isSystem ? 'System App' : 'User App' ?>
                                                    </span>
                                                </td>
                                                <td><?= $formatTime($app['time'] ?? 0) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No app usage data available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function () {
    var ctx = document.getElementById('activityChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Still', 'Walking', 'Vehicle', 'Bicycle', 'Running', 'Tilting', 'Unknown'],
            datasets: [{
                data: [
                    <?= $mobility['STILL'] ?>,
                    <?= $mobility['WALKING'] ?>,
                    <?= $mobility['IN_VEHICLE'] ?>,
                    <?= $mobility['ON_BICYCLE'] ?>,
                    <?= $mobility['RUNNING'] ?>,
                    <?= $mobility['TILTING'] ?>,
                    <?= $mobility['UNKNOWN'] ?>
                ],
                backgroundColor: ['#6c757d', '#28a745', '#007bff', '#ffc107', '#dc3545', '#17a2b8', '#e9ecef']
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

    var sCtx = document.getElementById('screenChart').getContext('2d');
    new Chart(sCtx, {
        type: 'pie',
        data: {
            labels: ['Screen On', 'Screen Off'],
            datasets: [{
                data: [<?= $mobility['screen_on'] ?>, <?= $mobility['screen_off'] ?>],
                backgroundColor: ['#20c997', '#adb5bd']
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true
        }
    });
});
</script>
