<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;

class FCMStatusStream extends BaseController
{
    public function stream(int $logId = 0)
    {
        if (!$logId) {
            return $this->response->setStatusCode(400, 'Log ID required.');
        }

        // Close session so we don't block other requests from this user!
        session_write_close();

        // Setup SSE Headers
        $this->response->setHeader('Content-Type', 'text/event-stream');
        $this->response->setHeader('Cache-Control', 'no-cache');
        $this->response->setHeader('Connection', 'keep-alive');
        $this->response->setHeader('X-Accel-Buffering', 'no'); // For Nginx

        $this->response->sendHeaders();
        flush();

        // Check if it's already completed in DB to avoid waiting if we missed the pub
        $db = \Config\Database::connect();
        $row = $db->table('tbl_user_actions')
            ->select('id, new_values, created_at')
            ->where('id', $logId)
            ->get()
            ->getRowArray();

        if (!$row) {
            echo "data: " . json_encode(['status' => 'error', 'message' => 'Action not found']) . "\n\n";
            flush();
            exit;
        }

        $nv = !empty($row['new_values']) ? json_decode($row['new_values'], true) : [];
        if (!empty($nv['device_ack'])) {
            // Already ACKed
            $ackData = $nv['device_ack'];
            $status = ($ackData['status'] === 'success') ? 'ack_success' : 'ack_failed';
            echo "data: " . json_encode(['status' => $status, 'message' => $ackData['message'] ?? '']) . "\n\n";
            flush();
            exit;
        }

        // Wait for Redis Pub/Sub
        try {
            $redisSvc = new \App\Services\RedisService();
            $client = clone $redisSvc->getClient();
            // Important: we need a dedicated connection for pubsub blocking
            
            if (!$client) {
                // Fallback to polling if Redis is down
                $this->pollDatabase($logId, $row['created_at']);
                exit;
            }

            // Set a read timeout on Predis for the blocking call
            $pubsub = $client->pubSubLoop(['subscribe' => "fcm:ack:{$logId}"]);
            
            $startTime = time();
            $timeout = 120; // 2 minutes

            foreach ($pubsub as $message) {
                if ($message->kind === 'message') {
                    // We got it!
                    echo "data: " . $message->payload . "\n\n";
                    flush();
                    $pubsub->unsubscribe();
                    break;
                }

                // Heartbeat / Timeout check
                if (time() - $startTime > $timeout) {
                    echo "data: " . json_encode(['status' => 'timeout', 'message' => 'Device did not respond in time.']) . "\n\n";
                    flush();
                    $pubsub->unsubscribe();
                    break;
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'FCMStatusStream redis error: ' . $e->getMessage());
            $this->pollDatabase($logId, $row['created_at']);
        }
        
        exit;
    }

    private function pollDatabase(int $logId, string $createdAt)
    {
        $db = \Config\Database::connect();
        $startTime = strtotime($createdAt);
        $timeout = 120;
        
        while (time() - $startTime < $timeout) {
            $row = $db->table('tbl_user_actions')->select('new_values')->where('id', $logId)->get()->getRowArray();
            $nv = !empty($row['new_values']) ? json_decode($row['new_values'], true) : [];
            
            if (!empty($nv['device_ack'])) {
                $ackData = $nv['device_ack'];
                $status = ($ackData['status'] === 'success') ? 'ack_success' : 'ack_failed';
                echo "data: " . json_encode(['status' => $status, 'message' => $ackData['message'] ?? '']) . "\n\n";
                flush();
                return;
            }
            
            sleep(2); // Poll every 2 seconds
            
            // Keep-alive heartbeat
            echo ": heartbeat\n\n";
            flush();
        }
        
        echo "data: " . json_encode(['status' => 'timeout', 'message' => 'Device did not respond in time.']) . "\n\n";
        flush();
    }
}
