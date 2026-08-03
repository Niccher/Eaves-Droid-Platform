<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-fingerprint text-secondary mr-2"></i>Biometric</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Fingerprint, face, iris sensors</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Biometric Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="biometricTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-fingerprint mr-1"></i> Sensor ID</th>
                            <th><i class="fas fa-tag mr-1"></i> Type</th>
                            <th><i class="fas fa-signal mr-1"></i> Strength</th>
                            <th><i class="fas fa-industry mr-1"></i> Vendor</th>
                            <th><i class="fas fa-user-check mr-1"></i> Enrolled</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-fingerprint fa-3x text-muted mb-3"></i><h4>No biometric data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $rid = $r['id'] ?? 0;
                            $typeLabels = [1 => 'Fingerprint', 2 => 'Face', 3 => 'Iris', 4 => 'Voice', 5 => 'Vein'];
                            $type = $typeLabels[$r['sensor_type'] ?? 0] ?? ($r['sensor_type'] ?? 'Unknown');
                            $strengthLabels = [0 => 'Unknown', 1 => 'Weak', 2 => 'Standard', 3 => 'Strong'];
                            $strength = $strengthLabels[$r['sensor_strength'] ?? 0] ?? 'Unknown';
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#biometric-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td><code><?= esc($r['sensor_id'] ?? '—') ?></code></td>
                                <td><span class="badge badge-secondary"><?= esc($type) ?></span></td>
                                <td><span class="badge badge-<?= $r['sensor_strength'] >= 3 ? 'success' : ($r['sensor_strength'] >= 2 ? 'warning' : 'info') ?>"><?= esc($strength) ?></span></td>
                                <td><?= esc($r['vendor'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($r['has_enrollments'])): ?>
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i><?= (int)($r['current_enrollments'] ?? 0) ?> enrolled</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">None</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/biometric/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="8" class="p-0 border-0">
                                    <div id="biometric-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-fingerprint mr-2"></i>Sensor Details</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Sensor ID</th><td><code><?= esc($r['sensor_id'] ?? '—') ?></code></td></tr>
                                                        <tr><th>Type</th><td><span class="badge badge-secondary"><?= esc($type) ?></span></td></tr>
                                                        <tr><th>Strength</th><td><span class="badge badge-<?= $r['sensor_strength'] >= 3 ? 'success' : ($r['sensor_strength'] >= 2 ? 'warning' : 'info') ?>"><?= esc($strength) ?></span></td></tr>
                                                        <tr><th>Vendor</th><td><?= esc($r['vendor'] ?? '—') ?></td></tr>
                                                        <tr><th>Version</th><td><?= esc($r['version'] ?? '—') ?></td></tr>
                                                        <tr><th>Max Enrollments</th><td><?= (int)($r['max_enrollments'] ?? 0) ?></td></tr>
                                                        <tr><th>Current Enrollments</th><td><?= (int)($r['current_enrollments'] ?? 0) ?></td></tr>
                                                        <tr><th>Has Enrollments</th><td><?= !empty($r['has_enrollments']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Hardware Detected</th><td><?= !empty($r['is_hardware_detected']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Hardware Available</th><td><?= !empty($r['is_hardware_available']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Device Secure</th><td><?= !empty($r['device_secure']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Crypto Object</th><td><?= !empty($r['crypto_object_supported']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-lock mr-2"></i>Security</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Authenticator ID</th><td><?= esc($r['authenticator_id'] ?? '—') ?></td></tr>
                                                        <tr><th>Challenge Counter</th><td><?= (int)($r['challenge_counter'] ?? 0) ?></td></tr>
                                                        <tr><th>Failed Attempts</th><td><?= (int)($r['failed_attempts'] ?? 0) ?></td></tr>
                                                        <tr><th>Lockout Time</th><td><?= (int)($r['lockout_time'] ?? 0) ?> ms</td></tr>
                                                        <tr><th>Lockout Permanent</th><td><?= !empty($r['lockout_permanent']) ? '<span class="badge badge-secondary">Yes</span>' : '<span class="badge badge-success">No</span>' ?></td></tr>
                                                        <tr><th>Hardware Auth Token</th><td><?= !empty($r['hardware_auth_token']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Enrollment Progress</th><td><?= (int)($r['enrollment_progress'] ?? 0) ?>%</td></tr>
                                                        <tr><th>Template Version</th><td><?= esc($r['template_version'] ?? '—') ?></td></tr>
                                                        <tr><th>Weak Auth Timeout</th><td><?= (int)($r['weak_auth_timeout_ms'] ?? 0) ?> ms</td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-12">
                                                    <h6 class="mt-3"><i class="fas fa-info-circle mr-2"></i>Snapshot Info</h6>
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