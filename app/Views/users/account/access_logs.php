<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-shield-alt text-primary mr-2"></i>
                            <?php echo $access_head ?? 'Access Logs' ?>
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-history text-primary mr-1"></i>
                                Total: <b><?php echo count($user_logs) ?? 0 ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Monitor all login attempts and access activities on your account</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="float-right mt-2">
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary" id="refreshLogs">
                                <i class="fas fa-sync-alt mr-1"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-download mr-1"></i> Export
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#" id="exportPDF">
                                    <i class="fas fa-file-pdf mr-2 text-danger"></i> PDF
                                </a>
                                <a class="dropdown-item" href="#" id="exportCSV">
                                    <i class="fas fa-file-csv mr-2 text-success"></i> CSV
                                </a>
                                <a class="dropdown-item" href="#" id="exportJSON">
                                    <i class="fas fa-file-code mr-2 text-warning"></i> JSON
                                </a>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary ml-2" data-toggle="modal" data-target="#filterModal">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>

    <!-- Stats Row -->
    <section class="content mb-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $totalLogs ?? 0 ?></h3>
                            <p>Total Log Entries</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <a href="#log_web" class="small-box-footer" data-toggle="tab">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $successfulLogins ?? 0 ?></h3>
                            <p>Successful Logins</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <a href="#" class="small-box-footer" data-filter="status:success">
                            Filter Success <i class="fas fa-filter"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $failedAttempts ?? 0 ?></h3>
                            <p>Failed Attempts</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <a href="#" class="small-box-footer" data-filter="status:failed">
                            Filter Failed <i class="fas fa-filter"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo $suspiciousActivities ?? 0 ?></h3>
                            <p>Suspicious Activities</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <a href="#" class="small-box-footer" data-filter="status:suspicious">
                            View Suspicious <i class="fas fa-eye"></i>
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
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list-alt mr-2"></i>
                                Access History Timeline
                                <small class="text-muted ml-2">Showing last <?php echo count($user_logs) ?> activities</small>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <div class="btn-group ml-2">
                                    <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="#" id="showAll"><i class="fas fa-eye mr-2"></i> Show All</a>
                                        <a class="dropdown-item" href="#" data-filter="platform:web"><i class="fas fa-globe mr-2"></i> Web Only</a>
                                        <a class="dropdown-item" href="#" data-filter="platform:android"><i class="fab fa-android mr-2"></i> Android Only</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#" data-filter="time:today"><i class="fas fa-calendar-day mr-2"></i> Today</a>
                                        <a class="dropdown-item" href="#" data-filter="time:week"><i class="fas fa-calendar-week mr-2"></i> This Week</a>
                                        <a class="dropdown-item" href="#" data-filter="time:month"><i class="fas fa-calendar-alt mr-2"></i> This Month</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="nav nav-pills card-header-pills">
                                        <a class="nav-link active" href="#log_web" data-toggle="tab">
                                            <i class="fas fa-globe mr-1"></i> Web Platform
                                            <span class="badge badge-light ml-1"><?php echo $webLogsCount ?? 0 ?></span>
                                        </a>
                                        <a class="nav-link" href="#log_android" data-toggle="tab">
                                            <i class="fab fa-android mr-1"></i> Android
                                            <span class="badge badge-light ml-1"><?php echo $androidLogsCount ?? 0 ?></span>
                                        </a>
                                        <a class="nav-link" href="#log_all" data-toggle="tab">
                                            <i class="fas fa-layer-group mr-1"></i> Combined View
                                            <span class="badge badge-light ml-1"><?php echo count($user_logs) ?? 0 ?></span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-content">
                                <!-- Web Platform Tab -->
                                <div class="active tab-pane" id="log_web">
                                    <?php if (!empty($webLogs)): ?>
                                        <div class="timeline timeline-inverse">
                                            <?php
                                            $currentDate = '';
                                            foreach ($webLogs as $entry):
                                                $timestamp = $entry['Timestamps'] ?? time();
                                                $date = date('d D M Y', $timestamp);
                                                $time = date('H:i:s a', $timestamp);
                                                $action = $entry['Action'] ?? 'Unknown Action';
                                                $ip = $entry['IP'] ?? 'N/A';
                                                $browser = $entry['Browser'] ?? 'Unknown';
                                                $status = $entry['Status'] ?? 'info';

                                                // Status icons and colors
                                                $statusConfig = [
                                                    'success' => ['icon' => 'check-circle', 'color' => 'success', 'bg' => 'bg-success'],
                                                    'failed' => ['icon' => 'times-circle', 'color' => 'danger', 'bg' => 'bg-danger'],
                                                    'warning' => ['icon' => 'exclamation-triangle', 'color' => 'warning', 'bg' => 'bg-warning'],
                                                    'info' => ['icon' => 'info-circle', 'color' => 'info', 'bg' => 'bg-info'],
                                                    'suspicious' => ['icon' => 'shield-alt', 'color' => 'purple', 'bg' => 'bg-purple']
                                                ];

                                                $statusInfo = $statusConfig[$status] ?? $statusConfig['info'];
                                                ?>

                                                <?php if ($currentDate != $date): ?>
                                                <div class="time-label">
                                                    <span class="bg-primary">
                                                        <i class="fas fa-calendar-day mr-1"></i>
                                                        <?php echo $date; ?>
                                                    </span>
                                                </div>
                                                <?php $currentDate = $date; ?>
                                            <?php endif; ?>

                                                <div class="timeline-item" data-status="<?php echo $status; ?>" data-platform="web">
                                                <span class="time">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <?php echo $time; ?>
                                                    <span class="badge badge-light ml-2">
                                                        <i class="fas fa-globe mr-1"></i> Web
                                                    </span>
                                                </span>
                                                    <i class="fas fa-<?php echo $statusInfo['icon']; ?> <?php echo $statusInfo['bg']; ?>"></i>
                                                    <div class="timeline-item">
                                                        <div class="timeline-header">
                                                            <h5 class="mb-1"><?php echo htmlspecialchars($action); ?></h5>
                                                            <div class="timeline-body mt-2">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <small class="text-muted">
                                                                            <i class="fas fa-laptop mr-1"></i>
                                                                            <strong>Browser:</strong> <?php echo htmlspecialchars($browser); ?>
                                                                        </small>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <small class="text-muted">
                                                                            <i class="fas fa-network-wired mr-1"></i>
                                                                            <strong>IP Address:</strong>
                                                                            <code><?php echo htmlspecialchars($ip); ?></code>
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="timeline-footer">
                                                        <span class="badge <?php echo $statusInfo['bg']; ?>">
                                                            <i class="fas fa-<?php echo $statusInfo['icon']; ?> mr-1"></i>
                                                            <?php echo ucfirst($status); ?>
                                                        </span>
                                                            <a href="#" class="btn btn-xs btn-outline-secondary float-right" data-toggle="modal" data-target="#logDetailsModal"
                                                               data-action="<?php echo htmlspecialchars($action); ?>"
                                                               data-time="<?php echo $time; ?>"
                                                               data-date="<?php echo $date; ?>"
                                                               data-ip="<?php echo htmlspecialchars($ip); ?>"
                                                               data-browser="<?php echo htmlspecialchars($browser); ?>"
                                                               data-status="<?php echo $status; ?>"
                                                               data-platform="Web">
                                                                <i class="fas fa-search mr-1"></i> Details
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            <div>
                                                <i class="far fa-clock bg-gray"></i>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-globe fa-3x text-muted mb-3"></i>
                                            <h4>No Web Access Logs</h4>
                                            <p class="text-muted">No web platform access activities found</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Android Tab -->
                                <div class="tab-pane" id="log_android">
                                    <?php if (!empty($androidLogs)): ?>
                                        <div class="timeline timeline-inverse">
                                            <?php
                                            $currentDate = '';
                                            foreach ($androidLogs as $entry):
                                                $timestamp = $entry['Timestamps'] ?? time();
                                                $date = date('d D M Y', $timestamp);
                                                $time = date('H:i:s a', $timestamp);
                                                $action = $entry['Action'] ?? 'Unknown Action';
                                                $device = $entry['Device'] ?? 'Unknown Device';
                                                $os_version = $entry['OS_Version'] ?? 'N/A';
                                                $status = $entry['Status'] ?? 'info';

                                                $statusConfig = [
                                                    'success' => ['icon' => 'check-circle', 'color' => 'success', 'bg' => 'bg-success'],
                                                    'failed' => ['icon' => 'times-circle', 'color' => 'danger', 'bg' => 'bg-danger'],
                                                    'warning' => ['icon' => 'exclamation-triangle', 'color' => 'warning', 'bg' => 'bg-warning'],
                                                    'info' => ['icon' => 'info-circle', 'color' => 'info', 'bg' => 'bg-info'],
                                                    'suspicious' => ['icon' => 'shield-alt', 'color' => 'purple', 'bg' => 'bg-purple']
                                                ];

                                                $statusInfo = $statusConfig[$status] ?? $statusConfig['info'];
                                                ?>

                                                <?php if ($currentDate != $date): ?>
                                                <div class="time-label">
                                                    <span class="bg-success">
                                                        <i class="fas fa-mobile-alt mr-1"></i>
                                                        <?php echo $date; ?>
                                                    </span>
                                                </div>
                                                <?php $currentDate = $date; ?>
                                            <?php endif; ?>

                                                <div class="timeline-item" data-status="<?php echo $status; ?>" data-platform="android">
                                                <span class="time">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <?php echo $time; ?>
                                                    <span class="badge badge-success ml-2">
                                                        <i class="fab fa-android mr-1"></i> Android
                                                    </span>
                                                </span>
                                                    <i class="fas fa-<?php echo $statusInfo['icon']; ?> <?php echo $statusInfo['bg']; ?>"></i>
                                                    <div class="timeline-item">
                                                        <div class="timeline-header">
                                                            <h5 class="mb-1"><?php echo htmlspecialchars($action); ?></h5>
                                                            <div class="timeline-body mt-2">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <small class="text-muted">
                                                                            <i class="fas fa-mobile-alt mr-1"></i>
                                                                            <strong>Device:</strong> <?php echo htmlspecialchars($device); ?>
                                                                        </small>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <small class="text-muted">
                                                                            <i class="fas fa-code-branch mr-1"></i>
                                                                            <strong>OS Version:</strong>
                                                                            <span class="badge badge-info"><?php echo htmlspecialchars($os_version); ?></span>
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="timeline-footer">
                                                        <span class="badge <?php echo $statusInfo['bg']; ?>">
                                                            <i class="fas fa-<?php echo $statusInfo['icon']; ?> mr-1"></i>
                                                            <?php echo ucfirst($status); ?>
                                                        </span>
                                                            <a href="#" class="btn btn-xs btn-outline-secondary float-right" data-toggle="modal" data-target="#logDetailsModal"
                                                               data-action="<?php echo htmlspecialchars($action); ?>"
                                                               data-time="<?php echo $time; ?>"
                                                               data-date="<?php echo $date; ?>"
                                                               data-device="<?php echo htmlspecialchars($device); ?>"
                                                               data-os="<?php echo htmlspecialchars($os_version); ?>"
                                                               data-status="<?php echo $status; ?>"
                                                               data-platform="Android">
                                                                <i class="fas fa-search mr-1"></i> Details
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            <div>
                                                <i class="far fa-clock bg-gray"></i>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fab fa-android fa-3x text-muted mb-3"></i>
                                            <h4>No Android Access Logs</h4>
                                            <p class="text-muted">No Android device access activities found</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Combined View Tab -->
                                <div class="tab-pane" id="log_all">
                                    <?php if (!empty($user_logs)): ?>
                                        <div class="timeline timeline-inverse">
                                            <?php
                                            $currentDate = '';
                                            foreach ($user_logs as $entry):
                                                $timestamp = $entry['Timestamps'] ?? time();
                                                $date = date('d D M Y', $timestamp);
                                                $time = date('H:i:s a', $timestamp);
                                                $action = $entry['Action'] ?? 'Unknown Action';
                                                $platform = $entry['Platform'] ?? 'Unknown';
                                                $status = $entry['Status'] ?? 'info';

                                                // Platform icons and colors
                                                $platformConfig = [
                                                    'web' => ['icon' => 'globe', 'color' => 'primary', 'bg' => 'bg-primary'],
                                                    'android' => ['icon' => 'android', 'color' => 'success', 'bg' => 'bg-success'],
                                                    'ios' => ['icon' => 'apple', 'color' => 'secondary', 'bg' => 'bg-secondary'],
                                                    'unknown' => ['icon' => 'question-circle', 'color' => 'dark', 'bg' => 'bg-dark']
                                                ];

                                                $platformInfo = $platformConfig[strtolower($platform)] ?? $platformConfig['unknown'];
                                                ?>

                                                <?php if ($currentDate != $date): ?>
                                                <div class="time-label">
                                                    <span class="bg-info">
                                                        <i class="fas fa-calendar-alt mr-1"></i>
                                                        <?php echo $date; ?>
                                                    </span>
                                                </div>
                                                <?php $currentDate = $date; ?>
                                            <?php endif; ?>

                                                <div class="timeline-item" data-status="<?php echo $status; ?>" data-platform="<?php echo strtolower($platform); ?>">
                                                <span class="time">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <?php echo $time; ?>
                                                </span>
                                                    <i class="fas fa-<?php echo $platformInfo['icon']; ?> <?php echo $platformInfo['bg']; ?>"></i>
                                                    <div class="timeline-item">
                                                        <h5 class="timeline-header mb-2">
                                                            <?php echo htmlspecialchars($action); ?>
                                                        </h5>
                                                        <div class="timeline-footer">
                                                        <span class="badge <?php echo $platformInfo['bg']; ?>">
                                                            <i class="fab fa-<?php echo $platformInfo['icon']; ?> mr-1"></i>
                                                            <?php echo ucfirst($platform); ?>
                                                        </span>
                                                            <small class="text-muted ml-2">
                                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                                <?php echo $entry['Location'] ?? 'Unknown Location'; ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            <div>
                                                <i class="far fa-clock bg-gray"></i>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                            <h4>No Access Logs</h4>
                                            <p class="text-muted">No access activities found for your account</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="dataTables_info" role="status">
                                        Showing last <?php echo min(50, count($user_logs)) ?> of <?php echo count($user_logs) ?> entries
                                        <?php if (!empty($lastUpdated)): ?>
                                            <br><small class="text-muted"><i class="fas fa-sync-alt mr-1"></i> Updated: <?php echo $lastUpdated; ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="prevLogs">
                                                <i class="fas fa-chevron-left mr-1"></i> Older
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="nextLogs">
                                                Newer <i class="fas fa-chevron-right ml-1"></i>
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
    </section>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" role="dialog" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logDetailsModalLabel">
                    <i class="fas fa-info-circle mr-2"></i>Access Log Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-tasks mr-1"></i> Action:</label>
                            <p class="form-control-static" id="detail-action"></p>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar mr-1"></i> Date:</label>
                            <p class="form-control-static" id="detail-date"></p>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-clock mr-1"></i> Time:</label>
                            <p class="form-control-static" id="detail-time"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-laptop mr-1"></i> Platform:</label>
                            <p class="form-control-static" id="detail-platform"></p>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-circle mr-1"></i> Status:</label>
                            <p class="form-control-static" id="detail-status"></p>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-info-circle mr-1"></i> Additional Info:</label>
                            <p class="form-control-static" id="detail-additional"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb mr-2"></i>
                            <strong>Note:</strong> This log entry cannot be modified. Contact support if you notice suspicious activity.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-danger" id="reportSuspicious">
                    <i class="fas fa-flag mr-1"></i> Report Suspicious
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">
                    <i class="fas fa-filter mr-2"></i>Filter Logs
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="form-group">
                        <label for="filterPlatform"><i class="fas fa-layer-group mr-1"></i> Platform</label>
                        <select class="form-control" id="filterPlatform">
                            <option value="">All Platforms</option>
                            <option value="web">Web Platform</option>
                            <option value="android">Android</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filterStatus"><i class="fas fa-circle mr-1"></i> Status</label>
                        <select class="form-control" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                            <option value="warning">Warning</option>
                            <option value="suspicious">Suspicious</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filterDate"><i class="fas fa-calendar mr-1"></i> Date Range</label>
                        <select class="form-control" id="filterDate">
                            <option value="">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="form-group" id="customDateRange" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label>From</label>
                                <input type="date" class="form-control" id="filterDateFrom">
                            </div>
                            <div class="col-md-6">
                                <label>To</label>
                                <input type="date" class="form-control" id="filterDateTo">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-secondary" id="clearFilters">
                    <i class="fas fa-eraser mr-1"></i> Clear
                </button>
                <button type="button" class="btn btn-primary" id="applyFilters">
                    <i class="fas fa-check mr-1"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline-item {
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .timeline-item:hover {
        background-color: rgba(0, 0, 0, 0.02);
        transform: translateX(5px);
    }

    .timeline-inverse .timeline-item .timeline-header {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 10px;
    }

    .timeline-inverse .timeline-item .timeline-body {
        padding: 10px 0;
    }

    .timeline-inverse .timeline-item .timeline-footer {
        border-top: 1px solid #e9ecef;
        padding-top: 10px;
        margin-top: 10px;
    }

    .timeline-time {
        background: #f8f9fa;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
    }

    .bg-purple {
        background-color: #6f42c1 !important;
    }

    .nav-pills .nav-link.active {
        background-color: #007bff;
    }

    .nav-pills .nav-link {
        border-radius: 0.25rem;
        margin-right: 5px;
    }

    .small-box {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        transition: transform 0.2s ease;
    }

    .small-box:hover {
        transform: translateY(-2px);
    }

    .card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }

    @media (max-width: 768px) {
        .timeline-item .timeline-body .row {
            flex-direction: column;
        }

        .timeline-item .timeline-body .col-md-6 {
            margin-bottom: 5px;
        }

        .card-header .card-title {
            font-size: 1.1rem;
        }
    }
