<?php

namespace App\Controllers\clients;

use App\Models\AnomaliesModel;

/**
 * AnomaliesController – Simplified Anomaly Detection Controller
 *
 * Single-page experience at /analysis/anomalies/results.
 * All algorithms are auto-selected based on admin config and user plan tier.
 * No multi-step wizard — users only see the results page.
 *
 * Entry points:
 *   GET  /analysis/anomalies/results         → Single page (empty/scanning/results)
 *   POST /analysis/anomalies/start           → AJAX: create job + kick detection
 *   GET  /analysis/anomalies/status/{jobId}  → AJAX: job progress JSON
 *   POST /analysis/anomalies/process/{jobId} → AJAX: kick Python detection
 *
 * Legacy redirects to /results:
 *   GET /analysis/anomalies
 *   GET /analysis/anomalies/algorithms
 *   GET /analysis/anomalies/run
 *   GET /analysis/anomalies/progress/{jobId}
 */
class AnomaliesController extends BaseClientController
{
    /** @var AnomaliesModel */
    protected AnomaliesModel $anomalyModel;

    /**
     * {@inheritdoc}
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->anomalyModel = new AnomaliesModel();
    }

    // -------------------------------------------------------------------------
    // Legacy redirects — wizard pages no longer exist
    // -------------------------------------------------------------------------

    /** URL: GET /analysis/anomalies */
    public function index()
    {
        return redirect()->to(base_url('analysis/anomalies/results'));
    }

    /** URL: GET /analysis/anomalies/algorithms */
    public function algorithms()
    {
        return redirect()->to(base_url('analysis/anomalies/results'));
    }

    /** URL: GET|POST /analysis/anomalies/run */
    public function run()
    {
        return redirect()->to(base_url('analysis/anomalies/results'));
    }

    /**
     * URL: GET /analysis/anomalies/progress/{jobId}
     * Redirect all progress page hits to the results page which now handles
     * inline progress tracking.
     */
    public function progress(int $jobId)
    {
        return redirect()->to(base_url("analysis/anomalies/results?job_id={$jobId}"));
    }

    // -------------------------------------------------------------------------
    // AJAX: Start a new scan
    // -------------------------------------------------------------------------

