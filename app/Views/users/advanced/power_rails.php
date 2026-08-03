<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-bolt text-secondary mr-2"></i>Power Rails</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Voltage, current, power per rail</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Power Rail Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="powerTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-tag mr-1"></i> Rail Name</th>
                            <th><i class="fas fa-bolt mr-1"></i> Type</th>
                            <th><i class="fas fa-tachometer-alt mr-1"></i> Voltage (mV)</th>
                            <th><i class="fas fa-plug mr-1"></i> Current (mA)</th>
                            <th><i class="fas fa-fire mr-1"></i> Power (mW)</th>
                            <th><i class="fas fa-thermometer-half mr-1"></i> Temp (°C)</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="9" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-bolt fa-3x text-muted mb-3"></i><h4>No power rail data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $rid = $r['id'] ?? 0;
                            $statusColor = !empty($r['is_enabled']) ? 'success' : 'secondary';
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#power-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td><strong><?= esc($r['rail_name'] ?? '—') ?></strong></td>
                                <td><span class="badge badge-secondary"><?= esc($r['rail_type'] ?? '—') ?></span></td>
                                <td><?= esc($r['voltage_mv'] ?? '—') ?> mV</td>
                                <td><?= esc($r['current_ma'] ?? '—') ?> mA</td>
                                <td><?= esc($r['power_mw'] ?? '—') ?> mW</td>
                                <td><?= esc($r['temperature_c'] ?? '—') ?> °C</td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/power_rails/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="9" class="p-0 border-0">
                                    <div id="power-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-bolt mr-2"></i>Rail Details</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Name</th><td><?= esc($r['rail_name'] ?? '—') ?></td></tr>
                                                        <tr><th>Type</th><td><span class="badge badge-secondary"><?= esc($r['rail_type'] ?? '—') ?></span></td></tr>
                                                        <tr><th>Voltage</th><td><?= esc($r['voltage_mv'] ?? '—') ?> mV</td></tr>
                                                        <tr><th>Voltage Range</th><td><?= esc($r['voltage_min_mv'] ?? '—') ?> – <?= esc($r['voltage_max_mv'] ?? '—') ?> mV</td></tr>
                                                        <tr><th>Current</th><td><?= esc($r['current_ma'] ?? '—') ?> mA</td></tr>
                                                        <tr><th>Max Current</th><td><?= esc($r['current_max_ma'] ?? '—') ?> mA</td></tr>
                                                        <tr><th>Power</th><td><?= esc($r['power_mw'] ?? '—') ?> mW</td></tr>
                                                        <tr><th>Temperature</th><td><?= esc($r['temperature_c'] ?? '—') ?> °C</td></tr>
                                                        <tr><th>Enabled</th><td><?= !empty($r['is_enabled']) ? '<span class="badge badge-secondary">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Status</th><td><span class="badge badge-<?= $statusColor ?>"><?= !empty($r['is_enabled']) ? 'Enabled' : 'Disabled' ?></span></td></tr>
                                                        <tr><th>Regulator</th><td><?= esc($r['regulator_type'] ?? '—') ?></td></tr>
                                                        <tr><th>Mode</th><td><?= esc($r['mode'] ?? '—') ?></td></tr>
                                                        <tr><th>Efficiency</th><td><?= esc($r['efficiency_percent'] ?? '—') ?>%</td></tr>
                                                        <tr><th>Consumers</th><td><?= (int)($r['num_consumers'] ?? 0) ?> (<?= is_array($r['consumer_names'] ?? []) ? implode(', ', $r['consumer_names']) : '—' ?>)</td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-cogs mr-2"></i>Advanced</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Remote Sense</th><td><?= !empty($r['remote_sense']) ? '<span class="badge badge-secondary">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Soft Start</th><td><?= esc($r['soft_start_us'] ?? '—') ?> µs</td></tr>
                                                        <tr><th>Ramp Delay</th><td><?= esc($r['ramp_delay_us'] ?? '—') ?> µs</td></tr>
                                                        <tr><th>Constraints</th><td><?= esc($r['constraints'] ?? '—') ?></td></tr>
                                                        <tr><th>Capacity</th><td><?= esc($r['capacity_percent'] ?? '—') ?>%</td></tr>
                                                        <tr><th>Health</th><td><?= esc($r['health'] ?? '—') ?></td></tr>
                                                        <tr><th>Technology</th><td><?= esc($r['technology'] ?? '—') ?></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-12">
                                                    <h6 class="mt-3"><i class="fas fa-info-circle mr-2"></i>Snapshot Info</h6>
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