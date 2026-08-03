<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-broadcast-tower text-secondary mr-2"></i>Cell Towers</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">GSM/LTE/NR towers, signal strength, operator info</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Cell Tower Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="cellTowersTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-satellite mr-1"></i> Type</th>
                            <th><i class="fas fa-signal mr-1"></i> Signal</th>
                            <th><i class="fas fa-building mr-1"></i> Operator</th>
                            <th><i class="fas fa-map-marker-alt mr-1"></i> Cell ID</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-broadcast-tower fa-3x text-muted mb-3"></i><h4>No cell tower data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $rid = $r['id'] ?? 0;
                            $typeLabels = ['GSM' => 'GSM', 'LTE' => 'LTE', 'NR' => '5G NR', 'CDMA' => 'CDMA'];
                            $type = $typeLabels[$r['tower_type'] ?? ''] ?? ($r['tower_type'] ?? 'Unknown');
                            $rsrp = $r['rsrp'] ?? null;
                            $rsrq = $r['rsrq'] ?? null;
                            $rssnr = $r['rssnr'] ?? null;
                            $signalColor = 'secondary';
                            if ($rsrp !== null) {
                                if ($rsrp > -80) $signalColor = 'success';
                                elseif ($rsrp > -90) $signalColor = 'warning';
                                elseif ($rsrp > -100) $signalColor = 'info';
                                else $signalColor = 'danger';
                            }
                            $cid = $r['cid'] ?? $r['nci'] ?? '—';
                            $operator = $r['network_operator_name'] ?? $r['network_operator'] ?? '—';
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#cell-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td><span class="badge badge-secondary"><?= esc($type) ?></span></td>
                                <td>
                                    <?php if ($rsrp !== null): ?>
                                        <span class="badge badge-<?= $signalColor ?>"><?= esc($rsrp) ?> dBm</span>
                                        <?php if ($rsrq !== null): ?><br><small>RSRQ: <?= esc($rsrq) ?> dB</small><?php endif; ?>
                                        <?php if ($rssnr !== null): ?><br><small>RSSNR: <?= esc($rssnr) ?> dB</small><?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($operator) ?></td>
                                <td><code><?= esc($cid) ?></code></td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/cell_towers/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="7" class="p-0 border-0">
                                    <div id="cell-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-broadcast-tower mr-2"></i>Cell Details</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Type</th><td><span class="badge badge-secondary"><?= esc($type) ?></span></td></tr>
                                                        <tr><th>Operator</th><td><?= esc($operator) ?></td></tr>
                                                        <tr><th>MCC / MNC</th><td><?= esc($r['mcc'] ?? '—') ?> / <?= esc($r['mnc'] ?? '—') ?></td></tr>
                                                        <tr><th>Cell ID / NCI</th><td><code><?= esc($cid) ?></code></td></tr>
                                                        <tr><th>LAC / TAC</th><td><?= esc($r['lac'] ?? '—') ?> / <?= esc($r['tac'] ?? '—') ?></td></tr>
                                                        <tr><th>PCI</th><td><?= esc($r['pci'] ?? '—') ?></td></tr>
                                                        <tr><th>Registered</th><td><?= !empty($r['is_registered']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Phone Type</th><td><?= esc($r['phone_type'] ?? '—') ?></td></tr>
                                                        <tr><th>SIM State</th><td><?= esc($r['sim_state'] ?? '—') ?></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-signal mr-2"></i>Signal Metrics</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <?php if ($rsrp !== null): ?><tr><th>RSRP</th><td><?= esc($rsrp) ?> dBm</td></tr><?php endif; ?>
                                                        <?php if (($r['rsrq'] ?? null) !== null): ?><tr><th>RSRQ</th><td><?= esc($r['rsrq']) ?> dB</td></tr><?php endif; ?>
                                                        <?php if (($r['rssnr'] ?? null) !== null): ?><tr><th>RSSNR</th><td><?= esc($r['rssnr']) ?> dB</td></tr><?php endif; ?>
                                                        <?php if (($r['cqi'] ?? null) !== null): ?><tr><th>CQI</th><td><?= esc($r['cqi']) ?></td></tr><?php endif; ?>
                                                        <?php if (($r['asu_level'] ?? null) !== null): ?><tr><th>ASU Level</th><td><?= esc($r['asu_level']) ?></td></tr><?php endif; ?>
                                                        <?php if (($r['psc'] ?? null) !== null): ?><tr><th>PSC</th><td><?= esc($r['psc']) ?></td></tr><?php endif; ?>
                                                        <?php if (($r['csi_rsrp'] ?? null) !== null): ?><tr><th>CSI-RSRP</th><td><?= esc($r['csi_rsrp']) ?> dBm</td></tr><?php endif; ?>
                                                        <?php if (($r['csi_rsrq'] ?? null) !== null): ?><tr><th>CSI-RSRQ</th><td><?= esc($r['csi_rsrq']) ?> dB</td></tr><?php endif; ?>
                                                        <?php if (($r['csi_sinr'] ?? null) !== null): ?><tr><th>CSI-SINR</th><td><?= esc($r['csi_sinr']) ?> dB</td></tr><?php endif; ?>
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