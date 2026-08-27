<?php
/* --------------------------------------------------------------
   Data supplied by the gate / controller
   -------------------------------------------------------------- */
$feature       = esc($feature ?? 'premium features');
$upgradePlans  = $upgradePlans ?? [];
$currentPlan   = strtolower((string) ($current_plan ?? 'free'));
$redirectTo    = $redirect_to ?? base_url('home');

/* --------------------------------------------------------------
   Load plan details (price, feature list) straight from the DB so
   whatever the superadmin has configured is always reflected here.
   -------------------------------------------------------------- */
$planModel = new \App\Models\PlanModel();

$featureLabels = [
    'wellbeing'          => 'Digital Wellbeing Analytics',
    'geofencing'         => 'Geo-Fencing & Location Intelligence',
    'risk_score'         => 'Anomaly Detection (Risk Scoring)',
    'correlation'        => 'Correlation Engine',
    'care_plan'          => 'Risk Score & Care Plans',
    'smart_timeline'     => 'Unified Smart Timeline',
    'forensic_export'    => 'Forensic Export & Reports',
    'push_notifications' => 'Real-time Push Alerts',
];

// Short descriptor for each feature, shown under its name in the comparison table.
$featureDescs = [
    'wellbeing'          => 'Usage trends & wellbeing summaries for all monitored activity.',
    'geofencing'         => 'Real-time geofences and location intelligence with breach alerts.',
    'risk_score'         => 'Anomaly detection with risk scoring across every data source.',
    'correlation'        => 'Cross-category engine linking contacts, calls, SMS and location.',
    'care_plan'          => 'Personalised risk score, trends and step-by-step care plans.',
    'smart_timeline'     => 'Unified smart timeline of all device events in one view.',
    'forensic_export'    => 'Forensic-grade exports and evidence-ready reports.',
    'push_notifications' => 'Instant push alerts for critical or suspicious events.',
];

$planTheme = [
    'gold'     => ['header' => 'bg-gradient-pink',   'btn' => 'btn-gradient-pink',   'text' => 'text-pink'],
    'platinum' => ['header' => 'bg-gradient-purple', 'btn' => 'btn-gradient-purple', 'text' => 'text-purple'],
];

$currentPlanName = $currentPlan !== '' ? ucfirst($currentPlan) : 'Free';

// Current plan's own DB stats (for the banner).
$currentVer   = $currentPlan !== '' ? $planModel->getCurrentVersion($currentPlan) : null;
$currentDevices     = $currentVer['max_devices'] ?? 1;
$currentHistoryDays = (int) ($currentVer['history_days'] ?? 30);
$currentSupport     = $currentVer['support_tier'] ?? 'standard';

$algoTierLabels = [
    'core'     => 'Core Algorithms',
    'advanced' => 'AdvancedController Algorithms',
    'deep'     => 'Deep-Learning Models',
];

// Normalise a feature / algorithm column to an array.
$asArr = static function ($v) {
    return is_array($v) ? $v : [];
};

/* Build card data for every upgrade plan + the user's current plan. */
$planDetails = [];
$matrix      = [];   // featureKey => [ planSlug => true/false ]
$allFeatures = [];
$allTiers    = [];

$plansToShow = $upgradePlans;
if ($currentPlan !== '' && !in_array($currentPlan, $plansToShow, true)) {
    $plansToShow = array_merge([$currentPlan], $plansToShow);
}

foreach ($plansToShow as $planKey) {
    $ver = $planModel->getCurrentVersion($planKey);
    if (!$ver) {
        continue;
    }

    $features = $asArr($ver['features'] ?? []);
    $tiers    = $asArr($ver['ml_algorithms'] ?? []);

    foreach ($features as $k => $enabled) {
        $allFeatures[$k] = $featureLabels[$k] ?? ucwords(str_replace('_', ' ', $k));
    }
    foreach ($tiers as $t) {
        $allTiers[$t] = $algoTierLabels[$t] ?? ucwords(str_replace('_', ' ', (string) $t));
    }

    foreach ($features as $k => $enabled) {
        $matrix[$k][$planKey] = $enabled === true;
    }

    $on = [];
    foreach ($features as $k => $enabled) {
        if ($enabled === true) {
            $on[] = $featureLabels[$k] ?? ucwords(str_replace('_', ' ', $k));
        }
    }

    $planDetails[$planKey] = [
        'name'            => ucfirst($planKey),
        'slug'            => $planKey,
        'monthly_cents'   => (int) ($ver['price_monthly_cents'] ?? 0),
        'yearly_cents'    => (int) ($ver['price_yearly_cents'] ?? 0),
        'currency'        => $ver['currency'] ?? 'USD',
        'features'        => $on,
        'max_devices'     => (int) ($ver['max_devices'] ?? 1),
        'history_days'    => (int) ($ver['history_days'] ?? 7),
        'algorithms'      => $tiers,
        'support'         => $ver['support_tier'] ?? 'standard',
        'is_current'      => $planKey === $currentPlan,
    ];
}

