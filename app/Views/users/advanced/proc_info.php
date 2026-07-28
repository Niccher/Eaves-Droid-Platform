<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-microchip text-secondary mr-2"></i>Proc Info</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Processor, memory, kernel, uptime, and network interface snapshots</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Proc Snapshots <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-linux text-info mr-1"></i> Kernel</th>
                            <th><i class="fas fa-clock text-muted mr-1"></i> Uptime</th>
                            <th><i class="fas fa-memory text-success mr-1"></i> Memory</th>
                            <th><i class="fas fa-microchip text-warning mr-1"></i> CPU</th>
                            <th><i class="fas fa-network-wired text-primary mr-1"></i> Interfaces</th>
                            <th><i class="fas fa-clock text-muted mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-microchip fa-3x text-muted mb-3"></i><h4>No proc data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r):
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $mem = $r['meminfo_json'] ?? [];
                            $cpu = $r['cpuinfo_json'] ?? [];
                            $uptime = $r['uptime_json'] ?? [];
                            $interfaces = $r['net_interfaces_json'] ?? [];
                            $uptimeStr = '—';
                            if (!empty($uptime['uptime_seconds'])) {
                                $sec = (int)$uptime['uptime_seconds'];
                                $d = intdiv($sec, 86400); $sec %= 86400;
                                $h = intdiv($sec, 3600); $sec %= 3600;
                                $m = intdiv($sec, 60);
                                $uptimeStr = "{$d}d {$h}h {$m}m";
                            }
                            $memTotal = isset($mem['memtotal']) ? round($mem['memtotal'] / 1024) . ' MB' : '—';
                            $memAvail = isset($mem['memavailable']) ? round($mem['memavailable'] / 1024) . ' MB' : '—';
                            $cpuCount = $cpu['cpu_count'] ?? '—';
                            $ifaceCount = is_array($interfaces) ? count($interfaces) : 0;
                        ?>
                            <tr>
                                                                <td><span class="text-muted small" title="<?= htmlspecialchars($r['version'] ?? '') ?>"><?= htmlspecialchars(mb_substr($r['version'] ?? '—', 0, 40)) ?><?= strlen($r['version'] ?? '') > 40 ? '…' : '' ?></span></td>
                                <td><?= $uptimeStr ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 mr-2" style="height: 8px;max-width:80px;">
                                            <div class="bg-success" role="progressbar" style="width: <?= $memTotal !== '—' && $memAvail !== '—' ? min(100, round((float)$memAvail / (float)$memTotal * 100)) : 0 ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?= $memAvail ?> / <?= $memTotal ?></small>
                                    </div>
                                </td>
                                <td><span class="badge badge-warning"><?= $cpuCount ?> cores</span></td>
                                <td><span class="badge badge-info"><?= $ifaceCount ?> ifaces</span></td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/proc_info/delete') ?>"
                                            title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
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
