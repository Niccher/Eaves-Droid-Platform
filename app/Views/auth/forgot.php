<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>Prj Images - Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
          content="Prj Images is a mobile data analysis platform that transforms raw mobile data into actionable insights through advanced analysis and visualization"/>
    <meta name="keywords"
          content="mobile data analysis, android data collection, data visualization, call analysis, SMS correlation, file structure generation"/>
    <meta content="domino" name="author"/>
    <meta content="support@chegecache.co.ke" name="support"/>
    <meta content="https://chegecache.co.ke/" name="Website"/>
    <meta content="Prj Images" name="application-name"/>
    <meta content="mobile data intelligence, data analysis platform" name="keywords"/>
    <!-- favicon -->
    <link rel="shortcut icon" href="<?= base_url('images/favicon.ico') ?>">
    <!-- Bootstrap -->
    <link href="<?php echo base_url('assets/landing/css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css"/>
    <!-- Icons -->
    <link href="<?php echo base_url('assets/landing/css/materialdesignicons.min.css'); ?>" rel="stylesheet"
          type="text/css"/>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- Main css -->
    <link href="<?php echo base_url('assets/landing/css/style.min.css'); ?>" rel="stylesheet" type="text/css"
          id="theme-opt"/>
    <link href="<?php echo base_url('assets/landing/css/colors/default.css'); ?>" rel="stylesheet" id="color-opt">
    <style>
        .bg-overlay-primary {
            background: linear-gradient(90deg, #4e73df 0%, #4e73df 100%);
            opacity: 0.9;
            position: absolute;
            height: 100%;
            width: 100%;
            right: 0;
            bottom: 0;
            left: 0;
            top: 0;
        }
    </style>
</head>
<body>
<!-- Loader -->
<div id="preloader">
    <div id="status">
        <div class="spinner">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
    </div>
</div>
<!-- Loader -->
<!-- Back to home Start -->
<div class="back-to-home rounded d-none d-sm-block">
    <a href="<?= base_url('/') ?>" class="text-white rounded d-inline-block text-center"><i
                class="mdi mdi-home"></i></a>
</div>
<!-- Back to home End -->

<!-- Hero Start -->
<section class="bg-home d-flex align-items-center"
         style="background: url('<?= base_url('images/authentication.jpg') ?>') center center;">
    <div class="bg-overlay bg-overlay-primary"></div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="login_page bg-white rounded p-4">
                    <div class="text-center">
                        <h4 class="mb-3">Forgot Password</h4>
                    </div>

                    <!-- Display messages -->
                    <?php if (session('error') !== null) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif ?>

                    <?php if (session('message') !== null) : ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= session('message') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif ?>

                    <?php if (session('errors') !== null) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php if (is_array(session('errors'))) : ?>
                                <?php foreach (session('errors') as $error) : ?>
                                    <?= esc($error) ?><br>
                                <?php endforeach ?>
                            <?php else : ?>
                                <?= esc(session('errors')) ?>
                            <?php endif ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif ?>

                    <form class="login-form" method="post" action="<?= url_to('forgot') ?>">
                        <?= csrf_field() ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <p class="text-muted">Please enter your email address. You will receive a link to create
                                    a new password via email.</p>
                                <div class="mb-3">
                                    <label class="form-label">Email address <span class="text-danger">*</span></label>
                                    <input type="email"
                                           class="form-control"
                                           name="email"
                                           placeholder="Email"
                                           value="<?= old('email') ?>"
                                           required>
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-lg-12">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Send Reset Link</button>
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-12 text-center mt-3">
                                <p class="mb-0">
                                    <a href="<?= url_to('login') ?>" class="text-dark fw-bold">
                                        <i class="mdi mdi-arrow-left"></i> Back to Login
                                    </a>
                                </p>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </form>
                </div>
            </div>
            <!--end col-->
        </div>
        <!--end row-->
    </div>
    <!--end container-->
</section>
<!--end section-->
<!-- Hero End -->

<!-- javascript -->
<script src="<?php echo base_url('assets/landing/js/bootstrap.bundle.min.js'); ?>"></script>
<!-- Icons -->
<script src="<?php echo base_url('assets/landing/js/feather.min.js'); ?>"></script>
<!-- Icons -->
<script src="<?php echo base_url('assets/landing/js/switcher.js'); ?>"></script>
<!-- Main Js -->
<script src="<?php echo base_url('assets/landing/js/app.js'); ?>"></script>

<script>
    // Hide preloader when page loads
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            document.getElementById('preloader').style.display = 'none';
        }, 500);
    });
</script>
</body>
</html>