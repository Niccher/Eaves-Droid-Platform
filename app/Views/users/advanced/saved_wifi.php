<?php /** @var array $rows @var int $total @var object $pager @var string $nav_urls */ ?>

<?php
// 1. Parse and extract all unique saved networks from the JSON blobs
$uniqueNetworks = [];
$vulnerableCount = 0;
$hiddenCount = 0;

foreach ($rows as $row) {
    $networks = [];
    if (isset($row['networks_json'])) {
        $decoded = is_string($row['networks_json']) ? json_decode($row['networks_json'], true) : $row['networks_json'];
        if (is_array($decoded)) {
            $networks = $decoded;
        }
    }

    foreach ($networks as $net) {
        $ssid = $net['ssid'] ?? $net['SSID'] ?? 'Unknown Network';
        
        // Skip empty names or default empty values
        if ($ssid === '' || $ssid === '<unknown ssid>') {
            continue;
        }

        $bssid = $net['bssid'] ?? $net['BSSID'] ?? 'N/A';
        $security = $net['security'] ?? $net['security_type'] ?? $net['capabilities'] ?? 'Unknown';
        $isHidden = isset($net['is_hidden']) ? (bool)$net['is_hidden'] : false;

        // Group by SSID to show unique networks
        $key = strtolower($ssid);
        if (!isset($uniqueNetworks[$key])) {
            // Check for insecure Open or WEP configurations
            $isInsecure = false;
            $secLower = strtolower($security);
            if (
                $secLower === 'open' || 
                $secLower === 'none' || 
                strpos($secLower, 'wep') !== false || 
                $security === '—' || 
                empty($security)
            ) {
                $isInsecure = true;
                $vulnerableCount++;
            }

            if ($isHidden) {
                $hiddenCount++;
            }

            $uniqueNetworks[$key] = [
                'ssid' => $ssid,
                'bssid' => [$bssid],
                'security' => $security,
                'is_hidden' => $isHidden,
                'insecure' => $isInsecure,
                'first_seen' => $row['extracted_at'] ?? null,
            ];
        } else {
            // Append BSSID if new
            if (!in_array($bssid, $uniqueNetworks[$key]['bssid'])) {
                $uniqueNetworks[$key]['bssid'][] = $bssid;
            }
        }
    }
}

// Sort: Put vulnerable networks first, then alphabetical
uasort($uniqueNetworks, function($a, $b) {
    if ($a['insecure'] && !$b['insecure']) return -1;
    if (!$a['insecure'] && $b['insecure']) return 1;
    return strcasecmp($a['ssid'], $b['ssid']);
});
?>

<style>
.saved-wifi-dashboard {
    margin-bottom: 20px;
}
.wifi-card {
    border-radius: 8px;
    background: #ffffff;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    border-top: 4px solid #adb5bd;
}
.wifi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.wifi-card.insecure-wifi {
    border-top-color: #dc3545; /* Red security alert border */
}
.wifi-card.secure-wifi {
    border-top-color: #28a745; /* Green secure border */
}
.callout-wifi {
    border-left: 5px solid #17a2b8;
    background: #fdfdfd;
    border-radius: 4px;
}
</style>

