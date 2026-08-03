<?php

namespace App\Controllers\clients;

use App\Models\Mod_Anomalies;

/**
 * Anomalies – Anomaly Detection Wizard Controller
 *
 * Three-step visual wizard (no database writes).
 * All data is sourced from Mod_Anomalies which returns hardcoded
 * demo content. Replace model methods with real queries when
 * integrating a live detection engine.
 *
 * Step 1 → index()      /analysis/anomalies
 * Step 2 → algorithms() /analysis/anomalies/algorithms
 * Step 3 → results()    /analysis/anomalies/results
 */
class Anomalies extends BaseClientController
{
    /** @var Mod_Anomalies */
    protected Mod_Anomalies $anomalyModel;

    /**
     * {@inheritdoc}
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->anomalyModel = new Mod_Anomalies();
    }

    // -------------------------------------------------------------------------
    // Step 1 – Info & Engine Selection
    // -------------------------------------------------------------------------

    /**
     * Renders the wizard landing page: tool info and engine radio group.
     *
     * URL: GET /analysis/anomalies
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse Rendered HTML or Redirect
     */
    public function index()
    {
        $adminSettings = $this->anomalyModel->getAdminAnomalySettings();

        // Check if user has a last completed job — redirect to its results
        $lastJob = $this->anomalyModel->getUserLastCompletedJob($this->userId);
        if ($lastJob) {
            $this->session->set('anomaly_engine', $lastJob['engine']);
            $algs = json_decode($lastJob['algorithms'] ?? '[]', true);
            $this->session->set('anomaly_algorithms', $algs);
            return redirect()->to(base_url('analysis/anomalies/results?job_id=' . $lastJob['id']));
        }

        // If admin has locked the engine, skip selection — go to algorithms
        if ($adminSettings['default_engine'] !== 'both') {
            return redirect()->to(base_url('analysis/anomalies/algorithms'));
        }

        // If already configured, go straight to results
        if ($this->session->has('anomaly_engine') && $this->session->has('anomaly_algorithms')) {
            return redirect()->to(base_url('analysis/anomalies/results'));
        }

        // Show engine selection page so the user can choose
        $data = $this->baseData();
        $data['engines'] = $this->anomalyModel->getEngines();

        return $this->renderWizardView('analysis/info', $data);
    }

    // -------------------------------------------------------------------------
    // Step 2 – Algorithm Selection
    // -------------------------------------------------------------------------

    /**
     * Renders per-category algorithm selection.
     *
     * URL: GET /analysis/anomalies/algorithms
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse Rendered HTML or Redirect
     */
    public function algorithms()
    {
        $adminSettings = $this->anomalyModel->getAdminAnomalySettings();

        // Resolve effective engine: admin config takes precedence
        if ($adminSettings['default_engine'] === 'both') {
            $engine = $this->session->get('anomaly_engine') ?? 'php';
        } else {
            $engine = $adminSettings['default_engine'];
        }
        $this->session->set('anomaly_engine', $engine);

        $categories = $this->anomalyModel->getAlgorithmCategories();

        // Filter algorithms by admin config
        if ($adminSettings['allowed_algorithms'] !== null) {
            $categories = $this->anomalyModel->filterAllowedAlgorithms(
                $categories,
                $adminSettings['allowed_algorithms']
            );
        }

        $data = $this->baseData();
        $data['categories'] = $categories;
        $data['engine'] = $engine;

        return $this->renderWizardView('analysis/select_algorithms', $data);
    }

    // -------------------------------------------------------------------------
    // Step 3 – Start Detection (non-blocking with progress)
    // -------------------------------------------------------------------------

    /**
     * Creates a detection job and redirects to the progress page.
     *
     * URL: GET|POST /analysis/anomalies/run
     */
    public function run()
    {
        // ── Handle POST: algorithm selection form submitted ──
        if ($this->request->getMethod() === 'post') {
            $algs = $this->request->getPost('algs');
            if ($algs && is_array($algs)) {
                $this->session->set('anomaly_algorithms', $algs);
            }
        }

        // ── Handle reset: clear session, reconfigure with PHP defaults ──
        if ($this->request->getGet('reset') === 'true') {
            $this->session->remove('anomaly_engine');
            $this->session->remove('anomaly_algorithms');
            $this->session->set('anomaly_engine', 'php');
            $this->session->set('anomaly_algorithms', $this->anomalyModel->getRandomPhpAlgorithms());
            return redirect()->to(base_url('analysis/anomalies/run'));
        }

        // ── Handle skip: auto-configure with PHP defaults (from landing page) ──
        if ($this->request->getGet('skip') === '1') {
            $this->session->set('anomaly_engine', 'php');
            $this->session->set('anomaly_algorithms', $this->anomalyModel->getRandomPhpAlgorithms());
        }

        // ── Configure from session ──
        $this->ensureConfigured();

        $selectedEngine = $this->session->get('anomaly_engine') ?? 'php';
        $selectedAlgs   = $this->session->get('anomaly_algorithms') ?? [];

        $adminSettings = $this->anomalyModel->getAdminAnomalySettings();
        $selectedAlgs = $this->filterAlgs($selectedAlgs, $adminSettings);

        if ($adminSettings['default_engine'] !== 'both') {
            $selectedEngine = $adminSettings['default_engine'];
            $this->session->set('anomaly_engine', $selectedEngine);
        }

        $scope = $this->request->getGet('scope') ?? 'full';

        // Count total algorithms
        $algCount = 0;
        foreach ($selectedAlgs as $algList) {
            $algCount += count((array)$algList);
        }

        // Create ml_jobs row
        $allAlgIds = [];
        foreach ($selectedAlgs as $algList) {
            foreach ((array)$algList as $aid) {
                $allAlgIds[] = $aid;
            }
        }

        $jobId = $this->anomalyModel->createJob(
            $this->userId,
            $selectedEngine,
            $allAlgIds,
            $scope,
            $algCount
        );

        // Store job info in session for the results page
        $this->session->set('anomaly_job_id', $jobId);
        $this->session->set('anomaly_scope', $scope);

        return redirect()->to(base_url("analysis/anomalies/progress/{$jobId}"));
    }

