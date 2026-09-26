<?php /** @var array $latest_camera @var array $latest_audio @var array $history @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-camera text-dark mr-2"></i>Media Hardware</h1>
                        <span class="badge badge-dark border p-2 text-white"><i class="fas fa-database mr-1"></i>Snapshots: <b><?= count($history ?? []) ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Camera specifications and audio input/output devices</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Current State - Tabbed -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="mediaTabs" role="tablist">
                                <li class="nav-item"><a class="nav-link active" id="tab-camera" data-toggle="tab" href="#camera" role="tab"><i class="fas fa-camera mr-1"></i>Cameras (<?= count($latest_camera ?? []) ?>)</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab-audio" data-toggle="tab" href="#audio" role="tab"><i class="fas fa-headphones mr-1"></i>Audio Devices (<?= count($latest_audio ?? []) ?>)</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="mediaTabsContent">
                                <!-- Camera Tab -->
                                <div class="tab-pane fade show active" id="camera" role="tabpanel">
                                    <?php if (!empty($latest_camera)): ?>
                                        <?php foreach ($latest_camera as $cam): ?>
                                            <div class="card mb-3">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0 d-flex justify-content-between align-items-center">
                                                        <span>
                                                            <i class="fas fa-camera text-dark mr-1"></i>
                                                            Camera ID: <?= htmlspecialchars($cam['camera_id'] ?? '—') ?>
                                                            <span class="badge badge-<?= ($cam['lens_facing'] ?? 1) == 0 ? 'primary' : 'success' ?> ml-2">
                                                                <?= ($cam['lens_facing'] ?? 1) == 0 ? 'Back' : 'Front' ?>
                                                            </span>
                                                        </span>
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6 class="text-muted mb-3">Resolution & Sensor</h6>
                                                            <dl class="row mb-0">
                                                                <dt class="col-sm-4">Pixel Array</dt><dd class="col-sm-8"><?= $cam['pixel_array_width'] ?? '—' ?> × <?= $cam['pixel_array_height'] ?? '—' ?></dd>
                                                                <dt class="col-sm-4">Physical Size</dt><dd class="col-sm-8"><?= $cam['physical_width_mm'] ?? '—' ?> × <?= $cam['physical_height_mm'] ?? '—' ?> mm</dd>
                                                                <dt class="col-sm-4">Pixel Size</dt><dd class="col-sm-8"><?= $cam['pixel_size_um'] ?? '—' ?> μm</dd>
                                                                <dt class="col-sm-4">Sensor Orientation</dt><dd class="col-sm-8"><?= $cam['sensor_orientation'] ?? '—' ?>°</dd>
                                                                <dt class="col-sm-4">Max Analog Sens.</dt><dd class="col-sm-8"><?= $cam['max_analog_sensitivity'] ?? '—' ?></dd>
                                                                <dt class="col-sm-4">Max Digital Zoom</dt><dd class="col-sm-8"><?= $cam['max_digital_zoom'] ?? '—' ?>x</dd>
                                                                <dt class="col-sm-4">Optical Zoom</dt><dd class="col-sm-8"><?= $cam['optical_zoom_range'] ?? '—' ?></dd>
                                                            </dl>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="text-muted mb-3">Features & Capabilities</h6>
                                                            <dl class="row mb-0">
                                                                <dt class="col-sm-4">Flash</dt><dd class="col-sm-8"><?= !empty($cam['flash_available']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                                <dt class="col-sm-4">Bokeh</dt><dd class="col-sm-8"><?= !empty($cam['bokeh_capabilities']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                                <dt class="col-sm-4">HEIC</dt><dd class="col-sm-8"><?= !empty($cam['heic_support']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                                <dt class="col-sm-4">HEVC</dt><dd class="col-sm-8"><?= !empty($cam['hevc_support']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                                <dt class="col-sm-4">AV1</dt><dd class="col-sm-8"><?= !empty($cam['av1_support']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                                <dt class="col-sm-4">10-bit Output</dt><dd class="col-sm-8"><?= !empty($cam['10bit_output']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                                <dt class="col-sm-4">Night Mode</dt><dd class="col-sm-8"><?= !empty($cam['night_mode_support']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                                <dt class="col-sm-4">Macro Mode</dt><dd class="col-sm-8"><?= !empty($cam['macro_mode_support']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                            </dl>
                                                        </div>
                                                    </div>
                                                    
                                                    <hr class="my-3">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <h6 class="text-muted mb-2">Focal Lengths</h6>
                                                            <?php $fl = json_decode($cam['available_focal_lengths'] ?? '[]', true); ?>
                                                            <?php if (!empty($fl)): ?><?php foreach ($fl as $f): ?><span class="badge badge-info mr-1 mb-1"><?= $f ?> mm</span><?php endforeach; ?><?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <h6 class="text-muted mb-2">Apertures</h6>
                                                            <?php $ap = json_decode($cam['apertures'] ?? '[]', true); ?>
                                                            <?php if (!empty($ap)): ?><?php foreach ($ap as $a): ?><span class="badge badge-warning mr-1 mb-1">f/<?= $a ?></span><?php endforeach; ?><?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <h6 class="text-muted mb-2">Effects / Scene Modes</h6>
                                                            <?php $eff = json_decode($cam['available_effects'] ?? '[]', true); $sc = json_decode($cam['available_scene_modes'] ?? '[]', true); ?>
                                                            <small class="text-muted">Effects: <?= count($eff) ?> • Scenes: <?= count($sc) ?></small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mt-3">
                                                        <div class="col-md-6">
                                                            <h6 class="text-muted mb-2">Capabilities</h6>
                                                            <?php $caps = json_decode($cam['available_capabilities'] ?? '[]', true); ?>
                                                            <div class="small" style="max-height:120px;overflow:auto;">
                                                                <?php foreach ($caps as $cap): ?><span class="badge badge-light text-dark mr-1 mb-1"><?= htmlspecialchars($cap) ?></span><?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="text-muted mb-2">HDR / Dynamic Range</h6>
                                                            <?php $hdr = json_decode($cam['hdr_capabilities'] ?? '[]', true); $dr = json_decode($cam['dynamic_range_profiles'] ?? '[]', true); ?>
                                                            <small class="text-muted">HDR: <?= count($hdr) ?> profiles • DR: <?= count($dr) ?> profiles</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No camera info data</p>
                                    <?php endif; ?>
                                </div>

                                <!-- Audio Tab -->
                                <div class="tab-pane fade" id="audio" role="tabpanel">
                                    <?php if (!empty($latest_audio)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped mb-0">
                                                <thead class="thead-light">
                                                <tr>
                                                    <th>#</th><th>Name</th><th>Type</th><th>Address</th>
                                                    <th>Channels</th><th>Sample Rates</th><th>Formats</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $i = 1; foreach ($latest_audio as $aud): ?>
                                                    <tr>
                                                        <td><?= $i++ ?></td>
                                                        <td><strong><?= htmlspecialchars($aud['name'] ?? '—') ?></strong></td>
                                                        <td><span class="badge badge-info"><?= htmlspecialchars($aud['type'] ?? '—') ?></span></td>
                                                        <td><code><?= htmlspecialchars($aud['address'] ?? '—') ?></code></td>
                                                        <td>
                                                            <?php $ch = json_decode($aud['channel_masks'] ?? '[]', true); ?>
                                                            <?php if (!empty($ch)): ?><?php foreach ($ch as $c): ?><span class="badge badge-light text-dark mr-1"><?= htmlspecialchars($c) ?></span><?php endforeach; ?><?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php $sr = json_decode($aud['sample_rates'] ?? '[]', true); ?>
                                                            <?php if (!empty($sr)): ?><?php foreach ($sr as $r): ?><span class="badge badge-secondary mr-1"><?= $r ?> Hz</span><?php endforeach; ?><?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php $fmt = json_decode($aud['formats'] ?? '[]', true); ?>
                                                            <?php if (!empty($fmt)): ?><?php foreach ($fmt as $f): ?><span class="badge badge-warning mr-1"><?= htmlspecialchars($f) ?></span><?php endforeach; ?><?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No audio devices data</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Media Hardware History</h3>
                            <div class="card-tools ml-auto"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#mediaHardwareHistoryModal">Show All (<?= count($history ?? []) ?>)</button><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                        <th><i class="fas fa-camera mr-1"></i>Cameras</th>
                                        <th><i class="fas fa-headphones mr-1"></i>Audio Devices</th>
                                        <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($history)): ?>
                                        <tr><td colspan="5" class="text-center py-5"><div class="empty-state"><i class="fas fa-camera fa-3x text-muted mb-3"></i><h4>No media hardware history</h4></div></td></tr>
                                    <?php else: foreach (array_slice($history,0,5) as $h): 
                                        $cam = $h['camera_info'] ?? [];
                                        $aud = $h['audio_devices'] ?? [];
                                    ?>
                                        <tr>
                                            <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                            <td>
                                                <span class="badge badge-dark"><?= count($cam) ?> camera<?= count($cam) !== 1 ? 's' : '' ?></span>
                                                <?php if ($cam): ?>
                                                    <br><small class="text-muted">
                                                        Max MP: <?= max(array_map(fn($c) => (($c['pixel_array_width'] ?? 0) * ($c['pixel_array_height'] ?? 0)) / 1e6, $cam)) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge badge-secondary"><?= count($aud) ?> device<?= count($aud) !== 1 ? 's' : '' ?></span></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/media_hardware/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer"><div class="float-right"><?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

    <!-- Modal: Full Media Hardware History -->
    <div class="modal fade" id="mediaHardwareHistoryModal" tabindex="-1" role="dialog" aria-labelledby="mediaHardwareHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="mediaHardwareHistoryModalLabel"><i class="fas fa-history mr-2"></i>Full Media Hardware History</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                    <th><i class="fas fa-camera mr-1"></i>Cameras</th>
                                    <th><i class="fas fa-headphones mr-1"></i>Audio Devices</th>
                                    <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history)): ?>
                                <tr><td colspan="5" class="text-center py-5"><div class="empty-state"><i class="fas fa-camera fa-3x text-muted mb-3"></i><h4>No media hardware history</h4></div></td></tr>
                                <?php else: foreach ($history as $h):
                                    $cam = $h['camera_info'] ?? [];
                                    $aud = $h['audio_devices'] ?? [];
                                ?>
                                <tr>
                                    <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                    <td>
                                        <span class="badge badge-dark"><?= count($cam) ?> camera<?= count($cam) !== 1 ? 's' : '' ?></span>
                                        <?php if ($cam): ?>
                                            <br><small class="text-muted">
                                                Max MP: <?= max(array_map(fn($c) => (($c['pixel_array_width'] ?? 0) * ($c['pixel_array_height'] ?? 0)) / 1e6, $cam)) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge badge-secondary"><?= count($aud) ?> device<?= count($aud) !== 1 ? 's' : '' ?></span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/media_hardware/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>