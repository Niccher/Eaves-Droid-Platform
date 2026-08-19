<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'device_id',
    ['vpn_active', 'proxy_active', 'user_ca_certs_json', 'system_ca_certs_json', 'vpn_config_json', 'device_admin_apps_json', 'dns_config_json', 'open_ports_json']
);

$snapshot = null;
if (!empty($rows)) {
    $snapshot = $rows[0];
}

function parseJsonArray($val): array {
    if (empty($val)) return [];
    if (is_array($val)) return $val;
    $decoded = json_decode($val, true);
    return is_array($decoded) ? $decoded : [];
}
?>

<style>
.sec-card     { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.sec-hero     { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px; padding: 20px; color: #fff; position: relative; }
.sec-hero::after { content: ''; position: absolute; right: 15px; bottom: 15px; font-family: "Font Awesome 5 Free"; font-weight: 900; content: "\f3ed"; font-size: 54px; opacity: 0.08; }
.sec-kv       { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.sec-kv:last-child { border-bottom: none; }
.sec-kv .sk   { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.sec-kv .sv   { font-weight: 700; color: #343a40; text-align: right; max-width: 65%; word-break: break-all; }
.pill-badge   { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; margin: 2px; }
.cert-box     { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 10px; margin-bottom: 8px; font-size: 12px; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout & Header -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-user-shield text-info mr-2"></i>Security Audit</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total Snapshots: <b><?= $total ?? 0 ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">VPN &amp; Proxy status, User/System CA certificates, open ports, device admin privileges, and DNS configuration</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>
      
      <!-- Educational Callout -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-shield-alt mr-2"></i>Security Audit &amp; Threat Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Auditing OS-level network tunnels, trusted authority certificates, device administrator policies, and active port bindings for vulnerability management.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Network Tunnels &amp; Proxies:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>VPN &amp; Proxy Interception:</b> Detects active network routing or interception proxies.</li>
              <li><b>DNS Configurations:</b> Audits system DNS servers and custom resolution vectors.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Trust &amp; Certificates:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>User CA Certificates:</b> Identifies custom user-installed Root CAs (MITM threat vector).</li>
              <li><b>System Authorities:</b> Maps OS pre-installed certificate authorities.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Privileges &amp; Ports:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Device Admin Apps:</b> Lists applications with elevated administrative system controls.</li>
              <li><b>Open Listening Ports:</b> Identifies background services bound to local/remote network ports.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <?php if (empty($rows) || $snapshot === null): ?>
        <div class="text-center py-5">
          <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Security Audit Data</h4>
          <p class="text-muted">Security audit records will appear here once extracted from connected devices.</p>
        </div>
      <?php else:
        $vpnActive    = !empty($snapshot['vpn_active']);
        $proxyActive  = !empty($snapshot['proxy_active']);
        $userCerts    = parseJsonArray($snapshot['user_ca_certs_json'] ?? $snapshot['user_ca_certs'] ?? []);
        $systemCerts  = parseJsonArray($snapshot['system_ca_certs_json'] ?? $snapshot['system_ca_certs'] ?? []);
        $adminApps    = parseJsonArray($snapshot['device_admin_apps_json'] ?? $snapshot['device_admin_apps'] ?? []);
        $openPorts    = parseJsonArray($snapshot['open_ports_json'] ?? $snapshot['open_ports'] ?? []);
        $dnsConfig    = parseJsonArray($snapshot['dns_config_json'] ?? $snapshot['dns_config'] ?? []);
        $vpnConfig    = parseJsonArray($snapshot['vpn_config_json'] ?? $snapshot['vpn_config'] ?? []);
        $ts           = !empty($snapshot['extracted_at']) ? format_timestamp_display((int)$snapshot['extracted_at']) : '—';
      ?>

        <!-- Hero Security Overview -->
        <div class="card sec-card mb-4">
          <div class="card-body p-0">
            <div class="sec-hero">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-center">
                  <span class="badge badge-info text-white font-weight-bold mr-2" style="border-radius:12px; padding:5px 12px; font-size:12px;">
                    <i class="fas fa-mobile-alt mr-1"></i>Device: <?= esc($snapshot['device_id'] ?? 'Default') ?>
                  </span>
                  <span class="text-white-50 style="font-size:12px;""><i class="fas fa-clock mr-1"></i>Last Audit: <?= $ts ?></span>
                </div>
                <div>
                  <?php if ($vpnActive): ?>
                    <span class="badge badge-warning font-weight-bold px-2 py-1"><i class="fas fa-lock mr-1"></i>VPN TUNNEL ACTIVE</span>
                  <?php else: ?>
                    <span class="badge badge-secondary font-weight-bold px-2 py-1"><i class="fas fa-unlock mr-1"></i>VPN INACTIVE</span>
                  <?php endif; ?>

                  <?php if ($proxyActive): ?>
                    <span class="badge badge-danger font-weight-bold px-2 py-1 ml-1"><i class="fas fa-sitemap mr-1"></i>PROXY ACTIVE</span>
                  <?php else: ?>
                    <span class="badge badge-success font-weight-bold px-2 py-1 ml-1"><i class="fas fa-check-circle mr-1"></i>DIRECT CONNECTION</span>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Quick Metrics Grid -->
              <div class="row text-center mt-3">
                <div class="col-6 col-md-3 border-right border-secondary mb-2 mb-md-0">
                  <div class="h3 font-weight-bold mb-0 text-warning"><?= count($userCerts) ?></div>
                  <small class="text-white-50 text-uppercase font-weight-bold" style="font-size:10px;">User CA Certs</small>
                </div>
                <div class="col-6 col-md-3 border-right border-secondary mb-2 mb-md-0">
                  <div class="h3 font-weight-bold mb-0 text-info"><?= count($adminApps) ?></div>
                  <small class="text-white-50 text-uppercase font-weight-bold" style="font-size:10px;">Device Admins</small>
                </div>
                <div class="col-6 col-md-3 border-right border-secondary">
                  <div class="h3 font-weight-bold mb-0 text-success"><?= count($openPorts) ?></div>
                  <small class="text-white-50 text-uppercase font-weight-bold" style="font-size:10px;">Open Ports</small>
                </div>
                <div class="col-6 col-md-3">
                  <div class="h3 font-weight-bold mb-0 text-light"><?= count($systemCerts) ?></div>
                  <small class="text-white-50 text-uppercase font-weight-bold" style="font-size:10px;">System CA Certs</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- Card 1: Network & Security Enforcements -->
          <div class="col-md-6 mb-4">
            <div class="card sec-card h-100">
              <div class="card-header"><h3 class="card-title font-weight-bold text-secondary"><i class="fas fa-network-wired mr-2"></i>Network Tunnels &amp; Configuration</h3></div>
              <div class="card-body">
                <div class="sec-kv">
                  <span class="sk">VPN Tunnel State</span>
                  <span class="sv"><?= $vpnActive ? '<span class="badge badge-warning">Active</span>' : '<span class="badge badge-secondary">Disabled</span>' ?></span>
                </div>
                <div class="sec-kv">
                  <span class="sk">Proxy Interception</span>
                  <span class="sv"><?= $proxyActive ? '<span class="badge badge-danger">Detected</span>' : '<span class="badge badge-success">None</span>' ?></span>
                </div>

                <!-- DNS Configurations -->
                <div class="mt-3">
                  <span class="sk d-block mb-2">DNS Server Configuration</span>
                  <?php 
                    $servers = $dnsConfig['servers'] ?? (is_array($dnsConfig) ? $dnsConfig : []);
                    if (!empty($servers) && is_array($servers)): 
                  ?>
                    <div class="d-flex flex-wrap">
                      <?php foreach ($servers as $srv): ?>
                        <span class="pill-badge bg-light border text-dark font-weight-bold"><i class="fas fa-server text-info mr-1"></i><?= esc(is_array($srv) ? ($srv['address'] ?? json_encode($srv)) : $srv) ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <p class="text-muted small mb-0">No custom DNS servers configured.</p>
                  <?php endif; ?>
                </div>

                <!-- VPN Configurations -->
                <div class="mt-3">
                  <span class="sk d-block mb-2">Active VPN Profiles</span>
                  <?php if (!empty($vpnConfig) && is_array($vpnConfig)): ?>
                    <div class="d-flex flex-wrap">
                      <?php foreach ($vpnConfig as $vpn): ?>
                        <span class="pill-badge bg-warning text-dark font-weight-bold"><i class="fas fa-shield-alt mr-1"></i><?= esc(is_array($vpn) ? ($vpn['name'] ?? $vpn['session'] ?? json_encode($vpn)) : $vpn) ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <p class="text-muted small mb-0">No custom VPN profiles established.</p>
                  <?php endif; ?>
                </div>

                <!-- Open Listening Ports -->
                <div class="mt-3">
                  <span class="sk d-block mb-2">Open Network Ports</span>
                  <?php if (!empty($openPorts) && is_array($openPorts)): ?>
                    <div class="d-flex flex-wrap">
                      <?php foreach ($openPorts as $port): ?>
                        <span class="pill-badge bg-danger text-white font-weight-bold"><i class="fas fa-door-open mr-1"></i>Port <?= esc(is_array($port) ? ($port['port'] ?? json_encode($port)) : $port) ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <p class="text-muted small mb-0">No listening network ports detected.</p>
                  <?php endif; ?>
                </div>

              </div>
            </div>
          </div>

          <!-- Card 2: Device Admin Apps & Elevation -->
          <div class="col-md-6 mb-4">
            <div class="card sec-card h-100">
              <div class="card-header">
                <h3 class="card-title font-weight-bold text-secondary">
                  <i class="fas fa-user-shield mr-2"></i>Device Admin Privilege Apps
                  <span class="badge badge-info ml-1"><?= count($adminApps) ?></span>
                </h3>
              </div>
              <div class="card-body">
                <?php if (empty($adminApps)): ?>
                  <div class="text-center py-4">
                    <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                    <p class="text-muted small mb-0">No third-party apps holding Device Administrator privilege.</p>
                  </div>
                <?php else: ?>
                  <div style="max-height: 280px; overflow-y: auto;">
                    <?php foreach ($adminApps as $app): 
                      $appName = is_array($app) ? ($app['label'] ?? $app['package'] ?? 'Unknown Admin App') : $app;
                      $pkg = is_array($app) ? ($app['package'] ?? '') : '';
                    ?>
                      <div class="cert-box">
                        <div class="font-weight-bold text-dark"><i class="fas fa-shield-alt text-warning mr-1"></i><?= esc($appName) ?></div>
                        <?php if ($pkg): ?><div class="text-muted" style="font-size:11px; font-family:monospace;"><?= esc($pkg) ?></div><?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Row 2: Certificates Telemetry -->
        <div class="row">
          <!-- User CA Certificates (MITM Vector) -->
          <div class="col-md-6 mb-4">
            <div class="card sec-card h-100">
              <div class="card-header">
                <h3 class="card-title font-weight-bold text-secondary">
                  <i class="fas fa-certificate text-warning mr-2"></i>User CA Certificates (Trust Store)
                  <span class="badge badge-warning ml-1"><?= count($userCerts) ?></span>
                </h3>
              </div>
              <div class="card-body">
                <?php if (empty($userCerts)): ?>
                  <div class="text-center py-4">
                    <i class="fas fa-lock text-success fa-2x mb-2"></i>
                    <p class="text-muted small mb-0">No custom User CA certificates installed (Low MITM risk).</p>
                  </div>
                <?php else: ?>
                  <div style="max-height: 280px; overflow-y: auto;">
                    <?php foreach ($userCerts as $cert): 
                      $subject = is_array($cert) ? ($cert['subject'] ?? $cert['name'] ?? json_encode($cert)) : $cert;
                      $issuer  = is_array($cert) ? ($cert['issuer'] ?? '') : '';
                    ?>
                      <div class="cert-box" style="border-left: 3px solid #ffc107;">
                        <div class="font-weight-bold text-dark"><i class="fas fa-key text-warning mr-1"></i><?= esc($subject) ?></div>
                        <?php if ($issuer): ?><div class="text-muted" style="font-size:11px;">Issuer: <?= esc($issuer) ?></div><?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- System CA Certificates -->
          <div class="col-md-6 mb-4">
            <div class="card sec-card h-100">
              <div class="card-header">
                <h3 class="card-title font-weight-bold text-secondary">
                  <i class="fas fa-university text-info mr-2"></i>System Root CA Certificates
                  <span class="badge badge-secondary ml-1"><?= count($systemCerts) ?></span>
                </h3>
              </div>
              <div class="card-body">
                <?php if (empty($systemCerts)): ?>
                  <p class="text-muted small mb-0">No system root CA certificate telemetry captured.</p>
                <?php else: ?>
                  <a class="font-weight-bold text-info d-block mb-2" style="font-size:12px;" data-toggle="collapse" href="#system-certs-list">
                    <i class="fas fa-eye mr-1"></i>Toggle System Root Authorities (<?= count($systemCerts) ?>)
                  </a>
                  <div id="system-certs-list" class="collapse" style="max-height: 240px; overflow-y: auto;">
                    <?php foreach ($systemCerts as $cert): 
                      $subject = is_array($cert) ? ($cert['subject'] ?? $cert['name'] ?? json_encode($cert)) : $cert;
                    ?>
                      <div class="cert-box">
                        <div class="text-dark font-weight-bold" style="font-size:11px;"><?= esc($subject) ?></div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
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

