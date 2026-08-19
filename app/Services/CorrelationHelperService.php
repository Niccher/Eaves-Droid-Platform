<?php

namespace App\Services;

use App\Models\ExtractModel;
use App\Models\FinderModel;
use App\Models\CryptModel;
use App\Models\MLAnalyzerModel;

/**
 * CorrelationHelperService — Helper class to offload data aggregations,
 * HTML list templating, and heavy relationship views from CorrelationController.
 */
class CorrelationHelperService
{
    protected FinderModel $finderModel;
    protected CryptModel $cryptModel;
    protected ExtractModel $extractModel;

    public function __construct()
    {
        $this->finderModel = new FinderModel();
        $this->cryptModel = new CryptModel();
        $this->extractModel = new ExtractModel();
    }

    /**
     * Get index data mapping categorized SMS and calls.
     */
    public function getIndexData(int $userId): array
    {
        $smsAnalysis = $this->finderModel->get_categorized_sms_counts($userId);
        $callAnalysis = $this->finderModel->get_categorized_call_counts($userId);

        return [
            'sms_analysis' => $smsAnalysis,
            'call_analysis' => $callAnalysis,
            'totalAnalyzedSMS' => $smsAnalysis['total'] ?? 0,
            'financialAlerts' => $smsAnalysis['financial'] ?? 0,
            'suspiciousCalls' => $callAnalysis['spam'] ?? 0,
            'newContacts' => $callAnalysis['new'] ?? 0,
            'financialSMS' => $smsAnalysis['financial'] ?? 0,
            'promotionalSMS' => $smsAnalysis['promo'] ?? 0,
            'maliciousSMS' => $smsAnalysis['malicious'] ?? 0,
            'otpSMS' => $smsAnalysis['otp'] ?? 0,
            'utilitySMS' => $smsAnalysis['utility'] ?? 0,
            'serviceSMS' => $smsAnalysis['service'] ?? 0,
            'personalSMS' => $smsAnalysis['personal'] ?? 0,
            'familyCalls' => $callAnalysis['family'] ?? 0,
            'newCalls' => $callAnalysis['new'] ?? 0,
            'businessCalls' => $callAnalysis['business'] ?? 0,
            'spamCalls' => $callAnalysis['spam'] ?? 0,
            'intlCalls' => $callAnalysis['intl'] ?? 0,
            'urgentCalls' => $callAnalysis['urgent'] ?? 0,
        ];
    }

    /**
     * Get SMS finance inputs and templated select dropdowns.
     */
    public function getSmsFinanceData(int $userId): array
    {
        $sms_dump = $this->finderModel->get_sms($userId);
        $sms_sender_list = [];
        foreach ($sms_dump as $sms) {
            $sms_sender_list[] = $sms['sms_number'];
        }
        asort($sms_sender_list);
        $sms_senders = array_values(array_unique($sms_sender_list));

        $list = '<label>Select new points.</label>
        <select class="form-control source_sms select2-hidden-accessible" multiple="" data-placeholder="Select a Contact to monitor" style="width: 100%;" tabindex="-1" aria-hidden="true">';

        foreach ($sms_senders as $sms_point) {
            $valu = $this->cryptModel->base64url_encode($sms_point);
            $list .= '<option value="' . $valu . '" >' . $sms_point . '</option>';
        }
        $list .= '        
        </select>';

        $sms_finance_points = $this->finderModel->get_points_sms_finance($userId);
        $sms_parserable = [];

        $list_finance = '
        <select class="form-control source_sms_finance select2-hidden-accessible" data-placeholder="Select a Contact to monitor" style="width: 100%;" tabindex="-1" aria-hidden="true">';

        foreach ($sms_finance_points as $sms_finance) {
            $valu = $this->cryptModel->base64url_encode($sms_finance["point_Name"]);
            $list_finance .= '<option value="' . $valu . '" >' . $sms_finance["point_Name"] . '</option>';
            $sms_parserable[] = $sms_finance["point_Name"];
        }
        $list_finance .= '        
        </select>';

        $sms_good_sms = [];
        if (!empty($sms_parserable)) {
            $sms_good_sms = $this->finderModel->get_sms_from_sender($userId, $sms_parserable);
        }

        return [
            'sms_data_points' => $list,
            'sms_data_points_source' => $list_finance,
            'sms_good_sms' => $sms_good_sms,
        ];
    }

