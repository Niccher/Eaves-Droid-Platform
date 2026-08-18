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
$latest = !empty($rows) ? $rows[0] : null;
$latestLocale = $latest['locale_region'] ?? [];
$latestFonts = $latest['system_fonts'] ?? [];
?>

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
                        <span class="badge badge-primary border p-2">
                            <i class="fas fa-database mr-1"></i>Total Records: <b><?= (int)$total ?></b>
                        </span>
                    </div>
                    <p class="text-muted mt-1 mb-0">System language, region configs, timezone mappings, text layout, and active font files on the device.</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <?php if ($latest): ?>
                <div class="row">
                    <!-- Column 1: Locale Region Configuration -->
                    <div class="col-md-5">
                        <div class="card card-primary card-outline shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title text-primary font-weight-bold">
                                    <i class="fas fa-sliders-h mr-2"></i>Locale Settings
                                </h3>
                                <div class="card-tools">
                                    <span class="text-muted small">
                                        <i class="fas fa-clock mr-1"></i>
                                        <?= !empty($latest['extracted_at']) ? date('M d, Y H:i', $latest['extracted_at'] / 1000) : 'N/A' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted" style="width:160px;"><i class="fas fa-language mr-2 text-secondary"></i>Language</td>
                                            <td><strong><?= htmlspecialchars($latest['language']) ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted"><i class="fas fa-flag mr-2 text-secondary"></i>Country / Region</td>
                                            <td><strong><?= htmlspecialchars($latest['country']) ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted"><i class="fas fa-clock mr-2 text-secondary"></i>Timezone</td>
                                            <td><code><?= htmlspecialchars($latest['timezone']) ?></code></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted"><i class="fas fa-desktop mr-2 text-secondary"></i>Display Name</td>
                                            <td><?= htmlspecialchars($latest['display_name']) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted"><i class="fas fa-align-left mr-2 text-secondary"></i>Text Direction</td>
                                            <td>
                                                <span class="badge badge-light border">
                                                    <?= htmlspecialchars($latest['text_layout_direction']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted"><i class="fas fa-font mr-2 text-secondary"></i>Font Scale</td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= htmlspecialchars($latest['font_scale']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: System Fonts Scrollable List -->
                    <div class="col-md-7">
                        <div class="card card-success card-outline shadow-sm mb-4">
                            <div class="card-header d-flex align-items-center">
                                <h3 class="card-title text-success font-weight-bold">
                                    <i class="fas fa-font mr-2"></i>System Fonts
                                </h3>
                                <div class="card-tools ml-auto d-flex align-items-center">
                                    <input type="text" id="fontSearchInput" class="form-control form-control-sm mr-2" placeholder="Search fonts..." style="width: 180px;">
                                    <span class="badge badge-success"><?= count($latestFonts) ?> fonts</span>
                                </div>
                            </div>
                            <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                                <?php if (empty($latestFonts)): ?>
                                    <p class="text-muted text-center py-4 mb-0">No active system fonts cataloged.</p>
                                <?php else: ?>
                                    <table class="table table-sm table-striped table-hover mb-0" id="fontsTable">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Font Name</th>
                                                <th>File Path</th>
                                                <th>Weight</th>
                                                <th>Style</th>
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
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($fontName) ?></strong></td>
                                                    <td><code style="font-size:11px;"><?= htmlspecialchars($filePath) ?></code></td>
                                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($fontWeight) ?></span></td>
                                                    <td><span class="badge badge-light border small"><?= htmlspecialchars($fontStyle) ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
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
                                                <h4>No System Locale data</h4>
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
                                        <td><span class="badge badge-info"><?= htmlspecialchars($r['font_scale'] ?? '—') ?></span></td>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('fontSearchInput')?.addEventListener('keyup', function() {
        var kw = this.value.toLowerCase();
        document.querySelectorAll('#fontsTable tbody tr').forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().indexOf(kw) > -1 ? '' : 'none';
        });
    });
});
</script>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
