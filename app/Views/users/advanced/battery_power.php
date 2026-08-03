<?php /** @var array $latest_dc @var array $latest_bs @var array $history @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-battery-three-quarters text-success mr-2"></i>Battery & Power</h1>
                        <span class="badge badge-success border p-2 text-white"><i class="fas fa-database mr-1"></i>Snapshots: <b><?= count($history ?? []) ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Battery health, charging state, capacity, and power consumption trends</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Current State Cards -->
            <div class="row mb-4">
                <!-- Battery Level & Health -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-success shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <?php if ($latest_dc):
                                $lvl = $latest_dc['battery_level_percent'] ?? 0;
                                $health = $latest_dc['battery_health'] ?? '—';
                                $charging = $latest_dc['battery_is_charging'] ?? false;
                                $plugUsb = $latest_dc['battery_plugged_usb'] ?? false;
                                $plugAc = $latest_dc['battery_plugged_ac'] ?? false;
                            ?>
                                <div class="mb-3">
                                    <span class="h1 font-weight-bold text-success"><?= number_format($lvl, 0) ?>%</span>
                                    <div class="progress mt-2" style="height:8px; max-width:120px; margin:0 auto;">
                                        <div class="progress-bar bg-<?= $lvl > 50 ? 'success' : ($lvl > 20 ? 'warning' : 'danger') ?>" style="width:<?= $lvl ?>%"></div>
                                    </div>
                                    <small class="text-muted d-block mt-1">Health: <?= $health ?></small>
                                </div>
                                <div>
                                    <span class="badge badge-<?= $charging ? 'success' : 'secondary' ?> p-2">
                                        <i class="fas fa-<?= $charging ? 'bolt' : 'battery' ?> mr-1"></i>
                                        <?= $charging ? 'Charging' : 'Discharging' ?>
                                    </span>
                                    <?php if ($plugUsb): ?><span class="badge badge-info ml-1 p-2"><i class="fas fa-usb mr-1"></i>USB</span><?php endif; ?>
                                    <?php if ($plugAc): ?><span class="badge badge-warning ml-1 p-2"><i class="fas fa-plug mr-1"></i>AC</span><?php endif; ?>
                                </div>
                            <?php else: ?>
                                <i class="fas fa-battery-quarter fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No data</h4>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Temperature & Voltage -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-danger shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <?php if ($latest_dc || $latest_bs): ?>
                                <h6 class="text-muted mb-3"><i class="fas fa-thermometer-half mr-1"></i>Temperature</h6>
                                <div class="h2 mb-1">
                                    <?= $latest_bs['temperature_celsius'] ?? $latest_dc['battery_temperature_celsius'] ?? '—' ?>°C
                                </div>
                                <hr class="my-3">
                                <h6 class="text-muted mb-3"><i class="fas fa-bolt mr-1"></i>Voltage</h6>
                                <div class="h3 mb-1">
                                    <?= isset($latest_dc['battery_voltage_mv']) ? number_format($latest_dc['battery_voltage_mv']) . ' mV' : ($latest_bs['voltage_mv'] ? number_format($latest_bs['voltage_mv']) . ' mV' : '—') ?>
                                </div>
                            <?php else: ?>
                                <i class="fas fa-thermometer fa-4x text-muted mb-3"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Capacity & Charge Counter -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-warning shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <?php if ($latest_bs): ?>
                                <h6 class="text-muted mb-3"><i class="fas fa-battery-full mr-1"></i>Capacity</h6>
                                <div class="h2 mb-1"><?= $latest_bs['capacity_percent'] ?? '—' ?>%</div>
                                <small class="text-muted">Design vs Current</small>
                                <hr class="my-3">
                                <h6 class="text-muted mb-3"><i class="fas fa-tachometer-alt mr-1"></i>Charge Counter</h6>
                                <div class="h4 mb-1">
                                    <?= $latest_bs['charge_counter_uah'] ? number_format($latest_bs['charge_counter_uah']) . ' μAh' : '—' ?>
                                </div>
                                <small class="text-muted">Current: <?= $latest_bs['current_now_ua'] ? number_format($latest_bs['current_now_ua']) . ' μA' : '—' ?></small>
                            <?php else: ?>
                                <i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Status Details -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-info shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <?php if ($latest_bs): ?>
                                <h6 class="text-muted mb-3"><i class="fas fa-info-circle mr-1"></i>Status Details</h6>
                                <dl class="row mb-0 text-left">
                                    <dt class="col-6 text-right">Status</dt><dd class="col-6"><?= $latest_bs['status'] ?? '—' ?></dd>
                                    <dt class="col-6 text-right">Health</dt><dd class="col-6"><?= $latest_bs['health'] ?? '—' ?></dd>
                                    <dt class="col-6 text-right">Plugged</dt><dd class="col-6"><?= $latest_bs['plugged_type'] ?? '—' ?></dd>
                                    <dt class="col-6 text-right">Technology</dt><dd class="col-6"><?= $latest_bs['technology'] ?? '—' ?></dd>
                                    <dt class="col-6 text-right">Temp (deci-C)</dt><dd class="col-6"><?= $latest_bs['temperature_deci_c'] ?? '—' ?></dd>
                                    <dt class="col-6 text-right">Energy Counter</dt><dd class="col-6"><?= $latest_bs['energy_counter_uwh'] ? number_format($latest_bs['energy_counter_uwh']) . ' μWh' : '—' ?></dd>
                                </dl>
                            <?php else: ?>
                                <i class="fas fa-info fa-4x text-muted mb-3"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Battery History</h3>
                            <div class="card-tools ml-auto">
                                <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#batteryHistoryModal">Show All (<?= count($history ?? []) ?>)</button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Extracted</th>
                                        <th><i class="fas fa-battery-half text-success mr-1"></i>Level</th>
                                        <th><i class="fas fa-plug text-warning mr-1"></i>Charging</th>
                                        <th><i class="fas fa-thermometer-half text-danger mr-1"></i>Temp</th>
                                        <th><i class="fas fa-bolt text-info mr-1"></i>Voltage</th>
                                        <th><i class="fas fa-battery-full text-warning mr-1"></i>Capacity</th>
                                        <th><i class="fas fa-tachometer-alt text-secondary mr-1"></i>Current</th>
                                        <th><i class="fas fa-info-circle text-muted mr-1"></i>Status / Health</th>
                                        <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($history)): ?>
                                        <tr><td colspan="9" class="text-center py-5">
                                            <div class="empty-state"><i class="fas fa-battery-quarter fa-3x text-muted mb-3"></i><h4>No battery history</h4><p class="text-muted">Data will appear here once extracted</p></div>
                                        </td></tr>
                                    <?php else: foreach (array_slice($history,0,5) as $h): ?>
                                        <?php 
                                            $dc = $h['device_context'] ?? [];
                                            $bs = $h['battery_stats'] ?? [];
                                            $lvl = $dc['battery_level_percent'] ?? $bs['level_percent'] ?? 0;
                                            $barCol = $lvl > 50 ? 'success' : ($lvl > 20 ? 'warning' : 'danger');
                                        ?>
                                        <tr>
                                            <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress mr-2" style="width:60px;height:8px;border-radius:4px;">
                                                        <div class="progress-bar bg-<?= $barCol ?>" style="width:<?= $lvl ?>%"></div>
                                                    </div>
                                                    <span class="font-weight-bold"><?= number_format($lvl, 0) ?>%</span>
                                                </div>
                                             </td>
<td>
                                                  <span class="badge badge-<?= ($latest_dc['battery_is_charging'] ?? $latest_bs['is_charging'] ?? false) ? 'success' : 'secondary' ?>">
                                                      <i class="fas fa-<?= ($latest_dc['battery_is_charging'] ?? $latest_bs['is_charging'] ?? false) ? 'bolt' : 'times' ?> mr-1"></i>
                                                      <?= ($latest_dc['battery_is_charging'] ?? $latest_bs['is_charging'] ?? false) ? 'Charging' : 'Discharging' ?>
                                                  </span>
                                                 <?php if (isset($dc['battery_plugged_usb']) && $dc['battery_plugged_usb']) { ?><span class="badge badge-info ml-1"><i class="fas fa-usb mr-1"></i>USB</span><?php } ?>
                                                 <?php if (!empty($bs['plugged_type'])) { ?><span class="badge badge-info ml-1"><i class="fas fa-usb mr-1"></i><?= esc($bs['plugged_type']) ?></span><?php } ?>
                                                 <?php if (isset($dc['battery_plugged_ac']) && $dc['battery_plugged_ac']) { ?><span class="badge badge-warning ml-1"><i class="fas fa-plug mr-1"></i>AC</span><?php } ?>
                                             </td>
                                            <td><?= $bs['temperature_celsius'] ?? $dc['battery_temperature_celsius'] ?? '—' ?>°C</td>
                                            <td><?= ($dc['battery_voltage_mv'] ?? $bs['voltage_mv'] ?? null) !== null ? number_format($dc['battery_voltage_mv'] ?? $bs['voltage_mv']) : '—' ?> mV</td>
                                            <td><?= $bs['capacity_percent'] ?? '—' ?>%</td>
                                            <td><?= ($bs['current_now_ua'] ?? null) !== null ? number_format($bs['current_now_ua']) . ' μA' : '—' ?></td>
                                            <td>
                                                <small><?= $bs['status'] ?? $dc['battery_health'] ?? '—' ?></small>
                                                <br><small class="text-muted"><?= $bs['health'] ?? '' ?></small>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-danger delete-row"
                                                        data-id="<?= $h['id'] ?? '' ?>"
                                                        data-url="<?= base_url('advanced/hardware/battery_power/delete') ?>"
                                                        title="Delete this snapshot">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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

    <!-- Modal: Full Battery History -->
    <div class="modal fade" id="batteryHistoryModal" tabindex="-1" role="dialog" aria-labelledby="batteryHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="batteryHistoryModalLabel"><i class="fas fa-history mr-2"></i>Full Battery History</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Extracted</th>
                                    <th><i class="fas fa-battery-half text-success mr-1"></i>Level</th>
                                    <th><i class="fas fa-plug text-warning mr-1"></i>Charging</th>
                                    <th><i class="fas fa-thermometer-half text-danger mr-1"></i>Temp</th>
                                    <th><i class="fas fa-bolt text-info mr-1"></i>Voltage</th>
                                    <th><i class="fas fa-battery-full text-warning mr-1"></i>Capacity</th>
                                    <th><i class="fas fa-tachometer-alt text-secondary mr-1"></i>Current</th>
                                    <th><i class="fas fa-info-circle text-muted mr-1"></i>Status / Health</th>
                                    <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history)): ?>
                                <tr><td colspan="9" class="text-center py-5"><div class="empty-state"><i class="fas fa-battery-quarter fa-3x text-muted mb-3"></i><h4>No battery history</h4><p class="text-muted">Data will appear here once extracted</p></div></td></tr>
                                <?php else: foreach ($history as $h): 
                                    $dc = $h['device_context'] ?? [];
                                    $bs = $h['battery_stats'] ?? [];
                                    $lvl = $dc['battery_level_percent'] ?? $bs['level_percent'] ?? 0;
                                    $barCol = $lvl > 50 ? 'success' : ($lvl > 20 ? 'warning' : 'danger');
                                ?>
                                <tr>
                                    <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress mr-2" style="width:60px;height:8px;border-radius:4px;">
                                                <div class="progress-bar bg-<?= $barCol ?>" style="width:<?= $lvl ?>%"></div>
                                            </div>
                                            <span class="font-weight-bold"><?= number_format($lvl, 0) ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= ($latest_dc['battery_is_charging'] ?? $latest_bs['is_charging'] ?? false) ? 'success' : 'secondary' ?>">
                                            <i class="fas fa-<?= ($latest_dc['battery_is_charging'] ?? $latest_bs['is_charging'] ?? false) ? 'bolt' : 'times' ?> mr-1"></i>
                                            <?= ($latest_dc['battery_is_charging'] ?? $latest_bs['is_charging'] ?? false) ? 'Charging' : 'Discharging' ?>
                                        </span>
                                        <?php if (isset($dc['battery_plugged_usb']) && $dc['battery_plugged_usb']) { ?><span class="badge badge-info ml-1"><i class="fas fa-usb mr-1"></i>USB</span><?php } ?>
                                        <?php if (!empty($bs['plugged_type'])) { ?><span class="badge badge-info ml-1"><i class="fas fa-usb mr-1"></i><?= esc($bs['plugged_type']) ?></span><?php } ?>
                                        <?php if (isset($dc['battery_plugged_ac']) && $dc['battery_plugged_ac']) { ?><span class="badge badge-warning ml-1"><i class="fas fa-plug mr-1"></i>AC</span><?php } ?>
                                    </td>
                                    <td><?= $bs['temperature_celsius'] ?? $dc['battery_temperature_celsius'] ?? '—' ?>°C</td>
                                    <td><?= ($dc['battery_voltage_mv'] ?? $bs['voltage_mv'] ?? null) !== null ? number_format($dc['battery_voltage_mv'] ?? $bs['voltage_mv']) : '—' ?> mV</td>
                                    <td><?= $bs['capacity_percent'] ?? '—' ?>%</td>
                                    <td><?= ($bs['current_now_ua'] ?? null) !== null ? number_format($bs['current_now_ua']) . ' μA' : '—' ?></td>
                                    <td>
                                        <small><?= $bs['status'] ?? $dc['battery_health'] ?? '—' ?></small>
                                        <br><small class="text-muted"><?= $bs['health'] ?? '' ?></small>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger delete-row"
                                                data-id="<?= $h['id'] ?? '' ?>"
                                                data-url="<?= base_url('advanced/hardware/battery_power/delete') ?>"
                                                title="Delete this snapshot">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>