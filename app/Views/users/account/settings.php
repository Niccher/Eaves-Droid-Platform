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
                    <p class="text-muted mt-2 mb-0">Manage your API tokens, connected devices, and security preferences</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-sliders-h mr-2"></i>
                                Settings Dashboard
                            </h3>
                        </div>
                        <div class="card-body">
                            <!-- Tabs Navigation -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <ul class="nav nav-tabs nav-justified" id="settingsTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="api-tokens-tab" data-toggle="tab" href="#api-tokens" role="tab">
                                                <div class="text-center">
                                                    <i class="fas fa-key fa-2x mb-2 text-primary"></i>
                                                    <h5 class="mb-1">API Tokens</h5>
                                                    <p class="mb-0 text-muted small">Manage authentication tokens</p>
                                                    <span class="badge badge-primary mt-1"><?php echo $total_tokens ?? 0 ?></span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="devices-tab" data-toggle="tab" href="#devices" role="tab">
                                                <div class="text-center">
                                                    <i class="fas fa-laptop fa-2x mb-2 text-success"></i>
                                                    <h5 class="mb-1">Devices</h5>
                                                    <p class="mb-0 text-muted small">Connected devices</p>
                                                    <span class="badge badge-success mt-1"><?php echo count($user_devices) ?? 0 ?></span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="sessions-tab" data-toggle="tab" href="#sessions" role="tab">
                                                <div class="text-center">
                                                    <i class="fas fa-user-clock fa-2x mb-2 text-warning"></i>
                                                    <h5 class="mb-1">Sessions</h5>
                                                    <p class="mb-0 text-muted small">Active sessions</p>
                                                    <span class="badge badge-warning mt-1"><?php echo $activeSessions ?? 0 ?></span>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Tab Content -->
                            <div class="tab-content" id="settingsTabsContent">

                                <!-- API Tokens Tab - Redesigned -->
                                <div class="tab-pane fade show active" id="api-tokens" role="tabpanel">
                                    <div class="alert alert-info mb-4">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>API Token Management:</strong> Manage your authentication tokens for Android app and API access. Tokens expire after 30 days.
                                    </div>

                                    <div class="row">
                                        <!-- Current Token Display -->
                                        <div class="col-lg-6">
                                            <div class="card card-primary shadow-sm">
                                                <div class="card-header bg-primary text-white">
                                                    <h5 class="card-title mb-0">
                                                        <i class="fab fa-android mr-2"></i>
                                                        Current API Token
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label for="currentToken" class="font-weight-bold">
                                                            <i class="fas fa-key mr-2"></i>
                                                            Token Value
                                                        </label>
                                                        <div class="input-group input-group-lg mb-3">
                                                            <input type="text"
                                                                   class="form-control font-monospace"
                                                                   id="currentToken"
                                                                   value="<?php echo htmlspecialchars($currentTokenDisplay); ?>"
                                                                   readonly>
                                                            <div class="input-group-append">
                                                                <button class="btn btn-outline-primary" type="button" id="copyTokenBtn" data-toggle="tooltip" title="Copy to clipboard">
                                                                    <i class="fas fa-copy"></i>
                                                                </button>
                                                                <button class="btn btn-outline-secondary" type="button" id="toggleTokenBtn" data-toggle="tooltip" title="Show/Hide token">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Token Metadata -->
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="card mb-3">
                                                                    <div class="card-body">
                                                                        <small class="text-muted">Created</small>
                                                                        <div class="font-weight-bold">
                                                                            <i class="far fa-calendar-alt mr-1"></i>
                                                                            <?php echo !empty($user_token['created_at']) ? date('M d, Y', strtotime($user_token['created_at'])) : 'N/A'; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="card mb-3">
                                                                    <div class="card-body">
                                                                        <small class="text-muted">Expires</small>
                                                                        <div class="font-weight-bold">
                                                                            <i class="far fa-calendar-times mr-1"></i>
                                                                            <?php echo $tokenExpiry ?? 'Never'; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Token Status Badge -->
                                                        <div class="mt-3">
                                                            <label>Status</label>
                                                            <div>
                                                                <?php if (!empty($user_token['status']) && $user_token['status'] == '00'): ?>
                                                                    <span class="badge badge-success badge-lg p-2">
                                                                        <i class="fas fa-check-circle mr-1"></i> ACTIVE
                                                                    </span>
                                                                    <small class="text-success ml-2">
                                                                        <i class="fas fa-circle mr-1"></i> Ready for use
                                                                    </small>
                                                                <?php else: ?>
                                                                    <span class="badge badge-danger badge-lg p-2">
                                                                        <i class="fas fa-times-circle mr-1"></i> INACTIVE
                                                                    </span>
                                                                    <small class="text-danger ml-2">
                                                                        <i class="fas fa-circle mr-1"></i> Token revoked or expired
                                                                    </small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- QR Code Section -->
                                        <div class="col-lg-6">
                                            <div class="card card-info shadow-sm">
                                                <div class="card-header bg-info text-white">
                                                    <h5 class="card-title mb-0">
                                                        <i class="fas fa-qrcode mr-2"></i>
                                                        QR Code Scanner
                                                    </h5>
                                                </div>
                                                <div class="card-body text-center">
                                                    <p class="text-muted mb-3">
                                                        Scan this QR code with your Android app for quick setup:
                                                    </p>

                                                    <!-- QR Code Container -->
                                                    <div class="mb-4" id="qrcode-container">
                                                        <div id="qrcode" style="width:200px; height:200px; margin: 0 auto;"></div>
                                                    </div>

                                                    <div class="alert alert-light border">
                                                        <small class="text-muted">
                                                            <i class="fas fa-lightbulb mr-1"></i>
                                                            <strong>Tip:</strong> Open the scanner in your Android app and point it at this QR code to automatically configure your token.
                                                        </small>
                                                    </div>

                                                    <!-- QR Code Actions -->
                                                    <div class="btn-group mt-2" role="group">
                                                        <button type="button" class="btn btn-outline-info" id="downloadQRBtn">
                                                            <i class="fas fa-download mr-1"></i> Download QR
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info" id="printQRBtn">
                                                            <i class="fas fa-print mr-1"></i> Print
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info" id="refreshQRBtn">
                                                            <i class="fas fa-redo mr-1"></i> Refresh
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Regenerate Token Section -->
                                    <div class="row mt-4">
                                        <div class="col-lg-8 offset-lg-2">
                                            <div class="card card-warning shadow-sm">
                                                <div class="card-header bg-warning text-white">
                                                    <h5 class="card-title mb-0">
                                                        <i class="fas fa-sync-alt mr-2"></i>
                                                        Regenerate Token
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="alert alert-warning border-warning">
                                                        <h6><i class="fas fa-exclamation-triangle mr-2"></i> Important Notice</h6>
                                                        <p class="mb-0">Regenerating your token will:</p>
                                                        <ul class="mb-0 mt-2 pl-3">
                                                            <li>Invalidate all current Android connections</li>
                                                            <li>Require re-authentication on all devices</li>
                                                            <li>Disconnect all active sessions</li>
                                                            <li>Require updating the token in your Android app</li>
                                                        </ul>
                                                    </div>

                                                    <form action="<?php echo base_url('account/regenerateToken'); ?>" method="post" id="regenerateTokenForm">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                                                        <div class="text-center">
                                                            <button type="submit" class="btn btn-warning btn-lg px-5" id="regenerateTokenBtn">
                                                                <i class="fas fa-sync-alt mr-2"></i> Regenerate Token
                                                            </button>
                                                            <p class="text-muted mt-2 small">
                                                                This will generate a new 32-character token
                                                            </p>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Devices Tab (remains same) -->
                                <div class="tab-pane fade" id="devices" role="tabpanel">
                                    <!-- ... existing devices tab content ... -->
                                </div>

                                <!-- Sessions Tab (remains same) -->
                                <div class="tab-pane fade" id="sessions" role="tabpanel">
                                    <!-- ... existing sessions tab content ... -->
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Last updated: <?php echo date('M d, Y H:i:s'); ?>
                                            </small>
                                        </div>
                                        <div>
                                            <a href="<?php echo base_url('account/access_logs'); ?>" class="btn btn-outline-primary">
                                                <i class="fas fa-history mr-1"></i> View Access Logs
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Include QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>

