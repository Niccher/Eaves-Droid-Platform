<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-spinner fa-pulse text-primary mr-2"></i>
                        Running Analysis
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>"><i class="fas fa-home mr-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>"><i class="fas fa-brain mr-1"></i>Intelligence</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis/anomalies') ?>"><i class="fas fa-bug mr-1"></i>Anomaly Detection</a></li>
                        <li class="breadcrumb-item active"><i class="fas fa-spinner mr-1"></i>Progress</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8 col-sm-10">
                    <div class="card card-primary card-outline shadow-lg">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-microscope fa-4x text-primary mb-3 fa-pulse"></i>
                            <h4 class="mb-2">Analyzing Your Data</h4>
                            <p class="text-muted mb-4" id="status-text">Initializing detection algorithms...</p>

                            <div class="progress progress-lg mb-2" style="height:24px; border-radius:12px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                     id="progress-bar"
                                     role="progressbar"
                                     style="width: 0%;"
                                     aria-valuenow="0"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    0%
                                </div>
                            </div>

                            <div class="row text-center mt-4">
                                <div class="col-6">
                                    <h5 class="mb-0" id="alg-done">0</h5>
                                    <small class="text-muted">Completed</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="mb-0" id="alg-total"><?= (int)($job['total_algorithms'] ?? 0) ?></h5>
                                    <small class="text-muted">Total Algorithms</small>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top">
                                <small class="text-muted" id="current-alg">
                                    <i class="fas fa-cog fa-spin mr-1"></i>
                                    <?= esc($job['current_algorithm'] ?? 'Preparing...') ?>
                                </small>
                            </div>
                            <div id="stuck-fallback" class="mt-3" style="display:none;">
                                <div class="alert alert-warning py-2 px-3 small" role="alert">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Taking longer than expected.
                                    <a href="<?= base_url("analysis/anomalies/results") ?>?job_id=<?= (int)($job['id'] ?? 0) ?>" class="alert-link">View Results</a>
                                    or
                                    <a href="<?= base_url("analysis/anomalies") ?>" class="alert-link">start a new analysis</a>.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
var CSRF_TOKEN = '<?= csrf_hash() ?>';
(function () {
    var jobId = <?= (int)$job['id'] ?>;
    var pollUrl = '<?= base_url("analysis/anomalies/status") ?>/' + jobId;
    var processUrl = '<?= base_url("analysis/anomalies/process") ?>/' + jobId;
    var resultsUrl = '<?= base_url("analysis/anomalies/results") ?>?job_id=' + jobId;
    var done = false;

    function updateUI(data) {
        var pct = Math.min(100, Math.max(0, data.progress_pct || 0));
        document.getElementById('progress-bar').style.width = pct + '%';
        document.getElementById('progress-bar').setAttribute('aria-valuenow', pct);
        document.getElementById('progress-bar').textContent = pct + '%';
        document.getElementById('alg-done').textContent = data.completed_algorithms || 0;
        if (data.current_algorithm) {
            document.getElementById('current-alg').innerHTML = '<i class="fas fa-cog fa-spin mr-1"></i>' + data.current_algorithm;
        }
        if (data.status === 'running' && data.current_algorithm) {
            var names = {
                'sms_freq': 'Frequency Spike Detector',
                'sms_time': 'Time-Pattern Analyser',
                'sms_cluster': 'Sender Cluster Analysis (K-Means)',
                'contacts_freq': 'New-Contact Frequency Monitor',
                'contacts_dup': 'Duplicate & Anomaly Detector',
                'calls_burst': 'Short-Call Burst Detector',
                'calls_night': 'Night-Activity Monitor',
                'loc_geofence': 'Geo-Fence Violation Detector',
                'loc_speed': 'Travel Speed Anomaly',
                'loc_dbscan': 'DBSCAN Trajectory Clustering',
                'apps_rep': 'Package Reputation Scanner',
                'apps_perm': 'Permission Anomaly Detector',
                'files_spike': 'File Creation Spike Detector',
                'act_screen': 'Screen-Time Anomaly Detector',
                'act_switch': 'App-Switch Rate Monitor',
                'dev_hw': 'Hardware Change Detector',
                'dev_net': 'Network Profile Monitor',
                'python_backend': 'Contacting Python backend...',
                'python_results': 'Processing Python results...',
            };
            var label = names[data.current_algorithm] || data.current_algorithm;
            document.getElementById('status-text').textContent = 'Running: ' + label;
        }
    }

    function poll() {
        if (done) return;
        fetch(pollUrl)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.error) {
                    document.getElementById('status-text').textContent = 'Error: ' + data.error;
                    setTimeout(poll, 3000);
                    return;
                }
                updateUI(data);
                if (data.status === 'completed' || data.status === 'failed') {
                    done = true;
                    document.getElementById('progress-bar').classList.remove('active', 'progress-bar-animated', 'progress-bar-striped');
                    document.getElementById('progress-bar').classList.add(data.status === 'completed' ? 'bg-success' : 'bg-danger');
                    document.getElementById('progress-bar').style.width = '100%';
                    document.getElementById('progress-bar').textContent = data.status === 'completed' ? 'Complete!' : 'Failed';
                    document.getElementById('status-text').textContent = data.status === 'completed' ? 'Analysis complete!' : 'Analysis failed: ' + (data.error_message || 'Unknown error');
                    document.querySelector('.fa-microscope').className = 'fas fa-' + (data.status === 'completed' ? 'check-circle text-success' : 'times-circle text-danger') + ' fa-4x mb-3';
                    document.querySelector('.fa-microscope').classList.remove('fa-pulse');
                    if (data.status === 'completed') {
                        setTimeout(function () { window.location.href = resultsUrl; }, 800);
                    }
                    return;
                }
                setTimeout(poll, 1200);
            })
            .catch(function () {
                setTimeout(poll, 2000);
            });
    }

    // Start processing in background
    fetch(processUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
    }).catch(function () {});

    // Start polling
    setTimeout(poll, 500);

    // Show fallback link after 60 seconds if still stuck
    setTimeout(function () {
        if (!done) {
            document.getElementById('stuck-fallback').style.display = 'block';
        }
    }, 60000);
})();
</script>
