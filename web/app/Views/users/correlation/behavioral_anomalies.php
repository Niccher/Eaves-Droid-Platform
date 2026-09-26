<?php
/** @var array $anomalies */

$catStyles = [
    'call'          => ['label' => 'Call Anomaly', 'icon' => 'fas fa-phone-slash', 'bg' => '#dc3545', 'fg' => '#ffffff'],
    'app_usage'     => ['label' => 'App Anomaly', 'icon' => 'fas fa-mobile-alt', 'bg' => '#ffc107', 'fg' => '#1f2d3d'],
    'location'      => ['label' => 'Location Anomaly', 'icon' => 'fas fa-map-marker-alt', 'bg' => '#fd7e14', 'fg' => '#ffffff'],
    'communication' => ['label' => 'Comm Anomaly', 'icon' => 'fas fa-comments', 'bg' => '#6f42c1', 'fg' => '#ffffff'],
    'other'         => ['label' => 'Anomaly', 'icon' => 'fas fa-exclamation-triangle', 'bg' => '#6c757d', 'fg' => '#ffffff'],
];

// Merge communication spikes from Python
foreach (($comm_spikes ?? []) as $cs) {
    $details = json_decode($cs['details'], true) ?: [];
    $anomalies[] = [
        'timestamp' => strtotime($cs['event_timestamp']) * 1000, // convert to ms
        'severity' => strtolower($cs['severity']) === 'high' ? 'danger' : 'warning',
        'type' => 'communication',
        'title' => $cs['algorithm'],
        'description' => $cs['anomaly'],
        'whitelist_category' => 'sms',
        'whitelist_identifier' => $details['contact'] ?? '',
    ];
}

// Re-sort anomalies by timestamp DESC
usort($anomalies, function($a, $b) {
    return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
});

