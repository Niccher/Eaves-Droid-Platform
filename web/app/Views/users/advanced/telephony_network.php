<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>
<?php
helper('coalesce');
$rows = coalesce_snapshots($rows, 'device_id');

// Process rows to extract nested arrays
foreach ($rows as &$row) {
    $ims = is_string($row['ims_volte'] ?? '') ? json_decode($row['ims_volte'], true) : ($row['ims_volte'] ?? []);
    $roaming = is_string($row['data_roaming'] ?? '') ? json_decode($row['data_roaming'], true) : ($row['data_roaming'] ?? []);
    
    $row['ims_parsed'] = $ims;
    $row['roaming_parsed'] = $roaming;
    $row['airplane_mode'] = $roaming['airplane_mode'] ?? ($ims['airplane_mode'] ?? '—');
    $row['data_state'] = $roaming['data_state'] ?? ($ims['data_state'] ?? '—');
    $row['carrier_name'] = $roaming['carrier_name'] ?? ($ims['carrier_name'] ?? '—');
    $row['data_network_type'] = $roaming['data_network_type'] ?? ($ims['data_network_type'] ?? '—');
}
unset($row);
$latest = !empty($rows) ? $rows[0] : null;
$latestIms = $latest['ims_parsed'] ?? [];
$latestRoaming = $latest['roaming_parsed'] ?? [];
?>