</style>

<script>
    $(document).ready(function() {
        // Refresh button
        $('#refreshLogs').click(function() {
            $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Refreshing...');
            setTimeout(() => {
                location.reload();
            }, 500);
        });

        // Export functionality
        $('#exportPDF').click(function(e) {
            e.preventDefault();
            alert('PDF export feature would be implemented here');
        });

        $('#exportCSV').click(function(e) {
            e.preventDefault();
            alert('CSV export feature would be implemented here');
        });

        $('#exportJSON').click(function(e) {
            e.preventDefault();
            alert('JSON export feature would be implemented here');
        });

        // Log details modal
        $('#logDetailsModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const modal = $(this);

            modal.find('#detail-action').text(button.data('action'));
            modal.find('#detail-date').text(button.data('date'));
            modal.find('#detail-time').text(button.data('time'));
            modal.find('#detail-platform').text(button.data('platform'));
            modal.find('#detail-status').text(button.data('status'));

            // Platform-specific additional info
            if (button.data('platform') === 'Web') {
                modal.find('#detail-additional').html(`
                <strong>IP:</strong> ${button.data('ip')}<br>
                <strong>Browser:</strong> ${button.data('browser')}
            `);
            } else if (button.data('platform') === 'Android') {
                modal.find('#detail-additional').html(`
                <strong>Device:</strong> ${button.data('device')}<br>
                <strong>OS Version:</strong> ${button.data('os')}
            `);
            }
        });

        // Report suspicious activity
        $('#reportSuspicious').click(function() {
            if (confirm('Are you sure you want to report this activity as suspicious? This will alert the security team.')) {
                alert('Activity reported. Security team has been notified.');
                $('#logDetailsModal').modal('hide');
            }
        });

        // Filter modal
        $('#filterDate').change(function() {
            if ($(this).val() === 'custom') {
                $('#customDateRange').slideDown();
            } else {
                $('#customDateRange').slideUp();
            }
        });

        // Apply filters
        $('#applyFilters').click(function() {
            const platform = $('#filterPlatform').val();
            const status = $('#filterStatus').val();
            const dateRange = $('#filterDate').val();

            // Apply filters logic here
            $('.timeline-item').each(function() {
                const itemPlatform = $(this).data('platform');
                const itemStatus = $(this).data('status');

                let show = true;

                if (platform && itemPlatform !== platform) {
                    show = false;
                }

                if (status && itemStatus !== status) {
                    show = false;
                }

                if (show) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            $('#filterModal').modal('hide');
        });

        // Clear filters
        $('#clearFilters').click(function() {
            $('#filterForm')[0].reset();
            $('.timeline-item').show();
        });

        // Filter from stats boxes
        $('.small-box-footer[data-filter]').click(function(e) {
            e.preventDefault();
            const filter = $(this).data('filter').split(':');
            const type = filter[0];
            const value = filter[1];

            if (type === 'status') {
                $('#filterStatus').val(value);
                $('#applyFilters').click();
            } else if (type === 'platform') {
                $('#filterPlatform').val(value);
                $('.nav-pills .nav-link[href="#log_' + value + '"]').tab('show');
            }
        });

        // Navigation
        $('#prevLogs').click(function() {
            alert('Older logs would load here');
        });

        $('#nextLogs').click(function() {
            alert('Newer logs would load here');
        });

        // Quick filter from dropdown
        $('.dropdown-menu a[data-filter]').click(function(e) {
            e.preventDefault();
            const filter = $(this).data('filter').split(':');
            const type = filter[0];
            const value = filter[1];

            if (type === 'platform') {
                $('.nav-pills .nav-link[href="#log_' + value + '"]').tab('show');
            } else if (type === 'time') {
                alert('Time filter for ' + value + ' would be applied');
            }
        });

        // Show all logs
        $('#showAll').click(function(e) {
            e.preventDefault();
            $('.timeline-item').show();
        });
    });
</script>