<style>
    .nav-tabs.nav-justified .nav-link {
        border-radius: 8px 8px 0 0;
        padding: 20px 15px;
        transition: all 0.3s ease;
        border: 1px solid #dee2e6;
        margin: 0 2px;
    }

    .nav-tabs.nav-justified .nav-link:hover {
        background-color: rgba(0, 123, 255, 0.1);
        transform: translateY(-2px);
    }

    .nav-tabs.nav-justified .nav-link.active {
        background-color: #fff;
        border-bottom: 3px solid #007bff;
        box-shadow: 0 -2px 10px rgba(0, 123, 255, 0.1);
    }

    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .badge-lg {
        font-size: 0.9rem;
        padding: 8px 12px;
        border-radius: 20px;
    }

    .font-monospace {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 14px;
        letter-spacing: 0.5px;
    }

    .input-group-lg .form-control {
        border-radius: 8px;
    }

    #qrcode-container {
        background: white;
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #dee2e6;
        display: inline-block;
    }

    .tab-pane {
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .btn-group .btn {
        border-radius: 5px;
        margin: 0 2px;
    }
</style>

<script>
    $(document).ready(function() {
        // Initialize QR Code
        function generateQRCode(token) {
            $('#qrcode').empty();
            if (token && token !== 'No token found') {
                QRCode.toCanvas(document.getElementById('qrcode'), token, {
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                }, function(error) {
                    if (error) console.error(error);
                });
            } else {
                $('#qrcode').html('<div class="text-center text-muted p-5"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><p>No token available for QR code</p></div>');
            }
        }

        // Generate initial QR code
        generateQRCode('<?php echo $currentTokenDisplay; ?>');

        // Token display toggle
        let tokenVisible = true;
        $('#toggleTokenBtn').click(function() {
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

        // Copy token functionality
        $('#copyTokenBtn').click(function() {
            const token = $('#currentToken').val();
            navigator.clipboard.writeText(token).then(() => {
                const original = $(this).html();
                $(this).html('<i class="fas fa-check text-success"></i>');
                $(this).tooltip('dispose').tooltip({title: 'Copied!'});

                // Show toast notification
                showToast('Token copied to clipboard!', 'success');

                setTimeout(() => {
                    $(this).html(original);
                    $(this).tooltip('dispose').tooltip({title: 'Copy to clipboard'});
                }, 2000);
            });
        });

        // QR Code actions
        $('#downloadQRBtn').click(function() {
            const canvas = document.querySelector('#qrcode canvas');
            if (canvas) {
                const link = document.createElement('a');
                link.download = 'api-token-qrcode.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                showToast('QR code downloaded!', 'success');
            }
        });

        $('#printQRBtn').click(function() {
            window.print();
        });

        $('#refreshQRBtn').click(function() {
            generateQRCode('<?php echo $currentTokenDisplay; ?>');
            showToast('QR code refreshed!', 'info');
        });

        // Regenerate token form submission
        $('#regenerateTokenForm').on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Regenerate Token?',
                html: `
                    <div class="text-left">
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> This action will:
                            <ul class="text-left pl-3">
                                <li>Invalidate current Android connections</li>
                                <li>Require re-authentication on all devices</li>
                                <li>Disconnect all active sessions</li>
                                <li>Require updating your Android app</li>
                            </ul>
                        </div>
                        <p><strong>Are you sure you want to proceed?</strong></p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, regenerate',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams(new FormData(this))
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                throw new Error(data.message);
                            }
                            return data;
                        })
                        .catch(error => {
                            Swal.showValidationMessage(`Request failed: ${error}`);
                        });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const data = result.value;

                    // Update token display
                    $('#currentToken').val(data.token);
                    generateQRCode(data.token);

                    Swal.fire({
                        title: 'Success!',
                        html: `
                            <div class="text-left">
                                <p>Token regenerated successfully!</p>
                                <div class="alert alert-success">
                                    <strong>New Token:</strong><br>
                                    <code class="d-block mt-2 p-2 bg-light">${data.token}</code>
                                </div>
                                <p class="text-muted small">Expires: ${data.expiry}</p>
                                <p><strong>Update your Android app with this new token.</strong></p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: 'Got it!'
                    });
                }
            });
        });

        // Toast notification function
        function showToast(message, type = 'info') {
            const toast = $(`
                <div class="toast fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 250px;">
                    <div class="toast-header bg-${type} text-white">
                        <strong class="mr-auto">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} mr-2"></i>
                            ${type.charAt(0).toUpperCase() + type.slice(1)}
                        </strong>
                        <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `);

            $('body').append(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        // Regenerate token form submission
        $('#regenerateTokenForm').on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Regenerate Token?',
                html: `
            <div class="text-left">
                <div class="alert alert-warning">
                    <strong>Warning:</strong> This action will:
                    <ul class="text-left pl-3">
                        <li>Invalidate current Android connections</li>
                        <li>Require re-authentication on all devices</li>
                        <li>Disconnect all active sessions</li>
                        <li>Require updating your Android app</li>
                    </ul>
                </div>
                <p><strong>Are you sure you want to proceed?</strong></p>
            </div>
        `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, regenerate',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams(new FormData(this))
                    })
                        .then(response => {
                            // Check content type to handle both JSON and HTML redirects
                            const contentType = response.headers.get("content-type");
                            if (contentType && contentType.indexOf("application/json") !== -1) {
                                return response.json();
                            } else {
                                // If it's not JSON, it's likely a redirect
                                window.location.href = response.url;
                                return { success: true };
                            }
                        })
                        .catch(error => {
                            Swal.showValidationMessage(`Request failed: ${error}`);
                        });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value && result.value.success) {
                    const data = result.value;

                    // Update token display
                    $('#currentToken').val(data.token);
                    generateQRCode(data.token);

                    Swal.fire({
                        title: 'Success!',
                        html: `
                    <div class="text-left">
                        <p>Token regenerated successfully!</p>
                        <div class="alert alert-success">
                            <strong>New Token:</strong><br>
                            <code class="d-block mt-2 p-2 bg-light">${data.token}</code>
                        </div>
                        <p class="text-muted small">Expires: ${data.expiry}</p>
                        <p><strong>Update your Android app with this new token.</strong></p>
                    </div>
                `,
                        icon: 'success',
                        confirmButtonText: 'Got it!'
                    });
                }
            });
        });

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Tab switching animation
        $('.nav-tabs a').on('click', function(e) {
            e.preventDefault();
            $(this).tab('show');
        });
    });
</script>