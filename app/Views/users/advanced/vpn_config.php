<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-network-wired text-secondary mr-2"></i>VPN Configuration</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Active VPN, protocol, DNS servers, routes, and excluded apps</p>
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
                            <th><i class="fas fa-power-off mr-1"></i>Active</th>
                            <th><i class="fas fa-box mr-1"></i>Interface</th>
                            <th><i class="fas fa-tag mr-1"></i>Protocol</th>
                            <th><i class="fas fa-server mr-1"></i>Server</th>
                            <th><i class="fas fa-lock mr-1"></i>Always On</th>
                            <th><i class="fas fa-list mr-1"></i>DNS</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-network-wired fa-3x text-muted mb-3"></i><h4>No VPN data</h4><p class="text-muted">Snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php $dns = $r['vpn_dns_servers'] ?? []; ?>
                            <tr>
                                <td class="text-center">
                                    <?= !empty($r['vpn_active']) ? '<span class="badge badge-success p-2">Connected</span>' : '<span class="badge badge-secondary p-2">Inactive</span>' ?>
                                </td>
                                <td><code class="small"><?= htmlspecialchars($r['vpn_interface'] ?? '—') ?></code></td>
                                <td><span class="badge badge-info p-2"><?= htmlspecialchars($r['vpn_protocol'] ?? '—') ?></span></td>
                                <td>
                                    <small><?= htmlspecialchars($r['vpn_server'] ?? ($r['vpn_package'] ?? ($r['vpn_label'] ?? '—'))) ?></small>
                                    <?php if (!empty($r['vpn_port'])): ?><br><small class="text-muted">port <?= (int)$r['vpn_port'] ?></small><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?= !empty($r['vpn_is_always_on']) ? '<span class="badge badge-warning p-2">Yes</span>' : '<span class="badge badge-secondary p-2">No</span>' ?>
                                </td>
                                <td>
                                    <?php if (is_array($dns) && !empty($dns)): ?>
                                        <code class="small"><?= htmlspecialchars(implode(', ', $dns)) ?></code>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/software/vpn_config/delete') ?>"
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
