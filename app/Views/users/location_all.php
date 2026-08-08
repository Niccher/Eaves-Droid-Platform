<?php
/**
 * @var array  $timeline
 * @var int    $totalLocations
 * @var int    $totalActivities
 * @var bool   $has_coords_filter
 * @var object $pager
 */

// ── Helper: normalise timestamp to seconds ────────────────────────────────────
function tsSec(int $ts): int {
    return $ts > 1_000_000_000_000 ? (int)($ts / 1000) : $ts;
}

// ── Pre-process timeline → fused groups ──────────────────────────────────────
// Strategy: walk the sorted-desc array; greedily pair the nearest location + activity
// that are within FUSE_WINDOW seconds of each other.
define('FUSE_WINDOW', 120); // seconds

$unfused = $timeline; // already sorted desc by _sort_time
$fused   = [];
$used    = [];

foreach ($unfused as $i => $a) {
    if (isset($used[$i])) continue;
    $aTs = tsSec((int)($a['_sort_time'] ?? 0));

    // Try to find the nearest partner of the opposite type within FUSE_WINDOW
    $bestJ = null;
    $bestDiff = PHP_INT_MAX;

    foreach ($unfused as $j => $b) {
        if ($j === $i || isset($used[$j]) || $b['_type'] === $a['_type']) continue;
        $bTs = tsSec((int)($b['_sort_time'] ?? 0));
        $diff = abs($aTs - $bTs);
        if ($diff <= FUSE_WINDOW && $diff < $bestDiff) {
            $bestDiff = $diff;
            $bestJ    = $j;
        }
    }

    if ($bestJ !== null) {
        $used[$i] = true;
        $used[$bestJ] = true;
        $loc = $a['_type'] === 'location' ? $a : $unfused[$bestJ];
        $act = $a['_type'] === 'activity' ? $a : $unfused[$bestJ];
        $fused[] = [
            'type'     => 'fused',
            'loc'      => $loc,
            'act'      => $act,
            'sort_ts'  => max(tsSec((int)($loc['_sort_time']??0)), tsSec((int)($act['_sort_time']??0))),
        ];
    } else {
        $used[$i] = true;
        $fused[] = [
            'type'    => 'solo',
            'entry'   => $a,
            'sort_ts' => $aTs,
        ];
    }
}

// Re-sort fused desc
usort($fused, fn($x,$y) => $y['sort_ts'] - $x['sort_ts']);

// ── Quick stats from this page ────────────────────────────────────────────────
$locWithCoords = 0; $actTypes = []; $lastLat = null; $lastLng = null;
foreach ($timeline as $e) {
    if ($e['_type'] === 'location') {
        if (!empty($e['latitude']) && !empty($e['longitude'])) {
            $locWithCoords++;
            if ($lastLat === null) { $lastLat = $e['latitude']; $lastLng = $e['longitude']; }
        }
    } else {
        $t = strtoupper($e['activity_type'] ?? 'UNKNOWN');
        $actTypes[$t] = ($actTypes[$t] ?? 0) + 1;
    }
}
arsort($actTypes);
$topAct = array_key_first($actTypes) ?? '—';

// ── Helpers ───────────────────────────────────────────────────────────────────
function renderConfRing(?int $confidence): string {
    if ($confidence === null) return '';
    $r = 15; $circ = round(2*M_PI*$r, 1);
    $dash = round($confidence/100*$circ, 1);
    $col  = $confidence >= 80 ? '#28a745' : ($confidence >= 50 ? '#ffc107' : '#dc3545');
    return "<div class='conf-ring-wrap' title='Confidence: {$confidence}%'>
        <svg width='40' height='40' viewBox='0 0 40 40' style='transform:rotate(-90deg)'>
          <circle cx='20' cy='20' r='{$r}' fill='none' stroke='#dee2e6' stroke-width='4'/>
          <circle cx='20' cy='20' r='{$r}' fill='none' stroke='{$col}' stroke-width='4'
            stroke-dasharray='{$dash} {$circ}' stroke-linecap='round'/>
        </svg>
        <div class='conf-ring-val'>{$confidence}%</div>
    </div>";
}

