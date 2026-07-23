<?php
$loggedIn = function_exists('auth') && auth()->loggedIn();
$isAdmin = $loggedIn && auth()->user() && auth()->user()->can('admin.access');
$msg = $message ?? 'We hit a snag. Please try again later.';
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
            <div class="card card-danger card-outline shadow-lg">
                <div class="card-header text-center bg-danger text-white py-4">
                    <i class="fas fa-exclamation-triangle fa-4x mb-3"></i>
                    <h1 class="m-0 font-weight-bold">Oops!</h1>
                    <h3 class="m-0 mt-2">Something went wrong.</h3>
                </div>
                <div class="card-body text-center py-5">
                    <p class="lead mb-4"><?= esc($msg) ?></p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="javascript:location.reload()" class="btn btn-outline-secondary mx-1"><i class="fas fa-sync mr-2"></i>Try Again</a>
                        <a href="<?= base_url($isAdmin ? 'admin/dashboard' : ($loggedIn ? 'home' : '')) ?>" class="btn btn-primary mx-1"><i class="fas fa-home mr-2"></i><?= $isAdmin ? 'Admin Dashboard' : ($loggedIn ? 'Dashboard' : 'Home') ?></a>
                    </div>
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
