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
                                        <strong>Single-Use Tokens:</strong> Each token can be used exactly once by one device for authentication. Create a new token, use it on your Android terminal via the app's token scanner, and it will be marked as used after verification.
                                    </div>

                                    <!-- Create New Token -->
                                    <div class="card card-success shadow-sm mb-4">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-plus-circle mr-2"></i>
                                                Create New Token
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <form id="createTokenForm" method="post">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                <div class="form-row align-items-end">
                                                    <div class="col-md-6 mb-3 mb-md-0">
                                                        <label for="tokenName" class="font-weight-bold">
                                                            <i class="fas fa-tag mr-1 text-success"></i>
                                                            Token Name (optional)
                                                        </label>
                                                        <input type="text"
                                                               class="form-control form-control-lg"
                                                               id="tokenName"
                                                               name="token_name"
                                                               placeholder="e.g. My Pixel 7">
                                                    </div>
                                                    <div class="col-md-4 mb-3 mb-md-0">
                                                        <label class="font-weight-bold d-block">
                                                            <i class="fas fa-qrcode mr-1 text-success"></i>
                                                            QR Code
                                                        </label>
                                                        <canvas id="new-token-qr" style="width:100px;height:100px;display:none;"></canvas>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="submit" class="btn btn-success btn-block btn-lg font-weight-bold shadow-sm" id="createTokenBtn">
                                                            <i class="fas fa-plus mr-2"></i> Create
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>

                                            <!-- Token result (shown after creation, disappears on refresh) -->
                                            <div id="tokenResult" style="display:none;" class="mt-3 border rounded p-4 bg-light">
                                                <div class="alert alert-success mb-3">
                                                    <i class="fas fa-check-circle mr-2"></i>
                                                    <strong>Token created!</strong> Copy it now &mdash; it will disappear when you leave this page.
                                                </div>
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <label class="font-weight-bold"><i class="fas fa-key mr-1"></i>Token Value</label>
                                                        <div class="input-group input-group-lg">
                                                            <input type="text" class="form-control font-monospace" id="generatedToken" readonly>
                                                            <div class="input-group-append">
                                                                <button class="btn btn-outline-primary" type="button" id="copyGeneratedToken">
                                                                    <i class="fas fa-copy"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 text-center">
                                                        <canvas id="result-qr" style="width:120px;height:120px;"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Used / Expired Tokens -->
                                    <div class="card card-secondary shadow-sm">
                                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-history mr-2"></i>
                                                Used &amp; Expired Tokens
                                            </h5>
                                            <span class="badge badge-light"><?php echo count($used_tokens); ?> total</span>
                                        </div>
                                        <div class="card-body p-0">
                                            <?php if (!empty($used_tokens)): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover align-middle mb-0">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th>Token</th>
                                                                <th>Name</th>
                                                                <th>Status</th>
                                                                <th>Used At</th>
                                                                <th>Created</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($used_tokens as $t): ?>
                                                                <tr>
                                                                    <td>
                                                                        <code class="font-monospace" style="font-size:0.85rem;">
                                                                            <?php echo htmlspecialchars($t['token'] ?? 'N/A'); ?>
                                                                        </code>
                                                                    </td>
                                                                    <td>
                                                                        <?php echo htmlspecialchars($t['device_name'] ?? '—'); ?>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge badge-secondary">
                                                                            <i class="fas fa-check-circle mr-1"></i> Used
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-muted small">
                                                                        <?php echo !empty($t['last_used_at']) ? date('M d, Y H:i', strtotime($t['last_used_at'])) : '—'; ?>
                                                                    </td>
                                                                    <td class="text-muted small">
                                                                        <?php echo !empty($t['created_at']) ? date('M d, Y H:i', strtotime($t['created_at'])) : '—'; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center py-5">
                                                    <div class="mb-3">
                                                        <i class="fas fa-ticket-alt fa-4x text-muted opacity-50"></i>
                                                    </div>
                                                    <h5 class="text-muted">No used tokens yet</h5>
                                                    <p class="text-muted small">Used tokens will appear here after devices authenticate.</p>
                                                </div>
                                            <?php endif; ?>
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

    .tab-pane {
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.1/qrcode.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    if (typeof jQuery === 'undefined') { console.error('jQuery not loaded'); return; }
    var $ = jQuery;
    $(document).ready(function() {

    function showToast(message, type) {
        type = type || 'info';
        var toast = $('<div class="toast fade show" role="alert" style="position:fixed;top:20px;right:20px;z-index:9999;min-width:250px;">'
            + '<div class="toast-header bg-' + type + ' text-white">'
            + '<strong class="mr-auto"><i class="fas fa-' + (type === 'success' ? 'check-circle' : 'info-circle') + ' mr-2"></i>'
            + type.charAt(0).toUpperCase() + type.slice(1) + '</strong>'
            + '<button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast"><span>&times;</span></button>'
            + '</div><div class="toast-body">' + message + '</div></div>');
        $('body').append(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    }

    // Create new token
    $('#createTokenForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#createTokenBtn');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Creating...');

        $.ajax({
            url: '<?php echo base_url('account/createToken'); ?>',
            method: 'POST',
            data: new URLSearchParams(new FormData(this)).toString(),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            success: function(res) {
                if (res.success) {
                    $('#createTokenForm').hide();
                    $('#generatedToken').val(res.token);
                    QRCode.toCanvas(document.getElementById('result-qr'), res.token, { width: 120, margin: 1 });
                    $('#tokenResult').show();
                    showToast('Token created! Scan the QR code from the app.', 'success');
                } else {
                    showToast(res.message || 'Failed to create token', 'danger');
                }
            },
            error: function() {
                showToast('Request failed', 'danger');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-plus mr-2"></i> Create');
            }
        });
    });

    // Copy generated token
    $('#copyGeneratedToken').click(function() {
        navigator.clipboard.writeText($('#generatedToken').val());
        showToast('Token copied!', 'success');
    });

    // Tab switching
    $('.nav-tabs a').on('click', function(e) {
        e.preventDefault();
        $(this).tab('show');
    });

    });
});
</script>