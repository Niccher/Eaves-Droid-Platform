<?php /** @var array $latest_sensors @var array $latest_gnss @var array $latest_vb @var array $history @var object $pager @var string $nav_urls */ ?>
<?php $latest_vibration = $latest_vb ?? []; ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-satellite text-warning mr-2"></i>Sensors & Location</h1>
                        <span class="badge badge-warning border p-2 text-white"><i class="fas fa-database mr-1"></i>Snapshots: <b><?= count($history ?? []) ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Hardware sensors, GNSS constellations, and haptic vibration capabilities</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Current State - Tabbed -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="sensorTabs" role="tablist">
                                <li class="nav-item"><a class="nav-link active" id="tab-sensors" data-toggle="tab" href="#sensors" role="tab"><i class="fas fa-microchip mr-1"></i>Sensors (<?= count($latest_sensors ?? []) ?>)</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab-gnss" data-toggle="tab" href="#gnss" role="tab"><i class="fas fa-satellite mr-1"></i>GNSS</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab-vibration" data-toggle="tab" href="#vibration" role="tab"><i class="fas fa-bolt mr-1"></i>Vibration</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="sensorTabsContent">
                                <!-- Sensors Tab -->
                                <div class="tab-pane fade show active" id="sensors" role="tabpanel">
                                    <?php if (!empty($latest_sensors)): ?>
                                        <div class="mb-3">
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php 
                                                    $typeCounts = [];
                                                    foreach ($latest_sensors as $s) { $typeCounts[$s['type_string'] ?? 'Unknown'] = ($typeCounts[$s['type_string'] ?? 'Unknown'] ?? 0) + 1; }
                                                    foreach ($typeCounts as $type => $count): 
                                                        $icon = 'fas fa-satellite-dish text-muted';
                                                        if (stripos($type, 'accel') !== false) $icon = 'fas fa-arrows-alt-v text-primary';
                                                        elseif (stripos($type, 'gyro') !== false) $icon = 'fas fa-arrows-alt text-info';
                                                        elseif (stripos($type, 'light') !== false) $icon = 'fas fa-sun text-warning';
                                                        elseif (stripos($type, 'proxim') !== false) $icon = 'fas fa-tachometer-alt text-danger';
                                                        elseif (stripos($type, 'magnet') !== false) $icon = 'fas fa-compass text-success';
                                                        elseif (stripos($type, 'pressure') !== false) $icon = 'fas fa-compress-alt text-secondary';
                                                        elseif (stripos($type, 'temp') !== false) $icon = 'fas fa-thermometer-half text-danger';
                                                        elseif (stripos($type, 'humidity') !== false) $icon = 'fas fa-tint text-info';
                                                        elseif (stripos($type, 'step') !== false) $icon = 'fas fa-shoe-prints text-success';
                                                        elseif (stripos($type, 'heart') !== false) $icon = 'fas fa-heartbeat text-danger';
                                                    ?>
                                                        <span class="badge badge-secondary p-2" title="<?= htmlspecialchars($type) ?>"><i class="<?= $icon ?> mr-1"></i><?= $count ?></span>
                                                    <?php endforeach; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped mb-0">
                                                <thead class="thead-light">
                                                <tr>
                                                    <th><i class="fas fa-microchip mr-1"></i>Sensor</th>
                                                    <th><i class="fas fa-building mr-1"></i>Vendor</th>
                                                    <th><i class="fas fa-tag mr-1"></i>Type</th>
                                                    <th><i class="fas fa-expand-arrows-alt mr-1"></i>Max Range</th>
                                                    <th><i class="fas fa-sliders-h mr-1"></i>Resolution</th>
                                                    <th><i class="fas fa-bolt mr-1"></i>Power (mA)</th>
                                                    <th><i class="fas fa-code-branch mr-1"></i>Version</th>
                                                    <th><i class="fas fa-cogs mr-1"></i>Flags</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($latest_sensors as $s): 
                                                    $typeId = $s['type_id'] ?? 0;
                                                    $icon = 'fas fa-satellite-dish text-muted';
                                                    if ($typeId == 1) $icon = 'fas fa-arrows-alt-v text-primary';      // Accelerometer
                                                    elseif ($typeId == 3) $icon = 'fas fa-compass text-success';         // Orientation
                                                    elseif ($typeId == 6) $icon = 'fas fa-arrows-alt text-info';         // Gyroscope
                                                    elseif ($typeId == 11 || $typeId == 5) $icon = 'fas fa-sun text-warning'; // Light
                                                    elseif ($typeId == 8) $icon = 'fas fa-tachometer-alt text-danger';   // Proximity
                                                    $power = $s['power_ma'] ?? 0;
                                                    $powerBadge = $power <= 1 ? 'success' : ($power <= 5 ? 'warning' : 'danger');
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="adv-avatar mr-2"><i class="<?= $icon ?>"></i></div>
                                                                <div>
                                                                    <div class="font-weight-bold"><?= htmlspecialchars($s['sensor_name'] ?? '—') ?></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><small><?= htmlspecialchars($s['vendor'] ?? '—') ?></small></td>
                                                        <td>
                                                            <span class="badge badge-secondary"><?= htmlspecialchars($s['type_string'] ?? '') ?></span>
                                                            <small class="text-muted d-block">ID: <?= $typeId ?></small>
                                                        </td>
                                                        <td><code><?= $s['maximum_range'] ?? '—' ?></code></td>
                                                        <td><code><?= $s['resolution'] ?? '—' ?></code></td>
                                                        <td>
                                                            <span class="badge badge-<?= $powerBadge ?>">
                                                                <?= $power ?? '—' ?> mA
                                                            </span>
                                                        </td>
                                                        <td><small class="text-muted">v<?= $s['version'] ?? '1' ?></small></td>
                                                        <td>
                                                            <?php if (!empty($s['is_wakeup'])): ?><span class="badge badge-info p-1 mr-1">Wakeup</span><?php endif; ?>
                                                            <?php if (!empty($s['is_dynamic'])): ?><span class="badge badge-warning p-1 mr-1">Dynamic</span><?php endif; ?>
                                                            <?php if ($s['reporting_mode']): ?><span class="badge badge-secondary p-1"><?= htmlspecialchars($s['reporting_mode']) ?></span><?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No sensor data</p>
                                    <?php endif; ?>
                                </div>

                                <!-- GNSS Tab -->
