<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ReportController extends BaseController
{
    public function generateReport(int $deviceId)
    {
        // 1. Gather all data for this device
        $db = \Config\Database::connect();
        $device = $db->table('tbl_device_profiles')->where('device_id', $deviceId)->get()->getRowArray();
        
        if (!$device) {
            return $this->response->setStatusCode(404, 'Device not found');
        }
        
        $data = [
            'calls'      => $this->getCallSummary($deviceId),
            'locations'  => $this->getLocationSummary($deviceId),
            'apps'       => $this->getAppDelta($deviceId),
            'anomalies'  => $this->getAnomalies($device['owner_id']),
        ];

        // 2. Ask LLM to write the narrative
        // We will call the ML backend endpoint for LLM generation
        $client = \Config\Services::curlrequest();
        $mlUrl = env('ML_API_URL', 'http://ml-backend:8000') . '/api/v1/llm/generate';
        
        $promptSys = "You are a certified forensic analyst writing a professional report. Use formal language. Do not fabricate data. Only describe what the JSON contains.";
        $promptUser = "Write a forensic report summary for this device data:\n" . json_encode($data);
        
        $narrative = "Forensic analysis summary could not be generated.";
        try {
            $resp = $client->post($mlUrl, [
                'json' => [
                    'system_prompt' => $promptSys,
                    'user_prompt' => $promptUser
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . env('ML_INTERNAL_TOKEN', 'internal-secret-token')
                ]
            ]);
            if ($resp->getStatusCode() === 200) {
                $llmResult = json_decode($resp->getBody(), true);
                if (isset($llmResult['text'])) {
                    $narrative = $llmResult['text'];
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to generate LLM narrative: ' . $e->getMessage());
        }

        // 3. Render to HTML
        $html = view('reports/forensic_template', [
            'device'    => $device,
            'narrative' => $narrative,
            'data'      => $data,
            'generated' => date('Y-m-d H:i:s'),
        ]);

        // 4. Ideally use dompdf to render to PDF. We'll return HTML for now or basic PDF if installed.
        // For demonstration, we just return the HTML view (can be printed to PDF by browser).
        return $this->response->setBody($html);
    }
    
    private function getCallSummary($deviceId) {
        $db = \Config\Database::connect();
        return $db->table('tbl_extracted_call_logs')
            ->select('type, COUNT(*) as count, SUM(duration_seconds) as total_duration')
            ->where('token_owner_id', $this->getOwnerId($deviceId))
            ->groupBy('type')
            ->get()->getResultArray();
    }
    
    private function getLocationSummary($deviceId) {
        $db = \Config\Database::connect();
        return $db->table('tbl_extracted_locations')
            ->select('latitude, longitude, created_at')
            ->where('token_owner_id', $this->getOwnerId($deviceId))
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();
    }
    
    private function getAppDelta($deviceId) {
        $db = \Config\Database::connect();
        return $db->table('tbl_extracted_apps')
            ->select('app_name, package_name, is_system')
            ->where('token_owner_id', $this->getOwnerId($deviceId))
            ->limit(10)
            ->get()->getResultArray();
    }
    
    private function getAnomalies($userId) {
        $db = \Config\Database::connect();
        return $db->table('tbl_ml_results')
            ->where('user_id', $userId)
            ->orderBy('score', 'DESC')
            ->limit(5)
            ->get()->getResultArray();
    }
    
    private function getOwnerId($deviceId) {
        $db = \Config\Database::connect();
        $row = $db->table('tbl_device_profiles')->select('owner_id')->where('device_id', $deviceId)->get()->getRowArray();
        return $row ? $row['owner_id'] : 0;
    }
}
