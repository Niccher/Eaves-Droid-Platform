<?php /** @var array $counts */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <h1 class="h2 mb-0"><i class="fas fa-microchip text-secondary mr-2"></i>Hardware</h1>
                    <p class="text-muted mt-1 mb-0">Device hardware information and sensor data</p>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-battery-three-quarters fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Device Metrics</h6>
                            <p class="text-muted small mb-2">CPU, memory, disk, and uptime information</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_device'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/device'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-wifi fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Network Info</h6>
                            <p class="text-muted small mb-2">Wi-Fi, IP addresses, and network configuration</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_network'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/network'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fab fa-bluetooth-b fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Bluetooth</h6>
                            <p class="text-muted small mb-2">Paired and connected Bluetooth devices</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_bluetooth'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/bluetooth'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-microchip fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Sensors</h6>
                            <p class="text-muted small mb-2">Light, gravity, accelerometer, and gyroscope readings</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_sensors'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/sensors'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-camera fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Camera Info</h6>
                            <p class="text-muted small mb-2">Camera specifications and captured media details</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_camera_info'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/camera_info'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-battery-full fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Battery Stats</h6>
                            <p class="text-muted small mb-2">Battery level, health, charging status, and capacity</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_battery_stats'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/battery_stats'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-microchip fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Proc Info</h6>
                            <p class="text-muted small mb-2">CPU, memory, kernel, uptime, and network interfaces</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_proc_info'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/proc_info'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-cogs fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Running Processes</h6>
                            <p class="text-muted small mb-2">Active processes with CPU, memory, and PIDs</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_processes'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/processes'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-sim-card fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">SIM Configs</h6>
                            <p class="text-muted small mb-2">SIM card parameters, operator, and network settings</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_sim_configs'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/sim-configs'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-signal fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Cell Towers</h6>
                            <p class="text-muted small mb-2">Neighboring cell scans with CID, LAC, RSSI</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_cell_towers'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/cell_towers'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-desktop fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Display Info</h6>
                            <p class="text-muted small mb-2">Resolution, density, refresh rate, multi-display</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_display_info'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/display_info'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-hdd fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Storage</h6>
                            <p class="text-muted small mb-2">Internal/external volumes and usage</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_storage'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/storage'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-thermometer-half fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Thermal</h6>
                            <p class="text-muted small mb-2">CPU/GPU temps, throttling, frequency scaling</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_thermal'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/thermal'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-mobile-alt fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">NFC</h6>
                            <p class="text-muted small mb-2">Adapter state, Secure NFC, features</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_nfc'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/nfc'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>