<div class="tab-pane fade" id="gnss" role="tabpanel">
                                     <?php if (!empty($latest_gnss)): ?>
                                         <div class="row">
                                             <div class="col-md-6">
                                                 <h6 class="text-muted mb-3"><i class="fas fa-satellite mr-1"></i>Constellations Supported</h6>
                                                 <?php $constellations = $latest_gnss['constellations_supported'] ?? []; ?>
                                                <?php if (!empty($constellations)): ?>
                                                    <div class="d-flex flex-wrap gap-1 mb-3">
                                                        <?php foreach ($constellations as $c): ?>
                                                            <span class="badge badge-primary p-2"><?= htmlspecialchars(is_array($c) ? ($c['name'] ?? '—') : $c) ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">None reported</span>
                                                <?php endif; ?>
                                                
                                                <h6 class="text-muted mb-3"><i class="fas fa-satellite-dish mr-1"></i>Antenna & Tracking</h6>
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4">Antenna Type</dt><dd class="col-sm-8"><?= htmlspecialchars($latest_gnss['antenna_type'] ?? '—') ?></dd>
                                                    <dt class="col-sm-4">Max Tracked</dt><dd class="col-sm-8"><span class="badge badge-info p-2"><?= (int)($latest_gnss['max_satellites_tracked'] ?? 0) ?> satellites</span></dd>
                                                    <dt class="col-sm-4">Max Used</dt><dd class="col-sm-8"><small class="text-muted"><?= (int)($latest_gnss['max_satellites_used'] ?? 0) ?> used in fix</small></dd>
                                                </dl>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-3"><i class="fas fa-check-double mr-1"></i>Capabilities</h6>
                                                <div class="d-flex flex-wrap gap-2 mb-3">
                                                    <?php if (!empty($latest_gnss['raw_measurements_supported'])): ?><span class="badge badge-success p-2"><i class="fas fa-wave-square mr-1"></i>Raw Measurements</span><?php endif; ?>
                                                    <?php if (!empty($latest_gnss['agps_supported'])): ?><span class="badge badge-info p-2"><i class="fas fa-map-marker-alt mr-1"></i>AGPS</span><?php endif; ?>
                                                    <?php if (!empty($latest_gnss['dead_reckoning_supported'])): ?><span class="badge badge-warning p-2"><i class="fas fa-compass mr-1"></i>Dead Reckoning</span><?php endif; ?>
                                                    <?php if (!empty($latest_gnss['gnss_year_of_hardware'])): ?><span class="badge badge-secondary p-2"><i class="fas fa-calendar mr-1"></i><?= htmlspecialchars($latest_gnss['gnss_year_of_hardware']) ?></span><?php endif; ?>
                                                </div>
                                                
                                                <?php if (!empty($latest_gnss['frequencies_supported'])): ?>
                                                    <h6 class="text-muted mb-2">Frequencies</h6>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php foreach ($latest_gnss['frequencies_supported'] as $f): ?>
                                                            <span class="badge badge-light text-dark p-2"><?= htmlspecialchars(is_array($f) ? ($f['name'] ?? json_encode($f)) : $f) ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No GNSS hardware data</p>
                                    <?php endif; ?>
                                </div>

                                <!-- Vibration Tab -->
                                <div class="tab-pane fade" id="vibration" role="tabpanel">
                                    <?php if ($latest_vibration): ?>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-3"><i class="fas fa-bolt mr-1"></i>Vibrator Capabilities</h6>
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4">Has Vibrator</dt><dd class="col-sm-8"><?= !empty($latest_vibration['has_vibrator']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                    <dt class="col-sm-4">Vibrator ID</dt><dd class="col-sm-8"><?= $latest_vibration['vibrator_id'] ?? '—' ?></dd>
                                                    <dt class="col-sm-4">Amplitude Control</dt><dd class="col-sm-8"><?= !empty($latest_vibration['amplitude_control']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                    <dt class="col-sm-4">Frequency Control</dt><dd class="col-sm-8"><?= !empty($latest_vibration['frequency_control']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></dd>
                                                </dl>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-3"><i class="fas fa-wave-square mr-1"></i>Supported Effects</h6>
                                                <?php $effects = json_decode($latest_vibration['supported_effects'] ?? '[]', true); ?>
                                                <?php if (!empty($effects)): ?>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php foreach ($effects as $effect): ?>
                                                            <span class="badge badge-info p-2"><?= htmlspecialchars($effect) ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <p class="text-muted">No effect data</p>
                                                <?php endif; ?>
                                                
                                                <hr class="my-3">
                                                <h6 class="text-muted mb-3"><i class="fas fa-list mr-1"></i>Primitive Support</h6>
                                                <?php $primitives = json_decode($latest_vibration['supported_primitives'] ?? '[]', true); ?>
                                                <?php if (!empty($primitives)): ?>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php foreach ($primitives as $p): ?>
                                                            <span class="badge badge-warning p-2"><?= htmlspecialchars($p) ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <p class="text-muted">No primitive data</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No vibration data</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Sensors & Location History</h3>
                            <div class="card-tools ml-auto"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#sensorsLocationHistoryModal">Show All (<?= count($history ?? []) ?>)</button><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                        <th><i class="fas fa-microchip mr-1"></i>Sensors</th>
                                        <th><i class="fas fa-satellite mr-1"></i>GNSS</th>
                                        <th><i class="fas fa-bolt mr-1"></i>Vibration</th>
                                        <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($history)): ?>
                                        <tr><td colspan="6" class="text-center py-5"><div class="empty-state"><i class="fas fa-satellite fa-3x text-muted mb-3"></i><h4>No sensor/location history</h4></div></td></tr>
                                    <?php else: foreach (array_slice($history,0,5) as $h): 
                                        $sn = $h['sensor_profile'] ?? [];
                                        $gnss = $h['gnss_hardware'] ?? [];
                                        $vib = $h['vibration'] ?? [];
                                        // Count unique sensor types in this snapshot
                                        $sensorTypes = [];
                                        foreach ($sn as $s) { $sensorTypes[$s['type_id'] ?? 0] = true; }
                                    ?>
                                        <tr>
                                            <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                            <td>
                                                <span class="badge badge-warning"><?= count($sensorTypes) ?> types</span>
                                                <br><small class="text-muted"><?= count($sn) ?> entries</small>
                                            </td>
                                            <td>
                                                <?php if ($gnss): ?>
                                                    <span class="badge badge-primary"><?= count($gnss['constellations_supported'] ?? []) ?> constellations</span>
                                                    <br><small class="text-muted">Max tracked: <?= (int)($gnss['max_satellites_tracked'] ?? 0) ?></small>
                                                    <?php if (!empty($gnss['raw_measurements_supported'])): ?><span class="badge badge-success ml-1">Raw</span><?php endif; ?>
                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($vib): ?>
                                                    <?= !empty($vib['has_vibrator']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?>
                                                    <br><small class="text-muted">Effects: <?= count(json_decode($vib['supported_effects'] ?? '[]', true)) ?></small>
                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/sensors_location/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer"><div class="float-right"><?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

    <!-- Modal: Full Sensors & Location History -->
    <div class="modal fade" id="sensorsLocationHistoryModal" tabindex="-1" role="dialog" aria-labelledby="sensorsLocationHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="sensorsLocationHistoryModalLabel"><i class="fas fa-history mr-2"></i>Full Sensors & Location History</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                    <th><i class="fas fa-microchip mr-1"></i>Sensors</th>
                                    <th><i class="fas fa-satellite mr-1"></i>GNSS</th>
                                    <th><i class="fas fa-bolt mr-1"></i>Vibration</th>
                                    <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history)): ?>
                                <tr><td colspan="6" class="text-center py-5"><div class="empty-state"><i class="fas fa-satellite fa-3x text-muted mb-3"></i><h4>No sensor/location history</h4></div></td></tr>
                                <?php else: foreach ($history as $h):
                                    $sn = $h['sensor_profile'] ?? [];
                                    $gnss = $h['gnss_hardware'] ?? [];
                                    $vib = $h['vibration'] ?? [];
                                    $sensorTypes = [];
                                    foreach ($sn as $s) { $sensorTypes[$s['type_id'] ?? 0] = true; }
                                ?>
                                <tr>
                                    <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                    <td>
                                        <span class="badge badge-warning"><?= count($sensorTypes) ?> types</span>
                                        <br><small class="text-muted"><?= count($sn) ?> entries</small>
                                    </td>
                                    <td>
                                        <?php if ($gnss): ?>
                                            <span class="badge badge-primary"><?= count($gnss['constellations_supported'] ?? []) ?> constellations</span>
                                            <br><small class="text-muted">Max tracked: <?= (int)($gnss['max_satellites_tracked'] ?? 0) ?></small>
                                            <?php if (!empty($gnss['raw_measurements_supported'])): ?><span class="badge badge-success ml-1">Raw</span><?php endif; ?>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($vib): ?>
                                            <?= !empty($vib['has_vibrator']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?>
                                            <br><small class="text-muted">Effects: <?= count(json_decode($vib['supported_effects'] ?? '[]', true)) ?></small>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/sensors_location/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>