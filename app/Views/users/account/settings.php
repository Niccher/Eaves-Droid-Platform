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
                                Account Settings & APIs
                            </h1>
                            <div class="ml-3">
                                <span class="badge badge-light border p-2">
                                    <i class="fas fa-key text-primary mr-1"></i>
                                    Security Level: <b>High</b>
                                </span>
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0">Manage your account settings, security, and API integrations</p>
                    </div>
                </div>
            </div>
            <!-- /.container-fluid -->
        </section>

        <!-- Quick Stats Row -->
        <section class="content mb-4">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo count($user_devices) ?? 0 ?></h3>
                                <p>Connected Devices</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <a href="#devicesTab" data-toggle="tab" class="small-box-footer">
                                Manage Devices <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo $activeSessions ?? 0 ?></h3>
                                <p>Active Sessions</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <a href="#" class="small-box-footer" data-toggle="modal" data-target="#sessionManagement">
                                View Sessions <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?php echo $securityEvents ?? 0 ?></h3>
                                <p>Security Events</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <a href="#" class="small-box-footer" data-toggle="modal" data-target="#securityLogs">
                                Review Events <i class="fas fa-search"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- Left Column - Settings Navigation -->
                    <div class="col-lg-3">
                        <!-- Settings Menu Card -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-sliders-h mr-2"></i>
                                    Settings Menu
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="nav flex-column nav-pills" id="settingsTabs" role="tablist" aria-orientation="vertical">
                                    <a class="nav-link active" id="api-tokens-tab" data-toggle="pill" href="#api-tokens" role="tab" aria-controls="api-tokens" aria-selected="true">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-info p-2">
                                                    <i class="fas fa-key"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">API Tokens</div>
                                                <small class="text-muted">Android & Web Tokens</small>
                                            </div>
                                        </div>
                                    </a>
                                    <a class="nav-link" id="devices-tab" data-toggle="pill" href="#devicesTab" role="tab" aria-controls="devicesTab" aria-selected="false">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-success p-2">
                                                    <i class="fas fa-mobile-alt"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Devices</div>
                                                <small class="text-muted">Connected Devices</small>
                                            </div>
                                            <span class="badge badge-light ml-auto"><?php echo count($user_devices) ?? 0 ?></span>
                                        </div>
                                    </a>
                                    <a class="nav-link" id="data-tab" data-toggle="pill" href="#dataTab" role="tab" aria-controls="dataTab" aria-selected="false">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-secondary p-2">
                                                    <i class="fas fa-database"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Data Management</div>
                                                <small class="text-muted">Export & Backup</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions Card -->
                        <div class="card card-success mt-4">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-bolt mr-2"></i>
                                    Quick Actions
                                </h3>
                            </div>
                            <div class="card-body">
                                <button type="button" class="btn btn-outline-primary btn-block mb-2" data-toggle="modal" data-target="#regenerateTokenModal">
                                    <i class="fas fa-key mr-2"></i> Regenerate Token
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Settings Content -->
                    <div class="col-lg-9">
                        <div class="tab-content" id="settingsContent">
                            <!-- API Tokens Tab -->
                            <div class="tab-pane fade show active" id="api-tokens" role="tabpanel" aria-labelledby="api-tokens-tab">
                                <div class="card card-primary card-outline">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-key mr-2"></i>
                                            API Tokens Management
                                        </h3>
                                        <div class="card-tools">
                                            <span class="badge badge-info">
                                                <i class="fas fa-android mr-1"></i> Android Integration
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="alert alert-info">
                                                    <h5><i class="fas fa-info-circle mr-2"></i> Token Security Notice</h5>
                                                    <p class="mb-0">API tokens are used to authenticate your devices and applications. Keep them secure and regenerate immediately if compromised.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card card-info">
                                                    <div class="card-header">
                                                        <h3 class="card-title">
                                                            <i class="fab fa-android mr-2"></i>
                                                            Android Client Token
                                                        </h3>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label for="androidToken">Current Token</label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" id="androidToken"
                                                                       value="<?php echo htmlspecialchars($user_token["Token"] ?? ''); ?>"
                                                                       readonly>
                                                                <div class="input-group-append">
                                                                    <button class="btn btn-outline-secondary" type="button" id="copyAndroidToken">
                                                                        <i class="fas fa-copy"></i>
                                                                    </button>
                                                                    <button class="btn btn-outline-secondary" type="button" id="showAndroidToken">
                                                                        <i class="fas fa-eye"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <small class="form-text text-muted">
                                                                <i class="fas fa-mobile-alt mr-1"></i> Used for Android app authentication
                                                            </small>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="tokenExpiry">Token Expiry</label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" id="tokenExpiry"
                                                                       value="<?php echo $tokenExpiry ?? 'Never'; ?>" readonly>
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text">
                                                                        <i class="far fa-calendar-alt"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#regenerateTokenModal">
                                                            <i class="fas fa-sync-alt mr-1"></i> Regenerate Token
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="card card-success">
                                                    <div class="card-header">
                                                        <h3 class="card-title">
                                                            <i class="fas fa-globe mr-2"></i>
                                                            Web API Token
                                                        </h3>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label for="webToken">Web API Token</label>
                                                            <div class="input-group">
                                                                <input type="password" class="form-control" id="webToken"
                                                                       value="<?php echo htmlspecialchars($web_token ?? 'Generate new token'); ?>"
                                                                       readonly>
                                                                <div class="input-group-append">
                                                                    <button class="btn btn-outline-secondary" type="button" id="copyWebToken">
                                                                        <i class="fas fa-copy"></i>
                                                                    </button>
                                                                    <button class="btn btn-outline-secondary" type="button" id="toggleWebToken">
                                                                        <i class="fas fa-eye"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <small class="form-text text-muted">
                                                                <i class="fas fa-code mr-1"></i> For API integrations and webhooks
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <button type="button" class="btn btn-success" id="generateWebToken">
                                                            <i class="fas fa-plus-circle mr-1"></i> Generate Web Token
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Devices Tab -->
                            <div class="tab-pane fade" id="devicesTab" role="tabpanel" aria-labelledby="devices-tab">
                                <div class="card card-success card-outline">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-mobile-alt mr-2"></i>
                                            Connected Devices Management
                                        </h3>
                                        <div class="card-tools">
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle mr-1"></i> <?php echo count($user_devices) ?? 0 ?> Active
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped">
                                                <thead class="thead-light">
                                                <tr>
                                                    <th>Device</th>
                                                    <th>IP Address</th>
                                                    <th>Last Connected</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($user_devices)): ?>
                                                    <?php foreach ($user_devices as $device): ?>
                                                        <?php
                                                        $ip = $device['IP'] ?? 'Unknown';
                                                        $timestamp = $device['Timestamps'] ?? time();
                                                        $action = $device['Action'] ?? 'Unknown';
                                                        $date = date('M d, Y', $timestamp);
                                                        $time = date('H:i:s', $timestamp);
                                                        $deviceType = strpos(strtolower($action), 'android') !== false ? 'Android' : 'Web';
                                                        $status = 'active';
                                                        $statusColor = 'success';
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="mr-3">
                                                                            <span class="badge badge-<?php echo $deviceType === 'Android' ? 'success' : 'primary'; ?> p-2">
                                                                                <i class="fas fa-<?php echo $deviceType === 'Android' ? 'mobile-alt' : 'laptop'; ?>"></i>
                                                                            </span>
                                                                    </div>
                                                                    <div>
                                                                        <div class="font-weight-bold"><?php echo $deviceType; ?> Device</div>
                                                                        <small class="text-muted"><?php echo htmlspecialchars($action); ?></small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <code class="text-dark"><?php echo htmlspecialchars($ip); ?></code>
                                                                <br>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                                                    <?php echo $device['Location'] ?? 'Unknown Location'; ?>
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <div class="text-dark"><?php echo $date; ?></div>
                                                                <small class="text-muted">
                                                                    <i class="far fa-clock mr-1"></i>
                                                                    <?php echo $time; ?>
                                                                </small>
                                                            </td>
                                                            <td>
                                                                    <span class="badge badge-<?php echo $statusColor; ?> p-2">
                                                                        <i class="fas fa-circle mr-1"></i>
                                                                        <?php echo ucfirst($status); ?>
                                                                    </span>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <button type="button" class="btn btn-outline-info" data-toggle="tooltip" title="View Details">
                                                                        <i class="fas fa-eye"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-outline-danger" data-toggle="tooltip" title="Disconnect">
                                                                        <i class="fas fa-ban"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center py-5">
                                                            <i class="fas fa-mobile-alt fa-3x text-muted mb-3"></i>
                                                            <h4>No Connected Devices</h4>
                                                            <p class="text-muted">No devices are currently connected to your account</p>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Management Tab -->
                            <div class="tab-pane fade" id="dataTab" role="tabpanel" aria-labelledby="data-tab">
                                <div class="card card-secondary card-outline">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-database mr-2"></i>
                                            Data Management
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Data management features will be available soon.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Regenerate Token Modal -->
    <div class="modal fade" id="regenerateTokenModal" tabindex="-1" role="dialog" aria-labelledby="regenerateTokenModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="regenerateTokenModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                        Regenerate Token
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-warning mr-2"></i> Important Notice</h5>
                        <p class="mb-0">Regenerating your token will:</p>
                        <ul class="mb-0 mt-2">
                            <li>Invalidate all current Android connections</li>
                            <li>Require re-authentication on all devices</li>
                            <li>Disconnect all active sessions</li>
                            <li>Require updating the token in your Android app</li>
                        </ul>
                    </div>
                    <p>Are you sure you want to proceed with token regeneration?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a href="<?php echo base_url('account/setting/token_generate'); ?>">
                        <button type="button" class="btn btn-danger">
                            <i class="fas fa-sync-alt mr-1"></i> Yes, Regenerate Token
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .info-box {
            border-radius: 0.25rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            transition: transform 0.2s ease;
        }

        .info-box:hover {
            transform: translateY(-2px);
        }

        .nav-pills .nav-link {
            border-radius: 0.25rem;
            margin-bottom: 5px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .nav-pills .nav-link:hover {
            background-color: rgba(0,0,0,0.05);
        }

        .nav-pills .nav-link.active {
            background-color: #007bff;
            box-shadow: 0 2px 4px rgba(0,123,255,.3);
        }

        .card {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }

        .input-group .btn {
            border-color: #ced4da;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,0.02);
        }

        @media (max-width: 768px) {
            .nav-pills .nav-link {
                padding: 10px;
            }

            .card-header .card-title {
                font-size: 1.1rem;
            }

            .btn-group {
                flex-wrap: wrap;
            }
        }
    </style>

    <script>
        $(document).ready(function() {
            // Copy token functionality
            $('#copyAndroidToken').click(function() {
                const token = $('#androidToken').val();
                navigator.clipboard.writeText(token).then(() => {
                    $(this).html('<i class="fas fa-check"></i>');
                    setTimeout(() => {
                        $(this).html('<i class="fas fa-copy"></i>');
                    }, 2000);
                });
            });

            $('#copyWebToken').click(function() {
                const token = $('#webToken').val();
                navigator.clipboard.writeText(token).then(() => {
                    $(this).html('<i class="fas fa-check"></i>');
                    setTimeout(() => {
                        $(this).html('<i class="fas fa-copy"></i>');
                    }, 2000);
                });
            });

            // Show/hide token
            $('#showAndroidToken').click(function() {
                const input = $('#androidToken');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    $(this).html('<i class="fas fa-eye-slash"></i>');
                } else {
                    input.attr('type', 'password');
                    $(this).html('<i class="fas fa-eye"></i>');
                }
            });

            $('#toggleWebToken').click(function() {
                const input = $('#webToken');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    $(this).html('<i class="fas fa-eye-slash"></i>');
                } else {
                    input.attr('type', 'password');
                    $(this).html('<i class="fas fa-eye"></i>');
                }
            });

            // Generate web token
            $('#generateWebToken').click(function() {
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Generating...');
                setTimeout(() => {
                    const newToken = 'web_' + Math.random().toString(36).substr(2, 32);
                    $('#webToken').val(newToken);
                    $(this).prop('disabled', false).html('<i class="fas fa-plus-circle mr-1"></i> Generate Web Token');
                    alert('New web token generated successfully!');
                }, 1000);
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>