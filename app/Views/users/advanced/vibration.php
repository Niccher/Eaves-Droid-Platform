<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'device_id',
    ['has_vibrator', 'has_amplitude_control', 'has_frequency_control', 'has_minimum_limit', 'has_maximum_limit', 'q_factor', 'resonant_frequency_hz', 'primitives', 'composites', 'supported_effects']
);

$actuator = null;
$allRows  = [];


$primIcons = [
    'CLICK'      => 'fa-mouse-pointer',
    'THUD'       => 'fa-circle',
    'SPIN'       => 'fa-sync-alt',
    'TICK'       => 'fa-clock',
    'LOW_TICK'   => 'fa-dot-circle',
    'SLOW_RISE'  => 'fa-arrow-up',
    'QUICK_RISE' => 'fa-bolt',
    'QUICK_FALL' => 'fa-arrow-down',
    'HEAVY_CLICK'=> 'fa-hand-rock',
];

foreach ($rows as $idx => $r) {
    $primitives = [];
    if (!empty($r['primitives'])) {
        $d = is_string($r['primitives']) ? json_decode($r['primitives'], true) : $r['primitives'];
        $primitives = is_array($d) ? $d : [];
    }
    $composite = [];
    if (!empty($r['composite_primitives'])) {
        $d = is_string($r['composite_primitives']) ? json_decode($r['composite_primitives'], true) : $r['composite_primitives'];
        $composite = is_array($d) ? $d : [];
    }
    $freqRange = [];
    if (!empty($r['frequency_range_hz'])) {
        $d = is_string($r['frequency_range_hz']) ? json_decode($r['frequency_range_hz'], true) : $r['frequency_range_hz'];
        $freqRange = is_array($d) ? $d : [];
    }

    $entry = [
        'has_vibrator'       => !empty($r['has_vibrator']),
        'amp_ctrl'           => !empty($r['supports_amplitude_control']),
        'freq_ctrl'          => !empty($r['supports_frequency_control']),
        'ext_ctrl'           => !empty($r['supports_external_control']),
        'braking'            => !empty($r['braking_supported']),
        'envelope'           => !empty($r['envelope_supported']),
        'pwm'                => !empty($r['pwm_supported']),
        'waveform'           => !empty($r['waveform_supported']),
        'id'                 => $r['actuator_id'] ?? 'Default Actuator',
        'max_amplitude'      => (int)($r['max_amplitude'] ?? 0),
        'resonant_freq'      => (float)($r['resonant_frequency_hz'] ?? 0),
        'q_factor'           => (float)($r['q_factor'] ?? 0),
        'type'               => strtoupper($r['actuator_type'] ?? 'ERM'),
        'freq_range'         => $freqRange,
        'primitives'         => $primitives,
        'composite'          => $composite,
        'extracted'          => !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—',
        'raw_ts'             => (int)($r['extracted_at'] ?? 0),
        'row_id'             => $r['id'] ?? 0,
    ];

    if ($idx === 0) {
        $actuator = $entry;
    }
    $allRows[] = $entry;
}
?>

