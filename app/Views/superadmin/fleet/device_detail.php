<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <h1><i class="fas fa-search-location text-primary mr-2"></i>Device Detail</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/fleet') ?>">Fleet</a></li>
                        <li class="breadcrumb-item active">Device Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content pt-1">
        <div class="container-fluid">
            <!-- Device Detail Callout -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="callout callout-danger">
                        <h5><i class="fas fa-search-location mr-2"></i>Device Detail Analysis</h5>
                        <p class="mb-1">This page provides a comprehensive forensic view of a single device. All metrics reflect the latest extraction (deduplicated by device_id). Use this view to assess device health, security posture, data collection completeness, and sync reliability.</p>
                        <ul class="mb-0 small">
                            <li><strong>Identity Metrics:</strong> Android version, brand, model, and owner linkage. Unlinked devices may indicate registration issues.</li>
                            <li><strong>Health Metrics:</strong> Last sync time, battery level, free storage, and carrier. Stale syncs (>24h), low battery, or low storage may indicate collection issues.</li>
                            <li><strong>Security Profile:</strong> Root status, debuggable flag, security patch age, encryption, screen lock, VPN, ADB. Red badges = immediate attention needed.</li>
                            <li><strong>Hardware & Build:</strong> CPU, RAM, storage capacity, display, sensors, build fingerprint. Useful for forensic capability assessment.</li>
                            <li><strong>Data Collected:</strong> Per-category record counts. Zero counts may indicate permission issues or disabled extractions.</li>
                            <li><strong>Recent Uploads:</strong> Recent extraction jobs with status, size, and timestamp. Failed uploads may indicate device connectivity or permission issues.</li>
                            <li><strong>Sync History:</strong> 30-day sync frequency chart. Gaps indicate device offline periods or collection failures.</li>
                        </ul>
                        <p class="mb-0 small mt-1"><strong>Tip:</strong> Click "View" on any upload to inspect raw data. Use Security Profile badges (red = issue) to prioritize remediation.</p>
                    </div>
                </div>
            </div>

            <!-- Device Profile Table (Compact) -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-mobile-alt mr-2"></i>Device Profile</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <th class="bg-light" style="width: 180px;">Field</th>
                                        <th class="bg-light" style="width: 150px;">Value</th>
                                        <th class="bg-light" style="width: 180px;">Description</th>
                                        <th class="bg-light" style="width: 180px;">Field</th>
                                        <th class="bg-light" style="width: 150px;">Value</th>
                                        <th class="bg-light" style="width: 180px;">Description</th>
                                    </tbody>
                                    <tr>
                                        <td class="bg-light"><strong>Device ID</strong></td>
                                        <td colspan="5"><code><?= htmlspecialchars($device['device_id']) ?></code></td>
                                    </tr>
                                    <tr>
                                        <td class="bg-light">Android Version</td>
                                        <td><?= $device['android_version'] ?? 'Unknown' ?></td>
                                        <td>OS Version</td>
                                        <td class="bg-light">Brand</td>
                                        <td><?= $device['device_brand'] ?? 'Unknown' ?></td>
                                        <td>Device Brand</td>
                                    </tr>
                                    <tr>
                                        <td class="bg-light">Model</td>
                                        <td><?= $device['device_model'] ?? 'Unknown' ?></td>
                                        <td>Device Model</td>
                                        <td class="bg-light">Owner</td>
                                        <td><?= $device['owner_id'] ? 'User #' . $device['owner_id'] : 'Unlinked' ?></td>
                                        <td><?= $device['owner_id'] ? 'Linked' : 'Unlinked' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bg-light">Last Sync</td>
                                        <td colspan="5">
                                            <?php
                                            $ts = $device['extraction_timestamp'] ?? 0;
                                            $parsed = $ts > 10000000000 ? date('M d, Y H:i', (int)($ts/1000)) : date('M d, Y H:i', (int)$ts);
                                            echo $parsed ?: 'Never';
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bg-light">Battery</td>
                                        <td><?= $device['battery_level'] ?? 0 ?>%</td>
                                        <td>Battery Level</td>
                                        <td class="bg-light">Free Space</td>
                                        <td><?= round($device['internal_storage_free_gb'] ?? 0, 1) ?> GB</td>
                                        <td>Available Storage</td>
                                    </tr>
                                    <tr>
                                        <td class="bg-light">Carrier</td>
                                        <td colspan="5"><?= $device['network_operator'] ?? 'Unknown' ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Callout -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="callout callout-danger">
                        <h5><i class="fas fa-shield-alt mr-2"></i>Security Profile</h5>
                        <p class="mb-1">This device's security posture is assessed based on root status, debug settings, security patch level, encryption, and app installation sources. Review each item below to assess risk.</p>
                        <ul class="mb-0 small">
                            <li><strong>Rooted:</strong> Root access bypasses OS security controls, allowing malicious apps full system access.</li>
                            <li><strong>Debuggable:</strong> Debuggable apps expose runtime internals, enabling reverse engineering and data extraction.</li>
                            <li><strong>Security Patch:</strong> Patches older than 90 days leave known vulnerabilities unpatched.</li>
                            <li><strong>Encryption:</strong> Full-disk encryption protects data at rest if device is lost/stolen.</li>
                            <li><strong>App Sources:</strong> Apps from unknown sources (sideloaded) bypass Play Store vetting.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Hardware Callout -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="callout callout-danger">
                        <h5><i class="fas fa-microchip mr-2"></i>Hardware & Build</h5>
                        <p class="mb-1">Device hardware specifications determine capability for data collection, storage, and forensic analysis. Key specs below.</p>
                        <ul class="mb-0 small">
                            <li><strong>CPU/ABI:</strong> Determines app compatibility and performance for data extraction tasks.</li>
                            <li><strong>RAM:</strong> Available memory affects concurrent extraction tasks and background processing.</li>
                            <li><strong>Storage:</strong> Total vs. free space determines how much forensic data can be retained locally.</li>
                            <li><strong>Build Fingerprint/ID:</strong> Uniquely identifies OS build for vulnerability correlation and OS version tracking.</li>
                            <li><strong>Sensors:</strong> Sensor count indicates available telemetry (location, motion, environment).</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Security Profile Details -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-shield-alt mr-2"></i>Security Profile</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <table class="table table-borderless table-sm">
                                        <tbody>
                                            <tr>
                                                <td>Rooted</td>
                                                <td class="text-right">
                                                    <span class="badge badge-<?= ($device['is_rooted'] ?? 0) ? 'danger' : 'success' ?>">
                                                        <?= ($device['is_rooted'] ?? 0) ? 'Yes' : 'No' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Debuggable</td>
                                                <td class="text-right">
                                                    <span class="badge badge-<?= ($device['is_debuggable'] ?? 0) ? 'warning' : 'success' ?>">
                                                        <?= ($device['is_debuggable'] ?? 0) ? 'Yes' : 'No' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>App Installer</td>
                                                <td class="text-right">
                                                    <small><?= $device['app_installer'] ?? 'Unknown' ?></small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Security Patch</td>
                                                <td class="text-right">
                                                    <span class="badge badge-<?= !empty($device['android_security_patch']) && strtotime($device['android_security_patch']) > strtotime('-90 days') ? 'success' : 'danger' ?>">
                                                        <?= $device['android_security_patch'] ?: 'Unknown' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-6">
                                    <table class="table table-borderless table-sm">
                                        <tbody>
                                            <tr>
                                                <td>Screen Lock</td>
                                                <td class="text-right">
                                                    <span class="badge badge-<?= ($device['has_screen_lock'] ?? 0) ? 'success' : 'danger' ?>">
                                                        <?= ($device['has_screen_lock'] ?? 0) ? 'Enabled' : 'Disabled' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Encryption</td>
                                                <td class="text-right">
                                                    <span class="badge badge-<?= ($device['is_encrypted'] ?? 0) ? 'success' : 'warning' ?>">
                                                        <?= ($device['is_encrypted'] ?? 0) ? 'Enabled' : 'Unknown' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>VPN Active</td>
                                                <td class="text-right">
                                                    <span class="badge badge-<?= ($device['vpn_active'] ?? 0) ? 'info' : 'secondary' ?>">
                                                        <?= ($device['vpn_active'] ?? 0) ? 'Yes' : 'No' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>ADB Enabled</td>
                                                <td class="text-right">
                                                    <span class="badge badge-<?= ($device['adb_enabled'] ?? 0) ? 'warning' : 'success' ?>">
                                                        <?= ($device['adb_enabled'] ?? 0) ? 'Yes' : 'No' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hardware & Build Details -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-microchip mr-2"></i>Hardware & Build</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm">
                                <tbody>
                                    <tr>
                                        <td>CPU</td>
                                        <td class="text-right"><small><?= $device['cpu_abi'] ?? 'Unknown' ?></small></td>
                                    </tr>
                                    <tr>
                                        <td>Cores</td>
                                        <td class="text-right"><small><?= $device['cpu_cores'] ?? 'Unknown' ?></small></td>
                                    </tr>
                                    <tr>
                                        <td>RAM Total</td>
                                        <td class="text-right"><small><?= round(($device['memory_total_mb'] ?? 0) / 1024, 1) ?> GB</small></td>
                                    </tr>
                                    <tr>
                                        <td>Storage Total</td>
                                        <td class="text-right"><small><?= round(($device['internal_storage_total_gb'] ?? 0), 1) ?> GB</small></td>
                                    </tr>
                                    <tr>
                                        <td>Build Fingerprint</td>
                                        <td class="text-right"><small><?= substr($device['build_fingerprint'] ?? 'Unknown', 0, 32) ?>...</small></td>
                                    </tr>
                                    <tr>
                                        <td>Build ID</td>
                                        <td class="text-right"><small><?= $device['build_id'] ?? 'Unknown' ?></small></td>
                                    </tr>
                                    <tr>
                                        <td>Display</td>
                                        <td class="text-right"><small><?= $device['display_width'] ?? 0 ?>x<?= $device['display_height'] ?? 0 ?></small></td>
                                    </tr>
                                    <tr>
                                        <td>Sensors</td>
                                        <td class="text-right"><small><?= $device['sensor_count'] ?? 0 ?> sensors</small></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Collected & Recent Uploads (Merged Table) -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-database mr-2"></i>Data Collected & Recent Uploads</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th class="bg-light" style="width: 180px;">Field</th>
                                        <th class="bg-light" style="width: 150px;">Value</th>
                                        <th class="bg-light" style="width: 180px;">Description</th>
                                        <th class="bg-light" style="width: 180px;">Field</th>
                                        <th class="bg-light" style="width: 150px;">Value</th>
                                        <th class="bg-light" style="width: 180px;">Description</th>
                                    </tr>
                                    <tr>
                                        <td class="bg-light"><strong>Data Collected</strong></td>
                                        <td colspan="5"></td>
                                    </tr>
                                    <tr>
                                        <td class="bg-light">SMS</td>
                                        <td><?= number_format($data_counts['sms'] ?? 0) ?></td>
                                        <td>SMS Messages</td>
                                        <td class="bg-light">Calls</td>
                                        <td><?= number_format($data_counts['calls'] ?? 0) ?></td>
                                        <td>Call Logs</td>
                                    </tr>
                                    <tr>
                                        <td class="bg-light">Contacts</td>
                                        <td><?= number_format($data_counts['contacts'] ?? 0) ?></td>
                                        <td>Contact Records</td>
                                        <td class="bg-light">Locations</td>
                                        <td><?= number_format($data_counts['locations'] ?? 0) ?></td>
                                        <td>GPS Locations</td>
                                    </tr>
                                    <tr>
                                        <td class="bg-light">Apps</td>
                                        <td><?= number_format($data_counts['apps'] ?? 0) ?></td>
                                        <td>Installed Apps</td>
                                        <td class="bg-light">Files</td>
                                        <td><?= number_format($data_counts['files'] ?? 0) ?></td>
                                        <td>File Records</td>
                                    </tr>
                                    <tr>
                                        <td class="bg-light"><strong>Recent Uploads</strong></td>
                                        <td colspan="5"></td>
                                    </tr>
                                    <tr>
                                        <td class="bg-light">Recent Activity</td>
                                        <td colspan="5">
                                            <?php if (!empty($uploads)): ?>
                                                <span class="badge badge-success"><?= count(array_filter($uploads, fn($u) => ($u['status'] ?? '') === 'completed')) ?></span> Completed
                                                <span class="badge badge-danger ms-2"><?= count(array_filter($uploads, fn($u) => ($u['status'] ?? '') === 'failed')) ?></span> Failed
                                                <span class="badge badge-warning ms-2"><?= count(array_filter($uploads, fn($u) => ($u['status'] ?? '') === 'pending')) ?></span> Pending
                                            <?php else: ?>
                                                <span class="text-muted">No recent uploads</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-upload mr-2"></i>Recent Uploads</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Size</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($uploads, 0, 20) as $upload): ?>
                                            <tr>
                                                <td><?= ucfirst($upload['file_category'] ?? 'Unknown') ?></td>
                                                <td>
                                                    <span class="badge badge-<?= 
                                                        $upload['status'] === 'completed' ? 'success' : 
                                                        ($upload['status'] === 'failed' ? 'danger' : 'warning') ?>">
                                                        <?= ucfirst($upload['status'] ?? 'pending') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?= isset($upload['file_size_bytes']) ? round($upload['file_size_bytes'] / 1024 / 1024, 2) . ' MB' : '—' ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <small><?= $upload['queued_at'] ?? '—' ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sync History Chart -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-chart-line mr-2"></i>Sync History (Last 30 Days)</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 300px; position: relative;">
                                <canvas id="deviceSyncChart"></canvas>
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
    // Generate mock sync history for the device
    // In production, this would come from the controller
    const dates = [];
    const syncs = [];
    for (let i = 29; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        dates.push(d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        syncs.push(Math.floor(Math.random() * 5));
    }

    new Chart(document.getElementById('deviceSyncChart'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Daily Syncs',
                data: syncs,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});
</script>