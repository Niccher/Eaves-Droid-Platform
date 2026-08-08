<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
$gnss    = null;
$allRows = [];

// Frequency band color mapping
$freqColors = [
    'L1'  => 'primary',   'L2'  => 'primary',  'L5'  => 'info',
    'E1'  => 'success',   'E5a' => 'success',  'E5b' => 'success', 'E6'  => 'success',
    'B1I' => 'warning',   'B1C' => 'warning',  'B2a' => 'warning', 'B2b' => 'warning', 'B3I' => 'warning',
    'G1'  => 'secondary', 'G2'  => 'secondary',
];

// Constellation MHz reference
$freqMhz = [
    'L1'=>'1575.42 MHz','L2'=>'1227.60 MHz','L5'=>'1176.45 MHz',
    'E1'=>'1575.42 MHz','E5a'=>'1176.45 MHz','E5b'=>'1207.14 MHz','E6'=>'1278.75 MHz',
    'B1I'=>'1561.10 MHz','B1C'=>'1575.42 MHz','B2a'=>'1176.45 MHz','B2b'=>'1207.14 MHz','B3I'=>'1268.52 MHz',
    'G1'=>'1602 MHz','G2'=>'1246 MHz',
];

// Nanoseconds formatter
function fmtNs(float $ns): string {
    if ($ns <= 0) return '—';
    if ($ns < 1000) return round($ns, 2) . ' ns';
    if ($ns < 1_000_000) return round($ns / 1000, 2) . ' µs';
    if ($ns < 1_000_000_000) return round($ns / 1_000_000, 2) . ' ms';
    return round($ns / 1_000_000_000, 3) . ' s';
}

foreach ($rows as $idx => $r) {
    $parseJson = fn($v) => is_string($v) ? json_decode($v, true) : (is_array($v) ? $v : []);

    $constellations    = $parseJson($r['constellations_supported'] ?? '[]');
    $freqs             = $parseJson($r['frequencies_supported'] ?? '[]');
    $agpsModes         = $parseJson($r['agps_modes'] ?? '[]');
    $antInfo           = $parseJson($r['antenna_info'] ?? '{}');
    $measCapabilities  = $parseJson($r['measurement_capabilities'] ?? '[]');

    $entry = [
        'id'            => $r['gnss_id'] ?? 'Unknown Receiver',
        'model'         => $r['gnss_hardware_model_id'] ?? '—',
        'year'          => $r['gnss_year_of_hardware'] ?? '—',
        'batch_size'    => (int)($r['gnss_batch_size'] ?? 0),
        'constellations'=> is_array($constellations) ? array_filter($constellations) : [],
        'freqs'         => is_array($freqs) ? array_filter($freqs) : [],
        'antenna'       => $r['antenna_type'] ?? 'Integrated Patch',
        'ant_info'      => is_array($antInfo) ? $antInfo : [],
        'meas_caps'     => is_array($measCapabilities) ? $measCapabilities : [],
        'tracked'       => (int)($r['max_satellites_tracked'] ?? 0),
        'used'          => (int)($r['max_satellites_used'] ?? 0),
        'agps'          => !empty($r['agps_supported']),
        'agps_modes'    => is_array($agpsModes) ? $agpsModes : [],
        'dead_reckoning'=> !empty($r['dead_reckoning_supported']),
        'raw_meas'      => !empty($r['raw_measurements_supported']),
        'corrections'   => !empty($r['correction_data_supported']),
        'nav_messages'  => !empty($r['navigation_messages_supported']),
        'status_supported'=> !empty($r['status_supported']),
        'gps_provider'  => !empty($r['gps_provider_available']),
        'time_offset_ns'=> (float)($r['time_offset_ns'] ?? 0),
        'leap_second'   => $r['leap_second'] ?? '—',
        'utc_accuracy_ns'=> (float)($r['utc_time_accuracy_ns'] ?? 0),
        'extracted'     => !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—',
        'row_id'        => $r['id'] ?? 0,
    ];

    if ($idx === 0) $gnss = $entry;
    $allRows[] = $entry;
}
?>

