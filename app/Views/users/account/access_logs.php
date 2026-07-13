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
    </section>

    <!-- Enhanced Stats Row -->
    <section class="content mb-4">
        <div class="container-fluid">
            <div class="row">
                <!-- All Activities -->
                <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="card card-primary card-outline shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase text-muted mb-1">Total Activities</h6>
                                    <h2 class="mb-0"><?php echo $totalLogs ?? 0 ?></h2>
                                </div>
                                <div class="icon-circle bg-primary text-white">
                                    <i class="fas fa-history fa-2x"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">All recorded access activities</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Successful Logins -->
                <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="card card-success card-outline shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase text-muted mb-1">Successful</h6>
                                    <h2 class="mb-0"><?php echo $successfulLogins ?? 0 ?></h2>
                                </div>
                                <div class="icon-circle bg-success text-white">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <?php if ($totalLogs > 0): ?>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: <?php echo ($successfulLogins / $totalLogs * 100); ?>%"></div>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo round(($successfulLogins / $totalLogs * 100), 1); ?>% success rate
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">No activities recorded</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Failed Attempts -->
                <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="card card-danger card-outline shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase text-muted mb-1">Failed</h6>
                                    <h2 class="mb-0"><?php echo $failedAttempts ?? 0 ?></h2>
                                </div>
                                <div class="icon-circle bg-danger text-white">
                                    <i class="fas fa-times-circle fa-2x"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <?php if ($totalLogs > 0): ?>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-danger" style="width: <?php echo ($failedAttempts / $totalLogs * 100); ?>%"></div>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo round(($failedAttempts / $totalLogs * 100), 1); ?>% failure rate
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">No activities recorded</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Suspicious Activities -->
                <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="card card-warning card-outline shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase text-muted mb-1">Suspicious</h6>
                                    <h2 class="mb-0"><?php echo $suspiciousActivities ?? 0 ?></h2>
                                </div>
                                <div class="icon-circle bg-warning text-white">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <?php if ($totalLogs > 0): ?>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: <?php echo ($suspiciousActivities / $totalLogs * 100); ?>%"></div>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo round(($suspiciousActivities / $totalLogs * 100), 1); ?>% suspicious rate
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">No activities recorded</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content with Tabbed Navigation -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="logsTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($activeTab === 'all') ? 'active' : ''; ?>"
                                       href="<?php echo base_url('account/access_logs/all'); ?>">
                                        <i class="fas fa-list mr-2"></i>
                                        All Activity
                                        <span class="badge badge-light ml-2"><?php echo $totalLogs ?? 0; ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($activeTab === 'web') ? 'active' : ''; ?>"
                                       href="<?php echo base_url('account/access_logs/web'); ?>">
                                        <i class="fas fa-desktop mr-2"></i>
                                        Web Activities
                                        <span class="badge badge-primary ml-2"><?php echo $webLogsCount ?? 0; ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($activeTab === 'android') ? 'active' : ''; ?>"
                                       href="<?php echo base_url('account/access_logs/android'); ?>">
                                        <i class="fab fa-android mr-2"></i>
                                        Android Activities
                                        <span class="badge badge-success ml-2"><?php echo $androidLogsCount ?? 0; ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($activeTab === 'uploads') ? 'active' : '';?>"
                                       href="<?php echo base_url('account/access_logs/uploads'); ?>">
                                        <i class="fas fa-file-upload mr-2"></i>
                                        File Uploads
                                        <span class="badge badge-info ml-2"><?php echo $fileLogsCount ?? 0; ?></span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <!-- Tab Content -->
                            <div class="tab-content" id="logsTabsContent">
                                <!-- All Activity Tab -->
                                <div class="tab-pane fade <?php echo ($activeTab === 'all') ? 'show active' : ''; ?>"
                                     id="all" role="tabpanel">
                                    <?php echo renderLogsTable($user_logs, 'All Activities'); ?>
                                </div>

                                <!-- Web View Tab -->
                                <div class="tab-pane fade <?php echo ($activeTab === 'web') ? 'show active' : ''; ?>"
                                     id="web" role="tabpanel">
                                    <?php echo renderLogsTable($webLogs, 'Web Activities'); ?>
                                </div>

                                <!-- Android View Tab -->
                                <div class="tab-pane fade <?php echo ($activeTab === 'android') ? 'show active' : ''; ?>"
                                     id="android" role="tabpanel">
                                    <?php echo renderLogsTable($androidLogs, 'Android Activities'); ?>
                                </div>
                                 <div class="tab-pane fade <?php echo ($activeTab === 'uploads') ? 'show active' : ''; ?>" id="uploads" role="tabpanel">
                                     <?php echo renderLogsTable($fileLogs, 'File Uploads'); ?>
                                 </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Showing <?php echo count($user_logs); ?> log entries
                                            </small>
                                        </div>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="exportLogsBtn">
                                                <i class="fas fa-download mr-1"></i> Export
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="filterLogsBtn">
                                                <i class="fas fa-filter mr-1"></i> Filter
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="refreshLogsBtn">
                                                <i class="fas fa-sync-alt mr-1"></i> Refresh
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

