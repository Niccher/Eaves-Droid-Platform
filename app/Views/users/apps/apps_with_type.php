<?php
/**
 * Apps List View with Type Filtering
 *
 * @var array $apps_dump
 * @var string $apps_head
 * @var string $apps_urls
 * @var int $totalApps
 * @var int $systemAppsCount
 * @var int $userAppsCount
 * @var int $recentAppsCount
 * @var int $disabledAppsCount
 * @var array $paginationData
 */
?>

<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-mobile-alt mr-2"></i>
                        <?= esc($apps_head ?? 'Apps') ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('apps') ?>">Apps</a></li>
                        <li class="breadcrumb-item active"><?= esc($apps_head ?? 'All Apps') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Navigation Buttons -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="btn-group" role="group">
                        <?= $apps_urls ?? '' ?>
                    </div>
                    <div class="float-right">
                        <span class="badge badge-info">
                            <i class="fas fa-layer-group"></i> Total: <?= $totalApps ?? 0 ?>
                        </span>
                        <span class="badge badge-secondary ml-1">
                            <i class="fas fa-microchip"></i> System: <?= $systemAppsCount ?? 0 ?>
                        </span>
                        <span class="badge badge-success ml-1">
                            <i class="fas fa-user"></i> User: <?= $userAppsCount ?? 0 ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Apps Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                Apps List
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                    <tr>
                                        <th width="30%">App Name</th>
                                        <th width="25%">Package Name</th>
                                        <th width="15%">Version</th>
                                        <th width="10%">Permissions</th>
                                        <th width="10%">Size</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($apps_dump)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="empty-state">
                                                    <i class="fas fa-mobile-alt fa-3x text-muted mb-3"></i>
                                                    <h4>No apps found</h4>
                                                    <p class="text-muted">
                                                        <?php if (isset($apps_head) && strpos($apps_head, 'System') !== false): ?>
                                                            No system apps found
                                                        <?php elseif (isset($apps_head) && strpos($apps_head, 'User') !== false): ?>
                                                            No user-installed apps found
                                                        <?php else: ?>
                                                            No apps available
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($apps_dump as $app): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($app['app_icon'])): ?>
                                                            <img src="data:image/png;base64,<?= esc($app['app_icon']) ?>"
                                                                 alt="<?= esc($app['Name'] ?? 'App Icon') ?>"
                                                                 class="img-circle mr-2" width="32" height="32">
                                                        <?php else: ?>
                                                            <div class="avatar-circle bg-primary text-white mr-2 d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
                                                                <i class="fas fa-mobile-alt"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <strong><?= esc($app['Name'] ?? 'Unknown') ?></strong>
                                                            <?php if (isset($app['is_system_app']) && $app['is_system_app']): ?>
                                                                <span class="badge badge-secondary badge-sm ml-1">System</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-success badge-sm ml-1">User</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <code class="text-muted" title="Package Name">
                                                        <?= esc($app['Package'] ?? 'N/A') ?>
                                                    </code>
                                                </td>
                                                <td>
                                                    <?php if (!empty($app['version_name'])): ?>
                                                        <span class="badge badge-info">
                                                                <?= esc($app['version_name']) ?>
                                                            </span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($app['Code'])): ?>
                                                        <small class="text-muted ml-1">(<?= esc($app['Code']) ?>)</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-shield-alt mr-1"></i>
                                                            <?= $app['permission_count'] ?? 0 ?>
                                                        </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $size = $app['app_size'] ?? 0;
                                                    $sizeMB = round($size / (1024 * 1024), 2);
                                                    $sizeColor = $sizeMB > 100 ? 'danger' : ($sizeMB > 50 ? 'warning' : 'success');
                                                    ?>
                                                    <span class="badge badge-<?= $sizeColor ?>">
                                                            <?= $sizeMB ?> MB
                                                        </span>
                                                </td>
                                                <td>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary view-app-details"
                                                            data-app='<?= htmlspecialchars(json_encode($app), ENT_QUOTES, 'UTF-8') ?>'>
                                                        <i class="fas fa-eye"></i> Details
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Card Footer with Pagination -->
                        <div class="card-footer clearfix">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="dataTables_info" role="status">
                                        Showing <?php echo (($currentPage - 1) * 50) + 1 ?>
                                        to <?php echo min($currentPage * 50, $totalApps ?? 0) ?>
                                        of <?php echo $totalApps ?? 0 ?> entries
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        <?php if (isset($pager) && $totalApps > 20): ?>
                                            <?= $pager->links('default', 'bootstrap5_full') ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- App Details Modal -->
