<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-battery-half text-secondary mr-2"></i>Doze &amp; Standby</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Doze state, battery saver, adaptive battery, and standby buckets</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Snapshots <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-power-off mr-1"></i>Doze State</th>
                            <th><i class="fas fa-battery-half mr-1"></i>Battery Saver</th>
                            <th><i class="fas fa-leaf mr-1"></i>Adaptive Battery</th>
                            <th><i class="fas fa-tachometer-alt mr-1"></i>Standby Bucket</th>
                            <th><i class="fas fa-box mr-1"></i>Apps</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-battery-half fa-3x text-muted mb-3"></i><h4>No doze/standby data</h4><p class="text-muted">Snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php $apps = $r['apps'] ?? []; ?>
                            <tr>
                                <td>
                                    <?= !empty($r['is_in_deep_doze']) ? '<span class="badge badge-danger p-2">Deep Doze</span>' : (!empty($r['is_in_light_doze']) ? '<span class="badge badge-warning p-2">Light Doze</span>' : '<span class="badge badge-success p-2">Active</span>') ?>
                                </td>
                                <td class="text-center">
                                    <?= !empty($r['battery_saver_enabled']) ? '<span class="badge badge-success p-2">On</span>' : '<span class="badge badge-secondary p-2">Off</span>' ?>
                                </td>
                                <td class="text-center">
                                    <?= !empty($r['adaptive_battery_enabled']) ? '<span class="badge badge-info p-2">Enabled</span>' : '<span class="badge badge-secondary p-2">Disabled</span>' ?>
                                </td>
                                <td>
                                    <?php if (!empty($r['device_standby_bucket'])): ?>
                                        <span class="badge badge-<?= in_array(strtolower($r['device_standby_bucket']), ['active', 'working_set']) ? 'success' : 'warning' ?> p-2">
                                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $r['device_standby_bucket']))) ?>
                                        </span>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                                <td class="text-center"><span class="badge badge-secondary p-2"><?= count($apps) ?></span></td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/software/doze_standby/delete') ?>"
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