    /**
     * Store SMS datapoints.
     */
    public function setSmsDatapoints(int $userId, string $points): void
    {
        $dated = date('Y-m-d H:i:s');
        $post_point = explode(',', str_replace('"', "", $points));
        foreach ($post_point as $item) {
            if (empty($item)) continue;
            $data_point = [
                "point_Owner" => $userId,
                "point_Name" => base64_decode(urldecode($item)),
                "point_Inserted" => $dated,
            ];
            $this->finderModel->set_sms_points_to_analyze_finance($data_point);
        }
    }

    /**
     * Get specific financial SMS sender data.
     */
    public function getSmsAnalyzeFinanceFrom(int $userId, string $source): array
    {
        $source_clean = $this->cryptModel->base64url_decode($source);
        return [
            'page_info_url' => $source_clean,
            'sms_good_sms' => $this->extractModel->get_sms_from($userId, $source_clean),
        ];
    }

    /**
     * Get financial transaction summary.
     */
    public function getFinancialSummary(int $userId): array
    {
        $transactions = $this->finderModel->get_financial_transactions($userId);
        $spendingByMonth = [];
        $totalSpending = 0;
        
        foreach ($transactions as $tx) {
            if ($tx['type'] !== 'income') {
                $month = $tx['month'];
                if (!isset($spendingByMonth[$month])) {
                    $spendingByMonth[$month] = 0;
                }
                $spendingByMonth[$month] += $tx['amount'];
                $totalSpending += $tx['amount'];
            }
        }
        
        return [
            'transactions' => array_slice($transactions, 0, 10),
            'spendingByMonth' => $spendingByMonth,
            'totalSpending' => $totalSpending,
            'raw_transactions' => $transactions,
        ];
    }

    /**
     * Get detailed finance analysis with charts and pagination.
     */
    public function getDetailedFinanceAnalysis(int $userId, int $page, int $perPage): array
    {
        $transactions = $this->finderModel->get_financial_transactions($userId);

        $spendingByMonth = [];
        $spendingByType = [
            'utility' => 0,
            'airtime' => 0,
            'transfer' => 0,
            'personal' => 0
        ];
        $totalSpending = 0;
        $incomeByMonth = [];
        
        foreach ($transactions as $tx) {
            $month = $tx['month'];
            if ($tx['type'] === 'income') {
                if (!isset($incomeByMonth[$month])) {
                    $incomeByMonth[$month] = 0;
                }
                $incomeByMonth[$month] += $tx['amount'];
            } else {
                if (!isset($spendingByMonth[$month])) {
                    $spendingByMonth[$month] = 0;
                }
                $spendingByMonth[$month] += $tx['amount'];
                
                if (!isset($spendingByType[$tx['type']])) {
                    $spendingByType[$tx['type']] = 0;
                }
                $spendingByType[$tx['type']] += $tx['amount'];
                
                $totalSpending += $tx['amount'];
            }
        }

        $total = count($transactions);
        $offset = ($page - 1) * $perPage;
        $slicedTransactions = array_slice($transactions, $offset, $perPage);

        $pager = \Config\Services::pager();
        $pagerLinks = $pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

        return [
            'financial_data' => [
                'transactions' => $slicedTransactions,
                'all_transactions' => $transactions,
                'spendingByMonth' => $spendingByMonth,
                'spendingByType' => $spendingByType,
                'incomeByMonth' => $incomeByMonth,
                'totalSpending' => $totalSpending
            ],
            'ml_insight' => MLAnalyzerModel::analyzeFinance($transactions),
            'pager_links' => $pagerLinks,
            'currentPage' => $page,
            'perPage' => $perPage,
            'total' => $total,
        ];
    }

    /**
     * Get social analysis details.
     */
    public function getSocialAnalysisData(int $userId, int $page, int $perPage): array
    {
        $allSocial = $this->finderModel->get_social_graph($userId);
        $total = count($allSocial);
        $socialGraphSlice = array_slice($allSocial, ($page - 1) * $perPage, $perPage);
        
        $pager = \Config\Services::pager();
        $pagerLinks = $pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

        return [
            'social_graph' => $socialGraphSlice,
            'pager_links' => $pagerLinks,
            'currentPage' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'ml_insight' => MLAnalyzerModel::analyzeSocial($socialGraphSlice),
        ];
    }

