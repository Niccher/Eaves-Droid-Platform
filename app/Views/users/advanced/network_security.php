<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-lock text-secondary mr-2"></i>Network Security</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">DNS Configuration and VPN Status</p>
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
                            <th><i class="fas fa-globe mr-1"></i>DNS Config</th>
                            <th><i class="fas fa-shield-alt mr-1"></i>VPN Config</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="4" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-lock fa-3x text-muted mb-3"></i><h4>No network security data</h4><p class="text-muted">Snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $dns = is_string($r['dns_config'] ?? null) ? json_decode($r['dns_config'], true) : ($r['dns_config'] ?? []);
                            $vpn = is_string($r['vpn_config'] ?? null) ? json_decode($r['vpn_config'], true) : ($r['vpn_config'] ?? []);
                            $rid = $r['id'] ?? 0;
                            ?>
                            <tr>
                                <td class="text-center">
                                    <?php if (!empty($dns)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-dns-<?= $rid ?>"
                                           class="badge badge-info p-2" title="Click to view DNS config">
                                            <i class="fas fa-eye mr-1"></i><?= !empty($dns['servers']) ? count($dns['servers']) : 0 ?> server<?= (!empty($dns['servers']) ? count($dns['servers']) : 0) !== 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($vpn)): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-vpn-<?= $rid ?>"
                                           class="badge badge-<?= !empty($vpn['is_active']) ? 'success' : 'secondary' ?> p-2" title="Click to view VPN config">
                                            <i class="fas fa-eye mr-1"></i><?= !empty($vpn['is_active']) ? 'Active' : 'Inactive' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $rid ?>"
                                            data-url="<?= base_url('advanced/software/network_security/delete') ?>"
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
    $dns = is_string($r['dns_config'] ?? null) ? json_decode($r['dns_config'], true) : ($r['dns_config'] ?? []);
    $vpn = is_string($r['vpn_config'] ?? null) ? json_decode($r['vpn_config'], true) : ($r['vpn_config'] ?? []);
    $rid = $r['id'] ?? 0;
?>

<!-- DNS Config Modal -->
<div class="modal fade" id="modal-dns-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-globe mr-2"></i>DNS Configuration</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <?php if (!empty($dns)): ?>
                    <div class="details-grid">
                        <div class="detail-card info">
                            <div class="detail-icon"><i class="fas fa-server"></i></div>
                            <div class="detail-label">Private DNS Mode</div>
                            <div class="detail-value">
                                <span class="badge badge-<?= ($dns['private_dns_mode'] ?? '') === 'hostname' ? 'success' : 'warning' ?>">
                                    <?= htmlspecialchars($dns['private_dns_mode'] ?? 'Off') ?>
                                </span>
                            </div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-server"></i></div>
                            <div class="detail-label">Private DNS Hostname</div>
                            <div class="detail-value"><code><?= htmlspecialchars($dns['private_dns_hostname'] ?? '—') ?></code></div>
                        </div>
                    </div>
                    <?php if (!empty($dns['servers'])): ?>
                        <h6 class="text-muted mt-3 mb-2"><i class="fas fa-list mr-1"></i>DNS Servers (<?= count($dns['servers']) ?>)</h6>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Server</th>
                                        <th>Interface</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach ($dns['servers'] as $server): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><code class="text-primary"><?= htmlspecialchars(is_array($server) ? ($server['address'] ?? $server['server'] ?? json_encode($server)) : $server) ?></code></td>
                                        <td><?= htmlspecialchars(is_array($server) ? ($server['interface'] ?? '—') : '—') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No DNS configuration data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- VPN Config Modal -->
<div class="modal fade" id="modal-vpn-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-shield-alt mr-2"></i>VPN Configuration</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <?php if (!empty($vpn)): ?>
                    <div class="details-grid">
                        <div class="detail-card <?= !empty($vpn['is_active']) ? 'success' : 'secondary' ?>">
                            <div class="detail-icon"><i class="fas fa-toggle-on"></i></div>
                            <div class="detail-label">Status</div>
                            <div class="detail-value">
                                <span class="badge badge-<?= !empty($vpn['is_active']) ? 'success' : 'secondary' ?> p-2">
                                    <?= !empty($vpn['is_active']) ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-network-wired"></i></div>
                            <div class="detail-label">Interface Name</div>
                            <div class="detail-value"><code><?= htmlspecialchars($vpn['interface_name'] ?? $vpn['interface'] ?? '—') ?></code></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-link"></i></div>
                            <div class="detail-label">VPN Type</div>
                            <div class="detail-value"><code><?= htmlspecialchars($vpn['type'] ?? $vpn['vpn_type'] ?? '—') ?></code></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-server"></i></div>
                            <div class="detail-label">Server Address</div>
                            <div class="detail-value"><code><?= htmlspecialchars($vpn['server_address'] ?? $vpn['server'] ?? '—') ?></code></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-user"></i></div>
                            <div class="detail-label">Username</div>
                            <div class="detail-value"><code><?= htmlspecialchars($vpn['username'] ?? '—') ?></code></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon"><i class="fas fa-lock"></i></div>
                            <div class="detail-label">Encrypted</div>
                            <div class="detail-value">
                                <span class="badge badge-<?= !empty($vpn['is_encrypted']) ? 'success' : 'warning' ?>">
                                    <?= !empty($vpn['is_encrypted']) ? 'Yes' : 'No' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No VPN configuration data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endforeach; endif; ?>

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
