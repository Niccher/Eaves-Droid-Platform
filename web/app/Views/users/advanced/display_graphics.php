<?php /** @var array $latest_di @var array $latest_hg @var array $history @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-desktop text-secondary mr-2"></i>Display & Graphics</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Snapshots: <b><?= count($history ?? []) ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Display metrics, GPU/renderer, media codecs, and input devices</p>
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
                            <ul class="nav nav-tabs card-header-tabs" id="displayTabs" role="tablist">
                                <li class="nav-item"><a class="nav-link active" id="tab-display" data-toggle="tab" href="#display" role="tab"><i class="fas fa-desktop mr-1"></i>Display</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab-gpu" data-toggle="tab" href="#gpu" role="tab"><i class="fas fa-memory mr-1"></i>GPU / Renderer</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab-codecs" data-toggle="tab" href="#codecs" role="tab"><i class="fas fa-film mr-1"></i>Media Codecs</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab-input" data-toggle="tab" href="#input" role="tab"><i class="fas fa-keyboard mr-1"></i>Input Devices</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="displayTabsContent">
                                <!-- Display Tab -->
                                <div class="tab-pane fade show active" id="display" role="tabpanel">
                                    <?php if ($latest_di): ?>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-3"><i class="fas fa-expand-arrows-alt mr-1"></i>Resolution & Density</h6>
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4">Width × Height</dt><dd class="col-sm-8 font-weight-bold"><?= $latest_di['width_px'] ?? '—' ?> × <?= $latest_di['height_px'] ?? '—' ?> px</dd>
                                                    <dt class="col-sm-4">Real Size</dt><dd class="col-sm-8"><?= $latest_di['real_width'] ?? '—' ?> × <?= $latest_di['real_height'] ?? '—' ?> px</dd>
                                                    <dt class="col-sm-4">Usable Size</dt><dd class="col-sm-8"><?= $latest_di['usable_width'] ?? '—' ?> × <?= $latest_di['usable_height'] ?? '—' ?> px</dd>
                                                    <dt class="col-sm-4">Density</dt><dd class="col-sm-8"><?= $latest_di['density'] ?? '—' ?> (<?= $latest_di['density_dpi'] ?? '—' ?> DPI)</dd>
                                                    <dt class="col-sm-4">XDPI / YDPI</dt><dd class="col-sm-8"><?= $latest_di['xdpi'] ?? '—' ?> / <?= $latest_di['ydpi'] ?? '—' ?></dd>
                                                    <dt class="col-sm-4">Scaled Density</dt><dd class="col-sm-8"><?= $latest_di['scaled_density'] ?? '—' ?></dd>
                                                </dl>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-3"><i class="fas fa-sync-alt mr-1"></i>Refresh & Rotation</h6>
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4">Refresh Rate</dt><dd class="col-sm-8 font-weight-bold"><?= $latest_di['refresh_rate'] ?? '—' ?> Hz</dd>
                                                    <dt class="col-sm-4">Mode</dt><dd class="col-sm-8"><?= $latest_di['mode_width'] ?? '—' ?> × <?= $latest_di['mode_height'] ?? '—' ?> @ <?= $latest_di['mode_refresh_rate'] ?? '—' ?> Hz</dd>
                                                    <dt class="col-sm-4">Rotation</dt><dd class="col-sm-8"><span class="badge badge-info"><?= $latest_di['rotation'] ?? '0' ?>°</span></dd>
                                                    <dt class="col-sm-4">Screen Layout</dt><dd class="col-sm-8"><?= $latest_di['screen_layout'] ?? '—' ?></dd>
                                                    <dt class="col-sm-4">Smallest Width DP</dt><dd class="col-sm-8"><?= $latest_di['smallest_screen_width_dp'] ?? '—' ?> dp</dd>
                                                    <dt class="col-sm-4">UI Mode</dt><dd class="col-sm-8"><?= $latest_di['ui_mode'] ?? '—' ?></dd>
                                                </dl>
                                            </div>
                                        </div>
                                        
                                        <?php $displays = json_decode($latest_di['displays_json'] ?? '[]', true); ?>
                                        <?php if (!empty($displays)): ?>
                                            <hr class="my-3">
                                            <h6 class="text-muted mb-2"><i class="fas fa-columns mr-1"></i>Multi-Display (<?= count($displays) ?>)</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="thead-light"><tr><th>Display ID</th><th>Width × Height</th><th>Density</th><th>Refresh</th><th>Flags</th></tr></thead>
                                                    <tbody>
                                                    <?php foreach (array_slice($displays,0,2) as $d): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($d['display_id'] ?? '—') ?></td>
                                                            <td><?= $d['width'] ?? '—' ?> × <?= $d['height'] ?? '—' ?></td>
                                                            <td><?= $d['density'] ?? '—' ?></td>
                                                            <td><?= $d['refresh_rate'] ?? '—' ?> Hz</td>
                                                            <td><?= htmlspecialchars($d['flags'] ?? '—') ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php if (count($displays) > 2): ?>
                                                <div class="text-right mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#multiDisplayModal">
                                                        <i class="fas fa-list mr-1"></i>Show All Displays
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No display info data</p>
                                    <?php endif; ?>
</div>
                                     <?php $latest_displays = $displays ?? []; ?>
 
                                 <!-- GPU / Renderer Tab -->
                                <div class="tab-pane fade" id="gpu" role="tabpanel">
                                    <?php if ($latest_hg): 
                                        $gpu = json_decode($latest_hg['gpu_renderer_json'] ?? '{}', true);
                                    ?>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-3"><i class="fas fa-microchip mr-1"></i>GPU Identity</h6>
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4">GL Vendor</dt><dd class="col-sm-8"><code><?= htmlspecialchars($gpu['gl_vendor'] ?? '—') ?></code></dd>
                                                    <dt class="col-sm-4">GL Renderer</dt><dd class="col-sm-8 font-weight-bold"><code><?= htmlspecialchars($gpu['gl_renderer'] ?? '—') ?></code></dd>
                                                    <dt class="col-sm-4">GL Version</dt><dd class="col-sm-8"><code><?= htmlspecialchars($gpu['gl_version'] ?? '—') ?></code></dd>
                                                    <dt class="col-sm-4">GLSL Version</dt><dd class="col-sm-8"><code><?= htmlspecialchars($gpu['gl_shading_language_version'] ?? '—') ?></code></dd>
                                                    <dt class="col-sm-4">EGL Vendor</dt><dd class="col-sm-8"><code><?= htmlspecialchars($gpu['egl_vendor'] ?? '—') ?></code></dd>
                                                    <dt class="col-sm-4">EGL Version</dt><dd class="col-sm-8"><code><?= htmlspecialchars($gpu['egl_version'] ?? '—') ?></code></dd>
                                                </dl>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-3"><i class="fas fa-expand-arrows-alt mr-1"></i>Capabilities & Limits</h6>
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4">Max Texture</dt><dd class="col-sm-8"><?= $gpu['max_texture_size'] ? number_format($gpu['max_texture_size']) . ' px' : '—' ?></dd>
                                                    <dt class="col-sm-4">Max Renderbuffer</dt><dd class="col-sm-8"><?= $gpu['max_renderbuffer_size'] ? number_format($gpu['max_renderbuffer_size']) . ' px' : '—' ?></dd>
                                                    <dt class="col-sm-4">Max Viewport</dt><dd class="col-sm-8"><?= $gpu['max_viewport_width'] ?? '—' ?> × <?= $gpu['max_viewport_height'] ?? '—' ?></dd>
                                                </dl>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-3"><i class="fas fa-check-double mr-1"></i>API Support</h6>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <span class="badge badge-<?= !empty($gpu['supports_gles30']) ? 'success' : 'secondary' ?> p-2">OpenGL ES 3.0 <?= !empty($gpu['supports_gles30']) ? '✓' : '✗' ?></span>
                                                    <span class="badge badge-<?= !empty($gpu['supports_gles31']) ? 'success' : 'secondary' ?> p-2">OpenGL ES 3.1 <?= !empty($gpu['supports_gles31']) ? '✓' : '✗' ?></span>
                                                    <span class="badge badge-<?= !empty($gpu['supports_gles32']) ? 'success' : 'secondary' ?> p-2">OpenGL ES 3.2 <?= !empty($gpu['supports_gles32']) ? '✓' : '✗' ?></span>
                                                    <span class="badge badge-<?= !empty($gpu['vulkan_available']) ? 'success' : 'secondary' ?> p-2">
                                                        Vulkan <?= !empty($gpu['vulkan_available']) ? '✓ v' . htmlspecialchars($gpu['vulkan_version'] ?? '?') : '✗' ?>
                                                    </span>
                                                </div>
                                            </div>
<div class="col-md-6">
                                                    <h6 class="text-muted mb-3"><i class="fas fa-puzzle-piece mr-1"></i>Extensions</h6>
                                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#glExtensionsModal">
                                                            GL Extensions (<?= count($gpu['gl_extensions'] ?? []) ?>)
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#eglExtensionsModal">
                                                            EGL Extensions (<?= count($gpu['egl_extensions'] ?? []) ?>)
                                                        </button>
                                                    </div>
                                                </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No GPU data</p>
                                    <?php endif; ?>
                                </div>

                                <!-- Media Codecs Tab -->
                                <div class="tab-pane fade" id="codecs" role="tabpanel">
                                    <?php if ($latest_hg): 
                                        $codecs = json_decode($latest_hg['media_codecs_json'] ?? '[]', true);
                                    ?>
                                        <?php if (!empty($codecs)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover table-striped mb-0">
                                                    <thead class="thead-light">
                                                    <tr>
                                                        <th>#</th><th>Name</th><th>Type</th><th>Enc/Dec</th><th>HW Accel</th>
                                                        <th>Max Resolution</th><th>Color Formats</th><th>Profiles</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php $i = 1; foreach (array_slice($codecs,0,3) as $c): ?>
                                                        <tr>
                                                            <td><?= $i++ ?></td>
                                                            <td><code><?= htmlspecialchars($c['name'] ?? '—') ?></code></td>
                                                            <td><code><?= htmlspecialchars($c['type'] ?? '—') ?></code></td>
                                                            <td>
                                                                <span class="badge badge-<?= !empty($c['is_encoder']) ? 'success' : 'info' ?>">
                                                                    <?= !empty($c['is_encoder']) ? 'Encoder' : 'Decoder' ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-<?= !empty($c['is_hardware_accelerated']) ? 'success' : 'secondary' ?>">
                                                                    <?= !empty($c['is_hardware_accelerated']) ? 'Yes' : 'Software' ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($c['max_width']) || !empty($c['max_height'])): ?>
                                                                    <?= $c['max_width'] ?? 0 ?> × <?= $c['max_height'] ?? 0 ?>
                                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($c['color_formats'])): ?>
                                                                    <?php foreach ($c['color_formats'] as $fmt): ?><span class="badge badge-light text-dark mr-1 mb-1"><?= htmlspecialchars($fmt) ?></span><?php endforeach; ?>
                                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($c['profile_levels'])): ?>
                                                                    <?php foreach ($c['profile_levels'] as $pl): ?>
                                                                        <span class="badge badge-primary mr-1 mb-1">P:<?= htmlspecialchars($pl['profile'] ?? '?') ?> L:<?= htmlspecialchars($pl['level'] ?? '?') ?></span>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php if (count($codecs) > 3): ?>
                                                <div class="text-right mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#mediaCodecsModal">
                                                        <i class="fas fa-list mr-1"></i>Show All Codecs
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="text-muted text-center py-4">No media codecs data</p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No hardware graphics data</p>
                                    <?php endif; ?>
</div>
                                     <?php $latest_codecs = $codecs ?? []; ?>
 
                                 <!-- Input Devices Tab -->
                                <div class="tab-pane fade" id="input" role="tabpanel">
                                    <?php if ($latest_hg): 
                                        $inputs = json_decode($latest_hg['input_devices_json'] ?? '[]', true);
                                    ?>
                                        <?php if (!empty($inputs)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover table-striped mb-0">
                                                    <thead class="thead-light">
                                                    <tr>
                                                        <th>#</th><th>Name</th><th>Descriptor</th><th>Vendor/Product</th>
                                                        <th>Sources</th><th>Keyboard Type</th><th>Motion Axes</th><th>Vibrator</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php $i = 1; foreach (array_slice($inputs,0,3) as $d): ?>
                                                        <tr>
                                                            <td><?= $i++ ?></td>
                                                            <td><strong><?= htmlspecialchars($d['name'] ?? '—') ?></strong></td>
                                                            <td><code><?= htmlspecialchars($d['descriptor'] ?? '—') ?></code></td>
                                                            <td>
                                                                <?php if (!empty($d['vendor_id']) || !empty($d['product_id'])): ?>
                                                                    0x<?= dechex($d['vendor_id'] ?? 0) ?> / 0x<?= dechex($d['product_id'] ?? 0) ?>
                                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($d['sources'])): ?>
                                                                    <?php foreach ($d['sources'] as $src): ?><span class="badge badge-info mr-1 mb-1"><?= htmlspecialchars($src) ?></span><?php endforeach; ?>
                                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                            </td>
                                                            <td><?= htmlspecialchars($d['keyboard_type'] ?? '—') ?></td>
                                                            <td>
                                                                <?php if (!empty($d['motion_ranges'])): ?>
                                                                    <?php foreach ($d['motion_ranges'] as $mr): ?><span class="badge badge-light text-dark mr-1 mb-1"><?= htmlspecialchars($mr['axis'] ?? '?') ?></span><?php endforeach; ?>
                                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-<?= !empty($d['has_vibrator']) ? 'success' : 'secondary' ?>">
                                                                    <?= !empty($d['has_vibrator']) ? 'Yes' : 'No' ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php if (count($inputs) > 3): ?>
                                                <div class="text-right mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#inputDevicesModal">
                                                        <i class="fas fa-list mr-1"></i>Show All Input Devices
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="text-muted text-center py-4">No input devices data</p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No hardware graphics data</p>
<?php endif; ?>
                                </div>
                                     <?php $latest_inputs = $inputs ?? []; ?>
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
                            <h3 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Display & Graphics History</h3>
                            <div class="card-tools ml-auto"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#displayGraphicsHistoryModal">Show All (<?= count($history ?? []) ?>)</button><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                        <th><i class="fas fa-desktop mr-1"></i>Display</th>
                                        <th><i class="fas fa-memory mr-1"></i>GPU</th>
                                        <th><i class="fas fa-film mr-1"></i>Codecs</th>
                                        <th><i class="fas fa-keyboard mr-1"></i>Input Devices</th>
                                        <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($history)): ?>
                                        <tr><td colspan="7" class="text-center py-5"><div class="empty-state"><i class="fas fa-desktop fa-3x text-muted mb-3"></i><h4>No display/graphics history</h4></div></td></tr>
                                    <?php else: foreach (array_slice($history,0,5) as $h): 
                                        $di = $h['display_info'] ?? [];
                                        $hg = $h['hardware_graphics'] ?? [];
                                        $gpu = json_decode($hg['gpu_renderer_json'] ?? '{}', true);
                                        $codecs = json_decode($hg['media_codecs_json'] ?? '[]', true);
                                        $inputs = json_decode($hg['input_devices_json'] ?? '[]', true);
                                    ?>
                                        <tr>
                                            <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                            <td>
                                                <?= $di['width_px'] ?? '—' ?>×<?= $di['height_px'] ?? '—' ?>
                                                <br><small class="text-muted"><?= $di['refresh_rate'] ?? '—' ?>Hz • <?= $di['density_dpi'] ?? '—' ?>DPI</small>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($gpu['gl_renderer'] ?? '—') ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($gpu['gl_version'] ?? '—') ?></small>
                                                <?php if (!empty($gpu['vulkan_available'])): ?><span class="badge badge-success ml-1">Vulkan</span><?php endif; ?>
                                            </td>
                                            <td><span class="badge badge-info"><?= count($codecs) ?></span></td>
                                            <td><span class="badge badge-warning"><?= count($inputs) ?></span></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/display_graphics/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
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
    
    <!-- Modal: Full Display & Graphics History -->
    <div class="modal fade" id="displayGraphicsHistoryModal" tabindex="-1" role="dialog" aria-labelledby="displayGraphicsHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="displayGraphicsHistoryModalLabel"><i class="fas fa-history mr-2"></i>Full Display & Graphics History</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                    <th><i class="fas fa-desktop mr-1"></i>Display</th>
                                    <th><i class="fas fa-memory mr-1"></i>GPU</th>
                                    <th><i class="fas fa-film mr-1"></i>Codecs</th>
                                    <th><i class="fas fa-keyboard mr-1"></i>Input Devices</th>
                                    <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history)): ?>
                                <tr><td colspan="7" class="text-center py-5"><div class="empty-state"><i class="fas fa-desktop fa-3x text-muted mb-3"></i><h4>No display/graphics history</h4></div></td></tr>
                                <?php else: foreach ($history as $h):
                                    $di = $h['display_info'] ?? [];
                                    $hg = $h['hardware_graphics'] ?? [];
                                    $gpu = json_decode($hg['gpu_renderer_json'] ?? '{}', true);
                                    $codecs = json_decode($hg['media_codecs_json'] ?? '[]', true);
                                    $inputs = json_decode($hg['input_devices_json'] ?? '[]', true);
                                ?>
                                <tr>
                                    <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                    <td>
                                        <?= $di['width_px'] ?? '—' ?>×<?= $di['height_px'] ?? '—' ?>
                                        <br><small class="text-muted"><?= $di['refresh_rate'] ?? '—' ?>Hz • <?= $di['density_dpi'] ?? '—' ?>DPI</small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($gpu['gl_renderer'] ?? '—') ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($gpu['gl_version'] ?? '—') ?></small>
                                        <?php if (!empty($gpu['vulkan_available'])): ?><span class="badge badge-success ml-1">Vulkan</span><?php endif; ?>
                                    </td>
                                    <td><span class="badge badge-info"><?= count($codecs) ?></span></td>
                                    <td><span class="badge badge-warning"><?= count($inputs) ?></span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/display_graphics/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
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

    <!-- Modal: GL Extensions -->
    <div class="modal fade" id="glExtensionsModal" tabindex="-1" role="dialog" aria-labelledby="glExtensionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="glExtensionsModalLabel"><i class="fas fa-puzzle-piece mr-2"></i>GL Extensions</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6>GL Extensions (<?= count($gpu['gl_extensions'] ?? []) ?>)</h6>
                    <div class="small">
                        <?php foreach ($gpu['gl_extensions'] ?? [] as $ext): ?>
                            <span class="badge badge-light text-dark mr-1 mb-1"><?= htmlspecialchars($ext) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: EGL Extensions -->
    <div class="modal fade" id="eglExtensionsModal" tabindex="-1" role="dialog" aria-labelledby="eglExtensionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="eglExtensionsModalLabel"><i class="fas fa-puzzle-piece mr-2"></i>EGL Extensions</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6>EGL Extensions (<?= count($gpu['egl_extensions'] ?? []) ?>)</h6>
                    <div class="small">
                        <?php foreach ($gpu['egl_extensions'] ?? [] as $ext): ?>
                            <span class="badge badge-light text-dark mr-1 mb-1"><?= htmlspecialchars($ext) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Multi-Display -->
    <div class="modal fade" id="multiDisplayModal" tabindex="-1" role="dialog" aria-labelledby="multiDisplayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="multiDisplayModalLabel"><i class="fas fa-columns mr-2"></i>All Displays</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light"><tr><th>Display ID</th><th>Width × Height</th><th>Density</th><th>Refresh</th><th>Flags</th></tr></thead>
                            <tbody>
                            <?php foreach ($latest_displays as $d): ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['display_id'] ?? '—') ?></td>
                                    <td><?= $d['width'] ?? '—' ?> × <?= $d['height'] ?? '—' ?></td>
                                    <td><?= $d['density'] ?? '—' ?></td>
                                    <td><?= $d['refresh_rate'] ?? '—' ?> Hz</td>
                                    <td><?= htmlspecialchars($d['flags'] ?? '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Input Devices -->
    <div class="modal fade" id="inputDevicesModal" tabindex="-1" role="dialog" aria-labelledby="inputDevicesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="inputDevicesModalLabel"><i class="fas fa-keyboard mr-2"></i>All Input Devices</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                            <tr>
                                <th>#</th><th>Name</th><th>Descriptor</th><th>Vendor/Product</th>
                                <th>Sources</th><th>Keyboard Type</th><th>Motion Axes</th><th>Vibrator</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i = 1; foreach ($latest_inputs as $d): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><strong><?= htmlspecialchars($d['name'] ?? '—') ?></strong></td>
                                    <td><code><?= htmlspecialchars($d['descriptor'] ?? '—') ?></code></td>
                                    <td>
                                        <?php if (!empty($d['vendor_id']) || !empty($d['product_id'])): ?>
                                            0x<?= dechex($d['vendor_id'] ?? 0) ?> / 0x<?= dechex($d['product_id'] ?? 0) ?>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($d['sources'])): ?>
                                            <?php foreach ($d['sources'] as $src): ?><span class="badge badge-info mr-1 mb-1"><?= htmlspecialchars($src) ?></span><?php endforeach; ?>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($d['keyboard_type'] ?? '—') ?></td>
                                    <td>
                                        <?php if (!empty($d['motion_ranges'])): ?>
                                            <?php foreach ($d['motion_ranges'] as $mr): ?><span class="badge badge-light text-dark mr-1 mb-1"><?= htmlspecialchars($mr['axis'] ?? '?') ?></span><?php endforeach; ?>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= !empty($d['has_vibrator']) ? 'success' : 'secondary' ?>">
                                            <?= !empty($d['has_vibrator']) ? 'Yes' : 'No' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Media Codecs -->
    <div class="modal fade" id="mediaCodecsModal" tabindex="-1" role="dialog" aria-labelledby="mediaCodecsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="mediaCodecsModalLabel"><i class="fas fa-film mr-2"></i>All Media Codecs</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                            <tr>
                                <th>#</th><th>Name</th><th>Type</th><th>Enc/Dec</th><th>HW Accel</th>
                                <th>Max Resolution</th><th>Color Formats</th><th>Profiles</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i = 1; foreach ($latest_codecs as $c): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><code><?= htmlspecialchars($c['name'] ?? '—') ?></code></td>
                                    <td><code><?= htmlspecialchars($c['type'] ?? '—') ?></code></td>
                                    <td>
                                        <span class="badge badge-<?= !empty($c['is_encoder']) ? 'success' : 'info' ?>">
                                            <?= !empty($c['is_encoder']) ? 'Encoder' : 'Decoder' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= !empty($c['is_hardware_accelerated']) ? 'success' : 'secondary' ?>">
                                            <?= !empty($c['is_hardware_accelerated']) ? 'Yes' : 'Software' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['max_width']) || !empty($c['max_height'])): ?>
                                            <?= $c['max_width'] ?? 0 ?> × <?= $c['max_height'] ?? 0 ?>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['color_formats'])): ?>
                                            <?php foreach ($c['color_formats'] as $fmt): ?><span class="badge badge-light text-dark mr-1 mb-1"><?= htmlspecialchars($fmt) ?></span><?php endforeach; ?>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['profile_levels'])): ?>
                                            <?php foreach ($c['profile_levels'] as $pl): ?>
                                                <span class="badge badge-primary mr-1 mb-1">P:<?= htmlspecialchars($pl['profile'] ?? '?') ?> L:<?= htmlspecialchars($pl['level'] ?? '?') ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>