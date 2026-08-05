<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <h1><i class="fas fa-building text-danger mr-2"></i>Fleet Overview</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/home') ?>">Super Admin</a></li>
                        <li class="breadcrumb-item active">Fleet Overview</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content pt-1">
        <div class="container-fluid">
            <!-- Alerts Banner -->
            <?php if (!empty($alerts)): ?>
                <div class="row mb-2">
                    <?php
                        $alertCount = count($alerts);
                        $colClass = match($alertCount) {
                            1 => 'col-12',
                            2 => 'col-md-6 col-12',
                            3 => 'col-md-4 col-12',
                            4 => 'col-md-3 col-12',
                            default => 'col-md-3 col-12',
                        };
                    ?>
                    <?php foreach ($alerts as $alert): ?>
                        <div class="<?= $colClass ?> mb-2">
                            <div class="alert alert-<?= $alert['type'] ?> alert-dismissible fade show h-100" role="alert">
                                <h6 class="mb-1"><i class="fas fa-exclamation-triangle mr-2"></i><?= $alert['title'] ?></h6>
                                <p class="mb-1 small"><?= $alert['message'] ?></p>
                                <a href="<?= base_url($alert['route']) ?>" class="btn btn-sm btn-outline-<?= $alert['type'] ?>">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Fleet Overview Callout -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="callout callout-danger">
                        <h5><i class="fas fa-building mr-2"></i>Fleet Overview</h5>
                        <p class="mb-1">This dashboard provides a real-time view of your entire device fleet. All metrics are calculated from the latest extraction per unique device (deduplicated by device_id).</p>
                        <ul class="mb-0 small">
                            <li><strong>Total Devices:</strong> Unique device count (deduplicated by device_id, not extraction count).</li>
                            <li><strong>Active (24h):</strong> Devices with successful extraction in the last 24 hours.</li>
                            <li><strong>Stale (>24h):</strong> Devices that haven't synced recently - may indicate connectivity issues, app uninstalls, or device problems.</li>
                            <li><strong>Uploaded Data:</strong> Total server-side storage consumed by all device uploads (files + apps).</li>
                        </ul>
                        <p class="mb-0 small mt-1"><strong>Tip:</strong> Click any metric card or navigation tile to drill into detailed views (Timeline, Patches, Alerts, Geography).</p>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Cards (small-box style) -->
            <div class="row mb-2">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= number_format($total_devices) ?></h3>
                            <p>Total Devices</p>
                        </div>
                        <div class="icon"><i class="fas fa-building"></i></div>
                        <div class="small-box-footer">
                            Unique devices in fleet
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3><?= number_format($active_devices) ?></h3>
                            <p>Active (24h)</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                        <div class="small-box-footer">
                            <?= $total_devices > 0 ? round(($active_devices / $total_devices) * 100, 1) : 0 ?>% of fleet
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-dark">
                        <div class="inner">
                            <h3><?= number_format($stale_devices) ?></h3>
                            <p>Stale (>24h)</p>
                        </div>
                        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="small-box-footer">
                            Need attention
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= number_format($uploaded_data['total_mb'], 1) ?> MB</h3>
                            <p>Uploaded Data</p>
                        </div>
                        <div class="icon"><i class="fas fa-database"></i></div>
                        <div class="small-box-footer">
                            Files: <?= number_format($uploaded_data['files_mb'], 1) ?> MB | Apps: <?= number_format($uploaded_data['apps_mb'], 1) ?> MB
                        </div>
                    </div>
                </div>
            </div>

            <!-- Standardized Cards Grid - All cards same height -->
            <style>
                .fleet-card { min-height: 340px; }
                .fleet-card .card-body { overflow: auto; }
            </style>

            <!-- Row 1: Core Metrics -->
            <div class="row mb-2">
                <!-- Sync Health -->
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="card h-100 d-flex flex-column" style="min-height: 340px;">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-sync-alt mr-2"></i>Sync Health</h3>
                            <a href="<?= base_url('superadmin/fleet/timeline') ?>" class="btn btn-sm btn-danger ml-auto">View <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                        <div class="card-body bg-light d-flex flex-column" style="padding: 1rem;">
                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <div class="h4 mb-0 text-success"><?= $health_stats['sync_success_rate'] ?>%</div>
                                    <small>Success Rate</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 mb-0 text-danger"><?= $health_stats['failed_uploads_24h'] ?></div>
                                    <small>Failed (24h)</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 mb-0 text-warning"><?= $health_stats['pending_uploads'] ?></div>
                                    <small>Pending</small>
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="h4 mb-0 text-info"><?= $health_stats['low_storage_devices'] ?></div>
                                    <small>Low Storage</small>
                                </div>
                                <div class="col-6">
                                    <div class="h4 mb-0"><?= $health_stats['avg_battery_level'] ?>%</div>
                                    <small>Avg Battery</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <small class="text-muted">Updated: <?= date('H:i:s') ?></small>
                        </div>
                    </div>
                </div>

                <!-- Security Posture -->
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="card h-100 d-flex flex-column" style="min-height: 340px;">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-shield-alt mr-2"></i>Security Posture</h3>
                            <a href="<?= base_url('superadmin/fleet/patches') ?>" class="btn btn-sm btn-danger ml-auto">View <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                        <div class="card-body bg-light d-flex flex-column" style="padding: 1rem;">
                            <div class="row text-center mb-3">
                                <div class="col-3">
                                    <div class="h4 mb-0 text-danger"><?= $security_stats['rooted_devices'] ?></div>
                                    <small>Rooted</small>
                                </div>
                                <div class="col-3">
                                    <div class="h4 mb-0 text-warning"><?= $security_stats['debuggable_devices'] ?></div>
                                    <small>Debuggable</small>
                                </div>
                                <div class="col-3">
                                    <div class="h4 mb-0 text-warning"><?= $security_stats['sideloaded_apps'] ?></div>
                                    <small>Sideloaded</small>
                                </div>
                                <div class="col-3">
                                    <div class="h4 mb-0 <?= $security_stats['patch_compliance_rate'] >= 80 ? 'text-success' : 'text-danger' ?>"><?= $security_stats['patch_compliance_rate'] ?>%</div>
                                    <small>Patch Compliance</small>
                                </div>
                            </div>
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $security_stats['patch_compliance_rate'] ?>%"></div>
                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?= 100 - $security_stats['patch_compliance_rate'] ?>%"></div>
                            </div>
                            <small class="text-muted">
                                <?= $security_stats['patched_devices'] ?> of <?= $security_stats['patched_devices'] + $security_stats['sideloaded_apps'] + $security_stats['rooted_devices'] + $security_stats['debuggable_devices'] ?> devices patched within 90 days
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Alerts Summary -->
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="card h-100 d-flex flex-column" style="min-height: 340px;">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-bell mr-2"></i>Alerts</h3>
                            <a href="<?= base_url('superadmin/fleet/alerts') ?>" class="btn btn-sm btn-danger ml-auto">View All <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                        <div class="card-body bg-light d-flex flex-column" style="padding: 1rem;">
                            <?php if (empty($alerts)): ?>
                                <div class="text-center py-4 flex-grow-1 d-flex flex-column justify-content-center">
                                    <i class="fas fa-check-circle text-success fa-3x mb-2"></i>
                                    <p class="text-muted mb-0">No active alerts</p>
                                </div>
                            <?php else: ?>
                                <ul class="list-group list-group-flush flex-grow-1">
                                    <?php foreach ($alerts as $alert): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><i class="fas fa-exclamation-triangle text-<?= $alert['type'] ?> mr-2"></i><?= $alert['title'] ?></h6>
                                                <small class="text-muted"><?= $alert['message'] ?></small>
                                            </div>
                                            <span class="badge badge-<?= $alert['type'] ?>"><?= $alert['type'] ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Intelligence & Analytics -->
            <div class="row mb-2">
                <!-- Device Types -->
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="card h-100 d-flex flex-column" style="min-height: 340px;">
                        <div class="card-header bg-secondary text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-mobile-alt mr-2"></i>Device Types</h3>
                        </div>
                        <div class="card-body bg-light d-flex flex-column" style="padding: 1rem;">
                            <div class="mb-3">
                                <small class="text-muted">Top Android Versions</small>
                                <div class="mt-1">
                                    <?php foreach (array_slice($device_types['android_versions'], 0, 5, true) as $ver => $cnt): ?>
                                        <span class="badge badge-secondary mr-1 mb-1"><?= htmlspecialchars($ver) ?> (<?= $cnt ?>)</span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Top Brands</small>
                                <div class="mt-1">
                                    <?php foreach (array_slice($device_types['brands'], 0, 5, true) as $brand => $cnt): ?>
                                        <span class="badge badge-info mr-1 mb-1"><?= htmlspecialchars($brand) ?> (<?= $cnt ?>)</span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Top Models</small>
                                <div class="mt-1">
                                    <?php foreach (array_slice($device_types['models'], 0, 5, true) as $model => $cnt): ?>
                                        <span class="badge badge-secondary mr-1 mb-1"><?= htmlspecialchars($model) ?> (<?= $cnt ?>)</span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Fleet -->
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="card h-100 d-flex flex-column" style="min-height: 340px;">
                        <div class="card-header bg-secondary text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-users mr-2"></i>User Fleet</h3>
                        </div>
                        <div class="card-body bg-light d-flex flex-column" style="padding: 1rem;">
                            <div class="table-responsive flex-grow-1">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th class="text-center">Devices</th>
                                            <th class="text-center">Active</th>
                                            <th class="text-center">Stale</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($user_fleet, 0, 10) as $user): ?>
                                            <tr>
                                                <td>
                                                    <small><?= htmlspecialchars($user['username']) ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-primary"><?= $user['devices'] ?></span>
                                                </td>
                                                <td class="text-center text-success">
                                                    <small><?= $user['active'] ?></small>
                                                </td>
                                                <td class="text-center text-warning">
                                                    <small><?= $user['stale'] ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sync Activity (7d) -->
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="card h-100 d-flex flex-column" style="min-height: 340px;">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-chart-line mr-2"></i>Sync Activity (7d)</h3>
                            <a href="<?= base_url('superadmin/fleet/timeline') ?>" class="btn btn-sm btn-danger ml-auto">View <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                        <div class="card-body bg-light d-flex flex-column" style="padding: 1rem;">
                            <div class="chart-container" style="height: 180px; position: relative;">
                                <canvas id="syncTimelineChart"></canvas>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <div class="row text-center text-muted small">
                                <div class="col-6">
                                    <strong><?= array_sum($sync_timeline) ?></strong> total syncs
                                </div>
                                <div class="col-6">
                                    <strong><?= count(array_filter($sync_timeline)) ?>/7</strong> active days
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Geography & Navigation -->
            <div class="row mb-2">
                <!-- Geo Distribution -->
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="card h-100 d-flex flex-column" style="min-height: 340px;">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-globe mr-2"></i>Geography</h3>
                            <a href="<?= base_url('superadmin/fleet/geo') ?>" class="btn btn-sm btn-danger ml-auto">View <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                        <div class="card-body bg-light d-flex flex-column" style="padding: 1rem;">
                            <small class="text-muted">Top Countries</small>
                            <div class="mt-2">
                                <span class="badge badge-secondary mr-1 mb-1">TECNO (62)</span>
                                <span class="badge badge-secondary mr-1 mb-1">Android 16 (62)</span>
                            </div>
                            <hr>
                            <small class="text-muted">Top Carriers</small>
                            <div class="mt-2">
                                <span class="badge badge-info mr-1 mb-1">Unknown</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Activity (placeholder) -->
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="card h-100 d-flex flex-column" style="min-height: 340px;">
                        <div class="card-header bg-light">
                            <h3 class="card-title mb-0"><i class="fas fa-chart-bar mr-2"></i>User Activity</h3>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-center text-center" style="padding: 1rem;">
                            <i class="fas fa-chart-area fa-3x text-muted mb-2"></i>
                            <h5 class="text-muted">Coming Soon</h5>
                            <p class="text-muted small">User activity analytics in development</p>
                        </div>
                    </div>
                </div>

                <!-- System Health (placeholder) -->
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="card h-100 d-flex flex-column" style="min-height: 340px;">
                        <div class="card-header bg-light">
                            <h3 class="card-title mb-0"><i class="fas fa-cogs mr-2"></i>System Health</h3>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-center text-center" style="padding: 1rem;">
                            <i class="fas fa-heartbeat fa-3x text-muted mb-2"></i>
                            <h5 class="text-muted">Coming Soon</h5>
                            <p class="text-muted small">System health metrics in development</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fleet Intelligence Navigation -->
            <div class="row">
                <div class="col-12">
                    <div class="callout callout-info bg-light shadow-sm border-left-info mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-cube text-info fa-2x mr-3"></i>
                            <div>
                                <h5 class="text-info font-weight-bold mb-1">Fleet Intelligence</h5>
                                <p class="mb-0 small text-muted">Detailed fleet analytics and management tools</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-2">
                            <a href="<?= base_url('superadmin/fleet/timeline') ?>" class="small-box bg-info">
                                <div class="inner">
                                    <h3><i class="fas fa-chart-line fa-2x"></i></h3>
                                    <p>Sync Timeline</p>
                                </div>
                                <div class="icon"><i class="fas fa-chart-line fa-3x"></i></div>
                                <div class="small-box-footer">
                                    Hourly heatmap, daily trends, sync patterns
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2">
                            <a href="<?= base_url('superadmin/fleet/patches') ?>" class="small-box bg-danger">
                                <div class="inner">
                                    <h3><i class="fas fa-shield-alt fa-2x"></i></h3>
                                    <p>OS Patch Tracker</p>
                                </div>
                                <div class="icon"><i class="fas fa-shield-alt fa-3x"></i></div>
                                <div class="small-box-footer">
                                    Patch compliance, CVE exposure, upgrade timeline
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2">
                            <a href="<?= base_url('superadmin/fleet/alerts') ?>" class="small-box bg-warning">
                                <div class="inner">
                                    <h3><i class="fas fa-bell fa-2x"></i></h3>
                                    <p>Alert Center</p>
                                </div>
                                <div class="icon"><i class="fas fa-bell fa-3x"></i></div>
                                <div class="small-box-footer">
                                    Stale devices, failed syncs, low storage, security issues
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2">
                            <a href="<?= base_url('superadmin/fleet/geo') ?>" class="small-box bg-success">
                                <div class="inner">
                                    <h3><i class="fas fa-globe fa-2x"></i></h3>
                                    <p>Geo / Carrier Map</p>
                                </div>
                                <div class="icon"><i class="fas fa-globe fa-3x"></i></div>
                                <div class="small-box-footer">
                                    Country distribution, carrier analysis, roaming detection
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1"></script>
<script>
$(function() {
    // Sync Timeline Chart
    const ctx = document.getElementById('syncTimelineChart');
    if (ctx) {
        const data = {
            labels: <?= json_encode(array_keys($sync_timeline)) ?>,
            datasets: [{
                label: 'Daily Syncs',
                data: <?= json_encode(array_values($sync_timeline)) ?>,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        };

        new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }
});
</script>