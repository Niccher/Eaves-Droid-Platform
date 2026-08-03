<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-microchip text-secondary mr-2"></i>Running Processes</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">App processes, services and 24h usage stats</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-list mr-2"></i>Process Snapshots <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-cogs text-primary mr-1"></i> Snapshot</th>
                            <th><i class="fas fa-microchip text-info mr-1"></i> Processes</th>
                            <th><i class="fas fa-cogs text-warning mr-1"></i> Services</th>
                            <th><i class="fas fa-clock text-muted mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="6" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-microchip fa-3x text-muted mb-3"></i><h4>No process data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                                $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                                $details = $r['process_details'] ?? [];
                                $services = $r['services'] ?? [];
                                $procCount = count($details);
                                $svcCount = count($services);
                                $rid = $r['id'] ?? 0;
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#proc-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td>
                                    <div class="font-weight-bold">Snapshot</div>
                                    <small class="text-muted">Device: <?= htmlspecialchars(mb_substr($r['device_id'] ?? '—', 0, 12)) ?>…</small>
                                </td>
                                <td><span class="badge badge-info"><i class="fas fa-microchip mr-1"></i> <?= $procCount ?> procs</span></td>
                                <td><span class="badge badge-secondary"><i class="fas fa-cogs mr-1"></i> <?= $svcCount ?> svcs</span></td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $rid ?>"
                                            data-url="<?= base_url('advanced/hardware/processes/delete') ?>"
                                            title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="6" class="p-0 border-0">
                                    <div id="proc-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-microchip mr-2"></i>Processes (<?= $procCount ?>)</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-striped mb-0 small">
                                                            <thead class="text-muted">
                                                            <tr>
                                                                <th>PID</th><th>Name</th><th>UID</th><th>Importance</th><th>Packages</th><th>LRU</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <?php foreach ($details as $d): ?>
                                                                <?php $impMap = [100=>'Foreg',200=>'Visible',300=>'Service',400=>'Bg',500=>'Empty',600=>'Cached']; ?>
                                                                <tr>
                                                                    <td class="pl-2"><code><?= $d['pid'] ?? '—' ?></code></td>
                                                                    <td><?= htmlspecialchars($d['process_name'] ?? '—') ?></td>
                                                                    <td><code><?= $d['uid'] ?? '—' ?></code></td>
                                                                    <td><span class="badge badge-<?= ($d['importance'] ?? 0) <= 200 ? 'danger' : 'secondary' ?>"><?= $impMap[$d['importance'] ?? 0] ?? 'Unk' ?></span></td>
                                                                    <td><?php $pkgs = json_decode($d['pkg_list_json'] ?? '[]', true); if (!empty($pkgs)): ?><span class="badge badge-success" title="<?= htmlspecialchars(implode(', ', $pkgs)) ?>"><?= count($pkgs) ?> pkg(s)</span><?php else: ?>—<?php endif; ?></td>
                                                                    <td><?= $d['lru'] ?? '—' ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-cogs mr-2"></i>Services (<?= $svcCount ?>)</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-striped mb-0 small">
                                                            <thead class="text-muted">
                                                            <tr>
                                                                <th>PID</th><th>Process</th><th>Service</th><th>Package</th><th>Active Since</th><th>Crash</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <?php foreach ($services as $s): ?>
                                                                <tr>
                                                                    <td class="pl-2"><code><?= $s['pid'] ?? '—' ?></code></td>
                                                                    <td><?= htmlspecialchars($s['process'] ?? '—') ?></td>
                                                                    <td><?= htmlspecialchars($s['service_class'] ?? '—') ?></td>
                                                                    <td><?= htmlspecialchars($s['service_package'] ?? '—') ?></td>
                                                                    <td><?= !empty($s['active_since']) ? format_timestamp_display((int)($s['active_since'] / 1000)) : '—' ?></td>
                                                                    <td><?= $s['crash_count'] ?? '0' ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <h6 class="mt-3"><i class="fas fa-info-circle mr-2"></i>Snapshot Info</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Extracted At</th><td><?= $ts ?></td></tr>
                                                        <tr><th>Entry ID</th><td><code><?= $rid ?></code></td></tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div></div></div></section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>