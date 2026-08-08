<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
// Deduplicate storage by volume_path — keep the most recent snapshot per mount point.
$seen   = [];
$unique = [];
foreach ($rows as $r) {
    $key = strtolower(trim($r['volume_path'] ?? $r['id'] ?? uniqid()));
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $unique[]   = $r;
    }
}
$rows = $unique;

function fmtBytes(int $bytes): string {
    if ($bytes <= 0) return '—';
    if ($bytes >= 1073741824) return round($bytes/1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return round($bytes/1048576, 1)    . ' MB';
    return round($bytes/1024, 0) . ' KB';
}

// Sort: internal first, then external
usort($rows, fn($a, $b) => ($a['is_removable'] ?? 0) <=> ($b['is_removable'] ?? 0));

$totalBytes = array_sum(array_column($rows, 'total_bytes'));
$usedBytes  = array_sum(array_column($rows, 'used_bytes'));
$freeBytes  = array_sum(array_column($rows, 'available_bytes'));
$overallPct = $totalBytes > 0 ? round($usedBytes / $totalBytes * 100) : 0;
?>

<style>
.stor-card      { border-radius:8px; background:#fff; box-shadow:0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2); }
.stor-hero      { background:linear-gradient(135deg,#1b1b2f 0%,#162447 60%,#1f4068 100%); border-radius:8px 8px 0 0; padding:18px 22px; color:#fff; }
.stor-kv        { display:flex; justify-content:space-between; align-items:center; padding:5px 0; border-bottom:1px solid #f0f0f0; font-size:13px; }
.stor-kv:last-child { border-bottom:none; }
.stor-kv .sk    { color:#6c757d; font-weight:600; font-size:11px; text-transform:uppercase; }
.stor-kv .sv    { font-weight:700; color:#343a40; }
.vol-bar        { height:12px; border-radius:6px; background:#dee2e6; overflow:hidden; margin:6px 0; }
.vol-bar-fill   { height:100%; border-radius:6px; transition:width .4s; background:linear-gradient(90deg,#007bff,#00c6ff); }
.vol-bar-fill.warn  { background:linear-gradient(90deg,#fd7e14,#ffc107); }
.vol-bar-fill.danger { background:linear-gradient(90deg,#dc3545,#e74c3c); }
.path-mono      { font-family:monospace; font-size:11px; background:#f0f4f8; border-radius:4px; padding:3px 7px; color:#2c3e50; }
.summary-ring-wrap { position:relative; width:90px; height:90px; flex-shrink:0; }
.stor-type-pill { border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-hdd text-info mr-2"></i>Storage Volumes</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Volumes: <b><?= count($rows) ?></b></span>
            <?php if ($overallPct > 90): ?>
            <span class="badge badge-danger border p-2 ml-1"><i class="fas fa-exclamation-triangle mr-1"></i>Storage Critical!</span>
            <?php elseif ($overallPct > 75): ?>
            <span class="badge badge-warning text-dark border p-2 ml-1"><i class="fas fa-exclamation mr-1"></i>Storage Low</span>
            <?php endif; ?>
          </div>
          <p class="text-muted mt-1 mb-0">Volume paths, capacity, utilization, mount state</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>

      <!-- Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-hdd mr-2"></i>Storage Volume Security Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Auditing block devices, partition structures, available space capacities, and removable storage mount points.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Mount Point Tracking:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Volume Paths:</b> Traces storage block mount directories on the system.</li>
              <li><b>Volume Labeling:</b> Shows system tags and partition names.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Capacity Analysis:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Formatted Bytes:</b> Computes actual storage space sizing attributes.</li>
              <li><b>Space Utilization:</b> Tracks storage allocation ratios to flag partition filling.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Removable Hardware:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Partition Class:</b> Identifies whether blocks are internal storage or hot-pluggable media cards.</li>
              <li><b>Block State:</b> Verifies current device block connection mounting states.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <?php if (empty($rows)): ?>
        <div class="text-center py-5">
          <i class="fas fa-hdd fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Storage Data</h4>
          <p class="text-muted">Storage volumes will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <!-- Overall summary bar -->
        <?php if ($totalBytes > 0): ?>
        <div class="card stor-card mb-4">
          <div class="stor-hero">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1 mr-3">
                <div style="font-size:13px;opacity:.7;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Total Device Storage Utilization</div>
                <div class="d-flex justify-content-between" style="font-size:13px;font-weight:700;color:rgba(255,255,255,.85);">
                  <span><?= fmtBytes($usedBytes) ?> used</span>
                  <span><?= fmtBytes($totalBytes) ?> total · <?= fmtBytes($freeBytes) ?> free</span>
                </div>
                <div class="vol-bar mt-1" style="height:16px;">
                  <div class="vol-bar-fill <?= $overallPct > 90 ? 'danger' : ($overallPct > 75 ? 'warn' : '') ?>" style="width:<?= $overallPct ?>%;"></div>
                </div>
              </div>
              <!-- Ring -->
              <?php
              $circ = 2 * pi() * 36;
              $dashOff = $circ - ($overallPct / 100) * $circ;
              $ringCol = $overallPct > 90 ? '#dc3545' : ($overallPct > 75 ? '#ffc107' : '#00c6ff');
              ?>
              <div class="summary-ring-wrap">
                <svg width="90" height="90" viewBox="0 0 90 90">
                  <circle cx="45" cy="45" r="36" fill="none" stroke="rgba(255,255,255,.12)" stroke-width="8"/>
                  <circle cx="45" cy="45" r="36" fill="none" stroke="<?= $ringCol ?>" stroke-width="8"
                    stroke-dasharray="<?= $circ ?>" stroke-dashoffset="<?= $dashOff ?>"
                    transform="rotate(-90 45 45)"/>
                  <text x="45" y="41" text-anchor="middle" fill="#fff" style="font-size:14px;font-weight:900;"><?= $overallPct ?>%</text>
                  <text x="45" y="55" text-anchor="middle" fill="rgba(255,255,255,.6)" style="font-size:8px;">USED</text>
                </svg>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Per-volume cards -->
        <div class="row">
        <?php foreach ($rows as $r):
          $rid        = $r['id'] ?? 0;
          $path       = $r['volume_path']        ?? '—';
          $desc       = $r['description']         ?? '';
          $removable  = !empty($r['is_removable']);
          $state      = strtolower($r['state'] ?? 'unknown');
          $totalB     = (int)($r['total_bytes']      ?? 0);
          $availB     = (int)($r['available_bytes']   ?? 0);
          $freeB      = (int)($r['free_bytes']        ?? 0);
          $usedB      = (int)($r['used_bytes']        ?? 0);
          $totalFmt   = $r['total_formatted']     ?? fmtBytes($totalB);
          $availFmt   = $r['available_formatted'] ?? fmtBytes($availB);
          $usedFmt    = $r['used_formatted']      ?? fmtBytes($usedB);
          $freeFmt    = fmtBytes($freeB);
          $pct        = $totalB > 0 ? round($usedB / $totalB * 100) : 0;
          $pctClass   = $pct > 90 ? 'danger' : ($pct > 75 ? 'warn' : '');
          $stateColor = $state === 'mounted' ? 'success' : ($state === 'unmounted' ? 'secondary' : 'warning');
          $ts         = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          ?>
          <div class="col-md-6 col-xl-4 mb-4">
            <div class="stor-card h-100">
              <!-- Header -->
              <div class="card-header d-flex justify-content-between align-items-center py-2">
                <div>
                  <span class="stor-type-pill <?= $removable ? 'badge badge-warning text-dark' : 'badge badge-primary' ?>">
                    <i class="fas <?= $removable ? 'fa-sd-card' : 'fa-hdd' ?> mr-1"></i><?= $removable ? 'Removable' : 'Internal' ?>
                  </span>
                  <span class="badge badge-<?= $stateColor ?> ml-1"><?= ucfirst($state) ?></span>
                </div>
                <button class="btn btn-sm btn-outline-danger delete-row py-0"
                  data-id="<?= $rid ?>"
                  data-url="<?= base_url('advanced/hardware/storage/delete') ?>">
                  <i class="fas fa-trash"></i>
                </button>
              </div>

              <div class="card-body">
                <!-- Path -->
                <div class="mb-3">
                  <div class="section-label text-muted" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;">Mount Point</div>
                  <div class="path-mono"><?= esc($path) ?></div>
                  <?php if ($desc): ?><div style="font-size:12px;color:#6c757d;margin-top:3px;"><?= esc($desc) ?></div><?php endif; ?>
                </div>

                <!-- Utilization bar -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between" style="font-size:12px;font-weight:700;">
                    <span class="text-muted">Used: <strong><?= esc($usedFmt) ?></strong></span>
                    <span class="text-muted">Total: <strong><?= esc($totalFmt) ?></strong></span>
                  </div>
                  <div class="vol-bar mt-1">
                    <div class="vol-bar-fill <?= $pctClass ?>" style="width:<?= $pct ?>%;"></div>
                  </div>
                  <div class="d-flex justify-content-between" style="font-size:11px;color:#6c757d;">
                    <span><?= $pct ?>% used</span>
                    <span><?= esc($availFmt) ?> available</span>
                  </div>
                </div>

                <!-- Detail KVs -->
                <div class="stor-kv"><span class="sk">Total Bytes</span><span class="sv"><?= number_format($totalB) ?></span></div>
                <div class="stor-kv"><span class="sk">Used Bytes</span><span class="sv"><?= number_format($usedB) ?></span></div>
                <div class="stor-kv"><span class="sk">Free Bytes</span><span class="sv"><?= number_format($freeB) ?></span></div>
                <div class="stor-kv"><span class="sk">Available Bytes</span><span class="sv"><?= number_format($availB) ?></span></div>
                <div class="stor-kv"><span class="sk">Extracted</span><span class="sv" style="font-size:11px;"><?= $ts ?></span></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        </div>

        <?php if (isset($pager)): ?>
          <div class="d-flex justify-content-end"><?= $pager->links('default', 'bootstrap5_full') ?></div>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>