<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-calendar-alt text-secondary mr-2"></i>Calendar Events</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">All calendar events and appointments found on device</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>


            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Events <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-heading mr-1"></i>Title</th>
                            <th><i class="fas fa-align-left mr-1"></i>Description</th>
                            <th><i class="fas fa-map-pin mr-1"></i>Location</th>
                            <th><i class="fas fa-play mr-1"></i>Start</th>
                            <th><i class="fas fa-stop mr-1"></i>End</th>
                            <th><i class="fas fa-user-tie mr-1"></i>Organizer</th>
                            <th><i class="fas fa-sun mr-1"></i>All Day</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-calendar-times fa-3x text-muted mb-3"></i><h4>No calendar events</h4><p class="text-muted">Events will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                                $start = !empty($r['start_time']) ? format_timestamp_display((int)$r['start_time']) : '—';
                                $end   = !empty($r['end_time'])   ? format_timestamp_display((int)$r['end_time'])   : '—';
                            ?>
                            <tr>
                                <td><div class="font-weight-bold"><?= htmlspecialchars($r['title'] ?? '—') ?></div></td>
                                <td><small class="text-muted"><?= htmlspecialchars(mb_strimwidth($r['description'] ?? '—', 0, 60, '…')) ?></small></td>
                                <td>
                                    <?php if ($r['location']): ?>
                                        <a href="https://www.google.com/maps/search/<?= urlencode($r['location']) ?>" target="_blank" class="text-sm text-primary">
                                            <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars(mb_strimwidth($r['location'], 0, 25, '…')) ?>
                                        </a>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                                <td><?= $start ?></td>
                                <td><?= $end ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($r['organizer'] ?? '—') ?></small></td>
                                <td>
                                    <span class="badge badge-<?= $r['all_day'] ? 'warning' : 'secondary' ?>">
                                        <?= $r['all_day'] ? 'All Day' : 'Timed' ?>
                                    </span>
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
