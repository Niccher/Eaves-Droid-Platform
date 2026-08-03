<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-volume-up text-secondary mr-2"></i>Audio Devices</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Audio sinks, sources, sample rates, latency</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Audio Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="audioTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-volume-up mr-1"></i> Device</th>
                            <th><i class="fas fa-tag mr-1"></i> Type</th>
                            <th><i class="fas fa-exchange-alt mr-1"></i> Sink/Source</th>
                            <th><i class="fas fa-wave-square mr-1"></i> Sample Rates</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-volume-up fa-3x text-muted mb-3"></i><h4>No audio data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $rid = $r['id'] ?? 0;
                            $typeMap = ['in' => 'Input', 'out' => 'Output', 'both' => 'Duplex'];
                            $type = $typeMap[$r['device_type'] ?? ''] ?? ($r['device_type'] ?? 'Unknown');
                            $isSink = !empty($r['is_sink']);
                            $isSource = !empty($r['is_source']);
                            $rates = is_array($r['sample_rates'] ?? []) ? implode(', ', $r['sample_rates']) : '—';
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#audio-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td><strong><?= esc($r['product_name'] ?? $r['audio_device_id'] ?? '—') ?></strong></td>
                                <td><span class="badge badge-secondary"><?= esc($type) ?></span></td>
                                <td>
                                    <?php if ($isSink && $isSource): ?>
                                        <span class="badge badge-success">Sink+Source</span>
                                    <?php elseif ($isSink): ?>
                                        <span class="badge badge-info">Sink</span>
                                    <?php elseif ($isSource): ?>
                                        <span class="badge badge-warning">Source</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= esc($rates) ?></code></td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/audio_devices/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="7" class="p-0 border-0">
                                    <div id="audio-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-volume-up mr-2"></i>Device Specs</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Device ID</th><td><code><?= esc($r['audio_device_id'] ?? '—') ?></code></td></tr>
                                                        <tr><th>Type</th><td><span class="badge badge-secondary"><?= esc($type) ?></span></td></tr>
                                                        <tr><th>Product Name</th><td><?= esc($r['product_name'] ?? '—') ?></td></tr>
                                                        <tr><th>Address</th><td><code><?= esc($r['address'] ?? '—') ?></code></td></tr>
                                                        <tr><th>Sink</th><td><?= $isSink ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Source</th><td><?= $isSource ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Sample Rates</th><td><?= esc($rates) ?></td></tr>
                                                        <tr><th>Channel Masks</th><td><?= is_array($r['channel_masks'] ?? []) ? implode(', ', $r['channel_masks']) : '—' ?></td></tr>
                                                        <tr><th>Channel Counts</th><td><?= is_array($r['channel_counts'] ?? []) ? implode(', ', $r['channel_counts']) : '—' ?></td></tr>
                                                        <tr><th>Encoding</th><td><?= esc($r['encoding'] ?? '—') ?></td></tr>
                                                        <tr><th>Format</th><td><?= esc($r['format'] ?? '—') ?></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-cogs mr-2"></i>Advanced</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Gain Range</th><td><?= esc($r['gain_min'] ?? '—') ?> – <?= esc($r['gain_max'] ?? '—') ?> (step: <?= esc($r['gain_step'] ?? '—') ?>)</td></tr>
                                                        <tr><th>Latency</th><td><?= esc($r['latency_low_ms'] ?? '—') ?> – <?= esc($r['latency_high_ms'] ?? '—') ?> ms</td></tr>
                                                        <tr><th>Supported UID</th><td><?= esc($r['supported_uid'] ?? '—') ?></td></tr>
                                                        <tr><th>Volume Handle</th><td><?= esc($r['volume_handle'] ?? '—') ?></td></tr>
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