<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-edit mr-2 text-primary"></i>Edit Plan Version</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/plans') ?>">Plans & Pricing</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-tags text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1"><?= esc($plan['name']) ?> <small class="text-muted">(<?= esc($plan['slug']) ?>)</small></h5>
                        <p class="mb-0 small text-muted">Saving creates a new version with a full audit record. Current version: <?= $current ? 'v' . $current['version'] : 'none' ?>.</p>
                    </div>
                </div>
            </div>

            <form method="post" action="<?= base_url("superadmin/plans/updateVersion/{$plan['id']}") ?>" id="planForm" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">

                <div class="row">
                    <!-- Left column (col-lg-6): Administrative, pricing,Timing, Alerts & standard features -->
                    <div class="col-lg-6 col-md-12">
                        <!-- Pricing Card -->
                        <div class="card card-primary card-outline shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-dollar-sign text-success mr-2"></i>Pricing</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="price_monthly" class="font-weight-bold">Monthly Price (USD)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt text-success"></i></span>
                                                </div>
                                                <input type="number" step="0.01" min="0" name="price_monthly" id="price_monthly"
                                                       class="form-control" required
                                                       value="<?= $current ? $current['price_monthly_cents'] / 100 : 0 ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="price_yearly" class="font-weight-bold">Yearly Price (USD)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-check text-primary"></i></span>
                                                </div>
                                                <input type="number" step="0.01" min="0" name="price_yearly" id="price_yearly"
                                                       class="form-control" required
                                                       value="<?= $current ? $current['price_yearly_cents'] / 100 : 0 ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Plan Limits Card -->
                        <div class="card card-primary card-outline shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-sliders-h text-primary mr-2"></i>Plan Limits</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="currency" class="font-weight-bold">Currency</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-money-bill-wave text-success"></i></span>
                                                </div>
                                                <input type="text" name="currency" id="currency" class="form-control"
                                                       value="<?= $current['currency'] ?? 'USD' ?>" maxlength="3" placeholder="USD">
                                            </div>
                                            <small class="form-text text-muted">ISO 4217 code</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="max_devices" class="font-weight-bold">Max Devices</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-mobile-alt text-info"></i></span>
                                                </div>
                                                <input type="number" name="max_devices" id="max_devices" class="form-control" required min="1"
                                                       value="<?= $current['max_devices'] ?? 1 ?>">
                                            </div>
                                            <small class="form-text text-muted">Allowed devices</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="history_days" class="font-weight-bold">History Retention</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-history text-warning"></i></span>
                                                </div>
                                                <input type="number" name="history_days" id="history_days" class="form-control" required min="1"
                                                       value="<?= $current['history_days'] ?? 10 ?>">
                                            </div>
                                            <small class="form-text text-muted">Days</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Effective From Card -->
                        <div class="card card-primary card-outline shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-clock text-warning mr-2"></i>Effective From</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="effective_from" class="font-weight-bold">Effective From</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt text-warning"></i></span>
                                        </div>
                                        <input type="datetime-local" name="effective_from" id="effective_from" class="form-control"
                                               value="<?= $current ? date('Y-m-d\TH:i', strtotime($current['effective_from'])) : date('Y-m-d\TH:i') ?>">
                                    </div>
                                    <small class="form-text text-muted">When this version becomes active</small>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="change_reason" class="font-weight-bold">Change Reason <span class="text-muted">(for audit log)</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-comment-alt text-secondary"></i></span>
                                        </div>
                                        <textarea name="change_reason" class="form-control" rows="3"
                                                  placeholder="Explain what changed and why..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alerts & Support Card -->
                        <div class="card card-primary card-outline shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-bell text-danger mr-2"></i>Alerts & Support</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <label class="font-weight-bold d-block">Real-Time Security Alerts</label>
                                        <div class="form-check form-check-inline mb-1">
                                            <input type="checkbox" name="alert_email" id="alert_email" class="form-check-input" value="1"
                                                   <?= ($current['alert_email'] ?? 1) ? 'checked' : '' ?>>
                                            <label class="form-check-label font-weight-bold" for="alert_email"><i class="fas fa-envelope text-success mr-1"></i>Email alerts</label>
                                        </div>
                                        <p class="text-muted small mb-3">Enables automated email notifications sent instantly when high-risk anomalies or geofence breaches are detected.</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="wellbeing_depth" class="font-weight-bold">Wellbeing Report Depth</label>
                                            <div class="input-group mb-1">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-day text-secondary"></i></span>
                                                </div>
                                                <select name="wellbeing_depth" id="wellbeing_depth" class="form-control">
                                                    <?php foreach ($wellbeingDepthOptions as $val => $label): ?>
                                                        <option value="<?= $val ?>" <?= (($current['wellbeing_depth'] ?? '7') === $val) ? 'selected' : '' ?>><?= $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <small class="text-muted d-block small">Defines the sliding window of days for which historical digital wellbeing data (screen time, unlocks) is retained and visible in the dashboard.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="support_tier" class="font-weight-bold">Support Tier</label>
                                            <div class="input-group mb-1">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-headset text-secondary"></i></span>
                                                </div>
                                                <select name="support_tier" id="support_tier" class="form-control">
                                                    <?php foreach ($supportTierOptions as $val => $label): ?>
                                                        <option value="<?= $val ?>" <?= (($current['support_tier'] ?? 'standard') === $val) ? 'selected' : '' ?>><?= $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <small class="text-muted d-block small">Assigns the customer support SLA priority level for users subscribed to this plan (Standard queue vs Priority 24/7 channels).</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Standard Features Card -->
                        <div class="card card-primary card-outline shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-star text-warning mr-2"></i>Standard Features</h3>
                            </div>
                            <div class="card-body p-3">
                                <?php $featureDefaults = $current ? json_decode($current['features'], true) : []; ?>
                                <?php foreach ($featuresList as $feat): ?>
                                    <div class="form-check form-check-inline mb-2">
                                        <input type="checkbox" name="feature_<?= $feat['key'] ?>"
                                               id="feature_<?= $feat['key'] ?>" class="form-check-input"
                                               value="1"
                                               <?= (!empty($featureDefaults[$feat['key']])) ? 'checked' : '' ?>>
                                        <label class="form-check-label font-weight-bold" for="feature_<?= $feat['key'] ?>">
                                            <?= $feat['label'] ?>
                                        </label>
                                    </div>
                                    <p class="text-muted small pl-4 mb-3 mt-0"><?= $feat['desc'] ?></p>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right column (col-lg-6): profiles + fcm + ml -->
                    <div class="col-lg-6 col-md-12">
                        <!-- Hardware Profile Card -->
                        <div class="card card-primary card-outline shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title text-indigo"><i class="fas fa-microchip text-indigo mr-2"></i>Hardware Profile</h3>
                            </div>
                            <div class="card-body p-3">
                                <?php $hwProfile = $featureDefaults['hardware_profile'] ?? 'basic'; ?>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="hw_profile_core" id="hw_profile_core" class="form-check-input" value="1" checked disabled>
                                    <label class="form-check-label font-weight-bold text-success" for="hw_profile_core">
                                        Core (Free) - Basic hardware capabilities
                                    </label>
                                    <p class="text-muted small mb-2">Includes basic hardware capabilities like local sensors, default alarms, and device locale.</p>
                                    <p class="text-muted small mb-3 mt-0">Tier: <span class="badge badge-success">Core</span> and above</p>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="hw_profile_advanced" id="hw_profile_advanced" class="form-check-input" value="1"
                                           <?= in_array($hwProfile, ['advanced', 'all']) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-weight-bold text-warning" for="hw_profile_advanced">
                                        Advanced (Gold) - Advanced telemetry
                                    </label>
                                    <p class="text-muted small mb-2">Includes storage indicators, battery logs, cell tower telemetry, and display metrics.</p>
                                    <p class="text-muted small mb-3 mt-0">Tier: <span class="badge badge-warning">Advanced</span> and above</p>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="hw_profile_deep" id="hw_profile_deep" class="form-check-input" value="1"
                                           <?= ($hwProfile === 'all') ? 'checked' : '' ?>>
                                    <label class="form-check-label font-weight-bold text-danger" for="hw_profile_deep">
                                        Deep (Platinum) - Raw streaming
                                    </label>
                                    <p class="text-muted small mb-2">Includes remote camera triggers, active sensor streaming, biometric authentication status, etc.</p>
                                    <p class="text-muted small mb-3 mt-0">Tier: <span class="badge badge-danger">Deep</span> and above</p>
                                </div>
                            </div>
                        </div>

                        <!-- Software Profile Card -->
                        <div class="card card-primary card-outline shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title text-navy"><i class="fas fa-laptop-code text-navy mr-2"></i>Software Profile</h3>
                            </div>
                            <div class="card-body p-3">
                                <?php $swProfile = $featureDefaults['software_profile'] ?? 'basic'; ?>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="sw_profile_core" id="sw_profile_core" class="form-check-input" value="1" checked disabled>
                                    <label class="form-check-label font-weight-bold text-success" for="sw_profile_core">
                                        Core (Free) - Default apps, locale
                                    </label>
                                    <p class="text-muted small mb-2">Includes default apps, local alarms, and locale monitoring.</p>
                                    <p class="text-muted small mb-3 mt-0">Tier: <span class="badge badge-success">Core</span> and above</p>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="sw_profile_advanced" id="sw_profile_advanced" class="form-check-input" value="1"
                                           <?= in_array($swProfile, ['advanced', 'all']) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-weight-bold text-warning" for="sw_profile_advanced">
                                        Advanced (Gold) - App data usage
                                    </label>
                                    <p class="text-muted small mb-2">Includes app data usage, saved wifi configurations, accessibility services, and keyboards list.</p>
                                    <p class="text-muted small mb-3 mt-0">Tier: <span class="badge badge-warning">Advanced</span> and above</p>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="sw_profile_deep" id="sw_profile_deep" class="form-check-input" value="1"
                                           <?= ($swProfile === 'all') ? 'checked' : '' ?>>
                                    <label class="form-check-label font-weight-bold text-danger" for="sw_profile_deep">
                                        Deep (Platinum) - Clipboard, Wellbeing, VPN logs
                                    </label>
                                    <p class="text-muted small mb-2">Includes clipboard contents, active screenshots, Wellbeing summaries, active VPN status, and health statistics.</p>
                                    <p class="text-muted small mb-3 mt-0">Tier: <span class="badge badge-danger">Deep</span> and above</p>
                                </div>
                            </div>
                        </div>

                        <!-- FCM Command Groups Card -->
                        <div class="card card-primary card-outline shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title text-success"><i class="fas fa-paper-plane text-success mr-2"></i>FCM Command Groups</h3>
                            </div>
                            <div class="card-body p-3">
                                <?php
                                    $fcmDefaults = $featureDefaults['fcm_groups'] ?? [];
                                    if (is_string($fcmDefaults)) {
                                        $fcmDefaults = json_decode($fcmDefaults, true) ?: [];
                                    }
                                ?>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="fcm_group_core" id="fcm_group_core" class="form-check-input" value="1" checked disabled>
                                    <label class="form-check-label font-weight-bold text-success" for="fcm_group_core">
                                        Core Commands (Free)
                                    </label>
                                    <p class="text-muted small mb-2">Includes basic command capabilities like beep, check health, and get contacts.</p>
                                    <p class="text-muted small mb-3 mt-0">Tier: <span class="badge badge-success">Core</span> and above</p>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="fcm_group_advanced" id="fcm_group_advanced" class="form-check-input" value="1"
                                           <?= in_array('advanced', $fcmDefaults) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-weight-bold text-warning" for="fcm_group_advanced">
                                        Advanced Commands (Gold)
                                    </label>
                                    <p class="text-muted small mb-2">Includes contacts, apps list, call logs, SMS, location, and device deactivation.</p>
                                    <p class="text-muted small mb-3 mt-0">Tier: <span class="badge badge-warning">Advanced</span> and above</p>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="fcm_group_deep" id="fcm_group_deep" class="form-check-input" value="1"
                                           <?= in_array('deep', $fcmDefaults) ? 'checked' : '' ?>>
                                    <label class="form-check-label font-weight-bold text-danger" for="fcm_group_deep">
                                        Deep Commands (Platinum)
                                    </label>
                                    <p class="text-muted small mb-2">Includes remote camera/audio capture, complete filesystem logs, remote wipe/logout, and file management.</p>
                                    <p class="text-muted small mb-3 mt-0">Tier: <span class="badge badge-danger">Deep</span> and above</p>
                                </div>
                            </div>
                        </div>

                        <!-- ML Algorithms Card -->
                        <div class="card card-primary card-outline shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title text-purple"><i class="fas fa-brain text-purple mr-2"></i>ML Algorithms</h3>
                            </div>
                            <div class="card-body p-3">
                                <?php $algoDefaults = $current ? json_decode($current['ml_algorithms'], true) : []; ?>
                                <?php foreach ($algorithmsList as $algo): ?>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="algo_<?= $algo['key'] ?>"
                                               id="algo_<?= $algo['key'] ?>" class="form-check-input"
                                               value="1"
                                               <?= ($algo['key'] === 'core' || in_array($algo['key'], $algoDefaults)) ? 'checked' : '' ?>
                                               <?= ($algo['key'] === 'core') ? 'disabled' : '' ?>>
                                        <label class="form-check-label font-weight-bold" for="algo_<?= $algo['key'] ?>">
                                            <?php if ($algo['key'] === 'core'): ?>
                                                <span class="text-success">Core (8 algorithms) — Basic statistical detection</span>
                                            <?php elseif ($algo['key'] === 'advanced'): ?>
                                                <span class="text-warning">Advanced (10 algorithms) — PHP-ML pattern analysis</span>
                                            <?php else: ?>
                                                <span class="text-danger">Deep (7 algorithms) — Full Python neural models</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                    <p class="text-muted small mb-2">
                                        <?php if ($algo['key'] === 'core'): ?>
                                            Performs core statistic anomaly calculations on basic SMS and call counts.
                                        <?php elseif ($algo['key'] === 'advanced'): ?>
                                            Evaluates behavioral pattern shifts and cluster anomalies using advanced ML libraries.
                                        <?php else: ?>
                                            Runs full neural networks, deep learning models, and complex behavioral trees.
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-muted small mb-3 mt-0">Tier: <span class="badge <?= $algo['key'] === 'core' ? 'badge-success' : ($algo['key'] === 'advanced' ? 'badge-warning' : 'badge-danger') ?>"><?= ucfirst($algo['key']) ?></span> and above</p>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-footer bg-white d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="previewChanges()">
                            <i class="fas fa-eye mr-1"></i> Preview Changes
                        </button>
                        <button type="submit" name="action" value="save" class="btn btn-primary ml-auto">
                            <i class="fas fa-save mr-1"></i> Create New Version
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<div id="previewModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fas fa-eye mr-2"></i>Preview Version Changes</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body" id="previewContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function previewChanges() {
    const form = document.getElementById('planForm');
    const data = new URLSearchParams(new FormData(form));
    data.set('action', 'preview');
    fetch('<?= base_url("superadmin/plans/updateVersion/{$plan['id']}") ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: data.toString()
    })
    .then(r => r.json())
    .then(res => {
        document.getElementById('previewContent').innerHTML = buildPreview(res);
        $('#previewModal').modal('show');
    })
    .catch(() => {
        document.getElementById('previewContent').innerHTML = '<div class="alert alert-danger">Failed to generate preview.</div>';
        $('#previewModal').modal('show');
    });
}

