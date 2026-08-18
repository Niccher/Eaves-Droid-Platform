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
     * Get comprehensive report data.
     */
    public function getReportData(int $userId): array
    {
        // Detailed Communication Stats
        $smsAnalysis = $this->finderModel->get_categorized_sms_counts($userId);
        $callAnalysis = $this->finderModel->get_categorized_call_counts($userId);

        // Financial Intelligence data (Expanded)
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

        // === PHP-ML Intelligence Data ===
        $mobilityData = $this->finderModel->get_mobility_aggregates($userId);
        $auditData = $this->finderModel->get_app_privacy_audit($userId);
        $forecastData = $this->finderModel->get_subscription_forecast($userId);
        $sentimentData = $this->finderModel->get_sentiment_profile($userId);
        $clusterData = $this->finderModel->get_geospatial_clusters($userId);
        $categoriesData = $this->finderModel->get_app_category_dist($userId);
        $storageData = $this->finderModel->get_storage_forensics($userId);

        return [
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
