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

            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Sections</h3></div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item"><a href="<?= base_url('admin/settings') ?>" class="nav-link"><i class="fas fa-cog mr-1"></i> General</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/api') ?>" class="nav-link"><i class="fas fa-plug mr-1"></i> API</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/security') ?>" class="nav-link"><i class="fas fa-shield-alt mr-1"></i> Security</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/notifications') ?>" class="nav-link"><i class="fas fa-bell mr-1"></i> Notifications</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/ml') ?>" class="nav-link active"><i class="fas fa-brain mr-1"></i> ML/AI</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/maintenance') ?>" class="nav-link"><i class="fas fa-tools mr-1"></i> Maintenance</a></li>
                                <li class="nav-item"><a href="<?= base_url('admin/settings/backup') ?>" class="nav-link"><i class="fas fa-hdd mr-1"></i> Backup</a></li>
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
                                    <label>Host</label>
                                    <input type="text" name="ml_python_host" class="form-control" value="<?= htmlspecialchars($settings['ml_python_host'] ?? 'localhost') ?>" placeholder="localhost">
                                </div>
                                <div class="form-group">
                                    <label>Port</label>
                                    <input type="number" name="ml_python_port" class="form-control" value="<?= htmlspecialchars($settings['ml_python_port'] ?? '5000') ?>" min="1" max="65535">
                                </div>
                                <div class="form-group">
                                    <label>API Endpoint</label>
                                    <input type="text" name="ml_python_endpoint" class="form-control" value="<?= htmlspecialchars($settings['ml_python_endpoint'] ?? '/api/analyze') ?>" placeholder="/api/analyze">
                                </div>
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
