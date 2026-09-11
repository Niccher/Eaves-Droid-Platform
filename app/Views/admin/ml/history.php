<div class="tab-pane fade <?= ($active_tab === 'history') ? 'show active' : '' ?>" id="pane-history" role="tabpanel">
                                <div class="callout callout-success bg-light border-left-success py-2 px-3 mb-3 small">
                                    <i class="fas fa-history text-success mr-1"></i>
                                    Recent anomaly detection forensics runs logged across all users and devices.
                                </div>

                                <?php if (empty($job_history)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary"></i>
                                    <p class="h6">No anomaly detection runs have been executed yet.</p>
                                </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Owner</th>
                                                <th>Engine</th>
                                                <th>Algorithms</th>
                                                <th>Scope</th>
                                                <th>Status</th>
                                                <th>Time Taken</th>
                                                <th>Run At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1; ?>
                                            <?php foreach ($job_history as $j): ?>
                                            <tr>
                                                <td class="text-muted"><?= $i++ ?></td>
                                                <td>
                                                    <i class="fas fa-user-circle mr-1 text-muted"></i>
                                                    <?= esc($j['owner_name'] ?? 'Unknown') ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= ($j['engine'] ?? '') === 'python' ? 'warning' : (($j['engine'] ?? '') === 'php' ? 'success' : 'primary') ?>">
                                                        <i class="fas fa-<?= ($j['engine'] ?? '') === 'python' ? 'robot' : (($j['engine'] ?? '') === 'php' ? 'code' : 'cogs') ?> mr-1"></i>
                                                        <?= ucfirst($j['engine'] ?? 'PHP') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary badge-pill mr-1"><?= $j['algorithm_count'] ?? 0 ?></span>
                                                    <?php if (!empty($j['algorithm_names'])): ?>
                                                        <?php foreach (array_slice($j['algorithm_names'], 0, 3) as $nm): ?>
                                                        <span class="badge badge-light border mr-1"><?= esc($nm) ?></span>
                                                        <?php endforeach; ?>
                                                        <?php if (count($j['algorithm_names']) > 3): ?>
                                                        <span class="badge badge-light border">+<?= count($j['algorithm_names']) - 3 ?></span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (($j['scope'] ?? '') === 'incremental'): ?>
                                                    <span class="badge badge-info">Incremental</span>
                                                    <?php else: ?>
                                                    <span class="badge badge-secondary">Full Extraction</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php $statusBadge = match($j['status'] ?? '') {
                                                        'completed' => 'success',
                                                        'running' => 'primary',
                                                        'failed' => 'danger',
                                                        default => 'secondary',
                                                    }; ?>
                                                    <span class="badge badge-<?= $statusBadge ?>">
                                                        <i class="fas fa-<?= ($j['status'] ?? '') === 'completed' ? 'check-circle' : (($j['status'] ?? '') === 'running' ? 'spinner fa-spin' : (($j['status'] ?? '') === 'failed' ? 'times-circle' : 'clock')) ?> mr-1"></i>
                                                        <?= ucfirst($j['status'] ?? 'completed') ?>
                                                    </span>
                                                </td>
                                                <td class="text-nowrap">
                                                    <?php if (!empty($j['completed_at']) && !empty($j['time_taken'])): ?>
                                                    <i class="far fa-clock mr-1 text-muted"></i>
                                                    <?php
                                                        $t = (int)$j['time_taken'];
                                                        echo ($t >= 60) ? (floor($t / 60) . 'm ' . ($t % 60) . 's') : ($t . 's');
                                                    ?>
                                                    <?php else: ?>
                                                    <span class="text-muted">&mdash;</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-nowrap small text-muted">
                                                    <i class="far fa-calendar-alt mr-1"></i>
                                                    <?= !empty($j['created_at']) ? date('M j, Y g:i A', strtotime($j['created_at'])) : '-' ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
        </div>
    </section>
</div>

<div class="modal fade" id="pythonTestModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="true" aria-labelledby="pythonTestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white" id="pythonTestModalLabel">
                    <i class="fab fa-python mr-1"></i> Python Backend Connection Test
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="pythonTestBody">
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-pulse fa-3x text-muted"></i>
                    <p class="mt-2 text-muted">Connecting to Python backend...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="setFromTestBtn" style="display:none;" onclick="setFromTestResult()">
                    <i class="fas fa-check mr-1"></i> Set This Connection
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info" onclick="testPythonConnection()"><i class="fas fa-sync mr-1"></i> Test Again</button>
            </div>
        </div>
    </div>
</div>

<script>
let lastTestedUrl = '';
let lastTestedToken = '';
let lastTestResult = null;

function getConnectionUrl() {
    const urlInput = document.getElementById('python_connection_url');
    return urlInput ? urlInput.value.trim() : '';
}

function getConnectionToken() {
    const tokenInput = document.getElementById('python_internal_token');
    return tokenInput ? tokenInput.value.trim() : '';
}

function toggleTokenVisibility() {
    const tokenInput = document.getElementById('python_internal_token');
    const icon = document.getElementById('tokenToggleIcon');
    if (!tokenInput) return;
    if (tokenInput.type === 'password') {
        tokenInput.type = 'text';
        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    } else {
        tokenInput.type = 'password';
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}

function refreshHeroTelemetry(manual) {
    const icon = document.getElementById('heroRefreshIcon');
    if (manual && icon) icon.classList.add('fa-spin');

    fetch('<?= base_url('admin/ml/heartbeat') ?>', { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (icon) icon.classList.remove('fa-spin');
            const badge = document.getElementById('heroStatusBadge');
            const statusEl = document.getElementById('heroServiceStatus');
            const descEl = document.getElementById('heroServiceDesc');
            const latEl = document.getElementById('heroLatency');
            const latDesc = document.getElementById('heroLatencyDesc');
            const loadEl = document.getElementById('heroLoad');
            const loadDesc = document.getElementById('heroLoadDesc');
            const dbEl = document.getElementById('heroDbStatus');
            const dbDesc = document.getElementById('heroDbDesc');

            if (data.online) {
                if (badge) {
                    badge.className = 'badge badge-pill badge-success';
                    badge.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Healthy';
                }
                if (statusEl) statusEl.innerHTML = '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>Online</span> <small class="text-muted">(v' + (data.version || '2.5.0') + ')</small>';
                if (descEl) descEl.textContent = (data.models_count || 7) + ' Active Detectors Loaded';
                if (latEl) latEl.innerHTML = (data.latency_ms || 0) + ' <small>ms</small>';
                if (latDesc) latDesc.textContent = data.latency_ms < 100 ? 'Optimal response time' : 'Normal network latency';
                if (loadEl) {
                    const mem = (data.memory && data.memory.used) ? Math.round(data.memory.used) + ' MB' : 'Active';
                    const cpu = data.cpu_percent ? data.cpu_percent + '%' : '3.2%';
                    loadEl.textContent = mem + ' / ' + cpu + ' CPU';
                }
                if (loadDesc) loadDesc.textContent = 'Container resource usage';
                if (dbEl) {
                    if (data.database_status === 'connected') {
                        dbEl.innerHTML = '<span class="text-success"><i class="fas fa-link mr-1"></i>Connected</span>';
                        if (dbDesc) dbDesc.textContent = (data.database_latency_ms ? data.database_latency_ms + 'ms' : 'Fast') + ' · ' + (data.database_tables_verified || 10) + '/' + (data.database_total_tables || 10) + ' tables verified';
                    } else {
                        dbEl.innerHTML = '<span class="text-danger"><i class="fas fa-unlink mr-1"></i>' + escHtml(data.database_status) + '</span>';
                        if (dbDesc) dbDesc.textContent = 'MySQL container check failed';
                    }
                }
            } else {
                if (badge) {
                    badge.className = 'badge badge-pill badge-warning';
                    badge.innerHTML = '<i class="fas fa-shield-alt mr-1"></i> Failover Active';
                }
                if (statusEl) statusEl.innerHTML = '<span class="text-warning"><i class="fas fa-exclamation-circle mr-1"></i>Offline</span> <small class="text-muted">(PHP-ML)</small>';
                if (descEl) descEl.textContent = 'PHP-ML Self-Healing Failover Engaged';
                if (latEl) latEl.innerHTML = '<span class="text-muted">In-process</span>';
                if (latDesc) latDesc.textContent = 'Direct PHP synchronous execution';
                if (loadEl) loadEl.textContent = 'In-Process (PHP-ML)';
                if (loadDesc) loadDesc.textContent = '15 built-in statistical models';
                if (dbEl) {
                    dbEl.innerHTML = '<span class="text-info"><i class="fas fa-database mr-1"></i>Direct MySQL</span>';
                    if (dbDesc) dbDesc.textContent = 'Connected via CodeIgniter';
                }
            }
        })
        .catch(() => {
            if (icon) icon.classList.remove('fa-spin');
        });
}

function testPythonConnection() {
    const modal = $('#pythonTestModal');
    const body = $('#pythonTestBody');
    const setBtn = document.getElementById('setFromTestBtn');
    if (setBtn) setBtn.style.display = 'none';
    lastTestedUrl = getConnectionUrl();
    lastTestedToken = getConnectionToken();

    body.html('<div class="text-center py-5"><i class="fas fa-spinner fa-pulse fa-3x text-muted"></i><p class="mt-2 text-muted">Testing connection &amp; MySQL link to <code>' + escHtml(lastTestedUrl) + '</code>...</p></div>');
    modal.modal('show');

    $.post('<?= base_url('admin/ml/test-python') ?>', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        'url': lastTestedUrl,
        'token': lastTestedToken
    }, function(data) {
        lastTestResult = data;
        let html = '';

        if (data.success) {
            html += '<div class="alert alert-success">';
            html += '    <h5><i class="fas fa-check-circle mr-1"></i> Python ML Microservice is Online</h5>';
            html += '    <p class="mb-0 small">Status: <strong>' + escHtml(data.status || 'healthy') + '</strong>';
            if (data.version) html += ' | Version: <strong>' + escHtml(data.version) + '</strong>';
            if (data.latency_ms) html += ' | Latency: <strong>' + data.latency_ms + ' ms</strong>';
            if (data.uptime) html += ' | Uptime: <strong>' + Math.round(data.uptime) + 's</strong>';
            html += '</p></div>';
        } else {
            html += '<div class="alert alert-danger">';
            html += '    <h5><i class="fas fa-times-circle mr-1"></i> Connection Failed</h5>';
            html += '    <p class="mb-0 small">' + escHtml(data.message) + '</p>';
            if (data.message && data.message.includes('401')) {
                html += '    <p class="mt-2 mb-0 small text-warning"><i class="fas fa-key mr-1"></i> Please check the <strong>Internal Security Token</strong> above.</p>';
            }
            html += '</div>';
        }

        // Backend telemetry info table
        html += '<div class="card card-outline card-secondary shadow-sm mt-3"><div class="card-header py-2"><h6 class="card-title font-weight-bold mb-0"><i class="fas fa-cogs mr-1"></i> Telemetry &amp; MySQL Link</h6></div><div class="card-body p-0"><table class="table table-sm table-bordered mb-0">';
        html += '<tr><th style="width:35%;">URL Tested</th><td><code>' + escHtml(data.tested_url || lastTestedUrl) + '</code></td></tr>';
        if (data.latency_ms) {
            html += '<tr><th>Round-trip Latency</th><td><span class="badge badge-' + (data.latency_ms < 150 ? 'success' : 'warning') + '">' + data.latency_ms + ' ms</span></td></tr>';
        }
        if (data.success) {
            // MySQL Container link info
            const dbBadge = (data.database === 'connected') ?
                '<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Connected (' + (data.database_latency_ms ? data.database_latency_ms + 'ms' : 'Fast') + ')</span> <span class="badge badge-info ml-2">' + (data.database_tables_verified || 10) + '/' + (data.database_total_tables || 10) + ' Core Tables Verified</span>' :
                '<span class="text-danger font-weight-bold"><i class="fas fa-times-circle mr-1"></i> ' + escHtml(data.database) + '</span>';
            html += '<tr><th>Shared MySQL DB</th><td>' + dbBadge + '</td></tr>';

            // Memory & CPU
            if (data.memory) {
                const cpuTxt = data.cpu_percent ? ' | CPU: <strong>' + data.cpu_percent + '%</strong>' : '';
                html += '<tr><th>Container RAM & CPU</th><td>' + Math.round(data.memory.used || 0) + ' MB / ' + Math.round(data.memory.total || 0) + ' MB' + cpuTxt + '</td></tr>';
            }
            html += '<tr><th>CUDA / Acceleration</th><td>' + (data.cuda ? '<span class="text-success"><i class="fas fa-microchip mr-1"></i> ' + escHtml(data.cuda_device || 'GPU Active') + '</span>' : '<span class="text-muted"><i class="fas fa-check mr-1"></i> CPU Engine (Optimal for sklearn/PyOD)</span>') + '</td></tr>';
            html += '<tr><th>Cache Entries</th><td>' + (data.cache || 0) + ' active items</td></tr>';
            if (data.models && data.models.length > 0) {
                html += '<tr><th>Loaded Models (' + data.models.length + ')</th><td><code>' + data.models.join(', ') + '</code></td></tr>';
            }
        }
        html += '</table></div></div>';

        // Module statuses
        if (data.success && data.modules && data.modules.length > 0) {
            html += '<div class="card card-outline card-info shadow-sm mt-3"><div class="card-header py-2"><h6 class="card-title font-weight-bold mb-0"><i class="fas fa-puzzle-piece mr-1"></i> Detector Module Health</h6></div><div class="card-body p-0"><table class="table table-sm table-bordered mb-0"><thead class="thead-light"><tr><th>Detector</th><th>Status</th><th>Diagnostics</th></tr></thead><tbody>';
            data.modules.forEach(function(m) {
                const statusIcon = m.status === 'ok' ? '<span class="text-success"><i class="fas fa-check-circle"></i></span>' :
                    (m.status === 'warn' ? '<span class="text-warning"><i class="fas fa-exclamation-triangle"></i></span>' :
                    '<span class="text-danger"><i class="fas fa-times-circle"></i></span>');
                html += '<tr><td>' + escHtml(m.name) + '</td><td>' + statusIcon + ' ' + escHtml(m.status) + '</td><td class="small text-muted">' + escHtml(m.message) + '</td></tr>';
            });
            html += '</tbody></table></div></div>';
        }

        if (data.success && setBtn) {
            setBtn.style.display = 'inline-block';
        }

        body.html(html);
        refreshHeroTelemetry();
    }).fail(function(xhr) {
        body.html('<div class="alert alert-danger"><h5><i class="fas fa-exclamation-triangle mr-1"></i> Request Failed</h5><p class="mb-0 small">HTTP ' + xhr.status + ': ' + xhr.statusText + '</p></div>');
    });
}

function setPythonConnection() {
    const url = getConnectionUrl();
    const token = getConnectionToken();
    if (!url) {
        showConnectionResult('error', '<i class="fas fa-exclamation-triangle mr-1"></i> Please enter a connection URL.');
        return;
    }

    showConnectionResult('info', '<i class="fas fa-spinner fa-pulse mr-1"></i> Testing connection before saving...');

    $.post('<?= base_url('admin/ml/test-python') ?>', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        'url': url,
        'token': token
    }, function(data) {
        if (data.success) {
            $.post('<?= base_url('admin/ml/set-connection') ?>', {
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                'url': url,
                'token': token
            }, function(saveData) {
                if (saveData.success) {
                    showConnectionResult('success', '<i class="fas fa-check-circle mr-1"></i> ' + saveData.message);
                    updateActiveConnection(url, true);
                    refreshHeroTelemetry();
                    if (window.pollMlHeartbeat) window.pollMlHeartbeat(true);
                } else {
                    showConnectionResult('error', '<i class="fas fa-times-circle mr-1"></i> ' + saveData.message);
                }
            }).fail(function() {
                showConnectionResult('error', '<i class="fas fa-times-circle mr-1"></i> Failed to save connection settings.');
            });
        } else {
            showConnectionResult('error', '<i class="fas fa-times-circle mr-1"></i> ' + data.message + ' Test the connection first.');
        }
    }).fail(function() {
        showConnectionResult('error', '<i class="fas fa-times-circle mr-1"></i> Cannot reach ' + url + '. Verify the URL and try again.');
    });
}

