<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-signal text-primary mr-2"></i>Cell Towers</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Neighboring cell towers with CID, LAC, RSSI, and network type</p>
                    <small class="text-muted">
                        <strong>Legend:</strong>
                        <span class="mr-3" title="Received Signal Strength Indicator - measure of signal power (higher/closer to 0 is better)"><abbr title="Received Signal Strength Indicator">RSSI</abbr> &mdash; Signal Strength</span>
                        <span class="mr-3" title="Reference Signal Received Power - LTE signal power/strength (e.g. -90 dBm is better than -120 dBm)"><abbr title="Reference Signal Received Power">RSRP</abbr> &mdash; Reference Signal Power</span>
                        <span class="mr-3" title="Reference Signal Received Quality - LTE signal quality (e.g. -10 dB is better than -20 dB)"><abbr title="Reference Signal Received Quality">RSRQ</abbr> &mdash; Reference Signal Quality</span>
                        <span class="mr-3" title="Channel Quality Indicator - LTE channel quality measure (higher is better, 0-15)"><abbr title="Channel Quality Indicator">CQI</abbr> &mdash; Channel Quality</span>
                    </small>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Cell Tower Snapshots <small class="text-muted ml-2"><?= count($rows) ?></small></h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Type</th>
                                <th>CID / NCI</th>
                                <th>LAC / TAC</th>
                                <th>MCC / MNC</th>
                                <th>PCI</th>
                                <th>RSSI (dBm)</th>
                                <th>RSRP / RSRQ</th>
                                <th>Extracted</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($rows)): foreach ($rows as $r):
                            $towers = $r['towers_json'] ?? '[]';
                            $towersArr = json_decode($towers, true) ?? [];
                            ?>
                            <?php if (!empty($towersArr)): foreach ($towersArr as $tower): ?>
                                <tr>
                                    <td><span class="badge badge-primary"><?= esc($tower['type'] ?? 'N/A') ?></span></td>
                                    <td><?= esc($tower['cid'] ?? $tower['nci'] ?? $tower['base_station_id'] ?? 'N/A') ?></td>
                                    <td><?= esc($tower['lac'] ?? $tower['tac'] ?? 'N/A') ?></td>
                                    <td><?= esc($tower['mcc'] ?? 'N/A') ?> / <?= esc($tower['mnc'] ?? 'N/A') ?></td>
                                    <td><?= esc($tower['pci'] ?? 'N/A') ?></td>
                                    <td><?= esc($tower['rssi'] ?? 'N/A') ?> dBm</td>
                                    <td><?= esc($tower['rsrp'] ?? 'N/A') ?> / <?= esc($tower['rsrq'] ?? 'N/A') ?></td>
                                    <td><?= date('M d, Y H:i', $r['extracted_at'] ?? 0) ?></td>
                                    <td class="text-center">
                                            <button class="btn btn-sm btn-outline-danger delete-row"
                                                data-id="<?= $r['id'] ?? '' ?>"
                                                data-url="<?= base_url('advanced/cell_towers/delete') ?>"
                                                title="Delete this row">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No tower data</td>
                                    <td><?= date('M d, Y H:i', $r['extracted_at'] ?? 0) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger delete-row"
                                                data-id="<?= $r['id'] ?? '' ?>"
                                                data-url="<?= base_url('advanced/cell_towers/delete') ?>"
                                                title="Delete this row">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="float-right">
                    <?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?>
                </div>
            </div>
        </div>
    </div></div></div></section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>