<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-universal-access text-info mr-2"></i>Accessibility Services</h1>
                        <span class="badge badge-info border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Enabled accessibility services and their capabilities</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-info shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Accessibility Snapshots <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-hashtag text-muted mr-1"></i> ID</th>
                            <th><i class="fas fa-cogs text-primary mr-1"></i> Service ID</th>
                            <th><i class="fas fa-box text-success mr-1"></i> Package</th>
                            <th><i class="fas fa-comment-dots text-warning mr-1"></i> Description</th>
                            <th><i class="fas fa-layer-group text-secondary mr-1"></i> Capabilities</th>
                            <th><i class="fas fa-flag text-danger mr-1"></i> Flags</th>
                            <th><i class="fas fa-clock text-muted mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-universal-access fa-3x text-muted mb-3"></i><h4>No accessibility data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                                $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                                $canRetrieve = $r['can_retrieve_window_content'] ?? 0;
                            ?>
                            <tr>
                                <td><code><?= $r['id'] ?? '—' ?></code></td>
                                <td><code><?= htmlspecialchars($r['service_id'] ?? '—') ?></code></td>
                                <td><code><?= htmlspecialchars($r['package_name'] ?? '—') ?></code></td>
                                <td><?= htmlspecialchars($r['description'] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge-secondary" data-toggle="tooltip" title="<?= htmlspecialchars($r['capabilities'] ?? '') ?>">
                                        <i class="fas fa-layer-group mr-1"></i><?= htmlspecialchars($r['capabilities'] ?? '—') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $r['flags'] ? 'warning' : 'light' ?>">
                                        <i class="fas fa-<?= $r['flags'] ? 'flag' : 'times' ?> mr-1"></i><?= $r['flags'] ? 'Has Flags' : 'None' ?>
                                    </span>
                                </td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/accessibility/delete') ?>"
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