<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
// Group & deduplicate: keep the latest state per unique device using audio_device_id or product_name
$seen = [];
$unique = [];
foreach ($rows as $r) {
    $devId = $r['audio_device_id'] ?? 0;
    $name  = $r['product_name'] ?? '—';
    $key   = strtolower(trim((string)$devId)) . '_' . strtolower(trim($name));
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $unique[]   = $r;
    }
}
$rows = $unique;

function parseAudioList($val): array {
    if (empty($val)) return [];
    if (is_array($val)) return $val;
    $decoded = json_decode($val, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
    return array_filter(array_map('trim', explode(',', $val)));
}
?>

<style>
.audio-card     { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.audio-hero     { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; position: relative; }
.audio-hero::after { content: ''; position: absolute; right: 15px; bottom: 15px; font-family: "Font Awesome 5 Free"; font-weight: 900; content: "\f028"; font-size: 54px; opacity: 0.05; }
.audio-kv       { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.audio-kv:last-child { border-bottom: none; }
.audio-kv .ak   { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.audio-kv .av   { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.pill-badge     { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; margin: 2px; }
.wave-visualizer { height: 35px; display: flex; align-items: center; gap: 3px; background: rgba(0,0,0,0.15); border-radius: 6px; padding: 0 12px; margin-top: 8px; overflow: hidden; }
.wave-bar       { width: 3px; height: 15px; background: #fff; border-radius: 2px; animation: bounce 1.2s infinite ease-in-out; }
.wave-bar:nth-child(2) { height: 25px; animation-delay: 0.15s; }
.wave-bar:nth-child(3) { height: 18px; animation-delay: 0.3s; }
.wave-bar:nth-child(4) { height: 8px;  animation-delay: 0.45s; }
.wave-bar:nth-child(5) { height: 22px; animation-delay: 0.6s; }
.wave-bar:nth-child(6) { height: 14px; animation-delay: 0.75s; }
@keyframes bounce { 0%, 100% { transform: scaleY(1); } 50% { transform: scaleY(1.8); } }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-volume-up text-info mr-2"></i>Audio Devices</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Hardware peripherals, audio input/output sinks, latency ranges, and sample rates</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>
      
      <!-- Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-volume-up mr-2"></i>Audio Device Security Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Auditing system audio peripherals, physical hardware drivers, Bluetooth speaker/headset sinks, input sources, and codec latency configurations.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Device Inventory:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Physical Routing:</b> Identifies driver addresses and connected peripheral models.</li>
              <li><b>Bus Interface:</b> Traces hardware connection parameters.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Signal Capabilities:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Sample Rates:</b> Lists supported playback rates (e.g. 44.1kHz up to 96kHz).</li>
              <li><b>Channel Profiles:</b> Validates mono/stereo configurations and masks.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Latency &amp; Gain:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Gain Boundaries:</b> Maps decibel range step parameters to verify volume controls.</li>
              <li><b>Latency Bounds:</b> Captures millisecond buffers to evaluate audio timing latency.</li>
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
          <i class="fas fa-volume-mute fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Audio Devices</h4>
          <p class="text-muted">Audio peripherals will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <div class="row">
        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $devId = $r['audio_device_id'] ?? '—';
          $name = $r['product_name'] ?? 'Generic Peripheral';
          $address = $r['address'] ?? '—';
          $isSink = !empty($r['is_sink']);
          $isSource = !empty($r['is_source']);
          $encoding = $r['encoding'] ?? '—';
          $format = $r['format'] ?? '—';
          $gainMin = $r['gain_min'] ?? '—';
          $gainMax = $r['gain_max'] ?? '—';
          $gainStep = $r['gain_step'] ?? '—';
          $latLow = $r['latency_low_ms'] ?? '—';
          $latHigh = $r['latency_high_ms'] ?? '—';
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          $rates = parseAudioList($r['sample_rates'] ?? '');
          $channels = parseAudioList($r['channel_masks'] ?? '');
          $counts = parseAudioList($r['channel_counts'] ?? '');
          
          $typeMap = ['in' => 'Input', 'out' => 'Output', 'both' => 'Duplex'];
          $type = $typeMap[$r['device_type'] ?? ''] ?? ($r['device_type'] ?? 'System Output');
          ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="audio-card h-100">
              
              <!-- Hero section -->
              <div class="audio-hero">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="badge badge-info text-white font-weight-bold" style="border-radius:12px; padding:3px 9px;">
                    <i class="fas fa-plug mr-1"></i>ID: <?= esc($devId) ?>
                  </span>
                  <div>
                    <?php if ($isSink && $isSource): ?>
                      <span class="badge badge-success font-weight-bold">DUPLEX</span>
                    <?php elseif ($isSink): ?>
                      <span class="badge badge-primary font-weight-bold">OUTPUT (SINK)</span>
                    <?php elseif ($isSource): ?>
                      <span class="badge badge-warning text-dark font-weight-bold">INPUT (SOURCE)</span>
                    <?php else: ?>
                      <span class="badge badge-secondary font-weight-bold">PERIPHERAL</span>
                    <?php endif; ?>
                  </div>
                </div>
                <h5 class="font-weight-bold mb-1 text-truncate" title="<?= esc($name) ?>"><?= esc($name) ?></h5>
                <small class="d-block" style="opacity:0.75; font-family:monospace;"><?= esc($type) ?> / <?= esc($address) ?></small>
                
                <div class="wave-visualizer">
                  <div class="wave-bar"></div>
                  <div class="wave-bar"></div>
                  <div class="wave-bar"></div>
                  <div class="wave-bar"></div>
                  <div class="wave-bar"></div>
                  <div class="wave-bar"></div>
                  <small class="text-white ml-auto" style="font-size:10px; font-family:monospace; letter-spacing:0.5px; opacity:0.85;">ACTIVE BUS</small>
                </div>
              </div>

              <!-- Details section -->
              <div class="p-3">
                <div class="audio-kv">
                  <span class="ak">Format / Encoding</span>
                  <span class="av"><span class="badge badge-secondary"><?= esc($format) ?></span> / <?= esc($encoding) ?></span>
                </div>
                <div class="audio-kv">
                  <span class="ak">Latency Specs</span>
                  <span class="av"><?= $latLow ?>ms - <?= $latHigh ?>ms (Low/High)</span>
                </div>
                <div class="audio-kv">
                  <span class="ak">Gain Range</span>
                  <span class="av"><?= $gainMin ?> to <?= $gainMax ?> dB (Step: <?= $gainStep ?>)</span>
                </div>
                
                <!-- Lists -->
                <?php if (!empty($rates)): ?>
                <div class="mt-3 mb-1">
                  <span class="ak d-block mb-1" style="font-size:10px; font-weight:700; color:#6c757d; text-transform:uppercase;">Supported Sample Rates</span>
                  <div>
                    <?php foreach ($rates as $r): ?>
                      <span class="pill-badge bg-light border text-dark font-weight-normal"><?= esc($r) ?> Hz</span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($channels) || !empty($counts)): ?>
                <div class="mt-2 mb-1">
                  <span class="ak d-block mb-1" style="font-size:10px; font-weight:700; color:#6c757d; text-transform:uppercase;">Channel Profiles</span>
                  <div>
                    <?php foreach ($counts as $c): ?>
                      <span class="pill-badge bg-info text-white"><?= esc($c) ?> Ch</span>
                    <?php endforeach; ?>
                    <?php foreach ($channels as $ch): ?>
                      <span class="pill-badge bg-secondary text-white font-weight-normal" style="font-family:monospace; font-size:10px;"><?= esc($ch) ?></span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <div class="audio-kv mt-3">
                  <span class="ak">Extracted</span>
                  <span class="sv" style="font-size:11px;"><?= $ts ?></span>
                </div>

                <div class="mt-3 text-right">
                  <button class="btn btn-sm btn-outline-danger delete-row py-0"
                    data-id="<?= $rid ?>"
                    data-url="<?= base_url('advanced/hardware/audio_devices/delete') ?>">
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