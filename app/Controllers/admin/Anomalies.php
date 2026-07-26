<?php

namespace App\Controllers\admin;

use App\Models\Mod_Anomalies;

class Anomalies extends BaseAdminController
{
    public function index()
    {
        if ($this->request->getMethod() === 'post') {
            return $this->save();
        }

        $db = $this->getDb();
        $model = new Mod_Anomalies();

        $saved = [];
        $rows = $db->table('settings')->where('class', 'anomaly')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        $defaultEngine = $saved['default_engine'] ?? 'both';
        $allowedRaw = $saved['allowed_algorithms'] ?? '';

        $jobHistory = $model->getJobHistory(50);

        return $this->renderView('admin/anomalies', [
            'pag' => 'admin-anomalies',
            'default_engine' => $defaultEngine,
            'allowed_algorithms' => $allowedRaw ? json_decode($allowedRaw, true) : [],
            'categories' => $model->getAlgorithmCategories(),
            'engines' => $model->getEngines(),
            'job_history' => $jobHistory,
        ]);
    }

    protected function save()
    {
        $db = $this->getDb();

        $engine = $this->request->getPost('default_engine');
        if ($engine && in_array($engine, ['php', 'python', 'both'])) {
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
        return redirect()->to(base_url('admin/anomalies'));
    }
}
