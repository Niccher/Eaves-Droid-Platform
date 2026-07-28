<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-chart-line text-info mr-2"></i>Data Usage</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Per-network mobile/WiFi data usage (current month totals)</p>
                    <small class="text-muted">
                        <strong>Legend:</strong>
                        <span class="mr-3" title="Received bytes — data downloaded from the network"><abbr title="Received">RX</abbr> &mdash; Downloaded</span>
                        <span class="mr-3" title="Transmitted bytes — data uploaded to the network"><abbr title="Transmitted">TX</abbr> &mdash; Uploaded</span>
                        <span class="mr-3" title="Total bytes — RX + TX combined"><abbr title="Total">Total</abbr> &mdash; Combined</span>
                        <span class="mr-3" title="Unique identifier for each record in the database">Sub ID &mdash; Record ID</span>
                    </small>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Usage Records <small class="text-muted ml-2"><?= count($rows) ?></small></h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Network</th>
                                <th>Sub ID</th>
                                <th>RX</th>
                                <th>TX</th>
                                <th>Total</th>
                                <th>Period</th>
                                <th>Extracted</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($rows)): foreach ($rows as $r):
                            $records = $r['usage_records_json'] ?? '[]';
                            $recordsArr = json_decode($records, true) ?? [];
                            $totals = $r['totals_json'] ?? '[]';
                            $totalsArr = json_decode($totals, true) ?? [];
                            ?>
                            <?php if (!empty($recordsArr)): foreach ($recordsArr as $rec): ?>
                                <tr>
                                    <td><span class="badge badge-<?= ($rec['is_wifi'] ?? 0) ? 'info' : 'primary' ?>"><?= esc($rec['network_type'] ?? 'N/A') ?></span></td>
                                    <td><?= esc($rec['sub_id'] ?? 'N/A') ?></td>
                                    <td><?= esc($rec['rx_formatted'] ?? ($rec['rx_bytes'] ? number_format($rec['rx_bytes']/1024/1024, 2) . ' MB' : 'N/A')) ?></td>
                                    <td><?= esc($rec['tx_formatted'] ?? ($rec['tx_bytes'] ? number_format($rec['tx_bytes']/1024/1024, 2) . ' MB' : 'N/A')) ?></td>
                                    <td><?= esc($rec['total_bytes'] ? number_format($rec['total_bytes']/1024/1024, 2) . ' MB' : 'N/A') ?></td>
                                    <td><?= date('M d', $rec['bucket_start'] ?? 0) ?> - <?= date('M d', $rec['bucket_end'] ?? 0) ?></td>
                                    <td><?= format_timestamp_display((int)$r['extracted_at']) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger delete-row"
                                                data-id="<?= $r['id'] ?? '' ?>"
                                                data-url="<?= base_url('advanced/data_usage/delete') ?>"
                                                title="Delete this row">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                            <?php if (!empty($totalsArr)): ?>
                                <tr class="table-active">
                                    <td colspan="2"><strong>Totals (<?= !empty($r['extracted_at']) ? date('M', (int)($r['extracted_at'] / 1000)) : '' ?>)</strong></td>
                                    <td><strong><?= esc($totalsArr['total_rx_formatted'] ?? 'N/A') ?></strong></td>
                                    <td><strong><?= esc($totalsArr['total_tx_formatted'] ?? 'N/A') ?></strong></td>
                                    <td><strong><?= esc(number_format(($totalsArr['total_rx'] ?? 0) + ($totalsArr['total_tx'] ?? 0), 2)) ?> MB</strong></td>
                                    <td></td>
                                    <td><?= format_timestamp_display((int)$r['extracted_at']) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger delete-row"
                                                data-id="<?= $r['id'] ?? '' ?>"
                                                data-url="<?= base_url('advanced/data_usage/delete') ?>"
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
            <div class="card-footer"><div class="float-right"><?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?></div></div>
        </div>
    </div></div></div></section>
</div>
<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>