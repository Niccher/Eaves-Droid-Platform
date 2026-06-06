<?= view('headers_footers/head_landing', ['pag' => 'register']) ?>

<div class="content-wrapper" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 50px 0;">
    <div class="register-box">
        <div class="register-logo">
            <a href="<?= base_url('/') ?>" class="text-white">
                <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8; width: 60px; height: 60px;">
                <span class="font-weight-light text-bold">Prj Images</span>
            </a>
        </div>

        <div class="card card-outline card-primary shadow-lg">
            <div class="card-body register-card-body rounded">
                <p class="login-box-msg text-bold">Register a new membership</p>

                <!-- Display validation errors -->
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

                <form action="<?= url_to('register') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="input-group mb-3">
                        <input type="text" name="username" class="form-control form-control-lg" placeholder="Full name" value="<?= old('username') ?>" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" value="<?= old('email') ?>" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="password_confirm" class="form-control form-control-lg" placeholder="Retype password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="agreeTerms" name="terms" value="agree" required>
                                <label for="agreeTerms">
                                    I agree to the <a href="<?= base_url('terms') ?>">terms</a>
                                </label>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block shadow-sm text-bold">Register</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>

                <div class="mt-4 text-center">
                    <a href="<?= url_to('login') ?>" class="text-primary small text-bold">I already have a membership</a>
                </div>
            </div>
            <!-- /.form-box -->
        </div><!-- /.card -->
    </div>
</div>

<?= view('headers_footers/footer_landing') ?>


