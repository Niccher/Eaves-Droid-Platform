<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'device_id',
    [],
    ['scheduled_jobs_json', 'alarm_clocks_json']
);

$latest = !empty($rows) ? $rows[0] : null;
$latestJobs = [];
$latestAlarms = [];
if ($latest) {
    $latestJobs = is_string($latest['scheduled_jobs_json'] ?? null)
        ? (json_decode($latest['scheduled_jobs_json'], true) ?: [])
        : ($latest['scheduled_jobs_json'] ?? []);
    $latestAlarms = is_string($latest['alarm_clocks_json'] ?? null)
        ? (json_decode($latest['alarm_clocks_json'], true) ?: [])
        : ($latest['alarm_clocks_json'] ?? []);
}
?>

<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3">
                            <i class="fas fa-history text-primary mr-2"></i>Alarms &amp; Scheduled Jobs
                        </h1>
                        <span class="badge badge-primary border p-2">
                            <i class="fas fa-database mr-1"></i>Total Records: <b><?= (int)$total ?></b>
                        </span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Scheduled JobScheduler background jobs and AlarmManager RTC/ELAPSED alarms on the device.</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <?php if ($latest): ?>
                <div class="row">
                    <!-- Column 1: Scheduled Jobs -->
                    <div class="col-md-7">
                        <div class="card card-primary card-outline shadow-sm mb-4">
                            <div class="card-header d-flex align-items-center">
                                <h3 class="card-title text-primary font-weight-bold">
                                    <i class="fas fa-tasks mr-2"></i>Scheduled Jobs
                                </h3>
                                <div class="card-tools ml-auto">
                                    <span class="badge badge-primary"><?= count($latestJobs) ?> active</span>
                                </div>
                            </div>
                            <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                                <?php if (empty($latestJobs)): ?>
                                    <p class="text-muted text-center py-4 mb-0">No active scheduled jobs found.</p>
                                <?php else: ?>
                                    <table class="table table-sm table-striped table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Job ID</th>
                                                <th>Target Service</th>
                                                <th>Trigger Details</th>
                                                <th>Constraints</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($latestJobs as $job): ?>
                                                <tr>
                                                    <td><code><?= htmlspecialchars($job['job_id']) ?></code></td>
                                                    <td>
                                                        <span class="font-weight-bold d-block text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($job['service']) ?>">
                                                            <?= esc(substr($job['service'], strrpos($job['service'], '.') + 1)) ?>
                                                        </span>
                                                        <small class="text-muted text-truncate d-block" style="max-width: 250px;" title="<?= htmlspecialchars($job['package']) ?>">
                                                            <?= htmlspecialchars($job['package']) ?>
                                                        </small>
                                                    </td>
                                                    <td class="small">
                                                        <?php if (!empty($job['is_periodic'])): ?>
                                                            <span class="badge badge-info mb-1"><i class="fas fa-sync-alt mr-1"></i>Periodic</span><br>
                                                            Interval: <?= round(($job['interval_millis'] ?? 0) / 60000, 1) ?> mins
                                                        <?php else: ?>
                                                            <span class="badge badge-light border mb-1">One-shot</span>
                                                            <?= !empty($job['minimum_latency_millis']) ? '<br>Delay: ' . round($job['minimum_latency_millis'] / 1000) . 's' : '' ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="small">
                                                        <?php
                                                        $constraints = [];
                                                        if (!empty($job['requires_charging'])) $constraints[] = 'Charging';
                                                        if (!empty($job['requires_idle'])) $constraints[] = 'Idle';
                                                        if (!empty($job['network_type']) && $job['network_type'] !== 'NONE') $constraints[] = 'Net: ' . $job['network_type'];
                                                        if (!empty($job['persisted'])) $constraints[] = 'Persisted';
                                                        
                                                        if (empty($constraints)): ?>
                                                            <span class="text-muted">—</span>
                                                        <?php else: foreach ($constraints as $c): ?>
                                                            <span class="badge badge-light border mr-1 mb-1"><?= $c ?></span>
                                                        <?php endforeach; endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Alarm Clocks -->
                    <div class="col-md-5">
                        <div class="card card-success card-outline shadow-sm mb-4">
                            <div class="card-header d-flex align-items-center">
                                <h3 class="card-title text-success font-weight-bold">
                                    <i class="fas fa-clock mr-2"></i>Alarm Clocks
                                </h3>
                                <div class="card-tools ml-auto">
                                    <span class="badge badge-success"><?= count($latestAlarms) ?> set</span>
                                </div>
                            </div>
                            <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                                <?php if (empty($latestAlarms)): ?>
                                    <p class="text-muted text-center py-4 mb-0">No active alarm clocks found.</p>
                                <?php else: ?>
                                    <table class="table table-sm table-striped table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Trigger Time</th>
                                                <th>Target Package</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($latestAlarms as $alarm): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-light border p-2 font-weight-bold">
                                                            <i class="fas fa-bell mr-1 text-warning"></i>
                                                            <?= htmlspecialchars($alarm['trigger_time_formatted'] ?: date('Y-m-d H:i:s', $alarm['trigger_time_millis'] / 1000)) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="text-truncate d-block small" style="max-width: 200px;" title="<?= htmlspecialchars($alarm['package']) ?>">
                                                            <?= htmlspecialchars($alarm['package']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Extraction History Table -->
            <div class="card card-secondary card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-history mr-2"></i>Extraction History
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Extracted</th>
                                    <th>Device ID</th>
                                    <th>Jobs Found</th>
                                    <th>Alarms Found</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rows)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <h4>No Alarm/Job data</h4>
                                                <p class="text-muted">Data will appear here once extracted from the Android app.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: foreach ($rows as $r): ?>
                                    <?php 
                                        $rawJobs = $r['scheduled_jobs_json'] ?? '[]';
                                        $rawAlarms = $r['alarm_clocks_json'] ?? '[]';
                                        $jobArr = is_string($rawJobs) ? (json_decode($rawJobs, true) ?: []) : (is_array($rawJobs) ? $rawJobs : []);
                                        $alarmArr = is_string($rawAlarms) ? (json_decode($rawAlarms, true) ?: []) : (is_array($rawAlarms) ? $rawAlarms : []);
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-light border p-2">
                                                <i class="fas fa-clock mr-1 text-muted"></i>
                                                <?= !empty($r['extracted_at']) ? date('M d, Y H:i', $r['extracted_at'] / 1000) : 'N/A' ?>
                                            </span>
                                        </td>
                                        <td><code><?= htmlspecialchars($r['device_id'] ?? 'N/A') ?></code></td>
                                        <td><span class="badge badge-primary"><?= count($jobArr) ?> Jobs</span></td>
                                        <td><span class="badge badge-success"><?= count($alarmArr) ?> Alarms</span></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if (isset($pager) && $total > 25): ?>
                    <div class="card-footer clearfix">
                        <div class="float-right">
                            <?= $pager->links('default', 'bootstrap5_full') ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>
</div>

<?php include __DIR__ . '/_adv_delete_script.php'; ?>