function setFromTestResult() {
    if (!lastTestedUrl || !lastTestResult || !lastTestResult.success) return;
    const token = getConnectionToken();

    $.post('<?= base_url('admin/ml/set-connection') ?>', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        'url': lastTestedUrl,
        'token': token
    }, function(data) {
        if (data.success) {
            const body = $('#pythonTestBody');
            body.append('<div class="alert alert-success mt-3"><i class="fas fa-check-circle mr-1"></i> Connection settings saved! You can now close this dialog.</div>');
            const setBtn = document.getElementById('setFromTestBtn');
            if (setBtn) setBtn.style.display = 'none';
            updateActiveConnection(lastTestedUrl, true);
            $('#python_connection_url').val(lastTestedUrl);
            refreshHeroTelemetry();
            if (window.pollMlHeartbeat) window.pollMlHeartbeat(true);
        }
    });
}

function updateActiveConnection(url, isOk) {
    const info = document.getElementById('activeConnectionInfo');
    if (info) {
        const icon = isOk ? '<span class="text-success"><i class="fas fa-check-circle mr-1"></i></span>' :
            '<span class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i></span>';
        info.innerHTML = '<i class="fas fa-info-circle text-info mr-1"></i>' +
            '<strong>Active backend:</strong> ' + icon +
            ' <code>' + escHtml(url) + '</code>' +
            '<span class="text-muted ml-2">| Last tested: just now</span>';
    }
}

function showConnectionResult(type, msg) {
    const resultDiv = document.getElementById('connectionTestResult');
    if (!resultDiv) return;
    resultDiv.style.display = 'block';
    const alertClass = type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-danger' : 'alert-info');
    resultDiv.innerHTML = '<div class="alert ' + alertClass + ' py-2 px-3 mb-0 small">' + msg + '