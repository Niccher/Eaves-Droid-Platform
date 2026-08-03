<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-memory text-secondary mr-2"></i>Hardware Graphics</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">GPU/Renderer, Media Codecs, and Input Devices</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="graphicsTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-memory mr-1"></i> GPU / Renderer</th>
                            <th><i class="fas fa-film mr-1"></i> Media Codecs</th>
                            <th><i class="fas fa-keyboard mr-1"></i> Input Devices</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="6" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-memory fa-3x text-muted mb-3"></i><h4>No hardware graphics data</h4><p class="text-muted">Snapshots will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $gpu = $r['gpu_renderer'] ?? [];
                            $codecs = $r['media_codecs'] ?? [];
                            $inputs = $r['input_devices'] ?? [];
                            $rid = $r['id'] ?? 0;
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#graphics-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td class="text-center">
                                    <?php if (!empty($gpu)): ?>
                                        <span class="badge badge-info p-2"><?= esc($gpu['gl_renderer'] ?? 'GPU') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($codecs)): ?>
                                        <button class="btn btn-sm btn-outline-warning details-row"
                                            data-title="Media Codecs (<?= count($codecs) ?>)"
                                            data-data='<?= esc(json_encode($codecs), 'attr') ?>'
                                            title="View media codecs">
                                            <i class="fas fa-film mr-1"></i> <?= count($codecs) ?> codec<?= count($codecs) !== 1 ? 's' : '' ?>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($inputs)): ?>
                                        <button class="btn btn-sm btn-outline-success details-row"
                                            data-title="Input Devices (<?= count($inputs) ?>)"
                                            data-data='<?= esc(json_encode($inputs), 'attr') ?>'
                                            title="View input devices">
                                            <i class="fas fa-keyboard mr-1"></i> <?= count($inputs) ?> device<?= count($inputs) !== 1 ? 's' : '' ?>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/hardware_graphics/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="6" class="p-0 border-0">
                                    <div id="graphics-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-memory mr-2"></i>GPU Renderer</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <?php if (!empty($gpu)): foreach ($gpu as $k => $v): ?>
                                                            <tr><th><?= esc(ucfirst(str_replace('_', ' ', $k))) ?></th><td><pre class="mb-0 small"><?= esc(is_array($v) ? json_encode($v, JSON_PRETTY_PRINT) : $v) ?></pre></td></tr>
                                                        <?php endforeach; else: ?>
                                                            <tr><td colspan="2" class="text-muted">No GPU data</td></tr>
                                                        <?php endif; ?>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="mb-2"><i class="fas fa-film mr-2"></i>Media Codecs <span class="badge badge-warning"><?= count($codecs) ?></span></h6>
                                                    <h6 class="mb-2"><i class="fas fa-keyboard mr-2"></i>Input Devices <span class="badge badge-success"><?= count($inputs) ?></span></h6>
                                                    <div class="text-muted small">Use the buttons in the table above to view full Media Codecs and Input Devices data.</div>
                                                </div>
                                                <div class="col-md-12">
                                                    <h6 class="mt-3"><i class="fas fa-info-circle mr-2"></i>Snapshot Info</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Extracted At</th><td><?= $ts ?></td></tr>
                                                        <tr><th>Entry ID</th><td><code><?= $rid ?></code></td></tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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