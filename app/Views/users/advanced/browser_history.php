<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-globe text-secondary mr-2"></i>Browser History</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Browsing history, bookmarks, and search activity</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>History <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-globe mr-1"></i>Title</th>
                            <th><i class="fas fa-link mr-1"></i>URL</th>
                            <th><i class="fas fa-compass mr-1"></i>Domain</th>
                            <th><i class="fas fa-browser mr-1"></i>Browser</th>
                            <th><i class="fas fa-eye mr-1"></i>Visits</th>
                            <th><i class="fas fa-clock mr-1"></i>Last Visit</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-globe fa-3x text-muted mb-3"></i><h4>No browser history</h4><p class="text-muted">History will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($r['title'] ?? '—') ?></strong>
                                    <?php if (!empty($r['is_bookmark'])): ?><span class="badge badge-warning ml-1 p-2"><i class="fas fa-bookmark mr-1"></i>Bookmark</span><?php endif; ?>
                                </td>
                                <td><code class="small"><?= htmlspecialchars(mb_substr($r['url'] ?? '—', 0, 60)) ?></code></td>
                                <td><span class="badge badge-info p-2"><?= htmlspecialchars($r['domain'] ?? '—') ?></span></td>
                                <td><small><?= htmlspecialchars($r['browser_package'] ?? '—') ?></small></td>
                                <td class="text-center"><span class="badge badge-primary p-2"><?= (int)($r['visit_count'] ?? 0) ?></span></td>
                                <td><?= !empty($r['last_visit_time']) ? format_timestamp_display((int)$r['last_visit_time']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/software/browser_history/delete') ?>"
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
