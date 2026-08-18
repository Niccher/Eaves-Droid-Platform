<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'device_id',
    ['gpu_renderer_json', 'media_codecs_json', 'input_devices_json']
);

$snapshot = null;
$allRows  = [];


foreach ($rows as $idx => $r) {
    $parseJ = fn($v) => is_string($v) ? (json_decode($v, true) ?: []) : (is_array($v) ? $v : []);

    $gpu    = $parseJ($r['gpu_renderer_json'] ?? '{}');
    $codecs = $parseJ($r['media_codecs_json'] ?? '[]');
    $inputs = $parseJ($r['input_devices_json'] ?? '[]');

    // Decode Vulkan version (packed uint32: major<<22 | minor<<12 | patch)
    $vulkanRaw = (int)($gpu['vulkan_api_version'] ?? 0);
    $vulkanStr = '—';
    if ($vulkanRaw > 0) {
        $vMaj = $vulkanRaw >> 22;
        $vMin = ($vulkanRaw >> 12) & 0x3ff;
        $vPat = $vulkanRaw & 0xfff;
        $vulkanStr = "{$vMaj}.{$vMin}.{$vPat}";
    }

    // Parse GL extensions
    $glExt = [];
    if (!empty($gpu['gl_extensions'])) {
        $extStr = is_array($gpu['gl_extensions']) ? implode(' ', $gpu['gl_extensions']) : $gpu['gl_extensions'];
        $glExt = array_filter(explode(' ', $extStr));
    }

    // Codec grouping
    $encoders = []; $decoders = [];
    foreach ((array)$codecs as $c) {
        $cname = is_array($c) ? ($c['name'] ?? '') : $c;
        $ctype = is_array($c) ? ($c['type'] ?? '') : '';
        if (stripos($ctype,'encoder') !== false || stripos($cname,'encoder') !== false) {
            $encoders[] = $c;
        } else {
            $decoders[] = $c;
        }
    }

    // Input device sources decoder
    $srcMap = [
        0x101=>'KEYBOARD',0x201=>'DPAD',0x301=>'GAMEPAD',0x501=>'JOYSTICK',
        0x1002=>'TOUCHSCREEN',0x2002=>'MOUSE',0x4002=>'STYLUS',0x8002=>'TRACKBALL',0x100000=>'SENSOR',
    ];
    $kbTypes = [0=>'None',1=>'Alpha',2=>'Numeric'];

    $entry = [
        'gpu'      => $gpu,
        'codecs'   => $codecs,
        'encoders' => $encoders,
        'decoders' => $decoders,
        'inputs'   => $inputs,
        'vulkan'   => $vulkanStr,
        'gl_ext'   => $glExt,
        'srcMap'   => $srcMap,
        'kbTypes'  => $kbTypes,
        'extracted'=> !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—',
        'row_id'   => $r['id'] ?? 0,
    ];

    if ($idx === 0) $snapshot = $entry;
    $allRows[] = $entry;
}

// Codec name cleaner
function cleanCodecName(string $n): string {
    $n = preg_replace('/^(OMX\.|c2\.(android\.|google\.|qcom\.|samsung\.|mtk\.)?)/', '', $n);
    return $n;
}

// Detect codec color (hardware vs software)
function codecColor(string $n): string {
    $nl = strtolower($n);
    if (strpos($nl,'qcom')!==false||strpos($nl,'adreno')!==false||strpos($nl,'samsung')!==false||strpos($nl,'mali')!==false||strpos($nl,'mediatek')!==false||strpos($nl,'mtk')!==false) return 'success';
    if (strpos($nl,'google')!==false||strpos($nl,'android')!==false||strpos($nl,'c2.android')!==false) return 'primary';
    return 'secondary';
}

// Codec display name highlights
function codecHighlight(string $n): string {
    $nl = strtolower($n);
    foreach (['h264','avc','h265','hevc','av1','vp8','vp9','aac','mp3','opus','flac','vorbis'] as $k) {
        if (strpos($nl,$k)!==false) return strtoupper($k);
    }
    return '';
}
?>

