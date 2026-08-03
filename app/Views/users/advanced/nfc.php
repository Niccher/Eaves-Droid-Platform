<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-credit-card text-secondary mr-2"></i>NFC</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">NFC availability, secure element, features</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>NFC Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="nfcTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-check-circle mr-1"></i> Available</th>
                            <th><i class="fas fa-toggle-on mr-1"></i> Enabled</th>
                            <th><i class="fas fa-shield-alt mr-1"></i> Secure</th>
                            <th><i class="fas fa-cogs mr-1"></i> Features</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-credit-card fa-3x text-muted mb-3"></i><h4>No NFC data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $rid = $r['id'] ?? 0;
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#nfc-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td><?= !empty($r['nfc_available']) ? '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Yes</span>' : '<span class="badge badge-secondary"><i class="fas fa-times mr-1"></i>No</span>' ?></td>
                                <td><?= !empty($r['nfc_enabled']) ? '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Yes</span>' : '<span class="badge badge-secondary"><i class="fas fa-times mr-1"></i>No</span>' ?></td>
                                <td><?= !empty($r['nfc_secure_nfc']) ? '<span class="badge badge-warning"><i class="fas fa-shield-alt mr-1"></i>Yes</span>' : '<span class="badge badge-secondary"><i class="fas fa-times mr-1"></i>No</span>' ?></td>
                                <td>
                                    <?php
                                    $features = $r['features'] ?? $r['features_json'] ?? [];
                                    $featureCount = is_array($features) ? count($features) : 0;
                                    echo $featureCount ? '<span class="badge badge-secondary">' . $featureCount . ' features</span>' : '<span class="text-muted">—</span>';
                                    ?>
                                </td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/nfc/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="7" class="p-0 border-0">
                                    <div id="nfc-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-credit-card mr-2"></i>NFC Status</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Available</th><td><?= !empty($r['nfc_available']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Enabled</th><td><?= !empty($r['nfc_enabled']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Supported</th><td><?= !empty($r['nfc_supported']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Secure NFC</th><td><?= !empty($r['nfc_secure_nfc']) ? '<span class="badge badge-warning">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Secure Supported</th><td><?= !empty($r['nfc_secure_supported']) ? '<span class="badge badge-warning">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-cogs mr-2"></i>Features</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <?php $features = $r['features'] ?? $r['features_json'] ?? null; ?>
                                                        <?php if (!empty($features)): ?>
                                                            <tr><td colspan="2"><pre class="mb-0 small"><?= esc(is_array($features) ? json_encode($features, JSON_PRETTY_PRINT) : $features) ?></pre></td></tr>
                                                        <?php else: ?>
                                                            <tr><td colspan="2" class="text-muted">No features data</td></tr>
                                                        <?php endif; ?>
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