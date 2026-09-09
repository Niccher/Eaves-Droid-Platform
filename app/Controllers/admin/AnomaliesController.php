<?php

namespace App\Controllers\admin;

use App\Models\AnomaliesModel;

class AnomaliesController extends BaseAdminController
{
    public function index()
    {
        if ($this->request->getMethod() === 'post') {
            return $this->save();
        }

        return redirect()->to(base_url('admin/ml?tab=algorithms'));
    }

    protected function save()
    {
        $db = $this->getDb();

        $engine = $this->request->getPost('default_engine');
        if ($engine && in_array($engine, ['php', 'python', 'both'])) {
            if ($engine === 'python' || $engine === 'both') {
                $model = new AnomaliesModel();
                $test = $model->testPythonConnection();
                if (!$test['success'] || ($test['status'] ?? '') !== 'ok') {
                    $url = $test['tested_url'] ?? 'unknown URL';
                    $msg = $test['message'] ?? 'Service offline';
                    session()->setFlashdata('error', "Cannot switch to Python or Hybrid engine: The Python ML service is unreachable or not healthy at {$url} (Error: {$msg}). Please ensure the service is running before enabling it.");
                    return redirect()->to(base_url('admin/ml?tab=engines'));
                }
            }

            $db->table('settings')->replace([
                'class' => 'anomaly',
                'key' => 'default_engine',
                'value' => $engine,
                'type' => 'string',
            ]);
        }

        $allowed = $this->request->getPost('allowed_algorithms');
        if ($allowed && is_array($allowed)) {
            $db->table('settings')->replace([
                'class' => 'anomaly',
                'key' => 'allowed_algorithms',
                'value' => json_encode(array_values($allowed)),
                'type' => 'string',
            ]);
        } else {
            $db->table('settings')->replace([
                'class' => 'anomaly',
                'key' => 'allowed_algorithms',
                'value' => '[]',
                'type' => 'string',
            ]);
        }

        // clean up any rows with invalid type values from prior saves
        $db->table('settings')
            ->where('class', 'anomaly')
            ->where('type', 'json')
            ->delete();

        $this->logAdminAction('update_anomaly_settings', 'low', true);

        session()->setFlashdata('success', 'Anomaly detection settings saved successfully.');
        return redirect()->to(base_url('admin/ml?tab=algorithms'));
    }
}