// Summary counts computation
$typeCounts = ['call' => 0, 'app_usage' => 0, 'location' => 0, 'communication' => 0];
foreach ($anomalies as $a) {
    $t = $a['type'] ?? 'other';
    if (isset($typeCounts[$t])) {
        $typeCounts[$t]++;
    }
}
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
                        <li class="breadcrumb-item active">Behavioral Anomalies</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-2"></i><?= session()->getFlashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= session()->getFlashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                
                <!-- Left Column: Chronological Anomaly Feed (col-md-8) -->
                <div class="col-md-8">
                    <div class="card card-warning card-outline shadow-sm">
                        <div class="card-header border-0 pb-0">
                            <h3 class="card-title font-weight-bold text-dark">
                                <i class="fas fa-stream mr-2 text-warning"></i> Anomaly Feed
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-warning p-2" style="border-radius: 8px; font-weight: 600; font-size: 0.75rem;">
                                    <i class="fas fa-bell mr-1"></i> <?= count($anomalies) ?> Events Flagged
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            <?php if (empty($anomalies)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3 d-block"></i>
                                    <h5 class="text-muted">No anomalies detected</h5>
                                    <p class="text-muted mb-0">All audited device activities align with the established baseline timeline.</p>
                                </div>
                            <?php else: ?>
                                <div class="timeline p-2" id="anomaly-timeline">
                                    <?php
                                    $lastDate = '';
                                    foreach ($anomalies as $anomaly):
                                        $rawTs = (int)($anomaly['timestamp'] ?? 0);
                                        $ts    = $rawTs > 9999999999 ? (int)($rawTs / 1000) : $rawTs;
                                        $dateStr = date('D, j M Y', $ts);
                                        $timeStr = date('h:i:s A', $ts);
                                        $sev   = $anomaly['severity'] ?? 'warning';
                                        $type  = $anomaly['type']     ?? 'other';

                                        $style = $catStyles[$type] ?? $catStyles['other'];
                                        $icon = $style['icon'];

                                        if ($dateStr !== $lastDate):
                                            $lastDate = $dateStr;
                                    ?>
                                            <div class="time-label" data-date="<?= esc($dateStr) ?>">
                                                <span class="bg-dark text-white shadow-sm" style="border-radius: 6px; font-size: 0.8rem;"><?= esc($dateStr) ?></span>
                                            </div>
                                    <?php 
                                        endif; 
                                    ?>
                                        <div class="tl-ev" data-category="<?= esc($type) ?>">
                                            <!-- Contrast-matched circle icon -->
                                            <i class="<?= esc($icon) ?>" style="background-color: <?= $style['bg'] ?> !important; color: <?= $style['fg'] ?> !important; width: 32px; height: 32px; line-height: 32px; text-align: center; border-radius: 50%; font-size: 0.85rem; position: absolute; left: 17px; top: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.15);"></i>
                                            
                                            <div class="timeline-item shadow-none border" style="border-radius: 8px; margin-left: 60px; margin-bottom: 20px; background-color: #fcfcfc;">
                                                <span class="time text-muted"><i class="fas fa-clock mr-1"></i> <?= esc($timeStr) ?></span>
                                                
                                                <h3 class="timeline-header" style="border-bottom: 0; padding: 12px 15px 6px 15px; font-size: 0.95rem; font-weight: 600;">
                                                    <span class="badge mr-2" style="font-size: 0.65rem; background-color: <?= $style['bg'] ?>; color: <?= $style['fg'] ?>; border: 1px solid <?= $style['fg'] ?>30;"><?= esc($style['label']) ?></span>
                                                    <span class="badge badge-<?= $sev === 'danger' ? 'danger' : ($sev === 'medium' ? 'secondary' : 'warning') ?> mr-2" style="font-size: 0.65rem;">
                                                        <i class="fas fa-exclamation-circle mr-1"></i><?= ucfirst($sev) ?>
                                                    </span>
                                                    <?= esc($anomaly['title']) ?>
                                                </h3>
                                                
                                                <div class="timeline-body text-muted pt-0 pb-3" style="font-size: 0.88rem; line-height: 1.5; padding: 0 15px;">
                                                    <?= esc($anomaly['description']) ?>
                                                </div>
                                                
                                                <?php if (!empty($anomaly['whitelist_identifier'])): ?>
                                                    <div class="timeline-footer pt-2 pb-2 bg-light d-flex align-items-center justify-content-between border-top" style="padding: 0 15px; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                                                        <small class="text-muted"><i class="fas fa-fingerprint mr-1"></i>Target: <code><?= esc($anomaly['whitelist_identifier']) ?></code></small>
                                                        <form action="<?= base_url('behavioral-anomalies/whitelist') ?>" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to whitelist and dismiss this target from anomalies?');">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="category" value="<?= esc($anomaly['whitelist_category']) ?>">
                                                            <input type="hidden" name="identifier" value="<?= esc($anomaly['whitelist_identifier']) ?>">
                                                            <button type="submit" class="btn btn-xs btn-outline-success rounded shadow-sm" style="border-radius: 6px;">
                                                                <i class="fas fa-check-circle mr-1"></i>Whitelist Target
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div><i class="fas fa-clock bg-gray text-white" style="box-shadow: 0 1px 3px rgba(0,0,0,0.15); width: 32px; height: 32px; line-height: 32px; position: absolute; left: 17px; border-radius: 50%; text-align: center;"></i></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sidebar Guideline & Summary Widgets (col-md-4) -->
                <div class="col-md-4">
                    
                    <!-- Card 1: Sleep Baseline Routine -->
                    <div class="card card-outline card-warning shadow-sm mb-3">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold text-dark">
                                <i class="fas fa-bed text-warning mr-1"></i> Sleep Baseline Profile
                            </h3>
                        </div>
                        <div class="card-body py-3 px-3">
                            <p class="text-sm text-muted mb-3">
                                Behavioral anomalies are triggered when unusual system activities occur outside the normal active cycle:
                            </p>
                            <div class="callout callout-danger py-2 px-3 mb-2" style="border-left-width: 4px;">
                                <span class="text-xs font-weight-bold text-danger uppercase d-block"><i class="fas fa-moon mr-1"></i> Sleep Baseline</span>
                                <strong class="text-sm">11:00 PM – 5:00 AM</strong>
                            </div>
                            <div class="callout callout-success py-2 px-3 mb-0" style="border-left-width: 4px;">
                                <span class="text-xs font-weight-bold text-success uppercase d-block"><i class="fas fa-sun mr-1"></i> Active Normal Baseline</span>
                                <strong class="text-sm">5:00 AM – 11:00 PM</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Active Whitelists -->
                    <div class="card card-outline card-success shadow-sm mb-3">
                        <div class="card-header border-0">
                            <h3 class="card-title font-weight-bold text-dark">
                                <i class="fas fa-check-double text-success mr-1"></i> Active Whitelist
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($active_whitelists)): ?>
                                <div class="text-center py-3 text-muted text-xs">
                                    No active whitelisted targets.
                                </div>
                            <?php else: ?>
                                <ul class="list-group list-group-flush text-xs">
                                    <?php foreach ($active_whitelists as $wl): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                            <div style="max-width: 80%; overflow-x: auto;">
                                                <span class="badge badge-light border mr-1"><?= esc(ucfirst($wl['category'])) ?></span>
                                                <code><?= esc($wl['identifier']) ?></code>
                                            </div>
                                            <form action="<?= base_url('analysis/behavioral-anomalies/remove-whitelist') ?>" method="POST" class="m-0" onsubmit="return confirm('Remove this target from whitelist? It will be re-analyzed.');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $wl['id'] ?>">
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Remove from Whitelist" style="border-radius: 6px;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Whitelist Audit Trail -->
                    <div class="card card-outline card-info shadow-sm mb-3">
                        <div class="card-header border-0">
                            <h3 class="card-title font-weight-bold text-dark">
                                <i class="fas fa-history text-info mr-1"></i> Whitelist Audit Logs
                            </h3>
                        </div>
                        <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                            <?php if (empty($whitelist_logs)): ?>
                                <div class="text-center py-3 text-muted text-xs">
                                    No audit log entries.
                                </div>
                            <?php else: ?>
                                <ul class="list-group list-group-flush text-xs">
                                    <?php foreach ($whitelist_logs as $log): ?>
                                        <li class="list-group-item py-2 px-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <strong><?= esc($log['action_taken_by']) ?></strong>
                                                <span class="text-muted" style="font-size: .65rem;"><?= date('M j, H:i', strtotime($log['created_at'])) ?></span>
                                            </div>
                                            <div class="text-muted" style="line-height: 1.3;">
                                                Target: <code><?= esc($log['identifier']) ?></code> (<?= esc($log['category']) ?>)
                                                <br>
                                                Reason: <span class="font-italic"><?= esc($log['reason'] ?? 'None provided') ?></span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card 2: Anomaly Metrics Summary -->
                    <div class="card card-outline card-secondary shadow-sm mb-3">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold text-dark">
                                <i class="fas fa-chart-pie text-secondary mr-1"></i> Anomaly Classification
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush text-xs">
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                    <span><i class="fas fa-exclamation-triangle text-danger mr-2"></i>Total Anomalies</span>
                                    <span class="badge badge-pill badge-danger font-weight-bold" style="font-size:0.75rem;"><?= count($anomalies) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                    <span><i class="fas fa-phone-slash text-danger mr-2"></i>Late Night Calls</span>
                                    <span class="badge badge-pill badge-light border" style="font-size:0.75rem;"><?= $typeCounts['call'] ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                    <span><i class="fas fa-mobile-alt text-warning mr-2"></i>Late Night App Sessions</span>
                                    <span class="badge badge-pill badge-light border" style="font-size:0.75rem;"><?= $typeCounts['app_usage'] ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                    <span><i class="fas fa-map-marker-alt text-danger mr-2"></i>Late Night Movement</span>
                                    <span class="badge badge-pill badge-light border" style="font-size:0.75rem;"><?= $typeCounts['location'] ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Card 3: Whitelisting Guidelines -->
                    <div class="card card-outline card-info shadow-sm mb-3">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold text-dark">
                                <i class="fas fa-shield-alt text-info mr-1"></i> Whitelisting Rule Guide
                            </h3>
                        </div>
                        <div class="card-body py-3 px-3 text-xs text-muted" style="line-height: 1.6;">
                            <p class="mb-2">
                                If a flagged event is routine (e.g. night shifts, regular emergency contacts), you can choose to whitelist the target.
                            </p>
                            <ul class="pl-3 mb-0">
                                <li><strong>Calls:</strong> Whitelist by phone number or contact identifier.</li>
                                <li><strong>Apps:</strong> Whitelist by bundle package name.</li>
                                <li>Dismissed targets will no longer trigger flags in the anomaly timeline.</li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </section>
</div>
<?php include __DIR__ . '/../advanced/_adv_style.php'; ?>
<?php include __DIR__ . '/../advanced/_adv_delete_script.php'; ?>
