<?php

namespace App\Controllers\clients;



use App\Models\Mod_Extract;
use App\Models\Mod_Finder;
use App\Models\Mod_Crypt;
use App\Models\Mod_Parse_Loot;
use App\Models\Mod_Receive;
use App\Models\Mod_Android;
use App\Models\Mod_User;
use App\Models\Mod_ML_Analyzer;
use App\Models\Mod_Anomalies;

class Correlation extends BaseClientController{

    public function index(){
        // Auth check handled in parent
	    $model_crypt = new Mod_Crypt();

	    $pg = 'correlation';
	    $data['pag'] = 'analysis';
	    $data["user_info"] = $this->finderModel->basic_user();
	    
        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        // Analysis Stats
        $data['sms_analysis'] = $this->finderModel->get_categorized_sms_counts($this->userId);
        $data['call_analysis'] = $this->finderModel->get_categorized_call_counts($this->userId);

        // Map data for view variables
        $data['totalAnalyzedSMS'] = $data['sms_analysis']['total'];
        $data['financialAlerts'] = $data['sms_analysis']['financial'];
        $data['suspiciousCalls'] = $data['call_analysis']['spam'];
        $data['newContacts'] = $data['call_analysis']['new'];

        $data['financialSMS'] = $data['sms_analysis']['financial'];
        $data['promotionalSMS'] = $data['sms_analysis']['promo'];
        $data['maliciousSMS'] = $data['sms_analysis']['malicious'];
        $data['otpSMS'] = $data['sms_analysis']['otp'];
        $data['utilitySMS'] = $data['sms_analysis']['utility'];
        $data['serviceSMS'] = $data['sms_analysis']['service'];
        $data['personalSMS'] = $data['sms_analysis']['personal'];

        $data['familyCalls'] = $data['call_analysis']['family'];
        $data['newCalls'] = $data['call_analysis']['new'];
        $data['businessCalls'] = $data['call_analysis']['business'];
        $data['spamCalls'] = $data['call_analysis']['spam'];
        $data['intlCalls'] = $data['call_analysis']['intl'];
        $data['urgentCalls'] = $data['call_analysis']['urgent'];

        // Analysis results passed from data merge

	    return view('headers_footers/head_users')
		    . view('headers_footers/sidebar_users', $data)
		    . view('users/correlation/'.$pg, $data)
		    . view('headers_footers/footer_users');
    }

    public function sms_finance(){
        // Auth check handled in parent
        //$model_finder = new Mod_Finder();
        $model_crypt = new Mod_Crypt();
        $model_extract = new Mod_Extract();
        //$encrypter = \Config\Services::encrypter();

        $pg = 'correlation';
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();

        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        $sms_dump = $this->finderModel->get_sms($data["user_info"]['id']);
        $sms_sender_list = array();
        $sms_parserable = array();
        $sms_good_sms = array();

        foreach ($sms_dump as $sms) {
            array_push($sms_sender_list,$sms['sms_number']);
        }
        asort($sms_sender_list);

        $sms_senders = array_values(array_unique($sms_sender_list));

        $list = '<label>Select new points.</label>
        <select class="form-control source_sms select2-hidden-accessible" multiple="" data-placeholder="Select a Contact to monitor" style="width: 100%;" tabindex="-1" aria-hidden="true">';

        foreach ($sms_senders as $sms_point) {
            $valu = $model_crypt->base64url_encode($sms_point);
            $list.='<option value="'.$valu.'" >'.$sms_point.'</option>';
        }
        $list .= '        
        </select>';

        $data['sms_data_points'] = $list;

        $sms_finance_points = $this->finderModel->get_points_sms_finance($data["user_info"]['id']);

        $list_finance = '
        <select class="form-control source_sms_finance select2-hidden-accessible" data-placeholder="Select a Contact to monitor" style="width: 100%;" tabindex="-1" aria-hidden="true">';

        foreach ($sms_finance_points as $sms_finance) {
            $valu = $model_crypt->base64url_encode($sms_finance["point_Name"]);
            $list_finance.='<option value="'.$valu.'" >'.$sms_finance["point_Name"].'</option>';
            array_push($sms_parserable,$sms_finance["point_Name"]);
        }
        $list_finance .= '        
        </select>';
        $data['sms_data_points_source'] = $list_finance;

        if (!empty($sms_parserable)){
            $sms_good_sms = $this->finderModel->get_sms_from_sender($data["user_info"]['id'] , $sms_parserable);
        }
        $data['sms_good_sms'] = $sms_good_sms;

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/sms_finance', $data)
            . view('headers_footers/tail_analyze_sms', $data);
    }

