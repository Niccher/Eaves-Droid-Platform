<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
// Group by device_id and keep only unique snapshots
$seen = [];
$unique = [];
foreach ($rows as $r) {
    $devId = $r['device_id'] ?? 'default';
    $key   = strtolower(trim((string)$devId));
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $unique[]   = $r;
    }
}
$rows = $unique;
?>

<style>
.net-card        { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.net-hero        { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; }
.net-kv          { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.net-kv:last-child { border-bottom: none; }
.net-kv .nk      { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.net-kv .nv      { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.net-badge       { border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 700; }
.section-label   { font-size: 10px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .4px; }
.data-box        { font-family: monospace; font-size: 11px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 8px; max-height: 150px; overflow-y: auto; color: #495057; }
.pill-item       { display: inline-block; background: #e9ecef; border-radius: 10px; padding: 1px 7px; font-size: 10px; color: #495057; margin: 2px; font-family: monospace; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-network-wired text-primary mr-2"></i>Network Interfaces</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active Snapshots: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">Hardware adapters, active routing tables, ARP caching tables, and WiFi Passpoint properties</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>

      <!-- Consistent Security Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-shield-alt mr-2"></i>Network Hardware &amp; Routing Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Auditing operational virtual interface cards (wlan, rmnet) and network routing configurations. Monitoring interface properties, hardware link capacities, and ARP entry counts detects potential man-in-the-middle redirects, active proxies, or packet spoofing bridges.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Network Adapters:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Active Interfaces:</b> Logs operational properties (IP addresses, hardware MAC, MTU settings) per adapter.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Routing &amp; Cache:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>IP Routing:</b> Audits routing table paths to trace DNS hijacking hooks.</li>
              <li><b>ARP Cache:</b> Audits IP-to-MAC mapping lists to catch local gateway spoofing.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Wi-Fi configuration:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>WiFi Passpoint:</b> Audits pre-installed automatic network profiles used for seamless cell redirection.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <?php if (empty($rows)): ?>
        <div class="text-center py-5">
          <i class="fas fa-network-wired fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Network Data Detected</h4>
          <p class="text-muted">Network details will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <div class="row">
        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          // JSON arrays parsed in model:
          $ifaces = $r['network_interfaces'] ?? [];
          $routes = $r['proc_net_dev'] ?? [];
          $links = $r['link_properties'] ?? [];
          $arp = $r['arp_cache'] ?? [];
          $passpoint = $r['wifi_passpoint'] ?? [];
          ?>

          <div class="col-md-12 mb-4">
            <div class="net-card">
              <!-- Hero -->
              <div class="net-hero">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <span class="badge badge-light net-badge"><i class="fas fa-wifi mr-1"></i><?= count($ifaces) ?> Interfaces</span>
                    <span class="badge badge-primary net-badge ml-1"><i class="fas fa-route mr-1"></i><?= count($routes) ?> Routes</span>
                  </div>
                  <div class="text-right">
                    <span class="small font-weight-bold">Device ID: <?= esc($r['device_id'] ?? '—') ?></span>
                  </div>
                </div>
              </div>

              <!-- Content Body -->
              <div class="p-3">
                <div class="row">
                  
                  <!-- Col 1: Interfaces & Enriched Links -->
                  <div class="col-md-6 mb-3">
                    <div class="section-label mb-2"><i class="fas fa-network-wired mr-1"></i>Active Interfaces (<?= count($ifaces) ?>)</div>
                    <?php if (!empty($ifaces)): ?>
                      <div class="data-box mb-3">
                        <?php foreach ($ifaces as $if): ?>
                          <div style="border-bottom:1px solid #e9ecef; padding:6px 0;">
                            <strong><?= esc($if['name'] ?? $if['interface'] ?? 'Unknown') ?></strong>
                            <?php if (!empty($if['ip_address'])): ?>
                              · IP: <code><?= esc(is_array($if['ip_address']) ? implode(', ', $if['ip_address']) : $if['ip_address']) ?></code>
                            <?php endif; ?>
                            <?php if (!empty($if['mac_address'])): ?>
                              · MAC: <code><?= esc($if['mac_address']) ?></code>
                            <?php endif; ?>
                            <?php if (isset($if['mtu'])): ?>
                              <span class="badge badge-light text-muted border ml-1">MTU: <?= esc($if['mtu']) ?></span>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <div class="text-muted small py-2 text-center mb-3" style="background:#f8f9fa; border:1px dashed #dee2e6; border-radius:4px;">No active interfaces declared</div>
                    <?php endif; ?>

                    <div class="section-label mb-2"><i class="fas fa-link mr-1"></i>Link Properties &amp; MTUs</div>
                    <?php if (!empty($links)): ?>
                      <div class="data-box">
                        <?php foreach ($links as $link): ?>
                          <div class="mb-2 pb-2 border-bottom" style="border-color:#e9ecef !important;">
                            <div class="d-flex justify-content-between align-items-center">
                              <strong>Interface: <?= esc($link['iface'] ?? $link['interface_name'] ?? 'Unknown') ?></strong>
                              <?php if (isset($link['mtu'])): ?>
                                <span class="badge badge-secondary">MTU: <?= esc($link['mtu']) ?></span>
                              <?php endif; ?>
                            </div>
                            <?php if (!empty($link['dns_servers'])): ?>
                              <div class="small text-muted mt-1"><i class="fas fa-server mr-1"></i>DNS: <code><?= esc(is_array($link['dns_servers']) ? implode(', ', $link['dns_servers']) : $link['dns_servers']) ?></code></div>
                            <?php endif; ?>
                            <?php if (!empty($link['domains'])): ?>
                              <div class="small text-muted"><i class="fas fa-globe mr-1"></i>Search Domains: <code><?= esc(is_array($link['domains']) ? implode(', ', $link['domains']) : $link['domains']) ?></code></div>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <div class="text-muted small py-2 text-center" style="background:#f8f9fa; border:1px dashed #dee2e6; border-radius:4px;">No link properties metadata available</div>
                    <?php endif; ?>
                  </div>

                  <!-- Col 2: Routing, ARP, Enriched Passpoint -->
                  <div class="col-md-6 mb-3">
                    <div class="section-label mb-2"><i class="fas fa-route mr-1"></i>Active Routing Table (<?= count($routes) ?>)</div>
                    <?php if (!empty($routes)): ?>
                      <div class="data-box mb-3">
                        <table class="table table-sm table-borderless mb-0" style="font-size:11px;">
                          <thead>
                            <tr style="border-bottom:1px solid #dee2e6;"><th>Iface</th><th>Destination</th><th>Gateway</th><th>Flags</th></tr>
                          </thead>
                          <tbody>
                            <?php foreach ($routes as $route): ?>
                              <tr>
                                <td><strong><?= esc($route['iface'] ?? $route['interface'] ?? '—') ?></strong></td>
                                <td><code><?= esc($route['destination'] ?? $route['dest'] ?? '—') ?></code></td>
                                <td><code><?= esc($route['gateway'] ?? '—') ?></code></td>
                                <td><span class="badge badge-secondary"><?= esc($route['flags'] ?? '—') ?></span></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php else: ?>
                      <div class="text-muted small py-2 text-center mb-3" style="background:#f8f9fa; border:1px dashed #dee2e6; border-radius:4px;">No active routing rules found</div>
                    <?php endif; ?>

                    <div class="row">
                      <div class="col-sm-6">
                        <div class="section-label mb-2"><i class="fas fa-sitemap mr-1"></i>ARP Cache (<?= count($arp) ?>)</div>
                        <?php if (!empty($arp)): ?>
                          <div class="data-box">
                            <?php foreach ($arp as $entry): ?>
                              <div style="border-bottom:1px solid #e9ecef; padding:3px 0; font-size:11px;">
                                <strong><?= esc($entry['ip'] ?? $entry['ip_address'] ?? '—') ?></strong><br>
                                <code class="text-muted"><?= esc($entry['mac'] ?? $entry['mac_address'] ?? '—') ?></code>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        <?php else: ?>
                          <div class="text-muted small py-2 text-center" style="background:#f8f9fa; border:1px dashed #dee2e6; border-radius:4px;">ARP table empty</div>
                        <?php endif; ?>
                      </div>
                      
                      <div class="col-sm-6">
                        <div class="section-label mb-2"><i class="fas fa-wifi mr-1"></i>WiFi Passpoint Configs (<?= count($passpoint) ?>)</div>
                        <?php if (!empty($passpoint)): ?>
                          <div class="data-box">
                            <?php foreach ($passpoint as $pp): ?>
                              <div class="mb-2 pb-2 border-bottom" style="font-size: 11px; border-color:#e9ecef !important;">
                                <?php if(is_array($pp)): ?>
                                  <strong><?= esc($pp['fqdn'] ?? $pp['provider_name'] ?? 'Passpoint') ?></strong>
                                  <?php if (!empty($pp['friendly_name'])): ?><div class="text-muted">Friendly: <?= esc($pp['friendly_name']) ?></div><?php endif; ?>
                                  <?php if (!empty($pp['credential_type'])): ?><span class="badge badge-light border">Auth: <?= esc($pp['credential_type']) ?></span><?php endif; ?>
                                <?php else: ?>
                                  <strong><?= esc($pp) ?></strong>
                                <?php endif; ?>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        <?php else: ?>
                          <div class="text-muted small py-2 text-center" style="background:#f8f9fa; border:1px dashed #dee2e6; border-radius:4px;">No Passpoint profiles</div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>

                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-3">
                  <small class="text-muted" style="font-size:10px;">Extracted: <?= $ts ?></small>
                  <button class="btn btn-sm btn-outline-danger delete-row py-0"
                    data-id="<?= $rid ?>"
                    data-url="<?= base_url('advanced/hardware/hardware_network/delete') ?>">
                    <i class="fas fa-trash mr-1"></i>Remove Snapshot
                  </button>
                </div>
              </div>
            </div>
          </div>

        <?php endforeach; ?>
        </div>

        <?php if (isset($pager)): ?>
          <div class="d-flex justify-content-end"><?= $pager->links('default', 'bootstrap5_full') ?></div>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>