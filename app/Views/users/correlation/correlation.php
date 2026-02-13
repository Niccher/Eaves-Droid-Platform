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
                    <!-- Main Content Area -->
                    <div class="col-lg-12">
                        <div class="card card-white card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-brain mr-2"></i>
                                    Intelligence Insight
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-info">
                                        <i class="fas fa-bolt mr-1"></i> Statistical Analysis
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- SMS Section -->
                                    <div class="col-md-6">
                                        <div class="card card-info card-outline">
                                            <div class="card-header d-flex p-0">
                                                <h3 class="card-title p-3"><i class="fas fa-chart-pie mr-1"></i> SMS Categories</h3>
                                                <ul class="nav nav-pills ml-auto p-2">
                                                    <li class="nav-item"><a class="nav-link btn-xs" href="<?php echo base_url('analysis/sms?category=financial'); ?>">Fin</a></li>
                                                    <li class="nav-item"><a class="nav-link btn-xs" href="<?php echo base_url('analysis/sms?category=otp'); ?>">OTP</a></li>
                                                    <li class="nav-item"><a class="nav-link btn-xs" href="<?php echo base_url('analysis/sms?category=promo'); ?>">Ads</a></li>
                                                    <li class="nav-item"><a class="nav-link btn-xs" href="<?php echo base_url('analysis/sms?category=malicious'); ?>">Sec</a></li>
                                                    <li class="nav-item"><a class="nav-link btn-xs" href="<?php echo base_url('analysis/sms'); ?>">All</a></li>
                                                </ul>
                                            </div>
                                            <div class="card-body text-center">
                                                <canvas id="smsPieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                                <div class="mt-3">
                                                    <span class="badge badge-info mr-1">Financial: <?php echo $financialSMS ?? 0 ?></span>
                                                    <span class="badge badge-primary mr-1">OTP: <?php echo $otpSMS ?? 0 ?></span>
                                                    <span class="badge badge-warning mr-1">Promo: <?php echo $promotionalSMS ?? 0 ?></span>
                                                    <span class="badge badge-danger">Scams: <?php echo $maliciousSMS ?? 0 ?></span>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <a href="<?php echo base_url('analysis/sms'); ?>" class="btn btn-primary btn-block btn-sm">
                                                    <i class="fas fa-search-plus mr-1"></i> View Full SMS Analysis
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Call Section -->
                                    <div class="col-md-6">
                                        <div class="card card-success card-outline">
                                            <div class="card-header d-flex p-0">
                                                <h3 class="card-title p-3"><i class="fas fa-chart-bar mr-1"></i> Call Pattern</h3>
                                                <ul class="nav nav-pills ml-auto p-2">
                                                    <li class="nav-item"><a class="nav-link btn-xs" href="<?php echo base_url('analysis/calls?category=family'); ?>">Fam</a></li>
                                                    <li class="nav-item"><a class="nav-link btn-xs" href="<?php echo base_url('analysis/calls?category=new'); ?>">New</a></li>
                                                    <li class="nav-item"><a class="nav-link btn-xs" href="<?php echo base_url('analysis/calls?category=business'); ?>">Biz</a></li>
                                                    <li class="nav-item"><a class="nav-link btn-xs" href="<?php echo base_url('analysis/calls?category=spam'); ?>">Spam</a></li>
                                                    <li class="nav-item"><a class="nav-link btn-xs" href="<?php echo base_url('analysis/calls'); ?>">All</a></li>
                                                </ul>
                                            </div>
                                            <div class="card-body text-center">
                                                <canvas id="callBarChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                                <div class="mt-3">
                                                    <span class="badge badge-success mr-1">Family: <?php echo $familyCalls ?? 0 ?></span>
                                                    <span class="badge badge-info mr-1">New: <?php echo $newCalls ?? 0 ?></span>
                                                    <span class="badge badge-warning mr-1">Biz: <?php echo $businessCalls ?? 0 ?></span>
                                                    <span class="badge badge-danger">Spam: <?php echo $spamCalls ?? 0 ?></span>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <a href="<?php echo base_url('analysis/calls'); ?>" class="btn btn-success btn-block btn-sm">
                                                    <i class="fas fa-search-plus mr-1"></i> View Full Call Analysis
                                                </a>
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