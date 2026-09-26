<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<style>
.health-box { border-radius: 12px; background: #fff; border: 1px solid #edf2f7; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 20px; overflow: hidden; }
.health-kpi { font-size: 28px; font-weight: 800; color: #2d3748; line-height: 1; }
.health-metric-card { background: #f8fafc; border-radius: 8px; border: 1px solid #edf2f7; padding: 16px; margin-bottom: 16px; }
.activity-card { border-radius: 8px; border: 1px solid #e2e8f0; padding: 14px 18px; margin-bottom: 12px; background: #fff; }
.activity-icon-wrap { width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <h1 class="h2 mb-0"><i class="fas fa-heartbeat text-danger mr-2"></i>Health &amp; Vitals Telemetry</h1>
                    <p class="text-muted mt-1 mb-0">Biometric vitals, daily activity metrics, sleep parameters, and fitness tracker logs</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>

            <!-- Callout -->
            <div class="callout callout-danger shadow-sm p-3 mb-4" style="border-left:5px solid #dc3545;background:#fdfcfc;">
                <h5 class="font-weight-bold text-danger"><i class="fas fa-shield-alt mr-2"></i>Privacy &amp; Wellness Logging</h5>
                <p class="text-secondary mb-0" style="font-size:14px;">Auditing health trackers ensures sensitive personal behaviors are not leaked to unprivileged applications or external servers.</p>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (empty($rows)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-heartbeat fa-3x text-muted mb-3"></i>
                    <h4 class="text-secondary">No Health Data Cataloged</h4>
                    <p class="text-muted">Activity logs will appear once synchronized.</p>
                </div>
            <?php else: 
                // Sum metrics from current page
                $totalSteps = 0;
                $totalCalories = 0;
                $totalDistanceMeters = 0;
                $hrReadings = [];
                foreach ($rows as $r) {
                    $steps = (int) ($r['step_count'] ?? 0);
                    if ($steps > 0) $totalSteps += $steps;
                    if (!empty($r['calories_kcal'])) $totalCalories += (int)$r['calories_kcal'];
                    if (!empty($r['heart_rate_bpm'])) $hrReadings[] = (int)$r['heart_rate_bpm'];
                    
                    // Distance calculation (use distance_meters or estimate from steps 1 step ≈ 0.762m)
                    if (!empty($r['distance_meters'])) {
                        $totalDistanceMeters += (float)$r['distance_meters'];
                    } elseif ($steps > 0) {
                        $totalDistanceMeters += ($steps * 0.762);
                    }
                }
                $avgHr = count($hrReadings) > 0 ? round(array_sum($hrReadings) / count($hrReadings)) : 0;
                $totalKm = round($totalDistanceMeters / 1000, 2);
                ?>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="health-metric-card d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">Accrued Steps</small>
                                <span class="health-kpi"><?= number_format($totalSteps) ?></span>
                                <small class="text-muted d-block mt-1">Steps on current list</small>
                            </div>
                            <i class="fas fa-walking fa-2x text-primary opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="health-metric-card d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">Traversed Distance</small>
                                <span class="health-kpi"><?= number_format($totalDistanceMeters) ?> <span style="font-size:14px; font-weight:600;">m</span></span>
                                <small class="text-muted d-block mt-1">Approx. <b><?= $totalKm ?> km</b> walked</small>
                            </div>
                            <i class="fas fa-route fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="health-metric-card d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">Vitals Indicator</small>
                                <span class="health-kpi"><?= $avgHr ?: '—' ?> <span style="font-size:14px; font-weight:600;">BPM</span></span>
                                <small class="text-muted d-block mt-1">Average heart rate spike</small>
                            </div>
                            <i class="fas fa-heartbeat fa-2x text-danger opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="health-metric-card d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">Energy Burned</small>
                                <span class="health-kpi"><?= number_format($totalCalories) ?> <span style="font-size:14px; font-weight:600;">Kcal</span></span>
                                <small class="text-muted d-block mt-1">Total active expenditure</small>
                            </div>
                            <i class="fas fa-fire fa-2x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>

                <div class="health-box">
                    <div class="bg-light px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold text-secondary"><i class="fas fa-running mr-2"></i>Synchronized Timeline Logs</h5>
                        <small class="text-muted">Showing <?= count($rows) ?> activities</small>
                    </div>
                    <div class="p-3 bg-light">
                        <?php foreach ($rows as $r):
                            $type = strtolower($r['data_type'] ?? '');
                            $icon = 'fa-heartbeat';
                            $bg = 'bg-info text-white';
                            $desc = 'General wellness update';
                            
                            $rowSteps = (int) ($r['step_count'] ?? 0);
                            $rowMeters = !empty($r['distance_meters']) ? (float)$r['distance_meters'] : ($rowSteps > 0 ? round($rowSteps * 0.762, 1) : 0);
                            $rowKm = $rowMeters > 0 ? round($rowMeters / 1000, 2) : 0;

                            if (str_contains($type, 'step') || $rowSteps > 0) {
                                $icon = 'fa-walking';
                                $bg = 'bg-primary text-white';
                                $desc = 'Logged ' . number_format($rowSteps ?: $r['value']) . ' steps (' . number_format($rowMeters) . ' m / ' . $rowKm . ' km).';
                            } elseif (str_contains($type, 'heart') || $r['heart_rate_bpm'] > 0) {
                                $icon = 'fa-heartbeat';
                                $bg = 'bg-danger text-white';
                                $desc = 'Heart rate reading: ' . ($r['heart_rate_bpm'] ?: $r['value']) . ' BPM.';
                            } elseif (str_contains($type, 'sleep') || $r['sleep_stage']) {
                                $icon = 'fa-bed';
                                $bg = 'bg-indigo text-white';
                                $desc = 'Sleep session: Stage ' . ($r['sleep_stage'] ?: 'Resting') . '.';
                            } elseif ($r['workout_type']) {
                                $icon = 'fa-dumbbell';
                                $bg = 'bg-success text-white';
                                $desc = 'Workout: ' . $r['workout_type'] . ' (' . ($r['calories_kcal'] ?: '—') . ' Kcal burned, ' . $rowKm . ' km).';
                            }
                            
                            $dateStr = $r['end_time'] ? format_timestamp_display((int)$r['end_time']) : ($r['extracted_at'] ? format_timestamp_display((int)$r['extracted_at']) : '—');
                            $rid = $r['id'] ?? 0;
                            ?>
                            <div class="activity-card d-flex align-items-center justify-content-between flex-wrap">
                                <div class="d-flex align-items-center">
                                    <div class="activity-icon-wrap <?= $bg ?> mr-3">
                                        <i class="fas <?= $icon ?>"></i>
                                    </div>
                                    <div>
                                        <strong class="text-dark d-block"><?= esc($r['data_type'] ?: 'Activity Record') ?></strong>
                                        <span class="text-secondary small d-block"><?= esc($desc) ?></span>
                                        <?php if (!empty($r['correlated_lat']) && !empty($r['correlated_lng'])): ?>
                                            <span class="badge badge-light border text-info mt-1"><i class="fas fa-map-marker-alt text-danger mr-1"></i>GPS Location: <?= esc(round($r['correlated_lat'], 4)) ?>, <?= esc(round($r['correlated_lng'], 4)) ?> (<?= esc($r['correlated_event_type'] ?? 'zone') ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-right d-flex align-items-center mt-2 mt-sm-0">
                                    <div class="mr-3">
                                        <span class="badge badge-light border text-muted d-block small">Source: <?= esc($r['data_source_name'] ?: 'Google Fit') ?></span>
                                        <small class="text-muted"><i class="fas fa-clock mr-1"></i><?= $dateStr ?></small>
                                    </div>
                                    <button class="btn btn-xs btn-outline-danger delete-row"
                                            data-id="<?= $rid ?>"
                                            data-url="<?= base_url('advanced/software/health_data/delete') ?>"
                                            title="Delete this entry">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="mt-4">
                            <?= $pager->links('default', 'bootstrap5_full') ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
