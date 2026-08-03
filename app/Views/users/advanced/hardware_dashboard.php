<?php /** @var array $latest @var int $totalSnapshots @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-tachometer-alt text-secondary mr-2"></i>Hardware Dashboard</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Snapshots: <b><?= $totalSnapshots ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Real-time device health overview across all hardware subsystems</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Key Metrics Row -->
            <div class="row mb-4">
                <!-- Battery -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-success shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-1"><i class="fas fa-battery-three-quarters text-success mr-1"></i>Battery</h6>
                                    <?php if (!empty($latest['device_context'])): ?>
                                        <?php $dc = $latest['device_context']; $lvl = $dc['battery_level_percent'] ?? 0; ?>
                                        <div class="h3 mb-1"><?= number_format($lvl, 0) ?>%</div>
                                        <div class="progress" style="height:6px;">
                                            <div class="progress-bar bg-<?= $lvl > 50 ? 'success' : ($lvl > 20 ? 'warning' : 'danger') ?>" style="width:<?= $lvl ?>%"></div>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <?= $dc['battery_health'] ?? '—' ?> • 
                                            <?= $dc['battery_is_charging'] ? '<i class="fas fa-bolt text-warning"></i> Charging' : 'Discharging' ?>
                                            <?php if ($dc['battery_plugged_usb']): ?> • <i class="fas fa-usb text-info"></i> USB<?php endif; ?>
                                            <?php if ($dc['battery_plugged_ac']): ?> • <i class="fas fa-plug text-warning"></i> AC<?php endif; ?>
                                        </small>
                                    <?php else: ?>
                                        <div class="h3 text-muted">—</div>
                                        <small class="text-muted">No data</small>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= base_url('advanced/hardware/battery_power') ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thermal -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-danger shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-1"><i class="fas fa-thermometer-half text-danger mr-1"></i>Thermal</h6>
                                    <?php if (!empty($latest['thermal'])): ?>
                                        <?php $th = $latest['thermal']; 
                                            $zones = json_decode($th['thermal_zones_json'] ?? '[]', true);
                                            $hottest = null;
                                            foreach ($zones as $z) { if (($z['temp_celsius'] ?? 0) > ($hottest['temp_celsius'] ?? 0)) $hottest = $z; }
                                            $throttle = json_decode($th['cpu_throttle_json'] ?? '[]', true);
                                            $throttleCount = array_sum(array_column($throttle, 'throttle_count'));
                                        ?>
                                        <div class="h3 mb-1"><?= $hottest ? number_format($hottest['temp_celsius'], 1) : '—' ?>°C</div>
                                        <small class="text-muted d-block">
                                            Hottest: <?= htmlspecialchars($hottest['zone_name'] ?? '—') ?>
                                            <?php if ($throttleCount > 0): ?>
                                                • <span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i><?= $throttleCount ?> throttles</span>
                                            <?php endif; ?>
                                        </small>
                                    <?php else: ?>
                                        <div class="h3 text-muted">—</div>
                                        <small class="text-muted">No data</small>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= base_url('advanced/hardware/system_performance') ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Storage -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-info shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-1"><i class="fas fa-hdd text-info mr-1"></i>Storage</h6>
                                    <?php if (!empty($latest['storage'])): ?>
                                        <?php $st = $latest['storage'];
                                            $volumes = json_decode($st['volumes_json'] ?? '[]', true);
                                            $internal = null;
                                            foreach ($volumes as $v) { if (stripos($v['path'] ?? '', 'internal') !== false || stripos($v['description'] ?? '', 'internal') !== false) { $internal = $v; break; } }
                                            $internal = $internal ?? ($volumes[0] ?? null);
                                            $total = $internal['info']['total_bytes'] ?? 0;
                                            $avail = $internal['info']['available_bytes'] ?? 0;
                                            $pct = $total > 0 ? round(($total - $avail) / $total * 100) : 0;
                                        ?>
                                        <div class="h3 mb-1"><?= $pct ?>% used</div>
                                        <div class="progress" style="height:6px;">
                                            <div class="progress-bar bg-<?= $pct > 90 ? 'danger' : ($pct > 70 ? 'warning' : 'success') ?>" style="width:<?= $pct ?>%"></div>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <?= $internal ? format_bytes($avail) : '—' ?> free of <?= $internal ? format_bytes($total) : '—' ?>
                                        </small>
                                    <?php else: ?>
                                        <div class="h3 text-muted">—</div>
                                        <small class="text-muted">No data</small>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= base_url('advanced/hardware/storage_peripherals') ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Network -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-1"><i class="fas fa-signal text-primary mr-1"></i>Network</h6>
                                    <?php if (!empty($latest['network_info'])): ?>
                                        <?php $ni = $latest['network_info']; ?>
                                        <div class="h3 mb-1">
                                            <?php if ($ni['wifi_ssid']): ?>
                                                <i class="fas fa-wifi text-success"></i> <?= htmlspecialchars($ni['wifi_ssid']) ?>
                                            <?php elseif ($ni['is_connected']): ?>
                                                <i class="fas fa-mobile-alt text-primary"></i> Mobile
                                            <?php else: ?>
                                                <i class="fas fa-times-circle text-danger"></i> Offline
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted d-block">
                                            <?= $ni['wifi_rssi'] ? $ni['wifi_rssi'] . ' dBm' : ($ni['network_operator_name'] ?? '—') ?>
                                            <?php if ($ni['is_roaming']): ?> • <span class="badge badge-warning">Roaming</span><?php endif; ?>
                                        </small>
                                    <?php else: ?>
                                        <div class="h3 text-muted">—</div>
                                        <small class="text-muted">No data</small>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= base_url('advanced/hardware/network_connectivity') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Metrics Row -->
            <div class="row mb-4">
                <!-- Display & Graphics -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-1"><i class="fas fa-desktop text-secondary mr-1"></i>Display & GPU</h6>
                                    <?php if (!empty($latest['display_info']) || !empty($latest['hardware_graphics'])): ?>
                                        <?php $di = $latest['display_info'] ?? []; $hg = $latest['hardware_graphics'] ?? []; ?>
                                        <div class="small mb-1">
                                            <?php if ($di): ?>
                                                <?= $di['width_px'] ?? '—' ?>×<?= $di['height_px'] ?? '—' ?> • 
                                                <?= $di['refresh_rate'] ?? '—' ?>Hz • 
                                                <?= $di['density_dpi'] ?? '—' ?> DPI
                                            <?php endif; ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?php if ($hg): ?>
                                                <?php $gpu = json_decode($hg['gpu_renderer_json'] ?? '{}', true); ?>
                                                GPU: <?= htmlspecialchars($gpu['gl_renderer'] ?? '—') ?>
                                                <?php if (!empty($gpu['vulkan_available'])): ?> • <span class="badge badge-success">Vulkan</span><?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="small text-muted">No data</div>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= base_url('advanced/hardware/display_graphics') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sensors & Location -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-warning shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-1"><i class="fas fa-satellite text-warning mr-1"></i>Sensors & GNSS</h6>
                                    <?php if (!empty($latest['sensors']) || !empty($latest['gnss_hardware'])): ?>
                                        <?php $sn = $latest['sensors'] ?? []; $gnss = $latest['gnss_hardware'] ?? []; ?>
                                        <div class="h4 mb-1"><?= count($sn) ?> sensors</div>
                                        <small class="text-muted d-block">
                                            <?php $highPwr = array_filter($sn, fn($s) => ($s['power_ma'] ?? 0) > 5); ?>
                                            <?= count($highPwr) ?> high-power
                                            <?php if ($gnss): ?>
                                                • <?= count($gnss['constellations_supported'] ?? []) ?> constellations
                                                <?php if (!empty($gnss['raw_measurements_supported'])): ?><span class="badge badge-success ml-1">Raw</span><?php endif; ?>
                                            <?php endif; ?>
                                        </small>
                                    <?php else: ?>
                                        <div class="h4 text-muted">—</div>
                                        <small class="text-muted">No data</small>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= base_url('advanced/hardware/sensors_location') ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media Hardware -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-dark shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-1"><i class="fas fa-camera text-dark mr-1"></i>Camera & Audio</h6>
                                    <?php if (!empty($latest['camera_info']) || !empty($latest['audio_devices'])): ?>
                                        <?php $cam = $latest['camera_info'] ?? []; $aud = $latest['audio_devices'] ?? []; ?>
                                        <div class="small mb-1">
                                            <?= count($cam) ?> camera<?= count($cam) !== 1 ? 's' : '' ?>
                                            <?php if ($cam): ?>
                                                • Max: <?= max(array_map(fn($c) => ($c['pixel_array_width'] ?? 0) * ($c['pixel_array_height'] ?? 0), $cam)) / 1e6 ?> MP
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted"><?= count($aud) ?> audio device<?= count($aud) !== 1 ? 's' : '' ?></small>
                                    <?php else: ?>
                                        <div class="small text-muted">No data</div>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= base_url('advanced/hardware/media_hardware') ?>" class="btn btn-sm btn-outline-dark"><i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Short-Range & Auth -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-purple shadow-sm h-100" style="border-color: #6f42c1;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-1"><i class="fas fa-bluetooth-b text-purple mr-1"></i>BT / NFC / Bio</h6>
                                    <?php 
                                        $bluetooth = $latest['bluetooth'] ?? $latest_bt ?? []; 
                                        $nfc = $latest['nfc'] ?? $latest_nf ?? []; 
                                        $bio = $latest['biometric'] ?? $latest_bm ?? [];
                                    ?>
                                    <div class="d-flex gap-2 mb-1">
                                        <span class="badge badge-<?= !empty($bluetooth['is_enabled']) ? 'success' : 'secondary' ?> p-2">
                                            <i class="fab fa-bluetooth-b mr-1"></i><?= !empty($bluetooth['is_enabled']) ? 'ON' : 'OFF' ?>
                                            <?php if (!empty($bluetooth) && !empty($bluetooth['paired_count'])): ?> (<?= $bluetooth['paired_count'] ?>)<?php endif; ?>
                                        </span>
                                        <span class="badge badge-<?= !empty($nfc['nfc_enabled']) ? 'info' : 'secondary' ?> p-2">
                                            <i class="fas fa-signal mr-1"></i>NFC <?= !empty($nfc['nfc_enabled']) ? 'ON' : 'OFF' ?>
                                        </span>
                                        <span class="badge badge-<?= !empty($bio) ? 'warning' : 'secondary' ?> p-2">
                                            <i class="fas fa-fingerprint mr-1"></i><?= !empty($bio) ? 'Enrolled' : 'None' ?>
                                        </span>
                                    </div>
                                </div>
                                <a href="<?= base_url('advanced/hardware/shortrange_auth') ?>" class="btn btn-sm btn-outline-purple" style="border-color:#6f42c1;color:#6f42c1;"><i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Snapshots Timeline -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history mr-2"></i>Recent Hardware Snapshots</h3>
                            <div class="card-tools ml-auto"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#recentSnapshotsModal">Show All (<?= count($recentSnapshots ?? []) ?>)</button><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                        <th><i class="fas fa-battery-half text-success mr-1"></i>Battery</th>
                                        <th><i class="fas fa-thermometer-half text-danger mr-1"></i>Thermal</th>
                                        <th><i class="fas fa-hdd text-info mr-1"></i>Storage</th>
                                        <th><i class="fas fa-signal text-primary mr-1"></i>Network</th>
                                        <th><i class="fas fa-satellite text-warning mr-1"></i>Sensors</th>
                                        <th><i class="fas fa-cogs text-muted mr-1"></i>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($recentSnapshots)): ?>
                                        <tr><td colspan="7" class="text-center py-5">
                                            <div class="empty-state"><i class="fas fa-history fa-3x text-muted mb-3"></i><h4>No snapshots</h4><p class="text-muted">Hardware data will appear here once extracted</p></div>
                                        </td></tr>
                                    <?php else: foreach (array_slice($recentSnapshots,0,5) as $i => $snap): ?>
                                        <tr>
                                            <td><small><?= format_timestamp_display((int)$snap['extracted_at']) ?></small></td>
                                            <td>
                                                <?php if (!empty($snap['device_context']['battery_level_percent'])): ?>
                                                    <?php $lvl = $snap['device_context']['battery_level_percent']; ?>
                                                    <span class="badge badge-<?= $lvl > 50 ? 'success' : ($lvl > 20 ? 'warning' : 'danger') ?>"><?= $lvl ?>%</span>
                                                    <?php if (!empty($snap['device_context']['battery_is_charging'])): ?><i class="fas fa-bolt text-warning ml-1"></i><?php endif; ?>
                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($snap['thermal']['thermal_zones_json'])): ?>
                                                    <?php $zones = json_decode($snap['thermal']['thermal_zones_json'], true); 
                                                        $hottest = null; foreach ($zones as $z) { if (($z['temp_celsius'] ?? 0) > ($hottest['temp_celsius'] ?? 0)) $hottest = $z; } ?>
                                                    <span class="font-weight-bold"><?= $hottest ? number_format($hottest['temp_celsius'], 1) : '—' ?>°C</span>
                                                    <?php if ($hottest): ?><small class="text-muted d-block"><?= htmlspecialchars($hottest['zone_name']) ?></small><?php endif; ?>
                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($snap['storage']['volumes_json'])): ?>
                                                    <?php $vols = json_decode($snap['storage']['volumes_json'], true);
                                                        $int = null; foreach ($vols as $v) { if (stripos($v['path'] ?? '', 'internal') !== false) { $int = $v; break; } }
                                                        $int = $int ?? ($vols[0] ?? null);
                                                        if ($int && ($int['info']['total_bytes'] ?? 0) > 0): 
                                                            $pct = round(($int['info']['total_bytes'] - $int['info']['available_bytes']) / $int['info']['total_bytes'] * 100);
                                                    ?>
                                                        <span class="badge badge-<?= $pct > 90 ? 'danger' : ($pct > 70 ? 'warning' : 'success') ?>"><?= $pct ?>%</span>
                                                        <small class="text-muted d-block"><?= format_bytes($int['info']['available_bytes'] ?? 0) ?> free</small>
                                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($snap['network_info']['wifi_ssid'])): ?>
                                                    <i class="fas fa-wifi text-success mr-1"></i><?= htmlspecialchars($snap['network_info']['wifi_ssid']) ?>
                                                    <?php if ($snap['network_info']['wifi_rssi']): ?><small class="text-muted d-block"><?= $snap['network_info']['wifi_rssi'] ?> dBm</small><?php endif; ?>
                                                <?php elseif (!empty($snap['network_info']['is_connected'])): ?>
                                                    <i class="fas fa-mobile-alt text-primary mr-1"></i>Mobile
                                                    <?php if ($snap['network_info']['network_operator_name']): ?><small class="text-muted d-block"><?= htmlspecialchars($snap['network_info']['network_operator_name']) ?></small><?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Offline</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($snap['sensors'])): ?>
                                                    <span class="badge badge-warning"><?= count($snap['sensors']) ?></span>
                                                    <?php if (!empty($snap['gnss_hardware']['constellations_supported'])): ?>
                                                        <small class="text-muted d-block"><?= count($snap['gnss_hardware']['constellations_supported']) ?> constellations</small>
                                                    <?php endif; ?>
                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $snap['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/hardware_dashboard/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="float-right">
                                <?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

    <!-- Modal: Full Recent Snapshots History -->
    <div class="modal fade" id="recentSnapshotsModal" tabindex="-1" role="dialog" aria-labelledby="recentSnapshotsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="recentSnapshotsModalLabel"><i class="fas fa-history mr-2"></i>Full Recent Hardware Snapshots</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                    <th><i class="fas fa-battery-half text-success mr-1"></i>Battery</th>
                                    <th><i class="fas fa-thermometer-half text-danger mr-1"></i>Thermal</th>
                                    <th><i class="fas fa-hdd text-info mr-1"></i>Storage</th>
                                    <th><i class="fas fa-signal text-primary mr-1"></i>Network</th>
                                    <th><i class="fas fa-satellite text-warning mr-1"></i>Sensors</th>
                                    <th class="text-center"><i class="fas fa-cogs text-muted mr-1"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentSnapshots)): ?>
                                <tr><td colspan="7" class="text-center py-5"><div class="empty-state"><i class="fas fa-history fa-3x text-muted mb-3"></i><h4>No snapshots</h4></div></td></tr>
                                <?php else: foreach ($recentSnapshots as $i => $snap): ?>
                                <tr>
                                    <td><small><?= format_timestamp_display((int)$snap['extracted_at']) ?></small></td>
                                    <td>
                                        <?php if (!empty($snap['device_context']['battery_level_percent'])): ?>
                                            <?php $lvl = $snap['device_context']['battery_level_percent']; ?>
                                            <span class="badge badge-<?= $lvl > 50 ? 'success' : ($lvl > 20 ? 'warning' : 'danger') ?>"><?= $lvl ?>%</span>
                                            <?php if (!empty($snap['device_context']['battery_is_charging'])): ?><i class="fas fa-bolt text-warning ml-1"></i><?php endif; ?>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($snap['thermal']['thermal_zones_json'])): ?>
                                            <?php $zones = json_decode($snap['thermal']['thermal_zones_json'], true); 
                                                $hottest = null; foreach ($zones as $z) { if (($z['temp_celsius'] ?? 0) > ($hottest['temp_celsius'] ?? 0)) $hottest = $z; } ?>
                                            <span class="font-weight-bold"><?= $hottest ? number_format($hottest['temp_celsius'], 1) : '—' ?>°C</span>
                                            <?php if ($hottest): ?><small class="text-muted d-block"><?= htmlspecialchars($hottest['zone_name']) ?></small><?php endif; ?>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($snap['storage']['volumes_json'])): ?>
                                            <?php $vols = json_decode($snap['storage']['volumes_json'], true);
                                                $int = null; foreach ($vols as $v) { if (stripos($v['path'] ?? '', 'internal') !== false) { $int = $v; break; } }
                                                $int = $int ?? ($vols[0] ?? null);
                                                if ($int && ($int['info']['total_bytes'] ?? 0) > 0): 
                                                    $pct = round(($int['info']['total_bytes'] - $int['info']['available_bytes']) / $int['info']['total_bytes'] * 100);
                                            ?>
                                                <span class="badge badge-<?= $pct > 90 ? 'danger' : ($pct > 70 ? 'warning' : 'success') ?>"><?= $pct ?>%</span>
                                                <small class="text-muted d-block"><?= format_bytes($int['info']['available_bytes'] ?? 0) ?> free</small>
                                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($snap['network_info']['wifi_ssid'])): ?>
                                            <i class="fas fa-wifi text-success mr-1"></i><?= htmlspecialchars($snap['network_info']['wifi_ssid']) ?>
                                            <?php if ($snap['network_info']['wifi_rssi']): ?><small class="text-muted d-block"><?= $snap['network_info']['wifi_rssi'] ?> dBm</small><?php endif; ?>
                                        <?php elseif (!empty($snap['network_info']['is_connected'])): ?>
                                            <i class="fas fa-mobile-alt text-primary mr-1"></i>Mobile
                                            <?php if ($snap['network_info']['network_operator_name']): ?><small class="text-muted d-block"><?= htmlspecialchars($snap['network_info']['network_operator_name']) ?></small><?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Offline</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($snap['sensors'])): ?>
                                            <span class="badge badge-warning"><?= count($snap['sensors']) ?></span>
                                            <?php if (!empty($snap['gnss_hardware']['constellations_supported'])): ?>
                                                <small class="text-muted d-block"><?= count($snap['gnss_hardware']['constellations_supported']) ?> constellations</small>
                                            <?php endif; ?>
                                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $snap['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/hardware_dashboard/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
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