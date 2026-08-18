<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
// Helper: parse JSON or comma-separated values
function parseCamField($v): array {
    if (empty($v)) return [];
    if (is_array($v)) return $v;
    $decoded = json_decode($v, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
    return array_filter(array_map('trim', explode(',', $v)));
}

// Capability integer labels
$capLabels = [
    0=>'BACKWARD_COMPATIBLE',1=>'MANUAL_SENSOR',2=>'MANUAL_POST_PROC',3=>'RAW',
    4=>'PRIVATE_REPROCESSING',5=>'READ_SENSOR_SETTINGS',6=>'BURST_CAPTURE',
    7=>'YUV_REPROCESSING',8=>'DEPTH_OUTPUT',9=>'CONSTRAINED_HIGHSPEED',
    10=>'MOTION_TRACKING',11=>'LOGICAL_MULTI_CAMERA',12=>'MONOCHROME',
    13=>'SECURE_IMAGE_DATA',14=>'SYSTEM_CAMERA',15=>'OFFLINE_PROCESSING',
    16=>'ULTRA_HIGH_RESOLUTION',17=>'REMOSAIC_REPROCESSING',18=>'DYNAMIC_DEPTH',
    19=>'ULTRA_HIGH_RESOLUTION_2',
];

// AE mode labels
$aeModeLabels = [0=>'Off',1=>'On',2=>'On Auto Flash',3=>'Always Flash',4=>'Auto Red-Eye',5=>'External Flash'];
// AF mode labels
$afModeLabels = [0=>'Off',1=>'Auto',2=>'Macro',3=>'Continuous Video',4=>'Continuous Picture',5=>'EDOF'];
// Lens facing labels
$facingLabels = ['0'=>'Back','1'=>'Front','2'=>'External',0=>'Back',1=>'Front',2=>'External'];
$facingColors = ['Back'=>'primary','Front'=>'success','External'=>'warning text-dark'];
$facingIcons  = ['Back'=>'fa-camera','Front'=>'fa-user','External'=>'fa-video'];

// Group cameras by facing — deduplicated by camera_id within each group.
foreach ($rows as &$r) {
    $r['camera_unique_key'] = ($r['lens_facing'] ?? '0') . '_' . ($r['camera_id'] ?? 'default');
}
unset($r);

helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'camera_unique_key',
    ['camera_id', 'lens_facing', 'megapixels', 'sensor_physical_size', 'focal_lengths', 'apertures', 'iso_range', 'has_flash', 'capabilities', 'available_video_stabilization', 'available_ae_modes', 'available_af_modes', 'available_effects', 'available_scene_modes']
);

$grouped = [];
foreach ($rows as $r) {
    $facing   = $r['lens_facing'] ?? '0';
    $label    = $facingLabels[$facing] ?? 'Back';
    $grouped[$label][] = $r;
}
?>

