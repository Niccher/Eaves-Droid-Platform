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
                            <!-- Explanation / Help Banner -->
                            <div class="alert alert-light border shadow-sm mb-4">
                                <h5 class="text-primary font-weight-bold mb-2">
                                    <i class="fas fa-info-circle mr-1"></i> Account Settings Overview
                                </h5>
                                <p class="text-secondary mb-0" style="font-size: 1.02rem;">
                                    Use this page to manage your device API connection credentials, view connected mobile terminals, and review the recent activity timeline. Keep your active token private.
                                </p>
                            </div>

                            <!-- Tabs Navigation -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="api-tokens-tab" data-toggle="tab" href="#api-tokens" role="tab">
                                                <i class="fas fa-key mr-1"></i> API Tokens
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="devices-tab" data-toggle="tab" href="#devices" role="tab">
                                                <i class="fas fa-mobile-alt mr-1"></i> Connected Devices (Last 10 Devices)
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="recent-uploads-tab" data-toggle="tab" href="#recent-uploads" role="tab">
                                                <i class="fas fa-cloud-upload-alt mr-1"></i> Recent Uploads (Last 10 Uploads)
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Tab Content -->
                            <div class="tab-content" id="settingsTabsContent">

                                <!-- API Tokens Tab -->
                                <div class="tab-pane fade show active" id="api-tokens" role="tabpanel">
                                    <div class="alert alert-info mb-4 shadow-sm">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>API Token Management:</strong> Use this token to authorize the extractor client on your Android terminal. Registered tokens automatically expire after 30 days.
                                    </div>

                                    <div class="row">
                                        <!-- Current Token Display & Action -->
                                        <div class="col-lg-6 mb-4">
                                            <div class="card card-primary shadow-sm h-100 mb-0">
                                                <div class="card-header bg-primary text-white">
                                                    <h5 class="card-title mb-0">
                                                        <i class="fab fa-android mr-2"></i>
                                                        Current API Token
                                                    </h5>
                                                </div>
                                                <div class="card-body d-flex flex-column justify-content-between">
                                                    <div>
                                                        <div class="form-group">
                                                            <label for="currentToken" class="font-weight-bold">
                                                                <i class="fas fa-key mr-2 text-primary"></i>
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
                                                                        <div class="card-body p-2 text-center">
                                                                            <small class="text-muted d-block">Created</small>
                                                                            <span class="font-weight-bold text-sm">
                                                                                <i class="far fa-calendar-alt mr-1 text-primary"></i>
                                                                                <?php echo !empty($user_token['created_at']) ? date('M d, Y, l H:i', strtotime($user_token['created_at'])) : 'N/A'; ?>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="card mb-3">
                                                                        <div class="card-body p-2 text-center">
                                                                            <small class="text-muted d-block">Expires</small>
                                                                            <span class="font-weight-bold text-sm">
                                                                                <i class="far fa-calendar-times mr-1 text-danger"></i>
                                                                                <?php echo $tokenExpiry ?? 'Never'; ?>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Token Status Badge -->
                                                            <div class="mt-2 mb-3">
                                                                <label class="text-muted text-sm d-block">Status</label>
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
                                                                            <i class="fas fa-circle mr-1"></i> Revoked/Expired
                                                                        </small>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Prominent Regenerate Button inside the card itself (Highly Visible!) -->
                                                    <div class="border-top pt-3 mt-2">
                                                        <div class="alert alert-warning py-2 px-3 mb-3 text-xs">
                                                            <i class="fas fa-exclamation-triangle mr-1"></i> Regenerating invalidates current Android configurations.
                                                        </div>
                                                        <form action="<?php echo base_url('account/regenerateToken'); ?>" method="post" id="regenerateTokenForm">
                                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                            <button type="submit" class="btn btn-warning btn-block btn-lg font-weight-bold shadow-sm" id="regenerateTokenBtn">
                                                                <i class="fas fa-sync-alt mr-2"></i> Regenerate Token
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- QR Code Section -->
                                        <div class="col-lg-6 mb-4">
                                            <div class="card card-info shadow-sm h-100 mb-0">
                                                <div class="card-header bg-info text-white">
                                                    <h5 class="card-title mb-0">
                                                        <i class="fas fa-qrcode mr-2"></i>
                                                        QR Code Scanner
                                                    </h5>
                                                </div>
                                                <div class="card-body text-center d-flex flex-column justify-content-between">
                                                    <div>
                                                        <p class="text-muted mb-3">
                                                            Scan this QR code with the extractor app scanner to quickly configure the API endpoint and active token:
                                                        </p>

                                                        <!-- QR Code Container -->
                                                        <div class="mb-4" id="qrcode-container">
                                                            <canvas id="qr-canvas" style="width:200px; height:200px; margin: 0 auto;"></canvas>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <!-- QR Code Actions -->
                                                        <div class="btn-group mt-2 mb-3" role="group">
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
                                                        <div class="alert alert-light border text-xs py-2 mb-0">
                                                            <i class="fas fa-lightbulb mr-1 text-warning"></i> Scan from within the app settings pane.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Devices Tab -->
                                <div class="tab-pane fade" id="devices" role="tabpanel">
                                    <div class="alert alert-light border shadow-sm mb-3">
                                        <i class="fas fa-info-circle mr-2 text-success"></i>
                                        <strong>Connected Devices:</strong> Below are the mobile devices that have authorized connections using your API tokens. A maximum of 10 active devices are displayed.
                                    </div>
                                    <?php if (!empty($user_devices)): ?>
                                        <div class="table-responsive shadow-sm border rounded bg-white">
                                            <table class="table table-hover table-striped align-middle mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Device Name</th>
                                                        <th>OS Version</th>
                                                        <th>Agent / Browser</th>
                                                        <th>IP Address</th>
                                                        <th>Last Active</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($user_devices as $device): ?>
                                                        <tr>
                                                            <td class="font-weight-bold align-middle">
                                                                <i class="fas fa-mobile-alt text-success mr-2"></i>
                                                                <?php echo htmlspecialchars($device['device_name']); ?>
                                                            </td>
                                                            <td class="align-middle">
                                                                <span class="badge badge-secondary"><?php echo htmlspecialchars($device['os']); ?></span>
                                                            </td>
                                                            <td class="align-middle"><?php echo htmlspecialchars($device['browser']); ?></td>
                                                            <td class="align-middle"><code><?php echo htmlspecialchars($device['ip_address']); ?></code></td>
                                                            <td class="align-middle text-muted"><?php echo $device['last_seen_formatted']; ?></td>
                                                            <td class="align-middle">
                                                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Authorized</span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="fas fa-laptop-medical fa-4x text-muted opacity-50"></i>
                                            </div>
                                            <h5 class="text-muted">No devices found</h5>
                                            <p class="text-muted small">Connected devices will appear here once registered.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Recent Uploads Tab -->
                                <div class="tab-pane fade" id="recent-uploads" role="tabpanel">
                                    <?php if (!empty($recent_files)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead class="bg-light">
                                                <tr>
                                                    <th>File Name</th>
                                                    <th>Size</th>
                                                    <th>Type</th>
                                                    <th>Date</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($recent_files as $file): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="mr-3">
                                                                    <i class="fas fa-file text-primary fa-lg"></i>
                                                                </div>
                                                                <div>
                                                                    <span class="font-weight-bold"><?php echo htmlspecialchars($file['name']); ?></span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><?php echo $file['formatted_size'] ?? number_format($file['size_bytes'] / 1024, 2) . ' KB'; ?></td>
                                                        <td>
                                                            <span class="badge badge-light border">
                                                                <?php echo strtoupper($file['extension'] ?? 'FILE'); ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-muted">
                                                            <?php echo !empty($file['created_at']) ? date('M d, Y, l H:i', strtotime($file['created_at'])) : 'N/A'; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="fas fa-cloud-upload-alt fa-4x text-muted opacity-50"></i>
                                            </div>
                                            <h5 class="text-muted">No recent uploads</h5>
                                            <p class="text-muted small">Files uploaded from your devices will appear here.</p>
                                        </div>
                                    <?php endif; ?>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.1/qrcode.min.js"></script>

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
    document.addEventListener("DOMContentLoaded", function() {
        // Ensure jQuery is loaded
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
            return;
        }
        var $ = jQuery;

        $(document).ready(function() {
        // Initialize QR Code
        function generateQRCode(token) {
            const canvas = document.getElementById('qr-canvas');
            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height); // Clear previous

            if (token && token !== 'No token found') {
                QRCode.toCanvas(canvas, token, {
                    width: 200,
                    margin: 2,
                    color: {
                        dark: "#000000",
                        light: "#ffffff"
                    },
                    errorCorrectionLevel: 'M'
                }, function(error) {
                    if (error) console.error(error);
                });
            } else {
                // Handle no token - maybe simple text on canvas or keep empty
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
            const canvas = document.getElementById('qr-canvas');
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
    });
</script>