<style>
.tel-card       { border-radius:8px; background:#fff; box-shadow:0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2); margin-bottom:20px; }
.tel-hero       { background:linear-gradient(135deg,#007bff 0%,#17a2b8 100%); border-radius:8px 8px 0 0; padding:18px 22px; color:#fff; }
.tel-kv         { display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid #f0f0f0; font-size:13px; }
.tel-kv:last-child { border-bottom:none; }
.tel-kv .tk     { color:#6c757d; font-weight:600; font-size:11px; text-transform:uppercase; }
.tel-kv .tv     { font-weight:700; color:#343a40; }
.cap-checkbox   { display:flex; align-items:center; gap:8px; padding:6px 0; border-bottom:1px solid #f8f9fa; font-size:13px; }
.cap-checkbox:last-child { border-bottom:none; }
.apn-pill       { font-family:monospace; background:#e9ecef; border-radius:4px; padding:3px 7px; font-size:11px; color:#495057; display:inline-block; margin:2px; }
</style>

<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="h2 mb-0 mr-3">
                            <i class="fas fa-signal text-primary mr-2"></i>Mobile Network &amp; Telephony
                        </h1>
                        <span class="badge badge-secondary border p-2 text-white">
                            <i class="fas fa-database mr-1"></i>Total Snapshots: <b><?= (int)$total ?></b>
                        </span>
                    </div>
                    <p class="text-muted mt-1 mb-0">Carrier status, SIM configurations, VoLTE/VoWiFi provisioning, and emergency carrier mappings.</p>
                </div>
                <div class="col-lg-5 text-right"><?= $nav_urls ?></div>
            </div>

            <!-- Upgraded Security Callout -->
            <div class="callout callout-info shadow-sm p-3 mb-4" style="border-left:5px solid #007bff;background:#fdfdfd;border-radius:4px;">
                <h5 class="font-weight-bold text-primary"><i class="fas fa-shield-alt mr-2"></i>Telephony &amp; IMS Security Auditing</h5>
                <p class="text-secondary mb-2" style="font-size:14px;">Cellular telemetry monitors IMS registration changes, provisioned APNs, and VoLTE options. Auditing these routes isolates rogue IMSI catchers (Stingrays) or malicious redirection settings on the radio level.</p>
                <div class="row" style="font-size:12px;">
                    <div class="col-md-4 border-right">
                        <b class="d-block mb-1">IMS Status:</b>
                        <span class="text-muted">Checks IP Multimedia Subsystem states, validating if VoLTE/VoWiFi is provisioned.</span>
                    </div>
                    <div class="col-md-4 border-right pl-md-3">
                        <b class="d-block mb-1">Access Point Names (APNs):</b>
                        <span class="text-muted">Audits APNs configured for routing cellular data packets, capturing custom network bypasses.</span>
                    </div>
                    <div class="col-md-4 pl-md-3">
                        <b class="d-block mb-1">Emergency Routing:</b>
                        <span class="text-muted">Extracts SIM-registered carrier emergency codes, exposing localized carrier profiles.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <?php if (empty($rows)): ?>
                <div class="text-center py-5 bg-white shadow-sm border rounded">
                    <i class="fas fa-signal fa-3x text-muted mb-3"></i>
                    <h4 class="text-secondary">No Telephony Data Found</h4>
                    <p class="text-muted">Telephony network parameters will appear once synchronized.</p>
                </div>
            <?php else: ?>

                <!-- Status Cards Grid -->
                <div class="row">
                    <!-- Carrier Info Card -->
                    <div class="col-md-4">
                        <div class="tel-card h-100">
                            <div class="tel-hero">
                                <div class="dev-brand" style="text-transform:uppercase; font-size:9px; opacity:0.8; letter-spacing:0.8px;">SIM Carrier Profile</div>
                                <div style="font-size:20px; font-weight:800;"><?= htmlspecialchars($latest['carrier_name'] ?? 'No Carrier') ?></div>
                            </div>
                            <div class="card-body p-3">
                                <div class="tel-kv">
                                    <span class="tk"><i class="fas fa-plane mr-1 text-muted"></i> Airplane Mode</span>
                                    <span class="tv">
                                        <?= !empty($latest['airplane_mode']) ? '<span class="badge badge-warning text-dark px-2">Enabled</span>' : '<span class="badge badge-light border px-2">Disabled</span>' ?>
                                    </span>
                                </div>
                                <div class="tel-kv">
                                    <span class="tk"><i class="fas fa-phone mr-1 text-muted"></i> Voicemail Count</span>
                                    <span class="tv"><?= htmlspecialchars($latest['voice_message_count'] ?? '0') ?></span>
                                </div>
                                <div class="tel-kv">
                                    <span class="tk"><i class="fas fa-terminal mr-1 text-muted"></i> USSD Support</span>
                                    <span class="tv"><?= !empty($latest['ussd_service_available']) ? '<span class="text-success">Available</span>' : '<span class="text-muted">Unavailable</span>' ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cellular Connection Card -->
                    <div class="col-md-4">
                        <div class="tel-card h-100">
                            <div class="tel-hero" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);">
                                <div class="dev-brand" style="text-transform:uppercase; font-size:9px; opacity:0.8; letter-spacing:0.8px;">Cellular Network</div>
                                <div style="font-size:20px; font-weight:800;"><?= htmlspecialchars($latest['data_network_type'] ?? 'No Data') ?></div>
                            </div>
                            <div class="card-body p-3">
                                <div class="tel-kv">
                                    <span class="tk"><i class="fas fa-signal mr-1 text-muted"></i> Connection State</span>
                                    <span class="tv"><span class="badge badge-light border"><?= htmlspecialchars($latest['data_state'] ?? '—') ?></span></span>
                                </div>
                                <div class="tel-kv">
                                    <span class="tk"><i class="fas fa-globe-africa mr-1 text-muted"></i> Data Roaming</span>
                                    <span class="tv">
                                        <?= !empty($latestRoaming['data_roaming_enabled']) ? '<span class="text-warning">Enabled</span>' : '<span class="text-muted">Disabled</span>' ?>
                                    </span>
                                </div>
                                <div class="tel-kv">
                                    <span class="tk"><i class="fas fa-network-wired mr-1 text-muted"></i> Network Tech</span>
                                    <span class="tv"><code><?= htmlspecialchars($latest['data_network_type'] ?? '—') ?></code></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- IMS Registry Card -->
                    <div class="col-md-4">
                        <div class="tel-card h-100">
                            <div class="tel-hero" style="background: linear-gradient(135deg, #343a40 0%, #212529 100%);">
                                <div class="dev-brand" style="text-transform:uppercase; font-size:9px; opacity:0.8; letter-spacing:0.8px;">IMS Registration</div>
                                <div style="font-size:20px; font-weight:800;"><?= htmlspecialchars($latest['ims_registration_state'] ?? 'Unregistered') ?></div>
                            </div>
                            <div class="card-body p-3">
                                <div class="tel-kv">
                                    <span class="tk"><i class="fas fa-microchip mr-1 text-muted"></i> Radio Technology</span>
                                    <span class="tv"><code><?= htmlspecialchars($latest['ims_registration_tech'] ?? '—') ?></code></span>
                                </div>
                                <div class="tel-kv">
                                    <span class="tk"><i class="fas fa-phone-volume mr-1 text-muted"></i> VoLTE Provisioned</span>
                                    <span class="tv"><?= !empty($latest['volte_provisioned']) ? '<span class="text-success">Yes</span>' : '<span class="text-muted">No</span>' ?></span>
                                </div>
                                <div class="tel-kv">
                                    <span class="tk"><i class="fas fa-wifi mr-1 text-muted"></i> VoWiFi Status</span>
                                    <span class="tv"><?= !empty($latest['vowifi_enabled']) ? '<span class="text-success">Active</span>' : '<span class="text-muted">Inactive</span>' ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Multi-Tab Detail Section -->
                <div class="card card-outline card-primary shadow-sm mb-4 mt-4">
                    <div class="card-header p-0 pt-1 border-bottom-0">
                        <ul class="nav nav-tabs" id="telephonyDetailsTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-caps-link" data-toggle="tab" href="#tab-caps" role="tab">
                                    <i class="fas fa-check-double text-success mr-1"></i> IMS Capabilities
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-apns-link" data-toggle="tab" href="#tab-apns" role="tab">
                                    <i class="fas fa-server text-info mr-1"></i> Provisioned APNs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-routes-link" data-toggle="tab" href="#tab-routes" role="tab">
                                    <i class="fas fa-phone-alt text-danger mr-1"></i> Emergency Routing
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-calling-link" data-toggle="tab" href="#tab-calling" role="tab">
                                    <i class="fas fa-phone-square-alt text-primary mr-1"></i> Call Features
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content" id="telephonyDetailsTabsContent">
                            <!-- Tab 1: IMS Capabilities -->
                            <div class="tab-pane fade show active" id="tab-caps" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="cap-checkbox">
                                            <i class="fas <?= !empty($latest['volte_provisioned']) ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
                                            <span>VoLTE Provisioned (Voice over LTE)</span>
                                        </div>
                                        <div class="cap-checkbox">
                                            <i class="fas <?= !empty($latest['vowifi_provisioned']) ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
                                            <span>VoWiFi Provisioned (Voice over Wi-Fi)</span>
                                        </div>
                                        <div class="cap-checkbox">
                                            <i class="fas <?= !empty($latest['vowifi_enabled']) ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
                                            <span>VoWiFi User Setting Enabled</span>
                                        </div>
                                        <div class="cap-checkbox">
                                            <i class="fas <?= !empty($latest['video_call_enabled']) ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
                                            <span>Video Calling Enabled</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 border-left">
                                        <div class="cap-checkbox">
                                            <i class="fas <?= isset($latestIms['rtcsupported']) && $latestIms['rtcsupported'] ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
                                            <span>Rtt Supported (Real-time Texting)</span>
                                        </div>
                                        <div class="cap-checkbox">
                                            <i class="fas <?= isset($latestIms['utsupported']) && $latestIms['utsupported'] ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
                                            <span>UT Interface Support (XCAP)</span>
                                        </div>
                                        <div class="cap-checkbox">
                                            <i class="fas <?= isset($latestIms['mmttel_supported']) && $latestIms['mmttel_supported'] ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
                                            <span>MMTEL Support (Multimedia Telephony)</span>
                                        </div>
                                        <div class="tel-kv pt-2">
                                            <span class="tk"><i class="fas fa-sliders-h mr-1 text-muted"></i> WFC Mode Preference</span>
                                            <span class="tv"><code><?= htmlspecialchars($latest['wfc_mode_pref'] ?? '—') ?></code></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 2: Provisioned APNs -->
                            <div class="tab-pane fade" id="tab-apns" role="tabpanel">
                                <?php
                                $apns = isset($latestIms['provisioned_ims_apns']) ? (is_string($latestIms['provisioned_ims_apns']) ? json_decode($latestIms['provisioned_ims_apns'], true) : $latestIms['provisioned_ims_apns']) : [];
                                if (empty($apns)): ?>
                                    <p class="text-muted text-center py-4 mb-0">No provisioned IMS APNs cataloged.</p>
                                <?php else: ?>
                                    <div class="alert alert-light border">
                                        <i class="fas fa-info-circle text-info mr-2"></i> The following packet data connections are registered for system IMS routing:
                                    </div>
                                    <div class="d-flex flex-wrap pt-2">
                                        <?php foreach ($apns as $apn): ?>
                                            <span class="apn-pill"><i class="fas fa-server mr-1 text-muted"></i> <?= htmlspecialchars($apn) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab 3: Emergency Routing -->
                            <div class="tab-pane fade" id="tab-routes" role="tabpanel">
                                <?php
                                $emergencyNumbers = isset($latestIms['emergency_numbers']) ? (is_string($latestIms['emergency_numbers']) ? json_decode($latestIms['emergency_numbers'], true) : $latestIms['emergency_numbers']) : [];
                                $categories = isset($latestIms['emergency_categories']) ? (is_string($latestIms['emergency_categories']) ? json_decode($latestIms['emergency_categories'], true) : $latestIms['emergency_categories']) : [];
                                if (empty($emergencyNumbers)): ?>
                                    <p class="text-muted text-center py-4 mb-0">No emergency dial routing codes reported.</p>
                                <?php else: ?>
                                    <table class="table table-sm table-striped table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="pl-2">Dial Code</th>
                                                <th>Country Code (MCC)</th>
                                                <th>Emergency Category / Services</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($emergencyNumbers as $idx => $num): ?>
                                                <tr>
                                                    <td class="pl-2 font-weight-bold text-danger"><i class="fas fa-phone-alt mr-2"></i> <?= htmlspecialchars($num) ?></td>
                                                    <td><code><?= htmlspecialchars($latestRoaming['mcc'] ?? '—') ?></code></td>
                                                    <td>
                                                        <span class="badge badge-light border">
                                                            <?= htmlspecialchars($categories[$idx] ?? 'Generic Support / Police / Medical') ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>

                            <!-- Tab 4: Calling Features -->
                            <div class="tab-pane fade" id="tab-calling" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="tel-kv">
                                            <span class="tk">Call Waiting status</span>
                                            <span class="tv"><?= !empty($latest['call_waiting_enabled']) ? '<span class="text-success font-weight-bold">Active</span>' : '<span class="text-muted">Inactive</span>' ?></span>
                                        </div>
                                        <div class="tel-kv">
                                            <span class="tk">Call Forwarding status</span>
                                            <span class="tv"><?= !empty($latest['call_forwarding_status']) ? '<span class="text-warning font-weight-bold">Active</span>' : '<span class="text-muted">No Forwarding</span>' ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 border-left">
                                        <div class="tel-kv">
                                            <span class="tk">Caller ID Presentation (CLIP)</span>
                                            <span class="tv"><?= isset($latestIms['clip_enabled']) && $latestIms['clip_enabled'] ? 'Enabled' : 'Disabled' ?></span>
                                        </div>
                                        <div class="tel-kv">
                                            <span class="tk">Caller ID Restriction (CLIR)</span>
                                            <span class="tv"><?= isset($latestIms['clir_enabled']) && $latestIms['clir_enabled'] ? 'Restricted' : 'Default' ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </section>
</div>
<?php include __DIR__ . '/_adv_delete_script.php'; ?>
