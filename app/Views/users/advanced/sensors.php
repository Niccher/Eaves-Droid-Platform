<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
// Group sensor readings by device_id and keep only unique sensors list
foreach ($rows as &$r) {
    $r['sensor_unique_key'] = ($r['device_id'] ?? 'default') . '_' . ($r['sensor_name'] ?? 'unknown');
}
unset($r);

helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'sensor_unique_key',
    ['sensor_name', 'vendor', 'type_id', 'type_string', 'version', 'maximum_range', 'resolution', 'power_ma', 'sensor_string_type', 'min_delay_us', 'max_delay_us', 'fifo_reserved_event_count', 'fifo_max_event_count', 'is_wakeup', 'is_dynamic', 'is_additional_info', 'reporting_mode', 'required_permission', 'permission_display_name', 'flags', 'direct_channel_type', 'direct_report_rates', 'additional_info', 'calibration_params', 'mounting_matrix', 'drivetime_us', 'event_time_ns', 'sensor_max_range']
);

$typeMap = [
    1 => 'Accelerometer', 2 => 'Magnetic Field', 3 => 'Orientation',
    4 => 'Gyroscope', 5 => 'Light', 6 => 'Pressure', 7 => 'Temperature',
    8 => 'Proximity', 9 => 'Gravity', 10 => 'Linear Acceleration',
    11 => 'Rotation Vector', 12 => 'Relative Humidity', 13 => 'Ambient Temperature',
    14 => 'Uncalibrated Magnetic', 15 => 'Game Rotation Vector', 16 => 'Uncalibrated Gyro',
    17 => 'Significant Motion', 18 => 'Step Detector', 19 => 'Step Counter',
    20 => 'Geomagnetic Rotation Vector', 21 => 'Heart Rate', 22 => 'Tilt Detector',
    23 => 'Wake Gesture', 24 => 'Glance Gesture', 25 => 'Pick Up Gesture',
    28 => 'Wrist Tilt Gesture', 29 => 'Device Orientation', 30 => 'Pose 6DOF',
    31 => 'Stationary Detect', 32 => 'Motion Detect', 33 => 'Heart Beat',
    34 => 'Dynamic Sensor Meta', 35 => 'Additional Info'
];
?>

