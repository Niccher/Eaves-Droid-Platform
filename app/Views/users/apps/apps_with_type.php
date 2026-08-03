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
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3">
                            <i class="fas fa-mobile-alt text-primary mr-2"></i>
                            <?= esc($apps_head ?? 'Apps') ?>
                        </h1>
                        <div class="d-flex align-items-center mt-2 mt-sm-0">
                            <span class="badge badge-light border p-2 mr-2">
                                <i class="fas fa-boxes text-primary mr-1"></i>
                                Total: <b><?php echo $totalApps ?? 0 ?></b>
                            </span>
                            <span class="badge badge-light border p-2 mr-2">
                                <i class="fas fa-shield-alt text-info mr-1"></i>
                                System: <b><?php echo $systemAppsCount ?? 0 ?></b>
                            </span>
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-user text-success mr-1"></i>
                                User: <b><?php echo $userAppsCount ?? 0 ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">View and manage apps installed on the device</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <nav aria-label="breadcrumb" class="float-right mt-2">
                        <ol class="breadcrumb bg-transparent p-0 mb-0">
                            <?php echo $apps_urls ?? ''; ?>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Apps Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary">
                        <div class="card-header d-flex align-items-center">
                            <h3 class="card-title text-white">
                                <i class="fas fa-list mr-1"></i>
                                Apps List
                            </h3>
                            <div class="card-tools ml-auto">
                                <button type="button" class="btn btn-success btn-sm" id="pdfExport" title="Export PDF">
                                    <i class="fas fa-file-pdf mr-1"></i> Export
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="border-bottom px-3 py-2">
                            <div class="input-group input-group-sm" style="max-width:350px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" class="form-control table-search" placeholder="Search by app name or package..." data-table="table-sortable">
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-sortable">
                                    <thead>
                                    <tr>
                                        <th width="40%">App</th>
                                        <th width="15%">Version</th>
                                        <th width="10%">Permissions</th>
                                        <th width="10%">Size</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($apps_dump)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
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
                                                            <div class="mt-1">
                                                                <code class="text-muted small" title="Package Name">
                                                                    <?= esc($app['Package'] ?? 'N/A') ?>
                                                                </code>
                                                            </div>
                                                        </div>
                                                    </div>
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
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger delete-row"
                                                            data-id="<?= $app['counter'] ?? '' ?>"
                                                            data-url="<?= base_url('apps/delete') ?>"
                                                            data-name="<?= esc($app['Name'] ?? '') ?>"
                                                            title="Delete this app entry">
                                                        <i class="fas fa-trash"></i>
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
                                    <div class="entry-info">
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

