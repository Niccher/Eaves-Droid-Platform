<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-camera text-secondary mr-2"></i>Camera Info</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Camera capabilities, focal lengths, HDR, video modes, and sensor details</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Camera Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="cameraTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-camera mr-1"></i> Camera ID</th>
                            <th><i class="fas fa-expand-arrows-alt mr-1"></i> Facing</th>
                            <th><i class="fas fa-image mr-1"></i> Resolution</th>
                            <th><i class="fas fa-bolt mr-1"></i> Focal Lengths</th>
                            <th><i class="fas fa-magic mr-1"></i> Capabilities</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-camera fa-3x text-muted mb-3"></i><h4>No camera data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $rid = $r['id'] ?? 0;
                            $facing = $r['lens_facing'] ?? 0;
                            $facingLabels = [0 => 'Back', 1 => 'Front', 2 => 'External'];
                            $facingLabel = $facingLabels[$facing] ?? 'Unknown';
                            $resolution = ($r['pixel_array_width'] ?? 0) . '×' . ($r['pixel_array_height'] ?? 0);
                            $focalLengths = $r['available_focal_lengths'] ?? [];
                            $focalCount = is_array($focalLengths) ? count($focalLengths) : 0;
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#camera-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td><code><?= esc($r['camera_id'] ?? '—') ?></code></td>
                                <td><span class="badge badge-secondary"><?= esc($facingLabel) ?></span></td>
                                <td><code class="text-monospace"><?= esc($resolution) ?></code></td>
                                <td><?= $focalCount ?> focal length<?= $focalCount !== 1 ? 's' : '' ?></td>
                                <td>
                                    <?php
                                    $caps = [];
                                    if (!empty($r['flash_available'])) $caps[] = '<span class="badge badge-warning"><i class="fas fa-bolt mr-1"></i>Flash</span>';
                                    if (!empty($r['hdr_capabilities'])) $caps[] = '<span class="badge badge-info"><i class="fas fa-sun mr-1"></i>HDR</span>';
                                    if (!empty($r['night_mode_support'])) $caps[] = '<span class="badge badge-secondary"><i class="fas fa-moon mr-1"></i>Night</span>';
                                    if (!empty($r['macro_mode_support'])) $caps[] = '<span class="badge badge-success"><i class="fas fa-search-plus mr-1"></i>Macro</span>';
                                    if (!empty($r['heic_support'])) $caps[] = '<span class="badge badge-secondary">HEIC</span>';
                                    if (!empty($r['hevc_support'])) $caps[] = '<span class="badge badge-primary">HEVC</span>';
                                    echo implode(' ', $caps);
                                    ?>
                                </td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/camera_info/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="8" class="p-0 border-0">
                                    <div id="camera-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-camera mr-2"></i>Camera Specs</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Camera ID</th><td><code><?= esc($r['camera_id'] ?? '—') ?></code></td></tr>
                                                        <tr><th>Facing</th><td><?= esc($facingLabel) ?></td></tr>
                                                        <tr><th>Resolution</th><td><?= esc($resolution) ?></td></tr>
                                                        <tr><th>Physical Size</th><td><?= esc(($r['physical_width_mm'] ?? '—') . '×' . ($r['physical_height_mm'] ?? '—')) ?> mm</td></tr>
                                                        <tr><th>Focal Lengths</th><td><?= $focalCount ?> values</td></tr>
                                                        <tr><th>Apertures</th><td><?= is_array($r['apertures'] ?? []) ? count($r['apertures']) : 0 ?> values</td></tr>
                                                        <tr><th>Flash</th><td><?= !empty($r['flash_available']) ? 'Yes' : 'No' ?></td></tr>
                                                        <tr><th>Max Zoom</th><td><?= esc($r['max_digital_zoom'] ?? '—') ?>x</td></tr>
                                                        <tr><th>Optical Zoom</th><td><?= esc($r['optical_zoom_range'] ?? '—') ?></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-video mr-2"></i>Video & HDR</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Video Stabilization</th><td><?= !empty($r['available_video_stabilization']) ? 'Yes' : 'No' ?></td></tr>
                                                        <tr><th>HDR</th><td><?= !empty($r['hdr_capabilities']) ? 'Yes' : 'No' ?></td></tr>
                                                        <tr><th>Night Mode</th><td><?= !empty($r['night_mode_support']) ? 'Yes' : 'No' ?></td></tr>
                                                        <tr><th>Macro</th><td><?= !empty($r['macro_mode_support']) ? 'Yes' : 'No' ?></td></tr>
                                                        <tr><th>HEIC</th><td><?= !empty($r['heic_support']) ? 'Yes' : 'No' ?></td></tr>
                                                        <tr><th>HEVC</th><td><?= !empty($r['hevc_support']) ? 'Yes' : 'No' ?></td></tr>
                                                        <tr><th>AV1</th><td><?= !empty($r['av1_support']) ? 'Yes' : 'No' ?></td></tr>
                                                        <tr><th>10-bit Output</th><td><?= !empty($r['10bit_output']) ? 'Yes' : 'No' ?></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-info-circle mr-2"></i>Snapshot Info</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Extracted At</th><td><?= $ts ?></td></tr>
                                                        <tr><th>Entry ID</th><td><code><?= $rid ?></code></td></tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>