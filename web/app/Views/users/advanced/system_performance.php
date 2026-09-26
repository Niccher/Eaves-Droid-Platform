<?php /** @var array $latest_proc @var array $latest_th @var array $latest_pr @var array $history @var object $pager @var string $nav_urls */ ?>
<?php $latest_thermal = $latest_th ?? []; $latest_power = $latest_pr ?? []; $latest_pi = $latest_proc ?? []; ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-tachometer-alt text-danger mr-2"></i>System Performance</h1>
                        <span class="badge badge-danger border p-2 text-white"><i class="fas fa-database mr-1"></i>Snapshots: <b><?= count($history ?? []) ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">CPU, memory, thermal zones, throttling, frequency scaling, and power rails</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Current State Cards -->
            <div class="row mb-4">
                <!-- CPU & Memory -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-3"><i class="fas fa-microchip mr-1"></i>CPU & Memory</h6>
                            <?php if ($latest_proc): 
                                $cpuinfo = json_decode($latest_pi['cpuinfo_json'] ?? '[]', true);
                                $meminfo = json_decode($latest_pi['meminfo_json'] ?? '[]', true);
                                $stat = json_decode($latest_pi['stat_json'] ?? '[]', true);
                                $uptime = json_decode($latest_pi['uptime_json'] ?? '[]', true);
                                $cores = 0;
                                if (is_array($cpuinfo)) {
                                    foreach ($cpuinfo as $cpu) { if (isset($cpu['processor'])) $cores++; }
                                }
                                $memTotal = $meminfo['MemTotal'] ?? 0;
                                $memAvail = $meminfo['MemAvailable'] ?? ($meminfo['MemFree'] ?? 0);
                                $memUsed = $memTotal - $memAvail;
                                $memPct = $memTotal > 0 ? round($memUsed / $memTotal * 100) : 0;
                            ?>
                                <div class="mb-3">
                                    <small class="text-muted">Cores</small>
                                    <div class="h4 mb-1"><?= $cores ?></div>
                                    <div class="progress" style="height:4px;"><div class="progress-bar bg-primary" style="width:100%"></div></div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Memory</small>
                                    <div class="h4 mb-1"><?= $memPct ?>% used</div>
                                    <div class="progress" style="height:4px;"><div class="progress-bar bg-<?= $memPct > 90 ? 'danger' : ($memPct > 70 ? 'warning' : 'success') ?>" style="width:<?= $memPct ?>%"></div></div>
                                    <small class="text-muted"><?= format_bytes($memAvail) ?> free of <?= format_bytes($memTotal) ?></small>
                                </div>
                                <div>
                                    <small class="text-muted">Uptime</small>
                                    <div class="h5 mb-0"><?= $uptime['formatted'] ?? ($uptime[0] ?? '—') ?></div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4"><i class="fas fa-microchip fa-3x text-muted mb-3"></i><p class="text-muted">No proc data</p></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Thermal -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-danger shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-3"><i class="fas fa-thermometer-half mr-1"></i>Thermal Zones</h6>
                            <?php if ($latest_thermal): 
                                $zones = json_decode($latest_thermal['thermal_zones_json'] ?? '[]', true);
                                $hottest = null;
                                foreach ($zones as $z) { if (($z['temp_celsius'] ?? 0) > ($hottest['temp_celsius'] ?? 0)) $hottest = $z; }
                                $throttle = json_decode($latest_thermal['cpu_throttle_json'] ?? '[]', true);
                                $throttleCount = array_sum(array_column($throttle, 'throttle_count'));
                            ?>
                                <div class="h2 mb-1"><?= $hottest ? number_format($hottest['temp_celsius'], 1) : '—' ?>°C</div>
                                <small class="text-muted d-block mb-2">Hottest: <?= htmlspecialchars($hottest['zone_name'] ?? '—') ?> (<?= htmlspecialchars($hottest['zone_type'] ?? '—') ?>)</small>
                                
                                <?php if ($throttleCount > 0): ?>
                                    <span class="badge badge-warning p-2 mb-2 d-inline-block">
                                        <i class="fas fa-exclamation-triangle mr-1"></i><?= $throttleCount ?> throttle events
                                    </span>
                                <?php endif; ?>
                                
                                <div class="small">
                                    <?php foreach (array_slice($zones, 0, 5) as $z): ?>
                                        <div class="d-flex justify-content-between">
                                            <span><?= htmlspecialchars($z['zone_name'] ?? '—') ?></span>
                                            <span class="font-weight-bold text-<?= ($z['temp_celsius'] ?? 0) > 60 ? 'danger' : (($z['temp_celsius'] ?? 0) > 45 ? 'warning' : 'success') ?>"><?= number_format($z['temp_celsius'] ?? 0, 1) ?>°C</span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($zones) > 5): ?><small class="text-muted">+<?= count($zones) - 5 ?> more zones</small><?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4"><i class="fas fa-thermometer fa-3x text-muted mb-3"></i><p class="text-muted">No thermal data</p></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- CPU Frequency -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-warning shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-3"><i class="fas fa-tachometer-alt mr-1"></i>CPU Frequency</h6>
                            <?php if ($latest_thermal): 
                                $freqs = json_decode($latest_thermal['cpu_frequencies_json'] ?? '[]', true);
                            ?>
                                <?php foreach (array_slice($freqs, 0, 8) as $f): ?>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between small">
                                            <span><?= htmlspecialchars($f['cpu_name'] ?? 'CPU') ?></span>
                                            <span class="font-weight-bold"><?= $f['scaling_cur_freq'] ? number_format($f['scaling_cur_freq'] / 1e6, 1) . ' GHz' : '—' ?></span>
                                        </div>
                                        <div class="progress" style="height:4px;">
                                            <?php $min = $f['scaling_min_freq'] ?? 0; $max = $f['scaling_max_freq'] ?? 1; $cur = $f['scaling_cur_freq'] ?? 0; $pct = $max > $min ? round(($cur - $min) / ($max - $min) * 100) : 0; ?>
                                            <div class="progress-bar bg-<?= $pct > 80 ? 'danger' : ($pct > 50 ? 'warning' : 'success') ?>" style="width:<?= $pct ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?= $f['scaling_governor'] ?? '—' ?> • Min: <?= $min ? number_format($min/1e6,1) : '?' ?> GHz • Max: <?= $max ? number_format($max/1e6,1) : '?' ?> GHz</small>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($freqs) > 8): ?><small class="text-muted">+<?= count($freqs) - 8 ?> more cores</small><?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-4"><i class="fas fa-tachometer-alt fa-3x text-muted mb-3"></i><p class="text-muted">No frequency data</p></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Power Rails -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-info shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-3"><i class="fas fa-bolt mr-1"></i>Power Rails</h6>
                            <?php if ($latest_power): 
                                // Power rails stored as individual rows, get latest snapshot
                                // For now show placeholder
                            ?>
                                <div class="text-center py-3">
                                    <i class="fas fa-bolt fa-3x text-info mb-3"></i>
                                    <p class="text-muted">Power rail data available</p>
                                    <small class="text-muted">View details for per-rail voltage/current</small>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4"><i class="fas fa-bolt fa-3x text-muted mb-3"></i><p class="text-muted">No power rail data</p></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Proc Details (Meminfo, CPUinfo, Stat, Version) -->
            <?php if ($latest_proc): 
                $cpuinfo = json_decode($latest_proc['cpuinfo_json'] ?? '[]', true);
                $meminfo = json_decode($latest_proc['meminfo_json'] ?? '[]', true);
                $version = $latest_proc['version'] ?? '—';
                $netIfaces = json_decode($latest_proc['net_interfaces_json'] ?? '[]', true);
                $netConns = json_decode($latest_proc['net_connections_json'] ?? '[]', true);
            ?>
            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm">
                        <div class="card-header"><h6 class="mb-0"><i class="fas fa-memory mr-1"></i>Memory Info (meminfo)</h6></div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:300px;overflow:auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <tbody>
                                    <?php foreach ($meminfo as $k => $v): ?>
                                        <tr><td class="font-weight-bold" style="width:40%"><?= htmlspecialchars($k) ?></td><td><?= is_numeric($v) && $v > 1024 ? format_bytes($v * 1024) : htmlspecialchars($v) ?></td></tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm">
                        <div class="card-header"><h6 class="mb-0"><i class="fas fa-microchip mr-1"></i>CPU Info (cpuinfo)</h6></div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:300px;overflow:auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <tbody>
                                    <?php if (is_array($cpuinfo) && !empty($cpuinfo[0])): ?>
                                        <?php foreach ($cpuinfo[0] as $k => $v): ?>
                                            <tr><td class="font-weight-bold" style="width:40%"><?= htmlspecialchars($k) ?></td><td><?= htmlspecialchars($v) ?></td></tr>
                                        <?php endforeach; ?>
                                    <?php else: foreach ($cpuinfo as $k => $v): ?>
                                        <tr><td class="font-weight-bold"><?= htmlspecialchars($k) ?></td><td><?= htmlspecialchars(is_array($v) ? json_encode($v) : $v) ?></td></tr>
                                    <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm">
                        <div class="card-header"><h6 class="mb-0"><i class="fas fa-code mr-1"></i>Kernel Version</h6></div>
                        <div class="card-body"><code class="small"><?= htmlspecialchars($version) ?></code></div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm">
                        <div class="card-header"><h6 class="mb-0"><i class="fas fa-network-wired mr-1"></i>Network Interfaces</h6></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="thead-light"><tr><th>Interface</th><th>IP Addresses</th><th>State</th><th>MTU</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($netIfaces as $iface): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($iface['name'] ?? '—') ?></strong></td>
                                            <td>
                                                <?php if (!empty($iface['ipv4'])): ?><?php foreach ($iface['ipv4'] as $ip): ?><span class="badge badge-light text-dark mr-1"><code><?= htmlspecialchars($ip) ?></code></span><?php endforeach; ?><?php endif; ?>
                                                <?php if (!empty($iface['ipv6'])): ?><?php foreach ($iface['ipv6'] as $ip): ?><span class="badge badge-secondary mr-1"><code><?= htmlspecialchars($ip) ?></code></span><?php endforeach; ?><?php endif; ?>
                                            </td>
                                            <td><span class="badge badge-<?= !empty($iface['up']) ? 'success' : 'secondary' ?>"><?= !empty($iface['up']) ? 'UP' : 'DOWN' ?></span></td>
                                            <td><?= $iface['mtu'] ?? '—' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- History Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Performance History</h3>
                            <div class="card-tools ml-auto">
                                <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#performanceHistoryModal">Show All (<?= count($history ?? []) ?>)</button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                        <th><i class="fas fa-microchip text-primary mr-1"></i>CPU</th>
                                        <th><i class="fas fa-memory text-info mr-1"></i>Memory</th>
                                        <th><i class="fas fa-thermometer-half text-danger mr-1"></i>Thermal</th>
                                        <th><i class="fas fa-tachometer-alt text-warning mr-1"></i>Freq (avg)</th>
                                        <th><i class="fas fa-bolt text-info mr-1"></i>Power Rails</th>
                                        <th><i class="fas fa-clock text-muted mr-1"></i>Uptime</th>
                                        <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($history)): ?>
                                        <tr><td colspan="9" class="text-center py-5"><div class="empty-state"><i class="fas fa-tachometer-alt fa-3x text-muted mb-3"></i><h4>No performance history</h4></div></td></tr>
                                    <?php else: foreach (array_slice($history,0,5) as $h): 
                                        $proc = $h['proc_info'] ?? [];
                                        $th = $h['thermal'] ?? [];
                                        $pr = $h['power_rails'] ?? [];
                                        $cpuinfo = json_decode($proc['cpuinfo_json'] ?? '[]', true);
                                        $meminfo = json_decode($proc['meminfo_json'] ?? '[]', true);
                                        $memTotal = $meminfo['MemTotal'] ?? 0;
                                        $memAvail = $meminfo['MemAvailable'] ?? ($meminfo['MemFree'] ?? 0);
                                        $memPct = $memTotal > 0 ? round(($memTotal - $memAvail) / $memTotal * 100) : 0;
                                        $cores = 0; if (is_array($cpuinfo)) { foreach ($cpuinfo as $cpu) { if (isset($cpu['processor'])) $cores++; } }
                                        $zones = json_decode($th['thermal_zones_json'] ?? '[]', true);
                                        $hottest = null; foreach ($zones as $z) { if (($z['temp_celsius'] ?? 0) > ($hottest['temp_celsius'] ?? 0)) $hottest = $z; }
                                        $freqs = json_decode($th['cpu_frequencies_json'] ?? '[]', true);
                                        $avgFreq = 0; $freqCount = 0; foreach ($freqs as $f) { if ($f['scaling_cur_freq']) { $avgFreq += $f['scaling_cur_freq']; $freqCount++; } } $avgFreq = $freqCount ? round($avgFreq / $freqCount / 1e6, 1) : 0;
                                    ?>
                                        <tr>
                                            <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                            <td><span class="badge badge-primary"><?= $cores ?> cores</span></td>
                                            <td><span class="badge badge-<?= $memPct > 90 ? 'danger' : ($memPct > 70 ? 'warning' : 'info') ?>"><?= $memPct ?>%</span> <small class="text-muted d-block"><?= format_bytes($memAvail) ?> free</small></td>
                                            <td>
                                                <?php if ($hottest): ?>
                                                    <span class="font-weight-bold text-<?= $hottest['temp_celsius'] > 60 ? 'danger' : ($hottest['temp_celsius'] > 45 ? 'warning' : 'success') ?>"><?= number_format($hottest['temp_celsius'], 1) ?>°C</span>
                                                    <small class="text-muted d-block"><?= htmlspecialchars($hottest['zone_name']) ?></small>
                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                            </td>
                                            <td><?= $avgFreq ?> GHz</td>
                                            <td><?= is_array($pr) ? count($pr) : 0 ?> rails</td>
                                            <td><small><?= !empty($proc['uptime_json']) ? (json_decode($proc['uptime_json'], true)['formatted'] ?? '—') : '—' ?></small></td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/system_performance/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
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

    <!-- Modal: Full Performance History -->
    <div class="modal fade" id="performanceHistoryModal" tabindex="-1" role="dialog" aria-labelledby="performanceHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="performanceHistoryModalLabel"><i class="fas fa-history mr-2"></i>Full Performance History</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                    <th><i class="fas fa-microchip text-primary mr-1"></i>CPU</th>
                                    <th><i class="fas fa-memory text-info mr-1"></i>Memory</th>
                                    <th><i class="fas fa-thermometer-half text-danger mr-1"></i>Thermal</th>
                                    <th><i class="fas fa-tachometer-alt text-warning mr-1"></i>Freq (avg)</th>
                                    <th><i class="fas fa-bolt text-info mr-1"></i>Power Rails</th>
                                    <th><i class="fas fa-clock text-muted mr-1"></i>Uptime</th>
                                    <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history)): ?>
                                <tr><td colspan="9" class="text-center py-5"><div class="empty-state"><i class="fas fa-tachometer-alt fa-3x text-muted mb-3"></i><h4>No performance history</h4></div></td></tr>
                                <?php else: foreach ($history as $h):
                                    $proc = $h['proc_info'] ?? [];
                                    $th = $h['thermal'] ?? [];
                                    $pr = $h['power_rails'] ?? [];
                                    $cpuinfo = json_decode($proc['cpuinfo_json'] ?? '[]', true);
                                    $meminfo = json_decode($proc['meminfo_json'] ?? '[]', true);
                                    $memTotal = $meminfo['MemTotal'] ?? 0;
                                    $memAvail = $meminfo['MemAvailable'] ?? ($meminfo['MemFree'] ?? 0);
                                    $memPct = $memTotal > 0 ? round(($memTotal - $memAvail) / $memTotal * 100) : 0;
                                    $cores = 0; if (is_array($cpuinfo)) { foreach ($cpuinfo as $cpu) { if (isset($cpu['processor'])) $cores++; } }
                                    $zones = json_decode($th['thermal_zones_json'] ?? '[]', true);
                                    $hottest = null; foreach ($zones as $z) { if (($z['temp_celsius'] ?? 0) > ($hottest['temp_celsius'] ?? 0)) $hottest = $z; }
                                    $freqs = json_decode($th['cpu_frequencies_json'] ?? '[]', true);
                                    $avgFreq = 0; $freqCount = 0; foreach ($freqs as $f) { if ($f['scaling_cur_freq']) { $avgFreq += $f['scaling_cur_freq']; $freqCount++; } } $avgFreq = $freqCount ? round($avgFreq / $freqCount / 1e6, 1) : 0;
                                ?>
                                <tr>
                                    <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                    <td><span class="badge badge-primary"><?= $cores ?> cores</span></td>
                                    <td><span class="badge badge-<?= $memPct > 90 ? 'danger' : ($memPct > 70 ? 'warning' : 'info') ?>"><?= $memPct ?>%</span> <small class="text-muted d-block"><?= format_bytes($memAvail) ?> free</small></td>
                                    <td>
                                        <?php if ($hottest): ?>
                                            <span class="font-weight-bold text-<?= $hottest['temp_celsius'] > 60 ? 'danger' : ($hottest['temp_celsius'] > 45 ? 'warning' : 'success') ?>"><?= number_format($hottest['temp_celsius'], 1) ?>°C</span>
                                            <small class="text-muted d-block"><?= htmlspecialchars($hottest['zone_name']) ?></small>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                    <td><?= $avgFreq ?> GHz</td>
                                    <td><?= is_array($pr) ? count($pr) : 0 ?> rails</td>
                                    <td><small><?= !empty($proc['uptime_json']) ? (json_decode($proc['uptime_json'], true)['formatted'] ?? '—') : '—' ?></small></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/system_performance/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
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