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
                    <!-- Left column: pricing + limits + stripe -->
                    <div class="col-lg-7">
                        <div class="card card-primary card-outline">
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
                                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
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
                                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
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

                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-cogs text-primary mr-2"></i>Plan Limits</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="currency" class="font-weight-bold">Currency</label>
                                            <input type="text" name="currency" id="currency" class="form-control"
                                                   value="<?= $current['currency'] ?? 'USD' ?>" maxlength="3" placeholder="USD">
                                            <small class="form-text text-muted">ISO 4217 code</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="max_devices" class="font-weight-bold">Max Devices</label>
                                            <input type="number" name="max_devices" id="max_devices" class="form-control" required min="1"
                                                   value="<?= $current['max_devices'] ?? 1 ?>">
                                            <small class="form-text text-muted">Allowed devices</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="history_days" class="font-weight-bold">History Retention</label>
                                            <input type="number" name="history_days" id="history_days" class="form-control" required min="1"
                                                   value="<?= $current['history_days'] ?? 10 ?>">
                                            <small class="form-text text-muted">Days</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fab fa-stripe text-info mr-2"></i>Stripe Integration</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="stripe_monthly" class="font-weight-bold">Price ID (Monthly)</label>
                                            <input type="text" name="stripe_monthly" id="stripe_monthly" class="form-control"
                                                   value="<?= $current['stripe_price_id_monthly'] ?? '' ?>" placeholder="price_xxxxx">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="stripe_yearly" class="font-weight-bold">Price ID (Yearly)</label>
                                            <input type="text" name="stripe_yearly" id="stripe_yearly" class="form-control"
                                                   value="<?= $current['stripe_price_id_yearly'] ?? '' ?>" placeholder="price_xxxxx">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-clock text-warning mr-2"></i>Effective From</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="effective_from" class="font-weight-bold">Effective From</label>
                                    <input type="datetime-local" name="effective_from" id="effective_from" class="form-control"
                                           value="<?= $current ? date('Y-m-d\TH:i', strtotime($current['effective_from'])) : date('Y-m-d\TH:i') ?>">
                                    <small class="form-text text-muted">When this version becomes active</small>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="change_reason" class="font-weight-bold">Change Reason <span class="text-muted">(for audit log)</span></label>
                                    <textarea name="change_reason" class="form-control" rows="3"
                                              placeholder="Explain what changed and why..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-bell text-danger mr-2"></i>Alerts & Support</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <label class="font-weight-bold d-block">Real-Time Security Alerts</label>
                                        <div class="form-check form-check-inline mb-2">
                                            <input type="checkbox" name="alert_email" id="alert_email" class="form-check-input" value="1"
                                                   <?= ($current['alert_email'] ?? 1) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="alert_email"><i class="fas fa-envelope text-muted mr-1"></i>Email alerts</label>
                                        </div>
                                        <div class="form-check form-check-inline mb-3">
                                            <input type="checkbox" name="alert_push" id="alert_push" class="form-check-input" value="1"
                                                   <?= ($current['alert_push'] ?? 1) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="alert_push"><i class="fas fa-mobile-alt text-muted mr-1"></i>Push notifications</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="wellbeing_depth" class="font-weight-bold">Wellbeing Report Depth</label>
                                            <select name="wellbeing_depth" id="wellbeing_depth" class="form-control">
                                                <?php foreach ($wellbeingDepthOptions as $val => $label): ?>
                                                    <option value="<?= $val ?>" <?= (($current['wellbeing_depth'] ?? '7') === $val) ? 'selected' : '' ?>><?= $label ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="support_tier" class="font-weight-bold">Support Tier</label>
                                            <select name="support_tier" id="support_tier" class="form-control">
                                                <?php foreach ($supportTierOptions as $val => $label): ?>
                                                    <option value="<?= $val ?>" <?= (($current['support_tier'] ?? 'standard') === $val) ? 'selected' : '' ?>><?= $label ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right column: features + algorithms -->
                    <div class="col-lg-5">
                        <?php $featureDefaults = $current ? json_decode($current['features'], true) : []; ?>
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-microchip text-indigo mr-2"></i>Hardware & Software Profiles</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="hardware_profile" class="font-weight-bold">Allowed Hardware Profile</label>
                                    <select name="hardware_profile" id="hardware_profile" class="form-control">
                                        <option value="basic" <?= (($featureDefaults['hardware_profile'] ?? 'basic') === 'basic') ? 'selected' : '' ?>>Basic (Free: Locale, default apps, alarms)</option>
                                        <option value="advanced" <?= (($featureDefaults['hardware_profile'] ?? 'basic') === 'advanced') ? 'selected' : '' ?>>Advanced (Gold: Storage, battery, cell towers, display, etc.)</option>
                                        <option value="all" <?= (($featureDefaults['hardware_profile'] ?? 'basic') === 'all') ? 'selected' : '' ?>>All Hardware Configs (Platinum: Camera, sensors, biometric, etc.)</option>
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="software_profile" class="font-weight-bold">Allowed Software Profile</label>
                                    <select name="software_profile" id="software_profile" class="form-control">
                                        <option value="basic" <?= (($featureDefaults['software_profile'] ?? 'basic') === 'basic') ? 'selected' : '' ?>>Basic (Free: default apps, alarms, locale)</option>
                                        <option value="advanced" <?= (($featureDefaults['software_profile'] ?? 'basic') === 'advanced') ? 'selected' : '' ?>>Advanced (Gold: data usage, saved wifi, accessibility, keyboards)</option>
                                        <option value="all" <?= (($featureDefaults['software_profile'] ?? 'basic') === 'all') ? 'selected' : '' ?>>All Software Configs (Platinum: clipboard, screenshots, Wellbeing, VPN, health, etc.)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-star text-warning mr-2"></i>Features</h3>
                            </div>
                            <div class="card-body p-3">
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

                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-brain text-purple mr-2"></i>ML Algorithms</h3>
                            </div>
                            <div class="card-body p-3">
                                <?php $algoDefaults = $current ? json_decode($current['ml_algorithms'], true) : []; ?>
                                <?php foreach ($algorithmsList as $algo): ?>
                                    <div class="form-check form-check-inline mb-2">
                                        <input type="checkbox" name="algo_<?= $algo['key'] ?>"
                                               id="algo_<?= $algo['key'] ?>" class="form-check-input"
                                               value="1"
                                               <?= in_array($algo['key'], $algoDefaults) ? 'checked' : '' ?>>
                                        <label class="form-check-label font-weight-bold" for="algo_<?= $algo['key'] ?>">
                                            <?= $algo['label'] ?>
                                        </label>
                                    </div>
                                    <p class="text-muted small pl-4 mb-3 mt-0">Tier: <span class="badge <?= $algo['tier'] === 'free' ? 'badge-success' : ($algo['tier'] === 'gold' ? 'badge-warning' : 'badge-danger') ?>"><?= ucfirst($algo['tier']) ?></span> and above</p>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-footer bg-white d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="previewChanges()">
                            <i class="fas fa-eye mr-1"></i> Preview Changes
                        </button>
                        <button type="submit" name="action" value="save" class="btn btn-primary">
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
            features += '<span class="badge ' + (v ? 'badge-success' : 'badge-secondary') + ' mr-1">'
                + '<i class="fas fa-' + (v ? 'check' : 'times') + ' mr-1"></i>' + k + '</span>';
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
        + '<hr><h6 class="font-weight-bold mb-2">Features</h6><div>' + features + '</div>'
        + '<hr><h6 class="font-weight-bold mb-2">ML Algorithms</h6><div>' + algos + '</div>';
}
</script>

<style>
.text-purple { color: #6f42c1 !important; }
</style>
