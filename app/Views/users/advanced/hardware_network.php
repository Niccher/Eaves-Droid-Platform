<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-network-wired text-secondary mr-2"></i>Network Interfaces</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Network interfaces, routes, ARP cache, WiFi Passpoint</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Network Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="networkTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-network-wired mr-1"></i> Interfaces</th>
                            <th><i class="fas fa-route mr-1"></i> Routes</th>
                            <th><i class="fas fa-link mr-1"></i> Link Properties</th>
                            <th><i class="fas fa-sitemap mr-1"></i> ARP Cache</th>
                            <th><i class="fas fa-wifi mr-1"></i> WiFi Passpoint</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-network-wired fa-3x text-muted mb-3"></i><h4>No network data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $rid = $r['id'] ?? 0;
                            $ifaces = $r['network_interfaces'] ?? [];
                            $routes = $r['proc_net_dev'] ?? [];
                            $links = $r['link_properties'] ?? [];
                            $arp = $r['arp_cache'] ?? [];
                            $passpoint = $r['wifi_passpoint'] ?? [];
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#network-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td>
                                    <?php if (!empty($ifaces)): ?>
                                        <button class="btn btn-sm btn-outline-info details-row"
                                            data-title="Network Interfaces (<?= count($ifaces) ?>)"
                                            data-data='<?= esc(json_encode($ifaces), 'attr') ?>'
                                            title="View network interfaces">
                                            <i class="fas fa-network-wired mr-1"></i> <?= count($ifaces) ?> interface<?= count($ifaces) !== 1 ? 's' : '' ?>
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-secondary p-2">0 interfaces</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-primary p-2"><?= count($routes) ?> route<?= count($routes) !== 1 ? 's' : '' ?></span></td>
                                <td>
                                    <?php if (!empty($links)): ?>
                                        <button class="btn btn-sm btn-outline-warning details-row"
                                            data-title="Link Properties (<?= count($links) ?>)"
                                            data-data='<?= esc(json_encode($links), 'attr') ?>'
                                            title="View link properties">
                                            <i class="fas fa-link mr-1"></i> <?= count($links) ?> link<?= count($links) !== 1 ? 's' : '' ?>
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-warning p-2">0 links</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-secondary p-2"><?= count($arp) ?> entr<?= count($arp) !== 1 ? 'ies' : 'y' ?></span></td>
                                <td><span class="badge badge-success p-2"><?= count($passpoint) ?> config<?= count($passpoint) !== 1 ? 's' : '' ?></span></td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/hardware_network/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="8" class="p-0 border-0">
                                    <div id="network-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-route mr-2"></i>Routes (<?= count($routes) ?>)</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <?php if (!empty($routes)): foreach ($routes as $route): ?>
                                                            <tr><td colspan="2"><pre class="mb-0 small"><?= esc(json_encode($route, JSON_PRETTY_PRINT)) ?></pre></td></tr>
                                                        <?php endforeach; else: ?>
                                                            <tr><td colspan="2" class="text-muted">No routes</td></tr>
                                                        <?php endif; ?>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-sitemap mr-2"></i>ARP Cache (<?= count($arp) ?>)</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <?php if (!empty($arp)): foreach ($arp as $entry): ?>
                                                            <tr><td colspan="2"><pre class="mb-0 small"><?= esc(json_encode($entry, JSON_PRETTY_PRINT)) ?></pre></td></tr>
                                                        <?php endforeach; else: ?>
                                                            <tr><td colspan="2" class="text-muted">No ARP</td></tr>
                                                        <?php endif; ?>
                                                    </table>
                                                </div>
                                                <div class="col-md-12">
                                                    <h6 class="mt-3"><i class="fas fa-wifi mr-2"></i>WiFi Passpoint (<?= count($passpoint) ?>)</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <?php if (!empty($passpoint)): foreach ($passpoint as $pp): ?>
                                                            <tr><td colspan="2"><pre class="mb-0 small"><?= esc(json_encode($pp, JSON_PRETTY_PRINT)) ?></pre></td></tr>
                                                        <?php endforeach; else: ?>
                                                            <tr><td colspan="2" class="text-muted">No Passpoint config</td></tr>
                                                        <?php endif; ?>
                                                    </table>
                                                </div>
                                                <div class="col-md-12">
                                                    <h6 class="mt-3"><i class="fas fa-info-circle mr-2"></i>Snapshot Info</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Extracted At</th><td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—' ?></td></tr>
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
            <div class="card-footer"><div class="float-right"><?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?></div></div>
        </div>
    </div></div></div></section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>