<?php

namespace App\Controllers\clients;

use App\Models\ExtractModel;
use App\Models\FinderModel;
use App\Models\CryptModel;
use App\Models\ParseLootModel;
use App\Models\ReceiveModel;
use App\Models\AndroidModel;
use App\Models\UserModel;
use App\Models\MLAnalyzerModel;
use App\Models\AnomaliesModel;

class CorrelationController extends BaseClientController
{
    protected $correlationHelper;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void
    {
        parent::initController($request, $response, $logger);
        $this->correlationHelper = new \App\Services\CorrelationHelperService();
    }

    public function index()
    {
        $pg = 'correlation';
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $indexData = $this->correlationHelper->getIndexData($this->userId);
        
        $data = array_merge($data, $counts, $indexData);

        // Fetch anomaly findings from latest job for composite index
        $anomModel = new AnomaliesModel();
        $latestJobAlerts = $anomModel->getAnomalyAlerts([
            'sms', 'contacts', 'call_logs', 'locations', 'apps', 'files', 'activity', 'device_info'
        ], $this->userId, 100);
        
        $highCount = 0;
        $medCount = 0;
        $lowCount = 0;
        foreach ($latestJobAlerts as $a) {
            $sev = strtolower($a['severity'] ?? 'low');
            if ($sev === 'high') {
                $highCount++;
            } elseif ($sev === 'medium') {
                $medCount++;
            } else {
                $lowCount++;
            }
        }
        
        // Calculate composite score
        $compositeScore = 0;
        $compositeScore += $highCount * 25;
        $compositeScore += $medCount * 10;
        $compositeScore += $lowCount * 3;
        
        // Add rule-based counts
        $maliciousSmsCount = (int)($data['maliciousSMS'] ?? 0);
        $spamCallCount = (int)($data['spamCalls'] ?? 0);
        $compositeScore += $maliciousSmsCount * 5;
        $compositeScore += $spamCallCount * 2;
        
        $threatIndex = min(100, $compositeScore);
        
        // Set labels
        if ($threatIndex >= 70) {
            $threatStatus = 'Critical Threat';
            $threatBadgeColor = 'danger';
            $threatColorBorder = '#dc3545';
        } elseif ($threatIndex >= 30) {
            $threatStatus = 'Warning';
            $threatBadgeColor = 'warning';
            $threatColorBorder = '#ffc107';
        } else {
            $threatStatus = 'Secured';
            $threatBadgeColor = 'success';
            $threatColorBorder = '#28a745';
        }
        
        $data['threat_index'] = $threatIndex;
        $data['threat_status'] = $threatStatus;
        $data['threat_badge_color'] = $threatBadgeColor;
        $data['threat_color_border'] = $threatColorBorder;
        $data['active_scanners_count'] = 11; // apps reputation, apps permission, apps autoencoder, notification hijack, accessibility abuse, sms bert, call isolation, file entropy, device oneclass, activity lstm, communication spikes
        $data['high_threat_count'] = $highCount;

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/' . $pg, $data)
            . view('headers_footers/footer_users');
    }

    /**
     * Rate-limited force manual execution of all registered ML models
     */
    public function forceScan()
    {
        $userId = $this->userId;
        $db = \Config\Database::connect();

        // 4-hour rate limit check
        $lastJob = $db->table('ml_jobs')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if ($lastJob) {
            $lastTime = strtotime($lastJob['created_at']);
            $diffHours = (time() - $lastTime) / 3600;
            if ($diffHours < 4) {
                $remainingMin = round((4 - $diffHours) * 60);
                session()->setFlashdata('error', "Please wait {$remainingMin} minute(s) before running another manual scan (rate limit: once per 4 hours).");
                return redirect()->to('/analysis');
            }
        }

        try {
            $anomModel = new AnomaliesModel();
            
            // Build selected algorithms structure
            $selectedAlgs = [];
            foreach ($anomModel->getAlgorithmCategories() as $catKey => $catInfo) {
                foreach ($catInfo['algorithms'] as $alg) {
                    $selectedAlgs[$catKey][] = $alg['id'];
                }
            }
            
            // Trigger Python/PHP detectors
            $anomModel->runPythonDetection($selectedAlgs, $userId, 'full');
            
            session()->setFlashdata('success', 'Manual telemetry scanning triggered successfully! Check the Anomaly Scanner for results shortly.');
        } catch (\Throwable $e) {
            session()->setFlashdata('error', 'Failed to trigger scan: ' . $e->getMessage());
        }

        return redirect()->to('/analysis');
    }

    public function smsFinance($id = null)
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();

        // Stats
        $counts = $this->getUserDataCounts();
        $financeData = $this->correlationHelper->getSmsFinanceData($this->userId);

