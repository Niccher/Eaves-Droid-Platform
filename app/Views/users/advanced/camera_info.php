<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-camera text-pink mr-2"></i>Camera Info</h1>
                        <span class="badge badge-pink border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Camera specifications: lenses, sensors, focal lengths, flash, effects</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-pink shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Camera Snapshots <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-camera text-primary mr-1"></i> Facing</th>
                            <th><i class="fas fa-expand-arrows-alt text-info mr-1"></i> Sensor Size</th>
                            <th><i class="fas fa-project-diagram text-warning mr-1"></i> Focal Lengths</th>
                            <th><i class="fas fa-bolt text-danger mr-1"></i> Flash</th>
                            <th><i class="fas fa-magic text-success mr-1"></i> Effects</th>
                            <th><i class="fas fa-image text-secondary mr-1"></i> Max Resolution</th>
                            <th><i class="fas fa-clock text-muted mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="9" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-camera fa-3x text-muted mb-3"></i><h4>No camera data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else:
                            $seenCameras = [];
                            foreach ($rows as $r):
                                $cameraKey = ($r['lens_facing'] ?? '') . '|' . ($r['physical_width_mm'] ?? 0);
                                if (in_array($cameraKey, $seenCameras)) continue;
                                $seenCameras[] = $cameraKey;
                                $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                                $flash = $r['flash_available'] ?? 0;
                                $effects = $r['available_effects'] ?? [];
                                $focal = $r['available_focal_lengths'] ?? [];
                            ?>
                             <tr>
                                 <td>
                                     <span class="badge badge-<?= ($r['lens_facing'] ?? '') === 'front' ? 'info' : (($r['lens_facing'] ?? '') === 'back' ? 'success' : 'secondary') ?>">
                                        <i class="fas fa-<?= ($r['lens_facing'] ?? '') === 'front' ? 'user' : (($r['lens_facing'] ?? '') === 'back' ? 'camera' : 'question') ?> mr-1"></i>
                                        <?= ucfirst($r['lens_facing'] ?? 'unknown') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="font-weight-bold">
                                        <i class="fas fa-square-full mr-1 text-primary"></i>
                                        <?= number_format($r['physical_width_mm'] ?? 0, 2) ?> x <?= number_format($r['physical_height_mm'] ?? 0, 2) ?> mm
                                    </div>
                                    <small class="text-muted">Pixels: <?= number_format($r['pixel_array_width'] ?? 0) ?> x <?= number_format($r['pixel_array_height'] ?? 0) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($focal)): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($focal as $f): ?>
                                                <span class="badge badge-warning"><i class="fas fa-ruler mr-1"></i><?= number_format((float)$f, 1) ?>mm</span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $flash ? 'danger' : 'light' ?>">
                                        <i class="fas fa-<?= $flash ? 'bolt' : 'times' ?> mr-1"></i><?= $flash ? 'Yes' : 'No' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($effects)): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($effects as $e): ?>
                                                <span class="badge badge-success"><i class="fas fa-magic mr-1"></i><?= $e ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="font-weight-bold">
                                        <i class="fas fa-image mr-1 text-secondary"></i>
                                        <?= number_format($r['max_jpeg_width'] ?? 0) ?> x <?= number_format($r['max_jpeg_height'] ?? 0) ?>
                                    </div>
                                </td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/camera_info/delete') ?>"
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
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>