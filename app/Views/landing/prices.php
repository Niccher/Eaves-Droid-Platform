<?php
    $plans = $plans ?? [];
    $free = $plans['free'] ?? null;
    $gold = $plans['gold'] ?? null;
    $plat = $plans['platinum'] ?? null;

    $f = fn($p, $k) => ($p['features'][$k] ?? false);
    $money = fn($cents) => '$' . number_format(($cents ?? 0) / 100, 2);
    $hasAlgo = fn($p, $algo) => in_array($algo, $p['ml_algorithms'] ?? []);
    $alertLine = fn($p) => (($p['alert_email'] ?? 0) && ($p['alert_push'] ?? 0)) ? 'Email + push' : (($p['alert_email'] ?? 0) ? 'Email' : (($p['alert_push'] ?? 0) ? 'Push' : 'None'));
    $wellbeingLine = fn($p) => ($p['wellbeing_depth'] ?? '7') === 'all' ? 'All data' : ($p['wellbeing_depth'] ?? '7') . '-day summary';
    $supportLine = fn($p) => ($p['support_tier'] ?? 'standard') === 'priority' ? 'Priority 24/7' : 'Standard';
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
        <div class="container">
            <div class="row">

                <!-- Free Plan -->
                <div class="col-lg-4 mb-4">
                    <div class="card card-outline shadow-sm h-100 text-center border-primary">
                        <div class="card-header bg-gradient-secondary border-bottom-0">
                            <h3 class="card-title text-bold text-white" style="float:none"><i class="fas fa-star mr-2"></i>Free</h3>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h2 class="text-primary"><?= $free ? $money($free['price_monthly_cents']) : '$0' ?><span class="h4 text-muted">/month</span></h2>
                            <p class="text-muted small">For getting started with one device</p>
                            <hr>
                            <ul class="list-unstyled mb-4 text-left flex-grow-1">
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <?= $devices($free, 'free') ?> connected device<?= $devices($free, 'free') != 1 ? 's' : '' ?></li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <?= $history($free, 'free') ?> days of data history</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Device health &amp; battery monitoring</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Basic data overview</li>
                                <li class="mb-2 <?= $hasAlgo($free, 'core') ? '' : 'text-muted' ?>"><i class="fas fa-<?= $hasAlgo($free, 'core') ? 'check text-success' : 'times' ?> mr-2"></i> ML / anomaly analysis</li>
                                <li class="mb-2 <?= ($free['alert_email'] ?? 0) || ($free['alert_push'] ?? 0) ? '' : 'text-muted' ?>"><i class="fas fa-<?= (($free['alert_email'] ?? 0) || ($free['alert_push'] ?? 0)) ? 'check text-success' : 'times' ?> mr-2"></i> Real-time security alerts</li>
                                <li class="mb-2 <?= $f($free, 'forensic_export') ? '' : 'text-muted' ?>"><i class="fas fa-<?= $f($free, 'forensic_export') ? 'check text-success' : 'times' ?> mr-2"></i> Forensic export</li>
                            </ul>
                            <a href="<?= url_to('register') ?>" class="btn btn-gradient-secondary btn-block shadow-sm mt-auto">Get Started Free</a>
                        </div>
                    </div>
                </div>

                <!-- Gold Plan -->
                <div class="col-lg-4 mb-4">
                    <div class="card card-outline shadow-sm h-100 text-center border-success popular">
                        <div class="card-header bg-gradient-pink border-bottom-0">
                            <span class="badge badge-light float-right mt-1">Popular</span>
                            <h3 class="card-title text-bold text-white" style="float:none"><i class="fas fa-crown mr-2"></i>Gold</h3>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h2 class="text-success"><?= $gold ? $money($gold['price_monthly_cents']) : '$4.99' ?><span class="h4 text-muted">/month</span></h2>
                            <p class="text-muted small">For monitoring a small circle of devices</p>
                            <hr>
                            <ul class="list-unstyled mb-4 text-left flex-grow-1">
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Up to <?= $devices($gold, 'gold') ?> connected devices</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <?= $history($gold, 'gold') ?> days of data history</li>
                                <li class="mb-2 <?= $hasAlgo($gold, 'core') ? '' : 'text-muted' ?>"><i class="fas fa-<?= $hasAlgo($gold, 'core') ? 'check text-success' : 'times' ?> mr-2"></i> ML analysis with core algorithms</li>
                                <li class="mb-2 <?= ($gold['alert_email'] ?? 0) || ($gold['alert_push'] ?? 0) ? '' : 'text-muted' ?>"><i class="fas fa-<?= (($gold['alert_email'] ?? 0) || ($gold['alert_push'] ?? 0)) ? 'check text-success' : 'times' ?> mr-2"></i> Real-time security alerts (<?= $alertLine($gold) ?>)</li>
                                <li class="mb-2 <?= $f($gold, 'geofencing') ? '' : 'text-muted' ?>"><i class="fas fa-<?= $f($gold, 'geofencing') ? 'check text-success' : 'times' ?> mr-2"></i> Location safety &amp; geofencing</li>
                                <li class="mb-2 <?= $f($gold, 'wellbeing') ? '' : 'text-muted' ?>"><i class="fas fa-<?= $f($gold, 'wellbeing') ? 'check text-success' : 'times' ?> mr-2"></i> <?= $wellbeingLine($gold) ?> wellbeing summary</li>
                                <li class="mb-2 <?= $f($gold, 'forensic_export') ? '' : 'text-muted' ?>"><i class="fas fa-<?= $f($gold, 'forensic_export') ? 'check text-success' : 'times' ?> mr-2"></i> Forensic export</li>
                            </ul>
                            <a href="<?= url_to('register') ?>" class="btn btn-gradient-pink btn-block shadow-sm mt-auto">Choose Gold</a>
                        </div>
                    </div>
                </div>

                <!-- Platinum Plan -->
                <div class="col-lg-4 mb-4">
                    <div class="card card-outline shadow-sm h-100 text-center border-warning">
                        <div class="card-header bg-gradient-purple border-bottom-0">
                            <h3 class="card-title text-bold text-white" style="float:none"><i class="fas fa-gem mr-2"></i>Platinum</h3>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h2 class="text-warning"><?= $plat ? $money($plat['price_monthly_cents']) : '$9.99' ?><span class="h4 text-muted">/month</span></h2>
                            <p class="text-muted small">Full intelligence for you and your whole family</p>
                            <hr>
                            <ul class="list-unstyled mb-4 text-left flex-grow-1">
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Up to <?= $devices($plat, 'platinum') ?> connected devices</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <?= $history($plat, 'platinum') ?> days of data history</li>
                                <li class="mb-2 <?= $hasAlgo($plat, 'deep') ? '' : 'text-muted' ?>"><i class="fas fa-<?= $hasAlgo($plat, 'deep') ? 'check text-success' : 'times' ?> mr-2"></i> All ML algorithms (deep learning)</li>
                                <li class="mb-2 <?= ($plat['alert_email'] ?? 0) || ($plat['alert_push'] ?? 0) ? '' : 'text-muted' ?>"><i class="fas fa-<?= (($plat['alert_email'] ?? 0) || ($plat['alert_push'] ?? 0)) ? 'check text-success' : 'times' ?> mr-2"></i> Real-time alerts (<?= $alertLine($plat) ?>)</li>
                                <li class="mb-2 <?= $f($plat, 'risk_score') ? '' : 'text-muted' ?>"><i class="fas fa-<?= $f($plat, 'risk_score') ? 'check text-success' : 'times' ?> mr-2"></i> Device risk score &amp; correlation</li>
                                <li class="mb-2 <?= $f($plat, 'wellbeing') ? '' : 'text-muted' ?>"><i class="fas fa-<?= $f($plat, 'wellbeing') ? 'check text-success' : 'times' ?> mr-2"></i> <?= $wellbeingLine($plat) ?> wellbeing trends</li>
                                <li class="mb-2 <?= $f($plat, 'forensic_export') ? '' : 'text-muted' ?>"><i class="fas fa-<?= $f($plat, 'forensic_export') ? 'check text-success' : 'times' ?> mr-2"></i> Forensic / audit-ready export</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <?= $supportLine($plat) ?> support</li>
                            </ul>
                            <a href="<?= url_to('register') ?>" class="btn btn-gradient-purple btn-block shadow-sm mt-auto">Choose Platinum</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comparison Table -->
            <div class="card mt-4 shadow-sm">
                <div class="card-header bg-light">
                    <h3 class="card-title text-bold mb-0">Detailed Comparison</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-valign-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Capability</th>
                                    <th class="text-center">Free</th>
                                    <th class="text-center">Gold</th>
                                    <th class="text-center">Platinum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Connected devices</td>
                                    <td class="text-center"><span class="badge badge-primary"><?= $devices($free, 'free') ?></span></td>
                                    <td class="text-center"><span class="badge badge-success"><?= $devices($gold, 'gold') ?></span></td>
                                    <td class="text-center"><span class="badge badge-warning"><?= $devices($plat, 'platinum') ?></span></td>
                                </tr>
                                <tr>
                                    <td>Data history depth</td>
                                    <td class="text-center"><?= $history($free, 'free') ?> days</td>
                                    <td class="text-center"><?= $history($gold, 'gold') ?> days</td>
                                    <td class="text-center"><?= $history($plat, 'platinum') ?> days</td>
                                </tr>
                                <tr>
                                    <td>Monthly price</td>
                                    <td class="text-center"><?= $free ? $money($free['price_monthly_cents']) : '$0' ?></td>
                                    <td class="text-center"><?= $gold ? $money($gold['price_monthly_cents']) : '$4.99' ?></td>
                                    <td class="text-center"><?= $plat ? $money($plat['price_monthly_cents']) : '$9.99' ?></td>
                                </tr>
                                <tr>
                                    <td>ML / anomaly analysis</td>
                                    <td class="text-center <?= $hasAlgo($free, 'core') ? '' : 'text-muted' ?>"><?= $hasAlgo($free, 'core') ? 'Core' : '<i class="fas fa-times"></i>' ?></td>
                                    <td class="text-center <?= $hasAlgo($gold, 'core') ? '' : 'text-muted' ?>"><?= $hasAlgo($gold, 'core') ? 'Core algorithms' : '<i class="fas fa-times"></i>' ?></td>
                                    <td class="text-center <?= $hasAlgo($plat, 'deep') ? '' : 'text-muted' ?>"><?= $hasAlgo($plat, 'deep') ? 'All algorithms' : '<i class="fas fa-times"></i>' ?></td>
                                </tr>
                                <tr>
                                    <td>Device risk score &amp; correlation</td>
                                    <td class="text-center <?= $f($free, 'risk_score') ? '' : 'text-muted' ?>"><?= $f($free, 'risk_score') ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times"></i>' ?></td>
                                    <td class="text-center <?= $f($gold, 'risk_score') ? '' : 'text-muted' ?>"><?= $f($gold, 'risk_score') ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times"></i>' ?></td>
                                    <td class="text-center <?= $f($plat, 'risk_score') ? '' : 'text-muted' ?>"><?= $f($plat, 'risk_score') ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times"></i>' ?></td>
                                </tr>
                                <tr>
                                    <td>Real-time security alerts</td>
                                    <td class="text-center <?= (($free['alert_email'] ?? 0) || ($free['alert_push'] ?? 0)) ? '' : 'text-muted' ?>"><?= (($free['alert_email'] ?? 0) || ($free['alert_push'] ?? 0)) ? $alertLine($free) : '<i class="fas fa-times"></i>' ?></td>
                                    <td class="text-center <?= (($gold['alert_email'] ?? 0) || ($gold['alert_push'] ?? 0)) ? '' : 'text-muted' ?>"><?= (($gold['alert_email'] ?? 0) || ($gold['alert_push'] ?? 0)) ? $alertLine($gold) : '<i class="fas fa-times"></i>' ?></td>
                                    <td class="text-center <?= (($plat['alert_email'] ?? 0) || ($plat['alert_push'] ?? 0)) ? '' : 'text-muted' ?>"><?= (($plat['alert_email'] ?? 0) || ($plat['alert_push'] ?? 0)) ? $alertLine($plat) : '<i class="fas fa-times"></i>' ?></td>
                                </tr>
                                <tr>
                                    <td>Location safety &amp; geofencing</td>
                                    <td class="text-center <?= $f($free, 'geofencing') ? '' : 'text-muted' ?>"><?= $f($free, 'geofencing') ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times"></i>' ?></td>
                                    <td class="text-center <?= $f($gold, 'geofencing') ? '' : 'text-muted' ?>"><?= $f($gold, 'geofencing') ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times"></i>' ?></td>
                                    <td class="text-center <?= $f($plat, 'geofencing') ? '' : 'text-muted' ?>"><?= $f($plat, 'geofencing') ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times"></i>' ?></td>
                                </tr>
                                <tr>
                                    <td>Wellbeing &amp; lifestyle reports</td>
                                    <td class="text-center <?= $f($free, 'wellbeing') ? '' : 'text-muted' ?>"><?= $f($free, 'wellbeing') ? $wellbeingLine($free) : '<i class="fas fa-times"></i>' ?></td>
                                    <td class="text-center <?= $f($gold, 'wellbeing') ? '' : 'text-muted' ?>"><?= $f($gold, 'wellbeing') ? $wellbeingLine($gold) : '<i class="fas fa-times"></i>' ?></td>
                                    <td class="text-center <?= $f($plat, 'wellbeing') ? '' : 'text-muted' ?>"><?= $f($plat, 'wellbeing') ? $wellbeingLine($plat) : '<i class="fas fa-times"></i>' ?></td>
                                </tr>
                                <tr>
                                    <td>Forensic / audit export</td>
                                    <td class="text-center <?= $f($free, 'forensic_export') ? '' : 'text-muted' ?>"><?= $f($free, 'forensic_export') ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times"></i>' ?></td>
                                    <td class="text-center <?= $f($gold, 'forensic_export') ? '' : 'text-muted' ?>"><?= $f($gold, 'forensic_export') ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times"></i>' ?></td>
                                    <td class="text-center <?= $f($plat, 'forensic_export') ? '' : 'text-muted' ?>"><?= $f($plat, 'forensic_export') ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times"></i>' ?></td>
                                </tr>
                                <tr>
                                    <td>Support</td>
                                    <td class="text-center"><?= $supportLine($free) ?></td>
                                    <td class="text-center"><?= $supportLine($gold) ?></td>
                                    <td class="text-center"><?= $supportLine($plat) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Upgrade note -->
            <div class="alert alert-info mt-4 shadow-sm">
                <i class="fas fa-info-circle mr-2"></i>
                Free accounts keep their <?= $history($free, 'free') ?>-day window until you upgrade. Gold unlocks <?= $history($gold, 'gold') ?> days and core ML analysis; Platinum unlocks the full
                intelligence suite, deepest history, and forensic exports. Admin &amp; Superadmin accounts are unaffected by these plans.
            </div>
        </div>
    </section>

<style>
.bg-gradient-purple { background: linear-gradient(45deg, #6f42c1, #9058e6) !important; }
.bg-gradient-pink { background: linear-gradient(45deg, #e83e8c, #ffd700) !important; }
.bg-gradient-secondary { background: linear-gradient(45deg, #6c757d, #adb5bd) !important; }
.btn-gradient-secondary { background: linear-gradient(45deg, #6c757d, #adb5bd); color: #fff; border: none; }
.btn-gradient-pink { background: linear-gradient(45deg, #e83e8c, #ffd700); color: #fff; border: none; }
.btn-gradient-purple { background: linear-gradient(45deg, #6f42c1, #9058e6); color: #fff; border: none; }
.btn-gradient-secondary:hover, .btn-gradient-pink:hover, .btn-gradient-purple:hover { color: #fff; opacity: .9; }
</style>
