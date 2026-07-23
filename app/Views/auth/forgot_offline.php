<?= view('headers_footers/head_landing') ?>

<div class="content-wrapper" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 50px 0;">
    <div class="login-box">
        <div class="login-logo">
            <a href="<?= base_url('/') ?>" class="text-white">
                <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8; width: 60px; height: 60px;">
                <span class="font-weight-light text-bold">Eaves Droid</span>
            </a>
        </div>
        <div class="card card-outline card-primary shadow-lg">
            <div class="card-body login-card-body rounded">
                <p class="login-box-msg text-bold">Reset Password Offline</p>
                <p class="text-muted text-center small">Enter your email and a new password to reset directly (no email required).</p>

                <?php if (session('error') !== null) : ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?= session('error') ?>
                    </div>
                <?php endif ?>

                <?php if (session('message') !== null) : ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?= session('message') ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?= url_to('login') ?>" class="btn btn-primary btn-block shadow-sm text-bold">Login with new password</a>
                    </div>
                <?php endif ?>

                <?php if (session('errors') !== null) : ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
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
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" value="<?= old('email') ?>" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control form-control-lg" placeholder="New Password (min 8 chars)" required minlength="8">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="password_confirm" class="form-control form-control-lg" placeholder="Confirm New Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning btn-block shadow-sm text-bold">Reset Password Offline</button>
                        </div>
                    </div>
                </form>
                <?php endif ?>

                <div class="mt-4 text-center">
                    <p class="mb-1">
                        <a href="<?= url_to('forgot-password') ?>" class="text-primary small">Forgot Password (email)</a>
                    </p>
                    <p class="mb-0">
                        <a href="<?= url_to('login') ?>" class="text-primary small text-bold">Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('headers_footers/footer_landing') ?>
