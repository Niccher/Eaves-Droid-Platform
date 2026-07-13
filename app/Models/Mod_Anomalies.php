<?php

namespace App\Models;

use CodeIgniter\Model;
use PhpMl\Clustering\KMeans;
use PhpMl\AnomalyDetection\IsolationForest;

/**
 * Mod_Anomalies
 *
 * Provides static / dummy data for the Anomaly Detection wizard,
 * and contains functional templates using the php-ai/php-ml library
 * demonstrating how actual detections are calculated.
 */
class Mod_Anomalies extends Model
{
    // -------------------------------------------------------------------------
    // PHP-ML Mathematical Proof-of-Concepts (Demonstration execution methods)
    // -------------------------------------------------------------------------

    /**
     * Executes K-Means Clustering on contact frequencies to detect anomalies.
     * Demonstrates importing and using KMeans from the PhpMl\Clustering package.
     *
     * @param array<int, array{incoming: int, outgoing: int}> $samples
     * @return array{clusters: array, outliers: array}
     */
    public function runKMeansClustering(array $samples): array
    {
        // 1. Convert inputs to matching arrays of [incoming_count, outgoing_count]
        $formattedSamples = [];
        foreach ($samples as $s) {
            $formattedSamples[] = [(float)$s['incoming'], (float)$s['outgoing']];
        }

        if (count($formattedSamples) < 3) {
            return ['clusters' => [], 'outliers' => []];
        }

        // 2. Instantiate and run K-Means
        $kmeans = new KMeans(3); // 3 clusters: High-Frequency, Medium-Frequency, Outliers
        $clusters = $kmeans->cluster($formattedSamples);

        // 3. Simple anomaly identification (elements with long euclidean distance from centroids)
        $outliers = [];
        foreach ($clusters as $cIndex => $cluster) {
            if (count($cluster) <= 1) {
                // Clusters with only 1 contact represent extreme outliers
                $outliers = array_merge($outliers, $cluster);
            }
        }

        return [
            'clusters' => $clusters,
            'outliers' => $outliers,
        ];
    }

    /**
     * Runs Isolation Forest algorithm (PhpMl AnomalyDetection) over call characteristics.
     *
     * @param array<int, array{duration: int, hour: int, is_international: int}> $calls
     * @return array Identified call outliers
     */
    public function runIsolationForestDetection(array $calls): array
    {
        $samples = [];
        foreach ($calls as $call) {
            $samples[] = [
                (float)$call['duration'],
                (float)$call['hour'],
                (float)$call['is_international']
            ];
        }

        if (count($samples) < 5) {
            return [];
        }

        // Initialize Isolation Forest from PHP-ML
        // Note: php-ml isolation forest requires target training and threshold scoring.
        $estimator = new IsolationForest(0.10); // 10% contamination threshold
        $estimator->train($samples);

        $outliers = [];
        foreach ($samples as $index => $sample) {
            $score = $estimator->predict($sample);
            if ($score === -1 || $score === 1) { // Estimator returns anomaly status based on training metrics
                $outliers[] = $calls[$index];
            }
        }

        return $outliers;
    }

