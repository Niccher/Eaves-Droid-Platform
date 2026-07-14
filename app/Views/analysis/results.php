<?php
// ── Algorithm metadata: explanation + result meaning ────────────────────────
$algMeta = [
    'Frequency Spike Detector' => [
        'how'    => 'Uses Z-Score statistical analysis. All messages are bucketed into 15-minute windows and a mean + standard deviation is computed. Any window whose count exceeds mean + 2.5 σ is flagged as a spike.',
        'means'  => 'A flagged window means an unusually high number of messages arrived in a short period — consistent with bulk SMS campaigns, spyware exfiltration triggers, or social-engineering attacks.',
        'icon'   => 'fas fa-chart-line',
        'color'  => 'danger',
    ],
    'Time-Pattern Analyser' => [
        'how'    => 'Scans each message timestamp and flags any that fall inside the night window (23:00 – 05:00). No training data required; purely rule-based.',
        'means'  => 'Messages at off-hours may indicate covert communication, automated exfiltration, or contact with actors in distant time-zones.',
        'icon'   => 'fas fa-moon',
        'color'  => 'warning',
    ],
    'Sender Cluster Analysis (K-Means)' => [
        'how'    => 'Feature-engineers each sender into a 3-dimensional vector [message count, night activity ratio, average body length], normalises features to [0,1], then runs PHP-ML K-Means (k=3). Senders landing in singleton or very small clusters are flagged as outliers.',
        'means'  => 'A flagged sender exhibits a combination of volume, timing, and message-length that is distinctly different from all other senders — a strong indicator of automated/scripted senders or unknown threat actors.',
        'icon'   => 'fas fa-project-diagram',
        'color'  => 'danger',
    ],
    'New-Contact Frequency Monitor' => [
        'how'    => 'Counts contacts added per day and computes a 30-day rolling mean. Any day exceeding 3× the mean (minimum threshold: 3) is flagged.',
        'means'  => 'A sudden burst of new contact additions often signals data harvesting (bulk-export from another device), social engineering preparation, or malware syncing a remote contact list.',
        'icon'   => 'fas fa-user-plus',
        'color'  => 'success',
    ],
    'Duplicate & Anomaly Detector' => [
        'how'    => 'Strips all non-digit characters from each phone number and builds a hash-map. When a second contact shares the exact same normalised number, a collision is recorded.',
        'means'  => 'Duplicate numbers can indicate impersonation — an attacker adding a contact with a trusted person\'s number under a different name to intercept routed messages or calls.',
        'icon'   => 'fas fa-copy',
        'color'  => 'success',
    ],
    'Short-Call Burst Detector' => [
        'how'    => 'Filters calls shorter than 10 seconds, sorts them chronologically, and uses a sliding window (30 minutes) to find windows containing ≥ 3 such calls.',
        'means'  => 'Short-call bursts are a classic reconnaissance pattern — probing whether a number is active, or acting as an out-of-band signalling mechanism for malware command-and-control.',
        'icon'   => 'fas fa-bolt',
        'color'  => 'warning',
    ],
    'Night-Activity Monitor' => [
        'how'    => 'Rule-based: any call whose timestamp falls between 23:00 and 05:00 (local time) is flagged without further statistical analysis.',
        'means'  => 'Calls at night — especially to unsaved numbers — may indicate covert communication or deliberate timing to avoid detection by the device owner.',
        'icon'   => 'fas fa-moon',
        'color'  => 'warning',
    ],
    'Geo-Fence Violation Detector' => [
        'how'    => 'Computes the geographic centroid of all location points and calculates a home-zone radius (mean distance + 1 σ from centroid using Haversine formula). Points beyond 1.5× this radius are flagged.',
        'means'  => 'A device detected far outside its normal geographic range may indicate the device (or its owner) has been moved, or GPS spoofing is occurring.',
        'icon'   => 'fas fa-map-marked-alt',
        'color'  => 'primary',
    ],
    'Travel Speed Anomaly' => [
        'how'    => 'Calculates the Haversine distance between consecutive chronological location points and divides by elapsed seconds to get speed in km/h. Any speed > 900 km/h (commercial airspeed) is flagged as physically impossible.',
        'means'  => 'Impossible travel speeds indicate GPS spoofing, VPN-based location faking, or data corruption — all of which are common in device-monitoring evasion.',
        'icon'   => 'fas fa-tachometer-alt',
        'color'  => 'primary',
    ],
    'DBSCAN Trajectory Clustering' => [
        'how'    => 'Runs PHP-ML DBSCAN (epsilon=0.01°≈1 km, minSamples=2) on all [latitude, longitude] pairs. Points that do not belong to any cluster (noise points) are flagged as anomalous trajectory positions.',
        'means'  => 'Noise points represent isolated, one-off location coordinates far from any frequent zone — possible indicators of a surveillance visit, a dead-drop location, or GPS manipulation.',
        'icon'   => 'fas fa-dot-circle',
        'color'  => 'primary',
    ],
    'Package Reputation Scanner' => [
        'how'    => 'Checks each installed package name against a curated list of suspicious keywords (spy, hidden, track, keylog, ghost, etc.) using str_contains(). Off-hours installation time compounds severity.',
        'means'  => 'A matched package name pattern is a strong indicator of stalkerware, keyloggers, or remote-access trojans. Off-hours installation (without user awareness) elevates the severity to High.',
        'icon'   => 'fas fa-search',
        'color'  => 'info',
    ],
    'Permission Anomaly Detector' => [
        'how'    => 'Counts how many of 13 predefined sensitive permissions (READ_SMS, RECORD_AUDIO, ACCESS_FINE_LOCATION, DEVICE_ADMIN, etc.) each app requests. Z-Score is computed across all apps; any app exceeding mean + 2 σ is flagged.',
        'means'  => 'Over-privileged apps — requesting far more sensitive permissions than peers — are a primary attack vector. Legitimate flashlights don\'t need SMS access; flagged apps warrant manual review.',
        'icon'   => 'fas fa-lock-open',
        'color'  => 'info',
    ],
    'File Creation Spike Detector' => [
        'how'    => 'Aggregates file creation events per day. Computes the 30-day mean and flags any day where count exceeds 3× the mean.',
        'means'  => 'Sudden file creation spikes can indicate bulk data staging for exfiltration, ransomware file encryption activity, or a large app update installing payload files.',
        'icon'   => 'fas fa-file-medical',
        'color'  => 'secondary',
    ],
    'Screen-Time Anomaly Detector' => [
        'how'    => 'Aggregates daily screen-on minutes, computes the 30-day population mean and standard deviation. Days with |Z-score| > 2 are flagged as anomalous.',
        'means'  => 'Unusually high screen time may indicate the device is being used by a different person or for automated tasks. Anomalously low usage can suggest the device is being hidden or turned off intentionally.',
        'icon'   => 'fas fa-mobile-alt',
        'color'  => 'danger',
    ],
    'App-Switch Rate Monitor' => [
        'how'    => 'Groups app-usage events by hour and counts the number of distinct app transitions per hour. Periods exceeding 60 switches/hour are flagged.',
        'means'  => 'Bot-like or scripted app-switching (e.g., an automation framework, spyware scanning all apps, or a RAT executing commands) produces switch rates that far exceed normal human interaction patterns.',
        'icon'   => 'fas fa-random',
        'color'  => 'danger',
    ],
    'Hardware Change Detector' => [
        'how'    => 'Compares the current device profile snapshot against the most recent previous snapshot across 5 identifiers: IMEI, serial number, build fingerprint, Android ID, and MAC address.',
        'means'  => 'Any change to IMEI or Android ID is a high-severity event — these are hardware-level identifiers that should never change under normal use. Changes may indicate device replacement, firmware flashing, or identifier spoofing.',
        'icon'   => 'fas fa-microchip',
        'color'  => 'dark',
    ],
    'Network Profile Monitor' => [
        'how'    => 'Compares each Wi-Fi SSID seen in network logs against a known-safe list (currently empty — all SSIDs are considered new). Flags VPN connections and unknown SSIDs.',
        'means'  => 'Connections to unknown Wi-Fi networks expose the device to man-in-the-middle attacks. VPN usage may indicate an attempt to mask network traffic from the monitoring system.',
        'icon'   => 'fas fa-wifi',
        'color'  => 'dark',
    ],
];

