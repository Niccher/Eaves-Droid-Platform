<?php
// ─── Category display metadata ────────────────────────────────────────────────
$catMeta = [
    'sms'         => ['label' => 'SMS',          'icon' => 'fas fa-sms',             'color' => 'danger'],
    'contacts'    => ['label' => 'Contacts',      'icon' => 'fas fa-address-book',    'color' => 'success'],
    'call_logs'   => ['label' => 'Call Logs',     'icon' => 'fas fa-phone',           'color' => 'warning'],
    'locations'   => ['label' => 'Locations',     'icon' => 'fas fa-map-marker-alt',  'color' => 'primary'],
    'apps'        => ['label' => 'Apps',          'icon' => 'fas fa-th-large',        'color' => 'info'],
    'files'       => ['label' => 'Files',         'icon' => 'fas fa-folder',          'color' => 'secondary'],
    'activity'    => ['label' => 'Device Usage',  'icon' => 'fas fa-mobile-alt',      'color' => 'orange'],
    'device_info' => ['label' => 'Device Info',   'icon' => 'fas fa-microchip',       'color' => 'dark'],
];

// ─── Severity helpers ─────────────────────────────────────────────────────────
$sevColor = ['High' => 'danger', 'Medium' => 'warning', 'Low' => 'info'];
$sevIcon  = ['High' => 'fas fa-exclamation-circle', 'Medium' => 'fas fa-exclamation-triangle', 'Low' => 'fas fa-info-circle'];

// ─── Group results by category ────────────────────────────────────────────────
$grouped = [];
foreach (($results ?? []) as $row) {
    $cat = $row['category'] ?? 'other';
    $grouped[$cat][] = $row;
}

// Sort categories: worst first
uksort($grouped, function ($a, $b) use ($grouped) {
    $rank = ['High' => 2, 'Medium' => 1, 'Low' => 0];
    $getWorst = fn($rows) => max(array_map(fn($r) => $rank[$r['severity'] ?? 'Low'] ?? 0, $rows));
    return $getWorst($grouped[$b]) <=> $getWorst($grouped[$a]);
});

// ─── Threat level display colours (Bootstrap) ─────────────────────────────────
$threatColorMap = [
    'none'     => ['bg' => '#28a745', 'bs' => 'success'],
    'low'      => ['bg' => '#17a2b8', 'bs' => 'info'],
    'medium'   => ['bg' => '#ffc107', 'bs' => 'warning'],
    'high'     => ['bg' => '#dc3545', 'bs' => 'danger'],
    'critical' => ['bg' => '#343a40', 'bs' => 'dark'],
];

$td         = $threat_data ?? null;
$tdLevel    = $td['level']  ?? 'none';
$tdColor    = $threatColorMap[$tdLevel] ?? $threatColorMap['none'];

// ─── Scan history scope label ─────────────────────────────────────────────────
$scopeLabel = fn($s) => match($s) { 'incremental' => 'New data only', default => 'Full scan' };

// ─── Format elapsed time ──────────────────────────────────────────────────────
function fmtElapsed(?int $ms): string {
    if (!$ms) return '';
    if ($ms < 1000) return "{$ms}ms";
    $s = round($ms / 1000, 1);
    return $s < 60 ? "{$s}s" : round($s / 60, 1) . 'm';
}

// ─── Relative date ────────────────────────────────────────────────────────────
function relDate(?string $dt): string {
    if (!$dt) return 'Unknown';
    $ts   = strtotime($dt);
    return date('M jS D Y h:i:s A', $ts);
}
// ─── Format embedded dates in descriptions ────────────────────────────────────
function formatEmbeddedDates(string $text): string {
    // Match YYYY-MM-DD HH:MM:SS
    $text = preg_replace_callback('/\b(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})\b/', function($matches) {
        $ts = strtotime($matches[0]);
        return $ts ? date('M jS D Y h:i:s A', $ts) : $matches[0];
    }, $text);

    // Match YYYY-MM-DD
    $text = preg_replace_callback('/\b(\d{4})-(\d{2})-(\d{2})\b/', function($matches) {
        $ts = strtotime($matches[0]);
        return $ts ? date('M jS D Y', $ts) : $matches[0];
    }, $text);

    // Match HH:MM
    $text = preg_replace_callback('/\b(\d{2}):(\d{2})\b/', function($matches) {
        $ts = strtotime(date('Y-m-d ') . $matches[0]);
        return $ts ? date('h:i A', $ts) : $matches[0];
    }, $text);

    return $text;
}
?>

<style>
/* ───────────────────────── Anomaly Scanner Styles ─────────────────────────── */
.anomaly-wrapper          { padding: 20px 0; }

/* ── Health Card ── */
.health-card              { border-radius: 14px; overflow: hidden; position: relative; }
.health-card .hc-band     { height: 6px; width: 100%; }
.health-card .hc-body     { padding: 24px 28px 20px; }
.health-score-ring        { width: 80px; height: 80px; flex-shrink: 0; position: relative; }
.health-score-ring svg    { transform: rotate(-90deg); }
.health-score-ring .score-text {
    position: absolute; inset: 0; display: flex; flex-direction: column;
    align-items: center; justify-content: center; font-weight: 700;
    font-size: 1.25rem; line-height: 1;
}
.health-score-ring .score-sub { font-size: .65rem; font-weight: 500; opacity: .7; margin-top: 2px; }
.health-label             { font-size: 1.3rem; font-weight: 700; }
.health-sub               { font-size: .82rem; opacity: .7; margin-top: 2px; }
.threat-bar-wrap          { margin-top: 12px; height: 8px; background: rgba(0,0,0,.08); border-radius: 4px; overflow: hidden; }
.threat-bar               { height: 100%; border-radius: 4px; transition: width .8s ease; }

/* ── Rescan dropdown ── */
.rescan-btn-group         { position: relative; }
.rescan-dropdown          { display: none; position: absolute; right: 0; top: calc(100% + 6px);
                            background: #fff; border: 1px solid #dee2e6; border-radius: 10px;
                            box-shadow: 0 8px 24px rgba(0,0,0,.12); min-width: 200px; z-index: 50; overflow: hidden; }
