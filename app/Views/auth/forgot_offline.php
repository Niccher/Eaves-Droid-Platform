<?= view('headers_footers/head_landing', ['pag' => 'forgot_offline', 'page_title' => 'Reset Password Offline | Eaves Droid']) ?>

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
                                <span class="badge badge-warning px-2 py-1 small font-weight-normal text-dark"><i class="fas fa-bolt mr-1"></i> Offline Mode</span>
                            </div>
                        </div>

                        <h3 class="font-weight-bold mb-3 text-white">Emergency Offline Recovery</h3>
                        <p class="text-white-50 small mb-4" style="line-height: 1.6;">
                            Direct on-premise account recovery designed for closed intranet networks, staging environments, and air-gapped forensic labs.
                        </p>

                        <!-- Highlights List -->
                        <div class="auth-highlights mb-4">
                            <div class="d-flex align-items-start mb-3">
                                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center mr-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fas fa-plug font-xs"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1 text-white">No Mail Server Required</h6>
                                    <p class="text-white-50 small mb-0">Reset directly on your deployment without configuring external SMTP relays or outbound internet.</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start mb-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fas fa-shield-alt font-xs"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1 text-white">Local Verification Checks</h6>
                                    <p class="text-white-50 small mb-0">Verifies account presence in database and securely updates the password hash immediately.</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mr-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fas fa-undo-alt font-xs"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1 text-white">Instant Resumption</h6>
                                    <p class="text-white-50 small mb-0">Instantly sign back into your dashboard upon completion without email delays.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Badges & Guarantee -->
                    <div class="pt-4 border-top border-secondary">
                        <div class="d-flex flex-wrap align-items-center justify-content-between small text-white-50">
                            <span class="mr-2 mb-1"><i class="fas fa-code-branch text-primary mr-1"></i> WebApp: <strong>v<?= esc($appVersion) ?></strong></span>
                            <span class="mr-2 mb-1"><i class="fab fa-android text-success mr-1"></i> Android: <strong>v<?= esc($androidVersion) ?></strong></span>
                            <span class="mb-1"><i class="fas fa-robot text-warning mr-1"></i> ML Engine: <strong>v<?= esc($mlVersion) ?></strong></span>
                        </div>
                        <div class="mt-2 small text-white-50">
                            <i class="fas fa-server text-warning mr-1"></i> Self-Hosted Air-Gapped Ready
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
                            <span class="badge badge-warning px-2 py-1 small">Offline Mode</span>
                        </div>

                        <!-- Top Link -->
                        <div class="mb-4">
                            <a href="<?= url_to('login') ?>" class="text-muted small font-weight-bold">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Sign In
                            </a>
                        </div>

                        <!-- Form Title -->
                        <div class="mb-4">
                            <h3 class="font-weight-bold text-dark mb-1">Reset Password Offline</h3>
                            <p class="text-muted small">Enter your email and new credentials to update your password directly.</p>
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
                            <div class="text-center mt-3 mb-3">
                                <a href="<?= url_to('login') ?>" class="btn btn-primary btn-block shadow-sm font-weight-bold">
                                    <i class="fas fa-sign-in-alt mr-1"></i> Login with New Password
                                </a>
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

                        <?php if (session('message') === null) : ?>
                        <form action="<?= url_to('forgot-offline') ?>" method="post">
                            <?= csrf_field() ?>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-secondary mb-1">Account Email Address</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-envelope text-muted"></i></span>
                                    </div>
                                    <input type="email" name="email" class="form-control border-left-0 pl-0" placeholder="name@domain.com" value="<?= old('email') ?>" required autofocus>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-secondary mb-1">New Password (min 8 chars)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-lock text-muted"></i></span>
                                    </div>
                                    <input type="password" id="offlinePassword" name="password" class="form-control border-left-0 border-right-0 pl-0" placeholder="New password" required minlength="8">
                                    <div class="input-group-append">
                                        <button class="btn btn-light border border-left-0 text-muted toggle-password" type="button" data-target="#offlinePassword" title="Show/hide password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="small font-weight-bold text-secondary mb-1">Confirm New Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-lock text-muted"></i></span>
                                    </div>
                                    <input type="password" id="offlinePasswordConfirm" name="password_confirm" class="form-control border-left-0 border-right-0 pl-0" placeholder="Confirm new password" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-light border border-left-0 text-muted toggle-password" type="button" data-target="#offlinePasswordConfirm" title="Show/hide password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-warning btn-block shadow-sm py-2 font-weight-bold text-dark mb-3">
                                <i class="fas fa-key mr-1"></i> Reset Password Offline
                            </button>
                        </form>
                        <?php endif; ?>

                        <div class="text-center pt-3 border-top">
                            <p class="mb-2">
                                <a href="<?= url_to('forgot') ?>" class="text-primary small font-weight-bold">
                                    <i class="fas fa-envelope mr-1"></i> Standard Email Reset
                                </a>
                            </p>
                            <p class="text-muted small mb-0">
                                Return to 
                                <a href="<?= url_to('login') ?>" class="text-primary font-weight-bold">Sign in</a>
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
});
</script>

<?= view('headers_footers/footer_landing') ?>
