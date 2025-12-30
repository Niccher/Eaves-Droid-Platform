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
                                <?php echo $apps_head ?? 'Installed Apps' ?>
                            </h1>
                            <div class="ml-3">
                                <span class="badge badge-light border p-2">
                                    <i class="fas fa-boxes text-primary mr-1"></i>
                                    Total: <b><?php echo count($apps_dump) ?? 0 ?></b>
                                </span>
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0">View and manage apps installed on the device</p>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="float-right mt-2">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <?php echo $apps_urls; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <!-- Main Card -->
                        <div class="card card-secondary shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-th-large mr-2"></i>
                                    Applications
                                    <small class="text-muted ml-2">Showing <?php echo count($apps_dump) ?> of <?php echo count($apps_dump) ?> apps</small>
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <div class="btn-group ml-2">
                                        <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="#"><i class="fas fa-download mr-2"></i> Export</a>
                                            <a class="dropdown-item" href="#"><i class="fas fa-print mr-2"></i> Print</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#"><i class="fas fa-cog mr-2"></i> Settings</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead class="thead-light">
                                        <tr>
                                            <th width="35%">App Name</th>
                                            <th width="35%">Package Name</th>
                                            <th width="15%">Status</th>
                                            <th width="15%">App Code</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (empty($apps_dump)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-5">
                                                    <div class="empty-state">
                                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                                        <h4>No apps found</h4>
                                                        <p class="text-muted">No applications are installed on the device</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($apps_dump as $app): ?>
                                                <?php
                                                // Generate app icon based on first letter of app name
                                                $appName = $app['Name'];
                                                $firstLetter = strtoupper(substr($appName, 0, 1));

                                                // Color scheme for app icons
                                                $colorSchemes = [
                                                    'primary' => ['bg' => 'bg-primary', 'text' => 'text-white'],
                                                    'success' => ['bg' => 'bg-success', 'text' => 'text-white'],
                                                    'info' => ['bg' => 'bg-info', 'text' => 'text-white'],
                                                    'warning' => ['bg' => 'bg-warning', 'text' => 'text-dark'],
                                                    'danger' => ['bg' => 'bg-danger', 'text' => 'text-white'],
                                                    'purple' => ['bg' => 'bg-purple', 'text' => 'text-white'],
                                                    'pink' => ['bg' => 'bg-pink', 'text' => 'text-white'],
                                                    'teal' => ['bg' => 'bg-teal', 'text' => 'text-white'],
                                                    'orange' => ['bg' => 'bg-orange', 'text' => 'text-white'],
                                                    'indigo' => ['bg' => 'bg-indigo', 'text' => 'text-white']
                                                ];

                                                $colorKeys = array_keys($colorSchemes);
                                                $colorIndex = crc32($appName) % count($colorKeys);
                                                $selectedColor = $colorKeys[$colorIndex];
                                                $iconBg = $colorSchemes[$selectedColor]['bg'];
                                                $iconTextColor = $colorSchemes[$selectedColor]['text'];

                                                // Determine app category/type based on package name or keywords
                                                $packageName = strtolower($app['Package']);
                                                $appType = 'general';
                                                $typeIcon = 'cube';

                                                if (strpos($packageName, 'com.google') !== false) {
                                                    $appType = 'google';
                                                    $typeIcon = 'google';
                                                } elseif (strpos($packageName, 'com.facebook') !== false ||
                                                    strpos($packageName, 'com.instagram') !== false ||
                                                    strpos($packageName, 'com.twitter') !== false) {
                                                    $appType = 'social';
                                                    $typeIcon = 'users';
                                                } elseif (strpos($packageName, 'com.android') !== false) {
                                                    $appType = 'system';
                                                    $typeIcon = 'cogs';
                                                } elseif (strpos($packageName, 'game') !== false ||
                                                    strpos($appName, 'Game') !== false) {
                                                    $appType = 'game';
                                                    $typeIcon = 'gamepad';
                                                } elseif (strpos($packageName, 'music') !== false ||
                                                    strpos($appName, 'Music') !== false ||
                                                    strpos($appName, 'Player') !== false) {
                                                    $appType = 'media';
                                                    $typeIcon = 'music';
                                                } elseif (strpos($packageName, 'camera') !== false ||
                                                    strpos($appName, 'Camera') !== false ||
                                                    strpos($appName, 'Photo') !== false) {
                                                    $appType = 'camera';
                                                    $typeIcon = 'camera';
                                                }

                                                // Status badge configuration
                                                $statusConfig = [
                                                    'Active' => [
                                                        'icon' => 'check-circle',
                                                        'color' => 'success',
                                                        'bg' => 'bg-success-light',
                                                        'text' => 'text-success'
                                                    ],
                                                    'Inactive' => [
                                                        'icon' => 'pause-circle',
                                                        'color' => 'warning',
                                                        'bg' => 'bg-warning-light',
                                                        'text' => 'text-warning'
                                                    ],
                                                    'System' => [
                                                        'icon' => 'shield-alt',
                                                        'color' => 'info',
                                                        'bg' => 'bg-info-light',
                                                        'text' => 'text-info'
                                                    ],
                                                    'Disabled' => [
                                                        'icon' => 'ban',
                                                        'color' => 'danger',
                                                        'bg' => 'bg-danger-light',
                                                        'text' => 'text-danger'
                                                    ]
                                                ];

                                                // Default to Active status
                                                $status = 'Active';
                                                $statusInfo = $statusConfig[$status];
                                                ?>

                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-3">
                                                                <div class="avatar-circle-sm <?php echo $iconBg; ?> <?php echo $iconTextColor; ?> shadow-sm">
                                                                    <?php echo $firstLetter; ?>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="text-dark font-weight-bold"><?php echo htmlspecialchars($appName); ?></div>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-<?php echo $typeIcon; ?> mr-1 text-primary"></i>
                                                                    <?php echo ucfirst($appType); ?> App
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="package-name">
                                                            <code class="text-dark bg-light p-2 rounded d-inline-block">
                                                                <?php echo htmlspecialchars($app['Package']); ?>
                                                            </code>
                                                            <div class="mt-1">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-info-circle mr-1"></i>
                                                                    Package identifier
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?php echo $statusInfo['bg']; ?> <?php echo $statusInfo['text']; ?> p-2">
                                                            <i class="fas fa-<?php echo $statusInfo['icon']; ?> mr-1"></i>
                                                            <?php echo $status; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="app-code">
                                                            <span class="badge badge-light border p-2 font-monospace">
                                                                <i class="fas fa-hashtag mr-1 text-secondary"></i>
                                                                <?php echo htmlspecialchars($app['Code']); ?>
                                                            </span>
                                                            <div class="mt-1">
                                                                <small class="text-muted">Unique ID</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="dataTables_info" role="status">
                                            Showing 1 to <?php echo count($apps_dump) ?>
                                            of <?php echo count($apps_dump) ?> entries
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="float-right">
                                            <?php if (isset($pager) && $totalApps > $perPage): ?>
                                                <?php echo $pager->links('bootstrap5_full', 'bootstrap5_full'); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <style>
        .avatar-circle-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
        }

        .empty-state i {
            opacity: 0.5;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .package-name code {
            font-size: 0.85rem;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .app-code .badge {
            min-width: 80px;
            font-size: 0.9rem;
        }

        /* Extended Bootstrap colors */
        .bg-purple { background-color: #6f42c1 !important; }
        .bg-pink { background-color: #e83e8c !important; }
        .bg-teal { background-color: #20c997 !important; }
        .bg-orange { background-color: #fd7e14 !important; }
        .bg-indigo { background-color: #6610f2 !important; }

        /* Light background variants for status badges */
        .bg-success-light { background-color: rgba(40, 167, 69, 0.1) !important; }
        .bg-warning-light { background-color: rgba(255, 193, 7, 0.1) !important; }
        .bg-info-light { background-color: rgba(23, 162, 184, 0.1) !important; }
        .bg-danger-light { background-color: rgba(220, 53, 69, 0.1) !important; }
        .bg-primary-light { background-color: rgba(0, 123, 255, 0.1) !important; }

        /* Hover effects */
        .avatar-circle-sm:hover {
            transform: scale(1.1);
            transition: transform 0.2s ease;
        }

        tr:hover .package-name code {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        /* Font for monospace */
        .font-monospace {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
        }

        @media (max-width: 768px) {
            .dataTables_info {
                text-align: center;
                margin-bottom: 1rem;
            }

            .float-right {
                float: none !important;
                text-align: center;
            }

            .avatar-circle-sm {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }

            .package-name code {
                font-size: 0.75rem;
                max-width: 200px;
            }

            .app-code .badge {
                min-width: 60px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .card-header .card-title {
                font-size: 1.1rem;
            }

            .package-name code {
                max-width: 150px;
            }
        }
    </style>