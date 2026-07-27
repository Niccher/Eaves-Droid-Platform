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
                            <span class="text-muted small">External Python microservice (Docker) &mdash; scikit-learn, TensorFlow, GPU acceleration. Accessed via REST API.</span>
                        </div>
                    </div>
                </div>
            </div>

            <form action="<?= base_url('admin/settings/update') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="section" value="ml">

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header p-0">
                        <ul class="nav nav-pills ml-3 mt-2 mb-2" id="ml-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-general" data-toggle="pill" href="#pane-general" role="tab">
                                    <i class="fas fa-cog mr-1"></i> General
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-phpml" data-toggle="pill" href="#pane-phpml" role="tab">
                                    <i class="fab fa-php mr-1"></i> PHP-ML
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-python" data-toggle="pill" href="#pane-python" role="tab">
                                    <i class="fab fa-python mr-1"></i> Python
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-requirements" data-toggle="pill" href="#pane-requirements" role="tab">
                                    <i class="fas fa-clipboard-list mr-1"></i> Requirements
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">

                            <!-- ======================== GENERAL ======================== -->
                            <div class="tab-pane fade show active" id="pane-general" role="tabpanel">
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
                            </div>

                            <!-- ======================== PHP-ML ======================== -->
                            <div class="tab-pane fade" id="pane-phpml" role="tabpanel">

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
                                                    <strong>How it works:</strong> Isolation Forest isolates anomalies by randomly partitioning the data with decision trees. Anomalies are few and different &mdash; they require fewer partitions to isolate, producing shorter path lengths. The algorithm builds an ensemble of trees (the "forest") and scores each point by its average path length. Highly effective for multi-dimensional anomaly detection across SMS, calls, locations, and app behaviour simultaneously.
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
                                </div>
                            </div>

                            <!-- ======================== PYTHON ======================== -->
                            <div class="tab-pane fade" id="pane-python" role="tabpanel">
                                <div class="callout callout-warning bg-light py-2 px-3 mb-3 small">
                                    <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                                    <strong>Requires a running Python backend service.</strong> Deploy via Docker with the required Python dependencies (scikit-learn, TensorFlow/PyTorch, Flask/FastAPI). The Python backend provides GPU acceleration, deep learning models, and advanced algorithms not available in PHP-ML. Use it for production-scale deployments with &gt;50k records.
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

                                <!-- Autoencoder -->
                                <div class="card card-outline card-info shadow-sm mb-3">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-network-wired mr-1"></i> Autoencoder Neural Network</h3></div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Latent Dimensions <small class="text-muted">(default: 16)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_python_autoencoder_latent" class="form-control" value="<?= htmlspecialchars($settings['ml_python_autoencoder_latent'] ?? '16') ?>" min="2" max="128">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>How it works:</strong> The autoencoder learns to compress input features through a bottleneck (latent space) and reconstruct them. Anomalies produce high reconstruction error because they deviate from learned normal patterns.
                                                    <br><br>
                                                    <strong>Low (2&ndash;8):</strong> High compression &mdash; captures only the strongest patterns. Faster training. May miss subtle anomalies.
                                                    <br>
                                                    <strong>High (32+):</strong> Low compression &mdash; captures finer details. Higher fidelity but may overfit and miss generalised anomalies.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Training Epochs <small class="text-muted">(default: 50)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_python_autoencoder_epochs" class="form-control" value="<?= htmlspecialchars($settings['ml_python_autoencoder_epochs'] ?? '50') ?>" min="10" max="500">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>Low (10&ndash;20):</strong> Fast training, lower resource usage &mdash; risk of underfitting on complex patterns.
                                                    <br>
                                                    <strong>High (100+):</strong> Better convergence to data distribution &mdash; higher training time and resource usage. Risk of overfitting on small datasets.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-2 col-form-label">Anomaly Threshold (&sigma;) <small class="text-muted">(default: 3.0)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" step="0.1" name="ml_python_autoencoder_threshold" class="form-control" value="<?= htmlspecialchars($settings['ml_python_autoencoder_threshold'] ?? '3.0') ?>" min="1.0" max="6.0">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    Reconstruction error cutoff in standard deviations from the mean.
                                                    <br><br>
                                                    <strong>Low (2&sigma;):</strong> More sensitive &mdash; flags more points as anomalies. Higher recall, lower precision.
                                                    <br>
                                                    <strong>High (4&sigma;+):</strong> Very strict &mdash; only flags extreme deviations. Higher precision, may miss subtle anomalies.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- LSTM -->
                                <div class="card card-outline card-info shadow-sm mb-3">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> LSTM Sequence Predictor</h3></div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Sequence Length <small class="text-muted">(default: 20)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_python_lstm_sequence" class="form-control" value="<?= htmlspecialchars($settings['ml_python_lstm_sequence'] ?? '20') ?>" min="5" max="100">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    <strong>How it works:</strong> LSTM (Long Short-Term Memory) networks model sequential user behaviour &mdash; app switch patterns, location sequences, call timing. The network predicts the next event; high prediction error signals anomalous behaviour.
                                                    <br><br>
                                                    <strong>Low (5&ndash;10):</strong> Short memory window &mdash; faster training, less contextual awareness. May miss long-term pattern deviations.
                                                    <br>
                                                    <strong>High (50+):</strong> Extended memory &mdash; captures long-range dependencies. More accurate pattern modelling but slower training and higher memory usage.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-2 col-form-label">LSTM Units <small class="text-muted">(default: 64)</small></label>
                                            <div class="col-sm-10">
                                                <input type="number" name="ml_python_lstm_units" class="form-control" value="<?= htmlspecialchars($settings['ml_python_lstm_units'] ?? '64') ?>" min="16" max="256">
                                                <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                    <i class="fas fa-book-open text-info mr-1"></i>
                                                    Size of the LSTM hidden state. Controls the network's capacity to learn complex patterns.
                                                    <br><br>
                                                    <strong>Low (16&ndash;32):</strong> Simple patterns, fast inference, minimal GPU/CPU load.
                                                    <br>
                                                    <strong>High (128+):</strong> Complex pattern recognition, higher accuracy potential. Requires significantly more GPU memory and longer training.
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
                                </div>
                            </div>

                            <!-- ======================== REQUIREMENTS ======================== -->
                            <div class="tab-pane fade" id="pane-requirements" role="tabpanel">
                                <div class="callout callout-info bg-light py-2 px-3 mb-3 small">
                                    <i class="fas fa-info-circle text-info mr-1"></i>
                                    System requirements vary significantly based on dataset size. Below are recommended specifications for the PHP server and Python Docker backend at different data volumes. These assume processing SMS + Calls + Contacts + Locations + Apps + Files + Device Activity simultaneously.
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
                                                        <td>scikit-learn, numpy, Flask</td>
                                                        <td>Overkill for this size. PHP-ML sufficient. Runs on any Docker host.</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>&lt; 5k entries</strong></td>
                                                        <td>1 vCPU</td>
                                                        <td>512 MB</td>
                                                        <td>Not required</td>
                                                        <td>~1.5 GB</td>
                                                        <td>+ pandas, scipy</td>
                                                        <td>Autoencoder &amp; LSTM train in seconds. No GPU needed.</td>
                                                    </tr>
                                                    <tr class="table-warning">
                                                        <td><strong>&lt; 10k entries</strong></td>
                                                        <td>2 vCPU</td>
                                                        <td>1 GB</td>
                                                        <td>Optional</td>
                                                        <td>~1.8 GB</td>
                                                        <td>+ TensorFlow CPU or PyTorch CPU</td>
                                                        <td>Isolation Forest, One-Class SVM complete in &lt;10s. GPU optional for faster Autoencoder training.</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>&lt; 20k entries</strong></td>
                                                        <td>2&ndash;4 vCPU</td>
                                                        <td>2&ndash;4 GB</td>
                                                        <td>Recommended (4 GB+ VRAM)</td>
                                                        <td>~2.5 GB (with CUDA)</td>
                                                        <td>+ TensorFlow GPU / PyTorch CUDA</td>
                                                        <td>GPU accelerates Autoencoder &amp; LSTM training 5&ndash;10x. 4 GB VRAM sufficient for batch sizes up to 64.</td>
                                                    </tr>
                                                    <tr class="table-danger">
                                                        <td><strong>&lt; 50k entries</strong></td>
                                                        <td>4+ vCPU</td>
                                                        <td>4&ndash;8 GB</td>
                                                        <td>Strongly recommended (8 GB+ VRAM)</td>
                                                        <td>~3.5 GB (full CUDA toolkit)</td>
                                                        <td>+ TensorFlow GPU, CUDA 11+, cuDNN</td>
                                                        <td>Recommended production target for Python backend. All models complete within 60s with GPU. Use batch prediction for API responses.</td>
                                                    </tr>
                                                    <tr class="table-danger">
                                                        <td><strong>50k+ entries</strong></td>
                                                        <td>8+ vCPU</td>
                                                        <td>16&ndash;32 GB</td>
                                                        <td>Required (16 GB+ VRAM)</td>
                                                        <td>~4.5 GB + model storage</td>
                                                        <td>+ TensorFlow GPU, CUDA 12+, cuDNN, Rapids cuML</td>
                                                        <td>Large-scale production. Use Rapids cuML for GPU-accelerated DBSCAN &amp; LOF. Implement async job queue (Celery/Redis) with model caching. LSTM training may take several minutes.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card-footer small text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Python backend runs as a separate Docker container, communicating via HTTP REST. It does NOT share PHP server resources. GPU acceleration requires <code>nvidia-docker</code> runtime and compatible NVIDIA drivers on the host.
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
  ml-backend:
    image: eavesdroid/ml-backend:latest
    build: ./ml-backend
    ports:
      - "5000:5000"
    environment:
      - MODEL_DIR=/models
      - LOG_LEVEL=info
      - CUDA_VISIBLE_DEVICES=0  # GPU device ID, remove for CPU-only
    volumes:
      - ./models:/models
      - ./data:/data
    deploy:
      resources:
        limits:
          cpus: '4'
          memory: 8G
        reservations:
          cpus: '2'
          memory: 4G
    runtime: nvidia  # Remove for CPU-only deployments
    networks:
      - app-network
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:5000/health"]
      interval: 30s
      timeout: 10s
      retries: 3