.rescan-dropdown.open     { display: block; }
.rescan-dropdown a        { display: flex; align-items: center; gap: 10px; padding: 12px 16px;
                            font-size: .85rem; color: #343a40; text-decoration: none; transition: background .15s; }
.rescan-dropdown a:hover  { background: #f8f9fa; }
.rescan-dropdown a i      { width: 18px; text-align: center; opacity: .7; }
.rescan-divider           { height: 1px; background: #f0f0f0; }

/* ── Category pills ── */
.cat-pills                { display: flex; flex-wrap: wrap; gap: 8px; margin: 18px 0; }
.cat-pill                 { display: inline-flex; align-items: center; gap: 7px;
                            padding: 6px 14px 6px 10px; border-radius: 50px;
                            font-size: .78rem; font-weight: 600; cursor: pointer;
                            transition: transform .15s, box-shadow .15s;
                            text-decoration: none; border: none; background: none; }
.cat-pill:hover           { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.12); }
.cat-pill .pill-dot       { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

/* ── Finding cards ── */
.findings-section         { margin-bottom: 28px; }
.finding-card             { border-bottom: 1px solid #eee; padding: 16px 20px;
                            background: #fff; transition: background .15s; }
.finding-card:last-child  { border-bottom: none; }
.finding-card:hover       { background: #fafafa; }
.finding-sev-dot          { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
.finding-title            { font-weight: 600; font-size: .88rem; margin-bottom: 4px; }
.finding-body             { font-size: .82rem; color: #6c757d; line-height: 1.5; }
.finding-tech             { margin-top: 10px; border-top: 1px dashed #dee2e6; padding-top: 10px; }
.finding-tech pre         { font-size: .72rem; background: #f8f9fa; border-radius: 6px;
                            padding: 10px; max-height: 120px; overflow-y: auto; margin: 0; }
.tech-toggle              { font-size: .75rem; color: #6c757d; cursor: pointer; user-select: none;
                            display: inline-flex; align-items: center; gap: 4px;
                            border: none; background: none; padding: 0; margin-top: 8px; }
.tech-toggle:hover        { color: #495057; }

/* ── Empty state ── */
.anomaly-empty            { padding: 60px 20px; text-align: center; }
.anomaly-empty .ae-icon   { width: 90px; height: 90px; border-radius: 50%;
                            background: linear-gradient(135deg,#667eea,#764ba2);
                            display: flex; align-items: center; justify-content: center;
                            margin: 0 auto 24px; box-shadow: 0 8px 30px rgba(102,126,234,.35); }
.anomaly-empty .ae-icon i { font-size: 2rem; color: #fff; }
.anomaly-empty h4         { font-size: 1.4rem; font-weight: 700; color: #1a1a2e; margin-bottom: 10px; }
.anomaly-empty p          { color: #6c757d; max-width: 440px; margin: 0 auto 28px; line-height: 1.6; }
.scan-options             { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.scan-btn                 { display: inline-flex; align-items: center; gap: 8px;
                            padding: 13px 24px; border-radius: 10px; font-weight: 600;
                            font-size: .9rem; border: none; cursor: pointer; transition: all .2s;
                            text-decoration: none; }
.scan-btn-primary         { background: linear-gradient(135deg,#667eea,#764ba2); color: #fff;
                            box-shadow: 0 6px 20px rgba(102,126,234,.4); }
.scan-btn-primary:hover   { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(102,126,234,.5); color:#fff; }
.scan-btn-secondary       { background: #fff; color: #495057; border: 1.5px solid #dee2e6; }
.scan-btn-secondary:hover { border-color: #adb5bd; transform: translateY(-1px); }

/* ── Scanning progress ── */
.scanning-card            { border-radius: 14px; border: 1.5px solid #dee2e6; overflow: hidden; }
.scanning-header          { background: linear-gradient(135deg,#667eea,#764ba2);
                            color: #fff; padding: 20px 24px; display: flex; align-items: center; gap: 14px; }
.scanning-header .spin-icon { animation: spin 1.2s linear infinite; font-size: 1.4rem; }
@keyframes spin { to { transform: rotate(360deg); } }
.scanning-body            { padding: 22px 24px; background: #fff; }
.scan-progress-bar-wrap   { height: 10px; background: #f0f0f0; border-radius: 5px; overflow: hidden; margin: 14px 0; }
.scan-progress-bar        { height: 100%; border-radius: 5px;
                            background: linear-gradient(90deg,#667eea,#764ba2);
                            transition: width .8s ease; }
.scan-step-label          { font-size: .82rem; color: #6c757d; display: flex; align-items: center; gap: 6px; }
.scan-step-label::before  { content: ''; display: inline-block; width: 6px; height: 6px;
                            border-radius: 50%; background: #667eea; animation: pulse 1.2s infinite; }
@keyframes pulse          { 0%,100%{opacity:1}50%{opacity:.3} }
.scan-cats-live           { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 14px; }
.scan-cat-chip            { padding: 4px 10px; border-radius: 20px; font-size: .74rem;
                            font-weight: 600; background: #f0f0f5; color: #6c757d; transition: all .3s; }
.scan-cat-chip.active     { background: rgba(102,126,234,.15); color: #5a67d8; }
.scan-cat-chip.done       { background: rgba(40,167,69,.1); color: #28a745; }

/* ── History ── */
.history-card             { border-radius: 12px; border: 1px solid #eee; overflow: hidden; }
.history-card .hist-head  { padding: 14px 20px; background: #f8f9fa; border-bottom: 1px solid #eee;
                            display: flex; align-items: center; justify-content: space-between; }
.hist-item                { display: flex; align-items: center; gap: 14px; padding: 13px 20px;
                            border-bottom: 1px solid #f5f5f5; transition: background .15s; cursor: pointer; }
.hist-item:last-child     { border-bottom: none; }
.hist-item:hover          { background: #fafafa; }
.hist-dot                 { width: 38px; height: 38px; border-radius: 50%; display: flex;
                            align-items: center; justify-content: center; flex-shrink: 0; font-size: .85rem; }
.hist-score               { font-weight: 700; font-size: .95rem; }
.hist-meta                { font-size: .75rem; color: #6c757d; }
.hist-badge               { font-size: .7rem; padding: 2px 8px; border-radius: 10px; font-weight: 600; }

/* ── Sev summary counts ── */
.sev-counts               { display: flex; gap: 16px; flex-wrap: wrap; }
.sev-count-item           { display: flex; align-items: center; gap: 6px; font-size: .82rem; font-weight: 600; }
.sev-count-dot            { width: 10px; height: 10px; border-radius: 50%; }

/* ── Responsive ── */
@media (max-width: 768px) {
    .health-card .hc-body { padding: 16px; }
    .health-label         { font-size: 1.1rem; }
    .scan-options         { flex-direction: column; align-items: stretch; }
    .scan-btn             { justify-content: center; }
}
</style>

<!-- ════════════════════════════════════════════════════════════════════════════
     CONTENT WRAPPER
════════════════════════════════════════════════════════════════════════════ -->
<div class="content-wrapper">

    <!-- Page header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="h2 mb-0">
                        <i class="fas fa-bug mr-2 text-primary"></i>Anomaly Scanner
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home mr-1"></i>Home</a></li>
                        <li class="breadcrumb-item">Intelligence</li>
                        <li class="breadcrumb-item active">Anomaly Scanner</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content anomaly-wrapper">
        <div class="container-fluid">

            <!-- ════════════════ ALERT AREA ════════════════ -->
            <div id="anom-alert-area"></div>

            <!-- Consistent Security Callout -->
            <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
                <h5 class="font-weight-bold text-info"><i class="fas fa-shield-alt mr-2"></i>Heuristic Behavioral Intelligence &amp; Anomaly Auditing</h5>
                <p class="text-secondary mb-2" style="font-size:14px;">Automated statistical scanning of device activity. Auditing call metrics, message patterns, background location telemetry, app permissions, and file system spikes enables the detection of spyware, unauthorized usage, or data leakage.</p>
                <div class="row" style="font-size:12px;">
                    <div class="col-md-4 border-right">
                        <b class="d-block mb-1">Behavioral Spikes:</b>
                        <ul class="pl-3 mb-0 text-muted">
                            <li>Detects burst call sequences, off-hours messaging, or anomalous data extraction spikes.</li>
                        </ul>
                    </div>
                    <div class="col-md-4 border-right pl-md-3">
                        <b class="d-block mb-1">Identity &amp; Geofencing:</b>
                        <ul class="pl-3 mb-0 text-muted">
                            <li>Flags duplicate records, impossible velocities, or boundary violations.</li>
                        </ul>
                    </div>
                    <div class="col-md-4 pl-md-3">
                        <b class="d-block mb-1">Installed Footprint:</b>
                        <ul class="pl-3 mb-0 text-muted">
                            <li>Audits background permissions, privilege changes, and newly added packages.</li>
                        </ul>
                    </div>
                </div>
            </div>



            <?php if ($is_scanning): ?>
            <!-- ════════════════════════════════════════════
                 STATE: SCANNING IN PROGRESS
                 (also shows last completed results below)
            ════════════════════════════════════════════ -->
            <div class="card card-outline card-primary shadow-sm mb-4">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title font-weight-bold text-primary mb-0">
                        <i class="fas fa-circle-notch fa-spin mr-2"></i>Scan Running…
                    </h3>
                    <div class="card-tools ml-auto">
                        <span class="badge badge-primary font-weight-bold p-2" id="scan-pct-label">0%</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="scan-step-label text-muted" id="scan-current-alg">Starting checks…</span>
                    </div>
                    <div class="progress progress-sm rounded-pill my-3" style="height: 10px;">
                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" id="scan-progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small style="font-size:.75rem;color:#adb5bd;" id="scan-alg-counter">
                            0 of 0 checks complete
                        </small>
                    </div>
                    <div class="scan-cats-live mt-3" id="scan-cats-live">
                        <?php foreach ($catMeta as $ck => $cm): ?>
                        <span class="badge badge-light border mr-1 py-2 px-3" id="scat-<?= $ck ?>"><?= $cm['label'] ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php if ($has_report): ?>
            <div class="alert alert-light border" style="font-size:.82rem;border-radius:10px;">
                <i class="fas fa-clock mr-2 text-muted"></i>
                Showing results from your <strong>previous scan</strong> while the new one runs.
                This page will refresh automatically when complete.
            </div>
            <?php endif; ?>

            <?php elseif (!$has_report): ?>
            <!-- ════════════════════════════════════════════
                 STATE: EMPTY — NO SCAN YET
            ════════════════════════════════════════════ -->
            <div class="card card-outline card-secondary shadow-sm text-center py-5 px-4 mb-4">
                <div class="ae-icon mb-4" style="width: 90px; height: 90px; border-radius: 50%; background: rgba(108, 117, 125, 0.1); color: #6c757d; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="fas fa-shield-virus fa-3x"></i>
                </div>
                <h4 class="font-weight-bold">No Anomaly Scan Run Yet</h4>
                <p class="text-muted mx-auto" style="max-width: 480px; line-height: 1.6;">
                    The system will automatically check your device data across all
                    categories — SMS, calls, locations, apps, files and more — using
                    multiple detection algorithms matched to your plan.
                </p>
                <div class="scan-options mt-4">
                    <button id="btn-full-scan" class="btn btn-primary btn-lg" onclick="startScan('full')" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-play-circle mr-2"></i> Run Full Scan
                    </button>
                </div>
                <p class="text-muted mt-3 mb-0" style="font-size:.75rem;">
                    <i class="fas fa-lock mr-1"></i>
                    All analysis runs locally — no data leaves your server.
                </p>
            </div>
            <?php endif; ?>

            <?php if ($has_report && $td): ?>
            <!-- ════════════════════════════════════════════
                 STATE: RESULTS — HEALTH CARD
            ════════════════════════════════════════════ -->
            <div class="card card-outline card-<?= $tdColor['bs'] ?> shadow-sm mb-3">
                <div class="card-header d-flex align-items-center py-3">
                    <h3 class="card-title font-weight-bold text-<?= $tdColor['bs'] ?> d-flex align-items-center mb-0">
                        <i class="fas <?= $td['icon'] ?> mr-2"></i><?= $td['label'] ?>
                    </h3>
                    <div class="card-tools ml-auto">
                        <!-- Right: re-scan buttons -->
                        <?php if (!$is_scanning): ?>
                        <div class="rescan-btn-group" style="flex-shrink:0;">
                            <button class="btn btn-outline-info btn-sm mr-2" data-toggle="modal" data-target="#schedulerModal" style="border-radius:8px;font-size:.82rem;">
                                <i class="fas fa-calendar-alt mr-1"></i> Schedules
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" id="rescan-toggle-btn"
                                    onclick="toggleRescanMenu(event)" style="border-radius:8px;font-size:.82rem;">
                                <i class="fas fa-redo-alt mr-1"></i> Re-scan <i class="fas fa-caret-down ml-1"></i>
                            </button>
                            <div class="rescan-dropdown" id="rescan-dropdown">
                                <a href="#" onclick="startScan('full');closeRescanMenu();return false;">
                                    <i class="fas fa-database"></i>
                                    <div>
                                        <div style="font-weight:600;">Full Scan</div>
                                        <div style="font-size:.73rem;color:#adb5bd;">Analyse all device data from scratch</div>
                                    </div>
                                </a>
                                <div class="rescan-divider"></div>
                                <a href="#" onclick="startScan('incremental');closeRescanMenu();return false;">
                                    <i class="fas fa-bolt"></i>
                                    <div>
                                        <div style="font-weight:600;">Scan New Data</div>
                                        <div style="font-size:.73rem;color:#adb5bd;">Only data added since last scan</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-auto text-center mb-3 mb-md-0">
                            <?php
                            $score   = $td['score'];
                            $circum  = 2 * M_PI * 30; // r=30
                            $dash    = $circum;
                            $offset  = $circum - ($score / 100 * $circum);
                            ?>
                            <div class="health-score-ring mx-auto">
                                <svg width="80" height="80" viewBox="0 0 80 80">
                                    <circle cx="40" cy="40" r="30" fill="none" stroke="#f0f0f0" stroke-width="8"/>
                                    <circle cx="40" cy="40" r="30" fill="none"
                                            stroke="<?= $tdColor['bg'] ?>" stroke-width="8"
                                            stroke-dasharray="<?= $circum ?>"
                                            stroke-dashoffset="<?= $offset ?>"
                                            stroke-linecap="round"/>
                                </svg>
                                <div class="score-text" style="color:<?= $tdColor['bg'] ?>">
                                    <?= $score ?>
                                    <span class="score-sub">/ 100</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="health-sub text-muted" style="font-size: .85rem;">
                                <strong>Scan completed:</strong> <?= date('M jS D Y h:i:s A', strtotime($job['completed_at'] ?? 'now')) ?><br>
                                <strong>Setup:</strong> <?= count((array)json_decode($job['algorithms'] ?? '[]', true)) ?> algorithms active &nbsp;·&nbsp; <?= $scopeLabel($scope) ?>
                                <?php if (!empty($job['timing_ms'])): ?>
                                &nbsp;·&nbsp; Processed in <?= fmtElapsed((int)$job['timing_ms']) ?>
                                <?php endif; ?>
                            </div>
                            <!-- Severity count row -->
                            <?php if ($td['total'] > 0): ?>
                            <div class="sev-counts mt-2">
                                <?php if ($td['highCount'] > 0): ?>
                                <span class="badge badge-danger px-2 py-1 mr-1">
                                    <i class="fas fa-exclamation-circle mr-1"></i><?= $td['highCount'] ?> High
                                </span>
                                <?php endif; ?>
                                <?php if ($td['mediumCount'] > 0): ?>
                                <span class="badge badge-warning px-2 py-1 mr-1 text-dark">
                                    <i class="fas fa-exclamation-triangle mr-1"></i><?= $td['mediumCount'] ?> Medium
                                </span>
                                <?php endif; ?>
                                <?php if ($td['lowCount'] > 0): ?>
                                <span class="badge badge-info px-2 py-1">
                                    <i class="fas fa-info-circle mr-1"></i><?= $td['lowCount'] ?> Low
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="threat-bar-wrap">
                        <div class="threat-bar" style="width:<?= $score ?>%;background:<?= $tdColor['bg'] ?>;"></div>
                    </div>
                </div>
            </div>

            <!-- ── Category pills ── -->
            <?php if (!empty($grouped)): ?>
            <div class="cat-pills">
                <?php foreach ($grouped as $cat => $catRows):
                    $cm   = $catMeta[$cat] ?? ['label' => ucfirst(str_replace('_', ' ', $cat)), 'icon' => 'fas fa-circle', 'color' => 'secondary'];
                    $wst  = $td['categories'][$cat]['worst'] ?? 'Low';
                    $wstC = ['High' => '#dc3545', 'Medium' => '#ffc107', 'Low' => '#17a2b8'][$wst] ?? '#adb5bd';
                    $cnt  = count($catRows);
                ?>
                <a href="#cat-<?= $cat ?>" class="cat-pill"
                   style="background:rgba(<?= $wst === 'High' ? '220,53,69' : ($wst === 'Medium' ? '255,193,7' : '23,162,184') ?>,.1);color:<?= $wstC ?>">
                    <span class="pill-dot" style="background:<?= $wstC ?>;"></span>
                    <i class="<?= $cm['icon'] ?>"></i>
                    <?= $cm['label'] ?>
                    <span style="font-size:.7rem;opacity:.7;margin-left:2px;"><?= $cnt ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- ════════════════ FINDING CARDS ════════════════ -->
            <?php if (empty($grouped)): ?>
            <div class="card border-0 shadow-sm text-center p-4 mb-4" style="border-radius:14px;">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5 class="font-weight-bold">All Clear</h5>
                <p class="text-muted mb-0">No anomalies were detected in this scan. Your device data looks normal.</p>
            </div>
            <?php else: ?>
            <?php foreach ($grouped as $cat => $catRows):
                $cm  = $catMeta[$cat] ?? ['label' => ucfirst(str_replace('_', ' ', $cat)), 'icon' => 'fas fa-circle', 'color' => 'secondary'];
                $wst = $td['categories'][$cat]['worst'] ?? 'Low';
                $wstC = ['High' => 'danger', 'Medium' => 'warning', 'Low' => 'info'][$wst] ?? 'secondary';

                // Group findings by severity
                $highRows = [];
                $lowRows  = [];
                foreach ($catRows as $r) {
                    if (($r['severity'] ?? 'Low') === 'High') {
                        $highRows[] = $r;
                    } else {
                        $lowRows[] = $r;
                    }
                }
                
                $hasHigh  = !empty($highRows);
                $hasLow   = !empty($lowRows);
                $colClass = ($hasHigh && $hasLow) ? 'col-md-6' : 'col-12';
            ?>
            <div class="card card-outline card-<?= $wstC ?> shadow-sm mb-4" id="cat-<?= $cat ?>">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title font-weight-bold text-<?= $wstC ?> mb-0">
                        <i class="<?= $cm['icon'] ?> mr-2"></i><?= $cm['label'] ?> Analysis
                    </h3>
                    <div class="card-tools ml-auto">
                        <?php $catH=$td['categories'][$cat]['High']??0; $catM=$td['categories'][$cat]['Medium']??0; $catL=$td['categories'][$cat]['Low']??0; ?>
                        <?php if($catH>0):?><span class="badge badge-danger mr-1"><?=$catH?> High</span><?php endif;?>
                        <?php if($catM>0):?><span class="badge badge-warning text-dark mr-1"><?=$catM?> Medium</span><?php endif;?>
                        <?php if($catL>0):?><span class="badge badge-info mr-1"><?=$catL?> Low</span><?php endif;?>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row no-gutters">
                        
                        <!-- HIGH SEVERITY COLUMN -->
                        <?php if ($hasHigh): ?>
                        <div class="<?= $colClass ?> <?= ($hasHigh && $hasLow) ? 'border-right' : '' ?>">
                            <div class="bg-light px-3 py-2 border-bottom font-weight-bold text-danger" style="font-size: .82rem;">
                                <i class="fas fa-exclamation-circle mr-1"></i> High Risk Findings
                            </div>
                            <?php
                            $highGroups = [];
                            foreach ($highRows as $r) {
                                $highGroups[$r['algorithm'] ?? 'unknown'][] = $r;
                            }
                            foreach ($highGroups as $algKey => $findings):
                                $fCount  = count($findings);
                                $fFirst  = $findings[0];
                                $fSev    = 'High';
                                $fSevC   = 'danger';
                                $fSevDot = '#dc3545';
                                $tblId   = 'ctbl-'.preg_replace('/[^a-z0-9]/i','-',$cat.'-'.$algKey).'-high';
                                $aLow    = strtolower($fFirst['anomaly'] ?? '');
                                $hasTs   = !empty($fFirst['event_timestamp']);
                                $hasSc   = !empty($fFirst['score']);
                            ?>
                            <?php if ($fCount > 1): ?>
                            <div class="finding-card">
                                <div class="d-flex align-items-start" style="gap:12px;">
                                    <div class="finding-sev-dot" style="background:<?=$fSevDot?>;margin-top:5px;"></div>
                                    <div style="flex:1;">
                                        <div class="d-flex align-items-center mb-1" style="gap:8px;flex-wrap:wrap;">
                                            <span class="badge badge-<?=$fSevC?>" style="font-size:.68rem;"><?=strtoupper($fSev)?></span>
                                            <span class="badge badge-light" style="font-size:.68rem;border:1px solid #dee2e6;"><?=$fCount?> items</span>
                                        </div>
                                        <div class="finding-title"><?=esc(match(true) {
                                            str_contains($aLow,'duplicate phone') => "{$fCount} duplicate phone numbers detected",
                                            str_contains($aLow,'duplicate')       => "{$fCount} duplicate entries detected",
                                            str_contains($aLow,'night')           => "{$fCount} unusual night-time events detected",
                                            str_contains($aLow,'spike')           => "{$fCount} activity spikes detected",
                                            str_contains($aLow,'geofence')        => "{$fCount} geofence boundary violations detected",
                                            str_contains($aLow,'speed')           => "{$fCount} impossible travel events detected",
                                            str_contains($aLow,'permission')      => "{$fCount} over-privileged apps detected",
                                            str_contains($aLow,'suspicious')      => "{$fCount} suspicious items detected",
                                            str_contains($aLow,'phish')           => "{$fCount} potential phishing messages detected",
                                            str_contains($aLow,'burst')           => "{$fCount} short-call burst events detected",
                                            str_contains($aLow,'outlier')||str_contains($aLow,'anomal') => "{$fCount} anomalous events detected",
                                            default                               => "{$fCount} findings from this check",
                                        })?></div>
                                        <div class="finding-body" style="margin-top:3px;">Tap to expand and view each individual entry.</div>
                                        <button class="tech-toggle mt-2" id="<?=$tblId?>-btn" onclick="toggleCondensed('<?=$tblId?>')">
                                            <i class="fas fa-list fa-xs"></i>
                                            <span id="<?=$tblId?>-btn-label">View all <?=$fCount?> entries</span>
                                        </button>
                                        <div id="<?=$tblId?>" style="display:none;margin-top:12px;">
                                            <table class="table table-bordered table-hover table-valign-middle" style="font-size:.79rem;margin-bottom:6px;">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width:36px;padding:8px 10px;">#</th>
                                                        <th style="padding:8px 10px;">Finding</th>
                                                        <?php if($hasTs):?><th style="width:110px;padding:8px 10px;">Time</th><?php endif;?>
                                                        <?php if($hasSc):?><th style="width:70px;padding:8px 10px;text-align:right;">Score</th><?php endif;?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach($findings as $fi=>$frow):?>
                                                    <tr class="cnd-row" data-grp="<?=$tblId?>" data-idx="<?=$fi?>"<?=$fi>=50?' style="display:none;"':''?>>
                                                        <td style="color:#adb5bd;padding:7px 10px;vertical-align:top;"><?=$fi+1?></td>
                                                        <td style="padding:7px 10px;vertical-align:top;word-break:break-word;"><?=esc(formatEmbeddedDates($frow['anomaly']??''))?></td>
                                                        <?php if($hasTs):?>
                                                        <td style="color:#adb5bd;padding:7px 10px;vertical-align:top;white-space:nowrap;font-size:.73rem;"><?=!empty($frow['event_timestamp'])?date('M jS D Y h:i:s A',strtotime($frow['event_timestamp'])):'—'?></td>
                                                        <?php endif;?>
                                                        <?php if($hasSc):?>
                                                        <td style="text-align:right;padding:7px 10px;vertical-align:top;color:#6c757d;"><?=!empty($frow['score'])?round((float)$frow['score'],3):'—'?></td>
                                                        <?php endif;?>
                                                    </tr>
                                                <?php endforeach;?>
                                                </tbody>
                                            </table>
                                            <?php if($fCount>50):?>
                                            <div style="font-size:.75rem;color:#6c757d;display:flex;align-items:center;gap:10px;">
                                                <span id="<?=$tblId?>-info">Showing 1–50 of <?=$fCount?></span>
                                                <button class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;padding:2px 10px;border-radius:6px;" id="<?=$tblId?>-prev" onclick="pageCondensed('<?=$tblId?>',<?=$fCount?>,'prev')" disabled>‹ Prev</button>
                                                <button class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;padding:2px 10px;border-radius:6px;" id="<?=$tblId?>-next" onclick="pageCondensed('<?=$tblId?>',<?=$fCount?>,'next')">Next ›</button>
                                            </div>
                                            <?php endif;?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <?php foreach($findings as $fi=>$row):
                                $techId='tech-'.preg_replace('/[^a-z0-9]/i','-',$cat.'-'.$algKey).'-high-'.$fi;
                                $details=!empty($row['details'])?(is_string($row['details'])?@json_decode($row['details'],true):$row['details']):null;
                            ?>
                            <div class="finding-card">
                                <div class="d-flex align-items-start" style="gap:12px;">
                                    <div class="finding-sev-dot" style="background:<?=$fSevDot?>;margin-top:5px;"></div>
                                    <div style="flex:1;">
                                        <div class="d-flex align-items-center mb-1" style="gap:8px;flex-wrap:wrap;">
                                            <span class="badge badge-<?=$fSevC?>" style="font-size:.68rem;"><?=strtoupper($fSev)?></span>
                                            <?php if(!empty($row['event_timestamp'])):?>
                                            <span style="font-size:.73rem;color:#adb5bd;"><i class="far fa-clock mr-1"></i><?=date('M jS D Y h:i:s A',strtotime($row['event_timestamp']))?></span>
                                            <?php endif;?>
                                        </div>
                                        <div class="finding-title"><?=esc(formatEmbeddedDates($row['anomaly']??'Anomaly detected'))?></div>
                                        <?php if(!empty($row['score'])):?>
                                        <div class="finding-body">Anomaly confidence score: <strong><?=round((float)$row['score'],3)?></strong></div>
                                        <?php endif;?>
                                        <?php if(($details&&!empty($details))||!empty($row['algorithm'])):?>
                                        <button class="tech-toggle mt-2" onclick="toggleTech('<?=$techId?>')">
                                            <i class="fas fa-code fa-xs"></i><span id="<?=$techId?>-label">Technical details</span>
                                        </button>
                                        <div class="finding-tech" id="<?=$techId?>" style="display:none;">
                                            <?php if(!empty($row['algorithm'])):?>
                                            <div style="font-size:.73rem;color:#6c757d;margin-bottom:6px;"><i class="fas fa-cog mr-1"></i> Detection method: <strong><?=esc($row['algorithm'])?></strong></div>
                                            <?php endif;?>
                                            <?php if($details):?>
                                            <pre><?=esc(json_encode($details,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES))?></pre>
                                            <?php endif;?>
                                        </div>
                                        <?php endif;?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- MEDIUM/LOW SEVERITY COLUMN -->
                        <?php if ($hasLow): ?>
                        <div class="<?= $colClass ?>">
                            <div class="bg-light px-3 py-2 border-bottom font-weight-bold text-warning" style="font-size: .82rem;">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Medium &amp; Low Risk Findings
                            </div>
                            <?php
                            $lowGroups = [];
                            foreach ($lowRows as $r) {
                                $lowGroups[$r['algorithm'] ?? 'unknown'][] = $r;
                            }
                            foreach ($lowGroups as $algKey => $findings):
                                $fCount  = count($findings);
                                $fFirst  = $findings[0];
                                $fSev    = $fFirst['severity'] ?? 'Low';
                                $fSevC   = $sevColor[$fSev] ?? 'secondary';
                                $fSevDot = ['Medium'=>'#ffc107','Low'=>'#17a2b8'][$fSev] ?? '#adb5bd';
                                $tblId   = 'ctbl-'.preg_replace('/[^a-z0-9]/i','-',$cat.'-'.$algKey).'-low';
                                $aLow    = strtolower($fFirst['anomaly'] ?? '');
                                $hasTs   = !empty($fFirst['event_timestamp']);
                                $hasSc   = !empty($fFirst['score']);
                            ?>
                            <?php if ($fCount > 1): ?>
                            <div class="finding-card">
                                <div class="d-flex align-items-start" style="gap:12px;">
                                    <div class="finding-sev-dot" style="background:<?=$fSevDot?>;margin-top:5px;"></div>
                                    <div style="flex:1;">
                                        <div class="d-flex align-items-center mb-1" style="gap:8px;flex-wrap:wrap;">
                                            <span class="badge badge-<?=$fSevC?>" style="font-size:.68rem;"><?=strtoupper($fSev)?></span>
                                            <span class="badge badge-light" style="font-size:.68rem;border:1px solid #dee2e6;"><?=$fCount?> items</span>
                                        </div>
                                        <div class="finding-title"><?=esc(match(true) {
                                            str_contains($aLow,'duplicate phone') => "{$fCount} duplicate phone numbers detected",
                                            str_contains($aLow,'duplicate')       => "{$fCount} duplicate entries detected",
                                            str_contains($aLow,'night')           => "{$fCount} unusual night-time events detected",
                                            str_contains($aLow,'spike')           => "{$fCount} activity spikes detected",
                                            str_contains($aLow,'geofence')        => "{$fCount} geofence boundary violations detected",
                                            str_contains($aLow,'speed')           => "{$fCount} impossible travel events detected",
                                            str_contains($aLow,'permission')      => "{$fCount} over-privileged apps detected",
                                            str_contains($aLow,'suspicious')      => "{$fCount} suspicious items detected",
                                            str_contains($aLow,'phish')           => "{$fCount} potential phishing messages detected",
                                            str_contains($aLow,'burst')           => "{$fCount} short-call burst events detected",
                                            str_contains($aLow,'outlier')||str_contains($aLow,'anomal') => "{$fCount} anomalous events detected",
                                            default                               => "{$fCount} findings from this check",
                                        })?></div>
                                        <div class="finding-body" style="margin-top:3px;">Tap to expand and view each individual entry.</div>
                                        <button class="tech-toggle mt-2" id="<?=$tblId?>-btn" onclick="toggleCondensed('<?=$tblId?>')">
                                            <i class="fas fa-list fa-xs"></i>
                                            <span id="<?=$tblId?>-btn-label">View all <?=$fCount?> entries</span>
                                        </button>
                                        <div id="<?=$tblId?>" style="display:none;margin-top:12px;">
                                            <table class="table table-bordered table-hover table-valign-middle" style="font-size:.79rem;margin-bottom:6px;">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width:36px;padding:8px 10px;">#</th>
                                                        <th style="padding:8px 10px;">Finding</th>
                                                        <?php if($hasTs):?><th style="width:110px;padding:8px 10px;">Time</th><?php endif;?>
                                                        <?php if($hasSc):?><th style="width:70px;padding:8px 10px;text-align:right;">Score</th><?php endif;?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach($findings as $fi=>$frow):?>
                                                    <tr class="cnd-row" data-grp="<?=$tblId?>" data-idx="<?=$fi?>"<?=$fi>=50?' style="display:none;"':''?>>
                                                        <td style="color:#adb5bd;padding:7px 10px;vertical-align:top;"><?=$fi+1?></td>
                                                        <td style="padding:7px 10px;vertical-align:top;word-break:break-word;"><?=esc(formatEmbeddedDates($frow['anomaly']??''))?></td>
                                                        <?php if($hasTs):?>
                                                        <td style="color:#adb5bd;padding:7px 10px;vertical-align:top;white-space:nowrap;font-size:.73rem;"><?=!empty($frow['event_timestamp'])?date('M jS D Y h:i:s A',strtotime($frow['event_timestamp'])):'—'?></td>
                                                        <?php endif;?>
                                                        <?php if($hasSc):?>
                                                        <td style="text-align:right;padding:7px 10px;vertical-align:top;color:#6c757d;"><?=!empty($frow['score'])?round((float)$frow['score'],3):'—'?></td>
                                                        <?php endif;?>
                                                    </tr>
                                                <?php endforeach;?>
                                                </tbody>
                                            </table>
                                            <?php if($fCount>50):?>
                                            <div style="font-size:.75rem;color:#6c757d;display:flex;align-items:center;gap:10px;">
                                                <span id="<?=$tblId?>-info">Showing 1–50 of <?=$fCount?></span>
                                                <button class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;padding:2px 10px;border-radius:6px;" id="<?=$tblId?>-prev" onclick="pageCondensed('<?=$tblId?>',<?=$fCount?>,'prev')" disabled>‹ Prev</button>
                                                <button class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;padding:2px 10px;border-radius:6px;" id="<?=$tblId?>-next" onclick="pageCondensed('<?=$tblId?>',<?=$fCount?>,'next')">Next ›</button>
                                            </div>
                                            <?php endif;?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <?php foreach($findings as $fi=>$row):
                                $techId='tech-'.preg_replace('/[^a-z0-9]/i','-',$cat.'-'.$algKey).'-low-'.$fi;
                                $details=!empty($row['details'])?(is_string($row['details'])?@json_decode($row['details'],true):$row['details']):null;
                            ?>
                            <div class="finding-card">
                                <div class="d-flex align-items-start" style="gap:12px;">
                                    <div class="finding-sev-dot" style="background:<?=$fSevDot?>;margin-top:5px;"></div>
                                    <div style="flex:1;">
                                        <div class="d-flex align-items-center mb-1" style="gap:8px;flex-wrap:wrap;">
                                            <span class="badge badge-<?=$fSevC?>" style="font-size:.68rem;"><?=strtoupper($fSev)?></span>
                                            <?php if(!empty($row['event_timestamp'])):?>
                                            <span style="font-size:.73rem;color:#adb5bd;"><i class="far fa-clock mr-1"></i><?=date('M jS D Y h:i:s A',strtotime($row['event_timestamp']))?></span>
                                            <?php endif;?>
                                        </div>
                                        <div class="finding-title"><?=esc(formatEmbeddedDates($row['anomaly']??'Anomaly detected'))?></div>
                                        <?php if(!empty($row['score'])):?>
                                        <div class="finding-body">Anomaly confidence score: <strong><?=round((float)$row['score'],3)?></strong></div>
                                        <?php endif;?>
                                        <?php if(($details&&!empty($details))||!empty($row['algorithm'])):?>
                                        <button class="tech-toggle mt-2" onclick="toggleTech('<?=$techId?>')">
                                            <i class="fas fa-code fa-xs"></i><span id="<?=$techId?>-label">Technical details</span>
                                        </button>
                                        <div class="finding-tech" id="<?=$techId?>" style="display:none;">
                                            <?php if(!empty($row['algorithm'])):?>
                                            <div style="font-size:.73rem;color:#6c757d;margin-bottom:6px;"><i class="fas fa-cog mr-1"></i> Detection method: <strong><?=esc($row['algorithm'])?></strong></div>
                                            <?php endif;?>
                                            <?php if($details):?>
                                            <pre><?=esc(json_encode($details,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES))?></pre>
                                            <?php endif;?>
                                        </div>
                                        <?php endif;?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                    </div>
                </div><!-- /.card-body -->
            </div><!-- /.card -->
            <?php endforeach; /* grouped */?>
            <?php endif; /* empty(grouped) */?>

            <?php endif; // $has_report && $td ?>

            <!-- ════════════════════════════════════════════
                 SCAN HISTORY (always shown when jobs exist)
            ════════════════════════════════════════════ -->
            <?php if (!empty($recent_jobs)): ?>
            <div class="card card-outline card-secondary shadow-sm mt-4">
                <div class="card-header d-flex align-items-center py-3">
                    <h3 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-history mr-2 text-muted"></i>Scan History
                    </h3>
                    <div class="card-tools ml-auto">
                        <?php if (!$is_scanning): ?>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary" onclick="startScan('full')">
                                <i class="fas fa-play mr-1"></i>Full Scan
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="startScan('incremental')">
                                <i class="fas fa-bolt mr-1"></i>Scan New Data
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php foreach ($recent_jobs as $hj):
                        $hjStatus  = $hj['status'] ?? 'unknown';
                        $hjEngine  = $hj['engine'] ?? 'php';
                        $hjScope   = $hj['scope']  ?? 'full';
                        $hjCount   = (int)($hj['results_count'] ?? 0);
                        $hjDate    = $hj['completed_at'] ?? $hj['created_at'] ?? null;
                        $hjId      = (int)$hj['id'];
                        $hjAlgs    = count((array)json_decode($hj['algorithms'] ?? '[]', true));
                        $dotBg     = match($hjStatus) {
                            'completed' => '#28a745', 'failed' => '#dc3545',
                            'running','pending' => '#667eea', default => '#adb5bd'
                        };
                        $isActive  = $job && (int)($job['id'] ?? 0) === $hjId;
                    ?>
                    <div class="hist-item <?= $isActive ? 'bg-light' : '' ?>"
                         onclick="<?= $hjStatus === 'completed' ? "window.location='" . base_url('analysis/anomalies/results?job_id=' . $hjId) . "'" : 'void(0)' ?>">
                        <div class="hist-dot" style="background:<?= $dotBg ?>1a;color:<?= $dotBg ?>;">
                            <?php if ($hjStatus === 'running' || $hjStatus === 'pending'): ?>
                            <i class="fas fa-circle-notch fa-spin"></i>
                            <?php elseif ($hjStatus === 'completed'): ?>
                            <i class="fas fa-check"></i>
                            <?php elseif ($hjStatus === 'failed'): ?>
                            <i class="fas fa-times"></i>
                            <?php else: ?>
                            <i class="fas fa-clock"></i>
                            <?php endif; ?>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div class="d-flex align-items-center gap-2" style="gap:8px;flex-wrap:wrap;">
                                <span class="hist-score">
                                    <?= $hjStatus === 'completed' ? ($hjCount . ' finding' . ($hjCount !== 1 ? 's' : '')) : ucfirst($hjStatus) ?>
                                </span>
                                <?php if ($isActive): ?>
                                <span class="hist-badge text-primary" style="background:rgba(0,123,255,.15);">Current</span>
                                <?php endif; ?>
                                <span class="hist-badge badge-light border text-muted">
                                    <?= $scopeLabel($hjScope) ?>
                                </span>
                                <span class="hist-badge badge-light border text-muted">
                                    <?= $hjEngine === 'python' ? 'Python' : 'PHP' ?> engine
                                </span>
                            </div>
                            <div class="hist-meta mt-1">
                                <?= $hjAlgs ?> algorithms &middot;
                                <?= $hjDate ? relDate($hjDate) : 'Pending' ?>
                                <?php if (!empty($hj['timing_ms'])): ?>
                                &middot; <?= fmtElapsed((int)$hj['timing_ms']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($hjStatus === 'completed'): ?>
                        <i class="fas fa-chevron-right text-muted" style="font-size:.75rem;"></i>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- .container-fluid -->
    </section><!-- .content -->
<!-- Scheduler Modal -->
<div class="modal fade" id="schedulerModal" tabindex="-1" role="dialog" aria-labelledby="schedulerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="schedulerModalLabel"><i class="fas fa-calendar-alt text-info mr-2"></i> Manage Scan Frequencies</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 450px; overflow-y: auto;">
                <p class="text-muted small">Configure the execution frequency of individual ML telemetry detectors. High-severity or intensive scans can be rate-limited (minimum 4 hours) to prevent battery/CPU depletion.</p>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-valign-middle" style="font-size: .85rem;">
                        <thead>
                            <tr>
                                <th>Category / Detector</th>
                                <th>Description</th>
                                <th>Schedule / Interval</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($categories_list ?? []) as $catKey => $catInfo): ?>
                                <?php foreach ($catInfo['algorithms'] as $alg): ?>
                                    <?php 
                                    $currFreq = $schedule_map[$alg['id']] ?? 24; 
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold"><?= esc($alg['name']) ?></span>
                                            <br>
                                            <small class="text-muted"><?= esc($catInfo['label']) ?> (<code><?= esc($alg['id']) ?></code>)</small>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= esc($alg['description']) ?></small>
                                        </td>
                                        <form action="<?= base_url('analysis/anomalies/save-schedule') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="algorithm_id" value="<?= esc($alg['id']) ?>">
                                            <td>
                                                <select name="frequency_hours" class="form-control form-control-sm" style="width: 130px; display: inline-block;">
                                                    <option value="4" <?= $currFreq == 4 ? 'selected' : '' ?>>Every 4 hours</option>
                                                    <option value="8" <?= $currFreq == 8 ? 'selected' : '' ?>>Every 8 hours</option>
                                                    <option value="12" <?= $currFreq == 12 ? 'selected' : '' ?>>Every 12 hours</option>
                                                    <option value="24" <?= $currFreq == 24 ? 'selected' : '' ?>>Every 24 hours (Daily)</option>
                                                    <option value="48" <?= $currFreq == 48 ? 'selected' : '' ?>>Every 48 hours</option>
                                                    <option value="168" <?= $currFreq == 168 ? 'selected' : '' ?>>Weekly</option>
                                                </select>
                                            </td>
                                            <td>
                                                <button type="submit" class="btn btn-sm btn-primary font-weight-bold">
                                                    Save
                                                </button>
                                            </td>
                                        </form>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
</div>
</div><!-- .content-wrapper -->

<!-- ════════════════════════════════════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════════════════════════════════ -->
<script>
(function () {
    'use strict';

    /* ── Config injected from PHP ───────────────────────────────────────────── */
    var START_URL       = <?= json_encode($start_url ?? '') ?>;
    var STATUS_BASE     = <?= json_encode(($status_url_base ?? '') . '/') ?>;
    var PROCESS_BASE    = <?= json_encode(($process_url_base ?? '') . '/') ?>;
    var RESULTS_BASE    = <?= json_encode($results_base_url ?? '') ?>;
    var ACTIVE_JOB_ID   = <?= json_encode($active_job_id) ?>;
    var IS_SCANNING     = <?= json_encode((bool)$is_scanning) ?>;

    /* ── Human-readable algorithm name map ──────────────────────────────────── */
    var ALG_LABELS = {
        sms_freq:'SMS message frequency', sms_time:'Night SMS pattern', sms_cluster:'SMS sender clustering',
        sms_bert:'SMS phishing detection', contacts_freq:'Contact add frequency', contacts_dup:'Duplicate contacts',
        contacts_graph:'Contact network analysis', calls_burst:'Short-call bursts', calls_night:'Night call activity',
        calls_isolation:'Call outlier detection', loc_geofence:'Geofence monitoring', loc_speed:'Travel speed analysis',
        loc_dbscan:'Location clustering', apps_rep:'App reputation check', apps_perm:'App permissions analysis',
        apps_autoencoder:'App manifest scan', files_spike:'File creation spike', files_ext:'File extension scan',
        files_entropy:'Suspicious file scan', act_screen:'Screen time analysis', act_switch:'App switch rate',
        act_lstm:'Activity sequence model', dev_hw:'Hardware change check', dev_net:'Network profile monitor',
        dev_oneclass:'System state profiler', python_backend:'Python engine setup', python_results:'Python results fetch'
    };

    /* ── Category → data category mapping ─────────────────────────────────── */
    var ALG_TO_CAT = {
        sms_freq:'sms', sms_time:'sms', sms_cluster:'sms', sms_bert:'sms',
        contacts_freq:'contacts', contacts_dup:'contacts', contacts_graph:'contacts',
        calls_burst:'call_logs', calls_night:'call_logs', calls_isolation:'call_logs',
        loc_geofence:'locations', loc_speed:'locations', loc_dbscan:'locations',
        apps_rep:'apps', apps_perm:'apps', apps_autoencoder:'apps',
        files_spike:'files', files_ext:'files', files_entropy:'files',
        act_screen:'activity', act_switch:'activity', act_lstm:'activity',
        dev_hw:'device_info', dev_net:'device_info', dev_oneclass:'device_info'
    };

    /* ── Scanning state ─────────────────────────────────────────────────────── */
    var pollTimer = null;
    var activeJobId = ACTIVE_JOB_ID;

    if (IS_SCANNING && activeJobId) {
        startPolling(activeJobId);
    }

    function startPolling(jobId) {
        clearInterval(pollTimer);
        pollTimer = setInterval(function () { pollStatus(jobId); }, 1500);
    }

    function pollStatus(jobId) {
        fetch(STATUS_BASE + jobId)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.error) { clearInterval(pollTimer); return; }

                updateScanUI(data);

                if (data.status === 'completed') {
                    clearInterval(pollTimer);
                    setTimeout(function () {
                        window.location.href = RESULTS_BASE + '?job_id=' + jobId;
                    }, 800);
                } else if (data.status === 'failed') {
                    clearInterval(pollTimer);
                    showAlert('Scan failed: ' + (data.error_message || 'Unknown error'), 'danger');
                }
            })
            .catch(function () { /* network glitch — keep polling */ });
    }

    function updateScanUI(data) {
        var pct = Math.max(0, Math.min(100, data.progress_pct || 0));
        var bar = document.getElementById('scan-progress-bar');
        var pctLabel = document.getElementById('scan-pct-label');
        var counter  = document.getElementById('scan-alg-counter');
        var algLabel = document.getElementById('scan-current-alg');

        if (bar)      bar.style.width = pct + '%';
        if (pctLabel) pctLabel.textContent = pct + '%';
        if (counter)  counter.textContent =
            (data.completed_algorithms || 0) + ' of ' + (data.total_algorithms || '?') + ' checks complete';

        var algId = (data.current_algorithm || '').toLowerCase().replace(/\s+/g, '_');
        var algName = ALG_LABELS[algId] || data.current_algorithm || 'Checking…';
        if (algLabel) algLabel.textContent = algName;

        // Highlight active category chip
        var cat = ALG_TO_CAT[algId];
        if (cat) {
            var chips = document.querySelectorAll('.scan-cat-chip');
            chips.forEach(function (c) {
                if (c.id === 'scat-' + cat) { c.classList.add('active'); c.classList.remove('done'); }
            });
        }
        // Mark completed cats done
        if (pct >= 100) {
            document.querySelectorAll('.scan-cat-chip').forEach(function (c) {
                c.classList.remove('active'); c.classList.add('done');
            });
        }
    }

    /* ── Start scan ─────────────────────────────────────────────────────────── */
    window.startScan = function (scope) {
        var btn = document.getElementById('btn-full-scan');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-1"></i> Starting…'; }

        fetch(START_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest' },
            body: 'scope=' + encodeURIComponent(scope || 'full')
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.error) {
                showAlert(data.error, 'danger');
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-play-circle"></i> Run Full Scan'; }
                return;
            }
            activeJobId = data.job_id;
            // If Python engine, kick the process endpoint
            if (data.mode === 'process_needed') {
                fetch(PROCESS_BASE + data.job_id, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
            }
            // Reload page to show scanning state
            window.location.href = RESULTS_BASE + '?job_id=' + data.job_id;
        })
        .catch(function (e) {
            showAlert('Could not start scan. Please try again.', 'danger');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-play-circle"></i> Run Full Scan'; }
        });
    };

    /* ── Rescan dropdown ────────────────────────────────────────────────────── */
    window.toggleRescanMenu = function (e) {
        e.stopPropagation();
        var d = document.getElementById('rescan-dropdown');
        if (d) d.classList.toggle('open');
    };
    window.closeRescanMenu = function () {
        var d = document.getElementById('rescan-dropdown');
        if (d) d.classList.remove('open');
    };
    document.addEventListener('click', function () { closeRescanMenu(); });

    /* ── Technical detail toggle ─────────────────────────────────────────────── */
    window.toggleTech = function (id) {
        var el    = document.getElementById(id);
        var label = document.getElementById(id + '-label');
        if (!el) return;
        var open = el.style.display !== 'none';
        el.style.display = open ? 'none' : 'block';
        if (label) label.textContent = open ? 'Technical details' : 'Hide details';
    };

    /* ── Condensed entries toggle ───────────────────────────────────────────── */
    window.toggleCondensed = function (id) {
        var el = document.getElementById(id);
        var label = document.getElementById(id + '-btn-label');
        if (!el) return;
        var open = el.style.display !== 'none';
        el.style.display = open ? 'none' : 'block';
        if (label) {
            var count = el.querySelectorAll('tbody tr').length;
            label.textContent = open ? 'View all ' + count + ' entries' : 'Hide entries';
        }
    };

    /* ── Condensed client-side pagination ───────────────────────────────────── */
    window.pageCondensed = function (grpId, total, dir) {
        var rows = document.querySelectorAll('tr[data-grp="' + grpId + '"]');
        var info = document.getElementById(grpId + '-info');
        var prevBtn = document.getElementById(grpId + '-prev');
        var nextBtn = document.getElementById(grpId + '-next');
        if (!rows.length) return;

        // Find current page boundary
        var currentStart = 0;
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].style.display !== 'none') {
                currentStart = i;
                break;
            }
        }

        var newStart = currentStart;
        if (dir === 'next') {
            newStart = currentStart + 50;
        } else if (dir === 'prev') {
            newStart = Math.max(0, currentStart - 50);
        }

        var newEnd = Math.min(total, newStart + 50);

        // Toggle row visibility
        rows.forEach(function (row, idx) {
            if (idx >= newStart && idx < newEnd) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Update info & buttons
        if (info) info.textContent = 'Showing ' + (newStart + 1) + '–' + newEnd + ' of ' + total;
        if (prevBtn) prevBtn.disabled = (newStart === 0);
        if (nextBtn) nextBtn.disabled = (newEnd >= total);
    };

    /* ── Alert helper ───────────────────────────────────────────────────────── */
    function showAlert(msg, type) {
        var area = document.getElementById('anom-alert-area');
        if (!area) return;
        area.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            '<i class="fas fa-exclamation-circle mr-2"></i>' + msg +
            '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>';
    }

})();
</script>
