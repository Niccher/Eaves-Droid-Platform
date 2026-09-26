<?php
$loggedIn = function_exists('auth') && auth()->loggedIn();
$isAdmin = $loggedIn && auth()->user() && auth()->user()->can('admin.access');
$code = $error_code ?? 'Error';
$title = 'Something went wrong';
$icon = 'fa-exclamation-circle';
$color = 'danger';
$message = $message ?? ($error_message ?? 'An unexpected error occurred.');
?>

<?php if ($loggedIn): ?>
<?= view('headers_footers/head_users') ?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?= view($isAdmin ? 'headers_footers/sidebar_admin' : 'headers_footers/sidebar_users', [
        'user_info' => auth()->user()->toArray(),
        'sidebar_user_devices' => [],
        'active_device_id' => null,
        'user_token' => [],
    ]) ?>
    <div class="content-wrapper">
<?php else: ?>
<?= view('headers_footers/head_landing') ?>
<?php endif; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card card-<?= $color ?> card-outline shadow-lg">
                <div class="card-header text-center bg-secondary text-white py-4">
                    <i class="fas <?= $icon ?> fa-4x mb-3"></i>
                    <h1 class="m-0 font-weight-bold" style="font-size: 4rem;"><?= esc($code) ?></h1>
                    <h3 class="m-0 mt-2"><?= $title ?></h3>
                </div>
                <div class="card-body text-center py-5">
                    <p class="lead mb-4"><?= esc($message) ?></p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary mx-1"><i class="fas fa-arrow-left mr-2"></i>Go Back</a>
                        <a href="<?= base_url($isAdmin ? 'admin/dashboard' : ($loggedIn ? 'home' : '')) ?>" class="btn btn-primary mx-1"><i class="fas fa-home mr-2"></i><?= $isAdmin ? 'Admin Dashboard' : ($loggedIn ? 'Dashboard' : 'Home') ?></a>
                    </div>
                </div>
                <div class="card-footer text-muted text-center small">
                    Error Code: <?= esc($code) ?> &mdash; <?= date('Y-m-d H:i:s') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($loggedIn): ?>
    </div>
    <?= view('headers_footers/footer_users') ?>
<?php else: ?>
<?= view('headers_footers/footer_landing') ?>
<?php endif; ?>