function actMeta(array $e): array {
    $actType  = strtoupper($e['activity_type'] ?? $e['status'] ?? 'UNKNOWN');
    $icon = 'fa-question-circle'; $color = 'secondary'; $cls = '';
    if (str_contains($actType,'WALK')||str_contains($actType,'RUN'))  { $icon='fa-running';    $color='success';  }
    elseif (str_contains($actType,'STILL')||str_contains($actType,'IDLE')) { $icon='fa-stop-circle'; $color='secondary'; $cls='still'; }
    elseif (str_contains($actType,'VEHICLE')||str_contains($actType,'DRIV')){ $icon='fa-car';   $color='info';  $cls='vehicle'; }
    elseif (str_contains($actType,'BICYCLE'))                               { $icon='fa-bicycle'; $color='warning'; $cls='bicycle'; }
    elseif (str_contains($actType,'TILT'))                                  { $icon='fa-mobile-alt'; $color='dark'; }
    return compact('actType','icon','color','cls');
}
?>
<style>
/* ── Timeline spine ─────────────────────────────────────────────────────── */
.tl-wrap         { position:relative; padding-left:40px; }
.tl-wrap::before { content:''; position:absolute; left:18px; top:0; bottom:0;
                   width:2px; background:linear-gradient(to bottom,#dee2e6,#ced4da); }
.tl-day          { position:relative; z-index:1; display:flex; align-items:center;
                   margin:22px 0 14px -40px; }
.tl-day-dot      { width:14px; height:14px; border-radius:50%; background:#6c757d;
                   border:3px solid #fff; box-shadow:0 0 0 2px #ced4da; margin-right:12px; flex-shrink:0; }
.tl-day-label    { font-size:11px; font-weight:700; color:#6c757d; text-transform:uppercase;
                   letter-spacing:.5px; background:#f0f2f5; border-radius:12px; padding:2px 10px; }

/* ── Entry row ─────────────────────────────────────────────────────────── */
.tl-item         { position:relative; z-index:1; display:flex; margin-bottom:12px; }
.tl-icon-col     { width:36px; flex-shrink:0; margin-left:-40px; margin-right:10px;
                   display:flex; align-items:flex-start; padding-top:2px; }
.tl-icon         { width:36px; height:36px; border-radius:50%; display:flex;
                   align-items:center; justify-content:center; font-size:14px;
                   border:3px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,.2); flex-shrink:0; }

/* icon colours */
.tl-icon.fused    { background:linear-gradient(135deg,#6f42c1,#4a1f8c); color:#fff; }
.tl-icon.loc-only { background:linear-gradient(135deg,#007bff,#0056b3); color:#fff; }
.tl-icon.act-only.still  { background:linear-gradient(135deg,#6c757d,#495057); color:#fff; }
.tl-icon.act-only.vehicle{ background:linear-gradient(135deg,#17a2b8,#117a8b); color:#fff; }
.tl-icon.act-only.bicycle{ background:linear-gradient(135deg,#ffc107,#d39e00); color:#333; }
.tl-icon.act-only         { background:linear-gradient(135deg,#28a745,#1e7e34); color:#fff; }

/* ── Card ───────────────────────────────────────────────────────────────── */
.tl-card          { flex:1; border-radius:8px; background:#fff;
                    box-shadow:0 1px 4px rgba(0,0,0,.10); border:1px solid #e9ecef;
                    overflow:hidden; transition:box-shadow .2s; min-width:0; }
.tl-card:hover    { box-shadow:0 3px 12px rgba(0,0,0,.15); }

/* fused = two-tone left border */
.tl-card.fused    { border-left:4px solid #6f42c1; }
.tl-card.loc-only { border-left:4px solid #007bff; }
.tl-card.act-only         { border-left:4px solid #28a745; }
.tl-card.act-only.still   { border-left-color:#6c757d; }
.tl-card.act-only.vehicle { border-left-color:#17a2b8; }
.tl-card.act-only.bicycle { border-left-color:#ffc107; }

/* ── Fused two-column body ──────────────────────────────────────────────── */
.fused-body       { display:grid; grid-template-columns:1fr 1fr; }
.fused-col        { padding:10px 14px; }
.fused-col + .fused-col { border-left:1px dashed #dee2e6; }
.fused-header     { display:flex; align-items:center; justify-content:space-between;
                    padding:6px 14px; background:#f8f9fa; border-bottom:1px solid #e9ecef; }

/* solo card simple padding */
.solo-body        { padding:10px 14px; }

.tl-lbl           { font-size:10px; text-transform:uppercase; letter-spacing:.4px; color:#adb5bd; font-weight:700; }
.tl-time          { font-size:10.5px; color:#868e96; font-weight:600; }
.tl-coords        { font-family:monospace; font-size:12.5px; color:#343a40; font-weight:700; }
.col-section-title{ font-size:10.5px; font-weight:700; color:#495057; text-transform:uppercase;
                    letter-spacing:.4px; border-bottom:1px solid #e9ecef; padding-bottom:4px; margin-bottom:6px; }

.conf-ring-wrap   { position:relative; width:40px; height:40px; flex-shrink:0; }
.conf-ring-val    { position:absolute; inset:0; display:flex; align-items:center;
                    justify-content:center; font-size:9px; font-weight:700; color:#343a40; }

.stat-pill        { display:inline-flex; align-items:center; background:#fff;
                    border:1px solid #dee2e6; border-radius:20px; padding:4px 12px;
                    font-size:12px; font-weight:600; gap:6px; }
.tl-toolbar       { background:#fff; border:1px solid #dee2e6; border-radius:8px;
                    padding:10px 14px; margin-bottom:18px; display:flex; flex-wrap:wrap;
                    gap:10px; align-items:center; }
.map-thumb-link   { display:inline-flex; align-items:center; gap:4px; background:#e9f0ff;
                    border-radius:6px; padding:3px 8px; font-size:11px; color:#007bff;
                    text-decoration:none; border:1px solid #c3d6ff; transition:background .2s; }
.map-thumb-link:hover { background:#cfe2ff; }
.prov-badge       { border-radius:10px; padding:1px 7px; font-size:10px; font-weight:700; }

.kv               { display:flex; justify-content:space-between; font-size:11.5px;
                    padding:2px 0; border-bottom:1px solid #f5f5f5; }
.kv:last-child    { border-bottom:none; }
.kv .k            { color:#868e96; font-size:10.5px; }
.kv .v            { font-weight:700; color:#343a40; text-align:right; }

@media (max-width:600px) {
    .fused-body { grid-template-columns:1fr; }
    .fused-col+.fused-col { border-left:none; border-top:1px dashed #dee2e6; }
}
</style>

<div class="content-wrapper">

  <!-- ── Header ─────────────────────────────────────────────────────────── -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-start">
        <div class="col-lg-8">
          <div class="d-flex align-items-center mb-1">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-route text-primary mr-2"></i>Location &amp; Activity Timeline</h1>
          </div>
          <p class="text-muted mb-2">Chronological forensic feed — GPS pings and activity states fused by timestamp proximity</p>
          <div class="d-flex flex-wrap" style="gap:8px;">
            <span class="stat-pill"><i class="fas fa-map-marker-alt text-primary"></i><?= number_format($totalLocations) ?> Locations</span>
            <span class="stat-pill"><i class="fas fa-walking text-success"></i><?= number_format($totalActivities) ?> Activities</span>
            <span class="stat-pill"><i class="fas fa-object-group text-purple" style="color:#6f42c1"></i><?= count(array_filter($fused, fn($f)=>$f['type']==='fused')) ?> Fused</span>
            <span class="stat-pill"><i class="fas fa-crosshairs text-info"></i><?= $locWithCoords ?> w/ Coords</span>
            <?php if ($topAct !== '—'): ?>
              <span class="stat-pill"><i class="fas fa-chart-bar text-warning"></i><?= esc($topAct) ?> (<?= $actTypes[$topAct] ?>)</span>
            <?php endif; ?>
            <?php if ($lastLat !== null): ?>
              <a href="https://www.google.com/maps?q=<?= $lastLat ?>,<?= $lastLng ?>" target="_blank"
                 class="stat-pill text-decoration-none" style="border-color:#c3d6ff;color:#007bff;">
                <i class="fas fa-location-arrow"></i>Latest Fix
              </a>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-lg-4 text-right mt-2 mt-lg-0">
          <button class="btn btn-success btn-sm" id="pdfExport">
            <i class="fas fa-file-pdf mr-1"></i>Export PDF
          </button>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <!-- ── Toolbar ─────────────────────────────────────────────────────── -->
      <div class="tl-toolbar">
        <div class="input-group input-group-sm" style="max-width:240px;">
          <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
          <input id="tlSearch" type="text" class="form-control" placeholder="Search entries…">
        </div>
        <select id="typeFilter" class="form-control form-control-sm" style="max-width:170px;">
          <option value="all">All Entries</option>
          <option value="fused">Fused Only</option>
          <option value="location">Location Only</option>
          <option value="activity">Activity Only</option>
        </select>
        <div class="custom-control custom-switch ml-auto">
          <input type="checkbox" class="custom-control-input" id="hasCoordsToggle"
            <?= !empty($has_coords_filter) ? 'checked' : '' ?>
            onchange="window.location.href='<?= current_url() ?>?has_coords='+(this.checked?'1':'0')">
          <label class="custom-control-label" for="hasCoordsToggle"><small>Coords only</small></label>
        </div>
        <span class="text-muted small ml-2"><i class="fas fa-layer-group mr-1"></i><?= count($fused) ?> cards</span>
      </div>

      <!-- ── Timeline ────────────────────────────────────────────────────── -->
      <?php if (empty($fused)): ?>
        <div class="text-center py-5">
          <i class="fas fa-route fa-3x text-muted mb-3 d-block"></i>
          <h4 class="text-muted">No timeline data yet</h4>
          <p class="text-muted">Records will appear once extracted.</p>
        </div>
      <?php else: ?>

        <div class="tl-wrap" id="tlFeed">
        <?php
        $lastDay = null;
        foreach ($fused as $idx => $group):
            $isFused   = $group['type'] === 'fused';
            $groupTs   = $group['sort_ts'];
            $dayKey    = $groupTs > 0 ? date('Y-m-d', $groupTs) : '0000-00-00';
            $dayLabel  = $groupTs > 0 ? date('l, M j Y', $groupTs)   : 'Unknown date';
            $timeStr   = $groupTs > 0 ? date('H:i:s', $groupTs)       : '—';

            // Day separator
            if ($dayKey !== $lastDay):
                $lastDay = $dayKey;
        ?>
          <div class="tl-day" data-day="<?= esc($dayKey) ?>">
            <div class="tl-day-dot"></div>
            <span class="tl-day-label"><i class="fas fa-calendar-alt mr-1"></i><?= esc($dayLabel) ?></span>
          </div>
        <?php endif; ?>

        <?php if ($isFused):
            /* ══════════════════════════════════════════════════════
               FUSED CARD  — location + activity side by side
               ══════════════════════════════════════════════════════ */
            $loc = $group['loc'];
            $act = $group['act'];

            // Location fields
            $lat      = $loc['latitude']  ?? null;
            $lng      = $loc['longitude'] ?? null;
            $hasCoords= ($lat !== null && $lat !== '' && $lng !== null && $lng !== '');
            $accuracy = isset($loc['accuracy']) ? (int)$loc['accuracy']           : null;
            $speed    = isset($loc['speed'])    ? round((float)$loc['speed']*3.6,1) : null; // km/h
            $altitude = isset($loc['altitude']) ? round((float)$loc['altitude'],1)  : null;
            $bearing  = isset($loc['bearing'])  ? round((float)$loc['bearing'],0)   : null;
            $provider = $loc['provider']     ?? null;
            $locStatus= $loc['status']       ?? 'success';
            $locModel = $loc['device_model'] ?? '';
            $locUpload= !empty($loc['created_at']) ? date('M j, H:i', strtotime($loc['created_at'])) : '—';
            $locId    = $loc['counter'] ?? $loc['id'] ?? '';
            $accClass = $accuracy !== null ? ($accuracy>100?'danger':($accuracy>50?'warning':'success')) : 'secondary';
            $provIcon = $provider==='gps' ? 'fa-satellite' : ($provider==='network' ? 'fa-wifi' : 'fa-broadcast-tower');
            $provColor= $provider==='gps' ? 'primary'      : ($provider==='network' ? 'info'    : 'secondary');

            // Activity fields
            $aMeta      = actMeta($act);
            $confidence = isset($act['confidence']) ? (int)$act['confidence'] : null;
            $isInter    = ($act['is_interactive'] ?? 0) == 1;
            $screenOn   = ($act['screen_on']      ?? 0) == 1;
            $battery    = isset($act['battery_level']) ? (int)$act['battery_level'] : null;
            $charging   = $act['charging_status'] ?? '';
            $network    = strtoupper($act['network_type'] ?? '');
            $actModel   = $act['device_model'] ?? '';
            $androidVer = $act['android_version'] ?? '';
            $info       = $act['info'] ?? '';
            $actUpload  = !empty($act['created_at']) ? date('M j, H:i', strtotime($act['created_at'])) : '—';
            $actId      = $act['counter'] ?? $act['id'] ?? '';
            $battColor  = $battery !== null ? ($battery<=15?'danger':($battery<=30?'warning':'success')) : 'secondary';
            $netColor   = 'secondary';
            if (str_contains($network,'WIFI')) $netColor='primary';
            elseif (str_contains($network,'LTE')||str_contains($network,'4G')) $netColor='success';
            elseif (str_contains($network,'3G')) $netColor='info';
            elseif (str_contains($network,'2G')||str_contains($network,'EDGE')) $netColor='warning';

            $searchStr = strtolower(($lat??'').' '.($lng??'').' '.($provider??'').' '.($aMeta['actType']).' '.$locModel.' '.$network.' '.$info);
        ?>
          <div class="tl-item" data-type="fused" data-search="<?= esc($searchStr) ?>">
            <div class="tl-icon-col">
              <div class="tl-icon fused" title="Fused: Location + Activity"><i class="fas fa-layer-group"></i></div>
            </div>
            <div class="tl-card fused">

              <!-- Card header bar -->
              <div class="fused-header">
                <div class="d-flex align-items-center" style="gap:8px;">
                  <span class="badge badge-light border" style="color:#6f42c1;border-color:#d0b4ff!important;">
                    <i class="fas fa-layer-group mr-1" style="color:#6f42c1;"></i>Fused Event
                  </span>
                  <?php if ($locModel): ?>
                    <small class="text-muted"><i class="fas fa-mobile-alt mr-1"></i><?= esc($locModel) ?></small>
                  <?php endif; ?>
                </div>
                <div class="d-flex align-items-center" style="gap:8px;">
                  <span class="tl-time"><i class="fas fa-clock mr-1"></i><?= $timeStr ?></span>
                  <?php if ($hasCoords): ?>
                    <a href="https://www.google.com/maps?q=<?= $lat ?>,<?= $lng ?>" target="_blank" class="map-thumb-link">
                      <i class="fas fa-map-marked-alt"></i>Maps
                    </a>
                  <?php endif; ?>
                  <!-- delete buttons -->
                  <button class="btn btn-sm btn-outline-danger py-0 px-2 delete-row"
                    data-type="location" data-id="<?= $locId ?>"
                    data-url="<?= base_url('location/delete') ?>" title="Delete location">
                    <i class="fas fa-map-marker-alt"></i><i class="fas fa-times ml-1"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger py-0 px-2 delete-row"
                    data-type="activity" data-id="<?= $actId ?>"
                    data-url="<?= base_url('activities/delete') ?>" title="Delete activity">
                    <i class="fas fa-running"></i><i class="fas fa-times ml-1"></i>
                  </button>
                </div>
              </div>

              <!-- Two-column body -->
              <div class="fused-body">

                <!-- ── LEFT: Location ─────────────────────────────── -->
                <div class="fused-col">
                  <div class="col-section-title"><i class="fas fa-map-marker-alt text-primary mr-1"></i>Location</div>

                  <?php if ($hasCoords): ?>
                    <div class="tl-coords mb-2"><?= esc($lat) ?>, <?= esc($lng) ?></div>
                  <?php else: ?>
                    <div class="text-muted mb-2" style="font-size:12px;"><i class="fas fa-minus-circle mr-1"></i>No coordinates</div>
                  <?php endif; ?>

                  <div class="kv"><span class="k"><i class="fas fa-broadcast-tower text-muted mr-1"></i>Provider</span><span class="v">
                    <?php if ($provider): ?>
                      <span class="prov-badge badge badge-<?= $provColor ?>"><i class="fas <?= $provIcon ?> mr-1"></i><?= strtoupper($provider) ?></span>
                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                  </span></div>
                  <div class="kv"><span class="k"><i class="fas fa-bullseye text-<?= $accClass ?> mr-1"></i>Accuracy</span><span class="v">
                    <?= $accuracy !== null ? '<span class="badge badge-'.$accClass.'">'.$accuracy.' m</span>' : '—' ?>
                  </span></div>
                  <div class="kv"><span class="k"><i class="fas fa-tachometer-alt text-info mr-1"></i>Speed</span><span class="v"><?= $speed !== null ? $speed.' km/h' : '—' ?></span></div>
                  <div class="kv"><span class="k"><i class="fas fa-mountain text-secondary mr-1"></i>Altitude</span><span class="v"><?= $altitude !== null ? $altitude.' m' : '—' ?></span></div>
                  <div class="kv"><span class="k"><i class="fas fa-compass text-primary mr-1"></i>Bearing</span><span class="v"><?= $bearing !== null ? $bearing.'°' : '—' ?></span></div>
                  <div class="kv"><span class="k"><i class="fas fa-<?= $locStatus==='success'?'check-circle text-success':'exclamation-circle text-warning' ?> mr-1"></i>Status</span><span class="v">
                    <span class="badge badge-<?= $locStatus==='success'?'success':'warning' ?>"><?= esc($locStatus) ?></span>
                  </span></div>
                  <div class="kv"><span class="k"><i class="fas fa-cloud-upload-alt text-muted mr-1"></i>Uploaded</span><span class="v" style="font-size:10px;"><?= $locUpload ?></span></div>
                </div>

                <!-- ── RIGHT: Activity ────────────────────────────── -->
                <div class="fused-col">
                  <div class="col-section-title"><i class="fas fa-<?= $aMeta['icon'] ?> text-<?= $aMeta['color'] ?> mr-1"></i>Activity</div>

                  <div class="d-flex align-items-center mb-2" style="gap:10px;">
                    <?= renderConfRing($confidence) ?>
                    <div>
                      <div class="font-weight-bold" style="font-size:13px;"><?= esc($aMeta['actType']) ?></div>
                      <div style="font-size:11px;color:#868e96;"><?= $isInter ? 'Interactive' : 'Background' ?></div>
                    </div>
                  </div>

                  <div class="kv"><span class="k"><i class="fas fa-battery-<?= $battery!==null?($battery>70?'full':($battery>30?'half':'empty')):'half' ?> text-<?= $battColor ?> mr-1"></i>Battery</span><span class="v">
                    <?php if ($battery !== null): ?>
                      <span class="text-<?= $battColor ?>"><?= $battery ?>%</span>
                      <?php if ($charging): ?><small class="text-muted"> (<?= esc($charging) ?>)</small><?php endif; ?>
                    <?php else: ?>—<?php endif; ?>
                  </span></div>
                  <div class="kv"><span class="k"><i class="fas fa-signal text-<?= $netColor ?> mr-1"></i>Network</span><span class="v">
                    <?= $network ? '<span class="badge badge-'.$netColor.'">'.$network.'</span>' : '—' ?>
                  </span></div>
                  <div class="kv"><span class="k"><i class="fas fa-<?= $screenOn?'sun text-warning':'moon text-secondary' ?> mr-1"></i>Screen</span><span class="v">
                    <span class="badge badge-<?= $screenOn?'success':'secondary' ?>"><?= $screenOn?'ON':'OFF' ?></span>
                  </span></div>
                  <?php if ($androidVer): ?>
                    <div class="kv"><span class="k"><i class="fab fa-android text-success mr-1"></i>Android</span><span class="v"><?= esc($androidVer) ?></span></div>
                  <?php endif; ?>
                  <?php if ($info): ?>
                    <div class="mt-1 text-muted" style="font-size:10.5px;">
                      <i class="fas fa-info-circle mr-1"></i><?= esc(mb_substr($info,0,90)) ?><?= mb_strlen($info)>90?'…':'' ?>
                    </div>
                  <?php endif; ?>
                  <div class="kv"><span class="k"><i class="fas fa-cloud-upload-alt text-muted mr-1"></i>Uploaded</span><span class="v" style="font-size:10px;"><?= $actUpload ?></span></div>
                </div>

              </div><!-- /.fused-body -->
            </div><!-- /.tl-card.fused -->
          </div><!-- /.tl-item -->

        <?php else:
            /* ══════════════════════════════════════════════════════
               SOLO CARD  — location-only or activity-only
               ══════════════════════════════════════════════════════ */
            $e       = $group['entry'];
            $eType   = $e['_type'];
            $entryId = $e['counter'] ?? $e['id'] ?? $idx;
            $uploadedAt = !empty($e['created_at']) ? date('M j, H:i', strtotime($e['created_at'])) : '—';

            if ($eType === 'location'):
                $lat      = $e['latitude']  ?? null;
                $lng      = $e['longitude'] ?? null;
                $hasCoords= ($lat!==null && $lat!=='' && $lng!==null && $lng!=='');
                $accuracy = isset($e['accuracy']) ? (int)$e['accuracy'] : null;
                $speed    = isset($e['speed'])    ? round((float)$e['speed']*3.6,1) : null;
                $altitude = isset($e['altitude']) ? round((float)$e['altitude'],1)  : null;
                $bearing  = isset($e['bearing'])  ? round((float)$e['bearing'],0)   : null;
                $provider = $e['provider']    ?? null;
                $devModel = $e['device_model'] ?? '';
                $status   = $e['status']      ?? 'success';
                $accClass = $accuracy!==null ? ($accuracy>100?'danger':($accuracy>50?'warning':'success')) : 'secondary';
                $provIcon = $provider==='gps'?'fa-satellite':($provider==='network'?'fa-wifi':'fa-broadcast-tower');
                $provColor= $provider==='gps'?'primary':($provider==='network'?'info':'secondary');
                $search   = strtolower(($lat??'').' '.($lng??'').' '.($provider??'').' '.$devModel);
        ?>
          <div class="tl-item" data-type="location" data-search="<?= esc($search) ?>">
            <div class="tl-icon-col"><div class="tl-icon loc-only"><i class="fas fa-map-marker-alt"></i></div></div>
            <div class="tl-card loc-only">
              <div class="solo-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                  <div>
                    <span class="tl-lbl">Location Only</span>
                    <?php if ($hasCoords): ?>
                      <div class="tl-coords mt-1"><?= esc($lat) ?>, <?= esc($lng) ?></div>
                    <?php else: ?>
                      <div class="text-muted mt-1" style="font-size:12px;"><i class="fas fa-minus-circle mr-1"></i>No coordinates</div>
                    <?php endif; ?>
                  </div>
                  <div class="text-right">
                    <div class="tl-time"><i class="fas fa-clock mr-1"></i><?= $timeStr ?></div>
                    <div class="mt-1">
                      <?php if ($provider): ?>
                        <span class="prov-badge badge badge-<?= $provColor ?>"><i class="fas <?= $provIcon ?> mr-1"></i><?= strtoupper($provider) ?></span>
                      <?php endif; ?>
                      <span class="badge badge-<?= $status==='success'?'success':'warning' ?> ml-1"><?= esc($status) ?></span>
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-wrap mt-2" style="gap:10px;font-size:11.5px;">
                  <?php if ($accuracy!==null): ?><span title="Accuracy"><i class="fas fa-bullseye text-<?= $accClass ?> mr-1"></i><b><?= $accuracy ?>m</b></span><?php endif; ?>
                  <?php if ($speed!==null):    ?><span title="Speed"><i class="fas fa-tachometer-alt text-info mr-1"></i><b><?= $speed ?> km/h</b></span><?php endif; ?>
                  <?php if ($altitude!==null): ?><span title="Altitude"><i class="fas fa-mountain text-secondary mr-1"></i><b><?= $altitude ?>m</b></span><?php endif; ?>
                  <?php if ($bearing!==null):  ?><span title="Bearing"><i class="fas fa-compass text-primary mr-1"></i><b><?= $bearing ?>°</b></span><?php endif; ?>
                  <?php if ($provider):        ?><span title="Provider"><i class="fas <?= $provIcon ?> text-<?= $provColor ?> mr-1"></i><?= strtoupper($provider) ?></span><?php endif; ?>
                  <span title="Status"><i class="fas fa-<?= $status==='success'?'check-circle text-success':'exclamation-circle text-warning' ?> mr-1"></i><?= esc($status) ?></span>
                  <?php if ($devModel):        ?><span title="Device"><i class="fas fa-mobile-alt text-muted mr-1"></i><?= esc($devModel) ?></span><?php endif; ?>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                  <small class="text-muted"><i class="fas fa-cloud-upload-alt mr-1"></i><?= $uploadedAt ?></small>
                  <div class="d-flex" style="gap:6px;">
                    <?php if ($hasCoords): ?>
                      <a href="https://www.google.com/maps?q=<?= $lat ?>,<?= $lng ?>" target="_blank" class="map-thumb-link"><i class="fas fa-external-link-alt mr-1"></i>Maps</a>
                    <?php endif; ?>
                    <button class="btn btn-sm btn-outline-danger py-0 px-2 delete-row"
                      data-type="location" data-id="<?= $entryId ?>"
                      data-url="<?= base_url('location/delete') ?>"><i class="fas fa-trash"></i></button>
                  </div>
                </div>
              </div>
            </div>
          </div>

        <?php else:
            // ACTIVITY SOLO
            $aMeta      = actMeta($e);
            $confidence = isset($e['confidence']) ? (int)$e['confidence'] : null;
            $isInter    = ($e['is_interactive']??0)==1;
            $screenOn   = ($e['screen_on']??0)==1;
            $battery    = isset($e['battery_level'])?(int)$e['battery_level']:null;
            $charging   = $e['charging_status']??'';
            $network    = strtoupper($e['network_type']??'');
            $devModel   = $e['device_model']??'';
            $androidVer = $e['android_version']??'';
            $info       = $e['info']??'';
            $battColor  = $battery!==null?($battery<=15?'danger':($battery<=30?'warning':'success')):'secondary';
            $netColor   = 'secondary';
            if (str_contains($network,'WIFI')) $netColor='primary';
            elseif (str_contains($network,'LTE')||str_contains($network,'4G')) $netColor='success';
            elseif (str_contains($network,'3G')) $netColor='info';
            elseif (str_contains($network,'2G')||str_contains($network,'EDGE')) $netColor='warning';
            $search = strtolower($aMeta['actType'].' '.$devModel.' '.$network.' '.mb_substr($info,0,60));
        ?>
          <div class="tl-item" data-type="activity" data-search="<?= esc($search) ?>">
            <div class="tl-icon-col"><div class="tl-icon act-only <?= $aMeta['cls'] ?>"><i class="fas <?= $aMeta['icon'] ?>"></i></div></div>
            <div class="tl-card act-only <?= $aMeta['cls'] ?>">
              <div class="solo-body">
                <div class="d-flex align-items-start justify-content-between flex-wrap">
                  <div class="d-flex align-items-center" style="gap:10px;">
                    <?= renderConfRing($confidence) ?>
                    <div>
                      <span class="tl-lbl">Activity Only</span>
                      <div class="font-weight-bold mt-1" style="font-size:13px;"><?= esc($aMeta['actType']) ?></div>
                      <div style="font-size:11px;color:#868e96;"><?= $isInter?'Interactive':'Background' ?></div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="tl-time"><i class="fas fa-clock mr-1"></i><?= $timeStr ?></div>
                    <div class="mt-1">
                      <?php if ($network): ?><span class="badge badge-<?= $netColor ?>"><?= $network ?></span><?php endif; ?>
                      <?php if ($screenOn): ?><span class="badge badge-success"><i class="fas fa-sun mr-1"></i>Screen ON</span><?php endif; ?>
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-wrap mt-2" style="gap:10px;font-size:11.5px;">
                  <?php if ($battery!==null): ?>
                    <span title="Battery"><i class="fas fa-battery-<?= $battery>70?'full':($battery>30?'half':'empty') ?> text-<?= $battColor ?> mr-1"></i><b><?= $battery ?>%</b><?php if ($charging): ?> <span class="text-muted"><i class="fas fa-plug mr-1"></i><?= esc($charging) ?></span><?php endif; ?></span>
                  <?php endif; ?>
                  <?php if ($network): ?><span title="Network"><i class="fas fa-signal text-<?= $netColor ?> mr-1"></i><span class="badge badge-<?= $netColor ?> p-1"><?= $network ?></span></span><?php endif; ?>
                  <?php if ($screenOn): ?><span title="Screen"><i class="fas fa-sun text-warning mr-1"></i><b>Screen ON</b></span><?php endif; ?>
                  <?php if ($devModel): ?><span title="Device"><i class="fas fa-mobile-alt text-muted mr-1"></i><?= esc($devModel) ?></span><?php endif; ?>
                  <?php if ($androidVer): ?><span title="OS"><i class="fab fa-android text-success mr-1"></i><?= esc($androidVer) ?></span><?php endif; ?>
                </div>
                <?php if ($info): ?>
                  <div class="mt-2 text-muted" style="font-size:11px;"><i class="fas fa-info-circle mr-1"></i><?= esc(mb_substr($info,0,120)) ?><?= mb_strlen($info)>120?'…':'' ?></div>
                <?php endif; ?>
                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                  <small class="text-muted"><i class="fas fa-cloud-upload-alt mr-1"></i><?= $uploadedAt ?></small>
                  <button class="btn btn-sm btn-outline-danger py-0 px-2 delete-row"
                    data-type="activity" data-id="<?= $entryId ?>"
                    data-url="<?= base_url('activities/delete') ?>"><i class="fas fa-trash"></i></button>
                </div>
              </div>
            </div>
          </div>

        <?php endif; // activity solo ?>
        <?php endif; // solo vs fused ?>
        <?php endforeach; ?>

        </div><!-- /.tl-wrap -->

        <?php if (isset($pager)): ?>
          <div class="d-flex justify-content-end mt-4"><?= $pager->links('default','bootstrap5_full') ?></div>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </section>
</div>

<script>
/* ── Search ───────────────────────────────────────────────────── */
document.getElementById('tlSearch').addEventListener('keyup', function () {
    var kw = this.value.toLowerCase();
    document.querySelectorAll('#tlFeed .tl-item').forEach(function (el) {
        el.style.display = ((el.dataset.search||'') + el.textContent.toLowerCase()).includes(kw) ? '' : 'none';
    });
    hideSeparators();
});

/* ── Type filter ──────────────────────────────────────────────── */
document.getElementById('typeFilter').addEventListener('change', function () {
    var val = this.value;
    document.querySelectorAll('#tlFeed .tl-item').forEach(function (el) {
        el.style.display = (val === 'all' || el.dataset.type === val) ? '' : 'none';
    });
    hideSeparators();
});

/* ── Hide empty day separators ────────────────────────────────── */
function hideSeparators() {
    document.querySelectorAll('#tlFeed .tl-day').forEach(function (sep) {
        var sib = sep.nextElementSibling, hasVisible = false;
        while (sib && !sib.classList.contains('tl-day')) {
            if (sib.classList.contains('tl-item') && sib.style.display !== 'none') { hasVisible = true; break; }
            sib = sib.nextElementSibling;
        }
        sep.style.display = hasVisible ? '' : 'none';
    });
}

/* ── PDF Export ───────────────────────────────────────────────── */
document.getElementById('pdfExport').addEventListener('click', function () {
    Swal.fire({ title:'Generating PDF…', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
    html2pdf().set({
        margin:8, filename:'location_timeline_'+Date.now()+'.pdf',
        image:{type:'jpeg',quality:.97}, html2canvas:{scale:2},
        jsPDF:{unit:'mm',format:'a4',orientation:'portrait'}
    }).from(document.getElementById('tlFeed')).save()
      .then(()=>Swal.fire({icon:'success',title:'Export Complete',timer:2000,showConfirmButton:false}))
      .catch(()=>Swal.fire({icon:'error',title:'Export Failed',timer:2500,showConfirmButton:false}));
});
</script>

<?php include __DIR__ . '/partials/_delete_confirm.php'; ?>