<div class="modal fade" id="appDetailsModal" tabindex="-1" role="dialog" aria-labelledby="appDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="appDetailsModalLabel">
                    <i class="fas fa-info-circle mr-2"></i>
                    App Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div id="appIconContainer" class="mb-3">
                            <img id="appIcon" src="" alt="App Icon" class="img-fluid rounded-circle" style="max-width: 100px;">
                        </div>
                        <h5 id="appName" class="font-weight-bold"></h5>
                        <p id="appPackage" class="text-muted small"></p>
                        <div id="appBadges" class="mt-2"></div>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <!-- Basic Info -->
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2">
                                    <i class="fas fa-info-circle text-primary mr-1"></i>
                                    Basic Information
                                </h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><i class="fas fa-code text-muted mr-1"></i> Version Name:</td>
                                        <td><span id="appVersionName" class="font-weight-bold"></span></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-hashtag text-muted mr-1"></i> Version Code:</td>
                                        <td><span id="appVersionCode" class="badge badge-info"></span></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-weight text-muted mr-1"></i> App Size:</td>
                                        <td><span id="appSize" class="badge"></span></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-shield-alt text-muted mr-1"></i> Permissions:</td>
                                        <td><span id="appPermissions" class="badge badge-warning"></span></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- SDK Info -->
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2">
                                    <i class="fas fa-cogs text-primary mr-1"></i>
                                    SDK Information
                                </h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><i class="fas fa-bullseye text-muted mr-1"></i> Target SDK:</td>
                                        <td><span id="appTargetSdk" class="badge badge-dark"></span></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-arrow-down text-muted mr-1"></i> Min SDK:</td>
                                        <td><span id="appMinSdk" class="badge badge-secondary"></span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Installation Times -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2">
                                    <i class="fas fa-clock text-primary mr-1"></i>
                                    Installation Timeline
                                </h6>
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <i class="fas fa-download bg-success"></i>
                                        <div class="timeline-item-content">
                                            <span class="font-weight-bold">First Installed:</span>
                                            <span id="appFirstInstall" class="text-muted ml-2"></span>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <i class="fas fa-sync-alt bg-info"></i>
                                        <div class="timeline-item-content">
                                            <span class="font-weight-bold">Last Updated:</span>
                                            <span id="appLastUpdate" class="text-muted ml-2"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Permissions List -->
                        <div class="row mt-3" id="permissionsSection" style="display: none;">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2">
                                    <i class="fas fa-list text-primary mr-1"></i>
                                    Permission List
                                </h6>
                                <div id="appPermissionsList" class="permissions-list"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .badge-sm {
        font-size: 0.7em;
        padding: 2px 6px;
    }
    .permissions-list {
        max-height: 200px;
        overflow-y: auto;
        background: #f8f9fa;
        border-radius: 5px;
        padding: 10px;
    }
    .permissions-list .permission-item {
        padding: 3px 0;
        border-bottom: 1px solid #eee;
    }
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 15px;
    }
    .timeline-item i {
        position: absolute;
        left: -30px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    .timeline-item-content {
        padding-left: 10px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // App Details Modal Handler
        const viewButtons = document.querySelectorAll('.view-app-details');
        const modal = new bootstrap.Modal(document.getElementById('appDetailsModal'));

        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const appData = JSON.parse(this.getAttribute('data-app'));
                populateAppModal(appData);
                modal.show();
            });
        });

        function populateAppModal(app) {
            // Set basic info
            document.getElementById('appName').textContent = app.Name || 'Unknown';
            document.getElementById('appPackage').textContent = app.Package || 'N/A';
            document.getElementById('appVersionName').textContent = app.version_name || 'N/A';
            document.getElementById('appVersionCode').textContent = app.Code || 'N/A';

            // Set app icon
            const appIcon = document.getElementById('appIcon');
            if (app.app_icon) {
                appIcon.src = 'data:image/png;base64,' + app.app_icon;
                appIcon.style.display = 'block';
            } else {
                appIcon.style.display = 'none';
                document.getElementById('appIconContainer').innerHTML =
                    '<div class="avatar-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:100px;height:100px;">' +
                    '<i class="fas fa-mobile-alt fa-2x"></i>' +
                    '</div>';
            }

            // Set app badges
            const badgesContainer = document.getElementById('appBadges');
            badgesContainer.innerHTML = '';

            if (app.is_system_app) {
                badgesContainer.innerHTML += '<span class="badge badge-secondary mr-1">System App</span>';
            } else {
                badgesContainer.innerHTML += '<span class="badge badge-success mr-1">User App</span>';
            }

            // Set app size with color coding
            const size = app.app_size || 0;
            const sizeMB = (size / (1024 * 1024)).toFixed(2);
            const sizeColor = sizeMB > 100 ? 'danger' : (sizeMB > 50 ? 'warning' : 'success');
            document.getElementById('appSize').textContent = sizeMB + ' MB';
            document.getElementById('appSize').className = 'badge badge-' + sizeColor;

            // Set permissions
            const permCount = app.permission_count || 0;
            document.getElementById('appPermissions').textContent = permCount + ' permissions';

            // Set SDK info
            document.getElementById('appTargetSdk').textContent = app.target_sdk || 'N/A';
            document.getElementById('appMinSdk').textContent = app.min_sdk || 'N/A';

            // Set installation times
            document.getElementById('appFirstInstall').textContent = formatTimestamp(app.first_install_time);
            document.getElementById('appLastUpdate').textContent = formatTimestamp(app.last_update_time);

            // Set permissions list if available
            const permissionsSection = document.getElementById('permissionsSection');
            const permissionsList = document.getElementById('appPermissionsList');

            if (app.permissions && permCount > 0) {
                try {
                    const permissions = JSON.parse(app.permissions);
                    if (Array.isArray(permissions) && permissions.length > 0) {
                        permissionsList.innerHTML = '';
                        permissions.forEach(perm => {
                            const permItem = document.createElement('div');
                            permItem.className = 'permission-item';
                            permItem.innerHTML = `<i class="fas fa-check text-success mr-2"></i> ${perm}`;
                            permissionsList.appendChild(permItem);
                        });
                        permissionsSection.style.display = 'block';
                    } else {
                        permissionsSection.style.display = 'none';
                    }
                } catch (e) {
                    permissionsSection.style.display = 'none';
                }
            } else {
                permissionsSection.style.display = 'none';
            }
        }

        function formatTimestamp(timestamp) {
            if (!timestamp) return 'N/A';

            // Convert milliseconds to Date
            const date = new Date(parseInt(timestamp));

            // Format date
            const now = new Date();
            const diffMs = now - date;
            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

            if (diffDays === 0) {
                return 'Today at ' + date.toLocaleTimeString();
            } else if (diffDays === 1) {
                return 'Yesterday at ' + date.toLocaleTimeString();
            } else if (diffDays < 7) {
                return diffDays + ' days ago';
            } else {
                return date.toLocaleDateString() + ' at ' + date.toLocaleTimeString();
            }
        }
    });
</script>