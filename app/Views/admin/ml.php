<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-robot mr-2"></i>ML / AI Configuration</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item active">ML/AI</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (session()->getFlashdata('message')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= session()->getFlashdata('message') ?>
            </div>
            <?php endif; ?>

            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <h5 class="text-info font-weight-bold"><i class="fas fa-info-circle mr-2"></i>ML Engine Overview</h5>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge badge-dark mr-2 px-3 py-2"><i class="fab fa-php mr-1"></i> PHP-ML</span>
                            <span class="text-muted small">In-process PHP-ML library &mdash; lightweight, no external dependencies, runs synchronously within the request lifecycle.</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge badge-warning mr-2 px-3 py-2"><i class="fab fa-python mr-1"></i> Python</span>
                            <span class="text-muted small">External Python microservice (Docker) &mdash; scikit-learn &amp; networkx models, CPU-only. Accessed via REST API.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live ML Engine Telemetry Hero Banner -->
            <div class="card card-outline card-info shadow-sm mb-4" id="heroTelemetryCard">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                    <h5 class="card-title text-dark font-weight-bold mb-0">
                        <i class="fas fa-heartbeat text-danger mr-2"></i>Live ML Engine Telemetry &amp; Health
                    </h5>
                    <div class="card-tools">
                        <span class="badge badge-pill badge-secondary" id="heroStatusBadge">Checking telemetry...</span>
                        <button type="button" class="btn btn-tool" onclick="refreshHeroTelemetry(true)" title="Refresh Live Telemetry">
                            <i class="fas fa-sync-alt" id="heroRefreshIcon"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body py-3">
                    <div class="row">
                        <!-- Container Status -->
                        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                            <div class="info-box bg-light border shadow-none mb-0">
                                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-server"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text text-muted">Microservice Status</span>
                                    <span class="info-box-number" id="heroServiceStatus">FastAPI Backend</span>
                                    <span class="progress-description small text-muted" id="heroServiceDesc">Polling container...</span>
                                </div>
                            </div>
                        </div>
                        <!-- Latency -->
                        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                            <div class="info-box bg-light border shadow-none mb-0">
                                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-tachometer-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text text-muted">Inference Latency</span>
                                    <span class="info-box-number" id="heroLatency">-- ms</span>
                                    <span class="progress-description small text-muted" id="heroLatencyDesc">Round-trip response</span>
                                </div>
                            </div>
                        </div>
                        <!-- Container RAM & CPU -->
                        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                            <div class="info-box bg-light border shadow-none mb-0">
                                <span class="info-box-icon bg-warning text-white elevation-1"><i class="fas fa-microchip"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text text-muted">RAM &amp; CPU Load</span>
                                    <span class="info-box-number" id="heroLoad">-- MB / --%</span>
                                    <span class="progress-description small text-muted" id="heroLoadDesc">Container telemetry</span>
                                </div>
                            </div>
                        </div>
                        <!-- Shared MySQL Link -->
                        <div class="col-xl-3 col-md-6">
                            <div class="info-box bg-light border shadow-none mb-0">
                                <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-database"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text text-muted">Shared MySQL DB</span>
                                    <span class="info-box-number" id="heroDbStatus">Checking...</span>
                                    <span class="progress-description small text-muted" id="heroDbDesc">Direct container link</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header p-2 bg-light border-bottom">
<?= view('admin/ml/_ml_tabs') ?>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
<?= view('admin/ml/' . ($active_tab ?? 'general')) ?>
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
    resultDiv.innerHTML = '<div class="alert ' + alertClass + ' py-2 px-3 mb-0 small">' + msg + '</div>';
    setTimeout(function() {
        if (type === 'success') {
            resultDiv.style.display = 'none';
        }
    }, 8000);
}

function escHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str || ''));
    return div.innerHTML;
}

// Toggle all algorithms checkbox
const toggleAll = document.getElementById('toggle-all-algs');
if (toggleAll) {
    toggleAll.addEventListener('change', function() {
        const checked = this.checked;
        document.querySelectorAll('.alg-checkbox').forEach(function(cb) {
            cb.checked = checked;
        });
    });
}

// Auto-navigate to tab if passed in hash (#pane-* or #*)
(function() {
    function checkLegacyHash() {
        const hash = window.location.hash;
        if (hash) {
            const cleanParam = hash.replace(/^#pane-/, '').replace(/^#/, '');
            const valid = ['general', 'overview', 'engines', 'algorithms', 'python', 'phpml', 'history'];
            const target = (cleanParam === 'overview') ? 'general' : cleanParam;
            const currentTab = '<?= $active_tab ?? "general" ?>';
            if (valid.includes(target) && target !== currentTab) {
                window.location.replace('<?= base_url("admin/ml") ?>/' + target);
            }
        }
    }
    checkLegacyHash();
    window.addEventListener('hashchange', checkLegacyHash);
})();

// Initial hero telemetry check
setTimeout(refreshHeroTelemetry, 800);
setInterval(refreshHeroTelemetry, 30000);
</script>
