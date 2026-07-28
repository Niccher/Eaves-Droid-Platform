<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-thermometer-half text-danger mr-2"></i>Thermal</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">CPU/GPU temperatures, throttling, and frequency scaling</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Thermal Snapshots <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-tag text-info mr-1"></i> Type</th>
                            <th><i class="fas fa-microchip text-warning mr-1"></i> Zone / CPU</th>
                            <th><i class="fas fa-thermometer-half text-danger mr-1"></i> Temp (°C)</th>
                            <th><i class="fas fa-exclamation-triangle text-warning mr-1"></i> Throttle Count</th>
                            <th><i class="fas fa-tachometer-alt text-primary mr-1"></i> Min/Max Freq (MHz)</th>
                            <th><i class="fas fa-cogs text-success mr-1"></i> Governor</th>
                            <th><i class="fas fa-clock text-muted mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-thermometer-half fa-3x text-muted mb-3"></i><h4>No thermal data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r):
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $zones = $r['thermal_zones_json'] ?? '[]';
                            $throttle = $r['cpu_throttle_json'] ?? '[]';
                            $freqs = $r['cpu_frequencies_json'] ?? '[]';
                            $zonesArr = json_decode($zones, true) ?? [];
                            $throttleArr = json_decode($throttle, true) ?? [];
                            $freqsArr = json_decode($freqs, true) ?? [];
                            ?>
                            <?php if (!empty($zonesArr)): foreach ($zonesArr as $zone): ?>
                                <tr>
                                                                        <td><span class="badge badge-info">Zone</span></td>
                                    <td><?= esc($zone['zone'] ?? 'N/A') ?> (<?= esc($zone['type'] ?? 'unknown') ?>)</td>
                                    <td><?= esc($zone['temp_celsius'] ?? 'N/A') ?>°C</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td><?= $ts ?></td>
                                    <td class="text-center">
                                            <button class="btn btn-sm btn-outline-danger delete-row"
                                                data-id="<?= $r['id'] ?? '' ?>"
                                                data-url="<?= base_url('advanced/thermal/delete') ?>"
                                                title="Delete this row">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                            <?php if (!empty($throttleArr)): foreach ($throttleArr as $th): ?>
                                <tr>
                                                                        <td><span class="badge badge-warning">Throttle</span></td>
                                    <td><?= esc($th['cpu_name'] ?? 'N/A') ?></td>
                                    <td>-</td>
                                    <td><?= esc($th['throttle_count'] ?? 'N/A') ?></td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td><?= $ts ?></td>
                                    <td class="text-center">
                                            <button class="btn btn-sm btn-outline-danger delete-row"
                                                data-id="<?= $r['id'] ?? '' ?>"
                                                data-url="<?= base_url('advanced/thermal/delete') ?>"
                                                title="Delete this row">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                            <?php if (!empty($freqsArr)): foreach ($freqsArr as $freq): ?>
                                <tr>
                                                                        <td><span class="badge badge-primary">Freq</span></td>
                                    <td><?= esc($freq['cpu_name'] ?? 'N/A') ?></td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td><?= esc($freq['scaling_min_freq'] ?? 'N/A') ?> / <?= esc($freq['scaling_max_freq'] ?? 'N/A') ?></td>
                                    <td><?= esc($freq['scaling_governor'] ?? 'N/A') ?></td>
                                    <td><?= $ts ?></td>
                                    <td class="text-center">
                                            <button class="btn btn-sm btn-outline-danger delete-row"
                                                data-id="<?= $r['id'] ?? '' ?>"
                                                data-url="<?= base_url('advanced/thermal/delete') ?>"
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