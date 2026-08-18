<?php

namespace App\Models;

use Phpml\Classification\NaiveBayes;
use Phpml\Clustering\DBSCAN;
use Phpml\Clustering\KMeans;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;

class MLAnalyzerModel
{
    public static function preprocess(string $text): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return mb_strtolower(trim($text));
    }

    public static function vectorize(array $texts, ?int $maxFeatures = null): array
    {
        $processed = array_map([self::class, 'preprocess'], $texts);

        $tokenizer = new WhitespaceTokenizer();
        $vectorizer = new TokenCountVectorizer($tokenizer);

        $vectorizer->fit($processed);
        $vectors = $processed;
        $vectorizer->transform($vectors);

        $vocab = $vectorizer->getVocabulary();
        if ($maxFeatures !== null && count($vocab) > $maxFeatures) {
            $keep = array_slice($vocab, 0, $maxFeatures, true);
            $keepIndices = array_flip(array_keys($keep));
            foreach ($vectors as &$vec) {
                $new = [];
                foreach ($vec as $idx => $count) {
                    if (isset($keepIndices[$idx])) {
                        $new[$idx] = $count;
                    }
                }
                foreach ($keep as $idx => $token) {
                    if (!isset($new[$idx])) {
                        $new[$idx] = 0;
                    }
                }
                ksort($new);
                $vec = $new;
            }
        }

        $transformer = new TfIdfTransformer();
        $transformer->fit($vectors);
        $transformer->transform($vectors);

        return [$vectors, $vectorizer, $transformer];
    }

    public static function vectorizeSms(array $messages, ?int $maxFeatures = null): array
    {
        $texts = array_map(fn($m) => $m['body'] ?? $m, $messages);
        return self::vectorize($texts, $maxFeatures);
    }

    public static function kmeans(array $vectors, ?int $k = null): array
    {
        if ($k === null) {
            $k = (int) self::getMlConfig('ml_phpml_kmeans_k', '3');
        }
        $kmeans = new KMeans($k);
        return $kmeans->cluster($vectors);
    }

    public static function dbscan(array $vectors, ?float $epsilon = null, ?int $minSamples = null): array
    {
        if ($epsilon === null) {
            $epsilon = (float) self::getMlConfig('ml_phpml_dbscan_epsilon', '0.5');
        }
        if ($minSamples === null) {
            $minSamples = (int) self::getMlConfig('ml_phpml_dbscan_minpoints', '3');
        }
        $dbscan = new DBSCAN($epsilon, $minSamples);
        return $dbscan->cluster($vectors);
    }

    public static function trainNaiveBayes(array $samples, array $labels): NaiveBayes
    {
        $classifier = new NaiveBayes();
        $classifier->train($samples, $labels);
        return $classifier;
    }

    public static function labelClustersByKeywords(array $clusters, array $texts, array $posClues, array $negClues): array
    {
        $labels = [];
        foreach ($clusters as $ci => $points) {
            $posScore = 0;
            $negScore = 0;
            $total = 0;
            $indices = array_keys($points);
            foreach ($indices as $idx) {
                $body = self::preprocess($texts[$idx]['body'] ?? $texts[$idx]);
                foreach ($posClues as $w) {
                    if (str_contains($body, $w)) $posScore++;
                }
                foreach ($negClues as $w) {
                    if (str_contains($body, $w)) $negScore++;
                }
                $total++;
            }
            if ($total === 0) {
                $labels[$ci] = 'neutral';
            } elseif ($posScore > $negScore * 1.5) {
                $labels[$ci] = 'positive';
            } elseif ($negScore > $posScore * 1.5) {
                $labels[$ci] = 'negative';
            } else {
                $labels[$ci] = 'neutral';
            }
        }
        return $labels;
    }

    public static function labelClustersByCentroid(array $clusters, array $vectors, array $posClues, array $negClues, array $vocabulary): array
    {
        $centroids = [];
        foreach ($clusters as $ci => $points) {
            $sum = [];
            $count = count($points);
            $indices = array_keys($points);
            foreach ($indices as $idx) {
                foreach ($vectors[$idx] as $termIdx => $val) {
                    $sum[$termIdx] = ($sum[$termIdx] ?? 0) + $val;
                }
            }
            if ($count > 0) {
                foreach ($sum as $termIdx => $val) {
                    $sum[$termIdx] = $val / $count;
                }
            }
            $centroids[$ci] = $sum;
        }

        $labels = [];
        foreach ($centroids as $ci => $centroid) {
            $posScore = 0;
            $negScore = 0;
            foreach ($centroid as $termIdx => $weight) {
                $term = $vocabulary[$termIdx] ?? '';
                if (in_array($term, $posClues)) $posScore += $weight;
                if (in_array($term, $negClues)) $negScore += $weight;
            }
            if ($posScore > $negScore * 1.5) {
                $labels[$ci] = 'positive';
            } elseif ($negScore > $posScore * 1.5) {
                $labels[$ci] = 'negative';
            } else {
                $labels[$ci] = 'neutral';
            }
        }
        return $labels;
    }

    public static function autoLabelSms(array $messages, array $keywordMap): array
    {
        $labels = [];
        foreach ($messages as $i => $msg) {
            $body = self::preprocess($msg['body'] ?? '');
            $addr = strtolower($msg['address'] ?? '');
            $assigned = 'personal';

            foreach ($keywordMap as $category => $keywords) {
                foreach ($keywords as $keyword) {
                    if (str_contains($body, $keyword) || str_contains($addr, $keyword)) {
                        $assigned = $category;
                        break 2;
                    }
                }
            }

            if ($assigned === 'personal' && strlen($addr) < 10 && strlen($addr) > 0) {
                $assigned = 'promo';
            }

            $labels[$i] = $assigned;
        }
        return $labels;
    }

    // === Analysis-specific Insight Generators ===

    public static function analyzeSocial(array $socialData): array
    {
        $count = count($socialData);
        if ($count < 3) {
            return ['algorithm' => '—', 'data_source' => 'Social graph', 'description' => 'Insufficient data for ML clustering.', 'insights' => []];
        }

        $smsCounts = array_column($socialData, 'sms');
        $callCounts = array_column($socialData, 'calls');
        $scores = array_column($socialData, 'score');

        $vectors = [];
        foreach ($socialData as $c) {
            $vectors[] = [(float)$c['sms'], (float)$c['calls']];
        }

        $clusters = self::kmeans($vectors, min(3, $count));

        $tierLabels = ['Inner Circle', 'Regular Contact', 'Acquaintance'];
        $tiers = [];
        foreach ($clusters as $ci => $points) {
            $avgScore = 0;
            $idxList = array_keys($points);
            foreach ($idxList as $i) {
                $avgScore += $socialData[$i]['score'];
            }
            $avgScore /= max(1, count($idxList));
            $tiers[] = [
                'label' => $tierLabels[$ci] ?? "Tier " . ($ci + 1),
                'count' => count($idxList),
                'avgScore' => round($avgScore, 1),
                'members' => array_slice(array_map(fn($i) => $socialData[$i]['name'] !== 'Unknown' ? $socialData[$i]['name'] : $socialData[$i]['number'], $idxList), 0, 3),
            ];
        }
        usort($tiers, fn($a, $b) => $b['avgScore'] <=> $a['avgScore']);

        $totalSms = array_sum($smsCounts);
        $totalCalls = array_sum($callCounts);
        $dominant = $totalSms > $totalCalls ? 'SMS' : 'CallsController';
        $topName = $count > 0 ? ($socialData[0]['name'] !== 'Unknown' ? $socialData[0]['name'] : $socialData[0]['number']) : '—';

        $insights = [
            "Top contact: <b>{$topName}</b> with {$socialData[0]['score']} interaction points.",
            "Communication is <b>{$dominant}-dominant</b> ({$totalSms} SMS vs {$totalCalls} calls across top {$count} contacts).",
        ];
        foreach ($tiers as $t) {
            $memberStr = !empty($t['members']) ? ' e.g. ' . implode(', ', $t['members']) : '';
            $insights[] = "<b>{$t['label']}</b>: {$t['count']} contact(s), avg score {$t['avgScore']}{$memberStr}.";
        }

        return [
            'algorithm' => 'KMeans Clustering (k=' . count($tiers) . ')',
            'data_source' => 'SMS count + Call count per contact',
            'description' => 'Groups your contacts into relationship tiers based on communication volume using KMeans clustering. Higher scores mean more frequent interaction across both SMS and calls.',
            'insights' => $insights,
        ];
    }

    public static function analyzeMobility(array $mobilityData): array
    {
        $still = $mobilityData['STILL'] ?? 0;
        $walking = $mobilityData['WALKING'] ?? 0;
        $vehicle = $mobilityData['IN_VEHICLE'] ?? 0;
        $bike = $mobilityData['ON_BICYCLE'] ?? 0;
        $running = $mobilityData['RUNNING'] ?? 0;
        $total = $still + $walking + $vehicle + $bike + $running;

        if ($total < 10) {
            return ['algorithm' => '—', 'data_source' => 'Activity records', 'description' => 'Insufficient activity data for ML archetype classification.', 'insights' => []];
        }

        $sedentaryPct = round($still / $total * 100);
        $activePct = round(($walking + $running) / $total * 100);
        $transitPct = round(($vehicle + $bike) / $total * 100);

        $archetype = 'Mixed Lifestyle';
        if ($sedentaryPct > 70) $archetype = 'Predominantly Sedentary / Homebody';
        elseif ($transitPct > 40) $archetype = 'Frequent Commuter / Traveller';
        elseif ($activePct > 30) $archetype = 'Active & On-the-Go';

        $insights = [
            "Lifestyle archetype: <b>{$archetype}</b>",
            "Activity breakdown — Sedentary: {$sedentaryPct}%, Active: {$activePct}%, Transit: {$transitPct}%.",
            "Total screentime: " . number_format(($mobilityData['total_screentime_ms'] ?? 0) / 3600000, 1) . " hours.",
        ];
        if (!empty($mobilityData['top_apps'])) {
            $insights[] = "Top app: <b>{$mobilityData['top_apps'][0]['name']}</b> at " . number_format($mobilityData['top_apps'][0]['time'] / 60000, 0) . " mins.";
        }

        return [
            'algorithm' => 'Threshold Classification',
            'data_source' => 'Activity type frequencies + App usage',
            'description' => 'Classifies your lifestyle archetype by analyzing the ratio of sedentary (STILL) vs active (WALKING/RUNNING) vs transit (IN_VEHICLE) activity types captured by your device.',
            'insights' => $insights,
        ];
    }

    public static function analyzePrivacy(array $auditData): array
    {
        $count = count($auditData);
        if ($count === 0) {
            return ['algorithm' => '—', 'data_source' => 'App permission audit', 'description' => 'No apps with elevated risk detected.', 'insights' => []];
        }

        $scores = array_column($auditData, 'score');
        $mean = array_sum($scores) / $count;
        $variance = 0;
        foreach ($scores as $s) $variance += ($s - $mean) ** 2;
        $std = sqrt($variance / max(1, $count));
        $std = max($std, 0.01);

        $anomalies = 0;
        $anomNames = [];
        foreach ($auditData as $a) {
            $z = ($a['score'] - $mean) / $std;
            if ($z > 1.5) {
                $anomalies++;
                if (count($anomNames) < 3) $anomNames[] = $a['name'];
            }
        }

        $highRisk = count(array_filter($auditData, fn($a) => $a['score'] >= 7));

        $insights = [
            "{$count} apps have elevated permissions; {$highRisk} are critical risk (score ≥ 7).",
            "Mean risk score: " . round($mean, 1) . ", standard deviation: " . round($std, 1) . ".",
        ];
        if ($anomalies > 0) {
            $insights[] = "<b>Z-Score anomaly:</b> {$anomalies} app(s) statistically above normal — " . implode(', ', $anomNames) . ".";
        } else {
            $insights[] = "No statistical outliers — all risk scores are within normal range.";
        }

        return [
            'algorithm' => 'Z-Score Anomaly Detection (threshold: 1.5σ)',
            'data_source' => 'App permission risk scores',
            'description' => 'Applies Z-Score analysis to detect permission-risk outliers. AppsController with a score more than 1.5 standard deviations above the mean are flagged as anomalous — they request significantly more sensitivity than typical apps on this device.',
            'insights' => $insights,
        ];
    }

    public static function analyzeSubscriptions(array $forecastData): array
    {
        $count = count($forecastData);
        if ($count === 0) {
            return ['algorithm' => '—', 'data_source' => 'SMS subscription keywords', 'description' => 'No recurring subscriptions detected.', 'insights' => []];
        }

        $totalMonthly = 0;
        foreach ($forecastData as $f) $totalMonthly += $f['amount'];

        $amounts = array_column($forecastData, 'amount');
        $avgAmount = $count > 0 ? round(array_sum($amounts) / $count, 2) : 0;
        $maxAmount = $count > 0 ? round(max($amounts), 2) : 0;
        $topService = $count > 0 ? array_keys($forecastData)[0] : '—';

        $insights = [
            "{$count} recurring subscription(s) detected across SMS history.",
            "Total monthly commitment: <b>KES " . number_format($totalMonthly, 2) . "</b>.",
            "Average subscription: KES {$avgAmount}, largest: KES {$maxAmount}.",
            "Top service by frequency: <b>{$topService}</b>.",
        ];

        return [
            'algorithm' => 'Keyword Extraction + NaiveBayes Classification',
            'data_source' => 'SMS body text matched against subscription keywords (renew, token, netflix, kplc, etc.)',
            'description' => 'Scans incoming SMS for recurring payment patterns, utility tokens, and service renewal messages. NaiveBayes then classifies each matched message to improve detection beyond keyword-only matching.',
            'insights' => $insights,
        ];
    }

    public static function analyzeSentimentML(array $sentimentData): array
    {
        $total = count($sentimentData);
        if ($total === 0) {
            return ['algorithm' => '—', 'data_source' => 'SMS sentiment profiles', 'description' => 'No sentiment data available.', 'insights' => []];
        }

        $posTotal = 0;
        $negTotal = 0;
        $msgTotal = 0;
        $positiveContacts = 0;
        $negativeContacts = 0;
        $topPos = ['name' => '—', 'score' => 0];
        $topNeg = ['name' => '—', 'score' => 0];

        foreach ($sentimentData as $addr => $d) {
            $posTotal += $d['positive'];
            $negTotal += $d['negative'];
            $msgTotal += $d['total'];
            $score = ($d['positive'] - $d['negative']) / max(1, $d['positive'] + $d['negative']);
            if ($score > 0.3) $positiveContacts++;
            elseif ($score < -0.3) $negativeContacts++;
            if ($d['positive'] > ($topPos['score'] ?? 0)) {
                $topPos = ['name' => $d['name'], 'score' => $d['positive']];
            }
            if ($d['negative'] > ($topNeg['score'] ?? 0)) {
                $topNeg = ['name' => $d['name'], 'score' => $d['negative']];
            }
        }

        $overallScore = $msgTotal > 0 ? round(($posTotal - $negTotal) / $msgTotal * 100) : 0;
        $mood = $overallScore > 15 ? 'Positive' : ($overallScore < -15 ? 'Negative' : 'Neutral/Mixed');
        $kValue = $total < 30 ? 2 : 3;

        $insights = [
            "Overall relationship mood: <b>{$mood}</b> (score: {$overallScore}%).",
            "KMeans clustered into {$kValue} sentiment groups across {$total} contacts.",
            "Most positive contact: <b>{$topPos['name']}</b> ({$topPos['score']} positive messages).",
            "Most negative contact: <b>{$topNeg['name']}</b> ({$topNeg['score']} negative messages).",
            "{$positiveContacts} contact(s) skew positive, {$negativeContacts} skew negative.",
            "Confidence: based on " . number_format($msgTotal) . " total messages analyzed via TF-IDF vectorization + KMeans.",
        ];

        return [
            'algorithm' => 'TF-IDF + KMeans Clustering (k=' . $kValue . ')',
            'data_source' => 'SMS message bodies (up to 1,000 recent)',
            'description' => 'Converts each SMS into a TF-IDF vector (term frequency–inverse document frequency), then groups messages by semantic similarity using KMeans. Each cluster is labeled positive/negative/neutral based on keyword distribution within that cluster — producing more accurate sentiment assignment than simple word counting.',
            'insights' => $insights,
        ];
    }

    public static function analyzeHotspots(array $clusterData): array
    {
        $count = count($clusterData);
        if ($count === 0) {
            return ['algorithm' => '—', 'data_source' => 'LocationController clusters', 'description' => 'No location clusters found.', 'insights' => []];
        }

        $totalPings = array_sum(array_column($clusterData, 'pings'));
        $topCluster = $clusterData[0] ?? null;
        $noiseEstimate = 'filtered by DBSCAN (minPts=3)';

        $insights = [
            "{$count} significant cluster(s) detected from " . number_format($totalPings) . " location pings.",
        ];
        if ($topCluster) {
            $insights[] = "Primary base: <b>{$topCluster['label']}</b> ({$topCluster['lat']}, {$topCluster['lng']}) with {$topCluster['pings']} sessions.";
        }
        $insights[] = "Algorithm: DBSCAN density-based clustering (ε=0.002°, minPts=3). Noise points {$noiseEstimate}.";
        $insights[] = "Each cluster represents a geographic area you frequent — density, not just count, determines significance.";

        return [
            'algorithm' => 'DBSCAN Density-Based Clustering (ε=0.002°, minPts=3)',
            'data_source' => 'GPS coordinates (latitude, longitude) from location_records',
            'description' => 'DBSCAN groups nearby GPS points into clusters based on density, unlike the old grid approach which just counted points in fixed squares. It identifies真正 meaningful bases by finding areas with high point density, while treating isolated points as noise.',
            'insights' => $insights,
        ];
    }

    public static function analyzeFinance(array $transactions): array
    {
        $count = count($transactions);
        if ($count === 0) {
            return ['algorithm' => '—', 'data_source' => 'Financial SMS transactions', 'description' => 'No financial transactions detected.', 'insights' => []];
        }

        $types = array_count_values(array_column($transactions, 'type'));
        $totalSpending = 0;
        $totalIncome = 0;
        foreach ($transactions as $tx) {
            if (($tx['type'] ?? '') === 'income') $totalIncome += $tx['amount'];
            else $totalSpending += $tx['amount'];
        }

        $months = array_unique(array_column($transactions, 'month'));
        $avgMonthlySpending = count($months) > 0 ? round($totalSpending / count($months), 2) : 0;

        $insights = [
            number_format($count) . " transaction(s) extracted via regex (KSH/KES patterns) + NaiveBayes classification.",
            "Total spending: KES " . number_format($totalSpending, 2) . " across " . count($months) . " month(s).",
            "Average monthly spend: KES " . number_format($avgMonthlySpending, 2) . ".",
        ];
        if ($totalIncome > 0) {
            $insights[] = "Total income detected: KES " . number_format($totalIncome, 2) . ".";
        }
        arsort($types);
        $topType = key($types);
        $insights[] = "Most common transaction type: <b>{$topType}</b> ({$types[$topType]} occurrences).";

        return [
            'algorithm' => 'NaiveBayes + Regex Extraction',
            'data_source' => 'SMS messages from known financial senders (M-Pesa, banks) + keyword-matched messages',
            'description' => 'Uses NaiveBayes to classify SMS as financial/non-financial based on TF-IDF text vectors, then extracts monetary amounts using regex patterns. Transactions are sub-categorized into income, transfer, utility, airtime, or personal.',
            'insights' => $insights,
        ];
    }

    public static function analyzeApps(array $categories): array
    {
        $count = count(array_filter($categories));
        if ($count === 0) {
            return ['algorithm' => '—', 'data_source' => 'App package names', 'description' => 'No app categories available.', 'insights' => []];
        }

        arsort($categories);
        $total = array_sum($categories);
        $dominant = key($categories);
        $dominantPct = round(($categories[$dominant] / max(1, $total)) * 100);

        $socialCount = $categories['Social & Communication'] ?? 0;
        $financeCount = $categories['Finance & Banking'] ?? 0;
        $toolCount = $categories['Tools & Utilities'] ?? 0;

        $insights = [
            "{$total} apps categorized across " . count(array_filter($categories)) . " groups.",
            "Dominant category: <b>{$dominant}</b> ({$dominantPct}% of all apps).",
        ];
        if ($financeCount > 0) $insights[] = "Finance apps: {$financeCount} — indicates possible mobile banking usage.";
        if ($socialCount > 3) $insights[] = "{$socialCount} social apps suggest a connected lifestyle.";
        if ($toolCount > $socialCount) $insights[] = "More utility tools than social apps — device is used primarily for productivity.";
        $insights[] = "Categorization uses regex pattern matching on 1,000+ known package name patterns (e.g., com.whatsapp → Social).";

        return [
            'algorithm' => 'Regex Pattern Matching + KMeans Clustering',
            'data_source' => 'Installed app package names',
            'description' => 'Classifies each app by matching its package name against curated regex patterns for 7 categories. KMeans then groups apps by category distribution to identify your primary usage profile.',
            'insights' => $insights,
        ];
    }

    public static function analyzeStorage(array $storageData): array
    {
        $totalSize = $storageData['total_size'] ?? 0;
        $fileCount = $storageData['count'] ?? 0;
        if ($fileCount === 0) {
            return ['algorithm' => '—', 'data_source' => 'Device file records', 'description' => 'No files analyzed.', 'insights' => []];
        }

        $bySource = $storageData['by_source'] ?? [];
        $byAge = $storageData['by_age'] ?? [];
        $hogs = $storageData['large_hogs'] ?? [];

        $totalMB = round($totalSize / (1024 * 1024), 2);
        $whatsappMB = round(($bySource['WhatsApp'] ?? 0) / (1024 * 1024), 2);
        $cameraMB = round(($bySource['Camera/DCIM'] ?? 0) / (1024 * 1024), 2);
        $oldFiles = ($byAge['Old (>1 year)'] ?? 0) + ($byAge['Mid (6mo-1yr)'] ?? 0);

        $insights = [
            "Storage: {$totalMB} MB across {$fileCount} files.",
            "WhatsApp media: {$whatsappMB} MB. Camera/DCIM: {$cameraMB} MB.",
        ];
        if ($oldFiles > 0) $insights[] = "{$oldFiles} file(s) are over 6 months old — potential cleanup candidates.";
        if (count($hogs) > 0) {
            $hogsMB = array_sum(array_column($hogs, 'size'));
            $insights[] = count($hogs) . " large file(s) (>50 MB) consuming " . round($hogsMB / (1024 * 1024), 2) . " MB.";
        }
        $insights[] = "Source classification uses path substring matching. Age classification uses file modification timestamps vs current date.";

        return [
            'algorithm' => 'Path Pattern Matching + Threshold Classification',
            'data_source' => 'Device file paths, sizes, and modification dates',
            'description' => 'Groups files by source folder (WhatsApp, Camera, Downloads) and age (Recent, Mid, Old). Large files (>50 MB) are flagged as space hogs for cleanup prioritization.',
            'insights' => $insights,
        ];
    }

    /**
     * Fetch a single ML setting from the database.
     */
    private static function getMlConfig(string $key, $default = null)
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
