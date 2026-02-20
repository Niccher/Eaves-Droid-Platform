    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
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
                    <div class="col-lg-4 col-md-6">
                        <nav aria-label="breadcrumb" class="float-right mt-2">
                            <ol class="breadcrumb bg-transparent p-0 mb-0">
                                <li class="breadcrumb-item"><a href="<?= base_url('home') ?>"><i class="fas fa-home mr-1"></i>Home</a></li>
                                <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>">Intelligence</a></li>
                                <li class="breadcrumb-item active">Advanced</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
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
                                <a href="<?= base_url('analysis/advanced/finance') ?>" class="btn btn-success btn-block">
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
                                <a href="<?= base_url('analysis/advanced/location') ?>" class="btn btn-primary btn-block">
                                    <i class="fas fa-map mr-1"></i> View Heatmap & Paths
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <!-- Device Pulse -->
                    <div class="col-md-6">
                        <div class="card card-outline card-info shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-heartbeat mr-2"></i>
                                    Device "Pulse"
                                </h3>
                            </div>
                            <div class="card-body">
                                <p>Real-time health status: battery levels, storage availability, and network connectivity history.</p>
                                <div class="row text-center mt-3">
                                    <div class="col-4">
                                        <H5 class="mb-0 text-success">85%</H5>
                                        <small>Battery</small>
                                    </div>
                                    <div class="col-4">
                                        <H5 class="mb-0 text-info">12GB</H5>
                                        <small>Storage Free</small>
                                    </div>
                                    <div class="col-4">
                                        <H5 class="mb-0 text-primary">LTE</H5>
                                        <small>Signal</small>
                                    </div>
                                </div>
                                <a href="<?= base_url('analysis/advanced/device') ?>" class="btn btn-info btn-block mt-4">
                                    <i class="fas fa-heartbeat mr-1"></i> View Health Monitor
                                </a>
                            </div>
                        </div>
                    </div>

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
                                <a href="<?= base_url('analysis/advanced/social') ?>" class="btn btn-warning btn-block">
                                    <i class="fas fa-network-wired mr-1"></i> Open Social Map
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Lifestyle & Timeline Features -->
                <div class="row mt-4">
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
                                <a href="<?= base_url('analysis/advanced/lifestyle') ?>" class="btn btn-secondary btn-block">
                                    <i class="fas fa-fingerprint mr-1"></i> View Lifestyle Profile
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Intelligence Timeline -->
                    <div class="col-md-6">
                        <div class="card card-outline card-dark shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-history mr-2"></i>
                                    Universal Timeline
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-dark">Feed</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p>A vertical chronological story of the day, merging SMS, calls, and location updates into one feed.</p>
                                <div class="alert alert-light border">
                                    <i class="fas fa-stream mr-2"></i> Chronological Event Sequencing.
                                </div>
                                <a href="<?= base_url('analysis/timeline') ?>" class="btn btn-dark btn-block">
                                    <i class="fas fa-list-ul mr-1"></i> Open Timeline Feed
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
                                        <a href="<?= base_url('analysis/advanced/report') ?>" target="_blank" class="btn btn-danger btn-block">
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