networks:
  app-network:
    driver: bridge</code>
                                        </pre>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <div class="small">
                                                    <strong class="text-success"><i class="fas fa-check-circle mr-1"></i>CPU-only deployment:</strong><br>
                                                    Remove <code>runtime: nvidia</code>, remove <code>CUDA_VISIBLE_DEVICES</code>, reduce memory limits. Works on any Docker host.
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small">
                                                    <strong class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i>GPU deployment prerequisites:</strong><br>
                                                    NVIDIA drivers &ge; 525, Docker 19.03+, <code>nvidia-docker2</code> package, NVIDIA container toolkit.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Settings</button>
                    </div>
                </div>
            </form>
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
let lastTestResult = null;

function getConnectionUrl() {
    const urlInput = document.getElementById('python_connection_url');
    return urlInput ? urlInput.value.trim() : '';
}

function testPythonConnection() {
    const modal = $('#pythonTestModal');
    const body = $('#pythonTestBody');
    const setBtn = document.getElementById('setFromTestBtn');
    setBtn.style.display = 'none';
    lastTestedUrl = getConnectionUrl();

    body.html('<div class="text-center py-5"><i class="fas fa-spinner fa-pulse fa-3x text-muted"></i><p class="mt-2 text-muted">Connecting to <code>' + escHtml(lastTestedUrl) + '</code>...</p></div>');
    modal.modal('show');

    $.post('<?= base_url('admin/ml/test-python') ?>', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        'url': lastTestedUrl
    }, function(data) {
        lastTestResult = data;
        let html = '';

        if (data.success) {
            html += '<div class="alert alert-success">';
            html += '    <h5><i class="fas fa-check-circle mr-1"></i> Python Backend is running</h5>';
            html += '    <p class="mb-0 small">Status: <strong>' + escHtml(data.status || 'healthy') + '</strong>';
            if (data.version) html += ' | Version: <strong>' + escHtml(data.version) + '</strong>';
            if (data.uptime) html += ' | Uptime: <strong>' + Math.round(data.uptime) + 's</strong>';
            html += '</p></div>';
        } else {
            html += '<div class="alert alert-danger">';
            html += '    <h5><i class="fas fa-times-circle mr-1"></i> Connection Failed</h5>';
            html += '    <p class="mb-0 small">' + escHtml(data.message) + '</p>';
            html += '</div>';
        }

        // Backend info
        html += '<div class="card card-outline card-secondary shadow-sm mt-3"><div class="card-header"><h6 class="card-title"><i class="fas fa-cogs mr-1"></i> Backend Status</h6></div><div class="card-body p-0"><table class="table table-sm table-bordered mb-0">';
        html += '<tr><th>URL Tested</th><td><code>' + escHtml(data.tested_url || lastTestedUrl) + '</code></td></tr>';
        if (data.success) {
            html += '<tr><th>Database</th><td>' + (data.database === 'connected' ? '<span class="text-success"><i class="fas fa-check-circle mr-1"></i> Connected</span>' : '<span class="text-danger"><i class="fas fa-times-circle mr-1"></i> ' + escHtml(data.database) + '</span>') + '</td></tr>';
            html += '<tr><th>CUDA</th><td>' + (data.cuda ? '<span class="text-success"><i class="fas fa-microchip mr-1"></i> ' + escHtml(data.cuda_device || 'Available') + '</span>' : '<span class="text-muted"><i class="fas fa-times mr-1"></i> Not available</span>') + '</td></tr>';
            html += '<tr><th>Cache Entries</th><td>' + (data.cache || 0) + '</td></tr>';
            if (data.memory) {
                html += '<tr><th>Memory</th><td>' + Math.round(data.memory.used) + ' MB / ' + Math.round(data.memory.total) + ' MB</td></tr>';
            }
            if (data.models && data.models.length > 0) {
                html += '<tr><th>Models Loaded</th><td><code>' + data.models.join(', ') + '</code></td></tr>';
            }
        }
        html += '</table></div></div>';

        // Module statuses
        if (data.success && data.modules && data.modules.length > 0) {
            html += '<div class="card card-outline card-info shadow-sm mt-3"><div class="card-header"><h6 class="card-title"><i class="fas fa-puzzle-piece mr-1"></i> Module Health</h6></div><div class="card-body p-0"><table class="table table-sm table-bordered mb-0"><thead class="thead-light"><tr><th>Module</th><th>Status</th><th>Message</th></tr></thead><tbody>';
            data.modules.forEach(function(m) {
                const statusIcon = m.status === 'ok' ? '<span class="text-success"><i class="fas fa-check-circle"></i></span>' :
                    (m.status === 'warn' ? '<span class="text-warning"><i class="fas fa-exclamation-triangle"></i></span>' :
                    '<span class="text-danger"><i class="fas fa-times-circle"></i></span>');
                html += '<tr><td>' + escHtml(m.name) + '</td><td>' + statusIcon + ' ' + escHtml(m.status) + '</td><td class="small">' + escHtml(m.message) + '</td></tr>';
            });
            html += '</tbody></table></div></div>';
        }

        if (data.success) {
            setBtn.style.display = 'inline-block';
        }

        body.html(html);
    }).fail(function(xhr) {
        body.html('<div class="alert alert-danger"><h5><i class="fas fa-exclamation-triangle mr-1"></i> Request Failed</h5><p class="mb-0 small">HTTP ' + xhr.status + ': ' + xhr.statusText + '</p></div>');
    });
}

function setPythonConnection() {
    const url = getConnectionUrl();
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
                'url': url
            }, function(saveData) {
                if (saveData.success) {
                    showConnectionResult('success', '<i class="fas fa-check-circle mr-1"></i> ' + saveData.message);
                    updateActiveConnection(url, true);
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

    $.post('<?= base_url('admin/ml/set-connection') ?>', {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        'url': lastTestedUrl
    }, function(data) {
        if (data.success) {
            const body = $('#pythonTestBody');
            body.append('<div class="alert alert-success mt-3"><i class="fas fa-check-circle mr-1"></i> Connection saved! You can now close this dialog.</div>');
            document.getElementById('setFromTestBtn').style.display = 'none';
            updateActiveConnection(lastTestedUrl, true);
            $('#python_connection_url').val(lastTestedUrl);
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
</script>
