<?php
/** @var array $anomalies */
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-exclamation-triangle text-warning mr-2"></i> Behavioral Anomalies</h1>
                    <p class="text-muted mb-0">Pattern-of-Life Analysis — Unusual activity detected during sleep hours (11 PM – 5 AM)</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>">Intelligence</a></li>
                        <li class="breadcrumb-item active">Anomalies</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <!-- Summary info-boxes -->
            <div class="row">
                <?php
                $typeCounts = ['call' => 0, 'app_usage' => 0, 'location' => 0];
                foreach ($anomalies as $a) {
                    if (isset($typeCounts[$a['type']])) $typeCounts[$a['type']]++;
                }
                ?>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Anomalies</span>
                            <span class="info-box-number"><?= count($anomalies) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-phone-slash"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Late Night Calls</span>
                            <span class="info-box-number"><?= $typeCounts['call'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-purple elevation-1"><i class="fas fa-mobile-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Late Night App Use</span>
                            <span class="info-box-number"><?= $typeCounts['app_usage'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-map-marker-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Late Night Movement</span>
                            <span class="info-box-number"><?= $typeCounts['location'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Callout -->
            <div class="callout callout-warning">
                <h5><i class="fas fa-bed mr-2"></i>Sleep Baseline: 11:00 PM – 5:00 AM</h5>
                <p>All events below occurred outside the expected sleep window. These may indicate unusual or suspicious device activity worth investigating.</p>
            </div>

            <!-- Anomaly Timeline -->
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-stream mr-2"></i>Anomaly Feed</h3>
                    <div class="card-tools">
                        <span class="badge badge-warning"><?= count($anomalies) ?> events</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($anomalies)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3 d-block"></i>
                            <h4 class="text-muted">No anomalies detected</h4>
                            <p class="text-muted">All activity appears to fall within the expected routine window.</p>
                        </div>
                    <?php else: ?>
                        <div class="timeline p-3" id="anomaly-timeline">
                            <?php
                            $lastDate = '';
                            foreach ($anomalies as $anomaly):
                                $rawTs = (int)($anomaly['timestamp'] ?? 0);
                                $ts    = $rawTs > 9999999999 ? (int)($rawTs / 1000) : $rawTs;
                                $dateStr = date('d M Y', $ts);
                                $timeStr = date('H:i', $ts);
                                $sev   = $anomaly['severity'] ?? 'warning';
                                $type  = $anomaly['type']     ?? 'other';

                                $bgMap = [
                                    'call'     => 'bg-danger',
                                    'app_usage'=> 'bg-warning',
                                    'location' => 'bg-danger',
                                ];
                                $iconMap = [
                                    'call'     => 'fas fa-phone-slash',
                                    'app_usage'=> 'fas fa-mobile-alt',
                                    'location' => 'fas fa-map-marker-alt',
                                ];
                                $bg   = $bgMap[$type]   ?? 'bg-secondary';
                                $icon = $iconMap[$type]  ?? 'fas fa-question-circle';

                                if ($dateStr !== $lastDate):
                                    $lastDate = $dateStr;
                            ?>
                                <div class="time-label">
                                    <span class="bg-dark"><?= esc($dateStr) ?></span>
                                </div>
                            <?php endif; ?>
                                <div>
                                    <i class="<?= $icon ?> <?= $bg ?>"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="fas fa-clock"></i> <?= $timeStr ?></span>
                                        <h3 class="timeline-header">
                                            <span class="badge badge-<?= $sev === 'danger' ? 'danger' : 'warning' ?> mr-2">
                                                <i class="fas fa-exclamation-circle mr-1"></i><?= ucfirst($sev) ?>
                                            </span>
                                            <?= esc($anomaly['title']) ?>
                                        </h3>
                                        <div class="timeline-body text-muted">
                                            <?= esc($anomaly['description']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div>
                                <i class="fas fa-clock bg-gray"></i>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Routine Map Card -->
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map mr-2"></i>Routine Baseline</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="callout callout-success">
                                <h5><i class="fas fa-sun mr-2"></i>Active Hours</h5>
                                <p class="mb-0"><strong>5:00 AM – 11:00 PM</strong><br>
                                <small class="text-muted">Expected window of normal activity.</small></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="callout callout-danger">
                                <h5><i class="fas fa-moon mr-2"></i>Sleep Hours</h5>
                                <p class="mb-0"><strong>11:00 PM – 5:00 AM</strong><br>
                                <small class="text-muted">Activity in this range triggers anomaly flags.</small></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="callout callout-warning">
                                <h5><i class="fas fa-shield-alt mr-2"></i>Risk Indicators</h5>
                                <ul class="pl-3 mb-0 small text-muted">
                                    <li>Calls during sleep hours</li>
                                    <li>App usage after midnight</li>
                                    <li>Location changes at 3 AM</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
