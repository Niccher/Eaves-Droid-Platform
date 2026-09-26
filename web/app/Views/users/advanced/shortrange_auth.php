<?php /** @var array $latest_bt @var array $latest_nfc @var array $latest_bio @var array $history @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-bluetooth-b text-purple mr-2" style="color:#6f42c1;"></i>Short-Range & Auth</h1>
                        <span class="badge badge-purple border p-2 text-white" style="background:#6f42c1;border-color:#6f42c1;"><i class="fas fa-database mr-1"></i>Snapshots: <b><?= count($history ?? []) ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Bluetooth, NFC, and biometric authentication sensors</p>
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
                            <ul class="nav nav-tabs card-header-tabs" id="shortrangeTabs" role="tablist">
                                <li class="nav-item"><a class="nav-link active" id="tab-bt" data-toggle="tab" href="#bluetooth" role="tab"><i class="fab fa-bluetooth-b mr-1" style="color:#6f42c1;"></i>Bluetooth</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab-nfc" data-toggle="tab" href="#nfc" role="tab"><i class="fas fa-signal mr-1" style="color:#17a2b8;"></i>NFC</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab-bio" data-toggle="tab" href="#biometric" role="tab"><i class="fas fa-fingerprint mr-1" style="color:#ffc107;"></i>Biometric</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                                <div class="tab-content" id="shortrangeTabsContent">
                                    <!-- Bluetooth Tab -->
<div class="tab-pane fade show active" id="bluetooth" role="tabpanel">
                                         <?php if (!empty($latest_bt)): ?>
                                             <div class="row mb-3">
                                                 <div class="col-md-6">
                                                     <h6 class="text-muted mb-3"><i class="fab fa-bluetooth-b mr-1" style="color:#6f42c1;"></i>Adapter</h6>
                                                     <dl class="row mb-0">
                                                         <dt class="col-sm-4">Name</dt><dd class="col-sm-8 font-weight-bold"><?= htmlspecialchars($latest_bt['adapter_name'] ?? '—') ?></dd>
                                                        <dt class="col-sm-4">Address</dt><dd class="col-sm-8"><code><?= htmlspecialchars($latest_bt['adapter_address'] ?? '—') ?></code></dd>
                                                        <dt class="col-sm-4">State</dt><dd class="col-sm-8">
                                                            <span class="badge badge-<?= !empty($latest_bt['is_enabled']) ? 'success' : 'danger' ?> p-2">
                                                                <i class="fas fa-<?= !empty($latest_bt['is_enabled']) ? 'check-circle' : 'times-circle' ?> mr-1"></i>
                                                                <?= !empty($latest_bt['is_enabled']) ? 'Enabled' : 'Disabled' ?>
                                                            </span>
                                                        </dd>
                                                        <dt class="col-sm-4">Paired Devices</dt><dd class="col-sm-8"><span class="badge badge-warning p-2"><?= $latest_bt['paired_count'] ?? 0 ?></span></dd>
                                                    </dl>
                                                </div>
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-3"><i class="fas fa-list mr-1"></i>Paired Devices (<?= count($latest_bt['paired_devices'] ?? []) ?>)</h6>
                                                <?php $paired = $latest_bt['paired_devices'] ?? []; ?>
                                                <?php if (!empty($paired)): ?>
                                                    <div class="table-responsive" style="max-height:300px;overflow:auto;">
                                                        <table class="table table-sm table-hover mb-0">
                                                            <thead class="thead-light"><tr><th>Name</th><th>Address</th><th>Type</th><th>Bond</th><th>RSSI</th></tr></thead>
                                                            <tbody>
                                                            <?php foreach (array_slice($paired, 0, 20) as $dev): ?>
                                                                <tr>
                                                                    <td><strong><?= htmlspecialchars($dev['bt_name'] ?? $dev['name'] ?? '—') ?></strong></td>
                                                                    <td><code><?= htmlspecialchars($dev['bt_address'] ?? $dev['address'] ?? '—') ?></code></td>
                                                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($dev['bt_type'] ?? $dev['type'] ?? '—') ?></span></td>
                                                                    <td><span class="badge badge-<?= ($dev['bond_state'] ?? '') === 'BONDED' ? 'success' : 'warning' ?>"><?= htmlspecialchars($dev['bond_state'] ?? '—') ?></span></td>
                                                                    <td><?= $dev['rssi'] ?? '—' ?> dBm</td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <p class="text-muted">No paired devices</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No Bluetooth data</p>
                                    <?php endif; ?>
                                </div>

<!-- NFC Tab -->
                                 <div class="tab-pane fade" id="nfc" role="tabpanel">
                                     <?php if (!empty($latest_nfc)): ?>
                                         <div class="row">
                                             <div class="col-md-6">
                                                 <h6 class="text-muted mb-3"><i class="fas fa-signal mr-1" style="color:#17a2b8;"></i>NFC Status</h6>
                                                 <dl class="row mb-0">
                                                     <dt class="col-sm-4">Available</dt><dd class="col-sm-8"><?= !empty($latest_nfc['nfc_available']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                     <dt class="col-sm-4">Supported</dt><dd class="col-sm-8"><?= !empty($latest_nfc['nfc_supported']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                     <dt class="col-sm-4">Enabled</dt><dd class="col-sm-8"><?= !empty($latest_nfc['nfc_enabled']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                     <dt class="col-sm-4">Secure NFC</dt><dd class="col-sm-8"><?= !empty($latest_nfc['nfc_secure_nfc']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                     <dt class="col-sm-4">Secure Supported</dt><dd class="col-sm-8"><?= !empty($latest_nfc['nfc_secure_supported']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                 </dl>
                                                             </div>
                                             <div class="col-md-6">
                                                 <h6 class="text-muted mb-3"><i class="fas fa-puzzle-piece mr-1"></i>Features</h6>
                                                 <?php $features = json_decode($latest_nfc['features_json'] ?? '[]', true); ?>
                                                <?php if (!empty($features)): ?>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php foreach ($features as $f): ?>
                                                            <span class="badge badge-info p-2"><?= htmlspecialchars($f) ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <p class="text-muted">No features reported</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No NFC data</p>
                                    <?php endif; ?>
                                </div>

<!-- Biometric Tab -->
                                     <div class="tab-pane fade" id="biometric" role="tabpanel">
                                         <?php if (!empty($latest_bio)): ?>
                                        <div class="row">
                                            <?php foreach ($latest_bio as $bio): ?>
                                                <div class="col-md-6 mb-3">
                                                    <div class="card card-outline card-warning shadow-sm h-100" style="border-color:#ffc107;">
                                                        <div class="card-header bg-light">
                                                            <h6 class="mb-0 d-flex justify-content-between align-items-center">
                                                                <span>
                                                                    <i class="fas fa-fingerprint mr-1" style="color:#ffc107;"></i>
                                                                    <?= htmlspecialchars($bio['sensor_type'] ?? $bio['modality'] ?? 'Biometric Sensor') ?>
                                                                </span>
                                                                <span class="badge badge-<?= !empty($bio['enrolled']) ? 'success' : 'secondary' ?>">
                                                                    <?= !empty($bio['enrolled']) ? 'Enrolled' : 'Not Enrolled' ?>
                                                                </span>
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <dl class="row mb-0 small">
                                                                <dt class="col-sm-5">Sensor ID</dt><dd class="col-sm-7"><?= $bio['sensor_id'] ?? '—' ?></dd>
                                                                <dt class="col-sm-5">Strength</dt><dd class="col-sm-7"><?= htmlspecialchars($bio['strength'] ?? '—') ?></dd>
                                                                <dt class="col-sm-5">Authenticator IDs</dt><dd class="col-sm-7">
                                                                    <?php $ids = json_decode($bio['authenticator_ids'] ?? '[]', true); ?>
                                                                    <?php if (!empty($ids)): ?><?php foreach ($ids as $id): ?><span class="badge badge-light text-dark mr-1"><?= htmlspecialchars($id) ?></span><?php endforeach; ?><?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                                </dd>
                                                                <dt class="col-sm-5">Enroll Count</dt><dd class="col-sm-7"><?= (int)($bio['enroll_count'] ?? 0) ?></dd>
                                                                <dt class="col-sm-5">Max Enrollments</dt><dd class="col-sm-7"><?= (int)($bio['max_enrollments'] ?? 0) ?></dd>
                                                            </dl>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No biometric data</p>
                                    <?php endif; ?>
                                </div>
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
                            <h3 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Short-Range & Auth History</h3>
                            <div class="card-tools ml-auto"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#shortrangeAuthHistoryModal">Show All (<?= count($history ?? []) ?>)</button><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                        <th><i class="fab fa-bluetooth-b mr-1" style="color:#6f42c1;"></i>Bluetooth</th>
                                        <th><i class="fas fa-signal mr-1" style="color:#17a2b8;"></i>NFC</th>
                                        <th><i class="fas fa-fingerprint mr-1" style="color:#ffc107;"></i>Biometric</th>
                                        <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($history)): ?>
                                        <tr><td colspan="6" class="text-center py-5"><div class="empty-state"><i class="fas fa-bluetooth fa-3x text-muted mb-3"></i><h4>No short-range/auth history</h4></div></td></tr>
                                    <?php else: foreach (array_slice($history,0,5) as $h): 
                                        $bt = $h['bluetooth'] ?? [];
                                        $nfc = $h['nfc'] ?? [];
                                        $bio = $h['biometric'] ?? [];
                                        $paired = $bt['paired_devices'] ?? [];
                                    ?>
                                        <tr>
                                            <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                            <td>
                                                <span class="badge badge-<?= !empty($bt['is_enabled']) ? 'success' : 'danger' ?>">
                                                    <?= !empty($bt['is_enabled']) ? 'Enabled' : 'Disabled' ?>
                                                </span>
                                                <br><small class="text-muted"><?= count($paired) ?> paired</small>
                                                <?php if (!empty($bt['adapter_name'])): ?><br><small class="text-muted"><?= htmlspecialchars($bt['adapter_name']) ?></small><?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= !empty($nfc['nfc_enabled']) ? 'info' : 'secondary' ?>">
                                                    <?= !empty($nfc['nfc_enabled']) ? 'Enabled' : 'Disabled' ?>
                                                </span>
                                                <?php if (!empty($nfc['nfc_secure_nfc'])): ?><br><span class="badge badge-success">Secure NFC</span><?php endif; ?>
                                                <br><small class="text-muted">Features: <?= count(json_decode($nfc['features_json'] ?? '[]', true)) ?></small>
                                            </td>
                                            <td>
                                                <?php if (!empty($bio)): ?>
                                                    <?php $enrolled = array_filter($bio, fn($b) => !empty($b['enrolled'])); ?>
                                                    <span class="badge badge-<?= count($enrolled) > 0 ? 'warning' : 'secondary' ?>"><?= count($enrolled) ?> enrolled</span>
                                                    <br><small class="text-muted"><?= count($bio) ?> sensor<?= count($bio) !== 1 ? 's' : '' ?></small>
                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/shortrange_auth/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
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

    <!-- Modal: Full Short-Range & Auth History -->
    <div class="modal fade" id="shortrangeAuthHistoryModal" tabindex="-1" role="dialog" aria-labelledby="shortrangeAuthHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="shortrangeAuthHistoryModalLabel"><i class="fas fa-history mr-2"></i>Full Short-Range & Auth History</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                    <th><i class="fab fa-bluetooth-b mr-1" style="color:#6f42c1;"></i>Bluetooth</th>
                                    <th><i class="fas fa-signal mr-1" style="color:#17a2b8;"></i>NFC</th>
                                    <th><i class="fas fa-fingerprint mr-1" style="color:#ffc107;"></i>Biometric</th>
                                    <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history)): ?>
                                <tr><td colspan="6" class="text-center py-5"><div class="empty-state"><i class="fas fa-bluetooth fa-3x text-muted mb-3"></i><h4>No short-range/auth history</h4></div></td></tr>
                                <?php else: foreach ($history as $h):
                                    $bt = $h['bluetooth'] ?? [];
                                    $nfc = $h['nfc'] ?? [];
                                    $bio = $h['biometric'] ?? [];
                                    $paired = $bt['paired_devices'] ?? [];
                                ?>
                                <tr>
                                    <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                    <td>
                                        <span class="badge badge-<?= !empty($bt['is_enabled']) ? 'success' : 'danger' ?>">
                                            <?= !empty($bt['is_enabled']) ? 'Enabled' : 'Disabled' ?>
                                        </span>
                                        <br><small class="text-muted"><?= count($paired) ?> paired</small>
                                        <?php if (!empty($bt['adapter_name'])): ?><br><small class="text-muted"><?= htmlspecialchars($bt['adapter_name']) ?></small><?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= !empty($nfc['nfc_enabled']) ? 'info' : 'secondary' ?>">
                                            <?= !empty($nfc['nfc_enabled']) ? 'Enabled' : 'Disabled' ?>
                                        </span>
                                        <?php if (!empty($nfc['nfc_secure_nfc'])): ?><br><span class="badge badge-success">Secure NFC</span><?php endif; ?>
                                        <br><small class="text-muted">Features: <?= count(json_decode($nfc['features_json'] ?? '[]', true)) ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($bio)): ?>
                                            <?php $enrolled = array_filter($bio, fn($b) => !empty($b['enrolled'])); ?>
                                            <span class="badge badge-<?= count($enrolled) > 0 ? 'warning' : 'secondary' ?>"><?= count($enrolled) ?> enrolled</span>
                                            <br><small class="text-muted"><?= count($bio) ?> sensor<?= count($bio) !== 1 ? 's' : '' ?></small>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/shortrange_auth/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
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

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>