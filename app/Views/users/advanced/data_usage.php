<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php helper('coalesce'); $rows = coalesce_snapshots($rows, 'device_id'); ?>

<?php
// 1. Process and aggregate data usage across all snapshots
$appUsage = [];
$totalWifiRx = 0.0;
$totalWifiTx = 0.0;
$totalMobileRx = 0.0;
$totalMobileTx = 0.0;
$exfilCandidates = [];

foreach ($rows as $row) {
    // Decode per-app records from this snapshot
    $records = [];
    if (isset($row['usage_records_json'])) {
        $decoded = is_string($row['usage_records_json']) ? json_decode($row['usage_records_json'], true) : $row['usage_records_json'];
        if (is_array($decoded)) {
            $records = $decoded;
        }
    }

    foreach ($records as $rec) {
        $packageName = $rec['package_name'] ?? 'System/Unknown';
        if (empty($packageName) || $packageName === 'total') {
            continue;
        }

        $rx = (float)($rec['rx_bytes'] ?? 0);
        $tx = (float)($rec['tx_bytes'] ?? 0);
        $isWifi = isset($rec['is_wifi']) ? (bool)$rec['is_wifi'] : false;

        if (!isset($appUsage[$packageName])) {
            $appUsage[$packageName] = [
                'package'   => $packageName,
                'wifi_rx'   => 0.0,
                'wifi_tx'   => 0.0,
                'mobile_rx' => 0.0,
                'mobile_tx' => 0.0,
                'total_tx'  => 0.0,
                'total_rx'  => 0.0
            ];
        }

        if ($isWifi) {
            $appUsage[$packageName]['wifi_rx'] += $rx;
            $appUsage[$packageName]['wifi_tx'] += $tx;
            $totalWifiRx += $rx;
            $totalWifiTx += $tx;
        } else {
            $appUsage[$packageName]['mobile_rx'] += $rx;
            $appUsage[$packageName]['mobile_tx'] += $tx;
            $totalMobileRx += $rx;
            $totalMobileTx += $tx;
        }
        $appUsage[$packageName]['total_tx'] += $tx;
        $appUsage[$packageName]['total_rx'] += $rx;
    }
}

// Format bytes helper
if (!function_exists('format_bytes_adv')) {
    function format_bytes_adv(float $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $u = 0;
        while ($bytes >= 1024 && $u < count($units) - 1) {
            $bytes /= 1024;
            $u++;
        }
        return round($bytes, 2) . ' ' . $units[$u];
    }
}

// 2. Identify potential Data Exfiltration Candidates
// Rule: Outbound (Tx) is greater than 1MB AND Tx is at least double Rx (Tx > Rx * 2)
foreach ($appUsage as $packageName => $usage) {
    if ($usage['total_tx'] > 1024 * 1024 && $usage['total_tx'] > ($usage['total_rx'] * 2)) {
        $exfilCandidates[] = [
            'package' => $packageName,
            'tx' => format_bytes_adv($usage['total_tx']),
            'rx' => format_bytes_adv($usage['total_rx']),
            'ratio' => round($usage['total_tx'] / ($usage['total_rx'] ?: 1), 1)
        ];
    }
}

// Sort apps by total data consumed (Rx + Tx) DESC
uasort($appUsage, function($a, $b) {
    $totalA = $a['wifi_rx'] + $a['wifi_tx'] + $a['mobile_rx'] + $a['mobile_tx'];
    $totalB = $b['wifi_rx'] + $b['wifi_tx'] + $b['mobile_rx'] + $b['mobile_tx'];
    return $totalB <=> $totalA;
});

// Trim to Top 10 apps for the Chart visualization
$topAppsForChart = array_slice($appUsage, 0, 10);

// Prepare JSON arrays for Chart.js
$chartLabels = [];
$chartWifiRx = [];
$chartWifiTx = [];
$chartMobileRx = [];
$chartMobileTx = [];

