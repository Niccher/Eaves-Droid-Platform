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
            <div class="row">                <!-- Free Plan -->
                <div class="col-lg-4 mb-4">
                    <div class="card card-outline shadow-sm h-100 text-center border-primary">
                        <div class="card-header bg-gradient-secondary border-bottom-0">
                            <h3 class="card-title text-bold text-white" style="float:none"><i class="fas fa-star mr-2"></i>Free</h3>
                        </div>
                        <div class="card-body d-flex flex-column text-left">
                            <div class="text-center">
                                <h2 class="text-primary font-weight-bold"><?= $free ? $money($free['price_monthly_cents']) : '$0' ?><span class="h4 text-muted">/month</span></h2>
                                <p class="text-muted small">For getting started with basic monitoring</p>
                            </div>
                            <hr>
                            <ul class="list-unstyled mb-4 flex-grow-1">
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong><?= $devices($free, 'free') ?> Connected Device</strong> (Maximum 1 device)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong><?= $history($free, 'free') ?> Days History Retention</strong> (Standard logs)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong>Device Health Telemetry</strong> (Real-time battery, CPU, and memory)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong>Standard Timeline Overview</strong> (Basic events feed)</li>
                                <li class="mb-2 text-muted"><i class="fas fa-times mr-2"></i> Location Safety &amp; Geofencing</li>
                                <li class="mb-2 text-muted"><i class="fas fa-times mr-2"></i> Real-time Push &amp; Email Alerts</li>
                                <li class="mb-2 text-muted"><i class="fas fa-times mr-2"></i> Anomaly Detection &amp; Risk Scoring</li>
                                <li class="mb-2 text-muted"><i class="fas fa-times mr-2"></i> Wellbeing Analytics</li>
                                <li class="mb-2 text-muted"><i class="fas fa-times mr-2"></i> Priority support tier</li>
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
                        <div class="card-body d-flex flex-column text-left">
                            <div class="text-center">
                                <h2 class="text-success font-weight-bold"><?= $gold ? $money($gold['price_monthly_cents']) : '$4.99' ?><span class="h4 text-muted">/month</span></h2>
                                <p class="text-muted small">For monitoring a small circle of devices</p>
                            </div>
                            <hr>
                            <ul class="list-unstyled mb-4 flex-grow-1">
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong>Up to <?= $devices($gold, 'gold') ?> Connected Devices</strong></li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong><?= $history($gold, 'gold') ?> Days Extended Retention</strong> (Long-term data archiving)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong>Core Machine Learning Analysis</strong> (Identifies patterns &amp; anomalies)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong>Location Safety &amp; Geofencing</strong> (Define boundary logs)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong>Real-time Security Alerts</strong> (Instant <?= $alertLine($gold) ?> alerts)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong><?= $wellbeingLine($gold) ?> Wellbeing Reports</strong> (Usage &amp; habit analysis)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong>Forensic Data Export</strong> (PDF and CSV format logs)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong>Standard Support</strong> (Access to helpdesk email support)</li>
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
                        <div class="card-body d-flex flex-column text-left">
                            <div class="text-center">
                                <h2 class="text-warning font-weight-bold"><?= $plat ? $money($plat['price_monthly_cents']) : '$9.99' ?><span class="h4 text-muted">/month</span></h2>
                                <p class="text-muted small">Full intelligence for you and your whole family</p>
                            </div>
                            <hr>
                            <ul class="list-unstyled mb-4 flex-grow-1">
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong>Up to <?= $devices($plat, 'platinum') ?> Connected Devices</strong> (Ideal for families)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong><?= $history($plat, 'platinum') ?> Days Deep Retention</strong> (Half-year historical logs)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong>Advanced ML-Engine Detectors</strong> (Deep anomaly algorithms)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong>Location Safety &amp; Address Caching</strong> (Reverse geocoding address mapping)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong>Instant Alert Integration</strong> (Email + Push notifications)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong>Device Risk Score &amp; Correlation</strong> (Combines logs to score threat severity)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong><?= $wellbeingLine($plat) ?> Wellbeing Trends</strong> (Predictive lifestyle insight charts)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong>Audit-Ready Forensic Export</strong> (Signed reports with cryptographic signature)</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> <strong><?= $supportLine($plat) ?> Support</strong> (Dedicated 24/7 priority response)</li>
                            </ul>
                            <a href="<?= url_to('register') ?>" class="btn btn-gradient-purple btn-block shadow-sm mt-auto">Choose Platinum</a>
                        </div>
                    </div>
                </div>
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
.bg-gradient-secondary { background: linear-gradient(45deg, #6c757d, #adb5bd) !important; }
.btn-gradient-secondary { background: linear-gradient(45deg, #6c757d, #adb5bd); color: #fff; border: none; }
.btn-gradient-pink { background: linear-gradient(45deg, #e83e8c, #ffd700); color: #fff; border: none; }
.btn-gradient-purple { background: linear-gradient(45deg, #6f42c1, #9058e6); color: #fff; border: none; }
.btn-gradient-secondary:hover, .btn-gradient-pink:hover, .btn-gradient-purple:hover { color: #fff; opacity: .9; }
</style>