    // -------------------------------------------------------------------------
    // Detection Engines
    // -------------------------------------------------------------------------

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
                    ['color' => 'success', 'icon' => 'fas fa-check',         'text' => 'No extra setup'],
                    ['color' => 'info',    'icon' => 'fas fa-tachometer-alt', 'text' => 'Fast startup'],
                    ['color' => 'secondary','icon'=> 'fas fa-lock',          'text' => 'In-process'],
                ],
                'description' => 'Runs entirely within the PHP runtime. Suitable for most datasets. '
                               . 'Limited to PHP‑ML algorithm support (K-Means, Isolation Forest, etc.).',
                'default'     => true,
            ],
            [
                'id'          => 'python',
                'label'       => 'Python‑based Engine',
                'subtitle'    => 'Separate Docker container · scikit-learn / PyOD',
                'icon'        => 'fab fa-python',
                'icon_color'  => 'text-warning',
                'badges'      => [
                    ['color' => 'warning', 'icon' => 'fab fa-docker',          'text' => 'Requires Docker'],
                    ['color' => 'primary', 'icon' => 'fas fa-brain',           'text' => 'Deep learning'],
                    ['color' => 'danger',  'icon' => 'fas fa-server',          'text' => 'Separate process'],
                ],
                'description' => 'Offloads computation to an isolated Docker microservice. '
                               . 'Supports advanced models (LSTM, Autoencoders, DBSCAN) and larger datasets.',
                'default'     => false,
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Algorithm Catalogue
    // -------------------------------------------------------------------------

    /**
     * Returns all data categories with their candidate algorithms.
     * Supports indicating compatibilities (e.g. php/python compatible or python-only).
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
                        'compat'      => 'python',
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

    // -------------------------------------------------------------------------
    // Results (dummy / static demo data)
    // -------------------------------------------------------------------------

    /**
     * Returns a static list of dummy anomaly findings for the demo results view.
     *
     * @return array<int, array{category: string, icon: string, anomaly: string, severity: string, algorithm: string, timestamp: string}>
     */
    public function getDummyResults(): array
    {
        return [
            [
                'category'  => 'SMS',
                'icon'      => 'fas fa-sms',
                'anomaly'   => 'Unusual message frequency at 3 AM – 47 messages in 12 minutes',
                'severity'  => 'High',
                'algorithm' => 'Frequency Spike Detector',
                'timestamp' => '2026-07-12 03:12:00',
            ],
            [
                'category'  => 'Location',
                'icon'      => 'fas fa-map-marker-alt',
                'anomaly'   => 'Visit to unknown location (Industrial Zone, 38 km from home base)',
                'severity'  => 'Medium',
                'algorithm' => 'Geo-Fence Violation Detector',
                'timestamp' => '2026-07-13 01:45:00',
            ],
            [
                'category'  => 'Call Log',
                'icon'      => 'fas fa-phone',
                'anomaly'   => 'Burst of 11 short calls (< 8 s each) to unknown number +254-700-XXXX',
                'severity'  => 'Low',
                'algorithm' => 'Short-Call Burst Detector',
                'timestamp' => '2026-07-13 02:30:00',
            ],
            [
                'category'  => 'Installed Apps',
                'icon'      => 'fas fa-th-large',
                'anomaly'   => 'App "com.util.sync.hidden" installed at 02:17 AM – not on known-safe list',
                'severity'  => 'High',
                'algorithm' => 'Package Reputation Scanner',
                'timestamp' => '2026-07-11 02:17:43',
            ],
            [
                'category'  => 'Contacts',
                'icon'      => 'fas fa-address-book',
                'anomaly'   => '23 new contacts added within 4 minutes (7× above 30-day average)',
                'severity'  => 'High',
                'algorithm' => 'New-Contact Frequency Monitor',
                'timestamp' => '2026-07-10 14:55:22',
            ],
            [
                'category'  => 'Device Info',
                'icon'      => 'fas fa-microchip',
                'anomaly'   => 'IMEI changed between uploads: previous 35XXXXXX vs current 86XXXXXX',
                'severity'  => 'High',
                'algorithm' => 'Hardware Change Detector',
                'timestamp' => '2026-07-09 08:00:00',
            ],
            [
                'category'  => 'Files',
                'icon'      => 'fas fa-folder-open',
                'anomaly'   => '1,240 files created in /sdcard/Android/data in under 2 minutes',
                'severity'  => 'Medium',
                'algorithm' => 'File Creation Spike Detector',
                'timestamp' => '2026-07-12 22:41:10',
            ],
            [
                'category'  => 'Activity',
                'icon'      => 'fas fa-heartbeat',
                'anomaly'   => 'Screen-time 14.3 h on 2026-07-11 (baseline: 3.1 h ± 0.8 h)',
                'severity'  => 'Medium',
                'algorithm' => 'Screen-Time Anomaly Detector',
                'timestamp' => '2026-07-11 23:59:00',
            ],
            [
                'category'  => 'SMS',
                'icon'      => 'fas fa-sms',
                'anomaly'   => 'Messages from unrecognised sender bulk-received at 04:05 AM (18 msgs)',
                'severity'  => 'Medium',
                'algorithm' => 'Time-Pattern Analyser',
                'timestamp' => '2026-07-08 04:05:33',
            ],
            [
                'category'  => 'Location',
                'icon'      => 'fas fa-map-marker-alt',
                'anomaly'   => 'Device at unknown location for 4 h 20 min (01:00 – 05:20)',
                'severity'  => 'Medium',
                'algorithm' => 'Geo-Fence Violation Detector',
                'timestamp' => '2026-07-07 01:00:00',
            ],
            [
                'category'  => 'Call Log',
                'icon'      => 'fas fa-phone',
                'anomaly'   => 'Incoming call at 03:47 AM from unsaved international number (+44-20-XXX)',
                'severity'  => 'Low',
                'algorithm' => 'Night-Activity Monitor',
                'timestamp' => '2026-07-06 03:47:15',
            ],
            [
                'category'  => 'Installed Apps',
                'icon'      => 'fas fa-th-large',
                'anomaly'   => '"FlashLight Pro" requests 19 permissions including READ_SMS, RECORD_AUDIO',
                'severity'  => 'Low',
                'algorithm' => 'Permission Anomaly Detector',
                'timestamp' => '2026-07-05 11:23:00',
            ],
        ];
    }

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
}