// ── Category metadata ────────────────────────────────────────────────────────
$catMeta = [
    'SMS'           => ['icon' => 'fas fa-sms',           'color' => 'danger'],
    'Contacts'      => ['icon' => 'fas fa-address-book',  'color' => 'success'],
    'Call Log'      => ['icon' => 'fas fa-phone',         'color' => 'warning'],
    'Location'      => ['icon' => 'fas fa-map-marker-alt','color' => 'primary'],
    'Installed Apps'=> ['icon' => 'fas fa-th-large',      'color' => 'info'],
    'Files'         => ['icon' => 'fas fa-folder-open',   'color' => 'secondary'],
    'Activity'      => ['icon' => 'fas fa-heartbeat',     'color' => 'danger'],
    'Device Info'   => ['icon' => 'fas fa-microchip',     'color' => 'dark'],
];

// ── Group results by category ─────────────────────────────────────────────────
$grouped = [];
foreach ($results as $row) {
    $cat = $row['category'] ?? 'Other';
    $grouped[$cat][] = $row;
}
ksort($grouped);

// ── Severity map (badge + icon) ───────────────────────────────────────────────
$sevMap = $severity_map ?? [
    'High'   => ['badge' => 'danger',  'icon' => 'fas fa-angle-double-up'],
    'Medium' => ['badge' => 'warning', 'icon' => 'fas fa-angle-up'],
    'Low'    => ['badge' => 'info',    'icon' => 'fas fa-angle-right'],
];
?>
<!-- Step 3 – Anomaly Detection Wizard: Results -->
<div class="content-wrapper">

    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
                        Detection Results
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>"><i class="fas fa-home mr-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>"><i class="fas fa-brain mr-1"></i>Intelligence</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis/anomalies') ?>"><i class="fas fa-bug mr-1"></i>Anomaly Detection</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis/anomalies/algorithms?engine=' . esc($selected_engine)) ?>"><i class="fas fa-sliders-h mr-1"></i>Algorithms</a></li>
                        <li class="breadcrumb-item active"><i class="fas fa-table mr-1"></i>Results</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- ── Engine + Active Algorithms Banner ── -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card card-outline card-<?= esc($engine_meta['badge']) ?> shadow-sm mb-0">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
                                <!-- Engine badge -->
                                <span class="badge badge-<?= esc($engine_meta['badge']) ?> px-3 py-2" style="font-size:.85rem;">
                                    <i class="<?= esc($engine_meta['icon']) ?> mr-1"></i>
                                    <?= esc($engine_meta['label']) ?>
                                </span>
                                <span class="text-muted small font-weight-bold">Active algorithms:</span>
                                <?php
                                $algCatalogue = [
                                    'sms'         => ['sms_freq' => 'Frequency Spike', 'sms_time' => 'Time-Pattern', 'sms_cluster' => 'Sender K-Means', 'sms_bert' => 'BERT Phishing'],
                                    'contacts'    => ['contacts_freq' => 'Contact Frequency', 'contacts_dup' => 'Duplicate Detector', 'contacts_graph' => 'Graph GCN'],
                                    'call_logs'   => ['calls_burst' => 'Short-Call Burst', 'calls_night' => 'Night Monitor', 'calls_isolation' => 'Isolation Forest'],
                                    'locations'   => ['loc_geofence' => 'Geo-Fence', 'loc_speed' => 'Speed Anomaly', 'loc_dbscan' => 'DBSCAN Cluster'],
                                    'apps'        => ['apps_rep' => 'Pkg Reputation', 'apps_perm' => 'Permission Detector', 'apps_autoencoder' => 'Autoencoder'],
                                    'files'       => ['files_spike' => 'File Spike', 'files_ext' => 'Extension Mismatch', 'files_entropy' => 'Entropy Scanner'],
                                    'activity'    => ['act_screen' => 'Screen-Time', 'act_switch' => 'App-Switch Rate', 'act_lstm' => 'LSTM Sequence'],
                                    'device_info' => ['dev_hw' => 'HW Change', 'dev_net' => 'Network Profile', 'dev_oneclass' => 'One-Class SVM'],
                                ];
                                $badgeColors = ['sms'=>'danger','contacts'=>'success','call_logs'=>'warning','locations'=>'primary','apps'=>'info','files'=>'secondary','activity'=>'danger','device_info'=>'dark'];
                                $hasActive = false;
                                foreach (($selected_algs ?? []) as $cat => $algIds):
                                    foreach ((array)$algIds as $algId):
                                        $algName = $algCatalogue[$cat][$algId] ?? $algId;
                                        $color   = $badgeColors[$cat] ?? 'secondary';
                                        $hasActive = true;
                                ?>
                                <span class="badge badge-<?= $color ?>" style="font-size:.78rem; padding:.35em .65em;"><?= esc($algName) ?></span>
                                <?php endforeach; endforeach; ?>
                                <?php if (!$hasActive): ?>
                                <span class="text-muted small"><em>All defaults</em></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Summary info-boxes ── -->
            <div class="row mb-3">
                <div class="col-md-3 col-sm-6">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-list-ul"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Findings</span>
                            <span class="info-box-number"><?= $severity_counts['total'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-bolt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">High Severity</span>
                            <span class="info-box-number"><?= $severity_counts['high'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-exclamation"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Medium Severity</span>
                            <span class="info-box-number"><?= $severity_counts['medium'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-info"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Low Severity</span>
                            <span class="info-box-number"><?= $severity_counts['low'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Disclaimer callout ── -->
            <div class="callout callout-info bg-light shadow-sm mb-3">
                <h5>
                    <i class="<?= $selected_engine === 'python' ? 'fab fa-python' : 'fab fa-php' ?> mr-2 text-<?= esc($engine_meta['badge']) ?>"></i>
                    <?= esc($engine_meta['label']) ?> — Live Detection Results
                </h5>
                <p class="mb-0 text-muted">
                    <?php if ($selected_engine === 'python'): ?>
                        The Python engine (Docker microservice) is not yet connected. Results shown use the PHP fallback pipeline. Deploy the Python container to activate deep-learning models.
                    <?php else: ?>
                        Results are computed by the <strong>PHP-ML pipeline</strong> using statistical algorithms (Z-Score, Haversine distance, K-Means, DBSCAN, pattern matching) against your live database. Where a table has no data, a labelled demo finding is shown.
                    <?php endif; ?>
                </p>
            </div>

            <?php if (empty($results)): ?>
            <!-- Empty state -->
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shield-alt fa-4x text-success mb-3"></i>
                    <h4 class="text-success">No Anomalies Detected</h4>
                    <p class="text-muted">All selected algorithms ran successfully and found no anomalies in your data. This is a great sign!</p>
                </div>
            </div>
            <?php else: ?>

            <!-- ── Category Nav Pills ── -->
            <div class="card card-danger card-outline shadow-sm">
                <div class="card-header border-bottom-0 pb-0">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-2 text-danger"></i>
                        Detected Anomalies
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-danger mr-1"><?= count($results) ?> findings</span>
                    </div>
                </div>

                <!-- Category pills -->
                <div class="card-body pt-2 pb-0">
                    <ul class="nav nav-pills nav-fill flex-wrap" id="cat-tabs" role="tablist" style="gap:.25rem;">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-all" data-toggle="pill" href="#pane-all" role="tab" aria-controls="pane-all" aria-selected="true">
                                <i class="fas fa-list-ul mr-1"></i>All
                                <span class="badge badge-light ml-1"><?= count($results) ?></span>
                            </a>
                        </li>
                        <?php foreach ($grouped as $catName => $catRows):
                            $cm = $catMeta[$catName] ?? ['icon' => 'fas fa-circle', 'color' => 'secondary'];
                            $paneId = 'pane-' . preg_replace('/[^a-z0-9]/i', '_', strtolower($catName));
                            $tabId  = 'tab-'  . preg_replace('/[^a-z0-9]/i', '_', strtolower($catName));
                            $highCnt = count(array_filter($catRows, fn($r) => ($r['severity'] ?? '') === 'High'));
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" id="<?= $tabId ?>" data-toggle="pill" href="#<?= $paneId ?>" role="tab" aria-controls="<?= $paneId ?>" aria-selected="false">
                                <i class="<?= $cm['icon'] ?> mr-1"></i><?= esc($catName) ?>
                                <span class="badge badge-<?= $cm['color'] ?> ml-1"><?= count($catRows) ?></span>
                                <?php if ($highCnt > 0): ?>
                                <span class="badge badge-danger ml-1" title="<?= $highCnt ?> high severity"><i class="fas fa-bolt"></i></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Tab panes -->
                <div class="card-body pt-3">
                    <div class="tab-content" id="cat-tabs-content">

                        <!-- ── ALL pane ── -->
                        <div class="tab-pane fade show active" id="pane-all" role="tabpanel">
                            <?php renderTable('all', $results, $sevMap, $algMeta, $engine_meta); ?>
                        </div>

                        <!-- ── Per-category panes ── -->
                        <?php foreach ($grouped as $catName => $catRows):
                            $paneId = 'pane-' . preg_replace('/[^a-z0-9]/i', '_', strtolower($catName));
                            // Collect unique algorithms in this category
                            $algsInCat = array_unique(array_column($catRows, 'algorithm'));
                        ?>
                        <div class="tab-pane fade" id="<?= $paneId ?>" role="tabpanel">

                            <!-- Algorithm explanation cards -->
                            <?php foreach ($algsInCat as $algName):
                                $meta = $algMeta[$algName] ?? null;
                                if (!$meta) continue;
                                $mc = $meta['color'] ?? 'secondary';
                            ?>
                            <div class="card card-<?= $mc ?> card-outline shadow-sm mb-3">
                                <div class="card-header py-2" style="cursor:pointer;"
                                     data-toggle="collapse"
                                     data-target="#alg-explain-<?= md5($paneId . $algName) ?>"
                                     aria-expanded="false">
                                    <h3 class="card-title mb-0">
                                        <i class="<?= $meta['icon'] ?> text-<?= $mc ?> mr-2"></i>
                                        <strong><?= esc($algName) ?></strong>
                                        <small class="text-muted ml-2">— click to see how this algorithm works</small>
                                    </h3>
                                    <div class="card-tools">
                                        <span class="btn btn-tool"><i class="fas fa-chevron-down"></i></span>
                                    </div>
                                </div>
                                <div class="collapse" id="alg-explain-<?= md5($paneId . $algName) ?>">
                                    <div class="card-body py-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-<?= $mc ?> font-weight-bold">
                                                    <i class="fas fa-cogs mr-1"></i> How it works
                                                </h6>
                                                <p class="text-muted mb-0" style="font-size:.875rem;"><?= esc($meta['how']) ?></p>
                                            </div>
                                            <div class="col-md-6 mt-3 mt-md-0">
                                                <h6 class="text-<?= $mc ?> font-weight-bold">
                                                    <i class="fas fa-lightbulb mr-1"></i> What the results mean
                                                </h6>
                                                <p class="text-muted mb-0" style="font-size:.875rem;"><?= esc($meta['means']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <?php renderTable($paneId, $catRows, $sevMap, $algMeta, $engine_meta); ?>
                        </div>
                        <?php endforeach; ?>

                    </div><!-- /.tab-content -->
                </div><!-- /.card-body -->
            </div><!-- /.card -->

            <?php endif; // end empty check ?>

            <!-- ── Action Buttons ── -->
            <div class="row mt-3 mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
                    <button type="button" class="btn btn-outline-danger font-weight-bold" id="btn-reset">
                        <i class="fas fa-trash-restore mr-1"></i> Reset Anomaly Detections
                    </button>
                    <div class="d-flex flex-wrap" style="gap:.5rem;">
                        <a href="<?= base_url('analysis/anomalies') ?>" class="btn btn-outline-secondary font-weight-bold">
                            <i class="fas fa-cogs mr-1"></i> Change Engine
                        </a>
                        <a href="<?= base_url('analysis/anomalies/algorithms?engine=' . esc($selected_engine)) ?>" class="btn btn-outline-primary font-weight-bold">
                            <i class="fas fa-sliders-h mr-1"></i> Change Algorithms
                        </a>
                        <button type="button" class="btn btn-warning font-weight-bold shadow-sm" id="btn-rerun">
                            <i class="fas fa-redo mr-1"></i> Re-run Detection
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div><!-- /.content-wrapper -->

<?php
/**
 * Renders a paginated anomaly table for a given set of rows.
 *
 * @param string $paneId      Unique ID prefix for this table's pagination controls
 * @param array  $rows        Anomaly finding rows
 * @param array  $sevMap      Severity → badge/icon map
 * @param array  $algMeta     Algorithm metadata (used for badge colour)
 * @param array  $engineMeta  Engine metadata (badge colour for engine notes)
 */
function renderTable(string $paneId, array $rows, array $sevMap, array $algMeta, array $engineMeta = []): void
{
    $tableId = 'tbl-' . preg_replace('/[^a-z0-9]/i', '_', $paneId);
    $total   = count($rows);
    $perPage = 30;
    $pages   = max(1, (int) ceil($total / $perPage));
    ?>
    <div class="table-responsive" id="<?= $tableId ?>-wrap">
        <table class="table table-hover table-striped mb-0" id="<?= $tableId ?>">
            <thead class="thead-dark">
                <tr>
                    <th style="width:40px">#</th>
                    <th>Category</th>
                    <th>Detected Anomaly</th>
                    <th style="width:105px">Severity</th>
                    <th>Algorithm Used</th>
                    <th>Engine Notes</th>
                    <th style="width:150px">Timestamp</th>
                </tr>
            </thead>
            <tbody id="<?= $tableId ?>-body">
                <?php foreach ($rows as $i => $row):
                    $sev   = $sevMap[$row['severity']] ?? ['badge' => 'secondary', 'icon' => 'fas fa-circle'];
                    $am    = $algMeta[$row['algorithm'] ?? ''] ?? null;
                    $algColor = $am['color'] ?? 'secondary';
                ?>
                <tr class="result-row" data-page-index="<?= $i ?>" style="display:none;">
                    <td class="text-muted small align-middle"><?= $i + 1 ?></td>
                    <td class="align-middle font-weight-bold text-dark" style="white-space:nowrap;">
                        <i class="<?= esc($row['icon']) ?> mr-1 text-secondary"></i>
                        <?= esc($row['category']) ?>
                    </td>
                    <td class="align-middle small"><?= esc($row['anomaly']) ?></td>
                    <td class="align-middle" style="white-space:nowrap;">
                        <span class="badge badge-<?= $sev['badge'] ?>">
                            <i class="<?= $sev['icon'] ?> mr-1"></i><?= esc($row['severity']) ?>
                        </span>
                    </td>
                    <td class="align-middle" style="white-space:nowrap;">
                        <span class="badge badge-<?= $algColor ?>" style="font-size:.76rem; padding:.35em .6em; white-space:normal; max-width:160px; display:inline-block; text-align:left; line-height:1.3;">
                            <i class="<?= $am['icon'] ?? 'fas fa-cog' ?> mr-1"></i>
                            <?= esc($row['algorithm'] ?? '—') ?>
                        </span>
                    </td>
                    <td class="align-middle" style="font-size:.75rem; color:#555;">
                        <?php if (!empty($row['engine_note'])): ?>
                        <span class="badge badge-light border text-muted"
                              style="white-space:normal; text-align:left; display:inline-block; max-width:220px; line-height:1.3;">
                            <i class="fas fa-microscope mr-1 text-<?= esc($engineMeta['badge'] ?? 'secondary') ?>"></i>
                            <?= esc($row['engine_note']) ?>
                        </span>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="align-middle small" style="white-space:nowrap;">
                        <i class="far fa-clock mr-1 text-muted"></i><?= esc($row['timestamp']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total === 0): ?>
        <div class="text-center py-4 text-muted">
            <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>
            No findings in this category.
        </div>
        <?php endif; ?>
    </div>

    <?php if ($pages > 1): ?>
    <!-- Pagination controls -->
    <div class="d-flex justify-content-between align-items-center mt-2 px-1" id="<?= $tableId ?>-pag-wrap">
        <small class="text-muted" id="<?= $tableId ?>-pag-info"></small>
        <nav aria-label="Results pagination">
            <ul class="pagination pagination-sm mb-0" id="<?= $tableId ?>-pag">
                <!-- generated by JS -->
            </ul>
        </nav>
    </div>
    <?php endif; ?>

    <script>
    (function () {
        var tableId  = <?= json_encode($tableId) ?>;
        var total    = <?= $total ?>;
        var perPage  = <?= $perPage ?>;
        var pages    = <?= $pages ?>;
        var curPage  = 1;

        function showPage(p) {
            curPage = Math.max(1, Math.min(p, pages));
            var rows    = document.querySelectorAll('#' + tableId + '-body .result-row');
            var start   = (curPage - 1) * perPage;
            var end     = start + perPage;
            rows.forEach(function (tr, idx) {
                tr.style.display = (idx >= start && idx < end) ? '' : 'none';
            });

            // Update info text
            var infoEl = document.getElementById(tableId + '-pag-info');
            if (infoEl) {
                var from = Math.min(start + 1, total);
                var to   = Math.min(end, total);
                infoEl.textContent = 'Showing ' + from + '–' + to + ' of ' + total + ' findings';
            }

            // Rebuild pagination UL
            var ul = document.getElementById(tableId + '-pag');
            if (!ul) return;
            ul.innerHTML = '';

            // Prev
            var prev = document.createElement('li');
            prev.className = 'page-item' + (curPage === 1 ? ' disabled' : '');
            prev.innerHTML = '<a class="page-link" href="#" data-p="' + (curPage - 1) + '">&laquo;</a>';
            ul.appendChild(prev);

            // Page numbers (show at most 7 around current)
            var startP = Math.max(1, curPage - 3);
            var endP   = Math.min(pages, curPage + 3);
            if (startP > 1) {
                ul.appendChild(makeLi(1));
                if (startP > 2) ul.appendChild(makeLi('…', true));
            }
            for (var i = startP; i <= endP; i++) ul.appendChild(makeLi(i));
            if (endP < pages) {
                if (endP < pages - 1) ul.appendChild(makeLi('…', true));
                ul.appendChild(makeLi(pages));
            }

            // Next
            var next = document.createElement('li');
            next.className = 'page-item' + (curPage === pages ? ' disabled' : '');
            next.innerHTML = '<a class="page-link" href="#" data-p="' + (curPage + 1) + '">&raquo;</a>';
            ul.appendChild(next);

            // Bind clicks
            ul.querySelectorAll('.page-link').forEach(function (a) {
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    var p = parseInt(this.getAttribute('data-p'));
                    if (!isNaN(p)) showPage(p);
                });
            });
        }

        function makeLi(label, disabled) {
            var li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (label === curPage ? ' active' : '');
            li.innerHTML = '<a class="page-link" href="#" data-p="' + label + '">' + label + '</a>';
            return li;
        }

        // Initialise on load (also re-trigger when tab shown)
        showPage(1);

        // Re-run when Bootstrap tab is shown (rows may have been hidden)
        document.addEventListener('shown.bs.tab', function () { showPage(curPage); });
        document.querySelectorAll('[data-toggle="pill"]').forEach(function (el) {
            el.addEventListener('shown.bs.tab', function () { showPage(1); });
        });
    })();
    </script>
    <?php
}
?>

<!-- SweetAlert2 dialog scripts -->
<script>
document.getElementById('btn-reset').addEventListener('click', function () {
    Swal.fire({
        title: 'Reset Anomaly Settings?',
        text: 'This will clear your selected Engine and Algorithms and return you to Step 1.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash-restore mr-1"></i> Yes, Reset',
        cancelButtonText: 'Cancel'
    }).then(function (result) {
        if (result.isConfirmed) {
            window.location.href = '<?= base_url('analysis/anomalies/results?reset=true') ?>';
        }
    });
});

document.getElementById('btn-rerun').addEventListener('click', function () {
    Swal.fire({
        title: 'Re-run Detection?',
        text: 'This will re-execute the detection pipeline using the same engine and algorithm settings.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-redo mr-1"></i> Yes, Re-run',
        cancelButtonText: 'Cancel'
    }).then(function (result) {
        if (result.isConfirmed) {
            window.location.href = '<?= base_url('analysis/anomalies/results') ?>';
        }
    });
});
</script>
