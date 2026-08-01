<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-cogs text-secondary mr-2"></i>Running Processes</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Detailed process list with memory, CPU, and security context</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Processes <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-microchip mr-1"></i>PID</th>
                            <th><i class="fas fa-cube mr-1"></i>Name</th>
                            <th><i class="fas fa-tachometer-alt mr-1"></i>State</th>
                            <th><i class="fas fa-memory mr-1"></i>Memory</th>
                            <th><i class="fas fa-percent mr-1"></i>CPU</th>
                            <th><i class="fas fa-users mr-1"></i>Threads</th>
                            <th><i class="fas fa-clock mr-1"></i>Start</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-cogs fa-3x text-muted mb-3"></i><h4>No process data</h4><p class="text-muted">Processes will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <tr>
                                <td><span class="badge badge-secondary p-2"><?= (int)($r['pid'] ?? 0) ?></span></td>
                                <td>
                                    <strong><?= htmlspecialchars($r['name'] ?? '—') ?></strong>
                                    <?php if (!empty($r['cmdline']) && $r['cmdline'] !== $r['name']): ?><br><small class="text-muted"><?= htmlspecialchars(mb_substr($r['cmdline'], 0, 50)) ?></small><?php endif; ?>
                                </td>
                                <td><span class="badge badge-info p-2"><?= htmlspecialchars($r['state'] ?? '—') ?></span></td>
                                <td>
                                    <?php if (isset($r['pss_kb'])): ?>
                                        <span class="badge badge-primary p-2"><?= number_format((float)$r['pss_kb'] / 1024, 1) ?> MB</span>
                                    <?php elseif (isset($r['rss_kb'])): ?>
                                        <span class="badge badge-primary p-2"><?= number_format((float)$r['rss_kb'] / 1024, 1) ?> MB</span>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($r['cpu_percent'])): ?>
                                        <span class="badge badge-warning p-2"><?= round((float)$r['cpu_percent'], 1) ?>%</span>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                                <td class="text-center"><span class="badge badge-secondary p-2"><?= (int)($r['threads'] ?? 0) ?></span></td>
                                <td><?= !empty($r['start_time']) ? format_timestamp_display((int)$r['start_time']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/software/running_processes/delete') ?>"
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
