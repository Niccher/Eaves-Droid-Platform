<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <h1><i class="fas fa-shield-alt text-danger mr-2"></i>OS Patch Tracker</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/fleet') ?>">Fleet</a></li>
                        <li class="breadcrumb-item active">Patches</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content pt-1">
        <div class="container-fluid">
            <!-- OS Patch Tracker Callout -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="callout callout-danger">
                        <h5><i class="fas fa-shield-alt mr-2"></i>OS Patch Compliance Tracker</h5>
                        <p class="mb-1">Tracks Android security patch compliance across the fleet. Devices with patches older than 90 days are considered non-compliant and may be vulnerable to known exploits.</p>
                        <ul class="mb-0 small">
                            <li><strong>Compliant:</strong> Security patch within last 90 days - protected against known vulnerabilities.</li>
                            <li><strong>Non-Compliant:</strong> Patch older than 90 days - exposed to publicly known vulnerabilities.</li>
                            <li><strong>Unknown:</strong> No patch data available - device may not report patch level or extraction failed.</li>
                            <li><strong>Total Devices:</strong> Total unique devices tracked for patch compliance.</li>
                        </ul>
                        <p class="mb-0 small mt-1"><strong>Action:</strong> Prioritize non-compliant devices for OS updates. Use the table below to identify specific devices and Android versions needing attention.</p>
                    </div>
                </div>
            </div>

            <!-- Summary Cards (small-box style) -->
            <div class="row mb-2">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $compliant_count ?></h3>
                            <p>Compliant</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                        <div class="small-box-footer">
                            <?= $total_devices > 0 ? round(($compliant_count / $total_devices) * 100, 1) : 0 ?>%
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $non_compliant_count ?></h3>
                            <p>Non-Compliant</p>
                        </div>
                        <div class="icon"><i class="fas fa-times-circle"></i></div>
                        <div class="small-box-footer">
                            Patch > 90 days
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $total_devices - $compliant_count - $non_compliant_count ?></h3>
                            <p>Unknown</p>
                        </div>
                        <div class="icon"><i class="fas fa-question-circle"></i></div>
                        <div class="small-box-footer">
                            No patch data
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $total_devices ?></h3>
                            <p>Total Devices</p>
                        </div>
                        <div class="icon"><i class="fas fa-building"></i></div>
                        <div class="small-box-footer">
                            Tracked
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patch Details Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-table mr-2"></i>Patch Details</h3>
                            <div>
                                <a href="<?= base_url('superadmin/fleet/export') ?>?type=patches" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download mr-1"></i> Export CSV
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm" id="patchesTable">
                                    <thead>
                                        <tr>
                                            <th>Device ID</th>
                                            <th>User</th>
                                            <th>Android Version</th>
                                            <th>Patch Date</th>
                                            <th>Days Old</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_patches as $patch): ?>
                                            <tr>
                                                <td>
                                                    <code class="small"><?= htmlspecialchars(substr($patch['device_id'], 0, 16)) ?>...</code>
                                                </td>
                                                <td>
                                                    <?php if ($patch['owner_id'] > 0): ?>
                                                        <a href="<?= base_url('superadmin/fleet/user/' . $patch['owner_id']) ?>">User #<?= $patch['owner_id'] ?></a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Unlinked</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary"><?= htmlspecialchars($patch['android_version']) ?></span>
                                                </td>
                                                <td><?= $patch['patch_date'] !== 'Unknown' ? date('M d, Y', strtotime($patch['patch_date'])) : '<span class="text-muted">Unknown</span>' ?></td>
                                                <td>
                                                    <?php if ($patch['days_old'] !== null): ?>
                                                        <span class="<?= $patch['days_old'] > 90 ? 'text-danger font-weight-bold' : ($patch['days_old'] > 60 ? 'text-warning' : 'text-success') ?>">
                                                            <?= $patch['days_old'] ?> days
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($patch['patch_date'] === 'Unknown'): ?>
                                                        <span class="badge badge-secondary">Unknown</span>
                                                    <?php elseif ($patch['compliant']): ?>
                                                        <span class="badge badge-success">Compliant</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Non-Compliant</span>
                                                    <?php endif; ?>
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

            <!-- Compliance by Android Version -->
            <div class="row mt-2">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0"><i class="fas fa-chart-pie mr-2"></i>Compliance by Android Version</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 300px; position: relative;">
                                <canvas id="versionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0"><i class="fas fa-chart-line mr-2"></i>Patch Age Distribution</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 300px; position: relative;">
                                <canvas id="ageChart"></canvas>
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
    // Compliance by Android Version
    const versions = {};
    <?= json_encode($all_patches) ?>.forEach(p => {
        if (!versions[p.android_version]) {
            versions[p.android_version] = { compliant: 0, non: 0 };
        }
        if (p.patch_date === 'Unknown') {
            // skip
        } else if (p.compliant) {
            versions[p.android_version].compliant++;
        } else {
            versions[p.android_version].non++;
        }
    });

    const versionLabels = Object.keys(versions);
    const compliantData = versionLabels.map(v => versions[v].compliant);
    const nonCompliantData = versionLabels.map(v => versions[v].non);

    new Chart(document.getElementById('versionChart'), {
        type: 'bar',
        data: {
            labels: versionLabels,
            datasets: [
                { label: 'Compliant', data: compliantData, backgroundColor: 'rgba(40, 167, 69, 0.7)' },
                { label: 'Non-Compliant', data: nonCompliantData, backgroundColor: 'rgba(220, 53, 69, 0.7)' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, stacked: true }, x: { stacked: true } }
        }
    });

    // Patch Age Distribution
    const ageBuckets = { '0-30': 0, '31-60': 0, '61-90': 0, '91-180': 0, '181+': 0, 'Unknown': 0 };
    <?= json_encode($all_patches) ?>.forEach(p => {
        if (p.patch_date === 'Unknown') {
            ageBuckets['Unknown']++;
        } else if (p.days_old === null) {
            ageBuckets['Unknown']++;
        } else if (p.days_old <= 30) ageBuckets['0-30']++;
        else if (p.days_old <= 60) ageBuckets['31-60']++;
        else if (p.days_old <= 90) ageBuckets['61-90']++;
        else if (p.days_old <= 180) ageBuckets['91-180']++;
        else ageBuckets['181+']++;
    });

    new Chart(document.getElementById('ageChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(ageBuckets),
            datasets: [{
                data: Object.values(ageBuckets),
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(255, 152, 0, 0.7)',
                    'rgba(220, 53, 69, 0.7)',
                    'rgba(138, 43, 226, 0.7)',
                    'rgba(108, 117, 125, 0.7)'
                ]
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>

<script>
$(function() {
    $('#patchesTable').DataTable({
        order: [[4, 'desc']],
        pageLength: 25,
        responsive: true
    });
});
</script>