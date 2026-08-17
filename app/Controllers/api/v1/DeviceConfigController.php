<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class DeviceConfigController extends BaseController
{
    use ResponseTrait;

    /**
     * POST/PUT /api/v1/devices/config/sync
     * Device uploads its current config + permissions state.
     */
    public function sync()
    {
        $json = $this->request->getJSON(true) ?? [];
        $post = $this->request->getPost() ?? [];

        $token = $json['token'] ?? $post['token'] ?? null;
        $configRaw = $json['config'] ?? $json['config_json'] ?? $post['config_json'] ?? null;
        $permissionsRaw = $json['permissions'] ?? $json['permissions_json'] ?? $post['permissions_json'] ?? null;
        $deviceInfoRaw = $json['device_info'] ?? $json['device_info_json'] ?? $post['device_info_json'] ?? null;
        $metadataRaw = $json['metadata'] ?? $json['metadata_json'] ?? $post['metadata_json'] ?? null;

        $configJson = is_array($configRaw) ? json_encode($configRaw) : $configRaw;
        $permissionsJson = is_array($permissionsRaw) ? json_encode($permissionsRaw) : $permissionsRaw;
        $deviceInfoJson = is_array($deviceInfoRaw) ? json_encode($deviceInfoRaw) : $deviceInfoRaw;
        $metadataJson = is_array($metadataRaw) ? json_encode($metadataRaw) : $metadataRaw;

        if (!$token) {
            return $this->fail('Device token is required.', 400);
        }

        try {
            $db = \Config\Database::connect();

            $profile = $db->table('tbl_device_profiles')
                ->select('counter, device_id')
                ->where('fcm_token', $token)
                ->get()
                ->getRowArray();

            if (!$profile) {
                return $this->fail('Device not found for this token.', 404);
            }

            $userId = null;
            if (function_exists('auth') && auth()->loggedIn()) {
                $userId = (int) auth()->user()->id;
            }

            $existing = $db->table('tbl_device_configs')
                ->where('device_profile_id', $profile['counter'])
                ->get()
                ->getRowArray();

            $data = [
                'device_profile_id' => $profile['counter'],
                'user_id' => $userId,
                'config_json' => $configJson ?: null,
                'permissions_json' => $permissionsJson ?: null,
                'device_info_json' => $deviceInfoJson ?: null,
                'metadata_json' => $metadataJson ?: null,
                'last_synced_at' => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $db->table('tbl_device_configs')
                    ->where('device_profile_id', $profile['counter'])
                    ->update($data);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $db->table('tbl_device_configs')->insert($data);
            }

            return $this->respond([
                'success' => true,
                'message' => 'Config synced.',
            ]);
        } catch (\Exception $e) {
            log_message('error', 'DeviceConfig sync error: ' . $e->getMessage());
            return $this->fail('Server error.', 500);
        }
    }

    /**
     * GET /api/v1/devices/config/(:any)
     * Fetch the last known config for a device by FCM token.
     */
    public function fetch($token = null)
    {
        if (!$token) {
            return $this->fail('Device token is required.', 400);
        }

        try {
            $db = \Config\Database::connect();

            $config = $db->table('tbl_device_configs')
                ->select('tbl_device_configs.*, tbl_device_profiles.device_model, tbl_device_profiles.fcm_token')
                ->join('tbl_device_profiles', 'tbl_device_profiles.counter = tbl_device_configs.device_profile_id')
                ->where('tbl_device_profiles.fcm_token', $token)
                ->get()
                ->getRowArray();

            if (!$config) {
                return $this->respond([
                    'success' => true,
                    'config_json' => null,
                    'permissions_json' => null,
                    'message' => 'No config saved yet for this device.',
                ]);
            }

            return $this->respond([
                'success' => true,
                'config_json' => json_decode($config['config_json'], true) ?: new \stdClass(),
                'permissions_json' => json_decode($config['permissions_json'], true) ?: new \stdClass(),
                'device_info_json' => json_decode($config['device_info_json'], true) ?: new \stdClass(),
                'metadata_json' => json_decode($config['metadata_json'] ?? '{}', true) ?: new \stdClass(),
                'last_synced_at' => $config['last_synced_at'],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'DeviceConfig fetch error: ' . $e->getMessage());
            return $this->fail('Server error.', 500);
        }
    }

    /**
     * GET /api/v1/devices/defaults
     * Returns current app defaults for Android devices.
     */
    public function defaults()
    {
        try {
            $db = \Config\Database::connect();
            $row = $db->table('tbl_system_default_apps')
                ->orderBy('version', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if (!$row) {
                return $this->respond([
                    'success' => true,
                    'version' => 0,
                    'config_json' => new \stdClass(),
                ]);
            }

            return $this->respond([
                'success' => true,
                'version' => (int) $row['version'],
                'config_json' => json_decode($row['config_json'], true) ?: new \stdClass(),
                'updated_at' => $row['updated_at'],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Defaults fetch error: ' . $e->getMessage());
            return $this->fail('Server error.', 500);
        }
    }
}
