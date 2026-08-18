<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
helper('coalesce');
// Detailed processes are unique per device + pid. We coalesce to keep the latest stats per running process.
foreach ($rows as &$r) {
    $r['proc_unique_key'] = ($r['device_id'] ?? 'default') . '_' . ($r['pid'] ?? 0);
}
unset($r);

$rows = coalesce_snapshots(
    $rows,
    'proc_unique_key',
    ['pid', 'name', 'ppid', 'uid', 'importance', 'state', 'tid', 'nice', 'threads', 'vsize_kb', 'vsize_peak_kb', 'rss_kb', 'pss_kb', 'uss_kb', 'swap_kb', 'cpu_time_ms', 'cpu_time_user_ms', 'cpu_time_system_ms', 'start_time', 'elapsed_time_ms', 'processor', 'cmdline', 'gid', 'groups', 'priority', 'fd_count', 'socket_count', 'wake_lock_count', 'oom_score', 'oom_score_adj', 'cgroup', 'selinux_context', 'cpu_percent', 'open_files', 'memory_maps', 'stack_trace', 'capabilities_eff', 'capabilities_prm', 'capabilities_inh', 'capabilities_bnd', 'capabilities_amb']
);

// Group by device for a multi-device dashboard
$deviceGroups = [];
foreach ($rows as $r) {
    $dev = $r['device_id'] ?? 'Unknown Device';
    $deviceGroups[$dev][] = $r;
}
?>

