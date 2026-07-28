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
                    <p class="text-muted mt-1 mb-0">VPN/Proxy status, open ports, user & system CA certificates, VPN config, device admin apps, DNS configuration</p>
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
                            <th class="text-center"><i class="fas fa-lock mr-1"></i>VPN Config</th>
                            <th class="text-center"><i class="fas fa-certificate text-warning mr-1"></i>CA Certs (User)</th>
                            <th class="text-center"><i class="fas fa-certificate text-danger mr-1"></i>CA Certs (System)</th>
                            <th class="text-center"><i class="fas fa-door-open mr-1"></i>Open Ports</th>
                            <th class="text-center"><i class="fas fa-user-shield mr-1"></i>Device Admin Apps</th>
                            <th class="text-center"><i class="fas fa-dns mr-1"></i>DNS Config</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="10" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-shield-alt fa-3x text-muted mb-3"></i><h4>No security audit data</h4><p class="text-muted">Audit snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r):
                            $userCaCerts = $r['user_ca_certs'] ?? [];
                            $systemCaCerts = $r['system_ca_certs'] ?? [];
                            $ports = $r['open_ports'] ?? [];
                            $vpnConfig = $r['vpn_config'] ?? [];
                            $deviceAdmins = $r['device_admin_apps'] ?? [];
                            $dnsConfig = $r['dns_config'] ?? [];
                            $rid = $r['id'] ?? 0;
                        ?>
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
                                <td class="text-center">
                                    <?php if (!empty($vpnConfig)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-vpn-<?= $rid ?>"
                                           class="badge badge-info p-2" title="Click to view VPN details">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($userCaCerts)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-ca-user-<?= $rid ?>"
                                           class="badge badge-warning p-2" title="Click to view certificates">
                                            <i class="fas fa-certificate mr-1"></i><?= count($userCaCerts) ?> cert<?= count($userCaCerts) !== 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($systemCaCerts)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-ca-system-<?= $rid ?>"
                                           class="badge badge-danger p-2" title="Click to view certificates">
                                            <i class="fas fa-certificate mr-1"></i><?= count($systemCaCerts) ?> cert<?= count($systemCaCerts) !== 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($ports)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-ports-<?= $rid ?>"
                                           class="badge badge-danger p-2" title="Click to view open ports">
                                            <i class="fas fa-eye mr-1"></i><?= count($ports) ?> port<?= count($ports) !== 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($deviceAdmins)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-admin-<?= $rid ?>"
                                           class="badge badge-secondary p-2" title="Click to view device admin apps">
                                            <i class="fas fa-eye mr-1"></i><?= count($deviceAdmins) ?> app<?= count($deviceAdmins) !== 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($dnsConfig)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-dns-<?= $rid ?>"
                                           class="badge badge-primary p-2" title="Click to view DNS configuration">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $r['ts_display'] ?? '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $rid ?>"
                                            data-url="<?= base_url('advanced/security_audit/delete') ?>"
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

<?php if (!empty($rows)): foreach ($rows as $r):
    $userCaCerts = $r['user_ca_certs'] ?? [];
    $systemCaCerts = $r['system_ca_certs'] ?? [];
    $ports = $r['open_ports'] ?? [];
    $vpnConfig = $r['vpn_config'] ?? [];
    $deviceAdmins = $r['device_admin_apps'] ?? [];
    $dnsConfig = $r['dns_config'] ?? [];
    $rid = $r['id'] ?? 0;
?>

<!-- VPN Config Modal -->
<div class="modal fade" id="modal-vpn-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-lock mr-2"></i>VPN Configuration</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <?php if (!empty($vpnConfig)): ?>
                    <div class="details-grid">
                        <?php foreach (['interface_name' => 'Interface', 'vpn_package' => 'VPN App', 'proxy_host' => 'Proxy Host'] as $k => $l):
                            if (!empty($vpnConfig[$k])): ?>
                            <div class="detail-card">
                                <div class="detail-icon"><i class="fas fa-network-wired"></i></div>
                                <div class="detail-label"><?= $l ?></div>
                                <div class="detail-value"><code><?= htmlspecialchars($vpnConfig[$k]) ?></code></div>
                            </div>
                        <?php endif; endforeach; ?>
                        <?php if (!empty($vpnConfig['dns_servers'])): ?>
                            <div class="detail-card info">
                                <div class="detail-icon"><i class="fas fa-globe"></i></div>
                                <div class="detail-label">DNS Servers</div>
                                <div class="detail-value">
                                    <?php foreach ($vpnConfig['dns_servers'] as $dns): ?>
                                        <span class="badge badge-info mr-1"><?= htmlspecialchars($dns) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">No VPN active</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- CA Certs (User) Modal -->
<div class="modal fade" id="modal-ca-user-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-certificate text-warning mr-2"></i>User-Installed CA Certificates</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <?php if (!empty($userCaCerts)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Alias</th>
                                    <th>Subject</th>
                                    <th>Issuer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($userCaCerts as $cert): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><code><?= htmlspecialchars($cert['alias'] ?? '—') ?></code></td>
                                    <td><?= htmlspecialchars($cert['subject'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($cert['issuer'] ?? '—') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No user-installed CA certificates</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- CA Certs (System) Modal -->
<div class="modal fade" id="modal-ca-system-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-certificate text-danger mr-2"></i>System CA Certificates</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <?php if (!empty($systemCaCerts)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Alias</th>
                                    <th>Subject</th>
                                    <th>Issuer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($systemCaCerts as $cert): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><code><?= htmlspecialchars($cert['alias'] ?? '—') ?></code></td>
                                    <td><?= htmlspecialchars($cert['subject'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($cert['issuer'] ?? '—') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No system CA certificates</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Open Ports Modal -->
<div class="modal fade" id="modal-ports-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-door-open mr-2"></i>Open Ports</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <?php if (!empty($ports)): ?>
                    <div class="details-grid">
                        <?php foreach ($ports as $p):
                            $type = is_array($p) ? ($p['type'] ?? 'TCP') : 'TCP';
                            $port = is_array($p) ? ($p['port'] ?? '?') : $p;
                        ?>
                            <div class="detail-card <?= $type === 'UDP' ? 'info' : 'danger' ?>">
                                <div class="detail-icon"><i class="fas fa-<?= $type === 'UDP' ? 'random' : 'arrow-right' ?>"></i></div>
                                <div class="detail-label">Port <?= htmlspecialchars($port) ?></div>
                                <div class="detail-value">
                                    <span class="badge badge-<?= $type === 'UDP' ? 'info' : 'danger' ?>"><?= $type ?></span>
                                    <?php if (!empty($p['service'])): ?>
                                        <small class="d-block text-muted mt-1"><?= htmlspecialchars($p['service']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">No open ports detected</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Device Admin Apps Modal -->
<div class="modal fade" id="modal-admin-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-shield mr-2"></i>Device Admin Apps</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <?php if (!empty($deviceAdmins)): ?>
                    <div class="details-grid">
                        <?php foreach ($deviceAdmins as $admin): ?>
                            <div class="detail-card warning">
                                <div class="detail-icon"><i class="fas fa-shield-alt"></i></div>
                                <div class="detail-label"><?= htmlspecialchars($admin['label'] ?? $admin['package'] ?? 'Unknown') ?></div>
                                <div class="detail-value">
                                    <strong>Package:</strong> <code><?= htmlspecialchars($admin['package'] ?? '—') ?></code><br>
                                    <strong>Receiver:</strong> <small class="text-muted"><?= htmlspecialchars($admin['receiver'] ?? '—') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">No device admin apps</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- DNS Config Modal -->
<div class="modal fade" id="modal-dns-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-dns mr-2"></i>DNS Configuration</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <?php if (!empty($dnsConfig)): ?>
                    <div class="details-grid">
                        <?php if (!empty($dnsConfig['servers'])): ?>
                            <div class="detail-card info">
                                <div class="detail-icon"><i class="fas fa-server"></i></div>
                                <div class="detail-label">DNS Servers</div>
                                <div class="detail-value">
                                    <?php foreach ($dnsConfig['servers'] as $s): ?>
                                        <span class="badge badge-primary mr-1"><?= htmlspecialchars($s) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($dnsConfig['private_dns_mode'])): ?>
                            <div class="detail-card success">
                                <div class="detail-icon"><i class="fas fa-lock"></i></div>
                                <div class="detail-label">Private DNS Mode</div>
                                <div class="detail-value">
                                    <?php
                                    $modeMap = [0 => 'Off', 1 => 'Opportunistic', 2 => 'Hostname', 3 => 'Error'];
                                    $modeLabel = $modeMap[$dnsConfig['private_dns_mode']] ?? 'Unknown';
                                    ?>
                                    <span class="badge badge-<?= $dnsConfig['private_dns_mode'] == 2 ? 'success' : 'secondary' ?>">
                                        <?= $modeLabel ?>
                                    </span>
                                    <?php if (!empty($dnsConfig['private_dns_hostname'])): ?>
                                        <br><small class="text-muted">Hostname: <code><?= htmlspecialchars($dnsConfig['private_dns_hostname']) ?></code></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($dnsConfig['fallback_servers'])): ?>
                            <div class="detail-card warning">
                                <div class="detail-icon"><i class="fas fa-history"></i></div>
                                <div class="detail-label">Fallback Servers</div>
                                <div class="detail-value">
                                    <?php foreach ($dnsConfig['fallback_servers'] as $s): ?>
                                        <span class="badge badge-warning mr-1"><?= htmlspecialchars($s) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">No DNS configuration data</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endforeach; endif; ?>

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>