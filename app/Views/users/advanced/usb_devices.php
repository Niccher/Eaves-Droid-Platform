<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-usb text-secondary mr-2"></i>USB Devices</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Connected peripherals, power, speed</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>USB Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="usbTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-usb mr-1"></i> Device</th>
                            <th><i class="fas fa-tag mr-1"></i> Class</th>
                            <th><i class="fas fa-tachometer-alt mr-1"></i> Speed</th>
                            <th><i class="fas fa-plug mr-1"></i> Power (mA)</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-usb fa-3x text-muted mb-3"></i><h4>No USB data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $rid = $r['id'] ?? 0;
                            $speedLabels = [1 => 'Low (1.5 Mbps)', 2 => 'Full (12 Mbps)', 3 => 'High (480 Mbps)', 4 => 'SuperSpeed (5 Gbps)', 5 => 'SuperSpeed+ (10 Gbps)'];
                            $speed = $speedLabels[$r['speed'] ?? 0] ?? ($r['speed'] ?? 'Unknown');
                            $classLabels = [0 => 'Interface', 1 => 'Audio', 2 => 'Comm', 3 => 'HID', 5 => 'Physical', 6 => 'Image', 7 => 'Printer', 8 => 'Mass Storage', 9 => 'Hub', 10 => 'CDC Data', 11 => 'Smart Card', 13 => 'Security', 14 => 'Video', 15 => 'Personal Health', 16 => 'Diagnostic', 224 => 'Wireless', 225 => 'App Specific', 239 => 'Vendor Specific', 255 => 'Reserved'];
                            $devClass = $classLabels[$r['device_class'] ?? 0] ?? ($r['device_class'] ?? 'Unknown');
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#usb-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td>
                                    <strong><?= esc($r['product_name'] ?? $r['usb_device_id'] ?? '—') ?></strong>
                                    <br><small class="text-muted">VID:<?= esc($r['vendor_id'] ?? '—') ?> PID:<?= esc($r['product_id'] ?? '—') ?></small>
                                </td>
                                <td><span class="badge badge-secondary"><?= esc($devClass) ?></span></td>
                                <td><span class="badge badge-info"><?= esc($speed) ?></span></td>
                                <td><?= esc($r['power_ma'] ?? '—') ?> mA</td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/usb_devices/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="7" class="p-0 border-0">
                                    <div id="usb-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-usb mr-2"></i>Device Details</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Device ID</th><td><code><?= esc($r['usb_device_id'] ?? '—') ?></code></td></tr>
                                                        <tr><th>Vendor / Product</th><td>VID:<?= esc($r['vendor_id'] ?? '—') ?> / PID:<?= esc($r['product_id'] ?? '—') ?></td></tr>
                                                        <tr><th>Manufacturer</th><td><?= esc($r['manufacturer_name'] ?? '—') ?></td></tr>
                                                        <tr><th>Product</th><td><?= esc($r['product_name'] ?? '—') ?></td></tr>
                                                        <tr><th>Serial</th><td><?= esc($r['serial_number'] ?? '—') ?></td></tr>
                                                        <tr><th>Class</th><td><span class="badge badge-secondary"><?= esc($devClass) ?></span></td></tr>
                                                        <tr><th>Subclass / Protocol</th><td><?= esc($r['device_subclass'] ?? '—') ?> / <?= esc($r['device_protocol'] ?? '—') ?></td></tr>
                                                        <tr><th>Speed</th><td><span class="badge badge-info"><?= esc($speed) ?></span></td></tr>
                                                        <tr><th>Power</th><td><?= esc($r['power_ma'] ?? '—') ?> mA</td></tr>
                                                        <tr><th>Version</th><td><?= esc($r['version'] ?? '—') ?></td></tr>
                                                        <tr><th>Configurations</th><td><?= (int)($r['configuration_count'] ?? 0) ?></td></tr>
                                                        <tr><th>Interfaces</th><td><?= (int)($r['interface_count'] ?? 0) ?></td></tr>
                                                        <tr><th>Endpoints</th><td><?= (int)($r['endpoint_count'] ?? 0) ?></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-plug mr-2"></i>Capabilities</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Charging</th><td><?= !empty($r['is_charging']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Debug Accessory</th><td><?= !empty($r['is_debug_accessory']) ? '<span class="badge badge-warning">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Audio Accessory</th><td><?= !empty($r['is_audio_accessory']) ? '<span class="badge badge-info">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>MIDI</th><td><?= !empty($r['is_midi']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>ADB</th><td><?= !empty($r['is_adb']) ? '<span class="badge badge-primary">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Has Permission</th><td><?= !empty($r['has_permission']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Connected</th><td><?= esc($r['connected_time'] ?? '—') ?></td></tr>
                                                        <tr><th>Disconnected</th><td><?= esc($r['disconnected_time'] ?? '—') ?></td></tr>
                                                        <tr><th>Bytes Transferred</th><td><?= number_format($r['total_bytes_transferred'] ?? 0) ?></td></tr>
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