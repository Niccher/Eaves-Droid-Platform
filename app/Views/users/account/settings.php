<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-cogs text-primary mr-2"></i>
                            Account Settings
                        </h1>
                    </div>
                    <p class="text-muted mt-2 mb-0">Manage API tokens, connected devices, and recent uploads</p>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header p-0 border-bottom-0">
                            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="api-tokens-tab" data-toggle="tab" href="#api-tokens" role="tab">
                                        <i class="fas fa-key mr-2"></i>API Tokens
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="devices-tab" data-toggle="tab" href="#devices" role="tab">
                                        <i class="fas fa-mobile-alt mr-2"></i>Connected Devices
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="recent-uploads-tab" data-toggle="tab" href="#recent-uploads" role="tab">
                                        <i class="fas fa-cloud-upload-alt mr-2"></i>Recent Uploads
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="settingsTabsContent">

                                <!-- ==================== API TOKENS ==================== -->
                                <div class="tab-pane fade show active" id="api-tokens" role="tabpanel">
                                    <?php 
                                    $hasActiveToken = !empty($user_token['token']) && ($user_token['status'] ?? '') === '00';
                                    $activeToken = $user_token['token'] ?? '';
                                    $serverUrl = rtrim(site_url(), '/');
                                    ?>
                                    <div class="row">
                                        <!-- Active / Create Token Panel -->
                                        <div class="col-lg-5 mb-4">
                                            <div class="card card-secondary h-100 shadow-sm">
                                                <div class="card-header bg-gradient-secondary text-white d-flex justify-content-between align-items-center">
                                                    <h5 class="mb-0"><i class="fas fa-key mr-2"></i>API Access Token</h5>
                                                    <?php if ($hasActiveToken): ?>
                                                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Active</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="card-body text-center">
                                                    <?php if ($hasActiveToken): ?>
                                                    <!-- Active Token Display & QR -->
                                                    <div id="activeTokenSection">
                                                        <div class="d-flex justify-content-center mb-3">
                                                            <div class="p-2 border rounded bg-white shadow-sm" style="display:inline-block;">
                                                                <canvas id="active-qr" style="width:140px;height:140px;"></canvas>
                                                            </div>
                                                        </div>
                                                        <p class="text-muted small mb-2"><i class="fas fa-camera mr-1"></i>Scan with the Android app to auto-configure server and sign in.</p>
                                                        
                                                        <div class="input-group input-group-sm mb-2">
                                                            <input type="text" class="form-control text-center font-monospace font-weight-bold" id="activeTokenVal" value="<?= htmlspecialchars($activeToken) ?>" readonly>
                                                            <div class="input-group-append">
                                                                <button class="btn btn-outline-secondary" type="button" id="copyActiveToken" title="Copy Token">
                                                                    <i class="fas fa-copy"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3 text-center">
                                                            <small class="text-muted"><i class="fas fa-link mr-1"></i>Server URL:</small><br>
                                                            <a href="<?= $serverUrl ?>" target="_blank" class="badge badge-light border text-primary font-weight-bold p-2 text-wrap" style="word-break: break-all;">
                                                                <i class="fas fa-globe mr-1"></i><span><?= $serverUrl ?></span>
                                                            </a>
                                                        </div>

                                                        <hr>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnToggleNewToken">
                                                            <i class="fas fa-sync-alt mr-1"></i> Generate New / Replace Token
                                                        </button>
                                                    </div>
                                                    <?php endif; ?>

                                                    <!-- Generate Token Form -->
                                                    <div id="tokenFormContainer" style="<?= $hasActiveToken ? 'display:none;' : '' ?>">
                                                        <form id="createTokenForm" method="post">
                                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                                            <div class="form-group text-left">
                                                                <label for="tokenName" class="font-weight-bold small text-muted text-uppercase">Device Label</label>
                                                                <input type="text" class="form-control text-center" id="tokenName" name="token_name" placeholder="e.g. My Pixel 7">
                                                                <small class="text-muted">Optional label to identify this device session</small>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary btn-block shadow-sm font-weight-bold" id="createTokenBtn">
                                                                <i class="fas fa-plus mr-2"></i> Generate Token
                                                            </button>
                                                            <?php if ($hasActiveToken): ?>
                                                            <button type="button" class="btn btn-sm btn-link text-muted mt-2" id="btnCancelNewToken">
                                                                Cancel
                                                            </button>
                                                            <?php endif; ?>
                                                        </form>
                                                    </div>

                                                    <!-- Newly Generated Token Result -->
                                                    <div id="tokenResult" style="display:none;" class="mt-3">
                                                        <div class="alert alert-success py-2 text-left small">
                                                            <i class="fas fa-check-circle mr-1"></i> <strong>New Token Generated!</strong>
                                                        </div>
                                                        <div class="d-flex justify-content-center mb-3">
                                                            <div class="p-2 border rounded bg-white shadow-sm" style="display:inline-block;">
                                                                <canvas id="result-qr" style="width:140px;height:140px;"></canvas>
                                                            </div>
                                                        </div>
                                                        <div class="input-group input-group-sm mb-2">
                                                            <input type="text" class="form-control text-center font-monospace font-weight-bold" id="generatedToken" readonly>
                                                            <div class="input-group-append">
                                                                <button class="btn btn-primary" type="button" id="copyGeneratedToken" title="Copy to clipboard">
                                                                    <i class="fas fa-copy"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <p class="text-muted small mt-1 mb-2">Scan this QR from the Android app to login instantly.</p>
                                                        <div class="text-center">
                                                            <small class="text-muted"><i class="fas fa-link mr-1"></i>Server URL:</small><br>
                                                            <a href="<?= $serverUrl ?>" id="serverUrlLink" target="_blank" class="badge badge-light border text-primary font-weight-bold p-2 text-wrap" style="word-break: break-all;">
                                                                <i class="fas fa-globe mr-1"></i><span id="serverUrlText"><?= $serverUrl ?></span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Token History Panel -->
                                        <div class="col-lg-7 mb-4">
                                            <div class="card h-100 border shadow-sm">
                                                <div class="card-header bg-secondary text-white d-flex align-items-center">
                                                    <h5 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Token History</h5>
                                                    <div class="card-tools ml-auto">
                                                        <span class="badge badge-light"><?= count($used_tokens) ?> total</span>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <?php if (!empty($used_tokens)): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-hover align-middle mb-0">
                                                            <thead class="bg-light">
                                                                <tr>
                                                                    <th>Device</th>
                                                                    <th>Token</th>
                                                                    <th>Status</th>
                                                                    <th>Used</th>
                                                                    <th>Created</th>
                                                                    <th class="text-right">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($used_tokens as $t): ?>
                                                                <tr>
                                                                    <td><?= htmlspecialchars($t['device_name'] ?? '—') ?></td>
                                                                    <td><code class="small"><?= htmlspecialchars($t['token'] ?? '—') ?></code></td>
                                                                    <td><span class="badge badge-secondary"><?= ($t['status'] === '00') ? 'Active' : 'Used' ?></span></td>
                                                                    <td class="small text-muted"><?= !empty($t['last_used_at']) ? date('M d, Y H:i', strtotime($t['last_used_at'])) : '—' ?></td>
                                                                    <td class="small text-muted"><?= !empty($t['created_at']) ? date('M d, Y H:i', strtotime($t['created_at'])) : '—' ?></td>
                                                                    <td class="text-right">
                                                                        <button type="button" class="btn btn-xs btn-outline-primary btn-history-qr" 
                                                                            data-token="<?= htmlspecialchars($t['token'] ?? '') ?>" 
                                                                            data-device="<?= htmlspecialchars($t['device_name'] ?? 'Device') ?>"
                                                                            title="View Pairing QR">
                                                                            <i class="fas fa-qrcode"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <?php else: ?>
                                                    <div class="text-center py-5">
                                                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">No tokens have been used yet. Generate a token above and scan it from your Android device.</p>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ==================== DEVICES ==================== -->
                                <div class="tab-pane fade" id="devices" role="tabpanel">
                                    <div class="alert alert-light border mb-3">
                                        <i class="fas fa-info-circle mr-2 text-success"></i>
                                        <strong>Connected Devices:</strong> Mobile devices that have authenticated using your tokens.
                                        <a href="<?= base_url('account/devices') ?>" class="float-right">View Full Details <i class="fas fa-arrow-right ml-1"></i></a>
                                    </div>
                                    <?php if (!empty($user_devices)): ?>
                                    <div class="table-responsive shadow-sm border rounded bg-white">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Device</th>
                                                    <th>OS</th>
                                                    <th>Browser</th>
                                                    <th>IP</th>
                                                    <th>Last Active (File Upload)</th>
                                                    <th>First Contact</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($user_devices as $device): ?>
                                                <tr>
                                                    <td class="font-weight-bold"><i class="fas fa-mobile-alt text-success mr-2"></i><?= htmlspecialchars($device['device_name']) ?></td>
                                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($device['os']) ?></span></td>
                                                    <td><?= htmlspecialchars($device['browser']) ?></td>
                                                    <td><code><?= htmlspecialchars($device['ip_address']) ?></code></td>
                                                    <td class="text-muted small"><?= $device['last_seen_formatted'] ?></td>
                                                    <td class="text-muted small"><?= $device['first_contact_formatted'] ?></td>
                                                    <td><span class="badge badge-success">Authorized</span></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-mobile-alt fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No devices connected yet.</p>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- ==================== RECENT UPLOADS ==================== -->
                                <div class="tab-pane fade" id="recent-uploads" role="tabpanel">
                                    <div class="alert alert-light border mb-3">
                                        <i class="fas fa-info-circle mr-2 text-info"></i>
                                        <strong>Recent Uploads:</strong> Last 10 files uploaded from your devices.
                                    </div>
                                    <?php if (!empty($recent_files)): ?>
                                    <div class="table-responsive shadow-sm border rounded bg-white">
                                        <table class="table table-hover align-middle mb-0">
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
                                                    <td><i class="fas fa-file text-primary mr-2"></i><?= htmlspecialchars($file['name']) ?></td>
                                                    <td><?= $file['formatted_size'] ?? number_format($file['size_bytes'] / 1024, 2) . ' KB' ?></td>
                                                    <td><span class="badge badge-light border"><?= strtoupper($file['extension'] ?? 'FILE') ?></span></td>
                                                    <td class="text-muted small"><?= !empty($file['created_at']) ? date('M d, Y, H:i', strtotime($file['created_at'])) : '—' ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No recent uploads.</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- History QR Modal -->
