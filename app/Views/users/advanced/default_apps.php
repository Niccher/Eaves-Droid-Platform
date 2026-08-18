<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
helper('coalesce');
$rows = coalesce_snapshots(
    $rows,
    'device_id',
    ['default_browser_json', 'default_dialer_json', 'default_sms_json', 'default_launcher_json', 'default_email_json', 'default_maps_json', 'default_music_json', 'default_gallery_json']
);

// Process all rows to extract app names and package names from JSON
foreach ($rows as &$row) {
    $fields = ['default_browser_json', 'default_dialer_json', 'default_sms_json', 
               'default_launcher_json', 'default_email_json', 'default_maps_json',
               'default_music_json', 'default_gallery_json'];
    foreach ($fields as $field) {
        $raw = $row[$field] ?? '{}';
        $decoded = is_array($raw) ? $raw : (json_decode($raw, true) ?: []);
        $key = str_replace('_json', '', $field);
        $row[$key . '_name'] = $decoded['app_name'] ?? $decoded['package_name'] ?? '—';
        $row[$key . '_package'] = $decoded['package_name'] ?? '';
        $row[$key . '_is_system'] = $decoded['is_system'] ?? false;
    }
}
$latest = !empty($rows) ? $rows[0] : null;
?>

<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3">
                            <i class="fas fa-tasks text-primary mr-2"></i>Default Apps
                        </h1>
                        <span class="badge badge-primary border p-2">
                            <i class="fas fa-database mr-1"></i>Total: <b><?= (int)$total ?></b> records
                        </span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Default browser, dialer, SMS, launcher, and other default intent handlers set on the device.</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            
            <?php if ($latest): ?>
                <!-- Latest Default Apps Grid -->
                <div class="card card-primary card-outline shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title text-primary font-weight-bold">
                            <i class="fas fa-mobile-alt mr-2"></i>Active Defaults on Device
                        </h3>
                        <div class="card-tools">
                            <span class="text-muted small">
                                <i class="fas fa-clock mr-1"></i>Last Extracted: 
                                <strong>
                                    <?= !empty($latest['extracted_at']) ? date('M d, Y H:i:s', $latest['extracted_at'] / 1000) : 'N/A' ?>
                                </strong>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Card 1: Browser -->
                            <?php $isSys = !empty($latest['default_browser_is_system']); ?>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="info-box bg-light border shadow-sm">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-globe"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text text-muted font-weight-bold">Browser</span>
                                        <span class="info-box-number text-truncate" style="max-width: 100%;" title="<?= htmlspecialchars($latest['default_browser_name']) ?>">
                                            <?= htmlspecialchars($latest['default_browser_name']) ?>
                                        </span>
                                        <small class="text-muted text-truncate d-block" title="<?= htmlspecialchars($latest['default_browser_package']) ?>">
                                            <?= htmlspecialchars($latest['default_browser_package']) ?>
                                        </small>
                                        <?php if ($latest['default_browser_package']): ?>
                                            <span class="badge badge-<?= $isSys ? 'secondary' : 'info' ?> mt-1 align-self-start" style="font-size:10px;">
                                                <?= $isSys ? 'System App' : 'User App' ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: Dialer -->
                            <?php $isSys = !empty($latest['default_dialer_is_system']); ?>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="info-box bg-light border shadow-sm">
                                    <span class="info-box-icon bg-success"><i class="fas fa-phone"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text text-muted font-weight-bold">Dialer</span>
                                        <span class="info-box-number text-truncate" style="max-width: 100%;" title="<?= htmlspecialchars($latest['default_dialer_name']) ?>">
                                            <?= htmlspecialchars($latest['default_dialer_name']) ?>
                                        </span>
                                        <small class="text-muted text-truncate d-block" title="<?= htmlspecialchars($latest['default_dialer_package']) ?>">
                                            <?= htmlspecialchars($latest['default_dialer_package']) ?>
                                        </small>
                                        <?php if ($latest['default_dialer_package']): ?>
                                            <span class="badge badge-<?= $isSys ? 'secondary' : 'info' ?> mt-1 align-self-start" style="font-size:10px;">
                                                <?= $isSys ? 'System App' : 'User App' ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 3: SMS -->
                            <?php $isSys = !empty($latest['default_sms_is_system']); ?>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="info-box bg-light border shadow-sm">
                                    <span class="info-box-icon bg-info"><i class="fas fa-sms"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text text-muted font-weight-bold">SMS Client</span>
                                        <span class="info-box-number text-truncate" style="max-width: 100%;" title="<?= htmlspecialchars($latest['default_sms_name']) ?>">
                                            <?= htmlspecialchars($latest['default_sms_name']) ?>
                                        </span>
                                        <small class="text-muted text-truncate d-block" title="<?= htmlspecialchars($latest['default_sms_package']) ?>">
                                            <?= htmlspecialchars($latest['default_sms_package']) ?>
                                        </small>
                                        <?php if ($latest['default_sms_package']): ?>
                                            <span class="badge badge-<?= $isSys ? 'secondary' : 'info' ?> mt-1 align-self-start" style="font-size:10px;">
                                                <?= $isSys ? 'System App' : 'User App' ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 4: Launcher -->
                            <?php $isSys = !empty($latest['default_launcher_is_system']); ?>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="info-box bg-light border shadow-sm">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-rocket"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text text-muted font-weight-bold">Launcher</span>
                                        <span class="info-box-number text-truncate" style="max-width: 100%;" title="<?= htmlspecialchars($latest['default_launcher_name']) ?>">
                                            <?= htmlspecialchars($latest['default_launcher_name']) ?>
                                        </span>
                                        <small class="text-muted text-truncate d-block" title="<?= htmlspecialchars($latest['default_launcher_package']) ?>">
                                            <?= htmlspecialchars($latest['default_launcher_package']) ?>
                                        </small>
                                        <?php if ($latest['default_launcher_package']): ?>
                                            <span class="badge badge-<?= $isSys ? 'secondary' : 'info' ?> mt-1 align-self-start" style="font-size:10px;">
                                                <?= $isSys ? 'System App' : 'User App' ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Card 5: Email -->
                            <?php $isSys = !empty($latest['default_email_is_system']); ?>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="info-box bg-light border shadow-sm">
                                    <span class="info-box-icon bg-danger"><i class="fas fa-envelope"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text text-muted font-weight-bold">Email</span>
                                        <span class="info-box-number text-truncate" style="max-width: 100%;" title="<?= htmlspecialchars($latest['default_email_name']) ?>">
                                            <?= htmlspecialchars($latest['default_email_name']) ?>
                                        </span>
                                        <small class="text-muted text-truncate d-block" title="<?= htmlspecialchars($latest['default_email_package']) ?>">
                                            <?= htmlspecialchars($latest['default_email_package']) ?>
                                        </small>
                                        <?php if ($latest['default_email_package']): ?>
                                            <span class="badge badge-<?= $isSys ? 'secondary' : 'info' ?> mt-1 align-self-start" style="font-size:10px;">
                                                <?= $isSys ? 'System App' : 'User App' ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 6: Maps -->
                            <?php $isSys = !empty($latest['default_maps_is_system']); ?>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="info-box bg-light border shadow-sm">
                                    <span class="info-box-icon bg-teal"><i class="fas fa-map-marked-alt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text text-muted font-weight-bold">Maps / Navigation</span>
                                        <span class="info-box-number text-truncate" style="max-width: 100%;" title="<?= htmlspecialchars($latest['default_maps_name']) ?>">
                                            <?= htmlspecialchars($latest['default_maps_name']) ?>
                                        </span>
                                        <small class="text-muted text-truncate d-block" title="<?= htmlspecialchars($latest['default_maps_package']) ?>">
                                            <?= htmlspecialchars($latest['default_maps_package']) ?>
                                        </small>
                                        <?php if ($latest['default_maps_package']): ?>
                                            <span class="badge badge-<?= $isSys ? 'secondary' : 'info' ?> mt-1 align-self-start" style="font-size:10px;">
                                                <?= $isSys ? 'System App' : 'User App' ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 7: Music -->
                            <?php $isSys = !empty($latest['default_music_is_system']); ?>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="info-box bg-light border shadow-sm">
                                    <span class="info-box-icon bg-indigo"><i class="fas fa-music"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text text-muted font-weight-bold">Music Player</span>
                                        <span class="info-box-number text-truncate" style="max-width: 100%;" title="<?= htmlspecialchars($latest['default_music_name']) ?>">
                                            <?= htmlspecialchars($latest['default_music_name']) ?>
                                        </span>
                                        <small class="text-muted text-truncate d-block" title="<?= htmlspecialchars($latest['default_music_package']) ?>">
                                            <?= htmlspecialchars($latest['default_music_package']) ?>
                                        </small>
                                        <?php if ($latest['default_music_package']): ?>
                                            <span class="badge badge-<?= $isSys ? 'secondary' : 'info' ?> mt-1 align-self-start" style="font-size:10px;">
                                                <?= $isSys ? 'System App' : 'User App' ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 8: Gallery -->
                            <?php $isSys = !empty($latest['default_gallery_is_system']); ?>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="info-box bg-light border shadow-sm">
                                    <span class="info-box-icon bg-orange"><i class="fas fa-images"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text text-muted font-weight-bold">Gallery / Photos</span>
                                        <span class="info-box-number text-truncate" style="max-width: 100%;" title="<?= htmlspecialchars($latest['default_gallery_name']) ?>">
                                            <?= htmlspecialchars($latest['default_gallery_name']) ?>
                                        </span>
                                        <small class="text-muted text-truncate d-block" title="<?= htmlspecialchars($latest['default_gallery_package']) ?>">
                                            <?= htmlspecialchars($latest['default_gallery_package']) ?>
                                        </small>
                                        <?php if ($latest['default_gallery_package']): ?>
                                            <span class="badge badge-<?= $isSys ? 'secondary' : 'info' ?> mt-1 align-self-start" style="font-size:10px;">
                                                <?= $isSys ? 'System App' : 'User App' ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Extraction History Table Card -->
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
                                    <th>Browser</th>
                                    <th>Dialer</th>
                                    <th>SMS</th>
                                    <th>Launcher</th>
                                    <th>Email</th>
                                    <th>Maps</th>
                                    <th>Music</th>
                                    <th>Gallery</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rows)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <h4>No Default App data</h4>
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
                                        <td><strong><?= htmlspecialchars($r['default_browser_name'] ?? '—') ?></strong></td>
                                        <td><strong><?= htmlspecialchars($r['default_dialer_name'] ?? '—') ?></strong></td>
                                        <td><strong><?= htmlspecialchars($r['default_sms_name'] ?? '—') ?></strong></td>
                                        <td><strong><?= htmlspecialchars($r['default_launcher_name'] ?? '—') ?></strong></td>
                                        <td><strong><?= htmlspecialchars($r['default_email_name'] ?? '—') ?></strong></td>
                                        <td><strong><?= htmlspecialchars($r['default_maps_name'] ?? '—') ?></strong></td>
                                        <td><strong><?= htmlspecialchars($r['default_music_name'] ?? '—') ?></strong></td>
                                        <td><strong><?= htmlspecialchars($r['default_gallery_name'] ?? '—') ?></strong></td>
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

<?php include __DIR__ . '/_adv_delete_script.php'; ?>
