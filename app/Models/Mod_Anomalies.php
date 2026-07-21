<?php

namespace App\Models;

use CodeIgniter\Model;

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
                'default'     => true,
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
        $k = min(3, $countSenders);
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

        // Run DBSCAN: epsilon = 0.01 (approx 1 km), minSamples = 2
        try {
            $dbscan = new \Phpml\Clustering\DBSCAN(0.01, 2);
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
                    'engine_note' => 'PHP-ML DBSCAN outlier (epsilon=0.01, minSamples=2)',
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
    public function runPhpDetection(array $selectedAlgs, int $userId = 0): array
    {
        $results = [];

        // Map of algorithm ID → [method, data-fetch closure]
        $dispatch = [
            // SMS
            'sms_freq'      => fn() => $this->detectSmsFrequencySpike($this->fetchSms($userId)),
            'sms_time'      => fn() => $this->detectSmsTimePattern($this->fetchSms($userId)),
            'sms_cluster'   => fn() => $this->detectSmsCluster($this->fetchSms($userId)),

            // Contacts
            'contacts_freq' => fn() => $this->detectContactsFrequency($this->fetchContacts($userId)),
            'contacts_dup'  => fn() => $this->detectContactsDuplicates($this->fetchContacts($userId)),

            // Call Logs
            'calls_burst'   => fn() => $this->detectCallsBurst($this->fetchCallLogs($userId)),
            'calls_night'   => fn() => $this->detectCallsNight($this->fetchCallLogs($userId)),

            // Locations
            'loc_geofence'  => fn() => $this->detectLocationGeofence($this->fetchLocations($userId)),
            'loc_speed'     => fn() => $this->detectLocationSpeed($this->fetchLocations($userId)),
            'loc_dbscan'    => fn() => $this->detectLocationDbscan($this->fetchLocations($userId)),

            // Apps
            'apps_rep'      => fn() => $this->detectAppsReputation($this->fetchApps($userId)),
            'apps_perm'     => fn() => $this->detectAppsPermission($this->fetchApps($userId)),

            // Files
            'files_spike'   => fn() => $this->detectFilesSpike($this->fetchFiles($userId)),

            // Activity
            'act_screen'    => fn() => $this->detectActivityScreenTime($this->fetchActivityScreenTime($userId)),
            'act_switch'    => fn() => $this->detectActivitySwitchRate($this->fetchActivitySwitchRate($userId)),

            // Device Info
            'dev_hw'        => fn() => $this->detectDeviceHardwareChange(
                                    $this->fetchDeviceInfo($userId, 'current'),
                                    $this->fetchDeviceInfo($userId, 'previous')
                                ),
            'dev_net'       => fn() => $this->detectDeviceNetworkProfile(
                                    $this->fetchNetworkProfile($userId),
                                    []
                                ),
        ];

        foreach ($selectedAlgs as $category => $algList) {
            foreach ((array)$algList as $algId) {
                if (isset($dispatch[$algId])) {
                    $found = $dispatch[$algId]();
                    if (!empty($found)) {
                        $results = array_merge($results, $found);
                    }
                }
            }
        }

        // Sort by severity (High → Medium → Low)
        $severityOrder = ['High' => 0, 'Medium' => 1, 'Low' => 2];
        usort($results, function ($a, $b) use ($severityOrder) {
            return ($severityOrder[$a['severity']] ?? 9) <=> ($severityOrder[$b['severity']] ?? 9);
        });

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
     * Returns severity-to-badge mapping for the results view.
     *
     * @return array<string, array{badge: string, icon: string}>
     */
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
}
