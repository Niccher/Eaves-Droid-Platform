<?php

namespace App\Controllers;

use App\Libraries\ResilientSessionHandler;
use CodeIgniter\Controller;
use Config\Database;

class HealthController extends Controller
{
    public function index()
    {
        $dbConnected = false;
        $dbLatencyMs = 0.0;

        $t0 = microtime(true);
        try {
            $db = Database::connect();
            $db->query("SELECT 1");
            $dbConnected = true;
            $dbLatencyMs = round((microtime(true) - $t0) * 1000.0, 2);
        } catch (\Throwable $e) {
            $dbConnected = false;
            $dbLatencyMs = round((microtime(true) - $t0) * 1000.0, 2);
        }

        // 1. Resilience Telemetry (50ms non-blocking probe)
        $resilience = ResilientSessionHandler::getResilienceStatus();

        // 2. ML Service Health
        $mlHealth = null;
        $mlUrl = rtrim(getenv('ml_python_url') ?: (getenv('ML_PYTHON_URL') ?: 'http://ml:9070'), '/');
        $token = getenv('ML_INTERNAL_TOKEN') ?: 'default_secure_token_change_me_in_prod';

        try {
            $client = \Config\Services::curlrequest([
                'timeout'         => 3,
                'connect_timeout' => 2,
                'http_errors'     => false,
            ]);
            $response = $client->request('GET', $mlUrl . '/api/health', [
                'headers' => ['X-Internal-Token' => $token],
            ]);
            $mlHealth = json_decode($response->getBody(), true);
        } catch (\Throwable $e) {
            $mlHealth = ['status' => 'error', 'message' => $e->getMessage()];
        }

        // Determine WebApp status
        $webappStatus = 'healthy';
        if (!$dbConnected) {
            $webappStatus = 'critical';
        } elseif ($resilience['fallback_active']) {
            $webappStatus = 'healthy_degraded';
        }

        $healthStatus = [
            'webapp' => [
                'status'             => $webappStatus,
                'database_connected' => $dbConnected,
                'database_latency_ms'=> $dbLatencyMs,
                'active_engine'      => $resilience['session_engine'],
            ],
            'resilience' => $resilience,
            'ml_service' => $mlHealth,
            'timestamp'  => time(),
        ];

        $statusCode = (!$dbConnected) ? 503 : 200;
        return $this->response->setStatusCode($statusCode)->setJSON($healthStatus);
    }
}
