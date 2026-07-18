<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-shield-alt text-secondary mr-2"></i>Security Audit</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">VPN/Proxy status, open ports and user-installed CA certificates</p>
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
                            <th><i class="fas fa-shield-alt mr-1"></i>VPN</th>
                            <th><i class="fas fa-globe mr-1"></i>Proxy</th>
                            <th><i class="fas fa-certificate text-warning mr-1"></i>CA Certs</th>
                            <th><i class="fas fa-door-open mr-1"></i>Open Ports</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="5" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-shield-alt fa-3x text-muted mb-3"></i><h4>No security audit data</h4><p class="text-muted">Audit snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php $caCerts = $r['user_ca_certs'] ?? []; $ports = $r['open_ports'] ?? []; ?>
                            <tr>
                                <td>
                                    <span class="badge badge-<?= !empty($r['vpn_active']) ? 'success' : 'secondary' ?> p-2" style="min-width:60px;">
                                        <i class="fas fa-<?= !empty($r['vpn_active']) ? 'check' : 'times' ?> mr-1"></i>
                                        <?= !empty($r['vpn_active']) ? 'Active' : 'Off' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= !empty($r['proxy_active']) ? 'warning' : 'secondary' ?> p-2" style="min-width:60px;">
                                        <i class="fas fa-<?= !empty($r['proxy_active']) ? 'check' : 'times' ?> mr-1"></i>
                                        <?= !empty($r['proxy_active']) ? 'Active' : 'Off' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($caCerts)): ?>
                                        <span class="badge badge-warning" data-toggle="tooltip"
                                              title="<?= htmlspecialchars(implode("\n", array_map(function($c) {
                                                  return is_string($c) ? $c : (is_array($c) ? ($c['subject'] ?? json_encode($c)) : '—');
                                              }, $caCerts))) ?>">
                                            <i class="fas fa-certificate mr-1"></i><?= count($caCerts) ?> cert<?= count($caCerts) !== 1 ? 's' : '' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($ports)): ?>
                                        <?php foreach ($ports as $p): $port = is_array($p) ? ($p['port'] ?? $p['localPort'] ?? '?') : $p; ?>
                                            <span class="badge badge-danger mr-1" title="<?= htmlspecialchars(is_array($p) ? ($p['service'] ?? '') : '') ?>">
                                                <i class="fas fa-door-open mr-1"></i>Port <?= htmlspecialchars($port) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $r['ts_display'] ?? '<span class="text-muted">—</span>' ?></td>
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
