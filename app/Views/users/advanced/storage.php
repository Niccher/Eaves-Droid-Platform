<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-hdd text-secondary mr-2"></i>Storage</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Volumes, capacity, available space, mount points</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Storage Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="storageTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-folder mr-1"></i> Volume Path</th>
                            <th><i class="fas fa-hdd mr-1"></i> Total</th>
                            <th><i class="fas fa-arrow-down mr-1"></i> Available</th>
                            <th><i class="fas fa-arrow-up mr-1"></i> Used</th>
                            <th><i class="fas fa-tag mr-1"></i> Type</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-hdd fa-3x text-muted mb-3"></i><h4>No storage data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $rid = $r['id'] ?? 0;
                            $total = $r['total_formatted'] ?? '—';
                            $avail = $r['available_formatted'] ?? '—';
                            $used = $r['used_formatted'] ?? '—';
                            $pct = ($r['total_bytes'] ?? 0) > 0 ? round((($r['used_bytes'] ?? 0) / $r['total_bytes']) * 100) : 0;
                            $pctClass = $pct > 90 ? 'danger' : ($pct > 70 ? 'warning' : 'success');
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#storage-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td><code><?= esc($r['volume_path'] ?? '—') ?></code></td>
                                <td><?= esc($total) ?></td>
                                <td><?= esc($avail) ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 mr-2" style="height: 8px;">
                                            <div class="bg-<?= $pctClass ?>" role="progressbar" style="width: <?= $pct ?>%"></div>
                                        </div>
                                        <span class="font-weight-bold"><?= $used ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?= (!empty($r['is_removable'])) ? 'warning' : 'info' ?>">
                                        <?= (!empty($r['is_removable'])) ? 'Removable' : 'Internal' ?>
                                    </span>
                                </td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/storage/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="8" class="p-0 border-0">
                                    <div id="storage-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-hdd mr-2"></i>Volume Details</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Path</th><td><code><?= esc($r['volume_path'] ?? '—') ?></code></td></tr>
                                                        <tr><th>Description</th><td><?= esc($r['description'] ?? '—') ?></td></tr>
                                                        <tr><th>Total</th><td><?= esc($total) ?></td></tr>
                                                        <tr><th>Available</th><td><?= esc($avail) ?></td></tr>
                                                        <tr><th>Used</th><td><?= esc($used) ?></td></tr>
                                                        <tr><th>Free</th><td><?= esc($r['free_formatted'] ?? '—') ?></td></tr>
                                                        <tr><th>Usage</th><td><?= $pct ?>%</td></tr>
                                                        <tr><th>Type</th><td><?= (!empty($r['is_removable'])) ? 'Removable' : 'Internal' ?></td></tr>
                                                        <tr><th>State</th><td><span class="badge badge-<?= ($r['state'] ?? '') === 'mounted' ? 'success' : 'secondary' ?>"><?= ucfirst($r['state'] ?? '—') ?></span></td></tr>
                                                        <tr><th>Bytes Total</th><td><?= number_format($r['total_bytes'] ?? 0) ?></td></tr>
                                                        <tr><th>Bytes Available</th><td><?= number_format($r['available_bytes'] ?? 0) ?></td></tr>
                                                        <tr><th>Bytes Free</th><td><?= number_format($r['free_bytes'] ?? 0) ?></td></tr>
                                                        <tr><th>Bytes Used</th><td><?= number_format($r['used_bytes'] ?? 0) ?></td></tr>
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