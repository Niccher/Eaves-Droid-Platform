<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\HTTP\CURLRequest;

/**
 * Mod_Anomalies
 *
 * Provides static / dummy data for the Anomaly Detection wizard,
 * and contains functional PHP-ML-powered detection methods
 * that run the actual statistical and ML algorithms.
 *
 * Algorithm categories supported:
 *  - SMS           : Frequency Spike, Time-Pattern, Sender K-Means Clustering
 *  - Contacts      : New-Contact Frequency, Duplicate Detector
 *  - Call Logs     : Short-Call Burst, Night-Activity Monitor, Isolation Forest
 *  - Locations     : Geo-Fence Violation, Travel Speed Anomaly
 *  - Installed Apps: Package Reputation Scanner, Permission Anomaly Detector
 *  - Files         : File Creation Spike, Extension Mismatch Scanner
 *  - Device Activity: Screen-Time Anomaly, App-Switch Rate Monitor
 *  - Device Info   : Hardware Change Detector, Network Profile Monitor
 *
 * Python engine support:
 *  Delegates Python-only algorithms (BERT, GCN, Isolation Forest, Autoencoder,
 *  Entropy Scanner, LSTM, One-Class SVM) to the external ml-eaves-droid FastAPI
 *  service. Falls back to PHP-ML if the backend is unreachable.
 */
class Mod_Anomalies extends Model
{
    // =========================================================================
    // Engine catalogue
    // =========================================================================

    /**
     * Returns the list of available anomaly-detection engines.
     *
     * @return array
     */
    public function getEngines(): array
    {
        return [
            [
                'id'          => 'php',
                'label'       => 'PHP‑based Engine',
                'subtitle'    => 'Pure PHP · PHP‑ML library',
                'icon'        => 'fab fa-php',
                'icon_color'  => 'text-primary',
                'badges'      => [
                    ['color' => 'success',  'icon' => 'fas fa-check',          'text' => 'No extra setup'],
                    ['color' => 'info',     'icon' => 'fas fa-tachometer-alt', 'text' => 'Fast startup'],
                    ['color' => 'secondary','icon' => 'fas fa-lock',           'text' => 'In-process'],
                ],
                'description' => 'Runs entirely within the PHP runtime. Suitable for most datasets. '
                               . 'Uses PHP‑ML for K-Means clustering, statistical z-score analysis, '
                               . 'and rule-based pattern matching.',
                'default'     => false,
            ],
            [
                'id'          => 'python',
                'label'       => 'Python‑based Engine',
                'subtitle'    => 'Separate Docker container · scikit-learn / PyOD',
                'icon'        => 'fab fa-python',
                'icon_color'  => 'text-warning',
                'badges'      => [
                    ['color' => 'warning', 'icon' => 'fab fa-docker',  'text' => 'Requires Docker'],
                    ['color' => 'primary', 'icon' => 'fas fa-brain',   'text' => 'Deep learning'],
                    ['color' => 'danger',  'icon' => 'fas fa-server',  'text' => 'Separate process'],
                ],
                'description' => 'Offloads computation to an isolated Docker microservice. '
                               . 'Supports advanced models (LSTM, Autoencoders, DBSCAN) and larger datasets.',
                'default'     => false,
            ],
            [
                'id'          => 'both',
                'label'       => 'Hybrid Engine (PHP + Python)',
                'subtitle'    => 'Best of both worlds',
                'icon'        => 'fas fa-project-diagram',
                'icon_color'  => 'text-success',
                'badges'      => [
                    ['color' => 'success', 'icon' => 'fas fa-code',     'text' => 'PHP'],
                    ['color' => 'warning', 'icon' => 'fab fa-python',   'text' => 'Python'],
                    ['color' => 'primary', 'icon' => 'fas fa-cogs',     'text' => 'Auto-split'],
                ],
                'description' => 'Runs PHP‑compatible algorithms locally via PHP and Python‑only '
                               . 'algorithms via the remote Docker backend — automatically. '
                               . 'You get the speed of PHP for traditional stat/rule detectors '
                               . 'and the power of scikit-learn/PyOD for deep learning models.',
                'default'     => true,
            ],
        ];
    }

    // =========================================================================
    // Algorithm catalogue
    // =========================================================================

    /**
     * Builds a random-but-sensible algorithm selection using only PHP-compatible
     * algorithms. Always includes each category's default algorithm, then randomly
     * adds 0 or 1 extra non-default PHP-compatible algorithm per category.
     *
     * Used by index() and the reset handler so users always land on a live result
     * without having to step through the wizard manually.
     *
     * @return array  ['sms' => ['sms_freq', 'sms_time'], 'locations' => ['loc_geofence'], ...]
     */
    public function getRandomPhpAlgorithms(): array
    {
        $categories = $this->getAlgorithmCategories();
        $selected   = [];

        foreach ($categories as $catKey => $cat) {
            // Only PHP-compatible algorithms (compat != 'python')
            $phpAlgs = array_values(array_filter(
                $cat['algorithms'],
                fn($a) => ($a['compat'] ?? 'both') !== 'python'
            ));

            if (empty($phpAlgs)) {
                continue;
            }

            // Always include defaults
            $defaults = array_column(
                array_filter($phpAlgs, fn($a) => !empty($a['default'])),
                'id'
            );

            // Randomly add 0–1 non-default extras
            $nonDefaults = array_values(array_diff(
                array_column($phpAlgs, 'id'),
                $defaults
            ));

            $extras = [];
            if (!empty($nonDefaults) && rand(0, 1)) {
                shuffle($nonDefaults);
                $extras = [reset($nonDefaults)];
            }

            $selected[$catKey] = array_values(array_unique(array_merge($defaults, $extras)));
        }

        return $selected;
    }

    /**
     * Looks up a single algorithm's metadata (compat, name, category, etc.)
     * from the full algorithm catalogue by its unique ID.
     *
     * Used by runPythonDetection() to determine which algorithms should be
     * dispatched to the Python backend vs. handled by PHP-ML.
     *
     * @param  string     $algId  Algorithm ID (e.g. 'sms_bert', 'calls_isolation')
     * @return array|null         Algorithm metadata array, or null if not found
     */
    public function getAlgorithmInfo(string $algId): ?array
    {
        $categories = $this->getAlgorithmCategories();
        foreach ($categories as $catKey => $cat) {
            foreach ($cat['algorithms'] as $alg) {
                if ($alg['id'] === $algId) {
                    // Attach the category key for convenience
                    $alg['category_key'] = $catKey;
                    return $alg;
                }
            }
        }
        return null;
    }

    /**
     * Returns all data categories with their candidate algorithms.
     *
     * @return array
     */
    public function getAlgorithmCategories(): array
    {
        return [
            'sms' => [
                'label'  => 'SMS Messages',
                'icon'   => 'fas fa-sms',
                'color'  => 'danger',
                'algorithms' => [
                    [
                        'id'          => 'sms_freq',
                        'name'        => 'Frequency Spike Detector',
                        'default'     => true,
                        'compat'      => 'both',
                        'description' => 'Detects statistically unusual bursts in message frequency within a rolling time window.',
                        'strengths'   => 'Low false-positive rate; fast; works on small datasets.',
                        'weaknesses'  => 'Misses slow-building patterns; ignores message content.',
                    ],
                    [
                        'id'          => 'sms_time',
                        'name'        => 'Time-Pattern Analyser',
                        'default'     => false,
                        'compat'      => 'both',
                        'description' => 'Identifies messages sent or received during atypical hours relative to the user\'s historical baseline.',
                        'strengths'   => 'Catches night-time anomalies missed by frequency checks.',
                        'weaknesses'  => 'Requires sufficient data; time-zone sensitive.',
                    ],
                    [
                        'id'          => 'sms_cluster',
                        'name'        => 'Sender Cluster Analysis (K-Means)',
                        'default'     => false,
                        'compat'      => 'both',
                        'description' => 'Groups senders into K-Means clusters and flags outlier senders.',
                        'strengths'   => 'Surfaces unknown-sender anomalies; robust on large datasets.',
                        'weaknesses'  => 'Slow on first run; cluster count must be tuned.',
                    ],
                    [
                        'id'          => 'sms_bert',
                        'name'        => 'BERT Semantic Phishing Classifier',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Uses deep learning NLP (Transformer model) to analyze SMS message content for phishing semantics and intent.',
                        'strengths'   => 'Extremely high accuracy at detecting sophisticated social engineering.',
                        'weaknesses'  => 'Requires Python GPU/CPU acceleration; slow startup time.',
                    ],
                ],
            ],

            'contacts' => [
                'label'  => 'Contacts',
                'icon'   => 'fas fa-address-book',
                'color'  => 'success',
                'algorithms' => [
                    [
                        'id'          => 'contacts_freq',
                        'name'        => 'New-Contact Frequency Monitor',
                        'default'     => true,
                        'compat'      => 'both',
                        'description' => 'Flags sudden bursts of new contact additions that exceed the user\'s 30-day rolling average.',
                        'strengths'   => 'Simple; deterministic; near-zero false-positives.',
                        'weaknesses'  => 'Blind to individual suspicious entries; count-only.',
                    ],
                    [
                        'id'          => 'contacts_dup',
                        'name'        => 'Duplicate & Anomaly Detector',
                        'default'     => false,
                        'compat'      => 'both',
                        'description' => 'Detects exact and near-duplicate contact entries and flags contacts with suspicious naming conventions.',
                        'strengths'   => 'Catches social-engineering impersonation attempts.',
                        'weaknesses'  => 'High false-positives with common names.',
                    ],
                    [
                        'id'          => 'contacts_graph',
                        'name'        => 'Graph Relation Outlier Model (GCN)',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Maps contact relationships into a graph network database to identify structural anomalies and orphaned contacts.',
                        'strengths'   => 'Identifies hidden syndicates and spoofed hierarchies.',
                        'weaknesses'  => 'High computation overhead; memory intensive.',
                    ],
                ],
            ],

            'call_logs' => [
                'label'  => 'Call Logs',
                'icon'   => 'fas fa-phone',
                'color'  => 'warning',
                'algorithms' => [
                    [
                        'id'          => 'calls_burst',
                        'name'        => 'Short-Call Burst Detector',
                        'default'     => true,
                        'compat'      => 'both',
                        'description' => 'Flags clusters of very short calls (< 10 s) to the same or different numbers within a 30-minute window.',
                        'strengths'   => 'Highly effective against reconnaissance/beaconing patterns.',
                        'weaknesses'  => 'Can fire on legitimate redial behaviour.',
                    ],
                    [
                        'id'          => 'calls_night',
                        'name'        => 'Night-Activity Monitor',
                        'default'     => false,
                        'compat'      => 'both',
                        'description' => 'Detects calls made or received between 11 PM and 5 AM outside the user\'s normal night patterns.',
                        'strengths'   => 'Direct indicator of covert communication.',
                        'weaknesses'  => 'Night-shift workers will generate many false positives.',
                    ],
                    [
                        'id'          => 'calls_isolation',
                        'name'        => 'Isolation Forest Outlier Detection',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Applies Isolation Forest algorithm to multidimensional call attributes (duration, timestamp, direction, network).',
                        'strengths'   => 'Detects complex combined anomalies (e.g. short incoming call at night from abroad).',
                        'weaknesses'  => 'Requires model fitting phase; results are non-deterministic.',
                    ],
                ],
            ],

            'locations' => [
                'label'  => 'Locations',
                'icon'   => 'fas fa-map-marker-alt',
                'color'  => 'primary',
                'algorithms' => [
                    [
                        'id'          => 'loc_geofence',
                        'name'        => 'Geo-Fence Violation Detector',
                        'default'     => true,
                        'compat'      => 'both',
                        'description' => 'Computes frequently-visited zones and alerts whenever the device is detected outside all known zones.',
                        'strengths'   => 'Intuitive; produces actionable alerts.',
                        'weaknesses'  => 'Requires at least 2 weeks of location data to build zones.',
                    ],
                    [
                        'id'          => 'loc_speed',
                        'name'        => 'Travel Speed Anomaly',
                        'default'     => false,
                        'compat'      => 'both',
                        'description' => 'Calculates travel speed between consecutive location points and flags physically impossible displacements (> 900 km/h).',
                        'strengths'   => 'Zero false-positives for truly impossible speeds.',
                        'weaknesses'  => 'Does not detect plausible-but-suspicious travel.',
                    ],
                    [
                        'id'          => 'loc_dbscan',
                        'name'        => 'DBSCAN Trajectory Clustering',
                        'default'     => false,
                        'compat'      => 'both',
                        'description' => 'Uses Density-Based Spatial Clustering to group location coordinates and isolates anomalous path coordinates.',
                        'strengths'   => 'Finds unstructured paths and deviations without predefined geofences.',
                        'weaknesses'  => 'Computation scales poorly with very large datasets.',
                    ],
                ],
            ],

            'apps' => [
                'label'  => 'Installed Apps',
                'icon'   => 'fas fa-th-large',
                'color'  => 'info',
                'algorithms' => [
                    [
                        'id'          => 'apps_rep',
                        'name'        => 'Package Reputation Scanner',
                        'default'     => true,
                        'compat'      => 'both',
                        'description' => 'Compares installed package names against a curated known-safe list and flags any absent or matching spyware patterns.',
                        'strengths'   => 'Fast; requires no additional analysis.',
                        'weaknesses'  => 'Only as good as the known-safe list; misses novel malware.',
                    ],
                    [
                        'id'          => 'apps_perm',
                        'name'        => 'Permission Anomaly Detector',
                        'default'     => false,
                        'compat'      => 'both',
                        'description' => 'Flags user-installed apps requesting more permissions than 95% of apps in the same declared category.',
                        'strengths'   => 'Catches over-privileged apps effectively.',
                        'weaknesses'  => 'Category self-declaration is unverified on Android.',
                    ],
                    [
                        'id'          => 'apps_autoencoder',
                        'name'        => 'Neural Autoencoder App Classifier',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Trains an Autoencoder network on APK manifest components. Reconstructs features to find apps with abnormal configuration.',
                        'strengths'   => 'Detects zero-day custom spyware masquerading as benign utilities.',
                        'weaknesses'  => 'Blackbox model; difficult to interpret reasons behind alerts.',
                    ],
                ],
            ],

            'files' => [
                'label'  => 'Files',
                'icon'   => 'fas fa-folder-open',
                'color'  => 'secondary',
                'algorithms' => [
                    [
                        'id'          => 'files_spike',
                        'name'        => 'File Creation Spike Detector',
                        'default'     => true,
                        'compat'      => 'both',
                        'description' => 'Monitors total file creation events per day and alerts when a day\'s count exceeds 3× the 30-day average.',
                        'strengths'   => 'Simple and effective for bulk-copy / exfiltration-staging scenarios.',
                        'weaknesses'  => 'Noisy during app updates or system backups.',
                    ],
                    [
                        'id'          => 'files_ext',
                        'name'        => 'Extension Mismatch Scanner',
                        'default'     => false,
                        'compat'      => 'both',
                        'description' => 'Identifies files whose MIME type (from magic bytes) does not match their extension — a common obfuscation technique.',
                        'strengths'   => 'Catches disguised executables and hidden media.',
                        'weaknesses'  => 'I/O intensive; slow on large file sets.',
                    ],
                    [
                        'id'          => 'files_entropy',
                        'name'        => 'File Entropy & Encryption Scanner',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Calculates shannon entropy of file bytes to find encrypted archives or payload assets hidden in assets/ directories.',
                        'strengths'   => 'Reliable detection of packed payloads, ransomware outputs, or hidden executables.',
                        'weaknesses'  => 'Requires reading binary files, leading to high disk read execution times.',
                    ],
                ],
            ],

            'activity' => [
                'label'  => 'Device Activity',
                'icon'   => 'fas fa-heartbeat',
                'color'  => 'danger',
                'algorithms' => [
                    [
                        'id'          => 'act_screen',
                        'name'        => 'Screen-Time Anomaly Detector',
                        'default'     => true,
                        'compat'      => 'both',
                        'description' => 'Identifies days where total screen-on time deviates by more than 2 standard deviations from the 30-day rolling mean.',
                        'strengths'   => 'Catches both excessive and unexpectedly low usage.',
                        'weaknesses'  => 'Does not distinguish legitimate from suspicious use.',
                    ],
                    [
                        'id'          => 'act_switch',
                        'name'        => 'App-Switch Rate Monitor',
                        'default'     => false,
                        'compat'      => 'both',
                        'description' => 'Flags periods with abnormally high app-switching frequency (> 60 switches/hour) indicating automated or scripted behaviour.',
                        'strengths'   => 'Good indicator of bot-like or scripted device use.',
                        'weaknesses'  => 'Power users naturally switch apps frequently.',
                    ],
                    [
                        'id'          => 'act_lstm',
                        'name'        => 'LSTM Sequence Pattern Predictor',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Deep LSTM model predicting subsequent user interaction events. Reports high prediction error as abnormal.',
                        'strengths'   => 'Captures context-sensitive user behaviour flows.',
                        'weaknesses'  => 'High CPU usage during sequence inference.',
                    ],
                ],
            ],

            'device_info' => [
                'label'  => 'Device Info',
                'icon'   => 'fas fa-microchip',
                'color'  => 'dark',
                'algorithms' => [
                    [
                        'id'          => 'dev_hw',
                        'name'        => 'Hardware Change Detector',
                        'default'     => true,
                        'compat'      => 'both',
                        'description' => 'Monitors key device identifiers (IMEI, serial, build fingerprint) across uploads and alerts on any change.',
                        'strengths'   => 'Zero ambiguity when a genuine change occurs.',
                        'weaknesses'  => 'Only detects changes between upload snapshots, not in real time.',
                    ],
                    [
                        'id'          => 'dev_net',
                        'name'        => 'Network Profile Monitor',
                        'default'     => false,
                        'compat'      => 'both',
                        'description' => 'Flags unusual network connectivity — new Wi-Fi SSIDs, APN changes, or VPN activity outside the user\'s normal network profile.',
                        'strengths'   => 'Effective for detecting man-in-the-middle network insertion.',
                        'weaknesses'  => 'Frequent travellers will generate many alerts.',
                    ],
                    [
                        'id'          => 'dev_oneclass',
                        'name'        => 'One-Class SVM System-State Profiler',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Models normal operational bounds of CPU, RAM, battery temperature, and active radios to find abnormal system states.',
                        'strengths'   => 'Discovers background malware, crypto-miners, or active covert processes.',
                        'weaknesses'  => 'Easily influenced by heavy usage (gaming, charging).',
                    ],
                ],
            ],
        ];
    }

