<!-- Step 3 – Anomaly Detection Wizard: Results -->
<div class="content-wrapper">

    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
                        Detection Results
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>"><i class="fas fa-home mr-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>">Intelligence</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis/anomalies') ?>">Anomaly Detection</a></li>
                        <li class="breadcrumb-item active">Results</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Wizard Progress Bar (all steps complete) -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-outline card-success shadow-sm mb-0">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <!-- Step 1 (done) -->
                                <div class="d-flex align-items-center flex-column" style="min-width:90px;">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm"
                                         style="width:42px;height:42px;font-weight:700; border: 2px solid #fff;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <small class="mt-1 text-success font-weight-bold">Engine</small>
                                </div>
                                <div class="flex-grow-1 mx-3" style="height:3px;background:#28a745;"></div>
                                <!-- Step 2 (done) -->
                                <div class="d-flex align-items-center flex-column" style="min-width:90px;">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm"
                                         style="width:42px;height:42px;font-weight:700; border: 2px solid #fff;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <small class="mt-1 text-success font-weight-bold">Algorithms</small>
                                </div>
                                <div class="flex-grow-1 mx-3" style="height:3px;background:#28a745;"></div>
                                <!-- Step 3 (active/done) -->
                                <div class="d-flex align-items-center flex-column" style="min-width:90px;">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm"
                                         style="width:42px;height:42px;font-weight:700; border: 2px solid #fff;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <small class="mt-1 text-success font-weight-bold">Results</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary info-boxes -->
            <div class="row mb-3">
                <div class="col-md-3 col-sm-6">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-danger elevation-1">
                            <i class="fas fa-exclamation-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Anomalies</span>
                            <span class="info-box-number"><?= $severity_counts['total'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-danger elevation-1" style="background-color: #dc3545 !important;">
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
                        <span class="info-box-icon bg-warning elevation-1" style="background-color: #ffc107 !important;">
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
                        <span class="info-box-icon bg-info elevation-1" style="background-color: #17a2b8 !important;">
                            <i class="fas fa-info"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Low Severity</span>
                            <span class="info-box-number"><?= $severity_counts['low'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disclaimer callout -->
            <div class="callout callout-warning bg-light shadow-sm">
                <h5><i class="fas fa-flask mr-2 text-warning"></i>Live Data Contextual Results</h5>
                <p class="mb-0 text-muted">
                    The findings below are dynamically computed from your active database. In this demo simulation,
                    no new write operations are initiated. Use the reset action below to modify configurations.
                </p>
            </div>

            <!-- Results Table Card -->
            <div class="card card-danger card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-2 text-danger"></i>Detected Anomalies
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-danger"><?= count($results) ?> findings</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0" id="tbl-results">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Detected Anomaly</th>
                                    <th>Severity</th>
                                    <th>Algorithm Used</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $i => $row):
                                    $sev = $severity_map[$row['severity']] ?? ['badge'=>'secondary','icon'=>'fas fa-circle'];
                                ?>
                                <tr>
                                    <td class="text-muted small align-middle"><?= $i + 1 ?></td>
                                    <td class="align-middle font-weight-bold text-dark">
                                        <i class="<?= $row['icon'] ?> mr-1 text-secondary"></i>
                                        <?= esc($row['category']) ?>
                                    </td>
                                    <td class="align-middle small"><?= esc($row['anomaly']) ?></td>
                                    <td class="align-middle">
                                        <span class="badge badge-<?= $sev['badge'] ?>">
                                            <i class="<?= $sev['icon'] ?> mr-1"></i>
                                            <?= esc($row['severity']) ?>
                                        </span>
                                    </td>
                                    <td class="align-middle small text-muted"><?= esc($row['algorithm']) ?></td>
                                    <td class="align-middle small">
                                        <i class="far fa-clock mr-1"></i><?= esc($row['timestamp']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div><!-- /.card-body -->
            </div><!-- /.card -->

            <!-- Action buttons -->
            <div class="row mt-3 mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">

                    <!-- Reset Anomaly Detections (Takes back to First Page) -->
                    <button type="button" class="btn btn-outline-danger font-weight-bold" id="btn-reset">
                        <i class="fas fa-trash-restore mr-1"></i> Reset Anomaly Detections
                    </button>

                    <div>
                        <!-- Change Engine -->
                        <a href="<?= base_url('analysis/anomalies') ?>"
                           class="btn btn-outline-secondary font-weight-bold mr-1">
                            <i class="fas fa-cogs mr-1"></i> Change Engine
                        </a>

                        <!-- Back to Algorithms -->
                        <a href="<?= base_url('analysis/anomalies/algorithms') ?>"
                           class="btn btn-outline-primary font-weight-bold mr-1">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Algorithms
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
        text: 'This will reset your selected Engine and Algorithms and return you to step 1.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash-restore mr-1"></i> Yes, Reset',
        cancelButtonText:  'Cancel'
    }).then(function (result) {
        if (result.isConfirmed) {
            window.location.href = '<?= base_url('analysis/anomalies/results?reset=true') ?>';
        }
    });
});

document.getElementById('btn-rerun').addEventListener('click', function () {
    Swal.fire({
        title: 'Re-run Detection?',
        text: 'This will restart the anomaly detection process using the same engine and algorithm settings.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-redo mr-1"></i> Yes, Re-run',
        cancelButtonText:  'Cancel'
    }).then(function (result) {
        if (result.isConfirmed) {
            window.location.href = '<?= base_url('analysis/anomalies/results') ?>';
        }
    });
});
</script>