    /**
     * Get digital wellbeing aggregates.
     */
    public function getDigitalWellbeingData(int $userId, int $depthDays): array
    {
        $heatmapRaw = $this->finderModel->get_daily_usage_heatmap($userId);
        $heatmapData = [];
        foreach ($heatmapRaw as $row) {
            $heatmapData[$row['date']] = (int) round($row['total_time_ms'] / 60000); // ms → minutes
        }

        $split = $this->finderModel->get_dopamine_vs_productivity($userId);
        $totalMs = max(1, $split['dopamine_ms'] + $split['productivity_ms'] + $split['other_ms']);
        
        $topApps = $this->finderModel->get_top_time_sink_apps($userId, 7);
        $topAppsLabels = [];
        $topAppsValues = [];
        foreach ($topApps as $app) {
            $topAppsLabels[] = $app['app_name'] ?: $app['package_name'];
            $topAppsValues[] = (int) round($app['total_time_ms'] / 60000); // minutes
        }

        return [
            'dopamine_pct' => round($split['dopamine_ms'] / $totalMs * 100, 1),
            'productivity_pct' => round($split['productivity_ms'] / $totalMs * 100, 1),
            'other_pct' => round($split['other_ms'] / $totalMs * 100, 1),
            'dopamine_hrs' => round($split['dopamine_ms'] / 3600000, 1),
            'productivity_hrs' => round($split['productivity_ms'] / 3600000, 1),
            'other_hrs' => round($split['other_ms'] / 3600000, 1),
            'heatmap_json' => json_encode($heatmapData),
            'top_apps_labels' => json_encode($topAppsLabels),
            'top_apps_values' => json_encode($topAppsValues),
            'has_data' => !empty($heatmapRaw),
        ];
    }

    /**
     * Get privacy audit data and scam audit details.
     */
    public function getPrivacyAuditData(int $userId, int $page, int $perPage, int $scamPage, int $scamPerPage): array
    {
        $allAudit = $this->finderModel->get_app_privacy_audit($userId);
        $total = count($allAudit);
        
        $pager = \Config\Services::pager();
        $auditSlice = array_slice($allAudit, ($page - 1) * $perPage, $perPage);
        $auditPager = $pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
        
        $allScams = $this->finderModel->get_scam_sms_audit($userId);
        $scamTotal = count($allScams);
        $scamsSlice = array_slice($allScams, ($scamPage - 1) * $scamPerPage, $scamPerPage);
        $scamPager = $pager->makeLinks($scamPage, $scamPerPage, $scamTotal, 'bootstrap5_full');

        return [
            'audit' => $auditSlice,
            'audit_pager' => $auditPager,
            'audit_currentPage' => $page,
            'audit_perPage' => $perPage,
            'audit_total' => $total,
            'ml_insight' => MLAnalyzerModel::analyzePrivacy($auditSlice),
            'scams' => $scamsSlice,
            'scam_pager' => $scamPager,
            'scam_currentPage' => $scamPage,
            'scam_perPage' => $scamPerPage,
            'scam_total' => $scamTotal,
            'clipboard_alerts' => $this->finderModel->get_clipboard_privacy_monitor($userId),
            'sideloaded_apps' => $this->finderModel->get_sideloaded_app_audit($userId),
            'accessibility_abuses' => $this->finderModel->get_accessibility_abuse_audit($userId),
            'silent_captures' => $this->finderModel->get_silent_hardware_captures($userId),
        ];
    }


    /**
     * Get storage forensics data.
     */
    public function getStorageIntelligenceData(int $userId, int $page, int $perPage): array
    {
        $storage = $this->finderModel->get_storage_forensics($userId);
        $allFiles = !empty($storage['large_hogs']) ? $storage['large_hogs'] : $storage['top_files'];
        $total = count($allFiles);
        
        $pager = \Config\Services::pager();
        $storage['display_files'] = array_slice($allFiles, ($page - 1) * $perPage, $perPage);
        $pagerLinks = $pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

        return [
            'storage' => $storage,
            'pager_links' => $pagerLinks,
            'currentPage' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'ml_insight' => MLAnalyzerModel::analyzeStorage($storage),
        ];
    }

