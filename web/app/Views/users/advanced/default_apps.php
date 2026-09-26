<?php /** @var array $latest @var array $changes_history @var int $total @var string $nav_urls */ ?>

<?php
if ($latest) {
    $fields = ['browser', 'dialer', 'sms', 'launcher', 'email', 'maps', 'music', 'gallery'];
    foreach ($fields as $field) {
        $raw = $latest[$field] ?? [];
        $latest['default_' . $field . '_name'] = $raw['app_name'] ?? $raw['package_name'] ?? '—';
        $latest['default_' . $field . '_package'] = $raw['package_name'] ?? '';
        $latest['default_' . $field . '_is_system'] = $raw['is_system'] ?? false;
    }
}
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
            
            <?php if (!empty($changes_history)): ?>
                <!-- Recent Changes Warning Card -->
                <div class="card card-warning card-outline shadow-sm mb-4">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title text-warning font-weight-bold">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Recent Default App Changes Detected
                        </h3>
                    </div>
                    <div class="card-body py-2 px-3">
                        <p class="text-muted small mb-2">The default intent handlers on the device have been modified from their historical baselines. Review if these modifications were unauthorized:</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" style="font-size: .82rem;">
                                <thead>
                                    <tr>
                                        <th>Date Changed</th>
                                        <th>Default Type</th>
                                        <th>Previous App</th>
                                        <th>New App</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Show only the 3 most recent changes in the warning box
                                    $recentChanges = array_slice($changes_history, 0, 3);
                                    foreach ($recentChanges as $c): 
                                    ?>
                                        <tr>
                                            <td><?= date('M d, Y H:i:s', $c['extracted_at'] / 1000) ?></td>
                                            <td><span class="badge badge-light border"><?= esc(ucfirst($c['handler'])) ?></span></td>
                                            <td><span class="text-muted"><del><?= esc($c['old_app']) ?></del></span></td>
                                            <td><span class="text-danger font-weight-bold"><i class="fas fa-arrow-right mr-1"></i><?= esc($c['new_app']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

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

            <!-- Configuration Change History Card -->
            <div class="card card-secondary card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-history mr-2"></i>Default App Configuration Changes
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0" style="font-size: .85rem;">
                            <thead class="thead-light">
                                <tr>
                                    <th>Change Timestamp</th>
                                    <th>Default Type</th>
                                    <th>Previous Application</th>
                                    <th>New Application</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($changes_history)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                                            No configuration changes detected across all historical snapshots.
                                        </td>
                                    </tr>
                                <?php else: foreach ($changes_history as $c): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-light border p-2">
                                                <i class="fas fa-clock mr-1 text-muted"></i>
                                                <?= date('M d, Y H:i:s', $c['extracted_at'] / 1000) ?>
                                            </span>
                                        </td>
                                        <td><strong><?= esc(ucfirst($c['handler'])) ?></strong></td>
                                        <td><span class="text-muted"><del><?= esc($c['old_app']) ?></del></span></td>
                                        <td><span class="text-success font-weight-bold"><i class="fas fa-arrow-right mr-1"></i><?= esc($c['new_app']) ?></span></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<?php include __DIR__ . '/_adv_delete_script.php'; ?>
