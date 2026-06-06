<?php
/** @var array $rows @var int $total @var int $total_notifications @var object $pager @var string $nav_urls */
/** @var bool $detail_mode @var string|null $group_key @var array|null $summary @var string|null $back_url */
if (!empty($detail_mode)) {
    $appName = $summary['display_name'] ?? $summary['app_name'] ?? $group_key;
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-8 d-flex align-items-center">
                    <a href="<?= esc($back_url) ?>" class="btn btn-primary mr-3" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><i class="fas fa-arrow-left mr-1"></i> Back</a>
                    <div>
                        <h1 class="h2 mb-0"><i class="fas fa-bell text-warning mr-2"></i><?= htmlspecialchars($appName) ?></h1>
                        <?php if (!empty($summary['package_name']) && $summary['package_name'] !== $group_key): ?>
                            <p class="text-muted mt-1 mb-0"><code><?= htmlspecialchars($summary['package_name']) ?></code></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4 text-right">
                    <?= $nav_urls ?>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-bell"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Notifications</span>
                            <span class="info-box-number"><?= (int) ($summary['notification_count'] ?? $total) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-tv"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Screen Alerts</span>
                            <span class="info-box-number"><?= (int) ($summary['screen_count'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-secondary">
                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Last Arrived</span>
                            <span class="info-box-number" style="font-size:1rem;"><?= esc($summary['latest_ts_rel'] ?? '—') ?></span>
                            <span class="progress-description"><?= esc($summary['latest_ts_abs'] ?? '—') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card card-warning shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list mr-2"></i>All Notifications <small class="text-muted ml-2"><?= count($rows) ?> on this page</small></h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th><i class="fas fa-clock mr-1"></i>Arrived</th>
                                        <th><i class="fas fa-user mr-1"></i>Sender</th>
                                        <th><i class="fas fa-heading mr-1"></i>Title</th>
                                        <th><i class="fas fa-comment-alt mr-1"></i>Content</th>
                                        <th><i class="fas fa-tv mr-1"></i>Screen</th>
                                        <th><i class="fas fa-tag mr-1"></i>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($rows)): ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No notifications for this app.</td></tr>
                                    <?php else: foreach ($rows as $r): ?>
                                        <?php
                                            $act   = strtoupper($r['action'] ?? '');
                                            $actCol = $act === 'POSTED' ? 'success' : 'secondary';
                                            $isScreen = !empty($r['is_screen_notification']);
                                            $rowClass = $isScreen ? 'table-warning' : '';
                                        ?>
                                        <tr class="<?= $rowClass ?>">
                                            <td>
                                                <small class="d-block font-weight-bold"><?= esc($r['ts_rel'] ?? '—') ?></small>
                                                <small class="text-muted"><?= esc($r['ts_abs'] ?? '—') ?></small>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($r['sender'] ?? '—') ?></small>
                                                <?php if (!empty($r['sub_text'])): ?>
                                                    <small class="text-muted d-block"><?= htmlspecialchars(mb_strimwidth($r['sub_text'], 0, 40, '…')) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="font-weight-bold"><?= htmlspecialchars($r['title'] ?? '—') ?></td>
                                            <td><small class="text-muted"><?= htmlspecialchars(mb_strimwidth($r['text'] ?? '—', 0, 120, '…')) ?></small></td>
                                            <td>
                                                <?php if ($isScreen): ?>
                                                    <span class="badge badge-warning"><i class="fas fa-tv mr-1"></i>Screen</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge badge-<?= $actCol ?>"><?= $act ?></span></td>
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
<?php
} else {
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-bell text-warning mr-2"></i>Notifications</h1>
                        <span class="badge badge-warning border p-2"><i class="fas fa-cube mr-1"></i>Sources: <b><?= $total ?? 0 ?></b></span>
                        <?php if (!empty($total_notifications)): ?>
                            <span class="badge badge-secondary border p-2 ml-2"><i class="fas fa-database mr-1"></i>Total alerts: <b><?= $total_notifications ?></b></span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted mt-1 mb-0">Notification log grouped by app name (sender or package when unnamed)</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-warning shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bell mr-2"></i>Notification Log <small class="text-muted ml-2"><?= count($rows) ?> sources on this page</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-cube mr-1"></i>Source</th>
                            <th><i class="fas fa-hashtag mr-1"></i>Count</th>
                            <th><i class="fas fa-tv mr-1"></i>Screen Alerts</th>
                            <th><i class="fas fa-clock mr-1"></i>Last Activity</th>
                            <th><i class="fas fa-heading mr-1"></i>Latest Preview</th>
                            <th class="text-center"><i class="fas fa-eye mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="6" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-bell-slash fa-3x text-muted mb-3"></i><h4>No notifications</h4><p class="text-muted">Notifications will appear here once captured</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                                $tsAbs    = $r['latest_ts_abs'] ?? '—';
                                $tsRel    = $r['latest_ts_rel'] ?? '—';
                                $groupKey = $r['group_key'] ?? '';
                                $pkgEnc   = $r['group_url_enc'] ?? '';
                                $subtitle = $r['package_name'] ?? $r['sender'] ?? '';
                                $preview  = $r['latest_title_short'] ?? '—';
                            ?>
                            <tr>
                                <td>
                                    <?php $displayTitle = !empty($r['app_name']) ? $r['app_name'] : $groupKey; ?>
                                    <div class="font-weight-bold"><i class="fas fa-cube mr-1 text-warning"></i><?= htmlspecialchars($displayTitle ?: '—') ?></div>
                                    <?php if ($groupKey !== '' && $groupKey !== $displayTitle): ?>
                                        <small class="text-muted d-block"><?= htmlspecialchars($groupKey) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-warning"><?= (int) ($r['notification_count'] ?? 0) ?></span></td>
                                <td><span class="badge badge-info"><?= (int) ($r['screen_count'] ?? 0) ?></span></td>
                                <td>
                                    <small class="d-block font-weight-bold"><?= $tsRel ?></small>
                                    <small class="text-muted"><?= $tsAbs ?></small>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($preview) ?></small></td>
                                <td class="text-center">
                                    <a href="<?= base_url('advanced/notifications/' . $pkgEnc) ?>" class="btn btn-sm btn-outline-warning" title="View all notifications">
                                        <i class="fas fa-eye"></i>
                                    </a>
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
<?php
}
?>
<?php include __DIR__ . '/_adv_style.php'; ?>