    /**
     * Get sentiment analysis profiling data.
     */
    public function getSentimentAnalysisData(int $userId, int $page, int $perPage): array
    {
        $allSentiment = $this->finderModel->get_sentiment_profile($userId);
        $total = count($allSentiment);
        
        $pager = \Config\Services::pager();
        $sentimentSlice = array_slice($allSentiment, ($page - 1) * $perPage, $perPage);
        $pagerLinks = $pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

        return [
            'sentiment' => $sentimentSlice,
            'pager_links' => $pagerLinks,
            'currentPage' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'ml_insight' => MLAnalyzerModel::analyzeSentimentML($sentimentSlice),
        ];
    }

    /**
     * Get geospatial hotspots clustering.
     */
    public function getGeoclusteringHotspotsData(int $userId): array
    {
        $clusters = $this->finderModel->get_geospatial_clusters($userId);
        return [
            'clusters' => $clusters,
            'ml_insight' => MLAnalyzerModel::analyzeHotspots($clusters),
        ];
    }

    /**
     * Get cross-category correlation engine data.
     */
    public function getCorrelationEngineData(int $userId): array
    {
        $correlationService = new \App\Services\CorrelationService();
        $graph = $correlationService->buildGraph($userId);
        $topLinks = $correlationService->topLinks($userId, 20);
        $clusters = $correlationService->clusterContacts($userId);

        return [
            'graph' => $graph,
            'graph_json' => json_encode($graph),
            'top_links' => $topLinks,
            'clusters' => $clusters,
        ];
    }

    /**
     * Calculate speed & velocity anomalies between consecutive location points.
     */
    public function calculateSpeedAnomalies(array $locations): array
    {
        $anomalies = [];
        $count = count($locations);
        if ($count < 2) return [];

        for ($i = 0; $i < $count - 1; $i++) {
            $p1 = $locations[$i];
            $p2 = $locations[$i + 1];

            $lat1 = (float)($p1['latitude'] ?? 0);
            $lng1 = (float)($p1['longitude'] ?? 0);
            $lat2 = (float)($p2['latitude'] ?? 0);
            $lng2 = (float)($p2['longitude'] ?? 0);

            if ($lat1 == 0 || $lat2 == 0) continue;

            $t1 = strtotime($p1['timestamp'] ?? $p1['created_at'] ?? 'now');
            $t2 = strtotime($p2['timestamp'] ?? $p2['created_at'] ?? 'now');
            $dt = abs($t1 - $t2) / 3600; // in hours

            if ($dt < 0.001) continue; // avoid divide by zero

            // Haversine distance in km
            $rad = M_PI / 180;
            $dlat = ($lat2 - $lat1) * $rad;
            $dlng = ($lng2 - $lng1) * $rad;
            $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1 * $rad) * cos($lat2 * $rad) * sin($dlng / 2) * sin($dlng / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distKm = 6371 * $c;

            $speedKmH = round($distKm / $dt, 1);

            if ($speedKmH > 160.0 && $distKm > 3.0) {
                $anomalies[] = [
                    'from' => "{$lat1}, {$lng1}",
                    'to'   => "{$lat2}, {$lng2}",
                    'distance_km' => round($distKm, 1),
                    'time_minutes' => round($dt * 60, 1),
                    'speed_kmh' => $speedKmH,
                    'timestamp' => date('Y-m-d H:i:s', min($t1, $t2)),
                    'severity' => $speedKmH > 300 ? 'Critical (Spoof / Anomaly)' : 'High Speed Transit',
                ];
            }
        }
        return array_slice($anomalies, 0, 10);
    }

