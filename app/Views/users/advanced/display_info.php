<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-tv text-secondary mr-2"></i>Display Info</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Resolution, DPI, refresh rate, rotation, display metrics</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Display Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="displayTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-expand-arrows-alt mr-1"></i> Resolution</th>
                            <th><i class="fas fa-ruler mr-1"></i> DPI</th>
                            <th><i class="fas fa-sync-alt mr-1"></i> Refresh Rate</th>
                            <th><i class="fas fa-rotate-right mr-1"></i> Rotation</th>
                            <th><i class="fas fa-desktop mr-1"></i> Physical Size</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-tv fa-3x text-muted mb-3"></i><h4>No display data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $rid = $r['id'] ?? 0;
                            $res = ($r['width_px'] ?? 0) . '×' . ($r['height_px'] ?? 0);
                            $dpi = $r['density_dpi'] ?? 0;
                            $refresh = $r['refresh_rate'] ?? 0;
                            $rotationLabels = [0 => '0°', 90 => '90°', 180 => '180°', 270 => '270°'];
                            $rot = $rotationLabels[$r['rotation'] ?? 0] ?? ($r['rotation'] ?? 0) . '°';
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#display-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td><code class="text-monospace"><?= esc($res) ?></code></td>
                                <td><span class="badge badge-secondary"><?= esc($dpi) ?> DPI</span></td>
                                <td><?= esc($refresh) ?> Hz</td>
                                <td><span class="badge badge-secondary"><?= esc($rot) ?></span></td>
                                <td><?= esc(($r['real_width'] ?? 0) . '×' . ($r['real_height'] ?? 0)) ?> mm</td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/display_info/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="8" class="p-0 border-0">
                                    <div id="display-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-tv mr-2"></i>Display Specs</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Resolution</th><td><?= esc($res) ?></td></tr>
                                                        <tr><th>Width / Height</th><td><?= esc($r['width_px'] ?? '—') ?> × <?= esc($r['height_px'] ?? '—') ?> px</td></tr>
                                                        <tr><th>DPI / Density</th><td><?= esc($r['density_dpi'] ?? '—') ?> / <?= esc($r['density'] ?? '—') ?></td></tr>
                                                        <tr><th>XDPI / YDPI</th><td><?= esc($r['xdpi'] ?? '—') ?> / <?= esc($r['ydpi'] ?? '—') ?></td></tr>
                                                        <tr><th>Scaled Density</th><td><?= esc($r['scaled_density'] ?? '—') ?></td></tr>
                                                        <tr><th>Physical Size</th><td><?= esc($r['real_width'] ?? '—') ?> × <?= esc($r['real_height'] ?? '—') ?> mm</td></tr>
                                                        <tr><th>Refresh Rate</th><td><?= esc($r['refresh_rate'] ?? '—') ?> Hz</td></tr>
                                                        <tr><th>Mode</th><td><?= esc($r['mode_width'] ?? '—') ?> × <?= esc($r['mode_height'] ?? '—') ?> @ <?= esc($r['mode_refresh_rate'] ?? '—') ?> Hz</td></tr>
                                                        <tr><th>Rotation</th><td><?= esc($rot) ?></td></tr>
                                                        <tr><th>Usable Area</th><td><?= esc($r['usable_width'] ?? '—') ?> × <?= esc($r['usable_height'] ?? '—') ?> px</td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-info-circle mr-2"></i>Advanced</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <?php $displays = $r['displays'] ?? $r['displays_json'] ?? []; ?>
                                                        <tr>
                                                            <th>Displays JSON</th>
                                                            <td>
                                                                <?php if (!empty($displays)): ?>
                                                                    <button class="btn btn-sm btn-outline-info details-row"
                                                                        data-title="Displays JSON"
                                                                        data-data='<?= esc(json_encode(is_array($displays) ? $displays : [$displays]), 'attr') ?>'
                                                                        title="View displays JSON">
                                                                        <i class="fas fa-eye mr-1"></i> View Displays JSON (<?= is_array($displays) ? count($displays) : 1 ?>)
                                                                    </button>
                                                                <?php else: ?>
                                                                    <span class="text-muted">—</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <tr><th>Screen Layout</th><td><?= esc($r['screen_layout'] ?? '—') ?></td></tr>
                                                        <tr><th>Smallest Width</th><td><?= esc($r['smallest_screen_width_dp'] ?? '—') ?> dp</td></tr>
                                                        <tr><th>UI Mode</th><td><?= esc($r['ui_mode'] ?? '—') ?></td></tr>
                                                    </table>
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