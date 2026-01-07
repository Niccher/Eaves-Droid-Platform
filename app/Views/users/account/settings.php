<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-cogs text-primary mr-2"></i>
                            Account Settings
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-shield-alt text-primary mr-1"></i>
                                Security Level: <b>Standard</b>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Manage your account settings, API tokens, and security preferences</p>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <!-- API Tokens Card -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-key mr-2"></i>
                                API Tokens Management
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle mr-2"></i> Token Information</h5>
                                <p class="mb-0">API tokens are used to authenticate your Android device with our services. Keep them secure and regenerate if compromised.</p>
                            </div>

                            <!-- Current Token Display -->
                            <div class="form-group">
                                <label for="currentToken">
                                    <i class="fab fa-android mr-2 text-success"></i>
                                    Current Android Token
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="currentToken"
                                           value="<?php echo htmlspecialchars($user_token['Token'] ?? 'No token found'); ?>"
                                           readonly style="font-family: 'Courier New', monospace;">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="copyTokenBtn"
                                                data-toggle="tooltip" title="Copy to clipboard">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" type="button" id="showTokenBtn"
                                                data-toggle="tooltip" title="Show/Hide token">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    This token is required for Android app authentication. Copy and paste it into your Android app settings.
                                </small>
                            </div>

                            <!-- Token Details -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Token Created</label>
                                        <input type="text" class="form-control bg-light"
                                               value="<?php echo !empty($user_token['Token_Created']) ? date('M d, Y H:i', strtotime($user_token['Token_Created'])) : 'Unknown'; ?>"
                                               readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Token Expiry</label>
                                        <input type="text" class="form-control bg-light"
                                               value="<?php echo $tokenExpiry ?? 'Never'; ?>"
                                               readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Token Status -->
                            <div class="form-group">
                                <label>Token Status</label>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($user_token['Token_Status'])): ?>
                                        <?php if ($user_token['Token_Status'] == '00'): ?>
                                            <span class="badge badge-success p-2 mr-2">
                                                <i class="fas fa-check-circle mr-1"></i> Active
                                            </span>
                                            <span class="text-success">
                                                <i class="fas fa-circle mr-1"></i> Token is active and ready for use
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger p-2 mr-2">
                                                <i class="fas fa-times-circle mr-1"></i> Inactive
                                            </span>
                                            <span class="text-danger">
                                                <i class="fas fa-circle mr-1"></i> Token has been revoked or expired
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-warning p-2 mr-2">
                                            <i class="fas fa-exclamation-circle mr-1"></i> No Token
                                        </span>
                                        <span class="text-warning">
                                            <i class="fas fa-circle mr-1"></i> No active token found
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Regenerate Token Section -->
                            <div class="border-top mt-4 pt-4">
                                <h5>
                                    <i class="fas fa-sync-alt mr-2 text-warning"></i>
                                    Regenerate Token
                                </h5>
                                <p class="text-muted">Regenerating your token will invalidate the current token and require updating it in your Android app.</p>

                                <form action="<?php echo base_url('account/regenerateToken'); ?>" method="post" id="regenerateForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                                    <div class="alert alert-warning">
                                        <h5><i class="fas fa-exclamation-triangle mr-2"></i> Important Notice</h5>
                                        <p class="mb-0">Regenerating your token will:</p>
                                        <ul class="mb-0 mt-2">
                                            <li>Invalidate all current Android connections</li>
                                            <li>Require re-authentication on all devices</li>
                                            <li>Disconnect all active sessions</li>
                                            <li>Require updating the token in your Android app</li>
                                        </ul>
                                    </div>

                                    <button type="button" class="btn btn-danger" id="regenerateBtn">
                                        <i class="fas fa-sync-alt mr-1"></i> Regenerate Token
                                    </button>
                                    <a href="<?php echo base_url('account/home'); ?>" class="btn btn-outline-secondary ml-2">
                                        <i class="fas fa-times mr-1"></i> Cancel
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Connected Devices Card -->
                    <div class="card card-success mt-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-laptop mr-2"></i>
                                Connected Devices
                                <span class="badge badge-light ml-2"><?php echo count($user_devices); ?></span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($user_devices)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                        <tr>
                                            <th>Device</th>
                                            <th>IP Address</th>
                                            <th>Last Activity</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($user_devices as $device): ?>
                                            <?php
                                            $isActive = isset($device['last_seen']) && strtotime($device['last_seen']) > strtotime('-30 minutes');
                                            $statusColor = $isActive ? 'success' : 'secondary';
                                            $statusText = $isActive ? 'Active' : 'Inactive';
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <i class="fas fa-<?php echo $device['device_type'] == 'mobile' ? 'mobile-alt' : 'laptop'; ?> fa-2x text-<?php echo $device['device_type'] == 'mobile' ? 'success' : 'primary'; ?>"></i>
                                                        </div>
                                                        <div>
                                                            <div class="font-weight-bold"><?php echo htmlspecialchars($device['device_name']); ?></div>
                                                            <small class="text-muted">
                                                                <?php echo htmlspecialchars($device['os']); ?> · <?php echo htmlspecialchars($device['browser']); ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <code><?php echo htmlspecialchars($device['ip_address']); ?></code>
                                                </td>
                                                <td>
                                                    <?php echo $device['last_seen_formatted']; ?>
                                                </td>
                                                <td>
                                                        <span class="badge badge-<?php echo $statusColor; ?>">
                                                            <i class="fas fa-circle mr-1"></i>
                                                            <?php echo $statusText; ?>
                                                        </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-laptop fa-3x text-muted mb-3"></i>
                                    <h5>No Connected Devices</h5>
                                    <p class="text-muted">No devices are currently connected to your account</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Active Sessions Card -->
                    <div class="card card-warning mt-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-clock mr-2"></i>
                                Active Sessions
                                <span class="badge badge-light ml-2"><?php echo $activeSessions; ?></span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($user_sessions)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                        <tr>
                                            <th>Session ID</th>
                                            <th>Device</th>
                                            <th>Last Activity</th>
                                            <th>Activities</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($user_sessions as $session): ?>
                                            <?php
                                            $sessionId = substr($session['session_id'] ?? '', 0, 12) . '...';
                                            $isActive = $session['is_active'] ?? false;
                                            $statusColor = $isActive ? 'success' : 'secondary';
                                            $statusText = $isActive ? 'Active' : 'Expired';
                                            ?>
                                            <tr>
                                                <td>
                                                    <code class="small"><?php echo htmlspecialchars($sessionId); ?></code>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold"><?php echo htmlspecialchars($session['device_name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($session['ip_address']); ?></small>
                                                </td>
                                                <td>
                                                    <?php echo $session['last_activity_formatted']; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info"><?php echo $session['activity_count']; ?></span>
                                                </td>
                                                <td>
                                                        <span class="badge badge-<?php echo $statusColor; ?>">
                                                            <i class="fas fa-circle mr-1"></i>
                                                            <?php echo $statusText; ?>
                                                        </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-user-clock fa-3x text-muted mb-3"></i>
                                    <h5>No Active Sessions</h5>
                                    <p class="text-muted">No active sessions found for your account</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .input-group .btn {
        border-color: #ced4da;
    }

    .card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 0.25rem;
    }

    .card-header {
        border-bottom: 1px solid rgba(0,0,0,.125);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,.02);
    }

    code {
        background-color: #f8f9fa;
        padding: 2px 4px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
    }

    .alert {
        border-radius: 0.25rem;
    }
