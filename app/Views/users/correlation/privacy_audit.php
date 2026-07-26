<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-user-shield text-danger mr-2"></i> Privacy & Permission Audit</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-outline-info btn-sm" href="<?= base_url('analysis') ?>"><i class="fas fa-arrow-left mr-1"></i> Back to Analysis</a>
                </div>
            </div>
        </div>
    </section>

<?php if (isset($ml_insight) && !empty($ml_insight['insights'])): ?>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header">
                        <?php $_eng = (new \App\Models\Mod_Anomalies())->getDefaultEngine(); $_engLabel = match($_eng){'python'=>'Python Engine','both'=>'Hybrid Engine',default=>'PHP Engine'}; ?>
                        <h3 class="card-title"><i class="fas fa-brain mr-2"></i> <?= $_engLabel ?> Intelligence</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <span class="badge badge-info p-2"><?= $ml_insight['algorithm'] ?></span>
                                <p class="text-muted mt-2 mb-0"><small><?= $ml_insight['data_source'] ?></small></p>
                            </div>
                            <div class="col-md-8">
                                <p><?= $ml_insight['description'] ?></p>
                                <ul class="mb-0">
                                    <?php foreach ($ml_insight['insights'] as $insight): ?>
                                    <li><?= $insight ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?= view('analysis/anomaly_alert_card', ['anomaly_alerts' => $anomaly_alerts ?? []]) ?>

    <section class="content">
        <div class="container-fluid">
            <!-- Risk Highlights -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-danger">
                        <div class="card-header">
                            <h3 class="card-title">Security & Privacy Posture</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center border-right">
                                    <?php 
                                    $totalRisk = count($audit);
                                    $highRisk = count(array_filter($audit, fn($a) => $a['score'] >= 7));
                                    ?>
                                    <h1 class="display-4 text-danger font-weight-bold"><?= $highRisk ?></h1>
                                    <p class="text-muted">CRITICAL RISK APPS</p>
                                </div>
                                <div class="col-md-8">
                                    <h5>Risk Factors Detected:</h5>
                                    <ul>
                                        <li><b>Data Exfiltration</b>: Apps with SMS access + Internet access.</li>
                                        <li><b>Privacy Intrusion</b>: Apps with Microphone/Camera access in background.</li>
                                        <li><b>Movement Tracking</b>: Apps with Fine Location access.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Table -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                Detailed App Risk Audit
                                <small class="text-muted ml-2">Showing <?= count($audit) ?> of <?= $audit_total ?></small>
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-striped table-valign-middle">
                                    <thead>
                                        <tr>
                                            <th>Application</th>
                                            <th>Risk Level</th>
                                            <th>Sensitivity Flags</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($audit as $app): ?>
                                        <tr>
                                            <td>
                                                <b><?= $app['name'] ?></b><br>
                                                <small class="text-muted"><?= $app['package'] ?></small>
                                            </td>
                                            <td>
                                                <?php if ($app['score'] >= 7): ?>
                                                    <span class="badge badge-danger p-2"><i class="fas fa-exclamation-triangle mr-1"></i> Critical</span>
                                                <?php elseif ($app['score'] >= 4): ?>
                                                    <span class="badge badge-warning p-2"><i class="fas fa-exclamation mr-1"></i> Warning</span>
                                                <?php else: ?>
                                                    <span class="badge badge-info p-2">Elevated</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php foreach ($app['risks'] as $risk): ?>
                                                    <span class="badge badge-light border"><?= $risk ?></span>
                                                <?php endforeach; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary view-app-details" data-app='<?= htmlspecialchars(json_encode($app['app_data']), ENT_QUOTES, 'UTF-8') ?>'>
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($audit)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center p-4">
                                                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                                <p>No high-risk apps detected on current device scans.</p>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php if (isset($audit_pager)): ?>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="entry-info">
                                        Showing <?= (($audit_currentPage-1)*$audit_perPage+1) ?> to <?= min($audit_currentPage*$audit_perPage, $audit_total) ?> of <?= $audit_total ?> entries
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        <?= $audit_pager ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Scam SMS Audit Table -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header border-0 bg-light">
                            <h3 class="card-title text-danger"><i class="fas fa-sms mr-2"></i> Scam & Phishing SMS Audit</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive p-3">
                                <table id="scamSmsTable" class="table table-hover table-bordered table-valign-middle">
                                    <thead>
                                        <tr>
                                            <th>Sender Address</th>
                                            <th>Message Snippet</th>
                                            <th>Last Received</th>
                                            <th>Flag</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($scams)): ?>
                                            <?php foreach ($scams as $scam): ?>
                                            <tr>
                                                <td>
                                                    <b><?= esc($scam['address']) ?></b>
                                                </td>
                                                <td>
                                                    <span class="text-muted"><?= esc(substr($scam['body'], 0, 80)) ?>...</span>
                                                </td>
                                                <td>
                                                    <?= date('M j, Y, g:i a', $scam['date'] / 1000) ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-warning p-2"><i class="fas fa-exclamation-circle mr-1"></i> Suspicious</span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center p-4">
                                                    <i class="fas fa-shield-check text-success fa-2x mb-2"></i>
                                                    <p>No suspicious scam or phishing SMS detected.</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (isset($scam_pager)): ?>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="entry-info">
                                            Showing <?= (($scam_currentPage-1)*$scam_perPage+1) ?> to <?= min($scam_currentPage*$scam_perPage, $scam_total) ?> of <?= $scam_total ?> entries
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="float-right">
                                            <?= $scam_pager ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
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
</style>