<div class="content-wrapper">
    <!-- Main content-header at the top -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="h3 mb-0 font-weight-bold text-dark">
                        <i class="fas fa-wifi text-secondary mr-2"></i>Remembered WiFi Networks
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <?= $nav_urls ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Educational Callout Box -->
            <div class="callout callout-info callout-wifi shadow-sm p-3 mb-4">
                <h5 class="font-weight-bold text-info"><i class="fas fa-info-circle mr-2"></i> WiFi Memory Audit</h5>
                <p class="text-secondary mb-2" style="font-size: 14px;">
                    This view displays all WiFi access profiles saved in the device's connection memory. Monitored devices automatically attempt connection to these profiles when within range. Unsecured or open profiles present significant threats.
                </p>
                <div class="row mt-2" style="font-size: 12px;">
                    <div class="col-md-6 border-right">
                        <span class="font-weight-bold text-dark d-block mb-1">Security Vulnerabilities:</span>
                        <ul class="pl-3 mb-0 text-muted">
                            <li><b class="text-danger">Open / WEP networks:</b> Do not require passwords or use weak, legacy encryption. Hackers can spoof these SSIDs to execute <b>Evil-Twin attacks</b> and intercept all device network traffic.</li>
                        </ul>
                    </div>
                    <div class="col-md-6 pl-md-3 mt-2 mt-md-0">
                        <span class="font-weight-bold text-dark d-block mb-1">Location Analysis:</span>
                        <ul class="pl-3 mb-0 text-muted">
                            <li>The collection of SSIDs (e.g. "Work-WiFi", "Hotel-Guest") reveals a geographical trace of the locations the device owner regularly frequents.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Insecure warnings count banner -->
            <?php if ($vulnerableCount > 0): ?>
                <div class="alert alert-danger shadow-sm mb-4">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Security Alert: Vulnerable WiFi Profiles Detected</h5>
                    <span>We detected <b><?= $vulnerableCount ?></b> unsecured/open network profiles. The device is vulnerable to automatic hijacking near these locations.</span>
                </div>
            <?php endif; ?>

            <!-- Grid of Cards (Replaces the generic table) -->
            <div class="row">
                <?php if (empty($uniqueNetworks)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="empty-state">
                            <i class="fas fa-wifi fa-3x text-muted mb-3"></i>
                            <h4 class="text-secondary">No Saved WiFi Networks Found</h4>
                            <p class="text-muted">WiFi profile extraction data will appear here once retrieved.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($uniqueNetworks as $net): ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="card wifi-card <?= $net['insecure'] ? 'insecure-wifi' : 'secure-wifi' ?> h-100 shadow-sm">
                                <div class="card-body d-flex flex-column justify-content-between p-3">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="card-title font-weight-bold text-truncate mb-0" style="max-width: 80%;" title="<?= esc($net['ssid']) ?>">
                                                <i class="fas fa-wifi text-secondary mr-2" style="font-size: 14px;"></i><?= esc($net['ssid']) ?>
                                            </h5>
                                            <?php if ($net['insecure']): ?>
                                                <span class="badge badge-danger" title="Unsecured Network Profile"><i class="fas fa-unlock mr-1"></i>OPEN</span>
                                            <?php else: ?>
                                                <span class="badge badge-success" title="Secure Encrypted Network"><i class="fas fa-lock mr-1"></i>SECURE</span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="wifi-details" style="font-size: 13px;">
                                            <div class="mb-1 text-muted">
                                                <span class="font-weight-bold text-secondary">Security Type:</span>
                                                <span class="badge badge-light border"><?= esc($net['security']) ?></span>
                                            </div>
                                            <div class="mb-1 text-muted">
                                                <span class="font-weight-bold text-secondary">BSSIDs Seen:</span>
                                                <span class="text-dark font-weight-normal"><?= count($net['bssid']) ?> APs</span>
                                            </div>
                                            <?php if ($net['is_hidden']): ?>
                                                <div class="mb-1 text-muted">
                                                    <span class="badge badge-warning text-dark"><i class="fas fa-eye-slash mr-1"></i>Hidden SSID</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="border-top pt-2 mt-3 text-muted d-flex justify-content-between align-items-center" style="font-size: 11px;">
                                        <span>
                                            <i class="fas fa-fingerprint mr-1"></i>
                                            <span class="text-truncate d-inline-block" style="max-width: 140px;" title="AP Address: <?= esc(implode(', ', $net['bssid'])) ?>">
                                                <?= esc($net['bssid'][0]) ?>
                                            </span>
                                        </span>
                                        <?php if ($net['first_seen']): ?>
                                            <span title="First observed extraction timestamp">
                                                <i class="fas fa-clock mr-1"></i>
                                                <script>document.write(new Date(<?= $net['first_seen'] ?>).toLocaleDateString());</script>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </section>
</div>
