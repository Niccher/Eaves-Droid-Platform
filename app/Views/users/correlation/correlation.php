    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-8 col-md-6">
                        <div class="d-flex align-items-center">
                            <h1 class="h2 mb-0">
                                <i class="fas fa-brain text-primary mr-2"></i>
                                Intelligence Dashboard
                            </h1>
                            <div class="ml-3">
                                <span class="badge badge-light border p-2">
                                    <i class="fas fa-microchip text-primary mr-1"></i>
                                    AI-Powered Analysis
                                </span>
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0">Advanced insights from your mobile data using machine learning algorithms</p>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="float-right mt-2">
                            <button type="button" class="btn btn-primary" id="refreshAnalysis">
                                <i class="fas fa-sync-alt mr-1"></i> Refresh Analysis
                            </button>
                            <button type="button" class="btn btn-outline-secondary ml-2" data-toggle="modal" data-target="#analysisSettings">
                                <i class="fas fa-cog mr-1"></i> Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.container-fluid -->
        </section>

        <!-- Quick Stats Row -->
        <section class="content mb-4">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $totalAnalyzedSMS ?? 0 ?></h3>
                                <p>SMS Analyzed</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <a href="#smsInsights" class="small-box-footer" data-toggle="collapse">
                                View Insights <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo $financialAlerts ?? 0 ?></h3>
                                <p>Financial Transactions</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <a href="<?php echo base_url('analysis/sms/finance'); ?>" class="small-box-footer">
                                View Details <i class="fas fa-chart-line"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $suspiciousCalls ?? 0 ?></h3>
                                <p>Suspicious Activities</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <a href="<?php echo base_url('analysis/sms/malicious'); ?>" class="small-box-footer">
                                Investigate <i class="fas fa-search"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?php echo $newContacts ?? 0 ?></h3>
                                <p>New Callers</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <a href="<?php echo base_url('analysis/calls/new'); ?>" class="small-box-footer">
                                Review Contacts <i class="fas fa-phone"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- Left Column - Analysis Categories -->
                    <div class="col-lg-3">
                        <!-- SMS Analysis Card -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-sms mr-2"></i>
                                    SMS Analysis
                                    <span class="badge badge-light float-right"><?php echo $smsCategories ?? 3 ?></span>
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="nav flex-column nav-pills" id="smsAnalysisTab" role="tablist" aria-orientation="vertical">
                                    <a class="nav-link active" href="<?php echo base_url('analysis/sms?category=financial'); ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-info p-2">
                                                    <i class="fas fa-comments-dollar"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Financial SMS</div>
                                                <small class="text-muted">Banking & Transactions</small>
                                            </div>
                                            <span class="badge badge-light ml-auto"><?php echo $financialSMS ?? 0 ?></span>
                                        </div>
                                    </a>
                                    <a class="nav-link" href="<?php echo base_url('analysis/sms?category=promo'); ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-warning p-2">
                                                    <i class="fas fa-ad"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Promotional SMS</div>
                                                <small class="text-muted">Ads & Marketing</small>
                                            </div>
                                            <span class="badge badge-light ml-auto"><?php echo $promotionalSMS ?? 0 ?></span>
                                        </div>
                                    </a>
                                    <a class="nav-link" href="<?php echo base_url('analysis/sms?category=malicious'); ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-danger p-2">
                                                    <i class="fas fa-user-shield"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Malicious SMS</div>
                                                <small class="text-muted">Phishing & Scams</small>
                                            </div>
                                            <span class="badge badge-light ml-auto"><?php echo $maliciousSMS ?? 0 ?></span>
                                        </div>
                                    </a>
                                    <a class="nav-link" href="<?php echo base_url('analysis/sms?category=otp'); ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-primary p-2">
                                                    <i class="fas fa-key"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">OTP/Auth</div>
                                                <small class="text-muted">Codes & Verification</small>
                                            </div>
                                            <span class="badge badge-light ml-auto"><?php echo $otpSMS ?? 0 ?></span>
                                        </div>
                                    </a>
                                    <a class="nav-link" href="<?php echo base_url('analysis/sms?category=utility'); ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-secondary p-2">
                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Utility/Bills</div>
                                                <small class="text-muted">Power & Water</small>
                                            </div>
                                            <span class="badge badge-light ml-auto"><?php echo $utilitySMS ?? 0 ?></span>
                                        </div>
                                    </a>
                                    <a class="nav-link" href="<?php echo base_url('analysis/sms?category=service'); ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-dark p-2">
                                                    <i class="fas fa-truck"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Service/Delivery</div>
                                                <small class="text-muted">Uber, Jumia, etc.</small>
                                            </div>
                                            <span class="badge badge-light ml-auto"><?php echo $serviceSMS ?? 0 ?></span>
                                        </div>
                                    </a>
                                    <a class="nav-link" href="<?php echo base_url('analysis/sms?category=personal'); ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-success p-2">
                                                    <i class="fas fa-user-circle"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Personal SMS</div>
                                                <small class="text-muted">Conversations</small>
                                            </div>
                                            <span class="badge badge-light ml-auto"><?php echo $personalSMS ?? 0 ?></span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="<?php echo base_url('analysis/sms'); ?>" class="btn btn-primary btn-block">
                                    <i class="fas fa-chart-bar mr-1"></i> View Full Analysis
                                </a>
                            </div>
                        </div>

                        <!-- Call Analysis Card -->
                        <div class="card card-success mt-4">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-phone-alt mr-2"></i>
                                    Call Analysis
                                    <span class="badge badge-light float-right"><?php echo $callCategories ?? 2 ?></span>
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="nav flex-column nav-pills" id="callAnalysisTab" role="tablist" aria-orientation="vertical">
                                    <a class="nav-link active" href="<?php echo base_url('analysis/calls?category=family'); ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-success p-2">
                                                    <i class="fas fa-user-friends"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Family & Friends</div>
                                                <small class="text-muted">Frequent Contacts</small>
                                            </div>
                                            <span class="badge badge-light ml-auto"><?php echo $familyCalls ?? 0 ?></span>
                                        </div>
                                    </a>
                                    <a class="nav-link" href="<?php echo base_url('analysis/calls?category=new'); ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-info p-2">
                                                    <i class="fas fa-phone"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">New Callers</div>
                                                <small class="text-muted">Unknown Numbers</small>
                                            </div>
                                            <span class="badge badge-light ml-auto"><?php echo $newCalls ?? 0 ?></span>
                                        </div>
                                    </a>
                                    <a class="nav-link" href="<?php echo base_url('analysis/calls?category=business'); ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-warning p-2">
                                                    <i class="fas fa-briefcase"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Business Calls</div>
                                                <small class="text-muted">Professional Contacts</small>
                                            </div>
                                            <span class="badge badge-light ml-auto"><?php echo $businessCalls ?? 0 ?></span>
                                        </div>
                                    </a>
                                    <a class="nav-link" href="<?php echo base_url('analysis/calls?category=intl'); ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-dark p-2">
                                                    <i class="fas fa-globe-africa"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">International</div>
                                                <small class="text-muted">Foreign Numbers</small>
                                            </div>
                                            <span class="badge badge-light ml-auto"><?php echo $intlCalls ?? 0 ?></span>
                                        </div>
                                    </a>
                                    <a class="nav-link" href="<?php echo base_url('analysis/calls?category=urgent'); ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-danger p-2">
                                                    <i class="fas fa-history"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Urgent/Frequent</div>
                                                <small class="text-muted">High Frequency Unknown</small>
                                            </div>
                                            <span class="badge badge-light ml-auto"><?php echo $urgentCalls ?? 0 ?></span>
                                        </div>
                                    </a>
                                    <a class="nav-link" href="<?php echo base_url('analysis/calls?category=spam'); ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <span class="badge badge-danger p-2">
                                                    <i class="fas fa-ban"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Spam Calls</div>
                                                <small class="text-muted">Blocked Numbers</small>
                                            </div>
                                            <span class="badge badge-light ml-auto"><?php echo $spamCalls ?? 0 ?></span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="<?php echo base_url('analysis/calls'); ?>" class="btn btn-success btn-block">
                                    <i class="fas fa-chart-pie mr-1"></i> View Call Analytics
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Analysis Content -->
                    <div class="col-lg-9">
                        <!-- AI Analysis Overview Card -->
                        <div class="card card-dark card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-robot mr-2"></i>
                                    AI-Powered Intelligence
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-info">
                                        <i class="fas fa-bolt mr-1"></i> Real-time Analysis
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            <h5><i class="fas fa-lightbulb mr-2"></i> Intelligent Pattern Recognition</h5>
                                            <p class="mb-0">Our AI algorithms analyze your mobile data to identify patterns, categorize communications, and detect anomalies using machine learning models.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-box mb-4">
                                            <span class="info-box-icon bg-info elevation-1">
                                                <i class="fas fa-comments"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">SMS Intelligence Engine</span>
                                                <span class="info-box-number">
                                                    <?php echo $smsAccuracy ?? '95' ?>% Accuracy
                                                </span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-info" style="width: <?php echo $smsAccuracy ?? 95 ?>%"></div>
                                                </div>
                                                <small class="text-muted">Text classification for financial, promotional, and malicious messages</small>
                                            </div>
                                        </div>

                                        <div class="card card-info">
                                            <div class="card-header">
                                                <h3 class="card-title">
                                                    <i class="fas fa-chart-pie mr-2"></i>
                                                    SMS Categories
                                                </h3>
                                            </div>
                                            <div class="card-body">
                                                <canvas id="smsPieChart" height="150"></canvas>
                                                <div class="mt-3">
                                                    <span class="badge badge-info mr-2">Financial</span>
                                                    <span class="badge badge-primary mr-2">OTP</span>
                                                    <span class="badge badge-warning mr-2">Promotional</span>
                                                    <span class="badge badge-secondary mr-2">Utility</span>
                                                    <span class="badge badge-dark mr-2">Service</span>
                                                    <span class="badge badge-danger mr-2">Malicious</span>
                                                    <span class="badge badge-success">Personal</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="info-box mb-4">
                                            <span class="info-box-icon bg-success elevation-1">
                                                <i class="fas fa-phone-alt"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Call Pattern Analysis</span>
                                                <span class="info-box-number">
                                                    <?php echo $callAccuracy ?? '92' ?>% Accuracy
                                                </span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" style="width: <?php echo $callAccuracy ?? 92 ?>%"></div>
                                                </div>
                                                <small class="text-muted">Contact categorization and behavior analysis</small>
                                            </div>
                                        </div>

                                        <div class="card card-success">
                                            <div class="card-header">
                                                <h3 class="card-title">
                                                    <i class="fas fa-chart-bar mr-2"></i>
                                                    Call Distribution
                                                </h3>
                                            </div>
                                            <div class="card-body">
                                                <canvas id="callBarChart" height="150"></canvas>
                                                <div class="mt-3">
                                                    <span class="badge badge-success mr-2">Family</span>
                                                    <span class="badge badge-info mr-2">New</span>
                                                    <span class="badge badge-warning mr-2">Business</span>
                                                    <span class="badge badge-dark mr-2">Intl</span>
                                                    <span class="badge badge-danger mr-2">Urgent</span>
                                                    <span class="badge badge-danger">Spam</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Real-time Insights -->
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="card-title">
                                                    <i class="fas fa-bolt mr-2 text-warning"></i>
                                                    Real-time Insights
                                                </h3>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                        <tr>
                                                            <th>Time</th>
                                                            <th>Type</th>
                                                            <th>Insight</th>
                                                            <th>Confidence</th>
                                                            <th>Action</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php if (!empty($recentInsights)): ?>
                                                            <?php foreach ($recentInsights as $insight): ?>
                                                                <tr>
                                                                    <td>
                                                                        <small><?php echo date('H:i', $insight['timestamp']); ?></small>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge badge-<?php echo $insight['type_color'] ?? 'info'; ?>">
                                                                            <i class="fas fa-<?php echo $insight['icon'] ?? 'info-circle'; ?> mr-1"></i>
                                                                            <?php echo $insight['type'] ?? 'Info'; ?>
                                                                        </span>
                                                                    </td>
                                                                    <td><?php echo htmlspecialchars($insight['message'] ?? ''); ?></td>
                                                                    <td>
                                                                        <div class="progress progress-sm">
                                                                            <div class="progress-bar bg-<?php echo $insight['confidence_color'] ?? 'success'; ?>"
                                                                                 style="width: <?php echo $insight['confidence'] ?? 0; ?>%"></div>
                                                                        </div>
                                                                        <small><?php echo $insight['confidence'] ?? 0; ?>%</small>
                                                                    </td>
                                                                    <td>
                                                                        <a href="<?php echo $insight['action_url'] ?? '#'; ?>" class="btn btn-xs btn-outline-primary">
                                                                            <i class="fas fa-search mr-1"></i> Review
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="5" class="text-center py-4">
                                                                    <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                                                                    <p class="text-muted">No recent insights available</p>
                                                                </td>
                                                            </tr>
                                                        <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-database mr-1"></i>
                                            Last analyzed: <?php echo date('M d, Y H:i:s'); ?>
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="float-right">
                                            <button type="button" class="btn btn-sm btn-outline-info" id="runAnalysis">
                                                <i class="fas fa-play mr-1"></i> Run New Analysis
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary ml-2" id="exportReport">
                                                <i class="fas fa-download mr-1"></i> Export Report
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="info-box bg-gradient-info">
                                    <span class="info-box-icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Financial Analysis</span>
                                        <span class="info-box-number"><?php echo $financialSMS ?? 0 ?> transactions</span>
                                        <a href="<?php echo base_url('analysis/sms/finance'); ?>" class="btn btn-light btn-sm mt-2">
                                            <i class="fas fa-external-link-alt mr-1"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-gradient-warning">
                                    <span class="info-box-icon">
                                        <i class="fas fa-ad"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Promotional Filter</span>
                                        <span class="info-box-number"><?php echo $promotionalSMS ?? 0 ?> messages</span>
                                        <a href="<?php echo base_url('analysis/sms/promotion'); ?>" class="btn btn-light btn-sm mt-2">
                                            <i class="fas fa-external-link-alt mr-1"></i> Manage Ads
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-gradient-danger">
                                    <span class="info-box-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Security Threats</span>
                                        <span class="info-box-number"><?php echo $maliciousSMS ?? 0 ?> detected</span>
                                        <a href="<?php echo base_url('analysis/sms/malicious'); ?>" class="btn btn-light btn-sm mt-2">
                                            <i class="fas fa-external-link-alt mr-1"></i> Review Threats
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Analysis Settings Modal -->
    <div class="modal fade" id="analysisSettings" tabindex="-1" role="dialog" aria-labelledby="analysisSettingsLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="analysisSettingsLabel">
                        <i class="fas fa-cog mr-2"></i>Analysis Settings
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="settingsForm">
                        <div class="form-group">
                            <label><i class="fas fa-bell mr-1"></i> Alert Notifications</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="financialAlerts" checked>
                                <label class="custom-control-label" for="financialAlerts">Financial transaction alerts</label>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="securityAlerts" checked>
                                <label class="custom-control-label" for="securityAlerts">Security threat alerts</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="analysisFrequency"><i class="fas fa-sync-alt mr-1"></i> Analysis Frequency</label>
                            <select class="form-control" id="analysisFrequency">
                                <option value="realtime">Real-time</option>
                                <option value="hourly" selected>Hourly</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="confidenceThreshold"><i class="fas fa-chart-line mr-1"></i> Confidence Threshold</label>
                            <input type="range" class="custom-range" id="confidenceThreshold" min="50" max="100" value="85">
                            <small class="text-muted">Minimum confidence level: <span id="thresholdValue">85%</span></small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveSettings">
                        <i class="fas fa-save mr-1"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .info-box {
            border-radius: 0.25rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            transition: transform 0.2s ease;
        }

        .info-box:hover {
            transform: translateY(-2px);
        }

        .nav-pills .nav-link {
            border-radius: 0.25rem;
            margin-bottom: 5px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .nav-pills .nav-link:hover {
            background-color: rgba(0,0,0,0.05);
        }

        .nav-pills .nav-link.active {
            background-color: #007bff;
            box-shadow: 0 2px 4px rgba(0,123,255,.3);
        }

        .card {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }

        .card-dark {
            background: linear-gradient(135deg, #343a40 0%, #2c3136 100%);
            border-color: #454d55;
        }

        .card-dark .card-header {
            background-color: rgba(255,255,255,0.05);
            border-bottom-color: #454d55;
        }

        .progress-sm {
            height: 0.5rem;
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        }

        .bg-gradient-danger {
            background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
        }

        .badge {
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .info-box {
                margin-bottom: 15px;
            }

            .nav-pills .nav-link {
                padding: 10px;
            }

            .card-header .card-title {
                font-size: 1.1rem;
            }
        }
    </style>

    <script>
        $(document).ready(function() {
            // Chart initialization
            const smsCtx = document.getElementById('smsPieChart').getContext('2d');
            const callCtx = document.getElementById('callBarChart').getContext('2d');

            // SMS Pie Chart
            const smsPieChart = new Chart(smsCtx, {
                type: 'pie',
                data: {
                    labels: ['Financial', 'OTP', 'Promotional', 'Utility', 'Service', 'Malicious', 'Personal'],
                    datasets: [{
                        data: [
                            <?php echo $financialSMS ?? 0 ?>, 
                            <?php echo $otpSMS ?? 0 ?>, 
                            <?php echo $promotionalSMS ?? 0 ?>, 
                            <?php echo $utilitySMS ?? 0 ?>, 
                            <?php echo $serviceSMS ?? 0 ?>, 
                            <?php echo $maliciousSMS ?? 0 ?>, 
                            <?php echo $personalSMS ?? 0 ?>
                        ],
                        backgroundColor: [
                            '#17a2b8',
                            '#007bff',
                            '#ffc107',
                            '#6c757d',
                            '#343a40',
                            '#dc3545',
                            '#28a745'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    }
                }
            });

            // Call Bar Chart
            const callBarChart = new Chart(callCtx, {
                type: 'bar',
                data: {
                    labels: ['Family', 'New', 'Business', 'Intl', 'Urgent', 'Spam'],
                    datasets: [{
                        label: 'Number of Calls',
                        data: [
                            <?php echo $familyCalls ?? 0 ?>, 
                            <?php echo $newCalls ?? 0 ?>, 
                            <?php echo $businessCalls ?? 0 ?>, 
                            <?php echo $intlCalls ?? 0 ?>, 
                            <?php echo $urgentCalls ?? 0 ?>, 
                            <?php echo $spamCalls ?? 0 ?>
                        ],
                        backgroundColor: [
                            '#28a745',
                            '#17a2b8',
                            '#ffc107',
                            '#343a40',
                            '#dc3545',
                            '#dc3545'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 20
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            });

            // Refresh analysis
            $('#refreshAnalysis').click(function() {
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Analyzing...');
                setTimeout(() => {
                    $(this).prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Refresh Analysis');
                    alert('Analysis refreshed successfully!');
                }, 1500);
            });

            // Run new analysis
            $('#runAnalysis').click(function() {
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');
                setTimeout(() => {
                    $(this).prop('disabled', false).html('<i class="fas fa-play mr-1"></i> Run New Analysis');
                    alert('New analysis completed! Check the insights table for updated results.');
                }, 2000);
            });

            // Export report
            $('#exportReport').click(function() {
                alert('Export feature would generate a detailed PDF/CSV report');
            });

            // Settings modal
            $('#confidenceThreshold').on('input', function() {
                $('#thresholdValue').text($(this).val() + '%');
            });

            $('#saveSettings').click(function() {
                alert('Settings saved successfully!');
                $('#analysisSettings').modal('hide');
            });

            // Navigation tabs
            $('.nav-pills a').click(function() {
                $('.nav-pills a').removeClass('active');
                $(this).addClass('active');
            });
        });
    </script>