    // =========================================================================
    // PHP-ML Detection Algorithms
    // =========================================================================

    /**
     * SMS – Frequency Spike Detector
     *
     * Uses Z-Score analysis: flags any 15-minute window where the message
     * count is more than 2.5 standard deviations above the mean window count.
     *
     * @param  array $smsRows  Rows from sms table: [{body, date, address, type}, ...]
     * @return array           Detected anomaly rows
     */
    public function detectSmsFrequencySpike(array $smsRows): array
    {
        if (empty($smsRows)) {
            return $this->staticFallback('sms_freq');
        }

        // Bucket messages into 15-minute windows
        $buckets = [];
        foreach ($smsRows as $row) {
            $ts     = strtotime($row['date'] ?? 'now');
            $bucket = floor($ts / 900); // 900 seconds = 15 min
            $buckets[$bucket] = ($buckets[$bucket] ?? 0) + 1;
        }

        $counts = array_values($buckets);
        $mean   = array_sum($counts) / max(1, count($counts));
        $std    = $this->stdDev($counts, $mean);
        $thresh = $mean + 2.5 * max($std, 1);

        $findings = [];
        foreach ($buckets as $bucket => $cnt) {
            if ($cnt > $thresh) {
                $windowStart = date('Y-m-d H:i:s', $bucket * 900);
                $findings[]  = [
                    'category'  => 'SMS',
                    'icon'      => 'fas fa-sms',
                    'anomaly'   => "Unusual message burst: {$cnt} messages in a 15-minute window starting {$windowStart}",
                    'severity'  => $cnt > $thresh * 1.5 ? 'High' : 'Medium',
                    'algorithm' => 'Frequency Spike Detector',
                    'timestamp' => $windowStart,
                    'engine_note' => 'Z-Score threshold: ' . round($thresh, 1) . ' msgs/window (mean: ' . round($mean, 1) . ', σ: ' . round($std, 1) . ')',
                ];
            }
        }

        return $findings;
    }

    /**
     * SMS – Time-Pattern Analyser
     *
     * Flags messages received between 23:00 and 05:00 (night window).
     *
     * @param  array $smsRows
     * @return array
     */
    public function detectSmsTimePattern(array $smsRows): array
    {
        if (empty($smsRows)) {
            return $this->staticFallback('sms_time');
        }

        $findings = [];
        foreach ($smsRows as $row) {
            $ts   = strtotime($row['date'] ?? 'now');
            $hour = (int) date('G', $ts);
            if ($hour >= 23 || $hour < 5) {
                $findings[] = [
                    'category'  => 'SMS',
                    'icon'      => 'fas fa-sms',
                    'anomaly'   => 'SMS at off-hours (' . date('H:i', $ts) . ') from ' . esc($row['address'] ?? 'unknown'),
                    'severity'  => 'Medium',
                    'algorithm' => 'Time-Pattern Analyser',
                    'timestamp' => date('Y-m-d H:i:s', $ts),
                    'engine_note' => 'Night window: 23:00 – 05:00',
                ];
            }
        }

        return array_slice($findings, 0, 5);
    }

    /**
     * SMS – Sender Cluster Analysis (K-Means)
     *
     * Groups senders based on their activity (number of messages, frequency, and night-ratio)
     * and flags outlying senders (e.g. in single-member clusters or far from centroid).
     *
     * @param array $smsRows
     * @return array
     */
    public function detectSmsCluster(array $smsRows): array
    {
        if (empty($smsRows)) {
            return $this->staticFallback('sms_cluster');
        }

        // Aggregate statistics per sender (address)
        $senderStats = [];
        foreach ($smsRows as $row) {
            $sender = $row['address'] ?? 'unknown';
            $ts     = strtotime($row['date'] ?? 'now');
            $hour   = (int) date('G', $ts);
            $isNight = ($hour >= 23 || $hour < 5) ? 1 : 0;

            if (!isset($senderStats[$sender])) {
                $senderStats[$sender] = [
                    'count' => 0,
                    'night_count' => 0,
                    'lengths' => [],
                ];
            }
            $senderStats[$sender]['count']++;
            if ($isNight) {
                $senderStats[$sender]['night_count']++;
            }
            $senderStats[$sender]['lengths'][] = strlen($row['body'] ?? '');
        }

        // We need at least some unique senders to run K-Means
        $senders = array_keys($senderStats);
        $countSenders = count($senders);
        if ($countSenders < 3) {
            return []; // Not enough senders to cluster
        }

        // Build feature vectors: [total_count, night_ratio, avg_length]
        $samples = [];
        $senderIndexMap = [];
        $idx = 0;
        foreach ($senderStats as $sender => $stats) {
            $avgLength = count($stats['lengths']) > 0 ? (array_sum($stats['lengths']) / count($stats['lengths'])) : 0;
            $nightRatio = $stats['count'] > 0 ? ($stats['night_count'] / $stats['count']) : 0;
            
            $samples[$idx] = [
                (float)$stats['count'],
                (float)$nightRatio,
                (float)$avgLength
            ];
            $senderIndexMap[$idx] = $sender;
            $idx++;
        }

        // Normalize features
        $minVals = [INF, INF, INF];
        $maxVals = [-INF, -INF, -INF];
        foreach ($samples as $sample) {
            for ($i = 0; $i < 3; $i++) {
                if ($sample[$i] < $minVals[$i]) $minVals[$i] = $sample[$i];
                if ($sample[$i] > $maxVals[$i]) $maxVals[$i] = $sample[$i];
            }
        }
        $scaledSamples = [];
        foreach ($samples as $idx => $sample) {
            $scaled = [];
            for ($i = 0; $i < 3; $i++) {
                $range = $maxVals[$i] - $minVals[$i];
                $scaled[$i] = $range > 0 ? ($sample[$i] - $minVals[$i]) / $range : 0.0;
            }
            $scaledSamples[$idx] = $scaled;
        }

        // Run K-Means Clustering using PHP-ML
        $configuredK = max(2, (int)($this->getMlSetting('ml_phpml_kmeans_k', '3')));
        $k = min($configuredK, $countSenders);
        try {
            $kmeans = new \Phpml\Clustering\KMeans($k);
            $clusters = $kmeans->cluster($scaledSamples);
        } catch (\Throwable $e) {
            log_message('error', 'KMeans failed: ' . $e->getMessage());
            return [];
        }

        $findings = [];
        foreach ($clusters as $cId => $clusterPoints) {
            $size = count($clusterPoints);
            // If cluster is extremely small compared to others, all senders in it are outliers
            $isOutlierCluster = ($size === 1 && $countSenders >= 4) || ($size / $countSenders < 0.15 && $countSenders >= 6);

            foreach ($clusterPoints as $point) {
                // Find matching original index
                $foundIdx = null;
                foreach ($scaledSamples as $origIdx => $origVal) {
                    if ($origVal === $point) {
                        $foundIdx = $origIdx;
                        break;
                    }
                }

                if ($foundIdx !== null) {
                    $sender = $senderIndexMap[$foundIdx];
                    $rawStats = $senderStats[$sender];
                    
                    if ($isOutlierCluster || $rawStats['count'] > 100 || ($rawStats['night_count'] / $rawStats['count']) > 0.8) {
                        $findings[] = [
                            'category'  => 'SMS',
                            'icon'      => 'fas fa-sms',
                            'anomaly'   => 'Outlier sender behavior from "' . esc($sender) . '": ' . $rawStats['count'] . ' messages, ' . round(($rawStats['night_count'] / $rawStats['count']) * 100) . '% night activity',
                            'severity'  => $isOutlierCluster ? 'High' : 'Medium',
                            'algorithm' => 'Sender Cluster Analysis (K-Means)',
                            'timestamp' => date('Y-m-d H:i:s'),
                            'engine_note' => 'K-Means Cluster ID: ' . $cId . ' (Cluster size: ' . $size . ')',
                        ];
                    }
                }
            }
        }

        return $findings;
    }

    /**
     * Contacts – New-Contact Frequency Monitor
     *
     * Flags days where new contact additions exceed 3× the 30-day rolling average.
     *
     * @param  array $contactRows  [{display_name, last_modified, ...}, ...]
     * @return array
     */
    public function detectContactsFrequency(array $contactRows): array
    {
        if (empty($contactRows)) {
            return $this->staticFallback('contacts_freq');
        }

        // Bucket new contacts by day
        $daily = [];
        foreach ($contactRows as $row) {
            $day           = date('Y-m-d', strtotime($row['last_modified'] ?? 'today'));
            $daily[$day]   = ($daily[$day] ?? 0) + 1;
        }

        $counts = array_values($daily);
        $mean   = array_sum($counts) / max(1, count($counts));
        $thresh = max(3, $mean * 3);

        $findings = [];
        foreach ($daily as $day => $cnt) {
            if ($cnt > $thresh) {
                $findings[] = [
                    'category'  => 'Contacts',
                    'icon'      => 'fas fa-address-book',
                    'anomaly'   => "{$cnt} new contacts added on {$day} (" . round($cnt / max(1, $mean), 1) . '× above 30-day average)',
                    'severity'  => $cnt > $thresh * 1.5 ? 'High' : 'Medium',
                    'algorithm' => 'New-Contact Frequency Monitor',
                    'timestamp' => $day . ' 00:00:00',
                    'engine_note' => 'Daily threshold: ' . round($thresh) . ' (mean: ' . round($mean, 1) . '/day)',
                ];
            }
        }

        return $findings;
    }

    /**
     * Contacts – Duplicate & Anomaly Detector
     *
     * Finds contacts sharing the same phone number or suspiciously similar display names.
     *
     * @param  array $contactRows  [{display_name, phone_number, ...}, ...]
     * @return array
     */
    public function detectContactsDuplicates(array $contactRows): array
    {
        if (empty($contactRows)) {
            return $this->staticFallback('contacts_dup');
        }

        $phoneMap = [];
        $findings = [];

        foreach ($contactRows as $row) {
            $phone = preg_replace('/\D/', '', $row['phone_number'] ?? '');
            $name  = $row['display_name'] ?? 'Unknown';
            if ($phone) {
                if (isset($phoneMap[$phone])) {
                    $findings[] = [
                        'category'  => 'Contacts',
                        'icon'      => 'fas fa-address-book',
                        'anomaly'   => "Duplicate phone number shared by \"{$phoneMap[$phone]}\" and \"{$name}\" ({$phone})",
                        'severity'  => 'Medium',
                        'algorithm' => 'Duplicate & Anomaly Detector',
                        'timestamp' => date('Y-m-d H:i:s'),
                        'engine_note' => 'Exact phone number collision detected',
                    ];
                } else {
                    $phoneMap[$phone] = $name;
                }
            }
        }

        return $findings;
    }

