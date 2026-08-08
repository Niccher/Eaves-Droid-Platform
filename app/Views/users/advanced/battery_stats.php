<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
// Group & deduplicate: keep only the latest battery snapshot per device
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

// Helper maps
$healthMap = [
    'GOOD'          => ['Good', 'success', 'fa-heartbeat'],
    'OVERHEAT'      => ['Overheat', 'danger', 'fa-fire'],
    'DEAD'          => ['Dead', 'dark', 'fa-skull'],
    'OVER_VOLTAGE'  => ['Over Voltage', 'warning text-dark', 'fa-bolt'],
    'COLD'          => ['Cold', 'info', 'fa-snowflake'],
    'FAILURE'       => ['Unspecified Failure', 'danger', 'fa-exclamation-triangle'],
    'UNKNOWN'       => ['Unknown', 'secondary', 'fa-question-circle'],
];
$pluggedMap = [
    'AC'       => ['AC Wall Charger', 'primary', 'fa-plug'],
    'USB'      => ['USB Cable Port', 'info', 'fa-usb'],
    'WIRELESS' => ['Wireless Induction', 'success', 'fa-wifi'],
    'NONE'     => ['Unplugged (Battery)', 'secondary', 'fa-battery-full'],
];
?>

<style>
.bat-card       { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.bat-hero       { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 20px 22px 18px; color: #fff; position: relative; overflow: hidden; }
.bat-hero::after { content: ''; position: absolute; right: 15px; bottom: 10px; font-family: "Font Awesome 5 Free"; font-weight: 900; content: "\f241"; font-size: 60px; opacity: 0.05; }
.bat-kv         { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.bat-kv:last-child { border-bottom: none; }
.bat-kv .bk     { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.bat-kv .bv     { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.bat-silhouette { width: 50px; height: 90px; border: 3px solid #fff; border-radius: 6px; position: relative; padding: 3px; display: flex; align-items: flex-end; flex-shrink: 0; }
.bat-silhouette::before { content: ''; position: absolute; top: -8px; left: 50%; transform: translateX(-50%); width: 20px; height: 6px; background: #fff; border-radius: 2px 2px 0 0; }
.bat-level-fill { width: 100%; border-radius: 2px; transition: height 0.4s ease; }
.metric-pill    { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; }
.bar-wrap       { background: #f8f9fa; border-radius: 6px; padding: 10px; margin-top: 8px; border: 1px solid #e9ecef; }
.telemetry-row  { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 8px; text-align: center; }
.telemetry-box  { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 8px; }
.telemetry-val  { font-size: 14px; font-weight: 800; color: #2c3e50; font-family: monospace; }
.telemetry-lbl  { font-size: 9px; color: #6c757d; text-transform: uppercase; font-weight: 600; letter-spacing: 0.3px; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-battery-three-quarters text-info mr-2"></i>Battery Statistics</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active Profiles: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Hardware battery parameters, charging loops, current drain, and health diagnostics</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>
      
      <!-- Callout matching Biometric/Storage pages -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-battery-half mr-2"></i>Battery Security Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Forensic verification of battery discharge rates, temperature parameters, and current flows. Rapid voltage drop or temperature spikes often point to hidden background computations or malicious processes.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">State &amp; Health:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Health Status:</b> Flags overheating cells (OVERHEAT) or structural failures (COLD, DEAD).</li>
              <li><b>Charge Loop:</b> Verifies the connected source class (AC, USB, wireless).</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Flow Telemetry:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Current Draw:</b> Traces active micro-ampere flow rates (µA) to reveal processing activity.</li>
              <li><b>Charge Counter:</b> Computes absolute remaining capacity in µAh.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Physical Metrics:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Voltage Boundaries:</b> Tracks operating millivolts to detect unstable batteries.</li>
              <li><b>Thermal Runaway:</b> Monitors real-time Celsius temperatures to inspect device thermal profiles.</li>
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
          <i class="fas fa-battery-empty fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Battery logs</h4>
          <p class="text-muted">Battery profiles will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <div class="row">
        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $lvl = (int)($r['capacity_percent'] ?? $r['level_percent'] ?? 0);
          $chg = !empty($r['is_charging']);
          $healthRaw = strtoupper($r['health'] ?? 'UNKNOWN');
          $pluggedRaw = strtoupper($r['plugged_type'] ?? 'NONE');
          $tempC = (float)(($r['temperature_deci_c'] ?? 0) / 10 ?: ($r['temperature_celsius'] ?? 0));
          $volt = (int)($r['voltage_mv'] ?? 0);
          $tech = $r['technology'] ?? 'Li-ion';
          
          $chargeUah = (int)($r['charge_counter_uah'] ?? 0);
          $currentUa = (int)($r['current_now_ua'] ?? 0);
          $energyUwh = (int)($r['energy_counter_uwh'] ?? 0);
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          // Color decoders
          [$healthText, $healthColor, $healthIcon] = $healthMap[$healthRaw] ?? ['Other', 'secondary', 'fa-battery-full'];
          [$pluggedText, $pluggedColor, $pluggedIcon] = $pluggedMap[$pluggedRaw] ?? ['Unknown', 'secondary', 'fa-question-circle'];
          
          $lvlColor = $lvl > 50 ? '#28a745' : ($lvl > 20 ? '#ffc107' : '#dc3545');
          ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="bat-card h-100">
              
              <!-- Hero Layout -->
              <div class="bat-hero">
                <div class="d-flex align-items-center">
                  <div class="bat-silhouette mr-3">
                    <div class="bat-level-fill" style="height: <?= $lvl ?>%; background: <?= $lvlColor ?>;"></div>
                  </div>
                  <div>
                    <div style="font-size: 32px; font-weight: 900; line-height: 1;"><?= $lvl ?>%</div>
                    <div style="font-size: 13px; opacity: 0.9; margin-top: 4px;">
                      <i class="fas <?= $chg ? 'fa-bolt text-warning' : 'fa-info-circle' ?> mr-1"></i>
                      <?= $chg ? 'Charging' : 'Discharging' ?>
                    </div>
                    <div style="font-size: 11px; opacity: 0.75; font-family: monospace;"><?= esc($tech) ?> Cells</div>
                  </div>
                  
                  <div class="ml-auto text-right">
                    <span class="badge badge-<?= $healthColor ?> p-2 mb-2 d-inline-block">
                      <i class="fas <?= $healthIcon ?> mr-1"></i><?= $healthText ?>
                    </span>
                    <br>
                    <span class="badge badge-<?= $pluggedColor ?> p-2">
                      <i class="fas <?= $pluggedIcon ?> mr-1"></i><?= $pluggedText ?>
                    </span>
                  </div>
                </div>
              </div>

              <!-- Details/Telemetry -->
              <div class="p-3">
                <div class="section-label mb-2" style="font-size: 10px; font-weight: 700; color: #6c757d; text-transform: uppercase;">Battery Metrics</div>
                
                <!-- Telemetry Boxes -->
                <div class="telemetry-row mb-3">
                  <div class="telemetry-box">
                    <div class="telemetry-lbl">Temperature</div>
                    <div class="telemetry-val"><?= number_format($tempC, 1) ?> °C</div>
                  </div>
                  <div class="telemetry-box">
                    <div class="telemetry-lbl">Voltage</div>
                    <div class="telemetry-val"><?= number_format($volt / 1000, 2) ?> V</div>
                  </div>
                  <div class="telemetry-box">
                    <div class="telemetry-lbl">Current Flow</div>
                    <div class="telemetry-val"><?= number_format($currentUa) ?> µA</div>
                  </div>
                </div>

                <div class="bat-kv">
                  <span class="bk">Absolute Charge</span>
                  <span class="bv"><code><?= number_format($chargeUah) ?> µAh</code></span>
                </div>
                <div class="bat-kv">
                  <span class="bk">Energy Counter</span>
                  <span class="bv"><code><?= number_format($energyUwh) ?> µWh</code></span>
                </div>
                <div class="bat-kv">
                  <span class="bk">Plug State Code</span>
                  <span class="bv"><?= esc($pluggedRaw) ?></span>
                </div>
                <div class="bat-kv">
                  <span class="bk">Extracted</span>
                  <span class="sv" style="font-size:11px;"><?= $ts ?></span>
                </div>

                <div class="mt-3 text-right">
                  <button class="btn btn-sm btn-outline-danger delete-row py-0"
                    data-id="<?= $rid ?>"
                    data-url="<?= base_url('advanced/hardware/battery_stats/delete') ?>">
                    <i class="fas fa-trash mr-1"></i>Remove
                  </button>
                </div>
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