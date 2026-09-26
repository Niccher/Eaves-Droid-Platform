<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
helper('coalesce');
$rows = coalesce_snapshots($rows, 'device_id');

// Helper to extract JSON fields safely
$parseJson = fn($v) => is_string($v) ? (json_decode($v, true) ?: []) : (is_array($v) ? $v : []);

function getMemVal(array $meminfo, string $key) {
    foreach ([$key, $key.'_kB', $key.'_kb', strwords($key)] as $k) {
        if (isset($meminfo[$k])) return (int)$meminfo[$k] * 1024;
    }
    return 0;
}
function strwords($str) { return strtolower($str); }

function fmtBytes(int $bytes): string {
    if ($bytes <= 0) return '0';
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 0) . ' KB';
    return $bytes . ' B';
}
?>

<style>
.proc-card      { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.proc-hero      { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 20px 22px; color: #fff; }
.proc-kv        { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.proc-kv:last-child { border-bottom: none; }
.proc-kv .pk    { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.proc-kv .pv    { font-weight: 700; color: #343a40; text-align: right; max-width: 65%; word-break: break-all; }
.proc-badge     { border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 700; }
.mem-usage-bar  { height: 8px; border-radius: 4px; background: #e9ecef; overflow: hidden; margin-top: 5px; }
.mem-usage-fill { height: 100%; border-radius: 4px; background: #17a2b8; }
.mono-log       { font-family: monospace; font-size: 11px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; max-height: 120px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; }
.iface-tbl      { width:100%; font-size:11px; border-collapse:collapse; }
.iface-tbl th   { background:#e9ecef; padding:4px 8px; font-size:10px; text-transform:uppercase; color:#6c757d; font-weight:700; border-bottom:2px solid #dee2e6; text-align:left; }
.iface-tbl td   { padding:4px 8px; border-bottom:1px solid #f0f0f0; font-family:monospace; font-size:11px; }
.conn-state     { border-radius:8px; padding:1px 6px; font-size:9px; font-weight:700; color:#fff; display:inline-block; }
.scroll-box     { max-height:220px; overflow-y:auto; border:1px solid #dee2e6; border-radius:4px; }
.section-label  { font-size: 10px; font-weight: 700; color: #6c757d; text-transform: uppercase; }
.mem-grid       { display:grid; grid-template-columns:1fr 1fr; gap:0; }
.mem-grid .proc-kv { padding:3px 6px; font-size:12px; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-terminal text-info mr-2"></i>Proc &amp; Kernel Diagnostics</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Active Snapshots: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">System core stats, memory tables, kernel configuration, and network interfaces</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>

      <!-- Callout matching Biometric/Storage pages -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-terminal mr-2"></i>Proc &amp; Kernel Security Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Forensic inspection of the Linux virtual file system (<code>/proc</code>). Correlating system uptime, memory allocation details, and virtual network adapters exposes rootkits or data tunneling channels.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Kernel &amp; Uptime:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Kernel Version:</b> Audits operating system builds to check vulnerability ranges.</li>
              <li><b>Uptime Specs:</b> Tracks device boot intervals and idle timing variables.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Memory allocation:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Active/Slab Mem:</b> Tracks kernel memory pages to identify leakage or cache injection.</li>
              <li><b>CmaFree:</b> Verifies physical allocation bounds for target device cameras.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Network interfaces:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Interface Arrays:</b> Audits active network adapters (wlan0, rmnet, lo).</li>
              <li><b>Connection Traces:</b> Lists socket configurations and active port listeners.</li>
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
          <i class="fas fa-terminal fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Proc Data Detected</h4>
          <p class="text-muted">Proc logs will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $kernel = $r['version'] ?? '—';
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          $meminfo = $parseJson($r['meminfo_json'] ?? '{}');
          $cpuinfo = $parseJson($r['cpuinfo_json'] ?? '[]');
          $uptime  = $parseJson($r['uptime_json'] ?? '{}');
          $stat    = $parseJson($r['stat_json'] ?? '{}');
          $netIfs  = $parseJson($r['net_interfaces_json'] ?? '[]');
          $netConns= $parseJson($r['net_connections_json'] ?? '[]');
          
          // Core memory values
          $memTotal = getMemVal($meminfo, 'MemTotal');
          $memFree  = getMemVal($meminfo, 'MemFree');
          $memAvail = getMemVal($meminfo, 'MemAvailable');
          $active   = getMemVal($meminfo, 'Active');
          $cached   = getMemVal($meminfo, 'Cached');
          $slab     = getMemVal($meminfo, 'Slab');
          $cma      = getMemVal($meminfo, 'CmaFree');
          $swapFree = getMemVal($meminfo, 'SwapFree');
          $swapTotal= getMemVal($meminfo, 'SwapTotal');
          $buffers  = getMemVal($meminfo, 'Buffers');
          $dirty    = getMemVal($meminfo, 'Dirty');
          $writeback= getMemVal($meminfo, 'Writeback');
          $anonPages= getMemVal($meminfo, 'AnonPages');
          $mapped   = getMemVal($meminfo, 'Mapped');
          $shmem    = getMemVal($meminfo, 'Shmem');
          $kernStack= getMemVal($meminfo, 'KernelStack');
          $pageTbl  = getMemVal($meminfo, 'PageTables');
          $vmalloc  = getMemVal($meminfo, 'VmallocUsed');
          
          $memUsed = $memTotal - $memFree;
          $memPct = $memTotal > 0 ? round(($memUsed / $memTotal) * 100) : 0;
          
          // CPU count and details
          $cpuCount = is_array($cpuinfo) ? count($cpuinfo) : 1;
          
          // Uptime
          $up = isset($uptime['uptime']) ? round((float)$uptime['uptime'] / 3600, 1) . ' hrs' : '—';
          $idle = isset($uptime['idle']) ? round((float)$uptime['idle'] / 3600, 1) . ' hrs' : '—';
          ?>

          <div class="card proc-card mb-4">
            <!-- Hero -->
            <div class="proc-hero">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                  <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge badge-light proc-badge"><i class="fas fa-microchip mr-1"></i><?= $cpuCount ?> Cores</span>
                    <span class="badge badge-info proc-badge">Uptime: <?= $up ?></span>
                    <span class="badge badge-secondary proc-badge">Idle: <?= $idle ?></span>
                  </div>
                </div>
                <div class="text-right">
                  <span class="badge badge-success proc-badge"><i class="fas fa-network-wired mr-1"></i><?= count($netIfs) ?> Interfaces</span>
                  <span class="badge badge-primary proc-badge ml-1"><i class="fas fa-plug mr-1"></i><?= count($netConns) ?> Sockets</span>
                </div>
              </div>
              <div class="font-weight-bold" style="font-size: 15px; letter-spacing:0.3px; line-height: 1.2;">
                <?= esc(mb_substr($kernel, 0, 100)) ?><?= strlen($kernel) > 100 ? '...' : '' ?>
              </div>
              <small class="d-block mt-1" style="opacity: 0.85;">Kernel Release String</small>
            </div>

            <div class="card-body">
              <div class="row">

                <!-- Col 1: Memory -->
                <div class="col-md-4 mb-3">
                  <div class="section-label mb-2"><i class="fas fa-memory mr-1"></i>Core Memory Pages</div>
                  
                  <div class="mb-3">
                    <div class="d-flex justify-content-between" style="font-size: 12px; font-weight: 700;">
                      <span class="text-muted">Available: <strong><?= fmtBytes($memAvail) ?></strong></span>
                      <span class="text-muted">Total: <strong><?= fmtBytes($memTotal) ?></strong></span>
                    </div>
                    <div class="mem-usage-bar">
                      <div class="mem-usage-fill" style="width: <?= $memPct ?>%;"></div>
                    </div>
                    <div style="font-size:11px;color:#6c757d;" class="mt-1"><?= $memPct ?>% used · <?= fmtBytes($memFree) ?> free</div>
                  </div>

                  <div class="proc-kv"><span class="pk">Active Memory</span><span class="pv"><?= fmtBytes($active) ?></span></div>
                  <div class="proc-kv"><span class="pk">Cached Buffer</span><span class="pv"><?= fmtBytes($cached) ?></span></div>
                  <div class="proc-kv"><span class="pk">Slab Overhead</span><span class="pv"><?= fmtBytes($slab) ?></span></div>
                  <?php if ($swapTotal > 0): ?>
                  <div class="proc-kv"><span class="pk">Swap</span><span class="pv"><?= fmtBytes($swapFree) ?> / <?= fmtBytes($swapTotal) ?></span></div>
                  <?php else: ?>
                  <div class="proc-kv"><span class="pk">Swap Free</span><span class="pv"><?= fmtBytes($swapFree) ?></span></div>
                  <?php endif; ?>
                  <?php if ($cma > 0): ?>
                  <div class="proc-kv"><span class="pk">CMA Free Buffer</span><span class="pv"><?= fmtBytes($cma) ?></span></div>
                  <?php endif; ?>

                  <!-- Extended memory stats -->
                  <div class="section-label mt-3 mb-1"><i class="fas fa-th mr-1"></i>Extended Memory Map</div>
                  <div class="mem-grid">
                    <?php if ($buffers > 0): ?><div class="proc-kv"><span class="pk">Buffers</span><span class="pv"><?= fmtBytes($buffers) ?></span></div><?php endif; ?>
                    <?php if ($dirty > 0): ?><div class="proc-kv"><span class="pk">Dirty</span><span class="pv"><?= fmtBytes($dirty) ?></span></div><?php endif; ?>
                    <?php if ($writeback > 0): ?><div class="proc-kv"><span class="pk">Writeback</span><span class="pv"><?= fmtBytes($writeback) ?></span></div><?php endif; ?>
                    <?php if ($anonPages > 0): ?><div class="proc-kv"><span class="pk">AnonPages</span><span class="pv"><?= fmtBytes($anonPages) ?></span></div><?php endif; ?>
                    <?php if ($mapped > 0): ?><div class="proc-kv"><span class="pk">Mapped</span><span class="pv"><?= fmtBytes($mapped) ?></span></div><?php endif; ?>
                    <?php if ($shmem > 0): ?><div class="proc-kv"><span class="pk">Shmem</span><span class="pv"><?= fmtBytes($shmem) ?></span></div><?php endif; ?>
                    <?php if ($kernStack > 0): ?><div class="proc-kv"><span class="pk">KernelStack</span><span class="pv"><?= fmtBytes($kernStack) ?></span></div><?php endif; ?>
                    <?php if ($pageTbl > 0): ?><div class="proc-kv"><span class="pk">PageTables</span><span class="pv"><?= fmtBytes($pageTbl) ?></span></div><?php endif; ?>
                    <?php if ($vmalloc > 0): ?><div class="proc-kv"><span class="pk">VmallocUsed</span><span class="pv"><?= fmtBytes($vmalloc) ?></span></div><?php endif; ?>
                  </div>
                </div>

                <!-- Col 2: CPU Cores -->
                <div class="col-md-4 mb-3">
                  <div class="section-label mb-2"><i class="fas fa-microchip mr-1"></i>CPU Core Architecture (<?= $cpuCount ?>)</div>
                  <?php if (!empty($cpuinfo) && is_array($cpuinfo)): ?>
                  <div class="scroll-box">
                    <table class="iface-tbl">
                      <thead><tr><th>#</th><th>Model</th><th>MHz</th><th>BogoMIPS</th></tr></thead>
                      <tbody>
                        <?php foreach ($cpuinfo as $ci):
                          $coreIdx = $ci['processor'] ?? $ci['core_id'] ?? '?';
                          $coreModel = $ci['model_name'] ?? $ci['model name'] ?? $ci['Processor'] ?? '—';
                          $coreMhz = $ci['cpu_MHz'] ?? $ci['cpu MHz'] ?? $ci['clock'] ?? '—';
                          $coreBogo = $ci['bogomips'] ?? $ci['BogoMIPS'] ?? '—';
                        ?>
                        <tr>
                          <td class="text-center font-weight-bold"><?= esc($coreIdx) ?></td>
                          <td style="font-size:10px;max-width:180px;" class="text-truncate" title="<?= esc($coreModel) ?>"><?= esc(mb_substr($coreModel, 0, 40)) ?></td>
                          <td class="text-right"><?= esc($coreMhz) ?></td>
                          <td class="text-right"><?= esc($coreBogo) ?></td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                  <?php else: ?>
                  <div class="text-center text-muted py-3" style="font-size:12px;">No CPU core data available</div>
                  <?php endif; ?>

                  <!-- Kernel -->
                  <div class="section-label mt-3 mb-1"><i class="fas fa-terminal mr-1"></i>Kernel Release</div>
                  <div class="mono-log"><?= esc($kernel) ?></div>

                  <div class="proc-kv mt-2"><span class="pk">Extracted</span><span class="pv" style="font-size:11px;"><?= $ts ?></span></div>
                </div>

                <!-- Col 3: Network -->
                <div class="col-md-4 mb-3">
                  <div class="section-label mb-2"><i class="fas fa-network-wired mr-1"></i>Network Interfaces (<?= count($netIfs) ?>)</div>
                  <?php if (!empty($netIfs)): ?>
                  <div class="scroll-box mb-3">
                    <table class="iface-tbl">
                      <thead><tr><th>Interface</th><th>IP Address</th><th>MAC</th></tr></thead>
                      <tbody>
                        <?php foreach ($netIfs as $iface):
                          $ifName = $iface['name'] ?? $iface['interface'] ?? $iface['iface'] ?? '?';
                          $ifIp   = $iface['ip_address'] ?? $iface['ipAddress'] ?? $iface['address'] ?? $iface['ip'] ?? '—';
                          $ifMac  = $iface['mac_address'] ?? $iface['macAddress'] ?? $iface['hwaddr'] ?? $iface['mac'] ?? '—';
                          $ifFlags= $iface['flags'] ?? '';
                        ?>
                        <tr title="<?= esc(is_string($ifFlags) ? $ifFlags : json_encode($ifFlags)) ?>">
                          <td class="font-weight-bold"><?= esc($ifName) ?></td>
                          <td><?= esc(is_string($ifIp) ? $ifIp : json_encode($ifIp)) ?></td>
                          <td style="font-size:10px;"><?= esc($ifMac) ?></td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                  <?php else: ?>
                  <div class="text-center text-muted py-2" style="font-size:12px;">No interface data</div>
                  <?php endif; ?>

                  <div class="section-label mb-2"><i class="fas fa-plug mr-1"></i>Active Connections (<?= count($netConns) ?>)</div>
                  <?php if (!empty($netConns)): ?>
                  <details>
                    <summary style="cursor:pointer;font-size:11px;font-weight:700;color:#6c757d;">Show <?= count($netConns) ?> socket connections</summary>
                    <div class="scroll-box mt-2">
                      <table class="iface-tbl">
                        <thead><tr><th>Proto</th><th>Local</th><th>Remote</th><th>State</th></tr></thead>
                        <tbody>
                          <?php foreach ($netConns as $conn):
                            $proto = $conn['protocol'] ?? $conn['proto'] ?? $conn['type'] ?? '?';
                            $lAddr = ($conn['local_address'] ?? $conn['localAddress'] ?? $conn['local_ip'] ?? '*');
                            $lPort = $conn['local_port'] ?? $conn['localPort'] ?? '';
                            $rAddr = ($conn['remote_address'] ?? $conn['remoteAddress'] ?? $conn['remote_ip'] ?? '*');
                            $rPort = $conn['remote_port'] ?? $conn['remotePort'] ?? '';
                            $state = $conn['state'] ?? $conn['status'] ?? '—';
                            $stateUpper = strtoupper($state);
                            $stColor = match(true) {
                                str_contains($stateUpper, 'ESTABLISHED') => '#28a745',
                                str_contains($stateUpper, 'LISTEN') => '#007bff',
                                str_contains($stateUpper, 'TIME_WAIT') => '#ffc107',
                                str_contains($stateUpper, 'CLOSE') => '#dc3545',
                                str_contains($stateUpper, 'SYN') => '#fd7e14',
                                default => '#6c757d',
                            };
                          ?>
                          <tr>
                            <td><?= esc($proto) ?></td>
                            <td style="font-size:10px;"><?= esc($lAddr) ?><?= $lPort ? ':'.esc($lPort) : '' ?></td>
                            <td style="font-size:10px;"><?= esc($rAddr) ?><?= $rPort ? ':'.esc($rPort) : '' ?></td>
                            <td><span class="conn-state" style="background:<?= $stColor ?>;"><?= esc($state) ?></span></td>
                          </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </details>
                  <?php else: ?>
                  <div class="text-center text-muted py-2" style="font-size:12px;">No connection data</div>
                  <?php endif; ?>
                </div>

              </div>

              <!-- Footer -->
              <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                <small class="text-muted">Row ID: <?= $rid ?> · Device: <?= esc($r['device_id'] ?? '—') ?></small>
                <button class="btn btn-sm btn-outline-danger delete-row py-0"
                  data-id="<?= $rid ?>"
                  data-url="<?= base_url('advanced/hardware/proc_info/delete') ?>">
                  <i class="fas fa-trash mr-1"></i>Remove
                </button>
              </div>
            </div>
          </div>

        <?php endforeach; ?>

        <?php if (isset($pager)): ?>
          <div class="d-flex justify-content-end"><?= $pager->links('default', 'bootstrap5_full') ?></div>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