// Comparison-table columns in a fixed, logical order (gold then platinum),
// showing only plans that are present on the page.
$tablePlans = [];
foreach (['gold', 'platinum'] as $tk) {
    if (isset($planDetails[$tk])) {
        $tablePlans[$tk] = $planDetails[$tk];
    }
}
// If the current plan is something else (e.g. an active legacy plan), append it.
foreach ($planDetails as $pk => $p) {
    if (!isset($tablePlans[$pk])) {
        $tablePlans[$pk] = $p;
    }
}

// Keep the DB order deterministic (insertion order of features).
$planSlugs = array_keys($planDetails);

// Cards show ONLY the upgrade options (never the user's current plan).
$upgradeCards = [];
foreach ($upgradePlans as $key) {
    if (isset($planDetails[$key])) {
        $upgradeCards[$key] = $planDetails[$key];
    }
}
?>

<?php
$loggedIn     = function_exists('auth') && auth()->loggedIn();
$isSuperAdmin = $loggedIn && auth()->user() && auth()->user()->inGroup('superadmin');
$isAdmin      = $loggedIn && auth()->user() && auth()->user()->can('admin.access');
$code  = '403';
$title = 'Upgrade Required';
$icon  = 'fa-crown';
$color = 'warning';
?>
<?php if ($loggedIn): ?>
<?= view('headers_footers/head_users') ?>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<div class="wrapper">
    <?= view($isSuperAdmin ? 'headers_footers/sidebar_superadmin'
            : ($isAdmin ? 'headers_footers/sidebar_admin'
            : 'headers_footers/sidebar_users'), [
        'user_info'            => auth()->user()->toArray(),
        'sidebar_user_devices' => [],
        'active_device_id'     => null,
        'user_token'           => [],
        'pag'                  => $pag ?? null,
        'sub_pag'              => $sub_pag ?? null,
    ]) ?>
    <div class="content-wrapper">
<?php else: ?>
<?= view('headers_footers/head_landing') ?>
<?php endif; ?>

