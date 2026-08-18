<?php

namespace App\Libraries\AnomalyDetectors;

class SmsDetectors
{
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
            return [];
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
            return [];
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
            return [];
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
     * SMS – Phishing Keyword Heuristic
     *
     * Scans SMS message bodies for phishing keywords.
     */
    public function detectSmsBert(array $smsRows): array
    {
        if (empty($smsRows)) {
            return [];
        }

        $findings = [];
        $phishingKeywords = [
            'win', 'claim', 'prize', 'gift', 'award', 'verify', 'update', 'login', 'lock', 
            'suspended', 'compromised', 'bank', 'secure', 'account', 'limit', 'password', 
            'credential', 'click', 'link', 'alert', 'urgent', 'immediat', 'paypal', 'irs', 
            'tax', 'refund', 'parcel', 'package', 'delivery', 'shipment'
        ];

        foreach ($smsRows as $row) {
            $body = strtolower($row['body'] ?? '');
            $matched = [];
            foreach ($phishingKeywords as $kw) {
                if (strpos($body, $kw) !== false) {
                    $matched[] = $kw;
                }
            }

            if (count($matched) >= 2) {
                $findings[] = [
                    'category'  => 'SMS',
                    'icon'      => 'fas fa-sms',
                    'anomaly'   => 'Phishing indicators detected in message from ' . esc($row['address'] ?? 'Unknown') . ': "' . esc(substr($row['body'], 0, 80)) . '..."',
                    'severity'  => 'High',
                    'algorithm' => 'SMS Phishing Keyword Heuristic',
                    'timestamp' => $row['date'] ?? date('Y-m-d H:i:s'),
                    'engine_note'=> 'PHP heuristic - matched phishing keywords: [' . implode(', ', $matched) . ']',
                ];
            }
        }

        return array_slice($findings, 0, 5);
    }

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

    protected function getMlSetting(string $key, $default = null)
    {
        $db = \Config\Database::connect();
        $row = $db->table('settings')
            ->where('class', 'ml')
            ->where('key', $key)
            ->get()
            ->getRow();
        return $row ? $row->value : $default;
    }
}
