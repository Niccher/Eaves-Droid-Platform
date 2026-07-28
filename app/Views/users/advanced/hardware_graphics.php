<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-memory text-secondary mr-2"></i>Hardware Graphics</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">GPU/Renderer, Media Codecs, and Input Devices</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Snapshots <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-memory mr-1"></i>GPU / Renderer</th>
                            <th><i class="fas fa-film mr-1"></i>Media Codecs</th>
                            <th><i class="fas fa-keyboard mr-1"></i>Input Devices</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="5" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-memory fa-3x text-muted mb-3"></i><h4>No hardware graphics data</h4><p class="text-muted">Snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $gpu = $r['gpu_renderer'] ?? [];
                            $codecs = $r['media_codecs'] ?? [];
                            $inputs = $r['input_devices'] ?? [];
                            $rid = $r['id'] ?? 0;
                            ?>
                            <tr>
                                <td class="text-center">
                                    <?php if (!empty($gpu)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-gpu-<?= $rid ?>"
                                           class="badge badge-info p-2" title="Click to view GPU details">
                                            <i class="fas fa-eye mr-1"></i><?= $gpu['gl_renderer'] ?? 'View' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($codecs)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-codecs-<?= $rid ?>"
                                           class="badge badge-warning p-2" title="Click to view codecs">
                                            <i class="fas fa-eye mr-1"></i><?= count($codecs) ?> codec<?= count($codecs) !== 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($inputs)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-inputs-<?= $rid ?>"
                                           class="badge badge-success p-2" title="Click to view input devices">
                                            <i class="fas fa-eye mr-1"></i><?= count($inputs) ?> device<?= count($inputs) !== 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $rid ?>"
                                            data-url="<?= base_url('advanced/hardware/hardware_graphics/delete') ?>"
                                            title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer"><div class="float-right"><?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?></div></div>
        </div>
    </div></div></div></section>
</div>

<?php if (!empty($rows)): foreach ($rows as $r):
    $gpu = $r['gpu_renderer'] ?? [];
    $codecs = $r['media_codecs'] ?? [];
    $inputs = $r['input_devices'] ?? [];
    $rid = $r['id'] ?? 0;
?>

<!-- GPU / Renderer Modal -->
<div class="modal fade" id="modal-gpu-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-memory mr-2"></i>GPU / Renderer Information</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <?php if (!empty($gpu)): ?>
                    <!-- Section: GPU Identity -->
                    <div class="mb-4">
                        <h6 class="text-uppercase font-weight-bold text-muted mb-3" style="font-size:0.8rem; letter-spacing:1px;">
                            <i class="fas fa-microchip mr-1"></i> GPU Identity
                        </h6>
                        <div class="details-grid">
                            <div class="detail-card">
                                <div class="detail-icon"><i class="fas fa-industry"></i></div>
                                <div class="detail-label">GL Vendor</div>
                                <div class="detail-value"><code><?= htmlspecialchars($gpu['gl_vendor'] ?? '—') ?></code></div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-icon"><i class="fas fa-desktop"></i></div>
                                <div class="detail-label">GL Renderer</div>
                                <div class="detail-value"><code><?= htmlspecialchars($gpu['gl_renderer'] ?? '—') ?></code></div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-icon"><i class="fas fa-code"></i></div>
                                <div class="detail-label">GL Version</div>
                                <div class="detail-value"><code><?= htmlspecialchars($gpu['gl_version'] ?? '—') ?></code></div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-icon"><i class="fas fa-terminal"></i></div>
                                <div class="detail-label">GLSL Version</div>
                                <div class="detail-value"><code><?= htmlspecialchars($gpu['gl_shading_language_version'] ?? '—') ?></code></div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: EGL Information -->
                    <div class="mb-4">
                        <h6 class="text-uppercase font-weight-bold text-muted mb-3" style="font-size:0.8rem; letter-spacing:1px;">
                            <i class="fas fa-layer-group mr-1"></i> EGL Information
                        </h6>
                        <div class="details-grid">
                            <div class="detail-card">
                                <div class="detail-icon"><i class="fas fa-industry"></i></div>
                                <div class="detail-label">EGL Vendor</div>
                                <div class="detail-value"><code><?= htmlspecialchars($gpu['egl_vendor'] ?? '—') ?></code></div>
                            </div>
                            <div class="detail-card">
                                <div class="detail-icon"><i class="fas fa-code"></i></div>
                                <div class="detail-label">EGL Version</div>
                                <div class="detail-value"><code><?= htmlspecialchars($gpu['egl_version'] ?? '—') ?></code></div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Capabilities & Limits -->
                    <div class="mb-4">
                        <h6 class="text-uppercase font-weight-bold text-muted mb-3" style="font-size:0.8rem; letter-spacing:1px;">
                            <i class="fas fa-expand-arrows-alt mr-1"></i> Capabilities & Limits
                        </h6>
                        <div class="details-grid">
                            <div class="detail-card success">
                                <div class="detail-icon"><i class="fas fa-image"></i></div>
                                <div class="detail-label">Max Texture Size</div>
                                <div class="detail-value"><?= number_format($gpu['max_texture_size'] ?? 0) ?> px</div>
                            </div>
                            <div class="detail-card success">
                                <div class="detail-icon"><i class="fas fa-square"></i></div>
                                <div class="detail-label">Max Renderbuffer</div>
                                <div class="detail-value"><?= number_format($gpu['max_renderbuffer_size'] ?? 0) ?> px</div>
                            </div>
                            <div class="detail-card success">
                                <div class="detail-icon"><i class="fas fa-window-maximize"></i></div>
                                <div class="detail-label">Max Viewport</div>
                                <div class="detail-value"><?= $gpu['max_viewport_width'] ?? 0 ?> × <?= $gpu['max_viewport_height'] ?? 0 ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: API Support -->
                    <div class="mb-4">
                        <h6 class="text-uppercase font-weight-bold text-muted mb-3" style="font-size:0.8rem; letter-spacing:1px;">
                            <i class="fas fa-check-double mr-1"></i> API Support
                        </h6>
                        <div class="details-grid">
                            <div class="detail-card info">
                                <div class="detail-icon"><i class="fas fa-check-circle"></i></div>
                                <div class="detail-label">OpenGL ES 3.0</div>
                                <div class="detail-value"><?= !empty($gpu['supports_gles30']) ? '<span class="badge badge-success">Supported</span>' : '<span class="badge badge-secondary">Not Supported</span>' ?></div>
                            </div>
                            <div class="detail-card info">
                                <div class="detail-icon"><i class="fas fa-check-circle"></i></div>
                                <div class="detail-label">OpenGL ES 3.1</div>
                                <div class="detail-value"><?= !empty($gpu['supports_gles31']) ? '<span class="badge badge-success">Supported</span>' : '<span class="badge badge-secondary">Not Supported</span>' ?></div>
                            </div>
                            <div class="detail-card info">
                                <div class="detail-icon"><i class="fas fa-check-circle"></i></div>
                                <div class="detail-label">OpenGL ES 3.2</div>
                                <div class="detail-value"><?= !empty($gpu['supports_gles32']) ? '<span class="badge badge-success">Supported</span>' : '<span class="badge badge-secondary">Not Supported</span>' ?></div>
                            </div>
                            <div class="detail-card <?= !empty($gpu['vulkan_available']) ? 'success' : 'secondary' ?>">
                                <div class="detail-icon"><i class="fab fa-vulkan"></i></div>
                                <div class="detail-label">Vulkan</div>
                                <div class="detail-value">
                                    <?= !empty($gpu['vulkan_available']) ? '<span class="badge badge-success">Available</span> <span class="text-muted ml-1">v' . htmlspecialchars($gpu['vulkan_version'] ?? '?') . '</span>' : '<span class="badge badge-secondary">Not Available</span>' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Extensions -->
                    <div class="mb-2">
                        <h6 class="text-uppercase font-weight-bold text-muted mb-3" style="font-size:0.8rem; letter-spacing:1px;">
                            <i class="fas fa-puzzle-piece mr-1"></i> Extensions
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card card-outline card-warning h-100">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0" style="font-size:0.85rem;">
                                            <i class="fas fa-puzzle-piece mr-1 text-warning"></i>
                                            GL Extensions
                                            <span class="badge badge-warning float-right"><?= count($gpu['gl_extensions'] ?? []) ?></span>
                                        </h6>
                                    </div>
                                    <div class="card-body py-2" style="max-height:180px; overflow-y:auto;">
                                        <?php if (!empty($gpu['gl_extensions'])): ?>
                                            <?php foreach ($gpu['gl_extensions'] as $ext): ?>
                                                <span class="badge badge-light text-dark mr-1 mb-1"><?= htmlspecialchars($ext) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted font-italic">None</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card card-outline card-warning h-100">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0" style="font-size:0.85rem;">
                                            <i class="fas fa-puzzle-piece mr-1 text-warning"></i>
                                            EGL Extensions
                                            <span class="badge badge-warning float-right"><?= count($gpu['egl_extensions'] ?? []) ?></span>
                                        </h6>
                                    </div>
                                    <div class="card-body py-2" style="max-height:180px; overflow-y:auto;">
                                        <?php if (!empty($gpu['egl_extensions'])): ?>
                                            <?php foreach ($gpu['egl_extensions'] as $ext): ?>
                                                <span class="badge badge-light text-dark mr-1 mb-1"><?= htmlspecialchars($ext) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted font-italic">None</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">No GPU data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Media Codecs Modal -->
<div class="modal fade" id="modal-codecs-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-film mr-2"></i>Media Codecs (<?= count($codecs) ?>)</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <?php if (!empty($codecs)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Encoder/Decoder</th>
                                    <th>HW Accel</th>
                                    <th>Max Resolution</th>
                                    <th>Color Formats</th>
                                    <th>Profiles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($codecs as $c): ?>
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
                                            <?= $c['max_width'] ?? 0 ?> x <?= $c['max_height'] ?? 0 ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['color_formats'])): ?>
                                            <?php foreach ($c['color_formats'] as $fmt): ?>
                                                <span class="badge badge-light text-dark mr-1 mb-1"><?= htmlspecialchars($fmt) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['profile_levels'])): ?>
                                            <?php foreach ($c['profile_levels'] as $pl): ?>
                                                <span class="badge badge-primary mr-1 mb-1">
                                                    P:<?= htmlspecialchars($pl['profile'] ?? '?') ?> L:<?= htmlspecialchars($pl['level'] ?? '?') ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No media codecs data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Input Devices Modal -->