<div class="modal fade" id="historyQrModal" tabindex="-1" role="dialog" aria-labelledby="historyQrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-center shadow-lg border-0">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title font-weight-bold" id="historyQrModalLabel"><i class="fas fa-qrcode mr-2"></i>Token Pairing QR</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">Scan this QR code from the <strong>Eaves Droid</strong> app to authenticate.</p>
                <div class="d-flex justify-content-center mb-3">
                    <div class="p-2 border rounded bg-white shadow-sm" style="display:inline-block;">
                        <canvas id="historyQrCanvas" style="width:180px;height:180px;"></canvas>
                    </div>
                </div>

                <div class="mb-3 text-left">
                    <label class="font-weight-bold small text-muted text-uppercase mb-1">Server URL</label>
                    <input type="text" class="form-control form-control-sm text-center font-monospace" id="historyModalUrl" value="<?= rtrim(site_url(), '/') ?>" readonly>
                </div>

                <div class="mb-3 text-left">
                    <label class="font-weight-bold small text-muted text-uppercase mb-1">Token</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control text-center font-monospace font-weight-bold" id="historyModalToken" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="copyHistoryToken" title="Copy Token"><i class="fas fa-copy"></i></button>
                        </div>
                    </div>
                </div>

                <div class="text-muted small border-top pt-2 mt-3 text-left">
                    <strong>Device:</strong> <span id="historyModalDevice">—</span>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.1/qrcode.min.js"></script>
