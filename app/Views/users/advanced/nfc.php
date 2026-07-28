<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-mobile-alt text-purple mr-2"></i>NFC</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">NFC adapter state, Secure NFC, and supported features</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>NFC Snapshots <small class="text-muted ml-2"><?= count($rows) ?></small></h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Available</th>
                                <th>Enabled</th>
                                <th>Supported</th>
                                <th>Secure NFC</th>
                                <th>Features</th>
                                <th>Extracted</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($rows)): foreach ($rows as $r):
                            $features = $r['features_json'] ?? '[]';
                            $featuresArr = json_decode($features, true) ?? [];
                            ?>
                            <tr>
                                <td class="text-center">
                                    <?= isset($r['nfc_available']) && $r['nfc_available'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>' ?>
                                </td>
                                <td class="text-center">
                                    <?= isset($r['nfc_enabled']) && $r['nfc_enabled'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?>
                                </td>
                                <td class="text-center">
                                    <?= isset($r['nfc_supported']) && $r['nfc_supported'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>' ?>
                                </td>
                                <td class="text-center">
                                    <?= isset($r['nfc_secure_nfc']) && $r['nfc_secure_nfc'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?>
                                </td>
                                <td>
                                    <?php if (!empty($featuresArr)): ?>
                                        <span class="badge badge-info"><?= count($featuresArr) ?> features</span>
                                        <small class="text-muted ml-2"><?= esc(implode(', ', $featuresArr)) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">None</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y H:i', $r['extracted_at'] ?? 0) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/nfc/delete') ?>"
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