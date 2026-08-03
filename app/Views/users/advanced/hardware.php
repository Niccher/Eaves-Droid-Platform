<?php /** @var array $counts */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <h1 class="h2 mb-0"><i class="fas fa-microchip text-secondary mr-2"></i>Hardware</h1>
                    <p class="text-muted mt-1 mb-0">Individual hardware data categories — click to view snapshots</p>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Bluetooth -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-bluetooth-b fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Bluetooth</h6>
                            <p class="text-muted small mb-2">Adapter, paired devices, enabled state</p>
                            <span class="badge badge-pill badge-primary"><?php echo $counts['total_bluetooth'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/bluetooth'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Network Interfaces -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-info shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-info d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-network-wired fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Network Interfaces</h6>
                            <p class="text-muted small mb-2">Interfaces, routes, ARP, WiFi Passpoint</p>
                            <span class="badge badge-pill badge-info"><?php echo $counts['total_hardware_network'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/hardware_network'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- NFC -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-info shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-info d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-credit-card fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">NFC</h6>
                            <p class="text-muted small mb-2">Available, enabled, secure element</p>
                            <span class="badge badge-pill badge-info"><?php echo $counts['total_nfc'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/nfc'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Cell Towers -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-orange shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-orange d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-broadcast-tower fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Cell Towers</h6>
                            <p class="text-muted small mb-2">GSM/LTE/NR, signal strength, operator</p>
                            <span class="badge badge-pill badge-orange"><?php echo $counts['total_cell_towers'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/cell_towers'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Battery -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-success shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-battery-three-quarters fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Battery</h6>
                            <p class="text-muted small mb-2">Level, charging, health, temperature</p>
                            <span class="badge badge-pill badge-success"><?php echo $counts['total_battery_stats'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/battery_stats'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Power Rails -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-orange shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-orange d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-bolt fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Power Rails</h6>
                            <p class="text-muted small mb-2">Voltage, current, power per rail</p>
                            <span class="badge badge-pill badge-orange"><?php echo $counts['total_power_rails'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/power_rails'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Thermal -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-danger shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-danger d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-thermometer-half fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Thermal</h6>
                            <p class="text-muted small mb-2">Temperature zones, throttling, governors</p>
                            <span class="badge badge-pill badge-danger"><?php echo $counts['total_thermal'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/thermal'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Sensors -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-warning shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-warning d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-ruler fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Sensors</h6>
                            <p class="text-muted small mb-2">Accelerometer, gyro, magnetometer, etc.</p>
                            <span class="badge badge-pill badge-warning"><?php echo $counts['total_sensor_profile'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/sensors'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Graphics (GPU) -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-purple shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-purple d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-desktop fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Graphics (GPU)</h6>
                            <p class="text-muted small mb-2">GPU renderer, media codecs, input devices</p>
                            <span class="badge badge-pill badge-purple"><?php echo $counts['total_hardware_graphics'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/hardware_graphics'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Audio Devices -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-purple shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-purple d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-volume-up fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Audio Devices</h6>
                            <p class="text-muted small mb-2">Sinks, sources, sample rates, latency</p>
                            <span class="badge badge-pill badge-purple"><?php echo $counts['total_audio_devices'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/audio_devices'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Camera -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-pink shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-pink d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-camera fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Camera</h6>
                            <p class="text-muted small mb-2">Capabilities, focal lengths, HDR, video modes</p>
                            <span class="badge badge-pill badge-pink"><?php echo $counts['total_camera_info'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/camera_info'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Display -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-pink shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-pink d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-tv fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Display</h6>
                            <p class="text-muted small mb-2">Resolution, DPI, refresh rate, rotation</p>
                            <span class="badge badge-pill badge-pink"><?php echo $counts['total_display_info'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/display_info'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Storage -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-teal shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-teal d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-hdd fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Storage</h6>
                            <p class="text-muted small mb-2">Volumes, capacity, available space</p>
                            <span class="badge badge-pill badge-teal"><?php echo $counts['total_storage'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/storage'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- USB Devices -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-teal shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-teal d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-usb fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">USB Devices</h6>
                            <p class="text-muted small mb-2">Connected peripherals, power, speed</p>
                            <span class="badge badge-pill badge-teal"><?php echo $counts['total_usb_devices'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/usb_devices'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- GNSS / GPS -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-warning shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-warning d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-satellite fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">GNSS / GPS</h6>
                            <p class="text-muted small mb-2">Constellations, antenna, AGPS, raw measurements</p>
                            <span class="badge badge-pill badge-warning"><?php echo $counts['total_gnss_hardware'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/gnss_hardware'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Biometric -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-danger shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-danger d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-fingerprint fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Biometric</h6>
                            <p class="text-muted small mb-2">Fingerprint, face, iris sensors</p>
                            <span class="badge badge-pill badge-danger"><?php echo $counts['total_biometric'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/biometric'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Running Processes -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-dark shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-dark d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-tasks fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Running Processes</h6>
                            <p class="text-muted small mb-2">Process snapshots</p>
                            <span class="badge badge-pill badge-dark"><?php echo $counts['total_processes'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/processes'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Vibration -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-dark shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-dark d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-wave-square fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Vibration</h6>
                            <p class="text-muted small mb-2">Actuator type, amplitude, frequency</p>
                            <span class="badge badge-pill badge-dark"><?php echo $counts['total_vibration'] ?? 0; ?> snapshots</span>
                            <a href="<?php echo base_url('advanced/hardware/vibration'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Device Context -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-mobile-alt fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Device Context</h6>
                            <p class="text-muted small mb-2">Battery level, clipboard snapshot, locale, timezone</p>
                            <span class="badge badge-pill badge-primary"><?php echo $counts['total_device'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/device'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- SIM Configs -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-sim-card fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">SIM Configs</h6>
                            <p class="text-muted small mb-2">SIM card configuration, ICCID, carrier, slot info</p>
                            <span class="badge badge-pill badge-secondary"><?php echo $counts['total_sim_configs'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/sim-configs'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <!-- Proc Info -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card card-outline card-info shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-info d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                <i class="fas fa-terminal fa-2x text-white"></i>
                            </div>
                            <h6 class="card-title mb-1">Proc Info</h6>
                            <p class="text-muted small mb-2">Kernel /proc dump, memory, cpuinfo, mounts</p>
                            <span class="badge badge-pill badge-info"><?php echo $counts['total_proc_info'] ?? 0; ?> records</span>
                            <a href="<?php echo base_url('advanced/hardware/proc_info'); ?>" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>