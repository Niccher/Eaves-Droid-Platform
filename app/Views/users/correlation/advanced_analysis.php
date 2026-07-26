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
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-8 col-md-6">
                        <div class="d-flex align-items-center">
                            <h1 class="h2 mb-0">
                                <i class="fas fa-microchip text-info mr-2"></i>
                                Advanced Analysis Suite
                            </h1>
                            <div class="ml-3">
                                <span class="badge badge-info border p-2">
                                    <i class="fas fa-magic mr-1"></i> Data Intelligence
                                </span>
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0">Deep vertical insights and predictive modeling across mobile data streams</p>
                    </div>
                    <div class="col-lg-4 col-md-6 text-right">
                        <a class="btn btn-outline-info btn-sm" href="<?= base_url('analysis/refresh-ml') ?>"><i class="fas fa-sync-alt mr-1"></i> Refresh ML Analysis</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <?php if (isset($ml_insight_finance) && !empty($ml_insight_finance['insights'])): ?>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-outline card-info shadow-sm">
                            <div class="card-header">
                                <?php $_eng = (new \App\Models\Mod_Anomalies())->getDefaultEngine(); $_engLabel = match($_eng){'python'=>'Python Engine','both'=>'Hybrid Engine',default=>'PHP Engine'}; ?>
                        <h3 class="card-title"><i class="fas fa-brain mr-2"></i> <?= $_engLabel ?> Intelligence</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <span class="badge badge-info p-2"><?= $ml_insight_finance['algorithm'] ?></span>
                                        <p class="text-muted mt-2 mb-0"><small><?= $ml_insight_finance['data_source'] ?></small></p>
                                    </div>
                                    <div class="col-md-8">
                                        <p><?= $ml_insight_finance['description'] ?></p>
                                        <ul class="mb-0">
                                            <?php foreach ($ml_insight_finance['insights'] as $insight): ?>
                                            <li><?= $insight ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="container-fluid">
                
                <!-- Advanced Features Grid -->
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
                                <p>Analyze communication frequency to map out the social circle and interaction heatmaps.</p>
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
                                <p>Analyze activity patterns (walking, driving, still) and screen time habits to build a lifestyle profile.</p>
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
                                    <i class="fas fa-receipt mr-1"></i> View Subscriptions
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
                                    <span class="badge badge-info">Files</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p>Analyze disk usage by source (WhatsApp vs Camera) and identify aging media content or large space hogs.</p>
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
                                    <i class="fas fa-brain mr-1"></i> Analyze Relationship Tone
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
