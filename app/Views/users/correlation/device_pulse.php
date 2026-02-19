<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-8 col-md-6">
                        <div class="d-flex align-items-center">
                            <h1 class="h2 mb-0">
                                <i class="fas fa-heartbeat text-primary mr-2"></i>
                                Device Pulse
                            </h1>
                            <div class="ml-3">
                                <?php if (!empty($device)): ?>
                                    <span class="badge badge-success border p-2">
                                        <i class="fas fa-signal mr-1"></i> Online
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary border p-2">
                                        <i class="fas fa-wifi-slash mr-1"></i> Offline
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0">Real-time health monitoring and system status</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                
                <?php if (empty($device)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-1"></i> No device profile data found for this user.
                    </div>
                <?php else: ?>

                <!-- Top Stats -->
                <div class="row">
                    <!-- Battery -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-white shadow-sm">
                            <div class="inner">
                                <h3><?= $device['battery_level'] ?? 0 ?><sup style="font-size: 20px">%</sup></h3>
                                <p>Battery Level</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-battery-three-quarters text-success"></i>
                            </div>
                        </div>
                    </div>
                    <!-- Storage -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-white shadow-sm">
                            <div class="inner">
                                <h3><?= floor(($device['internal_storage_free_gb'] ?? 0) / ($device['internal_storage_total_gb'] ?? 1) * 100) ?><sup style="font-size: 20px">%</sup></h3>
                                <p>Free Storage</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-hdd text-info"></i>
                            </div>
                        </div>
                    </div>
                    <!-- RAM -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-white shadow-sm">
                            <div class="inner">
                                <h3><?= floor(($device['memory_free_mb'] ?? 0) / 1024) ?> <sup style="font-size: 20px">GB</sup></h3>
                                <p>Free RAM</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-microchip text-warning"></i>
                            </div>
                        </div>
                    </div>
                    <!-- Network -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-white shadow-sm">
                            <div class="inner">
                                <h3><?= ucfirst($device['network_type'] ?? ($device['network_operator'] ?? 'N/A')) ?></h3>
                                <p>Network</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-broadcast-tower text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Info -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-mobile-alt mr-1"></i> Device Specs</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <td>Model</td>
                                            <td><b><?= $device['device_model'] ?? 'N/A' ?></b></td>
                                        </tr>
                                        <tr>
                                            <td>Manufacturer</td>
                                            <td><?= $device['device_manufacturer'] ?? 'N/A' ?></td>
                                        </tr>
                                        <tr>
                                            <td>Android Version</td>
                                            <td><?= $device['android_version'] ?? 'N/A' ?> (SDK <?= $device['android_sdk_int'] ?? 'N/A' ?>)</td>
                                        </tr>
                                        <tr>
                                            <td>Device ID</td>
                                            <td><?= $device['android_id'] ?? 'N/A' ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> Security Status</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <td>Rooted</td>
                                            <td>
                                                <?php if (($device['is_rooted'] ?? 0) == 1): ?>
                                                    <span class="badge badge-danger">YES</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">NO</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Emulator</td>
                                            <td>
                                                <?php if (($device['is_emulator'] ?? 0) == 1): ?>
                                                    <span class="badge badge-warning">YES</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">NO</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>IP Address (Device)</td>
                                            <td><?= $device['device_ip_address'] ?? 'N/A' ?></td>
                                        </tr>
                                        <tr>
                                            <td>Last IP (Access Log)</td>
                                            <td><?= $device['last_ip_address'] ?? 'N/A' ?></td>
                                        </tr>
                                        <tr>
                                            <td>Last Login</td>
                                            <td><?= isset($device['last_login_time']) ? date('M d, Y H:i', strtotime($device['last_login_time'])) : 'N/A' ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                         <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i> 
                            <b>Note:</b> This data is not real-time. It represents the state at the time of the last extraction 
                            (<?= isset($device['last_activity_time']) ? date('M d, Y H:i:s', $device['last_activity_time'] / 1000) : 'Unknown' ?>).
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </section>
    </div>
