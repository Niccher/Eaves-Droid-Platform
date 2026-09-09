<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\HTTP\CURLRequest;

/**
 * AnomaliesModel
 *
 * Provides static / dummy data for the Anomaly Detection wizard,
 * and contains functional PHP-ML-powered detection methods
 * that run the actual statistical and ML algorithms.
 *
 * Algorithm categories supported:
 *  - SMS           : Frequency Spike, Time-Pattern, Sender K-Means Clustering
 *  - ContactsController      : New-Contact Frequency, Duplicate Detector
 *  - Call LogsController     : Short-Call Burst, Night-Activity Monitor, Isolation Forest
 *  - Locations     : Geo-Fence Violation, Travel Speed Anomaly
 *  - Installed AppsController: Package Reputation Scanner, Permission Anomaly Detector
 *  - FilesController         : File Creation Spike, Extension Mismatch Scanner
 *  - Device Activity: Screen-Time Anomaly, App-Switch Rate Monitor
 *  - Device Info   : Hardware Change Detector, Network Profile Monitor
 *
 * Python engine support:
 *  Delegates Python-only algorithms (BERT, GCN, Isolation Forest, Autoencoder,
 *  Entropy Scanner, LSTM, One-Class SVM) to the external ml-eaves-droid FastAPI
 *  service. Falls back to PHP-ML if the backend is unreachable.
 */
