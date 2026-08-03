<?php /** @var array $latest_dp @var array $latest_hg @var array $latest_sensors @var array $latest_camera @var array $history @var object $pager @var string $nav_urls */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3"><i class="fas fa-fingerprint text-secondary mr-2"></i>Device Fingerprint</h1>
                        <span class="badge badge-secondary border p-2 text-white"><i class="fas fa-database mr-1"></i>Snapshots: <b><?= count($history ?? []) ?></b></span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Build fingerprint, hardware IDs, GPU, sensors, camera, and network identifiers for device recognition</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Current Fingerprint Cards -->
            <div class="row mb-4">
                <!-- Build Fingerprint -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-3"><i class="fas fa-certificate mr-1"></i>Build Fingerprint</h6>
                            <?php if ($latest_dp): ?>
                                <code class="small d-block mb-2"><?= htmlspecialchars($latest_dp['build_fingerprint'] ?? '—') ?></code>
                                <dl class="row mb-0 small">
                                    <dt class="col-4">Brand</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['device_brand'] ?? '—') ?></dd>
                                    <dt class="col-4">Device</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['device_device'] ?? '—') ?></dd>
                                    <dt class="col-4">Product</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['device_product'] ?? '—') ?></dd>
                                    <dt class="col-4">Model</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['device_model'] ?? '—') ?></dd>
                                    <dt class="col-4">Manufacturer</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['device_manufacturer'] ?? '—') ?></dd>
                                    <dt class="col-4">Board</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['device_board'] ?? '—') ?></dd>
                                    <dt class="col-4">Hardware</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['device_hardware'] ?? '—') ?></dd>
                                </dl>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">No device profile data</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Android & Display -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-3"><i class="fab fa-android mr-1"></i>Android & Display</h6>
                            <?php if ($latest_dp): ?>
                                <dl class="row mb-0 small">
                                    <dt class="col-4">Android</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['android_version'] ?? '—') ?> (API <?= (int)($latest_dp['android_sdk_int'] ?? 0) ?>)</dd>
                                    <dt class="col-4">Codename</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['android_codename'] ?? '—') ?></dd>
                                    <dt class="col-4">Build ID</dt><dd class="col-8"><code><?= htmlspecialchars($latest_dp['build_id'] ?? '—') ?></code></dd>
                                    <dt class="col-4">Display</dt><dd class="col-8"><code><?= htmlspecialchars($latest_dp['build_display'] ?? '—') ?></code></dd>
                                    <dt class="col-4">Security Patch</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['android_security_patch'] ?? '—') ?></dd>
                                    <dt class="col-4">Resolution</dt><dd class="col-8"><?= $latest_dp['display_width'] ?? '—' ?> × <?= $latest_dp['display_height'] ?? '—' ?></dd>
                                    <dt class="col-4">Density</dt><dd class="col-8"><?= $latest_dp['display_density_dpi'] ?? '—' ?> DPI</dd>
                                </dl>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">No data</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Hardware IDs -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-3"><i class="fas fa-id-card mr-1"></i>Hardware IDs</h6>
                            <?php if ($latest_dp): ?>
                                <dl class="row mb-0 small">
                                    <dt class="col-4">IMEI</dt><dd class="col-8"><code><?= htmlspecialchars($latest_dp['imei'] ?? '—') ?></code></dd>
                                    <dt class="col-4">MEID</dt><dd class="col-8"><code><?= htmlspecialchars($latest_dp['meid'] ?? '—') ?></code></dd>
                                    <dt class="col-4">Android ID</dt><dd class="col-8"><code><?= htmlspecialchars($latest_dp['android_id'] ?? '—') ?></code></dd>
                                    <dt class="col-4">MAC</dt><dd class="col-8"><code><?= htmlspecialchars($latest_dp['mac_address'] ?? '—') ?></code></dd>
                                    <dt class="col-4">Phone</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['phone_number'] ?? '—') ?></dd>
                                    <dt class="col-4">SIM Serial</dt><dd class="col-8"><code><?= htmlspecialchars($latest_dp['sim_serial'] ?? '—') ?></code></dd>
                                    <dt class="col-4">Device ID</dt><dd class="col-8"><code><?= htmlspecialchars($latest_dp['device_id'] ?? '—') ?></code></dd>
                                </dl>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">No data</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GPU + Sensors + Camera Summary -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-info shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-3"><i class="fas fa-memory mr-1"></i>GPU Summary</h6>
                            <?php if ($latest_hg): 
                                $gpu = json_decode($latest_hg['gpu_renderer_json'] ?? '{}', true);
                            ?>
                                <div class="font-weight-bold"><?= htmlspecialchars($gpu['gl_renderer'] ?? '—') ?></div>
                                <small class="text-muted d-block">GL: <?= htmlspecialchars($gpu['gl_version'] ?? '—') ?></small>
                                <small class="text-muted d-block">Vendor: <?= htmlspecialchars($gpu['gl_vendor'] ?? '—') ?></small>
                                <?php if (!empty($gpu['vulkan_available'])): ?><span class="badge badge-success mt-2">Vulkan <?= htmlspecialchars($gpu['vulkan_version'] ?? '?') ?></span><?php endif; ?>
                                <div class="mt-2">
                                    <span class="badge badge-info"><?= count(json_decode($latest_hg['media_codecs_json'] ?? '[]', true)) ?> codecs</span>
                                    <span class="badge badge-warning"><?= count(json_decode($latest_hg['input_devices_json'] ?? '[]', true)) ?> input devices</span>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No GPU data</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-warning shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-3"><i class="fas fa-microchip mr-1"></i>Sensors Summary</h6>
                            <?php if (!empty($latest_sensors)): 
                                $typeCounts = [];
                                foreach ($latest_sensors as $s) { $typeCounts[$s['type_string'] ?? 'Unknown'] = ($typeCounts[$s['type_string'] ?? 'Unknown'] ?? 0) + 1; }
                            ?>
                                <div class="h4 mb-1"><?= count($latest_sensors) ?> sensors</div>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach ($typeCounts as $type => $count): ?>
                                        <span class="badge badge-secondary p-1"><?= $count ?>× <?= htmlspecialchars($type) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No sensor data</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-dark shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-3"><i class="fas fa-camera mr-1"></i>Camera Summary</h6>
                            <?php if (!empty($latest_camera)): ?>
                                <div class="h4 mb-1"><?= count($latest_camera) ?> camera<?= count($latest_camera) !== 1 ? 's' : '' ?></div>
                                <?php foreach ($latest_camera as $cam): ?>
                                    <div class="small mb-1">
                                        <span class="badge badge-<?= ($cam['lens_facing'] ?? 1) == 0 ? 'primary' : 'success' ?>">
                                            <?= ($cam['lens_facing'] ?? 1) == 0 ? 'Back' : 'Front' ?>
                                        </span>
                                        <?= $cam['pixel_array_width'] ?? '—' ?>×<?= $cam['pixel_array_height'] ?? '—' ?>
                                        <?php if ($cam['flash_available']): ?><span class="badge badge-warning">Flash</span><?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No camera data</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Details -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-3"><i class="fas fa-cogs mr-1"></i>System Details</h6>
                            <?php if ($latest_dp): ?>
                                <dl class="row mb-0 small">
                                    <dt class="col-4">CPU Cores</dt><dd class="col-8"><?= (int)($latest_dp['cpu_cores'] ?? 0) ?></dd>
                                    <dt class="col-4">CPU ABI</dt><dd class="col-8"><code><?= htmlspecialchars($latest_dp['cpu_abi'] ?? '—') ?></code></dd>
                                    <dt class="col-4">CPU ABIs</dt><dd class="col-8"><code><?= htmlspecialchars($latest_dp['cpu_abis'] ?? '—') ?></code></dd>
                                    <dt class="col-4">Kernel</dt><dd class="col-8"><code><?= htmlspecialchars($latest_dp['kernel_info'] ?? '—') ?></code></dd>
                                    <dt class="col-4">Memory Total</dt><dd class="col-8"><?= format_bytes(($latest_dp['memory_total_mb'] ?? 0) * 1048576) ?></dd>
                                    <dt class="col-4">Memory Free</dt><dd class="col-8"><?= format_bytes(($latest_dp['memory_free_mb'] ?? 0) * 1048576) ?></dd>
                                    <dt class="col-4">Internal Storage</dt><dd class="col-8"><?= format_bytes(($latest_dp['internal_storage_total_gb'] ?? 0) * 1073741824) ?></dd>
                                    <dt class="col-4">External Storage</dt><dd class="col-8"><?= format_bytes(($latest_dp['external_storage_total_gb'] ?? 0) * 1073741824) ?></dd>
                                    <dt class="col-4">Emulator</dt><dd class="col-8"><?= !empty($latest_dp['is_emulator']) ? '<span class="badge badge-warning">Yes</span>' : '<span class="badge badge-success">No</span>' ?></dd>
                                    <dt class="col-4">Rooted</dt><dd class="col-8"><?= !empty($latest_dp['is_rooted']) ? '<span class="badge badge-danger">Yes</span>' : '<span class="badge badge-success">No</span>' ?></dd>
                                    <dt class="col-4">App</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['app_package'] ?? '—') ?> v<?= htmlspecialchars($latest_dp['app_version'] ?? '—') ?></dd>
                                </dl>
                            <?php else: ?>
                                <p class="text-muted">No system details</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card card-outline card-secondary shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-3"><i class="fas fa-network-wired mr-1"></i>Network & Locale</h6>
                            <?php if ($latest_dp): ?>
                                <dl class="row mb-0 small">
                                    <dt class="col-4">IP Address</dt><dd class="col-8"><code><?= htmlspecialchars($latest_dp['device_ip_address'] ?? '—') ?></code></dd>
                                    <dt class="col-4">Phone Number</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['phone_number'] ?? '—') ?></dd>
                                    <dt class="col-4">Locale</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['language'] ?? '—') ?> / <?= htmlspecialchars($latest_dp['country'] ?? '—') ?></dd>
                                    <dt class="col-4">Timezone</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['timezone'] ?? '—') ?></dd>
                                    <dt class="col-4">SIM Operator</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['sim_operator'] ?? '—') ?></dd>
                                    <dt class="col-4">Network Operator</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['network_operator'] ?? '—') ?></dd>
                                    <dt class="col-4">SIM Country</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['sim_country'] ?? '—') ?></dd>
                                    <dt class="col-4">Network Country</dt><dd class="col-8"><?= htmlspecialchars($latest_dp['network_country'] ?? '—') ?></dd>
                                </dl>
                            <?php else: ?>
                                <p class="text-muted">No network/locale data</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Fingerprint History</h3>
                            <div class="card-tools ml-auto"><button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#fingerprintHistoryModal">Show All (<?= count($history ?? []) ?>)</button><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="width:160px;"><i class="fas fa-clock mr-1"></i>Time</th>
                                        <th><i class="fas fa-certificate mr-1"></i>Build Fingerprint</th>
                                        <th><i class="fab fa-android mr-1"></i>Android</th>
                                        <th><i class="fas fa-microchip mr-1"></i>CPU</th>
                                        <th><i class="fas fa-memory mr-1"></i>Memory</th>
                                        <th><i class="fas fa-hdd mr-1"></i>Storage</th>
                                        <th><i class="fas fa-fingerprint mr-1"></i>Fingerprint Hash</th>
                                        <th class="text-center"><i class="fas fa-cogs mr-1"></i>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($history)): ?>
                                        <tr><td colspan="9" class="text-center py-5"><div class="empty-state"><i class="fas fa-fingerprint fa-3x text-muted mb-3"></i><h4>No fingerprint history</h4></div></td></tr>
                                    <?php else: foreach (array_slice($history,0,5) as $h): 
                                        $dp = $h['device_profile'] ?? [];
                                        $hg = $h['hardware_graphics'] ?? [];
                                        $gpu = json_decode($hg['gpu_renderer_json'] ?? '{}', true);
                                        // Create a simple fingerprint hash from key hardware fields
                                        $fpData = ($dp['build_fingerprint'] ?? '') . '|' . ($dp['device_hardware'] ?? '') . '|' . ($dp['cpu_abi'] ?? '') . '|' . ($dp['display_width'] ?? '') . '|' . ($dp['display_height'] ?? '');
                                        $fpHash = md5($fpData);
                                    ?>
                                        <tr>
                                            <td><small><?= !empty($h['extracted_at']) ? format_timestamp_display((int)$h['extracted_at']) : '—' ?></small></td>
                                            <td><code class="small"><?= htmlspecialchars(substr($dp['build_fingerprint'] ?? '—', 0, 40)) ?></code></td>
                                            <td><?= htmlspecialchars($dp['android_version'] ?? '—') ?> (API <?= (int)($dp['android_sdk_int'] ?? 0) ?>)</td>
                                            <td><?= (int)($dp['cpu_cores'] ?? 0) ?> cores / <?= htmlspecialchars($dp['cpu_abi'] ?? '—') ?></td>
                                            <td><?= format_bytes(($dp['memory_total_mb'] ?? 0) * 1048576) ?></td>
                                            <td><?= format_bytes(($dp['internal_storage_total_gb'] ?? 0) * 1073741824) ?></td>
                                            <td><code class="small"><?= substr($fpHash, 0, 12) ?></code></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-danger delete-row" data-id="<?= $h['id'] ?? '' ?>" data-url="<?= base_url('advanced/hardware/device_fingerprint/delete') ?>" title="Delete this snapshot"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer"><div class="float-right"><?php if (isset($pager)): ?><?= $pager->links('default', 'bootstrap5_full') ?><?php endif; ?></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/_adv_style.php'; ?>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>