<?php /** @var array $latest_storage @var array $latest_usb @var array $history @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-hdd text-info mr-2"></i>Storage & Peripherals</h1>
                        <span class="badge badge-info border p-2 text-white"><i class="fas fa-database mr-1"></i>Snapshots: <b><?= count($history ?? []) ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Storage volumes, usage, and connected USB peripherals</p>
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
                            <ul class="nav nav-tabs card-header-tabs" id="storageTabs" role="tablist">
                                <li class="nav-item"><a class="nav-link active" id="tab-volumes" data-toggle="tab" href="#volumes" role="tab"><i class="fas fa-hdd mr-1"></i>Volumes</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab-usb" data-toggle="tab" href="#usb" role="tab"><i class="fas fa-usb mr-1"></i>USB Devices (<?= count($latest_usb ?? []) ?>)</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="storageTabsContent">
                                <!-- Volumes Tab -->
<div class="tab-pane fade show active" id="volumes" role="tabpanel">
                                     <?php if (!empty($latest_storage)): 
                                         $volumes = json_decode($latest_storage['volumes_json'] ?? '[]', true);
                                         $appCache = json_decode($latest_storage['app_cache_json'] ?? 'null', true);
                                         $appData = json_decode($latest_storage['app_data_json'] ?? 'null', true);
                                     ?>
                                        <!-- Storage Summary Cards -->
                                        <div class="row mb-4">
                                            <?php foreach ($volumes as $vol): 
                                                $info = $vol['info'] ?? [];
                                                $total = $info['total_bytes'] ?? 0;
                                                $avail = $info['available_bytes'] ?? 0;
                                                $used = $info['used_bytes'] ?? ($total - $avail);
                                                $pct = $total > 0 ? round($used / $total * 100) : 0;
                                                $isRemovable = !empty($vol['is_removable']);
                                            ?>
                                                <div class="col-lg-4 col-md-6 mb-3">
                                                    <div class="card card-outline card-<?= $isRemovable ? 'warning' : 'primary' ?> shadow-sm h-100">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <h6 class="mb-0">
                                                                    <i class="fas fa-<?= $isRemovable ? 'sd-card' : 'hdd' ?> mr-1"></i>
                                                                    <?= htmlspecialchars($vol['description'] ?? $vol['path'] ?? 'Volume') ?>
                                                                </h6>
                                                                <span class="badge badge-<?= $isRemovable ? 'warning' : 'secondary' ?>">
                                                                    <?= $isRemovable ? 'Removable' : 'Internal' ?>
                                                                </span>
                                                            </div>
                                                            <div class="h3 mb-1"><?= $pct ?>% used</div>
                                                            <div class="progress mb-2" style="height:8px;">
                                                                <div class="progress-bar bg-<?= $pct > 90 ? 'danger' : ($pct > 70 ? 'warning' : 'success') ?>" style="width:<?= $pct ?>%"></div>
                                                            </div>
                                                            <dl class="row mb-0 small">
                                                                <dt class="col-6">Total</dt><dd class="col-6"><?= format_bytes($total) ?></dd>
                                                                <dt class="col-6">Available</dt><dd class="col-6 text-success"><?= format_bytes($avail) ?></dd>
                                                                <dt class="col-6">Used</dt><dd class="col-6"><?= format_bytes($used) ?></dd>
                                                                <dt class="col-6">Path</dt><dd class="col-6"><code class="small"><?= htmlspecialchars($vol['path'] ?? '—') ?></code></dd>
                                                                <dt class="col-6">State</dt><dd class="col-6"><span class="badge badge-<?= ($vol['state'] ?? '') === 'mounted' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($vol['state'] ?? '—') ?></span></dd>
                                                            </dl>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            
                                            <?php if ($appCache): 
                                                $total = $appCache['total_bytes'] ?? 0;
                                                $avail = $appCache['available_bytes'] ?? 0;
                                                $pct = $total > 0 ? round(($total - $avail) / $total * 100) : 0;
                                            ?>
                                                <div class="col-lg-4 col-md-6 mb-3">
                                                    <div class="card card-outline card-secondary shadow-sm h-100">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <h6 class="mb-0"><i class="fas fa-memory mr-1"></i>App Cache</h6>
                                                                <span class="badge badge-secondary">Virtual</span>
                                                            </div>
                                                            <div class="h3 mb-1"><?= $pct ?>% used</div>
                                                            <div class="progress mb-2" style="height:8px;">
                                                                <div class="progress-bar bg-secondary" style="width:<?= $pct ?>%"></div>
                                                            </div>
                                                            <dl class="row mb-0 small">
                                                                <dt class="col-6">Total</dt><dd class="col-6"><?= format_bytes($total) ?></dd>
                                                                <dt class="col-6">Available</dt><dd class="col-6"><?= format_bytes($avail) ?></dd>
                                                            </dl>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($appData): 
                                                $total = $appData['total_bytes'] ?? 0;
                                                $avail = $appData['available_bytes'] ?? 0;
                                                $pct = $total > 0 ? round(($total - $avail) / $total * 100) : 0;
                                            ?>
                                                <div class="col-lg-4 col-md-6 mb-3">
                                                    <div class="card card-outline card-secondary shadow-sm h-100">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <h6 class="mb-0"><i class="fas fa-database mr-1"></i>App Data</h6>
                                                                <span class="badge badge-secondary">Virtual</span>
                                                            </div>
                                                            <div class="h3 mb-1"><?= $pct ?>% used</div>
                                                            <div class="progress mb-2" style="height:8px;">
                                                                <div class="progress-bar bg-secondary" style="width:<?= $pct ?>%"></div>
                                                            </div>
                                                            <dl class="row mb-0 small">
                                                                <dt class="col-6">Total</dt><dd class="col-6"><?= format_bytes($total) ?></dd>
                                                                <dt class="col-6">Available</dt><dd class="col-6"><?= format_bytes($avail) ?></dd>
                                                            </dl>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No storage data</p>
                                    <?php endif; ?>
                                </div>

                                <!-- USB Tab -->
                                <div class="tab-pane fade" id="usb" role="tabpanel">
                                    <?php if (!empty($latest_usb)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped mb-0">
                                                <thead class="thead-light">
                                                <tr>
                                                    <th>#</th><th>Name</th><th>Vendor / Product</th><th>Class</th>
                                                    <th>Speed</th><th>Manufacturer</th><th>Serial</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $i = 1; foreach ($latest_usb as $usb): ?>
                                                    <tr>
                                                        <td><?= $i++ ?></td>
                                                        <td><strong><?= htmlspecialchars($usb['name'] ?? $usb['product_name'] ?? '—') ?></strong></td>
                                                        <td>
                                                            <code>VID: 0x<?= dechex($usb['vendor_id'] ?? 0) ?></code> / 
                                                            <code>PID: 0x<?= dechex($usb['product_id'] ?? 0) ?></code>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-info"><?= htmlspecialchars($usb['device_class'] ?? '—') ?></span>
                                                            <?php if ($usb['device_subclass']): ?><small class="text-muted ml-1">Sub: <?= $usb['device_subclass'] ?></small><?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-<?= ($usb['speed'] ?? '') === 'USB 3.0' || ($usb['speed'] ?? '') === 'USB 3.1' || ($usb['speed'] ?? '') === 'USB 3.2' ? 'success' : (($usb['speed'] ?? '') === 'USB 2.0' ? 'warning' : 'secondary') ?>">
                                                                <?= htmlspecialchars($usb['speed'] ?? '—') ?>
                                                            </span>
                                                        </td>
                                                        <td><?= htmlspecialchars($usb['manufacturer'] ?? '—') ?></td>
                                                        <td><code><?= htmlspecialchars($usb['serial_number'] ?? '—') ?></code></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">No USB devices connected</p>
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
                            <h3 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Storage & USB History</h3>
                            <div class="card-tools ml-auto"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#storageUsbHistoryModal">Show All (<?= count($history ?? []) ?>)</button><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                        <th><i class="fas fa-hdd mr-1"></i>Volumes</th>
                                        <th><i class="fas fa-usb mr-1"></i>USB Devices</th>
                                        <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($history)): ?>
                                        <tr><td colspan="5" class="text-center py-5"><div class="empty-state"><i class="fas fa-hdd fa-3x text-muted mb-3"></i><h4>No storage/USB history</h4></div></td></tr>
                                    <?php else: foreach (array_slice($history,0,5) as $h): 
                                        $st = $h['storage'] ?? [];
                                        $usb = $h['usb_devices'] ?? [];
                                        $volumes = json_decode($st['volumes_json'] ?? '[]', true);
                                        $totalVolumes = count($volumes);
                                        $mountedVolumes = count(array_filter($volumes, fn($v) => ($v['state'] ?? '') === 'mounted'));
                                        // Calculate total storage
                                        $totalBytes = 0; $availBytes = 0;
                                        foreach ($volumes as $v) { $totalBytes += $v['info']['total_bytes'] ?? 0; $availBytes += $v['info']['available_bytes'] ?? 0; }
                                        $pct = $totalBytes > 0 ? round(($totalBytes - $availBytes) / $totalBytes * 100) : 0;
                                    ?>
                                        <tr>
                                            <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                            <td>
                                                <span class="badge badge-info"><?= $totalVolumes ?> volumes</span>
                                                <br><small class="text-muted"><?= $mountedVolumes ?> mounted</small>
                                                <?php if ($totalBytes): ?>
                                                    <br><span class="badge badge-<?= $pct > 90 ? 'danger' : ($pct > 70 ? 'warning' : 'success') ?>"><?= $pct ?>% used</span>
                                                    <br><small class="text-muted"><?= format_bytes($availBytes) ?> free</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary"><?= count($usb) ?> device<?= count($usb) !== 1 ? 's' : '' ?></span>
                                                <?php if ($usb): ?>
                                                    <br><small class="text-muted">
                                                        <?php $speeds = array_count_values(array_column($usb, 'speed')); ?>
                                                        <?php foreach ($speeds as $speed => $count): ?><span class="badge badge-light text-dark mr-1"><?= $count ?>× <?= htmlspecialchars($speed) ?></span><?php endforeach; ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/storage_peripherals/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
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

    <!-- Modal: Full Storage & USB History -->
    <div class="modal fade" id="storageUsbHistoryModal" tabindex="-1" role="dialog" aria-labelledby="storageUsbHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="storageUsbHistoryModalLabel"><i class="fas fa-history mr-2"></i>Full Storage & USB History</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                    <th><i class="fas fa-hdd mr-1"></i>Volumes</th>
                                    <th><i class="fas fa-usb mr-1"></i>USB Devices</th>
                                    <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history)): ?>
                                <tr><td colspan="5" class="text-center py-5"><div class="empty-state"><i class="fas fa-hdd fa-3x text-muted mb-3"></i><h4>No storage/USB history</h4></div></td></tr>
                                <?php else: foreach ($history as $h):
                                    $st = $h['storage'] ?? [];
                                    $usb = $h['usb_devices'] ?? [];
                                    $volumes = json_decode($st['volumes_json'] ?? '[]', true);
                                    $totalVolumes = count($volumes);
                                    $mountedVolumes = count(array_filter($volumes, fn($v) => ($v['state'] ?? '') === 'mounted'));
                                    $totalBytes = 0; $availBytes = 0;
                                    foreach ($volumes as $v) { $totalBytes += $v['info']['total_bytes'] ?? 0; $availBytes += $v['info']['available_bytes'] ?? 0; }
                                    $pct = $totalBytes > 0 ? round(($totalBytes - $availBytes) / $totalBytes * 100) : 0;
                                ?>
                                <tr>
                                    <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                    <td>
                                        <span class="badge badge-info"><?= $totalVolumes ?> volumes</span>
                                        <br><small class="text-muted"><?= $mountedVolumes ?> mounted</small>
                                        <?php if ($totalBytes): ?>
                                            <br><span class="badge badge-<?= $pct > 90 ? 'danger' : ($pct > 70 ? 'warning' : 'success') ?>"><?= $pct ?>% used</span>
                                            <br><small class="text-muted"><?= format_bytes($availBytes) ?> free</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary"><?= count($usb) ?> device<?= count($usb) !== 1 ? 's' : '' ?></span>
                                        <?php if ($usb): ?>
                                            <br><small class="text-muted">
                                                <?php $speeds = array_count_values(array_column($usb, 'speed')); ?>
                                                <?php foreach ($speeds as $speed => $count): ?><span class="badge badge-light text-dark mr-1"><?= $count ?>× <?= htmlspecialchars($speed) ?></span><?php endforeach; ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/storage_peripherals/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
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