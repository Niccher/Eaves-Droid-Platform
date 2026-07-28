<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-hdd text-secondary mr-2"></i>Storage</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Internal/external storage volumes, capacity, and usage</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Storage Snapshots <small class="text-muted ml-2"><?= count($rows) ?></small></h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Volume</th>
                                <th>Total</th>
                                <th>Available</th>
                                <th>Used</th>
                                <th>Removable</th>
                                <th>State</th>
                                <th>Extracted</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($rows)): foreach ($rows as $r):
                            $volumes = json_decode($r['volumes_json'] ?? '[]', true) ?? [];
                            $appCache = json_decode($r['app_cache_json'] ?? 'null', true);
                            $appData = json_decode($r['app_data_json'] ?? 'null', true);
                        ?>
                            <?php if (!empty($volumes)): foreach ($volumes as $vol):
                                $info = $vol['info'] ?? [];
                            ?>
                            <tr>
                                <td>
                                    <?= esc($vol['path'] ?? 'N/A') ?>
                                    <?php if (!empty($vol['description'])): ?>
                                        <br><small class="text-muted"><?= esc($vol['description']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($info['total_formatted'] ?? ($info['total_bytes'] ? number_format($info['total_bytes']/1024/1024/1024, 2) . ' GB' : 'N/A')) ?></td>
                                <td><?= esc($info['available_formatted'] ?? ($info['available_bytes'] ? number_format($info['available_bytes']/1024/1024/1024, 2) . ' GB' : 'N/A')) ?></td>
                                <td><?= esc($info['used_formatted'] ?? ($info['used_bytes'] ? number_format($info['used_bytes']/1024/1024/1024, 2) . ' GB' : 'N/A')) ?></td>
                                <td class="text-center"><?= isset($vol['is_removable']) && $vol['is_removable'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td>
                                <td><?= esc($vol['state'] ?? 'N/A') ?></td>
                                <td><?= date('M d, Y H:i', $r['extracted_at'] ?? 0) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/storage/delete') ?>"
                                            title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                            <?php if ($appCache): ?>
                            <tr>
                                <td><span class="badge badge-info">App Cache</span></td>
                                <td><?= esc($appCache['total_formatted'] ?? number_format($appCache['total_bytes']/1024/1024/1024, 2) . ' GB') ?></td>
                                <td><?= esc($appCache['available_formatted'] ?? number_format($appCache['available_bytes']/1024/1024/1024, 2) . ' GB') ?></td>
                                <td><?= esc($appCache['used_formatted'] ?? number_format($appCache['used_bytes']/1024/1024/1024, 2) . ' GB') ?></td>
                                <td class="text-center">-</td>
                                <td>-</td>
                                <td><?= date('M d, Y H:i', $r['extracted_at'] ?? 0) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/storage/delete') ?>"
                                            title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($appData): ?>
                            <tr>
                                <td><span class="badge badge-warning">App Data</span></td>
                                <td><?= esc($appData['total_formatted'] ?? number_format($appData['total_bytes']/1024/1024/1024, 2) . ' GB') ?></td>
                                <td><?= esc($appData['available_formatted'] ?? number_format($appData['available_bytes']/1024/1024/1024, 2) . ' GB') ?></td>
                                <td><?= esc($appData['used_formatted'] ?? number_format($appData['used_bytes']/1024/1024/1024, 2) . ' GB') ?></td>
                                <td class="text-center">-</td>
                                <td>-</td>
                                <td><?= date('M d, Y H:i', $r['extracted_at'] ?? 0) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/storage/delete') ?>"
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
