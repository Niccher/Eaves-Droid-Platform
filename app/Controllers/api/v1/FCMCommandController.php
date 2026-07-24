<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use App\Models\Mod_Log_User_Action;
use CodeIgniter\API\ResponseTrait;

class FCMCommandController extends BaseController
{
    use ResponseTrait;

    private $credentialsPath = WRITEPATH . 'firebase_credentials.json';
    private $projectId = 'project-2026-35b76';

    public function trigger($token = null, $category = 'all')
    {
        return $this->send($token, 'cmd_sync_now', $category);
    }

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

        $logId = $this->logCommandDispatch($token, $command, $payload, [], true);

        $result = $this->dispatchFCMV1($token, $command, $payload, $accessToken, $logId);

        $responseBody = json_decode(json_encode($result), true) ?? [];
        $success = !isset($responseBody['error']);

        $this->updateLogEntry($logId, $responseBody, $success);

        if ($success) {
            return $this->respond([
                'success' => true,
                'message' => "Remote action '$command' for '$payload' dispatched.",
                'fcm_response' => $result,
                'action_log_id' => $logId,
            ]);
        } else {
            return $this->fail([
                'success' => false,
                'message' => 'FCM dispatch failed.',
                'error' => $result->error ?? 'Unknown error',
                'action_log_id' => $logId,
            ], 500);
        }
    }

    /**
     * POST /api/v1/fcm/ack/(:num)
     * Callback from the Android device after processing a command.
     */
    public function ack($logId = null)
    {
        if (!$logId) {
            return $this->fail('Log ID required.', 400);
        }

        $status = $this->request->getPost('status');
        $deviceMessage = $this->request->getPost('message');
        $deviceStatus = $this->request->getPost('device_status');
        $permissions = $this->request->getPost('permissions');

        try {
            $db = \Config\Database::connect();
            $existing = $db->table('tbl_user_actions')->where('id', $logId)->get()->getRowArray();
            if (!$existing) {
                return $this->fail('Log entry not found.', 404);
            }

            $nv = !empty($existing['new_values']) ? json_decode($existing['new_values'], true) : [];
            $nv['device_ack'] = [
                'status' => $status ?? 'unknown',
                'message' => $deviceMessage ?? '',
                'device_status' => $deviceStatus ?? '',
                'acknowledged_at' => date('Y-m-d H:i:s'),
            ];
            if ($permissions) {
                $nv['device_permissions'] = $permissions;
            }

            $db->table('tbl_user_actions')
                ->where('id', $logId)
                ->update([
                    'new_values' => json_encode($nv),
                    'success' => ($status === 'success') ? 1 : 0,
                    'error_message' => ($status !== 'success') ? ($deviceMessage ?? 'Command failed on device') : null,
                ]);

            return $this->respond(['success' => true, 'message' => 'Acknowledged.']);
        } catch (\Exception $e) {
            log_message('error', 'FCM ack error: ' . $e->getMessage());
            return $this->fail('Server error.', 500);
        }
    }

    private function logCommandDispatch(string $token, string $command, string $payload, array $response, bool $success): int
    {
        try {
            $logModel = new Mod_Log_User_Action();
            $request = service('request');
            $userId = null;
            if (function_exists('auth') && auth()->loggedIn()) {
                $userId = (int) auth()->user()->id;
            }

            $actionMap = [
                'cmd_sms' => 'fetch_sms',
                'cmd_calls' => 'fetch_calls',
                'cmd_contacts' => 'fetch_contacts',
                'cmd_search_data' => 'search_data',
                'cmd_capture_photo' => 'capture_photo',
                'cmd_record_audio' => 'record_audio',
                'cmd_files' => 'fetch_files',
                'cmd_fetch_file' => 'fetch_file',
                'cmd_location' => 'fetch_location',
                'cmd_start_tracking' => 'start_tracking',
                'cmd_context' => 'fetch_context',
                'cmd_apps' => 'fetch_apps',
                'cmd_usage' => 'fetch_usage',
                'cmd_notifications' => 'fetch_notifications',
                'cmd_device_info' => 'fetch_device_info',
                'cmd_sensors' => 'fetch_sensors',
                'cmd_network' => 'fetch_network',
                'cmd_bluetooth' => 'fetch_bluetooth',
                'cmd_calendar' => 'fetch_calendar',
                'cmd_accounts' => 'fetch_accounts',
                'cmd_beep' => 'play_beep',
                'cmd_siren' => 'play_siren',
                'cmd_wipe_logs' => 'wipe_logs',
                'cmd_locate' => 'locate_device',
                'cmd_all' => 'sync_all',
                'cmd_sync_now' => 'sync_data',
                'cmd_reset_app' => 'reset_app',
                'cmd_deactivate' => 'deactivate_app',
                'cmd_reactivate' => 'reactivate_app',
                'cmd_logout' => 'logout_user',
                'cmd_uninstall_preserve' => 'uninstall_preserve',
                'cmd_uninstall_wipe' => 'uninstall_wipe',
                'cmd_update_prefs' => 'update_settings',
                'cmd_open_permission' => 'open_permission',
            ];

            $actionType = $actionMap[$command] ?? ('remote_cmd_' . str_replace('cmd_', '', $command));

            $logId = $logModel->logAction([
                'user_id' => $userId,
                'action_category' => 'system',
                'action_type' => $actionType,
                'action_severity' => 'medium',
                'success' => $success ? 1 : 0,
                'resource_id' => $token,
                'new_values' => json_encode([
                    'command' => $command,
                    'payload' => $payload,
                    'fcm_response' => $response,
                ]),
                'error_message' => !$success ? ($response['error']['message'] ?? json_encode($response)) : null,
                'request_url' => current_url(),
                'request_method' => $request->getMethod(),
            ]);

            return (int) $logId;
        } catch (\Exception $e) {
            log_message('error', 'Failed to log FCM command: ' . $e->getMessage());
            return 0;
        }
    }

    private function updateLogEntry(int $logId, array $responseBody, bool $success): void
    {
        if ($logId <= 0) return;
        try {
            $db = \Config\Database::connect();
            $existing = $db->table('tbl_user_actions')->where('id', $logId)->get()->getRowArray();
            if (!$existing) return;

            $nv = !empty($existing['new_values']) ? json_decode($existing['new_values'], true) : [];
            $nv['fcm_response'] = $responseBody;

            $db->table('tbl_user_actions')
                ->where('id', $logId)
                ->update([
                    'new_values' => json_encode($nv),
                    'success' => $success ? 1 : 0,
                    'error_message' => !$success ? ($responseBody['error']['message'] ?? json_encode($responseBody)) : null,
                ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to update FCM log: ' . $e->getMessage());
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

    private function dispatchFCMV1($token, $command, $payload, $accessToken, $logId = 0)
    {
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $message = [
            'message' => [
                'token' => $token,
                'data' => [
                    'command' => $command,
                    'payload' => $payload,
                    'sent_at' => date('Y-m-d H:i:s'),
                    'action_log_id' => (string) $logId,
                    'ack_url' => base_url('api/v1/fcm/ack/' . $logId),
                ]
            ]
        ];

        $requestData = service('request')->getVar();
        if (is_array($requestData)) {
            foreach ($requestData as $key => $value) {
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
