<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
helper('coalesce');
// Coalesce by service_id (since a device can have multiple services installed)
foreach ($rows as &$r) {
    $r['access_unique_key'] = ($r['device_id'] ?? 'default') . '_' . ($r['service_id'] ?? 'default');
}
unset($r);

$rows = coalesce_snapshots(
    $rows,
    'access_unique_key',
    ['service_id', 'package_name', 'description', 'feedback_type', 'flags', 'notification_timeout', 'settings_activity_name', 'can_retrieve_window_content', 'capabilities']
);
?>

<style>
.access-card { border-radius: 12px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #eef2f5; overflow: hidden; margin-bottom: 24px; transition: transform 0.2s; }
.access-card:hover { transform: translateY(-2px); }
.access-header { padding: 18px 20px; border-bottom: 1px solid #edf2f7; }
.danger-shield { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; border-radius: 8px; padding: 12px; }
.safe-shield { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 8px; padding: 12px; }
.access-pill { display: inline-block; background: #f1f5f9; border-radius: 6px; padding: 3px 8px; font-size: 11px; color: #475569; margin: 2px; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <h1 class="h2 mb-0"><i class="fas fa-universal-access text-primary mr-2"></i>Accessibility Services</h1>
                    <p class="text-muted mt-1 mb-0">Installed assistive utilities, screen readers, and permission scopes</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>

            <!-- Callout -->
            <div class="callout callout-warning shadow-sm p-3 mb-4" style="border-left:5px solid #ffc107; background:#fffdf5;">
                <h5 class="font-weight-bold text-warning"><i class="fas fa-exclamation-triangle mr-2"></i>Security Alert: Screen Scraping Risk</h5>
                <p class="text-secondary mb-0" style="font-size:14px;">Accessibility permissions permit apps to inspect window nodes, capture keystrokes, and simulate gestures. Trojans frequently exploit these hooks to hijack inputs or bypass MFA.</p>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (empty($rows)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-universal-access fa-3x text-muted mb-3"></i>
                    <h4 class="text-secondary">No Accessibility Services Cataloged</h4>
                    <p class="text-muted">Data will appear once extracted.</p>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($rows as $r):
                        $canScrape = !empty($r['can_retrieve_window_content']);
                        $caps = $r['capabilities'] ?? [];
                        if (is_string($caps)) $caps = json_decode($caps, true) ?: [];
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="access-card">
                                <div class="access-header d-flex align-items-start justify-content-between">
                                    <div class="text-truncate" style="max-width: 80%;">
                                        <h5 class="font-weight-bold text-dark text-truncate mb-1" title="<?= esc($r['service_id']) ?>">
                                            <?= esc(basename(str_replace('.', '/', $r['service_id']))) ?>
                                        </h5>
                                        <code class="text-xs text-muted text-truncate d-block" style="font-size: 11px;"><?= esc($r['package_name']) ?></code>
                                    </div>
                                    <i class="fas fa-universal-access fa-lg text-primary"></i>
                                </div>
                                <div class="p-3">
                                    <p class="text-secondary small mb-3" style="min-height: 40px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                        <?= esc($r['description'] ?: 'No description provided by package.') ?>
                                    </p>

                                    <!-- Security Shield -->
                                    <div class="mb-3 <?= $canScrape ? 'danger-shield' : 'safe-shield' ?>">
                                        <div class="d-flex align-items-center">
                                            <i class="fas <?= $canScrape ? 'fa-shield-alt' : 'fa-check-circle' ?> mr-2"></i>
                                            <div class="font-weight-bold small">
                                                <?= $canScrape ? 'RETRIEVES WINDOW CONTENT (RISK)' : 'No window content scraping' ?>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="font-weight-bold text-secondary mb-2" style="font-size: 11px; text-transform: uppercase;">Capabilities (<?= count($caps) ?>)</h6>
                                    <div class="mb-3" style="min-height: 50px;">
                                        <?php if (empty($caps)): ?>
                                            <span class="text-muted small">No special capabilities listed.</span>
                                        <?php else: foreach ($caps as $cap): ?>
                                            <span class="access-pill"><?= esc(str_replace('CAPABILITY_CAN_', '', $cap)) ?></span>
                                        <?php endforeach; endif; ?>
                                    </div>

                                    <div class="border-top pt-2 mt-2 small text-muted">
                                        <div class="d-flex justify-content-between">
                                            <span>Feedback Type: <code><?= (int)($r['feedback_type'] ?? 0) ?></code></span>
                                            <span>Timeout: <code><?= (int)($r['notification_timeout'] ?? 0) ?>ms</code></span>
                                        </div>
                                        <?php if ($r['settings_activity_name']): ?>
                                            <div class="text-truncate mt-1">
                                                <i class="fas fa-cog mr-1"></i>Settings: <code style="font-size:10px;"><?= esc($r['settings_activity_name']) ?></code>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