<script>
$(document).ready(function() {
    var serverUrl = '<?= rtrim(site_url(), '/') ?>';

    function showToast(msg, type) {
        type = type || 'info';
        var t = $('<div class="toast fade show" role="alert" style="position:fixed;top:20px;right:20px;z-index:9999;min-width:250px;">'
            + '<div class="toast-header bg-' + type + ' text-white">'
            + '<strong class="mr-auto"><i class="fas fa-' + (type==='success'?'check-circle':'info-circle') + ' mr-2"></i>'
            + type.charAt(0).toUpperCase()+type.slice(1) + '</strong>'
            + '<button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">&times;</button>'
            + '</div><div class="toast-body">' + msg + '</div></div>');
        $('body').append(t);
        setTimeout(function() { t.remove(); }, 3000);
    }

    // 1. Render active token QR on load if active token exists
    <?php if ($hasActiveToken): ?>
    var activeQrPayload = JSON.stringify({
        url: serverUrl,
        token: '<?= addslashes($activeToken) ?>',
        type: 'eaves_droid_auth'
    });
    var activeCanvas = document.getElementById('active-qr');
    if (activeCanvas) {
        QRCode.toCanvas(activeCanvas, activeQrPayload, { width: 140, margin: 1 }, function(err) {
            if (err) console.error('Active QR Render Error:', err);
        });
    }
    <?php endif; ?>

    // 2. Toggle new token form
    $('#btnToggleNewToken').on('click', function() {
        $('#activeTokenSection').slideUp(200);
        $('#tokenResult').hide();
        $('#tokenFormContainer').slideDown(200);
    });

    $('#btnCancelNewToken').on('click', function() {
        $('#tokenFormContainer').slideUp(200);
        $('#activeTokenSection').slideDown(200);
    });

    // 3. Create token AJAX
    $('#createTokenForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#createTokenBtn');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Creating...');
        $.ajax({
            url: '<?= base_url('account/createToken') ?>',
            method: 'POST',
            data: new URLSearchParams(new FormData(this)).toString(),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            success: function(res) {
                if (res.success) {
                    $('#tokenFormContainer').hide();
                    $('#generatedToken').val(res.token);
                    
                    var qrUrl = res.server_url || serverUrl;
                    var qrPayload = JSON.stringify({
                        url: qrUrl,
                        token: res.token,
                        type: 'eaves_droid_auth'
                    });
                    
                    QRCode.toCanvas(document.getElementById('result-qr'), qrPayload, { width: 140, margin: 1 });
                    $('#serverUrlLink').attr('href', qrUrl);
                    $('#serverUrlText').text(qrUrl);
                    $('#tokenResult').show();
                    showToast('Token created! Scan QR code from the Android app.', 'success');
                } else {
                    showToast(res.message || 'Failed', 'danger');
                    btn.prop('disabled', false).html('<i class="fas fa-plus mr-2"></i> Generate Token');
                }
            },
            error: function() {
                showToast('Request failed', 'danger');
                btn.prop('disabled', false).html('<i class="fas fa-plus mr-2"></i> Generate Token');
            }
        });
    });

    // 4. Token History QR Modal
    $('.btn-history-qr').on('click', function() {
        var token = $(this).data('token');
        var device = $(this).data('device');

        $('#historyModalToken').val(token);
        $('#historyModalDevice').text(device);

        var qrPayload = JSON.stringify({
            url: serverUrl,
            token: token,
            type: 'eaves_droid_auth'
        });

        QRCode.toCanvas(document.getElementById('historyQrCanvas'), qrPayload, { width: 180, margin: 1 });
        $('#historyQrModal').modal('show');
    });

    // 5. Copy buttons
    $('#copyActiveToken').click(function() {
        navigator.clipboard.writeText($('#activeTokenVal').val());
        showToast('Active token copied!', 'success');
    });

    $('#copyGeneratedToken').click(function() {
        navigator.clipboard.writeText($('#generatedToken').val());
        showToast('New token copied!', 'success');
    });

    $('#copyHistoryToken').click(function() {
        navigator.clipboard.writeText($('#historyModalToken').val());
        showToast('Token copied!', 'success');
    });
});
</script>
