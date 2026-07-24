<?php
$actionLabels = [
    'login_password'     => ['label' => 'Password Login',     'icon' => 'fa-sign-in-alt', 'color' => 'success'],
    'login_token'        => ['label' => 'Token Login',         'icon' => 'fa-key',         'color' => 'primary'],
    'login_qr'           => ['label' => 'QR Login',            'icon' => 'fa-qrcode',      'color' => 'info'],
    'login_nfc'          => ['label' => 'NFC Login',           'icon' => 'fa-mobile-alt',  'color' => 'info'],
    'login_android'      => ['label' => 'Android Login',       'icon' => 'fa-robot',       'color' => 'primary'],
    'login_token_android'=> ['label' => 'Android Token Login', 'icon' => 'fa-robot',       'color' => 'primary'],
    'token_verification' => ['label' => 'Token Verification',  'icon' => 'fa-key',         'color' => 'secondary'],
    'logout'             => ['label' => 'Logout',              'icon' => 'fa-sign-out-alt','color' => 'secondary'],
    'register'           => ['label' => 'Registration',        'icon' => 'fa-user-plus',   'color' => 'success'],
    'password_forgot'    => ['label' => 'Forgot Password',     'icon' => 'fa-question-circle','color' => 'warning'],
    'password_reset'     => ['label' => 'Password Reset',      'icon' => 'fa-key',         'color' => 'danger'],
    'password_reset_offline' => ['label' => 'Offline Password Reset','icon' => 'fa-key',  'color' => 'danger'],
];

function formatActionType(string $type): array {
    global $actionLabels;
    return $actionLabels[$type] ?? ['label' => ucfirst(str_replace('_', ' ', $type)), 'icon' => 'fa-circle', 'color' => 'secondary'];
}

function detectSource(array $log): string {
    $ua = $log['user_agent'] ?? '';
    if (stripos($ua, 'okhttp') !== false || stripos($ua, 'android') !== false) {
        return 'ANDROID_APP';
    }
    $browser = $log['browser'] ?? '';
    if (!empty($browser)) return $browser;
    return 'Web';
}
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-sign-in-alt mr-1"></i> Access Logs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/logs') ?>">Logs</a></li>
                        <li class="breadcrumb-item active">Access</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="btn-group">
                        <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-outline-primary">All</a>
                        <a href="<?= base_url('admin/logs/access') ?>" class="btn btn-sm btn-primary">Access</a>
                        <a href="<?= base_url('admin/logs/errors') ?>" class="btn btn-sm btn-outline-danger">Errors</a>
                        <a href="<?= base_url('admin/logs/api') ?>" class="btn btn-sm btn-outline-info">API</a>
                    </div>
                    <div class="card-tools">
                        <span class="badge badge-primary"><?= number_format($actionTotal) ?> total actions</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover" id="accessTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Source</th>
                                <th>IP Address</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($actions)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No access logs found.</td></tr>
                            <?php else: ?>
                            <?php foreach ($actions as $log): ?>
                            <?php $actionInfo = formatActionType($log['action_type'] ?? ''); ?>
                            <?php $source = detectSource($log); ?>
                            <tr>
                                <td class="text-nowrap">
                                    <small><?= htmlspecialchars($log['date'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <i class="fas fa-user-circle text-muted mr-1"></i>
                                    <?= htmlspecialchars($log['username'] ?? 'Guest') ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $actionInfo['color'] ?>">
                                        <i class="fas <?= $actionInfo['icon'] ?> mr-1"></i>
                                        <?= $actionInfo['label'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($source === 'ANDROID_APP'): ?>
                                    <span class="badge badge-dark"><i class="fas fa-robot mr-1"></i> ANDROID_APP</span>
                                    <?php else: ?>
                                    <span class="badge badge-secondary"><i class="fas fa-globe mr-1"></i> Web</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code>
                                </td>
                                <td>
                                    <?php if ($log['success']): ?>
                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Success</span>
                                    <?php else: ?>
                                    <span class="badge badge-danger"><i class="fas fa-times mr-1"></i> Failed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#accessTable').DataTable({
        order: [[0, 'desc']],
        searching: false,
        paging: false,
        responsive: true,
    });
});
</script>