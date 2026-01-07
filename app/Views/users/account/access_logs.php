<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-history text-primary mr-2"></i>
                            Access Logs
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-database text-primary mr-1"></i>
                                Total: <b><?php echo $totalLogs ?? 0 ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Monitor all authentication and access activities</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="float-right mt-2">
                        <small class="text-muted">
                            <i class="far fa-clock mr-1"></i>
                            Last updated: <?php echo $lastUpdated ?? 'Never'; ?>
                        </small>
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
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon">
                            <i class="fas fa-history"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Activities</span>
                            <span class="info-box-number"><?php echo $totalLogs ?? 0 ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Successful</span>
                            <span class="info-box-number"><?php echo $successfulLogins ?? 0 ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box bg-gradient-danger">
                        <span class="info-box-icon">
                            <i class="fas fa-times-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Failed</span>
                            <span class="info-box-number"><?php echo $failedAttempts ?? 0 ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box bg-gradient-warning">
                        <span class="info-box-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Suspicious</span>
                            <span class="info-box-number"><?php echo $suspiciousActivities ?? 0 ?></span>
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
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list-alt mr-2"></i>
                                Access History
                                <small class="text-muted ml-2">Showing last <?php echo min(100, count($user_logs)) ?> activities</small>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($user_logs)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="thead-light">
                                        <tr>
                                            <th width="15%">Date & Time</th>
                                            <th width="10%">Category</th>
                                            <th width="15%">Action</th>
                                            <th width="10%">Severity</th>
                                            <th width="15%">Device</th>
                                            <th width="10%">IP Address</th>
                                            <th width="10%">Status</th>
                                            <th width="15%">Details</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($user_logs as $entry): ?>
                                            <?php
                                            $timestamp = $entry['Timestamps'] ?? time();
                                            $date = date('d/m/Y', $timestamp);
                                            $time = date('H:i:s', $timestamp);
                                            $action = $entry['Action'] ?? 'Unknown Action';
                                            $category = $entry['action_category'] ?? 'system';
                                            $severity = $entry['action_severity'] ?? 'low';
                                            $deviceType = $entry['device_type'] ?? 'unknown';
                                            $deviceName = $entry['Device'] ?? 'Unknown Device';
                                            $ip = $entry['IP'] ?? 'N/A';
                                            $status = $entry['Status'] ?? 'info';

                                            // Category badge colors
                                            $categoryColors = [
                                                'authentication' => 'primary',
                                                'file' => 'info',
                                                'profile' => 'success',
                                                'admin' => 'warning',
                                                'system' => 'secondary',
                                                'security' => 'danger'
                                            ];

                                            // Severity badge colors
                                            $severityColors = [
                                                'low' => 'success',
                                                'medium' => 'warning',
                                                'high' => 'danger',
                                                'critical' => 'dark'
                                            ];

                                            // Status badge colors
                                            $statusColors = [
                                                'success' => 'success',
                                                'failed' => 'danger',
                                                'warning' => 'warning',
                                                'suspicious' => 'danger',
                                                'info' => 'info'
                                            ];

                                            // Status text
                                            $statusText = $status;
                                            if ($status === 'suspicious') {
                                                $statusText = 'Suspicious';
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="text-dark font-weight-bold"><?php echo $date; ?></div>
                                                    <small class="text-muted"><?php echo $time; ?></small>
                                                </td>
                                                <td>
                                                        <span class="badge badge-<?php echo $categoryColors[$category] ?? 'secondary'; ?>">
                                                            <i class="fas <?php echo $entry['CategoryIcon'] ?? 'fa-question-circle'; ?> mr-1"></i>
                                                            <?php echo ucfirst($category); ?>
                                                        </span>
                                                </td>
                                                <td>
                                                    <div class="text-dark"><?php echo htmlspecialchars($action); ?></div>
                                                    <?php if (!empty($entry['request_url'])): ?>
                                                        <small class="text-muted d-block text-truncate" style="max-width: 200px;">
                                                            <?php echo htmlspecialchars($entry['request_url']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                        <span class="badge badge-<?php echo $severityColors[$severity] ?? 'secondary'; ?>">
                                                            <i class="fas <?php echo $entry['SeverityIcon'] ?? 'fa-circle'; ?> mr-1"></i>
                                                            <?php echo ucfirst($severity); ?>
                                                        </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas <?php echo $entry['DeviceIcon'] ?? 'fa-question-circle text-muted'; ?> mr-2"></i>
                                                        <div>
                                                            <div class="text-dark"><?php echo htmlspecialchars($deviceName); ?></div>
                                                            <?php if (!empty($entry['operating_system'])): ?>
                                                                <small class="text-muted"><?php echo htmlspecialchars($entry['operating_system']); ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <code class="text-dark"><?php echo htmlspecialchars($ip); ?></code>
                                                    <?php if (!empty($entry['Location']) && $entry['Location'] !== 'Unknown Location'): ?>
                                                        <div class="text-muted small"><?php echo htmlspecialchars($entry['Location']); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                        <span class="badge badge-<?php echo $statusColors[$status] ?? 'info'; ?>">
                                                            <?php echo ucfirst($statusText); ?>
                                                        </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($entry['response_code'])): ?>
                                                        <span class="badge <?php echo $entry['response_code'] >= 400 ? 'badge-danger' : 'badge-success'; ?>">
                                                                HTTP <?php echo $entry['response_code']; ?>
                                                            </span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($entry['execution_time_ms'])): ?>
                                                        <div class="text-muted small">
                                                            <i class="fas fa-stopwatch mr-1"></i>
                                                            <?php echo $entry['execution_time_ms']; ?>ms
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($entry['error_message'])): ?>
                                                        <div class="text-danger small mt-1">
                                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                                            <?php echo htmlspecialchars(substr($entry['error_message'], 0, 50)); ?>...
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                    <h4>No Access Logs Found</h4>
                                    <p class="text-muted">No access activities have been recorded for your account yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Showing <?php echo count($user_logs); ?> log entries
                                            </small>
                                        </div>
                                        <div>
                                            <small class="text-muted">
                                                <i class="fas fa-sync-alt mr-1"></i>
                                                Auto-refresh every 5 minutes
                                            </small>
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

<style>
    .info-box {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        transition: transform 0.2s ease;
    }

    .info-box:hover {
        transform: translateY(-2px);
    }

    .info-box-icon {
        border-radius: 0.25rem 0 0 0.25rem;
    }

    .table td {
        vertical-align: middle;
    }

    .badge {
        font-weight: 500;
    }

    code {
        background-color: #f8f9fa;
        padding: 2px 4px;
        border-radius: 3px;
        font-size: 0.875em;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }
</style>

<script>
    $(document).ready(function() {
        // Auto-refresh page every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000); // 5 minutes = 300000 milliseconds

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Copy IP address on click
        $('code').on('click', function() {
            const text = $(this).text();
            navigator.clipboard.writeText(text).then(() => {
                const original = $(this).html();
                $(this).html('<i class="fas fa-check text-success mr-1"></i>' + text);
                setTimeout(() => {
                    $(this).html(original);
                }, 2000);
            });
        });
    });
</script>