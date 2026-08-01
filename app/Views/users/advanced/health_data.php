<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-heartbeat text-secondary mr-2"></i>Health Data</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Steps, heart rate, sleep, workouts, and health records</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Health Records <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-tag mr-1"></i>Type</th>
                            <th><i class="fas fa-hashtag mr-1"></i>Value</th>
                            <th><i class="fas fa-ruler mr-1"></i>Unit</th>
                            <th><i class="fas fa-mobile-alt mr-1"></i>Source</th>
                            <th><i class="fas fa-calendar mr-1"></i>End Time</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-heartbeat fa-3x text-muted mb-3"></i><h4>No health data</h4><p class="text-muted">Health records will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <tr>
                                <td><span class="badge badge-info p-2"><?= htmlspecialchars($r['data_type'] ?? '—') ?></span></td>
                                <td>
                                    <strong><?= htmlspecialchars($r['value'] ?? ($r['step_count'] ?? ($r['heart_rate_bpm'] ?? '—'))) ?></strong>
                                    <?php if (isset($r['distance_meters'])): ?><small class="text-muted ml-1"><?= (float)$r['distance_meters'] ?> m</small><?php endif; ?>
                                </td>
                                <td><code class="small"><?= htmlspecialchars($r['unit'] ?? '—') ?></code></td>
                                <td>
                                    <?php if (!empty($r['data_source_name'])): ?>
                                        <small><?= htmlspecialchars($r['data_source_name']) ?></small>
                                    <?php elseif (!empty($r['data_source_package'])): ?>
                                        <small><?= htmlspecialchars($r['data_source_package']) ?></small>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                                <td><?= !empty($r['end_time']) ? format_timestamp_display((int)$r['end_time']) : '<span class="text-muted">—</span>' ?></td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/software/health_data/delete') ?>"
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
