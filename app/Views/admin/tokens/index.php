<?php $tab = $active_tab ?? 'all'; ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1><i class="fas fa-key mr-2"></i>Token Management</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/tokens') ?>">Tokens</a></li>
                        <li class="breadcrumb-item active"><?= $tab === 'all' ? 'All' : ($tab === 'expired' ? 'Expired' : 'Analytics') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <?php if (session()->getFlashdata('message')): ?>
            <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?= session()->getFlashdata('message') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <?php
            $callouts = [
                'all' => ['title' => 'All API Tokens', 'desc' => 'Manage API access tokens for external integrations. View active tokens, revoke access, regenerate credentials, or delete tokens that are no longer needed. Each token is tied to a user and device.'],
                'expired' => ['title' => 'Expired Tokens', 'desc' => 'Tokens that have passed their expiration date. These tokens can no longer authenticate API requests. Review and clean up expired tokens to maintain security hygiene.'],
                'analytics' => ['title' => 'Token Analytics', 'desc' => 'Usage analytics for API tokens — total counts by status (active, used, expired, deleted), per-user token distribution, and a 30-day creation trend chart.'],
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
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'all' ? 'active' : '' ?>" href="<?= base_url('admin/tokens') ?>"><i class="fas fa-list mr-1"></i>All <span class="badge badge-secondary ml-1"><?= number_format($count_all) ?></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'expired' ? 'active' : '' ?>" href="<?= base_url('admin/tokens/expired') ?>"><i class="fas fa-clock mr-1"></i>Expired <span class="badge badge-secondary ml-1"><?= number_format($expired_count) ?></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'analytics' ? 'active' : '' ?>" href="<?= base_url('admin/tokens/analytics') ?>"><i class="fas fa-chart-bar mr-1"></i>Analytics</a>
                        </li>
                    </ul>
                </div>

                <!-- ===== ALL TOKENS ===== -->
                <?php if ($tab === 'all'): ?>
                <div class="card-body p-0">
                    <table class="table table-striped" id="tokenTable">
                        <thead>
                            <tr><th>ID</th><th>Owner</th><th>Token</th><th>Device</th><th>Status</th><th>Created</th><th>Last Used</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tokens)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No tokens found.</td></tr>
                            <?php else: ?>
                            <?php foreach ($tokens as $t): ?>
                            <tr>
                                <td><?= $t['counter'] ?></td>
                                <td><i class="fas fa-user-circle text-muted mr-1"></i><?= htmlspecialchars($t['username'] ?? 'Unknown') ?></td>
                                <td><code title="<?= htmlspecialchars($t['token']) ?>"><?= htmlspecialchars(substr($t['token'], 0, 16)) ?>...</code></td>
                                <td><small><?= htmlspecialchars($t['device_name'] ?? '-') ?></small></td>
                                <td>
                                    <?php $statusMap = ['00' => ['Active', 'success'], '11' => ['Used', 'warning'], '99' => ['Deleted', 'danger']];
                                          $s = $statusMap[$t['status']] ?? ['Unknown', 'secondary']; ?>
                                    <span class="badge badge-<?= $s[1] ?>"><?= $s[0] ?></span>
                                </td>
                                <td><?= htmlspecialchars($t['created_at'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($t['last_used_at'] ?? 'Never') ?></td>
                                <td class="text-nowrap">
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-sm btn-primary mr-1 btn-qr-modal" 
                                            data-token="<?= htmlspecialchars($t['token']) ?>" 
                                            data-owner="<?= htmlspecialchars($t['username'] ?? 'Unknown') ?>" 
                                            data-device="<?= htmlspecialchars($t['device_name'] ?? 'None') ?>"
                                            title="View Android Pairing QR Code">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                        <?php if ($t['status'] === '00'): ?>
                                        <form method="post" action="<?= base_url('admin/tokens/revoke/' . $t['counter']) ?>" class="action-form mr-1" data-confirm-title="Revoke Token" data-confirm-text="Are you sure you want to revoke this token? It will no longer authenticate API requests.">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-warning" title="Revoke"><i class="fas fa-pause"></i></button>
                                        </form>
                                        <form method="post" action="<?= base_url('admin/tokens/regenerate/' . $t['counter']) ?>" class="action-form mr-1" data-confirm-title="Regenerate Token" data-confirm-text="Are you sure you want to regenerate this token? The current token value will be replaced.">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-info" title="Regenerate"><i class="fas fa-sync"></i></button>
                                        </form>
                                        <?php endif; ?>
                                        <form method="post" action="<?= base_url('admin/tokens/delete/' . $t['counter']) ?>" class="action-form" data-confirm-title="Delete Token" data-confirm-text="Are you sure you want to delete this token? This action cannot be undone.">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
                                <?= $pager_all->links('default', 'bootstrap5_full') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
                <?php if ($tab === 'expired'): ?>
                <div class="card-body p-0">
                    <table class="table table-striped" id="expiredTable">
                        <thead>
                            <tr><th>ID</th><th>Owner</th><th>Device</th><th>Created</th><th>Expired</th><th>Last Used</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($expired_tokens)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No expired tokens.</td></tr>
                            <?php else: ?>
                            <?php foreach ($expired_tokens as $t): ?>
                            <tr>
                                <td><?= $t['counter'] ?></td>
                                <td><i class="fas fa-user-circle text-muted mr-1"></i><?= htmlspecialchars($t['username'] ?? 'Unknown') ?></td>
                                <td><small><?= htmlspecialchars($t['device_name'] ?? '-') ?></small></td>
                                <td><?= htmlspecialchars($t['created_at'] ?? '-') ?></td>
                                <td><span class="badge badge-danger"><?= htmlspecialchars($t['expires_at'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($t['last_used_at'] ?? 'Never') ?></td>
                                <td class="text-nowrap">
                                    <form method="post" action="<?= base_url('admin/tokens/delete/' . $t['counter']) ?>" class="action-form" data-confirm-title="Delete Token" data-confirm-text="Are you sure you want to permanently delete this token? This action cannot be undone.">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_expired > $per_page): ?>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="entry-info">
                                Showing <?= (($current_page - 1) * $per_page) + 1 ?>
                                to <?= min($current_page * $per_page, $total_expired) ?>
                                of <?= number_format($total_expired) ?> entries
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="float-right">
                                <?= $pager_expired->links('default', 'bootstrap5_full') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <!-- ===== ANALYTICS ===== -->
                <?php if ($tab === 'analytics'): ?>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-2 col-4">
                            <div class="small-box bg-info animated pulse">
                                <div class="inner"><h3><i class="fas fa-database mr-1"></i> <?= $total ?></h3><p>Total Tokens</p></div>
                                <div class="icon"><i class="fas fa-key"></i></div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-4">
                            <div class="small-box bg-success">
                                <div class="inner"><h3><i class="fas fa-check-circle mr-1"></i> <?= $active ?></h3><p>Active</p></div>
                                <div class="icon"><i class="fas fa-shield-alt"></i></div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-4">
                            <div class="small-box bg-warning">
                                <div class="inner"><h3><i class="fas fa-history mr-1"></i> <?= $used ?></h3><p>Used</p></div>
                                <div class="icon"><i class="fas fa-check-double"></i></div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-4">
                            <div class="small-box bg-danger">
                                <div class="inner"><h3><i class="fas fa-clock mr-1"></i> <?= $expired_count ?></h3><p>Expired</p></div>
                                <div class="icon"><i class="fas fa-hourglass-end"></i></div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-4">
                            <div class="small-box bg-secondary">
                                <div class="inner"><h3><i class="fas fa-trash-alt mr-1"></i> <?= $deleted ?></h3><p>Deleted</p></div>
                                <div class="icon"><i class="fas fa-times-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-info shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-users mr-1 text-info"></i> Tokens per User</h3>
                                    <div class="card-tools"><span class="badge badge-info"><?= count($per_user) ?> users</span></div>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm">
                                        <thead><tr><th>User</th><th class="text-center">Tokens</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($per_user as $pu): ?>
                                            <tr>
                                                <td><i class="fas fa-user-circle text-muted mr-1"></i><?= htmlspecialchars($pu['username'] ?? 'Unknown') ?></td>
                                                <td class="text-center"><span class="badge badge-info badge-pill"><?= $pu['token_count'] ?></span></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-outline card-info shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-chart-line mr-1 text-info"></i> Token Creation (30 days)</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="tokenChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<!-- QR Code Pairing Modal -->
<div class="modal fade" id="qrPairingModal" tabindex="-1" role="dialog" aria-labelledby="qrPairingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-center shadow-lg border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="qrPairingModalLabel"><i class="fas fa-qrcode mr-2"></i>Android App Pairing QR</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">Scan this QR code from the <strong>Eaves Droid</strong> Android app to automatically configure the server URL and authenticate instantly.</p>
                <div class="d-flex justify-content-center mb-3">
                    <div class="p-2 border rounded bg-white shadow-sm" style="display:inline-block;">
                        <canvas id="adminQrCanvas" style="width:200px;height:200px;"></canvas>
                    </div>
                </div>
                
                <div class="mb-3 text-left">
                    <label class="font-weight-bold small text-muted text-uppercase mb-1">Server URL</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control text-center font-monospace" id="modalServerUrl" value="<?= rtrim(site_url(), '/') ?>" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="copyModalUrl" title="Copy Server URL"><i class="fas fa-copy"></i></button>
                        </div>
                    </div>
                </div>

                <div class="mb-3 text-left">
                    <label class="font-weight-bold small text-muted text-uppercase mb-1">Token</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control text-center font-monospace font-weight-bold" id="modalToken" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="copyModalToken" title="Copy Token"><i class="fas fa-copy"></i></button>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between text-muted small border-top pt-2 mt-3">
                    <span><strong>Owner:</strong> <span id="modalOwner">—</span></span>
                    <span><strong>Device:</strong> <span id="modalDevice">—</span></span>
                </div>
            </div>
            <div class="modal-footer justify-content-between bg-light">
                <small class="text-muted"><i class="fas fa-shield-alt mr-1 text-primary"></i>Merged Auth Payload (URL + Token)</small>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.1/qrcode.min.js"></script>
<script>
$(document).ready(function() {
    var serverUrl = '<?= rtrim(site_url(), '/') ?>';

    $('.btn-qr-modal').on('click', function() {
        var token = $(this).data('token');
        var owner = $(this).data('owner');
        var device = $(this).data('device');

        $('#modalToken').val(token);
        $('#modalOwner').text(owner);
        $('#modalDevice').text(device);

        var qrPayload = JSON.stringify({
            url: serverUrl,
            token: token,
            type: 'eaves_droid_auth'
        });

        QRCode.toCanvas(document.getElementById('adminQrCanvas'), qrPayload, { width: 200, margin: 2 }, function(err) {
            if (err) console.error('QR Render Error:', err);
        });
        $('#qrPairingModal').modal('show');
    });

    $('#copyModalUrl').on('click', function() {
        navigator.clipboard.writeText($('#modalServerUrl').val());
        if (typeof toastr !== 'undefined') { toastr.success('Server URL copied to clipboard'); } else { alert('Server URL copied'); }
    });

    $('#copyModalToken').on('click', function() {
        navigator.clipboard.writeText($('#modalToken').val());
        if (typeof toastr !== 'undefined') { toastr.success('Token copied to clipboard'); } else { alert('Token copied'); }
    });

    <?php if ($tab === 'all'): ?>
    $('#tokenTable').DataTable({ order: [[0, 'desc']], searching: false, paging: false, responsive: true });
    <?php elseif ($tab === 'expired'): ?>
    $('#expiredTable').DataTable({ order: [[4, 'desc']], searching: false, paging: false, responsive: true });
    <?php elseif ($tab === 'analytics'): ?>
    new Chart(document.getElementById('tokenChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($usage_by_day, 'date')) ?: '[]' ?>,
            datasets: [{
                label: 'Tokens Created',
                data: <?= json_encode(array_column($usage_by_day, 'count')) ?: '[]' ?>,
                backgroundColor: '#17a2b8'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });
    <?php endif; ?>
});
</script>
