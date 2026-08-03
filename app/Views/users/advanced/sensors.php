<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-ruler text-secondary mr-2"></i>Sensor Profile</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Accelerometer, gyroscope, magnetometer, barometer, and other sensors</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Sensor Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="sensorTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-tag text-info mr-1"></i> Sensor Name</th>
                            <th><i class="fas fa-cogs text-primary mr-1"></i> Type</th>
                            <th><i class="fas fa-industry text-secondary mr-1"></i> Vendor</th>
                            <th><i class="fas fa-ruler text-secondary mr-1"></i> Max Range</th>
                            <th><i class="fas fa-tachometer-alt text-info mr-1"></i> Resolution</th>
                            <th><i class="fas fa-battery-half text-success mr-1"></i> Power (mA)</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="9" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-ruler fa-3x text-muted mb-3"></i><h4>No sensor data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $rid = $r['id'] ?? 0;
                            $typeMap = [
                                1 => 'Accelerometer', 2 => 'Magnetic Field', 3 => 'Orientation',
                                4 => 'Gyroscope', 5 => 'Light', 6 => 'Pressure', 7 => 'Temperature',
                                8 => 'Proximity', 9 => 'Gravity', 10 => 'Linear Acceleration',
                                11 => 'Rotation Vector', 12 => 'Relative Humidity', 13 => 'Ambient Temperature',
                                14 => 'Uncalibrated Magnetic', 15 => 'Game Rotation Vector', 16 => 'Uncalibrated Gyro',
                                17 => 'Significant Motion', 18 => 'Step Detector', 19 => 'Step Counter',
                                20 => 'Geomagnetic Rotation Vector', 21 => 'Heart Rate', 22 => 'Tilt Detector',
                                23 => 'Wake Gesture', 24 => 'Glance Gesture', 25 => 'Pick Up Gesture',
                                28 => 'Wrist Tilt Gesture', 29 => 'Device Orientation', 30 => 'Pose 6DOF',
                                31 => 'Stationary Detect', 32 => 'Motion Detect', 33 => 'Heart Beat',
                                34 => 'Dynamic Sensor Meta', 35 => 'Additional Info'
                            ];
                            $typeLabel = $typeMap[$r['type_id'] ?? 0] ?? ($r['type_string'] ?? 'Unknown');
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#sensor-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td><strong><?= esc($r['sensor_name'] ?? '—') ?></strong></td>
                                <td><span class="badge badge-primary"><?= esc($typeLabel) ?></span></td>
                                <td><?= esc($r['vendor'] ?? '—') ?></td>
                                <td><?= esc($r['maximum_range'] ?? '—') ?></td>
                                <td><?= esc($r['resolution'] ?? '—') ?></td>
                                <td><?= esc($r['power_ma'] ?? '—') ?> mA</td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/sensor_profile/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="9" class="p-0 border-0">
                                    <div id="sensor-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-ruler mr-2"></i>Sensor Specs</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Name</th><td><?= esc($r['sensor_name'] ?? '—') ?></td></tr>
                                                        <tr><th>Type</th><td><span class="badge badge-primary"><?= esc($typeLabel) ?></span></td></tr>
                                                        <tr><th>Type ID</th><td><?= esc($r['type_id'] ?? '—') ?></td></tr>
                                                        <tr><th>Vendor</th><td><?= esc($r['vendor'] ?? '—') ?></td></tr>
                                                        <tr><th>Version</th><td><?= esc($r['version'] ?? '—') ?></td></tr>
                                                        <tr><th>Max Range</th><td><?= esc($r['maximum_range'] ?? '—') ?></td></tr>
                                                        <tr><th>Resolution</th><td><?= esc($r['resolution'] ?? '—') ?></td></tr>
                                                        <tr><th>Power</th><td><?= esc($r['power_ma'] ?? '—') ?> mA</td></tr>
                                                        <tr><th>Min Delay</th><td><?= esc($r['min_delay_us'] ?? '—') ?> µs</td></tr>
                                                        <tr><th>Max Delay</th><td><?= esc($r['max_delay_us'] ?? '—') ?> µs</td></tr>
                                                        <tr><th>FIFO Reserved</th><td><?= esc($r['fifo_reserved_event_count'] ?? '—') ?></td></tr>
                                                        <tr><th>FIFO Max</th><td><?= esc($r['fifo_max_event_count'] ?? '—') ?></td></tr>
                                                        <tr><th>Wakeup</th><td><?= !empty($r['is_wakeup']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Dynamic</th><td><?= !empty($r['is_dynamic']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Reporting Mode</th><td><?= esc($r['reporting_mode'] ?? '—') ?></td></tr>
                                                        <tr><th>Direct Channel</th><td><?= esc($r['direct_channel_type'] ?? '—') ?></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-cogs mr-2"></i>Advanced</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Calibration Params</th><td><pre class="mb-0 small"><?= esc(json_encode($r['calibration_params'] ?? [], JSON_PRETTY_PRINT)) ?></pre></td></tr>
                                                        <tr><th>Mounting Matrix</th><td><pre class="mb-0 small"><?= esc(json_encode($r['mounting_matrix'] ?? [], JSON_PRETTY_PRINT)) ?></pre></td></tr>
                                                        <tr><th>Additional Info</th><td><pre class="mb-0 small"><?= esc(json_encode($r['additional_info'] ?? [], JSON_PRETTY_PRINT)) ?></pre></td></tr>
                                                        <tr><th>Permission</th><td><?= esc($r['required_permission'] ?? '—') ?></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-12">
                                                    <h6 class="mt-3"><i class="fas fa-info-circle mr-2"></i>Snapshot Info</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Extracted At</th><td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—' ?></td></tr>
                                                        <tr><th>Entry ID</th><td><code><?= $rid ?></code></td></tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </tr>
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