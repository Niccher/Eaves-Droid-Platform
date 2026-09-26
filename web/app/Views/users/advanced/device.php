<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
// Deduplicate by android_id + device_model + android_version (DB unique key)
$seen   = [];
$unique = [];
foreach ($rows as $r) {
    $key = ($r['android_id'] ?? 'x') . '_' . ($r['device_model'] ?? 'x') . '_' . ($r['android_version'] ?? 'x');
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $unique[]   = $r;
    }
}
$rows = $unique;
$parseJson = fn($v) => is_string($v) ? (json_decode($v, true) ?: []) : (is_array($v) ? $v : []);

// Android SDK → version name map
$sdkNames = [
    21=>'5.0 Lollipop',22=>'5.1 Lollipop MR1',23=>'6.0 Marshmallow',
    24=>'7.0 Nougat',25=>'7.1 Nougat MR1',26=>'8.0 Oreo',27=>'8.1 Oreo MR1',
    28=>'9 Pie',29=>'10 Q',30=>'11 R',31=>'12 S',32=>'12L S_V2',
    33=>'13 Tiramisu',34=>'14 UpsideDownCake',35=>'15 VanillaIceCream',
];

function fmtMb(int $mb): string {
    if ($mb <= 0) return '—';
    if ($mb >= 1024) return round($mb/1024, 1) . ' GB';
    return $mb . ' MB';
}
function fmtGb(int $gb): string { return $gb > 0 ? $gb . ' GB' : '—'; }
function sdkColor(int $sdk): string {
    if ($sdk >= 33) return 'success';
    if ($sdk >= 30) return 'primary';
    if ($sdk >= 28) return 'info';
    if ($sdk >= 26) return 'warning';
    return 'danger';
}
function fmtEpoch($ts): string {
    $v = (int)$ts;
    if ($v <= 0) return '—';
    if ($v > 9999999999) $v = (int)($v / 1000); // millis → seconds
    return date('Y-m-d H:i', $v);
}
?>