class AnomaliesModel extends Model
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
                        'name'        => 'SMS Phishing Keyword Heuristic',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Scans SMS message bodies for phishing / social-engineering keyword indicators (urgency, impersonation, credential requests).',
                        'strengths'   => 'Fast, deterministic, no model download needed; catches common smishing language.',
                        'weaknesses'  => 'Keyword-based — no deep semantic understanding; can miss novel phrasings.',
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
                        'name'        => 'Contact Graph Outlier Model',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Maps contact relationships into a graph (shared number prefixes / name similarity) and flags orphaned or low-connectivity contacts.',
                        'strengths'   => 'Identifies isolated or synthetic contacts and unusual relationship structures.',
                        'weaknesses'  => 'Graph heuristic — does not train a neural network; result quality depends on contact-list density.',
                    ],
                    [
                        'id'          => 'communication_spikes',
                        'name'        => 'Communication Spikes Detector',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Flags contacts with statistically abnormal spikes in communication frequency (Z > 3.0σ) compared to their historical average.',
                        'strengths'   => 'Discovers bursty or abnormal messaging and calling behaviors characteristic of active fraud, harassment, or command-and-control operations.',
                        'weaknesses'  => 'Requires historical SMS and call log records to establish baseline metrics.',
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
                        'name'        => 'App Manifest Anomaly Scanner (PCA)',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Uses PCA reconstruction error over package name, permission, and manifest-style features to find apps with abnormal configurations.',
                        'strengths'   => 'Detects over-privileged or suspiciously-named apps without a curated signature list.',
                        'weaknesses'  => 'PCA is a linear model — captures feature deviance, not true deep semantics.',
                    ],
                    [
                        'id'          => 'notification_hijack',
                        'name'        => 'Notification Interception Guard',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Flags 3rd-party apps running in the foreground during sensitive OTP/banking notification arrivals.',
                        'strengths'   => 'Identifies potential credential theft or OTP interception by active background services.',
                        'weaknesses'  => 'Rule-based timing correlation; requires process history telemetry.',
                    ],
                    [
                        'id'          => 'accessibility_abuse',
                        'name'        => 'Accessibility Abuse Detector',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Audits active accessibility services for unverified 3rd-party apps with high-risk UI scraping or overlay capabilities.',
                        'strengths'   => 'Identifies active screen-reader keyloggers and input simulation tools.',
                        'weaknesses'  => 'Requires accessibility service permission logs.',
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
                        'name'        => 'Suspicious File Metadata Scanner',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Flags files whose metadata (extension, path depth, location in Android data dirs, hidden names) suggests encrypted payloads, ransomware artefacts, or hidden executables.',
                        'strengths'   => 'Reliable indicator of packed payloads or disguised executables from stored metadata.',
                        'weaknesses'  => 'Metadata-only — does not read file bytes; byte-level entropy scanning requires raw file access.',
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
                        'name'        => 'Activity Sequence Predictor (MLP)',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Trains a small scikit-learn MLP on app-usage timestamps to model normal activity rhythms; flags windows where actual activity deviates from the prediction.',
                        'strengths'   => 'Captures non-linear usage rhythms without a heavy deep-learning stack.',
                        'weaknesses'  => 'MLP regression on timestamps — simpler than a recurrent sequence model.',
                    ],
                    [
                        'id'          => 'sleep_disturbance',
                        'name'        => 'Sleep Disturbance & Stealth Tracker',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Identifies suspicious device usage or stealth background app activity during nighttime hours (11 PM - 5:30 AM).',
                        'strengths'   => 'Identifies active screen indicators and background services operating when the user is expected to be asleep.',
                        'weaknesses'  => 'Prone to false alerts for users with erratic sleep patterns.',
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
                    [
                        'id'          => 'background_exfiltration',
                        'name'        => 'Background Data Exfiltration Profiler',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Flags apps executing statistically abnormal background network uploads (Z > 2.0σ) while screen is off.',
                        'strengths'   => 'Identifies silent data harvesting and background telemetry leaks.',
                        'weaknesses'  => 'Does not differentiate between system updates and malicious uploads.',
                    ],
                    [
                        'id'          => 'battery_drain',
                        'name'        => 'Battery Drain Outlier Model',
                        'default'     => false,
                        'compat'      => 'python',
                        'description' => 'Flags periods of abnormal battery depletion while screen is off and device is not charging, indicating hidden spyware/miners.',
                        'strengths'   => 'Discovers background malware, stealth trackers, or background mining loops.',
                        'weaknesses'  => 'Affected by degraded battery capacity or battery aging.',
                    ],
                ],
            ],
        ];
    }

    // =========================================================================
    // PHP-ML Detection Algorithms (Decoupled to App\\Libraries\\AnomalyDetectors)
    // =========================================================================

        public function runPhpDetection(array $selectedAlgs, int $userId = 0,
                                    int $jobId = 0): array
    {
        $results = [];

        $algCount = 0;
        foreach ($selectedAlgs as $algList) {
            $algCount += count((array)$algList);
        }
        $completed = 0;

        // Lazy-loaded database queries (caches results if multiple algorithms request same category)
        $smsData = null;
        $getSms = function() use (&$smsData, $userId) {
            if ($smsData === null) {
                $smsData = $this->fetchSms($userId);
            }
            return $smsData;
        };

        $contactsData = null;
        $getContacts = function() use (&$contactsData, $userId) {
            if ($contactsData === null) {
                $contactsData = $this->fetchContacts($userId);
            }
            return $contactsData;
        };

        $callsData = null;
        $getCalls = function() use (&$callsData, $userId) {
            if ($callsData === null) {
                $callsData = $this->fetchCallLogs($userId);
            }
            return $callsData;
        };

        $locData = null;
        $getLoc = function() use (&$locData, $userId) {
            if ($locData === null) {
                $locData = $this->fetchLocations($userId);
            }
            return $locData;
        };

        $appsData = null;
        $getApps = function() use (&$appsData, $userId) {
            if ($appsData === null) {
                $appsData = $this->fetchApps($userId);
            }
            return $appsData;
        };

        $filesData = null;
        $getFiles = function() use (&$filesData, $userId) {
            if ($filesData === null) {
                $filesData = $this->fetchFiles($userId);
            }
            return $filesData;
        };

        $actScreenData = null;
        $getActScreen = function() use (&$actScreenData, $userId) {
            if ($actScreenData === null) {
                $actScreenData = $this->fetchActivityScreenTime($userId);
            }
            return $actScreenData;
        };

        $actSwitchData = null;
        $getActSwitch = function() use (&$actSwitchData, $userId) {
            if ($actSwitchData === null) {
                $actSwitchData = $this->fetchActivitySwitchRate($userId);
            }
            return $actSwitchData;
        };

        $devCurrentData = null;
        $getDevCurrent = function() use (&$devCurrentData, $userId) {
            if ($devCurrentData === null) {
                $devCurrentData = $this->fetchDeviceInfo($userId, 'current');
            }
            return $devCurrentData;
        };

        $devPrevData = null;
        $getDevPrev = function() use (&$devPrevData, $userId) {
            if ($devPrevData === null) {
                $devPrevData = $this->fetchDeviceInfo($userId, 'previous');
            }
            return $devPrevData;
        };

        $netData = null;
        $getNet = function() use (&$netData, $userId) {
            if ($netData === null) {
                $netData = $this->fetchNetworkProfile($userId);
            }
            return $netData;
        };

        // Instantiate detector libraries
        $smsLib      = new \App\Libraries\AnomalyDetectors\SmsDetectors();
        $contactsLib = new \App\Libraries\AnomalyDetectors\ContactsDetectors();
        $callsLib    = new \App\Libraries\AnomalyDetectors\CallLogsDetectors();
        $locLib      = new \App\Libraries\AnomalyDetectors\LocationDetectors();
        $appsLib     = new \App\Libraries\AnomalyDetectors\AppDetectors();
        $filesLib    = new \App\Libraries\AnomalyDetectors\FileDetectors();
        $actLib      = new \App\Libraries\AnomalyDetectors\ActivityDetectors();
        $devLib      = new \App\Libraries\AnomalyDetectors\DeviceDetectors();

        $dispatch = [
            'sms_freq'      => function() use ($getSms, $smsLib) {
                $data = $getSms();
                return empty($data) ? $this->staticFallback('sms_freq') : $smsLib->detectSmsFrequencySpike($data);
            },
            'sms_time'      => function() use ($getSms, $smsLib) {
                $data = $getSms();
                return empty($data) ? $this->staticFallback('sms_time') : $smsLib->detectSmsTimePattern($data);
            },
            'sms_cluster'   => function() use ($getSms, $smsLib) {
                $data = $getSms();
                return empty($data) ? $this->staticFallback('sms_cluster') : $smsLib->detectSmsCluster($data);
            },
            'sms_bert'      => function() use ($getSms, $smsLib) {
                $data = $getSms();
                return empty($data) ? $this->staticFallback('sms_bert') : $smsLib->detectSmsBert($data);
            },
            'contacts_freq' => function() use ($getContacts, $contactsLib) {
                $data = $getContacts();
                return empty($data) ? $this->staticFallback('contacts_freq') : $contactsLib->detectContactsFrequency($data);
            },
            'contacts_dup'  => function() use ($getContacts, $contactsLib) {
                $data = $getContacts();
                return empty($data) ? $this->staticFallback('contacts_dup') : $contactsLib->detectContactsDuplicates($data);
            },
            'contacts_graph'=> function() use ($getContacts, $contactsLib) {
                $data = $getContacts();
                return empty($data) ? $this->staticFallback('contacts_graph') : $contactsLib->detectContactsGraph($data);
            },
            'calls_burst'   => function() use ($getCalls, $callsLib) {
                $data = $getCalls();
                return empty($data) ? $this->staticFallback('calls_burst') : $callsLib->detectCallsBurst($data);
            },
            'calls_night'   => function() use ($getCalls, $callsLib) {
                $data = $getCalls();
                return empty($data) ? $this->staticFallback('calls_night') : $callsLib->detectCallsNight($data);
            },
            'calls_isolation'=> function() use ($getCalls, $callsLib) {
                $data = $getCalls();
                return empty($data) ? $this->staticFallback('calls_isolation') : $callsLib->detectCallsIsolation($data);
            },
            'loc_geofence'  => function() use ($getLoc, $locLib) {
                $data = $getLoc();
                return empty($data) ? $this->staticFallback('loc_geofence') : $locLib->detectLocationGeofence($data);
            },
            'loc_speed'     => function() use ($getLoc, $locLib) {
                $data = $getLoc();
                return empty($data) ? $this->staticFallback('loc_speed') : $locLib->detectLocationSpeed($data);
            },
            'loc_dbscan'    => function() use ($getLoc, $locLib) {
                $data = $getLoc();
                return empty($data) ? $this->staticFallback('loc_dbscan') : $locLib->detectLocationDbscan($data);
            },
            'apps_rep'      => function() use ($getApps, $appsLib) {
                $data = $getApps();
                return empty($data) ? $this->staticFallback('apps_rep') : $appsLib->detectAppsReputation($data);
            },
            'apps_perm'     => function() use ($getApps, $appsLib) {
                $data = $getApps();
                return empty($data) ? $this->staticFallback('apps_perm') : $appsLib->detectAppsPermission($data);
            },
            'apps_autoencoder'=> function() use ($getApps, $appsLib) {
                $data = $getApps();
                return empty($data) ? $this->staticFallback('apps_autoencoder') : $appsLib->detectAppsAutoencoder($data);
            },
            'files_spike'   => function() use ($getFiles, $filesLib) {
                $data = $getFiles();
                return empty($data) ? $this->staticFallback('files_spike') : $filesLib->detectFilesSpike($data);
            },
            'files_entropy' => function() use ($getFiles, $filesLib) {
                $data = $getFiles();
                return empty($data) ? $this->staticFallback('files_entropy') : $filesLib->detectFilesEntropy($data);
            },
            'act_screen'    => function() use ($getActScreen, $actLib) {
                $data = $getActScreen();
                return empty($data) ? $this->staticFallback('act_screen') : $actLib->detectActivityScreenTime($data);
            },
            'act_switch'    => function() use ($getActSwitch, $actLib) {
                $data = $getActSwitch();
                return empty($data) ? $this->staticFallback('act_switch') : $actLib->detectActivitySwitchRate($data);
            },
            'act_lstm'      => function() use ($getActSwitch, $actLib) {
                $data = $getActSwitch();
                return empty($data) ? $this->staticFallback('act_lstm') : $actLib->detectActivityLstm($data);
            },
            'dev_hw'        => function() use ($getDevCurrent, $getDevPrev, $devLib) {
                $cur = $getDevCurrent();
                $prev = $getDevPrev();
                return (empty($cur) || empty($prev)) ? $this->staticFallback('dev_hw') : $devLib->detectDeviceHardwareChange($cur, $prev);
            },
            'dev_net'       => function() use ($getNet, $devLib) {
                $data = $getNet();
                return empty($data) ? $this->staticFallback('dev_net') : $devLib->detectDeviceNetworkProfile($data, []);
            },
            'dev_oneclass'  => function() use ($getDevCurrent, $devLib) {
                $data = $getDevCurrent();
                return empty($data) ? $this->staticFallback('dev_oneclass') : $devLib->detectDeviceOneclass($data);
            },
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
     * SettingsController stored in tbl_settings with class='ml':
     *   ml_python_host, ml_python_port, ml_python_endpoint
     *
     * @return array{host: string, port: int, endpoint: string, base_url: string}
     */
    public function getPythonSettings(): array
    {
        $host     = 'ml-eaves-droid';
        $port     = 9070;
        $endpoint = '/api/v1/analysis-jobs';
        $url      = '';
        $token    = 'default_secure_token_change_me_in_prod';

        if (getenv('PYTHON_BACKEND_HOST') !== false) {
            $host = getenv('PYTHON_BACKEND_HOST');
        }

        if (getenv('PYTHON_BACKEND_PORT') !== false) {
            $port = (int)getenv('PYTHON_BACKEND_PORT');
        }

        if (getenv('PYTHON_BACKEND_ENDPOINT') !== false) {
            $endpoint = getenv('PYTHON_BACKEND_ENDPOINT');
        }

        if (getenv('ML_INTERNAL_TOKEN') !== false) {
            $token = getenv('ML_INTERNAL_TOKEN');
        } elseif (getenv('PYTHON_INTERNAL_TOKEN') !== false) {
            $token = getenv('PYTHON_INTERNAL_TOKEN');
        }

        try {
            $rows = $this->db->table('settings')
                ->where('class', 'ml')
                ->whereIn('key', ['ml_python_host', 'ml_python_port', 'ml_python_endpoint', 'ml_python_url', 'ml_python_token'])
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                match ($row['key']) {
                    'ml_python_host'     => $host = $row['value'] ?: $host,
                    'ml_python_port'     => $port = (int)($row['value'] ?: $port),
                    'ml_python_endpoint' => $endpoint = $row['value'] ?: $endpoint,
                    'ml_python_url'      => $url = $row['value'] ?: '',
                    'ml_python_token'    => $token = $row['value'] ?: $token,
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
            'token'     => $token,
        ];
    }

    public function testPythonConnection(?string $testUrl = null, int $timeout = 6): array
    {
        $settings = $this->getPythonSettings();
        $baseUrl = $testUrl ? rtrim($testUrl, '/') : $settings['base_url'];
        $healthUrl = $baseUrl . '/api/v1/health';
        $token = $settings['token'] ?? 'default_secure_token_change_me_in_prod';

        $t0 = microtime(true);
        try {
            $client = service('curlrequest', [
                'timeout'         => $timeout,
                'connect_timeout' => min(3, $timeout),
                'http_errors'     => false,
                'headers'         => [
                    'X-Internal-Token' => $token,
                    'X-Request-ID'     => bin2hex(random_bytes(8)),
                    'Accept'           => 'application/json',
                ],
            ]);
            $response = $client->get($healthUrl);
            $latencyMs = round((microtime(true) - $t0) * 1000, 1);
            $statusCode = $response->getStatusCode();
            $body = $response->getBody() ? json_decode($response->getBody(), true) : [];

            if ($statusCode === 200) {
                return [
                    'success'                  => true,
                    'message'                  => 'Python backend is running.',
                    'tested_url'               => $baseUrl,
                    'latency_ms'               => $latencyMs,
                    'status'                   => $body['status'] ?? 'healthy',
                    'version'                  => $body['version'] ?? '2.5.0',
                    'models'                   => $body['models_loaded'] ?? $body['models'] ?? $body['algorithms'] ?? [],
                    'models_count'             => count($body['models_loaded'] ?? $body['models'] ?? []),
                    'modules'                  => $body['modules'] ?? [],
                    'database'                 => $body['database'] ?? '',
                    'database_latency_ms'      => $body['database_latency_ms'] ?? 0.0,
                    'database_tables_verified' => $body['database_tables_verified'] ?? 0,
                    'database_total_tables'    => $body['database_total_tables'] ?? 10,
                    'cuda'                     => $body['cuda_available'] ?? false,
                    'cuda_device'              => $body['cuda_device'] ?? '',
                    'memory'                   => $body['memory_mb'] ?? [],
                    'cpu_percent'              => $body['cpu_percent'] ?? 0.0,
                    'cache'                    => $body['cache_entries'] ?? 0,
                    'uptime'                   => $body['uptime_seconds'] ?? 0,
                    'settings'                 => $settings,
                ];
            }

            if ($statusCode === 401) {
                return [
                    'success'    => false,
                    'message'    => 'Authentication failed (HTTP 401). Internal security token mismatch.',
                    'tested_url' => $baseUrl,
                    'latency_ms' => $latencyMs,
                    'settings'   => $settings,
                ];
            }

            return [
                'success'    => false,
                'message'    => "Backend returned HTTP {$statusCode}.",
                'tested_url' => $baseUrl,
                'latency_ms' => $latencyMs,
                'settings'   => $settings,
            ];
        } catch (\Throwable $e) {
            $latencyMs = round((microtime(true) - $t0) * 1000, 1);
            return [
                'success'    => false,
                'message'    => 'Connection failed: ' . $e->getMessage(),
                'tested_url' => $baseUrl,
                'latency_ms' => $latencyMs,
                'settings'   => $settings,
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
            'endpoint'           => '/api/v1/analysis-jobs',
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
            'sms'         => ['table' => 'tbl_extracted_sms',          'owner' => 'owner_id'],
            'contacts'    => ['table' => 'tbl_extracted_contacts',     'owner' => 'owner_id'],
            'call_logs'   => ['table' => 'tbl_extracted_call_logs',         'owner' => 'owner_id'],
            'locations'   => ['table' => 'tbl_extracted_locations',     'owner' => 'owner_id'],
            'apps'        => ['table' => 'tbl_extracted_installed_apps',         'owner' => 'owner_id'],
            'files'       => ['table' => 'tbl_extracted_device_files', 'owner' => 'owner_id'],
            'activity'    => ['table' => 'tbl_system_app_usage',    'owner' => 'owner_id'],
            'device_info' => ['table' => 'tbl_device_profiles','owner' => 'device_id'],
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
                        'Accept'           => 'application/json',
                        'Content-Type'     => 'application/json',
                        'X-Internal-Token' => $settings['token'] ?? 'default_secure_token_change_me_in_prod',
                        'X-Request-ID'     => bin2hex(random_bytes(8)),
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
                        log_message('warning', '[ML Self-Healing] Python backend returned unexpected status: ' . ($body ?? '(empty)'));
                    }
                } else {
                    log_message('warning', sprintf(
                        '[ML Self-Healing] Python backend returned HTTP %d: %s. Initiating local failover.',
                        $statusCode,
                        $response->getBody() ?? '(empty)'
                    ));
                }
            } catch (\Throwable $e) {
                log_message('warning', '[ML Self-Healing] Python backend unreachable (' . $e->getMessage() . '). Initiating local failover.');
            }
        }

        // ── Fallback if Python returned nothing (Automated Self-Healing Failover) ──
        if ($hasPy && empty($pyResults)) {
            log_message('info', "[ML Self-Healing] Running automated PHP-ML fallback for job #{$jobId}");

            // Lazy-loaded database queries (caches results if multiple algorithms request same category)
            $smsData = null;
            $getSms = function() use (&$smsData, $userId) {
                if ($smsData === null) {
                    $smsData = $this->fetchSms($userId);
                }
                return $smsData;
            };

            $contactsData = null;
            $getContacts = function() use (&$contactsData, $userId) {
                if ($contactsData === null) {
                    $contactsData = $this->fetchContacts($userId);
                }
                return $contactsData;
            };

            $callsData = null;
            $getCalls = function() use (&$callsData, $userId) {
                if ($callsData === null) {
                    $callsData = $this->fetchCallLogs($userId);
                }
                return $callsData;
            };

            $appsData = null;
            $getApps = function() use (&$appsData, $userId) {
                if ($appsData === null) {
                    $appsData = $this->fetchApps($userId);
                }
                return $appsData;
            };

            $filesData = null;
            $getFiles = function() use (&$filesData, $userId) {
                if ($filesData === null) {
                    $filesData = $this->fetchFiles($userId);
                }
                return $filesData;
            };

            $actSwitchData = null;
            $getActSwitch = function() use (&$actSwitchData, $userId) {
                if ($actSwitchData === null) {
                    $actSwitchData = $this->fetchActivitySwitchRate($userId);
                }
                return $actSwitchData;
            };

            $devCurrentData = null;
            $getDevCurrent = function() use (&$devCurrentData, $userId) {
                if ($devCurrentData === null) {
                    $devCurrentData = $this->fetchDeviceInfo($userId, 'current');
                }
                return $devCurrentData;
            };

            // Lazy-loaded PHP dispatch table just for the Python-only algorithms
            $smsLib      = new \App\Libraries\AnomalyDetectors\SmsDetectors();
            $contactsLib = new \App\Libraries\AnomalyDetectors\ContactsDetectors();
            $callsLib    = new \App\Libraries\AnomalyDetectors\CallLogsDetectors();
            $appsLib     = new \App\Libraries\AnomalyDetectors\AppDetectors();
            $filesLib    = new \App\Libraries\AnomalyDetectors\FileDetectors();
            $actLib      = new \App\Libraries\AnomalyDetectors\ActivityDetectors();
            $devLib      = new \App\Libraries\AnomalyDetectors\DeviceDetectors();

            $pyPhpDispatch = [
                'sms_bert'        => function() use ($getSms, $smsLib) {
                    $data = $getSms();
                    return empty($data) ? $this->staticFallback('sms_bert') : $smsLib->detectSmsBert($data);
                },
                'contacts_graph'  => function() use ($getContacts, $contactsLib) {
                    $data = $getContacts();
                    return empty($data) ? $this->staticFallback('contacts_graph') : $contactsLib->detectContactsGraph($data);
                },
                'calls_isolation' => function() use ($getCalls, $callsLib) {
                    $data = $getCalls();
                    return empty($data) ? $this->staticFallback('calls_isolation') : $callsLib->detectCallsIsolation($data);
                },
                'apps_autoencoder'=> function() use ($getApps, $appsLib) {
                    $data = $getApps();
                    return empty($data) ? $this->staticFallback('apps_autoencoder') : $appsLib->detectAppsAutoencoder($data);
                },
                'files_entropy'   => function() use ($getFiles, $filesLib) {
                    $data = $getFiles();
                    return empty($data) ? $this->staticFallback('files_entropy') : $filesLib->detectFilesEntropy($data);
                },
                'act_lstm'        => function() use ($getActSwitch, $actLib) {
                    $data = $getActSwitch();
                    return empty($data) ? $this->staticFallback('act_lstm') : $actLib->detectActivityLstm($data);
                },
                'dev_oneclass'    => function() use ($getDevCurrent, $devLib) {
                    $data = $getDevCurrent();
                    return empty($data) ? $this->staticFallback('dev_oneclass') : $devLib->detectDeviceOneclass($data);
                },
            ];

            $pyAlgIds = [];
            foreach ($pyOnly as $category => $algList) {
                foreach ((array)$algList as $algId) {
                    $pyAlgIds[] = $algId;
                }
            }

            foreach ($pyAlgIds as $algId) {
                if (isset($pyPhpDispatch[$algId])) {
                    $found = $pyPhpDispatch[$algId]();
                    if (!empty($found)) {
                        foreach ($found as &$f) {
                            $f['algorithm_id'] = $algId;
                            $f['engine_note']  = ($f['engine_note'] ?? '') . ' (PHP-ML self-healing failover)';
                        }
                        unset($f);
                        $pyResults = array_merge($pyResults, $found);
                    }
                }
            }

            // Persist fallback results to database if a job row exists
            if ($jobId > 0 && !empty($pyResults)) {
                try {
                    foreach ($pyResults as $resItem) {
                        $this->db->table('ml_results')->insert([
                            'job_id'          => $jobId,
                            'user_id'         => $userId,
                            'algorithm'       => $resItem['algorithm_id'] ?? 'unknown',
                            'algorithm_id'    => $resItem['algorithm_id'] ?? 'unknown',
                            'category'        => $resItem['category'] ?? 'system',
                            'severity'        => $resItem['severity'] ?? 'Medium',
                            'anomaly'         => $resItem['anomaly'] ?? ($resItem['title'] ?? 'Anomaly detected'),
                            'score'           => (float)($resItem['score'] ?? 0.75),
                            'event_timestamp' => $resItem['event_timestamp'] ?? date('Y-m-d H:i:s'),
                            'details'         => json_encode($resItem['details'] ?? $resItem),
                            'engine'          => 'php_fallback',
                            'created_at'      => date('Y-m-d H:i:s'),
                        ]);
                    }

                    $this->db->table('ml_jobs')
                        ->where('id', $jobId)
                        ->update([
                            'status'     => 'completed',
                            'engine'     => 'php_fallback',
                            'notes'      => 'Automated PHP-ML self-healing failover engaged (Python container offline/timeout).',
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                } catch (\Throwable $e) {
                    log_message('error', 'Failover ml_results persistence error: ' . $e->getMessage());
                }

                $this->updateJobProgress($jobId, 3, 3, 'completed');
            }
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
            'locations'   => 'LocationController',
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
            return $this->db->table('tbl_extracted_sms')
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
            $rows = $this->db->table('tbl_extracted_contacts')
                ->select("display_name, phone_numbers, created_at AS last_modified")
                ->where('owner_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->limit(1000)
                ->get()->getResultArray();
            
            foreach ($rows as &$row) {
                $numbers = json_decode($row['phone_numbers'] ?? '', true);
                if (!empty($numbers) && is_array($numbers)) {
                    $first = $numbers[0];
                    if (is_array($first)) {
                        $row['phone_number'] = $first['number'] ?? $first['normalized_number'] ?? '';
                    } else {
                        $row['phone_number'] = $first;
                    }
                } else {
                    $row['phone_number'] = '';
                }
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
            return $this->db->table('tbl_extracted_call_logs')
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

    /** @return array LocationController rows for a user */
    protected function fetchLocations(int $userId): array
    {
        if ($userId <= 0) return [];
        try {
            return $this->db->table('tbl_extracted_locations')
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
            $rows = $this->db->table('tbl_extracted_installed_apps')
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
            return $this->db->table('tbl_extracted_device_files')
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
            return $this->db->table('tbl_system_app_usage')
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
            return $this->db->table('tbl_system_app_usage')
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
            $tokenChecksums = $this->db->table('tbl_user_api_tokens')
                ->select('device_checksum')
                ->where('owner_id', $userId)
                ->where('device_checksum !=', '')
                ->where('device_checksum IS NOT NULL')
                ->groupBy('device_checksum')
                ->get()->getResultArray();

            $uploadChecksums = $this->db->table('tbl_uploaded_files')
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

            $builder = $this->db->table('tbl_device_profiles')
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
            return $this->db->table('tbl_system_network_info')
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
                'category'   => 'LocationController',
                'icon'       => 'fas fa-map-marker-alt',
                'anomaly'    => 'Visit to unknown location (Industrial Zone, 38 km from home base)',
                'severity'   => 'Medium',
                'algorithm'  => 'Geo-Fence Violation Detector',
                'timestamp'  => '2026-07-13 01:45:00',
                'engine_note'=> 'HomeController zone radius: 12.4 km from centroid (-1.2921, 36.8219)',
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
                'category'   => 'LocationController',
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
            'loc_geofence'  => ['LocationController', 'fas fa-map-marker-alt', 'Visit to unknown location 38 km from home base', 'Medium', 'Geo-Fence Violation Detector', '2026-07-13 01:45:00', 'Demo data – no live location rows found'],
            'loc_speed'     => ['LocationController', 'fas fa-map-marker-alt', 'Travel speed 1,240 km/h detected between two consecutive points', 'High', 'Travel Speed Anomaly', '2026-07-11 08:00:00', 'Demo data – threshold: 900 km/h'],
            'apps_rep'      => ['Installed Apps', 'fas fa-th-large', 'App "com.util.sync.hidden" installed at 02:17 AM', 'High', 'Package Reputation Scanner', '2026-07-11 02:17:43', 'Demo data – matched pattern "hidden"'],
            'apps_perm'     => ['Installed Apps', 'fas fa-th-large', '"FlashLight Pro" requests 19 permissions: READ_SMS, RECORD_AUDIO…', 'Low', 'Permission Anomaly Detector', '2026-07-05 11:23:00', 'Demo data – Z-Score 2.8 σ'],
            'files_spike'   => ['Files', 'fas fa-folder-open', '1,240 files created in /sdcard/Android/data in under 2 minutes', 'Medium', 'File Creation Spike Detector', '2026-07-12 22:41:10', 'Demo data – 4× daily average'],
            'act_screen'    => ['Activity', 'fas fa-heartbeat', 'Screen-time 14.3 h on 2026-07-11 (baseline: 3.1 h ± 0.8 h)', 'Medium', 'Screen-Time Anomaly Detector', '2026-07-11 23:59:00', 'Demo data – Z-Score 3.7 σ'],
            'act_switch'    => ['Activity', 'fas fa-heartbeat', '87 app switches in one hour (2026-07-10 22:00) – possible scripted behaviour', 'Medium', 'App-Switch Rate Monitor', '2026-07-10 22:00:00', 'Demo data – threshold: 60 switches/hour'],
            'dev_hw'        => ['Device Info', 'fas fa-microchip', 'IMEI changed: previous 35XXXXXX → current 86XXXXXX', 'High', 'Hardware Change Detector', '2026-07-09 08:00:00', 'Demo data – IMEI field changed between uploads'],
            'dev_net'       => ['Device Info', 'fas fa-microchip', 'Connected to unknown Wi-Fi SSID "Guest_Open_5G" at 03:12 AM', 'Medium', 'Network Profile Monitor', '2026-07-08 03:12:00', 'Demo data – SSID not in known-safe list'],

            // ── Python-only algorithm fallbacks ──────────────────────────────
            'sms_bert'        => ['SMS', 'fas fa-sms', 'Phishing keyword heuristic flagged a message with 5 social-engineering indicators', 'High', 'SMS Phishing Keyword Heuristic', '2026-07-12 03:12:00', 'Python demo – keyword hits (threshold: 2)'],
            'contacts_graph'  => ['Contacts', 'fas fa-address-book', 'Graph model found 4 orphaned contacts with no relational edges', 'Medium', 'Contact Graph Outlier Model', '2026-07-10 14:55:22', 'Python demo – graph degree anomaly: 2.3 σ'],
            'calls_isolation' => ['Call Log', 'fas fa-phone', 'Isolation Forest flagged an anomalous short incoming call at 03:47 AM from international number', 'High', 'Isolation Forest Outlier Detection', '2026-07-13 02:30:00', 'Python demo – iForest contamination: 0.05, score: -0.32'],
            'apps_autoencoder' => ['Installed Apps', 'fas fa-th-large', 'PCA anomaly scanner detected app "com.security.fake" with abnormal manifest features', 'High', 'App Manifest Anomaly Scanner (PCA)', '2026-07-11 02:17:43', 'Python demo – PCA reconstruction error: 4.2 σ above mean'],
            'files_entropy'   => ['Files', 'fas fa-folder-open', 'Suspicious file metadata (.enc) found in /sdcard/Download – possible encrypted payload', 'Medium', 'Suspicious File Metadata Scanner', '2026-07-12 22:41:10', 'Python demo – high-risk extension in Android data dir'],
            'act_lstm'        => ['Activity', 'fas fa-heartbeat', 'MLP prediction error spike at 22:00 – 87 app switches deviated from learned activity rhythm', 'Medium', 'Activity Sequence Predictor (MLP)', '2026-07-10 22:00:00', 'Python demo – prediction error: 3.1 σ above baseline'],
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

    /**
     * Fetches all anomaly findings from the user's latest completed ml_job
     * for a specific algorithm ID. Returns an empty array if none.
     */
    public function getLatestJobResultsByAlgorithm(int $userId, string $algId): array
    {
        if ($userId <= 0 || empty($algId)) {
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
                ->where('algorithm_id', $algId)
                ->get()->getResultArray();

            return $rows ?: [];
        } catch (\Throwable $e) {
            log_message('error', 'getLatestJobResultsByAlgorithm failed: ' . $e->getMessage());
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
            'default_engine'    => 'php',
            'allowed_algorithms' => null, // null = all allowed
        ];

        try {
            $rows = $this->db->table('settings')
                ->where('class', 'anomaly')
                ->get()->getResultArray();

            foreach ($rows as $r) {
                if ($r['key'] === 'default_engine') {
                    $result['default_engine'] = $r['value'] ?: 'php';
                }
                if ($r['key'] === 'allowed_algorithms' && $r['value']) {
                    $decoded = json_decode($r['value'], true);
                    $result['allowed_algorithms'] = is_array($decoded) && !empty($decoded)
                        ? $decoded
                        : null;
                }
            }
        } catch (\Throwable $e) {
            // SettingsController table may not exist; return defaults
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
            // Core (Free) — simple threshold / statistical
            'sms_freq'      => 'core',
            'sms_time'      => 'core',
            'contacts_freq' => 'core',
            'calls_burst'   => 'core',
            'calls_night'   => 'core',
            'apps_rep'      => 'core',
            'files_spike'   => 'core',
            'dev_hw'        => 'core',

            // AdvancedController (Gold) — PHP-ML / moderate complexity
            'sms_cluster'   => 'advanced',
            'contacts_dup'  => 'advanced',
            'loc_geofence'  => 'advanced',
            'loc_speed'     => 'advanced',
            'loc_dbscan'    => 'advanced',
            'apps_perm'     => 'advanced',
            'files_ext'     => 'advanced',
            'act_screen'    => 'advanced',
            'act_switch'    => 'advanced',
            'dev_net'       => 'advanced',

            // Deep (Platinum) — full Python ML models
            'sms_bert'         => 'deep',
            'contacts_graph'   => 'deep',
            'calls_isolation'  => 'deep',
            'apps_autoencoder' => 'deep',
            'files_entropy'    => 'deep',
            'act_lstm'         => 'deep',
            'dev_oneclass'     => 'deep',
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
                'analysis_counts' => $this->getAnalysisCounts($userId),
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

    // -------------------------------------------------------------------------
    // Job helpers for single-page results flow
    // -------------------------------------------------------------------------

    /**
     * Returns the most recent pending or running job for a user, or null.
     *
     * @param  int        $userId
     * @return array|null ml_jobs row, or null if no active job
     */
    public function getRunningJobForUser(int $userId): ?array
    {
        try {
            $row = $this->db->table('ml_jobs')
                ->where('user_id', $userId)
                ->whereIn('status', ['pending', 'running'])
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()->getRowArray();
            return $row ?: null;
        } catch (\Throwable $e) {
            log_message('error', 'getRunningJobForUser: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Returns the N most recent jobs (any status) for a user, newest first.
     *
     * @param  int   $userId
     * @param  int   $limit   Max rows to return (default 6)
     * @return array          Array of ml_jobs rows
     */
    public function getRecentJobsForUser(int $userId, int $limit = 6): array
    {
        try {
            return $this->db->table('ml_jobs')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'getRecentJobsForUser: ' . $e->getMessage());
            return [];
        }
    }
}
