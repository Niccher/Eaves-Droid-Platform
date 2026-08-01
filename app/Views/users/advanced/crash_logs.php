<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-bug text-secondary mr-2"></i>Crash Logs</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Application crashes, exceptions, stack traces, and ANR events</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Crash Entries <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-box mr-1"></i>Package</th>
                            <th><i class="fas fa-tag mr-1"></i>Type</th>
                            <th><i class="fas fa-code mr-1"></i>Exception</th>
                            <th><i class="fas fa-microchip mr-1"></i>PID</th>
                            <th><i class="fas fa-clock mr-1"></i>Crash Time</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-bug fa-3x text-muted mb-3"></i><h4>No crash data</h4><p class="text-muted">Crashes will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($r['package_name'] ?? '—') ?></strong>
                                    <?php if (!empty($r['process_name'])): ?><br><small class="text-muted"><?= htmlspecialchars($r['process_name']) ?></small><?php endif; ?>
                                </td>
                                <td>
                                    <?php $type = strtolower($r['crash_type'] ?? ''); ?>
                                    <span class="badge badge-<?= str_contains($type, 'anr') ? 'danger' : (str_contains($type, 'native') ? 'warning' : 'info') ?> p-2">
                                        <?= htmlspecialchars($r['crash_type'] ?? '—') ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-danger"><?= htmlspecialchars($r['exception_class'] ?? '—') ?></strong>
                                    <?php if (!empty($r['exception_message'])): ?><br><small class="text-muted"><?= htmlspecialchars(mb_substr($r['exception_message'], 0, 60)) ?></small><?php endif; ?>
                                </td>
                                <td class="text-center"><span class="badge badge-secondary p-2"><?= (int)($r['pid'] ?? 0) ?></span></td>
                                <td><?= !empty($r['crash_time']) ? format_timestamp_display((int)$r['crash_time']) : '<span class="text-muted">—</span>' ?></td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/software/crash_logs/delete') ?>"
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
