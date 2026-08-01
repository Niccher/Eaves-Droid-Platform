<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-fingerprint text-secondary mr-2"></i>Biometric</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Fingerprint / face biometric sensor state and enrollment status</p>
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
                            <th><i class="fas fa-microchip mr-1"></i>Sensor</th>
                            <th><i class="fas fa-industry mr-1"></i>Vendor</th>
                            <th><i class="fas fa-check-circle mr-1"></i>Hardware</th>
                            <th><i class="fas fa-user-check mr-1"></i>Enrollments</th>
                            <th><i class="fas fa-shield-alt mr-1"></i>Secure</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-fingerprint fa-3x text-muted mb-3"></i><h4>No biometric data</h4><p class="text-muted">Snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($r['sensor_type'] ?? '—') ?></strong>
                                    <?php if (!empty($r['sensor_id'])): ?><br><small class="text-muted">ID: <?= htmlspecialchars($r['sensor_id']) ?></small><?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($r['vendor'] ?? '—') ?>
                                    <?php if (!empty($r['model'])): ?><br><small class="text-muted"><?= htmlspecialchars($r['model']) ?></small><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?= !empty($r['is_hardware_detected']) ? '<span class="badge badge-success p-2"><i class="fas fa-check mr-1"></i>Detected</span>' : '<span class="badge badge-danger p-2">Not detected</span>' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= ($r['current_enrollments'] ?? 0) > 0 ? 'success' : 'secondary' ?> p-2">
                                        <?= (int)($r['current_enrollments'] ?? 0) ?> / <?= (int)($r['max_enrollments'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?= !empty($r['device_secure']) ? '<span class="badge badge-success p-2">Yes</span>' : '<span class="badge badge-secondary p-2">No</span>' ?>
                                </td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/hardware/biometric/delete') ?>"
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