    /**
     * Call Logs – Short-Call Burst Detector
     *
     * Flags when 3 or more calls each shorter than 10 seconds occur within 30 minutes.
     *
     * @param  array $callRows  [{duration_seconds, date, number, type}, ...]
     * @return array
     */
    public function detectCallsBurst(array $callRows): array
    {
        if (empty($callRows)) {
            return $this->staticFallback('calls_burst');
        }

        // Isolate short calls (< 10 s)
        $shortCalls = array_filter($callRows, fn($c) => (int)($c['duration_seconds'] ?? 999) < 10);
        usort($shortCalls, fn($a, $b) => strtotime($a['date']) <=> strtotime($b['date']));
        $shortCalls = array_values($shortCalls);

        $findings = [];
        $i        = 0;
        while ($i < count($shortCalls)) {
            $window = [$shortCalls[$i]];
            $j      = $i + 1;
            while ($j < count($shortCalls) &&
                   (strtotime($shortCalls[$j]['date']) - strtotime($shortCalls[$i]['date'])) <= 1800) {
                $window[] = $shortCalls[$j];
                $j++;
            }
            if (count($window) >= 3) {
                $cnt   = count($window);
                $num   = $window[0]['number'] ?? 'unknown';
                $start = $window[0]['date'] ?? 'unknown';
                $findings[] = [
                    'category'  => 'Call Log',
                    'icon'      => 'fas fa-phone',
                    'anomaly'   => "Burst of {$cnt} short calls (< 10 s each) starting {$start} — number: {$num}",
                    'severity'  => $cnt >= 6 ? 'High' : 'Medium',
                    'algorithm' => 'Short-Call Burst Detector',
                    'timestamp' => $start,
                    'engine_note' => 'Window: 30 min; minimum burst size: 3 calls',
                ];
                $i = $j;
            } else {
                $i++;
            }
        }

        return $findings;
    }

    /**
     * Call Logs – Night-Activity Monitor
     *
     * Flags calls between 23:00 and 05:00.
     *
     * @param  array $callRows
     * @return array
     */
    public function detectCallsNight(array $callRows): array
    {
        if (empty($callRows)) {
            return $this->staticFallback('calls_night');
        }

        $findings = [];
        foreach ($callRows as $row) {
            $ts   = strtotime($row['date'] ?? 'now');
            $hour = (int) date('G', $ts);
            if ($hour >= 23 || $hour < 5) {
                $findings[] = [
                    'category'  => 'Call Log',
                    'icon'      => 'fas fa-phone',
                    'anomaly'   => 'Call at ' . date('H:i', $ts) . ' from/to ' . esc($row['number'] ?? 'unknown'),
                    'severity'  => 'Low',
                    'algorithm' => 'Night-Activity Monitor',
                    'timestamp' => date('Y-m-d H:i:s', $ts),
                    'engine_note' => 'Night window: 23:00 – 05:00',
                ];
            }
        }

        return array_slice($findings, 0, 5);
    }

    /**
     * Locations – Geo-Fence Violation Detector
     *
     * Builds a home zone (centroid ± radius) from the most frequent location cluster,
     * then flags any point outside that zone.
     *
     * @param  array $locationRows  [{latitude, longitude, timestamp}, ...]
     * @return array
     */
    public function detectLocationGeofence(array $locationRows): array
    {
        if (count($locationRows) < 5) {
            return $this->staticFallback('loc_geofence');
        }

        $lats = array_column($locationRows, 'latitude');
        $lngs = array_column($locationRows, 'longitude');
        $centLat = array_sum($lats) / count($lats);
        $centLng = array_sum($lngs) / count($lngs);

        // Build distances from centroid
        $distances = [];
        foreach ($locationRows as $row) {
            $distances[] = $this->haversine(
                (float)$row['latitude'], (float)$row['longitude'],
                $centLat, $centLng
            );
        }

        // Home zone radius = mean + 1 stddev (in km)
        $mean   = array_sum($distances) / count($distances);
        $std    = $this->stdDev($distances, $mean);
        $radius = $mean + $std;

        $findings = [];
        foreach ($locationRows as $row) {
            $dist = $this->haversine(
                (float)$row['latitude'], (float)$row['longitude'],
                $centLat, $centLng
            );
            if ($dist > $radius * 1.5) {
                $findings[] = [
                    'category'  => 'Location',
                    'icon'      => 'fas fa-map-marker-alt',
                    'anomaly'   => 'Device ' . round($dist, 1) . ' km from home zone at ' . ($row['timestamp'] ?? 'unknown'),
                    'severity'  => $dist > $radius * 3 ? 'High' : 'Medium',
                    'algorithm' => 'Geo-Fence Violation Detector',
                    'timestamp' => $row['timestamp'] ?? date('Y-m-d H:i:s'),
                    'engine_note' => 'Home zone radius: ' . round($radius, 1) . ' km from centroid (' . round($centLat, 4) . ', ' . round($centLng, 4) . ')',
                ];
            }
        }

        return $findings;
    }

    /**
     * Locations – Travel Speed Anomaly
     *
     * Detects physically impossible travel speeds between consecutive points.
     *
     * @param  array $locationRows  [{latitude, longitude, timestamp}, ...] (ordered chronologically)
     * @return array
     */
    public function detectLocationSpeed(array $locationRows): array
    {
        if (count($locationRows) < 2) {
            return $this->staticFallback('loc_speed');
        }

        usort($locationRows, fn($a, $b) => strtotime($a['timestamp']) <=> strtotime($b['timestamp']));
        $findings = [];

        for ($i = 1; $i < count($locationRows); $i++) {
            $prev  = $locationRows[$i - 1];
            $curr  = $locationRows[$i];
            $dist  = $this->haversine(
                (float)$prev['latitude'], (float)$prev['longitude'],
                (float)$curr['latitude'], (float)$curr['longitude']
            );
            $secs  = abs(strtotime($curr['timestamp']) - strtotime($prev['timestamp']));
            if ($secs < 1) {
                continue;
            }
            $kmph  = ($dist / $secs) * 3600;
            if ($kmph > 900) {
                $findings[] = [
                    'category'  => 'Location',
                    'icon'      => 'fas fa-map-marker-alt',
                    'anomaly'   => 'Impossible travel speed ' . round($kmph) . ' km/h between ' . ($prev['timestamp'] ?? '') . ' and ' . ($curr['timestamp'] ?? ''),
                    'severity'  => 'High',
                    'algorithm' => 'Travel Speed Anomaly',
                    'timestamp' => $curr['timestamp'] ?? date('Y-m-d H:i:s'),
                    'engine_note' => 'Threshold: 900 km/h (commercial air speed)',
                ];
            }
        }

        return $findings;
    }

    /**
     * Locations – DBSCAN Trajectory Clustering
     *
     * Clusters coordinates to find frequent/normal zones, and flags coordinate points
     * that do not belong to any cluster as trajectory anomalies.
     *
     * @param  array $locationRows  [{latitude, longitude, timestamp}, ...]
     * @return array
     */
    public function detectLocationDbscan(array $locationRows): array
    {
        if (count($locationRows) < 5) {
            return $this->staticFallback('loc_dbscan');
        }

        // Prepare sample data: [[lat, lng], [lat, lng], ...]
        $samples = [];
        foreach ($locationRows as $idx => $row) {
            $samples[$idx] = [
                (float)($row['latitude'] ?? 0.0),
                (float)($row['longitude'] ?? 0.0)
            ];
        }

        // Read DBSCAN params from DB settings
        $epsilon    = max(0.001, (float)($this->getMlSetting('ml_phpml_dbscan_epsilon', '0.01')));
        $minSamples = max(1, (int)($this->getMlSetting('ml_phpml_dbscan_minpoints', '2')));
        try {
            $dbscan = new \Phpml\Clustering\DBSCAN($epsilon, $minSamples);
            $clusters = $dbscan->cluster($samples);
        } catch (\Throwable $e) {
            log_message('error', 'DBSCAN failed: ' . $e->getMessage());
            return [];
        }

        // Identify noise/outliers (points not in any cluster)
        $clusteredIndices = [];
        foreach ($clusters as $cluster) {
            foreach ($cluster as $point) {
                // Find matching original indices
                foreach ($samples as $origIdx => $origVal) {
                    if ($origVal === $point) {
                        $clusteredIndices[$origIdx] = true;
                    }
                }
            }
        }

        $findings = [];
        foreach ($locationRows as $idx => $row) {
            if (!isset($clusteredIndices[$idx])) {
                $findings[] = [
                    'category'  => 'Location',
                    'icon'      => 'fas fa-map-marker-alt',
                    'anomaly'   => 'Anomalous trajectory point detected: (' . round($row['latitude'], 4) . ', ' . round($row['longitude'], 4) . ')',
                    'severity'  => 'Medium',
                    'algorithm' => 'DBSCAN Trajectory Clustering',
                    'timestamp' => $row['timestamp'] ?? date('Y-m-d H:i:s'),
                    'engine_note' => 'PHP-ML DBSCAN outlier (epsilon=' . $epsilon . ', minSamples=' . $minSamples . ')',
                ];
            }
        }

        return $findings;
    }

    /**
     * Installed Apps – Package Reputation Scanner
     *
     * Compares package names against a known-suspicious pattern list and
     * checks installation time (off-hours installs are flagged).
     *
     * @param  array $appRows  [{package_name, app_name, install_date}, ...]
     * @return array
     */
    public function detectAppsReputation(array $appRows): array
    {
        if (empty($appRows)) {
            return $this->staticFallback('apps_rep');
        }

        // Patterns that indicate potential spyware/stalkerware package names
        $suspiciousPatterns = [
            'hidden', 'spy', 'track', 'monitor', 'stealth', 'covert',
            'logger', 'keylog', 'remote', 'shadow', 'ghost', 'invisible',
            'util.sync', 'background.service', 'com.android.hidden',
        ];

        $findings = [];
        foreach ($appRows as $row) {
            $pkg  = strtolower($row['package_name'] ?? '');
            $name = $row['app_name'] ?? $pkg;
            $date = $row['install_date'] ?? null;

            foreach ($suspiciousPatterns as $pattern) {
                if (str_contains($pkg, $pattern)) {
                    $hour     = $date ? (int) date('G', strtotime($date)) : -1;
                    $offHours = ($hour >= 23 || $hour < 5);
                    $findings[] = [
                        'category'  => 'Installed Apps',
                        'icon'      => 'fas fa-th-large',
                        'anomaly'   => "Suspicious package \"{$name}\" ({$pkg}) installed" . ($offHours ? ' at off-hours (' . date('H:i', strtotime($date)) . ')' : ''),
                        'severity'  => $offHours ? 'High' : 'Medium',
                        'algorithm' => 'Package Reputation Scanner',
                        'timestamp' => $date ?? date('Y-m-d H:i:s'),
                        'engine_note' => "Matched suspicious pattern: \"{$pattern}\"",
                    ];
                    break;
                }
            }
        }

        return $findings;
    }

    /**
     * Installed Apps – Permission Anomaly Detector
     *
     * Flags apps with an unusually high number of sensitive permissions.
     *
     * @param  array $appRows  [{app_name, permissions: ['READ_SMS','RECORD_AUDIO',...], ...}, ...]
     * @return array
     */
    public function detectAppsPermission(array $appRows): array
    {
        if (empty($appRows)) {
            return $this->staticFallback('apps_perm');
        }

        $sensitivePerms = [
            'READ_SMS', 'RECEIVE_SMS', 'SEND_SMS',
            'RECORD_AUDIO', 'CAMERA',
            'READ_CONTACTS', 'WRITE_CONTACTS',
            'ACCESS_FINE_LOCATION', 'ACCESS_BACKGROUND_LOCATION',
            'READ_CALL_LOG', 'PROCESS_OUTGOING_CALLS',
            'SYSTEM_ALERT_WINDOW', 'DEVICE_ADMIN',
        ];

        $permCounts = [];
        foreach ($appRows as $row) {
            $perms      = (array)($row['permissions'] ?? []);
            $sensitiveN = count(array_intersect($perms, $sensitivePerms));
            $permCounts[] = $sensitiveN;
        }

        $mean   = array_sum($permCounts) / max(1, count($permCounts));
        $std    = $this->stdDev($permCounts, $mean);
        $thresh = $mean + 2 * max($std, 0.5);

        $findings = [];
        foreach ($appRows as $i => $row) {
            if ($permCounts[$i] > $thresh) {
                $matched = array_intersect((array)($row['permissions'] ?? []), $sensitivePerms);
                $findings[] = [
                    'category'  => 'Installed Apps',
                    'icon'      => 'fas fa-th-large',
                    'anomaly'   => '"' . esc($row['app_name'] ?? 'Unknown') . '" requests ' . $permCounts[$i] . ' sensitive permissions: ' . implode(', ', array_slice($matched, 0, 4)),
                    'severity'  => $permCounts[$i] >= 8 ? 'High' : 'Medium',
                    'algorithm' => 'Permission Anomaly Detector',
                    'timestamp' => $row['install_date'] ?? date('Y-m-d H:i:s'),
                    'engine_note' => 'Threshold: ' . round($thresh, 1) . ' sensitive permissions (mean: ' . round($mean, 1) . ', σ: ' . round($std, 1) . ')',
                ];
            }
        }

        return $findings;
    }

    /**
     * Files – File Creation Spike Detector
     *
     * Flags days where file creations are 3× the 30-day rolling average.
     *
     * @param  array $fileRows  [{file_name, created_at, path}, ...]
     * @return array
     */
    public function detectFilesSpike(array $fileRows): array
    {
        if (empty($fileRows)) {
            return $this->staticFallback('files_spike');
        }

        $daily = [];
        foreach ($fileRows as $row) {
            $day         = date('Y-m-d', strtotime($row['created_at'] ?? 'today'));
            $daily[$day] = ($daily[$day] ?? 0) + 1;
        }

        $counts = array_values($daily);
        $mean   = array_sum($counts) / max(1, count($counts));
        $thresh = $mean * 3;

        $findings = [];
        foreach ($daily as $day => $cnt) {
            if ($cnt > $thresh) {
                $findings[] = [
                    'category'  => 'Files',
                    'icon'      => 'fas fa-folder-open',
                    'anomaly'   => "{$cnt} files created on {$day} (" . round($cnt / max(1, $mean), 1) . '× the daily average)',
                    'severity'  => $cnt > $thresh * 2 ? 'High' : 'Medium',
                    'algorithm' => 'File Creation Spike Detector',
                    'timestamp' => $day . ' 00:00:00',
                    'engine_note' => 'Daily threshold: ' . round($thresh) . ' files (mean: ' . round($mean, 1) . '/day)',
                ];
            }
        }

        return $findings;
    }

