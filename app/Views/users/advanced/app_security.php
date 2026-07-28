<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-user-shield text-secondary mr-2"></i>App Security</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Device Admin Apps, App Permissions, and Running Services</p>
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
                            <th><i class="fas fa-shield-alt mr-1"></i>Device Admin Apps</th>
                            <th><i class="fas fa-key mr-1"></i>App Permissions Map</th>
                            <th><i class="fas fa-play-circle mr-1"></i>Running Services</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="5" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-user-shield fa-3x text-muted mb-3"></i><h4>No app security data</h4><p class="text-muted">Snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $admins = is_string($r['device_admin_apps'] ?? null) ? json_decode($r['device_admin_apps'], true) : ($r['device_admin_apps'] ?? []);
                            $perms = is_string($r['app_permissions_map'] ?? null) ? json_decode($r['app_permissions_map'], true) : ($r['app_permissions_map'] ?? []);
                            $services = is_string($r['running_services'] ?? null) ? json_decode($r['running_services'], true) : ($r['running_services'] ?? []);
                            $rid = $r['id'] ?? 0;
                            ?>
                            <tr>
                                <td class="text-center">
                                    <?php if (!empty($admins)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-admins-<?= $rid ?>"
                                           class="badge badge-danger p-2" title="Click to view device admin apps">
                                            <i class="fas fa-eye mr-1"></i><?= is_array($admins) ? count($admins) : 1 ?> app<?= (is_array($admins) ? count($admins) : 1) !== 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($perms)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-perms-<?= $rid ?>"
                                           class="badge badge-warning p-2" title="Click to view app permissions">
                                            <i class="fas fa-eye mr-1"></i><?= !empty($perms['apps']) ? count($perms['apps']) : 0 ?> app<?= (!empty($perms['apps']) ? count($perms['apps']) : 0) !== 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($services)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-services-<?= $rid ?>"
                                           class="badge badge-info p-2" title="Click to view running services">
                                            <i class="fas fa-eye mr-1"></i><?= is_array($services) ? count($services) : 1 ?> service<?= (is_array($services) ? count($services) : 1) !== 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $rid ?>"
                                            data-url="<?= base_url('advanced/software/app_security/delete') ?>"
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
    $admins = is_string($r['device_admin_apps'] ?? null) ? json_decode($r['device_admin_apps'], true) : ($r['device_admin_apps'] ?? []);
    $perms = is_string($r['app_permissions_map'] ?? null) ? json_decode($r['app_permissions_map'], true) : ($r['app_permissions_map'] ?? []);
    $services = is_string($r['running_services'] ?? null) ? json_decode($r['running_services'], true) : ($r['running_services'] ?? []);
    $rid = $r['id'] ?? 0;
?>

<!-- Device Admin Apps Modal -->
<div class="modal fade" id="modal-admins-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-shield-alt mr-2"></i>Device Admin Apps (<?= is_array($admins) ? count($admins) : 0 ?>)</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <?php if (!empty($admins) && is_array($admins)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Package Name</th>
                                    <th>App Label</th>
                                    <th>Permission</th>
                                    <th>Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($admins as $admin): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><code><?= htmlspecialchars($admin['package_name'] ?? $admin['package'] ?? '—') ?></code></td>
                                    <td><strong><?= htmlspecialchars($admin['app_label'] ?? $admin['name'] ?? '—') ?></strong></td>
                                    <td>
                                        <?php if (!empty($admin['permission'])): ?>
                                            <span class="badge badge-warning mr-1 mb-1"><?= htmlspecialchars($admin['permission']) ?></span>
                                        <?php elseif (!empty($admin['permissions'])): ?>
                                            <?php foreach ($admin['permissions'] as $perm): ?>
                                                <span class="badge badge-warning mr-1 mb-1"><?= htmlspecialchars($perm) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= !empty($admin['is_active']) ? 'success' : 'secondary' ?>">
                                            <?= !empty($admin['is_active']) ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No device admin apps data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- App Permissions Modal -->
<div class="modal fade" id="modal-perms-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-key mr-2"></i>App Permissions Map</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <?php if (!empty($perms)): ?>
                    <?php if (!empty($perms['top_permissions'])): ?>
                        <h6 class="text-muted mb-2"><i class="fas fa-star mr-1"></i>Top Permissions</h6>
                        <div class="mb-4">
                            <?php foreach ($perms['top_permissions'] as $tp): ?>
                                <span class="badge badge-danger mr-1 mb-1 p-2"><?= htmlspecialchars(is_array($tp) ? ($tp['permission'] ?? $tp['name'] ?? json_encode($tp)) : $tp) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($perms['apps'])): ?>
                        <h6 class="text-muted mb-2"><i class="fas fa-list mr-1"></i>Apps (<?= count($perms['apps']) ?>)</h6>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Package</th>
                                        <th>Permissions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach ($perms['apps'] as $app): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><code><?= htmlspecialchars($app['package_name'] ?? $app['package'] ?? '—') ?></code></td>
                                        <td>
                                            <?php if (!empty($app['permissions'])): ?>
                                                <?php foreach ($app['permissions'] as $p): ?>
                                                    <span class="badge badge-light text-dark mr-1 mb-1"><?= htmlspecialchars($p) ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No app permissions data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Running Services Modal -->
<div class="modal fade" id="modal-services-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-play-circle mr-2"></i>Running Services (<?= is_array($services) ? count($services) : 0 ?>)</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <?php if (!empty($services) && is_array($services)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Package</th>
                                    <th>Process</th>
                                    <th>Service Name</th>
                                    <th>Active Since</th>
                                    <th>Crash Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($services as $svc): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><code><?= htmlspecialchars($svc['package_name'] ?? $svc['package'] ?? '—') ?></code></td>
                                    <td><code><?= htmlspecialchars($svc['process'] ?? '—') ?></code></td>
                                    <td><strong><?= htmlspecialchars($svc['service_name'] ?? $svc['name'] ?? '—') ?></strong></td>
                                    <td><?= htmlspecialchars($svc['active_since'] ?? '—') ?></td>
                                    <td>
                                        <?php
                                        $crashes = $svc['crash_count'] ?? $svc['crashes'] ?? 0;
                                        ?>
                                        <span class="badge badge-<?= $crashes > 0 ? 'danger' : 'success' ?>">
                                            <?= $crashes ?> crash<?= $crashes !== 1 ? 'es' : '' ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No running services data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endforeach; endif; ?>

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
