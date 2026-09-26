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
                                <p class="mb-0 small"><?= $alert['message'] ?></p>
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
                            FilesController: <?= number_format($uploaded_data['files_mb'], 1) ?> MB | Apps: <?= number_format($uploaded_data['apps_mb'], 1) ?> MB
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
            <div class="row mb-3">
                <!-- Sync Health -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-primary h-100 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h3 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-sync-alt text-primary mr-2"></i>Sync Health
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-success px-2 py-1"><?= $health_stats['sync_success_rate'] ?>% Rate</span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="row text-center mb-3">
                                <div class="col-4 border-right">
                                    <h4 class="font-weight-bold text-success mb-0"><?= $health_stats['sync_success_rate'] ?>%</h4>
                                    <small class="text-muted text-uppercase font-weight-bold" style="font-size:10px;">Success</small>
                                </div>
                                <div class="col-4 border-right">
                                    <h4 class="font-weight-bold text-danger mb-0"><?= $health_stats['failed_uploads_24h'] ?></h4>
                                    <small class="text-muted text-uppercase font-weight-bold" style="font-size:10px;">Failed 24h</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="font-weight-bold text-warning mb-0"><?= $health_stats['pending_uploads'] ?></h4>
                                    <small class="text-muted text-uppercase font-weight-bold" style="font-size:10px;">Pending</small>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted"><i class="fas fa-hdd text-info mr-1"></i>Low Free Storage Devices:</span>
                                <span class="badge badge-info font-weight-bold"><?= $health_stats['low_storage_devices'] ?> device(s)</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted"><i class="fas fa-battery-half text-warning mr-1"></i>Avg Battery Level:</span>
                                <span class="badge badge-dark font-weight-bold"><?= $health_stats['avg_battery_level'] ?>%</span>
                            </div>
                        </div>
                        <div class="card-footer bg-light py-2">
                            <small class="text-muted"><i class="far fa-clock mr-1"></i>Updated: <?= date('H:i:s') ?></small>
                        </div>
                    </div>
                </div>

                <!-- Security Posture -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-danger h-100 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h3 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-shield-alt text-danger mr-2"></i>Security Posture
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-<?= $security_stats['patch_compliance_rate'] >= 80 ? 'success' : 'danger' ?> px-2 py-1">
                                    <?= $security_stats['patch_compliance_rate'] ?>% Patch
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="row text-center mb-3">
                                <div class="col-4 border-right">
                                    <h4 class="font-weight-bold text-danger mb-0"><?= $security_stats['rooted_devices'] ?></h4>
                                    <small class="text-muted text-uppercase font-weight-bold" style="font-size:10px;">Rooted</small>
                                </div>
                                <div class="col-4 border-right">
                                    <h4 class="font-weight-bold text-warning mb-0"><?= $security_stats['debuggable_devices'] ?></h4>
                                    <small class="text-muted text-uppercase font-weight-bold" style="font-size:10px;">Debuggable</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="font-weight-bold text-secondary mb-0"><?= $security_stats['sideloaded_apps'] ?></h4>
                                    <small class="text-muted text-uppercase font-weight-bold" style="font-size:10px;">Sideloaded</small>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted font-weight-bold">Security Patch Compliance:</small>
                                    <small class="font-weight-bold text-dark"><?= $security_stats['patched_devices'] ?> / <?= $security_stats['total_devices'] ?> Devices</small>
                                </div>
                                <div class="progress" style="height: 10px; border-radius: 5px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $security_stats['patch_compliance_rate'] ?>%"></div>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2" style="font-size:11px;">
                                <i class="fas fa-info-circle mr-1"></i>Devices with Android security patch within last 90 days.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Alerts Summary -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-warning h-100 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h3 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-bell text-warning mr-2"></i>Active Alerts
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-warning px-2 py-1"><?= count($alerts) ?> Issue(s)</span>
                            </div>
                        </div>
                        <div class="card-body p-3 d-flex flex-column">
                            <?php if (empty($alerts)): ?>
                                <div class="text-center py-4 my-auto">
                                    <i class="fas fa-check-circle text-success fa-3x mb-2"></i>
                                    <p class="text-muted font-weight-bold mb-0">All fleet systems operating normally.</p>
                                </div>
                            <?php else: ?>
                                <ul class="list-group list-group-flush w-100">
                                    <?php foreach ($alerts as $alert): ?>
                                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="d-block text-dark mb-0" style="font-size:13px;">
                                                    <i class="fas fa-exclamation-triangle text-<?= $alert['type'] ?> mr-1"></i><?= $alert['title'] ?>
                                                </strong>
                                                <small class="text-muted"><?= $alert['message'] ?></small>
                                            </div>
                                            <span class="badge badge-<?= $alert['type'] ?> text-uppercase"><?= $alert['type'] ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Intelligence & Analytics -->
            <div class="row mb-3">
                <!-- Device Types -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-info h-100 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h3 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-mobile-alt text-info mr-2"></i>Device Hardware Types
                            </h3>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size:10px;">Top Android OS Versions</small>
                                <div>
                                    <?php foreach (array_slice($device_types['android_versions'], 0, 5, true) as $ver => $cnt): ?>
                                        <span class="badge badge-secondary px-2 py-1 mr-1 mb-1 font-weight-bold">Android <?= htmlspecialchars($ver) ?> (<?= $cnt ?>)</span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size:10px;">Top Device Brands</small>
                                <div>
                                    <?php foreach (array_slice($device_types['brands'], 0, 5, true) as $brand => $cnt): ?>
                                        <span class="badge badge-info px-2 py-1 mr-1 mb-1 font-weight-bold"><?= htmlspecialchars($brand) ?> (<?= $cnt ?>)</span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size:10px;">Top Device Models</small>
                                <div>
                                    <?php foreach (array_slice($device_types['models'], 0, 5, true) as $model => $cnt): ?>
                                        <span class="badge badge-dark px-2 py-1 mr-1 mb-1 font-weight-bold"><?= htmlspecialchars($model) ?> (<?= $cnt ?>)</span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Fleet -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-primary h-100 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h3 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-users text-primary mr-2"></i>User Fleet Ownership
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0" style="font-size:13px;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>User</th>
                                            <th class="text-center">Devices</th>
                                            <th class="text-center">Active</th>
                                            <th class="text-center">Stale</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($user_fleet, 0, 8) as $user): ?>
                                            <tr>
                                                <td class="font-weight-bold text-dark"><?= htmlspecialchars($user['username']) ?></td>
                                                <td class="text-center"><span class="badge badge-primary px-2"><?= $user['devices'] ?></span></td>
                                                <td class="text-center text-success font-weight-bold"><?= $user['active'] ?></td>
                                                <td class="text-center text-warning font-weight-bold"><?= $user['stale'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sync Activity (7d) -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-success h-100 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h3 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-chart-line text-success mr-2"></i>Sync Activity (7 Days)
                            </h3>
                        </div>
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="chart-container flex-grow-1" style="height: 170px; position: relative;">
                                <canvas id="syncTimelineChart"></canvas>
                            </div>
                            <div class="row text-center mt-3 pt-2 border-top">
                                <div class="col-6 border-right">
                                    <span class="font-weight-bold text-dark h5 mb-0 d-block"><?= array_sum($sync_timeline) ?></span>
                                    <small class="text-muted text-uppercase" style="font-size:10px;">Total Syncs</small>
                                </div>
                                <div class="col-6">
                                    <span class="font-weight-bold text-success h5 mb-0 d-block"><?= count(array_filter($sync_timeline)) ?> / 7</span>
                                    <small class="text-muted text-uppercase" style="font-size:10px;">Active Days</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Geography & Activity -->
            <div class="row mb-3">
                <!-- Geo Distribution -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-secondary h-100 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h3 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-globe text-secondary mr-2"></i>Geography & Carriers
                            </h3>
                        </div>
                        <div class="card-body p-3">
                            <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size:10px;">Top Countries</small>
                            <div class="mb-3">
                                <?php if (!empty($geo_overview['top_countries'])): ?>
                                    <?php foreach ($geo_overview['top_countries'] as $c): ?>
                                        <span class="badge badge-secondary px-2 py-1 mr-1 mb-1 font-weight-bold">
                                            <i class="fas fa-flag mr-1"></i><?= htmlspecialchars(strtoupper($c['country'])) ?> (<?= $c['cnt'] ?>)
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <small class="text-muted d-block">No country data recorded</small>
                                <?php endif; ?>
                            </div>
                            <hr class="my-2">
                            <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size:10px;">Top Network Operators</small>
                            <div>
                                <?php if (!empty($geo_overview['top_carriers'])): ?>
                                    <?php foreach ($geo_overview['top_carriers'] as $car): ?>
                                        <span class="badge badge-info px-2 py-1 mr-1 mb-1 font-weight-bold">
                                            <i class="fas fa-signal mr-1"></i><?= htmlspecialchars($car['network_operator']) ?> (<?= $car['cnt'] ?>)
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <small class="text-muted d-block">No carrier operator data recorded</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin & Remote Activity -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-dark h-100 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h3 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-tasks text-dark mr-2"></i>Admin & Remote Activity
                            </h3>
                        </div>
                        <div class="card-body p-3">
                            <div class="row text-center mb-3">
                                <div class="col-6 border-right">
                                    <h4 class="font-weight-bold text-primary mb-0"><?= $user_activity['fcm_commands_24h'] ?></h4>
                                    <small class="text-muted text-uppercase font-weight-bold" style="font-size:10px;">FCM Commands (24h)</small>
                                </div>
                                <div class="col-6">
                                    <div class="h4 font-weight-bold text-info mb-0"><?= $user_activity['admin_actions_24h'] ?></div>
                                    <small class="text-muted text-uppercase font-weight-bold" style="font-size:10px;">Admin Logs (24h)</small>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted font-weight-bold">Top FCM Command:</small>
                                <span class="badge badge-dark px-2 py-1 font-weight-bold"><?= htmlspecialchars(strtoupper($user_activity['top_command'])) ?> (<?= $user_activity['top_command_count'] ?>)</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted font-weight-bold">Most Active Admin (7d):</small>
                                <span class="badge badge-success px-2 py-1 font-weight-bold"><?= htmlspecialchars($user_activity['top_admin']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System & Storage Health -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-info h-100 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h3 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-heartbeat text-info mr-2"></i>System & Storage Health
                            </h3>
                        </div>
                        <div class="card-body p-3">
                            <div class="row text-center mb-3">
                                <div class="col-6 border-right">
                                    <h4 class="font-weight-bold text-success mb-0"><?= $health_stats['avg_free_gb'] ?> GB</h4>
                                    <small class="text-muted text-uppercase font-weight-bold" style="font-size:10px;">Avg Free Storage</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="font-weight-bold text-warning mb-0"><?= $health_stats['avg_battery_level'] ?>%</h4>
                                    <small class="text-muted text-uppercase font-weight-bold" style="font-size:10px;">Avg Battery Level</small>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted font-weight-bold">FCM Reachable Devices:</small>
                                <div>
                                    <strong class="text-dark font-weight-bold" style="font-size:13px;"><?= $health_stats['fcm_reachable_devices'] ?></strong>
                                    <span class="badge badge-success ml-1"><?= $health_stats['fcm_reachability_rate'] ?>%</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted font-weight-bold">Pending Upload Jobs:</small>
                                <span class="badge badge-warning font-weight-bold"><?= $health_stats['pending_uploads'] ?> queued</span>
                            </div>
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