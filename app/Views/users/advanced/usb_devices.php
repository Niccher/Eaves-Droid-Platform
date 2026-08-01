<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-usb text-secondary mr-2"></i>USB Devices</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Connected USB peripherals, descriptors, and transfer statistics</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>USB Devices <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-plug mr-1"></i>Device</th>
                            <th><i class="fas fa-industry mr-1"></i>Manufacturer</th>
                            <th><i class="fas fa-hashtag mr-1"></i>Vendor:Product</th>
                            <th><i class="fas fa-tachometer-alt mr-1"></i>Speed</th>
                            <th><i class="fas fa-charging-station mr-1"></i>Power</th>
                            <th><i class="fas fa-bolt mr-1"></i>Charging</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-usb fa-3x text-muted mb-3"></i><h4>No USB device data</h4><p class="text-muted">Snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($r['product_name'] ?? '—') ?></strong>
                                    <?php if (!empty($r['serial_number'])): ?><br><small class="text-muted">SN: <?= htmlspecialchars($r['serial_number']) ?></small><?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($r['manufacturer_name'] ?? '—') ?></td>
                                <td>
                                    <code class="small"><?= htmlspecialchars($r['vendor_id'] ?? '—') ?>:<?= htmlspecialchars($r['product_id'] ?? '—') ?></code>
                                </td>
                                <td><span class="badge badge-info p-2"><?= htmlspecialchars($r['speed'] ?? '—') ?></span></td>
                                <td>
                                    <?php if (isset($r['power_ma'])): ?><span class="badge badge-warning p-2"><?= (int)$r['power_ma'] ?> mA</span><?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?= !empty($r['is_charging']) ? '<span class="badge badge-success p-2">Yes</span>' : '<span class="badge badge-secondary p-2">No</span>' ?>
                                </td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/hardware/usb_devices/delete') ?>"
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
