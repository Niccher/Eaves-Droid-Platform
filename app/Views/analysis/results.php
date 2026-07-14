<!-- Step 3 – Anomaly Detection Wizard: Results -->
<div class="content-wrapper">

    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
                        Detection Results
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">
                        <li class="breadcrumb-item">
                            <a href="<?= base_url('home') ?>">
                                <i class="fas fa-home mr-1"></i>Home
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?= base_url('analysis') ?>">
                                <i class="fas fa-brain mr-1"></i>Intelligence
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?= base_url('analysis/anomalies') ?>">
                                <i class="fas fa-bug mr-1"></i>Anomaly Detection
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?= base_url('analysis/anomalies/algorithms?engine=' . esc($selected_engine)) ?>">
                                <i class="fas fa-sliders-h mr-1"></i>Algorithms
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            <i class="fas fa-table mr-1"></i>Results
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- ── Wizard Progress Bar (all steps complete) ── -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-outline card-success shadow-sm mb-0">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <!-- Step 1 (done) -->
                                <a href="<?= base_url('analysis/anomalies') ?>"
                                   class="d-flex align-items-center flex-column text-decoration-none"
                                   style="min-width:90px;" title="Change engine">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm"
                                         style="width:42px;height:42px;font-weight:700;border:2px solid #fff;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <small class="mt-1 text-success font-weight-bold">Engine</small>
                                </a>
                                <div class="flex-grow-1 mx-3" style="height:3px;background:#28a745;"></div>
                                <!-- Step 2 (done) -->
                                <a href="<?= base_url('analysis/anomalies/algorithms?engine=' . esc($selected_engine)) ?>"
                                   class="d-flex align-items-center flex-column text-decoration-none"
                                   style="min-width:90px;" title="Change algorithms">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm"
                                         style="width:42px;height:42px;font-weight:700;border:2px solid #fff;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <small class="mt-1 text-success font-weight-bold">Algorithms</small>
                                </a>
                                <div class="flex-grow-1 mx-3" style="height:3px;background:#28a745;"></div>
                                <!-- Step 3 (active) -->
                                <div class="d-flex align-items-center flex-column" style="min-width:90px;">
                                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center shadow-sm"
                                         style="width:42px;height:42px;font-weight:700;border:2px solid #fff;">
                                        <i class="fas fa-table"></i>
                                    </div>
                                    <small class="mt-1 text-danger font-weight-bold">Results</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Engine + Active Algorithms Banner ── -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card card-outline card-<?= esc($engine_meta['badge']) ?> shadow-sm mb-0">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">

                                <!-- Engine badge -->
                                <span class="badge badge-<?= esc($engine_meta['badge']) ?> px-3 py-2"
                                      style="font-size:.85rem;">
                                    <i class="<?= esc($engine_meta['icon']) ?> mr-1"></i>
                                    <?= esc($engine_meta['label']) ?>
                                </span>

                                <span class="text-muted small font-weight-bold">Active algorithms:</span>

                                <?php
                                // Build a flat list of all category → algorithm name pairs
                                $algCatalogue = [
                                    'sms'         => ['sms_freq' => 'Frequency Spike', 'sms_time' => 'Time-Pattern', 'sms_cluster' => 'Sender K-Means', 'sms_bert' => 'BERT Phishing'],
                                    'contacts'    => ['contacts_freq' => 'Contact Frequency', 'contacts_dup' => 'Duplicate Detector', 'contacts_graph' => 'Graph GCN'],
                                    'call_logs'   => ['calls_burst' => 'Short-Call Burst', 'calls_night' => 'Night Monitor', 'calls_isolation' => 'Isolation Forest'],
                                    'locations'   => ['loc_geofence' => 'Geo-Fence', 'loc_speed' => 'Speed Anomaly', 'loc_dbscan' => 'DBSCAN Cluster'],
                                    'apps'        => ['apps_rep' => 'Pkg Reputation', 'apps_perm' => 'Permission Detector', 'apps_autoencoder' => 'Autoencoder'],
                                    'files'       => ['files_spike' => 'File Spike', 'files_ext' => 'Extension Mismatch', 'files_entropy' => 'Entropy Scanner'],
                                    'activity'    => ['act_screen' => 'Screen-Time', 'act_switch' => 'App-Switch Rate', 'act_lstm' => 'LSTM Sequence'],
                                    'device_info' => ['dev_hw' => 'HW Change', 'dev_net' => 'Network Profile', 'dev_oneclass' => 'One-Class SVM'],
                                ];
                                $badgeColors = ['sms'=>'danger','contacts'=>'success','call_logs'=>'warning','locations'=>'primary','apps'=>'info','files'=>'secondary','activity'=>'danger','device_info'=>'dark'];
                                $hasActive = false;
                                foreach (($selected_algs ?? []) as $cat => $algIds):
                                    foreach ((array)$algIds as $algId):
                                        $algName = $algCatalogue[$cat][$algId] ?? $algId;
                                        $color   = $badgeColors[$cat] ?? 'secondary';
                                        $hasActive = true;
                                ?>
                                <span class="badge badge-<?= $color ?>" style="font-size:.78rem; padding:.35em .65em;">
                                    <?= esc($algName) ?>
                                </span>
                                <?php endforeach; endforeach; ?>

                                <?php if (!$hasActive): ?>
                                <span class="text-muted small"><em>All defaults</em></span>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Summary info-boxes ── -->
            <div class="row mb-3">
                <div class="col-md-3 col-sm-6">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-secondary elevation-1">
                            <i class="fas fa-list-ul"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Findings</span>
                            <span class="info-box-number"><?= $severity_counts['total'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-danger elevation-1">
                            <i class="fas fa-bolt"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">High Severity</span>
                            <span class="info-box-number"><?= $severity_counts['high'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-warning elevation-1">
                            <i class="fas fa-exclamation"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Medium Severity</span>
                            <span class="info-box-number"><?= $severity_counts['medium'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-info elevation-1">
                            <i class="fas fa-info"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Low Severity</span>
                            <span class="info-box-number"><?= $severity_counts['low'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Disclaimer callout ── -->
            <div class="callout callout-info bg-light shadow-sm mb-3">
                <h5>
                    <i class="fas fa-<?= $selected_engine === 'python' ? 'python fab' : 'php fab' ?> mr-2 text-<?= esc($engine_meta['badge']) ?>"></i>
                    <?= esc($engine_meta['label']) ?> — Live Detection Results
                </h5>
                <p class="mb-0 text-muted">
                    <?php if ($selected_engine === 'python'): ?>
                        The Python engine (Docker microservice) is not yet connected. Results shown are simulated
                        using the PHP fallback pipeline with the same algorithm selection. Deploy the Python container
                        to activate deep-learning models (LSTM, Autoencoders, DBSCAN).
                    <?php else: ?>
                        Results are computed by the <strong>PHP-ML pipeline</strong> using statistical
                        algorithms (Z-Score, Haversine distance, pattern matching) against data fetched
                        from the database. When no live data is available, representative demo findings
                        are shown per algorithm. Use the reset action below to reconfigure.
                    <?php endif; ?>
                </p>
            </div>

            <!-- ── Results Table Card ── -->
            <div class="card card-danger card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-2 text-danger"></i>
                        Detected Anomalies
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-danger mr-1"><?= count($results) ?> findings</span>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0" id="tbl-results">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width:40px">#</th>
                                    <th>Category</th>
                                    <th>Detected Anomaly</th>
                                    <th style="width:110px">Severity</th>
                                    <th>Algorithm</th>
                                    <th>Engine Notes</th>
                                    <th style="width:150px">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $i => $row):
                                    $sev = $severity_map[$row['severity']] ?? ['badge' => 'secondary', 'icon' => 'fas fa-circle'];
                                ?>
                                <tr>
                                    <td class="text-muted small align-middle"><?= $i + 1 ?></td>
                                    <td class="align-middle font-weight-bold text-dark" style="white-space:nowrap;">
                                        <i class="<?= esc($row['icon']) ?> mr-1 text-secondary"></i>
                                        <?= esc($row['category']) ?>
                                    </td>
                                    <td class="align-middle small"><?= esc($row['anomaly']) ?></td>
                                    <td class="align-middle" style="white-space:nowrap;">
                                        <span class="badge badge-<?= $sev['badge'] ?>">
                                            <i class="<?= $sev['icon'] ?> mr-1"></i>
                                            <?= esc($row['severity']) ?>
                                        </span>
                                    </td>
                                    <td class="align-middle small text-muted">
                                        <?= esc($row['algorithm']) ?>
                                    </td>
                                    <td class="align-middle" style="font-size:.75rem; color:#555;">
                                        <?php if (!empty($row['engine_note'])): ?>
                                            <span class="badge badge-light border text-muted" style="white-space:normal; text-align:left; display:inline-block; max-width:220px; line-height:1.3;">
                                                <i class="fas fa-microscope mr-1 text-<?= esc($engine_meta['badge']) ?>"></i>
                                                <?= esc($row['engine_note']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle small" style="white-space:nowrap;">
                                        <i class="far fa-clock mr-1 text-muted"></i><?= esc($row['timestamp']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div><!-- /.card-body -->
            </div><!-- /.card -->

            <!-- ── Action Buttons ── -->
            <div class="row mt-3 mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">

                    <!-- Reset Anomaly Detections -->
                    <button type="button" class="btn btn-outline-danger font-weight-bold" id="btn-reset">
                        <i class="fas fa-trash-restore mr-1"></i> Reset Anomaly Detections
                    </button>

                    <div class="d-flex flex-wrap" style="gap:.5rem;">

                        <!-- Change Engine -->
                        <a href="<?= base_url('analysis/anomalies') ?>"
                           class="btn btn-outline-secondary font-weight-bold">
                            <i class="fas fa-cogs mr-1"></i> Change Engine
                        </a>

                        <!-- Change Algorithms -->
                        <a href="<?= base_url('analysis/anomalies/algorithms?engine=' . esc($selected_engine)) ?>"
                           class="btn btn-outline-primary font-weight-bold">
                            <i class="fas fa-sliders-h mr-1"></i> Change Algorithms
                        </a>

                        <!-- Re-run Detection -->
                        <button type="button" class="btn btn-warning font-weight-bold shadow-sm" id="btn-rerun">
                            <i class="fas fa-redo mr-1"></i> Re-run Detection
                        </button>

                    </div>
                </div>
            </div>

        </div>
    </section>

</div><!-- /.content-wrapper -->

<!-- SweetAlert2 dialog scripts -->
<script>
document.getElementById('btn-reset').addEventListener('click', function () {
    Swal.fire({
        title: 'Reset Anomaly Settings?',
        text: 'This will clear your selected Engine and Algorithms and return you to Step 1.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash-restore mr-1"></i> Yes, Reset',
        cancelButtonText: 'Cancel'
    }).then(function (result) {
        if (result.isConfirmed) {
            window.location.href = '<?= base_url('analysis/anomalies/results?reset=true') ?>';
        }
    });
});

document.getElementById('btn-rerun').addEventListener('click', function () {
    Swal.fire({
        title: 'Re-run Detection?',
        text: 'This will re-execute the detection pipeline using the same engine and algorithm settings.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-redo mr-1"></i> Yes, Re-run',
        cancelButtonText: 'Cancel'
    }).then(function (result) {
        if (result.isConfirmed) {
            window.location.href = '<?= base_url('analysis/anomalies/results') ?>';
        }
    });
});
</script>
