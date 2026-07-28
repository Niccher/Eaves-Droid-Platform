<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-wifi text-success mr-2"></i>Saved WiFi</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Configured/saved WiFi networks with security details</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Saved WiFi Snapshots <small class="text-muted ml-2"><?= count($rows) ?></small></h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>SSID</th>
                                <th>BSSID</th>
                                <th>Security</th>
                                <th>Protocols</th>
                                <th>Auth Algorithms</th>
                                <th>Hidden</th>
                                <th>Extracted</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($rows)): foreach ($rows as $r):
                            $networks = $r['networks_json'] ?? '[]';
                            $networksArr = json_decode($networks, true) ?? [];
                            ?>
                            <?php if (!empty($networksArr)): foreach ($networksArr as $net): ?>
                                <tr>
                                    <td><strong><?= esc($net['ssid'] ?? 'N/A') ?></strong></td>
                                    <td><code class="small"><?= esc($net['bssid'] ?? 'N/A') ?></code></td>
                                    <td><span class="badge badge-warning"><?= esc($net['security'] ?? 'OPEN') ?></span></td>
                                    <td>
                                        <?php 
                                            $protocols = $net['protocols_json'] ?? '[]';
                                            $protocolsArr = json_decode($protocols, true) ?? [];
                                            if (!empty($protocolsArr)): foreach ($protocolsArr as $p): ?>
                                                <span class="badge badge-light text-dark mr-1"><?= esc($p) ?></span>
                                            <?php endforeach; else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $auth = $net['auth_algorithms_json'] ?? '[]';
                                            $authArr = json_decode($auth, true) ?? [];
                                            if (!empty($authArr)): foreach ($authArr as $a): ?>
                                                <span class="badge badge-light text-dark mr-1"><?= esc($a) ?></span>
                                            <?php endforeach; else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?= isset($net['is_hidden']) && $net['is_hidden'] ? '<span class="badge badge-danger">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?>
                                    </td>
                                    <td><?= format_timestamp_display((int)$r['extracted_at']) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger delete-row"
                                                data-id="<?= $r['id'] ?? '' ?>"
                                                data-url="<?= base_url('advanced/saved_wifi/delete') ?>"
                                                title="Delete this row">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No saved networks</td>
                                    <td><?= format_timestamp_display((int)$r['extracted_at']) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger delete-row"
                                                data-id="<?= $r['id'] ?? '' ?>"
                                                data-url="<?= base_url('advanced/saved_wifi/delete') ?>"
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