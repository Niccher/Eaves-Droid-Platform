<?php
/**
 * Device Activity View
 *
 * @var array $activity_dump
 * @var int $totalActivities
 * @var object $pager
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
                                        <th>Activity</th>
                                        <th>Battery</th>
                                        <th>Screen</th>
                                        <th>Network</th>
                                        <th>Timestamp</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($activity_dump)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
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
                                            $activityTime = !empty($act['activity_time']) ? format_timestamp_display((int)$act['activity_time']) : '—';
                                            $extractedAt = !empty($act['extracted_at']) ? format_timestamp_display((int)$act['extracted_at']) : '—';
                                            $isInteractive = ($act['is_interactive'] ?? 0) == 1;
                                            $screenOn = ($act['screen_on'] ?? 0) == 1;
                                            $battery = $act['battery_level'] ?? null;
                                            $charging = $act['charging_status'] ?? '';
                                            $network = strtoupper($act['network_type'] ?? '—');
                                            $activity = strtoupper($act['status'] ?? $act['activity_type'] ?? '—');

                                            $actIcon = 'fa-question-circle';
                                            $actColor = 'secondary';
                                            if (strpos($activity, 'WALK') !== false || strpos($activity, 'RUN') !== false) {
                                                $actIcon = 'fa-running'; $actColor = 'success';
                                            } elseif (strpos($activity, 'STILL') !== false || strpos($activity, 'IDLE') !== false) {
                                                $actIcon = 'fa-stop-circle'; $actColor = 'secondary';
                                            } elseif (strpos($activity, 'VEHICLE') !== false || strpos($activity, 'DRIV') !== false) {
                                                $actIcon = 'fa-car'; $actColor = 'info';
                                            } elseif (strpos($activity, 'BICYCLE') !== false) {
                                                $actIcon = 'fa-bicycle'; $actColor = 'warning';
                                            } elseif (strpos($activity, 'TILT') !== false) {
                                                $actIcon = 'fa-mobile-alt'; $actColor = 'dark';
                                            } elseif (strpos($activity, 'UNKNOWN') !== false) {
                                                $actIcon = 'fa-question'; $actColor = 'light';
                                            }

                                            $battColor = 'success';
                                            if ($battery !== null) {
                                                if ($battery <= 15) $battColor = 'danger';
                                                elseif ($battery <= 30) $battColor = 'warning';
                                            }

                                            $netColor = 'secondary';
                                            if (strpos($network, 'WIFI') !== false) $netColor = 'primary';
                                            elseif (strpos($network, '4G') !== false || strpos($network, 'LTE') !== false) $netColor = 'success';
                                            elseif (strpos($network, '3G') !== false) $netColor = 'info';
                                            elseif (strpos($network, '2G') !== false || strpos($network, 'EDGE') !== false) $netColor = 'warning';
                                            elseif (strpos($network, '—') !== false || empty($act['network_type'])) $netColor = 'light';
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge badge-<?php echo $actColor; ?> p-2 mr-2" style="font-size:1rem;width:36px;">
                                                            <i class="fas <?php echo $actIcon; ?>"></i>
                                                        </span>
                                                        <div>
                                                            <strong><?php echo $activity; ?></strong>
                                                            <br><small class="text-muted"><?php echo $isInteractive ? 'Interactive' : 'Background'; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <?php if ($battery !== null): ?>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress progress-xs flex-grow-1 mr-2" style="max-width:60px;height:6px;">
                                                                <div class="progress-bar bg-<?php echo $battColor; ?>" style="width:<?php echo $battery; ?>%"></div>
                                                            </div>
                                                            <small class="font-weight-bold text-<?php echo $battColor; ?>"><?php echo $battery; ?>%</small>
                                                        </div>
                                                        <?php if ($charging): ?>
                                                            <small class="text-muted"><i class="fas fa-plug mr-1"></i><?php echo ucfirst($charging); ?></small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge badge-<?php echo $screenOn ? 'success' : 'secondary'; ?> p-2" style="font-size:0.85rem;">
                                                        <i class="fas <?php echo $screenOn ? 'fa-sun' : 'fa-moon'; ?> mr-1"></i>
                                                        <?php echo $screenOn ? 'ON' : 'OFF'; ?>
                                                    </span>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge badge-<?php echo $netColor; ?> p-2"><?php echo $network; ?></span>
                                                </td>
                                                <td class="align-middle">
                                                    <div><?php echo $activityTime; ?></div>
                                                    <?php if (!empty($act['extracted_at'])): ?><small class="text-muted">Extracted: <?php echo $extractedAt; ?></small><?php endif; ?>
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
