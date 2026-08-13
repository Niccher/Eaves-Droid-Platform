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

    // ── Python-only algorithms ──

    'SMS Phishing Keyword Heuristic' => [
        'how'    => 'Scans each SMS body against a curated list of phishing / social-engineering keyword indicators (urgency, impersonation, credential requests, unusual links). A message is flagged once it accumulates enough keyword hits.',
        'means'  => 'A high score indicates the message uses language patterns typical of smishing attacks — urgency, impersonation of trusted entities, suspicious links, or credential requests. Because it is keyword-based, novel phrasings may slip through.',
        'icon'   => 'fas fa-brain',
        'color'  => 'danger',
    ],
    'Contact Graph Outlier Model' => [
        'how'    => 'Builds a graph where contacts are nodes and shared phone-number prefixes / name similarity are edges. Contacts with degree 0 (orphaned) or unusually low connectivity are flagged.',
        'means'  => 'Flagged contacts are structurally isolated from the rest of the network — they share no number or name relationship with anyone else. This can reveal synthetic contacts, newly-added numbers, or covert nodes in a social network.',
        'icon'   => 'fas fa-share-alt',
        'color'  => 'success',
    ],
    'Isolation Forest Outlier Detection' => [
        'how'    => 'Treats each call as a multi-dimensional point (duration, hour, day-of-week, direction, network type). The Isolation Forest algorithm randomly partitions the feature space — outliers require fewer splits to isolate, producing a low anomaly score.',
        'means'  => 'A call flagged as anomalous deviates from the caller\'s normal patterns across multiple dimensions simultaneously — for example, a long-duration call at 3 AM to an international number. Such multi-factor outliers are unlikely to be innocent.',
        'icon'   => 'fas fa-tree',
        'color'  => 'warning',
    ],
    'App Manifest Anomaly Scanner (PCA)' => [
        'how'    => 'Extracts manifest-style features from each app (package-name patterns, sensitive permissions, name length) and fits a PCA model. Apps whose features are poorly reconstructed by the low-dimensional model have high reconstruction error and are flagged.',
        'means'  => 'A flagged app combines declarations rarely seen in the rest of the fleet — for example, a calculator requesting SMS permissions and background location. PCA captures feature deviance without needing a curated signature list.',
        'icon'   => 'fas fa-network-wired',
        'color'  => 'info',
    ],
    'Suspicious File Metadata Scanner' => [
        'how'    => 'Flags files whose metadata (high-risk extension, location inside Android data directories, deep paths, hidden names, suspicious keywords) suggests encrypted payloads, ransomware artefacts, or hidden executables. Works on stored metadata only.',
        'means'  => 'Encrypted or disguised payloads often use high-risk extensions in app-private directories. Because this runs on metadata alone, it cannot verify byte-level entropy — a file with raw content may warrant manual review.',
        'icon'   => 'fas fa-file-contract',
        'color'  => 'secondary',
    ],
    'Activity Sequence Predictor (MLP)' => [
        'how'    => 'Trains a small multi-layer perceptron (MLP) on chronologically ordered app-usage timestamps to model normal activity rhythms. At inference the model predicts the next usage time; a large prediction error flags the transition as unexpected.',
        'means'  => 'An unexpected activity sequence — e.g. heavy usage at an atypical hour — does not match the user\'s learned behaviour profile. The MLP is a lightweight non-linear sequence predictor, not a recurrent LSTM network.',
        'icon'   => 'fas fa-chart-line',
        'color'  => 'danger',
    ],
    'One-Class SVM System-State Profiler' => [
        'how'    => 'Collects system telemetry (CPU load, memory usage, battery temperature, active radios) at regular intervals. A one-class SVM learns the compact region of normal operational states; any point falling outside this decision boundary is flagged.',
        'means'  => 'Abnormal system states — high CPU with elevated battery temp while the screen is off, radios active but no user interaction — strongly indicate background malicious processes: cryptominers, C2 beaconing, or data exfiltration. The SVM catches combined deviations a single-threshold rule would miss.',
        'icon'   => 'fas fa-microchip',
        'color'  => 'dark',
    ],

    // ── Both-compat algorithms (not yet in PHP implementation) ──

    'Extension Mismatch Scanner' => [
        'how'    => 'Reads the first few bytes (magic bytes) of each file and compares them against known file-type signatures. If the detected MIME type contradicts the file\'s extension, a mismatch is recorded.',
        'means'  => 'A mismatch indicates deliberate renaming — a .jpg that is actually a ZIP archive, or a .txt that is an executable. This is a common obfuscation technique used to bypass security scans or trick users into opening malicious files.',
        'icon'   => 'fas fa-file-signature',
        'color'  => 'secondary',
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

// ── Category pill gradient colors ────────────────────────────────────────────
$pillGradient = ['danger', 'warning', 'info', 'primary', 'success', 'secondary', 'dark', 'danger'];
$pillIdx = 0;
$catPillColors = [];
foreach ($catMeta as $cat => $meta) {
    $catPillColors[$cat] = $pillGradient[$pillIdx % count($pillGradient)];
    $pillIdx++;
}

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
                        <li class="breadcrumb-item active"><i class="fas fa-table mr-1"></i>Results</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <?php if (isset($can_see_advanced) && !$can_see_advanced): ?>
            <!-- Advanced results are locked (Gold sees basic only) -->
            <div class="callout callout-danger d-flex flex-wrap align-items-center">
                <div class="mr-auto pr-3">
                    <i class="fas fa-crown fa-lg text-danger mr-2"></i>
                    <strong>You're viewing basic anomaly results.</strong>
                    <span class="d-block text-muted small">Upgrade to <span class="badge badge-danger"><?= ucfirst($advanced_plan ?? 'platinum') ?></span> to unlock
                    advanced &amp; deep-learning algorithms (BERT phishing, GCN graphs, isolation forests, LSTMs &amp; more).</span>
                </div>
                <a href="<?= esc($advanced_upgrade_url ?? '#') ?>" class="btn btn-secondary text-white font-weight-bold">
                    <i class="fas fa-arrow-up mr-1"></i> See Advanced Results
                </a>
            </div>
            <?php elseif (isset($can_see_advanced) && $can_see_advanced && ($current_plan ?? '') !== 'platinum'): ?>
            <div class="alert alert-success d-flex flex-wrap align-items-center shadow-sm" role="alert">
                <div class="mr-auto pr-3">
                    <i class="fas fa-check-circle fa-lg text-success mr-2"></i>
                    <strong>Advanced results unlocked.</strong>
                    <span class="d-block text-muted small">Your plan includes advanced &amp; deep-learning anomaly algorithms.</span>
                </div>
                <span class="badge badge-success"><i class="fas fa-crown mr-1"></i>Platinum Intelligence</span>
            </div>
            <?php endif; ?>


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


            <?php if (empty($results) && !empty($has_report)): ?>
            <!-- Empty state (report ran but no anomalies found) -->
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shield-alt fa-4x text-primary mb-3"></i>
                    <h4 class="text-primary">No Anomalies Detected</h4>
                    <p class="text-muted">All selected algorithms ran successfully and found no anomalies in your data. This is a great sign!</p>
                </div>
            </div>

            <?php elseif (empty($results)): ?>
            <!-- No report run yet → prompt to run anomaly detection -->
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <i class="fas fa-bug fa-4x text-danger mb-3"></i>
                    <h3 class="font-weight-bold text-dark">No Anomaly Report Yet</h3>
                    <p class="text-muted mx-auto" style="max-width:560px;">
                        Your anomaly report will appear here once a scan has been run.
                        Kick off a scan now to detect suspicious activity in your SMS,
                        calls, contacts, location, apps, files and device.
                    </p>
                    <a href="<?= esc($run_url ?? base_url('analysis/anomalies/run')) ?>"
                       class="btn btn-danger btn-lg font-weight-bold shadow-sm mt-2">
                        <i class="fas fa-play-circle mr-2"></i> Run Anomaly Detection
                    </a>
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

                <!-- Category pills (no "All" pill — first category is active) -->
                <div class="card-body pt-2 pb-0">
                    <ul class="nav nav-pills nav-fill flex-wrap" id="cat-tabs" role="tablist" style="gap:.25rem;">
                        <?php
                        $firstCat = true;
                        foreach ($grouped as $catName => $catRows):
                            $paneId   = 'pane-' . preg_replace('/[^a-z0-9]/i', '_', strtolower($catName));
                            $tabId    = 'tab-'  . preg_replace('/[^a-z0-9]/i', '_', strtolower($catName));
                            $highCnt  = count(array_filter($catRows, fn($r) => ($r['severity'] ?? '') === 'High'));
                            $pillClr  = $catPillColors[$catName] ?? 'primary';
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $firstCat ? 'active' : '' ?>" id="<?= $tabId ?>" data-toggle="pill"
                               href="#<?= $paneId ?>" role="tab" aria-controls="<?= $paneId ?>" aria-selected="<?= $firstCat ? 'true' : 'false' ?>"
                               data-pill-color="<?= $pillClr ?>">
                                <i class="<?= $catMeta[$catName]['icon'] ?? 'fas fa-circle' ?> mr-1 text-<?= $pillClr ?>"></i><?= esc($catName) ?>
                                <span class="badge badge-<?= $pillClr ?> ml-1"><?= count($catRows) ?></span>
                                <?php if ($highCnt > 0): ?>
                                <span class="badge badge-danger ml-1" title="<?= $highCnt ?> high severity"><i class="fas fa-bolt"></i></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php $firstCat = false; endforeach; ?>
                    </ul>
                </div>


                <!-- Tab panes -->
                <div class="card-body pt-3">
                    <div class="tab-content" id="cat-tabs-content">

                        <!-- Per-category panes (each has nested algorithm sub-tabs) -->
                        <?php
                        $firstCatPane = true;
                        foreach ($grouped as $catName => $catRows):
                            $paneId = 'pane-' . preg_replace('/[^a-z0-9]/i', '_', strtolower($catName));
                            $rowsByAlg = [];
                            foreach ($catRows as $row) {
                                $alg = $row['algorithm'] ?? 'Unknown';
                                $rowsByAlg[$alg][] = $row;
                            }
                        ?>
                        <div class="tab-pane fade <?= $firstCatPane ? 'show active' : '' ?>" id="<?= $paneId ?>" role="tabpanel">
<!-- Algorithm sub-tab nav -->
                            <ul class="nav nav-tabs flex-wrap mb-0" id="<?= $paneId ?>-alg-nav" role="tablist" style="border-bottom:2px solid #dee2e6;">
                                <?php $firstAlg = true; foreach ($rowsByAlg as $algName => $algRows):
                                    $am      = $algMeta[$algName] ?? null;
                                    $algHash = md5($paneId . $algName);
                                    $highN   = count(array_filter($algRows, fn($r) => ($r['severity'] ?? '') === 'High'));
                                ?>
                                <li class="nav-item">
                                 <a class="nav-link d-flex align-items-center <?= $firstAlg ? 'active' : '' ?>"
                                        id="algtab-<?= $algHash ?>"
                                        data-toggle="tab"
                                        href="#algpane-<?= $algHash ?>"
                                        role="tab"
                                        style="font-size:.82rem; padding:.45rem .9rem;">
                                          <i class="<?= $am['icon'] ?? 'fas fa-cog' ?> mr-1 text-<?= $am['color'] ?? 'primary' ?>"></i>
                                          <?= esc($algName) ?>
                                          <span class="badge badge-<?= $am['color'] ?? 'primary' ?> ml-2"><?= count($algRows) ?></span>
                                         <?php if ($highN > 0): ?>
                                         <span class="badge badge-danger ml-1" title="<?= $highN ?> High severity"><i class="fas fa-bolt"></i></span>
                                         <?php endif; ?>
                                     </a>
                                 </li>
                                 <?php $firstAlg = false; endforeach; ?>
                            </ul>

                            <!-- Algorithm sub-tab panes -->
                            <div class="tab-content border border-top-0 rounded-bottom" style="background:#fff;">
                                <?php $firstAlg = true; foreach ($rowsByAlg as $algName => $algRows):
                                    $am      = $algMeta[$algName] ?? null;
                                    $mc      = $am['color'] ?? 'secondary';
                                    $algHash = md5($paneId . $algName);
                                ?>
                                <div class="tab-pane fade <?= $firstAlg ? 'show active' : '' ?> p-3"
                                     id="algpane-<?= $algHash ?>" role="tabpanel">

<!-- Explanation card — expanded by default (no collapse class) -->
                                    <?php if ($am): ?>
                                    <div class="card card-info card-outline shadow-sm mb-3">
                                        <div class="card-header py-2">
                                            <h3 class="card-title mb-0">
                                                <i class="<?= $am['icon'] ?? 'fas fa-cog' ?> text-primary mr-2"></i>
                                                <strong><?= esc($algName) ?></strong>
                                                <small class="text-muted ml-2">— algorithm details</small>
                                            </h3>
                                            <div class="card-tools">
                                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <!-- card-body is shown by default; data-card-widget="collapse" handles toggle -->
                                        <div class="card-body py-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="text-primary font-weight-bold mb-2">
                                                        <i class="fas fa-cogs mr-1"></i> How it works
                                                    </h6>
                                                    <p class="text-muted mb-0" style="font-size:.875rem; line-height:1.6;"><?= esc($am['how']) ?></p>
                                                </div>
                                                <div class="col-md-6 mt-3 mt-md-0">
                                                    <h6 class="text-primary font-weight-bold mb-2">
                                                        <i class="fas fa-lightbulb mr-1"></i> What the results mean
                                                    </h6>
                                                    <p class="text-muted mb-0" style="font-size:.875rem; line-height:1.6;"><?= esc($am['means']) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Paginated results table — only rows for this algorithm -->
                                    <?php renderTable('alg_' . $algHash, $algRows, $sevMap, $algMeta, $engine_meta); ?>

                                </div>
                                <?php $firstAlg = false; endforeach; ?>
                            </div><!-- /.tab-content (algorithm level) -->

                        </div>
                        <?php $firstCatPane = false; endforeach; ?>

                    </div><!-- /.tab-content -->
                </div><!-- /.card-body -->
            </div><!-- /.card -->

            <?php endif; // end empty check ?>

            <!-- ── Action Buttons ── -->
            <div class="row mt-3 mb-4">
                <div class="col-12 d-flex justify-content-center flex-wrap" style="gap:.5rem;">
                    <a href="<?= base_url('analysis/anomalies/algorithms') ?>" class="btn btn-outline-primary font-weight-bold">
                        <i class="fas fa-sliders-h mr-1"></i> Change Algorithms
                    </a>
                    <button type="button" class="btn btn-warning font-weight-bold shadow-sm" id="btn-rerun">
                        <i class="fas fa-redo mr-1"></i> Re-run Detection
                    </button>
                    <a href="<?= base_url('analysis/anomalies/run') ?>" class="btn btn-primary font-weight-bold shadow-sm" id="btn-run-now" style="display:none;">
                        <i class="fas fa-play mr-1"></i> Run Now
                    </a>
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

<style>
/* Category pill active border */
.nav-pills .nav-link[data-pill-color] {
    border-left: 3px solid transparent;
    transition: border-color 0.2s;
}
</style>

<script>
// Bootstrap 4 / AdminLTE color mapping for pill borders
var pillColorMap = {
    danger: '#dc3545', warning: '#ffc107', info: '#17a2b8',
    primary: '#007bff', success: '#28a745', secondary: '#6c757d',
    dark: '#343a40', light: '#f8f9fa'
};
document.querySelectorAll('#cat-tabs a[data-toggle="pill"]').forEach(function (el) {
    el.addEventListener('shown.bs.tab', function () {
        var clr = this.getAttribute('data-pill-color');
        if (clr) this.style.borderLeftColor = pillColorMap[clr] || '#007bff';
    });
});
document.addEventListener('DOMContentLoaded', function () {
    var active = document.querySelector('#cat-tabs a.active[data-pill-color]');
    if (active) {
        var clr = active.getAttribute('data-pill-color');
        if (clr) active.style.borderLeftColor = pillColorMap[clr] || '#007bff';
    }
});
</script>

<!-- SweetAlert2 dialog scripts -->
<script>
document.getElementById('btn-rerun').addEventListener('click', function () {
    Swal.fire({
        title: 'Re-run Detection',
        html:
            '<div class="text-left" style="font-size:0.95rem;">' +
            '<p>Choose the analysis scope:</p>' +
            '<div class="custom-control custom-radio mb-2">' +
            '<input type="radio" id="rerun-full" name="rerunScope" value="full" class="custom-control-input" checked>' +
            '<label class="custom-control-label font-weight-bold" for="rerun-full">' +
            '<i class="fas fa-database text-primary mr-1"></i> Full Scan</label>' +
            '<small class="d-block text-muted ml-4">Re-analyze all data from scratch</small>' +
            '</div>' +
            '<div class="custom-control custom-radio">' +
            '<input type="radio" id="rerun-incr" name="rerunScope" value="incremental" class="custom-control-input">' +
            '<label class="custom-control-label font-weight-bold" for="rerun-incr">' +
            '<i class="fas fa-plus-circle text-success mr-1"></i> New Data Only</label>' +
            '<small class="d-block text-muted ml-4">Only analyze entries since the last analysis</small>' +
            '</div>' +
            '</div>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-redo mr-1"></i> Run',
        cancelButtonText: 'Cancel',
        preConfirm: function () {
            var scope = document.querySelector('input[name="rerunScope"]:checked');
            return scope ? scope.value : 'full';
        }
    }).then(function (result) {
        if (result.isConfirmed) {
            window.location.href = '<?= base_url('analysis/anomalies/run') ?>?scope=' + result.value;
        }
    });
});
</script>