<style>
.gpu-card { border-radius:8px; background:#fff; box-shadow:0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2); }
.gpu-hero { background:linear-gradient(135deg,#6c757d,#5a6268,#495057); border-radius:8px; padding:20px; color:#fff; }
.gpu-name { font-size:22px; font-weight:900; color:#fff; font-family:monospace; }
.gpu-sub  { font-size:12px; color:rgba(255,255,255,0.85); }
.ext-pill { font-size:9.5px; margin:1px; padding:2px 5px; background:#1e3a5f; color:#74b9ff; border-radius:3px; font-family:monospace; }
.codec-row { display:flex; align-items:center; padding:4px 8px; border-bottom:1px solid #f5f5f5; font-size:12.5px; }
.codec-row:last-child { border-bottom:none; }
.codec-name { flex:1; font-weight:600; }
.input-card { background:#f8f9fa; border:1px solid #e9ecef; border-radius:6px; padding:10px; margin-bottom:8px; }
.vk-badge { background:#6c5ce7; color:#fff; border-radius:4px; padding:3px 8px; font-family:monospace; font-size:11px; font-weight:700; }
.ogl-badge { background:#e17055; color:#fff; border-radius:4px; padding:3px 8px; font-family:monospace; font-size:11px; font-weight:700; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-memory text-secondary mr-2"></i>Hardware Graphics &amp; GPU</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">GPU renderer, Vulkan version, media codecs, and input device inventory</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>

      <!-- Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-cubes mr-2"></i>Graphics &amp; GPU Security Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Auditing system graphics pipelines, Vulkan API availability, media encoders/decoders, and hardware peripheral buses for capability verification.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">GPU Capabilities:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Renderer Details:</b> Identifies target GPU hardware architecture.</li>
              <li><b>Vulkan Version:</b> Identifies modern low-overhead computing API support.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Codec Matrix:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>HW vs SW Codecs:</b> Identifies energy-efficient hardware decoding vs virtual fallback packages.</li>
              <li><b>Capabilities:</b> Trace critical formats like AV1, HEVC, H264, Opus, and VP9.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Input Accessories:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Keyboards &amp; Pointers:</b> Lists attached physical inputs, touchscreen controllers, and DPADs.</li>
              <li><b>Haptic Feedback:</b> Identifies vibration motor interfaces on auxiliary devices.</li>
            </ul>
          </div>
        </div>
      </div>

      <?php if ($snapshot === null): ?>
        <div class="text-center py-5">
          <i class="fas fa-memory fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Graphics Data</h4>
          <p class="text-muted">Hardware graphics snapshots will appear here once extracted.</p>
        </div>
      <?php else:
        $gpu    = $snapshot['gpu'];
        $codecs = $snapshot['codecs'];
        $inputs = $snapshot['inputs'];
        ?>

        <div class="row mb-4">

          <!-- Card 1: GPU Renderer Profile -->
          <div class="col-md-6 mb-4">
            <div class="card gpu-card h-100">
              <div class="card-header"><h3 class="card-title font-weight-bold text-secondary"><i class="fas fa-microchip mr-2"></i>GPU Renderer Profile</h3></div>
              <div class="card-body p-0">
                <div class="gpu-hero m-3 mb-2">
                  <div class="gpu-name"><?= esc($gpu['gl_renderer'] ?? 'Unknown GPU') ?></div>
                  <div class="gpu-sub mt-1"><?= esc($gpu['gl_vendor'] ?? '') ?> &bull; <?= esc($gpu['egl_vendor'] ?? '') ?></div>
                  <div class="mt-2 d-flex flex-wrap gap-1">
                    <?php if (!empty($gpu['opengl_es_version'])): ?>
                      <span class="ogl-badge">OpenGL ES <?= esc($gpu['opengl_es_version']) ?></span>
                    <?php elseif (!empty($gpu['gl_version'])): ?>
                      <span class="ogl-badge"><?= esc($gpu['gl_version']) ?></span>
                    <?php endif; ?>
                    <?php if ($snapshot['vulkan'] !== '—'): ?>
                      <span class="vk-badge ml-1">Vulkan <?= esc($snapshot['vulkan']) ?></span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="px-3 pb-2">
                  <table class="table table-sm table-borderless mb-0" style="font-size:12.5px;">
                    <?php if (!empty($gpu['gl_vendor'])): ?><tr><th class="text-muted">GL Vendor</th><td class="font-weight-bold"><?= esc($gpu['gl_vendor']) ?></td></tr><?php endif; ?>
                    <?php if (!empty($gpu['egl_version'])): ?><tr><th class="text-muted">EGL Version</th><td><?= esc($gpu['egl_version']) ?></td></tr><?php endif; ?>
                    <?php if (!empty($gpu['egl_vendor'])): ?><tr><th class="text-muted">EGL Vendor</th><td><?= esc($gpu['egl_vendor']) ?></td></tr><?php endif; ?>
                    <?php foreach ($gpu as $k => $v): if (in_array($k, ['gl_renderer','gl_vendor','gl_version','gl_extensions','egl_version','egl_vendor','opengl_es_version','vulkan_api_version'])) continue; if (!is_scalar($v)) continue; ?>
                      <tr><th class="text-muted"><?= esc(ucwords(str_replace('_',' ',$k))) ?></th><td><?= esc($v) ?></td></tr>
                    <?php endforeach; ?>
                  </table>

                  <!-- GL Extensions -->
                  <?php if (!empty($snapshot['gl_ext'])): ?>
                  <div class="mt-2">
                    <a class="font-weight-bold text-secondary" style="font-size:12px;cursor:pointer;" data-toggle="collapse" href="#gl-ext-<?= $snapshot['row_id'] ?>">
                      <i class="fas fa-code mr-1"></i>GL Extensions <span class="badge badge-secondary"><?= count($snapshot['gl_ext']) ?></span>
                    </a>
                    <div id="gl-ext-<?= $snapshot['row_id'] ?>" class="collapse mt-1" style="max-height:160px;overflow-y:auto;background:#0d1117;border-radius:4px;padding:8px;">
                      <?php foreach ($snapshot['gl_ext'] as $ext): ?>
                        <span class="ext-pill"><?= esc($ext) ?></span>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 2: Media Codecs -->
          <div class="col-md-6 mb-4">
            <div class="card gpu-card h-100">
              <div class="card-header">
                <h3 class="card-title font-weight-bold text-secondary">
                  <i class="fas fa-film mr-2"></i>Media Codecs
                  <span class="badge badge-warning ml-1"><?= count($codecs) ?> total</span>
                  <span class="badge badge-success ml-1"><?= count($snapshot['encoders']) ?> enc</span>
                  <span class="badge badge-info ml-1"><?= count($snapshot['decoders']) ?> dec</span>
                </h3>
              </div>
              <div class="card-body p-0">
                <!-- Encoders -->
                <?php if (!empty($snapshot['encoders'])): ?>
                <div class="px-3 pt-2 pb-1">
                  <a class="font-weight-bold text-success d-block mb-1" style="font-size:12px;" data-toggle="collapse" href="#enc-list">
                    <i class="fas fa-upload mr-1"></i>Encoders (<?= count($snapshot['encoders']) ?>)
                  </a>
                  <div id="enc-list" class="collapse" style="max-height:200px;overflow-y:auto;">
                    <?php foreach ($snapshot['encoders'] as $c):
                      $cn = is_array($c) ? ($c['name'] ?? '') : $c;
                      $hl = codecHighlight($cn);
                      $clr = codecColor($cn);
                      $clean = cleanCodecName($cn);
                      ?>
                      <div class="codec-row">
                        <span class="codec-name"><?= esc($clean) ?> <?php if ($hl): ?><span class="badge badge-<?= $clr ?>" style="font-size:9px;"><?= $hl ?></span><?php endif; ?></span>
                        <span class="badge badge-<?= $clr ?>" style="font-size:9px;"><?= strpos(strtolower($cn),'qcom')!==false||strpos(strtolower($cn),'samsung')!==false ? 'HW' : 'SW' ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>
                <!-- Decoders -->
                <?php if (!empty($snapshot['decoders'])): ?>
                <div class="px-3 pt-2 pb-2">
                  <a class="font-weight-bold text-info d-block mb-1" style="font-size:12px;" data-toggle="collapse" href="#dec-list">
                    <i class="fas fa-download mr-1"></i>Decoders (<?= count($snapshot['decoders']) ?>)
                  </a>
                  <div id="dec-list" class="collapse show" style="max-height:220px;overflow-y:auto;">
                    <?php foreach ($snapshot['decoders'] as $c):
                      $cn = is_array($c) ? ($c['name'] ?? '') : $c;
                      $hl = codecHighlight($cn);
                      $clr = codecColor($cn);
                      $clean = cleanCodecName($cn);
                      ?>
                      <div class="codec-row">
                        <span class="codec-name"><?= esc($clean) ?> <?php if ($hl): ?><span class="badge badge-<?= $clr ?>" style="font-size:9px;"><?= $hl ?></span><?php endif; ?></span>
                        <span class="badge badge-<?= $clr ?>" style="font-size:9px;"><?= strpos(strtolower($cn),'qcom')!==false||strpos(strtolower($cn),'samsung')!==false ? 'HW' : 'SW' ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 3: Input Devices -->
        <?php if (!empty($inputs)): ?>
        <div class="card gpu-card mb-4">
          <div class="card-header">
            <h3 class="card-title font-weight-bold text-secondary">
              <i class="fas fa-keyboard mr-2"></i>Input Devices
              <span class="badge badge-secondary ml-1"><?= count($inputs) ?></span>
            </h3>
            <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
          </div>
          <div class="card-body">
            <div class="row">
              <?php foreach ($inputs as $dev):
                $dname  = is_array($dev) ? ($dev['name'] ?? 'Unknown Device') : $dev;
                $dsrc   = is_array($dev) ? ((int)($dev['sources'] ?? 0)) : 0;
                $dkb    = is_array($dev) ? ($snapshot['kbTypes'][(int)($dev['keyboard_type'] ?? 0)] ?? 'None') : 'None';
                $dhas_v = is_array($dev) ? !empty($dev['has_vibrator']) : false;
                $ddesc  = is_array($dev) ? ($dev['descriptor'] ?? '') : '';
                // Decode sources
                $srcLabels = [];
                foreach ($snapshot['srcMap'] as $mask => $label) { if ($dsrc & $mask) $srcLabels[] = $label; }
                ?>
                <div class="col-md-4 mb-3">
                  <div class="input-card">
                    <div class="font-weight-bold mb-1" style="font-size:13px;"><?= esc($dname) ?></div>
                    <?php if (!empty($srcLabels)): ?>
                      <div class="mb-1">
                        <?php foreach ($srcLabels as $sl): ?>
                          <span class="badge badge-secondary mr-1" style="font-size:10px;"><?= esc($sl) ?></span>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                    <div style="font-size:11.5px;color:#6c757d;">
                      Keyboard: <b><?= $dkb ?></b> &bull;
                      Vibrator: <b><?= $dhas_v ? 'Yes' : 'No' ?></b>
                    </div>
                    <?php if ($ddesc): ?>
                      <div style="font-size:10px;color:#adb5bd;font-family:monospace;word-break:break-all;" title="<?= esc($ddesc) ?>"><?= esc(substr($ddesc,0,32)) ?>…</div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>



      <?php endif; ?>
    </div>
  </section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>