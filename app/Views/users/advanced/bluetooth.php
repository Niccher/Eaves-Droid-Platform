<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fab fa-bluetooth-b text-primary mr-2"></i>Bluetooth</h1>
                        <span class="badge badge-primary border p-2"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Bluetooth adapter snapshots and paired device profiles</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>


            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid">
    <?php if (empty($rows)): ?>
        <div class="row"><div class="col-12">
            <div class="card card-primary shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="empty-state"><i class="fab fa-bluetooth fa-3x text-muted mb-3"></i><h4>No Bluetooth data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                </div>
            </div>
        </div></div>
    <?php else: foreach ($rows as $r): ?>
        <?php $ts = $r['extracted_at'] ? date('Y-m-d H:i', $r['extracted_at'] / 1000) : 'N/A'; ?>
        <div class="row mb-3">
            <div class="col-lg-4 col-md-6">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-primary elevation-1"><i class="fab fa-bluetooth-b"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Adapter</span>
                        <span class="info-box-number"><?= htmlspecialchars($r['adapter_name'] ?? 'Unknown') ?></span>
                        <small class="text-muted"><?= htmlspecialchars($r['adapter_address'] ?? '') ?></small>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-<?= $r['is_enabled'] ? 'success' : 'danger' ?> elevation-1">
                        <i class="fas fa-<?= $r['is_enabled'] ? 'check-circle' : 'times-circle' ?>"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Status</span>
                        <span class="info-box-number"><?= $r['is_enabled'] ? 'Enabled' : 'Disabled' ?></span>
                        <small class="text-muted">Snapped: <?= $ts ?></small>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-link"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Paired Devices</span>
                        <span class="info-box-number"><?= $r['paired_count'] ?? count($r['paired_devices'] ?? []) ?></span>
                        <small class="text-muted">Bonded</small>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!empty($r['paired_devices'])): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list-ul mr-2"></i>Paired Devices for <em><?= htmlspecialchars($r['adapter_name'] ?? 'adapter') ?></em></h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="thead-light">
                                <tr>
                                    <th>Device Name</th>
                                    <th>Address</th>
                                    <th>Type</th>
                                    <th>Bond State</th>
                                    <th>Alias</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($r['paired_devices'] as $dev): ?>
                                <tr>
                                    <td><i class="fab fa-bluetooth mr-1 text-primary"></i><strong><?= htmlspecialchars($dev['bt_name'] ?? '—') ?></strong></td>
                                    <td><code><?= htmlspecialchars($dev['bt_address'] ?? '—') ?></code></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($dev['bt_type'] ?? '—') ?></span></td>
                                    <td><span class="badge badge-<?= $dev['bond_state'] === 'BONDED' ? 'success' : 'warning' ?>"><?= htmlspecialchars($dev['bond_state'] ?? '—') ?></span></td>
                                    <td class="text-muted"><?= htmlspecialchars($dev['alias'] ?? '—') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; endif; ?>
    <div class="row"><div class="col-12 text-right"><?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?></div></div>
    </div></section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
