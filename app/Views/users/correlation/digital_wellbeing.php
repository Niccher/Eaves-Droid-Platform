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
                <div class="col-12 col-sm-4">
                    <div class="info-box bg-danger">
                        <span class="info-box-icon"><i class="fas fa-gamepad"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Social & Gaming</span>
                            <span class="info-box-number"><?= $dopamine_hrs ?> Hours</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $dopamine_pct ?>%"></div>
                            </div>
                            <span class="progress-description"><?= $dopamine_pct ?>% of total tracked time</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-4">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-briefcase"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Productivity & Work</span>
                            <span class="info-box-number"><?= $productivity_hrs ?> Hours</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $productivity_pct ?>%"></div>
                            </div>
                            <span class="progress-description"><?= $productivity_pct ?>% of total tracked time</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-4">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-mobile-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Other Apps</span>
                            <span class="info-box-number"><?= $other_hrs ?> Hours</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $other_pct ?>%"></div>
                            </div>
                            <span class="progress-description"><?= $other_pct ?>% of total tracked time</span>
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
                backgroundColor: ['#dc3545', '#28a745', '#17a2b8'], // AdminLTE Danger, Success, Info colors
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
                backgroundColor: '#007bff', // AdminLTE Primary color
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
});
</script>
