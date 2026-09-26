<style>
/* GitHub Style Heatmap (Kept as custom component) */
.heatmap-container {
    display: flex;
    justify-content: center;
    width: 100%;
    overflow-x: auto;
    padding-bottom: 10px;
}
.heatmap-grid {
    display: grid;
    grid-template-rows: repeat(7, 1fr);
    grid-auto-flow: column;
    gap: 4px;
}
.heatmap-cell {
    width: 14px;
    height: 14px;
    border-radius: 2px;
    background-color: #ebedf0;
    transition: all 0.2s ease;
    cursor: pointer;
}
.heatmap-cell:hover {
    transform: scale(1.2);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
.scale-1 { background-color: #cce5ff; }
.scale-2 { background-color: #66b2ff; }
.scale-3 { background-color: #007bff; }
.scale-4 { background-color: #0056b3; }

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}
</style>

<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-heartbeat text-primary mr-2"></i> Digital Wellbeing</h1>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <?php if(!$has_data): ?>
                <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-info"></i> No Usage Data Available</h5>
                    We haven't gathered enough app usage data yet. As you use your device and it syncs, your digital wellbeing metrics will appear here.
                </div>
            <?php else: ?>

            <!-- Info boxes -->
            <div class="row">
                <div class="col-12 col-sm-3">
                    <div class="info-box bg-danger shadow-sm">
                        <span class="info-box-icon"><i class="fas fa-gamepad"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Social & Gaming</span>
                            <span class="info-box-number"><?= $dopamine_hrs ?> Hours</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $dopamine_pct ?>%"></div>
                            </div>
                            <span class="progress-description"><?= $dopamine_pct ?>% of tracked time</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-3">
                    <div class="info-box bg-success shadow-sm">
                        <span class="info-box-icon"><i class="fas fa-briefcase"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Productivity & Work</span>
                            <span class="info-box-number"><?= $productivity_hrs ?> Hours</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $productivity_pct ?>%"></div>
                            </div>
                            <span class="progress-description"><?= $productivity_pct ?>% of tracked time</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-3">
                    <div class="info-box bg-info shadow-sm">
                        <span class="info-box-icon"><i class="fas fa-mobile-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Other Apps</span>
                            <span class="info-box-number"><?= $other_hrs ?> Hours</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $other_pct ?>%"></div>
                            </div>
                            <span class="progress-description"><?= $other_pct ?>% of tracked time</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-3">
                    <div class="info-box bg-warning shadow-sm">
                        <span class="info-box-icon text-white"><i class="fas fa-lock-open"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Device Unlocks</span>
                            <span class="info-box-number text-dark"><?= number_format($total_unlocks) ?> times</span>
                            <div class="progress">
                                <div class="progress-bar bg-dark" style="width: 100%"></div>
                            </div>
                            <span class="progress-description text-muted">Screen turns ON activations</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Heatmap Card -->
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Screen Time Intensity (Last 365 Days)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="heatmap-container">
                                <div class="heatmap-grid" id="heatmapGrid"></div>
                            </div>
                            <div class="d-flex justify-content-end align-items-center mt-3 text-sm">
                                <span class="mr-2">Less</span>
                                <div class="heatmap-cell mx-1"></div>
                                <div class="heatmap-cell mx-1 scale-1"></div>
                                <div class="heatmap-cell mx-1 scale-2"></div>
                                <div class="heatmap-cell mx-1 scale-3"></div>
                                <div class="heatmap-cell mx-1 scale-4"></div>
                                <span class="ml-2">More</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Doughnut Chart Card -->
                <div class="col-md-5">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Habit Split</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="habitChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Apps Chart Card -->
                <div class="col-md-7">
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-sort-amount-down mr-1"></i> Top Time Sinks</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="topAppsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(!empty($sleep)): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-moon mr-1"></i> Sleep Intervals</h3>
                            <span class="badge badge-info ml-2"><?= esc($wellbeing_depth_label) ?></span>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-striped">
                                <thead><tr><th>Date</th><th>Estimated Bedtime</th><th>Estimated Wake Time</th><th>Inactivity Duration</th></tr></thead>
                                <tbody>
                                <?php foreach ($sleep as $s): ?>
                                <tr>
                                    <td><?= esc($s['date']) ?></td>
                                    <td><?= esc($s['sleep_start']) ?></td>
                                    <td><?= esc($s['sleep_end']) ?></td>
                                    <td><?= esc($s['duration_hours']) ?> hrs</td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($sleep_anomalies)): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-warning card-outline">
                        <div class="card-header border-0">
                            <h3 class="card-title text-warning font-weight-bold">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Nighttime Sleep Disturbance &amp; Stealth App Activity
                            </h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-striped mb-0">
                                <thead><tr><th>Time</th><th>Threat Finding</th><th>Active Background Apps</th><th>Risk Score</th></tr></thead>
                                <tbody>
                                <?php foreach ($sleep_anomalies as $sa): ?>
                                <?php $details = json_decode($sa['details'], true) ?: []; ?>
                                <tr>
                                    <td><?= esc($sa['event_timestamp']) ?></td>
                                    <td><?= esc($sa['anomaly']) ?></td>
                                    <td><code><?= esc(implode(', ', (array)($details['active_apps'] ?? []))) ?></code></td>
                                    <td><span class="badge badge-danger"><?= esc($sa['score'] * 100) ?>%</span></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($screen_time)): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-desktop mr-1"></i> Daily Screen Time</h3>
                            <span class="badge badge-info ml-2"><?= esc($wellbeing_depth_label) ?></span>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="screenTimeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($addiction)): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-1"></i> App Addiction Report</h3>
                            <span class="badge badge-info ml-2"><?= esc($wellbeing_depth_label) ?></span>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-striped">
                                <thead><tr><th>App</th><th>Category</th><th>Minutes</th><th>Share</th><th>Risk</th></tr></thead>
                                <tbody>
                                <?php foreach ($addiction as $a): ?>
                                <tr>
                                    <td><?= esc($a['name']) ?></td>
                                    <td><?= esc($a['category']) ?></td>
                                    <td><?= esc($a['minutes']) ?></td>
                                    <td><?= esc($a['share_pct']) ?>%</td>
                                    <td><?= $a['addiction_risk'] ? '<span class="badge badge-danger">High</span>' : '<span class="badge badge-success">Low</span>' ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($activity_battery) && $is_platinum): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-walking mr-1"></i> Daily Steps</h3>
                            <span class="badge badge-info ml-2">Platinum</span>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="stepsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-battery-half mr-1"></i> Avg Battery Level</h3>
                            <span class="badge badge-info ml-2">Platinum</span>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="batteryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($battery_anomalies)): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-danger card-outline">
                        <div class="card-header border-0">
                            <h3 class="card-title text-danger font-weight-bold">
                                <i class="fas fa-battery-quarter mr-1"></i> Stealth Battery Drain Alerts (Screen Off Depletion)
                            </h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-striped mb-0">
                                <thead><tr><th>Time Detected</th><th>Anomaly Description</th><th>Depletion Rate</th><th>Risk Score</th></tr></thead>
                                <tbody>
                                <?php foreach ($battery_anomalies as $ba): ?>
                                <?php $details = json_decode($ba['details'], true) ?: []; ?>
                                <tr>
                                    <td><?= esc($ba['event_timestamp']) ?></td>
                                    <td><?= esc($ba['anomaly']) ?></td>
                                    <td><span class="text-danger font-weight-bold"><?= esc($details['drain_rate_percent_per_hour'] ?? '—') ?>% / hr</span></td>
                                    <td><span class="badge badge-danger"><?= esc($ba['score'] * 100) ?>%</span></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php endif; ?>

        </div>
    </section>
</div>

<!-- Chart.js and Custom Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if($has_data): ?>
    
    // 1. Render Heatmap
    const heatmapData = <?= $heatmap_json ?>;
    const grid = document.getElementById('heatmapGrid');
    
    // Generate last 365 days
    const today = new Date();
    const days = [];
    for(let i = 364; i >= 0; i--) {
        const d = new Date();
        d.setDate(today.getDate() - i);
        days.push(d);
    }
    
    // Find max value for scaling
    let maxVal = 0;
    for(let dateStr in heatmapData) {
        if(heatmapData[dateStr] > maxVal) maxVal = heatmapData[dateStr];
    }
    
    days.forEach(dateObj => {
        const dateStr = dateObj.toISOString().split('T')[0];
        const val = heatmapData[dateStr] || 0;
        
        const cell = document.createElement('div');
        cell.className = 'heatmap-cell';
        cell.title = dateStr + ': ' + (val > 0 ? (val/60).toFixed(1) + ' hrs' : 'No data');
        
        if (val > 0) {
            const ratio = val / (maxVal || 1);
            if (ratio > 0.75) cell.classList.add('scale-4');
            else if (ratio > 0.50) cell.classList.add('scale-3');
            else if (ratio > 0.25) cell.classList.add('scale-2');
            else cell.classList.add('scale-1');
        }
        
        grid.appendChild(cell);
    });

    // 2. Habit Split Doughnut Chart
    const habitCtx = document.getElementById('habitChart').getContext('2d');
    new Chart(habitCtx, {
        type: 'doughnut',
        data: {
            labels: ['Social/Gaming', 'Productivity', 'Other'],
            datasets: [{
                data: [<?= $dopamine_pct ?>, <?= $productivity_pct ?>, <?= $other_pct ?>],
                backgroundColor: ['#dc3545', '#28a745', '#17a2b8'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) { return context.label + ': ' + context.raw + '%'; }
                    }
                }
            }
        }
    });

    // 3. Top Apps Bar Chart
    const topAppsCtx = document.getElementById('topAppsChart').getContext('2d');
    new Chart(topAppsCtx, {
        type: 'bar',
        data: {
            labels: <?= $top_apps_labels ?>,
            datasets: [{
                label: 'Hours Spent',
                data: <?= $top_apps_values ?>.map(v => (v/60).toFixed(1)),
                backgroundColor: '#007bff',
                borderRadius: 4,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: { grid: { display: false } },
                y: { grid: { display: false } }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) { return context.raw + ' hrs'; }
                    }
                }
            }
        }
    });

    <?php endif; ?>

    <?php if(!empty($screen_time)): ?>
    // 4. Daily Screen Time Line Chart
    const screenTimeData = <?= json_encode($screen_time) ?>;
    const stCtx = document.getElementById('screenTimeChart');
    if (stCtx) {
        new Chart(stCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: screenTimeData.map(d => d.date),
                datasets: [{
                    label: 'Minutes',
                    data: screenTimeData.map(d => d.minutes),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0,123,255,0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) { return context.raw + ' min'; }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>

    <?php if(!empty($activity_battery) && $is_platinum): ?>
    // 5. Steps Chart
    const stepsData = <?= json_encode($activity_battery) ?>;
    const stepsCtx = document.getElementById('stepsChart');
    if (stepsCtx) {
        new Chart(stepsCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: stepsData.map(d => d.date),
                datasets: [{
                    label: 'Steps',
                    data: stepsData.map(d => d.steps || 0),
                    backgroundColor: '#28a745',
                    borderRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }

    // 6. Battery Chart
    const batteryData = <?= json_encode($activity_battery) ?>;
    const batteryCtx = document.getElementById('batteryChart');
    if (batteryCtx) {
        new Chart(batteryCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: batteryData.map(d => d.date),
                datasets: [{
                    label: 'Avg Battery %',
                    data: batteryData.map(d => d.battery || null),
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255,193,7,0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                    spanGaps: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, max: 100, grid: { display: false } },
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }
    <?php endif; ?>
});
</script>
