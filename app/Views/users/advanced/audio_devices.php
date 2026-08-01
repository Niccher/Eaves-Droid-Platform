<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-headphones text-secondary mr-2"></i>Audio Devices</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Audio input/output devices, streams, and volume profiles</p>
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
                            <th><i class="fas fa-tag mr-1"></i>Device</th>
                            <th><i class="fas fa-signal mr-1"></i>Type</th>
                            <th><i class="fas fa-arrows-alt-h mr-1"></i>Direction</th>
                            <th><i class="fas fa-wave-square mr-1"></i>Sample Rates</th>
                            <th><i class="fas fa-code mr-1"></i>Encoding</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-headphones fa-3x text-muted mb-3"></i><h4>No audio device data</h4><p class="text-muted">Snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($r['product_name'] ?? '—') ?></strong>
                                    <?php if (!empty($r['address'])): ?><br><small class="text-muted"><?= htmlspecialchars($r['address']) ?></small><?php endif; ?>
                                </td>
                                <td><span class="badge badge-info p-2"><?= htmlspecialchars($r['device_type'] ?? '—') ?></span></td>
                                <td>
                                    <?php if (!empty($r['is_sink'])): ?><span class="badge badge-success p-2 mr-1"><i class="fas fa-volume-up mr-1"></i>Sink</span><?php endif; ?>
                                    <?php if (!empty($r['is_source'])): ?><span class="badge badge-primary p-2"><i class="fas fa-microphone mr-1"></i>Source</span><?php endif; ?>
                                    <?php if (empty($r['is_sink']) && empty($r['is_source'])): ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(is_array($r['sample_rates']) ? implode(', ', $r['sample_rates']) : ($r['sample_rates'] ?? '—')) ?></td>
                                <td><code class="small"><?= htmlspecialchars($r['encoding'] ?? '—') ?></code></td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/hardware/audio_devices/delete') ?>"
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
