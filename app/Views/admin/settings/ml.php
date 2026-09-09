<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>ML / AI Configuration</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/settings') ?>">Settings</a></li>
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
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">ML / AI Settings</h5>
                        <p class="mb-0 small text-muted">Python backend connection settings, model endpoint URLs, timeout thresholds, and health-check configuration for the anomaly detection engine.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card card-outline card-primary shadow-sm mb-3">
                        <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-sliders-h mr-1 text-primary"></i> Settings Hub</h3></div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item"><a href="<?= base_url('admin/settings') ?>" class="nav-link"><i class="fas fa-cog mr-2 text-primary"></i> General</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/api') ?>" class="nav-link"><i class="fas fa-plug mr-2 text-info"></i> API</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/security') ?>" class="nav-link"><i class="fas fa-shield-alt mr-2 text-danger"></i> Security</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/notifications') ?>" class="nav-link"><i class="fas fa-envelope mr-2 text-primary"></i> Emails & SMTP</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/fcm') ?>" class="nav-link"><i class="fas fa-fire mr-2 text-warning"></i> Firebase (FCM)</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/maintenance') ?>" class="nav-link"><i class="fas fa-tools mr-2 text-secondary"></i> Maintenance</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage') ?>" class="nav-link"><i class="fas fa-chart-pie mr-2 text-success"></i> Storage Monitor</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/storage-cleanup') ?>" class="nav-link"><i class="fas fa-broom mr-2 text-warning"></i> Storage Cleanup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/email-triggers') ?>" class="nav-link"><i class="fas fa-bell mr-2 text-info"></i> Email Triggers</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/backup') ?>" class="nav-link"><i class="fas fa-database mr-2 text-info"></i> Backup</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/cron') ?>" class="nav-link"><i class="fas fa-clock mr-2 text-purple"></i> Cron Jobs</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <form action="<?= base_url('admin/settings/update') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="section" value="ml">

                        <div class="card">
                            <div class="card-header"><h3 class="card-title">General ML Settings</h3></div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Enable ML Features</label>
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="ml_enabled" value="0">
                                        <input type="checkbox" name="ml_enabled" class="custom-control-input" id="ml_enabled" value="1" <?= ($settings['ml_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="ml_enabled">Enable machine learning analysis</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Anomaly Detection</label>
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="ml_anomaly_enabled" value="0">
                                        <input type="checkbox" name="ml_anomaly_enabled" class="custom-control-input" id="ml_anomaly_enabled" value="1" <?= ($settings['ml_anomaly_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="ml_anomaly_enabled">Enable anomaly detection on uploaded data</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Analysis Schedule</label>
                                    <select name="ml_schedule_interval" class="form-control">
                                        <option value="hourly" <?= ($settings['ml_schedule_interval'] ?? 'daily') === 'hourly' ? 'selected' : '' ?>>Hourly</option>
                                        <option value="daily" <?= ($settings['ml_schedule_interval'] ?? 'daily') === 'daily' ? 'selected' : '' ?>>Daily</option>
                                        <option value="weekly" <?= ($settings['ml_schedule_interval'] ?? 'daily') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><h3 class="card-title">PHP-ML Settings</h3></div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>K-Means Clusters (K)</label>
                                    <input type="number" name="ml_phpml_kmeans_k" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_kmeans_k'] ?? '3') ?>" min="2" max="20">
                                    <small class="text-muted">Number of clusters for K-Means clustering algorithm</small>
                                </div>
                                <div class="form-group">
                                    <label>DBSCAN Epsilon</label>
                                    <input type="number" step="0.001" name="ml_phpml_dbscan_epsilon" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_dbscan_epsilon'] ?? '0.01') ?>" min="0.001" max="1">
                                    <small class="text-muted">Maximum distance between two samples for DBSCAN</small>
                                </div>
                                <div class="form-group">
                                    <label>DBSCAN Min Samples</label>
                                    <input type="number" name="ml_phpml_dbscan_minpoints" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_dbscan_minpoints'] ?? '2') ?>" min="1" max="50">
                                    <small class="text-muted">Minimum number of samples in a neighborhood for DBSCAN</small>
                                </div>
                                <div class="form-group">
                                    <label>Isolation Forest Trees</label>
                                    <input type="number" name="ml_phpml_isolationforest_trees" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_isolationforest_trees'] ?? '100') ?>" min="10" max="1000">
                                    <small class="text-muted">Number of trees in the Isolation Forest ensemble</small>
                                </div>
                                <div class="form-group">
                                    <label>Isolation Forest Samples</label>
                                    <input type="number" name="ml_phpml_isolationforest_samples" class="form-control" value="<?= htmlspecialchars($settings['ml_phpml_isolationforest_samples'] ?? '256') ?>" min="32" max="4096">
                                    <small class="text-muted">Number of samples for each Isolation Forest tree</small>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Python Backend (External)</h3></div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Enable Python Backend</label>
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="ml_python_enabled" value="0">
                                        <input type="checkbox" name="ml_python_enabled" class="custom-control-input" id="ml_python_enabled" value="1" <?= ($settings['ml_python_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="ml_python_enabled">Use external Python ML backend</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Connection URL</label>
                                    <div class="input-group">
                                        <input type="text" name="ml_python_url" class="form-control" value="<?= htmlspecialchars($settings['ml_python_url'] ?? 'http://ml-eaves-droid:9070') ?>" placeholder="http://ml-eaves-droid:9070">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light text-muted small" title="Full URL to the Python ML backend">
                                                <i class="fas fa-plug"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <small class="text-muted">Full URL of the Python ML backend (e.g., <code>http://ml-eaves-droid:9070</code>)</small>
                                </div>
                                <input type="hidden" name="ml_python_host" value="">
                                <input type="hidden" name="ml_python_port" value="">
                                <input type="hidden" name="ml_python_endpoint" value="">
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Settings</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
