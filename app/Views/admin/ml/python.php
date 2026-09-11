                            <div class="tab-pane active show" id="pane-python" role="tabpanel">
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
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-plug mr-1"></i> Microservice Connection Settings</h3></div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Microservice Status</label>
                                            <div class="col-sm-10">
                                                <div class="custom-control custom-switch">
                                                    <input type="hidden" name="ml_python_enabled" value="0">
                                                    <input type="checkbox" name="ml_python_enabled" class="custom-control-input" id="ml_python_enabled" value="1" <?= ($settings['ml_python_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                                    <label class="custom-control-label" for="ml_python_enabled">Enable Python ML Microservice (Fallback to PHP-ML if unreachable)</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Connection URL</label>
                                            <div class="col-sm-10">
                                                <input type="text" name="ml_python_url" id="python_connection_url" class="form-control" value="<?= htmlspecialchars($settings['ml_python_url'] ?? $python_settings['url'] ?? '') ?>" placeholder="http://ml-eaves-droid:9070">
                                                <small class="text-muted">Direct URL to the Python service. Overrides host/port below when set. Default internal Docker: <code>http://ml-eaves-droid:9070</code></small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Internal Security Token</label>
                                            <div class="col-sm-10">
                                                <div class="input-group">
                                                    <input type="password" name="ml_python_token" id="python_internal_token" class="form-control" value="<?= htmlspecialchars($settings['ml_python_token'] ?? $python_settings['token'] ?? '') ?>" placeholder="Enter shared ML internal secret token">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleTokenVisibility()" title="Toggle visibility">
                                                            <i class="fas fa-eye" id="tokenToggleIcon"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Pre-shared secret header (<code>X-Internal-Token</code>) required for inter-container communication.</small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Service Host</label>
                                            <div class="col-sm-4">
                                                <input type="text" name="ml_python_host" class="form-control" value="<?= htmlspecialchars($settings['ml_python_host'] ?? $python_settings['host'] ?? 'ml-eaves-droid') ?>">
                                                <small class="text-muted">Container name or hostname (e.g. <code>ml-eaves-droid</code>, <code>localhost</code>)</small>
                                            </div>
                                            <label class="col-sm-2 col-form-label">Service Port</label>
                                            <div class="col-sm-4">
                                                <input type="number" name="ml_python_port" class="form-control" value="<?= htmlspecialchars($settings['ml_python_port'] ?? $python_settings['port'] ?? '9070') ?>" min="1" max="65535">
                                                <small class="text-muted">Default: <code>9070</code> (Docker internal), <code>9071</code> (external)</small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Analysis Endpoint</label>
                                            <div class="col-sm-10">
                                                <input type="text" name="ml_python_endpoint" class="form-control" value="<?= htmlspecialchars($settings['ml_python_endpoint'] ?? $python_settings['endpoint'] ?? '/api/v1/analysis-jobs') ?>">
                                                <small class="text-muted">REST endpoint for analysis dispatch</small>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-2 col-form-label">Test Connection</label>
                                            <div class="col-sm-10">
                                                <button type="button" class="btn btn-info" onclick="testPythonConnection()"><i class="fas fa-plug mr-1"></i> Test Microservice Connection</button>
                                                <button type="button" class="btn btn-secondary ml-2" onclick="setPythonConnection()"><i class="fas fa-check mr-1"></i> Set as Active Backend</button>
                                                <div id="connectionTestResult" class="mt-2" style="display:none;"></div>
                                                <div id="activeConnectionInfo" class="mt-2 small text-muted">
                                                    <?php if (!empty($settings['ml_python_url'])): ?>
                                                    <i class="fas fa-info-circle text-info mr-1"></i>Active configured URL: <code><?= htmlspecialchars($settings['ml_python_url']) ?></code>
                                                    <?php else: ?>
                                                    <i class="fas fa-info-circle text-info mr-1"></i>Active backend: <code>http://<?= htmlspecialchars($settings['ml_python_host'] ?? 'ml-eaves-droid') ?>:<?= htmlspecialchars($settings['ml_python_port'] ?? '9070') ?></code>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Python Algorithm Parameters -->
                                <div class="card card-outline card-warning shadow-sm mb-3">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-brain mr-1"></i> Python Detector Parameters</h3></div>
                                    <div class="card-body">
                                        <!-- Isolation Forest (Py) -->
                                        <h5 class="text-warning font-weight-bold"><i class="fas fa-tree mr-1"></i> Isolation Forest (Python / scikit-learn)</h5>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Estimators / Trees <small class="text-muted">(default: 100)</small></label>
                                            <div class="col-sm-4">
                                                <input type="number" name="ml_py_iforest_n_estimators" class="form-control" value="<?= htmlspecialchars($settings['ml_py_iforest_n_estimators'] ?? '100') ?>" min="10" max="500">
                                            </div>
                                            <label class="col-sm-2 col-form-label">Contamination <small class="text-muted">(default: 0.05)</small></label>
                                            <div class="col-sm-4">
                                                <input type="number" step="0.01" name="ml_py_iforest_contamination" class="form-control" value="<?= htmlspecialchars($settings['ml_py_iforest_contamination'] ?? '0.05') ?>" min="0.01" max="0.5">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Max Samples <small class="text-muted">(default: auto)</small></label>
                                            <div class="col-sm-4">
                                                <input type="text" name="ml_py_iforest_max_samples" class="form-control" value="<?= htmlspecialchars($settings['ml_py_iforest_max_samples'] ?? 'auto') ?>" placeholder="auto or integer">
                                            </div>
                                            <label class="col-sm-2 col-form-label">Random State <small class="text-muted">(default: 42)</small></label>
                                            <div class="col-sm-4">
                                                <input type="number" name="ml_py_iforest_random_state" class="form-control" value="<?= htmlspecialchars($settings['ml_py_iforest_random_state'] ?? '42') ?>">
                                            </div>
                                        </div>
                                        <div class="callout callout-info bg-light py-2 px-3 mb-3 small">
                                            <i class="fas fa-book-open text-info mr-1"></i>
                                            <strong>How it works:</strong> Python implementation of Isolation Forest via <code>sklearn.ensemble.IsolationForest</code>. Faster and more memory-efficient than PHP-ML for datasets &gt;10k rows. Supports parallel processing via <code>n_jobs=-1</code>.
                                        </div>
                                        <hr>

                                        <!-- One-Class SVM -->
                                        <h5 class="text-warning font-weight-bold"><i class="fas fa-vector-square mr-1"></i> One-Class SVM</h5>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Kernel <small class="text-muted">(default: rbf)</small></label>
                                            <div class="col-sm-4">
                                                <select name="ml_py_ocsvm_kernel" class="form-control">
                                                    <?php $k = $settings['ml_py_ocsvm_kernel'] ?? 'rbf'; ?>
                                                    <option value="rbf" <?= $k === 'rbf' ? 'selected' : '' ?>>RBF (Radial Basis Function)</option>
                                                    <option value="linear" <?= $k === 'linear' ? 'selected' : '' ?>>Linear</option>
                                                    <option value="poly" <?= $k === 'poly' ? 'selected' : '' ?>>Polynomial</option>
                                                    <option value="sigmoid" <?= $k === 'sigmoid' ? 'selected' : '' ?>>Sigmoid</option>
                                                </select>
                                            </div>
                                            <label class="col-sm-2 col-form-label">Nu (&nu;) <small class="text-muted">(default: 0.05)</small></label>
                                            <div class="col-sm-4">
                                                <input type="number" step="0.01" name="ml_py_ocsvm_nu" class="form-control" value="<?= htmlspecialchars($settings['ml_py_ocsvm_nu'] ?? '0.05') ?>" min="0.01" max="0.5">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Gamma (&gamma;) <small class="text-muted">(default: scale)</small></label>
                                            <div class="col-sm-4">
                                                <input type="text" name="ml_py_ocsvm_gamma" class="form-control" value="<?= htmlspecialchars($settings['ml_py_ocsvm_gamma'] ?? 'scale') ?>" placeholder="scale, auto, or float">
                                            </div>
                                        </div>
                                        <div class="callout callout-info bg-light py-2 px-3 mb-3 small">
                                            <i class="fas fa-book-open text-info mr-1"></i>
                                            <strong>How it works:</strong> Fits a hyperplane around the "normal" data region in a high-dimensional feature space. Points outside the boundary are anomalies. Best for detecting multi-modal normal behaviour (e.g., user is active morning AND evening, but inactive mid-day).
                                            <br><br>
                                            <strong>Low &nu; (0.01&ndash;0.05):</strong> Tight boundary &mdash; very few false positives, flags only extreme deviations.
                                            <br>
                                            <strong>High &nu; (0.1&ndash;0.3):</strong> Loose boundary &mdash; higher sensitivity, flags more potential anomalies.
                                        </div>
                                        <hr>

                                        <!-- Local Outlier Factor (LOF) -->
                                        <h5 class="text-warning font-weight-bold"><i class="fas fa-braille mr-1"></i> Local Outlier Factor (LOF)</h5>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Number of Neighbours <small class="text-muted">(default: 20)</small></label>
                                            <div class="col-sm-4">
                                                <input type="number" name="ml_py_lof_n_neighbors" class="form-control" value="<?= htmlspecialchars($settings['ml_py_lof_n_neighbors'] ?? '20') ?>" min="2" max="100">
                                            </div>
                                            <label class="col-sm-2 col-form-label">Contamination <small class="text-muted">(default: 0.05)</small></label>
                                            <div class="col-sm-4">
                                                <input type="number" step="0.01" name="ml_py_lof_contamination" class="form-control" value="<?= htmlspecialchars($settings['ml_py_lof_contamination'] ?? '0.05') ?>" min="0.01" max="0.5">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Metric <small class="text-muted">(default: minkowski)</small></label>
                                            <div class="col-sm-4">
                                                <select name="ml_py_lof_metric" class="form-control">
                                                    <?php $m = $settings['ml_py_lof_metric'] ?? 'minkowski'; ?>
                                                    <option value="minkowski" <?= $m === 'minkowski' ? 'selected' : '' ?>>Minkowski (Euclidean)</option>
                                                    <option value="manhattan" <?= $m === 'manhattan' ? 'selected' : '' ?>>Manhattan</option>
                                                    <option value="cosine" <?= $m === 'cosine' ? 'selected' : '' ?>>Cosine</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="callout callout-info bg-light py-2 px-3 mb-3 small">
                                            <i class="fas fa-book-open text-info mr-1"></i>
                                            <strong>How it works:</strong> Compares the local density of a point to the local densities of its neighbours. A point that has a significantly lower density than its neighbours is considered an outlier. Highly effective for non-uniform density distributions (e.g. dense urban clusters vs sparse rural locations).
                                        </div>
                                        <hr>

                                        <!-- Autoencoder (Deep Learning) -->
                                        <h5 class="text-warning font-weight-bold"><i class="fas fa-network-wired mr-1"></i> Deep-Learning Autoencoder</h5>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Latent Dimension <small class="text-muted">(default: 4)</small></label>
                                            <div class="col-sm-4">
                                                <input type="number" name="ml_py_autoencoder_latent_dim" class="form-control" value="<?= htmlspecialchars($settings['ml_py_autoencoder_latent_dim'] ?? '4') ?>" min="2" max="32">
                                            </div>
                                            <label class="col-sm-2 col-form-label">Epochs <small class="text-muted">(default: 50)</small></label>
                                            <div class="col-sm-4">
                                                <input type="number" name="ml_py_autoencoder_epochs" class="form-control" value="<?= htmlspecialchars($settings['ml_py_autoencoder_epochs'] ?? '50') ?>" min="10" max="500">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Batch Size <small class="text-muted">(default: 32)</small></label>
                                            <div class="col-sm-4">
                                                <input type="number" name="ml_py_autoencoder_batch_size" class="form-control" value="<?= htmlspecialchars($settings['ml_py_autoencoder_batch_size'] ?? '32') ?>" min="8" max="256">
                                            </div>
                                            <label class="col-sm-2 col-form-label">Threshold Percentile <small class="text-muted">(default: 95)</small></label>
                                            <div class="col-sm-4">
                                                <input type="number" step="0.5" name="ml_py_autoencoder_threshold" class="form-control" value="<?= htmlspecialchars($settings['ml_py_autoencoder_threshold'] ?? '95.0') ?>" min="80" max="99.9">
                                            </div>
                                        </div>
                                        <div class="callout callout-info bg-light py-2 px-3 mb-0 small">
                                            <i class="fas fa-book-open text-info mr-1"></i>
                                            <strong>How it works:</strong> Trains a neural network to compress (encode) and reconstruct (decode) normal behavioural features. When presented with anomalous data, reconstruction error is high. Points with reconstruction error above the threshold percentile are flagged as anomalies. Detects non-linear patterns that statistical methods miss.
                                        </div>
                                    </div>
                                </div>

                                <!-- Python Model Capabilities Summary -->
                                <div class="card card-outline card-secondary shadow-sm mb-3">
                                    <div class="card-header"><h3 class="card-title"><i class="fas fa-list-check mr-1"></i> Python ML Model Capabilities</h3></div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>Contact Graph Anomaly Scanner</strong>
                                                <br><small>Analyzes communication graph metrics (degree, betweenness centrality, community structure) using NetworkX. Flags unexpected new connections and isolated high-frequency links. Params: <code>min_degree_threshold</code>, <code>community_resolution</code>.</small>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Activity MLP Classifier</strong>
                                                <br><small>Multi-layer perceptron neural network predicting normal vs anomalous device usage patterns by time of day, session duration, and app category transitions. Params: <code>hidden_layer_sizes</code>, <code>activation</code> (relu/tanh).</small>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Phishing Keyword Scanner</strong>
                                                <br><small>NLP-based heuristic scoring of SMS text and contact notes against known phishing/smishing indicators and credential-harvesting patterns. Params: <code>threshold_score</code> (0.0&ndash;1.0), <code>use_levenshtein</code>.</small>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-4">
                                                <strong>Suspicious File Classifier</strong>
                                                <br><small>Flags disguised extensions, double extensions, hidden directories, and abnormal MIME type mismatches in extracted device file lists. Params: <code>check_mime_mismatch</code>, <code>entropy_threshold</code>.</small>
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
                                    </div>
                                </div>
                                </form>
                            </div>