<div class="container-fluid upgrade-wrap">
    <div class="py-4">

        <!-- ══════════ HERO ══════════ -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning text-white mb-3"
                 style="width:88px;height:88px;font-size:2.4rem;box-shadow:0 8px 22px rgba(0,0,0,.18);">
                <i class="fas <?= $icon ?>"></i>
            </div>
            <h1 class="display-4 font-weight-bold text-dark mb-1">Unlock <?= $feature ?></h1>
            <p class="lead text-muted mb-0">
                Your current plan is <span class="badge badge-dark badge-pill text-uppercase px-3"><?= esc($currentPlan ?: 'free') ?></span>.
                Choose a plan below to upgrade and instantly enable <?= $feature ?>.
            </p>
        </div>

        <!-- Current plan banner -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius:.9rem;background:linear-gradient(135deg,#f8f9fa,#e9ecef);">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between px-4 py-3">
                <div class="d-flex align-items-center">
                    <div class="mr-3 d-flex align-items-center justify-content-center rounded-circle <?= $currentPlan === 'platinum' ? 'bg-gradient-purple' : 'bg-gradient-pink' ?> text-white font-weight-bold"
                         style="width:52px;height:52px;font-size:1.1rem;">
                        <i class="fas <?= $currentPlan === 'platinum' ? 'fa-crown' : ($currentPlan === 'gold' ? 'fa-gem' : 'fa-user-circle') ?>"></i>
                    </div>
                    <div>
                        <div class="small text-uppercase font-weight-bold text-muted">Your current plan</div>
                        <div class="h4 mb-0 font-weight-bold text-dark text-uppercase"><?= esc($currentPlanName) ?></div>
                    </div>
                </div>
                <div class="text-md-right">
                    <span class="badge badge-light border px-3 py-2"><i class="fas fa-mobile-alt text-secondary mr-1"></i> <?= esc($currentDevices) ?> device<?= $currentDevices > 1 ? 's' : '' ?></span>
                    <span class="badge badge-light border px-3 py-2"><i class="fas fa-history text-secondary mr-1"></i> <?= $currentHistoryDays >= 365 ? 'Full history' : $currentHistoryDays . ' days' ?></span>
                    <span class="badge badge-light border px-3 py-2"><i class="fas fa-headset text-secondary mr-1"></i> <?= ucfirst($currentSupport) ?> support</span>
                </div>
            </div>
        </div>

        <?php if (empty($planDetails)): ?>
        <div class="alert alert-info text-center shadow-sm">
            <i class="fas fa-info-circle mr-1"></i> No upgrade plans are currently available.
            <a class="font-weight-bold" href="<?= base_url('pricing') ?>">View pricing</a>.
        </div>
        <?php else: ?>

        <!-- ══════════ BILLING TOGGLE ══════════ -->
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

        <!-- ══════════ PLAN CARDS (upgrade options only) ══════════ -->
        <?php
        $cardCol = count($upgradeCards) >= 3 ? 'col-xl-4' : (count($upgradeCards) === 2 ? 'col-xl-6' : 'col-xl-6 mx-auto');
        ?>
        <div class="row justify-content-center g-4">
            <?php foreach ($upgradeCards as $key => $p): $isPlat = $key === 'platinum'; ?>
            <div class="col-12 col-md-6 <?= $cardCol ?> mb-4">
                <div class="card h-100 shadow-sm border-0 plan-card <?= $isPlat ? 'plan-platinum' : 'plan-gold' ?> <?= $p['is_current'] ? 'plan-current' : '' ?>"
                     data-plan="<?= esc($key) ?>">
                    <div class="card-header text-center py-3 <?= $planTheme[$isPlat ? 'platinum' : 'gold']['header'] ?> text-white" style="border:0;">
                        <?php if ($p['is_current']): ?><span class="badge badge-light mb-1"><i class="fas fa-check mr-1"></i>Your current plan</span><?php endif; ?>
                        <h3 class="m-0 font-weight-bold"><?= esc($p['name']) ?></h3>
                        <?php if ($isPlat): ?><span class="badge badge-dark mt-1"><i class="fas fa-crown mr-1"></i>MOST ADVANCED</span><?php endif; ?>
                        <?php if ($key === 'gold'): ?><span class="badge badge-light mt-1">POPULAR</span><?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column p-0">

                        <!-- Price -->
                        <div class="px-4 pt-4 pb-2">
                            <div class="d-flex align-items-baseline justify-content-center">
                                <span class="h2 mb-0 text-muted"><?= esc($p['currency']) ?></span>
                                <span class="display-5 font-weight-bold text-dark price-monthly"><?= number_format($p['monthly_cents'] / 100, 2) ?></span>
                                <span class="text-muted ml-1">/ month</span>
                            </div>
                            <div class="text-center">
                                <span class="text-success font-weight-bold price-yearly" style="display:none;">
                                    <?= esc($p['currency']) ?> <?= number_format($p['yearly_cents'] / 100, 2) ?> / year
                                </span>
                            </div>
                            <?php if ($p['yearly_cents'] > 0 && $p['monthly_cents'] > 0): ?>
                            <div class="text-center small text-muted">
                                <span class="save-badge">or <?= esc($p['currency']) ?> <?= number_format($p['yearly_cents'] / 100, 2) ?> / year — save <?= round(100 - ($p['yearly_cents'] / 12 / $p['monthly_cents'] * 100)) ?>%</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Highlights -->
                        <div class="px-4 py-3 flex-grow-1">
                            <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                                <span class="badge badge-light border px-3 py-2"><i class="fas fa-mobile-alt text-secondary mr-1"></i><strong><?= $p['max_devices'] ?></strong> device<?= $p['max_devices'] > 1 ? 's' : '' ?></span>
                                <span class="badge badge-light border px-3 py-2"><i class="fas fa-history text-secondary mr-1"></i><strong><?= $p['history_days'] >= 365 ? 'Full' : $p['history_days'] ?></strong> days history</span>
                                <span class="badge badge-light border px-3 py-2"><i class="fas fa-headset text-secondary mr-1"></i><?= ucfirst($p['support']) ?> support</span>
                            </div>

                            <?php if (!empty($p['algorithms'])): ?>
                            <div class="mb-2">
                                <div class="small text-muted text-uppercase font-weight-bold mb-1"><i class="fas fa-brain mr-1"></i>Included Algorithms</div>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach ($p['algorithms'] as $t): ?>
                                    <span class="badge badge-<?= $t === 'deep' ? 'dark' : ($t === 'advanced' ? 'warning' : 'primary') ?> px-2 py-1"><?= esc($algoTierLabels[$t] ?? ucfirst($t)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($p['features'])): ?>
                            <ul class="list-unstyled mb-0 plan-features">
                                <?php foreach ($p['features'] as $f): ?>
                                <li class="mb-1"><i class="fas fa-check text-success mr-2"></i><?= esc($f) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>

                        <!-- CTA -->
                        <div class="p-4 pt-0">
                            <?php if ($p['is_current']): ?>
                            <button type="button" class="btn btn-light btn-lg btn-block font-weight-bold" disabled>
                                <i class="fas fa-check mr-2"></i> Current Plan
                            </button>
                            <?php else: ?>
                            <button type="button" class="btn <?= $planTheme[$isPlat ? 'platinum' : 'gold']['btn'] ?> btn-lg btn-block font-weight-bold shadow-sm btn-upgrade"
                                    data-plan="<?= esc($key) ?>" data-name="<?= esc($p['name']) ?>"
                                    data-monthly="<?= number_format($p['monthly_cents'] / 100, 2) ?>"
                                    data-yearly="<?= number_format($p['yearly_cents'] / 100, 2) ?>"
                                    data-currency="<?= esc($p['currency']) ?>">
                                <i class="fas fa-arrow-up mr-2"></i> Upgrade to <?= esc($p['name']) ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ══════════ FEATURE COMPARISON TABLE ══════════ -->
        <div class="card shadow-sm border-0 mt-4 overflow-hidden">
            <div class="card-header border-0 px-4 py-3" style="background:linear-gradient(90deg,#6f42c1,#9058e6,#e83e8c);">
                <h5 class="card-title mb-0 font-weight-bold text-white">
                    <i class="fas fa-table mr-2"></i> Plan Comparison
                    <span class="small font-weight-light d-block d-md-inline ml-md-2">— what every plan gives you</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover comparison-table mb-0 align-middle">
                        <thead class="thead-dark">
                            <tr>
                                <th class="px-4" style="min-width:250px;">Feature</th>
                                <?php foreach ($tablePlans as $tpk => $p):
                                    $th = $planTheme[$tpk]['header'] ?? 'bg-dark';
                                    $isCur = $p['is_current']; ?>
                                <th class="text-center text-uppercase px-3">
                                    <span class="d-inline-block px-3 py-2 rounded <?= $th ?> text-white font-weight-bold">
                                        <?= esc($p['name']) ?>
                                        <?php if ($isCur): ?><i class="fas fa-check-circle ml-1" title="Your current plan"></i><?php endif; ?>
                                    </span>
                                    <?php if ($isCur): ?><div class="small text-warning font-weight-bold mt-1"><i class="fas fa-star mr-1"></i>Your current plan</div><?php endif; ?>
                                </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $dataRows = [
                                ['label' => 'Monthly price',  'key' => 'price_m', 'descr' => 'What you pay per month for this plan.'],
                                ['label' => 'Yearly price',   'key' => 'price_y', 'descr' => 'Pay once a year and save versus monthly billing.'],
                                ['label' => 'Devices',        'key' => 'devices', 'descr' => 'How many monitored devices are included.'],
                                ['label' => 'History',        'key' => 'history', 'descr' => 'How far back your monitoring history is retained.'],
                                ['label' => 'Support',        'key' => 'support', 'descr' => 'The level of support you get when you need help.'],
                                ['label' => 'Algorithms',     'key' => 'algos',   'descr' => 'The detection engines and models available to you.'],
                            ];
                            ?>
                            <?php foreach ($dataRows as $dr): ?>
                            <tr>
                                <td class="px-4">
                                    <div class="font-weight-bold text-dark"><?= $dr['label'] ?></div>
                                    <div class="small text-muted"><?= $dr['descr'] ?></div>
                                </td>
                                <?php foreach ($tablePlans as $tpk => $p): ?>
                                <td class="text-center px-3">
                                    <?php if ($dr['key'] === 'price_m'):
                                        echo $p['monthly_cents'] > 0
                                            ? '<span class="font-weight-bold">' . esc($p['currency']) . ' ' . number_format($p['monthly_cents'] / 100, 2) . '</span><div class="small text-success">per month</div>'
                                            : '<span class="text-success font-weight-bold">Free</span>';
                                    elseif ($dr['key'] === 'price_y'):
                                        echo $p['yearly_cents'] > 0
                                            ? '<span class="font-weight-bold">' . esc($p['currency']) . ' ' . number_format($p['yearly_cents'] / 100, 2) . '</span><div class="small text-success">per year</div>'
                                            : '<span class="text-success font-weight-bold">Free</span>';
                                    elseif ($dr['key'] === 'devices'):
                                        echo '<span class="font-weight-bold">' . $p['max_devices'] . '</span>';
                                    elseif ($dr['key'] === 'history'):
                                        echo '<span class="font-weight-bold">' . ($p['history_days'] >= 365 ? 'Full' : $p['history_days'] . ' days') . '</span>';
                                    elseif ($dr['key'] === 'support'):
                                        echo '<span class="badge badge-light border px-3 py-1">' . ucfirst($p['support']) . '</span>';
                                    elseif ($dr['key'] === 'algos'):
                                        if (empty($p['algorithms'])):
                                            echo '<span class="text-muted">—</span>';
                                        else:
                                            echo '<div class="d-inline-flex flex-column gap-1">';
                                            foreach ($p['algorithms'] as $t):
                                                echo '<span class="badge ' . ($t === 'deep' ? 'badge-dark' : ($t === 'advanced' ? 'badge-warning' : 'badge-primary')) . '">' . esc($algoTierLabels[$t] ?? ucfirst($t)) . '</span>';
                                            endforeach;
                                            echo '</div>';
                                        endif;
                                    endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>

                            <!-- Capabilities -->
                            <tr class="bg-light">
                                <td colspan="<?= count($tablePlans) + 1 ?>" class="px-4">
                                    <span class="small text-muted font-weight-bold text-uppercase"><i class="fas fa-cubes mr-1"></i>Capabilities</span>
                                </td>
                            </tr>
                            <?php foreach ($allFeatures as $fk => $flabel): ?>
                            <tr>
                                <td class="px-4">
                                    <div class="font-weight-bold text-dark"><?= esc($flabel) ?></div>
                                    <div class="small text-muted"><?= esc($featureDescs[$fk] ?? '') ?></div>
                                </td>
                                <?php foreach ($tablePlans as $tpk => $p): ?>
                                <td class="text-center px-3">
                                    <?php if (!empty($matrix[$fk][$tpk])): ?>
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                    <?php else: ?>
                                        <i class="fas fa-times-circle text-muted fa-lg"></i>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php endif; ?>

        <!-- ══════════ FOOTER ACTIONS ══════════ -->
        <div class="text-center mt-4">
            <a href="<?= base_url('pricing') ?>" class="btn btn-outline-secondary mr-2">
                <i class="fas fa-th-large mr-1"></i> View Full Pricing
            </a>
            <a href="<?= esc($redirectTo) ?>" class="btn btn-outline-dark">
                <i class="fas fa-arrow-left mr-1"></i> Go Back
            </a>
        </div>
    </div>
</div>

<!-- ══════════════════ PAYMENT MODAL ══════════════════ -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius:.9rem;">
      <div class="modal-header bg-dark text-white" style="border:0;">
        <h5 class="modal-title font-weight-bold"><i class="fas fa-lock mr-2 text-warning"></i>Upgrade to <span id="pay-plan-name">Platinum</span></h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body p-4">

        <!-- Summary -->
        <div class="d-flex justify-content-between align-items-center bg-light rounded p-3 mb-4">
            <div>
                <div class="small text-muted">Selected plan</div>
                <div class="font-weight-bold text-dark" id="pay-summary-plan">Platinum</div>
            </div>
            <div class="text-right">
                <div class="small text-muted">Amount due</div>
                <div class="font-weight-bold text-dark h4 mb-0"><span id="pay-currency">$</span><span id="pay-amount">99.00</span></div>
            </div>
        </div>

        <!-- Payment methods tabs -->
        <ul class="nav nav-pills nav-fill mb-3" id="pay-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#pay-mpesa" data-method="mpesa"><i class="fas fa-mobile-alt mr-1"></i>Safaricom M-Pesa</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#pay-airtel" data-method="airtel"><i class="fas fa-mobile-alt mr-1"></i>Airtel Money</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#pay-card" data-method="card"><i class="far fa-credit-card mr-1"></i>Debit/Credit Card</a></li>
        </ul>

        <div class="tab-content" id="pay-tabs-content">
            <!-- M-Pesa -->
            <div class="tab-pane fade show active" id="pay-mpesa" role="tabpanel">
                <div class="text-center py-4">
                    <i class="fas fa-mobile-alt fa-3x text-success mb-3"></i>
                    <p class="text-dark font-weight-bold">Pay via Safaricom M-Pesa</p>
                    <p class="text-muted small">You will be redirected to complete your payment. Enter your phone number on Pesapal's secure checkout page to receive an instant **STK Push PIN Prompt** on your phone, or use the provided Paybill/Till number instructions.</p>
                </div>
            </div>

            <!-- Airtel Money -->
            <div class="tab-pane fade" id="pay-airtel" role="tabpanel">
                <div class="text-center py-4">
                    <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                    <p class="text-dark font-weight-bold">Pay via Airtel Money</p>
                    <p class="text-muted small">You will be redirected to Pesapal's secure page where you can enter your mobile number and approve the wallet transaction transfer instantly.</p>
                </div>
            </div>

            <!-- Card -->
            <div class="tab-pane fade" id="pay-card" role="tabpanel">
                <div class="text-center py-4">
                    <i class="far fa-credit-card fa-3x text-info mb-3"></i>
                    <p class="text-dark font-weight-bold">Debit & Credit Cards (Visa / Mastercard)</p>
                    <p class="text-muted small">Securely process payments using Visa, Mastercard, or American Express issued by your bank (KCB, Equity, NCBA, Co-operative Bank, or any international bank). You will be redirected to enter your card details securely.</p>
                </div>
            </div>
        </div>

        <div class="alert alert-info text-center small mt-3 mb-0">
            <i class="fas fa-shield-alt text-success mr-1"></i> Payments are fully secured and processed by <strong>Pesapal (PCI-DSS Certified Gateway)</strong>. Eaves Droid does not store or process your financial card info or PINs.
        </div>



      </div>
      <div class="modal-footer" style="border:0;">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning btn-lg font-weight-bold" id="btn-pay">
            <i class="fas fa-lock mr-2"></i>Pay <span id="pay-btn-amount">$99.00</span>
        </button>
      </div>
    </div>
  </div>
</div>

<?php if ($loggedIn): ?>
    </div>
    <?= view('headers_footers/footer_users') ?>
<?php else: ?>
    <?= view('headers_footers/footer_landing') ?>
<?php endif; ?>

<style>
.upgrade-wrap { max-width: 1800px; }
.bg-gradient-pink { background: linear-gradient(45deg,#e83e8c,#ffd700) !important; }
.bg-gradient-purple { background: linear-gradient(45deg,#6f42c1,#9058e6) !important; }
.btn-gradient-pink { background: linear-gradient(45deg,#e83e8c,#ffd700); color:#fff; border:none; }
.btn-gradient-purple { background: linear-gradient(45deg,#6f42c1,#9058e6); color:#fff; border:none; }
.btn-gradient-pink:hover, .btn-gradient-purple:hover { color:#fff; opacity:.9; }
.plan-platinum .card-header { border-radius:0; background: linear-gradient(45deg,#6f42c1,#9058e6) !important; }
.plan-gold .card-header { border-radius:0; background: linear-gradient(45deg,#e83e8c,#ffd700) !important; }
.plan-card { transition: transform .15s ease, box-shadow .15s ease; }
.plan-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,.12) !important; }
.plan-current { outline: 2px solid #28a745; }
.plan-features { column-count: 2; column-gap: 1.5rem; font-size: .9rem; }
.plan-features li { break-inside: avoid; }
.save-badge { background:#e8f5e9; color:#1b5e20; padding:.15rem .5rem; border-radius:.35rem; }
.comparison-table th, .comparison-table td { padding: .75rem .9rem; }
.gap-1 { gap: .25rem; }
.gap-2 { gap: .5rem; }
@media (max-width: 768px) {
    .plan-features { column-count: 1; }
}
</style>

<script>
var CSRF_TOKEN = '<?= csrf_hash() ?>';
var upgradeTarget = { plan: '', name: '', monthly: 0, yearly: 0, currency: '$' };

document.querySelectorAll('.btn-upgrade').forEach(function (btn) {
    btn.addEventListener('click', function () {
        upgradeTarget = {
            plan:     this.getAttribute('data-plan'),
            name:     this.getAttribute('data-name'),
            monthly:  parseFloat(this.getAttribute('data-monthly')),
            yearly:   parseFloat(this.getAttribute('data-yearly')),
            currency: this.getAttribute('data-currency')
        };
        refreshPaySummary();
        $('#paymentModal').modal('show');
    });
});

function getBilling() {
    var checked = document.querySelector('input[name="billing"]:checked');
    return checked ? checked.value : 'monthly';
}

function refreshPaySummary() {
    var billing = getBilling();
    var amount = billing === 'yearly' ? upgradeTarget.yearly : upgradeTarget.monthly;
    var cur = upgradeTarget.currency || '$';
    document.getElementById('pay-plan-name').textContent   = upgradeTarget.name;
    document.getElementById('pay-summary-plan').textContent = upgradeTarget.name + ' (' + billing + ')';
    document.getElementById('pay-currency').textContent     = cur + ' ';
    document.getElementById('pay-amount').textContent       = amount.toFixed(2);
    document.getElementById('pay-btn-amount').textContent   = cur + ' ' + amount.toFixed(2);
}

document.querySelectorAll('input[name="billing"]').forEach(function (r) {
    r.addEventListener('change', refreshPaySummary);
});

// Update card prices shown on the page when billing toggles.
// NOTE: bind to the radio inputs, not the labels (labels never fire 'change').
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
        // Labels don't fire 'change' — apply the toggle on click too (safety).
        updateCardPrices();
        refreshPaySummary();
    });
});

function currentMethod() {
    var active = document.querySelector('#pay-tabs .nav-link.active');
    return active ? active.getAttribute('data-method') : 'card';
}

document.getElementById('btn-pay').addEventListener('click', function () {
    var btn = this;
    var method = currentMethod();
    var payBtnAmountText = document.getElementById('pay-btn-amount').textContent;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Redirecting to secure checkout...';

    var payload = {
        plan: upgradeTarget.plan,
        billing: getBilling(),
        payment_method: method,
        redirect_to: '<?= esc($redirectTo, 'js') ?>'
    };

    fetch('<?= base_url('billing/checkout') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: JSON.stringify(payload)
    })
    .then(function (res) { return res.json().then(function (d) { return { ok: res.ok, d: d }; }); })
    .then(function (r) {
        if (r.ok && r.d.success && r.d.redirect_url) {
            window.location.href = r.d.redirect_url;
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock mr-2"></i>Pay <span id="pay-btn-amount">' + payBtnAmountText + '</span>';
            
            var errMsg = 'Could not initiate payment session. Please try again.';
            if (r.d && r.d.messages) {
                if (typeof r.d.messages === 'object' && r.d.messages.error) {
                    errMsg = r.d.messages.error;
                } else if (typeof r.d.messages === 'string') {
                    errMsg = r.d.messages;
                }
            }
            Swal.fire('Payment failed', errMsg, 'error');
        }
    })
    .catch(function (err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock mr-2"></i>Pay <span id="pay-btn-amount">' + payBtnAmountText + '</span>';
        Swal.fire('Error', 'Could not reach the server. Please try again.', 'error');
    });
});
</script>
