<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <h1><i class="fas fa-globe text-info mr-2"></i>Geography & Carriers</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/fleet') ?>">Fleet</a></li>
                        <li class="breadcrumb-item active">Geography</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content pt-1">
        <div class="container-fluid">
            <!-- Geography Callout -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="callout callout-danger">
                        <h5><i class="fas fa-globe mr-2"></i>Geography & Carrier Intelligence</h5>
                        <p class="mb-1">This view shows the geographic distribution and carrier breakdown of your device fleet. Use it to identify regional concentrations, roaming devices, and carrier-specific issues.</p>
                        <ul class="mb-0 small">
                            <li><strong>Countries:</strong> Unique countries where devices have synced. High concentrations may indicate regional deployments or testing.</li>
                            <li><strong>Carriers:</strong> Mobile network operators. Devices on same carrier may share network characteristics or APN settings.</li>
                            <li><strong>Total Devices:</strong> Sum of all devices across all countries/carriers.</li>
                            <li><strong>Roaming Risk:</strong> Devices appearing in unexpected countries may indicate travel, VPN use, or location spoofing.</li>
                        </ul>
                        <p class="mb-0 small mt-1"><strong>Tip:</strong> Use the pie chart for country distribution and bar chart for top carriers. Export CSV for offline analysis.</p>
                    </div>
                </div>
            </div>

            <!-- Summary Cards (small-box style) -->
            <div class="row mb-2">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= count($countries) ?></h3>
                            <p>Countries</p>
                        </div>
                        <div class="icon"><i class="fas fa-globe"></i></div>
                        <div class="small-box-footer">
                            Unique Countries
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><?= count($carriers) ?></h3>
                            <p>Carriers</p>
                        </div>
                        <div class="icon"><i class="fas fa-signal"></i></div>
                        <div class="small-box-footer">
                            Unique Carriers
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= array_sum(array_column($countries, 'device_count')) ?></h3>
                            <p>Total Devices</p>
                        </div>
                        <div class="icon"><i class="fas fa-mobile-alt"></i></div>
                        <div class="small-box-footer">
                            Total Tracked
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $roaming_count ?? 0 ?></h3>
                            <p>Roaming Risk</p>
                        </div>
                        <div class="icon"><i class="fas fa-plane"></i></div>
                        <div class="small-box-footer">
                            SIM / Network Mismatch
                        </div>
                    </div>
                </div>
            </div>

            <!-- Countries Table -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-flag mr-2"></i>Countries</h3>
                            <a href="<?= base_url('superadmin/fleet/export') ?>?type=countries" class="btn btn-sm btn-primary">
                                <i class="fas fa-download mr-1"></i> Export
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm" id="countriesTable">
                                    <thead>
                                        <tr>
                                            <th>Country</th>
                                            <th class="text-right">Devices</th>
                                            <th class="text-right">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $totalDevices = array_sum(array_column($countries, 'device_count'));
                                        foreach ($countries as $country):
                                            $pct = $totalDevices > 0 ? round(($country['device_count'] / $totalDevices) * 100, 1) : 0;
                                        ?>
                                            <tr>
                                                <td>
                                                    <i class="fas fa-flag mr-2"></i><?= htmlspecialchars($country['country'] ?: 'Unknown') ?>
                                                </td>
                                                <td class="text-right"><?= number_format($country['device_count']) ?></td>
                                                <td class="text-right"><?= $pct ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carriers Table -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-signal mr-2"></i>Carriers</h3>
                            <a href="<?= base_url('superadmin/fleet/export') ?>?type=carriers" class="btn btn-sm btn-primary">
                                <i class="fas fa-download mr-1"></i> Export
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm" id="carriersTable">
                                    <thead>
                                        <tr>
                                            <th>Carrier</th>
                                            <th class="text-right">Devices</th>
                                            <th class="text-right">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $totalCarriers = array_sum(array_column($carriers, 'device_count'));
                                        foreach ($carriers as $carrier):
                                            $pct = $totalCarriers > 0 ? round(($carrier['device_count'] / $totalCarriers) * 100, 1) : 0;
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($carrier['network_operator'] ?: 'Unknown') ?></td>
                                                <td class="text-right"><?= number_format($carrier['device_count']) ?></td>
                                                <td class="text-right"><?= $pct ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row mt-2">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0"><i class="fas fa-chart-pie mr-2"></i>Device Distribution by Country</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 350px; position: relative;">
                                <canvas id="countryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0"><i class="fas fa-chart-bar mr-2"></i>Top Carriers</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 350px; position: relative;">
                                <canvas id="carrierChart"></canvas>
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
    // Country Chart
    const countryData = <?= json_encode($countries) ?>;
    const countryLabels = countryData.slice(0, 10).map(c => c.country || 'Unknown');
    const countryValues = countryData.slice(0, 10).map(c => c.device_count);

    new Chart(document.getElementById('countryChart'), {
        type: 'pie',
        data: {
            labels: countryLabels,
            datasets: [{
                data: countryValues,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(199, 199, 199, 0.7)',
                    'rgba(83, 102, 255, 0.7)',
                    'rgba(255, 99, 255, 0.7)',
                    'rgba(99, 255, 132, 0.7)'
                ]
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Carrier Chart
    const carrierData = <?= json_encode($carriers) ?>;
    const carrierLabels = carrierData.slice(0, 10).map(c => c.network_operator || 'Unknown');
    const carrierValues = carrierData.slice(0, 10).map(c => c.device_count);

    new Chart(document.getElementById('carrierChart'), {
        type: 'bar',
        data: {
            labels: carrierLabels,
            datasets: [{
                label: 'Devices',
                data: carrierValues,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });
});
</script>

<script>
$(function() {
    $('#countriesTable, #carriersTable').DataTable({
        order: [[1, 'desc']],
        pageLength: 25,
        responsive: true
    });
});
</script>