<!-- Modern App Details Modal -->
<div class="modal fade" id="appDetailsModal" tabindex="-1" role="dialog" aria-labelledby="appDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); padding: 1.5rem;">
                <h5 class="modal-title text-white font-weight-bold" id="appDetailsModalLabel">
                    <i class="fas fa-layer-group mr-2 opacity-75"></i> Application Insight
                </h5>
                <button type="button" class="close text-white opacity-75" data-dismiss="modal" aria-label="Close" style="text-shadow: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <!-- Header Card -->
                <div class="bg-light p-4 border-bottom d-flex align-items-center">
                    <div id="appIconContainer" class="mr-4">
                        <img id="appIcon" src="" alt="App Icon" class="shadow-sm" style="width: 80px; height: 80px; border-radius: 16px; object-fit: cover;">
                    </div>
                    <div class="flex-grow-1">
                        <h4 id="appName" class="font-weight-bold mb-1 text-dark"></h4>
                        <p id="appPackage" class="text-muted mb-2" style="font-family: monospace;"></p>
                        <div id="appBadges" class="d-flex gap-2"></div>
                    </div>
                </div>

                <div class="row m-0">
                    <div class="col-md-7 p-4 border-right">
                        <h6 class="text-uppercase text-muted font-weight-bold mb-3" style="letter-spacing: 1px; font-size: 0.85rem;">
                            <i class="fas fa-info-circle mr-2"></i>Technical Specs
                        </h6>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-0 pt-0">
                                <span><i class="fas fa-code text-muted mr-2"></i>Version</span>
                                <span id="appVersionName" class="font-weight-bold text-dark"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-0">
                                <span><i class="fas fa-hashtag text-muted mr-2"></i>Build Code</span>
                                <span id="appVersionCode" class="badge badge-light border text-dark"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-0">
                                <span><i class="fas fa-weight-hanging text-muted mr-2"></i>Size</span>
                                <span id="appSize" class="badge"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-0 pb-0">
                                <span><i class="fas fa-shield-alt text-muted mr-2"></i>Permissions</span>
                                <span id="appPermissions" class="badge badge-warning"></span>
                            </li>
                        </ul>

                        <h6 class="text-uppercase text-muted font-weight-bold mb-3" style="letter-spacing: 1px; font-size: 0.85rem;">
                            <i class="fas fa-cogs mr-2"></i>SDK Environment
                        </h6>
                        <div class="d-flex justify-content-between">
                            <div class="p-3 bg-light rounded text-center w-100 mr-2 border">
                                <small class="d-block text-muted mb-1">Target SDK</small>
                                <span id="appTargetSdk" class="font-weight-bold text-dark h5 mb-0"></span>
                            </div>
                            <div class="p-3 bg-light rounded text-center w-100 ml-2 border">
                                <small class="d-block text-muted mb-1">Min SDK</small>
                                <span id="appMinSdk" class="font-weight-bold text-dark h5 mb-0"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 p-4 bg-light">
                        <h6 class="text-uppercase text-muted font-weight-bold mb-3" style="letter-spacing: 1px; font-size: 0.85rem;">
                            <i class="fas fa-clock mr-2"></i>Timeline
                        </h6>
                        <div class="timeline-modern mb-4">
                            <div class="timeline-step">
                                <div class="step-icon bg-success"><i class="fas fa-download"></i></div>
                                <div class="step-content">
                                    <small class="text-muted d-block text-uppercase">First Installed</small>
                                    <span id="appFirstInstall" class="font-weight-bold text-dark"></span>
                                </div>
                            </div>
                            <div class="timeline-step mt-3">
                                <div class="step-icon bg-info"><i class="fas fa-sync-alt"></i></div>
                                <div class="step-content">
                                    <small class="text-muted d-block text-uppercase">Last Updated</small>
                                    <span id="appLastUpdate" class="font-weight-bold text-dark"></span>
                                </div>
                            </div>
                        </div>

                        <div id="permissionsSection" style="display: none;">
                            <h6 class="text-uppercase text-muted font-weight-bold mb-3" style="letter-spacing: 1px; font-size: 0.85rem;">
                                <i class="fas fa-list mr-2"></i>Access Rights
                            </h6>
                            <div id="appPermissionsList" class="permissions-list border bg-white shadow-sm"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 bg-light py-3">
                <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-dismiss="modal">Close Insight</button>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .badge-sm {
        font-size: 0.7em;
        padding: 2px 6px;
    }
    .permissions-list {
        max-height: 220px;
        overflow-y: auto;
        border-radius: 8px;
        padding: 12px;
    }
    .permissions-list .permission-item {
        padding: 6px 0;
        border-bottom: 1px solid #f1f1f1;
        font-size: 0.85rem;
    }
    .permissions-list .permission-item:last-child {
        border-bottom: none;
    }
    .timeline-modern {
        position: relative;
        padding-left: 10px;
    }
    .timeline-modern::before {
        content: '';
        position: absolute;
        left: 24px;
        top: 20px;
        bottom: 20px;
        width: 2px;
        background: #e9ecef;
    }
    .timeline-step {
        display: flex;
        align-items: flex-start;
        position: relative;
        z-index: 1;
    }
    .timeline-step .step-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 15px;
        box-shadow: 0 0 0 4px #f8f9fa;
    }
    .timeline-step .step-content {
        flex: 1;
        padding-top: 4px;
    }
    .table-sortable thead th { cursor: pointer; user-select: none; }
    .table-sortable thead th.sort-asc::after { content: ' \25B2'; font-size: 0.7em; }
    .table-sortable thead th.sort-desc::after { content: ' \25BC'; font-size: 0.7em; }
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
            let appIcon = document.getElementById('appIcon');
            if (!appIcon) {
                document.getElementById('appIconContainer').innerHTML = '<img id="appIcon" src="" alt="App Icon" class="shadow-sm" style="width: 80px; height: 80px; border-radius: 16px; object-fit: cover;">';
                appIcon = document.getElementById('appIcon');
            }

            if (app.app_icon) {
                appIcon.src = 'data:image/png;base64,' + app.app_icon;
                appIcon.style.display = 'block';
                const placeholder = document.getElementById('appIconContainer').querySelector('.avatar-circle');
                if (placeholder) placeholder.remove();
            } else {
                appIcon.style.display = 'none';
                if (!document.getElementById('appIconContainer').querySelector('.avatar-circle')) {
                    const div = document.createElement('div');
                    div.className = 'avatar-circle bg-primary text-white d-inline-flex align-items-center justify-content-center shadow-sm';
                    div.style.width = '80px';
                    div.style.height = '80px';
                    div.innerHTML = '<i class="fas fa-mobile-alt fa-2x"></i>';
                    document.getElementById('appIconContainer').appendChild(div);
                }
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

        // Real-time table search
        document.querySelector('.table-search')?.addEventListener('keyup', function() {
            var keyword = this.value.toLowerCase();
            var target = this.getAttribute('data-table');
            document.querySelectorAll('.' + target + ' tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().indexOf(keyword) > -1 ? '' : 'none';
            });
        });

        // Column sorting
        document.querySelectorAll('.table-sortable thead th').forEach(function(th) {
            th.addEventListener('click', function() {
                var table = this.closest('table');
                var tbody = table.querySelector('tbody');
                var index = Array.prototype.indexOf.call(this.parentNode.children, this);
                var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
                var asc = !this.classList.contains('sort-asc');
                table.querySelectorAll('thead th').forEach(function(h) { h.classList.remove('sort-asc', 'sort-desc'); });
                this.classList.toggle('sort-asc', asc);
                this.classList.toggle('sort-desc', !asc);
                rows.sort(function(a, b) {
                    var aVal = (a.querySelectorAll('td')[index]?.textContent || '').trim();
                    var bVal = (b.querySelectorAll('td')[index]?.textContent || '').trim();
                    var aNum = parseFloat(aVal), bNum = parseFloat(bVal);
                    if (!isNaN(aNum) && !isNaN(bNum)) return asc ? aNum - bNum : bNum - aNum;
                    return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                });
                rows.forEach(function(row) { tbody.appendChild(row); });
            });
        });

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

        // PDF Export
        $('#pdfExport').on('click', function () {
            var element = document.querySelector('.table-sortable');
            if (!element) return;
            Swal.fire({
                title: 'Generating PDF...',
                text: 'Please wait while we prepare your document',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            html2pdf().set({
                margin:       10,
                filename:     'apps_export_' + Date.now() + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, letterRendering: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
            }).from(element).save().then(function () {
                Swal.close();
                Swal.fire({ icon: 'success', title: 'Export Complete', text: 'PDF has been downloaded', timer: 2000, showConfirmButton: false });
            }).catch(function () {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Export Failed', text: 'Could not generate PDF', timer: 3000, showConfirmButton: false });
            });
        });
    });
</script>
<?php include __DIR__ . '/../partials/_delete_confirm.php'; ?>