    /**
     * Reverse geocode GPS cluster lat/lng with database caching.
     */
    public function reverseGeocodeCluster(float $lat, float $lng): string
    {
        $latKey = number_format($lat, 3, '.', '');
        $lngKey = number_format($lng, 3, '.', '');

        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('tbl_geo_address_cache')) {
                $cached = $db->table('tbl_geo_address_cache')
                    ->where('lat_rounded', $latKey)
                    ->where('lng_rounded', $lngKey)
                    ->get()->getRowArray();
                if (!empty($cached['display_name'])) {
                    return $cached['display_name'];
                }
            }
        } catch (\Exception $e) {}

        // Format clean default name
        $address = "Zone ({$latKey}°, {$lngKey}°)";

        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('tbl_geo_address_cache')) {
                $db->table('tbl_geo_address_cache')->insert([
                    'lat_rounded'  => $latKey,
                    'lng_rounded'  => $lngKey,
                    'display_name' => $address,
                    'created_at'   => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Exception $e) {}

        return $address;
    }

    /**
     * Get comprehensive report data with modular filtering and SHA-256 legal chain-of-custody.
     */
    public function getReportData(int $userId, array $selectedSections = []): array
    {
        $allSections = empty($selectedSections) || in_array('all', $selectedSections, true);

        // Detailed Communication Stats
        $smsAnalysis = $this->finderModel->get_categorized_sms_counts($userId);
        $callAnalysis = $this->finderModel->get_categorized_call_counts($userId);

        // Financial Intelligence data
        $transactions = $this->finderModel->get_financial_transactions($userId);
        $totalSpending = 0;
        foreach ($transactions as $tx) {
            if ($tx['type'] !== 'income') {
                $totalSpending += $tx['amount'];
            }
        }
        $financialSummary = [
            'total_spending' => $totalSpending,
            'tx_count' => count($transactions),
            'recent_tx' => array_slice($transactions, 0, 15)
        ];

        // Social & Contacts
        $socialGraph = $this->finderModel->get_social_graph($userId, 20);
        $socialGraphSlice = array_slice($socialGraph, 0, 10);
        $device = $this->finderModel->get_device_health($userId);

        // Apps Intelligence
        $apps = $this->finderModel->get_apps($userId, 10);
        $topApps = array_map(function($app) {
            return [
                'name' => $app['Name'],
                'package' => $app['Package'],
                'install_time' => $app['first_install_time']
            ];
        }, $apps);

        // Location History
        $recentLocations = $this->finderModel->get_locations($userId, 5);

        // ML Intelligence Data
        $mobilityData = $this->finderModel->get_mobility_aggregates($userId);
        $auditData = $this->finderModel->get_app_privacy_audit($userId);
        $forecastData = $this->finderModel->get_subscription_forecast($userId);
        $sentimentData = $this->finderModel->get_sentiment_profile($userId);
        $clusterData = $this->finderModel->get_geospatial_clusters($userId);
        $categoriesData = $this->finderModel->get_app_category_dist($userId);
        $storageData = $this->finderModel->get_storage_forensics($userId);

        // Calculate Executive Threat Score (0-100)
        $highRiskApps = count(array_filter($auditData, fn($a) => ($a['score'] ?? 0) >= 7));
        $threatScore = min(100, max(15, ($highRiskApps * 15) + (count($selectedSections) > 0 ? 10 : 5)));

        // SHA-256 Chain of Custody Signature
        $reportPayloadStr = $userId . '|' . date('Y-m-d H:i:s') . '|' . json_encode($allSections ? ['all'] : $selectedSections);
        $chainOfCustodyHash = strtoupper(hash('sha256', $reportPayloadStr));

        return [
            'sections_filter' => $allSections ? ['all'] : $selectedSections,
            'executive_threat_score' => $threatScore,
            'chain_of_custody_hash' => $chainOfCustodyHash,
            'sms_analysis' => $smsAnalysis,
            'call_analysis' => $callAnalysis,
            'financial_summary' => $financialSummary,
            'social_graph' => $socialGraphSlice,
            'device' => $device,
            'top_apps' => $topApps,
            'recent_locations' => $recentLocations,
            'ml_social' => MLAnalyzerModel::analyzeSocial($socialGraph),
            'ml_finance' => MLAnalyzerModel::analyzeFinance($transactions),
            'ml_mobility' => MLAnalyzerModel::analyzeMobility($mobilityData),
            'ml_privacy' => MLAnalyzerModel::analyzePrivacy($auditData),
            'ml_subscript' => MLAnalyzerModel::analyzeSubscriptions($forecastData),
            'ml_sentiment' => MLAnalyzerModel::analyzeSentimentML($sentimentData),
            'ml_hotspots' => MLAnalyzerModel::analyzeHotspots($clusterData),
            'ml_apps' => MLAnalyzerModel::analyzeApps($categoriesData),
            'ml_storage' => MLAnalyzerModel::analyzeStorage($storageData),
            'subscription_data' => $forecastData,
            'cluster_data' => $clusterData,
            'categories_data' => $categoriesData,
            'storage_data' => $storageData,
            'sentiment_profile' => $sentimentData,
            'raw_transactions' => $transactions,
        ];
    }
}