<div class="modal fade" id="modal-inputs-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-keyboard mr-2"></i>Input Devices (<?= count($inputs) ?>)</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <?php if (!empty($inputs)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Descriptor</th>
                                    <th>Vendor/Product</th>
                                    <th>Sources</th>
                                    <th>Keyboard Type</th>
                                    <th>Motion Axes</th>
                                    <th>Vibrator</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($inputs as $d): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><strong><?= htmlspecialchars($d['name'] ?? '—') ?></strong></td>
                                    <td><code><?= htmlspecialchars($d['descriptor'] ?? '—') ?></code></td>
                                    <td>
                                        <?php if (!empty($d['vendor_id']) || !empty($d['product_id'])): ?>
                                            0x<?= dechex($d['vendor_id'] ?? 0) ?> / 0x<?= dechex($d['product_id'] ?? 0) ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($d['sources'])): ?>
                                            <?php foreach ($d['sources'] as $src): ?>
                                                <span class="badge badge-info mr-1 mb-1"><?= htmlspecialchars($src) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($d['keyboard_type'] ?? '—') ?></td>
                                    <td>
                                        <?php if (!empty($d['motion_ranges'])): ?>
                                            <?php foreach ($d['motion_ranges'] as $mr): ?>
                                                <span class="badge badge-light text-dark mr-1 mb-1">
                                                    <?= htmlspecialchars($mr['axis'] ?? '?') ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
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
                <?php else: ?>
                    <p class="text-muted text-center p-4">No input devices data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endforeach; endif; ?>

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>