<style>
    .icon-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card {
        border-radius: 10px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }

    .card-outline {
        border-top: 3px solid #007bff;
    }

    .nav-tabs.card-header-tabs .nav-link {
        border-radius: 0;
        padding: 15px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .nav-tabs.card-header-tabs .nav-link.active {
        background-color: #fff;
        border-bottom: 3px solid #007bff;
        color: #007bff;
    }

    .nav-tabs.card-header-tabs .nav-link:hover:not(.active) {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transform: scale(1.002);
        transition: transform 0.2s ease;
    }

    .badge-pill {
        border-radius: 10rem;
        padding: 5px 10px;
        font-size: 0.85em;
    }

    .list-group-item {
        border-left: 0;
        border-right: 0;
        transition: background-color 0.2s ease;
    }

    .list-group-item:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }

    .btn-group .btn {
        border-radius: 5px;
        margin: 0 2px;
    }
</style>

<script>
    $(document).ready(function() {
        // Export logs functionality
        $('#exportLogsBtn').click(function() {
            Swal.fire({
                title: 'Export Logs',
                text: 'Select export format:',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'CSV',
                cancelButtonText: 'JSON',
                showDenyButton: true,
                denyButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    exportLogs('csv');
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    exportLogs('json');
                }
            });
        });

        function exportLogs(format) {
            const table = $('.table');
            let data = [];

            table.find('tbody tr').each(function() {
                let row = {};
                $(this).find('td').each(function(i) {
                    const header = table.find('th').eq(i).text().trim();
                    row[header] = $(this).text().trim();
                });
                data.push(row);
            });

            if (format === 'csv') {
                exportToCSV(data, 'access_logs.csv');
            } else {
                exportToJSON(data, 'access_logs.json');
            }
        }

        function exportToCSV(data, filename) {
            const headers = Object.keys(data[0] || {});
            const csv = [
                headers.join(','),
                ...data.map(row => headers.map(header => JSON.stringify(row[header] || '')).join(','))
            ].join('\n');

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            window.URL.revokeObjectURL(url);

            showToast('Logs exported as CSV!', 'success');
        }

        function exportToJSON(data, filename) {
            const json = JSON.stringify(data, null, 2);
            const blob = new Blob([json], { type: 'application/json' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            window.URL.revokeObjectURL(url);

            showToast('Logs exported as JSON!', 'success');
        }

        // Refresh logs
        $('#refreshLogsBtn').click(function() {
            const btn = $(this);
            const original = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Refreshing...');

            setTimeout(() => {
                location.reload();
            }, 1000);
        });

        // Filter logs
        $('#filterLogsBtn').click(function() {
            Swal.fire({
                title: 'Filter Logs',
                html: `
                    <div class="text-left">
                        <div class="form-group">
                            <label for="filterDate">Date Range</label>
                            <select class="form-control" id="filterDate">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="filterStatus">Status</label>
                            <select class="form-control" id="filterStatus">
                                <option value="all">All Status</option>
                                <option value="success">Successful Only</option>
                                <option value="failed">Failed Only</option>
                                <option value="suspicious">Suspicious Only</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="filterCategory">Category</label>
                            <select class="form-control" id="filterCategory">
                                <option value="all">All Categories</option>
                                <option value="authentication">Authentication</option>
                                <option value="security">Security</option>
                                <option value="profile">Profile</option>
                                <option value="system">System</option>
                            </select>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Apply Filters',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    return {
                        date: $('#filterDate').val(),
                        status: $('#filterStatus').val(),
                        category: $('#filterCategory').val()
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    showToast('Filters applied!', 'success');
                    // In a real implementation, you would make an AJAX request here
                    console.log('Filters:', result.value);
                }
            });
        });

        // Auto-refresh page every 5 minutes
        setInterval(function() {
            showToast('Refreshing logs...', 'info');
            setTimeout(() => {
                location.reload();
            }, 1000);
        }, 300000); // 5 minutes

        // Copy IP address on click
        $('code').on('click', function() {
            const text = $(this).text();
            navigator.clipboard.writeText(text).then(() => {
                const original = $(this).html();
                $(this).html('<i class="fas fa-check text-success mr-1"></i>' + text);
                showToast('IP address copied!', 'success');
                setTimeout(() => {
                    $(this).html(original);
                }, 2000);
            });
        });

        // Toast notification function
        function showToast(message, type = 'info') {
            const toast = $(`
                <div class="toast fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 250px;">
                    <div class="toast-header bg-${type} text-white">
                        <strong class="mr-auto">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} mr-2"></i>
                            ${type.charAt(0).toUpperCase() + type.slice(1)}
                        </strong>
                        <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `);

            $('body').append(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>