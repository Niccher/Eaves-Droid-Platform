<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-lock text-secondary mr-2"></i>Keyguard Events</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Unlock/lock events, auth methods, and lock screen settings</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Events <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-tag mr-1"></i>Event</th>
                            <th><i class="fas fa-key mr-1"></i>Method</th>
                            <th><i class="fas fa-check-circle mr-1"></i>Success</th>
                            <th><i class="fas fa-exclamation-triangle mr-1"></i>Failed Attempts</th>
                            <th><i class="fas fa-shield-alt mr-1"></i>Secure</th>
                            <th><i class="fas fa-clock mr-1"></i>Timestamp</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-lock fa-3x text-muted mb-3"></i><h4>No keyguard data</h4><p class="text-muted">Events will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <tr>
                                <td>
                                    <?php $etype = strtolower($r['event_type'] ?? ''); ?>
                                    <span class="badge badge-<?= str_contains($etype, 'lock') && !str_contains($etype, 'unlock') ? 'danger' : 'success' ?> p-2">
                                        <i class="fas fa-<?= str_contains($etype, 'lock') && !str_contains($etype, 'unlock') ? 'lock' : 'lock-open' ?> mr-1"></i>
                                        <?= htmlspecialchars($r['event_type'] ?? '—') ?>
                                    </span>
                                </td>
                                <td><span class="badge badge-info p-2"><?= htmlspecialchars($r['method'] ?? '—') ?></span></td>
                                <td class="text-center">
                                    <?= isset($r['success']) && $r['success'] !== null ? ($r['success'] ? '<span class="badge badge-success p-2">Yes</span>' : '<span class="badge badge-danger p-2">No</span>') : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="text-center">
                                    <?php if (isset($r['failed_attempts'])): ?>
                                        <span class="badge badge-<?= (int)$r['failed_attempts'] > 0 ? 'warning' : 'secondary' ?> p-2"><?= (int)$r['failed_attempts'] ?></span>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?= !empty($r['is_secure']) ? '<span class="badge badge-success p-2">Yes</span>' : '<span class="badge badge-secondary p-2">No</span>' ?>
                                </td>
                                <td><?= !empty($r['timestamp']) ? format_timestamp_display((int)$r['timestamp']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/software/keyguard/delete') ?>"
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