</style>

<script>
    $(document).ready(function() {
        // Copy token functionality
        $('#copyTokenBtn').click(function() {
            const token = $('#currentToken').val();
            navigator.clipboard.writeText(token).then(() => {
                const original = $(this).html();
                $(this).html('<i class="fas fa-check text-success"></i>');
                $(this).attr('title', 'Copied!');
                $(this).tooltip('dispose').tooltip();

                setTimeout(() => {
                    $(this).html(original);
                    $(this).attr('title', 'Copy to clipboard');
                    $(this).tooltip('dispose').tooltip();
                }, 2000);
            });
        });

        // Show/hide token
        let tokenVisible = false;
        $('#showTokenBtn').click(function() {
            const input = $('#currentToken');
            tokenVisible = !tokenVisible;

            if (tokenVisible) {
                input.attr('type', 'text');
                $(this).html('<i class="fas fa-eye-slash"></i>');
                $(this).attr('title', 'Hide token');
            } else {
                input.attr('type', 'password');
                $(this).html('<i class="fas fa-eye"></i>');
                $(this).attr('title', 'Show token');
            }

            $(this).tooltip('dispose').tooltip();
        });

        // Initialize token as hidden
        $('#currentToken').attr('type', 'password');

        // Regenerate token confirmation
        $('#regenerateBtn').click(function() {
            Swal.fire({
                title: 'Regenerate Token?',
                text: 'This will invalidate your current token and require updating your Android app. Are you sure?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, regenerate',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#regenerateForm').submit();
                }
            });
        });

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>