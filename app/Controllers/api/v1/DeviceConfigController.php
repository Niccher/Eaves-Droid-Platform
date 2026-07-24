<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class DeviceConfigController extends BaseController
{
    use ResponseTrait;

    /**
     * POST /api/v1/device/config/sync
     * Device uploads its current config + permissions state.
     */
    public function sync()
    {
        $token = $this->request->getPost('token');
        $configJson = $this->request->getPost('config_json');
        $permissionsJson = $this->request->getPost('permissions_json');
        $deviceInfoJson = $this->request->getPost('device_info_json');

        if (!$token) {
            return $this->fail('Device token is required.', 400);
        }

        try {
            $db = \Config\Database::connect();

            $profile = $db->table('tbl_device_profile')
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

            $existing = $db->table('tbl_device_config')
                ->where('device_profile_id', $profile['counter'])
                ->get()
                ->getRowArray();

            $data = [
                'device_profile_id' => $profile['counter'],
                'user_id' => $userId,
                'config_json' => $configJson ?: null,
                'permissions_json' => $permissionsJson ?: null,
                'device_info_json' => $deviceInfoJson ?: null,
                'last_synced_at' => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $db->table('tbl_device_config')
                    ->where('device_profile_id', $profile['counter'])
                    ->update($data);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $db->table('tbl_device_config')->insert($data);
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
     * GET /api/v1/device/config/(:any)
     * Fetch the last known config for a device by FCM token.
     */
    public function fetch($token = null)
    {
        if (!$token) {
            return $this->fail('Device token is required.', 400);
        }

        try {
            $db = \Config\Database::connect();

            $config = $db->table('tbl_device_config')
                ->select('tbl_device_config.*, tbl_device_profile.device_model, tbl_device_profile.fcm_token')
                ->join('tbl_device_profile', 'tbl_device_profile.counter = tbl_device_config.device_profile_id')
                ->where('tbl_device_profile.fcm_token', $token)
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
                'last_synced_at' => $config['last_synced_at'],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'DeviceConfig fetch error: ' . $e->getMessage());
            return $this->fail('Server error.', 500);
        }
    }
}