function buildPreview(res) {
    if (!res || !res.price_monthly_cents) {
        return '<div class="alert alert-warning">No preview data available.</div>';
    }
    let features = '';
    if (res.features) {
        Object.entries(res.features).forEach(([k, v]) => {
            if (Array.isArray(v)) {
                features += '<span class="badge badge-success mr-1 mb-1"><i class="fas fa-check mr-1"></i>' + k + ': ' + JSON.stringify(v) + '</span>';
            } else if (typeof v === 'boolean') {
                features += '<span class="badge ' + (v ? 'badge-success' : 'badge-secondary') + ' mr-1 mb-1">'
                    + '<i class="fas fa-' + (v ? 'check' : 'times') + ' mr-1"></i>' + k + '</span>';
            } else {
                features += '<span class="badge badge-info mr-1 mb-1">' + k + ': ' + v + '</span>';
            }
        });
    }
    let algos = '';
    (res.ml_algorithms || []).forEach(a => {
        algos += '<span class="badge badge-info mr-1"><i class="fas fa-microchip mr-1"></i>' + a + '</span>';
    });
    return '<div class="row">'
        + '<div class="col-md-6"><div class="small-box bg-success"><div class="inner"><h3>$' + (res.price_monthly_cents / 100).toFixed(2) + '</h3><p>Monthly Price</p></div></div></div>'
        + '<div class="col-md-6"><div class="small-box bg-primary"><div class="inner"><h3>$' + (res.price_yearly_cents / 100).toFixed(2) + '</h3><p>Yearly Price</p></div></div></div>'
        + '</div>'
        + '<div class="mb-3"><span class="badge badge-info mr-1">Max Devices: ' + res.max_devices + '</span>'
        + '<span class="badge badge-warning mr-1">History: ' + res.history_days + ' days</span></div>'
        + '<hr><h6 class="font-weight-bold mb-2">Features & Configurations</h6><div>' + features + '</div>'
        + '<hr><h6 class="font-weight-bold mb-2">ML Algorithms</h6><div>' + algos + '</div>';
}
</script>

<style>
.text-purple { color: #6f42c1 !important; }
.text-indigo { color: #6610f2 !important; }
.text-navy { color: #001f3f !important; }
</style>