foreach ($topAppsForChart as $app) {
    $parts = explode('.', $app['package']);
    $shortName = end($parts);
    if (strlen($shortName) < 4 && count($parts) > 1) {
        $shortName = $parts[count($parts)-2] . '.' . $shortName;
    }
    $chartLabels[] = $shortName;
    
    // Convert to Megabytes for clean chart rendering
    $chartWifiRx[] = round($app['wifi_rx'] / (1024 * 1024), 2);
    $chartWifiTx[] = round($app['wifi_tx'] / (1024 * 1024), 2);
    $chartMobileRx[] = round($app['mobile_rx'] / (1024 * 1024), 2);
    $chartMobileTx[] = round($app['mobile_tx'] / (1024 * 1024), 2);
}
?>

<style>
.data-usage-dashboard {
    margin-bottom: 20px;
}
.chart-card {
    border-radius: 8px;
    background: #ffffff;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}
.callout-data {
    border-left: 5px solid #17a2b8;
    background: #fdfdfd;
    border-radius: 4px;
}
.app-detail-row {
    font-size: 13px;
}
.totals-row {
    font-size: 13px;
    background-color: #f8f9fa !important;
    border-top: 2px solid #dee2e6;
}
</style>

<div class="content-wrapper">
    <!-- Keep content-header at the top -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="h3 mb-0 font-weight-bold text-dark">
                        <i class="fas fa-chart-line text-secondary mr-2"></i>Network Data Analysis
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <?= $nav_urls ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Informative Callout box explaining columns & visualization -->
            <div class="callout callout-info callout-data shadow-sm p-3 mb-4">
                <h5 class="font-weight-bold text-info"><i class="fas fa-info-circle mr-2"></i> Data Ingestion & Exfiltration Audit</h5>
                <p class="text-secondary mb-2" style="font-size: 14px;">
                    This module analyzes network socket traffic generated by individual application packages. By auditing outbound upload bytes (Tx) against inbound download bytes (Rx) on both WiFi and Mobile channels, we can identify suspicious background network activity.
                </p>
                <div class="row mt-2" style="font-size: 12px;">
                    <div class="col-md-6 border-right">
                        <span class="font-weight-bold text-dark d-block mb-1">Audit Indicators:</span>
                        <ul class="pl-3 mb-0 text-muted">
                            <li><b class="text-primary">WiFi Rx/Tx:</b> Standard high-volume data transfers.</li>
                            <li><b class="text-success">Mobile Rx/Tx:</b> Paid carrier data consumed by background sync services.</li>
                        </ul>
                    </div>
                    <div class="col-md-6 pl-md-3 mt-2 mt-md-0">
                        <span class="font-weight-bold text-dark d-block mb-1">Exfiltration Threat:</span>
                        <ul class="pl-3 mb-0 text-muted">
                            <li>Apps with a high ratio of **Tx (Sent) to Rx (Received)** that consume continuous background data may be actively exfiltrating device files, screenshots, or logs to remote servers.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Exfiltration Candidates Alert banner -->
            <?php if (!empty($exfilCandidates)): ?>
                <div class="alert alert-warning shadow-sm mb-4">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Suspicious Outbound Outliers Flagged</h5>
                    <div class="table-responsive p-0 mt-2" style="max-height: 150px;">
                        <table class="table table-sm table-borderless text-dark mb-0" style="font-size: 13px;">
                            <thead>
                                <tr class="border-bottom border-warning">
                                    <th>Package Name</th>
                                    <th>Sent (Tx)</th>
                                    <th>Received (Rx)</th>
                                    <th>Sent/Received Ratio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($exfilCandidates as $cand): ?>
                                    <tr>
                                        <td><code class="text-dark"><b><?= esc($cand['package']) ?></b></code></td>
                                        <td><span class="badge badge-danger"><?= esc($cand['tx']) ?></span></td>
                                        <td><span class="badge badge-secondary"><?= esc($cand['rx']) ?></span></td>
                                        <td><span class="badge badge-warning text-dark"><b><?= esc($cand['ratio']) ?>x</b> more sent</span></td>
                                    </tr>
                                	<?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Stacked bar chart Card -->
            <div class="card chart-card card-outline card-primary shadow-sm mb-4">
                <div class="card-header border-bottom-0">
                    <h3 class="card-title font-weight-bold text-secondary">
                        <i class="fas fa-chart-bar mr-2"></i> Top 10 Data Consuming Applications (MB)
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (empty($topAppsForChart)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-chart-area fa-3x text-muted mb-2"></i>
                            <p class="text-muted">No data usage metrics available in current scans.</p>
                        </div>
                    <?php else: ?>
                        <div style="position: relative; height: 320px; width: 100%;">
                            <canvas id="stackedUsageChart"></canvas>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Complete App Consumption List Table -->
            <?php if (!empty($appUsage)): ?>
                <div class="card card-secondary card-outline shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold text-secondary">
                            <i class="fas fa-list mr-2"></i> Application Consumption Directory
                        </h3>
                    </div>
                    <div class="card-body p-0 table-responsive" style="max-height: 450px; overflow-y: auto;">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Application / Package</th>
                                    <th>WiFi Received (Rx)</th>
                                    <th>WiFi Sent (Tx)</th>
                                    <th>Mobile Received (Rx)</th>
                                    <th>Mobile Sent (Tx)</th>
                                    <th class="text-right">Total Consumed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appUsage as $app): 
                                    $totalBytes = $app['wifi_rx'] + $app['wifi_tx'] + $app['mobile_rx'] + $app['mobile_tx'];
                                    ?>
                                    <tr class="app-detail-row">
                                        <td>
                                            <code class="text-dark font-weight-bold"><?= esc($app['package']) ?></code>
                                        </td>
                                        <td><i class="fas fa-download text-muted mr-1" style="font-size: 11px;"></i><?= format_bytes_adv($app['wifi_rx']) ?></td>
                                        <td><i class="fas fa-upload text-muted mr-1" style="font-size: 11px;"></i><?= format_bytes_adv($app['wifi_tx']) ?></td>
                                        <td><i class="fas fa-download text-muted mr-1" style="font-size: 11px;"></i><?= format_bytes_adv($app['mobile_rx']) ?></td>
                                        <td><i class="fas fa-upload text-muted mr-1" style="font-size: 11px;"></i><?= format_bytes_adv($app['mobile_tx']) ?></td>
                                        <td class="text-right font-weight-bold text-primary">
                                            <?= format_bytes_adv($totalBytes) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <!-- Totals Row -->
                                <tr class="totals-row font-weight-bold">
                                    <td>GRAND DEVICE TOTALS</td>
                                    <td><i class="fas fa-download text-info mr-1"></i><?= format_bytes_adv($totalWifiRx) ?></td>
                                    <td><i class="fas fa-upload text-info mr-1"></i><?= format_bytes_adv($totalWifiTx) ?></td>
                                    <td><i class="fas fa-download text-warning mr-1"></i><?= format_bytes_adv($totalMobileRx) ?></td>
                                    <td><i class="fas fa-upload text-warning mr-1"></i><?= format_bytes_adv($totalMobileTx) ?></td>
                                    <td class="text-right text-success font-weight-bold" style="font-size: 14px;">
                                        <?= format_bytes_adv($totalWifiRx + $totalWifiTx + $totalMobileRx + $totalMobileTx) ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </section>
</div>

<!-- Load Chart.js and build Stacked Bar Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('stackedUsageChart');
    if (!ctx) return;

    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [
                {
                    label: 'WiFi Received (Rx)',
                    data: <?= json_encode($chartWifiRx) ?>,
                    backgroundColor: '#17a2b8', // Info Cyan
                    stack: 'Stack 0',
                },
                {
                    label: 'WiFi Sent (Tx)',
                    data: <?= json_encode($chartWifiTx) ?>,
                    backgroundColor: '#20c997', // Teal
                    stack: 'Stack 0',
                },
                {
                    label: 'Mobile Received (Rx)',
                    data: <?= json_encode($chartMobileRx) ?>,
                    backgroundColor: '#ffc107', // Warning Yellow
                    stack: 'Stack 0',
                },
                {
                    label: 'Mobile Sent (Tx)',
                    data: <?= json_encode($chartMobileTx) ?>,
                    backgroundColor: '#dc3545', // Danger Red
                    stack: 'Stack 0',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y', // Horizontal bars
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw} MB`;
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    title: {
                        display: true,
                        text: 'Data Volume (Megabytes)',
                        font: { weight: 'bold' }
                    },
                    grid: { color: '#f4f6f9' }
                },
                y: {
                    stacked: true,
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
