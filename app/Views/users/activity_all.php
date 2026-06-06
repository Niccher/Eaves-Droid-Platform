<?php
/**
 * Device Activity View
 *
 * @var array $activity_dump
 * @var int $totalActivities
 * @var object $pager
 * @var string $nav_urls
 */
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-walking text-primary mr-2"></i>
                            Device Activity
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-chart-bar text-primary mr-1"></i>
                                Total: <b><?php echo $totalActivities ?? 0 ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Hardware status and user activity monitoring</p>
                </div>
                <div class="col-lg-4 col-md-6">
                </div>

            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-2"></i>
                                Events
                                <small class="text-muted ml-2">Showing <?php echo count($activity_dump) ?> entries</small>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 tabledump">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>Status/Activity</th>
                                        <th>Interactive</th>
                                        <th>Battery</th>
                                        <th>Network</th>
                                        <th>Screen</th>
                                        <th>Timestamp</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($activity_dump)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="empty-state">
                                                    <i class="fas fa-running fa-3x text-muted mb-3"></i>
                                                    <h4>No activity logs</h4>
                                                    <p class="text-muted">Device events will appear here</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($activity_dump as $act): ?>
                                            <?php
                                            $time = isset($act['activity_time']) ? date('Y-m-d H:i:s', $act['activity_time'] / 1000) : 'N/A';
                                            $interactiveBadge = ($act['is_interactive'] == 1) ? 'badge-success' : 'badge-danger';
                                            $screenBadge = ($act['screen_on'] == 1) ? 'badge-success' : 'badge-secondary';
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold"><?php echo strtoupper($act['status']); ?></div>
                                                    <small class="text-muted"><?php echo $act['activity_type'] ?? 'IDLE'; ?> (Conf: <?php echo $act['confidence']; ?>%)</small>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $interactiveBadge; ?>">
                                                        <?php echo ($act['is_interactive'] == 1) ? 'YES' : 'NO'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="progress progress-xs" style="width: 60px;">
                                                        <div class="progress-bar bg-<?php echo ($act['battery_level'] > 20) ? 'success' : 'danger'; ?>" style="width: <?php echo $act['battery_level']; ?>%"></div>
                                                    </div>
                                                    <small><?php echo $act['battery_level']; ?>% (<?php echo $act['charging_status']; ?>)</small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info"><?php echo strtoupper($act['network_type'] ?? 'unknown'); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $screenBadge; ?>">
                                                        <?php echo ($act['screen_on'] == 1) ? 'ON' : 'OFF'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div><?php echo $time; ?></div>
                                                    <small class="text-muted">Extracted: <?php echo date('Y-m-d H:i:s', $act['extracted_at'] / 1000); ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="float-right">
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

<style>
    .avatar-circle-sm { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .empty-state { padding: 3rem 1rem; text-align: center; }
    .empty-state i { opacity: 0.5; }
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
</style>