<style>
.proc-card { border-radius: 12px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #eef2f5; overflow: hidden; margin-bottom: 24px; }
.proc-header { background: #f8fafc; border-bottom: 1px solid #edf2f7; padding: 16px 20px; }
.proc-table th { font-size: 11px; text-transform: uppercase; color: #718096; letter-spacing: 0.5px; border-top: none; }
.proc-table td { vertical-align: middle; font-size: 13px; color: #2d3748; }
.mem-badge { background: #ebf8ff; color: #2b6cb0; border-radius: 6px; padding: 2px 8px; font-weight: bold; font-family: monospace; }
.cpu-badge { background: #fffaf0; color: #dd6b20; border-radius: 6px; padding: 2px 8px; font-weight: bold; font-family: monospace; }
.sys-badge { background: #f7fafc; color: #4a5568; border-radius: 6px; padding: 2px 8px; border: 1px solid #e2e8f0; }
.progress-bar-memory { height: 6px; border-radius: 3px; background: #edf2f7; margin-top: 4px; overflow: hidden; width: 80px; }
.progress-fill { height: 100%; background: #4299e1; border-radius: 3px; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <h1 class="h2 mb-0"><i class="fas fa-microchip text-primary mr-2"></i>Running Processes Monitor</h1>
                    <p class="text-muted mt-1 mb-0">Active application runtimes, background daemons, thread allocations, and memory footprints</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>

            <!-- Callout -->
            <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;">
                <h5 class="font-weight-bold text-info"><i class="fas fa-info-circle mr-2"></i>Process Telemetry &amp; Resource Auditing</h5>
                <p class="text-secondary mb-0" style="font-size:14px;">Auditing operational processes detects background spyware executors, unexpected root shells, keyloggers masquerading as systems daemons, or memory-leaking trojans.</p>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (empty($deviceGroups)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                    <h4 class="text-secondary">No processes cataloged</h4>
                    <p class="text-muted">Data will appear once extracted from the Android monitor.</p>
                </div>
            <?php else: foreach ($deviceGroups as $devId => $pList): 
                // Metrics
                $totalThreads = array_sum(array_column($pList, 'threads'));
                $totalRss = array_sum(array_column($pList, 'rss_kb')) / 1024; // MB
                $maxMemProc = null;
                foreach ($pList as $p) {
                    if (!$maxMemProc || ($p['rss_kb'] > $maxMemProc['rss_kb'])) {
                        $maxMemProc = $p;
                    }
                }
                ?>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-info"><i class="fas fa-tasks"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Processes</span>
                                <span class="info-box-number font-weight-bold" style="font-size:20px;"><?= count($pList) ?> active</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-success"><i class="fas fa-memory"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total RAM Footprint</span>
                                <span class="info-box-number font-weight-bold" style="font-size:20px;"><?= number_format($totalRss, 1) ?> MB</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Heaviest Application</span>
                                <span class="info-box-number font-weight-bold text-truncate" style="font-size:16px;max-width:200px;" title="<?= esc($maxMemProc['name'] ?? '—') ?>">
                                    <?= esc($maxMemProc['name'] ?? '—') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="proc-card">
                    <div class="proc-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold text-secondary"><i class="fas fa-mobile-alt mr-2"></i>Device: <code><?= esc($devId) ?></code></h5>
                        <span class="badge badge-light border"><i class="fas fa-network-wired mr-1 text-muted"></i><?= $totalThreads ?> active threads</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 proc-table">
                                <thead class="bg-light border-bottom">
                                    <tr>
                                        <th style="width: 50px;"></th>
                                        <th style="width: 100px;">PID (PPID)</th>
                                        <th>Process Name / Namespace</th>
                                        <th>USS / PSS / RSS</th>
                                        <th>CPU Usage</th>
                                        <th>Threads</th>
                                        <th>UID / User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pList as $p): 
                                        $pid = (int)($p['pid'] ?? 0);
                                        $ppid = (int)($p['ppid'] ?? 0);
                                        $rssMb = ($p['rss_kb'] ?? 0) / 1024;
                                        $pssMb = ($p['pss_kb'] ?? 0) / 1024;
                                        $ussMb = ($p['uss_kb'] ?? 0) / 1024;
                                        $cpu = (float)($p['cpu_percent'] ?? 0);
                                        $threads = (int)($p['threads'] ?? 1);
                                        $uid = (int)($p['uid'] ?? 0);
                                        
                                        // Simple heuristic to detect system/user apps
                                        $isSystem = $uid < 10000;
                                        ?>
                                        <tr class="accordion-toggle expandable-row" data-target="#proc-info-<?= $pid ?>">
                                            <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                            <td>
                                                <strong><?= $pid ?></strong>
                                                <small class="text-muted d-block">Parent: <?= $ppid ?></small>
                                            </td>
                                            <td>
                                                <strong class="text-primary"><?= esc($p['name']) ?></strong>
                                                <?php if ($p['cgroup']): ?>
                                                    <small class="text-muted d-block text-truncate" style="max-width:350px;">cgroup: <?= esc($p['cgroup']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="mem-badge"><?= number_format($pssMb ?: $rssMb, 1) ?> MB</span>
                                                <div class="progress-bar-memory">
                                                    <div class="progress-fill" style="width: <?= min(100, ($pssMb / 128) * 100) ?>%;"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="cpu-badge"><i class="fas fa-chart-line mr-1"></i><?= number_format($cpu, 1) ?>%</span>
                                            </td>
                                            <td>
                                                <span class="sys-badge"><?= $threads ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $isSystem ? 'secondary' : 'info' ?>">
                                                    <?= $isSystem ? 'System (' . $uid . ')' : 'User app (' . $uid . ')' ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr class="expandable-content" style="display:none;">
                                            <td colspan="7" class="p-0 border-0">
                                                <div id="proc-info-<?= $pid ?>">
                                                    <div class="card card-body bg-light border-0 m-0 p-3">
                                                        <div class="row">
                                                            <div class="col-md-6 border-right">
                                                                <h6 class="font-weight-bold text-secondary mb-2"><i class="fas fa-info-circle mr-2"></i>Process Metadata</h6>
                                                                <table class="table table-sm table-borderless small mb-0">
                                                                    <tr><th style="width:140px;">Command Line</th><td><code><?= esc($p['cmdline'] ?: $p['name']) ?></code></td></tr>
                                                                    <tr><th>SELinux Context</th><td><code><?= esc($p['selinux_context'] ?: '—') ?></code></td></tr>
                                                                    <tr><th>Nice / Priority</th><td><code>nice: <?= $p['nice'] ?? 0 ?> / priority: <?= $p['priority'] ?? 0 ?></code></td></tr>
                                                                    <tr><th>Memory details</th><td>USS: <?= number_format($ussMb, 1) ?> MB / Peak: <?= number_format(($p['vsize_peak_kb'] ?? 0)/1024, 1) ?> MB</td></tr>
                                                                    <tr><th>Descriptor counts</th><td>FDs: <?= (int)$p['fd_count'] ?> / Sockets: <?= (int)$p['socket_count'] ?></td></tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-6 pl-md-4">
                                                                <h6 class="font-weight-bold text-secondary mb-2"><i class="fas fa-shield-alt mr-2"></i>Effective Capabilities</h6>
                                                                <div class="mb-2">
                                                                    <?php 
                                                                    $caps = $p['capabilities_eff'] ?? [];
                                                                    if (empty($caps)): ?>
                                                                        <span class="text-muted small">No special Linux capabilities active.</span>
                                                                    <?php else: foreach ($caps as $cap): ?>
                                                                        <span class="badge badge-light border mr-1 font-weight-normal"><?= esc($cap) ?></span>
                                                                    <?php endforeach; endif; ?>
                                                                </div>
                                                                
                                                                <?php if (!empty($p['stack_trace'])): ?>
                                                                    <h6 class="font-weight-bold text-secondary mt-3 mb-2"><i class="fas fa-stream mr-2"></i>Active Stack Trace</h6>
                                                                    <pre class="bg-dark text-light p-2 rounded text-xs mb-0" style="max-height:150px; overflow-y:auto; font-size:11px;"><code><?= esc(implode("\n", $p['stack_trace'])) ?></code></pre>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>