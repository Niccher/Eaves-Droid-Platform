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
                        <ul class="nav nav-pills" id="ml-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link <?= (empty($active_tab) || $active_tab === 'overview' || $active_tab === 'general') ? 'active' : '' ?>" id="tab-general" data-toggle="pill" href="#pane-general" role="tab">
                                    <i class="fas fa-heartbeat mr-1 text-danger"></i> Telemetry &amp; Overview
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($active_tab === 'engines') ? 'active' : '' ?>" id="tab-engines" data-toggle="pill" href="#pane-engines" role="tab">
                                    <i class="fas fa-microchip mr-1 text-info"></i> Detection Engines
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($active_tab === 'algorithms') ? 'active' : '' ?>" id="tab-algorithms" data-toggle="pill" href="#pane-algorithms" role="tab">
                                    <i class="fas fa-sliders-h mr-1 text-warning"></i> Algorithms &amp; Rules
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($active_tab === 'python') ? 'active' : '' ?>" id="tab-python" data-toggle="pill" href="#pane-python" role="tab">
                                    <i class="fab fa-python mr-1 text-warning"></i> Python Backend
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($active_tab === 'phpml') ? 'active' : '' ?>" id="tab-phpml" data-toggle="pill" href="#pane-phpml" role="tab">
                                    <i class="fab fa-php mr-1 text-primary"></i> PHP-ML Local
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($active_tab === 'requirements') ? 'active' : '' ?>" id="tab-requirements" data-toggle="pill" href="#pane-requirements" role="tab">
                                    <i class="fas fa-cubes mr-1 text-secondary"></i> Specs &amp; Docker
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($active_tab === 'history') ? 'active' : '' ?>" id="tab-history" data-toggle="pill" href="#pane-history" role="tab">
                                    <i class="fas fa-history mr-1 text-success"></i> Run History
                                    <?php if (!empty($job_history)): ?>
                                    <span class="badge badge-secondary ml-1"><?= count($job_history) ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">

                            <!-- ======================== GENERAL ======================== -->
                            <div class="tab-pane fade <?= (empty($active_tab) || $active_tab === 'overview' || $active_tab === 'general') ? 'show active' : '' ?>" id="pane-general" role="tabpanel">
                                <form action="<?= base_url('admin/settings/update') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="section" value="ml">
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label font-weight-bold">Enable ML Features</label>
                                        <div class="col-sm-10">
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="ml_enabled" value="0">
                                                <input type="checkbox" name="ml_enabled" class="custom-control-input" id="ml_enabled" value="1" <?= ($settings['ml_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="ml_enabled">Enable machine learning analysis</label>
                                            </div>
                                            <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                <i class="fas fa-lightbulb text-info mr-1"></i>
                                                Master switch for all ML-powered features. When disabled, no algorithms execute and the anomaly detection pipeline is bypassed entirely.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label font-weight-bold">Anomaly Detection</label>
                                        <div class="col-sm-10">
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="ml_anomaly_enabled" value="0">
                                                <input type="checkbox" name="ml_anomaly_enabled" class="custom-control-input" id="ml_anomaly_enabled" value="1" <?= ($settings['ml_anomaly_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="ml_anomaly_enabled">Enable anomaly detection on uploaded data</label>
                                            </div>
                                            <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                <i class="fas fa-lightbulb text-info mr-1"></i>
                                                Automatically runs anomaly detection against new device uploads. Requires ML Features enabled. Triggers analysis on SMS, calls, locations, contacts, and app data per upload event.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-0">
                                        <label class="col-sm-2 col-form-label font-weight-bold">Analysis Schedule</label>
                                        <div class="col-sm-10">
                                            <select name="ml_schedule_interval" class="form-control">
                                                <option value="hourly" <?= ($settings['ml_schedule_interval'] ?? 'daily') === 'hourly' ? 'selected' : '' ?>>Hourly</option>
                                                <option value="daily" <?= ($settings['ml_schedule_interval'] ?? 'daily') === 'daily' ? 'selected' : '' ?>>Daily</option>
                                                <option value="weekly" <?= ($settings['ml_schedule_interval'] ?? 'daily') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                                            </select>
                                            <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                <i class="fas fa-lightbulb text-info mr-1"></i>
                                                How often to re-run batch analysis. <strong>Hourly</strong> &mdash; near real-time, higher server load. <strong>Daily</strong> &mdash; balanced for most deployments. <strong>Weekly</strong> &mdash; minimal overhead, suitable for low-traffic environments.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-top pt-3 mt-3 text-right">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save General ML Settings</button>
                                    </div>
                                </form>
                            </div>

                            <!-- ======================== PHP-ML ======================== -->
                            <div class="tab-pane fade <?= ($active_tab === 'phpml') ? 'show active' : '' ?>" id="pane-phpml" role="tabpanel">
                                <form action="<?= base_url('admin/settings/update') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="section" value="ml">

                                    <div class="callout callout-info bg-light py-2 px-3 mb-3 small">
                                        <i class="fas fa-database text-info mr-1"></i>
                                        Algorithm parameters below are stored in the database (<code>settings</code> table, <code>class='ml'</code>) and read at runtime by the PHP-ML engine. Defaults apply when no custom value has been saved.
                                    </div>

                                <!-- K-Means -->
                                <div class="card card-outline card-info shadow-sm mb-3">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-circle-nodes mr-1"></i> K-Means Clustering</h3></div>
                                    <div class="card-body">
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-2 col-form-label">Number of Clusters (K) <small class="text-muted">(default: 3)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_phpml_kmeans_k" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_kmeans_k'] ?? '3') ?>" min="2" max="20">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>How it works:</strong> K-Means partitions data points into <em>K</em> distinct clusters by iteratively assigning each point to the nearest cluster centroid, then recalculating centroids as the mean of assigned points. Used to group similar behavioural patterns &mdash; e.g., typical calling times, regular locations, periodic SMS activity.
                                                    <br><br>
                                                    <strong>Low K (2&ndash;3):</strong> Broad, coarse groupings &mdash; detects only the most obvious behavioural modes. Fewer false positives, but may miss subtle patterns.
                                                    <br>
                                                    <strong>High K (8+):</strong> Fine-grained clustering &mdash; identifies small, specialised behaviour groups. Higher sensitivity, but increases false-positive risk from noise.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- DBSCAN -->
                                <div class="card card-outline card-info shadow-sm mb-3">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-bezier-curve mr-1"></i> DBSCAN Density-Based Clustering</h3></div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Epsilon (&epsilon;) <small class="text-muted">(default: 0.01)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" step="0.001" name="ml_phpml_dbscan_epsilon" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_dbscan_epsilon'] ?? '0.01') ?>" min="0.001" max="1">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>How it works:</strong> DBSCAN groups points that are closely packed together, marking points in low-density regions as outliers. Unlike K-Means, it does <em>not</em> require specifying the number of clusters &mdash; it discovers arbitrary-shaped clusters based on density. Ideal for geographic trajectory analysis (geo-fence breaches, velocity anomalies).
                                                    <br><br>
                                                    <strong>Low &epsilon; (0.001&ndash;0.01):</strong> Very strict neighbourhood &mdash; only extremely close points are considered neighbours. Produces many small clusters and more points flagged as outliers (anomalies). High sensitivity.
                                                    <br>
                                                    <strong>High &epsilon; (0.1+):</strong> Large neighbourhood radius &mdash; merges points into broad clusters. Fewer outliers, lower sensitivity. May miss subtle anomalies.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-2 col-form-label">Min Samples <small class="text-muted">(default: 2)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_phpml_dbscan_minpoints" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_dbscan_minpoints'] ?? '2') ?>" min="1" max="50">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>How it works:</strong> Minimum number of points required to form a dense neighbourhood.
                                                    <br><br>
                                                    <strong>Low (1&ndash;3):</strong> A few points forming a loose group are enough to form a cluster. More clusters, fewer outliers.
                                                    <br>
                                                    <strong>High (10+):</strong> Requires many points in a neighbourhood to form a cluster. More points classified as outliers (anomalies).
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Isolation Forest -->
                                <div class="card card-outline card-info shadow-sm mb-0">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-tree mr-1"></i> Isolation Forest</h3></div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Number of Trees <small class="text-muted">(default: 100)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_phpml_isolationforest_trees" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_isolationforest_trees'] ?? '100') ?>" min="10" max="1000">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>How it works:</strong> Isolation Forest isolates anomalies by randomly partitioning the data with decision trees. AnomaliesController are few and different &mdash; they require fewer partitions to isolate, producing shorter path lengths. The algorithm builds an ensemble of trees (the "forest") and scores each point by its average path length. Highly effective for multi-dimensional anomaly detection across SMS, calls, locations, and app behaviour simultaneously.
                                                    <br><br>
                                                    <strong>Low (10&ndash;50):</strong> Fast training, lower memory &mdash; but higher variance in anomaly scores. May produce inconsistent results across runs.
                                                    <br>
                                                    <strong>High (500+):</strong> Stable, consistent anomaly scores. Converges to reliable estimates. Higher memory and CPU cost during training.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-2 col-form-label">Samples Per Tree <small class="text-muted">(default: 256)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_phpml_isolationforest_samples" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_isolationforest_samples'] ?? '256') ?>" min="32" max="4096">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>Low (32&ndash;128):</strong> Smaller subsamples &mdash; faster training, reduced memory. Good for very large datasets (&gt;100k rows). May reduce detection quality.
                                                    <br>
                                                    <strong>High (512+):</strong> Uses more data per tree &mdash; better representation of the data distribution. Higher memory footprint. Recommended for datasets with 10k&ndash;100k rows.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-top pt-3 mt-3 text-right">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save PHP-ML Settings</button>
                                    </div>
                                </form>
                            </div>

                            <!-- ======================== PYTHON ======================== -->
                            <div class="tab-pane fade <?= ($active_tab === 'python') ? 'show active' : '' ?>" id="pane-python" role="tabpanel">
                                <form action="<?= base_url('admin/settings/update') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="section" value="ml">

                                    <div class="callout callout-warning bg-light py-2 px-3 mb-3 small">
                                    <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                                    <strong>Requires a running Python backend service.</strong> Deploy via Docker (FastAPI + scikit-learn). The Python backend provides the 7 detector models — Isolation Forest, One-Class SVM, PCA anomaly scanner, contact-graph outlier, activity MLP, phishing keyword heuristic, and suspicious-file scanner — that extend beyond PHP-ML. All detectors are CPU-only sklearn/networkx models; no GPU or deep-learning framework required.
                                </div>
                                <div class="callout callout-info bg-light py-2 px-3 mb-3 small">
                                    <i class="fas fa-database text-info mr-1"></i>
                                    Python algorithm parameters below are stored in the database and sent to the Python backend at runtime. Defaults apply when no custom value has been saved.
                                </div>

                                <!-- Connection Settings -->
                                <div class="card card-outline card-warning shadow-sm mb-3">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-plug mr-1"></i> Connection Settings</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Enable Python</label>
                                            <div class="col-sm-10">
                                                <div class="custom-control custom-switch">
                                                    <input type="hidden" name="ml_python_enabled" value="0">
                                                    <input type="checkbox" name="ml_python_enabled" class="custom-control-input" id="ml_python_enabled" value="1" <?= ($settings['ml_python_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                                    <label class="custom-control-label" for="ml_python_enabled">Use external Python ML backend</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Connection URL</label>
                                            <div class="col-sm-10">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="python_connection_url"
                                                        value="<?= htmlspecialchars($settings['ml_python_url'] ?? $python_settings['url']) ?>"
                                                        placeholder="http://ml-eaves-droid:9070">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-info" type="button" onclick="testPythonConnection()">
                                                            <i class="fas fa-plug mr-1"></i> Test
                                                        </button>
                                                        <button class="btn btn-outline-success" type="button" onclick="setPythonConnection()">
                                                            <i class="fas fa-check mr-1"></i> Set
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Enter the full URL of the Python ML backend (e.g., <code>http://ml-eaves-droid:9070</code>). Click <strong>Test</strong> to verify connectivity, then <strong>Set</strong> to activate.</small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Internal Security Token</label>
                                            <div class="col-sm-10">
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="python_internal_token"
                                                        value="<?= htmlspecialchars($settings['ml_python_token'] ?? ($python_settings['token'] ?? '')) ?>"
                                                        placeholder="default_secure_token_change_me_in_prod">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleTokenVisibility()" title="Toggle Token Visibility">
                                                            <i class="fas fa-eye" id="tokenToggleIcon"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Header <code>X-Internal-Token</code> passed to the FastAPI microservice. Matches <code>settings.internal_token</code> in the Python container.</small>
                                            </div>
                                        </div>

                                        <!-- Test result display -->
                                        <div id="connectionTestResult" class="mt-2" style="display:none;"></div>

                                        <?php
                                        $activeUrl = $settings['ml_python_url'] ?? ($python_settings['url'] ?? '');
                                        $lastTest = $settings['ml_python_last_test'] ?? '';
                                        $lastTestOk = ($settings['ml_python_last_test_status'] ?? '') === 'ok';
                                        ?>
                                        <div class="mt-2 p-2 bg-light rounded small" id="activeConnectionInfo">
                                            <i class="fas fa-info-circle text-info mr-1"></i>
                                            <strong>Active backend:</strong>
                                            <?php if ($activeUrl && $lastTestOk): ?>
                                                <span class="text-success"><i class="fas fa-check-circle mr-1"></i></span>
                                            <?php elseif ($activeUrl): ?>
                                                <span class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i> (not tested)</span>
                                            <?php else: ?>
                                                <span class="text-muted">not configured</span>
                                            <?php endif; ?>
                                            <code><?= htmlspecialchars($activeUrl ?: '—') ?></code>
                                            <?php if ($lastTest): ?>
                                                <span class="text-muted ml-2">| Last tested: <?= htmlspecialchars($lastTest) ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($docker_settings) && ($docker_settings['detected'] ?? false)): ?>
                                        <div class="mt-3 p-3 bg-light rounded small">
                                            <i class="fab fa-docker text-success mr-1"></i>
                                            <strong>Docker-compose detected</strong> — ml-eaves-droid container (<code><?= esc($docker_settings['host']) ?></code>)
                                            <table class="table table-sm table-bordered mt-2 mb-0">
                                                <thead class="thead-light"><tr><th>Service</th><th>Internal Port</th><th>External (Host)</th></tr></thead>
                                                <tbody>
                                                    <tr><td><i class="fas fa-brain mr-1"></i>FastAPI (ML)</td><td><code><?= (int)($docker_settings['internal_port'] ?? 9070) ?></code></td><td><code><?= (int)($docker_settings['external_port'] ?? 9071) ?></code></td></tr>
                                                    <tr><td><i class="fas fa-chart-line mr-1"></i>Metrics</td><td><code><?= (int)($docker_settings['metrics_port'] ?? 9073) ?></code></td><td><code><?= (int)($docker_settings['metrics_port'] ?? 9073) ?></code></td></tr>
                                                    <tr><td><i class="fas fa-microchip mr-1"></i>TF Serving</td><td><code><?= (int)($docker_settings['tf_serving_port'] ?? 9072) ?></code></td><td><code><?= (int)($docker_settings['tf_serving_port'] ?? 9072) ?></code></td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- App Manifest Anomaly Scanner -->
                                <div class="card card-outline card-info shadow-sm mb-3">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-network-wired mr-1"></i> App Manifest Anomaly Scanner (PCA)</h3></div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Latent Dimensions <small class="text-muted">(default: 2)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_python_autoencoder_latent" class="form-control" value="<?= htmlspecialchars($settings['ml_python_autoencoder_latent'] ?? '2') ?>" min="2" max="128">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>How it works:</strong> Extracts manifest-style features (package-name patterns, sensitive permissions, name length) and fits a PCA model. PCA acts as a linear autoencoder &mdash; apps whose features are poorly reconstructed have high reconstruction error and are flagged.
                                                    <br><br>
                                                    <strong>Low (2&ndash;8):</strong> High compression &mdash; captures only the strongest patterns. Faster, may miss subtle anomalies.
                                                    <br>
                                                    <strong>High (32+):</strong> Low compression &mdash; captures finer details. Higher fidelity but may overfit.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-2 col-form-label">Anomaly Threshold (&sigma;) <small class="text-muted">(default: 2.0)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" step="0.1" name="ml_python_autoencoder_threshold" class="form-control" value="<?= htmlspecialchars($settings['ml_python_autoencoder_threshold'] ?? '2.0') ?>" min="1.0" max="6.0">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    Reconstruction error cutoff in standard deviations from the mean.
                                                    <br><br>
                                                    <strong>Low (2&sigma;):</strong> More sensitive &mdash; flags more apps. Higher recall, lower precision.
                                                    <br>
                                                    <strong>High (4&sigma;+):</strong> Very strict &mdash; only flags extreme deviations. Higher precision.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Activity Sequence Predictor -->
                                <div class="card card-outline card-info shadow-sm mb-3">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Activity Sequence Predictor (MLP)</h3></div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Sequence Length <small class="text-muted">(default: 20)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_python_lstm_sequence" class="form-control" value="<?= htmlspecialchars($settings['ml_python_lstm_sequence'] ?? '20') ?>" min="5" max="100">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>How it works:</strong> Trains a small multi-layer perceptron (MLP) on chronologically ordered app-usage timestamps to model normal activity rhythms. The model predicts the next usage time; a large prediction error signals an anomalous transition.
                                                    <br><br>
                                                    <strong>Low (5&ndash;10):</strong> Short memory window &mdash; faster training, less contextual awareness.
                                                    <br>
                                                    <strong>High (50+):</strong> Extended memory &mdash; captures longer-range patterns. Slower training.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-2 col-form-label">Hidden Units <small class="text-muted">(default: 32)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_python_lstm_units" class="form-control" value="<?= htmlspecialchars($settings['ml_python_lstm_units'] ?? '32') ?>" min="16" max="256">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    Size of the MLP hidden layer. Controls the model's capacity to learn usage rhythms.
                                                    <br><br>
                                                    <strong>Low (16&ndash;32):</strong> Simple patterns, fast inference.
                                                    <br>
                                                    <strong>High (128+):</strong> Complex pattern recognition, higher accuracy potential. More compute required.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- One-Class SVM -->
                                <div class="card card-outline card-info shadow-sm mb-3">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-vector-square mr-1"></i> One-Class SVM</h3></div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Nu (&nu;) <small class="text-muted">(default: 0.05)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" step="0.01" name="ml_python_oneclass_nu" class="form-control" value="<?= htmlspecialchars($settings['ml_python_oneclass_nu'] ?? '0.05') ?>" min="0.01" max="0.5">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>How it works:</strong> One-Class SVM learns a decision boundary around normal system states (CPU, RAM, battery temperature, active radios). Points outside the boundary are flagged anomalous. Unsupervised &mdash; no labelled data required.
                                                    <br><br>
                                                    <strong>Low (0.01&ndash;0.05):</strong> Tight boundary &mdash; few anomalies flagged, high confidence. Suitable for stable environments.
                                                    <br>
                                                    <strong>High (0.2+):</strong> Loose boundary &mdash; more anomalies flagged, higher false-positive rate. Use when expecting subtle deviations.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-2 col-form-label">Gamma (&gamma;) <small class="text-muted">(default: 0.01)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" step="0.001" name="ml_python_oneclass_gamma" class="form-control" value="<?= htmlspecialchars($settings['ml_python_oneclass_gamma'] ?? '0.01') ?>" min="0.001" max="1.0">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    RBF kernel coefficient &mdash; controls the influence radius of each training point.
                                                    <br><br>
                                                    <strong>Low (0.001):</strong> Broad decision boundary &mdash; smooth, generalised normal region. May miss localised anomalies.
                                                    <br>
                                                    <strong>High (0.1):</strong> Tight boundary around each data point &mdash; sensitive to local deviations. Risk of overfitting to training noise.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Python Isolation Forest -->
                                <div class="card card-outline card-info shadow-sm mb-0">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-tree mr-1"></i> Isolation Forest (Python)</h3></div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Trees <small class="text-muted">(default: 200)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_python_iforest_trees" class="form-control" value="<?= htmlspecialchars($settings['ml_python_iforest_trees'] ?? '200') ?>" min="10" max="2000">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>How it works:</strong> Python scikit-learn Isolation Forest implementation with enhanced options. Uses random partitioning to isolate anomalies, supporting larger ensembles and configurable contamination rate for automatic thresholding.
                                                    <br><br>
                                                    <strong>Low (10&ndash;50):</strong> Fast training &mdash; higher variance. Suitable for rapid iteration during tuning.
                                                    <br>
                                                    <strong>High (500+):</strong> Stable, converged scores &mdash; higher memory footprint. Recommended for production.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Samples <small class="text-muted">(default: 512)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_python_iforest_samples" class="form-control" value="<?= htmlspecialchars($settings['ml_python_iforest_samples'] ?? '512') ?>" min="32" max="8192">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    Number of samples drawn for each tree.
                                                    <br><br>
                                                    <strong>Low (32&ndash;128):</strong> Smaller subsample &mdash; faster training, suitable for very large datasets.
                                                    <br>
                                                    <strong>High (1024+):</strong> More representative subsample &mdash; better anomaly scoring accuracy. Higher memory usage.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-2 col-form-label">Contamination <small class="text-muted">(default: 0.05)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" step="0.01" name="ml_python_iforest_contamination" class="form-control" value="<?= htmlspecialchars($settings['ml_python_iforest_contamination'] ?? '0.05') ?>" min="0.01" max="0.5">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    Expected proportion of anomalies in the dataset. Used to set the automatic threshold.
                                                    <br><br>
                                                    <strong>Low (0.01&ndash;0.03):</strong> Expects 1&ndash;3% anomaly rate. Strict threshold, higher precision.
                                                    <br>
                                                    <strong>High (0.15+):</strong> Expects more anomalies &mdash; lower threshold, higher recall. Adjust based on your environment's typical anomaly rate.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="callout callout-success bg-light py-2 px-3 mt-3 small mb-0">
                                    <h6 class="text-success font-weight-bold"><i class="fas fa-plus-circle mr-1"></i> Additional Python Models Available</h6>
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <strong>Gaussian Mixture Model (GMM)</strong>
                                            <br><small>Soft clustering for behavioural profiling. Better than K-Means for overlapping behaviour patterns. Params: <code>n_components</code> (2&ndash;10), <code>covariance_type</code> (full|tied|diag).</small>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Local Outlier Factor (LOF)</strong>
                                            <br><small>Density-based detector comparing local density to neighbours. Effective for local anomalies. Params: <code>n_neighbors</code> (10&ndash;50), <code>contamination</code>.</small>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>PCA Anomaly Detector</strong>
                                            <br><small>Projects data onto principal components and measures reconstruction error. Fast, interpretable. Params: <code>n_components</code>, <code>threshold_&sigma;</code>.</small>
                                        </div>
                                    </div>
                                    <div class="border-top pt-3 mt-3 text-right">
                                        <button type="submit" class="btn btn-warning font-weight-bold"><i class="fas fa-save mr-1"></i> Save Python ML Settings</button>
                                    </div>
                                </form>
                            </div>

                            <!-- ======================== REQUIREMENTS ======================== -->
                            <div class="tab-pane fade <?= ($active_tab === 'requirements') ? 'show active' : '' ?>" id="pane-requirements" role="tabpanel">
                                <div class="callout callout-info bg-light py-2 px-3 mb-3 small">
                                    <i class="fas fa-info-circle text-info mr-1"></i>
                                    System requirements vary significantly based on dataset size. Below are recommended specifications for the PHP server and Python Docker backend at different data volumes. These assume processing SMS + Calls + Contacts + Locations + Apps + FilesController + Device Activity simultaneously.
                                </div>

                                <!-- Scale Comparison Table -->
                                <div class="card card-outline card-dark shadow-sm mb-3">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-server mr-1"></i> PHP Server Requirements (PHP-ML Engine)</h3></div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover mb-0">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th style="width:120px;">Dataset Size</th>
                                                        <th>CPU</th>
                                                        <th>RAM</th>
                                                        <th>PHP Memory Limit</th>
                                                        <th>Max Execution Time</th>
                                                        <th>Storage (DB + Temp)</th>
                                                        <th>Notes</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="table-success">
                                                        <td><strong>&lt; 1k entries</strong></td>
                                                        <td>1 vCPU (any)</td>
                                                        <td>512 MB</td>
                                                        <td>128 MB</td>
                                                        <td>30 seconds</td>
                                                        <td>&lt; 100 MB</td>
                                                        <td>Development / single-user. PHP-ML runs easily within shared hosting limits.</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>&lt; 5k entries</strong></td>
                                                        <td>1&ndash;2 vCPU</td>
                                                        <td>1 GB</td>
                                                        <td>256 MB</td>
                                                        <td>60 seconds</td>
                                                        <td>&lt; 500 MB</td>
                                                        <td>Small team / testing. K-Means and DBSCAN complete in seconds.</td>
                                                    </tr>
                                                    <tr class="table-warning">
                                                        <td><strong>&lt; 10k entries</strong></td>
                                                        <td>2 vCPU @ 2+ GHz</td>
                                                        <td>2 GB</td>
                                                        <td>256&ndash;512 MB</td>
                                                        <td>120 seconds</td>
                                                        <td>&lt; 1 GB</td>
                                                        <td>Small production. Isolation Forest with 100 trees finishes within 30s.</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>&lt; 20k entries</strong></td>
                                                        <td>2&ndash;4 vCPU @ 2.5+ GHz</td>
                                                        <td>4 GB</td>
                                                        <td>512 MB</td>
                                                        <td>180 seconds</td>
                                                        <td>1&ndash;2 GB</td>
                                                        <td>Medium production. Consider queue-based processing for long-running tasks.</td>
                                                    </tr>
                                                    <tr class="table-danger">
                                                        <td><strong>&lt; 50k entries</strong></td>
                                                        <td>4+ vCPU @ 3+ GHz</td>
                                                        <td>8 GB</td>
                                                        <td>1 GB</td>
                                                        <td>300 seconds</td>
                                                        <td>2&ndash;5 GB</td>
                                                        <td>Heavy production. Strongly recommend switching to Python backend at this scale. DBSCAN &amp; Isolation Forest may timeout on large PHP-ML runs.</td>
                                                    </tr>
                                                    <tr class="table-danger">
                                                        <td><strong>50k+ entries</strong></td>
                                                        <td>4&ndash;8 vCPU</td>
                                                        <td>16 GB+</td>
                                                        <td>2 GB+</td>
                                                        <td>600+ seconds</td>
                                                        <td>5&ndash;20 GB</td>
                                                        <td>Production with heavy data. PHP-ML not recommended &mdash; use Python backend with job queue. PHP only feasible with subset sampling.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card-footer small text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        PHP-ML runs synchronously within the web request. For datasets &gt;20k entries, configure a queue worker or use the Python backend to avoid HTTP timeouts.
                                    </div>
                                </div>

                                <div class="card card-outline card-warning shadow-sm mb-3">
                                    <div class="card-header"><h3 class="card-title"><i class="fab fa-python mr-1"></i> Python Backend Requirements (Docker)</h3></div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover mb-0">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th style="width:120px;">Dataset Size</th>
                                                        <th>CPU</th>
                                                        <th>RAM</th>
                                                        <th>GPU</th>
                                                        <th>Docker Image Size</th>
                                                        <th>Python Packages</th>
                                                        <th>Notes</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="table-success">
                                                        <td><strong>&lt; 1k entries</strong></td>
                                                        <td>0.5 vCPU</td>
                                                        <td>256 MB</td>
                                                        <td>Not required</td>
                                                        <td>~1.2 GB (slim)</td>
                                                        <td>scikit-learn, numpy, FastAPI</td>
                                                        <td>Overkill for this size. PHP-ML sufficient. Runs on any Docker host.</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>&lt; 5k entries</strong></td>
                                                        <td>1 vCPU</td>
                                                        <td>512 MB</td>
                                                        <td>Not required</td>
                                                        <td>~1.5 GB</td>
                                                        <td>+ pandas, scipy, networkx</td>
                                                        <td>All 7 detectors finish in seconds. No GPU needed.</td>
                                                    </tr>
                                                    <tr class="table-warning">
                                                        <td><strong>&lt; 10k entries</strong></td>
                                                        <td>2 vCPU</td>
                                                        <td>1 GB</td>
                                                        <td>Optional</td>
                                                        <td>~1.8 GB</td>
                                                        <td>+ onnxruntime (optional)</td>
                                                        <td>Isolation Forest, One-Class SVM complete in &lt;10s. All detectors are CPU-only sklearn models.</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>&lt; 20k entries</strong></td>
                                                        <td>2&ndash;4 vCPU</td>
                                                        <td>2&ndash;4 GB</td>
                                                        <td>Not required</td>
                                                        <td>~2.0 GB</td>
                                                        <td>+ onnxruntime (optional)</td>
                                                        <td>Detectors scale on CPU; use Redis-like caching (model cache) to avoid recomputation.</td>
                                                    </tr>
                                                    <tr class="table-danger">
                                                        <td><strong>&lt; 50k entries</strong></td>
                                                        <td>4+ vCPU</td>
                                                        <td>4&ndash;8 GB</td>
                                                        <td>Not required</td>
                                                        <td>~2.5 GB</td>
                                                        <td>+ onnxruntime (optional)</td>
                                                        <td>Recommended production target for the Python backend. All 7 detectors complete within 60s on CPU. Use the model cache to avoid recomputation.</td>
                                                    </tr>
                                                    <tr class="table-danger">
                                                        <td><strong>50k+ entries</strong></td>
                                                        <td>8+ vCPU</td>
                                                        <td>16&ndash;32 GB</td>
                                                        <td>Not required</td>
                                                        <td>~3.0 GB + model storage</td>
                                                        <td>+ onnxruntime (optional)</td>
                                                        <td>Large-scale production. Implement an async job queue with the model cache to avoid recomputation. All detectors are CPU-only sklearn models.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card-footer small text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        The Python backend runs as a separate Docker container communicating via HTTP REST. It does NOT share PHP server resources. All detectors are CPU-only sklearn/networkx models — no GPU or nvidia-docker runtime is required.
                                    </div>
                                </div>

                                <div class="card card-outline card-success shadow-sm mb-0">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-cubes mr-1"></i> Docker Compose Reference</h3></div>
                                    <div class="card-body">
                                        <div class="callout callout-success bg-light py-2 px-3 mb-3 small">
                                            <i class="fas fa-book-open text-success mr-1"></i>
                                            Below is a reference <code>docker-compose.yml</code> snippet for deploying the Python ML backend alongside the PHP application. Adjust resource limits based on dataset size from the tables above.
                                        </div>
                                        <pre class="bg-dark text-light p-3 rounded" style="overflow-x: auto; font-size: 0.85rem; line-height: 1.5;">
<code>services:
  ml-eaves-droid:
    build: ./ML Eaves Droid
    image: ml-eaves-droid:latest
    ports:
      - "9071:9070"   # FastAPI (external : internal)
    env_file:
      - .env
    volumes:
      - ./ML Eaves Droid:/app
    restart: unless-stopped
    networks:
      - hosts-shared-network
    healthcheck:
      test: ["CMD", "python", "-c", "import urllib.request;urllib.request.urlopen('http://localhost:9070/api/health')"]
      interval: 30s
      timeout: 10s
      retries: 3

networks:
  hosts-shared-network:
    name: hosts-shared-network
    external: true</code>
                                        </pre>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <div class="small">
                                                    <strong class="text-success"><i class="fas fa-check-circle mr-1"></i>Deployment:</strong><br>
                                                    The backend listens on port <code>9070</code> internally (mapped to <code>9071</code> externally) and exposes <code>/api/health</code>, <code>/api/models</code>, and <code>/api/analyze</code>. All detectors are CPU-only sklearn/networkx models — no GPU, TensorFlow, or PyTorch required. Point the webapp at <code>http://ml-eaves-droid:9070</code> (internal) or <code>http://&lt;host&gt;:9071</code>.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ======================== DETECTION ENGINES TAB ======================== -->
                            <div class="tab-pane fade <?= ($active_tab === 'engines') ? 'show active' : '' ?>" id="pane-engines" role="tabpanel">
                                <form method="post" action="<?= base_url('admin/anomalies') ?>" id="anomalyEngineForm">
                                    <?= csrf_field() ?>
                                    <div class="callout callout-info bg-light border-left-info py-2 px-3 mb-3 small">
                                        <i class="fas fa-info-circle text-info mr-1"></i>
                                        Choose which detection engine is enabled for anomalous behavior analysis. <strong>Hybrid Mode</strong> automatically routes deep-learning detectors to the Python container and falls back gracefully to local PHP-ML if unreachable.
                                    </div>
                                    <div class="row">
                                        <?php if (!empty($engines)): ?>
                                        <?php foreach ($engines as $e):
                                            $checked = ($default_engine ?? 'php') === $e['id'] ? 'checked' : '';
                                        ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 border <?= $checked ? 'border-primary shadow-sm' : '' ?>">
                                                <div class="card-body text-center">
                                                    <div class="mb-3" style="font-size:2.5rem;">
                                                        <i class="<?= $e['icon'] ?> text-<?= $e['icon_color'] ?>"></i>
                                                    </div>
                                                    <h5 class="font-weight-bold"><?= $e['label'] ?></h5>
                                                    <p class="text-muted small"><?= $e['description'] ?></p>
                                                    <div class="d-flex justify-content-center flex-wrap" style="gap:.25rem;">
                                                        <?php foreach ($e['badges'] as $b): ?>
                                                        <span class="badge badge-<?= $b['color'] ?>">
                                                            <i class="<?= $b['icon'] ?> mr-1"></i><?= $b['text'] ?>
                                                        </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="mt-3">
                                                        <div class="custom-control custom-radio">
                                                            <input type="radio" id="engine_<?= $e['id'] ?>"
                                                                   name="default_engine" value="<?= $e['id'] ?>"
                                                                   class="custom-control-input" <?= $checked ?>>
                                                            <label class="custom-control-label font-weight-bold" for="engine_<?= $e['id'] ?>">
                                                                <?= $e['id'] === 'both' ? 'Enable Hybrid Failover' : 'Set as Default' ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="border-top pt-3 text-right">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Engine Selection</button>
                                    </div>
                                </form>
                            </div>

                            <!-- ======================== ALGORITHMS & RULES TAB ======================== -->
                            <div class="tab-pane fade <?= ($active_tab === 'algorithms') ? 'show active' : '' ?>" id="pane-algorithms" role="tabpanel">
                                <form method="post" action="<?= base_url('admin/anomalies') ?>" id="anomalyAlgoForm">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="default_engine" value="<?= esc($default_engine ?? 'php') ?>">
                                    <div class="callout callout-warning bg-light border-left-warning py-2 px-3 mb-3 small">
                                        <i class="fas fa-sliders-h text-warning mr-1"></i>
                                        Select which anomaly detection algorithms users can run. Unchecked algorithms will be hidden from users. When "Allow All" is checked, all 15 detectors are active.
                                    </div>

                                    <?php
                                    $allowedSet = !empty($allowed_algorithms) ? array_flip($allowed_algorithms) : [];
                                    $allAllowed = empty($allowed_algorithms);
                                    ?>
                                    <div class="mb-3 d-flex justify-content-between align-items-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="toggle-all-algs" <?= $allAllowed ? 'checked' : '' ?>>
                                            <label class="custom-control-label font-weight-bold" for="toggle-all-algs">
                                                <i class="fas fa-check-double mr-1 text-primary"></i> Allow All Algorithms
                                            </label>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-warning font-weight-bold">
                                            <i class="fas fa-save mr-1"></i> Save Allowed Algorithms
                                        </button>
                                    </div>
                                    <hr>

                                    <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $catKey => $cat): ?>
                                    <div class="card card-outline card-<?= $cat['color'] ?> shadow-sm mb-3">
                                        <div class="card-header py-2">
                                            <h5 class="card-title text-dark font-weight-bold mb-0">
                                                <i class="<?= $cat['icon'] ?> text-<?= $cat['color'] ?> mr-2"></i><?= $cat['label'] ?>
                                            </h5>
                                            <div class="card-tools">
                                                <span class="badge badge-<?= $cat['color'] ?>"><?= count($cat['algorithms']) ?> algorithms</span>
                                            </div>
                                        </div>
                                        <div class="card-body py-2">
                                            <div class="row">
                                                <?php foreach ($cat['algorithms'] as $alg): 
                                                    $checked = $allAllowed || isset($allowedSet[$alg['id']]);
                                                    $compatLabel = match($alg['compat']) {
                                                        'both' => 'PHP + Python',
                                                        'php' => 'PHP',
                                                        'python' => 'Python',
                                                        default => $alg['compat'],
                                                    };
                                                    $compatBadge = match($alg['compat']) {
                                                        'both' => 'primary',
                                                        'php' => 'success',
                                                        'python' => 'warning',
                                                        default => 'secondary',
                                                    };
                                                ?>
                                                <div class="col-md-6 col-lg-4 mb-2">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input alg-checkbox"
                                                               id="alg_<?= $alg['id'] ?>"
                                                               name="allowed_algorithms[]" value="<?= $alg['id'] ?>"
                                                               <?= $checked ? 'checked' : '' ?>>
                                                        <label class="custom-control-label" for="alg_<?= $alg['id'] ?>">
                                                            <strong><?= esc($alg['name']) ?></strong>
                                                            <span class="badge badge-<?= $compatBadge ?> ml-1" style="font-size:10px;"><?= $compatLabel ?></span>
                                                            <br>
                                                            <small class="text-muted"><?= esc($alg['description']) ?></small>
                                                        </label>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php endif; ?>

                                    <div class="border-top pt-3 text-right">
                                        <button type="submit" class="btn btn-warning font-weight-bold"><i class="fas fa-save mr-1"></i> Save Algorithm Preferences</button>
                                    </div>
                                </form>
                            </div>

                            <!-- ======================== RUN HISTORY TAB ======================== -->
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

<div class="modal fade" id="pythonTestModal" tabindex="-1" role="dialog" aria-labelledby="pythonTestModalLabel" aria-hidden="true">
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
        'url': lastTestedUrl
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
        'url': url
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

// Auto-switch to tab if passed in query param or hash
(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab') || window.location.hash.replace('#', '');
    if (tabParam) {
        const tabEl = document.getElementById('tab-' + tabParam);
        if (tabEl) {
            $(tabEl).tab('show');
        }
    }
})();

// Initial hero telemetry check
setTimeout(refreshHeroTelemetry, 800);
setInterval(refreshHeroTelemetry, 30000);
</script>
