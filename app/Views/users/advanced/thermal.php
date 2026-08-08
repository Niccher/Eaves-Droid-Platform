<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
// Group thermal readings by device_id (or latest scan snapshot)
// Deduplicate: group by zone_name (or CPU name) to keep only the latest reading per thermal sensor zone.
$seen = [];
$unique = [];
foreach ($rows as $r) {
    // We group/dedupe by zone_name or cpu_name to avoid listing historical duplicate sensors
    $sensorName = !empty($r['zone_name']) ? $r['zone_name'] : (!empty($r['cpu_name']) ? $r['cpu_name'] : 'unknown');
    $key = strtolower(trim($sensorName));
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $unique[] = $r;
    }
}
$rows = $unique;

function tempStatus(float $temp): array {
    if ($temp <= 0) return ['Unknown', 'secondary', 'fa-question-circle'];
    if ($temp < 40) return ['Cool', 'success', 'fa-thermometer-empty'];
    if ($temp < 60) return ['Warm', 'info', 'fa-thermometer-quarter'];
    if ($temp < 75) return ['Hot', 'warning text-dark', 'fa-thermometer-half'];
    return ['Critical', 'danger', 'fa-thermometer-full'];
}
?>

<style>
.therm-card { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2); }
.therm-hero { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; }
.therm-kv { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.therm-kv:last-child { border-bottom: none; }
.therm-kv .tk { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.therm-kv .tv { font-weight: 700; color: #343a40; }
.gauge-bar { height: 10px; border-radius: 5px; background: #dee2e6; overflow: hidden; margin: 5px 0; }
.gauge-bar-fill { height: 100%; border-radius: 5px; transition: width .4s; }
.zone-pill { border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 700; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-thermometer-high text-danger mr-2"></i>Thermal Telemetry</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active Sensors: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">System temperatures, thermal zones, core throttle states, and CPU governor status</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>

      <!-- Upgraded Security Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-shield-alt mr-2"></i>Thermal &amp; Processor Core Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Forensic auditing of thermal sensor boundaries across core clusters and batteries. Monitoring frequency scaling governors and throttling events detects abnormal heat profiles indicative of background resource abuse or hidden processes.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Thermal Zones:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Zone Types:</b> Audits core processor temperatures alongside board-level heat maps.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Throttling Events:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Throttle Count:</b> Logs thermal mitigation occurrences that restrict operational speeds.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Governor Scaling:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Scaling Governor:</b> Monitors active policy limits (interactive, schedutil, performance).</li>
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
          <i class="fas fa-thermometer-empty fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Thermal Sensor Data</h4>
          <p class="text-muted">Thermal stats will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <div class="row">
        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $zoneName = $r['zone_name'] ?? '—';
          $zoneType = $r['zone_type'] ?? '—';
          $tempC = (float)($r['temp_celsius'] ?? 0);
          $tempRaw = $r['temp_raw'] ?? '—';
          $cpuName = $r['cpu_name'] ?? '—';
          $throttleCnt = $r['throttle_count'] ?? '0';
          $minFreq = $r['scaling_min_freq'] ?? '—';
          $maxFreq = $r['scaling_max_freq'] ?? '—';
          $curFreq = $r['scaling_cur_freq'] ?? '—';
          $governor = $r['scaling_governor'] ?? '—';
          $policy = $r['policy'] ?? '—';
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          [$statusText, $statusColor, $statusIcon] = tempStatus($tempC);
          $barPct = min(100, max(0, ($tempC / 100) * 100));
          ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="therm-card h-100">
              
              <!-- Updated Hero section using secondary gradient instead of dark red -->
              <div class="therm-hero">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div>
                    <span class="zone-pill badge badge-light">
                      <i class="fas fa-microchip mr-1"></i><?= esc($zoneType !== '—' ? $zoneType : 'Processor') ?>
                    </span>
                  </div>
                  <span class="badge badge-<?= $statusColor ?> p-2">
                    <i class="fas <?= $statusIcon ?> mr-1"></i><?= $statusText ?>
                  </span>
                </div>
                <div class="d-flex align-items-baseline">
                  <span style="font-size: 32px; font-weight: 900;"><?= $tempC ?></span>
                  <span style="font-size: 18px; font-weight: 600; margin-left: 2px;">°C</span>
                </div>
                <div class="gauge-bar mt-2">
                  <div class="gauge-bar-fill bg-<?= $statusColor === 'warning text-dark' ? 'warning' : $statusColor ?>" style="width: <?= $barPct ?>%;"></div>
                </div>
                <div class="text-right" style="font-size: 11px; opacity: 0.7;">Raw Value: <?= esc($tempRaw) ?></div>
              </div>

              <!-- Details Section -->
              <div class="p-3">
                <div class="therm-kv">
                  <span class="tk">Sensor Zone</span>
                  <span class="tv"><?= esc($zoneName) ?></span>
                </div>
                <?php if ($cpuName !== '—'): ?>
                <div class="therm-kv">
                  <span class="tk">Target Core</span>
                  <span class="tv"><code><?= esc($cpuName) ?></code></span>
                </div>
                <?php endif; ?>
                <div class="therm-kv">
                  <span class="tk">Throttle Count</span>
                  <span class="tv">
                    <span class="badge badge-<?= (int)$throttleCnt > 0 ? 'danger' : 'success' ?>">
                      <?= esc($throttleCnt) ?> events
                    </span>
                  </span>
                </div>
                <?php if ($curFreq !== '—'): ?>
                <div class="therm-kv">
                  <span class="tk">Current Freq</span>
                  <span class="tv"><?= esc($curFreq) ?> MHz</span>
                </div>
                <?php endif; ?>
                <?php if ($minFreq !== '—' || $maxFreq !== '—'): ?>
                <div class="therm-kv">
                  <span class="tk">Frequency Range</span>
                  <span class="tv"><?= esc($minFreq) ?> - <?= esc($maxFreq) ?> MHz</span>
                </div>
                <?php endif; ?>
                <div class="therm-kv">
                  <span class="tk">Governor</span>
                  <span class="tv"><span class="badge badge-secondary"><?= esc($governor) ?></span></span>
                </div>
                <?php if ($policy !== '—'): ?>
                <div class="therm-kv">
                  <span class="tk">Thermal Policy</span>
                  <span class="tv" style="font-size:11px; max-width:60%; text-align:right;"><?= esc($policy) ?></span>
                </div>
                <?php endif; ?>
                <div class="therm-kv">
                  <span class="tk">Extracted</span>
                  <span class="sv" style="font-size:11px;"><?= $ts ?></span>
                </div>

                <div class="mt-3 text-right">
                  <button class="btn btn-sm btn-outline-danger delete-row py-0"
                    data-id="<?= $rid ?>"
                    data-url="<?= base_url('advanced/hardware/thermal/delete') ?>">
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