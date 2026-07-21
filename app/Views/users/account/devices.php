<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex align-items-center">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-microchip text-primary mr-2"></i>
                            Device Fingerprint
                        </h1>
                        <div class="ml-3">
                            <span class="badge badge-light border p-2">
                                <i class="fas fa-mobile-alt text-primary mr-1"></i>
                                Devices: <b><?php echo count($device_profiles ?? []); ?></b>
                            </span>
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Full device fingerprint information collected from registered devices</p>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($device_profiles)): ?>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <ul class="nav nav-pills" id="devicePills" role="tablist">
                            <?php foreach ($device_profiles as $i => $dp): ?>
                                <?php
                                    $deviceLabel = ($dp['device_brand'] ?? 'Unknown') . ' ' . ($dp['device_model'] ?? 'Device');
                                    $deviceId = 'device-' . $i;
                                ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $i === 0 ? 'active' : ''; ?>"
                                       id="<?php echo $deviceId; ?>-tab"
                                       data-toggle="pill"
                                       href="#<?php echo $deviceId; ?>"
                                       role="tab">
                                        <i class="fas fa-mobile-alt mr-1"></i>
                                        <?php echo htmlspecialchars($deviceLabel); ?>
                                        <?php if (!empty($dp['android_version'])): ?>
                                            <span class="badge badge-light ml-1"><?php echo htmlspecialchars($dp['android_version']); ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="tab-content" id="devicePillsContent">
                    <?php foreach ($device_profiles as $i => $dp): ?>
                        <?php $deviceId = 'device-' . $i; ?>
                        <div class="tab-pane fade <?php echo $i === 0 ? 'show active' : ''; ?>"
                             id="<?php echo $deviceId; ?>"
                             role="tabpanel">

                            <!-- Summary Row -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-primary card-outline shadow-sm h-100">
                                        <div class="card-body text-center">
                                            <div class="mb-2"><i class="fas fa-mobile-alt fa-3x text-primary"></i></div>
                                            <h6 class="text-muted mb-1">Device</h6>
                                            <strong><?php echo htmlspecialchars($dp['device_brand'] ?? 'N/A'); ?> <?php echo htmlspecialchars($dp['device_model'] ?? ''); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($dp['device_manufacturer'] ?? ''); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-success card-outline shadow-sm h-100">
                                        <div class="card-body text-center">
                                            <div class="mb-2"><i class="fab fa-android fa-3x text-success"></i></div>
                                            <h6 class="text-muted mb-1">Android</h6>
                                            <strong><?php echo htmlspecialchars($dp['android_version'] ?? 'N/A'); ?></strong>
                                            <br><small class="text-muted">SDK <?php echo htmlspecialchars($dp['android_sdk_int'] ?? '?'); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-info card-outline shadow-sm h-100">
                                        <div class="card-body text-center">
                                            <div class="mb-2"><i class="fas fa-microchip fa-3x text-info"></i></div>
                                            <h6 class="text-muted mb-1">Hardware</h6>
                                            <strong><?php echo htmlspecialchars($dp['device_hardware'] ?? 'N/A'); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($dp['device_board'] ?? ''); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-secondary card-outline shadow-sm h-100">
                                        <div class="card-body text-center">
                                            <div class="mb-2"><i class="fas fa-shield-alt fa-3x text-secondary"></i></div>
                                            <h6 class="text-muted mb-1">Security</h6>
                                            <?php $rooted = !empty($dp['is_rooted']); ?>
                                            <strong class="<?php echo $rooted ? 'text-danger' : 'text-success'; ?>">
                                                <?php echo $rooted ? 'Rooted' : 'Secure'; ?>
                                            </strong>
                                            <br><small class="text-muted"><?php echo !empty($dp['is_emulator']) ? 'Emulator' : 'Physical Device'; ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Device Identity -->
                            <div class="card card-primary shadow-sm mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="fas fa-id-card mr-2"></i>Device Identity</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:180px;">Device ID</td><td><code><?php echo htmlspecialchars($dp['device_id'] ?? 'N/A'); ?></code></td></tr>
                                                <tr><td class="text-muted">Android ID</td><td><code><?php echo htmlspecialchars($dp['android_id'] ?? 'N/A'); ?></code></td></tr>
                                                <tr><td class="text-muted">Model</td><td><?php echo htmlspecialchars($dp['device_model'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Brand</td><td><?php echo htmlspecialchars($dp['device_brand'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Manufacturer</td><td><?php echo htmlspecialchars($dp['device_manufacturer'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Product</td><td><?php echo htmlspecialchars($dp['device_product'] ?? 'N/A'); ?></td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:180px;">Device Name</td><td><?php echo htmlspecialchars($dp['device_device'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Board</td><td><?php echo htmlspecialchars($dp['device_board'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Hardware</td><td><?php echo htmlspecialchars($dp['device_hardware'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Serial</td><td><code><?php echo htmlspecialchars($dp['build_serial'] ?? 'N/A'); ?></code></td></tr>
                                                <tr><td class="text-muted">MAC Address</td><td><code><?php echo htmlspecialchars($dp['mac_address'] ?? 'N/A'); ?></code></td></tr>
                                                <tr><td class="text-muted">IP Address</td><td><?php echo htmlspecialchars($dp['device_ip_address'] ?? 'N/A'); ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Android OS & Build -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-info shadow-sm mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0"><i class="fab fa-android mr-2"></i>Android OS</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Version</td><td><?php echo htmlspecialchars($dp['android_version'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">SDK</td><td><?php echo htmlspecialchars($dp['android_sdk_int'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Codename</td><td><?php echo htmlspecialchars($dp['android_codename'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Incremental</td><td><?php echo htmlspecialchars($dp['android_incremental'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Base OS</td><td><?php echo htmlspecialchars($dp['android_base_os'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Security Patch</td><td><?php echo htmlspecialchars($dp['android_security_patch'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Bootloader</td><td><?php echo htmlspecialchars($dp['bootloader'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Radio</td><td><?php echo htmlspecialchars($dp['radio_version'] ?? 'N/A'); ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-warning shadow-sm mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0"><i class="fas fa-cube mr-2"></i>Build</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Build ID</td><td><?php echo htmlspecialchars($dp['build_id'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Type</td><td><?php echo htmlspecialchars($dp['build_type'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Tags</td><td><?php echo htmlspecialchars($dp['build_tags'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Fingerprint</td><td><small><code><?php echo htmlspecialchars($dp['build_fingerprint'] ?? 'N/A'); ?></code></small></td></tr>
                                                <tr><td class="text-muted">Display</td><td><small><?php echo htmlspecialchars($dp['build_display'] ?? 'N/A'); ?></small></td></tr>
                                                <tr><td class="text-muted">User</td><td><?php echo htmlspecialchars($dp['build_user'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Host</td><td><?php echo htmlspecialchars($dp['build_host'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Build Time</td><td><?php echo !empty($dp['build_time']) ? date('Y-m-d H:i:s', $dp['build_time'] / 1000) : 'N/A'; ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Display & CPU/Memory -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-light shadow-sm mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0"><i class="fas fa-tv mr-2"></i>Display</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Resolution</td><td><?php echo htmlspecialchars($dp['display_width'] ?? '?'); ?>x<?php echo htmlspecialchars($dp['display_height'] ?? '?'); ?></td></tr>
                                                <tr><td class="text-muted">Density</td><td><?php echo htmlspecialchars($dp['display_density'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($dp['display_density_dpi'] ?? '?'); ?> dpi)</td></tr>
                                                <tr><td class="text-muted">Screen Size</td><td><?php echo htmlspecialchars($dp['screen_size_inches'] ?? 'N/A'); ?>"</td></tr>
                                                <tr><td class="text-muted">Refresh Rate</td><td><?php echo htmlspecialchars($dp['display_refresh_rate'] ?? 'N/A'); ?> Hz</td></tr>
                                                <tr><td class="text-muted">Mode</td><td><?php echo htmlspecialchars($dp['display_mode_width'] ?? '?'); ?>x<?php echo htmlspecialchars($dp['display_mode_height'] ?? '?'); ?> @ <?php echo htmlspecialchars($dp['display_mode_refresh'] ?? '?'); ?>Hz</td></tr>
                                                <tr><td class="text-muted">XDpi / YDpi</td><td><?php echo htmlspecialchars($dp['display_xdpi'] ?? 'N/A'); ?> / <?php echo htmlspecialchars($dp['display_ydpi'] ?? 'N/A'); ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-danger shadow-sm mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0"><i class="fas fa-microchip mr-2"></i>CPU & Memory</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">CPU Cores</td><td><?php echo htmlspecialchars($dp['cpu_cores'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">ABI</td><td><?php echo htmlspecialchars($dp['cpu_abi'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">ABIs</td><td><?php echo htmlspecialchars($dp['cpu_abis'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">CPU Detail</td><td><small><?php echo htmlspecialchars($dp['cpu_detail'] ?? 'N/A'); ?></small></td></tr>
                                                <tr><td class="text-muted">Total RAM</td><td><?php echo htmlspecialchars($dp['memory_total_mb'] ?? 'N/A'); ?> MB</td></tr>
                                                <tr><td class="text-muted">Free RAM</td><td><?php echo htmlspecialchars($dp['memory_free_mb'] ?? 'N/A'); ?> MB</td></tr>
                                                <tr><td class="text-muted">Available RAM</td><td><?php echo htmlspecialchars($dp['memory_available_mb'] ?? 'N/A'); ?> MB</td></tr>
                                                <tr><td class="text-muted">Max Heap</td><td><?php echo htmlspecialchars($dp['max_heap_mb'] ?? 'N/A'); ?> MB</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Storage -->
                            <div class="card card-secondary shadow-sm mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="fas fa-database mr-2"></i>Storage</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:180px;">Internal Total</td><td><?php echo htmlspecialchars($dp['internal_storage_total_gb'] ?? 'N/A'); ?> GB</td></tr>
                                                <tr><td class="text-muted">Internal Free</td><td><?php echo htmlspecialchars($dp['internal_storage_free_gb'] ?? 'N/A'); ?> GB</td></tr>
                                                <tr><td class="text-muted">Internal Used</td><td><?php echo htmlspecialchars($dp['internal_storage_used_gb'] ?? 'N/A'); ?> GB</td></tr>
                                                <tr><td class="text-muted">Internal Usable</td><td><?php echo htmlspecialchars($dp['internal_storage_usable_gb'] ?? 'N/A'); ?> GB</td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:180px;">External Total</td><td><?php echo htmlspecialchars($dp['external_storage_total_gb'] ?? 'N/A'); ?> GB</td></tr>
                                                <tr><td class="text-muted">External Free</td><td><?php echo htmlspecialchars($dp['external_storage_free_gb'] ?? 'N/A'); ?> GB</td></tr>
                                                <tr><td class="text-muted">External Used</td><td><?php echo htmlspecialchars($dp['external_storage_used_gb'] ?? 'N/A'); ?> GB</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Network & Telephony -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-info shadow-sm mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0"><i class="fas fa-wifi mr-2"></i>Network & Telephony</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">SIM Operator</td><td><?php echo htmlspecialchars($dp['sim_operator'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Network Operator</td><td><?php echo htmlspecialchars($dp['network_operator'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">SIM Country</td><td><?php echo htmlspecialchars($dp['sim_country'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Network Country</td><td><?php echo htmlspecialchars($dp['network_country'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">SIM State</td><td><?php echo htmlspecialchars($dp['sim_state'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Phone Number</td><td><?php echo htmlspecialchars($dp['phone_number'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">IMEI</td><td><code><?php echo htmlspecialchars($dp['imei'] ?? 'N/A'); ?></code></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-light shadow-sm mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0"><i class="fas fa-battery-three-quarters mr-2"></i>Battery & Sensors</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Level</td><td><?php echo htmlspecialchars($dp['battery_level'] ?? 'N/A'); ?>%</td></tr>
                                                <tr><td class="text-muted">Charging</td><td><?php echo !empty($dp['battery_charging']) ? 'Yes' : 'No'; ?></td></tr>
                                                <tr><td class="text-muted">Source</td><td><?php echo htmlspecialchars($dp['battery_source'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Temperature</td><td><?php echo htmlspecialchars($dp['battery_temperature_c'] ?? 'N/A'); ?> °C</td></tr>
                                                <tr><td class="text-muted">Voltage</td><td><?php echo htmlspecialchars($dp['battery_voltage_mv'] ?? 'N/A'); ?> mV</td></tr>
                                                <tr><td class="text-muted">Sensors Count</td><td><?php echo htmlspecialchars($dp['sensor_count'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Has Accelerometer</td><td><?php echo !empty($dp['has_accelerometer']) ? 'Yes' : 'No'; ?></td></tr>
                                                <tr><td class="text-muted">Has Gyroscope</td><td><?php echo !empty($dp['has_gyroscope']) ? 'Yes' : 'No'; ?></td></tr>
                                                <tr><td class="text-muted">Has Magnetometer</td><td><?php echo !empty($dp['has_magnetometer']) ? 'Yes' : 'No'; ?></td></tr>
                                                <tr><td class="text-muted">Has Proximity</td><td><?php echo !empty($dp['has_proximity']) ? 'Yes' : 'No'; ?></td></tr>
                                                <tr><td class="text-muted">Has Light Sensor</td><td><?php echo !empty($dp['has_light']) ? 'Yes' : 'No'; ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Locale & Runtime -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-warning shadow-sm mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0"><i class="fas fa-globe mr-2"></i>Locale & Time</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Language</td><td><?php echo htmlspecialchars($dp['language'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Country</td><td><?php echo htmlspecialchars($dp['country'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Timezone</td><td><?php echo htmlspecialchars($dp['timezone'] ?? 'N/A'); ?> (UTC<?php echo htmlspecialchars($dp['timezone_offset'] ?? '?'); ?>)</td></tr>
                                                <tr><td class="text-muted">Timezone Name</td><td><?php echo htmlspecialchars($dp['timezone_display_name'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Boot Time</td><td><?php echo !empty($dp['boot_time']) ? date('Y-m-d H:i:s', $dp['boot_time'] / 1000) : 'N/A'; ?></td></tr>
                                                <tr><td class="text-muted">Uptime</td><td><?php echo htmlspecialchars($dp['uptime_seconds'] ?? 'N/A'); ?>s</td></tr>
                                                <tr><td class="text-muted">System Load</td><td><?php echo htmlspecialchars($dp['system_load'] ?? 'N/A'); ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-secondary shadow-sm mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0"><i class="fas fa-cog mr-2"></i>System Properties</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">OS Name</td><td><?php echo htmlspecialchars($dp['os_name'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Java VM</td><td><?php echo htmlspecialchars($dp['java_vm_name'] ?? 'N/A'); ?> v<?php echo htmlspecialchars($dp['java_vm_version'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">User Language</td><td><?php echo htmlspecialchars($dp['user_language'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">User Region</td><td><?php echo htmlspecialchars($dp['user_region'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Kernel</td><td><small><?php echo htmlspecialchars($dp['kernel_info'] ?? 'N/A'); ?></small></td></tr>
                                                <tr><td class="text-muted">Kernel Version</td><td><small><?php echo htmlspecialchars($dp['kernel_version'] ?? 'N/A'); ?></small></td></tr>
                                                <tr><td class="text-muted">UI Mode</td><td><?php echo htmlspecialchars($dp['ui_mode'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Font Scale</td><td><?php echo htmlspecialchars($dp['font_scale'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Smallest Width</td><td><?php echo htmlspecialchars($dp['smallest_width_dp'] ?? 'N/A'); ?> dp</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Security & App Info -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-danger shadow-sm mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0"><i class="fas fa-shield-alt mr-2"></i>Security</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Rooted</td><td><?php echo !empty($dp['is_rooted']) ? '<span class="text-danger"><i class="fas fa-exclamation-circle mr-1"></i>Yes</span>' : '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>No</span>'; ?></td></tr>
                                                <tr><td class="text-muted">Emulator</td><td><?php echo !empty($dp['is_emulator']) ? '<span class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i>Yes</span>' : '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>No</span>'; ?></td></tr>
                                                <tr><td class="text-muted">Debuggable</td><td><?php echo !empty($dp['is_debuggable']) ? 'Yes' : 'No'; ?></td></tr>
                                                <tr><td class="text-muted">Test Build</td><td><?php echo !empty($dp['is_test_build']) ? 'Yes' : 'No'; ?></td></tr>
                                                <tr><td class="text-muted">System Features</td><td><?php echo htmlspecialchars($dp['system_feature_count'] ?? '0'); ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-info shadow-sm mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0"><i class="fas fa-file-alt mr-2"></i>App Info</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Package</td><td><?php echo htmlspecialchars($dp['app_package'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Version</td><td><?php echo htmlspecialchars($dp['app_version'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($dp['app_version_code'] ?? '?'); ?>)</td></tr>
                                                <tr><td class="text-muted">Target SDK</td><td><?php echo htmlspecialchars($dp['app_target_sdk'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Installer</td><td><?php echo htmlspecialchars($dp['app_installer'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">Signature Count</td><td><?php echo htmlspecialchars($dp['app_signature_count'] ?? 'N/A'); ?></td></tr>
                                                <tr><td class="text-muted">First Install</td><td><?php echo !empty($dp['app_first_install']) ? date('Y-m-d H:i:s', $dp['app_first_install'] / 1000) : 'N/A'; ?></td></tr>
                                                <tr><td class="text-muted">Last Update</td><td><?php echo !empty($dp['app_last_update']) ? date('Y-m-d H:i:s', $dp['app_last_update'] / 1000) : 'N/A'; ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- System Features Detail -->
                            <?php if (!empty($dp['system_features'])): ?>
                                <div class="card card-light shadow-sm mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="fas fa-list mr-2"></i>System Features</h5>
                                    </div>
                                    <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                        <?php $features = explode(',', $dp['system_features']); ?>
                                        <?php foreach ($features as $feature): ?>
                                            <?php $feature = trim($feature); if (empty($feature)) continue; ?>
                                            <span class="badge badge-info mr-1 mb-1"><?php echo htmlspecialchars($feature); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- FCM Token -->
                            <?php if (!empty($dp['fcm_token'])): ?>
                                <div class="card card-dark shadow-sm mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="fas fa-fire mr-2"></i>FCM Token</h5>
                                    </div>
                                    <div class="card-body">
                                        <code class="text-break"><?php echo htmlspecialchars($dp['fcm_token']); ?></code>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Extraction Metadata -->
                            <div class="card card-light shadow-sm mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="fas fa-clock mr-2"></i>Extraction Metadata</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Extractor Version:</strong> <?php echo htmlspecialchars($dp['extractor_version'] ?? 'N/A'); ?>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Timestamp:</strong> <?php echo !empty($dp['extraction_timestamp']) ? (is_numeric($dp['extraction_timestamp']) && strlen($dp['extraction_timestamp']) > 11 ? date('Y-m-d H:i:s', $dp['extraction_timestamp'] / 1000) : (is_numeric($dp['extraction_timestamp']) ? date('Y-m-d H:i:s', $dp['extraction_timestamp']) : htmlspecialchars($dp['extraction_timestamp']))) : 'N/A'; ?>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Recorded:</strong> <?php echo !empty($dp['current_time_formatted']) ? htmlspecialchars($dp['current_time_formatted']) : 'N/A'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-mobile-alt fa-5x text-muted"></i>
                        </div>
                        <h4 class="text-muted">No Devices Registered</h4>
                        <p class="text-muted">Device fingerprint data will appear here once the Android app registers a device against your account.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
