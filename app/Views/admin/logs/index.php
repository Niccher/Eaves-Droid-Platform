<?php
$actionLabels = [
    'login_password'      => ['label' => 'Password Login',     'icon' => 'fa-sign-in-alt', 'color' => 'success'],
    'login_token'         => ['label' => 'Token Login',         'icon' => 'fa-key',         'color' => 'primary'],
    'login_qr'            => ['label' => 'QR Login',            'icon' => 'fa-qrcode',      'color' => 'info'],
    'login_token_android' => ['label' => 'Android Token Login', 'icon' => 'fa-robot',       'color' => 'primary'],
    'token_verification'  => ['label' => 'Token Verification',  'icon' => 'fa-key',         'color' => 'secondary'],
    'file_upload'         => ['label' => 'File Upload',         'icon' => 'fa-upload',      'color' => 'info'],
    'device_registration' => ['label' => 'Device Registration', 'icon' => 'fa-mobile-alt',  'color' => 'warning'],
    'profile_update'      => ['label' => 'Profile Update',      'icon' => 'fa-user-edit',   'color' => 'primary'],
    'token_regenerate'    => ['label' => 'Token Regenerate',    'icon' => 'fa-sync',        'color' => 'warning'],
    'token_create'        => ['label' => 'Token Created',       'icon' => 'fa-plus-circle', 'color' => 'success'],
    'register'            => ['label' => 'Registration',        'icon' => 'fa-user-plus',   'color' => 'success'],
    'password_forgot'     => ['label' => 'Forgot Password',     'icon' => 'fa-question-circle','color' => 'warning'],
    'password_reset'      => ['label' => 'Password Reset',      'icon' => 'fa-key',         'color' => 'danger'],
    'password_reset_offline' => ['label' => 'Offline Password Reset','icon' => 'fa-key',  'color' => 'danger'],
    'logout'              => ['label' => 'Logout',              'icon' => 'fa-sign-out-alt','color' => 'secondary'],
    'maintenance_blocked' => ['label' => 'Maintenance Block',   'icon' => 'fa-shield-alt',  'color' => 'warning'],
];

