<?php
    $plans = $plans ?? [];
    $free = $plans['free'] ?? null;
    $gold = $plans['gold'] ?? null;
    $plat = $plans['platinum'] ?? null;

    $defaults = ['free' => [1, 10, 'free'], 'gold' => [3, 60, 'gold'], 'platinum' => [10, 180, 'platinum']];
    $devices = fn($p, $slug) => $p ? $p['max_devices'] : $defaults[$slug][0];
    $history = fn($p, $slug) => $p ? $p['history_days'] : $defaults[$slug][1];
?>

    <!-- Hero Section -->
    <div class="py-5 shadow-sm text-white text-center" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
        <div class="container">
            <h1 class="display-4 font-weight-bold">Pricing Plans</h1>
            <p class="lead mb-0">Monitor more devices, dig deeper into your data — pick the tier that fits you.</p>
        </div>
    </div>

    <!-- Pricing Cards -->
    <section class="content py-5">
        <div class="container" style="max-width: 1600px;">
            
            <!-- Billing toggle -->
            <div class="text-center mb-4">
                <div class="btn-group btn-group-lg btn-group-toggle shadow-sm" data-toggle="buttons">
                    <label class="btn btn-light active" id="lbl-monthly">
                        <input type="radio" name="billing" value="monthly" autocomplete="off" checked>
                        <i class="far fa-calendar-alt mr-1"></i> Monthly
                    </label>
                    <label class="btn btn-warning" id="lbl-yearly">
                        <input type="radio" name="billing" value="yearly" autocomplete="off">
                        <i class="fas fa-tags mr-1"></i> Yearly
                        <span class="badge badge-dark ml-1">Save</span>
                    </label>
                </div>
            </div>

            <div class="row justify-content-center g-4">
                <?php
                $planTheme = [
                    'free'     => ['header' => 'bg-secondary',       'btn' => 'btn-gradient-secondary', 'icon' => 'star',   'tag' => 'GET STARTED'],
                    'gold'     => ['header' => 'bg-gradient-pink',   'btn' => 'btn-gradient-pink',      'icon' => 'crown',  'tag' => 'POPULAR'],
                    'platinum' => ['header' => 'bg-gradient-purple', 'btn' => 'btn-gradient-purple',    'icon' => 'gem',    'tag' => 'MOST ADVANCED'],
                ];
                foreach (['free', 'gold', 'platinum'] as $key):
                    $p = $plans[$key] ?? null;
                    if (!$p) continue;
                    $isPlat = $key === 'platinum';
                ?>
                <div class="col-12 col-md-6 col-xl-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 plan-card <?= $isPlat ? 'plan-platinum' : ($key === 'gold' ? 'plan-gold' : '') ?>">
                        <div class="card-header text-center py-3 <?= $planTheme[$key]['header'] ?> text-white" style="border:0;">
                            <span class="badge badge-dark mt-1 mb-1"><i class="fas fa-<?= $planTheme[$key]['icon'] ?> mr-1"></i><?= $planTheme[$key]['tag'] ?></span>
                            <h3 class="m-0 font-weight-bold"><?= ucfirst($key) ?></h3>
                        </div>
                        <div class="card-body d-flex flex-column p-0">
                            <!-- Pricing Area -->
                            <div class="px-4 pt-4 pb-2 text-center">
                                <div class="d-flex align-items-baseline justify-content-center">
                                    <span class="h2 mb-0 text-muted"><?= esc($p['currency'] ?? '$') ?></span>
                                    <span class="display-5 font-weight-bold text-dark price-monthly"><?= number_format((int)($p['price_monthly_cents'] ?? 0) / 100, 2) ?></span>
                                    <span class="text-muted ml-1">/ month</span>
                                </div>
                                <div class="text-center">
                                    <span class="text-success font-weight-bold price-yearly" style="display:none;">
                                        <?= esc($p['currency'] ?? '$') ?> <?= number_format((int)($p['price_yearly_cents'] ?? 0) / 100, 2) ?> / year
                                    </span>
                                </div>
                                <?php if (($p['price_yearly_cents'] ?? 0) > 0 && ($p['price_monthly_cents'] ?? 0) > 0): ?>
                                <div class="text-center small text-muted mt-1">
                                    <span class="save-badge">or <?= esc($p['currency'] ?? '$') ?> <?= number_format((int)$p['price_yearly_cents'] / 100, 2) ?> / year — save <?= round(100 - ((int)$p['price_yearly_cents'] / 12 / (int)$p['price_monthly_cents'] * 100)) ?>%</span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- List Group Features -->
                            <?php
                            $featuresRaw = $p['features'] ?? [];
                            if (is_string($featuresRaw)) {
                                $featuresRaw = json_decode($featuresRaw, true) ?: [];
                            }
                            $hwProfile = $featuresRaw['hardware_profile'] ?? ($key === 'platinum' ? 'all' : ($key === 'gold' ? 'advanced' : 'basic'));
                            $swProfile = $featuresRaw['software_profile'] ?? ($key === 'platinum' ? 'all' : ($key === 'gold' ? 'advanced' : 'basic'));

                            $fcmGroups = $featuresRaw['fcm_groups'] ?? [];
                            if (empty($fcmGroups)) {
                                if ($key === 'free') $fcmGroups = ['core'];
                                elseif ($key === 'gold') $fcmGroups = ['core', 'advanced'];
                                elseif ($key === 'platinum') $fcmGroups = ['core', 'advanced', 'deep'];
                            }

                            $algos = $p['ml_algorithms'] ?? [];
                            if (is_string($algos)) {
                                $decoded = json_decode($algos, true);
                                $algos = is_array($decoded) ? $decoded : [];
                            }
                            if (empty($algos)) {
                                if ($key === 'free') $algos = ['core'];
                                elseif ($key === 'gold') $algos = ['core', 'advanced'];
                                elseif ($key === 'platinum') $algos = ['core', 'advanced', 'deep'];
                            }

                            $supportLabel = ($p['support_tier'] ?? 'standard') === 'priority' ? 'Priority 24/7' : 'Standard';
                            $wellbeingDays = (int)($featuresRaw['wellbeing_summary_days'] ?? ($key === 'platinum' ? 365 : ($key === 'gold' ? 7 : 0)));
                            $wellbeingLabel = $wellbeingDays > 0 ? "{$wellbeingDays} days summary" : 'Not included';

                            $alertsEmail = !empty($featuresRaw['alert_email']) || $key === 'platinum' || $key === 'gold';
                            $alertsPush = !empty($featuresRaw['alert_push']) || $key === 'platinum';
                            $alertsLabel = ($alertsEmail && $alertsPush) ? 'Email + Push' : ($alertsEmail ? 'Email Only' : 'None');

                            $standardFeaturesList = [
                                'risk_score' => 'Device Risk Score',
                                'geofencing' => 'Location Safety',
                                'forensic_export' => 'Forensic Export',
                                'wellbeing' => 'Wellbeing Insights',
                                'smart_timeline' => 'Smart Timeline',
                                'correlation' => 'Correlation Engine',
                                'care_plan' => 'Care Plans'
                            ];
                            ?>
                            <ul class="list-group list-group-flush text-sm" style="background: transparent;">
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-4 bg-transparent border-light">
                                    <span><i class="fas fa-mobile-alt text-info mr-2"></i>Max Devices</span>
                                    <span class="badge badge-pill badge-info px-3 py-1 font-weight-bold"><?= (int)($p['max_devices'] ?? 1) ?> device<?= (int)($p['max_devices'] ?? 1) > 1 ? 's' : '' ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-4 bg-transparent border-light">
                                    <span><i class="fas fa-history text-warning mr-2"></i>Retention Window</span>
                                    <span class="badge badge-pill badge-warning px-3 py-1 font-weight-bold"><?= (int)($p['history_days'] ?? 10) >= 365 ? 'Full' : ($p['history_days'] ?? 10) . ' days' ?></span>
                                </li>
                                <li class="list-group-item py-3 px-4 bg-transparent border-light">
                                    <span class="d-block mb-2 font-weight-bold text-muted small text-uppercase"><i class="fas fa-cubes text-indigo mr-1"></i>Device Telemetry Profiles</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge bg-indigo text-white mr-1 mb-1 px-2 py-1">
                                            HW: <?= $hwProfile === 'all' ? 'All (Platinum)' : ($hwProfile === 'advanced' ? 'Advanced (Gold)' : 'Basic (Free)') ?>
                                        </span>
                                        <span class="badge bg-navy text-white mr-1 mb-1 px-2 py-1">
                                            SW: <?= $swProfile === 'all' ? 'All (Platinum)' : ($swProfile === 'advanced' ? 'Advanced (Gold)' : 'Basic (Free)') ?>
                                        </span>
                                    </div>
                                </li>
                                <li class="list-group-item py-3 px-4 bg-transparent border-light">
                                    <span class="d-block mb-2 font-weight-bold text-muted small text-uppercase"><i class="fas fa-paper-plane text-success mr-1"></i>Remote Action Commands</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge <?= in_array('core', $fcmGroups, true) ? 'badge-success' : 'badge-light border text-muted' ?> mr-1 mb-1 px-2 py-1">Core (Free)</span>
                                        <span class="badge <?= in_array('advanced', $fcmGroups, true) ? 'badge-warning' : 'badge-light border text-muted' ?> mr-1 mb-1 px-2 py-1">Advanced (Gold)</span>
                                        <span class="badge <?= in_array('deep', $fcmGroups, true) ? 'badge-danger' : 'badge-light border text-muted' ?> mr-1 mb-1 px-2 py-1">Deep (Platinum)</span>
                                    </div>
                                </li>
                                <li class="list-group-item py-3 px-4 bg-transparent border-light">
                                    <span class="d-block mb-2 font-weight-bold text-muted small text-uppercase"><i class="fas fa-brain text-purple mr-1"></i>Machine Learning Engine</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge <?= in_array('core', $algos, true) ? 'badge-success' : 'badge-light border text-muted' ?> mr-1 mb-1 px-2 py-1">Core</span>
                                        <span class="badge <?= in_array('advanced', $algos, true) ? 'badge-warning' : 'badge-light border text-muted' ?> mr-1 mb-1 px-2 py-1">Advanced</span>
                                        <span class="badge <?= in_array('deep', $algos, true) ? 'badge-danger' : 'badge-light border text-muted' ?> mr-1 mb-1 px-2 py-1">Deep</span>
                                    </div>
                                </li>
                                <li class="list-group-item py-3 px-4 bg-transparent border-light">
                                    <span class="d-block mb-2 font-weight-bold text-muted small text-uppercase"><i class="fas fa-star text-warning mr-1"></i>Included Analytics Features</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($standardFeaturesList as $fk => $flabel): 
                                            $enabled = !empty($featuresRaw[$fk]) || ($key === 'platinum') || ($key === 'gold' && in_array($fk, ['risk_score', 'geofencing', 'forensic_export', 'smart_timeline'])) || ($key === 'free' && in_array($fk, ['forensic_export', 'smart_timeline']));
                                        ?>
                                        <span class="badge <?= $enabled ? 'badge-success' : 'badge-light border text-muted' ?> mr-1 mb-1 px-2 py-1" style="font-size: 0.75rem;">
                                            <i class="fas fa-<?= $enabled ? 'check-circle' : 'times-circle' ?> mr-1"></i><?= $flabel ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-4 bg-transparent border-light">
                                    <span><i class="fas fa-bell text-danger mr-2"></i>Security Alerts</span>
                                    <span class="badge badge-pill badge-light border px-2 py-1"><?= $alertsLabel ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-4 bg-transparent border-light">
                                    <span><i class="fas fa-heartbeat text-pink mr-2"></i>Wellbeing History</span>
                                    <span class="badge badge-pill badge-light border px-2 py-1"><?= $wellbeingLabel ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-4 bg-transparent border-light border-bottom-0 mb-3">
                                    <span><i class="fas fa-headset text-primary mr-2"></i>Support Response</span>
                                    <span class="badge badge-pill badge-light border px-2 py-1"><?= $supportLabel ?></span>
                                </li>
                            </ul>

                            <div class="p-4 pt-0">
                                <a href="<?= url_to('register') ?>" class="btn <?= $planTheme[$key]['btn'] ?> btn-lg btn-block font-weight-bold shadow-sm">
                                    <?= $key === 'free' ? 'Get Started Free' : 'Choose ' . ucfirst($key) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Upgrade note -->
            <div class="alert alert-info mt-4 shadow-sm">
                <i class="fas fa-info-circle mr-2"></i>
                Free accounts keep their <?= $history($free, 'free') ?>-day window until you upgrade. Gold unlocks <?= $history($gold, 'gold') ?> days and core ML analysis; Platinum unlocks the full
                intelligence suite, deepest history, and forensic exports. Admin &amp; Superadmin accounts are unaffected by these plans.
            </div>

            <!-- Pesapal Backing Info -->
            <div class="card mt-4 shadow-sm">
                <div class="card-body bg-light rounded p-4 text-center">
                    <h5 class="font-weight-bold mb-3"><i class="fas fa-shield-alt mr-2 text-success"></i>Secure Payments Powered by Pesapal</h5>
                    <p class="text-muted mb-3">All subscriptions are billed securely via <strong>Pesapal</strong>, a fully PCI-DSS certified payment aggregator. We support:</p>
                    <div class="row justify-content-center text-dark">
                        <div class="col-sm-3 mb-2">
                            <i class="fas fa-mobile-alt fa-2x text-success d-block mb-1"></i>
                            <strong>Safaricom M-Pesa</strong><br><small class="text-muted">STK Push / Paybill</small>
                        </div>
                        <div class="col-sm-3 mb-2">
                            <i class="fas fa-mobile-alt fa-2x text-primary d-block mb-1"></i>
                            <strong>Airtel Money</strong><br><small class="text-muted">Mobile Wallet Transfer</small>
                        </div>
                        <div class="col-sm-3 mb-2">
                            <i class="far fa-credit-card fa-2x text-info d-block mb-1"></i>
                            <strong>Debit & Credit Cards</strong><br><small class="text-muted">Visa / Mastercard</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<style>
