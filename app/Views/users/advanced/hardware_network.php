<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-network-wired text-secondary mr-2"></i>Network Hardware</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Network Interfaces, ARP Cache, Link Properties, and WiFi Passpoint</p>
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
                            <th><i class="fas fa-ethernet mr-1"></i>Network Interfaces</th>
                            <th><i class="fas fa-exchange-alt mr-1"></i>ARP Cache</th>
                            <th><i class="fas fa-link mr-1"></i>Link Properties</th>
                            <th><i class="fas fa-wifi mr-1"></i>WiFi Passpoint</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="6" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-network-wired fa-3x text-muted mb-3"></i><h4>No network hardware data</h4><p class="text-muted">Snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $interfaces = is_string($r['network_interfaces'] ?? null) ? json_decode($r['network_interfaces'], true) : ($r['network_interfaces'] ?? []);
                            $arp = is_string($r['arp_cache'] ?? null) ? json_decode($r['arp_cache'], true) : ($r['arp_cache'] ?? []);
                            $link = is_string($r['link_properties'] ?? null) ? json_decode($r['link_properties'], true) : ($r['link_properties'] ?? []);
                            $passpoint = is_string($r['wifi_passpoint'] ?? null) ? json_decode($r['wifi_passpoint'], true) : ($r['wifi_passpoint'] ?? []);
                            $rid = $r['id'] ?? 0;
                            ?>
                            <tr>
                                <td class="text-center">
                                    <?php if (!empty($interfaces)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-interfaces-<?= $rid ?>"
                                           class="badge badge-info p-2" title="Click to view network interfaces">
                                            <i class="fas fa-eye mr-1"></i><?= is_array($interfaces) ? count($interfaces) : 1 ?> interface<?= (is_array($interfaces) ? count($interfaces) : 1) !== 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($arp)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-arp-<?= $rid ?>"
                                           class="badge badge-warning p-2" title="Click to view ARP cache">
                                            <i class="fas fa-eye mr-1"></i><?= is_array($arp) ? count($arp) : 1 ?> entr<?= (is_array($arp) ? count($arp) : 1) !== 1 ? 'ies' : 'y' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($link)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-link-<?= $rid ?>"
                                           class="badge badge-success p-2" title="Click to view link properties">
                                            <i class="fas fa-eye mr-1"></i><?= is_array($link) ? count($link) : 1 ?> propert<?= (is_array($link) ? count($link) : 1) !== 1 ? 'ies' : 'y' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($passpoint)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-passpoint-<?= $rid ?>"
                                           class="badge badge-primary p-2" title="Click to view WiFi passpoint">
                                            <i class="fas fa-eye mr-1"></i><?= is_array($passpoint) ? count($passpoint) : 1 ?> entr<?= (is_array($passpoint) ? count($passpoint) : 1) !== 1 ? 'ies' : 'y' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $rid ?>"
                                            data-url="<?= base_url('advanced/hardware/hardware_network/delete') ?>"
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
    $interfaces = is_string($r['network_interfaces'] ?? null) ? json_decode($r['network_interfaces'], true) : ($r['network_interfaces'] ?? []);
    $arp = is_string($r['arp_cache'] ?? null) ? json_decode($r['arp_cache'], true) : ($r['arp_cache'] ?? []);
    $link = is_string($r['link_properties'] ?? null) ? json_decode($r['link_properties'], true) : ($r['link_properties'] ?? []);
    $passpoint = is_string($r['wifi_passpoint'] ?? null) ? json_decode($r['wifi_passpoint'], true) : ($r['wifi_passpoint'] ?? []);
    $rid = $r['id'] ?? 0;
?>

<!-- Network Interfaces Modal -->
<div class="modal fade" id="modal-interfaces-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-ethernet mr-2"></i>Network Interfaces (<?= is_array($interfaces) ? count($interfaces) : 0 ?>)</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <?php if (!empty($interfaces) && is_array($interfaces)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Display Name</th>
                                    <th>Type</th>
                                    <th>MAC Address</th>
                                    <th>IPv4 Addresses</th>
                                    <th>IPv6 Addresses</th>
                                    <th>State</th>
                                    <th>MTU</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($interfaces as $iface): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><strong><?= htmlspecialchars($iface['name'] ?? '—') ?></strong></td>
                                    <td><?= htmlspecialchars($iface['display_name'] ?? '—') ?></td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($iface['type'] ?? '—') ?></span></td>
                                    <td><code><?= htmlspecialchars($iface['mac_address'] ?? '—') ?></code></td>
                                    <td>
                                        <?php if (!empty($iface['ipv4_addresses'])): ?>
                                            <?php foreach ($iface['ipv4_addresses'] as $addr): ?>
                                                <span class="badge badge-light text-dark mr-1 mb-1"><code><?= htmlspecialchars($addr) ?></code></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($iface['ipv6_addresses'])): ?>
                                            <?php foreach ($iface['ipv6_addresses'] as $addr): ?>
                                                <span class="badge badge-light text-dark mr-1 mb-1"><code><?= htmlspecialchars($addr) ?></code></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= !empty($iface['is_up']) ? 'success' : 'secondary' ?>">
                                            <?= !empty($iface['is_up']) ? 'Up' : 'Down' ?>
                                        </span>
                                    </td>
                                    <td><?= $iface['mtu'] ?? '—' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No network interfaces data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ARP Cache Modal -->
<div class="modal fade" id="modal-arp-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exchange-alt mr-2"></i>ARP Cache (<?= is_array($arp) ? count($arp) : 0 ?>)</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <?php if (!empty($arp) && is_array($arp)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>IP Address</th>
                                    <th>MAC Address</th>
                                    <th>Interface</th>
                                    <th>Flags</th>
                                    <th>State</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($arp as $entry): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><code><?= htmlspecialchars($entry['ip_address'] ?? $entry['ip'] ?? '—') ?></code></td>
                                    <td><code><?= htmlspecialchars($entry['mac_address'] ?? $entry['mac'] ?? '—') ?></code></td>
                                    <td><?= htmlspecialchars($entry['interface'] ?? '—') ?></td>
                                    <td><span class="badge badge-light text-dark"><?= htmlspecialchars($entry['flags'] ?? '—') ?></span></td>
                                    <td>
                                        <span class="badge badge-<?= !empty($entry['is_reachable']) ? 'success' : 'warning' ?>">
                                            <?= !empty($entry['is_reachable']) ? 'Reachable' : 'Stale' ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No ARP cache data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Link Properties Modal -->
<div class="modal fade" id="modal-link-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-link mr-2"></i>Link Properties (<?= is_array($link) ? count($link) : 0 ?>)</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <?php if (!empty($link) && is_array($link)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Interface</th>
                                    <th>Link Speed</th>
                                    <th>DNS Servers</th>
                                    <th>Domains</th>
                                    <th>Routes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($link as $prop): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><strong><?= htmlspecialchars($prop['interface'] ?? '—') ?></strong></td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($prop['link_speed'] ?? $prop['speed'] ?? '—') ?></span></td>
                                    <td>
                                        <?php if (!empty($prop['dns_servers'])): ?>
                                            <?php foreach ($prop['dns_servers'] as $dns): ?>
                                                <span class="badge badge-light text-dark mr-1 mb-1"><code><?= htmlspecialchars($dns) ?></code></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($prop['domains'])): ?>
                                            <?php foreach ($prop['domains'] as $domain): ?>
                                                <span class="badge badge-warning mr-1 mb-1"><?= htmlspecialchars($domain) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($prop['routes'])): ?>
                                            <?php foreach ($prop['routes'] as $route): ?>
                                                <div><code class="small"><?= htmlspecialchars($route['destination'] ?? $route ?? '—') ?></code></div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No link properties data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- WiFi Passpoint Modal -->
<div class="modal fade" id="modal-passpoint-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-wifi mr-2"></i>WiFi Passpoint (<?= is_array($passpoint) ? count($passpoint) : 0 ?>)</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <?php if (!empty($passpoint) && is_array($passpoint)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Friendly Name</th>
                                    <th>SSID</th>
                                    <th>BSSID</th>
                                    <th>Security</th>
                                    <th>Channel</th>
                                    <th>Signal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($passpoint as $pp): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><strong><?= htmlspecialchars($pp['friendly_name'] ?? $pp['name'] ?? '—') ?></strong></td>
                                    <td><code><?= htmlspecialchars($pp['ssid'] ?? '—') ?></code></td>
                                    <td><code><?= htmlspecialchars($pp['bssid'] ?? '—') ?></code></td>
                                    <td>
                                        <?php if (!empty($pp['security_types'])): ?>
                                            <?php foreach ($pp['security_types'] as $sec): ?>
                                                <span class="badge badge-info mr-1 mb-1"><?= htmlspecialchars($sec) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $pp['channel'] ?? '—' ?></td>
                                    <td>
                                        <?php if (isset($pp['signal_level']) || isset($pp['signal'])): ?>
                                            <span class="badge badge-<?= ($pp['signal_level'] ?? $pp['signal'] ?? 0) > -50 ? 'success' : (($pp['signal_level'] ?? $pp['signal'] ?? 0) > -70 ? 'warning' : 'danger') ?>">
                                                <?= $pp['signal_level'] ?? $pp['signal'] ?? '—' ?> dBm
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No WiFi passpoint data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endforeach; endif; ?>

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