<style>
.vib-card { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2); }
.vib-card:hover { transform: translateY(-2px); box-shadow: 0 4px 14px rgba(0,0,0,.15); transition: all .15s; }
.waveform-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 14px 20px; position: relative; overflow: hidden; height: 90px; display:flex; align-items: center; justify-content: center; }
.wave-svg { width:100%; height:100%; stroke:#6c757d; stroke-width:2.5; fill:none; }
.metric-box { background: #f8f9fa; border-radius: 6px; padding: 10px; text-align: center; }
.metric-val { font-size: 24px; font-weight: 800; color: #343a40; line-height:1.1; }
.metric-lbl { font-size: 10px; text-transform: uppercase; letter-spacing:.5px; color: #6c757d; font-weight:600; }
.prim-pill { display:inline-flex; align-items:center; gap:5px; background:#2d3436; color:#dfe6e9; border-radius:20px; padding:4px 10px; font-size:12px; font-weight:600; margin:3px; }
.prim-pill i { color:#00cec9; }
.comp-pill { display:inline-flex; align-items:center; gap:5px; background:#6c5ce7; color:#fff; border-radius:20px; padding:4px 10px; font-size:12px; font-weight:600; margin:3px; }
.freq-bar-track { background:#e9ecef; border-radius:4px; height:18px; position:relative; overflow:visible; }
.freq-bar-fill { background: linear-gradient(90deg,#17a2b8,#007bff); border-radius:4px; height:18px; }
.feat-badge { font-size:12px; padding:6px 10px; margin:3px; }
.type-lra { background:#007bff; color:#fff; }
.type-erm { background:#e67e22; color:#fff; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2 align-items-center">
        <div class="col-sm-6">
          <h1 class="h3 mb-0 font-weight-bold text-dark">
            <i class="fas fa-wave-square text-secondary mr-2"></i>Haptic Actuator Diagnostics
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
        <h5 class="font-weight-bold text-info"><i class="fas fa-wave-square mr-2"></i>Vibration &amp; Haptic Engine</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">This profile details the physical haptic vibrator motor. Resonant frequency and Q factor confirm hardware authenticity and performance characteristics.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Actuator Types:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>LRA (Linear Resonant):</b> Precise, efficient haptics — used in flagship devices</li>
              <li><b>ERM (Eccentric Rotating Mass):</b> Traditional vibration motor — less precise</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Key Metrics:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Resonant Frequency:</b> Peak efficiency Hz (typically 150–250Hz for LRA)</li>
              <li><b>Q Factor:</b> Damping rate (higher = sharper, more tactile response)</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Primitives &amp; Composites:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li>Pre-compiled effect modules (CLICK, TICK, THUD) for tactile UI feedback</li>
              <li>Composite primitives are layered multi-step sequences</li>
            </ul>
          </div>
        </div>
      </div>

      <?php if ($actuator === null): ?>
        <div class="text-center py-5">
          <i class="fas fa-wave-square fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Vibration Actuator Data</h4>
          <p class="text-muted">Actuator diagnostics will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <!-- Main Dashboard: Latest Snapshot -->
        <div class="row mb-4">

          <!-- Card 1: Actuator Hardware Profile -->
          <div class="col-md-6 mb-4">
            <div class="card vib-card card-outline card-primary h-100">
              <div class="card-header">
                <h3 class="card-title font-weight-bold text-secondary">
                  <i class="fas fa-microchip mr-2"></i>Actuator Hardware Profile
                </h3>
              </div>
              <div class="card-body">
                <div class="text-center mb-3">
                  <h5 class="font-weight-bold mb-1"><?= esc($actuator['id']) ?></h5>
                  <span class="badge px-2 py-1 <?= $actuator['type'] === 'LRA' ? 'type-lra' : 'type-erm' ?>">
                    <?= esc($actuator['type']) ?> Actuator
                  </span>
                  <?php if (!$actuator['has_vibrator']): ?>
                    <span class="badge badge-danger ml-1">No Vibrator Detected</span>
                  <?php endif; ?>
                </div>

                <!-- Waveform viz -->
                <div class="waveform-box mb-4">
                  <?php
                    $amp = $actuator['max_amplitude'];
                    $a1 = max(2, $amp / 30); $a2 = max(2, $amp / 25); $a3 = max(2, $amp / 20);
                  ?>
                  <svg class="wave-svg" viewBox="0 0 100 30" preserveAspectRatio="none">
                    <path d="M 0,15 C 10,<?= 15-$a1 ?> 15,<?= 15+$a1 ?> 25,15 C 35,<?= 15-$a2 ?> 40,<?= 15+$a2 ?> 50,15 C 60,<?= 15-$a3 ?> 65,<?= 15+$a3 ?> 75,15 C 85,<?= 15-$a1 ?> 90,<?= 15+$a1 ?> 100,15"/>
                  </svg>
                  <small style="position:absolute;bottom:5px;right:8px;color:#28a745;font-size:9px;font-family:monospace;">WAVE DIAGNOSTIC</small>
                </div>

                <!-- Three metric boxes -->
                <div class="row mb-3 text-center">
                  <div class="col-4">
                    <div class="metric-box">
                      <div class="metric-val"><?= $actuator['resonant_freq'] ?: '—' ?></div>
                      <div class="metric-lbl">Resonant Hz</div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="metric-box">
                      <div class="metric-val"><?= $actuator['q_factor'] ?: '—' ?></div>
                      <div class="metric-lbl">Q Factor</div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="metric-box">
                      <div class="metric-val"><?= $actuator['max_amplitude'] ?></div>
                      <div class="metric-lbl">Max Amplitude</div>
                    </div>
                  </div>
                </div>

                <!-- Amplitude bar -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between mb-1" style="font-size:12px;">
                    <span class="font-weight-bold text-secondary">Voltage Amplitude:</span>
                    <span class="font-weight-bold text-dark"><?= $actuator['max_amplitude'] ?> / 255</span>
                  </div>
                  <div class="progress progress-sm">
                    <div class="progress-bar bg-success" style="width:<?= ($actuator['max_amplitude']/255)*100 ?>%"></div>
                  </div>
                </div>

                <!-- Frequency Range bar -->
                <?php
                  $fmin = (float)($actuator['freq_range']['min'] ?? $actuator['freq_range'][0] ?? 0);
                  $fmax = (float)($actuator['freq_range']['max'] ?? $actuator['freq_range'][1] ?? 0);
                ?>
                <?php if ($fmin > 0 || $fmax > 0): ?>
                <div class="mb-3">
                  <div class="d-flex justify-content-between mb-1" style="font-size:12px;">
                    <span class="font-weight-bold text-secondary">Operating Frequency Range:</span>
                    <span class="font-weight-bold text-info"><?= $fmin ?> – <?= $fmax ?> Hz</span>
                  </div>
                  <div class="d-flex align-items-center" style="gap:6px;font-size:11px;color:#6c757d;">
                    <span><?= $fmin ?> Hz</span>
                    <div class="flex-fill freq-bar-track">
                      <div class="freq-bar-fill" style="width:100%"></div>
                    </div>
                    <span><?= $fmax ?> Hz</span>
                  </div>
                </div>
                <?php endif; ?>

                <div class="border-top pt-2 mt-2 text-muted d-flex justify-content-between" style="font-size:11px;">
                  <span><i class="fas fa-wave-square mr-1"></i>Actuator Active</span>
                  <span><i class="fas fa-clock mr-1"></i><?= $actuator['extracted'] ?></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 2: Driver Capabilities & Primitives -->
          <div class="col-md-6 mb-4">
            <div class="card vib-card card-outline card-secondary h-100">
              <div class="card-header">
                <h3 class="card-title font-weight-bold text-secondary">
                  <i class="fas fa-cogs mr-2"></i>Driver Capabilities &amp; Primitives
                </h3>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <span class="font-weight-bold text-secondary d-block mb-2" style="font-size:12px;">Supported Driver Features:</span>
                  <div class="d-flex flex-wrap">
                    <?php
                    $feats = [
                        [$actuator['amp_ctrl'],   'Amplitude Ctrl',   'fa-sliders-h'],
                        [$actuator['freq_ctrl'],  'Frequency Ctrl',   'fa-tachometer-alt'],
                        [$actuator['ext_ctrl'],   'External Ctrl',    'fa-plug'],
                        [$actuator['braking'],    'Haptic Braking',   'fa-stop-circle'],
                        [$actuator['envelope'],   'Envelope Mod',     'fa-chart-area'],
                        [$actuator['pwm'],        'PWM Modulation',   'fa-wave-square'],
                        [$actuator['waveform'],   'Custom Waveforms', 'fa-project-diagram'],
                    ];
                    foreach ($feats as [$ok, $lbl, $ico]): ?>
                      <span class="badge feat-badge badge-<?= $ok ? 'success' : 'secondary' ?>">
                        <i class="fas <?= $ico ?> mr-1"></i><?= $ok ? '✓' : '✗' ?> <?= $lbl ?>
                      </span>
                    <?php endforeach; ?>
                  </div>
                </div>

                <hr class="my-2">

                <!-- Haptic Primitives -->
                <div class="mb-3">
                  <span class="font-weight-bold text-secondary d-block mb-2" style="font-size:12px;">
                    <i class="fas fa-fingerprint mr-1"></i>Haptic Effect Primitives
                    <?php if (!empty($actuator['primitives'])): ?>
                      <span class="badge badge-dark ml-1"><?= count($actuator['primitives']) ?></span>
                    <?php endif; ?>
                  </span>
                  <?php if (empty($actuator['primitives'])): ?>
                    <p class="text-muted small mb-0">No pre-compiled driver primitives configured.</p>
                  <?php else: ?>
                    <div style="max-height:130px;overflow-y:auto;">
                      <?php foreach ($actuator['primitives'] as $prim):
                        $primKey = strtoupper(is_array($prim) ? ($prim['name'] ?? '') : $prim);
                        $ico = $primIcons[$primKey] ?? 'fa-wave-square';
                        $label = ucwords(strtolower(str_replace('_', ' ', $primKey)));
                        ?>
                        <span class="prim-pill" title="Haptic Primitive: <?= esc($primKey) ?>">
                          <i class="fas <?= $ico ?>"></i><?= esc($label) ?>
                        </span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Composite Primitives -->
                <?php if (!empty($actuator['composite'])): ?>
                <div class="mb-2">
                  <span class="font-weight-bold text-secondary d-block mb-2" style="font-size:12px;">
                    <i class="fas fa-layer-group mr-1"></i>Composite Effects
                    <span class="badge badge-secondary ml-1"><?= count($actuator['composite']) ?></span>
                  </span>
                  <div style="max-height:100px;overflow-y:auto;">
                    <?php foreach ($actuator['composite'] as $cp):
                      $cpName = is_array($cp) ? ($cp['name'] ?? json_encode($cp)) : $cp;
                      ?>
                      <span class="comp-pill"><i class="fas fa-layer-group"></i><?= esc(ucwords(strtolower(str_replace('_', ' ', $cpName)))) ?></span>
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