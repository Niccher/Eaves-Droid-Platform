<?php /** @var array $rows @var array $history @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
// Prepare chart telemetry timeline from history
$chartLabels = [];
$chartLevels = [];
$chartTemps  = [];
$chartVolts  = [];

foreach ($history ?? [] as $h) {
    $ts = !empty($h['extracted_at']) ? date('M d H:i', (int)($h['extracted_at'] > 1000000000000 ? $h['extracted_at']/1000 : $h['extracted_at'])) : ($h['created_at'] ?? '—');
    $chartLabels[] = $ts;
    $chartLevels[] = round((float)($h['capacity_percent'] ?? $h['level_percent'] ?? 0), 1);
    $chartTemps[]  = round((float)(($h['temperature_deci_c'] ?? 0) / 10 ?: ($h['temperature_celsius'] ?? 0)), 1);
    $chartVolts[]  = round((int)($h['voltage_mv'] ?? 0) / 1000, 2);
}

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

      <?php if (!empty($chartLabels)): ?>
      <!-- Battery Telemetry Trend Chart -->
      <div class="card card-outline card-info shadow-sm mb-4">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="gap:10px;">
          <h3 class="card-title font-weight-bold m-0 text-dark">
            <i class="fas fa-chart-line text-info mr-2"></i>Battery &amp; Thermal Timeline
            <small class="text-muted ml-2">(Last <?= count($chartLabels) ?> Snapshots)</small>
          </h3>
          <div class="btn-group btn-group-sm" role="group" id="batteryChartControls">
            <button type="button" class="btn btn-outline-info active" data-view="all">
              <i class="fas fa-layer-group mr-1"></i>Combined View
            </button>
            <button type="button" class="btn btn-outline-success" data-view="level">
              <i class="fas fa-battery-half mr-1"></i>Battery % Only
            </button>
            <button type="button" class="btn btn-outline-danger" data-view="temp">
              <i class="fas fa-thermometer-half mr-1"></i>Thermal (°C)
            </button>
            <button type="button" class="btn btn-outline-primary" data-view="volt">
              <i class="fas fa-bolt mr-1"></i>Voltage (V)
            </button>
          </div>
        </div>
        <div class="card-body">
          <div style="height: 300px; position: relative;">
            <canvas id="batteryChart"></canvas>
          </div>
        </div>
        <div class="card-footer bg-light py-2 text-muted small d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
          <span><i class="fas fa-info-circle text-info mr-1"></i>Chronological progression of battery charge level, temperature fluctuations, and operating voltage.</span>
          <span class="d-flex" style="gap: 8px;">
            <span class="badge badge-success"><i class="fas fa-battery-half mr-1"></i>Battery Level (%)</span>
            <span class="badge badge-danger"><i class="fas fa-thermometer-half mr-1"></i>Temperature (°C)</span>
            <span class="badge badge-primary"><i class="fas fa-bolt mr-1"></i>Voltage (V)</span>
          </span>
        </div>
      </div>
      <?php endif; ?>

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

<?php if (!empty($chartLabels)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('batteryChart');
    if (!ctx) return;

    const labels = <?= json_encode($chartLabels) ?>;
    const levels = <?= json_encode($chartLevels) ?>;
    const temps  = <?= json_encode($chartTemps) ?>;
    const volts  = <?= json_encode($chartVolts) ?>;

    const chart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Battery Level (%)',
                    data: levels,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.12)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'yLevel',
                    pointRadius: 3,
                    pointHoverRadius: 6
                },
                {
                    label: 'Temperature (°C)',
                    data: temps,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.05)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'yTemp',
                    pointRadius: 3,
                    pointHoverRadius: 6
                },
                {
                    label: 'Voltage (V)',
                    data: volts,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.05)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'yVolt',
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    hidden: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 14,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                if (context.dataset.yAxisID === 'yLevel') {
                                    label += context.parsed.y + '%';
                                } else if (context.dataset.yAxisID === 'yTemp') {
                                    label += context.parsed.y + ' °C';
                                } else if (context.dataset.yAxisID === 'yVolt') {
                                    label += context.parsed.y + ' V';
                                } else {
                                    label += context.parsed.y;
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { maxRotation: 45, autoSkip: true, maxTicksLimit: 12 }
                },
                yLevel: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    min: 0,
                    max: 100,
                    title: { display: true, text: 'Battery (%)', color: '#28a745' },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                yTemp: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'Temp (°C)', color: '#dc3545' },
                    grid: { drawOnChartArea: false }
                },
                yVolt: {
                    type: 'linear',
                    display: false,
                    position: 'right',
                    title: { display: true, text: 'Voltage (V)', color: '#007bff' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });

    // Control buttons
    const buttons = document.querySelectorAll('#batteryChartControls button');
    buttons.forEach(btn => {
        btn.addEventListener('click', function() {
            buttons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const view = this.getAttribute('data-view');

            if (view === 'all') {
                chart.data.datasets[0].hidden = false;
                chart.data.datasets[1].hidden = false;
                chart.data.datasets[2].hidden = true;
                chart.options.scales.yLevel.display = true;
                chart.options.scales.yTemp.display = true;
                chart.options.scales.yVolt.display = false;
            } else if (view === 'level') {
                chart.data.datasets[0].hidden = false;
                chart.data.datasets[1].hidden = true;
                chart.data.datasets[2].hidden = true;
                chart.options.scales.yLevel.display = true;
                chart.options.scales.yTemp.display = false;
                chart.options.scales.yVolt.display = false;
            } else if (view === 'temp') {
                chart.data.datasets[0].hidden = true;
                chart.data.datasets[1].hidden = false;
                chart.data.datasets[2].hidden = true;
                chart.options.scales.yLevel.display = false;
                chart.options.scales.yTemp.display = true;
                chart.options.scales.yVolt.display = false;
            } else if (view === 'volt') {
                chart.data.datasets[0].hidden = true;
                chart.data.datasets[1].hidden = true;
                chart.data.datasets[2].hidden = false;
                chart.options.scales.yLevel.display = false;
                chart.options.scales.yTemp.display = false;
                chart.options.scales.yVolt.display = true;
            }
            chart.update();
        });
    });
});
</script>
<?php endif; ?>