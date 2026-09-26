<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-chart-line text-info mr-2"></i>Sync Timeline</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/fleet') ?>">Fleet</a></li>
                        <li class="breadcrumb-item active">Timeline</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content pt-1">
        <div class="container-fluid">
            <!-- Timeline Callout -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="callout callout-danger">
                        <h5><i class="fas fa-chart-line mr-2"></i>Sync Timeline Analysis</h5>
                        <p class="mb-1">This view provides a comprehensive view of device synchronization patterns across your fleet. Use it to identify sync anomalies, peak activity periods, and devices that may be experiencing connectivity issues.</p>
                        <ul class="mb-0 small">
                            <li><strong>Hourly Heatmap:</strong> Visualizes sync frequency by hour and day. Darker cells indicate higher sync activity. Look for gaps (white cells) during expected active hours.</li>
                            <li><strong>Daily Totals:</strong> Bar chart showing total syncs per day. Sudden drops may indicate fleet-wide issues; spikes may indicate batch uploads or mass device activity.</li>
                            <li><strong>Daily Breakdown Table:</strong> Day-by-day sync counts with trend indicators (up/down arrows). Compare consecutive days to spot anomalies.</li>
                        </ul>
                        <p class="mb-0 small mt-1"><strong>Tip:</strong> Devices with no syncs for 24+ hours appear as stale in the Alert Center. Investigate devices with consistent zero-activity rows.</p>
                    </div>
                </div>
            </div>

            <!-- Hourly Heatmap -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Hourly Sync Heatmap (Last 7 Days)</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Hour</th>
                                            <?php for ($d = 6; $d >= 0; $d--): ?>
                                                <th><?= date('M d', strtotime("-$d days")) ?></th>
                                            <?php endfor; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for ($h = 0; $h < 24; $h++): ?>
                                            <tr>
                                                <td class="font-weight-bold"><?= sprintf('%02d:00', $h) ?></td>
                                                <?php for ($d = 6; $d >= 0; $d--): ?>
                                                    <?php
                                                    $date = date('Y-m-d', strtotime("-$d days"));
                                                    $count = 0;
                                                    foreach ($hourly_heatmap as $row) {
                                                        if ($row['hour'] == $h && $row['date'] == $date) {
                                                            $count = (int)$row['syncs'];
                                                            break;
                                                        }
                                                    }
                                                    ?>
                                                    <td class="<?= $count > 0 ? 'bg-primary text-white' : '' ?>" style="min-width: 50px;">
                                                        <?= $count > 0 ? $count : '-' ?>
                                                    </td>
                                                <?php endfor; ?>
                                            </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Totals Chart -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Daily Sync Totals</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 300px; position: relative;">
                                <canvas id="dailyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Totals Table -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-table mr-2"></i>Daily Breakdown</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Syncs</th>
                                            <th>Trend</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $prev = 0;
                                        foreach ($daily_totals as $date => $count):
                                            $trend = $count > $prev ? '<i class="fas fa-arrow-up text-success"></i>' :
                                                ($count < $prev ? '<i class="fas fa-arrow-down text-danger"></i>' : '<i class="fas fa-minus text-muted"></i>');
                                            $prev = $count;
                                        ?>
                                            <tr>
                                                <td><?= date('D, M d, Y', strtotime($date)) ?></td>
                                                <td><?= number_format($count) ?></td>
                                                <td class="text-center"><?= $trend ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
    const ctx = document.getElementById('dailyChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($daily_totals)) ?>,
                datasets: [{
                    label: 'Daily Syncs',
                    data: <?= json_encode(array_values($daily_totals)) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
});
</script>