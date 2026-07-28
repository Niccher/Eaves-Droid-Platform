<?php
/** @var array $rows @var int $total @var int $total_snapshots @var object $pager @var string $nav_urls */
/** @var bool $detail_mode @var string|null $package_name @var array|null $summary @var array|null $app_detail @var array|null $sessions @var string|null $back_url */
if (!empty($detail_mode)) {
    $appName = $summary['app_name'] ?? $package_name;
    $totalMs = (int) ($summary['foreground_time_ms'] ?? 0);
    $totalHrs = round($totalMs / 3600000, 2);
    $lastUsed = $summary['last_used_display'] ?? '—';
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-12">
                    <a href="<?= esc($back_url) ?>" class="btn btn-primary" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><i class="fas fa-arrow-left mr-1"></i> Back to Usage</a>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-lg-8">
                    <h1 class="h2 mb-0"><i class="fas fa-chart-pie text-secondary mr-2"></i><?= htmlspecialchars($appName) ?></h1>
                    <p class="text-muted mt-1 mb-0"><code><?= htmlspecialchars($package_name) ?></code></p>
                </div>
                <div class="col-lg-4">
                    <?php if (!empty($app_detail)): ?>
                    <div class="card card-secondary card-outline shadow-sm mb-0">
                        <div class="card-body py-2 px-3">
                            <div class="row text-center">
                                <div class="col-4 border-right">
                                    <small class="text-muted d-block">Permissions</small>
                                    <strong><?= (int)($app_detail['permission_count'] ?? 0) ?></strong>
                                </div>
                                <div class="col-4 border-right">
                                    <small class="text-muted d-block">Size</small>
                                    <strong><?= $app_detail['app_size_display'] ?? '—' ?></strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Version</small>
                                    <strong><?= htmlspecialchars($app_detail['version_name'] ?? '—') ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-stopwatch"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Screen Time</span>
                            <span class="info-box-number"><?= $totalHrs ?> h</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-secondary">
                        <span class="info-box-icon"><i class="fas fa-history"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Last Used</span>
                            <span class="info-box-number" style="font-size:1rem;"><?= $lastUsed ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-primary">
                        <span class="info-box-icon"><i class="fas fa-layer-group"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Snapshots</span>
                            <span class="info-box-number"><?= (int) ($summary['snapshot_count'] ?? $total) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-<?= !empty($summary['is_system_app']) ? 'secondary' : 'success' ?>">
                        <span class="info-box-icon"><i class="fas fa-cog"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">App Type</span>
                            <span class="info-box-number" style="font-size:1rem;"><?= !empty($summary['is_system_app']) ? 'System' : 'User' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list mr-2"></i>App Usage Intervals <small class="text-muted ml-2"><?= count($rows) ?> records</small></h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th><i class="fas fa-stopwatch mr-1"></i>Total App Screen Time</th>
                                        <th><i class="fas fa-history mr-1"></i>Time App Was Opened</th>
                                        <th><i class="fas fa-hourglass-half mr-1"></i>Foreground Duration</th>
                                        <th><i class="fas fa-mobile-alt mr-1"></i>Device</th>
                                        <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($rows)): ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No snapshots for this app.</td></tr>
                                    <?php else: foreach ($rows as $i => $r): ?>
                                        <?php
                                            $ms  = $r['foreground_time_ms'] ?? 0;
                                            $hrs = round($ms / 3600000, 2);
                                            
                                            $timeTakenMs = $r['time_taken_ms'] ?? null;
                                            if ($timeTakenMs !== null) {
                                                $ttHrs = floor($timeTakenMs / 3600000);
                                                $ttMins = floor(($timeTakenMs % 3600000) / 60000);
                                                $ttSecs = floor(($timeTakenMs % 60000) / 1000);
                                                $timeTakenStr = '';
                                                if ($ttHrs > 0) $timeTakenStr .= $ttHrs . 'h ';
                                                if ($ttMins > 0 || $ttHrs > 0) $timeTakenStr .= $ttMins . 'm ';
                                                $timeTakenStr .= $ttSecs . 's';
                                            } else {
                                                $timeTakenStr = '—';
                                            }
                                        ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td>
                                                <span class="font-weight-bold"><?= $hrs ?> h</span>
                                                <small class="text-muted d-block"><?= number_format($ms) ?> ms</small>
                                            </td>
                                            <td><small><?= esc($r['last_used_display'] ?? '—') ?></small></td>
                                            <td>
                                                <span class="text-success font-weight-bold"><?= $timeTakenStr ?></span>
                                                <?php if ($timeTakenMs !== null): ?>
                                                    <small class="text-muted d-block">+<?= number_format($timeTakenMs) ?> ms</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><small class="text-muted"><?= htmlspecialchars($r['device_id'] ?? '—') ?></small></td>
                                            <td><small><?= esc($r['extracted_display'] ?? '—') ?></small></td>
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

            <?php if (!empty($sessions)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-secondary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-stream mr-2"></i>Session Events <small class="text-muted ml-2">latest <?= count($sessions) ?></small></h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>Event</th>
                                        <th>Timestamp</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($sessions as $s): ?>
                                        <tr>
                                            <td><span class="badge badge-secondary"><?= htmlspecialchars($s['event_type'] ?? '—') ?></span></td>
                                            <td><small><?= esc($s['timestamp_display'] ?? '—') ?></small></td>
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
        </div>
    </section>
</div>
<?php
} else {
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-chart-pie text-secondary mr-2"></i>App Usage</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Foreground screen time per application</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>


            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-mobile-alt mr-2"></i>App Usage Stats <small class="text-muted ml-2"><?= count($rows) ?> apps</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th><i class="fas fa-cube mr-1"></i>App / Package</th>
                            <th><i class="fas fa-stopwatch mr-1"></i>Screen Time</th>
                            <th><i class="fas fa-history mr-1"></i>Last Used</th>
                            <th><i class="fas fa-layer-group mr-1"></i>Snapshots</th>
                            <th><i class="fas fa-cog mr-1"></i>Type</th>
                            <th class="text-center"><i class="fas fa-eye mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-mobile-alt fa-3x text-muted mb-3"></i><h4>No usage data</h4><p class="text-muted">App usage will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $i => $r): ?>
                            <?php
                                $ms    = $r['foreground_time_ms'] ?? 0;
                                $hrs   = round($ms / 3600000, 2);
                                $pct   = min(100, $hrs * 10); // rough visual scale
                                $col   = $hrs > 2 ? 'danger' : ($hrs > 0.5 ? 'warning' : 'success');
                                $last   = $r['last_used_display'] ?? '—';
                                $pkgEnc = $r['package_url_enc'] ?? '';
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <div class="font-weight-bold"><i class="fas fa-cube mr-1 text-info"></i><?= htmlspecialchars($r['app_name'] ?? '—') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($r['package_name']) ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress mr-2" style="width:70px;height:8px;border-radius:4px;">
                                            <div class="progress-bar bg-<?= $col ?>" style="width:<?= $pct ?>%"></div>
                                        </div>
                                        <span><?= $hrs ?>h</span>
                                    </div>
                                    <small class="text-muted"><?= number_format($ms) ?> ms</small>
                                </td>
                                <td><small><?= esc($last) ?></small></td>
                                <td><span class="badge badge-info"><?= (int) ($r['snapshot_count'] ?? 0) ?></span></td>
                                <td><span class="badge badge-<?= $r['is_system_app'] ? 'secondary' : 'primary' ?>"><?= $r['is_system_app'] ? 'System' : 'User' ?></span></td>
                                <td class="text-center">
                                    <a href="<?= base_url('advanced/software/app-usage/' . $pkgEnc) ?>" class="btn btn-sm btn-outline-info mr-1" title="View all details"><i class="fas fa-eye"></i></a>
                                    <button class="btn btn-sm btn-outline-danger delete-app-usage-pkg"
                                            data-pkg="<?= $pkgEnc ?? '' ?>"
                                            data-name="<?= esc($r['app_name'] ?? '') ?>"
                                            title="Delete all usage data for this app">
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
<?php
}
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-app-usage-pkg').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var pkg = this.getAttribute('data-pkg');
            var name = this.getAttribute('data-name');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete all usage data?',
                    text: 'This will delete ALL usage snapshots for "' + name + '". This cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash"></i> Delete All'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        fetch('<?= base_url('advanced/software/app-usage/delete-package') ?>/' + pkg, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).then(function(r) { return r.json(); }).then(function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', 'All usage data has been deleted.', 'success').then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', response.message || 'Failed to delete.', 'error');
                            }
                        }).catch(function() {
                            Swal.fire('Error!', 'Failed to delete usage data.', 'error');
                        });
                    }
                });
            }
        });
    });
});
</script>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
