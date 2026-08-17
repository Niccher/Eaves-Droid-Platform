<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_User extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['email', 'username', 'active'];
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Gets basic user data if logged in.
     *
     * @return array|false
     */
    public function basic_user()
    {
        try {
            if (auth()->loggedIn()) {
                $user = auth()->user();
                $userArray = $user->toArray();
                $userArray['email'] = $user->getEmail();
                $userArray['id'] = $user->id;

                // Sync with user_profiles
                $profile = $this->db->table('user_profiles')
                    ->where('user_id', $user->id)
                    ->get()
                    ->getRowArray();

                if ($profile) {
                    $userArray['profile_image'] = $profile['profile_image'] ?? null;
                    $userArray['bio'] = $profile['bio'] ?? null;
                } else {
                    $userArray['profile_image'] = null;
                }

                // Fetch active subscription plan
                $subscription = $this->db->table('user_subscriptions')
                    ->select('plan, status, billing_cycle')
                    ->where('user_id', $user->id)
                    ->where('status', 'active')
                    ->where('current_period_end >=', date('Y-m-d H:i:s'))
                    ->orderBy('current_period_end', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();

                if ($subscription) {
                    $userArray['plan'] = $subscription['plan'] ?? 'free';
                } else {
                    $userArray['plan'] = 'free';
                }

                return $userArray;
            }
            log_message('error', 'User not logged in');
            return false;
        } catch (\Exception $e) {
            log_message('error', 'basic_user error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets user data from tbl_Users (custom table).
     *
     * @param int $user_id
     * @return array|false
     */
    public function get_data_tbl_users(int $user_id)
    {
        try {
            $result = $this->db->table('user_profiles')
                ->where('user_id', $user_id)
                ->limit(1)
                ->get()
                ->getRowArray();

            if (is_array($result)) {
                return $result;
            }

            log_message('info', 'No custom user data found in tbl_Users for user ID: ' . $user_id);
            return false;

        } catch (\Exception $e) {
            log_message('error', 'get_custom_user_data error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets user data from Shield's default 'users' table.
     *
     * @param int $user_id
     * @return array|false
     */
    public function get_data_users(int $user_id)
    {
        try {
            $result = $this->db->table('users')
                ->where('id', $user_id)
                ->limit(1)
                ->get()
                ->getRowArray();

            if (is_array($result)) {
                return $result;
            }

            log_message('info', 'No Shield user data found in users table for user ID: ' . $user_id);
            return false;

        } catch (\Exception $e) {
            log_message('error', 'get_shield_user_data error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets combined user variables from both tables.
     *
     * @param int $user_id
     * @return array|false
     */
    public function get_vars(int $user_id)
    {
        try {
            $customData  = $this->get_data_tbl_users($user_id);
            $shieldData  = $this->get_data_users($user_id);

            $result = [];

            if (is_array($customData)) {
                $result = array_merge($result, $customData);
            }

            if (is_array($shieldData)) {
                $result = array_merge($result, $shieldData);
            }

            if (!empty($result)) {
                return $result;
            }

            log_message('info', 'No user variables found for user ID: ' . $user_id);
            return false;

        } catch (\Exception $e) {
            log_message('error', 'get_vars error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a secure token with expiration.
     *
     * @param int $user_id
     * @param string $token
     * @param string $ip_add
     * @return bool
     */
    public function create_token(int $user_id, string $token, string $ip_add, ?string $token_name = null): bool
    {
        try {
            $dated = date('Y-m-d H:i:s');
            $future_date = date('Y-m-d H:i:s', strtotime('+30 days', strtotime($dated)));

            // Get user info for token naming
            $user = auth()->user();
            $userEmail = $user->getEmail();
            $username = $user->username ?? explode('@', $userEmail)[0];

            $data = [
                'created_at' => $dated,
                'owner_id' => $user_id,
                'token' => $token,
                'status' => "00",
                'initiator' => $ip_add,
                'expires_at' => $future_date,
                'device_name' => $token_name ?? ('Android_' . date('Ymd_His')),
                'last_used_at' => $dated,
                'ip_address' => $ip_add,
                'user_agent' => service('request')->getUserAgent()->getAgentString(),
                'token_type' => 'api',
                'is_refreshable' => 1,
                'scopes' => 'all',
                'device_checksum' => md5($token . $user_id . $dated),
                'android_id' => null // Can be set later when device connects
            ];

            if ($this->db->table('tbl_user_api_tokens')->insert($data)) {
                $logData = new Mod_Access_Logs();
                // Log action
                $logData->logAction([
                    'user_id' => $user_id,
                    'action_type' => 'Create Token',
                    'action_category' => 'authentication',
                    'action_severity' => 'medium',
                    // 'ip_address' => $this->request->getIPAddress(),
                    // 'user_agent' => $this->request->getUserAgent()->getAgentString(),
                    'request_url'     => current_url(),
                    'device_type' => 'desktop',
                    'success' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
                ]);

                log_message('info', 'Token created for user ' . $user_id);
                return true;
            }

            log_message('error', 'Token creation failed for user ' . $user_id);
            return false;
        } catch (\Exception $e) {
            log_message('error', 'create_token error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Marks a token as used.
     *
     * @param int $token_owner
     * @param string $token
     * @param int $token_id
     * @return bool
     */
    public function token_mark(int $token_owner, string $token, int $token_id): bool
    {
        try {
            $builder = $this->db->table('tbl_user_api_tokens');
            $builder->set('status', "11")
                ->set('last_used_at', date('Y-m-d H:i:s'))
                ->where('token', $token)
                ->where('counter', $token_id)  // Using 'counter' as the primary key
                ->where('owner_id', $token_owner);

            if ($builder->update()) {
                log_message('info', 'Token marked for owner ' . $token_owner);
                return true;
            }

            log_message('error', 'Token mark failed for owner ' . $token_owner);
            return false;
        } catch (\Exception $e) {
            log_message('error', 'token_mark error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets latest token for user.
     *
     * @param int $user_id
     * @return array|false
     */
    public function get_token(int $user_id)
    {
        try {
            $result = $this->db->table('tbl_user_api_tokens')
                ->where('owner_id', $user_id)
                ->where('status', '00')
                ->orderBy('counter', 'DESC')  // Using 'counter' as the primary key
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($result) {
                return $result;
            }
            log_message('info', 'No active token found for user ' . $user_id);
            return false;
        } catch (\Exception $e) {
            log_message('error', 'get_token error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates token metadata with real device information.
     *
     * @param string $token
     * @param string|null $device_checksum
     * @param string|null $android_id
     * @return bool
     */
    public function update_token_metadata(string $token, ?string $device_checksum = null, ?string $android_id = null): bool
    {
        try {
            $data = [];
            if ($device_checksum) $data['device_checksum'] = $device_checksum;
            if ($android_id) $data['android_id'] = $android_id;

            if (empty($data)) return true;

            return $this->db->table('tbl_user_api_tokens')
                ->where('token', $token)
                ->update($data);
        } catch (\Exception $e) {
            log_message('error', 'update_token_metadata error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets user devices from access logs.
     *
     * @param int $user_id
     * @return array
     */
    public function get_devices(int $user_id): array
    {
        try {
            // Get unique devices from access logs
            $devices = $this->db->table('tbl_user_actions')
                ->select('device_type, device_name, operating_system, browser, ip_address, MAX(created_at) as last_seen')
                ->where('user_id', $user_id)
                ->where('device_name IS NOT NULL')
                ->groupBy('device_name, ip_address')
                ->orderBy('last_seen', 'DESC')
                ->get()
                ->getResultArray();

            // Format the results
            $formattedDevices = [];
            foreach ($devices as $device) {
                $formattedDevices[] = [
                    'device_type' => $device['device_type'] ?? 'unknown',
                    'device_name' => $device['device_name'] ?? 'Unknown Device',
                    'os' => $device['operating_system'] ?? 'Unknown OS',
                    'browser' => $device['browser'] ?? 'Unknown Browser',
                    'ip_address' => $device['ip_address'] ?? 'N/A',
                    'last_seen' => $device['last_seen'] ?? date('Y-m-d H:i:s'),
                    'last_seen_formatted' => isset($device['last_seen']) ? date('M d, Y H:i', strtotime($device['last_seen'])) : 'Never'
                ];
            }

            return $formattedDevices;
        } catch (\Exception $e) {
            log_message('error', 'get_devices error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets user sessions from access logs.
     *
     * @param int $user_id
     * @return array
     */
    public function get_sessions(int $user_id): array
    {
        try {
            $sessions = $this->db->table('tbl_user_actions')
                ->select('session_id, ip_address, device_type, device_name, operating_system, browser, 
                         MAX(created_at) as last_activity, COUNT(*) as activity_count')
                ->where('user_id', $user_id)
                ->where('session_id IS NOT NULL')
                ->groupBy('session_id, ip_address')
                ->orderBy('last_activity', 'DESC')
                ->get()
                ->getResultArray();

            // Format the results
            $formattedSessions = [];
            foreach ($sessions as $session) {
                $formattedSessions[] = [
                    'session_id' => $session['session_id'],
                    'ip_address' => $session['ip_address'] ?? 'N/A',
                    'device_type' => $session['device_type'] ?? 'unknown',
                    'device_name' => $session['device_name'] ?? 'Unknown Device',
                    'os' => $session['operating_system'] ?? 'Unknown OS',
                    'browser' => $session['browser'] ?? 'Unknown Browser',
                    'last_activity' => $session['last_activity'],
                    'last_activity_formatted' => isset($session['last_activity']) ? date('M d, Y H:i', strtotime($session['last_activity'])) : 'Never',
                    'activity_count' => $session['activity_count'] ?? 0,
                    'is_active' => isset($session['last_activity']) && strtotime($session['last_activity']) > strtotime('-30 minutes')
                ];
            }

            return $formattedSessions;
        } catch (\Exception $e) {
            log_message('error', 'get_sessions error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets user security events.
     *
     * @param int $user_id
     * @return array
     */
    public function get_security_events(int $user_id): array
    {
        try {
            return $this->db->table('tbl_user_actions')
                ->where('user_id', $user_id)
                ->whereIn('action_category', ['authentication', 'security'])
                ->where('success', 0)
                ->orderBy('created_at', 'DESC')
                ->limit(20)
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_security_events error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets active sessions count.
     *
     * @param int $user_id
     * @return int
     */
    public function get_active_sessions_count(int $user_id): int
    {
        try {
            $thirtyMinutesAgo = date('Y-m-d H:i:s', strtotime('-30 minutes'));

            $result = $this->db->table('tbl_user_actions')
                ->select('COUNT(DISTINCT session_id) as session_count')
                ->where('user_id', $user_id)
                ->where('session_id IS NOT NULL')
                ->where('created_at >=', $thirtyMinutesAgo)
                ->get()
                ->getRow();

            return $result ? (int)$result->session_count : 0;
        } catch (\Exception $e) {
            log_message('error', 'get_active_sessions_count error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Gets security events count.
     *
     * @param int $user_id
     * @return int
     */
    public function get_security_events_count(int $user_id): int
    {
        try {
            return $this->db->table('tbl_user_actions')
                ->where('user_id', $user_id)
                ->whereIn('action_category', ['authentication', 'security'])
                ->where('success', 0)
                ->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'get_security_events_count error: ' . $e->getMessage());
            return 0;
        }
    }
    /**
     * Gets user devices with metadata (including FCM token) from device profile.
     *
     * @param int $user_id
     * @return array
     */
    public function get_user_devices_from_profile(int $user_id): array
    {
        try {
            // Step 1: Get device hardware IDs linked to this user
            // We check tbl_user_api_tokens, tbl_uploaded_files, and direct owner_id on device_profile
            
            // From tokens
            $tokenChecksums = $this->db->table('tbl_user_api_tokens')
                ->select('device_checksum')
                ->where('owner_id', $user_id)
                ->where('device_checksum !=', '')
                ->where('device_checksum IS NOT NULL')
                ->groupBy('device_checksum')
                ->get()
                ->getResultArray();

            // From uploaded files
            $uploadChecksums = $this->db->table('tbl_uploaded_files')
                ->select('device_checksum')
                ->where('token_owner_id', $user_id)
                ->where('device_checksum !=', '')
                ->where('device_checksum IS NOT NULL')
                ->groupBy('device_checksum')
                ->get()
                ->getResultArray();

            $allChecksums = array_unique(array_merge(
                array_column($tokenChecksums, 'device_checksum'),
                array_column($uploadChecksums, 'device_checksum')
            ));

            // Step 2: Fetch device profiles using checksums OR direct owner_id
            $builder = $this->db->table('tbl_device_profiles');

            if (!empty($allChecksums)) {
                $builder->whereIn('device_id', $allChecksums);
                $builder->orWhere('owner_id', $user_id);
            } else {
                $builder->where('owner_id', $user_id);
            }

            $devices = $builder
                ->select('tbl_device_profiles.*, tbl_device_profiles.created_at')
                ->groupBy('tbl_device_profiles.device_id')
                ->orderBy('extraction_timestamp', 'DESC')
                ->get()
                ->getResultArray();

            return $devices;

        } catch (\Exception $e) {
            log_message('error', 'get_user_devices_from_profile error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_used_tokens(int $user_id): array
    {
        try {
            return $this->db->table('tbl_user_api_tokens')
                ->where('owner_id', $user_id)
                ->where('status', '11')
                ->orderBy('counter', 'DESC')
                ->limit(50)
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_used_tokens error: ' . $e->getMessage());
            return [];
        }
    }
}