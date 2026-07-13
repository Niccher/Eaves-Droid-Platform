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
        // If results are already generated / configured, default landing is results page
        if ($this->session->has('anomaly_engine') && $this->session->has('anomaly_algorithms')) {
            return redirect()->to(base_url('analysis/anomalies/results'));
        }

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
        $engine = $this->request->getGet('engine');
        if ($engine) {
            $this->session->set('anomaly_engine', $engine);
        }

        // If no engine in URL or Session, redirect to Step 1
        if (!$this->session->has('anomaly_engine')) {
            return redirect()->to(base_url('analysis/anomalies'));
        }

        $data = $this->baseData();
        $data['categories'] = $this->anomalyModel->getAlgorithmCategories();

        return $this->renderWizardView('analysis/select_algorithms', $data);
    }

    // -------------------------------------------------------------------------
    // Step 3 – Anomaly Results
    // -------------------------------------------------------------------------

    /**
     * Renders the static demo anomaly results table.
     *
     * URL: GET/POST /analysis/anomalies/results
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse Rendered HTML or Redirect
     */
    public function results()
    {
        // If form is submitted via POST, save settings
        if ($this->request->getMethod() === 'post') {
            $algs = $this->request->getPost('algs');
            if ($algs && is_array($algs)) {
                $this->session->set('anomaly_algorithms', $algs);
            }
        }

        // If we are resetting configurations
        if ($this->request->getGet('reset') === 'true') {
            $this->session->remove('anomaly_engine');
            $this->session->remove('anomaly_algorithms');
            return redirect()->to(base_url('analysis/anomalies'));
        }

        // Check if both configurations are in session; if not redirect to step 1
        if (!$this->session->has('anomaly_engine')) {
            return redirect()->to(base_url('analysis/anomalies'));
        }
        if (!$this->session->has('anomaly_algorithms')) {
            $engine = $this->session->get('anomaly_engine');
            return redirect()->to(base_url('analysis/anomalies/algorithms?engine=' . $engine));
        }

        $data = $this->baseData();

        $results             = $this->anomalyModel->getDummyResults();
        $data['results']     = $results;
        $data['severity_map']     = $this->anomalyModel->getSeverityMap();
        $data['severity_counts']  = $this->anomalyModel->getSeverityCounts($results);

        return $this->renderWizardView('analysis/results', $data);
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
