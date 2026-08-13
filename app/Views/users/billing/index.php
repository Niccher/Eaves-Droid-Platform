<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-credit-card mr-2 text-primary"></i>Billing &amp; Plan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Billing</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid upgrade-wrap">

            <!-- Current plan banner -->
            <?php
            $currentPlan  = strtolower((string) ($current_plan ?? 'free'));
            $currentName  = $currentPlan ? ucfirst($currentPlan) : 'Free';
            $currentVer   = $plans[$currentPlan] ?? null;
            $currentDevices = (int) ($currentVer['max_devices'] ?? 1);
            $currentDays    = (int) ($currentVer['history_days'] ?? 10);
            $currentSupport = $currentVer['support_tier'] ?? 'standard';
            ?>
            <div class="card border-0 shadow-sm mb-4" style="border-radius:.9rem;background:linear-gradient(135deg,#f8f9fa,#e9ecef);">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between px-4 py-3">
                    <div class="d-flex align-items-center">
                        <div class="mr-3 d-flex align-items-center justify-content-center rounded-circle <?= $currentPlan === 'platinum' ? 'bg-gradient-purple' : ($currentPlan === 'gold' ? 'bg-gradient-pink' : 'bg-secondary') ?> text-white font-weight-bold" style="width:52px;height:52px;font-size:1.1rem;">
                            <i class="fas <?= $currentPlan === 'platinum' ? 'fa-gem' : ($currentPlan === 'gold' ? 'fa-crown' : 'fa-star') ?>"></i>
                        </div>
                        <div>
                            <div class="small text-uppercase font-weight-bold text-muted">Your current plan</div>
                            <div class="h4 mb-0 font-weight-bold text-dark text-uppercase"><?= esc($currentName) ?></div>
                        </div>
                    </div>
                    <div class="text-md-right">
                        <span class="badge badge-light border px-3 py-2"><i class="fas fa-mobile-alt text-secondary mr-1"></i> <?= $currentDevices ?> device<?= $currentDevices > 1 ? 's' : '' ?></span>
                        <span class="badge badge-light border px-3 py-2"><i class="fas fa-history text-secondary mr-1"></i> <?= $currentDays >= 365 ? 'Full history' : $currentDays . ' days' ?></span>
                        <span class="badge badge-light border px-3 py-2"><i class="fas fa-headset text-secondary mr-1"></i> <?= ucfirst($currentSupport) ?> support</span>
                    </div>
                </div>
            </div>

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

            <!-- Plan cards -->
            <div class="row justify-content-center g-4">
                <?php
                $planTheme = [
                    'free'     => ['header' => 'bg-secondary',       'btn' => 'btn-secondary'],
                    'gold'     => ['header' => 'bg-gradient-pink',   'btn' => 'btn-gradient-pink'],
                    'platinum' => ['header' => 'bg-gradient-purple', 'btn' => 'btn-gradient-purple'],
                ];
                foreach (['free', 'gold', 'platinum'] as $key):
                    $p = $plans[$key] ?? null;
                    if (!$p) continue;
                    $isCurrent = $key === $currentPlan;
                    $isUpgrade = in_array($key, $upgrade_plans, true);
                    $isPlat = $key === 'platinum';
                ?>
                <div class="col-12 col-md-6 col-xl-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 plan-card <?= $isPlat ? 'plan-platinum' : ($key === 'gold' ? 'plan-gold' : '') ?> <?= $isCurrent ? 'plan-current' : '' ?>">
                        <div class="card-header text-center py-3 <?= $planTheme[$key]['header'] ?> text-white" style="border:0;">
                            <?php if ($isCurrent): ?><span class="badge badge-light mb-1"><i class="fas fa-check mr-1"></i>Your current plan</span><?php endif; ?>
                            <?php if ($key === 'platinum'): ?><span class="badge badge-dark mt-1"><i class="fas fa-crown mr-1"></i>MOST ADVANCED</span><?php endif; ?>
                            <?php if ($key === 'gold'): ?><span class="badge badge-light mt-1">POPULAR</span><?php endif; ?>
                            <h3 class="m-0 font-weight-bold"><?= ucfirst($key) ?></h3>
                        </div>
                        <div class="card-body d-flex flex-column p-0">
                            <div class="px-4 pt-4 pb-2">
                                <div class="d-flex align-items-baseline justify-content-center">
                                    <span class="h2 mb-0 text-muted"><?= esc($p['currency'] ?? 'USD') ?></span>
                                    <span class="display-5 font-weight-bold text-dark price-monthly"><?= number_format((int)($p['price_monthly_cents'] ?? 0) / 100, 2) ?></span>
                                    <span class="text-muted ml-1">/ month</span>
                                </div>
                                <div class="text-center">
                                    <span class="text-success font-weight-bold price-yearly" style="display:none;">
                                        <?= esc($p['currency'] ?? 'USD') ?> <?= number_format((int)($p['price_yearly_cents'] ?? 0) / 100, 2) ?> / year
                                    </span>
                                </div>
                                <?php if (($p['price_yearly_cents'] ?? 0) > 0 && ($p['price_monthly_cents'] ?? 0) > 0): ?>
                                <div class="text-center small text-muted">
                                    <span class="save-badge">or <?= esc($p['currency'] ?? 'USD') ?> <?= number_format((int)$p['price_yearly_cents'] / 100, 2) ?> / year — save <?= round(100 - ((int)$p['price_yearly_cents'] / 12 / (int)$p['price_monthly_cents'] * 100)) ?>%</span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php
                            $featArr = $p['features'] ?? [];
                            if (is_string($featArr)) {
                                $featArr = json_decode($featArr, true) ?: [];
                            }
                            $hwProfile = $featArr['hardware_profile'] ?? 'basic';
                            $swProfile = $featArr['software_profile'] ?? 'basic';
                            ?>
                            <div class="px-4 py-3 flex-grow-1">
                                 <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                                     <span class="badge badge-light border px-3 py-2"><i class="fas fa-mobile-alt text-secondary mr-1"></i><strong><?= (int)($p['max_devices'] ?? 1) ?></strong> device<?= (int)($p['max_devices'] ?? 1) > 1 ? 's' : '' ?></span>
                                     <span class="badge badge-light border px-3 py-2"><i class="fas fa-history text-secondary mr-1"></i><strong><?= (int)($p['history_days'] ?? 10) >= 365 ? 'Full' : $p['history_days'] ?? 10 ?></strong> days history</span>
                                     <span class="badge badge-light border px-3 py-2"><i class="fas fa-headset text-secondary mr-1"></i><?= ucfirst($p['support_tier'] ?? 'standard') ?> support</span>
                                     <span class="badge badge-light border px-3 py-2"><i class="fas fa-microchip text-indigo mr-1"></i><strong><?= ucfirst($hwProfile) ?></strong> Hardware</span>
                                     <span class="badge badge-light border px-3 py-2"><i class="fas fa-laptop-code text-navy mr-1"></i><strong><?= ucfirst($swProfile) ?></strong> Software</span>
                                 </div>

                                 <?php
                                 $tiers = $p['ml_algorithms'] ?? [];
                                 if (is_string($tiers)) {
                                     $decoded = json_decode($tiers, true);
                                     $tiers = is_array($decoded) ? $decoded : [];
                                 }
                                 if (!empty($tiers)): ?>
                                 <div class="mb-2">
                                     <div class="small text-muted text-uppercase font-weight-bold mb-1"><i class="fas fa-brain mr-1"></i>Included Algorithms</div>
                                     <div class="d-flex flex-wrap gap-1">
                                         <?php foreach ($tiers as $t): ?>
                                         <span class="badge <?= $t === 'deep' ? 'badge-dark' : ($t === 'advanced' ? 'badge-warning' : 'badge-primary') ?> px-2 py-1"><?= esc(ucfirst($t)) ?></span>
                                         <?php endforeach; ?>
                                     </div>
                                 </div>
                                 <?php endif; ?>

                                 <?php $features = $p['features'] ?? []; if (!empty($features)): ?>
                                 <ul class="list-unstyled mb-0 plan-features">
                                     <?php 
                                     if (is_string($features)) {
                                         $features = json_decode($features, true) ?: [];
                                     }
                                     foreach ($features as $fk => $enabled):
                                         if ($fk === 'hardware_profile' || $fk === 'software_profile') continue;
                                         if ($enabled !== true) continue;
                                         $label = $feature_labels[$fk] ?? ucwords(str_replace('_', ' ', (string)$fk)); ?>
                                     <li class="mb-1"><i class="fas fa-check text-success mr-2"></i><?= esc($label) ?></li>
                                     <?php endforeach; ?>
                                 </ul>
                                 <?php endif; ?>
                            </div>

                            <div class="p-4 pt-0">
                                <?php if ($isCurrent): ?>
                                <button type="button" class="btn btn-light btn-lg btn-block font-weight-bold" disabled>
                                    <i class="fas fa-check mr-2"></i> Current Plan
                                </button>
                                <?php elseif ($isUpgrade): ?>
                                <button type="button" class="btn <?= $planTheme[$key]['btn'] ?> btn-lg btn-block font-weight-bold shadow-sm btn-upgrade"
                                        data-plan="<?= esc($key) ?>" data-name="<?= ucfirst($key) ?>"
                                        data-monthly="<?= number_format((int)($p['price_monthly_cents'] ?? 0) / 100, 2) ?>"
                                        data-yearly="<?= number_format((int)($p['price_yearly_cents'] ?? 0) / 100, 2) ?>"
                                        data-currency="<?= esc($p['currency'] ?? '$') ?>">
                                    <i class="fas fa-arrow-up mr-2"></i> Upgrade to <?= ucfirst($key) ?>
                                </button>
                                <?php else: ?>
                                <button type="button" class="btn btn-outline-secondary btn-lg btn-block font-weight-bold" disabled>
                                    <i class="fas fa-minus mr-2"></i> Downgrade — contact support
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-3 text-muted small">
                <i class="fas fa-info-circle mr-1"></i> This is a <strong>simulated checkout</strong> — no real payment is processed. Your plan is upgraded instantly for demo/testing.
            </div>
        </div>
    </section>
