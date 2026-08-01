<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-keyboard text-secondary mr-2"></i>Keyboard Input</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Installed input methods, default keyboard, and IME subtypes</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Input Methods <small class="text-muted ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><i class="fas fa-tag mr-1"></i>Keyboard</th>
                            <th><i class="fas fa-box mr-1"></i>Package</th>
                            <th><i class="fas fa-check-circle mr-1"></i>Enabled</th>
                            <th><i class="fas fa-star mr-1"></i>Default</th>
                            <th><i class="fas fa-flag mr-1"></i>System IME</th>
                            <th><i class="fas fa-list mr-1"></i>Subtypes</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-keyboard fa-3x text-muted mb-3"></i><h4>No keyboard data</h4><p class="text-muted">Input methods will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php $subtypes = $r['subtypes'] ?? []; ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['ime_label'] ?? '—') ?></strong></td>
                                <td><code class="small"><?= htmlspecialchars($r['ime_package'] ?? '—') ?></code></td>
                                <td class="text-center">
                                    <?= !empty($r['is_enabled']) ? '<span class="badge badge-success p-2">Yes</span>' : '<span class="badge badge-secondary p-2">No</span>' ?>
                                </td>
                                <td class="text-center">
                                    <?= !empty($r['is_default']) ? '<span class="badge badge-warning p-2"><i class="fas fa-star mr-1"></i>Default</span>' : '<span class="badge badge-secondary p-2">No</span>' ?>
                                </td>
                                <td class="text-center">
                                    <?= !empty($r['is_system_ime']) ? '<span class="badge badge-info p-2">Yes</span>' : '<span class="badge badge-secondary p-2">No</span>' ?>
                                </td>
                                <td class="text-center"><span class="badge badge-secondary p-2"><?= is_array($subtypes) ? count($subtypes) : 0 ?></span></td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $r['id'] ?? '' ?>"
                                            data-url="<?= base_url('advanced/software/keyboard_input/delete') ?>"
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
