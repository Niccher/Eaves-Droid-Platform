<?php

namespace App\Controllers\admin;

use App\Models\Mod_Anomalies;

class Ml extends BaseAdminController
{
    public function index()
    {
        $db = $this->getDb();

        $saved = [];
        $rows = $db->table('settings')->where('class', 'ml')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        $model = new Mod_Anomalies();
        $pythonSettings = $model->getPythonSettings();
        $dockerSettings = $model->getDockerComposeMLSettings();

        return $this->renderView('admin/ml', [
            'pag' => 'admin-ml',
            'settings' => $saved,
            'python_settings' => $pythonSettings,
            'docker_settings' => $dockerSettings,
        ]);
    }

    public function testPython()
    {
        $model = new Mod_Anomalies();
        $testUrl = $this->request->getPost('url');
        $result = $model->testPythonConnection($testUrl ?: null);
        return $this->response->setJSON($result);
    }

    public function setConnection()
    {
        $url = $this->request->getPost('url');
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
            'message' => 'Connection URL saved successfully.',
            'url'     => rtrim($url, '/'),
        ]);
    }
}