</div>

<!-- ══════════════════ PAYMENT MODAL (simulated) ══════════════════ -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius:.9rem;">
      <div class="modal-header bg-dark text-white" style="border:0;">
        <h5 class="modal-title font-weight-bold"><i class="fas fa-lock mr-2 text-warning"></i>Upgrade to <span id="pay-plan-name">Platinum</span></h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body p-4">

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

        <ul class="nav nav-pills nav-fill mb-3" id="pay-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#pay-card" data-method="card"><i class="far fa-credit-card mr-1"></i>Card</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#pay-mpesa" data-method="mpesa"><i class="fas fa-mobile-alt mr-1"></i>M-Pesa</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#pay-paypal" data-method="paypal"><i class="fab fa-paypal mr-1"></i>PayPal</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#pay-stripe" data-method="stripe"><i class="fab fa-stripe mr-1"></i>Stripe</a></li>
        </ul>

        <div class="tab-content" id="pay-tabs-content">
            <div class="tab-pane fade show active" id="pay-card" role="tabpanel">
                <div class="form-row">
                    <div class="form-group col-12">
                        <label>Cardholder Name</label>
                        <input type="text" class="form-control" id="cc-name" placeholder="John Doe" autocomplete="cc-name">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12">
                        <label>Card Number</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="far fa-credit-card"></i></span></div>
                            <input type="text" class="form-control" id="cc-number" placeholder="4242 4242 4242 4242" inputmode="numeric" autocomplete="cc-number">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-4"><label>Expiry</label><input type="text" class="form-control" id="cc-exp" placeholder="MM/YY" autocomplete="cc-exp"></div>
                    <div class="form-group col-4"><label>CVC</label><input type="text" class="form-control" id="cc-cvc" placeholder="123" autocomplete="cc-csc"></div>
                    <div class="form-group col-4"><label>Country</label><input type="text" class="form-control" id="cc-country" placeholder="Kenya"></div>
                </div>
            </div>

            <div class="tab-pane fade" id="pay-mpesa" role="tabpanel">
                <div class="text-center mb-3">
                    <i class="fas fa-mobile-alt fa-3x text-success"></i>
                    <p class="text-muted mb-0">You will receive an STK push prompt on your phone to approve the payment (simulated).</p>
                </div>
                <div class="form-group">
                    <label>M-Pesa Phone Number</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">+254</span></div>
                        <input type="text" class="form-control" id="mpesa-phone" placeholder="712 345 678" inputmode="numeric">
                    </div>
                </div>
                <div class="form-group">
                    <label>M-Pesa PIN</label>
                    <input type="password" class="form-control" id="mpesa-pin" placeholder="••••" inputmode="numeric">
                </div>
            </div>

            <div class="tab-pane fade" id="pay-paypal" role="tabpanel">
                <div class="text-center py-2">
                    <i class="fab fa-paypal fa-4x text-primary mb-3"></i>
                    <p class="text-muted">You will be redirected to PayPal to complete your purchase securely (simulated).</p>
                    <div id="paypal-button-container" class="mt-2">
                        <button class="btn btn-primary btn-lg w-100" id="btn-paypal-sim"><i class="fab fa-paypal mr-2"></i>Pay with PayPal</button>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pay-stripe" role="tabpanel">
                <div class="form-row">
                    <div class="form-group col-12">
                        <label>Card Number</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fab fa-stripe"></i></span></div>
                            <input type="text" class="form-control" id="stripe-number" placeholder="4242 4242 4242 4242" inputmode="numeric">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-6"><label>Expiry</label><input type="text" class="form-control" id="stripe-exp" placeholder="MM/YY"></div>
                    <div class="form-group col-6"><label>CVC</label><input type="text" class="form-control" id="stripe-cvc" placeholder="123"></div>
                </div>
            </div>
        </div>

        <div class="alert alert-warning small mt-3 mb-0">
            <i class="fas fa-info-circle mr-1"></i> This is a <strong>demo checkout</strong>. No real payment is charged — your subscription will be upgraded instantly.
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