    public function set_sms_datapoints($owner){
        //$model_finder = new Mod_Finder();

        // if (!auth()->loggedIn()){ return redirect()->to('login'); } // Handled in parent

        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        $dated = date('Y-m-d H:i:s');

        $post_point = explode(',', str_replace('"',"",$_POST['points']) );
        foreach ( $post_point as $item) {
            $data_point = array(
                "point_Owner" => $data["user_info"]['id'],
                "point_Name" =>  base64_decode(urldecode($item)),
                "point_Inserted" => $dated,
            );
            $this->finderModel->set_sms_points_to_analyze_finance($data_point);
        }
    }

    public function sms_analyze_finance_from($source){
        //$model_finder = new Mod_Finder();
        $model_crypt = new Mod_Crypt();
        $model_extract = new Mod_Extract();
        //$encrypter = \Config\Services::encrypter();
        // if (!auth()->loggedIn()){ return redirect()->to('login'); } // Handled in parent

        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();

        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        $source_clean = $model_crypt->base64url_decode($source);

        $data["page_info_url"] = $source_clean;

        $data['sms_good_sms'] = $model_extract->get_sms_from($data["user_info"]['id'], $source_clean);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/sms_finance_single_view', $data)
            . view('headers_footers/tail_analyze_sms', $data);

    }

    public function sms_analysis()
    {
        $category = $this->request->getGet('category') ?? 'financial';
        $perPage = 20;
        $page = $this->request->getGet('page') ?? 1;
        
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        $results = $this->finderModel->get_categorized_sms($this->userId, $category, $perPage, (int)$page);
        
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

    public function call_analysis()
    {
        $category = $this->request->getGet('category') ?? 'family';
        $perPage = 20;
        $page = $this->request->getGet('page') ?? 1;
        
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        $results = $this->finderModel->get_categorized_calls($this->userId, $category, $perPage, (int)$page);

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
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts, $this->getDeviceViewData());

        // Financial Intelligence data
        $transactions = $this->finderModel->get_financial_transactions($this->userId);
        $spendingByMonth = [];
        $totalSpending = 0;
        
        foreach ($transactions as $tx) {
            if ($tx['type'] !== 'income') {
                $month = $tx['month'];
                if (!isset($spendingByMonth[$month])) $spendingByMonth[$month] = 0;
                $spendingByMonth[$month] += $tx['amount'];
                $totalSpending += $tx['amount'];
            }
        }
        
        $data['financial_summary'] = [
            'transactions' => array_slice($transactions, 0, 10),
            'spendingByMonth' => $spendingByMonth,
            'totalSpending' => $totalSpending
        ];
        $data['ml_insight_finance'] = Mod_ML_Analyzer::analyzeFinance($transactions);

        $data['sms_analysis'] = $this->finderModel->get_categorized_sms_counts($this->userId);
        $data['call_analysis'] = $this->finderModel->get_categorized_call_counts($this->userId);

        return $this->renderAppView('users/correlation/advanced_analysis', $data);
    }

    public function refresh_ml()
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

        Mod_ML_Analyzer::analyzeFinance($transactions);
        Mod_ML_Analyzer::analyzeSocial($contacts);
        Mod_ML_Analyzer::analyzeMobility($mobility);
        Mod_ML_Analyzer::analyzePrivacy($audit);
        Mod_ML_Analyzer::analyzeSubscriptions($forecast);
        Mod_ML_Analyzer::analyzeSentimentML($sentiment);
        Mod_ML_Analyzer::analyzeHotspots($clusters);
        Mod_ML_Analyzer::analyzeStorage($storage);
        Mod_ML_Analyzer::analyzeApps($categories);

