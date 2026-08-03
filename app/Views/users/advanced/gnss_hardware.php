<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-satellite text-secondary mr-2"></i>GNSS / GPS</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Constellations, antenna, AGPS, raw measurements</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>GNSS Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="gnssTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-satellite mr-1"></i> GNSS ID</th>
                            <th><i class="fas fa-globe mr-1"></i> Constellations</th>
                            <th><i class="fas fa-antenna mr-1"></i> Antenna</th>
                            <th><i class="fas fa-signal mr-1"></i> Frequencies</th>
                            <th><i class="fas fa-satellite-dish mr-1"></i> Max Sats</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-satellite fa-3x text-muted mb-3"></i><h4>No GNSS data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $rid = $r['id'] ?? 0;
                            $constellations = $r['constellations_supported'] ?? [];
                            $constCount = is_array($constellations) ? count($constellations) : 0;
                            $freqs = $r['frequencies_supported'] ?? [];
                            $freqCount = is_array($freqs) ? count($freqs) : 0;
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#gnss-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td><code><?= esc($r['gnss_id'] ?? '—') ?></code></td>
                                <td><span class="badge badge-secondary"><?= $constCount ?> constellation<?= $constCount !== 1 ? 's' : '' ?></span></td>
                                <td><?= esc($r['antenna_type'] ?? '—') ?></td>
                                <td><span class="badge badge-info"><?= $freqCount ?> freq<?= $freqCount !== 1 ? 's' : '' ?></span></td>
                                <td><?= (int)($r['max_satellites_tracked'] ?? 0) ?> / <?= (int)($r['max_satellites_used'] ?? 0) ?></td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/gnss_hardware/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="8" class="p-0 border-0">
                                    <div id="gnss-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-satellite mr-2"></i>GNSS Specs</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>GNSS ID</th><td><code><?= esc($r['gnss_id'] ?? '—') ?></code></td></tr>
                                                        <tr><th>Constellations</th><td><?= $constCount ?> (<?= is_array($constellations) ? implode(', ', $constellations) : '—' ?>)</td></tr>
                                                        <tr><th>Antenna Type</th><td><?= esc($r['antenna_type'] ?? '—') ?></td></tr>
                                                        <tr><th>Frequencies</th><td><?= $freqCount ?> (<?= is_array($freqs) ? implode(', ', $freqs) : '—' ?>)</td></tr>
                                                        <tr><th>Max Tracked</th><td><?= (int)($r['max_satellites_tracked'] ?? 0) ?></td></tr>
                                                        <tr><th>Max Used</th><td><?= (int)($r['max_satellites_used'] ?? 0) ?></td></tr>
                                                        <tr><th>AGPS</th><td><?= !empty($r['agps_supported']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>AGPS Modes</th><td><?= is_array($r['agps_modes'] ?? []) ? implode(', ', $r['agps_modes']) : '—' ?></td></tr>
                                                        <tr><th>Dead Reckoning</th><td><?= !empty($r['dead_reckoning_supported']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-cogs mr-2"></i>Capabilities</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Raw Measurements</th><td><?= !empty($r['raw_measurements_supported']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Correction Data</th><td><?= !empty($r['correction_data_supported']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Navigation Messages</th><td><?= !empty($r['navigation_messages_supported']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Status Supported</th><td><?= !empty($r['status_supported']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Time Offset</th><td><?= esc($r['time_offset_ns'] ?? '—') ?> ns</td></tr>
                                                        <tr><th>Leap Second</th><td><?= esc($r['leap_second'] ?? '—') ?></td></tr>
                                                        <tr><th>UTC Accuracy</th><td><?= esc($r['utc_time_accuracy_ns'] ?? '—') ?> ns</td></tr>
                                                        <tr><th>GPS Provider</th><td><?= !empty($r['gps_provider_available']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
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