function fmtAction(string $type): array {
    global $actionLabels;
    if (str_starts_with($type, 'remote_cmd_')) {
        $cmd = substr($type, 11);
        return ['label' => 'FCM: ' . ucfirst(str_replace('_', ' ', $cmd)), 'icon' => 'fa-fire', 'color' => 'info'];
    }
    if (str_starts_with($type, 'admin_')) {
        $cmd = substr($type, 6);
        return ['label' => 'Admin: ' . ucfirst(str_replace('_', ' ', $cmd)), 'icon' => 'fa-user-shield', 'color' => 'danger'];
    }
    // New descriptive action types (fetch_sms, play_beep, reset_app, etc.)
    $descriptive = [
        'fetch_sms' => ['label' => 'Fetch SMS', 'icon' => 'fa-fire', 'color' => 'info'],
        'fetch_calls' => ['label' => 'Fetch Calls', 'icon' => 'fa-fire', 'color' => 'info'],
        'fetch_contacts' => ['label' => 'Fetch Contacts', 'icon' => 'fa-fire', 'color' => 'info'],
        'capture_photo' => ['label' => 'Capture Photo', 'icon' => 'fa-fire', 'color' => 'info'],
        'record_audio' => ['label' => 'Record Audio', 'icon' => 'fa-fire', 'color' => 'info'],
        'play_beep' => ['label' => 'Play Beep', 'icon' => 'fa-fire', 'color' => 'info'],
        'play_siren' => ['label' => 'Play Siren', 'icon' => 'fa-fire', 'color' => 'info'],
        'reset_app' => ['label' => 'Reset App', 'icon' => 'fa-fire', 'color' => 'warning'],
        'deactivate_app' => ['label' => 'Deactivate App', 'icon' => 'fa-fire', 'color' => 'warning'],
        'logout_user' => ['label' => 'Logout User', 'icon' => 'fa-fire', 'color' => 'warning'],
        'update_settings' => ['label' => 'Update Settings', 'icon' => 'fa-fire', 'color' => 'info'],
        'open_permission' => ['label' => 'Open Permission', 'icon' => 'fa-fire', 'color' => 'info'],
        'uninstall_preserve' => ['label' => 'Uninstall (Keep)', 'icon' => 'fa-fire', 'color' => 'danger'],
        'uninstall_wipe' => ['label' => 'Uninstall (Wipe)', 'icon' => 'fa-fire', 'color' => 'danger'],
        'sync_all' => ['label' => 'Sync All', 'icon' => 'fa-fire', 'color' => 'info'],
        'sync_data' => ['label' => 'Sync Data', 'icon' => 'fa-fire', 'color' => 'info'],
        'locate_device' => ['label' => 'Locate Device', 'icon' => 'fa-fire', 'color' => 'info'],
        'wipe_logs' => ['label' => 'Wipe Logs', 'icon' => 'fa-fire', 'color' => 'warning'],
        'reactivate_app' => ['label' => 'Reactivate App', 'icon' => 'fa-fire', 'color' => 'info'],
        'search_data' => ['label' => 'Search Data', 'icon' => 'fa-fire', 'color' => 'info'],
        'start_tracking' => ['label' => 'Start Tracking', 'icon' => 'fa-fire', 'color' => 'info'],
        'fetch_file' => ['label' => 'Fetch File', 'icon' => 'fa-fire', 'color' => 'info'],
    ];
    if (isset($descriptive[$type])) {
        return $descriptive[$type];
    }
    if (str_starts_with($type, 'upload_')) {
        $cat = substr($type, 7);
        return ['label' => 'Upload: ' . ucfirst(str_replace('_', ' ', $cat)), 'icon' => 'fa-upload', 'color' => 'info'];
    }
    return $actionLabels[$type] ?? ['label' => ucfirst(str_replace('_', ' ', $type)), 'icon' => 'fa-circle', 'color' => 'secondary'];
}
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-clipboard-list mr-1"></i> System Logs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item active">Logs</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (session()->getFlashdata('message')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= session()->getFlashdata('message') ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div class="btn-group">
                        <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-primary">All</a>
                        <a href="<?= base_url('admin/logs/access') ?>" class="btn btn-sm btn-outline-primary">Access</a>
                        <a href="<?= base_url('admin/logs/errors') ?>" class="btn btn-sm btn-outline-danger">Errors</a>
                        <a href="<?= base_url('admin/logs/api') ?>" class="btn btn-sm btn-outline-info">API</a>
                        <a href="<?= base_url('admin/logs/fcm') ?>" class="btn btn-sm btn-outline-info">FCM</a>
                        <a href="<?= base_url('admin/logs/maintenance') ?>" class="btn btn-sm btn-outline-warning">Maintenance</a>
                    </div>
                    <div class="card-tools">
                        <form method="post" action="<?= base_url('admin/logs/clear') ?>" style="display:inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Clear all logs?')"><i class="fas fa-trash"></i> Clear</button>
                        </form>
                        <form method="post" action="<?= base_url('admin/logs/export') ?>" style="display:inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-download"></i> Export CSV</button>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover" id="logsTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Category</th>
                                <th>Severity</th>
                                <th>IP</th>
                                <th>Status</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No logs found.</td></tr>
                            <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                            <?php $ai = fmtAction($log['action_type'] ?? '');
                                  $nv = !empty($log['new_values']) ? json_decode($log['new_values'], true) : [];
                                  $isFcm = str_starts_with($log['action_type'] ?? '', 'remote_cmd_');
                            ?>
                            <tr>
                                <td class="text-nowrap"><small><?= htmlspecialchars($log['date'] ?? '-') ?></small></td>
                                <td>
                                    <i class="fas fa-user-circle text-muted mr-1"></i>
                                    <?= htmlspecialchars($log['username'] ?? 'System') ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $ai['color'] ?>">
                                        <i class="fas <?= $ai['icon'] ?> mr-1"></i>
                                        <?= $ai['label'] ?>
                                    </span>
                                </td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($log['category'] ?? '-') ?></span></td>
                                <td>
                                    <span class="badge badge-<?= ($log['severity'] ?? 'low') === 'critical' ? 'danger' : (($log['severity'] ?? 'low') === 'high' ? 'warning' : (($log['severity'] ?? 'low') === 'medium' ? 'info' : 'secondary')) ?>">
                                        <?= htmlspecialchars($log['severity'] ?? 'low') ?>
                                    </span>
                                </td>
                                <td><code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td>
                                <td>
                                    <?php if ($log['success']): ?>
                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Success</span>
                                    <?php else: ?>
                                    <span class="badge badge-danger"><i class="fas fa-times mr-1"></i> Failed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isFcm && !empty($nv['command'])): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-terminal mr-1"></i><?= htmlspecialchars(str_replace('cmd_', '', $nv['command'])) ?>
                                        <?php if (!empty($nv['payload']) && $nv['payload'] !== 'all'): ?>/ <code><?= htmlspecialchars($nv['payload']) ?></code><?php endif; ?>
                                    </small>
                                    <?php elseif (!empty($log['error_message'])): ?>
                                    <small class="text-danger" title="<?= htmlspecialchars($log['error_message']) ?>">
                                        <i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars(mb_substr($log['error_message'], 0, 40)) ?>
                                    </small>
                                    <?php else: ?>
                                    <span class="text-muted small">—</span>
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
    $('#logsTable').DataTable({
        order: [[0, 'desc']],
        searching: false,
        paging: false,
        responsive: true,
    });
});
</script>