<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-fingerprint mr-2" style="color:#8b5cf6;"></i>Device Fingerprint
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
            <div class="callout" style="border-left-color:#8b5cf6; background:#faf5ff;">
                <h5 style="color:#8b5cf6;"><i class="fas fa-fingerprint mr-2"></i>Device Fingerprint Registry</h5>
                <p class="mb-0 text-muted small">
                    Full hardware and software fingerprint data collected from your registered Android devices.
                    <?php $total = count($device_profiles ?? []); ?>
                    <span class="badge ml-1" style="background:#8b5cf6; color:#fff;">
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
                                   role="tab"
                                   style="<?= $i === 0 ? 'background:#8b5cf6; color:#fff;' : 'background:#f3e8ff; color:#8b5cf6; border:1px solid #d8b4fe;' ?>">
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

                            <!-- ===== SUMMARY STAT CARDS ===== -->
                            <div class="row mb-4">
                                <!-- Device -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card h-100 shadow-sm" style="border-left:4px solid #8b5cf6;">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="mr-3" style="width:52px; height:52px; background:#f3e8ff; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                                                <i class="fas fa-mobile-alt fa-lg" style="color:#8b5cf6;"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small mb-0">Device</div>
                                                <strong class="d-block"><?= htmlspecialchars(($dp['device_brand'] ?? '') . ' ' . ($dp['device_model'] ?? 'N/A')) ?></strong>
                                                <small class="text-muted"><?= htmlspecialchars($dp['device_manufacturer'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Android -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card h-100 shadow-sm" style="border-left:4px solid #ec4899;">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="mr-3" style="width:52px; height:52px; background:#fce7f3; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                                                <i class="fab fa-android fa-lg" style="color:#ec4899;"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small mb-0">Android</div>
                                                <strong class="d-block"><?= htmlspecialchars($dp['android_version'] ?? 'N/A') ?></strong>
                                                <small class="text-muted">SDK <?= htmlspecialchars($dp['android_sdk_int'] ?? '?') ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Hardware -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card h-100 shadow-sm" style="border-left:4px solid #0ea5e9;">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="mr-3" style="width:52px; height:52px; background:#e0f2fe; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                                                <i class="fas fa-microchip fa-lg" style="color:#0ea5e9;"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small mb-0">Hardware</div>
                                                <strong class="d-block"><?= htmlspecialchars($dp['device_hardware'] ?? 'N/A') ?></strong>
                                                <small class="text-muted"><?= htmlspecialchars($dp['device_board'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Security -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <?php $rooted = !empty($dp['is_rooted']); ?>
                                    <div class="card h-100 shadow-sm" style="border-left:4px solid <?= $rooted ? '#ef4444' : '#10b981' ?>;">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="mr-3" style="width:52px; height:52px; background:<?= $rooted ? '#fee2e2' : '#d1fae5' ?>; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                                                <i class="fas fa-shield-alt fa-lg" style="color:<?= $rooted ? '#ef4444' : '#10b981' ?>;"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small mb-0">Security</div>
                                                <strong class="d-block" style="color:<?= $rooted ? '#ef4444' : '#10b981' ?>;">
                                                    <?= $rooted ? 'Rooted' : 'Secure' ?>
                                                </strong>
                                                <small class="text-muted"><?= !empty($dp['is_emulator']) ? 'Emulator' : 'Physical Device' ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== DEVICE IDENTITY ===== -->
                            <div class="card shadow-sm mb-4" style="border-top:3px solid #8b5cf6;">
                                <div class="card-header" style="background:#faf5ff; border-bottom:1px solid #ede9fe;">
                                    <h5 class="card-title mb-0" style="color:#8b5cf6;">
                                        <i class="fas fa-id-card mr-2"></i>Device Identity
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Device ID</td><td><code><?= htmlspecialchars($dp['device_id'] ?? 'N/A') ?></code></td></tr>
                                                <tr><td class="text-muted">Android ID</td><td><code><?= htmlspecialchars($dp['android_id'] ?? 'N/A') ?></code></td></tr>
                                                <tr><td class="text-muted">Model</td><td><?= htmlspecialchars($dp['device_model'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Brand</td><td><?= htmlspecialchars($dp['device_brand'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Manufacturer</td><td><?= htmlspecialchars($dp['device_manufacturer'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Product</td><td><?= htmlspecialchars($dp['device_product'] ?? 'N/A') ?></td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Device Name</td><td><?= htmlspecialchars($dp['device_device'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Board</td><td><?= htmlspecialchars($dp['device_board'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Hardware</td><td><?= htmlspecialchars($dp['device_hardware'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Serial</td><td><code><?= htmlspecialchars($dp['build_serial'] ?? 'N/A') ?></code></td></tr>
                                                <tr><td class="text-muted">MAC Address</td><td><code><?= htmlspecialchars($dp['mac_address'] ?? 'N/A') ?></code></td></tr>
                                                <tr><td class="text-muted">IP Address</td><td><?= htmlspecialchars($dp['device_ip_address'] ?? 'N/A') ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== ANDROID OS & BUILD ===== -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card shadow-sm mb-4" style="border-top:3px solid #6c757d;">
                                        <div class="card-header" style="background:#f8f9fa; border-bottom:1px solid #dee2e6;">
                                            <h5 class="card-title mb-0" style="color:#495057;">
                                                <i class="fab fa-android mr-2"></i>Android OS
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Version</td><td><?= htmlspecialchars($dp['android_version'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">SDK</td><td><?= htmlspecialchars($dp['android_sdk_int'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Codename</td><td><?= htmlspecialchars($dp['android_codename'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Incremental</td><td><?= htmlspecialchars($dp['android_incremental'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Base OS</td><td><?= htmlspecialchars($dp['android_base_os'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Security Patch</td><td><?= htmlspecialchars($dp['android_security_patch'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Bootloader</td><td><?= htmlspecialchars($dp['bootloader'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Radio</td><td><?= htmlspecialchars($dp['radio_version'] ?? 'N/A') ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card shadow-sm mb-4" style="border-top:3px solid #343a40;">
                                        <div class="card-header" style="background:#f8f9fa; border-bottom:1px solid #adb5bd;">
                                            <h5 class="card-title mb-0" style="color:#343a40;">
                                                <i class="fas fa-cube mr-2"></i>Build Info
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Build ID</td><td><?= htmlspecialchars($dp['build_id'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Type</td><td><?= htmlspecialchars($dp['build_type'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Tags</td><td><?= htmlspecialchars($dp['build_tags'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Fingerprint</td><td><small><code><?= htmlspecialchars($dp['build_fingerprint'] ?? 'N/A') ?></code></small></td></tr>
                                                <tr><td class="text-muted">Display</td><td><small><?= htmlspecialchars($dp['build_display'] ?? 'N/A') ?></small></td></tr>
                                                <tr><td class="text-muted">User</td><td><?= htmlspecialchars($dp['build_user'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Host</td><td><?= htmlspecialchars($dp['build_host'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Build Time</td><td><?= !empty($dp['build_time']) ? date('Y-m-d H:i', $dp['build_time'] / 1000) : 'N/A' ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== DISPLAY & CPU/MEMORY ===== -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card shadow-sm mb-4" style="border-top:3px solid #0ea5e9;">
                                        <div class="card-header" style="background:#f0f9ff; border-bottom:1px solid #bae6fd;">
                                            <h5 class="card-title mb-0" style="color:#0284c7;">
                                                <i class="fas fa-tv mr-2"></i>Display
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Resolution</td><td><?= htmlspecialchars($dp['display_width'] ?? '?') ?>×<?= htmlspecialchars($dp['display_height'] ?? '?') ?></td></tr>
                                                <tr><td class="text-muted">Density</td><td><?= htmlspecialchars($dp['display_density'] ?? 'N/A') ?> (<?= htmlspecialchars($dp['display_density_dpi'] ?? '?') ?> dpi)</td></tr>
                                                <tr><td class="text-muted">Screen Size</td><td><?= htmlspecialchars($dp['screen_size_inches'] ?? 'N/A') ?>"</td></tr>
                                                <tr><td class="text-muted">Refresh Rate</td><td><?= htmlspecialchars($dp['display_refresh_rate'] ?? 'N/A') ?> Hz</td></tr>
                                                <tr><td class="text-muted">Mode</td><td><?= htmlspecialchars($dp['display_mode_width'] ?? '?') ?>×<?= htmlspecialchars($dp['display_mode_height'] ?? '?') ?> @ <?= htmlspecialchars($dp['display_mode_refresh'] ?? '?') ?>Hz</td></tr>
                                                <tr><td class="text-muted">XDpi / YDpi</td><td><?= htmlspecialchars($dp['display_xdpi'] ?? 'N/A') ?> / <?= htmlspecialchars($dp['display_ydpi'] ?? 'N/A') ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card shadow-sm mb-4" style="border-top:3px solid #800020;">
                                        <div class="card-header" style="background:#fff5f5; border-bottom:1px solid #fecaca;">
                                            <h5 class="card-title mb-0" style="color:#800020;">
                                                <i class="fas fa-microchip mr-2"></i>CPU &amp; Memory
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">CPU Cores</td><td><?= htmlspecialchars($dp['cpu_cores'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">ABI</td><td><?= htmlspecialchars($dp['cpu_abi'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">ABIs</td><td><?= htmlspecialchars($dp['cpu_abis'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">CPU Detail</td><td><small><?= htmlspecialchars($dp['cpu_detail'] ?? 'N/A') ?></small></td></tr>
                                                <tr><td class="text-muted">Total RAM</td><td><?= htmlspecialchars($dp['memory_total_mb'] ?? 'N/A') ?> MB</td></tr>
                                                <tr><td class="text-muted">Free RAM</td><td><?= htmlspecialchars($dp['memory_free_mb'] ?? 'N/A') ?> MB</td></tr>
                                                <tr><td class="text-muted">Available RAM</td><td><?= htmlspecialchars($dp['memory_available_mb'] ?? 'N/A') ?> MB</td></tr>
                                                <tr><td class="text-muted">Max Heap</td><td><?= htmlspecialchars($dp['max_heap_mb'] ?? 'N/A') ?> MB</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== STORAGE ===== -->
                            <div class="card shadow-sm mb-4" style="border-top:3px solid #495057;">
                                <div class="card-header" style="background:#f8f9fa; border-bottom:1px solid #ced4da;">
                                    <h5 class="card-title mb-0" style="color:#343a40;">
                                        <i class="fas fa-database mr-2"></i>Storage
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-muted mb-2"><i class="fas fa-hdd mr-1"></i>Internal Storage</h6>
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Total</td><td><?= htmlspecialchars($dp['internal_storage_total_gb'] ?? 'N/A') ?> GB</td></tr>
                                                <tr><td class="text-muted">Free</td><td><?= htmlspecialchars($dp['internal_storage_free_gb'] ?? 'N/A') ?> GB</td></tr>
                                                <tr><td class="text-muted">Used</td><td><?= htmlspecialchars($dp['internal_storage_used_gb'] ?? 'N/A') ?> GB</td></tr>
                                                <tr><td class="text-muted">Usable</td><td><?= htmlspecialchars($dp['internal_storage_usable_gb'] ?? 'N/A') ?> GB</td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted mb-2"><i class="fas fa-sd-card mr-1"></i>External Storage</h6>
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Total</td><td><?= htmlspecialchars($dp['external_storage_total_gb'] ?? 'N/A') ?> GB</td></tr>
                                                <tr><td class="text-muted">Free</td><td><?= htmlspecialchars($dp['external_storage_free_gb'] ?? 'N/A') ?> GB</td></tr>
                                                <tr><td class="text-muted">Used</td><td><?= htmlspecialchars($dp['external_storage_used_gb'] ?? 'N/A') ?> GB</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== NETWORK & BATTERY ===== -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card shadow-sm mb-4" style="border-top:3px solid #06b6d4;">
                                        <div class="card-header" style="background:#ecfeff; border-bottom:1px solid #a5f3fc;">
                                            <h5 class="card-title mb-0" style="color:#0891b2;">
                                                <i class="fas fa-wifi mr-2"></i>Network &amp; Telephony
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">SIM Operator</td><td><?= htmlspecialchars($dp['sim_operator'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Network Operator</td><td><?= htmlspecialchars($dp['network_operator'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">SIM Country</td><td><?= htmlspecialchars($dp['sim_country'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Network Country</td><td><?= htmlspecialchars($dp['network_country'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">SIM State</td><td><?= htmlspecialchars($dp['sim_state'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Phone Number</td><td><?= htmlspecialchars($dp['phone_number'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">IMEI</td><td><code><?= htmlspecialchars($dp['imei'] ?? 'N/A') ?></code></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card shadow-sm mb-4" style="border-top:3px solid #dc3545;">
                                        <div class="card-header" style="background:#fff5f5; border-bottom:1px solid #f5c6cb;">
                                            <h5 class="card-title mb-0" style="color:#dc3545;">
                                                <i class="fas fa-battery-three-quarters mr-2"></i>Battery &amp; Sensors
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Level</td><td><?= htmlspecialchars($dp['battery_level'] ?? 'N/A') ?>%</td></tr>
                                                <tr><td class="text-muted">Charging</td><td><?= !empty($dp['battery_charging']) ? '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>Yes</span>' : 'No' ?></td></tr>
                                                <tr><td class="text-muted">Source</td><td><?= htmlspecialchars($dp['battery_source'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Temperature</td><td><?= htmlspecialchars($dp['battery_temperature_c'] ?? 'N/A') ?> °C</td></tr>
                                                <tr><td class="text-muted">Voltage</td><td><?= htmlspecialchars($dp['battery_voltage_mv'] ?? 'N/A') ?> mV</td></tr>
                                                <tr><td class="text-muted">Sensors Count</td><td><?= htmlspecialchars($dp['sensor_count'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Accelerometer</td><td><?= !empty($dp['has_accelerometer']) ? '<span class="badge" style="background:#d1fae5;color:#059669;">Yes</span>' : '<span class="badge badge-light">No</span>' ?></td></tr>
                                                <tr><td class="text-muted">Gyroscope</td><td><?= !empty($dp['has_gyroscope']) ? '<span class="badge" style="background:#d1fae5;color:#059669;">Yes</span>' : '<span class="badge badge-light">No</span>' ?></td></tr>
                                                <tr><td class="text-muted">Magnetometer</td><td><?= !empty($dp['has_magnetometer']) ? '<span class="badge" style="background:#d1fae5;color:#059669;">Yes</span>' : '<span class="badge badge-light">No</span>' ?></td></tr>
                                                <tr><td class="text-muted">Proximity</td><td><?= !empty($dp['has_proximity']) ? '<span class="badge" style="background:#d1fae5;color:#059669;">Yes</span>' : '<span class="badge badge-light">No</span>' ?></td></tr>
                                                <tr><td class="text-muted">Light Sensor</td><td><?= !empty($dp['has_light']) ? '<span class="badge" style="background:#d1fae5;color:#059669;">Yes</span>' : '<span class="badge badge-light">No</span>' ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== LOCALE & SYSTEM ===== -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card shadow-sm mb-4" style="border-top:3px solid #6c757d;">
                                        <div class="card-header" style="background:#f8f9fa; border-bottom:1px solid #dee2e6;">
                                            <h5 class="card-title mb-0" style="color:#495057;">
                                                <i class="fas fa-globe mr-2"></i>Locale &amp; Time
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Language</td><td><?= htmlspecialchars($dp['language'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Country</td><td><?= htmlspecialchars($dp['country'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Timezone</td><td><?= htmlspecialchars($dp['timezone'] ?? 'N/A') ?> (UTC<?= htmlspecialchars($dp['timezone_offset'] ?? '?') ?>)</td></tr>
                                                <tr><td class="text-muted">Timezone Name</td><td><?= htmlspecialchars($dp['timezone_display_name'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Boot Time</td><td><?= !empty($dp['boot_time']) ? date('Y-m-d H:i', $dp['boot_time'] / 1000) : 'N/A' ?></td></tr>
                                                <tr><td class="text-muted">Uptime</td><td><?= htmlspecialchars($dp['uptime_seconds'] ?? 'N/A') ?>s</td></tr>
                                                <tr><td class="text-muted">System Load</td><td><?= htmlspecialchars($dp['system_load'] ?? 'N/A') ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card shadow-sm mb-4" style="border-top:3px solid #343a40;">
                                        <div class="card-header" style="background:#f8f9fa; border-bottom:1px solid #adb5bd;">
                                            <h5 class="card-title mb-0" style="color:#343a40;">
                                                <i class="fas fa-cog mr-2"></i>System Properties
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">OS Name</td><td><?= htmlspecialchars($dp['os_name'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Java VM</td><td><?= htmlspecialchars($dp['java_vm_name'] ?? 'N/A') ?> v<?= htmlspecialchars($dp['java_vm_version'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">User Language</td><td><?= htmlspecialchars($dp['user_language'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">User Region</td><td><?= htmlspecialchars($dp['user_region'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Kernel</td><td><small><?= htmlspecialchars($dp['kernel_info'] ?? 'N/A') ?></small></td></tr>
                                                <tr><td class="text-muted">Kernel Version</td><td><small><?= htmlspecialchars($dp['kernel_version'] ?? 'N/A') ?></small></td></tr>
                                                <tr><td class="text-muted">UI Mode</td><td><?= htmlspecialchars($dp['ui_mode'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Font Scale</td><td><?= htmlspecialchars($dp['font_scale'] ?? 'N/A') ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== SECURITY & APP INFO ===== -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card shadow-sm mb-4" style="border-top:3px solid <?= !empty($dp['is_rooted']) ? '#ef4444' : '#10b981' ?>;">
                                        <div class="card-header" style="background:<?= !empty($dp['is_rooted']) ? '#fef2f2' : '#f0fdf4' ?>; border-bottom:1px solid <?= !empty($dp['is_rooted']) ? '#fecaca' : '#a7f3d0' ?>;">
                                            <h5 class="card-title mb-0" style="color:<?= !empty($dp['is_rooted']) ? '#dc2626' : '#059669' ?>;">
                                                <i class="fas fa-shield-alt mr-2"></i>Security
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Rooted</td><td><?= !empty($dp['is_rooted']) ? '<span class="text-danger"><i class="fas fa-exclamation-circle mr-1"></i>Yes</span>' : '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>No</span>' ?></td></tr>
                                                <tr><td class="text-muted">Emulator</td><td><?= !empty($dp['is_emulator']) ? '<span class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i>Yes</span>' : '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>No</span>' ?></td></tr>
                                                <tr><td class="text-muted">Debuggable</td><td><?= !empty($dp['is_debuggable']) ? 'Yes' : 'No' ?></td></tr>
                                                <tr><td class="text-muted">Test Build</td><td><?= !empty($dp['is_test_build']) ? 'Yes' : 'No' ?></td></tr>
                                                <tr><td class="text-muted">System Features</td><td><?= htmlspecialchars($dp['system_feature_count'] ?? '0') ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card shadow-sm mb-4" style="border-top:3px solid #800020;">
                                        <div class="card-header" style="background:#fff5f5; border-bottom:1px solid #fecaca;">
                                            <h5 class="card-title mb-0" style="color:#800020;">
                                                <i class="fas fa-file-code mr-2"></i>App Info
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr><td class="text-muted" style="width:160px;">Package</td><td><?= htmlspecialchars($dp['app_package'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Version</td><td><?= htmlspecialchars($dp['app_version'] ?? 'N/A') ?> (<?= htmlspecialchars($dp['app_version_code'] ?? '?') ?>)</td></tr>
                                                <tr><td class="text-muted">Target SDK</td><td><?= htmlspecialchars($dp['app_target_sdk'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Installer</td><td><?= htmlspecialchars($dp['app_installer'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">Signatures</td><td><?= htmlspecialchars($dp['app_signature_count'] ?? 'N/A') ?></td></tr>
                                                <tr><td class="text-muted">First Install</td><td><?= !empty($dp['app_first_install']) ? date('Y-m-d H:i', $dp['app_first_install'] / 1000) : 'N/A' ?></td></tr>
                                                <tr><td class="text-muted">Last Update</td><td><?= !empty($dp['app_last_update']) ? date('Y-m-d H:i', $dp['app_last_update'] / 1000) : 'N/A' ?></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== SYSTEM FEATURES BADGES ===== -->
                            <?php if (!empty($dp['system_features'])): ?>
                                <div class="card shadow-sm mb-4" style="border-top:3px solid #6c757d;">
                                    <div class="card-header" style="background:#f8f9fa; border-bottom:1px solid #dee2e6;">
                                        <h5 class="card-title mb-0" style="color:#495057;">
                                            <i class="fas fa-list-ul mr-2"></i>System Features
                                            <span class="badge ml-2" style="background:#6c757d;color:#fff;"><?= count(array_filter(array_map('trim', explode(',', $dp['system_features'])))) ?></span>
                                        </h5>
                                    </div>
                                    <div class="card-body" style="max-height:200px; overflow-y:auto;">
                                        <?php foreach (array_filter(array_map('trim', explode(',', $dp['system_features']))) as $feature): ?>
                                            <span class="badge mr-1 mb-1" style="background:#f8f9fa;color:#495057;border:1px solid #ced4da;font-size:11px;"><?= htmlspecialchars($feature) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- ===== FCM TOKEN ===== -->
                            <?php if (!empty($dp['fcm_token'])): ?>
                                <div class="card shadow-sm mb-4" style="border-top:3px solid #374151;">
                                    <div class="card-header" style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                                        <h5 class="card-title mb-0 text-dark">
                                            <i class="fas fa-fire mr-2" style="color:#f97316;"></i>FCM Push Token
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <code class="text-break" style="font-size:11.5px; color:#374151;"><?= htmlspecialchars($dp['fcm_token']) ?></code>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- ===== EXTRACTION METADATA ===== -->
                            <div class="card shadow-sm mb-4" style="border-top:3px solid #9ca3af;">
                                <div class="card-header" style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                                    <h5 class="card-title mb-0 text-muted">
                                        <i class="fas fa-clock mr-2"></i>Extraction Metadata
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <span class="text-muted small">Extractor Version</span><br>
                                            <strong><?= htmlspecialchars($dp['extractor_version'] ?? 'N/A') ?></strong>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="text-muted small">Extraction Timestamp</span><br>
                                            <strong><?= !empty($dp['extraction_timestamp']) ? (is_numeric($dp['extraction_timestamp']) && strlen($dp['extraction_timestamp']) > 11 ? date('Y-m-d H:i:s', $dp['extraction_timestamp'] / 1000) : (is_numeric($dp['extraction_timestamp']) ? date('Y-m-d H:i:s', $dp['extraction_timestamp']) : htmlspecialchars($dp['extraction_timestamp']))) : 'N/A' ?></strong>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="text-muted small">Recorded At</span><br>
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
                <div class="card shadow-sm" style="border-top:4px solid #8b5cf6;">
                    <div class="card-body text-center py-5">
                        <div class="mb-4" style="width:90px; height:90px; background:#f3e8ff; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto;">
                            <i class="fas fa-mobile-alt fa-3x" style="color:#8b5cf6;"></i>
                        </div>
                        <h4 class="font-weight-bold" style="color:#8b5cf6;">No Devices Registered</h4>
                        <p class="text-muted mb-0">Device fingerprint data will appear here once the Android app registers a device against your account.</p>
                    </div>
                </div>
            <?php endif; ?>

        </div><!-- /.container-fluid -->
    </section>
</div><!-- /.content-wrapper -->

<style>
/* Active pill fix for multi-device tab */
#devicePills .nav-link:not(.active) { transition: all 0.2s; }
#devicePills .nav-link:not(.active):hover {
    background: #e9ecef !important;
    color: #343a40 !important;
}
.table td { vertical-align: middle; }
/* Neutral code blocks — color stays in headers only */
code { background:#f1f3f5; color:#212529; padding:2px 6px; border-radius:4px; font-size:12px; border:1px solid #dee2e6; }
</style>
