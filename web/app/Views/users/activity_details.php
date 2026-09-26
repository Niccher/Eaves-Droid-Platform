<div class="content-wrapper">
  <!-- Content Header -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-3 align-items-center">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-running text-primary mr-2"></i>Detailed Activities</h1>
          <p class="text-muted mb-0">Hardware-level user activity and power telemetry analysis</p>
        </div>
        <div class="col-sm-6 text-right">
          <div class="btn-group btn-group-sm mb-2 mb-sm-0" role="group">
            <a href="<?= base_url('location') ?>" class="btn btn-outline-primary"><i class="fas fa-route mr-1"></i> Timeline</a>
            <a href="<?= base_url('location/map') ?>" class="btn btn-outline-primary"><i class="fas fa-map-marked-alt mr-1"></i> Map View</a>
            <a href="<?= base_url('activities') ?>" class="btn btn-primary"><i class="fas fa-running mr-1"></i> Activities</a>
          </div>
        </div>
      </div>

      <!-- Premium Redesigned Telemetry Cards with AdminLTE Properties -->
      <style>
        .sync-pulse {
          width: 6px;
          height: 6px;
          background-color: #17a2b8;
          border-radius: 50%;
          display: inline-block;
          box-shadow: 0 0 0 0 rgba(23, 162, 184, 0.7);
          animation: pulse 1.6s infinite;
        }
        @keyframes pulse {
          0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(23, 162, 184, 0.7);
          }
          70% {
            transform: scale(1);
            box-shadow: 0 0 0 5px rgba(23, 162, 184, 0);
          }
          100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(23, 162, 184, 0);
          }
        }
        .telemetry-visual-box {
          width: 46px;
          height: 46px;
          border-radius: 8px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 18px;
          flex-shrink: 0;
        }
        .bg-paired-light { background-color: rgba(23, 162, 184, 0.08); color: #17a2b8; }
        .bg-state-light { background-color: rgba(40, 167, 69, 0.08); color: #28a745; }
        .bg-battery-light { background-color: rgba(255, 193, 7, 0.08); color: #ffc107; }

        .battery-track {
          width: 100px;
          height: 5px;
          background-color: #e7eaf3;
          border-radius: 3px;
          margin-top: 6px;
          overflow: hidden;
        }
        .battery-fill {
          height: 100%;
          border-radius: 3px;
        }
      </style>

      <div class="row mt-2">
        <!-- Card: Paired Events -->
        <div class="col-lg-4 col-sm-6 mb-3">
          <div class="card card-outline card-info shadow-sm h-100 mb-0">
            <div class="card-body d-flex align-items-center justify-content-between p-3">
              <div>
                <span class="text-uppercase text-muted" style="font-size: 11px; font-weight: 700; letter-spacing: 0.8px;">Telemetry Feed</span>
                <h3 class="mb-0 mt-1" style="font-size: 26px; font-weight: 800; color: #1e2022;"><?= number_format($totalActivities) ?></h3>
                <small class="text-muted d-block mt-2">
                  <span class="sync-pulse mr-1"></span> Paired Sensor Events
                </small>
              </div>
              <div class="telemetry-visual-box bg-paired-light">
                <i class="fas fa-link"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Card: Dominant State -->
        <?php
        $topType = '—'; $maxHits = 0;
        foreach (($activity_stats['types'] ?? []) as $t) {
            if ($t['count'] > $maxHits) { $maxHits = $t['count']; $topType = $t['activity_type']; }
        }
        $stateLabel = strtoupper($topType);
        $stateDesc = 'Active baseline';
        $stateIcon = 'fa-chart-line';
        if (str_contains($stateLabel, 'STILL') || str_contains($stateLabel, 'IDLE')) {
            $stateDesc = 'Stationary / Idle';
            $stateIcon = 'fa-chair';
        } elseif (str_contains($stateLabel, 'WALK')) {
            $stateDesc = 'Walking pace';
            $stateIcon = 'fa-walking';
        } elseif (str_contains($stateLabel, 'VEHICLE')) {
            $stateDesc = 'In moving vehicle';
            $stateIcon = 'fa-car';
        }
        ?>
        <div class="col-lg-4 col-sm-6 mb-3">
          <div class="card card-outline card-success shadow-sm h-100 mb-0">
            <div class="card-body d-flex align-items-center justify-content-between p-3">
              <div>
                <span class="text-uppercase text-muted" style="font-size: 11px; font-weight: 700; letter-spacing: 0.8px;">Dominant State</span>
                <h3 class="mb-0 mt-1" style="font-size: 22px; font-weight: 800; color: #1e2022;"><?= htmlspecialchars($stateLabel) ?></h3>
                <small class="text-muted d-block mt-2">
                  <i class="fas fa-fire mr-1 text-success"></i> <?= $maxHits ?> hits &bull; <?= $stateDesc ?>
                </small>
              </div>
              <div class="telemetry-visual-box bg-state-light">
                <i class="fas <?= $stateIcon ?>"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Card: Avg Battery -->
        <?php
        $battVal = (int)($activity_stats['avg_battery'] ?? 0);
        $battColor = '#28a745'; // green
        if ($battVal < 20) {
            $battColor = '#dc3545'; // red
        } elseif ($battVal < 60) {
            $battColor = '#ffc107'; // amber
        }
        ?>
        <div class="col-lg-4 col-sm-6 mb-3">
          <div class="card card-outline card-warning shadow-sm h-100 mb-0">
            <div class="card-body d-flex align-items-center justify-content-between p-3">
              <div>
                <span class="text-uppercase text-muted" style="font-size: 11px; font-weight: 700; letter-spacing: 0.8px;">Average Battery</span>
                <h3 class="mb-0 mt-1" style="font-size: 26px; font-weight: 800; color: #1e2022;"><?= $battVal ?>%</h3>
                <div class="battery-track">
                  <div class="battery-fill" style="width: <?= $battVal ?>%; background-color: <?= $battColor ?>;"></div>
                </div>
              </div>
              <div class="telemetry-visual-box bg-battery-light">
                <i class="fas fa-battery-three-quarters" style="color: <?= $battColor ?>;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Chart.js Dependencies (bundled with AdminLTE or loaded from CDN) -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Main Content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">

        <!-- ── User Behavioral & Power Diagnostics Dashboard ──────────────── -->
        <?php
        $screenOn = $screenOff = 0;
        $charging = $discharging = 0;
        $netCounts = [];
        $confSum = $confCount = 0;

        foreach ($activities as $a) {
            // Screen state
            if (($a['screen_on'] ?? 0) == 1) $screenOn++; else $screenOff++;

            // Charging state
            $cs = strtolower($a['charging_status'] ?? '');
            if ($cs === 'charging' || $cs === '1' || $cs === 'true') $charging++;
            else $discharging++;

            // Network
            $nt = strtoupper($a['network_type'] ?? 'UNKNOWN');
            if ($nt === '' || $nt === 'UNKNOWN' || $nt === 'NONE') $nt = 'OFFLINE';
            $netCounts[$nt] = ($netCounts[$nt] ?? 0) + 1;

            // Confidence
            if (isset($a['confidence']) && (int)$a['confidence'] > 0) {
                $confSum += (int)$a['confidence'];
                $confCount++;
            }
        }
        arsort($netCounts);
        $total = count($activities);
        $avgConf = $confCount > 0 ? round($confSum / $confCount) : 0;
        $screenPct  = $total > 0 ? round($screenOn  / $total * 100) : 0;
        $chargingPct = $total > 0 ? round($charging / $total * 100) : 0;
        ?>

        <!-- Row 1: Screen + Charging + Confidence + Network overview -->
        <div class="col-12 mt-4">
          <div class="row">
            <!-- Screen State -->
            <div class="col-md-3 col-sm-6">
              <div class="info-box shadow-sm" style="border-left:4px solid #28a745!important;">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-sun"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Screen Active</span>
                  <span class="info-box-number"><?= $screenOn ?></span>
                  <div class="progress mt-1" style="height:4px;">
                    <div class="progress-bar bg-success" style="width:<?= $screenPct ?>%"></div>
                  </div>
                  <span class="progress-description"><?= $screenPct ?>% of sessions &bull; <?= $screenOff ?> background</span>
                </div>
              </div>
            </div>
            <!-- Charging State -->
            <div class="col-md-3 col-sm-6">
              <div class="info-box shadow-sm" style="border-left:4px solid #ffc107!important;">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-bolt"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Charging</span>
                  <span class="info-box-number"><?= $charging ?></span>
                  <div class="progress mt-1" style="height:4px;">
                    <div class="progress-bar bg-warning" style="width:<?= $chargingPct ?>%"></div>
                  </div>
                  <span class="progress-description"><?= $chargingPct ?>% charging &bull; <?= $discharging ?> discharging</span>
                </div>
              </div>
            </div>
            <!-- Avg Confidence -->
            <div class="col-md-3 col-sm-6">
              <div class="info-box shadow-sm" style="border-left:4px solid #17a2b8!important;">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-percentage"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Avg Confidence</span>
                  <span class="info-box-number"><?= $avgConf ?>%</span>
                  <div class="progress mt-1" style="height:4px;">
                    <div class="progress-bar bg-info" style="width:<?= $avgConf ?>%"></div>
                  </div>
                  <span class="progress-description">Sensor engine reliability score</span>
                </div>
              </div>
            </div>
            <!-- Total Records -->
            <div class="col-md-3 col-sm-6">
              <div class="info-box shadow-sm" style="border-left:4px solid #6c757d!important;">
                <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-database"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Paired Records</span>
                  <span class="info-box-number"><?= number_format($total) ?></span>
                  <div class="progress mt-1" style="height:4px;">
                    <div class="progress-bar bg-secondary" style="width:100%"></div>
                  </div>
                  <span class="progress-description">Matched w/ GPS fix</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Row 2: Network Connectivity Profile + Charts -->
        <div class="col-12 mt-3">
          <div class="row">
            <!-- Network Connectivity Profile -->
            <div class="col-md-4">
              <div class="card card-outline card-primary shadow-sm h-100">
                <div class="card-header">
                  <h3 class="card-title"><i class="fas fa-signal mr-2"></i>Network Profile</h3>
                </div>
                <div class="card-body p-0">
                  <?php if (empty($netCounts)): ?>
                    <div class="text-center py-4 text-muted"><i class="fas fa-wifi fa-2x mb-2 d-block"></i>No data</div>
                  <?php else: ?>
                    <ul class="list-group list-group-flush">
                      <?php foreach ($netCounts as $net => $cnt): ?>
                        <?php
                          $pct = $total > 0 ? round($cnt / $total * 100) : 0;
                          if (str_contains($net, 'WIFI') || str_contains($net, 'WI-FI')) {
                              $ic = 'fa-wifi'; $bc = 'bg-primary'; $tc = 'text-primary';
                          } elseif (str_contains($net, 'LTE') || str_contains($net, '4G') || str_contains($net, '5G')) {
                              $ic = 'fa-signal'; $bc = 'bg-success'; $tc = 'text-success';
                          } elseif (str_contains($net, '3G')) {
                              $ic = 'fa-signal'; $bc = 'bg-info'; $tc = 'text-info';
                          } elseif (str_contains($net, '2G') || str_contains($net, 'EDGE') || str_contains($net, 'GPRS')) {
                              $ic = 'fa-signal'; $bc = 'bg-warning'; $tc = 'text-warning';
                          } else {
                              $ic = 'fa-times-circle'; $bc = 'bg-secondary'; $tc = 'text-secondary';
                          }
                        ?>
                        <li class="list-group-item px-3 py-2">
                          <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="<?= $tc ?>"><i class="fas <?= $ic ?> mr-2"></i><strong><?= esc($net) ?></strong></span>
                            <span class="badge badge-light border"><?= $cnt ?> <small class="text-muted">(<?= $pct ?>%)</small></span>
                          </div>
                          <div class="progress" style="height:4px;">
                            <div class="progress-bar <?= $bc ?>" style="width:<?= $pct ?>%"></div>
                          </div>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Activity Distribution Chart -->
            <div class="col-md-4">
              <div class="card card-outline card-info shadow-sm h-100">
                <div class="card-header">
                  <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Activity Distribution</h3>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                  <canvas id="activityPieChart" style="min-height:220px;height:220px;max-height:220px;max-width:100%;"></canvas>
                </div>
              </div>
            </div>

            <!-- Battery Telemetry Chart -->
            <div class="col-md-4">
              <div class="card card-outline card-warning shadow-sm h-100">
                <div class="card-header">
                  <h3 class="card-title"><i class="fas fa-battery-half mr-2"></i>Battery Telemetry</h3>
                </div>
                <div class="card-body d-flex align-items-center">
                  <canvas id="batteryLineChart" style="min-height:220px;height:220px;max-height:220px;max-width:100%;"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // ── 1. Activity Pie Chart ──
    var activityTypes = <?= json_encode($activity_stats['types'] ?? []) ?>;
    var pieLabels = [];
    var pieData = [];
    
    activityTypes.forEach(function(item) {
        pieLabels.push(item.activity_type.toUpperCase());
        pieData.push(parseInt(item.count));
    });

    var ctxPie = document.getElementById('activityPieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: pieLabels,
            datasets: [{
                data: pieData,
                backgroundColor: ['#6c757d', '#28a745', '#17a2b8', '#ffc107', '#343a40', '#e83e8c'],
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });

    // ── 2. Battery Line Chart ──
    var activities = <?= json_encode($activities) ?>;
    // Reverse elements for chronological timeline plotting (left to right)
    var reversedActivities = activities.slice().reverse();

    var lineLabels = [];
    var lineData = [];

    reversedActivities.forEach(function(item) {
        if (item.battery_level !== null) {
            var timeStr = item.activity_time ? new Date(parseInt(item.activity_time)).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '';
            lineLabels.push(timeStr);
            lineData.push(parseInt(item.battery_level));
        }
    });

    var ctxLine = document.getElementById('batteryLineChart').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: lineLabels,
            datasets: [{
                label: 'Battery Level (%)',
                data: lineData,
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.3,
                fill: true,
                borderWidth: 2,
                pointRadius: 3
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                y: { min: 0, max: 100 }
            }
        }
    });
});
</script>
<?php include __DIR__ . '/partials/_delete_confirm.php'; ?>