    /**
     * Device Activity – Screen-Time Anomaly Detector
     *
     * Flags days where screen-on time deviates > 2σ from the 30-day mean.
     *
     * @param  array $activityRows  [{date, screen_on_minutes}, ...]
     * @return array
     */
    public function detectActivityScreenTime(array $activityRows): array
    {
        if (count($activityRows) < 3) {
            return $this->staticFallback('act_screen');
        }

        $times  = array_column($activityRows, 'screen_on_minutes');
        $mean   = array_sum($times) / count($times);
        $std    = $this->stdDev($times, $mean);

        $findings = [];
        foreach ($activityRows as $row) {
            $mins   = (float)$row['screen_on_minutes'];
            $zscore = ($std > 0) ? abs($mins - $mean) / $std : 0;
            if ($zscore > 2) {
                $hours = round($mins / 60, 1);
                $findings[] = [
                    'category'  => 'Activity',
                    'icon'      => 'fas fa-heartbeat',
                    'anomaly'   => "Screen-time {$hours} h on " . ($row['date'] ?? 'unknown') . " (baseline: " . round($mean / 60, 1) . ' h ± ' . round($std / 60, 1) . ' h)',
                    'severity'  => $zscore > 3 ? 'High' : 'Medium',
                    'algorithm' => 'Screen-Time Anomaly Detector',
                    'timestamp' => ($row['date'] ?? date('Y-m-d')) . ' 23:59:00',
                    'engine_note' => 'Z-Score: ' . round($zscore, 2) . ' σ above mean',
                ];
            }
        }

        return $findings;
    }

    /**
     * Device Activity – App-Switch Rate Monitor
     *
     * Flags hourly periods with > 60 app switches.
     *
     * @param  array $activityRows  [{timestamp, app_package}, ...]  (ordered chronologically)
     * @return array
     */
    public function detectActivitySwitchRate(array $activityRows): array
    {
        if (count($activityRows) < 5) {
            return $this->staticFallback('act_switch');
        }

        // Bucket by hour
        $hourBuckets = [];
        foreach ($activityRows as $row) {
            $hr = date('Y-m-d H', strtotime($row['timestamp'] ?? 'now'));
            if (!isset($hourBuckets[$hr])) {
                $hourBuckets[$hr] = ['switches' => 0, 'prev' => null];
            }
            if ($hourBuckets[$hr]['prev'] !== ($row['app_package'] ?? null)) {
                $hourBuckets[$hr]['switches']++;
                $hourBuckets[$hr]['prev'] = $row['app_package'] ?? null;
            }
        }

        $findings = [];
        foreach ($hourBuckets as $hr => $data) {
            if ($data['switches'] > 60) {
                $findings[] = [
                    'category'  => 'Activity',
                    'icon'      => 'fas fa-heartbeat',
                    'anomaly'   => $data['switches'] . ' app switches in one hour (' . $hr . ':00) — possible scripted behaviour',
                    'severity'  => $data['switches'] > 100 ? 'High' : 'Medium',
                    'algorithm' => 'App-Switch Rate Monitor',
                    'timestamp' => $hr . ':00:00',
                    'engine_note' => 'Threshold: 60 app switches/hour',
                ];
            }
        }

        return $findings;
    }

    /**
     * Device Info – Hardware Change Detector
     *
     * Compares current device identifiers with previous snapshot.
     *
     * @param  array $current   ['imei' => '...', 'serial' => '...', 'fingerprint' => '...']
     * @param  array $previous  Same structure from a prior upload
     * @return array
     */
    public function detectDeviceHardwareChange(array $current, array $previous): array
    {
        if (empty($current) || empty($previous)) {
            return $this->staticFallback('dev_hw');
        }

        $keys     = ['imei', 'serial', 'fingerprint', 'android_id', 'mac_address'];
        $findings = [];
        foreach ($keys as $key) {
            $cur = $current[$key] ?? null;
            $old = $previous[$key] ?? null;
            if ($cur && $old && $cur !== $old) {
                $findings[] = [
                    'category'  => 'Device Info',
                    'icon'      => 'fas fa-microchip',
                    'anomaly'   => strtoupper($key) . " changed: previous {$old} → current {$cur}",
                    'severity'  => in_array($key, ['imei', 'android_id']) ? 'High' : 'Medium',
                    'algorithm' => 'Hardware Change Detector',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'engine_note' => 'Identifier field: ' . $key,
                ];
            }
        }

        return $findings;
    }

    /**
     * Device Info – Network Profile Monitor
     *
     * Flags new Wi-Fi SSIDs, APN changes, or VPN usage not seen before.
     *
     * @param  array $netRows  [{ssid, type, vpn_active, timestamp}, ...]
     * @param  array $knownSsids  List of known/trusted SSIDs
     * @return array
     */
    public function detectDeviceNetworkProfile(array $netRows, array $knownSsids = []): array
    {
        if (empty($netRows)) {
            return $this->staticFallback('dev_net');
        }

        $findings = [];
        foreach ($netRows as $row) {
            $ssid = $row['ssid'] ?? '';
            $vpn  = !empty($row['vpn_active']);

            if ($ssid && !in_array($ssid, $knownSsids)) {
                $findings[] = [
                    'category'  => 'Device Info',
                    'icon'      => 'fas fa-microchip',
                    'anomaly'   => 'Connected to unknown Wi-Fi SSID: "' . esc($ssid) . '" at ' . ($row['timestamp'] ?? 'unknown'),
                    'severity'  => 'Medium',
                    'algorithm' => 'Network Profile Monitor',
                    'timestamp' => $row['timestamp'] ?? date('Y-m-d H:i:s'),
                    'engine_note' => 'Not in known-safe SSID list',
                ];
            }

            if ($vpn) {
                $findings[] = [
                    'category'  => 'Device Info',
                    'icon'      => 'fas fa-microchip',
                    'anomaly'   => 'VPN connection detected at ' . ($row['timestamp'] ?? 'unknown'),
                    'severity'  => 'Low',
                    'algorithm' => 'Network Profile Monitor',
                    'timestamp' => $row['timestamp'] ?? date('Y-m-d H:i:s'),
                    'engine_note' => 'VPN flag active in network profile',
                ];
            }
        }

        return array_slice($findings, 0, 5);
    }

    // =========================================================================
    // Orchestration – Run all selected algorithms and merge results
    // =========================================================================

    /**
     * Runs the PHP-ML detection pipeline for all selected algorithms.
     *
     * Pulls live data from the database (via CI4 query builder) for each
     * category and runs the corresponding detection method. Falls back to
     * static demo data when no live rows are available.
     *
     * @param  array  $selectedAlgs  Session algorithms map: ['sms' => ['sms_freq', 'sms_time'], ...]
     * @param  int    $userId        Target user ID for querying data (from BaseClientController::$userId)
     * @return array                 Merged anomaly findings
     */
    public function runPhpDetection(array $selectedAlgs, int $userId = 0,
                                    int $jobId = 0): array
    {
        $results = [];

        $algCount = 0;
        foreach ($selectedAlgs as $algList) {
            $algCount += count((array)$algList);
        }
        $completed = 0;

        $dispatch = [
            'sms_freq'      => fn() => $this->detectSmsFrequencySpike($this->fetchSms($userId)),
            'sms_time'      => fn() => $this->detectSmsTimePattern($this->fetchSms($userId)),
            'sms_cluster'   => fn() => $this->detectSmsCluster($this->fetchSms($userId)),
            'contacts_freq' => fn() => $this->detectContactsFrequency($this->fetchContacts($userId)),
            'contacts_dup'  => fn() => $this->detectContactsDuplicates($this->fetchContacts($userId)),
            'calls_burst'   => fn() => $this->detectCallsBurst($this->fetchCallLogs($userId)),
            'calls_night'   => fn() => $this->detectCallsNight($this->fetchCallLogs($userId)),
            'loc_geofence'  => fn() => $this->detectLocationGeofence($this->fetchLocations($userId)),
            'loc_speed'     => fn() => $this->detectLocationSpeed($this->fetchLocations($userId)),
            'loc_dbscan'    => fn() => $this->detectLocationDbscan($this->fetchLocations($userId)),
            'apps_rep'      => fn() => $this->detectAppsReputation($this->fetchApps($userId)),
            'apps_perm'     => fn() => $this->detectAppsPermission($this->fetchApps($userId)),
            'files_spike'   => fn() => $this->detectFilesSpike($this->fetchFiles($userId)),
            'act_screen'    => fn() => $this->detectActivityScreenTime($this->fetchActivityScreenTime($userId)),
            'act_switch'    => fn() => $this->detectActivitySwitchRate($this->fetchActivitySwitchRate($userId)),
            'dev_hw'        => fn() => $this->detectDeviceHardwareChange(
                                    $this->fetchDeviceInfo($userId, 'current'),
                                    $this->fetchDeviceInfo($userId, 'previous')),
            'dev_net'       => fn() => $this->detectDeviceNetworkProfile(
                                    $this->fetchNetworkProfile($userId), []),
        ];

        foreach ($selectedAlgs as $category => $algList) {
            foreach ((array)$algList as $algId) {
                if (isset($dispatch[$algId])) {
                    $completed++;
                    $startMs = $this->startAlgorithm($jobId, $completed, $algCount, $algId, 'php');
                    $found = $dispatch[$algId]();
                    $durationMs = $startMs ? (int)((microtime(true) * 1000) - $startMs) : 0;
                    $this->completeAlgorithm($jobId, $completed, $algCount, $algId, $durationMs);
                    if (!empty($found)) {
                        foreach ($found as &$f) {
                            $f['algorithm_id'] = $algId;
                        }
                        unset($f);
                        $results = array_merge($results, $found);
                    }
                }
            }
        }

        $severityOrder = ['High' => 0, 'Medium' => 1, 'Low' => 2];
        usort($results, function ($a, $b) use ($severityOrder) {
            return ($severityOrder[$a['severity']] ?? 9) <=> ($severityOrder[$b['severity']] ?? 9);
        });

        return $results;
    }

    // =========================================================================
    // Python Engine – Remote FastAPI Backend
    // =========================================================================

    /**
     * Reads the ml_python_* connection settings from the database.
     *
     * Settings stored in tbl_settings with class='ml':
     *   ml_python_host, ml_python_port, ml_python_endpoint
     *
     * @return array{host: string, port: int, endpoint: string, base_url: string}
     */
    public function getPythonSettings(): array
    {
        $host     = 'ml-eaves-droid';
        $port     = 9070;
        $endpoint = '/api/analyze';
        $url      = '';

        if (getenv('PYTHON_BACKEND_HOST') !== false) {
            $host = getenv('PYTHON_BACKEND_HOST');
        }

        if (getenv('PYTHON_BACKEND_PORT') !== false) {
            $port = (int)getenv('PYTHON_BACKEND_PORT');
        }

        if (getenv('PYTHON_BACKEND_ENDPOINT') !== false) {
            $endpoint = getenv('PYTHON_BACKEND_ENDPOINT');
        }

        try {
            $rows = $this->db->table('settings')
                ->where('class', 'ml')
                ->whereIn('key', ['ml_python_host', 'ml_python_port', 'ml_python_endpoint', 'ml_python_url'])
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                match ($row['key']) {
                    'ml_python_host'     => $host = $row['value'] ?: $host,
                    'ml_python_port'     => $port = (int)($row['value'] ?: $port),
                    'ml_python_endpoint' => $endpoint = $row['value'] ?: $endpoint,
                    'ml_python_url'      => $url = $row['value'] ?: '',
                    default              => null,
                };
            }
        } catch (\Throwable $e) {
            // Use defaults if settings table doesn't exist yet
        }

        // If a full URL is stored, use it directly
        if ($url) {
            $baseUrl = rtrim($url, '/');
            // Parse URL for display purposes
            $parts = parse_url($baseUrl);
            $host = $parts['host'] ?? $host;
            $port = $parts['port'] ?? $port;
        } else {
            $baseUrl = rtrim("http://{$host}:{$port}", '/');
        }

