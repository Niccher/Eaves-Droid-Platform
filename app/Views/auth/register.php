<?= view('headers_footers/head_landing', ['pag' => 'register', 'page_title' => 'Register | Eaves Droid']) ?>

<?php
helper('version');
$system_versions = function_exists('get_system_version_data') ? get_system_version_data() : [];
$appVersion = $system_versions['platform_version'] ?? '2.9.1';
$androidVersion = '2.8.1';
$mlVersion = '2.5.1';
?>

<div class="auth-split-layout py-4 py-md-5" style="background-color: #f8fafc; min-height: calc(100vh - 120px); display: flex; align-items: center; justify-content: center;">
    <div class="container" style="max-width: 1060px;">
        <div class="card shadow-lg border-0 overflow-hidden" style="border-radius: 16px;">
            <div class="row no-gutters">
                <!-- Left Showcase Column (Desktop) -->
                <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-between p-5 text-white" style="background: linear-gradient(145deg, #0f172a 0%, #1e293b 60%, #0b1120 100%);">
                    <div>
                        <!-- Brand Header -->
                        <div class="d-flex align-items-center mb-4">
                            <img src="<?= base_url('assets/img/logo.png') ?>" alt="Eaves Droid Logo" class="img-circle mr-3 shadow" style="width: 48px; height: 48px; object-fit: contain; background: #ffffff; padding: 4px;">
                            <div>
                                <h4 class="font-weight-bold mb-0 text-white" style="letter-spacing: -0.5px;">Eaves Droid</h4>
                                <span class="badge badge-primary px-2 py-1 small font-weight-normal"><?= esc($appVersion) ?> Platform</span>
                            </div>
                        </div>

                        <h3 class="font-weight-bold mb-3 text-white">Join Eaves Droid</h3>
                        <p class="text-white-50 small mb-4" style="line-height: 1.6;">
                            Create your account to start collecting, correlating, and analyzing mobile forensic telemetry with dual-engine AI.
                        </p>

                        <!-- Highlights List -->
                        <div class="auth-highlights mb-4">
                            <div class="d-flex align-items-start mb-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fas fa-server font-xs"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1 text-white">Self-Hosted Ownership</h6>
                                    <p class="text-white-50 small mb-0">Your database, your servers. Total privacy and complete sovereignty over all telemetry.</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start mb-3">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mr-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fas fa-qrcode font-xs"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1 text-white">Instant Agent Onboarding</h6>
                                    <p class="text-white-50 small mb-0">Pair Android collector devices in seconds via numeric code or dynamic QR pairing.</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start mb-3">
                                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center mr-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fas fa-brain font-xs"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1 text-white">Automated Anomaly Scoring</h6>
                                    <p class="text-white-50 small mb-0">Isolation Forest and One-Class SVM continuously monitor for behavioral outliers.</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start">
                                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center mr-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fas fa-shield-alt font-xs"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1 text-white">Enterprise Role Security</h6>
                                    <p class="text-white-50 small mb-0">Fine-grained RBAC controls protecting sensitive device feeds and reports.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Badges & Guarantee -->
                    <div class="pt-4 border-top border-secondary">
                        <div class="d-flex flex-wrap align-items-center justify-content-between small text-white-50">
                            <span class="mr-2 mb-1"><i class="fas fa-code-branch text-primary mr-1"></i> WebApp: <strong><?= esc($appVersion) ?></strong></span>
                            <span class="mr-2 mb-1"><i class="fab fa-android text-success mr-1"></i> Android: <strong>v<?= esc($androidVersion) ?></strong></span>
                            <span class="mb-1"><i class="fas fa-robot text-warning mr-1"></i> ML Engine: <strong>v<?= esc($mlVersion) ?></strong></span>
                        </div>
                        <div class="mt-2 small text-white-50">
                            <i class="fas fa-check-circle text-success mr-1"></i> Free &amp; Open Source Mobile Intelligence
                        </div>
                    </div>
                </div>

                <!-- Right Form Column -->
                <div class="col-lg-6 col-12 d-flex flex-column justify-content-center p-4 p-md-5 bg-white">
                    <div class="w-100" style="max-width: 440px; margin: 0 auto;">
                        
                        <!-- Mobile Brand Header -->
                        <div class="d-lg-none text-center mb-4">
                            <img src="<?= base_url('assets/img/logo.png') ?>" alt="Eaves Droid Logo" class="img-circle shadow-sm mb-2" style="width: 52px; height: 52px; object-fit: contain;">
                            <h4 class="font-weight-bold text-dark mb-0">Eaves Droid</h4>
                            <span class="badge badge-primary px-2 py-1 small"><?= esc($appVersion) ?></span>
                        </div>

                        <!-- Top Link -->
                        <div class="mb-3">
                            <a href="<?= base_url('landing') ?>" class="text-muted small font-weight-bold">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Homepage
                            </a>
                        </div>

                        <!-- Form Title -->
                        <div class="mb-3">
                            <h3 class="font-weight-bold text-dark mb-1">Create an Account</h3>
                            <p class="text-muted small">Sign up in seconds to start managing your device fleet</p>
                        </div>

                        <!-- Display alerts -->
                        <?php if (session('error') !== null) : ?>
                            <div class="alert alert-danger alert-dismissible fade show small py-2 px-3 mb-3">
                                <i class="fas fa-exclamation-circle mr-1"></i> <?= session('error') ?>
                                <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif ?>

                        <?php if (session('errors') !== null) : ?>
                            <div class="alert alert-danger alert-dismissible fade show small py-2 px-3 mb-3">
                                <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
                                <?php if (is_array(session('errors'))) : ?>
                                    <ul class="mb-0 pl-3">
                                        <?php foreach (session('errors') as $error) : ?>
                                            <li><?= esc($error) ?></li>
                                        <?php endforeach ?>
                                    </ul>
                                <?php else : ?>
                                    <?= esc(session('errors')) ?>
                                <?php endif ?>
                            </div>
                        <?php endif ?>

                        <?php if (session('message') !== null) : ?>
                            <div class="alert alert-success alert-dismissible fade show small py-2 px-3 mb-3">
                                <i class="fas fa-check-circle mr-1"></i> <?= session('message') ?>
                                <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif ?>

                        <form action="<?= url_to('register') ?>" method="post">
                            <?= csrf_field() ?>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-secondary mb-1">Full Name / Username</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-user text-muted"></i></span>
                                    </div>
                                    <input type="text" name="username" class="form-control border-left-0 pl-0" placeholder="John Doe" value="<?= old('username') ?>" required autofocus>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-secondary mb-1">Email Address</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-envelope text-muted"></i></span>
                                    </div>
                                    <input type="email" name="email" class="form-control border-left-0 pl-0" placeholder="name@domain.com" value="<?= old('email') ?>" required>
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-secondary mb-1">Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-lock text-muted"></i></span>
                                    </div>
                                    <input type="password" id="registerPassword" name="password" class="form-control border-left-0 border-right-0 pl-0" placeholder="Min 8 characters" required autocomplete="new-password">
                                    <div class="input-group-append">
                                        <button class="btn btn-light border border-left-0 text-muted toggle-password" type="button" data-target="#registerPassword" title="Show/hide password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- Password Strength Meter -->
                                <div class="progress mt-2" style="height: 5px;">
                                    <div id="passwordStrengthBar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1 mb-2">
                                    <span id="passwordStrengthText" class="small text-muted font-weight-bold">Password Strength</span>
                                </div>
                                <!-- Requirements Checklist -->
                                <div class="row no-gutters small text-muted mb-2">
                                    <div class="col-6 mb-1" id="ruleLength">
                                        <i class="far fa-circle mr-1"></i> Min 8 chars
                                    </div>
                                    <div class="col-6 mb-1" id="ruleCase">
                                        <i class="far fa-circle mr-1"></i> Upper &amp; lower case
                                    </div>
                                    <div class="col-6" id="ruleNumber">
                                        <i class="far fa-circle mr-1"></i> At least 1 number
                                    </div>
                                    <div class="col-6" id="ruleSpecial">
                                        <i class="far fa-circle mr-1"></i> Special symbol
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-secondary mb-1">Retype Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-lock text-muted"></i></span>
                                    </div>
                                    <input type="password" id="registerPasswordConfirm" name="password_confirm" class="form-control border-left-0 border-right-0 pl-0" placeholder="Confirm your password" required autocomplete="new-password">
                                    <div class="input-group-append">
                                        <button class="btn btn-light border border-left-0 text-muted toggle-password" type="button" data-target="#registerPasswordConfirm" title="Show/hide password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="passwordMatchStatus" class="mt-1"></div>
                            </div>

                            <div class="form-group mb-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="agreeTerms" name="terms" value="agree" required>
                                    <label class="custom-control-label small text-muted font-weight-normal" for="agreeTerms">
                                        I agree to the <a href="<?= base_url('faqs_terms') ?>" class="text-primary font-weight-bold" target="_blank">Terms of Service</a> &amp; <a href="<?= base_url('privacy-policy') ?>" class="text-primary font-weight-bold" target="_blank">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block shadow-sm py-2 font-weight-bold mb-3">
                                <i class="fas fa-user-plus mr-1"></i> Create Account
                            </button>
                        </form>

                        <div class="text-center pt-3 border-top">
                            <p class="text-muted small mb-0">
                                Already have an account? 
                                <a href="<?= url_to('login') ?>" class="text-primary font-weight-bold">Sign in here</a>
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetSelector = this.getAttribute('data-target');
            var input = document.querySelector(targetSelector);
            if (!input) return;
            var icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        });
    });

    // Password strength calculation
    var passwordInput = document.getElementById('registerPassword');
    var confirmInput = document.getElementById('registerPasswordConfirm');
    var strengthBar = document.getElementById('passwordStrengthBar');
    var strengthText = document.getElementById('passwordStrengthText');
    var matchStatus = document.getElementById('passwordMatchStatus');

    var ruleLength = document.getElementById('ruleLength');
    var ruleCase = document.getElementById('ruleCase');
    var ruleNumber = document.getElementById('ruleNumber');
    var ruleSpecial = document.getElementById('ruleSpecial');

    function updateRule(el, valid) {
        if (!el) return;
        var icon = el.querySelector('i');
        if (valid) {
            el.classList.remove('text-muted');
            el.classList.add('text-success');
            if (icon) {
                icon.className = 'fas fa-check-circle mr-1 text-success';
            }
        } else {
            el.classList.remove('text-success');
            el.classList.add('text-muted');
            if (icon) {
                icon.className = 'far fa-circle mr-1 text-muted';
            }
        }
    }

    function checkPassword() {
        if (!passwordInput) return;
        var val = passwordInput.value;
        var score = 0;

        var hasLen = val.length >= 8;
        var hasCase = /[a-z]/.test(val) && /[A-Z]/.test(val);
        var hasNum = /\d/.test(val);
        var hasSpec = /[^A-Za-z0-9]/.test(val);

        if (hasLen) score++;
        if (hasCase) score++;
        if (hasNum) score++;
        if (hasSpec) score++;

        updateRule(ruleLength, hasLen);
        updateRule(ruleCase, hasCase);
        updateRule(ruleNumber, hasNum);
        updateRule(ruleSpecial, hasSpec);

        if (strengthBar && strengthText) {
            if (val.length === 0) {
                strengthBar.style.width = '0%';
                strengthBar.className = 'progress-bar';
                strengthText.textContent = 'Password Strength';
                strengthText.className = 'small text-muted font-weight-bold';
            } else if (score === 1) {
                strengthBar.style.width = '25%';
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.textContent = 'Weak';
                strengthText.className = 'small text-danger font-weight-bold';
            } else if (score === 2) {
                strengthBar.style.width = '50%';
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.textContent = 'Fair';
                strengthText.className = 'small text-warning font-weight-bold';
            } else if (score === 3) {
                strengthBar.style.width = '75%';
                strengthBar.className = 'progress-bar bg-info';
                strengthText.textContent = 'Good';
                strengthText.className = 'small text-info font-weight-bold';
            } else if (score === 4) {
                strengthBar.style.width = '100%';
                strengthBar.className = 'progress-bar bg-success';
                strengthText.textContent = 'Strong';
                strengthText.className = 'small text-success font-weight-bold';
            }
        }

        checkMatch();
    }

    function checkMatch() {
        if (!confirmInput || !passwordInput || !matchStatus) return;
        var p = passwordInput.value;
        var c = confirmInput.value;
        if (c.length === 0) {
            matchStatus.innerHTML = '';
        } else if (p === c) {
            matchStatus.innerHTML = '<span class="text-success small font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Passwords match</span>';
        } else {
            matchStatus.innerHTML = '<span class="text-danger small font-weight-bold"><i class="fas fa-times-circle mr-1"></i> Passwords do not match</span>';
        }
    }

    if (passwordInput) passwordInput.addEventListener('input', checkPassword);
    if (confirmInput) confirmInput.addEventListener('input', checkMatch);
});
</script>

<?= view('headers_footers/footer_landing') ?>
