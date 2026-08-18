<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
helper('coalesce');
$rows = coalesce_snapshots($rows, 'device_id');
?>

<style>
.proc-snap-card { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.proc-snap-hero { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; }
.proc-snap-kv   { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.proc-snap-kv:last-child { border-bottom: none; }
.proc-snap-kv .snk { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.proc-snap-kv .snv { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.proc-badge     { border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 700; }
.list-wrapper   { max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 6px; background: #f8f9fa; }
.list-item-row  { display: flex; align-items: center; justify-content: space-between; padding: 6px 12px; border-bottom: 1px solid #e9ecef; font-size: 12.5px; }
.list-item-row:last-child { border-bottom: none; }
.pkg-badge      { font-size: 9.5px; padding: 2px 6px; background: #17a2b8; color: #fff; border-radius: 4px; font-family: monospace; }
.stat-mini      { border-radius:6px; padding:10px 14px; text-align:center; flex:1; min-width:120px; }
.stat-mini .sm-v { font-size:22px; font-weight:900; }
.stat-mini .sm-l { font-size:10px; text-transform:uppercase; font-weight:600; opacity:.7; }
.imp-bar        { height:6px; border-radius:3px; overflow:hidden; display:flex; margin:8px 0; }
.imp-bar div    { height:100%; transition:width .3s; }
.fg-dot         { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:4px; vertical-align:middle; }
.pkg-pill       { display:inline-block; background:#e9ecef; border-radius:6px; padding:0 5px; font-size:9px; font-family:monospace; color:#495057; margin:1px; }
.lru-rank       { font-size:9px; color:#adb5bd; font-weight:600; margin-left:6px; }
.section-label  { font-size: 10px; font-weight: 700; color: #6c757d; text-transform: uppercase; }
</style>

<div class="content-wrapper">
  <!-- Educational Callout -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
          <div class="d-flex align-items-center flex-wrap">
            <h1 class="h2 mb-0 mr-3"><i class="fas fa-tasks text-info mr-2"></i>Running Processes</h1>
            <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Snapshots: <b><?= count($rows) ?></b></span>
          </div>
          <p class="text-muted mt-1 mb-0">System process hierarchy, running application task trees, and active service threads</p>
        </div>
        <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
      </div>

      <!-- Callout matching Biometric/Storage pages -->
      <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
        <h5 class="font-weight-bold text-info"><i class="fas fa-tasks mr-2"></i>Process &amp; Task Security Telemetry</h5>
        <p class="text-secondary mb-2" style="font-size:14px;">Forensic mapping of active Linux execution trees. Checking application Importance classifications and monitoring background service thread configurations is essential to locate hidden keyloggers or crypto-miners.</p>
        <div class="row" style="font-size:12px;">
          <div class="col-md-4 border-right">
            <b class="d-block mb-1">Process Hierarchy:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Importance States:</b> Flags foreground apps (Foreg/Visible) vs background threads.</li>
              <li><b>Process Bounds:</b> Monitors LRU ranking indicators for RAM management.</li>
            </ul>
          </div>
          <div class="col-md-4 border-right pl-md-3">
            <b class="d-block mb-1">Active Services:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Service Classes:</b> Audits running service subclasses attached to app packages.</li>
              <li><b>Life Cycle:</b> Monitors uptime duration parameters since service startup.</li>
            </ul>
          </div>
          <div class="col-md-4 pl-md-3">
            <b class="d-block mb-1">Threat Flags:</b>
            <ul class="pl-3 mb-0 text-muted">
              <li><b>Crash Frequency:</b> Identifies services with high crash rates indicating memory injection.</li>
              <li><b>Package Lists:</b> Resolves package IDs associated with running tasks.</li>
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
          <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
          <h4 class="text-secondary">No Process Data Detected</h4>
          <p class="text-muted">Process snapshots will appear here once extracted.</p>
        </div>
      <?php else: ?>

        <?php foreach ($rows as $r):
          $rid = $r['id'] ?? 0;
          $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
          
          $details  = $r['process_details'] ?? [];
          $services = $r['services'] ?? [];
          if (is_string($details)) $details = json_decode($details, true) ?: [];
          if (is_string($services)) $services = json_decode($services, true) ?: [];
          $procCount = count($details);
          $svcCount  = count($services);

          // Compute stats
          $fgProcs = 0; $bgProcs = 0; $fgSvcs = 0; $totalCrashes = 0;
          $impDist = ['fg'=>0, 'visible'=>0, 'service'=>0, 'bg'=>0, 'empty'=>0];
          foreach ($details as $d) {
              $imp = (int)($d['importance'] ?? 999);
              if ($imp <= 100) { $fgProcs++; $impDist['fg']++; }
              elseif ($imp <= 200) { $fgProcs++; $impDist['visible']++; }
              elseif ($imp <= 300) { $impDist['service']++; }
              elseif ($imp <= 400) { $bgProcs++; $impDist['bg']++; }
              else { $bgProcs++; $impDist['empty']++; }
          }
          foreach ($services as $s) {
              if (!empty($s['foreground'])) $fgSvcs++;
              $totalCrashes += (int)($s['crash_count'] ?? 0);
          }
          $impTotal = max(1, array_sum($impDist));
          ?>

          <div class="card proc-snap-card mb-4">
            <!-- Hero -->
            <div class="proc-snap-hero">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <h5 class="font-weight-bold mb-0"><i class="fas fa-history mr-2"></i>Process Snapshot</h5>
                  <small class="d-block mt-1" style="opacity: 0.85;">Device UID: <?= esc($r['device_id'] ?? '—') ?></small>
                </div>
                <div class="text-right">
                  <span class="badge badge-light proc-badge"><i class="fas fa-microchip mr-1"></i><?= $procCount ?> Processes</span>
                  <span class="badge badge-info proc-badge ml-1"><i class="fas fa-cogs mr-1"></i><?= $svcCount ?> Services</span>
                </div>
              </div>
            </div>

            <div class="card-body">

              <!-- Mini Stats Row -->
              <div class="d-flex flex-wrap gap-2 mb-3">
                <div class="stat-mini" style="background:#fff3cd;">
                  <div class="sm-v text-warning"><?= $fgProcs ?></div>
                  <div class="sm-l text-dark"><i class="fas fa-eye mr-1"></i>Foreground</div>
                </div>
                <div class="stat-mini" style="background:#e9ecef;">
                  <div class="sm-v text-secondary"><?= $bgProcs ?></div>
                  <div class="sm-l"><i class="fas fa-moon mr-1"></i>Background</div>
                </div>
                <div class="stat-mini" style="background:#d4edda;">
                  <div class="sm-v text-success"><?= $fgSvcs ?></div>
                  <div class="sm-l"><i class="fas fa-broadcast-tower mr-1"></i>FG Services</div>
                </div>
                <div class="stat-mini" style="background:<?= $totalCrashes > 0 ? '#f8d7da' : '#e9ecef' ?>;">
                  <div class="sm-v <?= $totalCrashes > 0 ? 'text-danger' : 'text-secondary' ?>"><?= $totalCrashes ?></div>
                  <div class="sm-l"><i class="fas fa-exclamation-triangle mr-1"></i>Crashes</div>
                </div>
              </div>

              <!-- Importance Distribution Bar -->
              <?php if ($procCount > 0): ?>
              <div class="section-label mb-1"><i class="fas fa-chart-bar mr-1"></i>Importance Distribution</div>
              <div class="imp-bar">
                <?php if ($impDist['fg'] > 0): ?><div style="width:<?= round($impDist['fg']/$impTotal*100,1) ?>%;background:#dc3545;" title="Foreground: <?= $impDist['fg'] ?>"></div><?php endif; ?>
                <?php if ($impDist['visible'] > 0): ?><div style="width:<?= round($impDist['visible']/$impTotal*100,1) ?>%;background:#fd7e14;" title="Visible: <?= $impDist['visible'] ?>"></div><?php endif; ?>
                <?php if ($impDist['service'] > 0): ?><div style="width:<?= round($impDist['service']/$impTotal*100,1) ?>%;background:#007bff;" title="Service: <?= $impDist['service'] ?>"></div><?php endif; ?>
                <?php if ($impDist['bg'] > 0): ?><div style="width:<?= round($impDist['bg']/$impTotal*100,1) ?>%;background:#adb5bd;" title="Background: <?= $impDist['bg'] ?>"></div><?php endif; ?>
                <?php if ($impDist['empty'] > 0): ?><div style="width:<?= round($impDist['empty']/$impTotal*100,1) ?>%;background:#dee2e6;" title="Empty/Cached: <?= $impDist['empty'] ?>"></div><?php endif; ?>
              </div>
              <div class="d-flex flex-wrap gap-2 mb-3" style="font-size:10px;">
                <span><span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:#dc3545;margin-right:3px;"></span>FG <?= $impDist['fg'] ?></span>
                <span><span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:#fd7e14;margin-right:3px;"></span>Visible <?= $impDist['visible'] ?></span>
                <span><span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:#007bff;margin-right:3px;"></span>Service <?= $impDist['service'] ?></span>
                <span><span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:#adb5bd;margin-right:3px;"></span>BG <?= $impDist['bg'] ?></span>
                <span><span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:#dee2e6;margin-right:3px;"></span>Empty <?= $impDist['empty'] ?></span>
              </div>
              <?php endif; ?>

              <div class="row">
                
                <!-- Col 1: Processes list -->
                <div class="col-md-6 mb-4">
                  <div class="section-label mb-2">Active Processes (<?= $procCount ?>)</div>
                  <div class="list-wrapper">
                    <?php if (empty($details)): ?>
                      <div class="text-center py-4 text-muted">No process details recorded</div>
                    <?php else: foreach ($details as $d):
                      $imp = (int)($d['importance'] ?? 0);
                      $badgeClass = $imp <= 200 ? 'badge-danger' : ($imp <= 300 ? 'badge-primary' : 'badge-secondary');
                      $impMap = [100=>'Foreg', 200=>'Visible', 300=>'Service', 400=>'Bg', 500=>'Empty', 600=>'Cached'];
                      $impLabel = $impMap[$imp] ?? 'Unk';
                      $lru = $d['lru'] ?? null;
                      $pkgList = $d['pkg_list'] ?? $d['pkgList'] ?? [];
                      if (is_string($pkgList)) $pkgList = array_filter(array_map('trim', explode(',', $pkgList)));
                      ?>
                      <div class="list-item-row" style="flex-direction:column;align-items:stretch;">
                        <div class="d-flex align-items-center justify-content-between">
                          <div class="d-flex align-items-center">
                            <code style="font-size:10px; width:45px; display:inline-block;" class="text-muted">PID <?= esc($d['pid'] ?? '?') ?></code>
                            <span class="font-weight-bold text-truncate ml-1" style="max-width:200px;" title="<?= esc($d['process_name'] ?? '') ?>"><?= esc($d['process_name'] ?? '—') ?></span>
                            <?php if ($lru !== null): ?><span class="lru-rank">LRU #<?= esc($lru) ?></span><?php endif; ?>
                          </div>
                          <div>
                            <span class="badge <?= $badgeClass ?> mr-1" style="font-size: 9px;"><?= $impLabel ?></span>
                            <code style="font-size: 10px;">UID <?= esc($d['uid'] ?? '0') ?></code>
                          </div>
                        </div>
                        <?php if (!empty($pkgList)): ?>
                        <div class="mt-1">
                          <?php foreach (array_slice($pkgList, 0, 5) as $pkg): ?>
                          <span class="pkg-pill"><?= esc($pkg) ?></span>
                          <?php endforeach; ?>
                          <?php if (count($pkgList) > 5): ?><span class="pkg-pill">+<?= count($pkgList) - 5 ?> more</span><?php endif; ?>
                        </div>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; endif; ?>
                  </div>
                </div>

                <!-- Col 2: Services list -->
                <div class="col-md-6 mb-4">
                  <div class="section-label mb-2">Active Service Threads (<?= $svcCount ?>)</div>
                  <div class="list-wrapper">
                    <?php if (empty($services)): ?>
                      <div class="text-center py-4 text-muted">No service threads recorded</div>
                    <?php else: foreach ($services as $s):
                      $crashes = (int)($s['crash_count'] ?? 0);
                      $isFg = !empty($s['foreground']);
                      $clientCnt = (int)($s['client_count'] ?? 0);
                      $activeSince = $s['activeSince'] ?? $s['active_since'] ?? null;
                      $fullClass = $s['service_class'] ?? '';
                      $shortClass = preg_replace('/^.*\./', '', $fullClass);
                      ?>
                      <div class="list-item-row" style="flex-direction:column;align-items:stretch;">
                        <div class="d-flex align-items-center justify-content-between">
                          <div class="d-flex align-items-center">
                            <span class="fg-dot" style="background:<?= $isFg ? '#28a745' : '#dee2e6' ?>;"></span>
                            <code style="font-size:10px; width:45px; display:inline-block;" class="text-muted">PID <?= esc($s['pid'] ?? '?') ?></code>
                            <span class="font-weight-bold text-truncate ml-1" style="max-width:180px;" title="<?= esc($fullClass) ?>"><?= esc($shortClass) ?></span>
                          </div>
                          <div class="d-flex align-items-center gap-1">
                            <?php if ($crashes > 0): ?>
                              <span class="badge badge-danger mr-1" style="font-size: 9px;"><i class="fas fa-exclamation-triangle mr-1"></i><?= $crashes ?></span>
                            <?php endif; ?>
                            <?php if ($clientCnt > 0): ?>
                              <span class="badge badge-info mr-1" style="font-size: 9px;"><i class="fas fa-user mr-1"></i><?= $clientCnt ?> clients</span>
                            <?php endif; ?>
                            <span class="pkg-badge"><?= esc($s['service_package'] ?? '—') ?></span>
                          </div>
                        </div>
                        <?php if ($activeSince): ?>
                        <div class="mt-1" style="font-size:10px;color:#adb5bd;">
                          <i class="fas fa-clock mr-1"></i>Active since: <?= esc(is_numeric($activeSince) ? date('H:i:s', (int)($activeSince/1000)) : $activeSince) ?>
                        </div>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; endif; ?>
                  </div>
                </div>

              </div>

              <!-- Footer values -->
              <div class="proc-snap-kv"><span class="snk">Snapshot Timestamp</span><span class="snv"><?= $ts ?></span></div>
              <div class="proc-snap-kv">
                <span class="snk">Action Diagnostics</span>
                <span class="snv">
                  <button class="btn btn-sm btn-outline-danger delete-row py-0"
                    data-id="<?= $rid ?>"
                    data-url="<?= base_url('advanced/hardware/processes/delete') ?>">
                    <i class="fas fa-trash mr-1"></i>Remove Snapshot
                  </button>
                </span>
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