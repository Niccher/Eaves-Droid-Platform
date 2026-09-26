<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-fingerprint mr-2 text-primary"></i>Device Fingerprint
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">My Devices</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <!-- Page Callout -->
            <div class="callout callout-primary shadow-sm bg-white">
                <h5><i class="fas fa-info-circle mr-2 text-primary"></i>Device Fingerprint Registry</h5>
                <p class="mb-0 text-muted small">
                    Full hardware and software fingerprint data collected from your registered Android devices.
                    <?php $total = count($device_profiles ?? []); ?>
                    <span class="badge badge-primary ml-1">
                        <?= $total ?> device<?= $total !== 1 ? 's' : '' ?> registered
                    </span>
                </p>
            </div>

            <?php if (!empty($device_profiles)): ?>

                <!-- Device Switcher Pills -->
                <?php if (count($device_profiles) > 1): ?>
                <div class="mb-4">
                    <ul class="nav nav-pills" id="devicePills" role="tablist">
                        <?php foreach ($device_profiles as $i => $dp): ?>
                            <?php
                                $deviceLabel = trim(($dp['device_brand'] ?? '') . ' ' . ($dp['device_model'] ?? 'Device'));
                                $deviceId = 'device-' . $i;
                            ?>
                            <li class="nav-item mr-2 mb-2">
                                <a class="nav-link <?= $i === 0 ? 'active' : '' ?>"
                                   id="<?= $deviceId ?>-tab"
                                   data-toggle="pill"
                                   href="#<?= $deviceId ?>"
                                   role="tab">
                                    <i class="fas fa-mobile-alt mr-1"></i>
                                    <?= htmlspecialchars($deviceLabel) ?>
                                    <?php if (!empty($dp['android_version'])): ?>
                                        <span class="badge badge-light ml-1"><?= htmlspecialchars($dp['android_version']) ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Tab Content -->
                <div class="tab-content" id="devicePillsContent">
                    <?php foreach ($device_profiles as $i => $dp): ?>
                        <?php $deviceId = 'device-' . $i; ?>
                        <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>"
                             id="<?= $deviceId ?>"
                             role="tabpanel">

                            <!-- ===== SUMMARY INFO BOXES (ADMINLTE THEME) ===== -->
                            <div class="row mb-4">
                                <!-- Device -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="info-box shadow-sm">
                                        <span class="info-box-icon bg-primary"><i class="fas fa-mobile-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Device</span>
                                            <span class="info-box-number"><?= htmlspecialchars(($dp['device_brand'] ?? '') . ' ' . ($dp['device_model'] ?? 'N/A')) ?></span>
                                            <span class="text-muted small"><?= htmlspecialchars($dp['device_manufacturer'] ?? '') ?></span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Android -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="info-box shadow-sm">
                                        <span class="info-box-icon bg-success"><i class="fab fa-android"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Android OS</span>
                                            <span class="info-box-number"><?= htmlspecialchars($dp['android_version'] ?? 'N/A') ?></span>
                                            <span class="text-muted small">SDK <?= htmlspecialchars($dp['android_sdk_int'] ?? '?') ?></span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Hardware -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="info-box shadow-sm">
                                        <span class="info-box-icon bg-info"><i class="fas fa-microchip"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Hardware</span>
                                            <span class="info-box-number"><?= htmlspecialchars($dp['device_hardware'] ?? 'N/A') ?></span>
                                            <span class="text-muted small"><?= htmlspecialchars($dp['device_board'] ?? '') ?></span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Security -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <?php $rooted = !empty($dp['is_rooted']); ?>
                                    <div class="info-box shadow-sm">
                                        <span class="info-box-icon bg-<?= $rooted ? 'danger' : 'teal' ?>"><i class="fas fa-shield-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Security</span>
                                            <span class="info-box-number text-<?= $rooted ? 'danger' : 'success' ?>"><?= $rooted ? 'Rooted' : 'Secure' ?></span>
                                            <span class="text-muted small"><?= !empty($dp['is_emulator']) ? 'Emulator' : 'Physical' ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== DEVICE IDENTITY ===== -->
                            <div class="card card-primary card-outline shadow-sm mb-4">
                                <div class="card-header">
                                    <h3 class="card-title text-primary">
                                        <i class="fas fa-id-card mr-2"></i>Device Identity
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless table-hover">
                                                <tr><td class="text-muted" style="width:180px;"><i class="fas fa-fingerprint mr-2 text-secondary"></i>Device ID</td><td><code><?= htmlspecialchars($dp['device_id'] ?? 'N/A') ?></code></td></tr>
                                                <tr><td class="text-muted"><i class="fab fa-android mr-2 text-secondary"></i>Android ID</td><td><code><?= htmlspecialchars($dp['android_id'] ?? 'N/A') ?></code></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-mobile-alt mr-2 text-secondary"></i>Model</td><td><?= htmlspecialchars($dp['device_model'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-tag mr-2 text-secondary"></i>Brand</td><td><?= htmlspecialchars($dp['device_brand'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-industry mr-2 text-secondary"></i>Manufacturer</td><td><?= htmlspecialchars($dp['device_manufacturer'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-box mr-2 text-secondary"></i>Product</td><td><?= htmlspecialchars($dp['device_product'] ?? 'N/A') ?></td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless table-hover">
                                                <tr><td class="text-muted" style="width:160px;"><i class="fas fa-signature mr-2 text-secondary"></i>Device Name</td><td><?= htmlspecialchars($dp['device_device'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-microchip mr-2 text-secondary"></i>Board</td><td><?= htmlspecialchars($dp['device_board'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-cpu mr-2 text-secondary"></i>Hardware</td><td><?= htmlspecialchars($dp['device_hardware'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-barcode mr-2 text-secondary"></i>Serial</td><td><code><?= htmlspecialchars($dp['build_serial'] ?? 'N/A') ?></code></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-network-wired mr-2 text-secondary"></i>MAC Address</td><td><code><?= htmlspecialchars($dp['mac_address'] ?? 'N/A') ?></code></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-globe mr-2 text-secondary"></i>IP Address</td><td><?= htmlspecialchars($dp['device_ip_address'] ?? 'N/A') ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== ANDROID OS & BUILD ===== -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-info card-outline shadow-sm mb-4">
                                        <div class="card-header">
                                            <h3 class="card-title text-info">
                                                <i class="fab fa-android mr-2"></i>Android OS
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless table-hover">
                                                <tr><td class="text-muted" style="width:160px;"><i class="fab fa-android mr-2 text-secondary"></i>Version</td><td><?= htmlspecialchars($dp['android_version'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-code mr-2 text-secondary"></i>SDK</td><td><?= htmlspecialchars($dp['android_sdk_int'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-terminal mr-2 text-secondary"></i>Codename</td><td><?= htmlspecialchars($dp['android_codename'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-plus mr-2 text-secondary"></i>Incremental</td><td><?= htmlspecialchars($dp['android_incremental'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-layer-group mr-2 text-secondary"></i>Base OS</td><td><?= htmlspecialchars($dp['android_base_os'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-user-shield mr-2 text-secondary"></i>Security Patch</td><td><?= htmlspecialchars($dp['android_security_patch'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-power-off mr-2 text-secondary"></i>Bootloader</td><td><?= htmlspecialchars($dp['bootloader'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-broadcast-tower mr-2 text-secondary"></i>Radio</td><td><?= htmlspecialchars($dp['radio_version'] ?? 'N/A') ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-secondary card-outline shadow-sm mb-4">
                                        <div class="card-header">
                                            <h3 class="card-title text-secondary">
                                                <i class="fas fa-cube mr-2"></i>Build Info
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless table-hover">
                                                <tr><td class="text-muted" style="width:160px;"><i class="fas fa-hashtag mr-2 text-secondary"></i>Build ID</td><td><?= htmlspecialchars($dp['build_id'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-info mr-2 text-secondary"></i>Type</td><td><?= htmlspecialchars($dp['build_type'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-tags mr-2 text-secondary"></i>Tags</td><td><?= htmlspecialchars($dp['build_tags'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-fingerprint mr-2 text-secondary"></i>Fingerprint</td><td><small><code><?= htmlspecialchars($dp['build_fingerprint'] ?? 'N/A') ?></code></small></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-desktop mr-2 text-secondary"></i>Display</td><td><small><?= htmlspecialchars($dp['build_display'] ?? 'N/A') ?></small></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-user mr-2 text-secondary"></i>User</td><td><?= htmlspecialchars($dp['build_user'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-server mr-2 text-secondary"></i>Host</td><td><?= htmlspecialchars($dp['build_host'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-calendar-alt mr-2 text-secondary"></i>Build Time</td><td><?= !empty($dp['build_time']) ? date('Y-m-d H:i', $dp['build_time'] / 1000) : 'N/A' ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== DISPLAY & CPU/MEMORY ===== -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-info card-outline shadow-sm mb-4">
                                        <div class="card-header">
                                            <h3 class="card-title text-info">
                                                <i class="fas fa-tv mr-2"></i>Display Settings
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless table-hover">
                                                <tr><td class="text-muted" style="width:160px;"><i class="fas fa-expand mr-2 text-secondary"></i>Resolution</td><td><?= htmlspecialchars($dp['display_width'] ?? '?') ?>×<?= htmlspecialchars($dp['display_height'] ?? '?') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-compress mr-2 text-secondary"></i>Density</td><td><?= htmlspecialchars($dp['display_density'] ?? 'N/A') ?> (<?= htmlspecialchars($dp['display_density_dpi'] ?? '?') ?> dpi)</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-ruler-combined mr-2 text-secondary"></i>Screen Size</td><td><?= htmlspecialchars($dp['screen_size_inches'] ?? 'N/A') ?>"</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-wave-square mr-2 text-secondary"></i>Refresh Rate</td><td><?= htmlspecialchars($dp['display_refresh_rate'] ?? 'N/A') ?> Hz</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-sliders-h mr-2 text-secondary"></i>Mode</td><td><?= htmlspecialchars($dp['display_mode_width'] ?? '?') ?>×<?= htmlspecialchars($dp['display_mode_height'] ?? '?') ?> @ <?= htmlspecialchars($dp['display_mode_refresh'] ?? '?') ?>Hz</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-arrows-alt mr-2 text-secondary"></i>XDpi / YDpi</td><td><?= htmlspecialchars($dp['display_xdpi'] ?? 'N/A') ?> / <?= htmlspecialchars($dp['display_ydpi'] ?? 'N/A') ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-secondary card-outline shadow-sm mb-4">
                                        <div class="card-header">
                                            <h3 class="card-title text-secondary">
                                                <i class="fas fa-microchip mr-2"></i>CPU &amp; Memory
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless table-hover">
                                                <tr><td class="text-muted" style="width:160px;"><i class="fas fa-microchip mr-2 text-secondary"></i>CPU Cores</td><td><?= htmlspecialchars($dp['cpu_cores'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-cogs mr-2 text-secondary"></i>ABI</td><td><?= htmlspecialchars($dp['cpu_abi'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-cogs mr-2 text-secondary"></i>ABIs</td><td><?= htmlspecialchars($dp['cpu_abis'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-align-left mr-2 text-secondary"></i>CPU Detail</td><td><small><?= htmlspecialchars($dp['cpu_detail'] ?? 'N/A') ?></small></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-memory mr-2 text-secondary"></i>Total RAM</td><td><?= htmlspecialchars($dp['memory_total_mb'] ?? 'N/A') ?> MB</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-memory mr-2 text-secondary"></i>Free RAM</td><td><?= htmlspecialchars($dp['memory_free_mb'] ?? 'N/A') ?> MB</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-memory mr-2 text-secondary"></i>Available RAM</td><td><?= htmlspecialchars($dp['memory_available_mb'] ?? 'N/A') ?> MB</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-memory mr-2 text-secondary"></i>Max Heap</td><td><?= htmlspecialchars($dp['max_heap_mb'] ?? 'N/A') ?> MB</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== STORAGE ===== -->
                            <div class="card card-success card-outline shadow-sm mb-4">
                                <div class="card-header">
                                    <h3 class="card-title text-success">
                                        <i class="fas fa-database mr-2"></i>Storage Allocation
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-muted mb-2 font-weight-bold"><i class="fas fa-hdd mr-1 text-primary"></i>Internal Storage</h6>
                                            <table class="table table-sm table-borderless table-hover">
                                                <tr><td class="text-muted" style="width:160px;"><i class="fas fa-hdd mr-2 text-secondary"></i>Total Space</td><td><?= htmlspecialchars($dp['internal_storage_total_gb'] ?? 'N/A') ?> GB</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-hdd mr-2 text-secondary"></i>Free Space</td><td><?= htmlspecialchars($dp['internal_storage_free_gb'] ?? 'N/A') ?> GB</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-hdd mr-2 text-secondary"></i>Used Space</td><td><?= htmlspecialchars($dp['internal_storage_used_gb'] ?? 'N/A') ?> GB</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-hdd mr-2 text-secondary"></i>Usable Space</td><td><?= htmlspecialchars($dp['internal_storage_usable_gb'] ?? 'N/A') ?> GB</td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted mb-2 font-weight-bold"><i class="fas fa-sd-card mr-1 text-success"></i>External Storage</h6>
                                            <table class="table table-sm table-borderless table-hover">
                                                <tr><td class="text-muted" style="width:160px;"><i class="fas fa-hdd mr-2 text-secondary"></i>Total Space</td><td><?= htmlspecialchars($dp['external_storage_total_gb'] ?? 'N/A') ?> GB</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-hdd mr-2 text-secondary"></i>Free Space</td><td><?= htmlspecialchars($dp['external_storage_free_gb'] ?? 'N/A') ?> GB</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-hdd mr-2 text-secondary"></i>Used Space</td><td><?= htmlspecialchars($dp['external_storage_used_gb'] ?? 'N/A') ?> GB</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== NETWORK & BATTERY ===== -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-success card-outline shadow-sm mb-4">
                                        <div class="card-header">
                                            <h3 class="card-title text-success">
                                                <i class="fas fa-wifi mr-2"></i>Network &amp; Telephony
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless table-hover">
                                                <tr><td class="text-muted" style="width:160px;"><i class="fas fa-sim-card mr-2 text-secondary"></i>SIM Operator</td><td><?= htmlspecialchars($dp['sim_operator'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-signal mr-2 text-secondary"></i>Network Operator</td><td><?= htmlspecialchars($dp['network_operator'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-flag mr-2 text-secondary"></i>SIM Country</td><td><?= htmlspecialchars($dp['sim_country'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-flag mr-2 text-secondary"></i>Network Country</td><td><?= htmlspecialchars($dp['network_country'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-info-circle mr-2 text-secondary"></i>SIM State</td><td><?= htmlspecialchars($dp['sim_state'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-phone mr-2 text-secondary"></i>Phone Number</td><td><?= htmlspecialchars($dp['phone_number'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-barcode mr-2 text-secondary"></i>IMEI</td><td><code><?= htmlspecialchars($dp['imei'] ?? 'N/A') ?></code></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-danger card-outline shadow-sm mb-4">
                                        <div class="card-header">
                                            <h3 class="card-title text-danger">
                                                <i class="fas fa-battery-three-quarters mr-2"></i>Battery &amp; Sensors
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless table-hover">
                                                <tr><td class="text-muted" style="width:160px;"><i class="fas fa-battery-three-quarters mr-2 text-secondary"></i>Level</td><td><?= htmlspecialchars($dp['battery_level'] ?? 'N/A') ?>%</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-bolt mr-2 text-secondary"></i>Charging</td><td><?= !empty($dp['battery_charging']) ? '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>Yes</span>' : 'No' ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-plug mr-2 text-secondary"></i>Source</td><td><?= htmlspecialchars($dp['battery_source'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-thermometer-half mr-2 text-secondary"></i>Temperature</td><td><?= htmlspecialchars($dp['battery_temperature_c'] ?? 'N/A') ?> °C</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-bolt mr-2 text-secondary"></i>Voltage</td><td><?= htmlspecialchars($dp['battery_voltage_mv'] ?? 'N/A') ?> mV</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-compass mr-2 text-secondary"></i>Sensors Count</td><td><?= htmlspecialchars($dp['sensor_count'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-circle mr-2 text-secondary"></i>Accelerometer</td><td><?= !empty($dp['has_accelerometer']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-circle mr-2 text-secondary"></i>Gyroscope</td><td><?= !empty($dp['has_gyroscope']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-circle mr-2 text-secondary"></i>Magnetometer</td><td><?= !empty($dp['has_magnetometer']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-circle mr-2 text-secondary"></i>Proximity</td><td><?= !empty($dp['has_proximity']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-circle mr-2 text-secondary"></i>Light Sensor</td><td><?= !empty($dp['has_light']) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== LOCALE & SYSTEM ===== -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-secondary card-outline shadow-sm mb-4">
                                        <div class="card-header">
                                            <h3 class="card-title text-secondary">
                                                <i class="fas fa-globe mr-2"></i>Locale &amp; Time
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless table-hover">
                                                <tr><td class="text-muted" style="width:160px;"><i class="fas fa-language mr-2 text-secondary"></i>Language</td><td><?= htmlspecialchars($dp['language'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-flag mr-2 text-secondary"></i>Country</td><td><?= htmlspecialchars($dp['country'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-clock mr-2 text-secondary"></i>Timezone</td><td><?= htmlspecialchars($dp['timezone'] ?? 'N/A') ?> (UTC<?= htmlspecialchars($dp['timezone_offset'] ?? '?') ?>)</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-clock mr-2 text-secondary"></i>Timezone Name</td><td><?= htmlspecialchars($dp['timezone_display_name'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-history mr-2 text-secondary"></i>Boot Time</td><td><?= !empty($dp['boot_time']) ? date('Y-m-d H:i', $dp['boot_time'] / 1000) : 'N/A' ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-hourglass-half mr-2 text-secondary"></i>Uptime</td><td><?= htmlspecialchars($dp['uptime_seconds'] ?? 'N/A') ?>s</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-chart-line mr-2 text-secondary"></i>System Load</td><td><?= htmlspecialchars($dp['system_load'] ?? 'N/A') ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-secondary card-outline shadow-sm mb-4">
                                        <div class="card-header">
                                            <h3 class="card-title text-secondary">
                                                <i class="fas fa-cog mr-2"></i>System Properties
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless table-hover">
                                                <tr><td class="text-muted" style="width:160px;"><i class="fas fa-desktop mr-2 text-secondary"></i>OS Name</td><td><?= htmlspecialchars($dp['os_name'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-coffee mr-2 text-secondary"></i>Java VM</td><td><?= htmlspecialchars($dp['java_vm_name'] ?? 'N/A') ?> v<?= htmlspecialchars($dp['java_vm_version'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-language mr-2 text-secondary"></i>User Language</td><td><?= htmlspecialchars($dp['user_language'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-globe mr-2 text-secondary"></i>User Region</td><td><?= htmlspecialchars($dp['user_region'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-microchip mr-2 text-secondary"></i>Kernel</td><td><small><?= htmlspecialchars($dp['kernel_info'] ?? 'N/A') ?></small></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-code-branch mr-2 text-secondary"></i>Kernel Version</td><td><small><?= htmlspecialchars($dp['kernel_version'] ?? 'N/A') ?></small></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-palette mr-2 text-secondary"></i>UI Mode</td><td><?= htmlspecialchars($dp['ui_mode'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-font mr-2 text-secondary"></i>Font Scale</td><td><?= htmlspecialchars($dp['font_scale'] ?? 'N/A') ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== SECURITY & APP INFO ===== -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-danger card-outline shadow-sm mb-4">
                                        <div class="card-header">
                                            <h3 class="card-title text-danger">
                                                <i class="fas fa-shield-alt mr-2"></i>Security State
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless table-hover">
                                                <tr><td class="text-muted" style="width:160px;"><i class="fas fa-user-lock mr-2 text-secondary"></i>Rooted</td><td><?= !empty($dp['is_rooted']) ? '<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-circle mr-1"></i>Yes</span>' : '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>No</span>' ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-desktop mr-2 text-secondary"></i>Emulator</td><td><?= !empty($dp['is_emulator']) ? '<span class="text-warning font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i>Yes</span>' : '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>No</span>' ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-bug mr-2 text-secondary"></i>Debuggable</td><td><?= !empty($dp['is_debuggable']) ? 'Yes' : 'No' ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-flask mr-2 text-secondary"></i>Test Build</td><td><?= !empty($dp['is_test_build']) ? 'Yes' : 'No' ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-list mr-2 text-secondary"></i>System Features</td><td><?= htmlspecialchars($dp['system_feature_count'] ?? '0') ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-danger card-outline shadow-sm mb-4">
                                        <div class="card-header">
                                            <h3 class="card-title text-danger">
                                                <i class="fas fa-file-code mr-2"></i>App Installation
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless table-hover">
                                                <tr><td class="text-muted" style="width:160px;"><i class="fas fa-archive mr-2 text-secondary"></i>Package Name</td><td><?= htmlspecialchars($dp['app_package'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-tag mr-2 text-secondary"></i>Version</td><td><?= htmlspecialchars($dp['app_version'] ?? 'N/A') ?> (<?= htmlspecialchars($dp['app_version_code'] ?? '?') ?>)</td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-code-branch mr-2 text-secondary"></i>Target SDK</td><td><?= htmlspecialchars($dp['app_target_sdk'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-download mr-2 text-secondary"></i>Installer</td><td><?= htmlspecialchars($dp['app_installer'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-key mr-2 text-secondary"></i>Signatures</td><td><?= htmlspecialchars($dp['app_signature_count'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-calendar-plus mr-2 text-secondary"></i>First Install</td><td><?= !empty($dp['app_first_install']) ? date('Y-m-d H:i', $dp['app_first_install'] / 1000) : 'N/A' ?></td></tr>
                                                <tr><td class="text-muted"><i class="fas fa-calendar-check mr-2 text-secondary"></i>Last Update</td><td><?= !empty($dp['app_last_update']) ? date('Y-m-d H:i', $dp['app_last_update'] / 1000) : 'N/A' ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== SYSTEM FEATURES BADGES ===== -->
                            <?php if (!empty($dp['system_features'])): ?>
                                <div class="card card-secondary card-outline shadow-sm mb-4">
                                    <div class="card-header">
                                        <h3 class="card-title text-secondary">
                                            <i class="fas fa-list-ul mr-2"></i>System Features
                                            <span class="badge badge-secondary ml-2"><?= count(array_filter(array_map('trim', explode(',', $dp['system_features'])))) ?></span>
                                        </h3>
                                    </div>
                                    <div class="card-body" style="max-height:200px; overflow-y:auto;">
                                        <?php foreach (array_filter(array_map('trim', explode(',', $dp['system_features']))) as $feature): ?>
                                            <span class="badge badge-light border mr-1 mb-1" style="font-size:11px;"><?= htmlspecialchars($feature) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- ===== FCM TOKEN ===== -->
                            <?php if (!empty($dp['fcm_token'])): ?>
                                <div class="card card-secondary card-outline shadow-sm mb-4">
                                    <div class="card-header">
                                        <h3 class="card-title text-secondary">
                                            <i class="fas fa-bell mr-2"></i>FCM Push Token
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <code class="text-break" style="font-size:11.5px;"><?= htmlspecialchars($dp['fcm_token']) ?></code>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- ===== EXTRACTION METADATA ===== -->
                            <div class="card card-secondary card-outline shadow-sm mb-4">
                                <div class="card-header">
                                    <h3 class="card-title text-muted">
                                        <i class="fas fa-clock mr-2"></i>Extraction Metadata
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <span class="text-muted small"><i class="fas fa-code mr-1 text-secondary"></i>Extractor Version</span><br>
                                            <strong><?= htmlspecialchars($dp['extractor_version'] ?? 'N/A') ?></strong>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="text-muted small"><i class="fas fa-calendar-alt mr-1 text-secondary"></i>Extraction Timestamp</span><br>
                                            <strong><?= !empty($dp['extraction_timestamp']) ? (is_numeric($dp['extraction_timestamp']) && strlen($dp['extraction_timestamp']) > 11 ? date('Y-m-d H:i:s', $dp['extraction_timestamp'] / 1000) : (is_numeric($dp['extraction_timestamp']) ? date('Y-m-d H:i:s', $dp['extraction_timestamp']) : htmlspecialchars($dp['extraction_timestamp']))) : 'N/A' ?></strong>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="text-muted small"><i class="fas fa-history mr-1 text-secondary"></i>Recorded At</span><br>
                                            <strong><?= !empty($dp['current_time_formatted']) ? htmlspecialchars($dp['current_time_formatted']) : 'N/A' ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /.tab-pane -->
                    <?php endforeach; ?>
                </div><!-- /.tab-content -->

            <?php else: ?>
                <!-- Empty State -->
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4 d-flex align-items-center justify-content-center" style="width:90px; height:90px; background:#e8f0fe; border-radius:50%; margin:0 auto;">
                            <i class="fas fa-mobile-alt fa-3x text-primary"></i>
                        </div>
                        <h4 class="font-weight-bold text-primary">No Devices Registered</h4>
                        <p class="text-muted mb-0">Device fingerprint data will appear here once the Android app registers a device against your account.</p>
                    </div>
                </div>
            <?php endif; ?>

        </div><!-- /.container-fluid -->
    </section>
</div><!-- /.content-wrapper -->

<style>
/* Active pill fix for multi-device tab */
.table td { vertical-align: middle; }
code { background:#f1f3f5; color:#212529; padding:2px 6px; border-radius:4px; font-size:12px; border:1px solid #dee2e6; }
</style>
