<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-keyboard text-warning mr-2"></i>Input Methods (IMEs)</h1>
                        <span class="badge badge-warning border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Enabled keyboards, subtypes and locale configurations</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-warning shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>IME Snapshots <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-keyboard text-primary mr-1"></i> IME ID</th>
                            <th><i class="fas fa-box text-success mr-1"></i> Package</th>
                            <th><i class="fas fa-tag text-info mr-1"></i> Label</th>
                            <th><i class="fas fa-globe text-secondary mr-1"></i> Subtypes</th>
                            <th><i class="fas fa-clock text-muted mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="6" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-keyboard fa-3x text-muted mb-3"></i><h4>No IME data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                                $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                                $subtypes = $r['subtypes'] ?? [];
                                $isSys = $r['is_system'] ?? 0;
                                $isAux = $r['is_auxiliary'] ?? 0;
                            ?>
                            <tr>
                                                                <td><code><?= htmlspecialchars($r['ime_id'] ?? '—') ?></code></td>
                                <td><code><?= htmlspecialchars($r['package_name'] ?? '—') ?></code></td>
                                <td><?= htmlspecialchars($r['label'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($subtypes)): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($subtypes as $st): ?>
                                                <span class="badge badge-info" style="font-size: 0.65rem;">
                                                    <i class="fas fa-globe mr-1"></i><?= htmlspecialchars($st['locale'] ?? '?') ?>
                                                    <?php if (!empty($st['mode'])): ?><span class="ml-1">[<?= htmlspecialchars($st['mode']) ?>]</span><?php endif; ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/input_methods/delete') ?>"
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