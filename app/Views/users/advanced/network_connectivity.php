<?php /** @var array $latest_ni @var array $latest_nh @var array $latest_ct @var array $history @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-signal text-primary mr-2"></i>Network & Connectivity</h1>
                        <span class="badge badge-primary border p-2 text-white"><i class="fas fa-database mr-1"></i>Snapshots: <b><?= count($history ?? []) ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">SIM, operator, WiFi, interfaces, ARP, link properties, Passpoint, and cell towers</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Current State - Tabbed -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="networkTabs" role="tablist">
                                <li class="nav-item"><a class="nav-link active" id="tab-sim" data-toggle="tab" href="#sim" role="tab"><i class="fas fa-sim-card mr-1"></i>SIM & Operator</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab-wifi" data-toggle="tab" href="#wifi" role="tab"><i class="fas fa-wifi mr-1"></i>WiFi & Passpoint</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab-interfaces" data-toggle="tab" href="#interfaces" role="tab"><i class="fas fa-ethernet mr-1"></i>Interfaces</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab-cells" data-toggle="tab" href="#cells" role="tab"><i class="fas fa-broadcast-tower mr-1"></i>Cell Towers</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="networkTabsContent">
                                <!-- SIM & Operator Tab -->
                                <div class="tab-pane fade show active" id="sim" role="tabpanel">
                                    <?php if ($latest_ni): ?>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4">Operator</dt><dd class="col-sm-8"><?= htmlspecialchars($latest_ni['network_operator_name'] ?? '—') ?></dd>
                                                    <dt class="col-sm-4">MCC/MNC</dt><dd class="col-sm-8"><?= htmlspecialchars($latest_ni['network_country_iso'] ?? '—') ?> / <?= htmlspecialchars($latest_ni['network_operator'] ?? '—') ?></dd>
                                                    <dt class="col-sm-4">SIM Operator</dt><dd class="col-sm-8"><?= htmlspecialchars($latest_ni['sim_operator_name'] ?? '—') ?></dd>
                                                    <dt class="col-sm-4">SIM State</dt><dd class="col-sm-8"><span class="badge badge-<?= ($latest_ni['sim_state'] ?? '') === 'READY' ? 'success' : 'warning' ?>"><?= htmlspecialchars($latest_ni['sim_state'] ?? '—') ?></span></dd>
                                                    <dt class="col-sm-4">Phone Type</dt><dd class="col-sm-8"><?= htmlspecialchars($latest_ni['phone_type'] ?? '—') ?></dd>
                                                    <dt class="col-sm-4">Data Network</dt><dd class="col-sm-8"><?= htmlspecialchars($latest_ni['data_network_type'] ?? '—') ?></dd>
                                                    <dt class="col-sm-4">5G</dt><dd class="col-sm-8">
                                                        <?php if ($latest_ni['is_5g_nsa']): ?><span class="badge badge-info">NSA</span><?php endif; ?>
                                                        <?php if ($latest_ni['is_5g_sa']): ?><span class="badge badge-success ml-1">SA</span><?php endif; ?>
                                                    </dd>
                                                    <dt class="col-sm-4">Roaming</dt><dd class="col-sm-8"><?= !empty($latest_ni['is_roaming']) ? '<span class="badge badge-warning">Yes</span>' : '<span class="badge badge-success">No</span>' ?></dd>
                                                </dl>
                                            </div>
                                            <div class="col-md-6">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4">IMEI</dt><dd class="col-sm-8"><code><?= htmlspecialchars($latest_ni['device_imei'] ?? '—') ?></code></dd>
                                                    <dt class="col-sm-4">Subscriber ID</dt><dd class="col-sm-8"><code><?= htmlspecialchars($latest_ni['subscriber_id'] ?? '—') ?></code></dd>
                                                    <dt class="col-sm-4">SIM Serial</dt><dd class="col-sm-8"><code><?= htmlspecialchars($latest_ni['sim_serial'] ?? '—') ?></code></dd>
                                                    <dt class="col-sm-4">NR Band</dt><dd class="col-sm-8"><?= $latest_ni['nr_band'] ?? '—' ?></dd>
                                                    <dt class="col-sm-4">NR SCS</dt><dd class="col-sm-8"><?= $latest_ni['nr_scs'] ?? '—' ?></dd>
                                                    <dt class="col-sm-4">SSB Freq</dt><dd class="col-sm-8"><?= $latest_ni['nr_ssb_frequency'] ?? '—' ?></dd>
                                                </dl>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No network info data</p>
                                    <?php endif; ?>
                                </div>

                                <!-- WiFi & Passpoint Tab -->
                                <div class="tab-pane fade" id="wifi" role="tabpanel">
                                    <?php if ($latest_ni && $latest_ni['wifi_ssid']): ?>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-2"><i class="fas fa-wifi text-success mr-1"></i>Connected WiFi</h6>
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4">SSID</dt><dd class="col-sm-8 font-weight-bold"><?= htmlspecialchars($latest_ni['wifi_ssid']) ?></dd>
                                                    <dt class="col-sm-4">BSSID</dt><dd class="col-sm-8"><code><?= htmlspecialchars($latest_ni['wifi_bssid'] ?? '—') ?></code></dd>
                                                    <dt class="col-sm-4">RSSI</dt><dd class="col-sm-8">
                                                        <?php $rssi = $latest_ni['wifi_rssi'] ?? 0; $badge = $rssi > -50 ? 'success' : ($rssi > -70 ? 'warning' : 'danger'); ?>
                                                        <span class="badge badge-<?= $badge ?>"><?= $rssi ?> dBm</span>
                                                    </dd>
                                                    <dt class="col-sm-4">Frequency</dt><dd class="col-sm-8"><?= $latest_ni['wifi_frequency'] ?? $latest_ni['wifi_frequency_mhz'] ?? '—' ?> MHz</dd>
                                                    <dt class="col-sm-4">Link Speed</dt><dd class="col-sm-8"><?= $latest_ni['wifi_link_speed'] ?? $latest_ni['wifi_link_speed_mbps'] ?? '—' ?> Mbps</dd>
                                                    <dt class="col-sm-4">Channel</dt><dd class="col-sm-8"><?= $latest_ni['wifi_channel'] ?? '—' ?></dd>
                                                    <dt class="col-sm-4">Standard</dt><dd class="col-sm-8"><?= $latest_ni['wifi_standard'] ?? '—' ?></dd>
                                                    <dt class="col-sm-4">PHY Mode</dt><dd class="col-sm-8"><?= $latest_ni['wifi_phy_mode'] ?? '—' ?></dd>
                                                    <dt class="col-sm-4">SNR</dt><dd class="col-sm-8"><?= $latest_ni['wifi_snr'] ?? '—' ?> dB</dd>
                                                    <dt class="col-sm-4">IP Address</dt><dd class="col-sm-8"><code><?= htmlspecialchars($latest_ni['wifi_ip_address'] ?? '—') ?></code></dd>
                                                    <dt class="col-sm-4">MAC Address</dt><dd class="col-sm-8"><code><?= htmlspecialchars($latest_ni['wifi_mac_address'] ?? '—') ?></code></dd>
                                                </dl>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-2"><i class="fas fa-wifi text-primary mr-1"></i>WiFi Passpoint</h6>
                                                <?php if ($latest_nh): 
                                                    $passpoint = json_decode($latest_nh['wifi_passpoint'] ?? '[]', true);
                                                ?>
                                                    <?php if (!empty($passpoint)): ?>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-hover mb-0">
                                                                <thead class="thead-light"><tr><th>SSID</th><th>BSSID</th><th>Security</th><th>Signal</th></tr></thead>
                                                                <tbody>
                                                                <?php foreach (array_slice($passpoint, 0, 10) as $pp): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($pp['friendly_name'] ?? $pp['ssid'] ?? '—') ?></td>
                                                                        <td><code><?= htmlspecialchars($pp['bssid'] ?? '—') ?></code></td>
                                                                        <td>
                                                                            <?php if (!empty($pp['security_types'])): ?>
                                                                                <?php foreach ($pp['security_types'] as $sec): ?><span class="badge badge-info mr-1"><?= htmlspecialchars($sec) ?></span><?php endforeach; ?>
                                                                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                                        </td>
                                                                        <td>
                                                                            <?php $sig = $pp['signal_level'] ?? $pp['signal'] ?? 0; $sb = $sig > -50 ? 'success' : ($sig > -70 ? 'warning' : 'danger'); ?>
                                                                            <span class="badge badge-<?= $sb ?>"><?= $sig ?> dBm</span>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php else: ?>
                                                        <p class="text-muted">No Passpoint data</p>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <p class="text-muted">No hardware network data</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No WiFi data</p>
                                    <?php endif; ?>
                                </div>

                                <!-- Interfaces Tab -->
                                <div class="tab-pane fade" id="interfaces" role="tabpanel">
                                    <?php if ($latest_nh): 
                                        $interfaces = json_decode($latest_nh['network_interfaces'] ?? '[]', true);
                                        $arp = json_decode($latest_nh['arp_cache'] ?? '[]', true);
                                        $link = json_decode($latest_nh['link_properties'] ?? '[]', true);
                                    ?>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <h6 class="text-muted mb-2"><i class="fas fa-ethernet mr-1"></i>Network Interfaces (<?= count($interfaces) ?>)</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover mb-0">
                                                        <thead class="thead-light"><tr><th>Name</th><th>Type</th><th>MAC</th><th>IPv4</th><th>State</th></tr></thead>
                                                        <tbody>
                                                        <?php foreach ($interfaces as $iface): ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($iface['name'] ?? '—') ?></strong></td>
                                                                <td><span class="badge badge-info"><?= htmlspecialchars($iface['type'] ?? '—') ?></span></td>
                                                                <td><code><?= htmlspecialchars($iface['mac_address'] ?? '—') ?></code></td>
                                                                <td>
                                                                    <?php if (!empty($iface['ipv4_addresses'])): ?>
                                                                        <?php foreach ($iface['ipv4_addresses'] as $ip): ?><span class="badge badge-light text-dark mr-1"><code><?= htmlspecialchars($ip) ?></code></span><?php endforeach; ?>
                                                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                                </td>
                                                                <td><span class="badge badge-<?= !empty($iface['is_up']) ? 'success' : 'secondary' ?>"><?= !empty($iface['is_up']) ? 'Up' : 'Down' ?></span></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <h6 class="text-muted mb-2"><i class="fas fa-exchange-alt mr-1"></i>ARP Cache (<?= count($arp) ?>)</h6>
                                                <div class="table-responsive" style="max-height:250px;overflow:auto;">
                                                    <table class="table table-sm table-hover mb-0">
                                                        <thead class="thead-light"><tr><th>IP</th><th>MAC</th><th>Interface</th><th>State</th></tr></thead>
                                                        <tbody>
                                                        <?php foreach (array_slice($arp, 0, 20) as $entry): ?>
                                                            <tr>
                                                                <td><code><?= htmlspecialchars($entry['ip_address'] ?? $entry['ip'] ?? '—') ?></code></td>
                                                                <td><code><?= htmlspecialchars($entry['mac_address'] ?? $entry['mac'] ?? '—') ?></code></td>
                                                                <td><?= htmlspecialchars($entry['interface'] ?? '—') ?></td>
                                                                <td><span class="badge badge-<?= !empty($entry['is_reachable']) ? 'success' : 'warning' ?>"><?= !empty($entry['is_reachable']) ? 'Reachable' : 'Stale' ?></span></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <h6 class="text-muted mb-2"><i class="fas fa-link mr-1"></i>Link Properties (<?= count($link) ?>)</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover mb-0">
                                                        <thead class="thead-light"><tr><th>Interface</th><th>Speed</th><th>DNS</th><th>Domains</th></tr></thead>
                                                        <tbody>
                                                        <?php foreach ($link as $prop): ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($prop['interface'] ?? '—') ?></strong></td>
                                                                <td><span class="badge badge-info"><?= htmlspecialchars($prop['link_speed'] ?? $prop['speed'] ?? '—') ?></span></td>
                                                                <td><?php if (!empty($prop['dns_servers'])): ?><?php foreach ($prop['dns_servers'] as $dns): ?><span class="badge badge-light text-dark mr-1"><code><?= htmlspecialchars($dns) ?></code></span><?php endforeach; ?><?php else: ?><span class="text-muted">—</span><?php endif; ?></td>
                                                                <td><?php if (!empty($prop['domains'])): ?><?php foreach ($prop['domains'] as $d): ?><span class="badge badge-warning mr-1"><?= htmlspecialchars($d) ?></span><?php endforeach; ?><?php else: ?><span class="text-muted">—</span><?php endif; ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No hardware network data</p>
                                    <?php endif; ?>
                                </div>

                                <!-- Cell Towers Tab -->
                                <div class="tab-pane fade" id="cells" role="tabpanel">
                                    <?php if ($latest_ct): 
                                        $towers = json_decode($latest_ct['towers_json'] ?? '[]', true);
                                        // Find serving cell (is_registered = 1 or first)
                                        $serving = null;
                                        foreach ($towers as $t) { if (!empty($t['is_registered'])) { $serving = $t; break; } }
                                        $serving = $serving ?? ($towers[0] ?? null);
                                    ?>
                                        <?php if ($serving): ?>
                                            <div class="alert alert-success mb-3">
                                                <h6 class="mb-1"><i class="fas fa-broadcast-tower mr-1"></i>Serving Cell</h6>
                                                <div class="row small">
                                                    <div class="col-md-3"><strong>Type:</strong> <?= htmlspecialchars($serving['type'] ?? '—') ?></div>
                                                    <div class="col-md-3"><strong>CID:</strong> <?= htmlspecialchars($serving['cid'] ?? '—') ?></div>
                                                    <div class="col-md-3"><strong>LAC/TAC:</strong> <?= htmlspecialchars($serving['lac'] ?? $serving['tac'] ?? '—') ?></div>
                                                    <div class="col-md-3"><strong>PCI:</strong> <?= htmlspecialchars($serving['pci'] ?? '—') ?></div>
                                                    <div class="col-md-3"><strong>MCC/MNC:</strong> <?= htmlspecialchars($serving['mcc'] ?? '—') ?>/<?= htmlspecialchars($serving['mnc'] ?? '—') ?></div>
                                                    <div class="col-md-3"><strong>RSSI:</strong> <span class="badge badge-<?= ($serving['rssi'] ?? -100) > -70 ? 'success' : (($serving['rssi'] ?? -100) > -90 ? 'warning' : 'danger') ?>"><?= $serving['rssi'] ?? '—' ?> dBm</span></div>
                                                    <div class="col-md-3"><strong>RSRP:</strong> <?= $serving['rsrp'] ?? '—' ?> dBm</div>
                                                    <div class="col-md-3"><strong>RSRQ:</strong> <?= $serving['rsrq'] ?? '—' ?> dB</div>
                                                    <div class="col-md-3"><strong>Band:</strong> <?= $serving['bandwidth'] ?? '—' ?></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <h6 class="text-muted mb-2"><i class="fas fa-list mr-1"></i>Neighboring Cells (<?= count($towers) - ($serving ? 1 : 0) ?>)</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="thead-light">
                                                <tr>
                                                    <th>Type</th><th>CID</th><th>LAC/TAC</th><th>PCI</th><th>MCC/MNC</th>
                                                    <th>RSSI</th><th>RSRP</th><th>RSRQ</th><th>Reg</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($towers as $t): if ($t === $serving) continue; ?>
                                                    <tr>
                                                        <td><span class="badge badge-secondary"><?= htmlspecialchars($t['type'] ?? '—') ?></span></td>
                                                        <td><?= htmlspecialchars($t['cid'] ?? $t['nci'] ?? '—') ?></td>
                                                        <td><?= htmlspecialchars($t['lac'] ?? $t['tac'] ?? '—') ?></td>
                                                        <td><?= htmlspecialchars($t['pci'] ?? '—') ?></td>
                                                        <td><?= htmlspecialchars($t['mcc'] ?? '—') ?>/<?= htmlspecialchars($t['mnc'] ?? '—') ?></td>
                                                        <td><?php $r=$t['rssi']??-100;$b=$r>-70?'success':($r>-90?'warning':'danger');?><span class="badge badge-<?=$b?>"><?=$r?> dBm</span></td>
                                                        <td><?= $t['rsrp'] ?? '—' ?> dBm</td>
                                                        <td><?= $t['rsrq'] ?? '—' ?> dB</td>
                                                        <td><?= !empty($t['is_registered']) ? '<span class="badge badge-success">✓</span>' : '—' ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No cell tower data</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Network History</h3>
                            <div class="card-tools ml-auto">
                                <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#networkHistoryModal">Show All (<?= count($history ?? []) ?>)</button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                        <th><i class="fas fa-sim-card mr-1"></i>Operator</th>
                                        <th><i class="fas fa-wifi text-success mr-1"></i>WiFi</th>
                                        <th><i class="fas fa-signal text-primary mr-1"></i>Mobile</th>
                                        <th><i class="fas fa-ethernet mr-1"></i>Interfaces</th>
                                        <th><i class="fas fa-broadcast-tower text-warning mr-1"></i>Cells</th>
                                        <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($history)): ?>
                                        <tr><td colspan="8" class="text-center py-5"><div class="empty-state"><i class="fas fa-signal fa-3x text-muted mb-3"></i><h4>No network history</h4></div></td></tr>
                                    <?php else: foreach (array_slice($history,0,5) as $h): 
                                        $ni = $h['network_info'] ?? [];
                                        $nh = $h['hardware_network'] ?? [];
                                        $ct = $h['cell_towers'] ?? [];
                                        $towers = json_decode($ct['towers_json'] ?? '[]', true);
                                    ?>
                                        <tr>
                                            <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                            <td>
                                                <strong><?= htmlspecialchars($ni['network_operator_name'] ?? $ni['sim_operator_name'] ?? '—') ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($ni['data_network_type'] ?? '—') ?></small>
                                            </td>
                                            <td>
                                                <?php if (!empty($ni['wifi_ssid'])): ?>
                                                    <i class="fas fa-wifi text-success mr-1"></i><?= htmlspecialchars($ni['wifi_ssid']) ?>
                                                    <br><small class="text-muted"><?= $ni['wifi_rssi'] ?? '—' ?> dBm</small>
                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                            </td>
<td>
                                                 <?php if (($ni['is_connected'] ?? false) && !$ni['wifi_ssid']): ?>
                                                     <i class="fas fa-mobile-alt text-primary mr-1"></i><?= htmlspecialchars($ni['network_operator_name'] ?? 'Mobile') ?>
                                                     <br><small class="text-muted"><?= $ni['data_network_type'] ?? '—' ?></small>
                                                 <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                             </td>
                                            <td>
                                                <?php $ifaces = json_decode($nh['network_interfaces'] ?? '[]', true); ?>
                                                <span class="badge badge-info"><?= count($ifaces) ?></span>
                                                <?php $up = array_filter($ifaces, fn($i) => !empty($i['is_up'])); ?>
                                                <small class="text-muted d-block"><?= count($up) ?> up</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-warning"><?= count($towers) ?></span>
                                                <?php $serving = null; foreach($towers as $t){ if(!empty($t['is_registered'])){$serving=$t;break;} } ?>
                                                <?php if ($serving): ?><small class="text-muted d-block">RSRP: <?= $serving['rsrp'] ?? '—' ?> dBm</small><?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/network_connectivity/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer"><div class="float-right"><?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal: Full Network History -->
    <div class="modal fade" id="networkHistoryModal" tabindex="-1" role="dialog" aria-labelledby="networkHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="networkHistoryModalLabel"><i class="fas fa-history mr-2"></i>Full Network History</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                    <th><i class="fas fa-sim-card mr-1"></i>Operator</th>
                                    <th><i class="fas fa-wifi text-success mr-1"></i>WiFi</th>
                                    <th><i class="fas fa-signal text-primary mr-1"></i>Mobile</th>
                                    <th><i class="fas fa-ethernet mr-1"></i>Interfaces</th>
                                    <th><i class="fas fa-broadcast-tower text-warning mr-1"></i>Cells</th>
                                    <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history)): ?>
                                <tr><td colspan="8" class="text-center py-5"><div class="empty-state"><i class="fas fa-signal fa-3x text-muted mb-3"></i><h4>No network history</h4></div></td></tr>
                                <?php else: foreach ($history as $h): 
                                    $ni = $h['network_info'] ?? [];
                                    $nh = $h['hardware_network'] ?? [];
                                    $ct = $h['cell_towers'] ?? [];
                                    $towers = json_decode($ct['towers_json'] ?? '[]', true);
                                ?>
                                <tr>
                                    <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                    <td>
                                        <strong><?= htmlspecialchars($ni['network_operator_name'] ?? $ni['sim_operator_name'] ?? '—') ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($ni['data_network_type'] ?? '—') ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($ni['wifi_ssid'])): ?>
                                            <i class="fas fa-wifi text-success mr-1"></i><?= htmlspecialchars($ni['wifi_ssid']) ?>
                                            <br><small class="text-muted"><?= $ni['wifi_rssi'] ?? '—' ?> dBm</small>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                    <td>
                                         <?php if (($ni['is_connected'] ?? false) && !$ni['wifi_ssid']): ?>
                                             <i class="fas fa-mobile-alt text-primary mr-1"></i><?= htmlspecialchars($ni['network_operator_name'] ?? 'Mobile') ?>
                                             <br><small class="text-muted"><?= $ni['data_network_type'] ?? '—' ?></small>
                                         <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                     </td>
                                    <td>
                                        <?php $ifaces = json_decode($nh['network_interfaces'] ?? '[]', true); ?>
                                        <span class="badge badge-info"><?= count($ifaces) ?></span>
                                        <?php $up = array_filter($ifaces, fn($i) => !empty($i['is_up'])); ?>
                                        <small class="text-muted d-block"><?= count($up) ?> up</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning"><?= count($towers) ?></span>
                                        <?php $serving = null; foreach($towers as $t){ if(!empty($t['is_registered'])){$serving=$t;break;} } ?>
                                        <?php if ($serving): ?><small class="text-muted d-block">RSRP: <?= $serving['rsrp'] ?? '—' ?> dBm</small><?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/network_connectivity/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>