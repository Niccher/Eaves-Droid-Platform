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
                                    <div class="row">
                                        <!-- Create Token Panel -->
                                        <div class="col-lg-5 mb-4">
                                            <div class="card card-success h-100 border-success shadow-sm">
                                                <div class="card-header bg-success text-white">
                                                    <h5 class="mb-0"><i class="fas fa-plus-circle mr-2"></i>New Token</h5>
                                                </div>
                                                <div class="card-body text-center">
                                                    <form id="createTokenForm" method="post">
                                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                                        <div class="form-group">
                                                            <label for="tokenName" class="font-weight-bold">Device Label</label>
                                                            <input type="text" class="form-control text-center" id="tokenName" name="token_name" placeholder="e.g. My Pixel 7">
                                                            <small class="text-muted">Optional name to identify this token</small>
                                                        </div>
                                                        <button type="submit" class="btn btn-success btn-lg btn-block shadow-sm font-weight-bold" id="createTokenBtn">
                                                            <i class="fas fa-plus mr-2"></i> Generate Token
                                                        </button>
                                                    </form>

                                                    <div id="tokenResult" style="display:none;" class="mt-4">
                                                        <hr>
                                                        <div class="alert alert-success py-2">
                                                            <i class="fas fa-check-circle mr-1"></i> <strong>Token created!</strong> Copy it now — it won't be shown again.
                                                        </div>
                                                        <div class="input-group input-group-lg mb-3">
                                                            <input type="text" class="form-control text-center font-weight-bold" id="generatedToken" readonly>
                                                            <div class="input-group-append">
                                                                <button class="btn btn-primary" type="button" id="copyGeneratedToken" title="Copy to clipboard">
                                                                    <i class="fas fa-copy"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <canvas id="result-qr" style="width:130px;height:130px;margin:0 auto;"></canvas>
                                                        <p class="text-muted small mt-2">Scan this QR from the Android app's token scanner</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Token History Panel -->
                                        <div class="col-lg-7 mb-4">
                                            <div class="card h-100 border shadow-sm">
                                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                                    <h5 class="mb-0"><i class="fas fa-history mr-2"></i>Token History</h5>
                                                    <span class="badge badge-light"><?= count($used_tokens) ?> total</span>
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
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($used_tokens as $t): ?>
                                                                <tr>
                                                                    <td><?= htmlspecialchars($t['device_name'] ?? '—') ?></td>
                                                                    <td><code class="small"><?= htmlspecialchars($t['token'] ?? '—') ?></code></td>
                                                                    <td><span class="badge badge-secondary">Used</span></td>
                                                                    <td class="small text-muted"><?= !empty($t['last_used_at']) ? date('M d, Y H:i', strtotime($t['last_used_at'])) : '—' ?></td>
                                                                    <td class="small text-muted"><?= !empty($t['created_at']) ? date('M d, Y H:i', strtotime($t['created_at'])) : '—' ?></td>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.1/qrcode.min.js"></script>
<script>
$(document).ready(function() {
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
                    $('#createTokenForm').hide();
                    $('#generatedToken').val(res.token);
                    QRCode.toCanvas(document.getElementById('result-qr'), res.token, { width: 130, margin: 1 });
                    $('#tokenResult').show();
                    showToast('Token created! Scan from the Android app.', 'success');
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

    $('#copyGeneratedToken').click(function() {
        navigator.clipboard.writeText($('#generatedToken').val());
        showToast('Token copied!', 'success');
    });
});
</script>