.bg-gradient-purple { background: linear-gradient(45deg, #6f42c1, #9058e6) !important; }
.bg-gradient-pink { background: linear-gradient(45deg, #e83e8c, #ffd700) !important; }
.btn-gradient-secondary { background: linear-gradient(45deg, #6c757d, #adb5bd); color: #fff; border: none; }
.btn-gradient-pink { background: linear-gradient(45deg, #e83e8c, #ffd700); color: #fff; border: none; }
.btn-gradient-purple { background: linear-gradient(45deg, #6f42c1, #9058e6); color: #fff; border: none; }
.btn-gradient-secondary:hover, .btn-gradient-pink:hover, .btn-gradient-purple:hover { color: #fff; opacity: .9; }
.plan-platinum .card-header { border-radius:0; background: linear-gradient(45deg,#6f42c1,#9058e6) !important; }
.plan-gold .card-header { border-radius:0; background: linear-gradient(45deg,#e83e8c,#ffd700) !important; }
.plan-card { transition: transform .15s ease, box-shadow .15s ease; }
.plan-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,.12) !important; }
.save-badge { background:#e8f5e9; color:#1b5e20; padding:.15rem .5rem; border-radius:.35rem; }
.gap-1 { gap: .25rem; }
.gap-2 { gap: .5rem; }
.bg-indigo { background-color: #6610f2 !important; }
.bg-navy { background-color: #001f3f !important; }
</style>

<script>
function getBilling() {
    var checked = document.querySelector('input[name="billing"]:checked');
    return checked ? checked.value : 'monthly';
}

function updateCardPrices() {
    var yearly = getBilling() === 'yearly';
    document.querySelectorAll('.price-monthly').forEach(function (el) { el.style.display = yearly ? 'none' : ''; });
    document.querySelectorAll('.price-yearly').forEach(function (el) { el.style.display = yearly ? '' : 'none'; });
}

document.querySelectorAll('input[name="billing"]').forEach(function (r) {
    r.addEventListener('change', updateCardPrices);
});

document.querySelectorAll('#lbl-monthly, #lbl-yearly').forEach(function (lbl) {
    lbl.addEventListener('click', function () {
        updateCardPrices();
    });
});
</script>
