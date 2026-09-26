<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header pt-3 pb-2">
        <div class="container-fluid">
            <div class="row align-items-center mb-2">
                <div class="col-sm-6">
                    <h1 class="h3 mb-0 text-dark font-weight-bold">
                        <i class="fas fa-user-shield text-danger mr-2"></i> Privacy &amp; Surveillance Audit
                    </h1>
                    <p class="text-muted mb-0 small">Surveillance detection across background sensors, clipboard reads, and accessibility services.</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-outline-secondary btn-sm shadow-sm" href="<?= base_url('analysis') ?>"><i class="fas fa-arrow-left mr-1"></i> Back to Analysis</a>
                </div>
            </div>
        </div>
    </section>

<?php if (isset($ml_insight) && !empty($ml_insight['insights'])): ?>
<?php $_eng = (new \App\Models\AnomaliesModel())->getDefaultEngine(); $_engLabel = match($_eng){'python'=>'Python Engine','both'=>'Hybrid Engine',default=>'PHP Engine'}; ?>
<section class="content mb-4">
    <div class="container-fluid">
        <div class="card bg-secondary text-white shadow-sm border-0" style="border-radius: 8px;">
            <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0 d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <div class="rounded-circle p-3 mr-3 shadow" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);">
                        <i class="fas fa-shield-alt fa-lg text-white"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-weight-bold text-white"><?= $_engLabel ?> Privacy Risk Assessment &amp; Threat Synthesis</h4>
                        <small class="text-light opacity-75">Automated detection of silent camera/mic captures, clipboard snooping, &amp; keylogger services</small>
                    </div>
                </div>
                <div>
                    <span class="badge badge-pill badge-danger px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
                        <i class="fas fa-microchip mr-1"></i> Algorithm: <?= esc($ml_insight['algorithm'] ?? 'Permission Risk Scorer') ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-5 mb-3 mb-md-0 border-right-md border-rose pr-md-4">
                        <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                            <h6 class="text-rose font-weight-bold mb-2" style="color: #fb7185;"><i class="fas fa-database mr-2"></i> What Is Happening</h6>
                            <p class="mb-0 text-light opacity-90" style="font-size: 0.9rem; line-height: 1.5;">
                                <?= esc($ml_insight['description'] ?? 'Audits installed app permissions, detects background hardware capture while display is off, scans clipboard reads for credit cards/passwords, and flags sideloaded APK origins.') ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-7 pl-md-4">
                        <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-lightbulb mr-2"></i> Privacy Threat Audit Findings</h6>
                        <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
                            <?php foreach ($ml_insight['insights'] as $insight): ?>
                            <li class="mb-2 d-flex align-items-start">
                                <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                <span><?= $insight ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
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

                    <!-- 🎙️ Silent Background Mic & Camera Access -->
                    <div class="card card-outline card-danger shadow-sm mt-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-video-slash text-danger mr-2"></i> Silent Mic &amp; Camera Hardware Access (Screen-Off Surveillance)</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Hardware Sensor</th>
                                        <th>Application</th>
                                        <th>Screen State</th>
                                        <th>Timestamp</th>
                                        <th>Threat Level</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($silent_captures)): ?>
                                        <?php foreach ($silent_captures as $cap): ?>
                                        <tr>
                                            <td><b><i class="fas fa-microphone text-danger mr-1"></i> <?= esc($cap['sensor']) ?></b></td>
                                            <td><code><?= esc($cap['package_name']) ?></code></td>
                                            <td><span class="badge badge-dark"><?= esc($cap['screen_state']) ?></span></td>
                                            <td><?= $cap['timestamp'] ?></td>
                                            <td><span class="badge badge-danger"><?= esc($cap['severity']) ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center text-muted py-3">No silent hardware accesses recorded during screen-off intervals.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 📋 Clipboard Data Monitor -->
                    <div class="card card-outline card-warning shadow-sm mt-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-clipboard-check text-warning mr-2"></i> Clipboard Sensitive Data Interception Monitor</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Reading Package</th>
                                        <th>Sensitive Data Classification</th>
                                        <th>Masked Clip Content</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($clipboard_alerts)): ?>
                                        <?php foreach ($clipboard_alerts as $clip): ?>
                                        <tr>
                                            <td><code><?= esc($clip['package_name']) ?></code></td>
                                            <td>
                                                <span class="badge badge-<?= str_contains($clip['clip_type'], 'PASSWORD') || str_contains($clip['clip_type'], 'CREDIT') ? 'danger' : 'warning' ?>">
                                                    <?= esc($clip['clip_type']) ?>
                                                </span>
                                            </td>
                                            <td><code><?= esc($clip['masked_text']) ?></code></td>
                                            <td><?= $clip['created_at'] ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center text-muted py-3">Zero clipboard sensitive text reads detected.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 📦 Sideloaded APK Store Origin & ♿ Accessibility Abuses -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card card-outline card-danger shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-box-open text-danger mr-2"></i> Sideloaded APK &amp; Untrusted Store Origin</h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Application / Package</th>
                                                <th>Installer Source</th>
                                                <th>Risk</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($sideloaded_apps)): ?>
                                                <?php foreach ($sideloaded_apps as $side): ?>
                                                <tr>
                                                    <td>
                                                        <div><b><?= esc($side['app_name']) ?></b></div>
                                                        <small class="text-muted"><code><?= esc($side['package_name']) ?></code></small>
                                                    </td>
                                                    <td><small><?= esc($side['installer_source']) ?></small></td>
                                                    <td><span class="badge badge-danger"><?= esc($side['risk_level']) ?></span></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="3" class="text-center text-muted py-3">100% of installed applications originate from verified official app stores.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-outline card-danger shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-universal-access text-danger mr-2"></i> Accessibility Service Abuse Audit</h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Service ID</th>
                                                <th>Package</th>
                                                <th>Over-Privileged Capability</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($accessibility_abuses)): ?>
                                                <?php foreach ($accessibility_abuses as $acc): ?>
                                                <tr>
                                                    <td><code><?= esc($acc['service_id']) ?></code></td>
                                                    <td><code><?= esc($acc['package_name']) ?></code></td>
                                                    <td><small class="text-danger"><b><?= esc($acc['capability']) ?></b></small></td>
                                                    <td><span class="badge badge-warning"><?= esc($acc['status']) ?></span></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="4" class="text-center text-muted py-3">Zero active accessibility service abuses or keylogger threats.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
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
