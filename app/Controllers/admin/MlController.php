<?php

namespace App\Controllers\admin;

use App\Models\AnomaliesModel;

class MlController extends BaseAdminController
{
    public function index($tab = 'general')
    {
        $validTabs = ['general', 'overview', 'engines', 'algorithms', 'python', 'phpml', 'history'];
        $getTab = $this->request->getGet('tab');
        if (!empty($getTab) && in_array($getTab, $validTabs)) {
            $tab = $getTab;
        }

        if (!in_array($tab, $validTabs)) {
            $tab = 'general';
        }
        if ($tab === 'overview') {
            $tab = 'general';
        }

        $db = $this->getDb();

        $saved = [];
        $rows = $db->table('settings')->where('class', 'ml')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        $anomalySaved = [];
        $anomalyRows = $db->table('settings')->where('class', 'anomaly')->get()->getResultArray();
        foreach ($anomalyRows as $ar) {
            $anomalySaved[$ar['key']] = $ar['value'];
        }

        $defaultEngine = $anomalySaved['default_engine'] ?? 'php';
        $allowedRaw = $anomalySaved['allowed_algorithms'] ?? '';

        $model = new AnomaliesModel();
        $pythonSettings = $model->getPythonSettings();
        $dockerSettings = $model->getDockerComposeMLSettings();
        $jobHistory = $model->getJobHistory(50);

        return $this->renderView('admin/ml', [
            'pag' => 'admin-ml',
            'settings' => $saved,
            'python_settings' => $pythonSettings,
            'docker_settings' => $dockerSettings,
            'default_engine' => $defaultEngine,
            'allowed_algorithms' => $allowedRaw ? json_decode($allowedRaw, true) : [],
            'categories' => $model->getAlgorithmCategories(),
            'engines' => $model->getEngines(),
            'job_history' => $jobHistory,
            'active_tab' => $tab,
        ]);
    }

    public function testPython()
    {
        $model = new AnomaliesModel();
        $testUrl = $this->request->getPost('url');
        $token = $this->request->getPost('token');
        $result = $model->testPythonConnection($testUrl ?: null, 6, $token ?: null);
        return $this->response->setJSON($result);
    }

    public function heartbeat()
    {
        $model = new AnomaliesModel();
        $testResult = $model->testPythonConnection(null, 3);

        $db = $this->getDb();
        $mlSetting = $db->table('settings')->where('class', 'ml')->where('key', 'ml_enabled')->get()->getRow();
        $mlEnabled = ($mlSetting && $mlSetting->value === '1');

        $pythonSetting = $db->table('settings')->where('class', 'ml')->where('key', 'ml_python_enabled')->get()->getRow();
        $pythonEnabled = ($pythonSetting && $pythonSetting->value === '1');

        return $this->response->setJSON([
            'online'                   => $testResult['success'] ?? false,
            'status'                   => $testResult['status'] ?? 'offline',
            'version'                  => $testResult['version'] ?? '2.5.0',
            'latency_ms'               => $testResult['latency_ms'] ?? 0,
            'memory'                   => $testResult['memory'] ?? ['used' => 0, 'total' => 0],
            'cpu_percent'              => $testResult['cpu_percent'] ?? 0.0,
            'models_count'             => $testResult['models_count'] ?? count($testResult['models'] ?? []),
            'models'                   => $testResult['models'] ?? [],
            'database_status'          => $testResult['database'] ?? 'unknown',
            'database_latency_ms'      => $testResult['database_latency_ms'] ?? 0.0,
            'database_tables_verified' => $testResult['database_tables_verified'] ?? 0,
            'database_total_tables'    => $testResult['database_total_tables'] ?? 10,
            'ml_enabled'               => $mlEnabled,
            'python_enabled'           => $pythonEnabled,
            'engine_mode'              => $pythonEnabled ? 'hybrid' : 'php',
            'failover_ready'           => true,
            'tested_url'               => $testResult['tested_url'] ?? '',
            'timestamp'                => time(),
        ]);
    }

    public function testDatabase()
    {
        $t0 = microtime(true);
        $db = $this->getDb();
        $coreTables = [
            'tbl_extracted_sms',
            'tbl_extracted_contacts',
            'tbl_extracted_call_logs',
            'tbl_extracted_locations',
            'tbl_extracted_installed_apps',
            'tbl_extracted_device_files',
            'tbl_system_app_usage',
            'tbl_device_profiles',
            'ml_jobs',
            'ml_results',
        ];

        try {
            $db->query('SELECT 1');
            $phpDbLatency = round((microtime(true) - $t0) * 1000, 2);
            $tables = $db->listTables();
            $verified = 0;
            foreach ($coreTables as $t) {
                if (in_array($t, $tables)) {
                    $verified++;
                }
            }

            // Also query Python backend DB status
            $model = new AnomaliesModel();
            $pyTest = $model->testPythonConnection(null, 4);

            return $this->response->setJSON([
                'success'                  => true,
                'php_db_connected'         => true,
                'php_db_latency_ms'        => $phpDbLatency,
                'php_tables_verified'      => $verified,
                'php_total_tables'         => count($coreTables),
                'python_db_connected'      => ($pyTest['database'] ?? '') === 'connected',
                'python_db_latency_ms'     => $pyTest['database_latency_ms'] ?? 0.0,
                'python_tables_verified'   => $pyTest['database_tables_verified'] ?? 0,
                'python_total_tables'      => $pyTest['database_total_tables'] ?? count($coreTables),
                'python_online'            => $pyTest['success'] ?? false,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database test failed: ' . $e->getMessage(),
            ]);
        }
    }

    public function setConnection()
    {
        $url = $this->request->getPost('url');
        $token = $this->request->getPost('token');
        if (!$url) {
            return $this->response->setJSON(['success' => false, 'message' => 'No URL provided.']);
        }

        $db = $this->getDb();
        $db->table('settings')->delete(['class' => 'ml', 'key' => 'ml_python_url']);

        // Parse URL to also save individual host/port/endpoint
        $parts = parse_url($url);
        $host = $parts['host'] ?? '';
        $port = $parts['port'] ?? '9070';
        $path = $parts['path'] ?? '/api/v1/analysis-jobs';

        $settings = [
            ['class' => 'ml', 'key' => 'ml_python_url', 'value' => rtrim($url, '/'), 'type' => 'string'],
            ['class' => 'ml', 'key' => 'ml_python_host', 'value' => $host, 'type' => 'string'],
            ['class' => 'ml', 'key' => 'ml_python_port', 'value' => (string)$port, 'type' => 'string'],
            ['class' => 'ml', 'key' => 'ml_python_endpoint', 'value' => $path ?: '/api/v1/analysis-jobs', 'type' => 'string'],
        ];

        if ($token !== null && trim($token) !== '') {
            $settings[] = ['class' => 'ml', 'key' => 'ml_python_token', 'value' => trim($token), 'type' => 'string'];
        }

        foreach ($settings as $s) {
            $existing = $db->table('settings')
                ->where('class', 'ml')
                ->where('key', $s['key'])
                ->get()
                ->getRow();

            if ($existing) {
                $db->table('settings')->update(
                    ['value' => $s['value'], 'updated_at' => date('Y-m-d H:i:s')],
                    ['class' => 'ml', 'key' => $s['key']]
                );
            } else {
                $s['created_at'] = date('Y-m-d H:i:s');
                $s['updated_at'] = date('Y-m-d H:i:s');
                $db->table('settings')->insert($s);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Connection settings saved successfully.',
            'url'     => rtrim($url, '/'),
        ]);
    }
}
