<?php /** @var int|null $latest_ts @var array $latest_jobs @var array $latest_alarms @var array $deleted_jobs @var array $deleted_alarms @var int $total @var string $nav_urls */ ?>
<?php
$latest = $latest_ts ? ['extracted_at' => $latest_ts] : null;
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

            <?php if (!empty($deleted_jobs) || !empty($deleted_alarms)): ?>
                <!-- Cancelled Alarms Warning Card -->
                <div class="card card-warning card-outline shadow-sm mb-4">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title text-warning font-weight-bold">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Recently Cancelled Alarms / Jobs Detected
                        </h3>
                    </div>
                    <div class="card-body py-2 px-3">
                        <p class="text-muted small mb-2">The following background services or scheduled alarms were present in the previous scan but are missing in the latest snapshot. This could indicate process killing, task manipulation, or application tampering:</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" style="font-size: .82rem;">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Identifier / Service</th>
                                        <th>Package Name</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($deleted_jobs as $job): ?>
                                        <tr>
                                            <td><span class="badge badge-primary">Job</span></td>
                                            <td><code>Job ID: <?= esc($job['job_id']) ?></code> (<?= esc(substr($job['service'], strrpos($job['service'], '.') + 1)) ?>)</td>
                                            <td><?= esc($job['package']) ?></td>
                                            <td><span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Missing</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php foreach ($deleted_alarms as $alarm): ?>
                                        <tr>
                                            <td><span class="badge badge-success">Alarm</span></td>
                                            <td><?= esc($alarm['trigger_time_formatted'] ?: date('Y-m-d H:i:s', $alarm['trigger_time_millis'] / 1000)) ?></td>
                                            <td><?= esc($alarm['package']) ?></td>
                                            <td><span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Deleted</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

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
                                    <span class="badge badge-primary"><?= count($latest_jobs) ?> active</span>
                                </div>
                            </div>
                            <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                                <?php if (empty($latest_jobs)): ?>
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
                                            <?php foreach ($latest_jobs as $job): ?>
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
                                    <span class="badge badge-success"><?= count($latest_alarms) ?> set</span>
                                </div>
                            </div>
                            <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                                <?php if (empty($latest_alarms)): ?>
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
                                            <?php foreach ($latest_alarms as $alarm): ?>
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

            <!-- Telemetry Context Card -->
            <div class="card card-secondary card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-info-circle mr-2"></i>Telemetry Context
                    </h3>
                </div>
                <div class="card-body py-3">
                    <p class="text-secondary mb-0" style="font-size: 14px;">
                        This dashboard monitors active jobs and alarms scheduled by applications on the device. Background jobs represent services managed by the OS Scheduler (e.g. syncs, telemetry uploads, backup tasks), while alarm clocks represent absolute trigger triggers set by the user or apps.
                    </p>
                </div>
            </div>

        </div>
    </section>
</div>

<?php include __DIR__ . '/_adv_delete_script.php'; ?>