        return [
            'host'      => $host,
            'port'      => $port,
            'endpoint'  => $endpoint,
            'base_url'  => $baseUrl,
            'url'       => $baseUrl,
        ];
    }

    public function testPythonConnection(?string $testUrl = null): array
    {
        $settings = $this->getPythonSettings();
        $baseUrl = $testUrl ? rtrim($testUrl, '/') : $settings['base_url'];
        $healthUrl = $baseUrl . '/api/health';

        try {
            $client = service('curlrequest', [
                'timeout'         => 10,
                'connect_timeout' => 5,
                'http_errors'     => false,
            ]);
            $response = $client->get($healthUrl);
            $statusCode = $response->getStatusCode();
            $body = $response->getBody() ? json_decode($response->getBody(), true) : [];

            if ($statusCode === 200) {
                return [
                    'success'      => true,
                    'message'      => 'Python backend is running.',
                    'tested_url'   => $baseUrl,
                    'status'       => $body['status'] ?? 'healthy',
                    'version'      => $body['version'] ?? '',
                    'models'       => $body['models_loaded'] ?? $body['models'] ?? $body['algorithms'] ?? [],
                    'modules'      => $body['modules'] ?? [],
                    'database'     => $body['database'] ?? '',
                    'cuda'         => $body['cuda_available'] ?? false,
                    'cuda_device'  => $body['cuda_device'] ?? '',
                    'memory'       => $body['memory_mb'] ?? [],
                    'cache'        => $body['cache_entries'] ?? 0,
                    'uptime'       => $body['uptime_seconds'] ?? 0,
                    'settings'     => $settings,
                ];
            }

            return [
                'success'    => false,
                'message'    => "Backend returned HTTP {$statusCode}.",
                'tested_url' => $baseUrl,
                'settings'   => $settings,
            ];
        } catch (\Throwable $e) {
            return [
                'success'  => false,
                'message'  => 'Connection failed: ' . $e->getMessage(),
                'settings' => $settings,
            ];
        }
    }

    /**
     * Parse the central docker-compose.yml to extract ml-eaves-droid
     * container service info (host, ports, image) so the admin ML page
     * can pre-fill defaults automatically.
     *
     * @return array {host, internal_port, external_port, metrics_port, tf_serving_port, endpoint}
     */
    public function getDockerComposeMLSettings(): array
    {
        $defaults = [
            'host'               => 'ml-eaves-droid',
            'internal_port'      => 9070,
            'external_port'      => 9071,
            'metrics_port'       => 9073,
            'tf_serving_port'    => 9072,
            'debug_port'         => 9094,
            'mgmt_port'          => 9095,
            'endpoint'           => '/api/analyze',
            'detected'           => false,
            'compose_file'       => '/home/niccher/Music/hosts/docker-compose.yml',
        ];

        $composeFile = '/home/niccher/Music/hosts/docker-compose.yml';
        if (!file_exists($composeFile)) {
            return $defaults;
        }

        $content = file_get_contents($composeFile);
        if ($content === false) {
            return $defaults;
        }

        // Find the ml-eaves-droid service block
        if (!preg_match('/ml-eaves-droid\s*:\s*(?:\n|.)+?(?=\n  \w)/s', $content, $serviceMatch)) {
            return $defaults;
        }

        $serviceBlock = $serviceMatch[0];

        // Extract container name
        if (preg_match('/container_name:\s*\$\{[^}]*:\s*([^}]+)\}/', $serviceBlock, $m)) {
            $defaults['host'] = trim($m[1]);
        } elseif (preg_match('/container_name:\s*(\S+)/', $serviceBlock, $m)) {
            $defaults['host'] = trim($m[1]);
        }

        // Extract ports from the format "${VAR_NAME:-DEFAULT}:CONTAINER_PORT"
        // e.g. "${ML_EAVES_DROID_API_PORT:-9071}:8000"
        if (preg_match_all('/-\s*"\$\{[^}:]+:(\d+)\}:(\d+)"/', $serviceBlock, $portMatches, PREG_SET_ORDER)) {
            $seen = [];
            foreach ($portMatches as $pm) {
                $externalPort = (int)$pm[1];
                $containerPort = (int)$pm[2];
                if (in_array($containerPort, $seen)) continue;
                $seen[] = $containerPort;
                if ($containerPort === 9070 || $containerPort === 8000) {
                    $defaults['internal_port'] = $containerPort;
                    $defaults['external_port'] = $externalPort;
                } elseif ($containerPort === 9073 || $containerPort === 9090) {
                    $defaults['metrics_port'] = $externalPort;
                } elseif ($containerPort === 9072 || $containerPort === 8501) {
                    $defaults['tf_serving_port'] = $externalPort;
                } elseif ($containerPort === 9094) {
                    $defaults['debug_port'] = $externalPort;
                } elseif ($containerPort === 9095) {
                    $defaults['mgmt_port'] = $externalPort;
                }
            }
        }

        // Detect the actual compose file location for reference
        $defaults['detected'] = true;

        return $defaults;
    }

    /**
     * Returns analysis counts per category so the wizard UI can show
     * "X new entries since last analysis" and offer full/incremental choices.
     *
     * Reads from ml_analysis_tracking (updated by Python backend after each job)
     * and compares against the current record count in each data table.
     *
     * @param  int  $userId
     * @return array  ['sms' => ['total' => 1500, 'analyzed' => 500, 'new' => 1000], ...]
     */
    public function getAnalysisCounts(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $categories = $this->getAlgorithmCategories();
        $counts     = [];

        // How many records each category's primary table has for this user
        $tableMap = [
            'sms'         => ['table' => 'tbl_sms',          'owner' => 'owner_id'],
            'contacts'    => ['table' => 'tbl_contacts',     'owner' => 'owner_id'],
            'call_logs'   => ['table' => 'tbl_logs',         'owner' => 'owner_id'],
            'locations'   => ['table' => 'tbl_location',     'owner' => 'owner_id'],
            'apps'        => ['table' => 'tbl_apps',         'owner' => 'owner_id'],
            'files'       => ['table' => 'tbl_device_files', 'owner' => 'owner_id'],
            'activity'    => ['table' => 'tbl_app_usage',    'owner' => 'owner_id'],
            'device_info' => ['table' => 'tbl_device_profile','owner' => 'device_id'],
        ];

        // Fetch tracking rows for this user
        $tracking = [];
        try {
            $rows = $this->db->table('ml_analysis_tracking')
                ->where('user_id', $userId)
                ->get()->getResultArray();
            foreach ($rows as $r) {
                $tracking[$r['category']] = $r;
            }
        } catch (\Throwable $e) {
            // Table may not exist yet; return empty counts
        }

        foreach ($categories as $catKey => $cat) {
            $info   = $tableMap[$catKey] ?? null;
            $total  = 0;
            $analyzed = (int)($tracking[$catKey]['total_analyzed'] ?? 0);

            if ($info) {
                try {
                    $total = (int)$this->db->table($info['table'])
                        ->where($info['owner'], $userId)
                        ->countAllResults();
                } catch (\Throwable $e) {
                    $total = 0;
                }
            }

            $counts[$catKey] = [
                'label'    => $cat['label'],
                'total'    => $total,
                'analyzed' => $analyzed,
                'new'      => max(0, $total - $analyzed),
            ];
        }

        return $counts;
    }

    /**
     * Runs the Python-backed anomaly detection pipeline (DB-direct mode).
     *
     * 1. PHP-compatible algorithms run locally via runPhpDetection().
     * 2. Python-only algorithms:
     *    a. Read ml_analysis_tracking to get last_analyzed_at cutoff.
     *    b. INSERT a row in ml_jobs with scope + incremental_since.
     *    c. POST a lightweight JSON to the FastAPI backend (no data blobs).
     *    d. The Python backend queries MySQL directly, stores findings in
     *       ml_results, and returns a status response.
     *    e. SELECT FROM ml_results WHERE job_id = ? and map to view format.
     * 3. If the backend is unreachable, show static demo fallbacks.
     *
     * @param  array $selectedAlgs  Session algorithms map
     * @param  int   $userId        Authenticated user ID
     * @param  string $scope        'full' | 'incremental' (from wizard UI)
     * @return array                Merged anomaly findings
     */
    public function runPythonDetection(array $selectedAlgs, int $userId = 0,
                                       string $scope = 'full',
                                       int $jobId = 0): array
    {
        // ── Split algorithms into PHP-compatible and Python-only ──
        $phpAlgs = [];
        $pyOnly  = [];

        foreach ($selectedAlgs as $category => $algList) {
            foreach ((array)$algList as $algId) {
                $meta = $this->getAlgorithmInfo($algId);
                if (!$meta) {
                    continue;
                }
                if (($meta['compat'] ?? 'both') === 'python') {
                    $pyOnly[$category][] = $algId;
                } else {
                    $phpAlgs[$category][] = $algId;
                }
            }
        }

        // ── Determine incremental cutoff from ml_analysis_tracking ──
        $incrementalSince = null;
        if ($scope === 'incremental') {
            try {
                $tracking = $this->db->table('ml_analysis_tracking')
                    ->select('MIN(last_analyzed_at) AS cutoff')
                    ->where('user_id', $userId)
                    ->get()->getRowArray();
                $incrementalSince = $tracking['cutoff'] ?? null;
            } catch (\Throwable $e) {
                $incrementalSince = null;
            }
        }

        // ── Collect all algorithm IDs ──
        $allAlgIds = [];
        foreach ($phpAlgs as $algList) {
            foreach ((array)$algList as $algId) {
                $allAlgIds[] = $algId;
            }
        }
        foreach ($pyOnly as $algList) {
            foreach ((array)$algList as $algId) {
                $allAlgIds[] = $algId;
            }
        }
        $allAlgIds = array_values(array_unique($allAlgIds));

        // ── Determine engine label ──
        $hasPhp   = !empty($phpAlgs);
        $hasPy    = !empty($pyOnly);
        $engine   = $hasPhp && $hasPy ? 'both' : ($hasPy ? 'python' : 'php');

        // Only create a job row if one wasn't provided by the caller
        if ($jobId <= 0) {
            $jobEntry = [
                'user_id'    => $userId,
                'engine'     => $engine,
                'algorithms' => json_encode($allAlgIds),
                'scope'      => $scope,
                'status'     => 'running',
            ];
            try {
                $this->ensureJobTrackingColumns();
                $this->db->table('ml_jobs')->insert($jobEntry);
                $jobId = $this->db->insertID();
            } catch (\Throwable $e) {
                log_message('error', 'Failed to create ml_jobs row: ' . $e->getMessage());
            }
        }

        // ── Run PHP-compatible algorithms locally ──
        $results = [];
        if ($hasPhp) {
            $results = $this->runPhpDetection($phpAlgs, $userId, $jobId);
        }

        // ── Handle Python-only algorithms ──
        $pyResults = [];
        if ($hasPy && $jobId > 0) {
            // Report progress before Python dispatch
            $this->updateJobProgress($jobId, 1, 3, 'python_backend');

            $pyAlgIds = [];
            foreach ($pyOnly as $algList) {
                foreach ((array)$algList as $algId) {
                    $pyAlgIds[] = $algId;
                }
            }

            $settings = $this->getPythonSettings();
            $url      = $settings['base_url'] . $settings['endpoint'];

            // Fetch all ML config params from DB to pass to Python backend
            $mlParams = [];
            try {
                $paramRows = $this->db->table('settings')
                    ->where('class', 'ml')
                    ->get()
                    ->getResultArray();
                foreach ($paramRows as $pr) {
                    $mlParams[$pr['key']] = $pr['value'];
                }
            } catch (\Throwable $e) {
                // Use empty params if settings table doesn't exist yet
            }

            $payload = [
                'job_id'            => $jobId,
                'user_id'           => $userId,
                'algorithms'        => $pyAlgIds,
                'scope'             => $scope,
                'incremental_since' => $incrementalSince,
                'params'            => $mlParams,
            ];

            try {
                $client = service('curlrequest', [
                    'timeout'         => 120,
                    'connect_timeout' => 5,
                    'http_errors'     => false,
                    'headers'         => [
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                ]);

                $response = $client->post($url, [
                    'body' => json_encode($payload),
                ]);

                $statusCode = $response->getStatusCode();

                if ($statusCode === 200) {
                    $body = $response->getBody();
                    $decoded = json_decode($body);

                    if ($decoded && isset($decoded->status) && $decoded->status === 'completed') {
                        // Python finished — read results from ml_results
                        $this->updateJobProgress($jobId, 2, 3, 'python_results');
                        $pyResults = $this->fetchJobResults($jobId, $userId);
                    } else {
                        log_message('error', 'Python backend returned unexpected response: ' . ($body ?? '(empty)'));
                    }
                } else {
                    log_message('error', sprintf(
                        'Python backend HTTP %d: %s',
                        $statusCode,
                        $response->getBody() ?? '(empty)'
                    ));
                }
            } catch (\Throwable $e) {
                log_message('error', 'Python backend unreachable: ' . $e->getMessage());
            }
        }

        // ── Fallback if Python returned nothing ──
        if ($hasPy && empty($pyResults)) {
            $pyAlgIds = [];
            foreach ($pyOnly as $algList) {
                foreach ((array)$algList as $algId) {
                    $pyAlgIds[] = $algId;
                }
            }
            foreach ($pyAlgIds as $algId) {
                $fallback = $this->staticFallback($algId);
                if (!empty($fallback)) {
                    $pyResults = array_merge($pyResults, $fallback);
                }
            }

            foreach ($pyResults as &$r) {
                $r['engine_note'] .= ' — Python backend offline, showing demo data';
            }
            unset($r);
        }

        // ── Mark job completed if PHP-only (no Python backend call) ──
        if (!$hasPy && $jobId > 0) {
            $this->completeJob($jobId);
        }

        // ── Merge PHP + Python results ──
        $results = array_merge($results, $pyResults);

        $severityOrder = ['High' => 0, 'Medium' => 1, 'Low' => 2];
        usort($results, function ($a, $b) use ($severityOrder) {
            return ($severityOrder[$a['severity']] ?? 9) <=> ($severityOrder[$b['severity']] ?? 9);
        });

        return $results;
    }

    /**
     * Reads anomaly findings from ml_results for a completed job and maps
     * them into the associative-array format the wizard view expects.
     *
     * @param  int  $jobId
     * @param  int  $userId
     * @return array
     */
    public function fetchJobResults(int $jobId, int $userId): array
    {
        $iconMap = [
            'sms'         => 'fas fa-sms',
            'contacts'    => 'fas fa-address-book',
            'call_logs'   => 'fas fa-phone',
            'locations'   => 'fas fa-map-marker-alt',
            'apps'        => 'fas fa-th-large',
            'files'       => 'fas fa-folder-open',
            'activity'    => 'fas fa-heartbeat',
            'device_info' => 'fas fa-microchip',
        ];

        $labelMap = [
            'sms'         => 'SMS',
            'contacts'    => 'Contacts',
            'call_logs'   => 'Call Log',
            'locations'   => 'Location',
            'apps'        => 'Installed Apps',
            'files'       => 'Files',
            'activity'    => 'Activity',
            'device_info' => 'Device Info',
        ];

        try {
            $rows = $this->db->table('ml_results')
                ->where('job_id', $jobId)
                ->where('user_id', $userId)
                ->orderBy("FIELD(severity, 'High', 'Medium', 'Low')")
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'fetchJobResults failed: ' . $e->getMessage());
            return [];
        }

        $results = [];
        foreach ($rows as $r) {
            $cat = $r['category'] ?? 'unknown';
            $results[] = [
                'category'    => $labelMap[$cat] ?? ucfirst($cat),
                'icon'        => $iconMap[$cat] ?? 'fas fa-question-circle',
                'anomaly'     => $r['anomaly'] ?? 'No details',
                'severity'    => $r['severity'] ?? 'Medium',
                'algorithm'   => $r['algorithm'] ?? 'Unknown',
                'timestamp'   => $r['event_timestamp'] ?? $r['created_at'] ?? date('Y-m-d H:i:s'),
                'engine_note' => sprintf(
                    'Python %s (score: %.4f)',
                    $r['algorithm_id'] ?? 'detector',
                    (float)($r['score'] ?? 0)
                ),
            ];
        }

        return $results;
    }

    // =========================================================================
    // Data Fetchers (CI4 query builder wrappers)
    // =========================================================================

    /** @return array SMS rows for a user */
    protected function fetchSms(int $userId): array
    {
        if ($userId <= 0) return [];
        try {
            return $this->db->table('tbl_sms')
                ->select("address, body, DATE_FORMAT(FROM_UNIXTIME(sms_date/1000), '%Y-%m-%d %H:%i:%s') AS date")
                ->where('owner_id', $userId)
                ->orderBy('sms_date', 'DESC')
                ->limit(500)
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'fetchSms failed: ' . $e->getMessage());
            return [];
        }
    }

    /** @return array Contact rows for a user */
    protected function fetchContacts(int $userId): array
    {
        if ($userId <= 0) return [];
        try {
            $rows = $this->db->table('tbl_contacts')
                ->select("display_name, phone_numbers, created_at AS last_modified")
                ->where('owner_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->limit(1000)
                ->get()->getResultArray();
            
            foreach ($rows as &$row) {
                $numbers = json_decode($row['phone_numbers'] ?? '', true);
                $row['phone_number'] = (!empty($numbers) && is_array($numbers)) ? $numbers[0] : '';
            }
            return $rows;
        } catch (\Throwable $e) {
            log_message('error', 'fetchContacts failed: ' . $e->getMessage());
            return [];
        }
    }

    /** @return array Call log rows for a user */
    protected function fetchCallLogs(int $userId): array
    {
        if ($userId <= 0) return [];
        try {
            return $this->db->table('tbl_logs')
                ->select("phone_number AS number, duration_seconds, DATE_FORMAT(FROM_UNIXTIME(call_date/1000), '%Y-%m-%d %H:%i:%s') AS date")
                ->where('owner_id', $userId)
                ->orderBy('call_date', 'DESC')
                ->limit(500)
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'fetchCallLogs failed: ' . $e->getMessage());
            return [];
        }
    }

    /** @return array Location rows for a user */
    protected function fetchLocations(int $userId): array
    {
        if ($userId <= 0) return [];
        try {
            return $this->db->table('tbl_location')
                ->select("latitude, longitude, created_at AS timestamp")
                ->where('owner_id', $userId)
                ->orderBy('created_at', 'ASC')
                ->limit(1000)
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'fetchLocations failed: ' . $e->getMessage());
            return [];
        }
    }

    /** @return array Installed app rows for a user */
    protected function fetchApps(int $userId): array
    {
        if ($userId <= 0) return [];
        try {
            $rows = $this->db->table('tbl_apps')
                ->select("app_name, package_name, permissions, created_at AS install_date")
                ->where('owner_id', $userId)
                ->get()->getResultArray();
            
            foreach ($rows as &$row) {
                if (is_string($row['permissions'])) {
                    $row['permissions'] = json_decode($row['permissions'], true) ?? [];
                }
            }
            return $rows;
        } catch (\Throwable $e) {
            log_message('error', 'fetchApps failed: ' . $e->getMessage());
            return [];
        }
    }

    /** @return array File rows for a user */
    protected function fetchFiles(int $userId): array
    {
        if ($userId <= 0) return [];
        try {
            return $this->db->table('tbl_device_files')
                ->select("name AS file_name, created_at")
                ->where('owner_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->limit(2000)
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'fetchFiles failed: ' . $e->getMessage());
            return [];
        }
    }

    /** @return array Activity rows for daily screen time */
    protected function fetchActivityScreenTime(int $userId): array
    {
        if ($userId <= 0) return [];
        try {
            return $this->db->table('tbl_app_usage')
                ->select("DATE(created_at) AS date, SUM(foreground_time_minutes) AS screen_on_minutes")
                ->where('owner_id', $userId)
                ->groupBy("DATE(created_at)")
                ->orderBy("DATE(created_at)", 'DESC')
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'fetchActivityScreenTime failed: ' . $e->getMessage());
            return [];
        }
    }

    /** @return array Activity rows for app switches */
    protected function fetchActivitySwitchRate(int $userId): array
    {
        if ($userId <= 0) return [];
        try {
            return $this->db->table('tbl_app_usage')
                ->select("package_name AS app_package, DATE_FORMAT(FROM_UNIXTIME(last_time_used/1000), '%Y-%m-%d %H:%i:%s') AS timestamp")
                ->where('owner_id', $userId)
                ->orderBy('last_time_used', 'ASC')
                ->limit(2000)
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'fetchActivitySwitchRate failed: ' . $e->getMessage());
            return [];
        }
    }

    /** @return array Device info (current or previous snapshot) for a user */
    protected function fetchDeviceInfo(int $userId, string $snapshot = 'current'): array
    {
        if ($userId <= 0) return [];
        try {
            $tokenChecksums = $this->db->table('tbl_tokens')
                ->select('device_checksum')
                ->where('owner_id', $userId)
                ->where('device_checksum !=', '')
                ->where('device_checksum IS NOT NULL')
                ->groupBy('device_checksum')
                ->get()->getResultArray();

            $uploadChecksums = $this->db->table('uploaded_files')
                ->select('device_checksum')
                ->where('token_owner_id', $userId)
                ->where('device_checksum !=', '')
                ->where('device_checksum IS NOT NULL')
                ->groupBy('device_checksum')
                ->get()->getResultArray();

            $allChecksums = array_unique(array_merge(
                array_column($tokenChecksums, 'device_checksum'),
                array_column($uploadChecksums, 'device_checksum')
            ));

            if (empty($allChecksums)) {
                return [];
            }

            $builder = $this->db->table('tbl_device_profile')
                ->whereIn('device_id', $allChecksums);

            if ($snapshot === 'previous') {
                $rows = $builder->orderBy('extraction_timestamp', 'DESC')
                    ->limit(2)
                    ->get()->getResultArray();
                return (count($rows) >= 2) ? $rows[1] : [];
            } else {
                $row = $builder->orderBy('extraction_timestamp', 'DESC')
                    ->limit(1)
                    ->get()->getRowArray();
                return $row ?? [];
            }
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** @return array Network profile rows for a user */
    protected function fetchNetworkProfile(int $userId): array
    {
        if ($userId <= 0) return [];
        try {
            return $this->db->table('tbl_network_info')
                ->select("wifi_ssid AS ssid, connection_type AS type, 0 AS vpn_active, DATE_FORMAT(FROM_UNIXTIME(extracted_at/1000), '%Y-%m-%d %H:%i:%s') AS timestamp")
                ->where('owner_id', $userId)
                ->orderBy('extracted_at', 'DESC')
                ->limit(200)
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'fetchNetworkProfile failed: ' . $e->getMessage());
            return [];
        }
    }

    // =========================================================================
    // Static demo data (used as fallback when DB tables are empty/missing)
    // =========================================================================

    /**
     * Returns a static list of dummy anomaly findings for the demo results view.
     *
     * @return array
     */
    public function getDummyResults(): array
    {
        return [
            [
                'category'   => 'SMS',
                'icon'       => 'fas fa-sms',
                'anomaly'    => 'Unusual message frequency at 3 AM – 47 messages in 12 minutes',
                'severity'   => 'High',
                'algorithm'  => 'Frequency Spike Detector',
                'timestamp'  => '2026-07-12 03:12:00',
                'engine_note'=> 'Z-Score 4.2 σ (mean: 3.1 msgs/window, σ: 2.4)',
            ],
            [
                'category'   => 'Location',
                'icon'       => 'fas fa-map-marker-alt',
                'anomaly'    => 'Visit to unknown location (Industrial Zone, 38 km from home base)',
                'severity'   => 'Medium',
                'algorithm'  => 'Geo-Fence Violation Detector',
                'timestamp'  => '2026-07-13 01:45:00',
                'engine_note'=> 'Home zone radius: 12.4 km from centroid (-1.2921, 36.8219)',
            ],
            [
                'category'   => 'Call Log',
                'icon'       => 'fas fa-phone',
                'anomaly'    => 'Burst of 11 short calls (< 8 s each) to unknown number +254-700-XXXX',
                'severity'   => 'High',
                'algorithm'  => 'Short-Call Burst Detector',
                'timestamp'  => '2026-07-13 02:30:00',
                'engine_note'=> 'Window: 30 min; 11 calls detected (threshold: 3)',
            ],
            [
                'category'   => 'Installed Apps',
                'icon'       => 'fas fa-th-large',
                'anomaly'    => 'App "com.util.sync.hidden" installed at 02:17 AM – not on known-safe list',
                'severity'   => 'High',
                'algorithm'  => 'Package Reputation Scanner',
                'timestamp'  => '2026-07-11 02:17:43',
                'engine_note'=> 'Matched pattern: "hidden"; installed during night window',
            ],
            [
                'category'   => 'Contacts',
                'icon'       => 'fas fa-address-book',
                'anomaly'    => '23 new contacts added within 4 minutes (7× above 30-day average)',
                'severity'   => 'High',
                'algorithm'  => 'New-Contact Frequency Monitor',
                'timestamp'  => '2026-07-10 14:55:22',
                'engine_note'=> 'Daily threshold: 4; 23 added on 2026-07-10',
            ],
            [
                'category'   => 'Device Info',
                'icon'       => 'fas fa-microchip',
                'anomaly'    => 'IMEI changed between uploads: previous 35XXXXXX → current 86XXXXXX',
                'severity'   => 'High',
                'algorithm'  => 'Hardware Change Detector',
                'timestamp'  => '2026-07-09 08:00:00',
                'engine_note'=> 'Identifier field: imei',
            ],
            [
                'category'   => 'Files',
                'icon'       => 'fas fa-folder-open',
                'anomaly'    => '1,240 files created in /sdcard/Android/data in under 2 minutes',
                'severity'   => 'Medium',
                'algorithm'  => 'File Creation Spike Detector',
                'timestamp'  => '2026-07-12 22:41:10',
                'engine_note'=> 'Daily threshold: 310 files; 1,240 created on 2026-07-12',
            ],
            [
                'category'   => 'Activity',
                'icon'       => 'fas fa-heartbeat',
                'anomaly'    => 'Screen-time 14.3 h on 2026-07-11 (baseline: 3.1 h ± 0.8 h)',
                'severity'   => 'Medium',
                'algorithm'  => 'Screen-Time Anomaly Detector',
                'timestamp'  => '2026-07-11 23:59:00',
                'engine_note'=> 'Z-Score: 3.7 σ above mean',
            ],
            [
                'category'   => 'SMS',
                'icon'       => 'fas fa-sms',
                'anomaly'    => 'Messages from unrecognised sender bulk-received at 04:05 AM (18 msgs)',
                'severity'   => 'Medium',
                'algorithm'  => 'Time-Pattern Analyser',
                'timestamp'  => '2026-07-08 04:05:33',
                'engine_note'=> 'Night window: 23:00 – 05:00',
            ],
            [
                'category'   => 'Location',
                'icon'       => 'fas fa-map-marker-alt',
                'anomaly'    => 'Device at unknown location for 4 h 20 min (01:00 – 05:20)',
                'severity'   => 'Medium',
                'algorithm'  => 'Geo-Fence Violation Detector',
                'timestamp'  => '2026-07-07 01:00:00',
                'engine_note'=> '26 km from home zone centroid',
            ],
            [
                'category'   => 'Call Log',
                'icon'       => 'fas fa-phone',
                'anomaly'    => 'Incoming call at 03:47 AM from unsaved international number (+44-20-XXX)',
                'severity'   => 'Low',
                'algorithm'  => 'Night-Activity Monitor',
                'timestamp'  => '2026-07-06 03:47:15',
                'engine_note'=> 'Night window: 23:00 – 05:00',
            ],
            [
                'category'   => 'Installed Apps',
                'icon'       => 'fas fa-th-large',
                'anomaly'    => '"FlashLight Pro" requests 19 permissions including READ_SMS, RECORD_AUDIO',
                'severity'   => 'Low',
                'algorithm'  => 'Permission Anomaly Detector',
                'timestamp'  => '2026-07-05 11:23:00',
                'engine_note'=> 'Threshold: 5 sensitive permissions; 19 detected (Z-Score 2.8 σ)',
            ],
        ];
    }

    /**
     * Returns the appropriate static fallback entries for a given algorithm ID.
     *
     * @param  string $algId
     * @return array
     */
    protected function staticFallback(string $algId): array
    {
        $map = [
            'sms_freq'      => ['SMS', 'fas fa-sms', 'Unusual message frequency at 3 AM – 47 messages in 12 minutes', 'High', 'Frequency Spike Detector', '2026-07-12 03:12:00', 'Z-Score 4.2 σ (demo data – no live SMS rows found)'],
            'sms_time'      => ['SMS', 'fas fa-sms', 'Messages from unrecognised sender bulk-received at 04:05 AM', 'Medium', 'Time-Pattern Analyser', '2026-07-08 04:05:33', 'Night window 23:00–05:00 (demo data)'],
            'contacts_freq' => ['Contacts', 'fas fa-address-book', '23 new contacts added within 4 minutes (7× above average)', 'High', 'New-Contact Frequency Monitor', '2026-07-10 14:55:22', 'Demo data – no live contacts rows found'],
            'contacts_dup'  => ['Contacts', 'fas fa-address-book', 'Duplicate phone number shared by two contact entries', 'Medium', 'Duplicate & Anomaly Detector', '2026-07-09 10:00:00', 'Demo data – no live contacts rows found'],
            'calls_burst'   => ['Call Log', 'fas fa-phone', 'Burst of 11 short calls (< 8 s each) to +254-700-XXXX', 'High', 'Short-Call Burst Detector', '2026-07-13 02:30:00', 'Demo data – no live call log rows found'],
            'calls_night'   => ['Call Log', 'fas fa-phone', 'Incoming call at 03:47 AM from unsaved international number', 'Low', 'Night-Activity Monitor', '2026-07-06 03:47:15', 'Demo data – no live call log rows found'],
            'loc_geofence'  => ['Location', 'fas fa-map-marker-alt', 'Visit to unknown location 38 km from home base', 'Medium', 'Geo-Fence Violation Detector', '2026-07-13 01:45:00', 'Demo data – no live location rows found'],
            'loc_speed'     => ['Location', 'fas fa-map-marker-alt', 'Travel speed 1,240 km/h detected between two consecutive points', 'High', 'Travel Speed Anomaly', '2026-07-11 08:00:00', 'Demo data – threshold: 900 km/h'],
            'apps_rep'      => ['Installed Apps', 'fas fa-th-large', 'App "com.util.sync.hidden" installed at 02:17 AM', 'High', 'Package Reputation Scanner', '2026-07-11 02:17:43', 'Demo data – matched pattern "hidden"'],
            'apps_perm'     => ['Installed Apps', 'fas fa-th-large', '"FlashLight Pro" requests 19 permissions: READ_SMS, RECORD_AUDIO…', 'Low', 'Permission Anomaly Detector', '2026-07-05 11:23:00', 'Demo data – Z-Score 2.8 σ'],
            'files_spike'   => ['Files', 'fas fa-folder-open', '1,240 files created in /sdcard/Android/data in under 2 minutes', 'Medium', 'File Creation Spike Detector', '2026-07-12 22:41:10', 'Demo data – 4× daily average'],
            'act_screen'    => ['Activity', 'fas fa-heartbeat', 'Screen-time 14.3 h on 2026-07-11 (baseline: 3.1 h ± 0.8 h)', 'Medium', 'Screen-Time Anomaly Detector', '2026-07-11 23:59:00', 'Demo data – Z-Score 3.7 σ'],
            'act_switch'    => ['Activity', 'fas fa-heartbeat', '87 app switches in one hour (2026-07-10 22:00) – possible scripted behaviour', 'Medium', 'App-Switch Rate Monitor', '2026-07-10 22:00:00', 'Demo data – threshold: 60 switches/hour'],
            'dev_hw'        => ['Device Info', 'fas fa-microchip', 'IMEI changed: previous 35XXXXXX → current 86XXXXXX', 'High', 'Hardware Change Detector', '2026-07-09 08:00:00', 'Demo data – IMEI field changed between uploads'],
            'dev_net'       => ['Device Info', 'fas fa-microchip', 'Connected to unknown Wi-Fi SSID "Guest_Open_5G" at 03:12 AM', 'Medium', 'Network Profile Monitor', '2026-07-08 03:12:00', 'Demo data – SSID not in known-safe list'],

            // ── Python-only algorithm fallbacks ──────────────────────────────
            'sms_bert'        => ['SMS', 'fas fa-sms', 'BERT classifier flagged a message with high phishing probability (92%)', 'High', 'BERT Semantic Phishing Classifier', '2026-07-12 03:12:00', 'Python demo – BERT transformer model (threshold: 0.85)'],
            'contacts_graph'  => ['Contacts', 'fas fa-address-book', 'Graph model found 4 orphaned contacts with no relational edges', 'Medium', 'Graph Relation Outlier Model (GCN)', '2026-07-10 14:55:22', 'Python demo – GCN embedding anomaly score: 2.3 σ'],
            'calls_isolation' => ['Call Log', 'fas fa-phone', 'Isolation Forest flagged an anomalous short incoming call at 03:47 AM from international number', 'High', 'Isolation Forest Outlier Detection', '2026-07-13 02:30:00', 'Python demo – iForest contamination: 0.05, score: -0.32'],
            'apps_autoencoder' => ['Installed Apps', 'fas fa-th-large', 'Autoencoder detected app "com.security.fake" with abnormal manifest structure', 'High', 'Neural Autoencoder App Classifier', '2026-07-11 02:17:43', 'Python demo – reconstruction error: 4.2 σ above mean'],
            'files_entropy'   => ['Files', 'fas fa-folder-open', 'High-entropy file (7.6 b/byte) found in /sdcard/Download – possible encrypted payload', 'Medium', 'File Entropy & Encryption Scanner', '2026-07-12 22:41:10', 'Python demo – Shannon entropy threshold: 7.2'],
            'act_lstm'        => ['Activity', 'fas fa-heartbeat', 'LSTM prediction error spike at 22:00 – 87 app switches deviated from learned sequence pattern', 'Medium', 'LSTM Sequence Pattern Predictor', '2026-07-10 22:00:00', 'Python demo – prediction error: 3.1 σ above baseline'],
            'dev_oneclass'    => ['Device Info', 'fas fa-microchip', 'One-Class SVM detected abnormal system state: CPU 97%, RAM 89%, battery 43 °C', 'High', 'One-Class SVM System-State Profiler', '2026-07-09 08:00:00', 'Python demo – nu=0.05, gamma=0.01, boundary distance: -0.41'],
        ];

        if (!isset($map[$algId])) {
            return [];
        }

        [$cat, $icon, $anomaly, $sev, $alg, $ts, $note] = $map[$algId];
        return [[
            'category'   => $cat,
            'icon'       => $icon,
            'anomaly'    => $anomaly,
            'severity'   => $sev,
            'algorithm'  => $alg,
            'timestamp'  => $ts,
            'engine_note'=> $note,
        ]];
    }

    // =========================================================================
    // View helpers
    // =========================================================================

    /**
     * Returns anomaly alert findings from the most recent completed job
     * for the specified categories. Used to embed alert cards into
     * the intelligence dashboard pages.
     *
     * @param  string[] $categoryKeys  Machine category keys (sms, contacts, locations, ...)
     * @param  int      $userId
     * @param  int      $limit         Max alerts to return
     * @return array
     */
    public function getAnomalyAlerts(array $categoryKeys, int $userId, int $limit = 5): array
    {
        if ($userId <= 0 || empty($categoryKeys)) {
            return [];
        }

        try {
            $job = $this->db->table('ml_jobs')
                ->where('user_id', $userId)
                ->where('status', 'completed')
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()->getRowArray();

            if (!$job) {
                return [];
            }

            $rows = $this->db->table('ml_results')
                ->where('job_id', $job['id'])
                ->where('user_id', $userId)
                ->whereIn('category', $categoryKeys)
                ->orderBy("FIELD(severity, 'High', 'Medium', 'Low')")
                ->limit($limit)
                ->get()->getResultArray();

            return $rows ?: [];
        } catch (\Throwable $e) {
            log_message('error', 'getAnomalyAlerts failed: ' . $e->getMessage());
            return [];
        }
    }

    // =========================================================================
    // Admin configuration helpers
    // =========================================================================

    /**
     * Reads anomaly detection settings from the DB settings table
     * (class='anomaly'). Returns default engine and allowed algorithm list.
     *
     * @return array{default_engine: string, allowed_algorithms: string[]|null}
     */
    public function getAdminAnomalySettings(): array
    {
        $result = [
            'default_engine'    => 'both',
            'allowed_algorithms' => null, // null = all allowed
        ];

        try {
            $rows = $this->db->table('settings')
                ->where('class', 'anomaly')
                ->get()->getResultArray();

            foreach ($rows as $r) {
                if ($r['key'] === 'default_engine') {
                    $result['default_engine'] = $r['value'] ?: 'both';
                }
                if ($r['key'] === 'allowed_algorithms' && $r['value']) {
                    $decoded = json_decode($r['value'], true);
                    $result['allowed_algorithms'] = is_array($decoded) && !empty($decoded)
                        ? $decoded
                        : null;
                }
            }
        } catch (\Throwable $e) {
            // Settings table may not exist; return defaults
        }

        return $result;
    }

    /**
     * Returns the allowed algorithm IDs from admin config.
     * Returns null if all algorithms are allowed.
     *
     * @return string[]|null
     */
    public function getAllowedAlgorithmIds(): ?array
    {
        $settings = $this->getAdminAnomalySettings();
        return $settings['allowed_algorithms'];
    }

    /**
     * Returns the admin-configured default engine.
     *
     * @return string 'php' | 'python' | 'both'
     */
    public function getDefaultEngine(): string
    {
        $settings = $this->getAdminAnomalySettings();
        return $settings['default_engine'];
    }

    /**
     * Filters algorithm categories to only include algorithms
     * present in the allowed list. If allowed list is null/empty,
     * returns all algorithms unchanged.
     *
     * @param  array      $categories Full category tree from getAlgorithmCategories()
     * @param  string[]|null $allowedIds Algorithm IDs to keep, or null for all
     * @return array
     */
    public function filterAllowedAlgorithms(array $categories, ?array $allowedIds): array
    {
        if ($allowedIds === null || empty($allowedIds)) {
            return $categories;
        }

        $allowedSet = array_flip($allowedIds);
        $filtered = [];

        foreach ($categories as $catKey => $cat) {
            $filteredAlgs = array_values(array_filter(
                $cat['algorithms'],
                fn($a) => isset($allowedSet[$a['id']])
            ));

            if (!empty($filteredAlgs)) {
                $cat['algorithms'] = $filteredAlgs;
                $filtered[$catKey] = $cat;
            }
        }

        return $filtered;
    }

    /**
     * Map every known algorithm id to its plan tier (core|advanced|deep).
     * Python-only / heavy-ML models are gated to higher tiers.
     */
    public function getAlgorithmTiers(): array
    {
        return [
            // Core (available on free and up)
            'sms_freq' => 'core', 'sms_time' => 'core', 'sms_cluster' => 'core',
            'contacts_freq' => 'core', 'contacts_dup' => 'core',
            'calls_burst' => 'core', 'calls_night' => 'core',
            'loc_geofence' => 'core', 'loc_speed' => 'core', 'loc_dbscan' => 'core',
            'apps_rep' => 'core', 'apps_perm' => 'core',
            'files_spike' => 'core', 'files_ext' => 'core',
            'act_screen' => 'core', 'act_switch' => 'core',
            'dev_hw' => 'core', 'dev_net' => 'core',
            // Advanced
            'files_entropy' => 'advanced',
            // Deep (python models)
            'sms_bert' => 'deep', 'contacts_graph' => 'deep',
            'calls_isolation' => 'deep', 'apps_autoencoder' => 'deep',
            'act_lstm' => 'deep', 'dev_oneclass' => 'deep',
        ];
    }

    /**
     * Filter categories by a user's allowed plan algorithm ids.
     */
    public function filterByPlanAlgorithms(array $categories, array $allowedIds): array
    {
        if (empty($allowedIds)) {
            return [];
        }
        return $this->filterAllowedAlgorithms($categories, $allowedIds);
    }

    /**
     * Returns severity-to-badge mapping for the results view.
     *
     * @return array<string, array{badge: string, icon: string}>
     */
    // =========================================================================
    // Job history
    // =========================================================================

    /**
     * Ensures the ml_jobs table has columns needed for tracking run timing.
     */
    public function ensureJobTrackingColumns(): void
    {
        try {
            $this->db->query("ALTER TABLE ml_jobs ADD COLUMN completed_at DATETIME DEFAULT NULL AFTER status");
        } catch (\Throwable $e) {
            // Column already exists
        }
    }

    /**
     * Returns anomaly detection run history (all users, for admin view).
     *
     * @param  int  $limit
     * @return array
     */
    public function getJobHistory(int $limit = 50): array
    {
        $this->ensureJobTrackingColumns();

        try {
            $rows = $this->db->table('ml_jobs j')
                ->select("
                    j.id, j.user_id, j.engine, j.algorithms, j.scope,
                    j.status, j.created_at, j.completed_at,
                    u.username AS owner_name,
                    TIMESTAMPDIFF(SECOND, j.created_at, COALESCE(j.completed_at, j.created_at)) AS time_taken
                ")
                ->join('users u', 'u.id = j.user_id', 'left')
                ->orderBy('j.created_at', 'DESC')
                ->limit($limit)
                ->get()
                ->getResultArray();

            foreach ($rows as &$row) {
                $algIds = json_decode($row['algorithms'] ?? '[]', true);
                $algNames = [];
                foreach ((array)$algIds as $aid) {
                    $algNames[] = $this->algorithmDisplayName($aid);
                }
                $row['algorithm_names'] = array_filter($algNames);
                $row['algorithm_count'] = count($algIds);
            }
            unset($row);

            return $rows;
        } catch (\Throwable $e) {
            log_message('error', 'getJobHistory failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Returns a human-readable display name for an algorithm ID.
     */
    protected function algorithmDisplayName(string $algorithmId): ?string
    {
        $categories = $this->getAlgorithmCategories();
        foreach ($categories as $cat) {
            foreach ($cat['algorithms'] as $alg) {
                if ($alg['id'] === $algorithmId) {
                    return $alg['name'];
                }
            }
        }
        return null;
    }

    public function getSeverityMap(): array
    {
        return [
            'High'   => ['badge' => 'danger',  'icon' => 'fas fa-angle-double-up'],
            'Medium' => ['badge' => 'warning', 'icon' => 'fas fa-angle-up'],
            'Low'    => ['badge' => 'info',    'icon' => 'fas fa-angle-right'],
        ];
    }

    /**
     * Aggregates severity counts from a results array.
     *
     * @param  array $results
     * @return array{total: int, high: int, medium: int, low: int}
     */
    public function getSeverityCounts(array $results): array
    {
        $counts = ['total' => count($results), 'high' => 0, 'medium' => 0, 'low' => 0];
        foreach ($results as $r) {
            match (strtolower($r['severity'] ?? '')) {
                'high'   => $counts['high']++,
                'medium' => $counts['medium']++,
                'low'    => $counts['low']++,
                default  => null,
            };
        }
        return $counts;
    }

    // =========================================================================
    // Math utilities
    // =========================================================================

    /**
     * Calculates the population standard deviation of a numeric array.
     *
     * @param  float[] $values
     * @param  float   $mean   Pre-computed mean (pass 0 to auto-compute)
     * @return float
     */
    protected function stdDev(array $values, float $mean = 0): float
    {
        if (count($values) < 2) {
            return 0.0;
        }
        if ($mean === 0.0) {
            $mean = array_sum($values) / count($values);
        }
        $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values)) / count($values);
        return sqrt($variance);
    }

    /**
     * Calculates the Haversine distance between two lat/lng points in kilometres.
     *
     * @param  float $lat1
     * @param  float $lon1
     * @param  float $lat2
     * @param  float $lon2
     * @return float  Distance in km
     */
    protected function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R  = 6371; // Earth radius in km
        $dL = deg2rad($lat2 - $lat1);
        $dG = deg2rad($lon2 - $lon1);
        $a  = sin($dL / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dG / 2) ** 2;
        return $R * 2 * asin(sqrt($a));
    }

    /**
     * Persist anomaly results to ml_results so the results page can read them.
     */
    public function saveResults(int $jobId, int $userId, array $results): void
    {
        try {
            $batch = [];
            $hasHighSeverity = false;
            foreach ($results as $r) {
                $batch[] = [
                    'job_id'          => $jobId,
                    'user_id'         => $userId,
                    'category'        => $r['category'] ?? '',
                    'algorithm'       => $r['algorithm'] ?? '',
                    'algorithm_id'    => $r['algorithm_id'] ?? '',
                    'severity'        => $r['severity'] ?? 'Low',
                    'anomaly'         => $r['anomaly'] ?? '',
                    'score'           => (float)($r['score'] ?? 0),
                    'event_timestamp' => $r['timestamp'] ?? null,
                    'details'         => json_encode([
                        'engine_note' => $r['engine_note'] ?? '',
                        'icon'        => $r['icon'] ?? '',
                    ]),
                    'created_at'      => date('Y-m-d H:i:s'),
                ];
                if (($r['severity'] ?? 'Low') === 'High') {
                    $hasHighSeverity = true;
                }
            }
            if (!empty($batch)) {
                $this->db->table('ml_results')->insertBatch($batch);
                
                // Trigger risk score recomputation for affected user/devices
                $this->triggerRiskRecompute($batch);
            }

            // Send high severity anomaly alert to admins
            if ($hasHighSeverity) {
                $this->sendHighSeverityAnomalyAlert($jobId, $userId, $results);
            }
        } catch (\Throwable $e) {
            log_message('error', 'saveResults failed: ' . $e->getMessage());
        }
    }

    /**
     * Trigger risk score recomputation after ML results are saved.
     */
    private function triggerRiskRecompute(array $batch): void
    {
        try {
            // Extract unique user/device pairs from the batch
            $pairs = [];
            foreach ($batch as $row) {
                $key = $row['user_id'] . ':' . $row['device_id'];
                if (!isset($pairs[$key])) {
                    $pairs[$key] = [
                        'user_id' => $row['user_id'],
                        'device_id' => $row['device_id'],
                    ];
                }
            }
            
            if (empty($pairs)) return;
            
            $riskService = new \App\Services\RiskScoreService();
            foreach ($pairs as $pair) {
                $riskService->computeScore($pair['user_id'], $pair['device_id'], 30);
            }
            
            log_message('info', 'RiskScore: Triggered recompute for ' . count($pairs) . ' user/device pairs');
        } catch (\Exception $e) {
            log_message('error', 'RiskScore trigger failed: ' . $e->getMessage());
        }
    }

    /**
     * Create a new ml_jobs row and return its ID.
     */
    public function createJob(int $userId, string $engine, array $algIds,
                              string $scope = 'full', int $totalAlgorithms = 0): int
    {
        $this->ensureJobTrackingColumns();
        try {
            $this->db->table('ml_jobs')->insert([
                'user_id'          => $userId,
                'engine'           => $engine,
                'algorithms'       => json_encode($algIds),
                'scope'            => $scope,
                'status'           => 'running',
                'total_algorithms' => $totalAlgorithms,
                'progress_pct'     => 0,
                'started_at'       => date('Y-m-d H:i:s'),
                'created_at'       => date('Y-m-d H:i:s'),
            ]);
            return $this->db->insertID();
        } catch (\Throwable $e) {
            log_message('error', 'createJob failed: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }

    /**
     * Fetch a single ml_jobs row by ID.
     */
    public function getJob(int $jobId): ?array
    {
        $row = $this->db->table('ml_jobs')
            ->where('id', $jobId)
            ->get()
            ->getRowArray();
        return $row ?: null;
    }

    /**
     * Get user email from multiple sources (auth_identities, users table, user_profiles)
     */
    private function getUserEmail(int $userId): ?string
    {
        // Try auth_identities (email_password)
        $emailRow = $this->db->table('auth_identities')
            ->select('secret AS email')
            ->where('user_id', $userId)
            ->where('type', 'email_password')
            ->get()
            ->getRowArray();
        if ($emailRow && !empty($emailRow['email'])) {
            return $emailRow['email'];
        }

        return null;
    }

    /**
     * Fetch the last completed ml_jobs row for a given user.
     */
    public function getUserLastCompletedJob(int $userId): ?array
    {
        try {
            $row = $this->db->table('ml_jobs')
                ->where('user_id', $userId)
                ->where('status', 'completed')
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
            return $row ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Fetch a single ML setting from the database.
     */
    protected function getMlSetting(string $key, $default = null)
    {
        $row = $this->db->table('settings')
            ->where('class', 'ml')
            ->where('key', $key)
            ->get()
            ->getRow();
        return $row ? $row->value : $default;
    }

    /**
     * Update ml_jobs progress columns.
     */
    public function updateJobProgress(int $jobId, int $completed, int $total, string $algId): void
    {
        if ($jobId <= 0) return;
        $pct = $total > 0 ? (int)round(($completed / $total) * 100) : 0;
        try {
            $this->db->table('ml_jobs')
                ->where('id', $jobId)
                ->update([
                    'progress_pct'         => min(99, $pct),
                    'completed_algorithms' => $completed,
                    'total_algorithms'     => $total,
                    'current_algorithm'    => $algId,
                ]);
        } catch (\Throwable $e) {
            // Ignore progress update failures
        }
    }

    public function startAlgorithm(int $jobId, int $completed, int $total, string $algId, string $engine = 'php'): ?float
    {
        if ($jobId <= 0) return null;
        $startMs = microtime(true) * 1000;

        $logs = $this->getAlgorithmLogsFromDb($jobId);
        $logs[] = [
            'id'           => $algId,
            'engine'       => $engine,
            'status'       => 'running',
            'started_at'   => date('Y-m-d H:i:s'),
            'completed_at' => null,
            'duration_ms'  => null,
        ];
        $this->saveAlgorithmLogsToDb($jobId, $logs);
        $this->updateJobProgress($jobId, $completed, $total, $algId);

        return $startMs;
    }

    public function completeAlgorithm(int $jobId, int $completed, int $total, string $algId, int $durationMs): void
    {
        if ($jobId <= 0) return;

        $logs = $this->getAlgorithmLogsFromDb($jobId);
        foreach ($logs as &$entry) {
            if ($entry['id'] === $algId && $entry['status'] === 'running') {
                $entry['status']       = 'completed';
                $entry['completed_at'] = date('Y-m-d H:i:s');
                $entry['duration_ms']  = $durationMs;
                break;
            }
        }
        unset($entry);
        $this->saveAlgorithmLogsToDb($jobId, $logs);
        $this->updateJobProgress($jobId, $completed, $total, $algId);
    }

    public function getAlgorithmLogs(int $jobId): array
    {
        $job = $this->getJob($jobId);
        $algorithmLogs = $this->getAlgorithmLogsFromDb($jobId);

        $results = $this->db->table('ml_results')
            ->where('job_id', $jobId)
            ->get()
            ->getResultArray();

        $resultsByAlg = [];
        foreach ($results as $r) {
            $resultsByAlg[$r['algorithm_id']][] = $r;
        }

        // If no algorithm_logs stored (old jobs), build from ml_results
        if (empty($algorithmLogs) && !empty($results)) {
            $seen = [];
            foreach ($results as $r) {
                $algId = $r['algorithm_id'] ?? '';
                if (!$algId || isset($seen[$algId])) continue;
                $seen[$algId] = true;
                $name = $this->algorithmDisplayName($algId) ?? $algId;
                $compat = $this->getAlgorithmCompat($algId);
                $algorithmLogs[] = [
                    'id'           => $algId,
                    'name'         => $name,
                    'engine'       => $compat === 'python' ? 'python' : 'php',
                    'status'       => 'completed',
                    'started_at'   => null,
                    'completed_at' => null,
                    'duration_ms'  => null,
                    'findings'     => $resultsByAlg[$algId] ?? [],
                ];
            }
        }

        // Enrich logs with algorithm display names and compat info
        $categories = $this->getAlgorithmCategories();
        foreach ($algorithmLogs as &$entry) {
            if (empty($entry['name'])) {
                $entry['name'] = $this->algorithmDisplayName($entry['id']) ?? $entry['id'];
            }
            if (empty($entry['engine'])) {
                $entry['engine'] = $this->getAlgorithmCompat($entry['id']);
            }
            $entry['findings'] = $resultsByAlg[$entry['id']] ?? [];
        }

        return $algorithmLogs;
    }

    private function getAlgorithmCompat(string $algId): string
    {
        $categories = $this->getAlgorithmCategories();
        foreach ($categories as $cat) {
            foreach ($cat['algorithms'] as $alg) {
                if ($alg['id'] === $algId) {
                    return $alg['compat'] ?? 'both';
                }
            }
        }
        return 'both';
    }

    private function getAlgorithmLogsFromDb(int $jobId): array
    {
        $row = $this->db->table('ml_jobs')
            ->select('algorithm_logs')
            ->where('id', $jobId)
            ->get()
            ->getRowArray();
        if (!$row || empty($row['algorithm_logs'])) return [];
        $decoded = json_decode($row['algorithm_logs'], true);
        return is_array($decoded) ? $decoded : [];
    }

    private function saveAlgorithmLogsToDb(int $jobId, array $logs): void
    {
        try {
            $this->db->table('ml_jobs')
                ->where('id', $jobId)
                ->update(['algorithm_logs' => json_encode($logs)]);
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    public function completeJob(int $jobId, ?string $error = null): void
    {
        if ($jobId <= 0) return;
        try {
            $logs = $this->getAlgorithmLogsFromDb($jobId);
            foreach ($logs as &$entry) {
                if ($entry['status'] === 'running') {
                    $entry['status']       = $error ? 'failed' : 'interrupted';
                    $entry['completed_at'] = date('Y-m-d H:i:s');
                }
            }
            unset($entry);
            $this->saveAlgorithmLogsToDb($jobId, $logs);

            $this->db->table('ml_jobs')
                ->where('id', $jobId)
                ->update([
                    'status'            => $error ? 'failed' : 'completed',
                    'progress_pct'      => 100,
                    'completed_at'      => date('Y-m-d H:i:s'),
                    'error_message'     => $error,
                ]);

            // Send completion email if job succeeded
            if (!$error) {
                $job = $this->getJob($jobId);
                if ($job) {
                    $algIds = json_decode($job['algorithms'] ?? '[]', true);
                    $this->sendAnalysisCompleteEmail((int)$job['user_id'], $jobId, count((array)$algIds));
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'completeJob failed for job #' . $jobId . ': ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    public function sendAnalysisCompleteEmail(int $userId, int $jobId, int $totalAlgs): void
    {
        $userEmail = $this->getUserEmail($userId);
        if (!$userEmail) {
            log_message('warning', 'sendAnalysisCompleteEmail: No email found for user ' . $userId);
            return;
        }

        $userName = 'User';
        $user = $this->db->table('users')->select('username')->where('id', $userId)->get()->getRowArray();
        if ($user && !empty($user['username'])) {
            $userName = $user['username'];
        }

        $job = $this->getJob($jobId);
        if (!$job) return;

        $results = $this->db->table('ml_results')
            ->where('job_id', $jobId)
            ->where('user_id', $userId)
            ->orderBy("FIELD(severity, 'High', 'Medium', 'Low')")
            ->get()
            ->getResultArray();

        $algorithms = [];
        $algIds = json_decode($job['algorithms'] ?? '[]', true);
        if (is_array($algIds)) {
            foreach ($algIds as $algId) {
                $info = $this->getAlgorithmInfo((string)$algId);
                if ($info) {
                    $algorithms[] = [
                        'name'         => $info['name'] ?? $algId,
                        'category_key' => $info['category_key'] ?? 'other',
                        'compat'       => $info['compat'] ?? 'both',
                    ];
                }
            }
        }

        $startedAt = $job['started_at'] ?? $job['created_at'] ?? null;
        $completedAt = $job['completed_at'] ?? 'N/A';
        $timeTaken = 'N/A';
        if ($startedAt && $completedAt && $completedAt !== 'N/A') {
            $startTs = strtotime($startedAt);
            $endTs = strtotime($completedAt);
            $diffSecs = max(0, $endTs - $startTs);
            if ($diffSecs < 60) {
                $timeTaken = $diffSecs . ' seconds';
            } elseif ($diffSecs < 3600) {
                $timeTaken = round($diffSecs / 60, 1) . ' minutes';
            } else {
                $timeTaken = round($diffSecs / 3600, 2) . ' hours';
            }
        }

        $categorySummary = [];
        $topFindings = [];
        foreach ($results as $r) {
            $cat = $r['category'] ?? 'Other';
            if (!isset($categorySummary[$cat])) {
                $categorySummary[$cat] = ['High' => 0, 'Medium' => 0, 'Low' => 0, 'total' => 0];
            }
            $sev = $r['severity'] ?? 'Low';
            $categorySummary[$cat][$sev]++;
            $categorySummary[$cat]['total']++;
            $topFindings[] = [
                'algorithm' => $r['algorithm'] ?? $r['algorithm_id'] ?? 'Unknown',
                'anomaly'   => $r['anomaly'] ?? 'No details',
                'severity'  => $sev,
                'score'     => (float)($r['score'] ?? 0),
                'category'  => $cat,
            ];
        }

        usort($topFindings, function ($a, $b) {
            $sevOrder = ['High' => 0, 'Medium' => 1, 'Low' => 2];
            return ($sevOrder[$a['severity']] ?? 9) <=> ($sevOrder[$b['severity']] ?? 9);
        });
        $topFindings = array_slice($topFindings, 0, 10);

        $resultsUrl = site_url("analysis/results/{$jobId}");

        $sent = send_templated_email(
            $userEmail,
            'Eaves Droid — Anomaly Analysis Complete (Job #' . $jobId . ')',
            'email/anomaly_analysis_complete',
            [
                'userName'        => $userName,
                'jobId'           => $jobId,
                'engine'          => $job['engine'] ?? 'unknown',
                'timeTaken'       => $timeTaken,
                'totalAlgs'       => $totalAlgs,
                'totalFindings'   => count($results),
                'completedAt'     => $completedAt,
                'algorithms'      => $algorithms,
                'categorySummary' => $categorySummary,
                'topFindings'     => $topFindings,
                'resultsUrl'      => $resultsUrl,
                'securityAction'        => 'Anomaly Analysis Complete',
                'securityDescription'   => 'Scheduled ML anomaly detection job finished. Results available for review.',
                'securityStatus'        => 'success',
                'securityInitiatedBy'   => 'System (Scheduled Job #' . $jobId . ')',
                'securityBrowser'       => 'CLI (Background Worker)',
                'securityBrowserIp'     => 'N/A',
                'securityExecutedAt'    => date('Y-m-d H:i:s'),
            ]
        );

        if (!$sent) {
            log_message('error', 'sendAnalysisCompleteEmail: failed to send to ' . $userEmail . ' for job #' . $jobId);
        } else {
            log_message('info', 'sendAnalysisCompleteEmail: sent to ' . $userEmail . ' for job #' . $jobId);
        }
    }

    private function sendHighSeverityAnomalyAlert(int $jobId, int $userId, array $results): void
    {
        try {
            $admins = send_admin_notification(
                'Eaves Droid — High Severity Anomaly Alert (Job #' . $jobId . ')',
                'email/admin/anomaly_high',
                [
                    'jobId'         => $jobId,
                    'highCount'     => count(array_filter($results, fn($r) => ($r['severity'] ?? 'Low') === 'High')),
                    'highFindings'  => array_filter($results, fn($r) => ($r['severity'] ?? 'Low') === 'High'),
                    'resultsUrl'    => site_url("analysis/results/{$jobId}"),
                    'securityAction'        => 'High Severity Anomaly Detected',
                    'securityDescription'   => 'ML anomaly detection job #' . $jobId . ' found high severity anomalies.',
                    'securityStatus'        => 'warning',
                    'securityInitiatedBy'   => 'System (ML Engine)',
                    'securityBrowser'       => 'CLI (ML Worker)',
                    'securityBrowserIp'     => 'N/A',
                    'securityExecutedAt'    => date('Y-m-d H:i:s'),
                ]
            );
        } catch (\Throwable $e) {
            log_message('error', 'sendHighSeverityAnomalyAlert failed: ' . $e->getMessage());
        }
    }
}
