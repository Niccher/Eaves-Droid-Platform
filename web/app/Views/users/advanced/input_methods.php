<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
foreach ($rows as &$r) {
    $r['ime_unique_key'] = ($r['device_id'] ?? 'default') . '_' . ($r['ime_id'] ?? 'unknown');
}
unset($r);

helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'ime_unique_key',
    ['ime_id', 'package_name', 'label', 'is_system', 'service_name', 'is_auxiliary'],
    ['subtypes']
);
?>

<style>
.ime-card     { border-radius: 8px; background: #fff; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
.ime-hero     { background: linear-gradient(135deg, #6c757d 0%, #5a6268 60%, #495057 100%); border-radius: 8px 8px 0 0; padding: 18px 22px; color: #fff; position: relative; }
.ime-hero::after { content: ''; position: absolute; right: 15px; bottom: 15px; font-family: "Font Awesome 5 Free"; font-weight: 900; content: "\f11c"; font-size: 54px; opacity: 0.05; }
.ime-hero.system-ime { background: linear-gradient(135deg, #5a6268 0%, #474f56 60%, #343a40 100%); }
.ime-kv       { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.ime-kv:last-child { border-bottom: none; }
.ime-kv .ik   { color: #6c757d; font-weight: 600; font-size: 11px; text-transform: uppercase; }
.ime-kv .iv   { font-weight: 700; color: #343a40; text-align: right; max-width: 60%; word-break: break-all; }
.sub-badge    { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; margin: 2px; }
</style>

<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3">
                            <i class="fas fa-keyboard text-primary mr-2"></i>Keyboard Input Methods
                        </h1>
                        <span class="badge badge-secondary border p-2 text-white">
                            <i class="fas fa-database mr-1"></i>Active IMEs: <b><?= count($rows) ?></b>
                        </span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Active keyboards, input method configurations, support languages, and keyguard integration parameters.</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>

            <!-- Upgraded Security Callout -->
            <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #007bff;background:#fdfdfd;border-radius:4px;">
                <h5 class="font-weight-bold text-primary"><i class="fas fa-shield-alt mr-2"></i>Keyboard &amp; IME Integrity Auditing</h5>
                <p class="text-secondary mb-2" style="font-size:14px;">Keyloggers and custom malicious keyboards often masquerade as default system tools. Auditing registered Input Method Editors (IMEs) and verification of signing identities protects credentials and user privacy from key extraction.</p>
                <div class="row" style="font-size:12px;">
                    <div class="col-md-6 border-right">
                        <b class="d-block mb-1">IME Packages:</b>
                        <span class="text-muted">Checks packages registered as input handlers. Non-system/third-party keyboards are flagged for manual security validation.</span>
                    </div>
                    <div class="col-md-6 pl-md-3">
                        <b class="d-block mb-1">Language Subtypes:</b>
                        <span class="text-muted">Exposes active input methods, auxiliary layouts, voice recording plugins, and clipboard handlers inside keyboard bundles.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <?php if (empty($rows)): ?>
                <div class="text-center py-5 bg-white shadow-sm border rounded">
                    <i class="fas fa-keyboard fa-3x text-muted mb-3"></i>
                    <h4 class="text-secondary">No Keyboard Data Found</h4>
                    <p class="text-muted">Input method statistics will appear here once synchronized.</p>
                </div>
            <?php else: ?>

                <div class="row">
                    <?php foreach ($rows as $idx => $r):
                        $isSystem = !empty($r['is_system']);
                        $subtypes = $r['subtypes'] ?? [];
                        if (is_string($subtypes)) {
                            $subtypes = json_decode($subtypes, true) ?: [];
                        }
                    ?>
                        <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                            <div class="ime-card w-100 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Keyboard Header -->
                                    <div class="ime-hero <?= $isSystem ? 'system-ime' : '' ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div style="max-width: 70%;">
                                                <div class="dev-brand" style="text-transform: uppercase; font-size: 9px; opacity: 0.75; letter-spacing: 0.8px;">
                                                    <?= $isSystem ? 'System Keyboard' : 'User Keyboard' ?>
                                                </div>
                                                <div style="font-size: 16px; font-weight: 800; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                                    <?= htmlspecialchars($r['label'] ?? 'Unknown IME') ?>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-light text-dark font-weight-bold px-2 py-1" style="font-size: 10px;">
                                                    <?= $isSystem ? 'SYSTEM' : 'THIRD-PARTY' ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Keyboard Details -->
                                    <div class="card-body p-3">
                                        <div class="ime-kv">
                                            <span class="ik">IME ID</span>
                                            <span class="iv text-muted" style="max-width: 60%; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?= htmlspecialchars($r['ime_id'] ?? '') ?>">
                                                <?= htmlspecialchars($r['ime_id'] ?? '—') ?>
                                            </span>
                                        </div>
                                        <div class="ime-kv">
                                            <span class="ik">Package</span>
                                            <span class="iv text-primary" style="max-width: 60%; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?= htmlspecialchars($r['package_name'] ?? '') ?>">
                                                <?= htmlspecialchars($r['package_name'] ?? '—') ?>
                                            </span>
                                        </div>
                                        <div class="ime-kv">
                                            <span class="ik">Service Name</span>
                                            <span class="iv text-muted" style="max-width: 60%; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?= htmlspecialchars($r['service_name'] ?? '') ?>">
                                                <?= htmlspecialchars($r['service_name'] ?? '—') ?>
                                            </span>
                                        </div>
                                        <div class="ime-kv">
                                            <span class="ik">Auxiliary IME</span>
                                            <span class="iv"><?= !empty($r['is_auxiliary']) ? '<span class="text-warning font-weight-bold">Yes</span>' : '<span class="text-muted">No</span>' ?></span>
                                        </div>

                                        <!-- Subtypes Grid (Badges) -->
                                        <div class="mt-3">
                                            <span class="ik d-block mb-1">Configured Subtypes (<?= count($subtypes) ?>)</span>
                                            <div class="d-flex flex-wrap">
                                                <?php if (empty($subtypes)): ?>
                                                    <small class="text-muted">No language subtypes reported.</small>
                                                <?php else:
                                                    $limit = 3;
                                                    foreach (array_slice($subtypes, 0, $limit) as $st):
                                                        $mode = $st['mode'] ?? 'keyboard';
                                                        $locale = $st['locale'] ?? 'N/A';
                                                        $stName = $st['name'] ?? $locale;
                                                        $badgeColor = match($mode) {
                                                            'voice'     => 'badge-info',
                                                            'keyboard'  => 'badge-primary',
                                                            default     => 'badge-secondary'
                                                        };
                                                        $icon = match($mode) {
                                                            'voice'     => '🎙️',
                                                            'keyboard'  => '⌨️',
                                                            default     => '📝'
                                                        };
                                                ?>
                                                        <span class="badge <?= $badgeColor ?> sub-badge" title="<?= htmlspecialchars($stName) ?>">
                                                            <?= $icon ?> <?= htmlspecialchars($locale) ?> (<?= htmlspecialchars($mode) ?>)
                                                        </span>
                                                    <?php endforeach;
                                                    if (count($subtypes) > $limit): ?>
                                                        <button type="button" class="btn btn-link btn-xs p-0 ml-1 text-primary font-weight-bold align-self-center" style="font-size: 11px;"
                                                                data-toggle="modal" data-target="#subtypesModal-<?= $idx ?>">
                                                            + <?= (count($subtypes) - $limit) ?> More
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Subtypes List Modal -->
                        <?php if (count($subtypes) > 3): ?>
                            <div class="modal fade" id="subtypesModal-<?= $idx ?>" tabindex="-1" role="dialog" aria-labelledby="subtypesModalLabel-<?= $idx ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content" style="border-radius: 8px;">
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title font-weight-bold text-primary" id="subtypesModalLabel-<?= $idx ?>">
                                                <i class="fas fa-keyboard mr-2"></i>Subtypes: <?= htmlspecialchars($r['label']) ?>
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body p-0">
                                            <table class="table table-sm table-striped mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="pl-3">Subtype Name</th>
                                                        <th>Locale</th>
                                                        <th>Mode</th>
                                                        <th>ASCII Capable</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($subtypes as $st): ?>
                                                        <tr>
                                                            <td class="pl-3 font-weight-bold"><?= htmlspecialchars($st['name'] ?? '—') ?></td>
                                                            <td><code><?= htmlspecialchars($st['locale'] ?? '—') ?></code></td>
                                                            <td>
                                                                <span class="badge badge-light border">
                                                                    <?= ($st['mode'] ?? 'keyboard') === 'voice' ? '🎙️ voice' : '⌨️ keyboard' ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?= !empty($st['is_ascii_capable']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

            <!-- System Locale & Timezone Section -->
            <?php if (!empty($locale)): 
                $reg = $locale['locale_region'] ?? [];
            ?>
                <div class="card card-outline card-primary shadow-sm mt-4">
                    <div class="card-header py-2">
                        <h5 class="card-title font-weight-bold mb-0"><i class="fas fa-language text-primary mr-2"></i>System Locale &amp; Active Timezone</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 border-right">
                                <small class="text-muted d-block uppercase font-weight-bold" style="font-size:10px;">Primary Language</small>
                                <span class="font-weight-bold h5 text-dark"><?= esc($reg['language'] ?? '—') ?></span>
                                <small class="text-muted d-block mt-1">Country: <b><?= esc($reg['country'] ?? '—') ?></b></small>
                            </div>
                            <div class="col-md-3 border-right">
                                <small class="text-muted d-block uppercase font-weight-bold" style="font-size:10px;">Display Locale</small>
                                <span class="font-weight-bold h5 text-dark"><?= esc($reg['display_name'] ?? '—') ?></span>
                                <small class="text-muted d-block mt-1">Tag: <code><?= esc($reg['language_tag'] ?? 'en-US') ?></code></small>
                            </div>
                            <div class="col-md-3 border-right">
                                <small class="text-muted d-block uppercase font-weight-bold" style="font-size:10px;">System Timezone</small>
                                <span class="font-weight-bold h5 text-primary"><?= esc($reg['timezone'] ?? '—') ?></span>
                                <small class="text-muted d-block mt-1">Offset: <?= esc($reg['timezone_offset'] ?? '—') ?></small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block uppercase font-weight-bold" style="font-size:10px;">Last Snapshot</small>
                                <span class="font-weight-bold text-secondary"><?= esc($locale['ts_display'] ?? '—') ?></span>
                                <small class="text-muted d-block mt-1">Total Snapshots: <b><?= count($all_locales ?? []) ?></b></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
