<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-signal text-secondary mr-2"></i>Mobile Network</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">IMS/VoLTE Status and Data Roaming Configuration</p>
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
                            <th><i class="fas fa-phone-alt mr-1"></i>IMS / VoLTE</th>
                            <th><i class="fas fa-sync-alt mr-1"></i>Data Roaming</th>
                            <th><i class="fas fa-broadcast-tower mr-1"></i>Carrier</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="5" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-signal fa-3x text-muted mb-3"></i><h4>No mobile network data</h4><p class="text-muted">Snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ims = is_string($r['ims_volte'] ?? null) ? json_decode($r['ims_volte'], true) : ($r['ims_volte'] ?? []);
                            $roaming = is_string($r['data_roaming'] ?? null) ? json_decode($r['data_roaming'], true) : ($r['data_roaming'] ?? []);
                            $rid = $r['id'] ?? 0;
                            ?>
                            <tr>
                                <td class="text-center">
                                    <?php if (!empty($ims)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-ims-<?= $rid ?>"
                                           class="badge badge-<?= !empty($ims['volte_available']) ? 'success' : 'secondary' ?> p-2" title="Click to view IMS/VoLTE">
                                            <i class="fas fa-eye mr-1"></i><?= !empty($ims['volte_available']) ? 'VoLTE Active' : 'VoLTE Inactive' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($roaming)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-roaming-<?= $rid ?>"
                                           class="badge badge-<?= !empty($roaming['data_roaming_enabled']) ? 'success' : 'warning' ?> p-2" title="Click to view data roaming">
                                            <i class="fas fa-eye mr-1"></i><?= !empty($roaming['data_roaming_enabled']) ? 'Enabled' : 'Disabled' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($ims['carrier_name'])): ?>
                                        <span class="badge badge-primary p-2"><?= htmlspecialchars($ims['carrier_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $rid ?>"
                                            data-url="<?= base_url('advanced/software/telephony_network/delete') ?>"
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

<?php if (!empty($rows)): foreach ($rows as $r):
    $ims = is_string($r['ims_volte'] ?? null) ? json_decode($r['ims_volte'], true) : ($r['ims_volte'] ?? []);
    $roaming = is_string($r['data_roaming'] ?? null) ? json_decode($r['data_roaming'], true) : ($r['data_roaming'] ?? []);
    $rid = $r['id'] ?? 0;
?>

<!-- IMS / VoLTE Modal -->
<div class="modal fade" id="modal-ims-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-phone-alt mr-2"></i>IMS / VoLTE Information</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <?php if (!empty($ims)): ?>
                    <div class="details-grid">
                        <div class="detail-card <?= !empty($ims['volte_available']) ? 'success' : 'secondary' ?>">
                            <div class="detail-icon"><i class="fas fa-phone-alt"></i></div>
                            <div class="detail-label">VoLTE Available</div>
                            <div class="detail-value">
                                <span class="badge badge-<?= !empty($ims['volte_available']) ? 'success' : 'secondary' ?> p-2">
                                    <?= !empty($ims['volte_available']) ? 'Available' : 'Not Available' ?>
                                </span>
                            </div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-broadcast-tower"></i></div>
                            <div class="detail-label">Carrier Name</div>
                            <div class="detail-value"><code><?= htmlspecialchars($ims['carrier_name'] ?? '—') ?></code></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-network-wired"></i></div>
                            <div class="detail-label">Data Network Type</div>
                            <div class="detail-value"><span class="badge badge-info"><?= htmlspecialchars($ims['data_network_type'] ?? '—') ?></span></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-sim-card"></i></div>
                            <div class="detail-label">MCC / MNC</div>
                            <div class="detail-value"><code><?= htmlspecialchars($ims['mcc'] ?? '—') ?> / <?= htmlspecialchars($ims['mnc'] ?? '—') ?></code></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-id-card"></i></div>
                            <div class="detail-label">IMSI</div>
                            <div class="detail-value"><code><?= htmlspecialchars($ims['imsi'] ?? '—') ?></code></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-phone"></i></div>
                            <div class="detail-label">Phone Number</div>
                            <div class="detail-value"><code><?= htmlspecialchars($ims['phone_number'] ?? '—') ?></code></div>
                        </div>
                        <div class="detail-card info">
                            <div class="detail-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="detail-label">VoWiFi</div>
                            <div class="detail-value">
                                <span class="badge badge-<?= !empty($ims['vowifi_available']) ? 'success' : 'secondary' ?>">
                                    <?= !empty($ims['vowifi_available']) ? 'Available' : 'Not Available' ?>
                                </span>
                            </div>
                        </div>
                        <div class="detail-card info">
                            <div class="detail-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="detail-label">VoNR</div>
                            <div class="detail-value">
                                <span class="badge badge-<?= !empty($ims['vonr_available']) ? 'success' : 'secondary' ?>">
                                    <?= !empty($ims['vonr_available']) ? 'Available' : 'Not Available' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No IMS/VoLTE data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Data Roaming Modal -->
<div class="modal fade" id="modal-roaming-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-sync-alt mr-2"></i>Data Roaming Configuration</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <?php if (!empty($roaming)): ?>
                    <div class="details-grid">
                        <div class="detail-card <?= !empty($roaming['data_roaming_enabled']) ? 'success' : 'secondary' ?>">
                            <div class="detail-icon"><i class="fas fa-sync-alt"></i></div>
                            <div class="detail-label">Data Roaming</div>
                            <div class="detail-value">
                                <span class="badge badge-<?= !empty($roaming['data_roaming_enabled']) ? 'success' : 'secondary' ?> p-2">
                                    <?= !empty($roaming['data_roaming_enabled']) ? 'Enabled' : 'Disabled' ?>
                                </span>
                            </div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-wifi"></i></div>
                            <div class="detail-label">Data State</div>
                            <div class="detail-value">
                                <span class="badge badge-info p-2"><?= htmlspecialchars($roaming['data_state'] ?? '—') ?></span>
                            </div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-plane"></i></div>
                            <div class="detail-label">Airplane Mode</div>
                            <div class="detail-value">
                                <span class="badge badge-<?= !empty($roaming['airplane_mode']) ? 'danger' : 'success' ?>">
                                    <?= !empty($roaming['airplane_mode']) ? 'On' : 'Off' ?>
                                </span>
                            </div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-signal"></i></div>
                            <div class="detail-label">Signal Strength</div>
                            <div class="detail-value">
                                <?= isset($roaming['signal_strength']) ? $roaming['signal_strength'] . ' dBm' : '—' ?>
                            </div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-exchange-alt"></i></div>
                            <div class="detail-label">Data Activity</div>
                            <div class="detail-value">
                                <span class="badge badge-info"><?= htmlspecialchars($roaming['data_activity'] ?? '—') ?></span>
                            </div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-phone"></i></div>
                            <div class="detail-label">Network Operator</div>
                            <div class="detail-value"><code><?= htmlspecialchars($roaming['network_operator'] ?? $roaming['operator'] ?? '—') ?></code></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-globe"></i></div>
                            <div class="detail-label">Network Country</div>
                            <div class="detail-value"><code><?= htmlspecialchars($roaming['network_country'] ?? '—') ?></code></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-sim-card"></i></div>
                            <div class="detail-label">SIM Operator</div>
                            <div class="detail-value"><code><?= htmlspecialchars($roaming['sim_operator'] ?? '—') ?></code></div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No data roaming data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endforeach; endif; ?>

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