        session()->setFlashdata('success', 'PHP-ML analysis refreshed successfully!');

        return redirect()->to(base_url('analysis'));
    }

    public function finance_analysis()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        // Financial Intelligence data
        $transactions = $this->finderModel->get_financial_transactions($this->userId);
        
        // Extract unique senders for the filter
        $senders = [];
        foreach ($transactions as $tx) {
            $senders[] = $tx['sender'];
        }
        $data['senders'] = array_unique($senders);
        asort($data['senders']);

        // Handle filtering
        $selectedSender = $this->request->getGet('sender');
        $data['selected_sender'] = $selectedSender;

        if ($selectedSender) {
            $transactions = array_filter($transactions, function($tx) use ($selectedSender) {
                return $tx['sender'] === $selectedSender;
            });
        }

        // Group by month and type for charts
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
                if (!isset($incomeByMonth[$month])) $incomeByMonth[$month] = 0;
                $incomeByMonth[$month] += $tx['amount'];
            } else {
                if (!isset($spendingByMonth[$month])) $spendingByMonth[$month] = 0;
                $spendingByMonth[$month] += $tx['amount'];
                
                if (!isset($spendingByType[$tx['type']])) $spendingByType[$tx['type']] = 0;
                $spendingByType[$tx['type']] += $tx['amount'];
                
                $totalSpending += $tx['amount'];
            }
        }

        $data['financial_data'] = [
            'transactions' => $transactions,
            'spendingByMonth' => $spendingByMonth,
            'spendingByType' => $spendingByType,
            'incomeByMonth' => $incomeByMonth,
            'totalSpending' => $totalSpending
        ];
        $data['ml_insight'] = Mod_ML_Analyzer::analyzeFinance($data['financial_data']['transactions']);
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['sms']);

        // Pagination
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 15;
        $total = count($transactions);
        $offset = ($page - 1) * $perPage;
        
        // Slice for table view
        $data['financial_data']['transactions'] = array_slice($transactions, $offset, $perPage);
        
        $pager = \Config\Services::pager();
        $data['pager_links'] = $pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
        $data['currentPage'] = $page;
        $data['perPage'] = $perPage;
        $data['total'] = $total;

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/finance_analysis', $data)
            . view('headers_footers/footer_users');
    }

    public function location_analysis()
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
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['locations']);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/location_analysis', $data)
            . view('headers_footers/footer_users');
    }

    public function device_pulse()
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

    public function social_analysis()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        $allSocial = $this->finderModel->get_social_graph($this->userId);
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 15;
        $total = count($allSocial);
        
        $pager = \Config\Services::pager();
        $data['social_graph'] = array_slice($allSocial, ($page - 1) * $perPage, $perPage);
        $data['pager_links'] = $pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
        $data['currentPage'] = $page;
        $data['perPage'] = $perPage;
        $data['total'] = $total;
        $data['ml_insight'] = Mod_ML_Analyzer::analyzeSocial($data['social_graph']);
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['contacts']);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/social_analysis', $data)
            . view('headers_footers/footer_users');
    }

    public function generate_report()
    {
        // Disable debug toolbar to prevent HTML injection into PDF binary
        if (ENVIRONMENT !== 'production') {
            service('toolbar')->respond();
        }

        // Fetch data
        $data['user_info'] = $this->finderModel->basic_user();
        $data['counts'] = $this->getUserDataCounts();

        // Detailed Communication Stats
        $data['sms_analysis'] = $this->finderModel->get_categorized_sms_counts($this->userId);
        $data['call_analysis'] = $this->finderModel->get_categorized_call_counts($this->userId);

        // Financial Intelligence data (Expanded)
        $transactions = $this->finderModel->get_financial_transactions($this->userId);
        $totalSpending = 0;
        foreach ($transactions as $tx) {
            if ($tx['type'] !== 'income') $totalSpending += $tx['amount'];
        }
        $data['financial_summary'] = [
            'total_spending' => $totalSpending,
            'tx_count' => count($transactions),
            'recent_tx' => array_slice($transactions, 0, 15) // Show 15 instead of 5
        ];

        // Social & Contacts
        $socialGraph = $this->finderModel->get_social_graph($this->userId, 20);
        $data['social_graph'] = array_slice($socialGraph, 0, 10);
        $data['device'] = $this->finderModel->get_device_health($this->userId);

        // Apps Intelligence (New)
        $apps = $this->finderModel->get_apps($this->userId, 10);
        $data['top_apps'] = array_map(function($app) {
            return [
                'name' => $app['Name'],
                'package' => $app['Package'],
                'install_time' => $app['first_install_time']
            ];
        }, $apps);

        // Location History (New)
        $data['recent_locations'] = $this->finderModel->get_locations($this->userId, 5);

        // === PHP-ML Intelligence Data ===
        $mobilityData = $this->finderModel->get_mobility_aggregates($this->userId);
        $auditData = $this->finderModel->get_app_privacy_audit($this->userId);
        $forecastData = $this->finderModel->get_subscription_forecast($this->userId);
        $sentimentData = $this->finderModel->get_sentiment_profile($this->userId);
        $clusterData = $this->finderModel->get_geospatial_clusters($this->userId);
        $categoriesData = $this->finderModel->get_app_category_dist($this->userId);
        $storageData = $this->finderModel->get_storage_forensics($this->userId);

        $data['ml_social']    = Mod_ML_Analyzer::analyzeSocial($socialGraph);
        $data['ml_finance']   = Mod_ML_Analyzer::analyzeFinance($transactions);
        $data['ml_mobility']  = Mod_ML_Analyzer::analyzeMobility($mobilityData);
        $data['ml_privacy']   = Mod_ML_Analyzer::analyzePrivacy($auditData);
        $data['ml_subscript'] = Mod_ML_Analyzer::analyzeSubscriptions($forecastData);
        $data['ml_sentiment'] = Mod_ML_Analyzer::analyzeSentimentML($sentimentData);
        $data['ml_hotspots']  = Mod_ML_Analyzer::analyzeHotspots($clusterData);
        $data['ml_apps']      = Mod_ML_Analyzer::analyzeApps($categoriesData);
        $data['ml_storage']   = Mod_ML_Analyzer::analyzeStorage($storageData);

        // Pass raw data for tables in report
        $data['subscription_data'] = $forecastData;
        $data['cluster_data'] = $clusterData;
        $data['categories_data'] = $categoriesData;
        $data['storage_data'] = $storageData;
        $data['sentiment_profile'] = $sentimentData;

        $data['date'] = date('F j, Y');

        // Clean all strings for UTF-8 and HTML safety
        $data = $this->utf8CleanArray($data);

        // Load view (ensure view file has no BOM/whitespace)
        $html = view('users/correlation/report_template', $data);

        // Generate PDF
        require_once ROOTPATH . 'vendor/autoload.php';
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('defaultFont', 'DejaVu Sans');
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
    public function lifestyle_analysis()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        
        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        $data['mobility'] = $this->finderModel->get_mobility_aggregates($this->userId);
        $data['ml_insight'] = Mod_ML_Analyzer::analyzeMobility($data['mobility']);
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['activity']);
        
        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/lifestyle_analysis', $data)
            . view('headers_footers/footer_users');
    }

    /**
     * Universal Intelligence Timeline.
     */
    public function intelligence_timeline()
    {
        $data['pag']       = 'analysis';
        $data['sub_pag']   = 'timeline';
        $data['user_info'] = $this->finderModel->basic_user();

        $counts = $this->getUserDataCounts();
        $data   = array_merge($data, $counts, $this->getDeviceViewData());

        $perPage = 100;
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

        // Default tab: Advanced when ?type= is a non-basic event type
        $advTypes = ['upload', 'app_usage', 'file', 'keyguard', 'health', 'location', 'activity', 'other'];
        $data['default_tab'] = (in_array($filterType, $advTypes, true)) ? 'advanced' : 'basic';

        // Basic: SMS + Calls only (bounded by plan window)
        $data['basic_timeline'] = $this->finderModel->get_basic_timeline($this->userId, $perPage, $days);

        // Advanced: ALL events (excluding sms/call which belong to Basic tab)
        // We fetch up to 1000 events; client-side filter pills handle the
        // filtering without a page reload. URL ?type= only drives initial state.
        $allAdvanced = $this->finderModel->get_unified_timeline_filtered(
            $this->userId, 'all', 1000, ['sms', 'call'], $days
        );
        $data['advanced_timeline'] = $allAdvanced;
        $data['adv_total'] = count($allAdvanced);
        $data['adv_filter'] = $filterType;
        $data['adv_per_page'] = $perPage;
        $data['adv_page'] = (int)($this->request->getGet('p') ?? 1);

        // Pivot (crisis-mode daily breakdown)
        $data['pivot'] = $this->finderModel->get_timeline_pivot($this->userId, $days);

        return $this->renderAppView('users/correlation/intelligence_timeline', $data);
    }

    /**
     * Privacy & Permission Audit.
     */
    public function privacy_audit()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts());

        $allAudit = $this->finderModel->get_app_privacy_audit($this->userId);
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 15;
        $total = count($allAudit);
        
        $pager = \Config\Services::pager();
        $data['audit'] = array_slice($allAudit, ($page - 1) * $perPage, $perPage);
        $data['audit_pager'] = $pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
        $data['audit_currentPage'] = $page;
        $data['audit_perPage'] = $perPage;
        $data['audit_total'] = $total;
        
        $data['ml_insight'] = Mod_ML_Analyzer::analyzePrivacy($data['audit']);
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['apps', 'device_info']);
        
        $allScams = $this->finderModel->get_scam_sms_audit($this->userId);
        $scamPage = (int) ($this->request->getGet('scam_page') ?? 1);
        $scamPerPage = 15;
        $scamTotal = count($allScams);
        
        $data['scams'] = array_slice($allScams, ($scamPage - 1) * $scamPerPage, $scamPerPage);
        $data['scam_pager'] = $pager->makeLinks($scamPage, $scamPerPage, $scamTotal, 'bootstrap5_full');
        $data['scam_currentPage'] = $scamPage;
        $data['scam_perPage'] = $scamPerPage;
        $data['scam_total'] = $scamTotal;

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/privacy_audit', $data)
            . view('headers_footers/footer_users');
    }

    /**
     * Recurring Bills & Subscription Tracker.
     */
    public function subscription_tracker()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts());

        $data['forecast'] = $this->finderModel->get_subscription_forecast($this->userId);
        $data['ml_insight'] = Mod_ML_Analyzer::analyzeSubscriptions($data['forecast']);
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['sms']);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/subscription_tracker', $data)
            . view('headers_footers/footer_users');
    }

    /**
     * App Portfolio & Categorization.
     */
    public function app_portfolio()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts());

        $data['categories'] = $this->finderModel->get_app_category_dist($this->userId);
        $data['ml_insight'] = Mod_ML_Analyzer::analyzeApps($data['categories']);
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['apps']);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/app_portfolio', $data)
            . view('headers_footers/footer_users');
    }

    /**
     * Media & Storage Intelligence.
     */
    public function storage_intelligence()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts());

        $storage = $this->finderModel->get_storage_forensics($this->userId);
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 15;
        $allFiles = !empty($storage['large_hogs']) ? $storage['large_hogs'] : $storage['top_files'];
        $total = count($allFiles);
        
        $pager = \Config\Services::pager();
        $storage['display_files'] = array_slice($allFiles, ($page - 1) * $perPage, $perPage);
        $data['storage'] = $storage;
        $data['pager_links'] = $pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
        $data['currentPage'] = $page;
        $data['perPage'] = $perPage;
        $data['total'] = $total;
        $data['ml_insight'] = Mod_ML_Analyzer::analyzeStorage($data['storage']);
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['files']);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/storage_intelligence', $data)
            . view('headers_footers/footer_users');
    }

    /**
     * Sentiment & Relationship Health.
     */
    public function sentiment_analysis()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts());

        $allSentiment = $this->finderModel->get_sentiment_profile($this->userId);
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 15;
        $total = count($allSentiment);
        
        $pager = \Config\Services::pager();
        $data['sentiment'] = array_slice($allSentiment, ($page - 1) * $perPage, $perPage);
        $data['pager_links'] = $pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
        $data['currentPage'] = $page;
        $data['perPage'] = $perPage;
        $data['total'] = $total;
        $data['ml_insight'] = Mod_ML_Analyzer::analyzeSentimentML($data['sentiment']);
        $data['anomaly_alerts'] = $this->getAnomalyAlertsForPage(['sms']);

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/correlation/sentiment_analysis', $data)
            . view('headers_footers/footer_users');
    }


    /**
     * Geospatial Hotspot Clustering.
     */
    public function geoclustering_hotspots()
    {
        $data['pag'] = 'analysis';
        $data["user_info"] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts());

        $data['clusters'] = $this->finderModel->get_geospatial_clusters($this->userId);
        $data['ml_insight'] = Mod_ML_Analyzer::analyzeHotspots($data['clusters']);
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
        $model = new Mod_Anomalies();
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
    public function digital_wellbeing()
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

        // Raw heatmap rows → keyed by date string for JS
        $heatmapRaw = $this->finderModel->get_daily_usage_heatmap($this->userId);
        $heatmapData = [];
        foreach ($heatmapRaw as $row) {
            $heatmapData[$row['date']] = (int) round($row['total_time_ms'] / 60000); // ms → minutes
        }

        // Dopamine vs Productivity split
        $split = $this->finderModel->get_dopamine_vs_productivity($this->userId);
        $totalMs = max(1, $split['dopamine_ms'] + $split['productivity_ms'] + $split['other_ms']);
        $data['dopamine_pct']    = round($split['dopamine_ms']    / $totalMs * 100, 1);
        $data['productivity_pct'] = round($split['productivity_ms'] / $totalMs * 100, 1);
        $data['other_pct']       = round($split['other_ms']        / $totalMs * 100, 1);
        $data['dopamine_hrs']    = round($split['dopamine_ms']    / 3600000, 1);
        $data['productivity_hrs'] = round($split['productivity_ms'] / 3600000, 1);
        $data['other_hrs']       = round($split['other_ms']        / 3600000, 1);

        // Top apps
        $topApps = $this->finderModel->get_top_time_sink_apps($this->userId, 7);
        $topAppsLabels = [];
        $topAppsValues = [];
        foreach ($topApps as $app) {
            $topAppsLabels[] = $app['app_name'] ?: $app['package_name'];
            $topAppsValues[] = (int) round($app['total_time_ms'] / 60000); // minutes
        }

        $data['heatmap_json']       = json_encode($heatmapData);
        $data['top_apps_labels']    = json_encode($topAppsLabels);
        $data['top_apps_values']    = json_encode($topAppsValues);
        $data['has_data']           = !empty($heatmapRaw);

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

        return $this->renderAppView('users/correlation/digital_wellbeing', $data);
    }

    /**
     * Behavioral Anomaly & Pattern-of-Life Analysis.
     */
    public function behavioral_anomalies()
    {
        $data['pag']       = 'intelligence';
        $data['sub_pag']   = 'behavioral';
        $data['user_info'] = $this->finderModel->basic_user();
        $data = array_merge($data, $this->getUserDataCounts(), $this->getDeviceViewData());

        $data['anomalies'] = $this->finderModel->get_behavioral_anomalies($this->userId);

        return $this->renderAppView('users/correlation/behavioral_anomalies', $data);
    }
}