<script>
$(document).ready(function() {
    // App Details Modal Handler
    const viewButtons = document.querySelectorAll('.view-app-details');
    let modalInstance = null;
    if (document.getElementById('appDetailsModal')) {
        modalInstance = new bootstrap.Modal(document.getElementById('appDetailsModal'));
    }

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const appData = JSON.parse(this.getAttribute('data-app'));
            populateAppModal(appData);
            if(modalInstance) modalInstance.show();
        });
    });

    function populateAppModal(app) {
        document.getElementById('appName').textContent = app.app_name || 'Unknown';
        document.getElementById('appPackage').textContent = app.package_name || 'N/A';
        document.getElementById('appVersionName').textContent = app.version_name || 'N/A';
        document.getElementById('appVersionCode').textContent = app.version_code || 'N/A';

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

        const badgesContainer = document.getElementById('appBadges');
        badgesContainer.innerHTML = '';
        if (app.is_system_app == 1 || app.is_system_app == true) {
            badgesContainer.innerHTML += '<span class="badge badge-secondary mr-1">System App</span>';
        } else {
            badgesContainer.innerHTML += '<span class="badge badge-success mr-1">User App</span>';
        }

        const size = app.app_size || 0;
        const sizeMB = (size / (1024 * 1024)).toFixed(2);
        const sizeColor = sizeMB > 100 ? 'danger' : (sizeMB > 50 ? 'warning' : 'success');
        document.getElementById('appSize').textContent = sizeMB + ' MB';
        document.getElementById('appSize').className = 'badge badge-' + sizeColor;

        const permCount = app.permission_count || 0;
        document.getElementById('appPermissions').textContent = permCount + ' permissions';

        document.getElementById('appTargetSdk').textContent = app.target_sdk || 'N/A';
        document.getElementById('appMinSdk').textContent = app.min_sdk || 'N/A';

        document.getElementById('appFirstInstall').textContent = formatTimestamp(app.first_install_time);
        document.getElementById('appLastUpdate').textContent = formatTimestamp(app.last_update_time);

        const permissionsSection = document.getElementById('permissionsSection');
        const permissionsList = document.getElementById('appPermissionsList');

        if (app.permissions && permCount > 0) {
            try {
                const permissions = (typeof app.permissions === 'string') ? app.permissions.split(',') : app.permissions;
                if (Array.isArray(permissions) && permissions.length > 0) {
                    permissionsList.innerHTML = '';
                    permissions.forEach(perm => {
                        if(perm.trim() !== '') {
                            const permItem = document.createElement('div');
                            permItem.className = 'permission-item';
                            permItem.innerHTML = `<i class="fas fa-check text-success mr-2"></i> ${perm.trim()}`;
                            permissionsList.appendChild(permItem);
                        }
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
        const date = new Date(parseInt(timestamp));
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
