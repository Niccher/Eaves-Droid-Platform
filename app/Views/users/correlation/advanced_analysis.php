<div class="content-wrapper">
    <?php if (session()->getFlashdata('success')): ?>
    <div class="container-fluid mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    </div>
    <?php endif; ?>
        <!-- Content Header -->
        <section class="content-header pt-3 pb-2">
            <div class="container-fluid">
                <div class="row align-items-center mb-2">
                    <div class="col-sm-6">
                        <h1 class="h3 mb-0 text-dark font-weight-bold">
                            <i class="fas fa-microchip text-info mr-2"></i> Cross-Stream Analysis Suite
                        </h1>
                        <p class="text-muted mb-0 small">Cross-vector correlation engine analyzing financial, spatial, social, and telemetry streams.</p>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a class="btn btn-info btn-sm shadow-sm" href="<?= base_url('analysis/refresh-ml') ?>">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh ML Models
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content mb-4">
            <?php if (isset($ml_insight_finance) && !empty($ml_insight_finance['insights'])): ?>
            <?php $_eng = (new \App\Models\AnomaliesModel())->getDefaultEngine(); $_engLabel = match($_eng){'python'=>'Python Engine','both'=>'Hybrid Engine',default=>'PHP Engine'}; ?>
            <div class="container-fluid">
                <div class="card bg-secondary text-white shadow-sm border-0" style="border-radius: 8px;">
                    <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0 d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center mb-2 mb-md-0">
                            <div class="rounded-circle p-3 mr-3 shadow" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                                <i class="fas fa-brain fa-lg text-white"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 font-weight-bold text-white"><?= $_engLabel ?> Cross-Stream Correlation &amp; Synthesis</h4>
                                <small class="text-light opacity-75">Multi-vector analysis of spending, location paths, and social connections</small>
                            </div>
                        </div>
                        <div>
                            <span class="badge badge-pill badge-info px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
                                <i class="fas fa-microchip mr-1"></i> <?= esc($ml_insight_finance['algorithm'] ?? 'PHP-ML Correlation') ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-lg-4 col-md-5 mb-3 mb-md-0 border-right-md border-secondary pr-md-4">
                                <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                                    <h6 class="text-info font-weight-bold mb-2"><i class="fas fa-database mr-2"></i> What Is Happening</h6>
                                    <p class="mb-0 text-light opacity-90" style="font-size: 0.9rem; line-height: 1.5;">
                                        <?= esc($ml_insight_finance['description'] ?? 'Correlates mobile wallet transactions, location clusters, and communication frequency to detect behavioral anomalies.') ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-8 col-md-7 pl-md-4">
                                <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-lightbulb mr-2"></i> Key Intelligence Findings</h6>
                                <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
                                    <?php foreach ($ml_insight_finance['insights'] as $insight): ?>
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                        <span><?= $insight ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </section>
            <?php endif; ?>

            <section class="content">
                <div class="container-fluid">
                    <!-- AdvancedController Features Grid -->
                <div class="row">
                    <!-- Financial Intelligence -->
                    <div class="col-md-6">
                        <div class="card card-outline card-success shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-money-bill-wave mr-2"></i>
                                    Financial Intelligence
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-success">Live</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p>Vertical analysis of financial transactions, mobile money (M-Pesa, etc.), and billing cycles.</p>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="description-block border-right">
                                            <h5 class="description-header text-danger">Ksh <?= number_format($financial_summary['totalSpending'] ?? 0, 2) ?></h5>
                                            <span class="description-text">TOTAL SPENT</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="description-block">
                                            <h5 class="description-header text-success"><?= count($financial_summary['transactions'] ?? []) ?></h5>
                                            <span class="description-text">RECENT TX</span>
                                        </div>
                                    </div>
                                </div>
                                <a href="<?= base_url('analysis/finance') ?>" class="btn btn-success btn-block">
                                    <i class="fas fa-chart-line mr-1"></i> Open Finance Dashboard
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Location Heatmap -->
                    <div class="col-md-6">
                        <div class="card card-outline card-primary shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-map-marked-alt mr-2"></i>
                                    Location Intelligence
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-primary">Heatmap</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p>Visualize frequent locations, stay durations, and movement paths on an interactive map.</p>
                                <div class="alert alert-light border">
                                    <i class="fas fa-thmr-2"></i> Heatmap & Daily Pathing Visualization.
                                </div>
                                <a href="<?= base_url('analysis/location') ?>" class="btn btn-primary btn-block">
                                    <i class="fas fa-map mr-1"></i> View Heatmap & Paths
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <!-- Social Mapping -->
                    <div class="col-md-6">
                        <div class="card card-outline card-warning shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-users mr-2"></i>
                                    Relationship Mapping
                                </h3>
                            </div>
                            <div class="card-body">
                                <p>AnalyzeController communication frequency to map out the social circle and interaction heatmaps.</p>
                                <div class="alert alert-light border">
                                    <i class="fas fa-project-diagram mr-2"></i> Top 10 Connections & New Contact Alerts.
                                </div>
                                <a href="<?= base_url('analysis/social') ?>" class="btn btn-warning btn-block">
                                    <i class="fas fa-network-wired mr-1"></i> Open Social Map
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Lifestyle Profile -->
                    <div class="col-md-6">
                        <div class="card card-outline card-secondary shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-walking mr-2"></i>
                                    Lifestyle & Mobility
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-secondary">New</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p>AnalyzeController activity patterns (walking, driving, still) and screen time habits to build a lifestyle profile.</p>
                                <div class="alert alert-light border">
                                    <i class="fas fa-history mr-2"></i> Movement Trends & Digital Balance.
                                </div>
                                <a href="<?= base_url('analysis/lifestyle') ?>" class="btn btn-secondary btn-block">
                                    <i class="fas fa-fingerprint mr-1"></i> View Lifestyle Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Privacy & Finance Intelligence -->
                <div class="row mt-4">
                    <!-- Privacy Audit -->
                    <div class="col-md-6">
                        <div class="card card-outline card-danger shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user-shield mr-2"></i>
                                    Privacy & Permission Audit
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-danger">Security</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p>Identify high-risk applications based on dangerous permission combinations and background access patterns.</p>
                                <div class="alert alert-light border">
                                    <i class="fas fa-shield-alt mr-2"></i> Risk Scoring & Sensitivity Evaluation.
                                </div>
                                <a href="<?= base_url('analysis/privacy') ?>" class="btn btn-danger btn-block">
                                    <i class="fas fa-search-plus mr-1"></i> Start Privacy Audit
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Tracker -->
                    <div class="col-md-6">
                        <div class="card card-outline card-success shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-calendar-check mr-2"></i>
                                    Subscription Tracker
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-success">Proactive</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p>Forecast monthly financial commitments by detecting recurring billing patterns in SMS history.</p>
                                <div class="alert alert-light border">
                                    <i class="fas fa-coins mr-2"></i> Bill Detection & Expense Forecasting.
                                </div>
                                <a href="<?= base_url('analysis/subscriptions') ?>" class="btn btn-success btn-block">
                                    <i class="fas fa-receipt mr-1"></i> View SubscriptionsController
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Portfolio & Storage Intelligence -->
                <div class="row mt-4">
                    <!-- App Portfolio -->
                    <div class="col-md-6">
                        <div class="card card-outline card-primary shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-th-large mr-2"></i>
                                    App Portfolio Profiling
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-primary">Logic</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p>Categorize the user's digital life (Social, Finance, Productivity) based on installed application distribution.</p>
                                <div class="alert alert-light border">
                                    <i class="fas fa-chart-pie mr-2"></i> Usage Categorization & Patterns.
                                </div>
                                <a href="<?= base_url('analysis/apps') ?>" class="btn btn-primary btn-block">
                                    <i class="fas fa-briefcase mr-1"></i> View App Portfolio
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Storage Forensics -->
                    <div class="col-md-6">
                        <div class="card card-outline card-info shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-hdd mr-2"></i>
                                    Media & Storage Forensics
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-info">FilesController</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p>AnalyzeController disk usage by source (WhatsApp vs Camera) and identify aging media content or large space hogs.</p>
                                <div class="alert alert-light border">
                                    <i class="fas fa-folder-open mr-2"></i> Storage Health & Source Auditing.
                                </div>
                                <a href="<?= base_url('analysis/storage') ?>" class="btn btn-info btn-block">
                                    <i class="fas fa-database mr-1"></i> Open Storage Forensics
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Psychographic & Geospatial Intelligence -->
                <div class="row mt-4">
                    <!-- Sentiment Analysis -->
                    <div class="col-md-6">
                        <div class="card card-outline card-warning shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-smile mr-2"></i>
                                    Sentiment & Social Tone
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-warning">Pro</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p>Perform automated sentiment analysis on SMS history to identify the emotional health of key relationships.</p>
                                <div class="alert alert-light border">
                                    <i class="fas fa-heartbeat mr-2"></i> Emotional Profiling & Social Health.
                                </div>
                                <a href="<?= base_url('analysis/sentiment') ?>" class="btn btn-warning btn-block">
                                    <i class="fas fa-brain mr-1"></i> AnalyzeController Relationship Tone
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Geospatial Hotspots -->
                    <div class="col-md-6">
                        <div class="card card-outline card-success shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-map-marked-alt mr-2"></i>
                                    Geo-Hotspot Clustering
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-success">Location</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p>Automatically cluster coordinate pings to identify and name "Home," "Work," and frequent social bases.</p>
                                <div class="alert alert-light border">
                                    <i class="fas fa-draw-polygon mr-2"></i> Base of Operations Analysis.
                                </div>
                                <a href="<?= base_url('analysis/hotspots') ?>" class="btn btn-success btn-block">
                                    <i class="fas fa-thumbtack mr-1"></i> View Physical Bases
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Intelligence Reports -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card card-outline card-danger shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-pdf mr-2"></i>
                                    Automated Intelligence Reports
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <p>Generate professional-grade summary reports in PDF format for weekly activity or specific investigation cases.</p>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="<?= base_url('analysis/report') ?>" target="_blank" class="btn btn-danger btn-block">
                                            <i class="fas fa-download mr-1"></i> Generate Weekly Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
