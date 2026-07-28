<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-clock text-warning mr-2"></i>Alarms & Jobs</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Scheduled JobScheduler jobs and AlarmManager alarms</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Alarm/Job Snapshots <small class="text-muted ml-2"><?= count($rows) ?></small></h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Type</th>
                                <th>Job ID / Alarm</th>
                                <th>Package</th>
                                <th>Service / Package</th>
                                <th>Periodic</th>
                                <th>Network</th>
                                <th>Charging</th>
                                <th>Extracted</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($rows)): foreach ($rows as $r):
                            $jobs = $r['scheduled_jobs_json'] ?? '[]';
                            $jobsArr = json_decode($jobs, true) ?? [];
                            $alarms = $r['alarm_clocks_json'] ?? '[]';
                            $alarmsArr = json_decode($alarms, true) ?? [];
                            ?>
                            <?php if (!empty($jobsArr)): foreach ($jobsArr as $job): ?>
                                <tr>
                                    <td><span class="badge badge-primary">Job</span></td>
                                    <td><code class="small"><?= esc($job['job_id'] ?? 'N/A') ?></code></td>
                                    <td><?= esc($job['package_name'] ?? 'N/A') ?></td>
                                    <td><?= esc($job['service_class'] ?? 'N/A') ?></td>
                                    <td class="text-center">
                                        <?= isset($job['is_periodic']) && $job['is_periodic'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?>
                                        <?php if (isset($job['interval_millis'])): ?>
                                            <br><small class="text-muted"><?= number_format($job['interval_millis']/1000/60, 1) ?> min</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info"><?= esc($job['network_type'] ?? 'N/A') ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?= isset($job['requires_charging']) && $job['requires_charging'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?>
                                    </td>
                                    <td><?= format_timestamp_display((int)$r['extracted_at']) ?></td>
                                    <td class="text-center">
                                            <button class="btn btn-sm btn-outline-danger delete-row"
                                                data-id="<?= $r['id'] ?? '' ?>"
                                                data-url="<?= base_url('advanced/alarms/delete') ?>"
                                                title="Delete this row">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                            <?php if (!empty($alarmsArr)): foreach ($alarmsArr as $alarm): ?>
                                <tr>
                                    <td><span class="badge badge-warning">Alarm</span></td>
                                    <td><code class="small"><?= date('M d, H:i', $alarm['trigger_time'] ?? 0) ?></code></td>
                                    <td><?= esc($alarm['package_name'] ?? 'N/A') ?></td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td><?= format_timestamp_display((int)$r['extracted_at']) ?></td>
                                    <td class="text-center">
                                            <button class="btn btn-sm btn-outline-danger delete-row"
                                                data-id="<?= $r['id'] ?? '' ?>"
                                                data-url="<?= base_url('advanced/alarms/delete') ?>"
                                                title="Delete this row">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer"><div class="float-right"><?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?></div></div>
        </div>
    </div></div></div></section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>