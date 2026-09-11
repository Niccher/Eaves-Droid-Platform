<?= view('headers_footers/head_landing', ['pag' => 'reset', 'page_title' => 'Reset Password | Eaves Droid']) ?>

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

                        <h3 class="font-weight-bold mb-3 text-white">Choose a New Password</h3>
                        <p class="text-white-50 small mb-4" style="line-height: 1.6;">
                            Secure your account with a strong passphrase to prevent unauthorized access to linked mobile agents and forensic data.
                        </p>

                        <!-- Highlights List -->
                        <div class="auth-highlights mb-4">
                            <div class="d-flex align-items-start mb-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fas fa-key font-xs"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1 text-white">Argon2 / Bcrypt Hash Protection</h6>
                                    <p class="text-white-50 small mb-0">Credentials are secured using state-of-the-art memory-hard adaptive hashing.</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start mb-3">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mr-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fas fa-lock font-xs"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1 text-white">Single-Use Token Validation</h6>
                                    <p class="text-white-50 small mb-0">Once updated, the recovery token is immediately invalidated for your protection.</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start">
                                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center mr-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fas fa-history font-xs"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1 text-white">Session Invalidation</h6>
                                    <p class="text-white-50 small mb-0">Existing active sessions on other devices will be terminated upon reset.</p>
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
                            <i class="fas fa-shield-alt text-success mr-1"></i> Shield Enterprise Authentication Guard
                        </div>
                    </div>
                </div>

                <!-- Right Form Column -->
                <div class="col-lg-6 col-12 d-flex flex-column justify-content-center p-4 p-md-5 bg-white">
                    <div class="w-100" style="max-width: 420px; margin: 0 auto;">
                        
                        <!-- Mobile Brand Header -->
                        <div class="d-lg-none text-center mb-4">
                            <img src="<?= base_url('assets/img/logo.png') ?>" alt="Eaves Droid Logo" class="img-circle shadow-sm mb-2" style="width: 52px; height: 52px; object-fit: contain;">
                            <h4 class="font-weight-bold text-dark mb-0">Eaves Droid</h4>
                            <span class="badge badge-primary px-2 py-1 small"><?= esc($appVersion) ?></span>
                        </div>

                        <!-- Top Link -->
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <a href="<?= url_to('login') ?>" class="text-muted small font-weight-bold">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Sign In
                            </a>
                            <a href="<?= url_to('demo-login') ?>" class="btn btn-sm btn-outline-warning shadow-sm font-weight-bold">
                                <i class="fas fa-magic mr-1"></i> Try Interactive Demo
                            </a>
                        </div>

                        <!-- Form Title -->
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <h3 class="font-weight-bold text-dark mb-1">Set New Password</h3>
                            <p class="text-muted small">Choose a new secure password for your account</p>
                        </div>

                        <!-- Display alerts -->
                        <?php if (session('error') !== null) : ?>
                            <div class="alert alert-danger alert-dismissible fade show small py-2 px-3 mb-3">
                                <i class="fas fa-exclamation-circle mr-1"></i> <?= session('error') ?>
                                <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif ?>

                        <?php if (session('message') !== null) : ?>
                            <div class="alert alert-success alert-dismissible fade show small py-2 px-3 mb-3">
                                <i class="fas fa-check-circle mr-1"></i> <?= session('message') ?>
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

                        <form action="<?= url_to('reset-password') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="token" value="<?= $token ?>">

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-secondary mb-1">New Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-lock text-muted"></i></span>
                                    </div>
                                    <input type="password" id="resetPassword" name="password" class="form-control border-left-0 border-right-0 pl-0" placeholder="New Password (min 8 chars)" required autocomplete="new-password">
                                    <div class="input-group-append">
                                        <button class="btn btn-light border border-left-0 text-muted toggle-password" type="button" data-target="#resetPassword" title="Show/hide password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div id="resetStrengthBar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span id="resetStrengthText" class="small text-muted font-weight-bold">Password Strength</span>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="small font-weight-bold text-secondary mb-1">Confirm New Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-lock text-muted"></i></span>
                                    </div>
                                    <input type="password" id="resetPasswordConfirm" name="password_confirm" class="form-control border-left-0 border-right-0 pl-0" placeholder="Retype New Password" required autocomplete="new-password">
                                    <div class="input-group-append">
                                        <button class="btn btn-light border border-left-0 text-muted toggle-password" type="button" data-target="#resetPasswordConfirm" title="Show/hide password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="resetMatchStatus" class="mt-1"></div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block shadow-sm py-2 font-weight-bold mb-3">
                                <i class="fas fa-check-circle mr-1"></i> Update Password
                            </button>
                        </form>

                        <div class="text-center pt-3 border-top">
                            <a href="<?= url_to('login') ?>" class="text-primary font-weight-bold small">
                                Return to Sign In
                            </a>
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

    var pInput = document.getElementById('resetPassword');
    var cInput = document.getElementById('resetPasswordConfirm');
    var sBar = document.getElementById('resetStrengthBar');
    var sText = document.getElementById('resetStrengthText');
    var mStatus = document.getElementById('resetMatchStatus');

    function checkResetStrength() {
        if (!pInput || !sBar || !sText) return;
        var val = pInput.value;
        var score = 0;
        if (val.length >= 8) score++;
        if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score++;
        if (/\d/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        if (val.length === 0) {
            sBar.style.width = '0%';
            sBar.className = 'progress-bar';
            sText.textContent = 'Password Strength';
            sText.className = 'small text-muted font-weight-bold';
        } else if (score === 1) {
            sBar.style.width = '25%';
            sBar.className = 'progress-bar bg-danger';
            sText.textContent = 'Weak';
            sText.className = 'small text-danger font-weight-bold';
        } else if (score === 2) {
            sBar.style.width = '50%';
            sBar.className = 'progress-bar bg-warning';
            sText.textContent = 'Fair';
            sText.className = 'small text-warning font-weight-bold';
        } else if (score === 3) {
            sBar.style.width = '75%';
            sBar.className = 'progress-bar bg-info';
            sText.textContent = 'Good';
            sText.className = 'small text-info font-weight-bold';
        } else if (score === 4) {
            sBar.style.width = '100%';
            sBar.className = 'progress-bar bg-success';
            sText.textContent = 'Strong';
            sText.className = 'small text-success font-weight-bold';
        }

        checkResetMatch();
    }

    function checkResetMatch() {
        if (!cInput || !pInput || !mStatus) return;
        var p = pInput.value;
        var c = cInput.value;
        if (c.length === 0) {
            mStatus.innerHTML = '';
        } else if (p === c) {
            mStatus.innerHTML = '<span class="text-success small font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Passwords match</span>';
        } else {
            mStatus.innerHTML = '<span class="text-danger small font-weight-bold"><i class="fas fa-times-circle mr-1"></i> Passwords do not match</span>';
        }
    }

    if (pInput) pInput.addEventListener('input', checkResetStrength);
    if (cInput) cInput.addEventListener('input', checkResetMatch);
});
</script>

<?= view('headers_footers/footer_landing') ?>