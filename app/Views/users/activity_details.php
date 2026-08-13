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

      <!-- AdminLTE Small Boxes -->
      <div class="row">
        <div class="col-lg-4 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= number_format($totalActivities) ?></h3>
              <p>Total Recorded Events</p>
            </div>
            <div class="icon"><i class="fas fa-walking"></i></div>
          </div>
        </div>

        <div class="col-lg-4 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3>
                <?php
                $topType = 'STILL'; $max = 0;
                foreach (($activity_stats['types'] ?? []) as $t) {
                    if ($t['count'] > $max) {
                        $max = $t['count'];
                        $topType = $t['activity_type'];
                    }
                }
                echo htmlspecialchars(strtoupper($topType));
                ?>
              </h3>
              <p>Dominant State (<?= $max ?> hits)</p>
            </div>
            <div class="icon"><i class="fas fa-heartbeat"></i></div>
          </div>
        </div>

        <div class="col-lg-4 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= $activity_stats['avg_battery'] ?? 0 ?>%</h3>
              <p>Average Battery Level</p>
            </div>
            <div class="icon"><i class="fas fa-battery-half"></i></div>
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
        <!-- Chart: Activity Time Distribution -->
        <div class="col-md-6">
          <div class="card card-info card-outline shadow-sm">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Activity Distribution</h3>
            </div>
            <div class="card-body">
              <canvas id="activityPieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
          </div>
        </div>

        <!-- Chart: Battery Drain Rate -->
        <div class="col-md-6">
          <div class="card card-warning card-outline shadow-sm">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-bolt mr-2"></i>Battery Level Telemetry</h3>
            </div>
            <div class="card-body">
              <canvas id="batteryLineChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
          </div>
        </div>

        <!-- Native AdminLTE Timeline -->
        <div class="col-12 mt-4">
          <div class="card card-secondary shadow-sm">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Chronological Activity Timeline</h3>
            </div>
            <div class="card-body">
              <div class="timeline timeline-inverse px-3 pt-3">
                <?php if (empty($activities)): ?>
                  <div class="text-center py-5">
                    <i class="fas fa-running fa-3x text-muted mb-3 d-block"></i>
                    <h5 class="text-muted">No activity logs recorded</h5>
                  </div>
                <?php else: ?>
                  <?php
                  $lastDayLabel = '';
                  foreach ($activities as $act):
                      $ts = (int)($act['activity_time'] ?? $act['extracted_at'] ?? 0);
                      $tsSec = $ts > 1_000_000_000_000 ? (int)($ts / 1000) : $ts;
                      $dayLabel = $tsSec > 0 ? date('M d, Y', $tsSec) : 'Unknown date';
                      $timeLabel = $tsSec > 0 ? date('H:i:s', $tsSec) : '—';
                      
                      $activityType = strtoupper($act['activity_type'] ?? $act['status'] ?? 'UNKNOWN');
                      $confidence = $act['confidence'] ?? 0;
                      $battery = $act['battery_level'] ?? null;
                      $screenOn = ($act['screen_on'] ?? 0) == 1;

                      // Styles
                      $icon = 'fa-question'; $color = 'bg-secondary';
                      if (str_contains($activityType, 'WALK') || str_contains($activityType, 'RUN')) {
                          $icon = 'fa-running'; $color = 'bg-success';
                      } elseif (str_contains($activityType, 'STILL') || str_contains($activityType, 'IDLE')) {
                          $icon = 'fa-stop'; $color = 'bg-gray';
                      } elseif (str_contains($activityType, 'VEHICLE') || str_contains($activityType, 'DRIV')) {
                          $icon = 'fa-car'; $color = 'bg-info';
                      } elseif (str_contains($activityType, 'BICYCLE')) {
                          $icon = 'fa-bicycle'; $color = 'bg-warning';
                      }
                      
                      if ($dayLabel !== $lastDayLabel):
                          $lastDayLabel = $dayLabel;
                  ?>
                    <div class="time-label">
                      <span class="bg-primary text-white"><?= esc($dayLabel) ?></span>
                    </div>
                  <?php endif; ?>

                  <div>
                    <i class="fas <?= $icon ?> <?= $color ?> text-white"></i>
                    <div class="timeline-item shadow-sm border mb-3">
                      <span class="time"><i class="fas fa-clock mr-1"></i><?= $timeLabel ?></span>
                      <h3 class="timeline-header font-weight-bold">
                        <?= esc($activityType) ?> 
                        <?php if ($confidence > 0): ?>
                          <span class="badge badge-light border ml-2"><?= esc($confidence) ?>% Confidence</span>
                        <?php endif; ?>
                      </h3>
                      <div class="timeline-body py-2">
                        <div class="row">
                          <div class="col-sm-4">
                            <i class="fas fa-battery-half text-muted mr-1"></i> 
                            <b>Battery:</b> <?= $battery !== null ? esc($battery) . '%' : '—' ?>
                          </div>
                          <div class="col-sm-4">
                            <i class="fas fa-desktop text-muted mr-1"></i> 
                            <b>Screen State:</b> <span class="badge badge-<?= $screenOn ? 'success' : 'secondary' ?>"><?= $screenOn ? 'ON' : 'OFF' ?></span>
                          </div>
                          <div class="col-sm-4">
                            <i class="fas fa-signal text-muted mr-1"></i> 
                            <b>Network:</b> <?= strtoupper(esc($act['network_type'] ?? '—')) ?>
                          </div>
                        </div>
                      </div>
                      <div class="timeline-footer py-1 px-3 d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="fas fa-mobile-alt mr-1"></i><?= esc($act['device_model'] ?? 'N/A') ?></small>
                        <button type="button" class="btn btn-xs btn-outline-danger delete-row"
                                data-id="<?= $act['counter'] ?? $act['id'] ?? '' ?>"
                                data-url="<?= base_url('activities/delete') ?>"
                                title="Delete event">
                          <i class="fas fa-trash"></i> Delete
                        </button>
                      </div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
            <div class="card-footer">
              <div class="float-right my-2">
                <?php if (isset($pager)): ?>
                  <?= $pager->links('default', 'bootstrap5_full') ?>
                <?php endif; ?>
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
