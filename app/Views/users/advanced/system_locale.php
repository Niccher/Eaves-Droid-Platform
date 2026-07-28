<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-language text-secondary mr-2"></i>System Locale</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Language, Region, Timezone, and Font Configuration</p>
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
                            <th><i class="fas fa-language mr-1"></i>Language</th>
                            <th><i class="fas fa-flag mr-1"></i>Country</th>
                            <th><i class="fas fa-clock mr-1"></i>Timezone</th>
                            <th><i class="fas fa-text-height mr-1"></i>Font Scale</th>
                            <th><i class="fas fa-font mr-1"></i>Font Files</th>
                            <th><i class="fas fa-clock mr-1"></i>Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-language fa-3x text-muted mb-3"></i><h4>No system locale data</h4><p class="text-muted">Snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $locale = is_string($r['locale_region'] ?? null) ? json_decode($r['locale_region'], true) : ($r['locale_region'] ?? []);
                            $fonts = is_string($r['system_fonts'] ?? null) ? json_decode($r['system_fonts'], true) : ($r['system_fonts'] ?? []);
                            $rid = $r['id'] ?? 0;
                            ?>
                            <tr>
                                <td>
                                    <?php if (!empty($locale['language'])): ?>
                                        <span class="badge badge-primary p-2"><i class="fas fa-language mr-1"></i><?= htmlspecialchars($locale['language']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($locale['country'])): ?>
                                        <span class="badge badge-info p-2"><i class="fas fa-flag mr-1"></i><?= htmlspecialchars($locale['country']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($locale['timezone'])): ?>
                                        <code class="small"><?= htmlspecialchars($locale['timezone']) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($locale['font_scale'])): ?>
                                        <span class="badge badge-<?= $locale['font_scale'] > 1.0 ? 'warning' : ($locale['font_scale'] < 1.0 ? 'info' : 'success') ?> p-2">
                                            <?= htmlspecialchars($locale['font_scale']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($fonts['font_files']) && is_array($fonts['font_files'])): ?>
                                        <a href="#" data-toggle="modal" data-target="#modal-fonts-<?= $rid ?>"
                                           class="badge badge-success p-2" title="Click to view font files">
                                            <i class="fas fa-eye mr-1"></i><?= $fonts['font_count'] ?? count($fonts['font_files']) ?> font<?= ($fonts['font_count'] ?? count($fonts['font_files'])) !== 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                            data-id="<?= $rid ?>"
                                            data-url="<?= base_url('advanced/software/system_locale/delete') ?>"
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
    $locale = is_string($r['locale_region'] ?? null) ? json_decode($r['locale_region'], true) : ($r['locale_region'] ?? []);
    $fonts = is_string($r['system_fonts'] ?? null) ? json_decode($r['system_fonts'], true) : ($r['system_fonts'] ?? []);
    $rid = $r['id'] ?? 0;
?>

<!-- System Fonts Modal -->
<div class="modal fade" id="modal-fonts-<?= $rid ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-font mr-2"></i>System Fonts (<?= $fonts['font_count'] ?? count($fonts['font_files'] ?? []) ?>)</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <?php if (!empty($fonts['font_files']) && is_array($fonts['font_files'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Font Name</th>
                                    <th>File Path</th>
                                    <th>Style</th>
                                    <th>Weight</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($fonts['font_files'] as $font): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><strong><?= htmlspecialchars(is_array($font) ? ($font['name'] ?? $font['font_name'] ?? '—') : $font) ?></strong></td>
                                    <td><code class="small"><?= htmlspecialchars(is_array($font) ? ($font['file_path'] ?? $font['path'] ?? '—') : '—') ?></code></td>
                                    <td>
                                        <?php if (is_array($font) && !empty($font['style'])): ?>
                                            <span class="badge badge-info"><?= htmlspecialchars($font['style']) ?></span>
                                        <?php elseif (is_array($font) && !empty($font['font_style'])): ?>
                                            <span class="badge badge-info"><?= htmlspecialchars($font['font_style']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (is_array($font) && isset($font['weight'])): ?>
                                            <span class="badge badge-light text-dark"><?= htmlspecialchars($font['weight']) ?></span>
                                        <?php elseif (is_array($font) && isset($font['font_weight'])): ?>
                                            <span class="badge badge-light text-dark"><?= htmlspecialchars($font['font_weight']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center p-4">No font files data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endforeach; endif; ?>

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
