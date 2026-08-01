<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-satellite text-secondary mr-2"></i>GNSS Hardware</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">GPS/GNSS receiver capabilities, constellations, and measurements</p>
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
                            <th><i class="fas fa-satellite mr-1"></i>Constellations</th>
                            <th><i class="fas fa-satellite-dish mr-1"></i>Antenna</th>
                            <th><i class="fas fa-satellite mr-1"></i>Max Satellites</th>
                            <th><i class="fas fa-tachometer-alt mr-1"></i>Capabilities</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="6" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-satellite fa-3x text-muted mb-3"></i><h4>No GNSS hardware data</h4><p class="text-muted">Snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php $constellations = $r['constellations_supported'] ?? []; ?>
                            <tr>
                                <td>
                                    <?php if (is_array($constellations) && !empty($constellations)): ?>
                                        <?php foreach (array_slice($constellations, 0, 4) as $c): ?>
                                            <span class="badge badge-primary p-2 mr-1"><?= htmlspecialchars(is_array($c) ? ($c['name'] ?? '—') : $c) ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($constellations) > 4): ?><span class="badge badge-secondary p-2">+<?= count($constellations) - 4 ?></span><?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($r['antenna_type'] ?? '—') ?></td>
                                <td class="text-center">
                                    <span class="badge badge-info p-2"><?= (int)($r['max_satellites_tracked'] ?? 0) ?> tracked</span>
                                    <?php if (($r['max_satellites_used'] ?? 0)): ?><br><small class="text-muted"><?= (int)$r['max_satellites_used'] ?> used</small><?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($r['raw_measurements_supported'])): ?><span class="badge badge-success p-2 mr-1">Raw Meas.</span><?php endif; ?>
                                    <?php if (!empty($r['agps_supported'])): ?><span class="badge badge-info p-2 mr-1">AGPS</span><?php endif; ?>
                                    <?php if (!empty($r['dead_reckoning_supported'])): ?><span class="badge badge-warning p-2 mr-1">DR</span><?php endif; ?>
                                    <?php if (!empty($r['gnss_year_of_hardware'])): ?><span class="badge badge-secondary p-2"><?= htmlspecialchars($r['gnss_year_of_hardware']) ?></span><?php endif; ?>
                                </td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/hardware/gnss_hardware/delete') ?>"
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
