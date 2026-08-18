<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-building text-danger mr-2"></i>Fleet Overview</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/home') ?>">Super Admin</a></li>
                        <li class="breadcrumb-item active">Fleet Overview</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Summary Cards -->
            <div class="row">
                <!-- Total Devices Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= number_format($total_devices) ?></h3>
                            <p>Total Devices</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <a href="#fleet-details" class="small-box-footer">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Active Devices Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= number_format($active_devices) ?></h3>
                            <p>Active (24h)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <a href="#fleet-details" class="small-box-footer">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Stale Devices Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= number_format($stale_devices) ?></h3>
                            <p>Stale (>7 days)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <a href="#fleet-details" class="small-box-footer">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Storage Usage Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $storage_summary['total_mb'] ?> MB</h3>
                            <p>Total Storage</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-hdd"></i>
                        </div>
                        <a href="#fleet-details" class="small-box-footer">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Fleet Details Section -->
            <div id="fleet-details" class="row">
                <!-- Data Category Distribution -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Data Distribution by Category</h3>
                        </div>
                        <div class="card-body">
                            <?php foreach ($data_by_category as $category): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><?= $category['label'] ?>:</span>
                                    <div class="d-flex align-items-center">
                                        <div class="progress mr-2" style="width: 100px; height: 8px;">
                                            <div class="progress-bar bg-primary" role="progressbar" 
                                                 style="width: <?= $category['percentage'] ?>%;"
                                                 aria-valuenow="<?= $category['percentage'] ?>" 
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small><?= $category['count'] ?> (<?= $category['percentage'] ?>%)</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if ($data_by_category['Files']['storage'] > 0 || $data_by_category['Apps']['storage'] > 0): ?>
                                <hr>
                                <h6>Storage Summary:</h6>
                                <div class="d-flex justify-content-between">
                                    <span>FilesController Storage:</span>
                                    <span><?= number_format(floor($data_by_category['Files']['storage'] / (1024*1024)), 2) ?> MB</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Apps Storage:</span>
                                    <span><?= number_format(floor($data_by_category['Apps']['storage'] / (1024*1024)), 2) ?> MB</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Devices Per User -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-users mr-2"></i>Devices Per User (Top 10)</h3>
                        </div>
                        <div class="card-body">
                            <?php foreach ($devices_per_user as $user): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong><?= htmlspecialchars($user['username']) ?> (ID: <?= $user['id'] ?>)</strong>
                                        <br>
                                        <small class="text-success">Active: <?= $user['active_count'] ?></small> | 
                                        <small class="text-warning">Stale: <?= $user['stale_count'] ?></small>
                                    </div>
                                    <span class="badge badge-primary badge-pill"><?= $user['device_count'] ?> devices</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- AdvancedController Statistics -->
            <div class="row mt-4">
                <!-- Activity Trends -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Activity Trends (Last 30 Days)</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <h6>Daily Syncs:</h6>
                                    <?php if (!empty($activity_trends['daily_syncs'])): ?>
                                        <?php foreach (array_slice($activity_trends['daily_syncs'], -7) as $day): ?>
                                            <div class="d-flex justify-content-between mb-1">
                                                <small><?= date('M d', strtotime($day['date'])) ?>:</small>
                                                <small><?= $day['sync_count'] ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted">No sync data available</p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-6">
                                    <h6>Daily Data Extracts:</h6>
                                    <?php if (!empty($activity_trends['daily_extracts'])): ?>
                                        <?php foreach (array_slice($activity_trends['daily_extracts'], -7) as $day): ?>
                                            <div class="d-flex justify-content-between mb-1">
                                                <small><?= date('M d', strtotime($day['date'])) ?>:</small>
                                                <small><?= $day['extract_count'] ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted">No extract data available</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Device Types -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-mobile-alt mr-2"></i>Device Types</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($device_types['os_versions'])): ?>
                                <h6>Top OS Versions:</h6>
                                <?php foreach ($device_types['os_versions'] as $os => $count): ?>
                                    <div class="d-flex justify-content-between mb-1">
                                        <small><?= htmlspecialchars($os) ?></small>
                                        <small><?= $count ?> devices</small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <?php if (!empty($device_types['brands'])): ?>
                                <h6 class="mt-3">Top Brands:</h6>
                                <?php foreach ($device_types['brands'] as $brand => $count): ?>
                                    <div class="d-flex justify-content-between mb-1">
                                        <small><?= htmlspecialchars($brand) ?></small>
                                        <small><?= $count ?> devices</small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-cogs mr-2"></i>Fleet Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <button class="btn btn-block btn-info" onclick="window.location='<?= base_url('admin/remote-device') ?>';">
                                        <i class="fas fa-mobile-alt mr-1"></i> Remote Device Console
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-block btn-success" onclick="window.location='<?= base_url('superadmin/omni-search') ?>';">
                                        <i class="fas fa-search mr-1"></i> Omni Search
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-block btn-warning" onclick="window.location='<?= base_url('admin/reports') ?>';">
                                        <i class="fas fa-chart-bar mr-1"></i> Reports
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-block btn-danger" onclick="window.location='<?= base_url('superadmin/users') ?>';">
                                        <i class="fas fa-users mr-1"></i> Role Matrix
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>