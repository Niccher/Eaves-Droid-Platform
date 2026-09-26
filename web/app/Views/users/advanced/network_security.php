<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
helper('coalesce');
$rows = coalesce_snapshots($rows, 'device_id', ['dns_config_json', 'vpn_config_json']);

function parseNetJson($val): array {
    if (empty($val)) return [];
    if (is_array($val)) return $val;
    $decoded = json_decode($val, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
    return [];
}
?>

<style>
.net-card     { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.net-hero     { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; position: relative; }
.net-hero::after { content: ''; position: absolute; right: 15px; bottom: 15px; font-family: "Font Awesome 5 Free"; font-weight: 900; content: "\f3ed"; font-size: 54px; opacity: 0.05; }
.net-kv       { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.net-kv:last-child { border-bottom: none; }
.net-kv .nk   { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.net-kv .nv   { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.pill-badge   { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; margin: 2px; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-shield-alt text-info mr-2"></i>Network Security</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">DNS configuration, private DNS resolvers, active VPN tunnels, and interface routing</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>
      
      <!-- Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-network-wired mr-2"></i>DNS &amp; VPN Security Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Auditing name servers and tunnel interfaces prevents DNS hijacking attacks and validates encrypting network gateway paths.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-6 border-right">
            <b class="d-block mb-1">Name Resolution:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>DNS Servers:</b> Validates active name servers against spoofed/rogue IPs.</li>
              <li><b>Private DNS Mode:</b> Checks if DNS over TLS (DoT) or HTTPS (DoH) is active.</li>
            </ul>
          </div>
          <div class="col-md-6 pl-md-3">
            <b class="d-block mb-1">Enclave Tunnels:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>VPN Interfaces:</b> Verifies virtual interfaces (e.g. `tun0`, `ppp0`).</li>
              <li><b>Lockdown/Always-On:</b> Detects leak protection rules.</li>
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
          <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Network Security Data</h4>
          <p class="text-muted">Network configurations will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <div class="row">
        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $dns = parseNetJson($r['dns_config_json'] ?? '');
          $vpn = parseNetJson($r['vpn_config_json'] ?? '');
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          $dnsServers = $dns['servers'] ?? ($dns['dns_servers'] ?? []);
          $privateDnsMode = $dns['private_dns_mode'] ?? ($dns['mode'] ?? 'off');
          $dnsInterface = $dns['interface_name'] ?? ($dns['interface'] ?? '—');
          
          $vpnIsActive = !empty($vpn['is_active']) || !empty($vpn['vpn_active']);
          $vpnInterface = $vpn['interface_name'] ?? ($vpn['interface'] ?? '—');
          $vpnServer = $vpn['server_address'] ?? ($vpn['server'] ?? '—');
          $vpnLockdown = !empty($vpn['lockdown']) || !empty($vpn['is_lockdown']);
          ?>
          <div class="col-md-6 mb-4">
            <div class="net-card h-100">
              
              <!-- Hero section -->
              <div class="net-hero" style="background: <?= $vpnIsActive ? 'linear-gradient(135deg, #28a745 0%, #208739 100%)' : 'linear-gradient(135deg, #6c757d 0%, #495057 100%)' ?>;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="badge badge-light text-dark font-weight-bold" style="border-radius:12px; padding:3px 9px;">
                    <i class="fas fa-network-wired mr-1"></i>Device: <?= esc($r['device_id']) ?>
                  </span>
                  <div>
                    <?php if ($vpnIsActive): ?>
                      <span class="badge badge-success font-weight-bold"><i class="fas fa-lock mr-1"></i>TUNNEL SECURED</span>
                    <?php else: ?>
                      <span class="badge badge-secondary font-weight-bold">UNPROTECTED</span>
                    <?php endif; ?>
                  </div>
                </div>
                <h5 class="font-weight-bold mb-1 text-truncate">DNS mode: <?= esc(strtoupper($privateDnsMode)) ?></h5>
                <small class="d-block" style="opacity:0.75; font-family:monospace;">Active Interfaces: DNS [<?= esc($dnsInterface) ?>] / VPN [<?= esc($vpnInterface) ?>]</small>
              </div>

              <!-- Details section -->
              <div class="p-3">
                
                <h6 class="font-weight-bold text-secondary mb-2" style="font-size: 11px; text-transform: uppercase;">DNS Name Resolution</h6>
                <div class="net-kv">
                  <span class="nk">DNS Interface</span>
                  <span class="av"><code><?= esc($dnsInterface) ?></code></span>
                </div>
                <div class="net-kv">
                  <span class="nk">Private DNS Mode</span>
                  <span class="av"><span class="badge badge-<?= $privateDnsMode !== 'off' ? 'success' : 'warning' ?>"><?= esc($privateDnsMode) ?></span></span>
                </div>
                <?php if (!empty($dnsServers)): ?>
                <div class="mt-2 mb-3">
                  <span class="nk d-block mb-1" style="font-size:10px; font-weight:700; color:#6c757d;">DNS Servers (<?= count($dnsServers) ?>)</span>
                  <div>
                    <?php foreach ($dnsServers as $server): ?>
                      <span class="pill-badge bg-light border text-dark font-weight-normal"><code><?= esc($server) ?></code></span>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <h6 class="font-weight-bold text-secondary mt-3 mb-2" style="font-size: 11px; text-transform: uppercase;">VPN Interface Settings</h6>
                <div class="net-kv">
                  <span class="nk">VPN Interface</span>
                  <span class="av"><code><?= esc($vpnInterface) ?></code></span>
                </div>
                <div class="net-kv">
                  <span class="nk">Server Endpoint</span>
                  <span class="av"><code><?= esc($vpnServer) ?></code></span>
                </div>
                <div class="net-kv">
                  <span class="nk">Lockdown Mode</span>
                  <span class="av"><?= $vpnLockdown ? '<span class="badge badge-danger">Active (Always-On)</span>' : '<span class="badge badge-secondary">Disabled</span>' ?></span>
                </div>

                <div class="net-kv mt-3 border-top pt-2">
                  <span class="nk">Extracted</span>
                  <span class="sv" style="font-size:11px;"><?= $ts ?></span>
                </div>

              </div>

            </div>
          </div>
        <?php endforeach; ?>
        </div>

      <?php endif; ?>
    </div>
  </section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