    /**
     * Creates a detection job and kicks background processing.
     * Called via AJAX POST from the results page.
     *
     * URL: POST /analysis/anomalies/start
     * Body param: scope = 'full' | 'incremental'
     */
    public function startScan()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Method not allowed']);
        }

        $adminSettings = $this->anomalyModel->getAdminAnomalySettings();
        $engine = $adminSettings['default_engine'];
        if ($engine === 'both') {
            // When admin allows both, prefer PHP for faster startup
            $engine = 'php';
        }

        $scope = $this->request->getPost('scope') ?? 'full';

        // For incremental scans, find the previous completion timestamp
        $incrementalSince = null;
        if ($scope === 'incremental') {
            $lastJob = $this->anomalyModel->getUserLastCompletedJob($this->userId);
            if ($lastJob && !empty($lastJob['completed_at'])) {
                $incrementalSince = $lastJob['completed_at'];
            } else {
                // No prior job — fall back to a full scan
                $scope = 'full';
            }
        }

        // Build algorithm list from all categories, filtered by plan tier
        $categories = $this->anomalyModel->getAlgorithmCategories();

        // Apply admin whitelist
        if ($adminSettings['allowed_algorithms'] !== null) {
            $categories = $this->anomalyModel->filterAllowedAlgorithms(
                $categories,
                $adminSettings['allowed_algorithms']
            );
        }

        // Apply plan tier filter
        $categories = $this->filterByPlan($categories);

        if (empty($categories)) {
            $gate = new \App\Services\PlanGate();
            return $this->response->setJSON([
                'error'       => 'No algorithms are available for your current plan.',
                'upgrade_url' => base_url('analysis/anomalies/advanced'),
            ])->setStatusCode(422);
        }

        // Collect all algorithm IDs, respecting engine compatibility
        $allAlgIds = [];
        foreach ($categories as $cat) {
            foreach ($cat['algorithms'] as $alg) {
                $compat = $alg['compat'] ?? 'both';
                // PHP engine: skip Python-only algorithms
                if ($engine === 'php' && $compat === 'python') {
                    continue;
                }
                $allAlgIds[] = $alg['id'];
            }
        }

        if (empty($allAlgIds)) {
            return $this->response->setJSON([
                'error' => 'No compatible algorithms available for this engine.',
            ])->setStatusCode(422);
        }

        // Create the ml_jobs row
        $jobId = $this->anomalyModel->createJob(
            $this->userId,
            $engine,
            $allAlgIds,
            $scope,
            count($allAlgIds)
        );

        // Persist in session for process() / results() compatibility
        $this->session->set('anomaly_engine',     $engine);
        $this->session->set('anomaly_algorithms', $allAlgIds);
        $this->session->set('anomaly_job_id',     $jobId);
        $this->session->set('anomaly_scope',      $scope);

        // Kick the background job
        if ($engine === 'php') {
            $sparkPath = ROOTPATH . 'spark';
            $cmd = "php {$sparkPath} anomalies:run-job {$jobId}";
            exec($cmd . ' > /dev/null 2>&1 &');
            $mode = 'background';
        } else {
            // Python engine: client must POST to /process/{jobId}
            $mode = 'process_needed';
        }

        return $this->response->setJSON([
            'job_id'    => $jobId,
            'status'    => 'started',
            'engine'    => $engine,
            'scope'     => $scope,
            'alg_count' => count($allAlgIds),
            'mode'      => $mode,
        ]);
    }

    // -------------------------------------------------------------------------
    // AJAX: Job status polling
    // -------------------------------------------------------------------------

    /**
     * Returns the current job progress as JSON.
     *
     * URL: GET /analysis/anomalies/status/{jobId}
     */
    public function status(int $jobId)
    {
        $job = $this->anomalyModel->getJob($jobId);
        if (!$job) {
            return $this->response->setJSON(['error' => 'Job not found']);
        }

        return $this->response->setJSON([
            'status'               => $job['status'],
            'progress_pct'         => (int)($job['progress_pct'] ?? 0),
            'current_algorithm'    => $job['current_algorithm'] ?? '',
            'completed_algorithms' => (int)($job['completed_algorithms'] ?? 0),
            'total_algorithms'     => (int)($job['total_algorithms'] ?? 0),
            'error_message'        => $job['error_message'] ?? null,
        ]);
    }

    // -------------------------------------------------------------------------
    // AJAX: Kick Python detection (unchanged from original)
    // -------------------------------------------------------------------------

    /**
     * Kicks off Python detection synchronously or PHP spark in background.
     *
     * URL: POST /analysis/anomalies/process/{jobId}
     */
    public function process(int $jobId)
    {
        $job = $this->anomalyModel->getJob($jobId);
        if (!$job || $job['status'] !== 'running') {
            return $this->response->setJSON(['error' => 'Job not ready']);
        }

        $selectedEngine = $this->session->get('anomaly_engine') ?? 'php';

        if ($selectedEngine === 'php') {
            $sparkPath = ROOTPATH . 'spark';
            $cmd = "php {$sparkPath} anomalies:run-job {$jobId}";
            exec($cmd . ' > /dev/null 2>&1 &');
            return $this->response->setJSON(['status' => 'started', 'mode' => 'background']);
        }

        // Python detection runs synchronously
        $selectedAlgs  = $this->session->get('anomaly_algorithms') ?? [];
        $adminSettings = $this->anomalyModel->getAdminAnomalySettings();
        $selectedAlgs  = $this->filterAlgs($selectedAlgs, $adminSettings);
        $scope         = $this->session->get('anomaly_scope') ?? 'full';

        try {
            $results = $this->anomalyModel->runPythonDetection($selectedAlgs, $this->userId, $scope, $jobId);
            $this->anomalyModel->saveResults($jobId, $this->userId, $results);
            $this->anomalyModel->completeJob($jobId);
        } catch (\Throwable $e) {
            $this->anomalyModel->completeJob($jobId, $e->getMessage());
        }

        return $this->response->setJSON(['status' => 'completed']);
    }

    // -------------------------------------------------------------------------
    // Main page: single results entry point
    // -------------------------------------------------------------------------

    /**
     * The single anomaly scanner page.
     * Handles three states: empty, scanning, and results.
     *
     * URL: GET /analysis/anomalies/results
     */
    public function results()
    {
        $gate    = new \App\Services\PlanGate();
        $planKey = $gate->currentPlanKey($this->userId);

        // Plan gating
        $allowedIds = $gate->allowedAlgorithmIds($this->userId, $this->anomalyModel->getAlgorithmTiers());
        if (empty($allowedIds)) {
            return $this->renderUpgrade(
                'Anomaly Detection',
                $gate->upgradePlansForFeature($this->userId, 'risk_score'),
                base_url('analysis/anomalies/results')
            );
        }

        $canSeeAdvanced = $gate->hasFeature($this->userId, 'correlation')
                       || in_array($planKey, ['platinum'], true);

        $data = $this->baseData();
        $data['current_plan']         = $planKey;
        $data['can_see_advanced']     = $canSeeAdvanced;
        $data['advanced_upgrade_url'] = base_url('analysis/anomalies/advanced');
        $data['start_url']            = base_url('analysis/anomalies/start');
        $data['status_url_base']      = base_url('analysis/anomalies/status');
        $data['process_url_base']     = base_url('analysis/anomalies/process');
        $data['results_base_url']     = base_url('analysis/anomalies/results');

        // ── Detect an in-progress job ──
        $runningJob = $this->anomalyModel->getRunningJobForUser($this->userId);
        $data['is_scanning']   = !empty($runningJob);
        $data['active_job_id'] = $runningJob ? (int)$runningJob['id'] : null;
        $data['active_engine'] = $runningJob ? ($runningJob['engine'] ?? 'php') : null;

        // ── Scan history (most recent 6 scans) ──
        $data['recent_jobs'] = $this->anomalyModel->getRecentJobsForUser($this->userId, 6);

        // ── Resolve which completed job's results to show ──
        $jobId = null;
        if (!$runningJob) {
            // Prefer explicit ?job_id query param, then session fallback
            $jobId = $this->request->getGet('job_id')
                  ?? $this->session->get('anomaly_job_id');
        } else {
            // While scanning, show the previous completed job's results underneath
            $lastCompleted = $this->anomalyModel->getUserLastCompletedJob($this->userId);
            if ($lastCompleted) {
                $jobId = $lastCompleted['id'];
            }
        }

        $job = $jobId ? $this->anomalyModel->getJob((int)$jobId) : null;

        // Only treat as a completed report if actually completed
        $data['has_report'] = false;
        $data['results']    = [];
        $data['threat_data'] = null;
        $data['job']        = null;
        $data['scope']      = 'full';

        if ($job && $job['status'] === 'completed') {
            $results = $this->anomalyModel->fetchJobResults((int)$jobId, $this->userId);
            $data['has_report']  = true;
            $data['results']     = $results;
            $data['threat_data'] = $this->computeThreatSummary($results);
            $data['job']         = $job;
            $data['scope']       = $job['scope'] ?? 'full';
        }

        $data['severity_map']    = $this->anomalyModel->getSeverityMap();
        $data['analysis_counts'] = $this->anomalyModel->getAnalysisCounts($this->userId);

        return $this->renderWizardView('analysis/results', $data);
    }

    /**
     * URL: GET /analysis/anomalies/advanced
     */
    public function upgradeAdvanced()
    {
        $gate    = new \App\Services\PlanGate();
        $planKey = $gate->currentPlanKey($this->userId);

        if ($planKey === 'platinum') {
            return redirect()->to(base_url('analysis/anomalies/results'));
        }

        return $this->renderUpgrade(
            'AdvancedController Anomaly Algorithms',
            ['platinum'],
            base_url('analysis/anomalies/results')
        );
    }

    // -------------------------------------------------------------------------
    // Private: threat summary
    // -------------------------------------------------------------------------

    /**
     * Aggregates raw ml_results rows into a simplified threat summary.
     *
     * @param  array $results  Rows from ml_results (each has 'severity', 'category', etc.)
     * @return array
     */
    private function computeThreatSummary(array $results): array
    {
        $highCount   = 0;
        $mediumCount = 0;
        $lowCount    = 0;
        $categories  = [];
        $sevRank     = ['Low' => 0, 'Medium' => 1, 'High' => 2];

        foreach ($results as $row) {
            $sev = $row['severity'] ?? 'Low';
            $cat = $row['category'] ?? 'other';

            match ($sev) {
                'High'   => $highCount++,
                'Medium' => $mediumCount++,
                default  => $lowCount++,
            };

            if (!isset($categories[$cat])) {
                $categories[$cat] = ['High' => 0, 'Medium' => 0, 'Low' => 0, 'worst' => 'Low'];
            }
            $categories[$cat][$sev]++;

            // Track the worst severity seen in this category
            if (($sevRank[$sev] ?? 0) > ($sevRank[$categories[$cat]['worst']] ?? 0)) {
                $categories[$cat]['worst'] = $sev;
            }
        }

        $total = $highCount + $mediumCount + $lowCount;
        $raw   = ($highCount * 3) + ($mediumCount * 1.0) + ($lowCount * 0.2);
        $max   = $total > 0 ? $total * 3 : 1;
        $score = min(100, (int)round($raw / $max * 100));

        if ($total === 0) {
            $level = 'none';     $label = 'All Clear';          $color = 'success'; $icon = 'fa-check-circle';
        } elseif ($score <= 25) {
            $level = 'low';      $label = 'Minor Observations'; $color = 'info';    $icon = 'fa-info-circle';
        } elseif ($score <= 55) {
            $level = 'medium';   $label = 'Attention Needed';   $color = 'warning'; $icon = 'fa-exclamation-triangle';
        } elseif ($score <= 79) {
            $level = 'high';     $label = 'Issues Detected';    $color = 'danger';  $icon = 'fa-exclamation-circle';
        } else {
            $level = 'critical'; $label = 'Critical Findings';  $color = 'dark';    $icon = 'fa-skull-crossbones';
        }

        return compact('level', 'label', 'color', 'icon', 'score',
                       'highCount', 'mediumCount', 'lowCount', 'total', 'categories');
    }

    // -------------------------------------------------------------------------
    // Private helpers (preserved from original)
    // -------------------------------------------------------------------------

    private function renderUpgrade(string $featureLabel, array $upgradePlans, string $redirectTo): string
    {
        $gate = new \App\Services\PlanGate();
        return view('errors/custom_errors/subscription_upgrade', [
            'feature'      => $featureLabel,
            'upgradePlans' => $upgradePlans,
            'current_plan' => $gate->currentPlanKey($this->userId),
            'redirect_to'  => $redirectTo,
            'pag'          => 'intelligence',
            'sub_pag'      => 'anomalies',
        ]);
    }

    private function filterAlgs(array $selectedAlgs, array $adminSettings): array
    {
        if ($adminSettings['allowed_algorithms'] === null) {
            return $selectedAlgs;
        }
        foreach ($selectedAlgs as $catKey => $algList) {
            $selectedAlgs[$catKey] = array_values(array_intersect(
                (array)$algList,
                $adminSettings['allowed_algorithms']
            ));
            if (empty($selectedAlgs[$catKey])) {
                unset($selectedAlgs[$catKey]);
            }
        }
        return $selectedAlgs;
    }

    private function filterByPlan(array $categories): array
    {
        try {
            $gate = new \App\Services\PlanGate();
            $allowedIds = $gate->allowedAlgorithmIds($this->userId, $this->anomalyModel->getAlgorithmTiers());
            if (empty($allowedIds)) {
                return [];
            }
            return $this->anomalyModel->filterByPlanAlgorithms($categories, $allowedIds);
        } catch (\Throwable $e) {
            log_message('error', 'PlanGate filterByPlan error: ' . $e->getMessage());
            return [];
        }
    }

    private function intersectWithPlan(array $selectedAlgs): array
    {
        try {
            $gate = new \App\Services\PlanGate();
            $allowedIds = $gate->allowedAlgorithmIds($this->userId, $this->anomalyModel->getAlgorithmTiers());
            if (empty($allowedIds)) {
                return [];
            }
            $allowedSet = array_flip($allowedIds);
            foreach ($selectedAlgs as $catKey => $algList) {
                $selectedAlgs[$catKey] = array_values(array_filter(
                    (array)$algList,
                    fn($aid) => isset($allowedSet[$aid])
                ));
                if (empty($selectedAlgs[$catKey])) {
                    unset($selectedAlgs[$catKey]);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'PlanGate intersectWithPlan error: ' . $e->getMessage());
        }
        return $selectedAlgs;
    }

    private function baseData(): array
    {
        return array_merge(
            ['user_info' => $this->finderModel->basic_user()],
            $this->getUserDataCounts(),
            $this->getDeviceViewData(),
            ['pag' => 'intelligence', 'sub_pag' => 'anomalies']
        );
    }

    private function renderWizardView(string $mainView, array $data): string
    {
        return view('headers_footers/head_users', $data)
             . view('headers_footers/sidebar_users', $data)
             . view($mainView, $data)
             . view('headers_footers/footer_users', $data);
    }
}
