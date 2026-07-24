<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use App\Models\Mod_Log_User_Action;
use CodeIgniter\API\ResponseTrait;

/**
 * Controller to manage and send remote commands to Android devices via FCM (HTTP v1).
 */
class FCMCommandController extends BaseController
{
    use ResponseTrait;

    private $credentialsPath = WRITEPATH . 'firebase_credentials.json';
    private $projectId = 'project-2026-35b76';

    /**
     * Dedicated endpoint to trigger specific data extractors.
     * Example: /api/v1/fcm/trigger/DEVICE_TOKEN/sms
     */
    public function trigger($token = null, $category = 'all')
    {
        return $this->send($token, 'cmd_sync_now', $category);
    }

    /**
     * Endpoint to trigger a remote command
     * URL Example: /api/v1/fcm/send/DEVICE_TOKEN/cmd_sync_now/sms
     */
    public function send($token = null, $command = null, $payload = 'all')
    {
        if (!$token || !$command) {
            return $this->fail('Device token and command are required.', 400);
        }

        if (!file_exists($this->credentialsPath)) {
            return $this->fail('Firebase credentials file missing.', 500);
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return $this->fail('Failed to generate OAuth2 token.', 500);
        }

        $result = $this->dispatchFCMV1($token, $command, $payload, $accessToken);

        $success = $result && !isset($result->error);

        $this->logCommandDispatch($token, $command, $payload, $success);

        if ($success) {
            return $this->respond([
                'success' => true,
                'message' => "Remote action '$command' for '$payload' dispatched.",
                'fcm_response' => $result
            ]);
        } else {
            return $this->fail([
                'success' => false,
                'message' => 'FCM dispatch failed.',
                'error' => $result->error ?? 'Unknown error'
            ], 500);
        }
    }

    private function logCommandDispatch(string $token, string $command, string $payload, bool $success): void
    {
        try {
            $logModel = new Mod_Log_User_Action();
            $request = service('request');
            $userId = null;
            if (function_exists('auth') && auth()->loggedIn()) {
                $userId = (int) auth()->user()->id;
            }

            $logModel->logAction([
                'user_id' => $userId,
                'action_category' => 'system',
                'action_type' => 'remote_cmd_' . str_replace('cmd_', '', $command),
                'action_severity' => 'medium',
                'success' => $success ? 1 : 0,
                'resource_id' => $token,
                'new_values' => json_encode([
                    'command' => $command,
                    'payload' => $payload,
                ]),
                'request_url' => current_url(),
                'request_method' => $request->getMethod(),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log FCM command: ' . $e->getMessage());
        }
    }

    private function getAccessToken()
    {
        $json = json_decode(file_get_contents($this->credentialsPath), true);
        $now = time();
        
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss' => $json['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]));

        $signature = '';
        openssl_sign("$header.$payload", $signature, $json['private_key'], 'SHA256');
        $jwt = "$header.$payload." . base64_encode($signature);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));

        $result = json_decode(curl_exec($ch));
        curl_close($ch);

        return $result->access_token ?? null;
    }

    private function dispatchFCMV1($token, $command, $payload, $accessToken)
    {
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $message = [
            'message' => [
                'token' => $token,
                'data' => [
                    'command' => $command,
                    'payload' => $payload,
                    'sent_at' => date('Y-m-d H:i:s')
                ]
            ]
        ];

        // Append extra parameters from GET/POST
        $requestData = service('request')->getVar();
        if (is_array($requestData)) {
            foreach ($requestData as $key => $value) {
                // Ignore CI routing or system vars
                if (!in_array($key, ['token', 'command', 'payload']) && !str_starts_with($key, 'csrf_')) {
                    $message['message']['data'][$key] = (string) $value;
                }
            }
        }

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
}