        $data = array_merge($data, $counts, $financeData);

        // Fetch Python SMS phishing scores
        $anomModel = new AnomaliesModel();
        $pySmsResults = $anomModel->getLatestJobResultsByAlgorithm($this->userId, 'sms_bert');
        $phishingMap = [];
        foreach ($pySmsResults as $row) {
            $details = json_decode($row['details'] ?? '{}', true) ?? [];
            $sender = preg_replace('/[^0-9]/', '', $details['sender'] ?? '');
            $senderNorm = strlen($sender) >= 9 ? substr($sender, -9) : $sender;
            if ($senderNorm) {
                $phishingMap[$senderNorm][] = [
                    'preview'  => strtolower($details['body_preview'] ?? ''),
                    'score'    => (float)($row['score'] ?? 0),
                    'severity' => $row['severity'] ?? 'Medium',
                ];
            }
        }
        $data['phishing_map'] = $phishingMap;

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/sms_finance', $data)
            . view('headers_footers/tail_analyze_sms', $data);

    }

    public function setSmsDatapoints($owner)
    {
        $points = $this->request->getPost('points') ?? '';
        $this->correlationHelper->setSmsDatapoints($this->userId, $points);
    }

    public function smsAnalyzeFinanceFrom($source, $param2 = null)
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();

        // Stats
        $counts = $this->getUserDataCounts();
        $sourceData = $this->correlationHelper->getSmsAnalyzeFinanceFrom($this->userId, $source);

        $data = array_merge($data, $counts, $sourceData);

        // Fetch Python SMS phishing scores
        $anomModel = new AnomaliesModel();
        $pySmsResults = $anomModel->getLatestJobResultsByAlgorithm($this->userId, 'sms_bert');
        $phishingMap = [];
        foreach ($pySmsResults as $row) {
            $details = json_decode($row['details'] ?? '{}', true) ?? [];
            $sender = preg_replace('/[^0-9]/', '', $details['sender'] ?? '');
            $senderNorm = strlen($sender) >= 9 ? substr($sender, -9) : $sender;
            if ($senderNorm) {
                $phishingMap[$senderNorm][] = [
                    'preview'  => strtolower($details['body_preview'] ?? ''),
                    'score'    => (float)($row['score'] ?? 0),
                    'severity' => $row['severity'] ?? 'Medium',
                ];
            }
        }
        $data['phishing_map'] = $phishingMap;

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/sms_finance_single_view', $data)
            . view('headers_footers/tail_analyze_sms', $data);

    }

    public function smsAnalysis()
    {
        $category = $this->request->getGet('category') ?? 'financial';
        $perPage = 20;
        $page = (int) ($this->request->getGet('page') ?? 1);
        
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $results = $this->finderModel->get_categorized_sms($this->userId, $category, $perPage, $page);
        
        $data = array_merge($data, $counts);
        $data['current_category'] = $category;
        $data['categorized_sms'] = $results['data'];
        $data['total'] = $results['total'];
        $data['perPage'] = $perPage;
        
        // Pass counts for the tabs
        $data['sms_counts'] = $this->finderModel->get_categorized_sms_counts($this->userId);

        // Setup Pager
        $data['pager'] = \Config\Services::pager();
        $data['pager_links'] = $data['pager']->makeLinks($page, $perPage, $results['total'], 'bootstrap5_full');

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/sms_analysis', $data)
            . view('headers_footers/footer_users');
    }

    public function callAnalysis()
    {
        $category = $this->request->getGet('category') ?? 'family';
        $perPage = 20;
        $page = (int) ($this->request->getGet('page') ?? 1);
        
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $results = $this->finderModel->get_categorized_calls($this->userId, $category, $perPage, $page);

        $data = array_merge($data, $counts);
        $data['current_category'] = $category;
        $data['categorized_calls'] = $results['data'];
        $data['total'] = $results['total'];
        $data['perPage'] = $perPage;
        
        // Pass counts for the tabs
        $data['call_counts'] = $this->finderModel->get_categorized_call_counts($this->userId);

        // Setup Pager
        $data['pager'] = \Config\Services::pager();
        $data['pager_links'] = $data['pager']->makeLinks($page, $perPage, $results['total'], 'bootstrap5_full');

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/call_analysis', $data)
            . view('headers_footers/footer_users');
    }

    public function advanced()
    {
        $subModel = new \App\Models\SubscriptionModel();
        $userTier = $subModel->getPlanTier($this->userId);

        $data['pag'] = 'analysis';
        $data['userTier'] = $userTier;
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $deviceData = $this->getDeviceViewData();
        $financialSummary = $this->correlationHelper->getFinancialSummary($this->userId);

        $data = array_merge($data, $counts, $deviceData);
        
        $data['financial_summary'] = [
            'transactions' => $financialSummary['transactions'],
            'spendingByMonth' => $financialSummary['spendingByMonth'],
            'totalSpending' => $financialSummary['totalSpending']
        ];
        $data['ml_insight_finance'] = MLAnalyzerModel::analyzeFinance($financialSummary['raw_transactions']);

        $data['sms_analysis'] = $this->finderModel->get_categorized_sms_counts($this->userId);
        $data['call_analysis'] = $this->finderModel->get_categorized_call_counts($this->userId);

        // Fetch anomaly findings from latest job for composite index
        $anomModel = new AnomaliesModel();
        $latestJobAlerts = $anomModel->getAnomalyAlerts([
            'sms', 'contacts', 'call_logs', 'locations', 'apps', 'files', 'activity', 'device_info'
        ], $this->userId, 100);
        
        $highCount = 0;
        $medCount = 0;
        $lowCount = 0;
        foreach ($latestJobAlerts as $a) {
            $sev = strtolower($a['severity'] ?? 'low');
            if ($sev === 'high') {
                $highCount++;
            } elseif ($sev === 'medium') {
                $medCount++;
            } else {
                $lowCount++;
            }
        }
        
        // Calculate composite score
        $compositeScore = 0;
        $compositeScore += $highCount * 25;
        $compositeScore += $medCount * 10;
        $compositeScore += $lowCount * 3;
        
        // Add rule-based counts
        $maliciousSmsCount = (int)($data['sms_analysis']['malicious'] ?? 0);
        $spamCallCount = (int)($data['call_analysis']['spam'] ?? 0);
        $compositeScore += $maliciousSmsCount * 5;
        $compositeScore += $spamCallCount * 2;
        
        $threatIndex = min(100, $compositeScore);
        
        // Set labels
        if ($threatIndex >= 70) {
            $threatStatus = 'Critical Threat';
            $threatBadgeColor = 'danger';
            $threatColorBorder = '#dc3545';
        } elseif ($threatIndex >= 30) {
            $threatStatus = 'Warning';
            $threatBadgeColor = 'warning';
            $threatColorBorder = '#ffc107';
        } else {
            $threatStatus = 'Secured';
            $threatBadgeColor = 'success';
            $threatColorBorder = '#28a745';
        }
        
        $data['threat_index'] = $threatIndex;
        $data['threat_status'] = $threatStatus;
        $data['threat_badge_color'] = $threatBadgeColor;
        $data['threat_color_border'] = $threatColorBorder;
        $data['active_scanners_count'] = 11;
        $data['high_threat_count'] = $highCount;

        return $this->renderAppView('users/correlation/advanced_analysis', $data);
    }

    public function refreshMl()
    {
        $transactions = $this->finderModel->get_financial_transactions($this->userId);
        $contacts = $this->finderModel->get_social_graph($this->userId);
        $mobility = $this->finderModel->get_mobility_aggregates($this->userId);
        $audit = $this->finderModel->get_app_privacy_audit($this->userId);
        $forecast = $this->finderModel->get_subscription_forecast($this->userId);
        $sentiment = $this->finderModel->get_sentiment_profile($this->userId);
        $clusters = $this->finderModel->get_geospatial_clusters($this->userId);
        $storage = $this->finderModel->get_storage_forensics($this->userId);
        $categories = $this->finderModel->get_app_category_dist($this->userId);

        MLAnalyzerModel::analyzeFinance($transactions);
        MLAnalyzerModel::analyzeSocial($contacts);
        MLAnalyzerModel::analyzeMobility($mobility);
        MLAnalyzerModel::analyzePrivacy($audit);
        MLAnalyzerModel::analyzeSubscriptions($forecast);
        MLAnalyzerModel::analyzeSentimentML($sentiment);
        MLAnalyzerModel::analyzeHotspots($clusters);
        MLAnalyzerModel::analyzeStorage($storage);
        MLAnalyzerModel::analyzeApps($categories);

        session()->setFlashdata('success', 'PHP-ML analysis refreshed successfully!');

        return redirect()->to(base_url('analysis'));
    }

    public function financeAnalysis()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 15;

        $financeAnalysis = $this->correlationHelper->getDetailedFinanceAnalysis($this->userId, (int)$page, $perPage);

        $data = array_merge($data, $counts);
        $data['financial_data'] = $financeAnalysis['financial_data'];
        $data['ml_insight'] = $financeAnalysis['ml_insight'];
        $data['pager_links'] = $financeAnalysis['pager_links'];
        $data['currentPage'] = $financeAnalysis['currentPage'];
        $data['perPage'] = $financeAnalysis['perPage'];
        $data['total'] = $financeAnalysis['total'];
        
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['sms']);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/finance_analysis', $data)
            . view('headers_footers/footer_users');
    }

    public function locationAnalysis()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        // Filters
        $data['start_date'] = $this->request->getGet('start_date');
        $data['end_date'] = $this->request->getGet('end_date');

        if ($data['start_date'] && $data['end_date']) {
            $data['locations'] = $this->finderModel->get_location_history_filtered($this->userId, $data['start_date'], $data['end_date']);
        } else {
            $data['locations'] = $this->finderModel->get_location_history($this->userId);
        }
        
        // Enriched Location Datasets
        $data['cell_towers'] = $this->finderModel->get_cell_tower_fallbacks($this->userId);
        $data['speed_anomalies'] = $this->correlationHelper->calculateSpeedAnomalies($data['locations']);
        $data['geofence_events'] = $this->finderModel->get_geofence_events($this->userId);
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['locations']);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/location_analysis', $data)
            . view('headers_footers/footer_users');
    }

    public function devicePulse()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        $data['device'] = $this->finderModel->get_device_health($this->userId);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/device_pulse', $data)
            . view('headers_footers/footer_users');
    }

    public function socialAnalysis()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 15;

        $socialData = $this->correlationHelper->getSocialAnalysisData($this->userId, $page, $perPage);

        $data = array_merge($data, $counts);
        $data['social_graph'] = $socialData['social_graph'];
        $data['pager_links'] = $socialData['pager_links'];
        $data['currentPage'] = $socialData['currentPage'];
        $data['perPage'] = $socialData['perPage'];
        $data['total'] = $socialData['total'];
        $data['ml_insight'] = $socialData['ml_insight'];
        
        // Enriched Social Metrics
        $data['contact_response'] = $this->finderModel->get_contact_response_metrics($this->userId);
        $data['relationship_age'] = $this->finderModel->get_first_last_contact_timestamps($this->userId);
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['contacts']);

        // Python Graph Outliers
        $anomModel = new AnomaliesModel();
        $pyGraphResults = $anomModel->getLatestJobResultsByAlgorithm($this->userId, 'contacts_graph');
        $graphOrphans = [];
        $graphOutliers = [];
        foreach ($pyGraphResults as $row) {
            $details = json_decode($row['details'] ?? '{}', true) ?? [];
            $contactInfo = [
                'name'     => $details['contact'] ?? $row['anomaly'] ?? 'Unknown',
                'phone'    => $details['phone'] ?? '',
                'score'    => (float)($row['score'] ?? 0),
                'severity' => $row['severity'] ?? 'Medium',
            ];
            if (($details['reason'] ?? '') === 'zero_interaction') {
                $graphOrphans[] = $contactInfo;
            } else {
                $graphOutliers[] = $contactInfo;
            }
        }
        $data['graph_orphans'] = $graphOrphans;
        $data['graph_outliers'] = $graphOutliers;


        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/social_analysis', $data)
            . view('headers_footers/footer_users');
    }

    public function generateReport()
    {
        // Disable debug toolbar to prevent HTML injection into PDF binary
        if (ENVIRONMENT !== 'production') {
            service('toolbar')->respond();
        }

        // Fetch data
        $data['user_info'] = $this->finderModel->basic_user();
        $data['counts'] = $this->getUserDataCounts();

        // Get selected section filter from POST / GET
        $rawSections = $this->request->getPost('sections') ?? $this->request->getGet('sections') ?? [];
        if (is_string($rawSections)) {
            $rawSections = explode(',', $rawSections);
        }
        $selectedSections = array_filter(array_map('trim', (array)$rawSections));

        $reportData = $this->correlationHelper->getReportData($this->userId, $selectedSections);

        $data['sections_filter'] = $reportData['sections_filter'];
        $data['executive_threat_score'] = $reportData['executive_threat_score'];
        $data['chain_of_custody_hash'] = $reportData['chain_of_custody_hash'];

        $data['sms_analysis'] = $reportData['sms_analysis'];
        $data['call_analysis'] = $reportData['call_analysis'];
        $data['financial_summary'] = $reportData['financial_summary'];
        $data['social_graph'] = $reportData['social_graph'];
        $data['device'] = $reportData['device'];
        $data['top_apps'] = $reportData['top_apps'];
        $data['recent_locations'] = $reportData['recent_locations'];
        $data['ml_social'] = $reportData['ml_social'];
        $data['ml_finance'] = $reportData['ml_finance'];
        $data['ml_mobility'] = $reportData['ml_mobility'];
        $data['ml_privacy'] = $reportData['ml_privacy'];
        $data['ml_subscript'] = $reportData['ml_subscript'];
        $data['ml_sentiment'] = $reportData['ml_sentiment'];
        $data['ml_hotspots'] = $reportData['ml_hotspots'];
        $data['ml_apps'] = $reportData['ml_apps'];
        $data['ml_storage'] = $reportData['ml_storage'];

        // Pass raw data for tables in report
        $data['subscription_data'] = $reportData['subscription_data'];
        $data['cluster_data'] = $reportData['cluster_data'];
        $data['categories_data'] = $reportData['categories_data'];
        $data['storage_data'] = $reportData['storage_data'];
        $data['sentiment_profile'] = $reportData['sentiment_profile'];

        $data['date'] = date('F j, Y');

        // Clean all strings for UTF-8 and HTML safety
        $data = $this->utf8CleanArray($data);

        // Load view (ensure view file has no BOM/whitespace)
        $html = view('users/correlation/report_template', $data);

        // Generate PDF
        require_once ROOTPATH . 'vendor/autoload.php';
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', false); // Prevent SSRF
        $options->set('isLocalFilesystemEnabled', false); // Prevent LFI
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Explicitly set headers and stream
        header('Content-Type: application/pdf');
        $dompdf->stream("Intelligence_Report_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
        exit;
    }

    /**
     * Lifestyle & Mobility Profiling dashboard.
     */
    public function lifestyleAnalysis()
    {
        $uri = service('request')->getUri()->getPath();
        if (str_contains($uri, 'advanced/software')) {
            $data['active_tab'] = 'digital_wellbeing';
            $data['back_url']   = base_url('advanced/software');
            $data['back_label'] = 'Back to Software';
        } else {
            $data['pag']        = 'analysis';
            $data['back_url']   = base_url('analysis');
            $data['back_label'] = 'Back to Analysis';
        }
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        $data['mobility'] = $this->finderModel->get_mobility_aggregates($this->userId);
        $data['circadian'] = $this->finderModel->get_circadian_sleep_profile($this->userId);
        $data['travel_dist'] = $this->finderModel->get_daily_travel_distances($this->userId);
        $data['ml_insight'] = MLAnalyzerModel::analyzeMobility($data['mobility']);
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['activity']);
        
        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/lifestyle_analysis', $data)
            . view('headers_footers/footer_users');
    }


    public function intelligenceTimeline()
    {
        $data['pag']       = 'analysis';
        $data['sub_pag']   = 'timeline';
        $data['user_info'] = $this->finderModel->basic_user();

        $counts = $this->getUserDataCounts();
        $data   = array_merge($data, $counts, $this->getDeviceViewData());

        $filterType = $this->request->getGet('type') ?? 'all';

        // Plan-gated history window (Free=7d, Gold=30d, Platinum=full)
        $gate = new \App\Services\PlanGate();
        $limits = $gate->limits($this->userId);
        $maxHistory = (int)($limits['history_days'] ?? 7);
        $data['plan'] = $limits['plan'] ?? 'free';
        $data['max_history_days'] = $maxHistory;
        $data['history_label'] = $maxHistory >= 365 ? 'Full history' : ($maxHistory . ' days');
        $requested = (int)($this->request->getGet('days') ?? 0);
        $days = ($requested > 0) ? min($requested, $maxHistory) : $maxHistory;
        $data['timeline_days'] = $days;

        // Determine plan total limit
        $totalLimit = 50;
        if ($data['plan'] === 'gold') {
            $totalLimit = 100;
        } elseif ($data['plan'] === 'platinum') {
            $totalLimit = 150;
        }
        $data['timeline_limit'] = $totalLimit;

        // Fetch merged comprehensive timeline
        $timeline = $this->finderModel->get_unified_timeline($this->userId, $totalLimit, $days, true);
        $data['timeline'] = $timeline;

        return $this->renderAppView('users/correlation/intelligence_timeline', $data);
    }

    /**
     * Privacy & Permission Audit.
     */
    public function privacyAudit()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts());

        $page = (int) ($this->request->getGet('page') ?? 1);
        $scamPage = (int) ($this->request->getGet('scam_page') ?? 1);
        $perPage = 15;

        $privacyData = $this->correlationHelper->getPrivacyAuditData($this->userId, $page, $perPage, $scamPage, $perPage);

        $data['audit'] = $privacyData['audit'];
        $data['audit_pager'] = $privacyData['audit_pager'];
        $data['audit_currentPage'] = $privacyData['audit_currentPage'];
        $data['audit_perPage'] = $privacyData['audit_perPage'];
        $data['audit_total'] = $privacyData['audit_total'];
        $data['ml_insight'] = $privacyData['ml_insight'];
        
        $data['scams'] = $privacyData['scams'];
        $data['scam_pager'] = $privacyData['scam_pager'];
        $data['scam_currentPage'] = $privacyData['scam_currentPage'];
        $data['scam_perPage'] = $privacyData['scam_perPage'];
        $data['scam_total'] = $privacyData['scam_total'];

        // Enriched Privacy Data Arrays
        $data['clipboard_alerts'] = $privacyData['clipboard_alerts'];
        $data['sideloaded_apps'] = $privacyData['sideloaded_apps'];
        $data['accessibility_abuses'] = $privacyData['accessibility_abuses'];
        $data['silent_captures'] = $privacyData['silent_captures'];

        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['apps', 'device_info', 'activity']);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/privacy_audit', $data)
            . view('headers_footers/footer_users');
    }

    /**
     * Recurring Bills & Subscription Tracker.
     */
    public function subscriptionTracker()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts());

        $data['forecast'] = $this->finderModel->get_subscription_forecast($this->userId);
        $data['ml_insight'] = MLAnalyzerModel::analyzeSubscriptions($data['forecast']);
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['sms']);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/subscription_tracker', $data)
            . view('headers_footers/footer_users');
    }

    /**
     * App Portfolio & Categorization.
     */
    public function appPortfolio()
    {
        $uri = service('request')->getUri()->getPath();
        if (str_contains($uri, 'advanced/software')) {
            $data['active_tab'] = 'app_usage';
            $data['back_url']   = base_url('advanced/software');
            $data['back_label'] = 'Back to Software';
        } else {
            $data['pag']        = 'analysis';
            $data['back_url']   = base_url('analysis');
            $data['back_label'] = 'Back to Analysis';
        }
        $data["user_info"] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts());

        $data['categories'] = $this->finderModel->get_app_category_dist($this->userId);
        $data['bandwidth_usage'] = $this->finderModel->get_app_bandwidth_usage($this->userId);
        $data['crash_analytics'] = $this->finderModel->get_app_crash_analytics($this->userId);
        $data['bloatware_apps']  = $this->finderModel->get_unused_bloatware_apps($this->userId);

        $data['ml_insight'] = MLAnalyzerModel::analyzeApps($data['categories']);
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['apps']);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/app_portfolio', $data)
            . view('headers_footers/footer_users');
    }


    /**
     * Media & Storage Intelligence.
     */
    public function storageIntelligence()
    {
        $uri = service('request')->getUri()->getPath();
        if (str_contains($uri, 'advanced/hardware')) {
            $data['active_tab'] = 'storage';
            $data['back_url']   = base_url('advanced/hardware');
            $data['back_label'] = 'Back to Hardware';
        } else {
            $data['pag']        = 'analysis';
            $data['back_url']   = base_url('analysis');
            $data['back_label'] = 'Back to Analysis';
        }
        $data["user_info"] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts());

        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 15;

        $storageData = $this->correlationHelper->getStorageIntelligenceData($this->userId, $page, $perPage);

        $data['storage'] = $storageData['storage'];
        $data['pager_links'] = $storageData['pager_links'];
        $data['currentPage'] = $storageData['currentPage'];
        $data['perPage'] = $storageData['perPage'];
        $data['total'] = $storageData['total'];
        $data['ml_insight'] = $storageData['ml_insight'];
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['files']);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/storage_intelligence', $data)
            . view('headers_footers/footer_users');
    }

    /**
     * Sentiment & Relationship Health.
     */
    public function sentimentAnalysis()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts());

        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 15;

        $sentimentData = $this->correlationHelper->getSentimentAnalysisData($this->userId, $page, $perPage);

        $data['sentiment'] = $sentimentData['sentiment'];
        $data['pager_links'] = $sentimentData['pager_links'];
        $data['currentPage'] = $sentimentData['currentPage'];
        $data['perPage'] = $sentimentData['perPage'];
        $data['total'] = $sentimentData['total'];
        $data['ml_insight'] = $sentimentData['ml_insight'];
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['sms']);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/sentiment_analysis', $data)
            . view('headers_footers/footer_users');
    }


    /**
     * Geospatial Hotspot Clustering.
     */
    public function geoclusteringHotspots()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts());

        $hotspotsData = $this->correlationHelper->getGeoclusteringHotspotsData($this->userId);

        $clusters = $hotspotsData['clusters'];
        foreach ($clusters as &$c) {
            if (!empty($c['lat']) && !empty($c['lng'])) {
                $c['address_label'] = $this->correlationHelper->reverseGeocodeCluster((float)$c['lat'], (float)$c['lng']);
            }
        }
        unset($c);

        $data['clusters'] = $clusters;
        $data['ml_insight'] = $hotspotsData['ml_insight'];
        $data['bluetooth_colocation'] = $this->finderModel->get_paired_bluetooth_colocation($this->userId);
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['locations']);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/geospatial_hotspots', $data)
            . view('headers_footers/footer_users');
    }


    /**
     * Fetches anomaly alerts from the most recent completed ml_job
     * for the given category keys. Returns an empty array if none.
     */
    protected function getAnomalyAlertsForPage(array $categories): array
    {
        $model = new AnomaliesModel();
        return $model->getAnomalyAlerts($categories, $this->userId);
    }

    /**
     * Recursively clean array data: convert to UTF-8 and escape for HTML.
     */
    private function utf8CleanArray($data) {
        if (is_array($data)) {
            return array_map([$this, 'utf8CleanArray'], $data);
        }
        if (is_string($data)) {
            // Convert to UTF-8 if not already
            $enc = mb_detect_encoding($data, mb_detect_order(), true);
            if ($enc && $enc !== 'UTF-8') {
                $data = mb_convert_encoding($data, 'UTF-8', $enc);
            } elseif (!$enc) {
                // Fallback: assume it's UTF-8 but clean invalid sequences
                $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            }
            // Escape for HTML output (use ENT_QUOTES to handle both quotes)
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }

    /**
     * Digital Wellbeing & Screen Time Analytics.
     */
    public function digitalWellbeing()
    {
        $data['pag']       = 'intelligence';
        $data['sub_pag']   = 'wellbeing';
        $data['user_info'] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts(), $this->getDeviceViewData());

        // Plan depth (Gold/Platinum) — bound all ranges by wellbeing_depth/history
        $gate = new \App\Services\PlanGate();
        $limits = $gate->limits($this->userId);
        $data['plan'] = $limits['plan'] ?? 'free';
        $depthDays = (int)($limits['wellbeing_depth'] ?? ($limits['history_days'] ?? 30));
        if ($depthDays === 0) $depthDays = (int)($limits['history_days'] ?? 30);
        $data['wellbeing_days'] = $depthDays;
        $data['wellbeing_depth_label'] = ($limits['wellbeing_depth'] ?? '') === 'all' ? 'All data' : ($depthDays . ' days');

        $wellbeingData = $this->correlationHelper->getDigitalWellbeingData($this->userId, $depthDays);

        $data = array_merge($data, $wellbeingData);
        $data['total_unlocks'] = $this->finderModel->get_total_unlocks($this->userId, $depthDays);

        // ——— New Platinum/Gold intelligence panels ———
        // Sleep inference (Gold+)
        $data['sleep'] = $this->finderModel->get_sleep_intervals($this->userId, $depthDays);

        // Daily screen time (Gold+)
        $data['screen_time'] = $this->finderModel->get_daily_screen_time($this->userId, $depthDays);

        // App addiction report (Gold+)
        $data['addiction'] = $this->finderModel->get_app_addiction_report($this->userId, $depthDays);

        // Steps + battery trends (Platinum)
        $data['activity_battery'] = $this->finderModel->get_activity_battery_trends($this->userId, $depthDays);
        $data['is_platinum'] = ($data['plan'] === 'platinum');

        // Fetch Python sleep and battery anomalies
        $anomModel = new AnomaliesModel();
        $data['sleep_anomalies'] = $anomModel->getLatestJobResultsByAlgorithm($this->userId, 'sleep_disturbance');
        $data['battery_anomalies'] = $anomModel->getLatestJobResultsByAlgorithm($this->userId, 'battery_drain');

        return $this->renderAppView('users/correlation/digital_wellbeing', $data);
    }

    /**
     * Behavioral Anomaly & Pattern-of-Life Analysis.
     */
    public function behavioralAnomalies()
    {
        $data['pag']       = 'intelligence';
        $data['sub_pag']   = 'behavioral';
        $data['user_info'] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts(), $this->getDeviceViewData());

        $gate = new \App\Services\PlanGate();
        $limits = $gate->limits($this->userId);
        $plan = strtolower($limits['plan'] ?? 'free');
        $data['plan'] = $plan;

        $rawAnomalies = $this->finderModel->get_behavioral_anomalies($this->userId);
        
        $filtered = [];
        foreach ($rawAnomalies as $anomaly) {
            $type = $anomaly['type'] ?? 'other';
            if ($plan === 'free' && $type === 'call') {
                $filtered[] = $anomaly;
            } elseif ($plan === 'gold' && in_array($type, ['call', 'communication', 'app_usage'], true)) {
                $filtered[] = $anomaly;
            } elseif ($plan === 'platinum') {
                $filtered[] = $anomaly;
            }
        }
        $data['anomalies'] = $filtered;

        // Fetch active blocklist/whitelists
        $db = \Config\Database::connect();
        $data['active_whitelists'] = $db->table('tbl_user_blocklists')
            ->where('owner_id', $this->userId)
            ->get()->getResultArray();

        // Fetch whitelist audit logs
        $data['whitelist_logs'] = $db->table('tbl_whitelist_logs')
            ->where('user_id', $this->userId)
            ->orderBy('created_at', 'DESC')
            ->limit(20)
            ->get()->getResultArray();

        // Fetch Python communication spikes findings
        $anomModel = new AnomaliesModel();
        $data['comm_spikes'] = $anomModel->getLatestJobResultsByAlgorithm($this->userId, 'communication_spikes');

        return $this->renderAppView('users/correlation/behavioral_anomalies', $data);
    }

    /**
     * Reverses/deletes a whitelisted item and logs the removal.
     */
    public function removeWhitelist()
    {
        $userId = $this->userId;
        $id = (int)$this->request->getPost('id');

        if ($id <= 0) {
            session()->setFlashdata('error', 'Invalid whitelist record ID.');
            return redirect()->to('/analysis/behavioral-anomalies');
        }

        $db = \Config\Database::connect();
        $item = $db->table('tbl_user_blocklists')
            ->where('id', $id)
            ->where('owner_id', $userId)
            ->get()->getRowArray();

        if (!$item) {
            session()->setFlashdata('error', 'Whitelist item not found.');
            return redirect()->to('/analysis/behavioral-anomalies');
        }

        // Delete item
        $db->table('tbl_user_blocklists')
            ->where('id', $id)
            ->delete();

        // Log audit trail
        $db->table('tbl_whitelist_logs')->insert([
            'user_id'         => $userId,
            'category'        => $item['category'],
            'identifier'      => $item['identifier'],
            'reason'          => 'Whitelist reversed by user',
            'action_taken_by' => 'User (removed)'
        ]);

        session()->setFlashdata('success', 'Whitelist target "' . esc($item['identifier']) . '" has been removed and restored to analysis.');
        return redirect()->to('/analysis/behavioral-anomalies');
    }

    /**
     * Whitelist and dismiss a behavioral anomaly.
     */
    public function whitelistAnomaly()
    {
        $userId     = $this->userId;
        $category   = $this->request->getPost('category');
        $identifier = trim($this->request->getPost('identifier') ?? '');
        $description = 'Whitelisted from Behavioral Anomalies timeline';

        if (empty($category) || empty($identifier)) {
            session()->setFlashdata('error', 'Invalid whitelist request parameters.');
            return redirect()->to('/analysis/behavioral-anomalies');
        }

        // Ensure blocklist table is active
        $db = \Config\Database::connect();
        if (!$db->tableExists('tbl_user_blocklists')) {
            $db->query("
                CREATE TABLE IF NOT EXISTS `tbl_user_blocklists` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `owner_id` INT(11) UNSIGNED NOT NULL,
                    `category` ENUM('sms','call','notification','app_usage','location') NOT NULL,
                    `identifier` VARCHAR(255) NOT NULL,
                    `description` VARCHAR(255) DEFAULT NULL,
                    `created_at` DATETIME DEFAULT NULL,
                    `updated_at` DATETIME DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `owner_id` (`owner_id`),
                    KEY `category` (`category`),
                    UNIQUE KEY `uq_block` (`owner_id`, `category`, `identifier`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        $modBlocklist = new \App\Models\BlocklistModel();
        if ($modBlocklist->addBlock($userId, $category, $identifier, $description)) {
            session()->setFlashdata('success', 'Anomaly target "' . esc($identifier) . '" has been whitelisted.');
            
            // Insert into whitelist logs
            $db->table('tbl_whitelist_logs')->insert([
                'user_id'         => $userId,
                'category'        => $category,
                'identifier'      => $identifier,
                'reason'          => 'Whitelisted from anomalies timeline',
                'action_taken_by' => 'User'
            ]);

            $logModel = new \App\Models\LogUserActionModel();
            $logModel->logAction([
                'user_id' => $userId,
                'action_category' => 'system',
                'action_type' => 'blocklist_add',
                'action_severity' => 'low',
                'success' => 1,
                'new_values' => json_encode(['category' => $category, 'identifier' => $identifier]),
            ]);
        } else {
            session()->setFlashdata('info', 'This entry was already whitelisted.');
        }

        return redirect()->to('/analysis/behavioral-anomalies');
    }
}

