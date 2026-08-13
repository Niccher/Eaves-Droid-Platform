<?php
/**
 * @var array  $timeline   merged cards: ['type'=>'merged','loc'=>array|null,'act'=>array|null,'sort_ts'=>int]
 * @var int    $totalLocations
 * @var int    $totalActivities
 * @var bool   $has_coords_filter
 * @var object $pager
 */

// ── Helper: normalise timestamp to seconds ────────────────────────────────────
function tsSec(int $ts): int {
    return $ts > 1_000_000_000_000 ? (int)($ts / 1000) : $ts;
}

// ── Quick stats from this page ────────────────────────────────────────────────
$locWithCoords = 0; $actTypes = []; $lastLat = null; $lastLng = null;
foreach ($timeline as $card) {
    $loc = $card['loc'] ?? null;
    $act = $card['act'] ?? null;
    if ($loc && !empty($loc['latitude']) && !empty($loc['longitude'])) {
        $locWithCoords++;
        if ($lastLat === null) { $lastLat = $loc['latitude']; $lastLng = $loc['longitude']; }
    }
    if ($act) {
        $t = strtoupper($act['activity_type'] ?? 'UNKNOWN');
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
.tl-icon.merged  { background:linear-gradient(135deg,#6f42c1,#4a1f8c); color:#fff; }
.tl-icon.loc-only{ background:linear-gradient(135deg,#007bff,#0056b3); color:#fff; }
.tl-icon.act-only.still  { background:linear-gradient(135deg,#6c757d,#495057); color:#fff; }
.tl-icon.act-only.vehicle{ background:linear-gradient(135deg,#17a2b8,#117a8b); color:#fff; }
.tl-icon.act-only.bicycle{ background:linear-gradient(135deg,#ffc107,#d39e00); color:#333; }
.tl-icon.act-only         { background:linear-gradient(135deg,#28a745,#1e7e34); color:#fff; }

/* ── Card ───────────────────────────────────────────────────────────────── */
.tl-card          { flex:1; border-radius:8px; background:#fff;
                    box-shadow:0 1px 4px rgba(0,0,0,.10); border:1px solid #e9ecef;
                    overflow:hidden; transition:box-shadow .2s; min-width:0; }
.tl-card:hover    { box-shadow:0 3px 12px rgba(0,0,0,.15); }
.tl-card.merged   { border-left:4px solid #6f42c1; }
.tl-card.loc-only { border-left:4px solid #007bff; }
.tl-card.act-only         { border-left:4px solid #28a745; }
.tl-card.act-only.still   { border-left-color:#6c757d; }
.tl-card.act-only.vehicle { border-left-color:#17a2b8; }
.tl-card.act-only.bicycle { border-left-color:#ffc107; }

/* ── Unified card body: location + activity in one card ────────────────── */
.card-header-bar    { display:flex; align-items:center; justify-content:space-between;
                      padding:6px 14px; background:#f8f9fa; border-bottom:1px solid #e9ecef; }
.card-body-sections { display:grid; grid-template-columns:1fr 1fr; }
.card-section       { padding:10px 14px; }
.card-section + .card-section { border-left:1px dashed #dee2e6; }
.card-section-title { font-size:10.5px; font-weight:700; color:#495057; text-transform:uppercase;
                      letter-spacing:.4px; border-bottom:1px solid #e9ecef; padding-bottom:4px; margin-bottom:6px; }

.tl-lbl           { font-size:10px; text-transform:uppercase; letter-spacing:.4px; color:#adb5bd; font-weight:700; }
.tl-time          { font-size:10.5px; color:#868e96; font-weight:600; }
.tl-coords        { font-family:monospace; font-size:12.5px; color:#343a40; font-weight:700; }
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
    .card-body-sections { grid-template-columns:1fr; }
    .card-section + .card-section { border-left:none; border-top:1px dashed #dee2e6; }
}
</style>

<div class="content-wrapper">

  <!-- ── Header ─────────────────────────────────────────────────────────── -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-start">
        <div class="col-lg-8">
          <div class="d-flex align-items-center mb-1">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-route text-primary mr-2"></i>Location &amp; Activity</h1>
          </div>
          <p class="text-muted mb-2">Chronological forensic feed — each card combines the GPS fix with the activity state recorded at the same extraction</p>
          <div class="d-flex flex-wrap" style="gap:8px;">
            <span class="stat-pill"><i class="fas fa-map-marker-alt text-primary"></i><?= number_format($totalLocations) ?> Locations</span>
            <span class="stat-pill"><i class="fas fa-walking text-success"></i><?= number_format($totalActivities) ?> Activities</span>
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
          <div class="btn-group btn-group-sm mb-2" role="group">
            <a href="<?= base_url('location') ?>" class="btn btn-primary"><i class="fas fa-route mr-1"></i> Timeline</a>
            <a href="<?= base_url('location/map') ?>" class="btn btn-outline-primary"><i class="fas fa-map-marked-alt mr-1"></i> Map View</a>
            <a href="<?= base_url('activities') ?>" class="btn btn-outline-primary"><i class="fas fa-running mr-1"></i> Activities</a>
          </div>
          &nbsp;&nbsp;
          <button class="btn btn-success btn-sm mb-2" id="pdfExport">
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
        <select id="typeFilter" class="form-control form-control-sm" style="max-width:190px;">
          <option value="all">All Entries</option>
          <option value="both">Location + Activity</option>
          <option value="location">Location Only</option>
          <option value="activity">Activity Only</option>
        </select>
        <div class="custom-control custom-switch ml-auto">
          <input type="checkbox" class="custom-control-input" id="hasCoordsToggle"
            <?= !empty($has_coords_filter) ? 'checked' : '' ?>
            onchange="window.location.href='<?= current_url() ?>?has_coords='+(this.checked?'1':'0')">
          <label class="custom-control-label" for="hasCoordsToggle"><small>Coords only</small></label>
        </div>
        <span class="text-muted small ml-2"><i class="fas fa-layer-group mr-1"></i><?= count($timeline) ?> cards</span>
      </div>

      <!-- ── Timeline ────────────────────────────────────────────────────── -->
      <?php if (empty($timeline)): ?>
        <div class="text-center py-5">
          <i class="fas fa-route fa-3x text-muted mb-3 d-block"></i>
          <h4 class="text-muted">No timeline data yet</h4>
          <p class="text-muted">Records will appear once extracted.</p>
        </div>
      <?php else: ?>

        <div class="tl-wrap" id="tlFeed">
        <?php
        $lastDay = null;
        foreach ($timeline as $idx => $card):
            $loc = $card['loc'] ?? null;
            $act = $card['act'] ?? null;
            $cardTs = $card['sort_ts'] ?? 0;
            $tsS = tsSec((int)$cardTs);
            $dayKey   = $tsS > 0 ? date('Y-m-d', $tsS)   : '0000-00-00';
            $dayLabel = $tsS > 0 ? date('l, M j Y', $tsS) : 'Unknown date';
            $timeStr  = $tsS > 0 ? date('H:i:s', $tsS)   : '—';

            $hasLoc = $loc !== null;
            $hasAct = $act !== null;
            $dataType = $hasLoc && $hasAct ? 'both' : ($hasLoc ? 'location' : 'activity');
            $iconClass = $hasLoc && $hasAct ? 'merged' : ($hasLoc ? 'loc-only' : 'act-only ' . actMeta($act)['cls']);
            $iconIcon  = $hasLoc && $hasAct ? 'fa-layer-group' : ($hasLoc ? 'fa-map-marker-alt' : actMeta($act)['icon']);
            $iconTitle = $hasLoc && $hasAct ? 'Location + Activity' : ($hasLoc ? 'Location' : 'Activity');

            // Location fields
            $lat       = $loc['latitude']  ?? null;
            $lng       = $loc['longitude'] ?? null;
            $hasCoords = ($lat !== null && $lat !== '' && $lng !== null && $lng !== '');
            $accuracy  = isset($loc['accuracy']) ? (int)$loc['accuracy'] : null;
            $speed     = isset($loc['speed']) ? round((float)$loc['speed']*3.6,1) : (isset($loc['speed_kmh']) ? round((float)$loc['speed_kmh'],1) : null);
            $altitude  = isset($loc['altitude']) ? round((float)$loc['altitude'],1) : null;
            $bearing   = isset($loc['bearing']) ? round((float)$loc['bearing'],0) : null;
            $provider  = $loc['provider'] ?? null;
            $locStatus = $loc['status'] ?? 'success';
            $locModel  = $loc['device_model'] ?? '';
            $locUpload = !empty($loc['created_at']) ? date('M j, H:i', strtotime($loc['created_at'])) : '—';
            $locId     = $loc['counter'] ?? $loc['id'] ?? '';
            $accClass  = $accuracy !== null ? ($accuracy>100?'danger':($accuracy>50?'warning':'success')) : 'secondary';
            $provIcon  = $provider==='gps'?'fa-satellite':($provider==='network'?'fa-wifi':'fa-broadcast-tower');
            $provColor = $provider==='gps'?'primary':($provider==='network'?'info':'secondary');

            // Activity fields
            $aMeta      = $hasAct ? actMeta($act) : ['actType'=>'','icon'=>'','color'=>'','cls'=>''];
            $confidence = $hasAct ? (isset($act['confidence']) ? (int)$act['confidence'] : null) : null;
            $isInter    = $hasAct ? (($act['is_interactive'] ?? 0) == 1) : false;
            $screenOn   = $hasAct ? (($act['screen_on'] ?? 0) == 1) : false;
            $battery    = $hasAct ? (isset($act['battery_level']) ? (int)$act['battery_level'] : null) : null;
            $charging   = $hasAct ? ($act['charging_status'] ?? '') : '';
            $network    = $hasAct ? strtoupper($act['network_type'] ?? '') : '';
            $actModel   = $hasAct ? ($act['device_model'] ?? '') : '';
            $androidVer = $hasAct ? ($act['android_version'] ?? '') : '';
            $info       = $hasAct ? ($act['info'] ?? '') : '';
            $actUpload  = $hasAct && !empty($act['created_at']) ? date('M j, H:i', strtotime($act['created_at'])) : '—';
            $actId      = $act['counter'] ?? $act['id'] ?? '';
            $battColor  = $battery !== null ? ($battery<=15?'danger':($battery<=30?'warning':'success')) : 'secondary';
            $netColor   = 'secondary';
            if (str_contains($network,'WIFI')) $netColor='primary';
            elseif (str_contains($network,'LTE')||str_contains($network,'4G')) $netColor='success';
            elseif (str_contains($network,'3G')) $netColor='info';
            elseif (str_contains($network,'2G')||str_contains($network,'EDGE')) $netColor='warning';

            $searchStr = strtolower(($lat??'').' '.($lng??'').' '.($provider??'').' '.$aMeta['actType'].' '.$locModel.' '.$network.' '.$info);
        ?>
          <div class="tl-item" data-type="<?= $dataType ?>" data-search="<?= esc($searchStr) ?>">
            <div class="tl-icon-col">
              <div class="tl-icon <?= $iconClass ?>" title="<?= $iconTitle ?>"><i class="fas <?= $iconIcon ?>"></i></div>
            </div>
            <div class="tl-card <?= $iconClass ?>">

              <!-- Card header bar -->
              <div class="card-header-bar">
                <div class="d-flex align-items-center" style="gap:8px;">
                  <span class="badge badge-light border">
                    <i class="fas <?= $hasLoc ? 'fa-map-marker-alt' : 'fa-running' ?> mr-1"></i>
                    <?= $hasLoc && $hasAct ? 'Location + Activity' : ($hasLoc ? 'Location' : 'Activity') ?>
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
                  <?php if ($hasLoc && $locId !== ''): ?>
                    <button class="btn btn-sm btn-outline-danger py-0 px-2 delete-row"
                      data-type="location" data-id="<?= $locId ?>"
                      data-url="<?= base_url('location/delete') ?>" title="Delete location">
                      <i class="fas fa-map-marker-alt"></i><i class="fas fa-times ml-1"></i>
                    </button>
                  <?php endif; ?>
                  <?php if ($hasAct && $actId !== ''): ?>
                    <button class="btn btn-sm btn-outline-danger py-0 px-2 delete-row"
                      data-type="activity" data-id="<?= $actId ?>"
                      data-url="<?= base_url('activities/delete') ?>" title="Delete activity">
                      <i class="fas fa-running"></i><i class="fas fa-times ml-1"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </div>

              <div class="card-body-sections">

                <!-- ── LEFT: Location ────────────────────────────────────── -->
                <?php if ($hasLoc): ?>
                <div class="card-section">
                  <div class="card-section-title"><i class="fas fa-map-marker-alt text-primary mr-1"></i>Location</div>

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
                <?php else: ?>
                <div class="card-section">
                  <div class="card-section-title"><i class="fas fa-map-marker-alt text-primary mr-1"></i>Location</div>
                  <div class="text-muted" style="font-size:12px;"><i class="fas fa-minus-circle mr-1"></i>No location recorded for this event</div>
                </div>
                <?php endif; ?>

                <!-- ── RIGHT: Activity ───────────────────────────────────── -->
                <?php if ($hasAct): ?>
                <div class="card-section">
                  <div class="card-section-title"><i class="fas fa-<?= $aMeta['icon'] ?> text-<?= $aMeta['color'] ?> mr-1"></i>Activity</div>

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
                <?php else: ?>
                <div class="card-section">
                  <div class="card-section-title"><i class="fas fa-running text-success mr-1"></i>Activity</div>
                  <div class="text-muted" style="font-size:12px;"><i class="fas fa-minus-circle mr-1"></i>No activity state for this event</div>
                </div>
                <?php endif; ?>

              </div><!-- /.card-body-sections -->
            </div><!-- /.tl-card -->
          </div><!-- /.tl-item -->
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
        var show = (val === 'all') || (el.dataset.type === val);
        el.style.display = show ? '' : 'none';
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
