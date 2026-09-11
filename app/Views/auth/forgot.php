<?= view('headers_footers/head_landing', ['pag' => 'forgot', 'page_title' => 'Forgot Password | Eaves Droid']) ?>

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

                        <h3 class="font-weight-bold mb-3 text-white">Account Recovery</h3>
                        <p class="text-white-50 small mb-4" style="line-height: 1.6;">
                            Safe and encrypted identity verification protocols designed to protect your forensic investigation feeds.
                        </p>

                        <!-- Highlights List -->
                        <div class="auth-highlights mb-4">
                            <div class="d-flex align-items-start mb-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fas fa-key font-xs"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1 text-white">Time-Limited Reset Tokens</h6>
                                    <p class="text-white-50 small mb-0">Cryptographically signed, single-use reset links that expire automatically within 1 hour.</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start mb-3">
                                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center mr-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fas fa-network-wired font-xs"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1 text-white">Offline Emergency Mode</h6>
                                    <p class="text-white-50 small mb-0">Working on an air-gapped or intranet setup? Use offline reset without external email dependencies.</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mr-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fas fa-user-shield font-xs"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1 text-white">Anti-Harvesting Defense</h6>
                                    <p class="text-white-50 small mb-0">Rate limiting and generic response codes prevent unauthorized user enumeration.</p>
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
                        <div class="mb-4">
                            <a href="<?= url_to('login') ?>" class="text-muted small font-weight-bold">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Sign In
                            </a>
                        </div>

                        <!-- Form Title -->
                        <div class="mb-4">
                            <h3 class="font-weight-bold text-dark mb-1">Forgot Password</h3>
                            <p class="text-muted small">Enter your account email to receive a secure password recovery link.</p>
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

                        <form action="<?= url_to('forgot') ?>" method="post">
                            <?= csrf_field() ?>

                            <div class="form-group mb-4">
                                <label class="small font-weight-bold text-secondary mb-1">Registered Email Address</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-envelope text-muted"></i></span>
                                    </div>
                                    <input type="email" name="email" class="form-control border-left-0 pl-0" placeholder="name@domain.com" value="<?= old('email') ?>" required autofocus>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block shadow-sm py-2 font-weight-bold mb-3">
                                <i class="fas fa-paper-plane mr-1"></i> Send Recovery Link
                            </button>
                        </form>

                        <div class="text-center pt-3 border-top">
                            <p class="mb-2">
                                <a href="<?= url_to('forgot-offline') ?>" class="text-warning small font-weight-bold">
                                    <i class="fas fa-bolt mr-1"></i> Reset Password Offline (Air-Gapped)
                                </a>
                            </p>
                            <p class="text-muted small mb-0">
                                Remember your password? 
                                <a href="<?= url_to('login') ?>" class="text-primary font-weight-bold">Sign in here</a>
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('headers_footers/footer_landing') ?>