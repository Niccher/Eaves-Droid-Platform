<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'device_id',
    ['width_px', 'height_px', 'real_width', 'real_height', 'usable_width', 'usable_height', 'density_dpi', 'refresh_rate', 'mode_refresh_rate', 'mode_width', 'mode_height', 'screen_layout', 'ui_mode', 'xdpi', 'ydpi', 'scaled_density', 'hdr_capable'],
    ['displays', 'displays_json']
);
?>
<?php
// DPI category helper
function dpiCategory(int $dpi): array {
    if ($dpi <= 0)  return ['Unknown', 'secondary'];
    if ($dpi < 160) return ['ldpi',    'danger'];
    if ($dpi < 240) return ['mdpi',    'warning text-dark'];
    if ($dpi < 320) return ['hdpi',    'info'];
    if ($dpi < 480) return ['xhdpi',   'primary'];
    if ($dpi < 640) return ['xxhdpi',  'success'];
    return ['xxxhdpi', 'purple'];
}

// Screen layout decode
$layoutLabels = [1=>'Small',2=>'Normal',3=>'Large',4=>'X-Large'];
$layoutIcons  = [1=>'fa-mobile-alt',2=>'fa-mobile-alt',3=>'fa-tablet-alt',4=>'fa-desktop'];

// UI Mode decode
$uiModeLabels = [0=>'Normal',1=>'Normal',2=>'Desk / Docked',3=>'Car Mode',4=>'TV',5=>'Appliance',6=>'Watch'];
$uiModeIcons  = [0=>'fa-mobile-alt',1=>'fa-mobile-alt',2=>'fa-desktop',3=>'fa-car',4=>'fa-tv',5=>'fa-plug',6=>'fa-clock'];

// Rotation decode
$rotLabels    = [0=>'Portrait (0°)',1=>'Landscape (90°)',2=>'Reverse Portrait (180°)',3=>'Reverse Landscape (270°)'];

// GCD for aspect ratio
function gcd(int $a, int $b): int { return $b === 0 ? $a : gcd($b, $a % $b); }
function aspectRatio(int $w, int $h): string {
    if ($w <= 0 || $h <= 0) return '—';
    $g = gcd($w, $h);
    return ($w/$g) . ':' . ($h/$g);
}
?>

