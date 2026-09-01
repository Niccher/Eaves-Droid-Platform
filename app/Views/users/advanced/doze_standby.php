<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'device_id',
    ['is_in_deep_doze', 'is_in_light_doze', 'battery_saver_enabled', 'adaptive_battery_enabled', 'device_standby_bucket', 'power_save_mode', 'battery_saver_since', 'next_maintenance_window', 'last_standby_transition', 'adaptive_battery_learning'],
    ['apps']
);
?>

<style>
.doze-card { border-radius: 12px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #eef2f5; overflow: hidden; margin-bottom: 24px; }
.doze-header { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: #fff; padding: 20px 24px; }
.doze-ring-box { width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
.ring-deep { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 2px solid #3b82f6; }
.ring-light { background: rgba(139, 92, 246, 0.2); color: #a78bfa; border: 2px solid #8b5cf6; }
.ring-active { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 2px solid #f59e0b; }
.doze-meta-box { background: #f8fafc; border-radius: 8px; border: 1px solid #eef2f5; padding: 12px 16px; height: 100%; }
.doze-label { font-size: 10px; font-weight: 700; color: #8892a0; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
.doze-val { font-size: 14px; font-weight: 600; color: #2c3e50; }
.app-doze-card { border-radius: 8px; background: #fff; border: 1px solid #e2e8f0; padding: 12px; margin-bottom: 12px; display: flex; align-items: center; justify-content: justify; }
.bucket-stat { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; margin-right: 10px; margin-bottom: 10px; font-size: 13px; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <h1 class="h2 mb-0"><i class="fas fa-moon text-primary mr-2"></i>Doze &amp; Standby Bucket</h1>
                    <p class="text-muted mt-1 mb-0">Device idle optimization states, power save protocols, and app background buckets</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>

            <!-- Callout -->
            <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
                <h5 class="font-weight-bold text-info"><i class="fas fa-battery-half mr-2"></i>Android Doze &amp; App Standby Power Forensics</h5>
                <p class="text-secondary mb-2" style="font-size:14px;">Audits OS idle power optimizations (Deep/Light Doze) and App Standby Buckets assigned by Android PowerManager to throttle background activity.</p>
                <div class="row" style="font-size:12px;">
                    <div class="col-md-4 border-right">
                        <b class="d-block mb-1">Doze Mode States:</b>
                        <span class="text-muted"><b>Deep Doze:</b> Suspends CPU locks, network access, and background jobs during prolonged idle.<br><b>Light Doze:</b> Periodically allows maintenance windows.</span>
                    </div>
                    <div class="col-md-4 border-right">
                        <b class="d-block mb-1">App Standby Buckets:</b>
                        <span class="text-muted"><b>Active:</b> Currently in use.<br><b>Working Set / Frequent:</b> Regular use.<br><b>Rare / Restricted:</b> Strict job, alarm, and network throttling.</span>
                    </div>
                    <div class="col-md-4">
                        <b class="d-block mb-1">Security Audit Vector:</b>
                        <span class="text-muted">Detects malicious apps or background services attempting to bypass battery optimization limits or hold continuous wake-locks.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (empty($rows)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-moon fa-3x text-muted mb-3"></i>
                    <h4 class="text-secondary">No Doze state captured</h4>
                    <p class="text-muted">Doze configuration stats will appear once extracted.</p>
                </div>
            <?php else: foreach ($rows as $r):
                $deep = !empty($r['is_in_deep_doze']);
                $light = !empty($r['is_in_light_doze']);
                $saver = !empty($r['battery_saver_enabled']);
                
                $stateIcon = 'fa-sun';
                $stateClass = 'ring-active';
                $stateLabel = 'Active (Awake)';
                if ($deep) {
                    $stateIcon = 'fa-moon';
                    $stateClass = 'ring-deep';
                    $stateLabel = 'Deep Doze (Sleep)';
                } elseif ($light) {
                    $stateIcon = 'fa-cloud-moon';
                    $stateClass = 'ring-light';
                    $stateLabel = 'Light Doze (Idle)';
                }
                
                $apps = $r['apps'] ?? [];
                if (is_string($apps)) $apps = json_decode($apps, true) ?: [];
                
                // Group by bucket and count
                $bucketStats = [];
                $restrictedApps = [];
                foreach ($apps as $pkg => $bucket) {
                    $bucketStr = is_array($bucket) ? ($bucket['bucket'] ?? $bucket['state'] ?? 'UNKNOWN') : (string)$bucket;
                    $bucketStrLower = strtolower(trim($bucketStr));
                    $bucketStats[$bucketStrLower] = ($bucketStats[$bucketStrLower] ?? 0) + 1;
                    
                    // We collect non-active apps to display since active apps are too many
                    if ($bucketStrLower !== 'active') {
                        $restrictedApps[$pkg] = $bucketStr;
                    }
                }
                ?>
                <div class="doze-card">
                    <div class="doze-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="doze-ring-box <?= $stateClass ?> mr-3">
                                <i class="fas <?= $stateIcon ?>"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 font-weight-bold"><?= $stateLabel ?></h4>
                                <p class="mb-0 opacity-75 font-family-monospace" style="font-size: 12px;">Device ID: <?= esc($r['device_id']) ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-<?= $saver ? 'danger' : 'success' ?> px-3 py-2 font-weight-bold">
                                <?= $saver ? 'BATTERY SAVER ACTIVE' : 'STANDARD POWER' ?>
                            </span>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="doze-meta-box">
                                    <div class="doze-label"><i class="fas fa-brain mr-1"></i>Adaptive Battery</div>
                                    <div class="doze-val">
                                        <span class="badge badge-<?= !empty($r['adaptive_battery_enabled']) ? 'success' : 'secondary' ?>">
                                            <?= !empty($r['adaptive_battery_enabled']) ? 'Enabled' : 'Disabled' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="doze-meta-box">
                                    <div class="doze-label"><i class="fas fa-layer-group mr-1"></i>Standby Bucket</div>
                                    <div class="doze-val"><span class="badge badge-primary"><?= esc(strtoupper($r['device_standby_bucket'] ?? 'Active')) ?></span></div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="doze-meta-box">
                                    <div class="doze-label"><i class="fas fa-tools mr-1"></i>Next Maintenance</div>
                                    <div class="doze-val"><?= $r['next_maintenance_window'] ? format_timestamp_display((int)$r['next_maintenance_window']) : '—' ?></div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="doze-meta-box">
                                    <div class="doze-label"><i class="fas fa-history mr-1"></i>Last transition</div>
                                    <div class="doze-val"><?= $r['last_standby_transition'] ? format_timestamp_display((int)$r['last_standby_transition']) : '—' ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Bucket Statistics Summary -->
                        <h5 class="font-weight-bold text-secondary mb-3"><i class="fas fa-chart-pie mr-2 text-primary"></i>Standby State Summary (<?= count($apps) ?> total apps)</h5>
                        <div class="d-flex flex-wrap mb-4">
                            <?php 
                            $badgeMap = [
                                'active' => 'badge-success',
                                'working_set' => 'badge-success',
                                'frequent' => 'badge-warning',
                                'rare' => 'badge-warning',
                                'restricted' => 'badge-danger',
                            ];
                            foreach ($bucketStats as $bucketName => $count): 
                                $bClass = $badgeMap[$bucketName] ?? 'badge-secondary';
                                ?>
                                <div class="bucket-stat d-flex align-items-center">
                                    <span class="mr-2 font-weight-bold text-uppercase" style="font-size: 11px;"><?= esc($bucketName) ?>:</span>
                                    <span class="badge <?= $bClass ?> font-weight-bold"><?= $count ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Limited App Standby List to optimize DOM footprint -->
                        <h5 class="font-weight-bold text-secondary mb-3"><i class="fas fa-layer-group mr-2 text-primary"></i>App Standby Bucket Listing (Showing restricted/restricted-state apps: <?= count($restrictedApps) ?>)</h5>
                        <div class="row" style="max-height: 400px; overflow-y: auto;">
                            <?php if (empty($restrictedApps)): ?>
                                <div class="col-12"><span class="text-muted small">All applications are currently in ACTIVE standby state (no restrictions logged).</span></div>
                            <?php else: 
                                $limit = 60;
                                $i = 0;
                                foreach ($restrictedApps as $pkg => $bucket): 
                                    if ($i >= $limit) {
                                        $remaining = count($restrictedApps) - $limit;
                                        echo '<div class="col-12 text-center text-muted small mt-2">... and ' . $remaining . ' more restricted apps.</div>';
                                        break;
                                    }
                                    $bucketStrLower = strtolower($bucket);
                                    $bClass = 'badge-secondary';
                                    if (in_array($bucketStrLower, ['working_set'])) $bClass = 'badge-success';
                                    elseif (in_array($bucketStrLower, ['frequent', 'rare'])) $bClass = 'badge-warning';
                                    elseif ($bucketStrLower == 'restricted') $bClass = 'badge-danger';
                                    ?>
                                    <div class="col-md-4">
                                        <div class="app-doze-card d-flex justify-content-between align-items-center">
                                            <div class="text-truncate mr-2" style="max-width: 70%;">
                                                <strong class="small text-truncate d-block" title="<?= esc($pkg) ?>"><?= esc(basename(str_replace('.', '/', $pkg))) ?></strong>
                                                <code class="text-muted" style="font-size:9px;"><?= esc($pkg) ?></code>
                                            </div>
                                            <span class="badge <?= $bClass ?> px-2 py-1 small"><?= esc($bucket) ?></span>
                                        </div>
                                    </div>
                                <?php $i++; endforeach; endif; ?>
                        </div>

                        <div class="mt-4 pt-3 border-top text-right text-muted small">
                            <i class="fas fa-clock mr-1"></i> Last updated: <?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—' ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </section>
</div>
