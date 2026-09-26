<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use App\Models\CryptModel;

class HealthStatusStream extends BaseController
{
    public function stream($token = null)
    {
        if (empty($token)) {
            return $this->response->setStatusCode(400, 'Missing device token identifier');
        }

        // Close session so we don't block other requests from this user
        session_write_close();

        // Setup SSE Headers
        $this->response->setHeader('Content-Type', 'text/event-stream');
        $this->response->setHeader('Cache-Control', 'no-cache');
        $this->response->setHeader('Connection', 'keep-alive');
        $this->response->setHeader('X-Accel-Buffering', 'no'); // For Nginx

        $this->response->sendHeaders();
        flush();

        $deviceId = null;
        $db = \Config\Database::connect();

        // Resolve device ID
        $crypt = new CryptModel();
        $decryptedCounter = $crypt->decrypt_id($token);
        if ($decryptedCounter && is_numeric($decryptedCounter)) {
            $device = $db->table('tbl_device_profiles')
                ->select('device_id')
                ->where('counter', (int)$decryptedCounter)
                ->get()
                ->getRowArray();
            if ($device && !empty($device['device_id'])) {
                $deviceId = $device['device_id'];
            }
        }

        if (!$deviceId && strlen($token) === 64 && ctype_xdigit($token)) {
            $deviceId = $token;
        }

        if (empty($deviceId)) {
            echo "data: " . json_encode(['success' => false, 'message' => 'Invalid device identifier']) . "\n\n";
            flush();
            exit;
        }

        // Wait for Redis Pub/Sub
        try {
            $redisSvc = new \App\Services\RedisService();
            $client = clone $redisSvc->getClient();
            
            if (!$client) {
                $this->pollDatabase($deviceId);
                exit;
            }

            $pubsub = $client->pubSubLoop(['subscribe' => "health:update:{$deviceId}"]);
            
            $startTime = time();
            $timeout = 120; // 2 minutes

            foreach ($pubsub as $message) {
                if ($message->kind === 'message') {
                    echo "data: " . json_encode(['success' => true, 'data' => json_decode($message->payload, true)]) . "\n\n";
                    flush();
                    $pubsub->unsubscribe();
                    break;
                }

                if (time() - $startTime > $timeout) {
                    echo "data: " . json_encode(['success' => false, 'message' => 'Device did not respond in time.']) . "\n\n";
                    flush();
                    $pubsub->unsubscribe();
                    break;
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'HealthStatusStream redis error: ' . $e->getMessage());
            $this->pollDatabase($deviceId);
        }
        
        exit;
    }

    private function pollDatabase(string $deviceId)
    {
        $db = \Config\Database::connect();
        $startTime = time();
        $timeout = 120;
        
        // Initial state
        $initial = $db->table('tbl_device_health_checks')
            ->where('device_id', $deviceId)
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
            
        $initialTime = $initial ? strtotime($initial['created_at']) : 0;
        
        while (time() - $startTime < $timeout) {
            $latest = $db->table('tbl_device_health_checks')
                ->where('device_id', $deviceId)
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
                
            $latestTime = $latest ? strtotime($latest['created_at']) : 0;
            
            // Return only if we have a NEW record since we started polling
            if ($latestTime > $initialTime || ($latestTime > $startTime - 5)) {
                echo "data: " . json_encode(['success' => true, 'data' => $latest]) . "\n\n";
                flush();
                return;
            }
            
            sleep(2);
            echo ": heartbeat\n\n";
            flush();
        }
        
        echo "data: " . json_encode(['success' => false, 'message' => 'Device did not respond in time.']) . "\n\n";
        flush();
    }
}
