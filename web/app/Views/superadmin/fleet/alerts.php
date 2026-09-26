<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <h1><i class="fas fa-bell text-warning mr-2"></i>Alert Center</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/fleet') ?>">Fleet</a></li>
                        <li class="breadcrumb-item active">Alerts</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content pt-1">
        <div class="container-fluid">
            <!-- Alert Center Callout -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="callout callout-danger">
                        <h5><i class="fas fa-bell mr-2"></i>Alert Center</h5>
                        <p class="mb-1">The Alert Center aggregates fleet-wide anomalies detected in real-time. Alerts are generated dynamically from current device state - no historical storage, always current.</p>
                        <ul class="mb-0 small">
                            <li><strong>Stale Devices:</strong> Devices with no successful extraction in 24+ hours. May indicate device loss, connectivity issues, app uninstall, or user inactivity.</li>
                            <li><strong>Security Alerts:</strong> Rooted devices, debuggable apps, outdated security patches (>90 days), disabled encryption, or sideloaded apps. Each poses data integrity risks.</li>
                            <li><strong>Storage Alerts:</strong> Devices with <1GB free space. May cause upload failures, incomplete extractions, or app crashes.</li>
                            <li><strong>Outdated Patches:</strong> Devices with security patches older than 90 days. Vulnerable to known CVEs.</li>
                        </ul>
                        <p class="mb-0 small mt-1"><strong>Workflow:</strong> Click "View" on any summary alert to see affected devices. Use the filter buttons (Stale/Security/Storage) to narrow the detailed table. Click "View" on any row to jump to Device Detail.</p>
                    </div>
                </div>
            </div>

            <!-- Summary Alerts (small-box style) -->
            <div class="row mb-2">
                <?php foreach ($summary_alerts as $alert): ?>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <a href="<?= base_url($alert['route']) ?>" class="small-box bg-<?= $alert['type'] ?>">
                            <div class="inner">
                                <h3><i class="fas fa-exclamation-triangle fa-2x"></i></h3>
                                <p><?= $alert['title'] ?></p>
                            </div>
                            <div class="icon"><i class="fas fa-exclamation-triangle fa-3x"></i></div>
                            <div class="small-box-footer">
                                <?= $alert['message'] ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Detailed Alerts Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-list mr-2"></i>All Alerts</h3>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary" data-filter="all">All</button>
                                <button class="btn btn-outline-danger" data-filter="stale">Stale</button>
                                <button class="btn btn-outline-danger" data-filter="security">Security</button>
                                <button class="btn btn-outline-warning" data-filter="storage">Storage</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm" id="alertsTable">
                                    <thead>
                                        <tr>
                                            <th>Severity</th>
                                            <th>Type</th>
                                            <th>Device</th>
                                            <th>Message</th>
                                            <th>Last Sync</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detailed_alerts as $alert): ?>
                                            <tr data-type="<?= $alert['type'] ?>">
                                                <td>
                                                    <span class="badge badge-<?= $alert['severity'] ?>">
                                                        <i class="fas fa-exclamation-circle mr-1"></i><?= ucfirst($alert['severity']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= $alert['type'] === 'stale' ? 'warning' : ($alert['type'] === 'security' ? 'danger' : 'info') ?>">
                                                        <?= ucfirst($alert['type']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <code class="small"><?= htmlspecialchars(substr($alert['device_id'], 0, 16)) ?>...</code>
                                                </td>
                                                <td><?= $alert['message'] ?></td>
                                                <td>
                                                    <?php if (!empty($alert['last_sync'])): ?>
                                                        <small><?= $alert['last_sync'] ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted">Never</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?= base_url('superadmin/fleet/device/' . $alert['device_id']) ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </a>
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

            <!-- Alert Statistics -->
            <div class="row mt-2">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0"><i class="fas fa-chart-pie mr-2"></i>Alerts by Type</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 300px; position: relative;">
                                <canvas id="alertTypeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Alerts by Severity</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 300px; position: relative;">
                                <canvas id="alertSeverityChart"></canvas>
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
    // Count alerts by type
    const typeCounts = { stale: 0, security: 0, storage: 0 };
    const severityCounts = { danger: 0, warning: 0, info: 0 };
    
    <?= json_encode($detailed_alerts) ?>.forEach(a => {
        typeCounts[a.type] = (typeCounts[a.type] || 0) + 1;
        severityCounts[a.severity] = (severityCounts[a.severity] || 0) + 1;
    });

    // Alert Type Chart
    new Chart(document.getElementById('alertTypeChart'), {
        type: 'doughnut',
        data: {
            labels: ['Stale', 'Security', 'Storage'],
            datasets: [{
                data: [typeCounts.stale || 0, typeCounts.security || 0, typeCounts.storage || 0],
                backgroundColor: ['rgba(255, 193, 7, 0.7)', 'rgba(220, 53, 69, 0.7)', 'rgba(23, 162, 184, 0.7)']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Severity Chart
    new Chart(document.getElementById('alertSeverityChart'), {
        type: 'bar',
        data: {
            labels: ['Danger', 'Warning', 'Info'],
            datasets: [{
                label: 'Alerts',
                data: [severityCounts.danger || 0, severityCounts.warning || 0, severityCounts.info || 0],
                backgroundColor: ['rgba(220, 53, 69, 0.7)', 'rgba(255, 193, 7, 0.7)', 'rgba(23, 162, 184, 0.7)']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });
});
</script>

<script>
$(function() {
    $('#alertsTable').DataTable({
        order: [[1, 'asc']],
        pageLength: 25,
        responsive: true
    });

    // Filter buttons
    $('.btn-group button').on('click', function() {
        const filter = $(this).data('filter');
        $('.btn-group button').removeClass('active');
        $(this).addClass('active');
        
        $('#alertsTable tbody tr').each(function() {
            const type = $(this).data('type');
            if (filter === 'all' || type === filter) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>