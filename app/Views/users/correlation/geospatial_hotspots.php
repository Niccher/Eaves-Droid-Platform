<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-map-marked-alt text-success mr-2"></i> Base of Operations Analysis</h1>
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

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-7">
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">Top Identified Bases</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($clusters as $i => $c): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge badge-success mr-2">#<?= ($i+1) ?></span>
                                            <b><?= esc($c['label']) ?></b><br>
                                            <small class="text-muted"><?= $c['lat'] ?>, <?= $c['lng'] ?></small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-light border"><?= $c['pings'] ?> Sessions</span><br>
                                            <small class="text-xs">Last: <?= date('M j, H:i', $c['last_seen'] / 1000) ?></small>
                                        </div>
                                        <a href="https://www.google.com/maps?q=<?= $c['lat'] ?>,<?= $c['lng'] ?>" target="_blank" class="btn btn-sm btn-outline-success ml-3">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Heuristic Classification</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light border">
                                <h5><i class="fas fa-home mr-2"></i> Primary Base</h5>
                                <p class="mb-0 small">Identified by maximum stay duration and pings during late-night hours (00:00 - 06:00).</p>
                            </div>
                            <div class="alert alert-light border">
                                <h5><i class="fas fa-briefcase mr-2"></i> Frequent Hotspot</h5>
                                <p class="mb-0 small">Secondary clusters with pings concentrated during business hours (09:00 - 17:00).</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
