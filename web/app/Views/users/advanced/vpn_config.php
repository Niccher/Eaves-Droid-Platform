<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'device_id',
    ['vpn_active', 'vpn_protocol', 'vpn_server', 'vpn_package', 'vpn_is_always_on', 'vpn_interface', 'vpn_mtu', 'vpn_is_lockdown', 'vpn_label', 'vpn_block_non_vpn', 'vpn_auth_type', 'vpn_ca_cert_sha256', 'vpn_client_cert_sha256', 'vpn_port'],
    ['vpn_dns_servers', 'vpn_routes', 'vpn_apps', 'vpn_dns_search_domains', 'vpn_excluded_apps', 'vpn_included_apps']
);
?>

<style>
.vpn-card { border-radius: 12px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid #eef2f5; overflow: hidden; margin-bottom: 24px; }
.vpn-header { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: #fff; padding: 20px 24px; }
.vpn-status-shield { width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
.shield-active { background: rgba(40, 167, 69, 0.2); color: #28a745; border: 2px solid #28a745; }
.shield-inactive { background: rgba(108, 117, 125, 0.2); color: #6c757d; border: 2px solid #6c757d; }
.vpn-grid-label { font-size: 10px; font-weight: 700; color: #8892a0; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
.vpn-grid-val { font-size: 14px; font-weight: 600; color: #2c3e50; }
.vpn-meta-box { background: #f8fafc; border-radius: 8px; border: 1px solid #eef2f5; padding: 12px 16px; height: 100%; }
.vpn-pill { display: inline-block; background: #e2e8f0; border-radius: 6px; padding: 3px 8px; font-size: 11px; font-family: monospace; color: #475569; margin: 2px; }
.route-pill { display: inline-block; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 3px 8px; font-size: 11px; font-family: monospace; color: #166534; margin: 2px; }
.dns-pill { display: inline-block; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 3px 8px; font-size: 11px; font-family: monospace; color: #1e40af; margin: 2px; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <h1 class="h2 mb-0"><i class="fas fa-shield-alt text-primary mr-2"></i>VPN &amp; Secure Tunnels</h1>
                    <p class="text-muted mt-1 mb-0">Active VPN connection profiles, proxy configurations, routing paths, and DNS configurations</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>

            <!-- Callout -->
            <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;">
                <h5 class="font-weight-bold text-info"><i class="fas fa-network-wired mr-2"></i>Security Auditing Scopes</h5>
                <p class="text-secondary mb-0" style="font-size:14px;">VPN configurations are analyzed to check if user traffic is routed through encrypted tunnels or if unexpected mock providers are intercepting web requests.</p>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (empty($rows)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                    <h4 class="text-secondary">No VPN profiles found</h4>
                    <p class="text-muted">VPN configurations will appear once extracted.</p>
                </div>
            <?php else: foreach ($rows as $r):
                $active = !empty($r['vpn_active']);
                $dns = $r['vpn_dns_servers'] ?? [];
                if (is_string($dns)) $dns = json_decode($dns, true) ?: [];
                $routes = $r['vpn_routes'] ?? [];
                if (is_string($routes)) $routes = json_decode($routes, true) ?: [];
                $excluded = $r['vpn_excluded_apps'] ?? [];
                if (is_string($excluded)) $excluded = json_decode($excluded, true) ?: [];
                $included = $r['vpn_included_apps'] ?? [];
                if (is_string($included)) $included = json_decode($included, true) ?: [];
                ?>
                <div class="vpn-card">
                    <div class="vpn-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="vpn-status-shield <?= $active ? 'shield-active' : 'shield-inactive' ?> mr-3">
                                <i class="fas <?= $active ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 font-weight-bold"><?= $active ? 'Secure Tunnel Active' : 'No Active VPN connection' ?></h4>
                                <p class="mb-0 opacity-75 font-family-monospace" style="font-size: 12px;">Device ID: <?= esc($r['device_id']) ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-<?= $active ? 'success' : 'secondary' ?> px-3 py-2 font-weight-bold">
                                <?= $active ? 'PROTECTED' : 'UNPROTECTED' ?>
                            </span>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="vpn-meta-box">
                                    <div class="vpn-grid-label"><i class="fas fa-network-wired mr-1"></i>Protocol</div>
                                    <div class="vpn-grid-val"><span class="badge badge-primary px-2"><?= esc($r['vpn_protocol'] ?: 'N/A') ?></span></div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="vpn-meta-box">
                                    <div class="vpn-grid-label"><i class="fas fa-server mr-1"></i>Server Address</div>
                                    <div class="vpn-grid-val"><?= esc($r['vpn_server'] ?: '—') ?></div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="vpn-meta-box">
                                    <div class="vpn-grid-label"><i class="fas fa-sitemap mr-1"></i>Interface &amp; MTU</div>
                                    <div class="vpn-grid-val">
                                        <code><?= esc($r['vpn_interface'] ?: '—') ?></code>
                                        <?php if ($r['vpn_mtu']): ?>
                                            <span class="text-muted small ml-1">(MTU: <?= (int)$r['vpn_mtu'] ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="vpn-meta-box">
                                    <div class="vpn-grid-label"><i class="fas fa-cogs mr-1"></i>Always On / Lockdown</div>
                                    <div class="vpn-grid-val">
                                        <span class="badge badge-<?= !empty($r['vpn_is_always_on']) ? 'success' : 'light border' ?> mr-1">Always-On: <?= !empty($r['vpn_is_always_on']) ? 'Yes' : 'No' ?></span>
                                        <span class="badge badge-<?= !empty($r['vpn_is_lockdown']) ? 'danger' : 'light border' ?>">Lockdown: <?= !empty($r['vpn_is_lockdown']) ? 'Yes' : 'No' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h6 class="font-weight-bold text-secondary mb-3"><i class="fas fa-server mr-2 text-primary"></i>DNS Servers (<?= count($dns) ?>)</h6>
                                <div class="border rounded p-3 bg-light" style="min-height: 80px;">
                                    <?php if (empty($dns)): ?>
                                        <span class="text-muted small">No custom DNS servers routed via VPN.</span>
                                    <?php else: foreach ($dns as $ip): ?>
                                        <span class="dns-pill"><i class="fas fa-globe mr-1"></i><?= esc($ip) ?></span>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <h6 class="font-weight-bold text-secondary mb-3"><i class="fas fa-route mr-2 text-primary"></i>Configured Routing Paths (<?= count($routes) ?>)</h6>
                                <div class="border rounded p-3 bg-light" style="min-height: 80px;">
                                    <?php if (empty($routes)): ?>
                                        <span class="text-muted small">Default routing configuration.</span>
                                    <?php else: foreach ($routes as $route): ?>
                                        <span class="route-pill"><i class="fas fa-directions mr-1"></i><?= esc($route) ?></span>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="font-weight-bold text-secondary mb-3"><i class="fas fa-ban mr-2 text-danger"></i>Excluded Apps (<?= count($excluded) ?>)</h6>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto; background: #fff;">
                                    <?php if (empty($excluded)): ?>
                                        <span class="text-muted small">No applications excluded from tunnel.</span>
                                    <?php else: foreach ($excluded as $pkg): ?>
                                        <span class="vpn-pill"><?= esc($pkg) ?></span>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="font-weight-bold text-secondary mb-3"><i class="fas fa-check-circle mr-2 text-success"></i>Included Apps (<?= count($included) ?>)</h6>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto; background: #fff;">
                                    <?php if (empty($included)): ?>
                                        <span class="text-muted small">All application traffic is routed (Default).</span>
                                    <?php else: foreach ($included as $pkg): ?>
                                        <span class="vpn-pill"><?= esc($pkg) ?></span>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top text-right text-muted small">
                            <i class="fas fa-clock mr-1"></i> Last updated: <?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—' ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </section>
</div>