    /**
     * Progress page — polls /analysis/anomalies/status/{jobId} every 2s.
     *
     * URL: GET /analysis/anomalies/progress/{jobId}
     */
    public function progress(int $jobId)
    {
        $job = $this->anomalyModel->getJob($jobId);
        if (!$job) {
            return redirect()->to(base_url('analysis/anomalies'))
                ->with('error', 'Job not found.');
        }

        // If job is already done, skip progress and go straight to results
        if ($job['status'] === 'completed' || $job['status'] === 'failed') {
            return redirect()->to(base_url("analysis/anomalies/results?job_id={$jobId}"));
        }

        $data = $this->baseData();
        $data['job'] = $job;

        return $this->renderWizardView('analysis/progress', $data);
    }

    /**
     * AJAX status endpoint — returns job progress as JSON.
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
            'status'             => $job['status'],
            'progress_pct'       => (int)($job['progress_pct'] ?? 0),
            'current_algorithm'  => $job['current_algorithm'] ?? '',
            'completed_algorithms' => (int)($job['completed_algorithms'] ?? 0),
            'total_algorithms'   => (int)($job['total_algorithms'] ?? 0),
            'error_message'      => $job['error_message'] ?? null,
        ]);
    }

    /**
     * Kicks off detection in the background (called by progress page JS).
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
        $selectedAlgs   = $this->session->get('anomaly_algorithms') ?? [];
        $adminSettings = $this->anomalyModel->getAdminAnomalySettings();
        $selectedAlgs = $this->filterAlgs($selectedAlgs, $adminSettings);

        $scope = $this->session->get('anomaly_scope') ?? 'full';

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
    // Step 4 – Anomaly Results (reads cached or runs if needed)
    // -------------------------------------------------------------------------

    /**
     * Shows detection results. If job exists and is completed, reads from DB.
     *
     * URL: GET /analysis/anomalies/results?job_id=X
     */
    public function results()
    {
        $jobId = $this->request->getGet('job_id')
              ?? $this->session->get('anomaly_job_id');

        // ── Handle old-style direct access (no job) — redirect to run ──
        if (!$jobId) {
            return redirect()->to(base_url('analysis/anomalies/run'));
        }

        $job = $this->anomalyModel->getJob($jobId);
        if (!$job || $job['status'] === 'running' || $job['status'] === 'pending') {
            return redirect()->to(base_url("analysis/anomalies/progress/{$jobId}"));
        }

        $selectedEngine = $this->session->get('anomaly_engine') ?? 'php';

        // Fetch results from ml_results table
        $results = [];
        if ($job['status'] === 'completed') {
            $results = $this->anomalyModel->fetchJobResults($jobId, $this->userId);
        }

        $engineLabels = [
            'php'    => ['label' => 'PHP Engine',    'icon' => 'fab fa-php',    'badge' => 'primary'],
            'python' => ['label' => 'Python Engine',  'icon' => 'fab fa-python', 'badge' => 'warning'],
        ];
        $engineMeta = $engineLabels[$selectedEngine] ?? $engineLabels['php'];

        $data = $this->baseData();
        $data['results']         = $results;
        $data['selected_engine'] = $selectedEngine;
        $data['engine_meta']     = $engineMeta;
        $data['severity_map']    = $this->anomalyModel->getSeverityMap();
        $data['severity_counts'] = $this->anomalyModel->getSeverityCounts($results);
        $data['selected_algs']   = $this->session->get('anomaly_algorithms') ?? [];
        $data['analysis_counts'] = $this->anomalyModel->getAnalysisCounts($this->userId);
        $data['scope']           = $job['scope'] ?? 'full';
        $data['job']             = $job;

        return $this->renderWizardView('analysis/results', $data);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Ensure engine and algorithms are configured in session.
     */
    private function ensureConfigured(): void
    {
        if (!$this->session->has('anomaly_engine')) {
            $this->session->set('anomaly_engine', 'php');
        }
        if (!$this->session->has('anomaly_algorithms')) {
            $this->session->set('anomaly_algorithms', $this->anomalyModel->getRandomPhpAlgorithms());
        }
    }

    /**
     * Filter algorithms against admin allowed list.
     */
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

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Builds the common data array shared by all three wizard steps.
     *
     * @return array
     */
    private function baseData(): array
    {
        return array_merge(
            ['user_info' => $this->finderModel->basic_user()],
            $this->getUserDataCounts(),
            $this->getDeviceViewData(),
            [
                'pag'     => 'intelligence',
                'sub_pag' => 'anomalies',
            ]
        );
    }

    /**
     * Wraps a view with the standard users layout (header + sidebar + footer).
     *
     * @param  string $mainView  View path relative to app/Views/
     * @param  array  $data      Data to pass to all view partials
     * @return string
     */
    private function renderWizardView(string $mainView, array $data): string
    {
        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($mainView, $data)
            . view('headers_footers/footer_users', $data);
    }
}
