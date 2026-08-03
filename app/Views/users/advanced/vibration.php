<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-wave-square text-secondary mr-2"></i>Vibration</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i> Total: <b><?= $total ?? 0 ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Actuator type, amplitude, frequency</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>
    <section class="content"><div class="container-fluid"><div class="row"><div class="col-12">
        <div class="card card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Vibration Snapshots <small class="text-white ml-2"><?= count($rows) ?> entries</small></h3>
                <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 table-sortable" id="vibrationTable">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th><i class="fas fa-check-circle mr-1"></i> Has Vibrator</th>
                            <th><i class="fas fa-sliders-h mr-1"></i> Amplitude Ctrl</th>
                            <th><i class="fas fa-wave-square mr-1"></i> Frequency Ctrl</th>
                            <th><i class="fas fa-tachometer-alt mr-1"></i> Max Amp</th>
                            <th><i class="fas fa-wave-square mr-1"></i> Resonant Freq</th>
                            <th><i class="fas fa-clock mr-1"></i> Extracted</th>
                            <th class="text-center"><i class="fas fa-cogs mr-1"></i> Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-wave-square fa-3x text-muted mb-3"></i><h4>No vibration data</h4><p class="text-muted">Data will appear here once extracted</p></div>
                            </td></tr>
                        <?php else: foreach ($rows as $r): ?>
                            <?php
                            $ts = !empty($r['extracted_at']) ? format_timestamp_display((int)$r['extracted_at']) : '—';
                            $rid = $r['id'] ?? 0;
                            ?>
                            <tr class="accordion-toggle expandable-row" data-target="#vibration-details-<?= $rid ?>">
                                <td class="text-center"><i class="fas fa-chevron-down text-muted chevron-icon"></i></td>
                                <td><?= !empty($r['has_vibrator']) ? '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Yes</span>' : '<span class="badge badge-secondary"><i class="fas fa-times mr-1"></i>No</span>' ?></td>
                                <td><?= !empty($r['supports_amplitude_control']) ? '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Yes</span>' : '<span class="badge badge-secondary"><i class="fas fa-times mr-1"></i>No</span>' ?></td>
                                <td><?= !empty($r['supports_frequency_control']) ? '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Yes</span>' : '<span class="badge badge-secondary"><i class="fas fa-times mr-1"></i>No</span>' ?></td>
                                <td><?= esc($r['max_amplitude'] ?? '—') ?></td>
                                <td><?= esc($r['resonant_frequency_hz'] ?? '—') ?> Hz</td>
                                <td><?= $ts ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger delete-row"
                                        data-id="<?= $rid ?>"
                                        data-url="<?= base_url('advanced/hardware/vibration/delete') ?>"
                                        title="Delete this row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="expandable-content" style="display:none;">
                                <td colspan="8" class="p-0 border-0">
                                    <div id="vibration-details-<?= $rid ?>">
                                        <div class="card card-body bg-light border-0 m-0 p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-wave-square mr-2"></i>Actuator Details</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Has Vibrator</th><td><?= !empty($r['has_vibrator']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Amplitude Control</th><td><?= !empty($r['supports_amplitude_control']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Frequency Control</th><td><?= !empty($r['supports_frequency_control']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Actuator ID</th><td><?= esc($r['actuator_id'] ?? '—') ?></td></tr>
                                                        <tr><th>Max Amplitude</th><td><?= esc($r['max_amplitude'] ?? '—') ?></td></tr>
                                                        <tr><th>Resonant Frequency</th><td><?= esc($r['resonant_frequency_hz'] ?? '—') ?> Hz</td></tr>
                                                        <tr><th>Q Factor</th><td><?= esc($r['q_factor'] ?? '—') ?></td></tr>
                                                        <tr><th>Actuator Type</th><td><?= esc($r['actuator_type'] ?? '—') ?></td></tr>
                                                        <tr><th>Frequency Range</th><td><?= is_array($r['frequency_range_hz'] ?? []) ? implode(' – ', $r['frequency_range_hz']) . ' Hz' : '—' ?></td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-cogs mr-2"></i>Capabilities</h6>
                                                    <table class="table table-sm table-borderless mb-0 small">
                                                        <tr><th>Primitives</th><td><pre class="mb-0 small"><?= esc(json_encode($r['primitives'] ?? [], JSON_PRETTY_PRINT)) ?></pre></td></tr>
                                                        <tr><th>Composite Primitives</th><td><pre class="mb-0 small"><?= esc(json_encode($r['composite_primitives'] ?? [], JSON_PRETTY_PRINT)) ?></pre></td></tr>
                                                        <tr><th>External Control</th><td><?= !empty($r['supports_external_control']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Braking</th><td><?= !empty($r['braking_supported']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Envelope</th><td><?= !empty($r['envelope_supported']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>PWM</th><td><?= !empty($r['pwm_supported']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                        <tr><th>Waveform</th><td><?= !empty($r['waveform_supported']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
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