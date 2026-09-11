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
