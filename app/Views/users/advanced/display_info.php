<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-desktop text-secondary mr-2"></i>Display Info</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Display resolution, density, refresh rate, and multi-display information</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Display Snapshots <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-expand-arrows-alt text-info mr-1"></i> Resolution</th>
                            <th><i class="fas fa-compress-arrows-alt text-warning mr-1"></i> Density (DPI)</th>
                            <th><i class="fas fa-sync-alt text-primary mr-1"></i> Refresh Rate</th>
                            <th><i class="fas fa-tv text-success mr-1"></i> Rotation / Mode</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="6" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-desktop fa-3x text-muted mb-3"></i><h4>No display data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else:
                            $seenDisplays = [];
                            foreach ($rows as $r):
                                $dispKey = ($r['width_px'] ?? '') . 'x' . ($r['height_px'] ?? '') . 'x' . ($r['density_dpi'] ?? '');
                                if (in_array($dispKey, $seenDisplays)) continue;
                                $seenDisplays[] = $dispKey;
                                $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                                $res = ($r['width_px'] ?? '—') . '×' . ($r['height_px'] ?? '—');
                                $realRes = ($r['real_width'] ?? '—') . '×' . ($r['real_height'] ?? '—');
                                $dpi = $r['density_dpi'] ?? '—';
                                $refresh = $r['refresh_rate'] ?? '—';
                                $rotation = $r['rotation'] ?? '—';
                        ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($res) ?></strong>
                                    <br><small class="text-muted">Real: <?= htmlspecialchars($realRes) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= $dpi ?> dpi</span>
                                    <br><small class="text-muted">Scaled: <?= $r['scaled_density'] ?? '—' ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-primary"><?= $refresh ?> Hz</span>
                                    <?php if (!empty($r['mode_refresh_rate'])): ?>
                                        <br><small class="text-muted">Mode: <?= $r['mode_width'] ?? '—' ?>×<?= $r['mode_height'] ?? '—' ?> @ <?= $r['mode_refresh_rate'] ?? '—' ?> Hz</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-warning">Rot: <?= $rotation ?></span>
                                    <?php if (!empty($r['displays_json'])): ?>
                                        <br><small class="text-muted"><?= is_array(json_decode($r['displays_json'], true)) ? count(json_decode($r['displays_json'], true)) : 0 ?> displays</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/display_info/delete') ?>"
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
