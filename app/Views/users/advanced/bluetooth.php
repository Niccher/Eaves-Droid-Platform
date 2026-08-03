<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-bluetooth-b text-secondary mr-2"></i>Bluetooth</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Adapter state, paired devices count</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Bluetooth Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="bluetoothTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-toggle-on mr-1"></i> Enabled</th>
                            <th><i class="fas fa-microchip mr-1"></i> Adapter Name</th>
                            <th><i class="fas fa-exchange-alt mr-1"></i> Address</th>
                            <th><i class="fas fa-link mr-1"></i> Paired</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-bluetooth fa-3x text-muted mb-3"></i><h4>No bluetooth data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $rid = $r['id'] ?? 0;
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#bluetooth-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td>
                                    <?php if (!empty($r['is_enabled'])): ?>
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Enabled</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><i class="fas fa-times mr-1"></i>Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($r['adapter_name'] ?? '—') ?></td>
                                <td><code><?= esc($r['adapter_address'] ?? '—') ?></code></td>
                                <td><span class="badge badge-primary"><?= (int)($r['paired_count'] ?? 0) ?> paired</span></td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/bluetooth/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="7" class="p-0 border-0">
                                    <div id="bluetooth-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-bluetooth-b mr-2"></i>Adapter Details</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Enabled</th><td><?= !empty($r['is_enabled']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Name</th><td><?= esc($r['adapter_name'] ?? '—') ?></td></tr>
                                                        <tr><th>Address</th><td><code><?= esc($r['adapter_address'] ?? '—') ?></code></td></tr>
                                                        <tr><th>Paired Devices</th><td><span class="badge badge-primary"><?= (int)($r['paired_count'] ?? 0) ?></span></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-info-circle mr-2"></i>Snapshot Info</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Extracted At</th><td><?= $ts ?></td></tr>
                                                        <tr><th>Entry ID</th><td><code><?= $rid ?></code></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-12">
                                                    <h6 class="mt-3"><i class="fas fa-broadcast-tower mr-2"></i>Connected / Paired Devices <span class="badge badge-secondary"><?= count($r['paired_devices'] ?? []) ?> device(s)</span></h6>
                                                    <?php $paired = $r['paired_devices'] ?? []; ?>
                                                    <?php if (!empty($paired)): ?>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-striped mb-0 small">
                                                                <thead class="text-muted">
                                                                <tr>
                                                                    <th>Name</th>
                                                                    <th>Address</th>
                                                                    <th>Type</th>
                                                                    <th>Bond State</th>
                                                                    <th>Connection</th>
                                                                    <th>RSSI</th>
                                                                    <th>Last Connected</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php foreach ($paired as $d): ?>
                                                                    <tr>
                                                                        <td class="font-weight-bold"><?= esc($d['bt_name'] ?? '—') ?></td>
                                                                        <td><code><?= esc($d['bt_address'] ?? '—') ?></code></td>
                                                                        <td><span class="badge badge-info"><?= esc($d['bt_type'] ?? '—') ?></span></td>
                                                                        <td><?= esc($d['bond_state'] ?? '—') ?></td>
                                                                        <td>
                                                                            <?php $conn = strtolower((string)($d['connection_state'] ?? '')); ?>
                                                                            <?php if (in_array($conn, ['connected', 'connecting', '1'], true)): ?>
                                                                                <span class="badge badge-success"><i class="fas fa-link mr-1"></i><?= esc($d['connection_state'] ?? 'Connected') ?></span>
                                                                            <?php else: ?>
                                                                                <span class="badge badge-secondary"><?= esc($d['connection_state'] ?? 'Disconnected') ?></span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <td><?= isset($d['rssi']) ? esc($d['rssi']) . ' dBm' : '—' ?></td>
                                                                        <td><?= !empty($d['last_connected_time']) ? format_timestamp_display((int)$d['last_connected_time']) : '—' ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-muted small">No paired devices recorded</div>
                                                    <?php endif; ?>
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