<style>
.cam-card { border-radius:8px; background:#fff; box-shadow:0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2); }
.cam-card.facing-back  { border-top:4px solid #007bff; }
.cam-card.facing-front { border-top:4px solid #28a745; }
.cam-card.facing-external { border-top:4px solid #ffc107; }
.mp-hero { font-size:36px; font-weight:900; color:#343a40; line-height:1; }
.spec-table td, .spec-table th { padding:5px 8px; font-size:12.5px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
.spec-table th { color:#6c757d; font-weight:600; width:45%; }
.spec-table td { font-weight:600; color:#343a40; }
.mode-pill { font-size:11px; margin:2px; padding:3px 8px; background:#e9ecef; border-radius:12px; color:#495057; }
.cap-pill  { font-size:10px; margin:2px; padding:3px 7px; background:#343a40; color:#adb5bd; border-radius:3px; font-family:monospace; }
.lens-pill { font-size:12px; margin:2px; padding:5px 10px; background:#dee2e6; border-radius:16px; font-weight:600; }
.section-hdr { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:#6c757d; font-weight:700; padding-bottom:4px; border-bottom:2px solid #e9ecef; margin-bottom:8px; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-camera text-secondary mr-2"></i>Camera Hardware Audit</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Sensor specs, optics, codec capabilities, and API feature matrix per camera</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <!-- Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-camera mr-2"></i>Camera System Audit</h5>
        <p class="text-secondary mb-1" style="font-size:14px;">Complete hardware profile of each camera sensor and its capabilities. Multi-camera logical configurations and under-display cameras are flagged for forensic significance.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Camera Facing Directions:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Back:</b> Primary shooting camera, typically highest resolution</li>
              <li><b>Front:</b> Selfie/facial recognition camera</li>
              <li><b>External:</b> Connected via USB-C or wireless — unusual and warrants attention</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Pixel Size (µm):</b>
            <ul class="pl-3 mb-0 text-muted">
              <li>Larger pixel size = better low-light performance</li>
              <li>Typical: 0.8µm (compact) → 2.0µm+ (flagship large sensor)</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Codec Security Note:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li>HEVC/AV1 support enables high-compression silent video recording</li>
              <li>Logical multi-camera = hardware camera array used as single logical unit</li>
            </ul>
          </div>
        </div>
      </div>

      <?php if (empty($rows)): ?>
        <div class="text-center py-5">
          <i class="fas fa-camera fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Camera Data</h4>
          <p class="text-muted">Camera profiles will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <?php foreach ($grouped as $facingName => $cams): ?>
        <!-- Facing Group Header -->
        <div class="d-flex align-items-center mb-3 mt-2">
          <i class="fas <?= $facingIcons[$facingName] ?? 'fa-camera' ?> mr-2 text-<?= explode(' ', $facingColors[$facingName] ?? 'secondary')[0] ?>" style="font-size:18px;"></i>
          <h4 class="font-weight-bold mb-0 text-dark"><?= esc($facingName) ?> Camera<?= count($cams) > 1 ? 's' : '' ?></h4>
          <span class="badge badge-<?= explode(' ', $facingColors[$facingName] ?? 'secondary')[0] ?> ml-2"><?= count($cams) ?></span>
        </div>

        <div class="row">
          <?php foreach ($cams as $r):
            $rid = $r['id'] ?? 0;
            $ts  = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
            $pw  = (int)($r['pixel_array_width'] ?? 0);
            $ph  = (int)($r['pixel_array_height'] ?? 0);
            $rw  = (int)($r['real_width'] ?? $pw);
            $rh  = (int)($r['real_height'] ?? $ph);
            $mp  = ($pw * $ph) > 0 ? round(($pw * $ph) / 1_000_000, 1) : 0;
            $jpegMp = ($r['max_jpeg_width'] ?? 0) * ($r['max_jpeg_height'] ?? 0) > 0 ? round(($r['max_jpeg_width'] * $r['max_jpeg_height']) / 1_000_000, 1) : 0;
            $maxFps = ($r['min_frame_duration'] ?? 0) > 0 ? round(1e9 / $r['min_frame_duration']) : 0;
            $focalLens = parseCamField($r['available_focal_lengths'] ?? $r['focal_lengths'] ?? '');
            $apertures = parseCamField($r['apertures'] ?? '');
            $effects   = parseCamField($r['available_effects'] ?? '');
            $sceneModes= parseCamField($r['available_scene_modes'] ?? '');
            $aeModes   = parseCamField($r['available_ae_modes'] ?? '');
            $afModes   = parseCamField($r['available_af_modes'] ?? '');
            $vsStab    = parseCamField($r['available_video_stabilization'] ?? '');
            $caps      = parseCamField($r['available_capabilities'] ?? '');
            $physIds   = parseCamField($r['physical_camera_ids'] ?? '');
            $facingFC  = $facingColors[$facingName] ?? 'secondary';
            $facingLower = strtolower($facingName);
            ?>
            <div class="col-xl-6 mb-4">
              <div class="card cam-card facing-<?= $facingLower ?> h-100">
                <div class="card-header">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <span class="font-weight-bold text-dark" style="font-size:15px;">
                        <i class="fas <?= $facingIcons[$facingName] ?? 'fa-camera' ?> mr-2"></i>
                        Camera <code><?= esc($r['camera_id'] ?? $rid) ?></code>
                      </span>
                    </div>
                    <div class="text-right">
                      <span class="badge badge-<?= $facingFC ?> px-2 py-1"><?= esc($facingName) ?></span>
                      <?php if (!empty($r['logical_multi_camera'])): ?>
                        <span class="badge badge-info px-2 py-1 ml-1">LOGICAL MULTI-CAM</span>
                      <?php endif; ?>
                      <?php if (!empty($r['under_display_camera'])): ?>
                        <span class="badge badge-dark px-2 py-1 ml-1">UNDER-DISPLAY</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <div class="card-body p-3">

                  <div class="row mb-3">
                    <!-- Section 1: Sensor Specs -->
                    <div class="col-6 border-right pr-3">
                      <div class="section-hdr"><i class="fas fa-microchip mr-1"></i>Sensor</div>
                      <?php if ($mp > 0): ?>
                        <div class="text-center mb-2">
                          <div class="mp-hero"><?= $mp ?> <span style="font-size:18px;font-weight:600;color:#6c757d;">MP</span></div>
                          <small class="text-muted"><?= $pw ?> × <?= $ph ?> px</small>
                        </div>
                      <?php endif; ?>
                      <table class="spec-table w-100">
                        <?php if (!empty($r['active_array_size'])): ?><tr><th>Active Array</th><td><?= esc($r['active_array_size']) ?></td></tr><?php endif; ?>
                        <?php if ($r['physical_width_mm'] ?? 0): ?><tr><th>Physical Size</th><td><?= round($r['physical_width_mm'],2) ?> × <?= round($r['physical_height_mm'],2) ?> mm</td></tr><?php endif; ?>
                        <?php if ($r['pixel_size_um'] ?? 0): ?><tr><th>Pixel Pitch</th><td><?= $r['pixel_size_um'] ?> µm</td></tr><?php endif; ?>
                        <?php if ($r['max_analog_sensitivity'] ?? 0): ?><tr><th>Max ISO</th><td><?= number_format($r['max_analog_sensitivity']) ?></td></tr><?php endif; ?>
                        <?php if ($r['sensor_orientation'] ?? 0): ?><tr><th>Sensor Rotation</th><td><?= esc($r['sensor_orientation']) ?>°</td></tr><?php endif; ?>
                        <?php if ($jpegMp > 0): ?><tr><th>Max JPEG</th><td><?= $jpegMp ?> MP (<?= $r['max_jpeg_width'] ?> × <?= $r['max_jpeg_height'] ?>)</td></tr><?php endif; ?>
                      </table>
                    </div>

                    <!-- Section 2: Optics -->
                    <div class="col-6 pl-3">
                      <div class="section-hdr"><i class="fas fa-eye mr-1"></i>Optics</div>
                      <?php if (!empty($focalLens)): ?>
                        <div class="mb-2">
                          <small class="text-muted font-weight-bold d-block" style="font-size:11px;">Focal Lengths:</small>
                          <?php foreach ($focalLens as $fl): ?>
                            <span class="lens-pill">f=<?= esc(round((float)$fl, 2)) ?>mm</span>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                      <?php if (!empty($apertures)): ?>
                        <div class="mb-2">
                          <small class="text-muted font-weight-bold d-block" style="font-size:11px;">Apertures:</small>
                          <?php foreach ($apertures as $ap): ?>
                            <span class="lens-pill">f/<?= esc(round((float)$ap, 1)) ?></span>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                      <table class="spec-table w-100">
                        <?php if ($r['max_digital_zoom'] ?? 0): ?><tr><th>Digital Zoom</th><td><?= esc($r['max_digital_zoom']) ?>×</td></tr><?php endif; ?>
                        <?php if (!empty($r['optical_zoom_range'])): ?><tr><th>Optical Zoom</th><td><?= esc($r['optical_zoom_range']) ?></td></tr><?php endif; ?>
                        <?php if ($maxFps > 0): ?><tr><th>Max FPS</th><td><span class="badge badge-dark"><?= $maxFps ?> fps</span></td></tr><?php endif; ?>
                        <?php if (!empty($physIds)): ?><tr><th>Physical Cam IDs</th><td><?= esc(implode(', ', $physIds)) ?></td></tr><?php endif; ?>
                      </table>
                    </div>
                  </div>

                  <!-- Section 3: Codec & Format Capabilities -->
                  <div class="mb-3">
                    <div class="section-hdr"><i class="fas fa-film mr-1"></i>Codec &amp; Format Support</div>
                    <div class="d-flex flex-wrap">
                      <?php
                      $capFlags = [
                          [!empty($r['flash_available']),   'Flash',       'fa-bolt',        'warning'],
                          [!empty($r['hdr_capabilities']),  'HDR',         'fa-sun',         'info'],
                          [!empty($r['night_mode_support']),'Night Mode',  'fa-moon',        'secondary'],
                          [!empty($r['macro_mode_support']),'Macro',       'fa-search-plus', 'success'],
                          [!empty($r['bokeh_capabilities']),'Bokeh/Portrait','fa-circle',    'primary'],
                          [!empty($r['heic_support']),      'HEIC',        'fa-image',       'secondary'],
                          [!empty($r['hevc_support']),      'HEVC (H.265)','fa-video',       'primary'],
                          [!empty($r['av1_support']),       'AV1',         'fa-film',        'success'],
                          [!empty($r['10bit_output']),      '10-bit Output','fa-palette',    'dark'],
                          [count(array_filter($vsStab, fn($v) => strtolower($v) !== 'off')) > 0, 'Video Stabilization','fa-balance-scale','info'],
                          [!empty($r['logical_multi_camera']),'Multi-Camera Fusion','fa-layer-group','primary'],
                          [!empty($r['under_display_camera']),'Under-Display','fa-eye-slash','dark'],
                      ];
                      foreach ($capFlags as [$ok, $lbl, $ico, $color]): ?>
                        <span class="badge badge-<?= $ok ? $color : 'light border' ?> mr-1 mb-1" style="font-size:11px;padding:5px 8px;<?= !$ok ? 'color:#adb5bd;' : '' ?>">
                          <i class="fas <?= $ico ?> mr-1"></i><?= $lbl ?>
                        </span>
                      <?php endforeach; ?>
                    </div>
                  </div>

                  <!-- Section 4: Camera Modes (collapsible) -->
                  <div class="mb-2">
                    <a class="d-block font-weight-bold text-secondary" style="font-size:12px;cursor:pointer;" data-toggle="collapse" href="#modes-<?= $rid ?>">
                      <i class="fas fa-chevron-down mr-1"></i>Camera Modes &amp; Capabilities
                    </a>
                    <div id="modes-<?= $rid ?>" class="collapse mt-2">
                      <div class="row">
                        <?php if (!empty($aeModes)): ?>
                        <div class="col-6 mb-2">
                          <small class="font-weight-bold text-muted d-block mb-1">Auto Exposure Modes:</small>
                          <?php foreach ($aeModes as $m): $mi=(int)$m; ?>
                            <span class="mode-pill"><?= esc($aeModeLabels[$mi] ?? "AE Mode $m") ?></span>
                          <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($afModes)): ?>
                        <div class="col-6 mb-2">
                          <small class="font-weight-bold text-muted d-block mb-1">Auto Focus Modes:</small>
                          <?php foreach ($afModes as $m): $mi=(int)$m; ?>
                            <span class="mode-pill"><?= esc($afModeLabels[$mi] ?? "AF Mode $m") ?></span>
                          <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($sceneModes)): ?>
                        <div class="col-6 mb-2">
                          <small class="font-weight-bold text-muted d-block mb-1">Scene Modes (<?= count($sceneModes) ?>):</small>
                          <?php foreach (array_slice($sceneModes, 0, 10) as $m): ?>
                            <span class="mode-pill"><?= esc(is_numeric($m) ? "Mode $m" : $m) ?></span>
                          <?php endforeach; ?>
                          <?php if (count($sceneModes) > 10): ?><span class="mode-pill">+<?= count($sceneModes)-10 ?> more</span><?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($effects)): ?>
                        <div class="col-6 mb-2">
                          <small class="font-weight-bold text-muted d-block mb-1">Photo Effects (<?= count($effects) ?>):</small>
                          <?php foreach ($effects as $m): ?>
                            <span class="mode-pill"><?= esc(is_numeric($m) ? "Effect $m" : $m) ?></span>
                          <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($caps)): ?>
                        <div class="col-12 mt-1">
                          <small class="font-weight-bold text-muted d-block mb-1">Camera2 API Capabilities:</small>
                          <?php foreach ($caps as $c): $ci=(int)$c; ?>
                            <span class="cap-pill"><?= esc($capLabels[$ci] ?? "CAP_$c") ?></span>
                          <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                      </div>
                      <!-- Key counts -->
                      <div class="row mt-2" style="font-size:11px;">
                        <?php if (!empty($r['available_request_keys'])): ?>
                        <div class="col-4 text-center">
                          <div class="font-weight-bold"><?= count(parseCamField($r['available_request_keys'])) ?></div>
                          <div class="text-muted">Request Keys</div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($r['available_result_keys'])): ?>
                        <div class="col-4 text-center">
                          <div class="font-weight-bold"><?= count(parseCamField($r['available_result_keys'])) ?></div>
                          <div class="text-muted">Result Keys</div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($r['available_characteristics_keys'])): ?>
                        <div class="col-4 text-center">
                          <div class="font-weight-bold"><?= count(parseCamField($r['available_characteristics_keys'])) ?></div>
                          <div class="text-muted">Characteristics Keys</div>
                        </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>

                  <div class="border-top pt-2 mt-1 text-muted d-flex justify-content-between" style="font-size:11px;">
                    <span><i class="fas fa-clock mr-1"></i>Extracted: <?= $ts ?></span>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

        <!-- Pager -->
        <?php if (isset($pager)): ?>
          <div class="d-flex justify-content-end mt-2"><?= $pager->links('default', 'bootstrap5_full') ?></div>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>