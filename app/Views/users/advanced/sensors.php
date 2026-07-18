<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-microchip text-secondary mr-2"></i>Sensor Profile</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Hardware sensors enumerated on the device</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>


            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Sensors <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-microchip mr-1"></i>Sensor</th>
                            <th><i class="fas fa-building mr-1"></i>Vendor</th>
                            <th><i class="fas fa-tag mr-1"></i>Type</th>
                            <th><i class="fas fa-expand-arrows-alt mr-1"></i>Max Range</th>
                            <th><i class="fas fa-sliders-h mr-1"></i>Resolution</th>
                            <th><i class="fas fa-bolt mr-1"></i>Power</th>
                            <th><i class="fas fa-code-branch mr-1"></i>Version</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-microchip fa-3x text-muted mb-3"></i><h4>No sensor data</h4><p class="text-muted">Sensor profiles will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                                $sensorIcons = [
                                    1  => 'fas fa-arrows-alt-v text-primary',   // accelerometer
                                    3  => 'fas fa-compass text-success',         // orientation
                                    6  => 'fas fa-arrows-alt text-info',         // gyroscope
                                    11 => 'fas fa-sun text-warning',             // light
                                    5  => 'fas fa-lightbulb text-warning',       // light alt
                                    8  => 'fas fa-tachometer-alt text-danger',   // proximity
                                ];
                                $icon = $sensorIcons[$r['type_id'] ?? 0] ?? 'fas fa-satellite-dish text-muted';
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="adv-avatar mr-2"><i class="<?= $icon ?>"></i></div>
                                        <div>
                                            <div class="font-weight-bold"><?= htmlspecialchars($r['sensor_name'] ?? '—') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><small><?= htmlspecialchars($r['vendor'] ?? '—') ?></small></td>
                                <td>
                                    <span class="badge badge-secondary"><?= htmlspecialchars($r['type_string'] ?? '') ?></span>
                                    <small class="text-muted d-block">ID: <?= $r['type_id'] ?></small>
                                </td>
                                <td><code><?= $r['maximum_range'] ?? '—' ?></code></td>
                                <td><code><?= $r['resolution'] ?? '—' ?></code></td>
                                <td>
                                    <span class="badge badge-<?= ($r['power_ma'] ?? 0) <= 1 ? 'success' : (($r['power_ma'] <= 5) ? 'warning' : 'danger') ?>">
                                        <?= $r['power_ma'] ?? '—' ?> mA
                                    </span>
                                </td>
                                <td><small class="text-muted">v<?= $r['version'] ?? '1' ?></small></td>
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