<style>
.gnss-card { border-radius:8px; background:#fff; box-shadow:0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2); }
.radar-box { border-radius:50%; border:2px dashed #007bff; width:130px; height:130px; margin:0 auto; position:relative;
  background:radial-gradient(circle,rgba(0,123,255,.05) 20%,rgba(0,123,255,.15) 100%); display:flex; align-items:center; justify-content:center; }
.radar-sweep { position:absolute; width:50%; height:50%; background:linear-gradient(45deg,rgba(0,123,255,.2) 0%,transparent 80%);
  top:0; left:0; transform-origin:bottom right; animation:radar-sweep 4s linear infinite; border-bottom-right-radius:100%; }
@keyframes radar-sweep { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
.const-pill { font-size:11.5px; margin:2px; padding:5px 10px; font-weight:600; border-radius:20px; }
.freq-pill { font-size:11px; margin:3px; padding:4px 10px; font-weight:600; border-radius:4px; cursor:default; }
.feat-badge { font-size:12px; padding:5px 9px; margin:3px; }
.timing-box { background:#f8f9fa; border:1px solid #dee2e6; color:#495057; border-radius:6px; padding:10px 14px; font-family:monospace; }
.timing-val { font-size:20px; font-weight:800; color:#2c3e50; }
.timing-lbl { font-size:10px; color:#6c757d; text-transform:uppercase; letter-spacing:.5px; }
.cap-badge { font-size:10.5px; margin:2px; padding:4px 8px; background:#343a40; color:#adb5bd; border-radius:4px; font-family:monospace; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2 align-items-center">
        <div class="col-sm-6">
          <h1 class="h3 mb-0 font-weight-bold text-dark">
            <i class="fas fa-satellite text-secondary mr-2"></i>GNSS &amp; GPS Hardware Telemetry
          </h1>
        </div>
        <div class="col-sm-6 text-right"><?= $nav_urls ?></div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <!-- Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-satellite mr-2"></i>GNSS Receiver Diagnostics</h5>
        <p class="text-secondary mb-1" style="font-size:14px;">Specifications of the on-board Global Navigation Satellite System (GNSS) chip. Multi-constellation and dual-frequency capability determine location tracking accuracy and spoofing resistance.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-3 border-right"><b class="d-block mb-1">Constellations:</b><p class="text-muted mb-0">GPS (US), GLONASS (RU), Galileo (EU), BeiDou (CN), QZSS (JP) — more = faster lock</p></div>
          <div class="col-md-3 border-right pl-md-3"><b class="d-block mb-1">Dual Frequency:</b><p class="text-muted mb-0">L1+L5 support gives centimeter-level accuracy &amp; protects against spoofing attacks</p></div>
          <div class="col-md-3 border-right pl-md-3"><b class="d-block mb-1">UTC Accuracy:</b><p class="text-muted mb-0">Nanosecond-precision UTC synchronization reveals receiver quality and timing capabilities</p></div>
          <div class="col-md-3 pl-md-3"><b class="d-block mb-1">Leap Second:</b><p class="text-muted mb-0">GPS time vs UTC offset; currently 18 seconds. Receiver must track this for accurate timestamps</p></div>
        </div>
      </div>

      <?php if ($gnss === null): ?>
        <div class="text-center py-5">
          <i class="fas fa-satellite fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No GNSS Hardware Detected</h4>
          <p class="text-muted">GPS hardware logs will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <div class="row mb-4">

          <!-- Card 1: Receiver Identity & Constellations -->
          <div class="col-md-4 mb-4">
            <div class="card gnss-card card-outline card-primary h-100">
              <div class="card-header">
                <h3 class="card-title font-weight-bold text-secondary"><i class="fas fa-satellite-dish mr-2"></i>Receiver &amp; Constellations</h3>
              </div>
              <div class="card-body">
                <div class="text-center mb-1">
                  <h5 class="font-weight-bold mb-0"><?= esc($gnss['model'] !== '—' ? $gnss['model'] : $gnss['id']) ?></h5>
                  <small class="text-muted">ID: <?= esc($gnss['id']) ?></small>
                  <?php if ($gnss['year'] !== '—'): ?>
                    <span class="badge badge-secondary ml-1"><?= esc($gnss['year']) ?></span>
                  <?php endif; ?>
                </div>

                <div class="radar-box my-3">
                  <div class="radar-sweep"></div>
                  <i class="fas fa-satellite fa-2x text-primary" style="z-index:2;"></i>
                </div>

                <div class="text-center mb-3">
                  <small class="font-weight-bold text-muted d-block mb-1" style="font-size:11px;text-transform:uppercase;">Constellations Locked</small>
                  <div class="d-flex flex-wrap justify-content-center">
                    <?php if (empty($gnss['constellations'])): ?>
                      <span class="badge badge-secondary const-pill">GPS Only</span>
                    <?php else: foreach ($gnss['constellations'] as $con):
                        $cl = strtolower($con);
                        $cc = strpos($cl,'glonass')!==false?'info':(strpos($cl,'galileo')!==false?'success':(strpos($cl,'beidou')!==false?'warning text-dark':(strpos($cl,'qzss')!==false?'purple text-white':'primary')));
                        ?>
                        <span class="badge badge-<?= $cc ?> const-pill"><?= esc($con) ?></span>
                    <?php endforeach; endif; ?>
                  </div>
                </div>

                <!-- Satellites bar -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between mb-1" style="font-size:12px;">
                    <span class="font-weight-bold text-secondary">Satellites (Used / Tracked):</span>
                    <span class="font-weight-bold text-dark"><?= $gnss['used'] ?> / <?= $gnss['tracked'] ?></span>
                  </div>
                  <div class="progress progress-sm">
                    <div class="progress-bar bg-info" style="width:<?= $gnss['tracked'] > 0 ? ($gnss['used']/$gnss['tracked'])*100 : 0 ?>%"></div>
                  </div>
                </div>

                <!-- Batch Size -->
                <?php if ($gnss['batch_size'] > 0): ?>
                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                  <span class="font-weight-bold text-secondary" style="font-size:12px;">Measurement Batch Size:</span>
                  <span class="badge badge-dark"><?= $gnss['batch_size'] ?> measurements</span>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Card 2: Timing Precision -->
          <div class="col-md-4 mb-4">
            <div class="card gnss-card card-outline card-dark h-100">
              <div class="card-header">
                <h3 class="card-title font-weight-bold text-secondary"><i class="fas fa-clock mr-2"></i>Timing &amp; Precision</h3>
              </div>
              <div class="card-body">
                <!-- UTC Accuracy -->
                <div class="timing-box mb-3">
                  <div class="timing-lbl">UTC Time Accuracy</div>
                  <div class="timing-val"><?= fmtNs($gnss['utc_accuracy_ns']) ?></div>
                  <div class="timing-lbl mt-1">Nanosecond-grade time synchronization</div>
                </div>

                <!-- Time Offset -->
                <div class="timing-box mb-3" style="background:#2d3436;">
                  <div class="timing-lbl">GPS Time Offset</div>
                  <div class="timing-val"><?= fmtNs($gnss['time_offset_ns']) ?></div>
                  <div class="timing-lbl mt-1">Clock bias from GPS system time</div>
                </div>

                <!-- Leap Second -->
                <div class="p-2 bg-light rounded mb-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <small class="font-weight-bold text-secondary d-block" style="font-size:11px;text-transform:uppercase;">GPS Leap Second</small>
                      <span class="font-weight-bold" style="font-size:20px;"><?= esc($gnss['leap_second']) ?></span>
                      <small class="text-muted d-block" style="font-size:11px;">seconds ahead of UTC</small>
                    </div>
                    <i class="fas fa-history fa-2x text-muted"></i>
                  </div>
                </div>

                <!-- Status supported -->
                <div class="p-2 bg-light rounded">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-secondary" style="font-size:12px;">GNSS Status Reporting:</span>
                    <span class="badge badge-<?= $gnss['status_supported'] ? 'success' : 'secondary' ?>">
                      <?= $gnss['status_supported'] ? 'Supported' : 'Not Supported' ?>
                    </span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="font-weight-bold text-secondary" style="font-size:12px;">GPS Provider:</span>
                    <span class="badge badge-<?= $gnss['gps_provider'] ? 'success' : 'danger' ?>">
                      <?= $gnss['gps_provider'] ? 'Available' : 'Unavailable' ?>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 3: Frequencies, Capabilities, Antenna -->
          <div class="col-md-4 mb-4">
            <div class="card gnss-card card-outline card-secondary h-100">
              <div class="card-header">
                <h3 class="card-title font-weight-bold text-secondary"><i class="fas fa-broadcast-tower mr-2"></i>Frequencies &amp; Capabilities</h3>
              </div>
              <div class="card-body">
                <!-- Antenna -->
                <div class="mb-3">
                  <small class="font-weight-bold text-muted d-block mb-1" style="font-size:11px;text-transform:uppercase;">Antenna Architecture</small>
                  <span class="font-weight-bold text-dark"><?= esc($gnss['antenna']) ?></span>
                  <?php if (!empty($gnss['ant_info'])): ?>
                    <?php
                    $hasPhaseCenter = !empty($gnss['ant_info']['phaseCenterOffsetCoordinateMillimeters']);
                    $hasCoupling    = !empty($gnss['ant_info']['couplingMatrix']);
                    ?>
                    <div class="mt-1">
                      <?php if ($hasPhaseCenter): ?><span class="badge badge-light border mr-1">Phase Center Offset</span><?php endif; ?>
                      <?php if ($hasCoupling): ?><span class="badge badge-light border">Coupling Matrix</span><?php endif; ?>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Frequencies -->
                <?php if (!empty($gnss['freqs'])): ?>
                <div class="mb-3">
                  <small class="font-weight-bold text-muted d-block mb-1" style="font-size:11px;text-transform:uppercase;">Supported Frequency Bands</small>
                  <div class="d-flex flex-wrap">
                    <?php foreach ($gnss['freqs'] as $freq):
                      $fk = strtoupper(trim($freq));
                      $fc = $freqColors[$fk] ?? 'secondary';
                      $fmhz = $freqMhz[$fk] ?? '';
                      ?>
                      <span class="badge badge-<?= $fc ?> freq-pill" title="<?= $fk ?>: <?= $fmhz ?>">
                        <?= esc($freq) ?><?= $fmhz ? " <small style='font-weight:400;font-size:9px;'>{$fmhz}</small>" : '' ?>
                      </span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <!-- Measurement Capabilities -->
                <?php if (!empty($gnss['meas_caps'])): ?>
                <div class="mb-3">
                  <small class="font-weight-bold text-muted d-block mb-1" style="font-size:11px;text-transform:uppercase;">Measurement Capabilities</small>
                  <div>
                    <?php foreach ($gnss['meas_caps'] as $cap): ?>
                      <span class="cap-badge"><?= esc(str_replace('_', ' ', $cap)) ?></span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <!-- Feature Matrix -->
                <div class="mb-2">
                  <small class="font-weight-bold text-muted d-block mb-1" style="font-size:11px;text-transform:uppercase;">Receiver Features</small>
                  <div class="d-flex flex-wrap">
                    <?php
                    $features = [
                        [$gnss['agps'],          'AGPS',          'fa-cloud'],
                        [$gnss['dead_reckoning'], 'Dead Reckoning','fa-route'],
                        [$gnss['raw_meas'],       'Raw Carrier Meas.','fa-wave-square'],
                        [$gnss['corrections'],    'DGNSS Corrections','fa-check-double'],
                        [$gnss['nav_messages'],   'Nav Messages',  'fa-scroll'],
                    ];
                    foreach ($features as [$ok, $lbl, $ico]): ?>
                      <span class="badge feat-badge badge-<?= $ok ? 'success' : 'secondary' ?>">
                        <i class="fas <?= $ico ?> mr-1"></i><?= $lbl ?>
                      </span>
                    <?php endforeach; ?>
                  </div>
                </div>

                <!-- AGPS Modes -->
                <?php if (!empty($gnss['agps_modes'])): ?>
                <div>
                  <small class="font-weight-bold text-muted d-block mb-1" style="font-size:11px;text-transform:uppercase;">AGPS Modes</small>
                  <div class="border rounded bg-light p-2">
                    <?php foreach ((array)$gnss['agps_modes'] as $mode): ?>
                      <span class="badge badge-light border mr-1 mb-1"><?= esc($mode) ?></span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>



      <?php endif; ?>
    </div>
  </section>
</div>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>