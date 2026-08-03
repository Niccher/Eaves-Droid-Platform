<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-battery-three-quarters text-secondary mr-2"></i>Battery Stats</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Detailed battery counters: capacity, charge counter, current, energy, health, temperature</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Battery Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="batteryTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-battery-three-quarters text-secondary mr-1"></i> Capacity (%)</th>
                            <th><i class="fas fa-bolt text-danger mr-1"></i> Charge (µAh)</th>
                            <th><i class="fas fa-tachometer-alt text-info mr-1"></i> Current (µA)</th>
                            <th><i class="fas fa-fire text-danger mr-1"></i> Energy (µWh)</th>
                            <th><i class="fas fa-heartbeat text-danger mr-1"></i> Health</th>
                            <th><i class="fas fa-thermometer-half text-secondary mr-1"></i> Temp (°C)</th>
                            <th><i class="fas fa-plug text-info mr-1"></i> Charging</th>
                            <th><i class="fas fa-clock text-muted mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="10" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-battery-empty fa-3x text-muted mb-3"></i><h4>No battery data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $healthMap = ['GOOD' => 'success', 'OVERHEAT' => 'danger', 'DEAD' => 'dark', 'OVER_VOLTAGE' => 'warning', 'COLD' => 'info', 'FAILURE' => 'dark', 'UNKNOWN' => 'secondary'];
                            $healthClass = $healthMap[$r['health'] ?? ''] ?? 'secondary';
                            $rid = $r['id'] ?? 0;
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#battery-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 mr-2" style="height: 8px;">
                                            <div class="bg-<?= $healthClass === 'success' ? 'success' : 'warning' ?>" role="progressbar" style="width: <?= min(100, max(0, (int)($r['capacity_percent'] ?? 0))) ?>%"></div>
                                        </div>
                                        <span class="font-weight-bold"><?= (int)($r['capacity_percent'] ?? 0) ?>%</span>
                                    </div>
                                </td>
                                <td><?= number_format($r['charge_counter_uah'] ?? 0) ?> µAh</td>
                                <td><?= number_format($r['current_now_ua'] ?? 0) ?> µA</td>
                                <td><?= number_format($r['energy_counter_uwh'] ?? 0) ?> µWh</td>
                                <td><span class="badge badge-<?= $healthClass ?>"><?= htmlspecialchars($r['health'] ?? '—') ?></span></td>
                                <td><?= number_format(($r['temperature_deci_c'] ?? 0) / 10, 1) ?> °C</td>
                                <td>
                                    <?php if (!empty($r['is_charging'])): ?>
                                        <span class="badge badge-success"><i class="fas fa-bolt mr-1"></i>Charging</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Not Charging</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/battery_stats/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="10" class="p-0 border-0">
                                    <div id="battery-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-battery-three-quarters mr-2"></i>Battery Details</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Level (%)</th><td><?= (int)($r['level_percent'] ?? 0) ?>%</td></tr>
                                                        <tr><th>Capacity (%)</th><td><?= (int)($r['capacity_percent'] ?? 0) ?>%</td></tr>
                                                        <tr><th>Charge Counter</th><td><?= number_format($r['charge_counter_uah'] ?? 0) ?> µAh</td></tr>
                                                        <tr><th>Current</th><td><?= number_format($r['current_now_ua'] ?? 0) ?> µA</td></tr>
                                                        <tr><th>Energy</th><td><?= number_format($r['energy_counter_uwh'] ?? 0) ?> µWh</td></tr>
                                                        <tr><th>Health</th><td><span class="badge badge-<?= $healthClass ?>"><?= htmlspecialchars($r['health'] ?? '—') ?></span></td></tr>
                                                        <tr><th>Temperature</th><td><?= number_format(($r['temperature_deci_c'] ?? 0) / 10, 1) ?> °C</td></tr>
                                                        <tr><th>Status</th><td><?= htmlspecialchars($r['status'] ?? '—') ?></td></tr>
                                                        <tr><th>Plugged Type</th><td><?= htmlspecialchars($r['plugged_type'] ?? '—') ?></td></tr>
                                                        <tr><th>Technology</th><td><?= htmlspecialchars($r['technology'] ?? '—') ?></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-info-circle mr-2"></i>Snapshot Info</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Extracted At</th><td><?= $ts ?></td></tr>
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