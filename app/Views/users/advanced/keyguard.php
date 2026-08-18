<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
// Separate the latest lockscreen configuration metrics from the event rows
$latestConfig = null;
foreach ($rows as $r) {
    if ($r['is_secure'] !== null) {
        $latestConfig = $r;
        break;
    }
}
?>

<style>
.key-card { border-radius: 12px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #eef2f5; overflow: hidden; margin-bottom: 24px; }
.key-header { background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%); color: #fff; padding: 18px 22px; }
.key-metric-box { background: #f8fafc; border-radius: 8px; border: 1px solid #eef2f5; padding: 12px 16px; height: 100%; }
.key-label { font-size: 10px; font-weight: 700; color: #8892a0; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
.key-val { font-size: 14px; font-weight: 600; color: #2c3e50; }
.audit-item { position: relative; padding-left: 30px; margin-bottom: 18px; }
.audit-item::before { content: ''; position: absolute; left: 10px; top: 0; bottom: -18px; width: 2px; background: #edf2f7; }
.audit-item:last-child::before { display: none; }
.audit-dot { position: absolute; left: 5px; top: 3px; width: 12px; height: 12px; border-radius: 50%; background: #a0aec0; border: 2px solid #fff; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <h1 class="h2 mb-0"><i class="fas fa-lock text-primary mr-2"></i>Keyguard &amp; Lockscreen Security</h1>
                    <p class="text-muted mt-1 mb-0">Lockscreen event audits, system authentication methods, and credential encryption indicators</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>

            <!-- Callout -->
            <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;">
                <h5 class="font-weight-bold text-info"><i class="fas fa-key mr-2"></i>Authentication Telemetry</h5>
                <p class="text-secondary mb-0" style="font-size:14px;">Auditing keyguard settings and transition attempts flags abnormal unlock patterns or brute force attempts targeting physical security.</p>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- System Security Settings Card -->
            <div class="key-card">
                <div class="key-header">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-shield-alt mr-2"></i>Device Credential Settings Status</h5>
                </div>
                <div class="p-4 bg-light">
                    <?php if (!$latestConfig): ?>
                        <span class="text-muted small">No lockscreen settings cataloged yet.</span>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="key-metric-box">
                                    <div class="key-label"><i class="fas fa-shield-alt mr-1"></i>Secure Lockscreen</div>
                                    <div class="key-val">
                                        <span class="badge badge-<?= !empty($latestConfig['is_secure']) ? 'success' : 'danger' ?> px-2 py-1">
                                            <?= !empty($latestConfig['is_secure']) ? 'SECURE' : 'INSECURE' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="key-metric-box">
                                    <div class="key-label"><i class="fas fa-hdd mr-1"></i>Storage Encryption</div>
                                    <div class="key-val">
                                        <span class="badge badge-<?= str_contains(strtolower($latestConfig['storage_encryption_status'] ?? ''), 'encrypt') ? 'success' : 'warning' ?> px-2 py-1">
                                            <?= esc(strtoupper($latestConfig['storage_encryption_status'] ?? 'UNKNOWN')) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="key-metric-box">
                                    <div class="key-label"><i class="fas fa-bell mr-1"></i>Notifs on Lockscreen</div>
                                    <div class="key-val"><?= esc($latestConfig['notifications_on_lockscreen'] ?: 'Enabled') ?></div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="key-metric-box">
                                    <div class="key-label"><i class="fas fa-eye-slash mr-1"></i>Sensitive Content</div>
                                    <div class="key-val">
                                        <span class="badge badge-<?= !empty($latestConfig['sensitive_notifications_hidden']) ? 'success' : 'warning' ?> px-2 py-1">
                                            <?= !empty($latestConfig['sensitive_notifications_hidden']) ? 'HIDDEN' : 'VISIBLE' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lockscreen Event Audits -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 font-weight-bold text-secondary"><i class="fas fa-history mr-2"></i>Lock / Unlock Transitions Event Logs</h5>
                </div>
                <div class="card-body bg-light p-4">
                    <?php if (empty($rows)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                            <h4 class="text-secondary">No keyguard events logged</h4>
                            <p class="text-muted">Transition events will display here once captured.</p>
                        </div>
                    <?php else: foreach ($rows as $r):
                        $isUnlock = strtolower($r['event_type'] ?? '') == 'unlock';
                        $success = !empty($r['success']);
                        $ts = !empty($r['timestamp']) ? format_timestamp_display((int)$r['timestamp']) : ($r['extracted_at'] ? format_timestamp_display((int)$r['extracted_at']) : '—');
                        $failed = (int)($r['failed_attempts'] ?? 0);
                        
                        $dotColor = '#a0aec0';
                        if ($isUnlock) {
                            $dotColor = $success ? '#48bb78' : '#e53e3e';
                        } else {
                            $dotColor = '#edf2f7';
                        }
                        
                        $rid = $r['id'] ?? 0;
                        ?>
                        <div class="audit-item">
                            <div class="audit-dot" style="background: <?= $dotColor ?>;"></div>
                            <div class="card border-0 shadow-xs mb-0" style="border-radius: 8px;">
                                <div class="p-3 d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="badge badge-<?= $isUnlock ? ($success ? 'success' : 'danger') : 'secondary' ?> mr-2 px-2 py-1 font-weight-bold">
                                            <i class="fas <?= $isUnlock ? ($success ? 'fa-unlock' : 'fa-times-circle') : 'fa-lock' ?> mr-1"></i>
                                            <?= $isUnlock ? ($success ? 'UNLOCKED' : 'UNLOCK ATTEMPT FAILED') : 'LOCKED' ?>
                                        </span>
                                        <?php if ($r['method']): ?>
                                            <span class="badge badge-light border text-secondary mr-2 font-weight-normal"><i class="fas fa-fingerprint mr-1"></i>Method: <?= esc($r['method']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($failed > 0): ?>
                                            <span class="badge badge-danger mr-2">Failed attempts: <?= $failed ?></span>
                                        <?php endif; ?>
                                        <?php if ($r['strong_auth_required_reason']): ?>
                                            <span class="text-danger small font-family-monospace mr-2">Reason: <?= esc($r['strong_auth_required_reason']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex align-items-center mt-2 mt-sm-0">
                                        <small class="text-muted mr-3"><i class="fas fa-clock mr-1"></i><?= $ts ?></small>
                                        <button class="btn btn-xs btn-outline-danger delete-row"
                                                data-id="<?= $rid ?>"
                                                data-url="<?= base_url('advanced/software/keyguard/delete') ?>"
                                                title="Delete this entry">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="mt-4">
                        <?= $pager->links('default', 'bootstrap5_full') ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