<style>
.sens-card       { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.sens-hero       { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; }
.sens-kv         { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.sens-kv:last-child { border-bottom: none; }
.sens-kv .sk     { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.sens-kv .sv     { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.sens-badge      { border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 700; }
.section-label   { font-size: 10px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .4px; }
.matrix-grid     { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 6px; font-family: monospace; font-size: 11px; text-align: center; max-width: 180px; }
.matrix-cell     { background: #fff; padding: 2px; border: 1px solid #e9ecef; border-radius: 2px; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-ruler text-primary mr-2"></i>Sensor Profile</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active Sensors: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Hardware components, reporting channels, maximum ranges, and advanced sensor calibration data</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>

      <!-- Consistent Security Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-shield-alt mr-2"></i>Sensor Array Telemetry &amp; Hardware Auditing</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Auditing built-in hardware sensors (accelerometers, gyroscopes, magnetometers). Verifying details like vendor signatures, resolution tolerances, and reporting delay behaviors detects spoofed emulator layouts or unauthorized micro-tracking hardware blocks.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Sensor Registry:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Reporting Type:</b> Identifies physical tracking zones (Orientation, Gyroscope, Significant Motion).</li>
              <li><b>Wakeup Flag:</b> Flags sensors capable of waking up the APU core from low-power sleep.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Hardware Limits:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Resolution &amp; Max Range:</b> Checks physical tolerances. Synthesized emulators often report perfect integer metrics.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">AdvancedController Calibrations:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Mounting Matrix:</b> Evaluates physical alignment coordinates to detect custom sensor frameworks.</li>
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
          <i class="fas fa-ruler fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Sensor Profile Data Detected</h4>
          <p class="text-muted">Sensor info will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <div class="row">
        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          $typeLabel = $typeMap[$r['type_id'] ?? 0] ?? ($r['type_string'] ?? 'Unknown');
          $power = $r['power_ma'] ?? null;
          $maxRange = $r['maximum_range'] ?? null;
          $resolution = $r['resolution'] ?? null;
          
          $isWakeup = !empty($r['is_wakeup']);
          $isDynamic = !empty($r['is_dynamic']);
          
          // Parse JSON fields
          $calib = is_string($r['calibration_params'] ?? null) ? json_decode($r['calibration_params'], true) : ($r['calibration_params'] ?? []);
          $matrix = is_string($r['mounting_matrix'] ?? null) ? json_decode($r['mounting_matrix'], true) : ($r['mounting_matrix'] ?? []);
          $addInfo = is_string($r['additional_info'] ?? null) ? json_decode($r['additional_info'], true) : ($r['additional_info'] ?? []);
          ?>

          <div class="col-md-6 col-lg-4 mb-4">
            <div class="sens-card h-100">
              <!-- Hero -->
              <div class="sens-hero">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div>
                    <span class="sens-badge badge badge-light">
                      ID <?= esc($r['type_id'] ?? '0') ?>
                    </span>
                  </div>
                  <span class="badge badge-primary p-2">
                    <?= esc($typeLabel) ?>
                  </span>
                </div>
                <div class="font-weight-bold" style="font-size: 16px; letter-spacing:0.3px; line-height: 1.2;">
                  <?= esc($r['sensor_name'] ?? '—') ?>
                </div>
                <div style="font-size: 11px; opacity: 0.85; margin-top: 2px;">
                  Vendor: <?= esc($r['vendor'] ?? '—') ?>
                </div>
              </div>

              <!-- Content Body -->
              <div class="p-3">
                <div class="section-label mb-2"><i class="fas fa-sliders-h mr-1"></i>Operational Specs</div>
                <?php if ($maxRange !== null): ?>
                  <div class="sens-kv"><span class="sk">Maximum Range</span><span class="sv"><?= esc($maxRange) ?></span></div>
                <?php endif; ?>
                <?php if ($resolution !== null): ?>
                  <div class="sens-kv"><span class="sk">Resolution</span><span class="sv"><?= esc($resolution) ?></span></div>
                <?php endif; ?>
                <?php if ($power !== null): ?>
                  <div class="sens-kv"><span class="sk">Power Drain</span><span class="sv"><?= esc($power) ?> mA</span></div>
                <?php endif; ?>
                
                <div class="sens-kv"><span class="sk">Wake-up Sensor</span><span class="sv"><?= $isWakeup ? 'Yes' : 'No' ?></span></div>
                <div class="sens-kv"><span class="sk">Dynamic Sensor</span><span class="sv"><?= $isDynamic ? 'Yes' : 'No' ?></span></div>
                
                <?php if (!empty($r['reporting_mode'])): ?>
                  <div class="sens-kv"><span class="sk">Reporting Mode</span><span class="sv"><code><?= esc($r['reporting_mode']) ?></code></span></div>
                <?php endif; ?>
                
                <div class="sens-kv"><span class="sk">Version / Permission</span><span class="sv" style="font-size:11px;">v<?= esc($r['version'] ?? '—') ?> / <?= esc($r['required_permission'] ?? 'None') ?></span></div>

                <!-- AdvancedController Enriched Data Blocks -->
                <?php if (!empty($matrix) && is_array($matrix) && count($matrix) >= 9): ?>
                  <div class="section-label mt-3 mb-1"><i class="fas fa-th-large mr-1"></i>Mounting Alignment Matrix</div>
                  <div class="matrix-grid">
                    <?php for($i=0; $i<9; $i++): ?>
                      <div class="matrix-cell"><?= esc(round((float)($matrix[$i] ?? 0), 2)) ?></div>
                    <?php endfor; ?>
                  </div>
                <?php elseif (!empty($matrix)): ?>
                  <div class="section-label mt-3 mb-1"><i class="fas fa-th-large mr-1"></i>Mounting Alignment</div>
                  <div class="matrix-box"><?= esc(json_encode($matrix)) ?></div>
                <?php endif; ?>

                <?php if (!empty($calib) && is_array($calib)): ?>
                  <div class="section-label mt-3 mb-1"><i class="fas fa-drafting-compass mr-1"></i>Calibration Parameters</div>
                  <div class="bg-light p-2 rounded border" style="font-size: 11px;">
                    <?php foreach ($calib as $k => $val): ?>
                      <div class="d-flex justify-content-between py-1 border-bottom" style="border-color:#e9ecef !important;">
                        <span class="text-muted font-weight-bold" style="font-size: 9px; text-transform: uppercase;"><?= esc(str_replace('_', ' ', $k)) ?></span>
                        <code class="text-dark font-weight-bold"><?= esc(is_array($val) ? json_encode($val) : $val) ?></code>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-3">
                  <small class="text-muted" style="font-size:10px;">Extracted: <?= $ts ?></small>
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