<style>
.upgrade-wrap { max-width: 1600px; }
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

    if (method === 'card' || method === 'stripe') {
        var num = method === 'card' ? document.getElementById('cc-number') : document.getElementById('stripe-number');
        if (num.value.trim().replace(/\s+/g, '').length < 12) {
            Swal.fire('Invalid card', 'Please enter a valid card number.', 'warning');
            return;
        }
    }
    if (method === 'mpesa') {
        var ph = document.getElementById('mpesa-phone').value.replace(/\s+/g, '');
        if (ph.length < 9) {
            Swal.fire('Invalid number', 'Please enter a valid M-Pesa phone number.', 'warning');
            return;
        }
    }

    var payBtnAmountText = document.getElementById('pay-btn-amount').textContent;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Processing...';

    var payload = {
        plan: upgradeTarget.plan,
        billing: getBilling(),
        payment_method: method,
        redirect_to: '<?= base_url('billing') ?>'
    };

    fetch('<?= base_url('billing/simulate') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: JSON.stringify(payload)
    })
    .then(function (res) { return res.json().then(function (d) { return { ok: res.ok, d: d }; }); })
    .then(function (r) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock mr-2"></i>Pay <span id="pay-btn-amount">' + payBtnAmountText + '</span>';
        if (r.ok && r.d.success) {
            $('#paymentModal').modal('hide');
            Swal.fire({
                title: 'Payment Successful!',
                html: '<i class="fas fa-check-circle text-success fa-3x d-block mb-3"></i>You have upgraded to <strong>' + r.d.plan_name + '</strong>. Your new plan is now active.',
                icon: 'success',
                confirmButtonText: 'Continue',
                allowOutsideClick: false
            }).then(function () {
                window.location.href = r.d.redirect_to || '<?= base_url('billing') ?>';
            });
        } else {
            Swal.fire('Payment failed', (r.d && r.d.messages) || 'Something went wrong. Please try again.', 'error');
        }
    })
    .catch(function (err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock mr-2"></i>Pay <span id="pay-btn-amount">' + payBtnAmountText + '</span>';
        Swal.fire('Error', 'Could not reach the server. Please try again.', 'error');
    });
});

document.getElementById('btn-paypal-sim').addEventListener('click', function () {
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Redirecting to PayPal...';
    setTimeout(function () {
        btn.disabled = false;
        btn.innerHTML = '<i class="fab fa-paypal mr-2"></i>Pay with PayPal';
        document.querySelectorAll('#pay-tabs .nav-link').forEach(function (t) { t.classList.remove('active'); });
        document.querySelector('#pay-tabs .nav-link[data-method="paypal"]').classList.add('active');
        document.getElementById('btn-pay').click();
    }, 1200);
});
</script>
