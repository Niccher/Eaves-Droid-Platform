<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
// Separate the latest lockscreen configuration metrics from the event rows
$latestConfig = null;
foreach ($rows as $r) {
    if (isset($r['is_secure']) && $r['is_secure'] !== null) {
        $latestConfig = $r;
        break;
    }
}
?>

<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3">
                            <i class="fas fa-lock text-primary mr-2"></i>Keyguard &amp; Security
                        </h1>
                        <span class="badge badge-primary border p-2">
                            <i class="fas fa-database mr-1"></i>Total Records: <b><?= (int)$total ?></b>
                        </span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Lockscreen event audits, system authentication methods, and credential encryption indicators.</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <!-- System Security Settings Card -->
            <div class="card card-outline card-primary shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title font-weight-bold mb-0 text-primary"><i class="fas fa-shield-alt mr-2"></i>Device Credential Status</h5>
                </div>
                <div class="card-body bg-light">
                    <?php if (!$latestConfig): ?>
                        <span class="text-muted small">No lockscreen settings cataloged yet.</span>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="p-3 bg-white rounded border height-100 text-center">
                                    <span class="text-muted small d-block mb-2 font-weight-bold text-uppercase"><i class="fas fa-shield-alt mr-1"></i>Secure Lockscreen</span>
                                    <span class="badge badge-<?= !empty($latestConfig['is_secure']) ? 'success' : 'danger' ?> px-3 py-2 font-weight-bold">
                                        <?= !empty($latestConfig['is_secure']) ? 'SECURE' : 'INSECURE' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="p-3 bg-white rounded border height-100 text-center">
                                    <span class="text-muted small d-block mb-2 font-weight-bold text-uppercase"><i class="fas fa-hdd mr-1"></i>Storage Encryption</span>
                                    <span class="badge badge-<?= str_contains(strtolower($latestConfig['storage_encryption_status'] ?? ''), 'encrypt') ? 'success' : 'warning' ?> px-3 py-2 font-weight-bold">
                                        <?= esc(strtoupper($latestConfig['storage_encryption_status'] ?? 'UNKNOWN')) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="p-3 bg-white rounded border height-100 text-center">
                                    <span class="text-muted small d-block mb-2 font-weight-bold text-uppercase"><i class="fas fa-bell mr-1"></i>Lockscreen Notifs</span>
                                    <span class="badge badge-secondary px-3 py-2 font-weight-bold"><?= esc($latestConfig['notifications_on_lockscreen'] ?: 'Enabled') ?></span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="p-3 bg-white rounded border height-100 text-center">
                                    <span class="text-muted small d-block mb-2 font-weight-bold text-uppercase"><i class="fas fa-eye-slash mr-1"></i>Sensitive Content</span>
                                    <span class="badge badge-<?= !empty($latestConfig['sensitive_notifications_hidden']) ? 'success' : 'warning' ?> px-3 py-2 font-weight-bold">
                                        <?= !empty($latestConfig['sensitive_notifications_hidden']) ? 'HIDDEN' : 'VISIBLE' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lockscreen Event Audits -->
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header">
                    <h5 class="card-title font-weight-bold mb-0"><i class="fas fa-history mr-2"></i>Event Timeline</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($rows)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                            <h4 class="text-secondary">No keyguard events logged</h4>
                            <p class="text-muted">Transition events will display here once captured.</p>
                        </div>
                    <?php else: ?>
                        <div class="timeline timeline-inverse">
                            <?php 
                            $lastDateLabel = '';
                            foreach ($rows as $r):
                                $isUnlock = strtolower($r['event_type'] ?? '') == 'unlock';
                                $success = !empty($r['success']);
                                $timestampVal = !empty($r['timestamp']) ? (int)$r['timestamp'] : ($r['extracted_at'] ? (int)$r['extracted_at'] : null);
                                $dateLabel = $timestampVal ? date('d M. Y', $timestampVal / 1000) : 'Unknown Date';
                                $timeLabel = $timestampVal ? date('H:i:s', $timestampVal / 1000) : '—';
                                $failed = (int)($r['failed_attempts'] ?? 0);

                                if ($dateLabel !== $lastDateLabel):
                                    $lastDateLabel = $dateLabel;
                            ?>
                                    <div class="time-label">
                                        <span class="bg-primary px-3"><?= $dateLabel ?></span>
                                    </div>
                            <?php endif; ?>

                                <div>
                                    <?php if ($isUnlock): ?>
                                        <i class="fas <?= $success ? 'fa-unlock bg-success' : 'fa-times-circle bg-danger' ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-lock bg-secondary"></i>
                                    <?php endif; ?>

                                    <div class="timeline-item shadow-none border">
                                        <span class="time text-muted"><i class="fas fa-clock mr-1"></i><?= $timeLabel ?></span>
                                        <h3 class="timeline-header font-weight-bold">
                                            <?php if ($isUnlock): ?>
                                                <span class="text-<?= $success ? 'success' : 'danger' ?>">
                                                    <?= $success ? 'Device Unlocked' : 'Failed Unlock Attempt' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-secondary">Device Locked</span>
                                            <?php endif; ?>
                                        </h3>
                                        <div class="timeline-body py-2">
                                            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                                <?php if (!empty($r['method'])): ?>
                                                    <span class="badge badge-light border"><i class="fas fa-fingerprint mr-1 text-primary"></i>Method: <?= esc($r['method']) ?></span>
                                                <?php endif; ?>
                                                <?php if ($failed > 0): ?>
                                                    <span class="badge badge-danger">Failed Attempts: <?= $failed ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($r['strong_auth_required_reason'])): ?>
                                                    <span class="text-danger small"><i class="fas fa-exclamation-triangle mr-1"></i>Reason: <?= esc($r['strong_auth_required_reason']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="timeline-footer py-2 text-right">
                                            <button class="btn btn-xs btn-outline-danger delete-row"
                                                    data-id="<?= esc($r['id']) ?>"
                                                    data-url="<?= base_url('advanced/software/keyguard/delete/' . esc($r['id'])) ?>">
                                                <i class="fas fa-trash mr-1"></i>Delete Record
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div>
                                <i class="far fa-clock bg-gray"></i>
                            </div>
                        </div>

                        <div class="mt-4">
                            <?= $pager->links('default', 'bootstrap5_full') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </section>
</div>

<?= view('users/advanced/_adv_delete_script') ?>
