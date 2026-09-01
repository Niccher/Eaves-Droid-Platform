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
                    <div class="card card-primary card-outline shadow-sm mb-0">
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
                <div class="col-md-7">
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold mb-0 text-primary"><i class="fas fa-list mr-2"></i>App Usage Intervals <small class="ml-2"><?= count($rows) ?> records</small></h3>
                            <div class="card-tools ml-auto">
                                <button type="button" class="btn btn-tool btn-sm text-secondary" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th><i class="fas fa-stopwatch mr-1"></i>Total Screen Time</th>
                                        <th><i class="fas fa-history mr-1"></i>Opened Time</th>
                                        <th><i class="fas fa-hourglass-half mr-1"></i>Duration</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($rows)): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No snapshots for this app.</td></tr>
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
                                                <span class="font-weight-bold text-dark"><?= $hrs ?> h</span>
                                                <small class="text-muted d-block"><?= number_format($ms) ?> ms</small>
                                            </td>
                                            <td><small class="text-muted font-weight-bold"><?= esc($r['last_used_display'] ?? '—') ?></small></td>
                                            <td>
                                                <span class="text-success font-weight-bold"><?= $timeTakenStr ?></span>
                                                <?php if ($timeTakenMs !== null): ?>
                                                    <small class="text-muted d-block">+<?= number_format($timeTakenMs) ?> ms</small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light"><div class="float-right"><?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?></div></div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card card-warning card-outline shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold mb-0 text-warning"><i class="fas fa-stream mr-2"></i>Sessions Timeline</h3>
                        </div>
                        <div class="card-body" style="max-height: 580px; overflow-y: auto;">
                            <?php if (empty($sessions)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-history fa-3x mb-3 text-light"></i>
                                    <p>No lifecycle sessions logged.</p>
                                </div>
                            <?php else: ?>
                                <div class="timeline timeline-inverse mb-0">
                                    <?php foreach ($sessions as $s): 
                                        $type = strtoupper($s['event_type'] ?? 'UNKNOWN');
                                        $badgeColor = 'secondary';
                                        $icon = 'fa-info-circle';
                                        if (strpos($type, 'FOREGROUND') !== false) {
                                            $badgeColor = 'success';
                                            $icon = 'fa-play-circle';
                                        } elseif (strpos($type, 'BACKGROUND') !== false) {
                                            $badgeColor = 'primary';
                                            $icon = 'fa-pause-circle';
                                        } elseif (strpos($type, 'STOP') !== false || strpos($type, 'DESTROY') !== false) {
                                            $badgeColor = 'danger';
                                            $icon = 'fa-stop-circle';
                                        }
                                    ?>
                                        <div>
                                            <i class="fas <?= $icon ?> bg-<?= $badgeColor ?>"></i>
                                            <div class="timeline-item shadow-none border">
                                                <span class="time text-muted small"><i class="far fa-clock mr-1"></i><?= esc($s['timestamp_display'] ?? '—') ?></span>
                                                <h3 class="timeline-header font-weight-bold" style="font-size:0.85rem; border-bottom:0;">
                                                    <span class="badge badge-<?= $badgeColor ?>"><?= $type ?></span>
                                                </h3>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div>
                                        <i class="far fa-clock bg-gray"></i>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php
} else {
    // Redesigned Summary Mode
?>
<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3">
                            <i class="fas fa-chart-bar text-primary mr-2"></i>App Usage
                        </h1>
                        <span class="badge badge-primary border p-2">
                            <i class="fas fa-database mr-1"></i>Total Checked Apps: <b><?= (int)$total ?></b>
                        </span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Foreground active screen time captured per application.</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?? '' ?></div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Informative Callout Alert -->
            <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #17a2b8;background:#fdfdfd;border-radius:4px;">
                <h5 class="font-weight-bold text-info"><i class="fas fa-chart-line mr-2"></i>App Foreground Screen Time &amp; Telemetry Diagnostics</h5>
                <p class="text-secondary mb-2" style="font-size:14px;">Audits application active foreground screen time metrics per package. Helps identify high-engagement apps, usage patterns, and OS power management state shifts.</p>
                <div class="row" style="font-size:12px;">
                    <div class="col-md-4 border-right">
                        <b class="d-block mb-1">Cumulative Screen Time:</b>
                        <span class="text-muted">Reported by Android <code>UsageStatsManager</code> as total active foreground time accumulated since last device boot or OS stats reset.</span>
                    </div>
                    <div class="col-md-4 border-right">
                        <b class="d-block mb-1">Interval Usage Delta:</b>
                        <span class="text-muted">Measures actual foreground screen duration spent during the window between successive telemetry extractions.</span>
                    </div>
                    <div class="col-md-4">
                        <b class="d-block mb-1">Background / Standby Estimate:</b>
                        <span class="text-muted">Calculates elapsed time where the application was idle or running in background without active user interaction.</span>
                    </div>
                </div>
            </div>

            <!-- Interactive App Usage List -->
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header d-flex align-items-center py-2">
                    <h5 class="card-title font-weight-bold mb-0 text-primary"><i class="fas fa-list mr-2"></i>Applications List</h5>
                    <div class="card-tools ml-auto">
                        <input type="text" id="appUsageSearch" class="form-value form-control form-control-sm" placeholder="Search apps...">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>App / Package</th>
                                    <th>Screen Time</th>
                                    <th>Last Used</th>
                                    <th>Snapshots</th>
                                    <th>Type</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="appUsageTableBody">
                                <?php if (empty($rows)): ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted">No application usage details logged.</td></tr>
                                <?php else: foreach ($rows as $row): 
                                    $pkgEnc = $row['package_url_enc'] ?? '';
                                    $appName = $row['app_name'] ?? '';
                                    $pkgName = $row['package_name'] ?? '';
                                ?>
                                    <tr class="app-usage-row" data-name="<?= esc(strtolower($appName)) ?>" data-package="<?= esc(strtolower($pkgName)) ?>">
                                        <td>
                                            <span class="font-weight-bold d-block text-dark"><?= esc($appName ?: 'Unknown App') ?></span>
                                            <small class="text-muted"><?= esc($pkgName) ?></small>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold"><?= round(((int)$row['foreground_time_ms']) / 3600000, 1) ?>h</span>
                                            <small class="text-muted d-block"><?= number_format($row['foreground_time_ms']) ?> ms</small>
                                        </td>
                                        <td><small class="text-muted"><?= esc($row['last_used_display'] ?? '—') ?></small></td>
                                        <td><span class="badge badge-info"><?= esc($row['snapshot_count']) ?></span></td>
                                        <td>
                                            <span class="badge badge-<?= !empty($row['is_system_app']) ? 'secondary' : 'primary' ?>">
                                                <?= !empty($row['is_system_app']) ? 'System' : 'User' ?>
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <div class="btn-group" role="group">
                                                <?php if ($pkgEnc !== ''): ?>
                                                    <a href="<?= base_url('advanced/software/app-usage/' . $pkgEnc) ?>" class="btn btn-sm btn-outline-info" title="View details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-outline-danger delete-app-usage-pkg" data-pkg="<?= esc($pkgEnc) ?>" data-name="<?= esc($appName ?: $pkgName) ?>" title="Delete record">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer py-2 bg-light">
                    <div class="float-right"><?= $pager->links('default', 'bootstrap5_full') ?></div>
                </div>
            </div>

        </div>
    </section>
</div>

<script>
var CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
var CSRF_TOKEN_HASH = '<?= csrf_hash() ?>';
document.addEventListener('DOMContentLoaded', function() {
    // Search Filter
    const searchInput = document.getElementById('appUsageSearch');
    const tableRows = document.querySelectorAll('.app-usage-row');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            tableRows.forEach(row => {
                const name = row.getAttribute('data-name');
                const pkg = row.getAttribute('data-package');
                if (name.includes(query) || pkg.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Delete Trigger
    document.querySelectorAll('.delete-app-usage-pkg').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var pkg = this.getAttribute('data-pkg');
            var name = this.getAttribute('data-name');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete All App Usage?',
                    html: 'This will permanently delete <strong>ALL</strong> usage snapshots for <strong>"' + name + '"</strong> and all of its associated data.'
                       + '<br><span class="text-danger mt-1 d-inline-block"><i class="fas fa-exclamation-triangle mr-1"></i>This action cannot be undone.</span>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        var postData = new URLSearchParams();
                        postData.append(CSRF_TOKEN_NAME, CSRF_TOKEN_HASH);
                        fetch('<?= base_url('advanced/software/app-usage/delete-package') ?>/' + pkg, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: postData
                        }).then(function(r) { return r.json(); }).then(function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', 'All usage data for "' + name + '" has been deleted.', 'success').then(function() {
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
<?php
}
?>