<style>
.disp-card { border-radius:8px; background:#fff; box-shadow:0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2); }
.res-hero { font-size:28px; font-weight:900; color:#343a40; line-height:1.1; }
.res-sub  { font-size:13px; color:#6c757d; }
.dpi-ring { width:90px; height:90px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-direction:column; font-weight:900; font-size:22px; }
.spec-kv { display:flex; justify-content:space-between; align-items:center; padding:5px 0; border-bottom:1px solid #f0f0f0; font-size:13px; }
.spec-kv:last-child { border-bottom:none; }
.spec-kv .sk { color:#6c757d; font-weight:600; font-size:11px; text-transform:uppercase; }
.spec-kv .sv { font-weight:700; color:#343a40; }
.phone-svg { display:block; margin:0 auto; }
.display-card-inner { background:#f8f9fa; border:1px solid #e9ecef; border-radius:6px; padding:10px; margin-bottom:8px; }
.hz-badge-high  { background:#28a745; color:#fff; }
.hz-badge-mid   { background:#007bff; color:#fff; }
.hz-badge-low   { background:#6c757d; color:#fff; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-tv text-secondary mr-2"></i>Display Hardware Profile</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Resolution, DPI, refresh rate, density categories, and connected display inventory</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>

      <!-- Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-tv mr-2"></i>Display Security Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Auditing target screen metrics, resolution density profiles, refresh capabilities, and active connected display controllers.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Screen Metrics:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Logical vs Real Resolution:</b> Identifies screen dimension scaling mismatch.</li>
              <li><b>Aspect Ratio:</b> Determines device silhouette profile parameters.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Display Quality:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>DPI Classifications:</b> Groups pixel densities from ldpi up to xxxhdpi.</li>
              <li><b>Refresh Rates:</b> Highlights dynamic frames (60Hz up to 120Hz+).</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">System Layout:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>UI Mode &amp; Layout:</b> Decodes UI masks (TV, watch, desk docking, car mode).</li>
              <li><b>Display Array:</b> Enumerates active external screens and screen states.</li>
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
          <i class="fas fa-tv fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Display Data</h4>
          <p class="text-muted">Display profiles will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <?php foreach ($rows as $ri => $r):
          $rid     = $r['id'] ?? 0;
          $ts      = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          $wpx     = (int)($r['width_px'] ?? 0);
          $hpx     = (int)($r['height_px'] ?? 0);
          $rw      = (int)($r['real_width'] ?? $wpx);
          $rh      = (int)($r['real_height'] ?? $hpx);
          $uw      = (int)($r['usable_width'] ?? 0);
          $uh      = (int)($r['usable_height'] ?? 0);
          $dpi     = (int)($r['density_dpi'] ?? 0);
          $refresh = (float)($r['refresh_rate'] ?? 0);
          $mRefr   = (float)($r['mode_refresh_rate'] ?? $refresh);
          $mW      = (int)($r['mode_width'] ?? $wpx);
          $mH      = (int)($r['mode_height'] ?? $hpx);
          $rot     = (int)($r['rotation'] ?? 0);
          $mpDisp  = $rw * $rh > 0 ? round(($rw * $rh) / 1_000_000, 2) : 0;
          $ar      = aspectRatio($wpx, $hpx);
          $rotLabel= $rotLabels[$rot] ?? "{$rot}°";
          $scLayout= (int)($r['screen_layout'] ?? 0) & 0x0f;  // low 4 bits
          $uiMode  = (int)($r['ui_mode'] ?? 0) & 0x0f;
          [$dpiCat, $dpiColor] = dpiCategory($dpi);
          $hzBadge = $refresh > 90 ? 'hz-badge-high' : ($refresh > 60 ? 'hz-badge-mid' : 'hz-badge-low');

          // displays_json
          $displays = $r['displays'] ?? $r['displays_json'] ?? [];
          if (is_string($displays)) $displays = json_decode($displays, true) ?: [];

          // Only show first 2 rows expanded by default; rest hidden
          $isFirst = ($ri === 0);
          ?>
          <div class="card disp-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="card-title font-weight-bold text-secondary mb-0">
                <i class="fas fa-desktop mr-2"></i>
                <?= $wpx ?>×<?= $hpx ?> &nbsp;
                <span class="badge badge-secondary"><?= $dpi ?> DPI</span>
                &nbsp;<span class="badge <?= $hzBadge ?>"><?= $refresh ?> Hz</span>
              </h3>
              <div>
                <small class="text-muted mr-2"><?= $ts ?></small>
                <button class="btn btn-tool btn-sm" data-toggle="collapse" data-target="#disp-<?= $rid ?>">
                  <i class="fas fa-<?= $isFirst ? 'minus' : 'plus' ?>"></i>
                </button>
              </div>
            </div>

            <div id="disp-<?= $rid ?>" class="<?= $isFirst ? '' : 'collapse' ?>">
              <div class="card-body">
                <div class="row">

                  <!-- Card 1: Screen Metrics -->
                  <div class="col-md-4 mb-3">
                    <div class="disp-card p-3 h-100">
                      <div class="text-center mb-3">
                        <?php
                        // Simple phone SVG proportional outline
                        $svgH = 100;
                        $svgW = $hpx > 0 ? round(($wpx / $hpx) * $svgH) : 50;
                        $svgW = max(40, min(80, $svgW));
                        $br = 6;
                        ?>
                        <svg class="phone-svg mb-2" width="<?= $svgW+16 ?>" height="<?= $svgH+24 ?>" viewBox="0 0 <?= $svgW+16 ?> <?= $svgH+24 ?>">
                          <rect x="4" y="4" width="<?= $svgW+8 ?>" height="<?= $svgH+16 ?>" rx="<?= $br+2 ?>" fill="#343a40" stroke="none"/>
                          <rect x="8" y="8" width="<?= $svgW ?>" height="<?= $svgH ?>" rx="<?= $br ?>" fill="#0a0a2e"/>
                          <circle cx="<?= ($svgW+16)/2 ?>" cy="<?= $svgH+14 ?>" r="3" fill="#555"/>
                          <text x="50%" y="<?= $svgH/2+8 ?>" text-anchor="middle" fill="#00d4ff" style="font-size:7px;font-family:monospace;"><?= $wpx ?>×<?= $hpx ?></text>
                        </svg>
                        <div class="res-hero"><?= $wpx ?>×<?= $hpx ?></div>
                        <div class="res-sub">Logical Resolution</div>
                        <?php if ($mpDisp > 0): ?>
                          <div class="badge badge-primary mt-1"><?= $mpDisp ?> MP Display</div>
                        <?php endif; ?>
                      </div>
                      <div class="spec-kv"><span class="sk">Aspect Ratio</span><span class="sv"><?= esc($ar) ?></span></div>
                      <div class="spec-kv"><span class="sk">Real Resolution</span><span class="sv"><?= $rw ?>×<?= $rh ?></span></div>
                      <?php if ($uw && $uh): ?>
                      <div class="spec-kv"><span class="sk">Usable Area</span><span class="sv"><?= $uw ?>×<?= $uh ?> px</span></div>
                      <?php endif; ?>
                      <div class="spec-kv"><span class="sk">Rotation</span><span class="sv"><?= esc($rotLabel) ?></span></div>
                      <div class="spec-kv"><span class="sk">Current Mode</span><span class="sv"><?= $mW ?>×<?= $mH ?> @ <?= $mRefr ?>Hz</span></div>
                    </div>
                  </div>

                  <!-- Card 2: Display Quality -->
                  <div class="col-md-4 mb-3">
                    <div class="disp-card p-3 h-100">
                      <!-- DPI Ring -->
                      <div class="text-center mb-3">
                        <?php
                        $ringPct = min(100, ($dpi / 640) * 100);
                        $circumference = 2 * pi() * 36;
                        $dashOffset = $circumference - ($ringPct / 100) * $circumference;
                        $dpiColorCss = ['danger'=>'#dc3545','warning text-dark'=>'#ffc107','info'=>'#17a2b8','primary'=>'#007bff','success'=>'#28a745','purple'=>'#6f42c1','secondary'=>'#6c757d'];
                        $ringColor = $dpiColorCss[$dpiColor] ?? '#6c757d';
                        ?>
                        <svg width="90" height="90" viewBox="0 0 90 90">
                          <circle cx="45" cy="45" r="36" fill="none" stroke="#e9ecef" stroke-width="8"/>
                          <circle cx="45" cy="45" r="36" fill="none" stroke="<?= $ringColor ?>" stroke-width="8"
                            stroke-dasharray="<?= $circumference ?>" stroke-dashoffset="<?= $dashOffset ?>"
                            transform="rotate(-90 45 45)"/>
                          <text x="45" y="40" text-anchor="middle" style="font-size:13px;font-weight:900;fill:#343a40;"><?= $dpi ?></text>
                          <text x="45" y="54" text-anchor="middle" style="font-size:8px;fill:#6c757d;">DPI</text>
                        </svg>
                        <div><span class="badge badge-<?= $dpiColor ?>"><?= $dpiCat ?></span></div>
                      </div>

                      <div class="spec-kv"><span class="sk">X DPI</span><span class="sv"><?= round($r['xdpi'] ?? 0, 1) ?></span></div>
                      <div class="spec-kv"><span class="sk">Y DPI</span><span class="sv"><?= round($r['ydpi'] ?? 0, 1) ?></span></div>
                      <div class="spec-kv"><span class="sk">Density</span><span class="sv"><?= $r['density'] ?? '—' ?></span></div>
                      <div class="spec-kv"><span class="sk">Scaled Density</span><span class="sv"><?= $r['scaled_density'] ?? '—' ?></span></div>
                      <div class="spec-kv">
                        <span class="sk">Refresh Rate</span>
                        <span class="sv"><span class="badge <?= $hzBadge ?>"><?= $refresh ?> Hz</span></span>
                      </div>
                      <?php if ($r['smallest_screen_width_dp'] ?? 0): ?>
                      <div class="spec-kv"><span class="sk">Smallest Width</span><span class="sv"><?= $r['smallest_screen_width_dp'] ?> dp</span></div>
                      <?php endif; ?>
                    </div>
                  </div>

                  <!-- Card 3: System Config & Displays JSON -->
                  <div class="col-md-4 mb-3">
                    <div class="disp-card p-3 h-100">
                      <div class="mb-3">
                        <small class="font-weight-bold text-muted d-block mb-1" style="font-size:11px;text-transform:uppercase;">System Display Configuration</small>
                        <?php if ($scLayout > 0): ?>
                        <div class="spec-kv">
                          <span class="sk">Screen Layout</span>
                          <span class="sv"><i class="fas <?= $layoutIcons[$scLayout] ?? 'fa-mobile-alt' ?> mr-1"></i><?= esc($layoutLabels[$scLayout] ?? 'Unknown') ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($uiMode > 0): ?>
                        <div class="spec-kv">
                          <span class="sk">UI Mode</span>
                          <span class="sv"><i class="fas <?= $uiModeIcons[$uiMode] ?? 'fa-mobile-alt' ?> mr-1"></i><?= esc($uiModeLabels[$uiMode] ?? 'Unknown') ?></span>
                        </div>
                        <?php endif; ?>
                      </div>

                      <!-- displays_json parsed -->
                      <?php if (!empty($displays) && is_array($displays)): ?>
                      <div>
                        <small class="font-weight-bold text-muted d-block mb-1" style="font-size:11px;text-transform:uppercase;">Connected Displays (<?= count($displays) ?>)</small>
                        <?php foreach ($displays as $disp): if (!is_array($disp)) continue;
                          $dstate = strtoupper($disp['state'] ?? $disp['displayState'] ?? 'UNKNOWN');
                          $dispId = $disp['id'] ?? '?';
                          $dname  = $disp['name'] ?? $disp['displayName'] ?? "Display #{$dispId}";
                          $dtype  = $disp['type'] ?? $disp['displayType'] ?? '—';
                          $stateBadge = $dstate === 'ON' ? 'success' : ($dstate === 'OFF' ? 'secondary' : 'warning text-dark');
                          ?>
                          <div class="display-card-inner">
                            <div class="d-flex justify-content-between align-items-center">
                              <span class="font-weight-bold" style="font-size:12px;"><?= esc($dname) ?></span>
                              <span class="badge badge-<?= $stateBadge ?>"><?= esc($dstate) ?></span>
                            </div>
                            <?php if ($dtype && $dtype !== '—'): ?>
                              <small class="text-muted">Type: <?= esc($dtype) ?></small>
                            <?php endif; ?>
                            <?php if (!empty($disp['width']) && !empty($disp['height'])): ?>
                              <div style="font-size:11px;color:#6c757d;"><?= $disp['width'] ?>×<?= $disp['height'] ?><?= !empty($disp['refreshRate']) ? ' @ '.$disp['refreshRate'].'Hz' : '' ?></div>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
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