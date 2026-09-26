<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RetryFcmQueue extends BaseCommand
{
    protected $group       = 'FCM';
    protected $name        = 'fcm:retry';
    protected $description = 'Retries queued FCM commands that failed due to transient errors.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        // Get up to 50 jobs ready for retry
        $pending = $db->table('tbl_fcm_retry_queue')
            ->where('attempts <', 5)
            ->where('next_retry <=', date('Y-m-d H:i:s'))
            ->orderBy('next_retry', 'ASC')
            ->limit(50)
            ->get()
            ->getResultArray();

        if (empty($pending)) {
            CLI::write('No pending FCM jobs to retry.', 'green');
            return;
        }

        $fcmController = new \App\Controllers\api\v1\FCMCommandController();
        // We need to inject the request/response to avoid errors, or just instantiate it carefully.
        // Actually, FCMCommandController expects $this->request to be set.
        // Let's use the controller if possible, or extract the sending logic to a Service.
        // Currently the logic is entangled in the controller.
        
        // Wait, since we are in a command, instantiating the controller directly is tricky because of `request`.
        // Let's refactor the bare minimum or just create a mock request.
        $request = \Config\Services::request();
        $response = \Config\Services::response();
        $logger = \Config\Services::logger();
        
        $fcmController->initController($request, $response, $logger);

        $accessToken = $fcmController->getAccessToken();
        
        if (!$accessToken) {
            CLI::write('Failed to get FCM access token.', 'red');
            return;
        }

        // We need access to dispatchFCMV1. Since it's private, we can't call it. 
        // We'll use Reflection to bypass this for the CLI, or better, we'll just do the cURL here.
        $projectId = $this->getProjectId($fcmController);

        foreach ($pending as $job) {
            CLI::write("Retrying job ID {$job['id']} for token {$job['fcm_token']}...");
            
            $result = $this->dispatchFCMV1($job['fcm_token'], $job['command'], $job['payload'], $accessToken, $projectId, $job['action_log_id']);
            
            $responseBody = json_decode(json_encode($result), true) ?? [];
            $success = ($result !== null && !isset($responseBody['error']));
            
            if ($success) {
                // Remove from queue
                $db->table('tbl_fcm_retry_queue')->delete(['id' => $job['id']]);
                CLI::write("Job {$job['id']} succeeded.", 'green');
            } else {
                // Increment attempts
                $errorBody = $result->error ?? null;
                $errorCode = ($result === null) ? 'CURL_ERROR' : ($errorBody->status ?? 'UNKNOWN_ERROR');
                
                $isUnregistered = in_array($errorCode, ['UNREGISTERED', 'NOT_FOUND'], true)
                               || (is_object($errorBody) && ($errorBody->message ?? '') === 'NotRegistered');

                if ($isUnregistered) {
                    $db->table('tbl_device_profiles')
                        ->where('fcm_token', $job['fcm_token'])
                        ->update(['fcm_token' => null]);
                        
                    $db->table('tbl_fcm_retry_queue')->delete(['id' => $job['id']]);
                    CLI::write("Job {$job['id']} failed (UNREGISTERED). Token cleared.", 'yellow');
                } else {
                    $this->incrementAttempts($db, $job, $errorCode);
                    CLI::write("Job {$job['id']} failed ({$errorCode}). Scheduled for retry.", 'red');
                }
            }
        }
    }

    private function getProjectId($controller): string
    {
        $reflection = new \ReflectionClass($controller);
        $property = $reflection->getProperty('projectId');
        $property->setAccessible(true);
        return $property->getValue($controller);
    }

    private function dispatchFCMV1($token, $command, $payload, $accessToken, $projectId, $logId)
    {
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $message = [
            'message' => [
                'token' => $token,
                'data' => [
                    'command' => $command,
                    'payload' => $payload,
                    'sent_at' => date('Y-m-d H:i:s'),
                    'action_log_id' => (string) $logId,
                    'ack_url' => base_url('api/v1/command-acknowledgements/' . $logId),
                ]
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }

    private function incrementAttempts($db, array $job, string $error): void
    {
        $attempts  = $job['attempts'] + 1;
        $nextRetry = time() + (60 * pow(2, $attempts));  // 2min, 4min, 8min, 16min...

        $db->table('tbl_fcm_retry_queue')->update(
            ['attempts' => $attempts, 'next_retry' => date('Y-m-d H:i:s', $nextRetry), 'last_error' => $error],
            ['id' => $job['id']]
        );
    }
}