<style>
.dev-card       { border-radius:8px; background:#fff; box-shadow:0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2); }
.dev-hero       { background:linear-gradient(135deg,#6c757d 0%,#5a6268 60%,#495057 100%); border-radius:8px 8px 0 0; padding:20px 22px 16px; color:#fff; position:relative; overflow:hidden; }
.dev-hero::before { content:''; position:absolute; top:-40px; right:-40px; width:140px; height:140px; border-radius:50%; background:rgba(255,255,255,.04); }
.dev-model      { font-size:20px; font-weight:900; }
.dev-brand      { font-size:12px; opacity:.65; text-transform:uppercase; letter-spacing:1px; }
.dev-kv         { display:flex; justify-content:space-between; align-items:center; padding:5px 0; border-bottom:1px solid #f0f0f0; font-size:13px; }
.dev-kv:last-child { border-bottom:none; }
.dev-kv .dk     { color:#6c757d; font-weight:600; font-size:11px; text-transform:uppercase; }
.dev-kv .dv     { font-weight:700; color:#343a40; max-width:60%; text-align:right; word-break:break-all; }
.threat-banner  { background:#dc3545; color:#fff; border-radius:5px; padding:7px 12px; font-size:12px; font-weight:700; margin-bottom:8px; display:flex; align-items:center; gap:8px; }
.abi-pill       { background:#e9ecef; border-radius:12px; padding:2px 8px; font-size:11px; font-weight:600; color:#495057; margin:2px; display:inline-block; font-family:monospace; }
.bar-section    { background:#f8f9fa; border-radius:6px; padding:10px; margin-top:8px; }
.storage-bar    { height:8px; border-radius:4px; background:#dee2e6; overflow:hidden; margin:4px 0; }
.storage-bar-fill { height:100%; border-radius:4px; transition:width .4s; }
.section-label  { font-size:10px; font-weight:700; text-transform:uppercase; color:#6c757d; letter-spacing:.4px; margin-bottom:4px; }
.fingerprint-mono { font-family:monospace; font-size:10px; color:#6c757d; word-break:break-all; margin-top:4px; background:#f0f0f0; padding:4px 6px; border-radius:4px; }
/* Display grid */
.disp-grid      { display:grid; grid-template-columns:repeat(3, 1fr); gap:6px; margin-top:6px; }
.disp-cell      { background:#f8f9fa; border-radius:5px; padding:6px 8px; text-align:center; }
.disp-cell .dc-v { font-size:15px; font-weight:900; color:#343a40; }
.disp-cell .dc-l { font-size:9px; font-weight:600; text-transform:uppercase; color:#868e96; }
/* App timeline */
.app-timeline   { position:relative; padding-left:18px; margin-top:6px; }
.app-timeline::before { content:''; position:absolute; left:6px; top:4px; bottom:4px; width:2px; background:#dee2e6; border-radius:1px; }
.app-tl-item    { position:relative; margin-bottom:6px; font-size:11px; }
.app-tl-item::before { content:''; position:absolute; left:-15px; top:5px; width:8px; height:8px; border-radius:50%; background:#17a2b8; border:2px solid #fff; }
.app-tl-item .tl-label { font-size:9px; font-weight:700; text-transform:uppercase; color:#6c757d; }
.app-tl-item .tl-val   { font-weight:700; color:#343a40; }
/* Raw JSON panels */
.raw-json-panel { background:#f8f9fa; border:1px solid #e9ecef; border-radius:6px; padding:12px; margin:8px 16px 12px; }
.raw-json-panel summary { cursor:pointer; font-weight:700; font-size:12px; text-transform:uppercase; color:#6c757d; letter-spacing:.3px; }
.raw-json-panel summary::-webkit-details-marker { color:#adb5bd; }
.raw-json-kv { display:flex; justify-content:space-between; padding:3px 0; border-bottom:1px solid #e9ecef; font-size:12px; }
.raw-json-kv:last-child { border-bottom:none; }
.raw-json-kv .rk { color:#868e96; font-weight:600; font-size:10px; text-transform:uppercase; }
.raw-json-kv .rv { font-weight:700; color:#495057; text-align:right; max-width:65%; word-break:break-all; font-size:11px; }
.feat-pill { display:inline-block; background:#e9ecef; border-radius:10px; padding:1px 7px; font-size:10px; color:#495057; margin:2px; font-family:monospace; }
.raw-scroll { max-height:180px; overflow-y:auto; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-mobile-alt text-primary mr-2"></i>Device Hardware Profile</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Unique Devices: <b><?= count($rows) ?></b></span>
            <?php $rooted = array_filter($rows, fn($r) => !empty($r['is_rooted'])); ?>
            <?php if (!empty($rooted)): ?>
            <span class="badge badge-danger border p-2 ml-1"><i class="fas fa-skull-crossbones mr-1"></i><?= count($rooted) ?> Rooted!</span>
            <?php endif; ?>
            <?php $emulators = array_filter($rows, fn($r) => !empty($r['is_emulator'])); ?>
            <?php if (!empty($emulators)): ?>
            <span class="badge badge-warning text-dark border p-2 ml-1"><i class="fas fa-desktop mr-1"></i><?= count($emulators) ?> Emulator(s)</span>
            <?php endif; ?>
          </div>
          <p class="text-muted mt-1 mb-0">Hardware specs, Android build, CPU, memory, storage, display, and security flags</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>

      <!-- Callout matching Biometric standard -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-mobile-alt mr-2"></i>Device Hardware &amp; Sandbox Verification Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Audits target build attributes, board codes, display hardware, and active runtime variables. Correlating build fingerprints with root state flags exposes synthetic sandbox environments or tampered firmware.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Build &amp; Identity:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Build Fingerprint:</b> Unique signature combining brand, product, device, and version.</li>
              <li><b>Security Patch:</b> Validates the OS is up-to-date against CVE databases.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Hardware &amp; Display:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>CPU/ABI:</b> Verifies instruction set matches physical silicon (detects emulators).</li>
              <li><b>Display DPI:</b> Screen density metrics that distinguish real hardware from virtual displays.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Network &amp; Extraction:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>IMEI/MEID:</b> Hardware radio identifiers for SIM-based tracking verification.</li>
              <li><b>App Install Timeline:</b> Monitors extractor installation and update timestamps.</li>
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
          <i class="fas fa-mobile-alt fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Device Data</h4>
          <p class="text-muted">Device profiles will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <?php foreach ($rows as $r):
          $rid        = $r['id'] ?? 0;
          $model      = $r['device_model'] ?? '—';
          $brand      = strtoupper($r['device_brand'] ?? '');
          $mfr        = $r['device_manufacturer'] ?? '—';
          $product    = $r['device_product'] ?? '—';
          $device     = $r['device_device'] ?? '—';
          $board      = $r['device_board'] ?? '—';
          $hw         = $r['device_hardware'] ?? '—';
          $avVer      = $r['android_version'] ?? '—';
          $sdk        = (int)($r['android_sdk_int'] ?? 0);
          $codename   = $r['android_codename'] ?? '—';
          $incr       = $r['android_incremental'] ?? '—';
          $baseOs     = $r['android_base_os'] ?? '—';
          $secPatch   = $r['android_security_patch'] ?? '—';
          $buildId    = $r['build_id'] ?? '—';
          $buildType  = $r['build_type'] ?? '—';
          $buildTags  = $r['build_tags'] ?? '—';
          $buildFp    = $r['build_fingerprint'] ?? '—';
          $buildUser  = $r['build_user'] ?? '—';
          $buildHost  = $r['build_host'] ?? '—';
          $buildDisplay= $r['build_display'] ?? '—';
          $buildTime  = $r['build_time'] ?? 0;
          $androidId  = $r['android_id'] ?? '—';
          $cpuCores   = (int)($r['cpu_cores'] ?? 0);
          $cpuAbi     = $r['cpu_abi'] ?? '—';
          $cpuAbis    = array_filter(array_map('trim', explode(',', $r['cpu_abis'] ?? '')));
          $memTotal   = (int)($r['memory_total_mb'] ?? 0);
          $memFree    = (int)($r['memory_free_mb'] ?? 0);
          $memUsed    = $memTotal - $memFree;
          $memPct     = $memTotal > 0 ? round($memUsed / $memTotal * 100) : 0;
          $memPctColor= $memPct > 85 ? '#dc3545' : ($memPct > 65 ? '#ffc107' : '#28a745');
          $intTotal   = (int)($r['internal_storage_total_gb'] ?? 0);
          $intFree    = (int)($r['internal_storage_free_gb'] ?? 0);
          $intUsed    = $intTotal - $intFree;
          $intPct     = $intTotal > 0 ? round($intUsed / $intTotal * 100) : 0;
          $intColor   = $intPct > 90 ? '#dc3545' : ($intPct > 75 ? '#ffc107' : '#007bff');
          $extTotal   = (int)($r['external_storage_total_gb'] ?? 0);
          $extFree    = (int)($r['external_storage_free_gb'] ?? 0);
          $extPct     = $extTotal > 0 ? round(($extTotal-$extFree)/$extTotal*100) : 0;
          $phone      = $r['phone_number'] ?? '—';
          $simOp      = $r['sim_operator'] ?? '—';
          $netOp      = $r['network_operator'] ?? '—';
          $simCty     = strtoupper($r['sim_country'] ?? '');
          $netCty     = strtoupper($r['network_country'] ?? '');
          $imei       = $r['imei'] ?? '—';
          $meid       = $r['meid'] ?? '—';
          $devId      = $r['device_id'] ?? '—';
          $simState   = $r['sim_state'] ?? '—';
          $lang       = $r['language'] ?? '—';
          $country    = $r['country'] ?? '—';
          $tz         = $r['timezone'] ?? '—';
          $tzOff      = (int)($r['timezone_offset'] ?? 0);
          $batLvl     = (float)($r['battery_level'] ?? 0);
          $batChg     = !empty($r['battery_charging']);
          $sensorCnt  = (int)($r['sensor_count'] ?? 0);
          $mac        = $r['mac_address'] ?? '—';
          $kernel     = $r['kernel_info'] ?? '—';
          $isEmulator = !empty($r['is_emulator']);
          $isRooted   = !empty($r['is_rooted']);
          $appPkg     = $r['app_package'] ?? '—';
          $appVer     = $r['app_version'] ?? '—';
          $appVerCode = $r['app_version_code'] ?? '';
          $appInst    = $r['app_first_install'] ?? 0;
          $appUpd     = $r['app_last_update'] ?? 0;
          $extTs      = $r['extraction_timestamp'] ?? 0;
          $extractorV = $r['extractor_version'] ?? '—';
          $curTimeFmt = $r['current_time_formatted'] ?? '';
          // Display metrics
          $dWidth     = (int)($r['display_width'] ?? 0);
          $dHeight    = (int)($r['display_height'] ?? 0);
          $dDensity   = (float)($r['display_density'] ?? 0);
          $dDpi       = (int)($r['display_density_dpi'] ?? 0);
          $dScaled    = (float)($r['display_scaled_density'] ?? 0);
          $dXdpi      = (float)($r['display_xdpi'] ?? 0);
          $dYdpi      = (float)($r['display_ydpi'] ?? 0);
          // Raw JSON
          $rawJson    = $parseJson($r['raw_device_json'] ?? '{}');
          $sdkName    = $sdkNames[$sdk] ?? "API $sdk";
          $sdkColor   = sdkColor($sdk);
          $batColor   = $batLvl > 50 ? '#28a745' : ($batLvl > 20 ? '#ffc107' : '#dc3545');
          // DPI bucket
          $dpiBucket  = $dDpi >= 560 ? 'xxxhdpi' : ($dDpi >= 400 ? 'xxhdpi' : ($dDpi >= 320 ? 'xhdpi' : ($dDpi >= 240 ? 'hdpi' : ($dDpi >= 160 ? 'mdpi' : 'ldpi'))));
          ?>

          <div class="card dev-card mb-4">
            <!-- Hero -->
            <div class="dev-hero">
              <div class="d-flex align-items-start">
                <div class="flex-grow-1">
                  <?php if ($isRooted || $isEmulator): ?>
                  <div class="d-flex flex-wrap gap-1 mb-2">
                    <?php if ($isRooted): ?>
                    <span class="badge badge-danger"><i class="fas fa-skull-crossbones mr-1"></i>ROOTED</span>
                    <?php endif; ?>
                    <?php if ($isEmulator): ?>
                    <span class="badge badge-warning text-dark"><i class="fas fa-desktop mr-1"></i>EMULATOR</span>
                    <?php endif; ?>
                  </div>
                  <?php endif; ?>
                  <div class="dev-brand"><?= esc($brand) ?></div>
                  <div class="dev-model"><?= esc($model) ?></div>
                  <div style="font-size:12px;opacity:.6;margin-top:2px;"><?= esc($mfr) ?> · <?= esc($product) ?> · <?= esc($hw) ?></div>
                  <div style="font-size:11px;opacity:.5;margin-top:4px;font-family:monospace;"><?= esc($androidId) ?></div>
                </div>
                <div class="ml-3 text-right">
                  <!-- Android version ring -->
                  <?php
                  $ringPct = min(100, ($sdk / 35) * 100);
                  $circ = 2 * pi() * 22;
                  $dashOffset = $circ - ($ringPct / 100) * $circ;
                  $ringColors = ['success'=>'#28a745','primary'=>'#007bff','info'=>'#17a2b8','warning'=>'#ffc107','danger'=>'#dc3545'];
                  $rc = $ringColors[$sdkColor] ?? '#6c757d';
                  ?>
                  <svg width="60" height="60" viewBox="0 0 60 60">
                    <circle cx="30" cy="30" r="22" fill="none" stroke="rgba(255,255,255,.15)" stroke-width="5"/>
                    <circle cx="30" cy="30" r="22" fill="none" stroke="<?= $rc ?>" stroke-width="5"
                      stroke-dasharray="<?= $circ ?>" stroke-dashoffset="<?= $dashOffset ?>"
                      transform="rotate(-90 30 30)"/>
                    <text x="30" y="27" text-anchor="middle" fill="#fff" style="font-size:10px;font-weight:900;"><?= $avVer ?></text>
                    <text x="30" y="38" text-anchor="middle" fill="rgba(255,255,255,.6)" style="font-size:7px;">Android</text>
                  </svg>
                  <div><span class="badge badge-<?= $sdkColor ?>" style="font-size:9px;">API <?= $sdk ?></span></div>
                </div>
              </div>
            </div>

            <div class="card-body">
              <div class="row">

                <!-- Col 1: Build & Android -->
                <div class="col-lg-3 col-md-6 mb-3">
                  <div class="section-label"><i class="fab fa-android mr-1"></i>Android Build</div>
                  <div class="dev-kv"><span class="dk">Version</span><span class="dv"><?= esc($sdkName) ?></span></div>
                  <div class="dev-kv"><span class="dk">Security Patch</span><span class="dv"><?= esc($secPatch) ?></span></div>
                  <div class="dev-kv"><span class="dk">Build ID</span><span class="dv"><code style="font-size:11px;"><?= esc($buildId) ?></code></span></div>
                  <div class="dev-kv"><span class="dk">Build Type</span><span class="dv"><?= esc($buildType) ?></span></div>
                  <div class="dev-kv"><span class="dk">Build Tags</span><span class="dv" style="font-size:11px;"><?= esc($buildTags) ?></span></div>
                  <div class="dev-kv"><span class="dk">Build Display</span><span class="dv" style="font-size:11px;"><?= esc($buildDisplay) ?></span></div>
                  <div class="dev-kv"><span class="dk">Codename</span><span class="dv"><?= esc($codename) ?></span></div>
                  <div class="dev-kv"><span class="dk">Incremental</span><span class="dv" style="font-size:11px;"><?= esc($incr) ?></span></div>
                  <?php if ($baseOs && $baseOs !== '—' && $baseOs !== ''): ?>
                  <div class="dev-kv"><span class="dk">Base OS</span><span class="dv" style="font-size:11px;"><?= esc($baseOs) ?></span></div>
                  <?php endif; ?>
                  <div class="dev-kv"><span class="dk">Build User@Host</span><span class="dv" style="font-size:11px;"><?= esc($buildUser) ?>@<?= esc($buildHost) ?></span></div>
                  <?php if ($buildTime > 0): ?>
                  <div class="dev-kv"><span class="dk">Build Time</span><span class="dv" style="font-size:11px;"><?= fmtEpoch($buildTime) ?></span></div>
                  <?php endif; ?>
                  <?php if ($buildFp && $buildFp !== '—'): ?>
                  <div class="fingerprint-mono"><?= esc($buildFp) ?></div>
                  <?php endif; ?>
                </div>

                <!-- Col 2: CPU & Memory & Storage -->
                <div class="col-lg-3 col-md-6 mb-3">
                  <div class="section-label"><i class="fas fa-microchip mr-1"></i>CPU & Hardware</div>
                  <div class="dev-kv"><span class="dk">Board</span><span class="dv"><?= esc($board) ?></span></div>
                  <div class="dev-kv"><span class="dk">Hardware</span><span class="dv"><?= esc($hw) ?></span></div>
                  <div class="dev-kv"><span class="dk">Device Code</span><span class="dv"><code><?= esc($device) ?></code></span></div>
                  <div class="dev-kv"><span class="dk">CPU Cores</span><span class="dv"><?= $cpuCores > 0 ? $cpuCores . ' cores' : '—' ?></span></div>
                  <div class="dev-kv"><span class="dk">Primary ABI</span><span class="dv"><code><?= esc($cpuAbi) ?></code></span></div>
                  <?php if (!empty($cpuAbis)): ?>
                  <div class="dev-kv"><span class="dk">Supported ABIs</span><span class="dv"><?php foreach($cpuAbis as $a): ?><span class="abi-pill"><?= esc($a) ?></span><?php endforeach; ?></span></div>
                  <?php endif; ?>

                  <div class="bar-section mt-3">
                    <div class="section-label">RAM</div>
                    <div class="d-flex justify-content-between" style="font-size:12px;font-weight:700;">
                      <span><?= fmtMb($memUsed) ?> used</span><span><?= fmtMb($memTotal) ?> total</span>
                    </div>
                    <div class="storage-bar"><div class="storage-bar-fill" style="width:<?= $memPct ?>%;background:<?= $memPctColor ?>;"></div></div>
                    <div style="font-size:11px;color:#6c757d;"><?= $memPct ?>% used · <?= fmtMb($memFree) ?> free</div>
                  </div>

                  <div class="bar-section mt-2">
                    <div class="section-label">Internal Storage</div>
                    <div class="d-flex justify-content-between" style="font-size:12px;font-weight:700;">
                      <span><?= fmtGb($intUsed) ?> used</span><span><?= fmtGb($intTotal) ?> total</span>
                    </div>
                    <div class="storage-bar"><div class="storage-bar-fill" style="width:<?= $intPct ?>%;background:<?= $intColor ?>;"></div></div>
                    <div style="font-size:11px;color:#6c757d;"><?= $intPct ?>% · Free: <?= fmtGb($intFree) ?><?= !empty($r['internal_storage_usable_gb']) ? ' · Usable: '.fmtGb((int)$r['internal_storage_usable_gb']) : '' ?></div>
                  </div>

                  <?php if ($extTotal > 0): ?>
                  <div class="bar-section mt-2">
                    <div class="section-label">External Storage</div>
                    <div class="storage-bar"><div class="storage-bar-fill" style="width:<?= $extPct ?>%;background:#17a2b8;"></div></div>
                    <div style="font-size:11px;color:#6c757d;"><?= $extPct ?>% · Free: <?= fmtGb($extFree) ?> / <?= fmtGb($extTotal) ?></div>
                  </div>
                  <?php endif; ?>

                  <!-- Battery -->
                  <div class="bar-section mt-2">
                    <div class="section-label"><i class="fas fa-battery-<?= $batLvl > 75 ? 'full' : ($batLvl > 50 ? 'three-quarters' : ($batLvl > 25 ? 'half' : 'quarter')) ?> mr-1"></i>Battery <?= $batChg ? '<span class="badge badge-success" style="font-size:9px;">Charging</span>' : '' ?></div>
                    <div class="storage-bar"><div class="storage-bar-fill" style="width:<?= $batLvl ?>%;background:<?= $batColor ?>;"></div></div>
                    <div style="font-size:11px;color:#6c757d;"><?= round($batLvl) ?>%</div>
                  </div>
                </div>

                <!-- Col 3: Display & Locale -->
                <div class="col-lg-3 col-md-6 mb-3">
                  <?php if ($dWidth > 0 || $dHeight > 0): ?>
                  <div class="section-label"><i class="fas fa-tv mr-1"></i>Display Hardware</div>
                  <div class="dev-kv"><span class="dk">Resolution</span><span class="dv"><code><?= $dWidth ?>×<?= $dHeight ?></code></span></div>
                  <?php if ($dDpi > 0): ?>
                  <div class="dev-kv"><span class="dk">Density DPI</span><span class="dv"><?= $dDpi ?> <span class="abi-pill"><?= $dpiBucket ?></span></span></div>
                  <?php endif; ?>
                  <div class="disp-grid">
                    <?php if ($dDensity > 0): ?>
                    <div class="disp-cell"><div class="dc-v"><?= round($dDensity, 2) ?></div><div class="dc-l">Density</div></div>
                    <?php endif; ?>
                    <?php if ($dScaled > 0): ?>
                    <div class="disp-cell"><div class="dc-v"><?= round($dScaled, 2) ?></div><div class="dc-l">Scaled</div></div>
                    <?php endif; ?>
                    <?php if ($dXdpi > 0): ?>
                    <div class="disp-cell"><div class="dc-v"><?= round($dXdpi, 1) ?></div><div class="dc-l">X DPI</div></div>
                    <?php endif; ?>
                    <?php if ($dYdpi > 0): ?>
                    <div class="disp-cell"><div class="dc-v"><?= round($dYdpi, 1) ?></div><div class="dc-l">Y DPI</div></div>
                    <?php endif; ?>
                    <?php if ($dWidth > 0 && $dHeight > 0): ?>
                    <?php $aspect = $dHeight > 0 ? round($dWidth / $dHeight, 2) : 0; ?>
                    <div class="disp-cell"><div class="dc-v"><?= $aspect ?></div><div class="dc-l">Aspect</div></div>
                    <?php endif; ?>
                    <?php if ($dWidth > 0 && $dDpi > 0): ?>
                    <?php $diagIn = round(sqrt($dWidth*$dWidth + $dHeight*$dHeight) / $dDpi, 1); ?>
                    <div class="disp-cell"><div class="dc-v"><?= $diagIn ?>"</div><div class="dc-l">Diagonal</div></div>
                    <?php endif; ?>
                  </div>
                  <?php endif; ?>

                  <div class="section-label mt-3"><i class="fas fa-globe-americas mr-1"></i>Locale & Time</div>
                  <div class="dev-kv"><span class="dk">Language</span><span class="dv"><?= esc($lang) ?> / <?= esc($country) ?></span></div>
                  <div class="dev-kv"><span class="dk">Timezone</span><span class="dv" style="font-size:11px;"><?= esc($tz) ?> <?= $tzOff !== 0 ? '(UTC'.($tzOff>=0?'+':'').$tzOff.')' : '' ?></span></div>
                  <?php if ($curTimeFmt): ?>
                  <div class="dev-kv"><span class="dk">Device Time</span><span class="dv" style="font-size:11px;"><?= esc($curTimeFmt) ?></span></div>
                  <?php endif; ?>
                  <div class="dev-kv"><span class="dk">Sensors</span><span class="dv"><?= $sensorCnt > 0 ? '<span class="badge badge-info" style="font-size:10px;">'.$sensorCnt.' sensors</span>' : '—' ?></span></div>

                  <?php if ($kernel && $kernel !== '—'): ?>
                  <div class="section-label mt-3"><i class="fas fa-terminal mr-1"></i>Kernel</div>
                  <div class="fingerprint-mono"><?= esc($kernel) ?></div>
                  <?php endif; ?>
                </div>

                <!-- Col 4: Network, Identity & App -->
                <div class="col-lg-3 col-md-6 mb-3">
                  <div class="section-label"><i class="fas fa-sim-card mr-1"></i>Network & Identity</div>
                  <?php if ($imei && $imei !== '—'): ?>
                  <div class="dev-kv"><span class="dk">IMEI</span><span class="dv"><code style="font-size:11px;"><?= esc($imei) ?></code></span></div>
                  <?php endif; ?>
                  <?php if ($meid && $meid !== '—'): ?>
                  <div class="dev-kv"><span class="dk">MEID</span><span class="dv"><code style="font-size:11px;"><?= esc($meid) ?></code></span></div>
                  <?php endif; ?>
                  <?php if ($devId && $devId !== '—'): ?>
                  <div class="dev-kv"><span class="dk">Device ID</span><span class="dv"><code style="font-size:11px;"><?= esc($devId) ?></code></span></div>
                  <?php endif; ?>
                  <?php if ($phone && $phone !== '—'): ?>
                  <div class="dev-kv"><span class="dk">Phone Number</span><span class="dv"><?= esc($phone) ?></span></div>
                  <?php endif; ?>
                  <div class="dev-kv"><span class="dk">SIM Operator</span><span class="dv"><?= esc($simOp) ?></span></div>
                  <div class="dev-kv"><span class="dk">Network Operator</span><span class="dv"><?= esc($netOp) ?></span></div>
                  <div class="dev-kv"><span class="dk">SIM Country</span><span class="dv"><?= esc($simCty) ?><?= ($netCty && $netCty !== $simCty) ? ' / <small class="text-muted">Net: '.esc($netCty).'</small>' : '' ?></span></div>
                  <div class="dev-kv"><span class="dk">SIM State</span><span class="dv"><?= esc($simState) ?></span></div>
                  <div class="dev-kv"><span class="dk">MAC Address</span><span class="dv"><code style="font-size:11px;"><?= esc($mac) ?></code></span></div>

                  <!-- App & Extraction Timeline -->
                  <div class="section-label mt-3"><i class="fas fa-box-open mr-1"></i>Extractor App</div>
                  <div class="dev-kv"><span class="dk">Package</span><span class="dv" style="font-size:11px;"><?= esc($appPkg) ?></span></div>
                  <div class="dev-kv"><span class="dk">Version</span><span class="dv"><?= esc($appVer) ?><?= $appVerCode ? ' <small class="text-muted">(code '.$appVerCode.')</small>' : '' ?></span></div>
                  <?php if ($extractorV && $extractorV !== '—'): ?>
                  <div class="dev-kv"><span class="dk">Extractor SDK</span><span class="dv">v<?= esc($extractorV) ?></span></div>
                  <?php endif; ?>

                  <div class="app-timeline">
                    <div class="app-tl-item">
                      <div class="tl-label">First Installed</div>
                      <div class="tl-val"><?= fmtEpoch($appInst) ?></div>
                    </div>
                    <div class="app-tl-item">
                      <div class="tl-label">Last Updated</div>
                      <div class="tl-val"><?= fmtEpoch($appUpd) ?></div>
                    </div>
                    <div class="app-tl-item" style="margin-bottom:0;">
                      <div class="tl-label">Data Extracted</div>
                      <div class="tl-val"><?= $extTs > 0 ? format_timestamp_display($extTs) : '—' ?></div>
                    </div>
                  </div>
                </div>

              </div>
            </div>

            <!-- Raw JSON Expandable Panel -->
            <?php if (!empty($rawJson)): ?>
            <details class="raw-json-panel">
              <summary><i class="fas fa-database mr-1"></i>Raw Device Properties (<?= count($rawJson) ?> keys)</summary>
              <div class="row mt-3">
                <?php
                // Extract structured sections from raw JSON
                $sysprops = $rawJson['system_properties'] ?? $rawJson['build_props'] ?? $rawJson['systemProperties'] ?? [];
                if (empty($sysprops)) {
                    foreach ($rawJson as $k => $v) {
                        if (is_string($v) && (str_starts_with($k, 'ro.') || str_starts_with($k, 'persist.') || str_starts_with($k, 'sys.') || str_starts_with($k, 'gsm.'))) {
                            $sysprops[$k] = $v;
                        }
                    }
                }
                $secProviders = $rawJson['security_providers'] ?? $rawJson['securityProviders'] ?? [];
                $sensors      = $rawJson['sensors'] ?? $rawJson['sensor_list'] ?? $rawJson['sensorList'] ?? [];
                $features     = $rawJson['features'] ?? $rawJson['system_features'] ?? $rawJson['systemFeatures'] ?? [];
                $locales      = $rawJson['supported_locales'] ?? $rawJson['locales'] ?? [];
                $sharedLibs   = $rawJson['shared_libraries'] ?? $rawJson['sharedLibraries'] ?? [];
                $inputDevs    = $rawJson['input_devices'] ?? $rawJson['inputDevices'] ?? [];
                $accounts     = $rawJson['accounts'] ?? [];
                ?>

                <?php if (!empty($sysprops)): ?>
                <div class="col-md-4 mb-3">
                  <div class="section-label"><i class="fas fa-cog mr-1"></i>System Properties (<?= count($sysprops) ?>)</div>
                  <div class="raw-scroll">
                    <?php foreach (array_slice($sysprops, 0, 30, true) as $pk => $pv): ?>
                    <div class="raw-json-kv"><span class="rk"><?= esc($pk) ?></span><span class="rv"><?= esc(is_string($pv) ? $pv : json_encode($pv)) ?></span></div>
                    <?php endforeach; ?>
                    <?php if (count($sysprops) > 30): ?>
                    <div class="text-center text-muted" style="font-size:10px;padding:4px;">… and <?= count($sysprops) - 30 ?> more</div>
                    <?php endif; ?>
                  </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($secProviders)): ?>
                <div class="col-md-4 mb-3">
                  <div class="section-label"><i class="fas fa-shield-alt mr-1"></i>Security Providers (<?= count($secProviders) ?>)</div>
                  <div class="raw-scroll">
                    <?php foreach ($secProviders as $sp): ?>
                    <div class="feat-pill"><?= esc(is_string($sp) ? $sp : ($sp['name'] ?? json_encode($sp))) ?></div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($sensors)): ?>
                <div class="col-md-4 mb-3">
                  <div class="section-label"><i class="fas fa-broadcast-tower mr-1"></i>Sensor Hardware (<?= count($sensors) ?>)</div>
                  <div class="raw-scroll">
                    <?php foreach ($sensors as $sn): ?>
                    <div class="raw-json-kv">
                      <span class="rk"><?= esc(is_string($sn) ? $sn : ($sn['name'] ?? ($sn['sensor_name'] ?? '?'))) ?></span>
                      <span class="rv"><?= esc(is_string($sn) ? '' : ($sn['type'] ?? ($sn['vendor'] ?? ($sn['sensor_type'] ?? '')))) ?></span>
                    </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($features)): ?>
                <div class="col-md-6 mb-2">
                  <div class="section-label"><i class="fas fa-puzzle-piece mr-1"></i>System Features <span class="badge badge-secondary" style="font-size:9px;"><?= count($features) ?></span></div>
                  <div class="raw-scroll" style="max-height:120px;">
                    <?php foreach ($features as $ft): ?>
                    <span class="feat-pill"><?= esc(is_string($ft) ? $ft : ($ft['name'] ?? json_encode($ft))) ?></span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($sharedLibs)): ?>
                <div class="col-md-6 mb-2">
                  <div class="section-label"><i class="fas fa-layer-group mr-1"></i>Shared Libraries <span class="badge badge-secondary" style="font-size:9px;"><?= count($sharedLibs) ?></span></div>
                  <div class="raw-scroll" style="max-height:120px;">
                    <?php foreach ($sharedLibs as $lib): ?>
                    <span class="feat-pill"><?= esc(is_string($lib) ? $lib : ($lib['name'] ?? json_encode($lib))) ?></span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($inputDevs)): ?>
                <div class="col-md-6 mb-2">
                  <div class="section-label"><i class="fas fa-keyboard mr-1"></i>Input Devices (<?= count($inputDevs) ?>)</div>
                  <div class="raw-scroll">
                    <?php foreach ($inputDevs as $idev): ?>
                    <div class="raw-json-kv">
                      <span class="rk"><?= esc(is_string($idev) ? $idev : ($idev['name'] ?? ($idev['device_name'] ?? '?'))) ?></span>
                      <span class="rv"><?= esc(is_string($idev) ? '' : ($idev['descriptor'] ?? ($idev['product_id'] ?? ''))) ?></span>
                    </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($locales)): ?>
                <div class="col-md-6 mb-2">
                  <div class="section-label"><i class="fas fa-language mr-1"></i>Supported Locales <span class="badge badge-secondary" style="font-size:9px;"><?= count($locales) ?></span></div>
                  <div class="raw-scroll" style="max-height:100px;">
                    <?php foreach ($locales as $loc): ?>
                    <span class="feat-pill"><?= esc(is_string($loc) ? $loc : json_encode($loc)) ?></span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <?php if (empty($sysprops) && empty($secProviders) && empty($sensors) && empty($features) && empty($sharedLibs) && empty($inputDevs) && empty($locales)): ?>
                <div class="col-12">
                  <div class="section-label"><i class="fas fa-code mr-1"></i>All Properties (<?= count($rawJson) ?>)</div>
                  <div class="raw-scroll">
                    <?php foreach (array_slice($rawJson, 0, 50, true) as $rk => $rv): ?>
                    <div class="raw-json-kv">
                      <span class="rk"><?= esc($rk) ?></span>
                      <span class="rv"><?= esc(is_string($rv) ? $rv : json_encode($rv)) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($rawJson) > 50): ?>
                    <div class="text-center text-muted" style="font-size:10px;padding:4px;">… and <?= count($rawJson) - 50 ?> more</div>
                    <?php endif; ?>
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </details>
            <?php endif; ?>

            <div class="card-footer d-flex justify-content-between align-items-center py-2">
              <small class="text-muted">Row ID: <?= $rid ?></small>
              <button class="btn btn-sm btn-outline-danger delete-row py-0"
                data-id="<?= $rid ?>"
                data-url="<?= base_url('advanced/hardware/device/delete') ?>">
                <i class="fas fa-trash mr-1"></i>Remove
              </button>
            </div>
          </div>

        <?php endforeach; ?>

        <?php if (isset($pager)): ?>
          <div class="d-flex justify-content-end"><?= $pager->links('default', 'bootstrap5_full') ?></div>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
