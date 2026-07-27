<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Finder extends Model
{
    protected $table = 'tbl_users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [];
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    public $pager; // Changed from protected to public

    protected ?string $deviceId = null;

    public function setDeviceId(?string $deviceId): void
    {
        $this->deviceId = $deviceId;
    }

    protected function applyOwnerDeviceFilter($builder, int $user_id)
    {
        $builder->where('owner_id', $user_id);
        if (!empty($this->deviceId) && $this->deviceId !== 'all') {
            $builder->where('device_id', $this->deviceId);
        }
        return $builder;
    }

    protected function fq(string $table, int $userId)
    {
        $builder = $this->db->table($table);
        $builder->where('owner_id', $userId);
        if (!empty($this->deviceId) && $this->deviceId !== 'all') {
            $builder->where('device_id', $this->deviceId);
        }
        return $builder;
    }

    /**
     * Count records for a user in a given table, using the same owner/device filter.
     */
    public function cq(string $table, int $userId): int
    {
        try {
            $result = $this->fq($table, $userId)->countAllResults();
            return $result ?: 0;
        } catch (\Exception $e) {
            log_message('error', "count query error for {$table}: " . $e->getMessage());
            return 0;
        }
    }

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
                $userArray['id'] = $user->id; // Ensure ID is present

                // Join with user_profiles to get the avatar and other settings
                $profile = $this->db->table('user_profiles')
                    ->where('user_id', $user->id)
                    ->get()
                    ->getRowArray();

                if ($profile) {
                    $userArray['profile_image'] = $profile['profile_image'] ?? null;
                    $userArray['bio'] = $profile['bio'] ?? null;
                    $userArray['language'] = $profile['language'] ?? 'en';
                    $userArray['timezone'] = $profile['timezone'] ?? 'UTC';
                    $userArray['theme'] = $profile['theme'] ?? 'light';
                } else {
                    $userArray['profile_image'] = null;
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
     * Generalized count query.
     *
     * @param string $table
     * @param int $user_id
     * @param array $extraWhere Optional extra where conditions
     * @param string|null $blockColumn Optional column for exclusions
     * @param array $blockedValues Optional values to exclude
     * @return int
     */
    protected function getCount(string $table, int $user_id, array $extraWhere = [], ?string $blockColumn = null, array $blockedValues = []): int
    {
        try {
            $builder = $this->db->table($table);

            $this->applyOwnerDeviceFilter($builder, $user_id);

            if (!empty($extraWhere)) {
                $builder->where($extraWhere);
            }
            if ($blockColumn && !empty($blockedValues)) {
                $builder->whereNotIn($blockColumn, $blockedValues);
            }
            return $builder->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'getCount error for ' . $table . ': ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete apps by user ID.
     *
     * @param int $user_id
     * @return bool
     */
    public function deleteAppsByUser(int $user_id): bool
    {
        try {
            $builder = $this->db->table('tbl_apps');
            return $this->applyOwnerDeviceFilter($builder, $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteAppsByUser error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete call logs by user ID.
     *
     * @param int $user_id
     * @return bool
     */
    public function deleteCallsByUser(int $user_id): bool
    {
        try {
            $builder = $this->db->table('tbl_logs');
            return $this->applyOwnerDeviceFilter($builder, $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteCallsByUser error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete contacts by user ID.
     *
     * @param int $user_id
     * @return bool
     */
    public function deleteContactsByUser(int $user_id): bool
    {
        try {
            $builder = $this->db->table('tbl_contacts');
            return $this->applyOwnerDeviceFilter($builder, $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteContactsByUser error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete SMS by user ID.
     *
     * @param int $user_id
     * @return bool
     */
    public function deleteSmsByUser(int $user_id): bool
    {
        try {
            $builder = $this->db->table('tbl_sms');
            return $this->applyOwnerDeviceFilter($builder, $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteSmsByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteLocationByUser(int $user_id): bool
    {
        try {
            return $this->fq('tbl_location', $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteLocationByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteActivityByUser(int $user_id): bool
    {
        try {
            return $this->fq('tbl_activity', $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteActivityByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteDeviceFilesByUser(int $user_id): bool
    {
        try {
            return $this->fq('tbl_device_files', $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteDeviceFilesByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteDeviceContextByUser(int $user_id): bool
    {
        return $this->fq('tbl_device_context', $user_id)->delete();
    }
    public function deleteNetworkInfoByUser(int $user_id): bool
    {
        return $this->fq('tbl_network_info', $user_id)->delete();
    }
    public function deleteAccountsByUser(int $user_id): bool
    {
        return $this->fq('tbl_accounts', $user_id)->delete();
    }
    public function deleteCalendarByUser(int $user_id): bool
    {
        return $this->fq('tbl_calendar_events', $user_id)->delete();
    }
    public function deleteAppUsageByUser(int $user_id): bool
    {
        return $this->fq('tbl_app_usage', $user_id)->delete();
    }
    public function deleteNotificationsByUser(int $user_id): bool
    {
        return $this->fq('tbl_notifications', $user_id)->delete();
    }

    /**
     * Delete notifications for a specific app (package) for a user.
     *
     * @param int $user_id
     * @param string $packageName
     * @return bool
     */
    public function delete_notifications_by_app(int $user_id, string $packageName): bool
    {
        try {
            $builder = $this->db->table('tbl_notifications');
            return $this->applyOwnerDeviceFilter($builder, $user_id)
                           ->where('package_name', $packageName)
                           ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_notifications_by_app error: ' . $e->getMessage());
            return false;
        }
    }
    public function deleteBluetoothByUser(int $user_id): bool
    {
        return $this->fq('tbl_bluetooth', $user_id)->delete();
    }
    public function deleteSensorsByUser(int $user_id): bool
    {
        return $this->fq('tbl_sensor_profile', $user_id)->delete();
    }

    /**
     * Gets count of SMS messages for user.
     *
     * @param int $user_id
     * @return int
     */
    public function get_count_Sms(int $user_id): int
    {
        $blocked = $this->getBlockedIdentifiers($user_id, 'sms');
        return $this->getCount('tbl_sms', $user_id, [], 'address', $blocked);
    }

    /**
     * Gets count of SMS messages by category.
     *
     * @param int $user_id
     * @param string $category
     * @return int
     */
    public function get_count_Sms_category(int $user_id, string $category): int
    {
        $blocked = $this->getBlockedIdentifiers($user_id, 'sms');
        return $this->getCount('tbl_sms', $user_id, ['sms_type' => $category], 'address', $blocked);
    }

    /**
     * Helper: Get blocked identifiers for a category
     */
    protected function getBlockedIdentifiers(int $userId, string $category): array
    {
        try {
            if (!$this->db->tableExists('tbl_blocklist')) return [];
            
            $blocks = $this->db->table('tbl_blocklist')
                           ->select('identifier')
                           ->where('owner_id', $userId)
                           ->where('category', $category)
                           ->get()
                           ->getResultArray();
            return array_column($blocks, 'identifier');
        } catch (\Exception $e) {
            return [];
        }
    }

    public function get_count_Apps(int $user_id): int
    {
        try {
            $blocked = $this->getBlockedIdentifiers($user_id, 'app_usage');
            $builder = $this->fq('tbl_apps', $user_id);
            if (!empty($blocked)) {
                $builder->whereNotIn('package_name', $blocked);
            }
            return $builder->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'get_count_Apps error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Gets count of apps by category (system or user).
     *
     * @param int $user_id
     * @param int $is_system
     * @return int
     */
    public function get_count_Apps_category(int $user_id, int $is_system): int
    {
        return $this->getCount('tbl_apps', $user_id, ['is_system_app' => $is_system]);
    }

    /**
     * Gets count of contacts for user.
     *
     * @param int $user_id
     * @return int
     */
    public function get_count_Contacts(int $user_id): int
    {
        return $this->getCount('tbl_contacts', $user_id);
    }

    /**
     * Gets count of call logs for user.
     *
     * @param int $user_id
     * @return int
     */
    public function get_count_Calls(int $user_id): int
    {
        $blocked = $this->getBlockedIdentifiers($user_id, 'call');
        return $this->getCount('tbl_logs', $user_id, [], 'phone_number', $blocked);
    }

    /**
     * Gets count of files for user.
     *
     * @param int $user_id
     * @return int
     */
    public function get_count_Files(int $user_id): int
    {
        return $this->getCount('tbl_device_files', $user_id);
    }

    /**
     * Gets count of locations for user.
     *
     * @param int $user_id
     * @param bool $hasCoordsOnly Only count entries with non-null coordinates
     * @return int
     */
    public function get_count_Location(int $user_id, bool $hasCoordsOnly = false): int
    {
        if ($hasCoordsOnly) {
            try {
                return $this->fq('tbl_location', $user_id)
                    ->where('latitude IS NOT NULL')
                    ->where('longitude IS NOT NULL')
                    ->where('latitude !=', '')
                    ->where('longitude !=', '')
                    ->countAllResults();
            } catch (\Exception $e) {
                log_message('error', 'get_count_Location (hasCoords) error: ' . $e->getMessage());
                return 0;
            }
        }
        return $this->getCount('tbl_location', $user_id);
    }

    /**
     * Gets count of activities for user.
     *
     * @param int $user_id
     * @return int
     */
    public function get_count_Activity(int $user_id): int
    {
        return $this->getCount('tbl_activity', $user_id);
    }

    /**
     * Gets contact name by phone number (used by dashboard).
     *
     * @param string $nom
     * @return array|null
     */
    public function get_contact(string $nom)
    {
        try {
            $user_id = auth()->user()->id;
            return $this->db->table('tbl_contacts')
                ->select('display_name as Name')
                ->like('phone_numbers', $nom)
                ->get()
                ->getRowArray();
        } catch (\Exception $e) {
            log_message('error', 'get_contact error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets contact info by phone number.
     *
     * @param string $contactNumber1
     * @return array|null
     */
    public function get_contact_info($contactNumber1)
    {
        try {
            $user_id = auth()->user()->id;
            $builder = $this->db->table('tbl_contacts');
            $query_sent = $builder->select('*')
                ->like('phone_numbers', $contactNumber1)
                ->limit(1)
                ->get();
            return $query_sent->getRowArray();
        } catch (\Exception $e) {
            log_message('error', 'get_contact_info error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets contacts with new schema mapping.
     *
     * @param int $userId
     * @param int $perPage
     * @return array
     */
    public function get_contacts1(int $userId, int $perPage = 25): array
    {
        try {
            $builder = $this->db->table('tbl_contacts');

            // Get total count for pagination
            $total = $this->get_count_Contacts($userId);

            // Get page number from request
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $results = $builder->select('
                counter as ID,
                contact_id,
                display_name as Name,
                phone_numbers,
                phone_count
            ')
                ->where('owner_id', $userId)
                ->orderBy('display_name', 'ASC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Process phone numbers from JSON
            foreach ($results as &$row) {
                $phoneNumbers = json_decode($row['phone_numbers'], true);
                $row['Number'] = !empty($phoneNumbers) ? $phoneNumbers[0] : '';
                unset($row['phone_numbers']);
            }

            // Set up pagination
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_contacts error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets contacts with new schema mapping.
     *
     * @param int $userId
     * @param int $perPage
     * @return array
     */
    public function get_contacts(int $userId, int $perPage = 25): array
    {
        try {
            $builder = $this->db->table('tbl_contacts');

            // Get total count for pagination
            $total = $this->get_count_Contacts($userId);

            // Get page number from request
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $results = $builder->select('
            counter as ID,
            contact_id,
            display_name as Name,
            phone_numbers,
            phone_count,
            last_contacted,
            is_favorite,
            contact_frequency,
            device_id,
            created_at
        ')
                ->where('owner_id', $userId)
                ->orderBy('display_name', 'ASC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Process phone numbers from JSON
            foreach ($results as &$row) {
                $phoneNumbers = json_decode($row['phone_numbers'], true);
                $row['Number'] = !empty($phoneNumbers) ? $phoneNumbers[0] : '';
                // Keep the original phone numbers array for the modal
                $row['phone_numbers_array'] = $phoneNumbers ?: [];
                unset($row['phone_numbers']);
            }

            // Set up pagination
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_contacts error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets SMS by type with new schema mapping.
     *
     * @param int $user_id
     * @param string $sms_type
     * @param int $perPage
     * @return array
     */
    public function get_sms_type(int $user_id, string $sms_type, int $perPage = 25): array
    {
        try {
            $builder = $this->db->table('tbl_sms');

            // Get total count for pagination
            $total = $this->get_count_Sms_category($user_id, $sms_type);

            // Get page number from request
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $query = $builder->select('
                counter as id,
                android_sms_id,
                thread_id as sms_thread_id,
                address as sms_number,
                body as sms_body,
                sms_date as sms_time,
                sms_type
            ')
                ->where('sms_type', $sms_type);

            $blocked = $this->getBlockedIdentifiers($user_id, 'sms');
            if (!empty($blocked)) {
                $query->whereNotIn('address', $blocked);
            }

            $results = $query->orderBy('sms_date', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Set up pagination
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_sms_type error: ' . $e->getMessage());
            return ['error' => 'get_sms_type error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets all SMS with new schema mapping.
     *
     * @param int $user_id
     * @param int $perPage
     * @return array
     */
    public function get_sms(int $user_id, int $perPage = 25): array
    {
        try {
            $builder = $this->db->table('tbl_sms');

            // Get total count for pagination
            $total = $this->get_count_Sms($user_id);

            // Get page number from request
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $query = $builder->select('
                counter as id,
                android_sms_id,
                thread_id as sms_thread_id,
                address as sms_number,
                body as sms_body,
                sms_date as sms_time,
                sms_type
            ');

            $blocked = $this->getBlockedIdentifiers($user_id, 'sms');
            if (!empty($blocked)) {
                $query->whereNotIn('address', $blocked);
            }

            $results = $query->orderBy('sms_date', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Set up pagination
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_sms error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets active SMS stats with new schema mapping.
     *
     * @param int $user_id
     * @param int $perPage
     * @return array
     */
    public function get_sms_active(int $user_id, int $perPage = 15): array
    {
        try {
            $builder = $this->db->table('tbl_sms')
                ->select('address as sms_number, thread_id as sms_thread_id, count(*) AS Totals');
                
            $blocked = $this->getBlockedIdentifiers($user_id, 'sms');
            if (!empty($blocked)) {
                $builder->whereNotIn('address', $blocked);
            }

            return $builder->groupBy('address')
                ->orderBy('Totals', 'DESC')
                ->limit($perPage)
                ->get()
                ->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'get_sms_active error: ' . $e->getMessage());
            return ['error' => 'get_sms_active error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets all call logs with new schema mapping.
     *
     * @param int $user_id
     * @param int $perPage
     * @return array
     */
    public function get_call_logs(int $user_id, int $perPage = 25): array
    {
        try {
            $builder = $this->db->table('tbl_logs');

            // Get total count for pagination
            $total = $this->get_count_Calls($user_id);

            // Get page number from request
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $query = $builder->select('
                counter,
                contact_name as Saved,
                phone_number as Caller,
                call_date as Timestamp,
                duration_seconds as Durations,
                call_type as Type
            ');
                
            $blocked = $this->getBlockedIdentifiers($user_id, 'call');
            if (!empty($blocked)) {
                $query->whereNotIn('phone_number', $blocked);
            }

            $results = $query->orderBy('call_date', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Set up pagination
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_call_logs error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets limited calls by type with new schema mapping.
     *
     * @param int $user_id
     * @param string $category
     * @param int $perPage
     * @return array
     */
    public function get_calls_limited(int $user_id, string $category, int $perPage = 25): array
    {
        try {
            $builder = $this->db->table('tbl_logs');

            // Get total count for this category
            $total = $this->applyOwnerDeviceFilter($builder, $user_id)
                ->where('call_type', $category)
                ->countAllResults();

            // Get page number from request
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $query = $builder->select('
                counter,
                contact_name as Saved,
                phone_number as Caller,
                call_date as Timestamp,
                duration_seconds as Durations,
                call_type as Type
            ')
                ->where('call_type', $category);
                
            $blocked = $this->getBlockedIdentifiers($user_id, 'call');
            if (!empty($blocked)) {
                $query->whereNotIn('phone_number', $blocked);
            }

            $results = $query->orderBy('call_date', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Set up pagination
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_calls_limited error: ' . $e->getMessage());
            return ['error' => 'get_calls_limited error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets active calls stats with new schema mapping.
     *
     * @param int $user_id
     * @param int $perPage
     * @return array
     */
    public function get_calls_active(int $user_id, int $perPage = 15): array
    {
        try {
            $builder = $this->db->table('tbl_logs')
                ->select('phone_number as Caller, contact_name as Saved, count(*) AS Totals');
                
            $blocked = $this->getBlockedIdentifiers($user_id, 'call');
            if (!empty($blocked)) {
                $builder->whereNotIn('phone_number', $blocked);
            }

            return $builder->groupBy('phone_number')
                ->orderBy('Totals', 'DESC')
                ->limit($perPage)
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_calls_active error: ' . $e->getMessage());
            return ['error' => 'get_calls_active error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets all apps with new schema mapping.
     *
     * @param int $user_id
     * @param int $perPage
     * @return array
     */
    public function get_apps(int $user_id, int $perPage = 20): array
    {
        try {
            $builder = $this->db->table('tbl_apps');

            // Get total count for pagination
            $total = $this->get_count_Apps($user_id);

            // Get page number from request
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $query = $builder->select('
                counter,
                app_name as Name,
                package_name as Package,
                version_code as Code,
                version_name,
                app_icon,
                app_size,
                permissions,
                permission_count,
                is_system_app,
                first_install_time,
                last_update_time,
                target_sdk,
                min_sdk
            ');
                
            $blocked = $this->getBlockedIdentifiers($user_id, 'app_usage');
            if (!empty($blocked)) {
                $query->whereNotIn('package_name', $blocked);
            }

            $results = $query->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Set up pagination
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_apps error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets SMS from sender(s) with new schema mapping.
     *
     * @param int $user_id
     * @param array|string $sender
     * @param int $perPage
     * @return array
     */
    public function get_sms_from_sender(int $user_id, $sender, int $perPage = 20): array
    {
        try {
            $builder = $this->db->table('tbl_sms');

            // Get total count for pagination
            if (is_array($sender)) {
                $total = $this->applyOwnerDeviceFilter($builder, $user_id)
                    ->whereIn('address', $sender)
                    ->countAllResults();
            } else {
                $total = $this->applyOwnerDeviceFilter($builder, $user_id)
                    ->where('address', $sender)
                    ->countAllResults();
            }

            // Get page number from request
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $query = $builder->select('
                counter as id,
                android_sms_id,
                thread_id as sms_thread_id,
                address as sms_number,
                body as sms_body,
                sms_date as sms_time,
                sms_type
            ')
                ->orderBy('sms_date', 'DESC');

            if (is_array($sender)) {
                $query->whereIn('address', $sender);
            } else {
                $query->where('address', $sender);
            }

            $results = $query->limit($perPage, $offset)->get()->getResultArray();

            // Set up pagination
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_sms_from_sender error: ' . $e->getMessage());
            return ['error' => 'get_sms_from_sender error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets pager instance.
     *
     * @return \CodeIgniter\Pager\Pager
     */
    public function getPager()
    {
        if (!$this->pager) {
            $this->pager = \Config\Services::pager();
        }
        return $this->pager;
    }

    /**
     * Gets all locations with pagination.
     *
     * @param int $user_id
     * @param int $perPage
     * @param bool $hasCoordsOnly Only return entries with non-null coordinates
     * @return array
     */
    public function get_locations(int $user_id, int $perPage = 25, bool $hasCoordsOnly = false): array
    {
        try {
            $builder = $this->db->table('tbl_location');
            $total = $this->get_count_Location($user_id, $hasCoordsOnly);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $builder->select('tbl_location.*, tbl_device_profile.device_model, tbl_device_profile.device_brand');
            $builder->join('tbl_device_profile', 'tbl_device_profile.device_id = tbl_location.device_id', 'left');
            $builder->where('tbl_location.owner_id', $user_id);

            if (!empty($this->deviceId) && $this->deviceId !== 'all') {
                $builder->where('tbl_location.device_id', $this->deviceId);
            }

            if ($hasCoordsOnly) {
                $builder->where('tbl_location.latitude IS NOT NULL')
                        ->where('tbl_location.longitude IS NOT NULL')
                        ->where('tbl_location.latitude !=', '')
                        ->where('tbl_location.longitude !=', '');
            }

            $results = $builder->orderBy('tbl_location.extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_locations error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets all activities with pagination.
     *
     * @param int $user_id
     * @param int $perPage
     * @return array
     */
    public function get_activities(int $user_id, int $perPage = 25): array
    {
        try {
            $builder = $this->db->table('tbl_activity');
            $total = $this->get_count_Activity($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $typeFilter = service('request')->getGet('type');
            $builder->select('tbl_activity.*, tbl_device_profile.device_model, tbl_device_profile.device_brand, tbl_device_profile.android_version');
            $builder->join('tbl_device_profile', 'tbl_device_profile.device_id = tbl_activity.device_id', 'left');
            $builder->where('tbl_activity.owner_id', $user_id);

            if (!empty($this->deviceId) && $this->deviceId !== 'all') {
                $builder->where('tbl_activity.device_id', $this->deviceId);
            }

            if (!empty($typeFilter) && $typeFilter !== 'all') {
                $builder->where('tbl_activity.activity_type', $typeFilter);
            }

            $results = $builder
                ->orderBy('tbl_activity.extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_activities error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_activity_stats(int $user_id): array
    {
        try {
            $builder = $this->db->table('tbl_activity');
            $this->applyOwnerDeviceFilter($builder, $user_id);

            $total = $builder->countAllResults();

            $todayBuilder = $this->db->table('tbl_activity');
            $this->applyOwnerDeviceFilter($todayBuilder, $user_id);
            $todayBuilder->where('DATE(created_at)', date('Y-m-d'));
            $todayCount = $todayBuilder->countAllResults();

            $typeBuilder = $this->db->table('tbl_activity');
            $this->applyOwnerDeviceFilter($typeBuilder, $user_id);
            $typeBuilder->select('activity_type, COUNT(*) as cnt');
            $typeBuilder->where('activity_type IS NOT NULL');
            $typeBuilder->groupBy('activity_type');
            $typeBuilder->orderBy('cnt', 'DESC');
            $typeBuilder->limit(1);
            $topType = $typeBuilder->get()->getRowArray();

            $battBuilder = $this->db->table('tbl_activity');
            $this->applyOwnerDeviceFilter($battBuilder, $user_id);
            $battBuilder->selectAvg('battery_level', 'avg_battery');
            $avgBattery = $battBuilder->get()->getRow()->avg_battery ?? 0;

            $distinctTypes = [];
            $rawTypes = $this->db->query(
                "SELECT DISTINCT activity_type FROM tbl_activity WHERE owner_id = ? AND activity_type IS NOT NULL AND activity_type != '' ORDER BY activity_type ASC",
                [$user_id]
            )->getResultArray();
            $distinctTypes = array_column($rawTypes, 'activity_type');

            return [
                'total' => $total,
                'today' => $todayCount,
                'top_type' => $topType['activity_type'] ?? null,
                'top_type_count' => $topType['cnt'] ?? 0,
                'avg_battery' => round((float) $avgBattery),
                'distinct_types' => $distinctTypes,
            ];
        } catch (\Exception $e) {
            log_message('error', 'get_activity_stats error: ' . $e->getMessage());
            return [
                'total' => 0, 'today' => 0, 'top_type' => null,
                'top_type_count' => 0, 'avg_battery' => 0, 'distinct_types' => [],
            ];
        }
    }

    // ── Advanced Extractor Counts ─────────────────────────────────────────────

    public function get_count_DeviceContext(int $user_id): int
    {
        return $this->getCount('tbl_device_context', $user_id);
    }
    public function get_count_NetworkInfo(int $user_id): int
    {
        return $this->getCount('tbl_network_info', $user_id);
    }
    public function get_count_Accounts(int $user_id): int
    {
        return $this->getCount('tbl_accounts', $user_id);
    }
    public function get_count_Calendar(int $user_id): int
    {
        return $this->getCount('tbl_calendar_events', $user_id);
    }
    public function get_count_AppUsage(int $user_id): int
    {
        $blocked = $this->getBlockedIdentifiers($user_id, 'app_usage');
        return $this->getCount('tbl_app_usage', $user_id, [], 'package_name', $blocked);
    }
    public function get_count_Notifications(int $user_id): int
    {
        $blocked = $this->getBlockedIdentifiers($user_id, 'notification');
        return $this->getCount('tbl_notifications', $user_id, [], 'package_name', $blocked);
    }
    public function get_count_Bluetooth(int $user_id): int
    {
        return $this->getCount('tbl_bluetooth', $user_id);
    }
    public function get_count_Sensors(int $user_id): int
    {
        return $this->getCount('tbl_sensor_profile', $user_id);
    }
    public function get_count_SecurityAudit(int $user_id): int
    {
        return $this->getCount('tbl_security_audit', $user_id);
    }

    public function get_count_CameraInfo(int $user_id): int
    {
        return $this->getCount('tbl_camera_info', $user_id);
    }

    public function get_count_BatteryStats(int $user_id): int
    {
        return $this->getCount('tbl_battery_stats', $user_id);
    }

    public function get_count_Accessibility(int $user_id): int
    {
        return $this->getCount('tbl_accessibility_services', $user_id);
    }

    public function get_count_InputMethods(int $user_id): int
    {
        return $this->getCount('tbl_input_methods', $user_id);
    }

    public function get_count_Processes(int $user_id): int
    {
        return $this->getCount('tbl_running_processes', $user_id);
    }

    public function get_count_ProcInfo(int $user_id): int
    {
        return $this->getCount('tbl_proc_info', $user_id);
    }

    public function get_count_CapturedMedia(int $user_id): int
    {
        return $this->getCount('tbl_captured_media', $user_id);
    }

    public function get_count_SimConfig(int $user_id): int
    {
        return $this->getCount('tbl_sim_configs', $user_id);
    }

    // ── Export Fetch Methods ─────────────────────────────────────────────
    public function export_device_context(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_device_context', $user_id)->limit($limit)->get()->getResultArray();
    }
    public function export_network_info(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_network_info', $user_id)->limit($limit)->get()->getResultArray();
    }
    public function export_accounts(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_accounts', $user_id)->limit($limit)->get()->getResultArray();
    }
    public function export_calendar_events(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_calendar_events', $user_id)->limit($limit)->get()->getResultArray();
    }
    public function export_app_usage(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_app_usage', $user_id)->limit($limit)->get()->getResultArray();
    }
    public function export_notifications(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_notifications', $user_id)->limit($limit)->get()->getResultArray();
    }
    public function export_bluetooth(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_bluetooth', $user_id)->limit($limit)->get()->getResultArray();
    }
    public function export_sensors(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_sensor_profile', $user_id)->limit($limit)->get()->getResultArray();
    }
    public function export_security_audit(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_security_audit', $user_id)->limit($limit)->get()->getResultArray();
    }
    public function export_device_files(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_device_files', $user_id)->limit($limit)->get()->getResultArray();
    }

    // ── Advanced Extractor Paginated Queries ──────────────────────────────────

    public function get_device_context(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_DeviceContext($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_device_context', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_device_context: ' . $e->getMessage());
            return [];
        }
    }

    public function get_network_info(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_NetworkInfo($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_network_info', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_network_info: ' . $e->getMessage());
            return [];
        }
    }

    public function get_nearby_wifi(int $network_info_id): array
    {
        try {
            return $this->db->table('tbl_nearby_wifi')
                ->where('network_info_id', $network_info_id)
                ->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_nearby_wifi: ' . $e->getMessage());
            return [];
        }
    }

    public function get_accounts(int $user_id, int $perPage = 50): array
    {
        try {
            $total = $this->get_count_Accounts($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_accounts', $user_id)
                ->orderBy('account_type', 'ASC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_accounts: ' . $e->getMessage());
            return [];
        }
    }

    public function get_calendar_events(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Calendar($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_calendar_events', $user_id)
                ->orderBy('start_time', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_calendar_events: ' . $e->getMessage());
            return [];
        }
    }

    public function get_app_usage(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_AppUsage($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_app_usage', $user_id)
                ->orderBy('foreground_time_ms', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_app_usage: ' . $e->getMessage());
            return [];
        }
    }

    public function get_count_app_usage_packages(int $user_id): int
    {
        try {
            $builder = $this->db->table('tbl_app_usage');
            $this->applyOwnerDeviceFilter($builder, $user_id);
            $row = $builder->select('COUNT(DISTINCT package_name) AS cnt', false)
                ->get()
                ->getRowArray();

            return (int) ($row['cnt'] ?? 0);
        } catch (\Exception $e) {
            log_message('error', 'get_count_app_usage_packages: ' . $e->getMessage());

            return 0;
        }
    }

    public function get_app_usage_grouped(int $user_id, int $perPage = 50): array
    {
        try {
            $total = $this->get_count_app_usage_packages($user_id);
            $page = (int) (service('request')->getGet('page') ?? 1);
            $offset = ($page - 1) * $perPage;

            $builder = $this->db->table('tbl_app_usage');
            $this->applyOwnerDeviceFilter($builder, $user_id);
            $results = $builder
                ->select('package_name', false)
                ->select('MAX(app_name) AS app_name', false)
                ->select('SUM(foreground_time_ms) AS foreground_time_ms', false)
                ->select('MAX(last_time_used) AS last_time_used', false)
                ->select('COUNT(*) AS snapshot_count', false)
                ->select('MAX(extracted_at) AS last_extracted_at', false)
                ->select('MAX(is_system_app) AS is_system_app', false)
                ->groupBy('package_name')
                ->orderBy('SUM(foreground_time_ms)', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_app_usage_grouped: ' . $e->getMessage());

            return [];
        }
    }

    public function get_count_app_usage_for_package(int $user_id, string $package_name): int
    {
        try {
            return $this->fq('tbl_app_usage', $user_id)
                ->where('package_name', $package_name)
                ->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'get_count_app_usage_for_package: ' . $e->getMessage());

            return 0;
        }
    }

    public function get_app_usage_for_package(int $user_id, string $package_name, int $perPage = 50): array
    {
        try {
            $total = $this->get_count_app_usage_for_package($user_id, $package_name);
            $page = (int) (service('request')->getGet('page') ?? 1);
            $offset = ($page - 1) * $perPage;

            $results = $this->fq('tbl_app_usage', $user_id)
                ->where('package_name', $package_name)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_app_usage_for_package: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * @return array{rows: array, total: int}
     */
    public function get_app_detail_by_package(int $user_id, string $package_name): array
    {
        try {
            $builder = $this->fq('tbl_apps', $user_id);
            $row = $builder
                ->select('app_name, package_name, version_name, version_code, app_size, permission_count, target_sdk, min_sdk, first_install_time, last_update_time, is_system_app')
                ->where('package_name', $package_name)
                ->get()
                ->getRowArray();
            if ($row && !empty($row['first_install_time'])) {
                $ts = is_numeric($row['first_install_time'])
                    ? (strlen($row['first_install_time']) > 11 ? (int)($row['first_install_time'] / 1000) : (int)$row['first_install_time'])
                    : strtotime($row['first_install_time']);
                $row['first_install_display'] = $ts ? date('M j, Y, g:i A', $ts) : '—';
            } else {
                $row['first_install_display'] = '—';
            }
            if ($row && !empty($row['last_update_time'])) {
                $ts = is_numeric($row['last_update_time'])
                    ? (strlen($row['last_update_time']) > 11 ? (int)($row['last_update_time'] / 1000) : (int)$row['last_update_time'])
                    : strtotime($row['last_update_time']);
                $row['last_update_display'] = $ts ? date('M j, Y, g:i A', $ts) : '—';
            } else {
                $row['last_update_display'] = '—';
            }
            if ($row && !empty($row['app_size'])) {
                $size = (int)$row['app_size'];
                if ($size > 1048576) {
                    $row['app_size_display'] = round($size / 1048576, 1) . ' MB';
                } elseif ($size > 1024) {
                    $row['app_size_display'] = round($size / 1024, 1) . ' KB';
                } else {
                    $row['app_size_display'] = $size . ' B';
                }
            } else {
                $row['app_size_display'] = '—';
            }
            return $row ?: [];
        } catch (\Exception $e) {
            log_message('error', 'get_app_detail_by_package: ' . $e->getMessage());
            return [];
        }
    }

    public function get_app_usage_package_summary(int $user_id, string $package_name): array
    {
        try {
            $builder = $this->fq('tbl_app_usage', $user_id);
            $row = $builder
                ->select('MAX(app_name) AS app_name', false)
                ->select('SUM(foreground_time_ms) AS foreground_time_ms', false)
                ->select('MAX(last_time_used) AS last_time_used', false)
                ->select('COUNT(*) AS snapshot_count', false)
                ->select('MAX(is_system_app) AS is_system_app', false)
                ->where('package_name', $package_name)
                ->get()
                ->getRowArray();

            return $row ?: [];
        } catch (\Exception $e) {
            log_message('error', 'get_app_usage_package_summary: ' . $e->getMessage());

            return [];
        }
    }

    public function get_app_usage_sessions_for_package(int $user_id, string $package_name, int $perPage = 50): array
    {
        try {
            $builder = $this->fq('tbl_app_usage', $user_id);
            $usageIds = $builder
                ->select('id')
                ->where('package_name', $package_name)
                ->get()
                ->getResultArray();

            if (empty($usageIds)) {
                return [];
            }

            $ids = array_column($usageIds, 'id');

            return $this->fq('tbl_app_usage_sessions', $user_id)
                ->whereIn('app_usage_id', $ids)
                ->orderBy('timestamp', 'DESC')
                ->limit($perPage)
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_app_usage_sessions_for_package: ' . $e->getMessage());

            return [];
        }
    }

    public function get_notifications(int $user_id, int $perPage = 50): array
    {
        try {
            $total = $this->get_count_Notifications($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_notifications', $user_id)
                ->orderBy('notification_timestamp', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_notifications: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Group notifications by app name, with sender/package fallbacks when app_name is empty.
     */
    private function notificationGroupKeySql(): string
    {
        return "COALESCE(
            NULLIF(TRIM(package_name), ''),
            NULLIF(TRIM(app_name), ''),
            NULLIF(TRIM(sender), ''),
            '(Unlabeled)'
        )";
    }

    private function applyNotificationGroupFilter($builder, string $group_key): void
    {
        $builder->where($this->notificationGroupKeySql() . ' = ' . $this->db->escape($group_key), null, false);
    }

    private function notificationScreenCountSelect(): string
    {
        if ($this->db->fieldExists('is_screen_notification', 'tbl_notifications')) {
            return 'SUM(is_screen_notification) AS screen_count';
        }

        return '0 AS screen_count';
    }

    public function get_count_notification_groups(int $user_id): int
    {
        try {
            $groupSql = $this->notificationGroupKeySql();
            $builder = $this->fq('tbl_notifications', $user_id);
            $row = $builder
                ->select("COUNT(DISTINCT {$groupSql}) AS cnt", false)
                ->get()
                ->getRowArray();

            return (int) ($row['cnt'] ?? 0);
        } catch (\Exception $e) {
            log_message('error', 'get_count_notification_groups: ' . $e->getMessage());

            return 0;
        }
    }

    /** @deprecated Use get_count_notification_groups() */
    public function get_count_notification_packages(int $user_id): int
    {
        return $this->get_count_notification_groups($user_id);
    }

    public function get_notifications_grouped(int $user_id, int $perPage = 50): array
    {
        try {
            $total = $this->get_count_notification_groups($user_id);
            $page = (int) (service('request')->getGet('page') ?? 1);
            $offset = ($page - 1) * $perPage;
            $groupSql = $this->notificationGroupKeySql();

            $results = $this->db->table('tbl_notifications')
                ->select("{$groupSql} AS group_key", false)
                ->select('MAX(app_name) AS app_name', false)
                ->select('MAX(package_name) AS package_name', false)
                ->select('MAX(sender) AS sender', false)
                ->select('COUNT(*) AS notification_count', false)
                ->select($this->notificationScreenCountSelect(), false)
                ->select('MAX(notification_timestamp) AS latest_timestamp', false)
                ->select(
                    "SUBSTRING_INDEX(GROUP_CONCAT(title ORDER BY notification_timestamp DESC SEPARATOR '||'), '||', 1) AS latest_title",
                    false
                )
                ->groupBy($groupSql, false)
                ->orderBy('MAX(notification_timestamp)', 'DESC', false)
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_notifications_grouped: ' . $e->getMessage());

            return [];
        }
    }

    public function get_count_notifications_for_group(int $user_id, string $group_key): int
    {
        try {
            $builder = $this->fq('tbl_notifications', $user_id);
            $this->applyNotificationGroupFilter($builder, $group_key);

            return $builder->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'get_count_notifications_for_group: ' . $e->getMessage());

            return 0;
        }
    }

    /** @deprecated Use get_count_notifications_for_group() */
    public function get_count_notifications_for_package(int $user_id, string $package_name): int
    {
        return $this->get_count_notifications_for_group($user_id, $package_name);
    }

    public function get_notifications_for_group(int $user_id, string $group_key, int $perPage = 50): array
    {
        try {
            $total = $this->get_count_notifications_for_group($user_id, $group_key);
            $page = (int) (service('request')->getGet('page') ?? 1);
            $offset = ($page - 1) * $perPage;

            $builder = $this->fq('tbl_notifications', $user_id);
            $this->applyNotificationGroupFilter($builder, $group_key);

            $results = $builder
                ->orderBy('notification_timestamp', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_notifications_for_group: ' . $e->getMessage());

            return [];
        }
    }

    /** @deprecated Use get_notifications_for_group() */
    public function get_notifications_for_package(int $user_id, string $package_name, int $perPage = 50): array
    {
        return $this->get_notifications_for_group($user_id, $package_name, $perPage);
    }

    /**
     * @return array<string, mixed>
     */
    public function get_notifications_group_summary(int $user_id, string $group_key): array
    {
        try {
            $builder = $this->db->table('tbl_notifications')
                ->select('MAX(app_name) AS app_name', false)
                ->select('MAX(package_name) AS package_name', false)
                ->select('MAX(sender) AS sender', false)
                ->select('COUNT(*) AS notification_count', false)
                ->select($this->notificationScreenCountSelect(), false)
                ->select('MAX(notification_timestamp) AS latest_timestamp', false);
            $this->applyNotificationGroupFilter($builder, $group_key);

            $row = $builder->get()->getRowArray();
            if ($row) {
                $row['display_name'] = $group_key;
            }

            return $row ?: [];
        } catch (\Exception $e) {
            log_message('error', 'get_notifications_group_summary: ' . $e->getMessage());

            return [];
        }
    }

    /** @deprecated Use get_notifications_group_summary() */
    public function get_notifications_package_summary(int $user_id, string $package_name): array
    {
        return $this->get_notifications_group_summary($user_id, $package_name);
    }

    public function get_bluetooth(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Bluetooth($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_bluetooth', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            // For each snapshot, fetch paired devices
            foreach ($results as &$row) {
                $row['paired_devices'] = $this->db->table('tbl_bluetooth_paired')
                    ->where('bluetooth_id', $row['id'])->get()->getResultArray();
            }
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_bluetooth: ' . $e->getMessage());
            return [];
        }
    }

    public function get_sensor_profile(int $user_id, int $perPage = 50): array
    {
        try {
            $total = $this->get_count_Sensors($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_sensor_profile', $user_id)
                ->orderBy('type_id', 'ASC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_sensor_profile: ' . $e->getMessage());
            return [];
        }
    }

    public function get_security_audit(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_SecurityAudit($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_security_audit', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            if (!empty($results)) {
                foreach ($results as &$r) {
                    if (!empty($r['user_ca_certs_json'])) {
                        $decoded = json_decode($r['user_ca_certs_json'], true);
                        $r['user_ca_certs'] = is_array($decoded) ? $decoded : [];
                    } else {
                        $r['user_ca_certs'] = [];
                    }
                    if (!empty($r['open_ports_json'])) {
                        $decoded = json_decode($r['open_ports_json'], true);
                        $r['open_ports'] = is_array($decoded) ? $decoded : [];
                    } else {
                        $r['open_ports'] = [];
                    }
                }
                unset($r);
            }
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_security_audit: ' . $e->getMessage());
            return [];
        }
    }

    public function get_camera_info(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_CameraInfo($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_camera_info', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['available_focal_lengths','available_effects','available_scene_modes','available_video_stabilization','available_ae_modes','available_af_modes'] as $col) {
                    if (isset($r[$col]) && is_string($r[$col])) {
                        $r[$col] = json_decode($r[$col], true) ?? [];
                    }
                }
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_camera_info: ' . $e->getMessage());
            return [];
        }
    }

    public function get_battery_stats(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_BatteryStats($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_battery_stats', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_battery_stats: ' . $e->getMessage());
            return [];
        }
    }

    public function get_accessibility(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Accessibility($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_accessibility_services', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_accessibility: ' . $e->getMessage());
            return [];
        }
    }

    public function get_input_methods(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_InputMethods($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_input_methods', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            // Attach subtypes
            foreach ($results as &$r) {
                $subs = $this->db->table('tbl_input_method_subtypes')
                    ->where('input_method_id', $r['id'])
                    ->get()->getResultArray();
                $r['subtypes'] = $subs;
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_input_methods: ' . $e->getMessage());
            return [];
        }
    }

    public function get_proc_info(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_ProcInfo($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_proc_info', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            foreach ($results as &$r) {
                foreach (['meminfo_json','cpuinfo_json','stat_json','uptime_json','net_interfaces_json','net_connections_json'] as $col) {
                    if (isset($r[$col]) && is_string($r[$col])) {
                        $r[$col] = json_decode($r[$col], true) ?? [];
                    }
                }
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_proc_info: ' . $e->getMessage());
            return [];
        }
    }

    public function get_processes(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Processes($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_running_processes', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            // Attach process details and services
            foreach ($results as &$r) {
                $details = $this->db->table('tbl_running_process_details')
                    ->where('running_processes_id', $r['id'])
                    ->get()->getResultArray();
                $r['process_details'] = $details;
                $services = $this->db->table('tbl_running_services')
                    ->where('running_process_id', $r['id'])
                    ->get()->getResultArray();
                $r['services'] = $services;
                // Decode pkg_list_json
                if (!empty($r['pkg_list_json'])) {
                    $r['pkg_list'] = json_decode($r['pkg_list_json'], true);
                }
            }
            unset($r);
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_processes: ' . $e->getMessage());
            return [];
        }
    }

    public function get_categorized_sms_counts(int $userId): array
{
        $total = $this->db->table('tbl_sms')->where('owner_id', $userId)->countAllResults();

        $all_sms = $this->db->table('tbl_sms')
            ->select('address, body')
            ->where('owner_id', $userId)
            ->orderBy('sms_date', 'DESC')
            ->get()
            ->getResultArray();

        $counts = [
            'financial' => 0, 'otp' => 0, 'promo' => 0,
            'utility' => 0, 'service' => 0, 'malicious' => 0,
            'personal' => 0, 'total' => $total
        ];

        if ($total === 0) {
            return $counts;
        }

        $fin_keys = ['bank', 'mpesa', 'equity', 'kcb', 'transaction', 'kes', 'paid', 'received', 'balance', 'credited', 'debited', 'reversal'];
        $otp_keys = ['code', 'otp', 'verification', 'login', 'password reset'];
        $promo_keys = ['offer', 'discount', '% off', 'sale', 'win', 'subscribe', 'buy', 'promo', 'exclusive', 'betting', 'bet', 'jackpot'];
        $util_keys = ['kplc', 'water', 'token', 'zuku', 'fiber', 'safaricom home', 'bill', 'due date'];
        $serv_keys = ['uber', 'bolt', 'jumia', 'dhl', 'courier', 'delivery', 'ride', 'food'];
        $mal_keys = ['won lottery', 'congratulations you have won', 'prize', 'kshs 50,000'];

        $keywordMap = [
            'malicious' => $mal_keys,
            'otp' => $otp_keys,
            'financial' => array_merge($fin_senders, $fin_keys),
            'utility' => $util_keys,
            'service' => $serv_keys,
            'promo' => $promo_keys,
        ];

        $rawBodies = [];
        foreach ($all_sms as $sms) {
            $body = $this->decode_sms_body($sms['body']);
            $rawBodies[] = [
                'address' => strtolower($sms['address']),
                'body' => strtolower($body),
            ];
        }

        $autoLabels = Mod_ML_Analyzer::autoLabelSms($rawBodies, $keywordMap);

        if ($total < 20) {
            foreach ($autoLabels as $label) {
                if (isset($counts[$label])) $counts[$label]++;
            }
            return $counts;
        }

        $mlLimit = 500;
        $useMl = $total <= $mlLimit;
        $mlIndices = $useMl ? range(0, $total - 1) : array_rand($rawBodies, $mlLimit);

        try {
            $mlTexts = array_map(fn($i) => $rawBodies[$i]['body'], $mlIndices);
            [$vectors, $vectorizer] = Mod_ML_Analyzer::vectorize($mlTexts, 200);

            $trainSamples = [];
            $trainLabels = [];
            foreach ($mlIndices as $pos => $origIdx) {
                $trainSamples[] = $vectors[$pos];
                $trainLabels[] = $autoLabels[$origIdx];
            }

            $classifier = Mod_ML_Analyzer::trainNaiveBayes($trainSamples, $trainLabels);
            $predictions = $classifier->predict($vectors);

            foreach ($mlIndices as $pos => $origIdx) {
                $label = $predictions[$pos];
                if (isset($counts[$label])) $counts[$label]++;
            }

            if (!$useMl) {
                foreach ($autoLabels as $i => $label) {
                    if (!in_array($i, $mlIndices, true)) {
                        if (isset($counts[$label])) $counts[$label]++;
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'NaiveBayes SMS categorization failed: ' . $e->getMessage());
            foreach ($autoLabels as $label) {
                if (isset($counts[$label])) $counts[$label]++;
            }
        }

        return $counts;
    }

    /**
     * Get categorized call counts for dashboard.
     */
    public function get_categorized_call_counts(int $userId): array
    {
        $builder = $this->db->table('tbl_logs');
        $all_logs = $builder->select('phone_number, contact_name, call_type, call_date')
            ->where('owner_id', $userId)
            ->get()
            ->getResultArray();

        $counts = [
            'family' => 0,
            'business' => 0,
            'intl' => 0,
            'urgent' => 0,
            'spam' => 0,
            'new' => 0,
            'total' => count($all_logs)
        ];

        if (empty($all_logs))
            return $counts;

        $freq = [];
        $recent_threshold = strtotime('-24 hours') * 1000;

        foreach ($all_logs as $log) {
            $num = $log['phone_number'];
            if (!isset($freq[$num])) {
                $freq[$num] = ['count' => 0, 'recent' => 0, 'name' => $log['contact_name']];
            }
            $freq[$num]['count']++;
            if ($log['call_date'] > $recent_threshold) {
                $freq[$num]['recent']++;
            }
        }

        $business_keys = ['ltd', 'inc', 'bank', 'service', 'delivery', 'support', 'office'];

        foreach ($all_logs as $log) {
            $num = $log['phone_number'];
            $name = strtolower($log['contact_name'] ?? '');
            $type = $log['call_type'];

            // Spam
            if ($type === 'blocked' || ($type === 'rejected' && empty($log['contact_name']) && $freq[$num]['count'] > 3)) {
                $counts['spam']++;
                continue;
            }

            // Intl
            if (strpos($num, '+') === 0 && strpos($num, '+254') !== 0) {
                $counts['intl']++;
                continue;
            }

            // Family
            if (!empty($log['contact_name']) && $freq[$num]['count'] > 10) {
                $counts['family']++;
                continue;
            }

            // Business
            $is_biz = false;
            foreach ($business_keys as $key) {
                if (strpos($name, $key) !== false) {
                    $is_biz = true;
                    break;
                }
            }
            if ($is_biz) {
                $counts['business']++;
                continue;
            }

            // Urgent
            if (empty($log['contact_name']) && $freq[$num]['recent'] > 5) {
                $counts['urgent']++;
                continue;
            }

            // New
            if (empty($log['contact_name']) && $freq[$num]['count'] == 1) {
                $counts['new']++;
                continue;
            }
        }

        return $counts;
    }
    /**
     * Get categorized SMS items with pagination.
     */
    public function get_categorized_sms(int $userId, string $category, int $perPage = 20, int $page = 1): array
    {
        $all_sms = $this->db->table('tbl_sms')
            ->select('address, body, sms_date as sms_time, sms_type')
            ->where('owner_id', $userId)
            ->orderBy('sms_date', 'DESC')
            ->get()
            ->getResultArray();

        $fin_senders = ['kcb', 'kcb_mobile', 'equitybank', 'equity', 'coopbank', 'mcoopcash', 'ncba', 'ncba_loop', 'absa', 'absabank', 'stanbic', 'stanbic_ke', 'familybank', 'stanchart', 'dtb', 'im_bank', 'postbank', 'mpesa'];
        $fin_keys = ['bank', 'mpesa', 'equity', 'kcb', 'transaction', 'kes', 'paid', 'received', 'balance', 'credited', 'debited', 'reversal'];
        $otp_keys = ['code', 'otp', 'verification', 'login', 'password reset'];
        $promo_keys = ['offer', 'discount', '% off', 'sale', 'win', 'subscribe', 'buy', 'promo', 'exclusive', 'betting', 'bet', 'jackpot'];
        $util_keys = ['kplc', 'water', 'token', 'zuku', 'fiber', 'safaricom home', 'bill', 'due date'];
        $serv_keys = ['uber', 'bolt', 'jumia', 'dhl', 'courier', 'delivery', 'ride', 'food'];
        $mal_keys = ['won lottery', 'congratulations you have won', 'prize', 'kshs 50,000'];

        $filtered = [];

        foreach ($all_sms as $sms) {
            $body_text = $this->decode_sms_body($sms['body']);

            $sms['body'] = $body_text; // Return decoded body
            $body = strtolower($body_text);
            $addr = strtolower($sms['address']);
            $addr_len = strlen($addr);

            $current_cat = 'personal';
            $match = false;

            // Priority 1: Financial Sender IDs
            if (in_array($addr, $fin_senders)) {
                $current_cat = 'financial';
                $match = true;
            }

            // Priority 2: Malicious check
            if (!$match) {
                foreach ($mal_keys as $key) {
                    if (strpos($body, $key) !== false) {
                        $current_cat = 'malicious';
                        $match = true;
                        break;
                    }
                }
            }

            // OTP
            if (!$match) {
                foreach ($otp_keys as $key) {
                    if (strpos($body, $key) !== false) {
                        $current_cat = 'otp';
                        $match = true;
                        break;
                    }
                }
            }

            // Financial Keywords
            if (!$match && $addr_len < 10) {
                foreach ($fin_keys as $key) {
                    if (strpos($body, $key) !== false) {
                        $current_cat = 'financial';
                        $match = true;
                        break;
                    }
                }
            }

            // Utility
            if (!$match) {
                foreach ($util_keys as $key) {
                    if (strpos($body, $key) !== false) {
                        $current_cat = 'utility';
                        $match = true;
                        break;
                    }
                }
            }

            // Service
            if (!$match) {
                foreach ($serv_keys as $key) {
                    if (strpos($body, $key) !== false) {
                        $current_cat = 'service';
                        $match = true;
                        break;
                    }
                }
            }

            // Promo
            if (!$match) {
                if ($addr_len < 10) {
                    $current_cat = 'promo';
                    $match = true;
                } else {
                    foreach ($promo_keys as $key) {
                        if (strpos($body, $key) !== false) {
                            $current_cat = 'promo';
                            $match = true;
                            break;
                        }
                    }
                }
            }

            if (!$match && $addr_len >= 10) {
                $current_cat = 'personal';
            }

            if ($current_cat === $category) {
                $filtered[] = $sms;
            }
        }

        $total = count($filtered);
        $offset = ($page - 1) * $perPage;
        $data = array_slice($filtered, $offset, $perPage);

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Get categorized call items with pagination.
     */
    public function get_categorized_calls(int $userId, string $category, int $perPage = 20, int $page = 1): array
    {
        $all_logs = $this->db->table('tbl_logs')
            ->select('phone_number, contact_name, call_type, call_date, duration_seconds')
            ->where('owner_id', $userId)
            ->orderBy('call_date', 'DESC')
            ->get()
            ->getResultArray();

        $freq = [];
        $recent_threshold = strtotime('-24 hours') * 1000;
        foreach ($all_logs as $log) {
            $num = $log['phone_number'];
            if (!isset($freq[$num]))
                $freq[$num] = ['count' => 0, 'recent' => 0];
            $freq[$num]['count']++;
            if ($log['call_date'] > $recent_threshold)
                $freq[$num]['recent']++;
        }

        $business_keys = ['ltd', 'inc', 'bank', 'service', 'delivery', 'support', 'office'];
        $filtered = [];

        foreach ($all_logs as $log) {
            $num = $log['phone_number'];
            $name = strtolower($log['contact_name'] ?? '');
            $type = $log['call_type'];

            $current_cat = 'other';

            if ($type === 'blocked' || ($type === 'rejected' && empty($log['contact_name']) && $freq[$num]['count'] > 3)) {
                $current_cat = 'spam';
            } else if (strpos($num, '+') === 0 && strpos($num, '+254') !== 0) {
                $current_cat = 'intl';
            } else if (!empty($log['contact_name']) && $freq[$num]['count'] > 10) {
                $current_cat = 'family';
            } else {
                $is_biz = false;
                foreach ($business_keys as $key) {
                    if (strpos($name, $key) !== false) {
                        $is_biz = true;
                        break;
                    }
                }
                if ($is_biz) {
                    $current_cat = 'business';
                } else if (empty($log['contact_name']) && $freq[$num]['recent'] > 5) {
                    $current_cat = 'urgent';
                } else if (empty($log['contact_name']) && $freq[$num]['count'] == 1) {
                    $current_cat = 'new';
                }
            }

            if ($current_cat === $category) {
                $filtered[] = $log;
            }
        }

        $total = count($filtered);
        $offset = ($page - 1) * $perPage;
        $data = array_slice($filtered, $offset, $perPage);

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Search SMS by keyword.
     */
    public function search_sms(int $userId, string $query): array
    {
        return $this->db->table('tbl_sms')
            ->select('address as Number, body as Message, sms_date as Date, sms_type as Type')
            ->where('owner_id', $userId)
            ->groupStart()
            ->like('address', $query)
            ->orLike('body', $query)
            ->groupEnd()
            ->orderBy('sms_date', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Search Call Logs by keyword.
     */
    public function search_calls(int $userId, string $query): array
    {
        return $this->db->table('tbl_logs')
            ->select('contact_name as Name, phone_number as Number, call_date as Date, call_type as Type, duration_seconds as Duration')
            ->where('owner_id', $userId)
            ->groupStart()
            ->like('contact_name', $query)
            ->orLike('phone_number', $query)
            ->groupEnd()
            ->orderBy('call_date', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Search Contacts by keyword.
     */
    public function search_contacts(int $userId, string $query): array
    {
        return $this->db->table('tbl_contacts')
            ->select('display_name as Name, phone_numbers as Number, last_contacted, contact_id')
            ->where('owner_id', $userId)
            ->groupStart()
            ->like('display_name', $query)
            ->orLike('phone_numbers', $query)
            ->groupEnd()
            ->orderBy('display_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Search Files by keyword.
     */
    public function search_files(int $userId, string $query): array
    {
        return $this->db->table('tbl_device_files')
            ->select('name as file_name, path as file_path, size_bytes as file_size, category as file_type, last_modified')
            ->where('owner_id', $userId)
            ->groupStart()
            ->like('name', $query)
            ->orLike('path', $query)
            ->groupEnd()
            ->orderBy('last_modified', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Extracts financial transactions from SMS for Advanced Analysis.
     */
    public function get_financial_transactions(int $userId): array
    {
        $all_sms = $this->db->table('tbl_sms')
            ->select('address, body, sms_date')
            ->where('owner_id', $userId)
            ->orderBy('sms_date', 'DESC')
            ->get()
            ->getResultArray();

        $transactions = [];
        $fin_senders = ['kcb', 'kcb_mobile', 'equitybank', 'equity', 'coopbank', 'mcoopcash', 'ncba', 'ncba_loop', 'absa', 'absabank', 'stanbic', 'stanbic_ke', 'familybank', 'stanchart', 'dtb', 'im_bank', 'postbank', 'mpesa'];
        $fin_keys = ['kes', 'ksh', 'paid', 'received', 'credited', 'debited', 'balance', 'transaction'];

        foreach ($all_sms as $sms) {
            $body_text = $this->decode_sms_body($sms['body']);
            $body = strtolower($body_text);
            $addr = strtolower($sms['address']);
            $addr_len = strlen($addr);

            $is_fin = in_array($addr, $fin_senders);
            if (!$is_fin && $addr_len < 10) {
                foreach ($fin_keys as $key) {
                    if (strpos($body, $key) !== false) {
                        $is_fin = true;
                        break;
                    }
                }
            }

            if ($is_fin) {
                // Regex for amount: Ksh/KES followed by numbers (supports comma as thousand separator)
                if (preg_match('/(?:ksh|kes)[\s]?([\d,]+(?:\.\d{2})?)/i', $body, $matches)) {
                    $amount = (float) str_replace(',', '', $matches[1]);

                    // Categorization
                    $type = 'personal';
                    if (strpos($body, 'kplc') !== false || strpos($body, 'token') !== false)
                        $type = 'utility';
                    else if (strpos($body, 'airtime') !== false)
                        $type = 'airtime';
                    else if (strpos($body, 'sent to') !== false || strpos($body, 'paid to') !== false)
                        $type = 'transfer';
                    else if (strpos($body, 'received') !== false || strpos($body, 'credited') !== false)
                        $type = 'income';

                    $transactions[] = [
                        'date' => $sms['sms_date'],
                        'amount' => $amount,
                        'type' => $type,
                        'description' => $body_text,
                        'sender' => $sms['address'],
                        'month' => date('Y-m', $sms['sms_date'] / 1000)
                    ];
                }
            }
        }

        return $transactions;
    }
    /**
     * Get all location records for heatmap.
     */
    public function get_location_history(int $userId): array
    {
        try {
            return $this->db->table('tbl_location')
                ->where('owner_id', $userId)
                ->orderBy('extracted_at', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_location_history error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets location history for user filtered by date.
     *
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function get_location_history_filtered(int $userId, string $startDate, string $endDate): array
    {
        try {
            return $this->db->table('tbl_location')
                ->where('owner_id', $userId)
                ->where('extracted_at >=', $startDate . ' 00:00:00')
                ->where('extracted_at <=', $endDate . ' 23:59:59')
                ->orderBy('extracted_at', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_location_history_filtered error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get device health stats from profile.
     */

    public function get_device_health(int $userId): array
    {
        // Try getting device_id from location updates first (most frequent)
        $query = $this->db->table('tbl_location')
            ->select('device_id')
            ->where('owner_id', $userId)
            ->orderBy('location_time', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();

        $device_id = $query ? $query->device_id : null;

        // Fallback to apps if no location data
        if (!$device_id) {
            $query = $this->db->table('tbl_apps')
                ->select('device_id')
                ->where('owner_id', $userId)
                ->orderBy('updated_at', 'DESC')
                ->limit(1)
                ->get()
                ->getRow();
            $device_id = $query ? $query->device_id : null;
        }

        if (!$device_id)
            return [];

        $profile = $this->db->table('tbl_device_profile')
            ->where('device_id', $device_id)
            ->get()
            ->getRowArray() ?? [];

        // Get latest activity for network/battery
        $activity = $this->db->table('tbl_activity')
            ->where('owner_id', $userId)
            ->where('device_id', $device_id)
            ->orderBy('extracted_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        // Get latest access log for IP
        $log = $this->db->table('tbl_user_actions')
            ->select('ip_address, created_at')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        // Merge data
        if ($activity) {
            $profile['network_type'] = $activity['network_type'];
            $profile['battery_level'] = $activity['battery_level']; // Prefer activity battery if newer
            $profile['charging_status'] = $activity['charging_status'];
            $profile['last_activity_time'] = $activity['extracted_at'];
        }

        if ($log) {
            $profile['last_ip_address'] = $log['ip_address'];
            $profile['last_login_time'] = $log['created_at'];
        }

        return $profile;
    }

    /**
     * Get social graph data (top contacts by interaction).
     */
    public function get_social_graph(int $userId, int $limit = 20): array
    {
        // 1. Get SMS counts
        $sms_data = $this->db->table('tbl_sms')
            ->select('address, COUNT(*) as count')
            ->where('owner_id', $userId)
            ->groupBy('address')
            ->get()
            ->getResultArray();

        // 2. Get Call counts
        $call_data = $this->db->table('tbl_logs')
            ->select('phone_number, contact_name, COUNT(*) as count')
            ->where('owner_id', $userId)
            ->groupBy('phone_number')
            ->get()
            ->getResultArray();

        // 3. Merge and Score
        $social_map = [];

        foreach ($sms_data as $sms) {
            $num = $sms['address'];
            if (!isset($social_map[$num])) {
                $social_map[$num] = ['number' => $num, 'name' => 'Unknown', 'sms' => 0, 'calls' => 0, 'score' => 0];
            }
            $social_map[$num]['sms'] += $sms['count'];
            $social_map[$num]['score'] += $sms['count'] * 1; // 1 point per SMS
        }

        foreach ($call_data as $call) {
            $num = $call['phone_number'];
            if (!isset($social_map[$num])) {
                $social_map[$num] = ['number' => $num, 'name' => $call['contact_name'] ?: 'Unknown', 'sms' => 0, 'calls' => 0, 'score' => 0];
            } else {
                if ($social_map[$num]['name'] === 'Unknown' && !empty($call['contact_name'])) {
                    $social_map[$num]['name'] = $call['contact_name'];
                }
            }
            $social_map[$num]['calls'] += $call['count'];
            $social_map[$num]['score'] += $call['count'] * 5; // 5 points per Call
        }

        // 4. Resolve Names from Contacts table for remaining Unknowns
        $unknowns = array_keys(array_filter($social_map, fn($c) => $c['name'] === 'Unknown'));
        if (!empty($unknowns)) {
            // Processing unknowns in chunks to avoid query limits if needed, but for top 20 it's fine.
            // Actually querying all potential matches.
            $contacts = $this->db->table('tbl_contacts')
                ->select('phone_numbers, display_name')
                ->where('owner_id', $userId)
                ->get()
                ->getResultArray();

            foreach ($contacts as $contact) {
                $nums = json_decode($contact['phone_numbers'], true);
                $name = $contact['display_name'];

                if (is_array($nums)) {
                    foreach ($nums as $num) {
                        if (isset($social_map[$num])) {
                            $social_map[$num]['name'] = $name;
                        }
                    }
                }
            }
        }

        // 5. Sort by score
        usort($social_map, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($social_map, 0, $limit);
    }

    /**
     * Aggregates mobility data for lifestyle profiling.
     */
    public function get_mobility_aggregates(int $userId): array
    {
        $activities = $this->db->table('tbl_activity')
            ->where('owner_id', $userId)
            ->orderBy('activity_time', 'ASC')
            ->get()
            ->getResultArray();

        $stats = [
            'STILL' => 0,
            'WALKING' => 0,
            'IN_VEHICLE' => 0,
            'ON_BICYCLE' => 0,
            'RUNNING' => 0,
            'TILTING' => 0,
            'UNKNOWN' => 0,
            'screen_on' => 0,
            'screen_off' => 0,
            'total_screentime_ms' => 0,
            'top_apps' => []
        ];

        if (!empty($activities)) {
            foreach ($activities as $act) {
                $type = strtoupper($act['activity_type'] ?? 'UNKNOWN');
                if (isset($stats[$type])) {
                    $stats[$type]++;
                } else {
                    $stats['UNKNOWN']++;
                }

                if ($act['screen_on'] == 1)
                    $stats['screen_on']++;
                else
                    $stats['screen_off']++;
            }
        }

        $app_usage = $this->db->table('tbl_app_usage')
            ->where('owner_id', $userId)
            ->orderBy('foreground_time_ms', 'DESC')
            ->get()
            ->getResultArray();

        if (!empty($app_usage)) {
            $appMap = [];
            foreach ($app_usage as $usage) {
                $time = (int) $usage['foreground_time_ms'];
                $stats['total_screentime_ms'] += $time;
                $pkg = $usage['package_name'];

                if (!isset($appMap[$pkg])) {
                    $appMap[$pkg] = ['name' => $pkg, 'time' => 0];
                }
                $appMap[$pkg]['time'] += $time;
            }

            $apps = $this->db->table('tbl_apps')
                ->select('package_name, app_name')
                ->where('owner_id', $userId)
                ->get()
                ->getResultArray();

            $nameMap = [];
            foreach ($apps as $a)
                $nameMap[$a['package_name']] = $a['app_name'];

            foreach ($appMap as &$am) {
                if (isset($nameMap[$am['name']])) {
                    $am['name'] = $nameMap[$am['name']];
                }
            }

            usort($appMap, fn($a, $b) => $b['time'] <=> $a['time']);
            $stats['top_apps'] = array_slice($appMap, 0, 5);
        }

        return $stats;
    }

    /**
     * Basic timeline: SMS + Call events only (for the Basic Timeline pill).
     *
     * @param int $userId
     * @param int $limit   Max rows per data source (total can be up to 2× limit)
     * @return array       Chronologically sorted event array
     */
    public function get_basic_timeline(int $userId, int $limit = 200): array
    {
        $timeline = [];

        // SMS events
        try {
            $sms = $this->db->table('tbl_sms')
                ->select('address, body, sms_date, sms_type')
                ->where('owner_id', $userId)
                ->orderBy('sms_date', 'DESC')
                ->limit($limit)
                ->get()->getResultArray();

            foreach ($sms as $s) {
                $isInbox = strtolower($s['sms_type'] ?? '') === 'inbox';
                $timeline[] = [
                    'type'     => 'sms',
                    'subtype'  => $isInbox ? 'inbox' : 'sent',
                    'title'    => ($isInbox ? 'Received from ' : 'Sent to ') . ($s['address'] ?? '—'),
                    'body'     => $this->decode_sms_body($s['body'] ?? ''),
                    'meta'     => $s['address'] ?? '',
                    'time'     => (int) ($s['sms_date'] ?? 0),
                    'icon'     => $isInbox ? 'fas fa-envelope-open-text' : 'fas fa-paper-plane',
                    'color'    => $isInbox ? 'bg-primary' : 'bg-indigo',
                ];
            }
        } catch (\Throwable $e) {
            log_message('error', 'get_basic_timeline SMS: ' . $e->getMessage());
        }

        // Call events
        try {
            $calls = $this->db->table('tbl_logs')
                ->select('phone_number, contact_name, call_type, call_date, duration_seconds')
                ->where('owner_id', $userId)
                ->orderBy('call_date', 'DESC')
                ->limit($limit)
                ->get()->getResultArray();

            foreach ($calls as $c) {
                $who  = !empty($c['contact_name']) ? $c['contact_name'] : $c['phone_number'];
                $type = strtolower($c['call_type'] ?? 'call');
                $timeline[] = [
                    'type'    => 'call',
                    'subtype' => $type,
                    'title'   => ucfirst($type) . ' call — ' . $who,
                    'body'    => 'Duration: ' . (int) $c['duration_seconds'] . 's',
                    'meta'    => $c['phone_number'] ?? '',
                    'time'    => (int) ($c['call_date'] ?? 0),
                    'icon'    => $type === 'missed' ? 'fas fa-phone-missed-call' : ($type === 'outgoing' ? 'fas fa-phone-outgoing' : 'fas fa-phone-incoming'),
                    'color'   => $type === 'missed' ? 'bg-danger' : ($type === 'outgoing' ? 'bg-success' : 'bg-teal'),
                ];
            }
        } catch (\Throwable $e) {
            log_message('error', 'get_basic_timeline Calls: ' . $e->getMessage());
        }

        usort($timeline, fn($a, $b) => $b['time'] <=> $a['time']);
        return $timeline;
    }

    /**
     * Unified chronological timeline of ALL device events.
     * Sources: SMS, Calls, Activities, Locations, App-Usage sessions,
     *          App installs (tbl_apps), Device files, tbl_receive (upload events).
     *
     * Every source is wrapped in try/catch so a missing table never
     * breaks the page. Final list is sorted DESC and sliced to $limit.
     */
    public function get_unified_timeline(int $userId, int $limit = 100): array
    {
        $timeline = [];
        $src      = (int) ceil($limit / 6); // per-source cap

        // ── 1. SMS ────────────────────────────────────────────────────────────
        try {
            $rows = $this->db->table('tbl_sms')
                ->select('address, body, sms_date, sms_type')
                ->where('owner_id', $userId)
                ->orderBy('sms_date', 'DESC')
                ->limit($src)
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $inbox = strtolower($r['sms_type'] ?? '') === 'inbox';
                $timeline[] = [
                    'type'  => 'sms',
                    'title' => ($inbox ? 'Received SMS from ' : 'Sent SMS to ') . ($r['address'] ?? '?'),
                    'body'  => mb_strimwidth($this->decode_sms_body($r['body'] ?? ''), 0, 300, '…'),
                    'time'  => (int) ($r['sms_date'] ?? 0),
                    'icon'  => $inbox ? 'fas fa-envelope-open-text' : 'fas fa-paper-plane',
                    'color' => $inbox ? 'bg-primary' : 'bg-indigo',
                ];
            }
        } catch (\Throwable $e) { log_message('error', 'timeline SMS: ' . $e->getMessage()); }

        // ── 2. Calls ──────────────────────────────────────────────────────────
        try {
            $rows = $this->db->table('tbl_logs')
                ->select('phone_number, contact_name, call_type, call_date, duration_seconds')
                ->where('owner_id', $userId)
                ->orderBy('call_date', 'DESC')
                ->limit($src)
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $who  = !empty($r['contact_name']) ? $r['contact_name'] : ($r['phone_number'] ?? '?');
                $type = strtolower($r['call_type'] ?? 'call');
                $dur  = (int) ($r['duration_seconds'] ?? 0);
                $timeline[] = [
                    'type'     => 'call',
                    'subtitle' => $type,
                    'title'    => ucfirst($type) . ' call — ' . $who,
                    'body'     => 'Duration: ' . $dur . 's' . ($dur === 0 && $type === 'missed' ? ' (missed)' : ''),
                    'time'     => (int) ($r['call_date'] ?? 0),
                    'icon'     => $type === 'missed' ? 'fas fa-phone-slash' : ($type === 'outgoing' ? 'fas fa-phone-alt' : 'fas fa-phone-incoming'),
                    'color'    => $type === 'missed' ? 'bg-danger' : ($type === 'outgoing' ? 'bg-success' : 'bg-teal'),
                ];
            }
        } catch (\Throwable $e) { log_message('error', 'timeline Calls: ' . $e->getMessage()); }

        // ── 3. Physical activity ──────────────────────────────────────────────
        try {
            $rows = $this->db->table('tbl_activity')
                ->select('activity_type, activity_time, confidence, screen_on, battery_level, network_type, info')
                ->where('owner_id', $userId)
                ->where('confidence >', 60)
                ->orderBy('activity_time', 'DESC')
                ->limit($src)
                ->get()->getResultArray();

            $actIcons = [
                'still'      => 'fas fa-bed',
                'walking'    => 'fas fa-walking',
                'running'    => 'fas fa-running',
                'in_vehicle' => 'fas fa-car',
                'on_bicycle' => 'fas fa-bicycle',
                'tilting'    => 'fas fa-redo',
            ];
            foreach ($rows as $r) {
                $atype  = strtolower($r['activity_type'] ?? 'unknown');
                $screen = ($r['screen_on'] ?? 0) ? 'Screen ON' : 'Screen OFF';
                $bat    = !empty($r['battery_level']) ? ' · Battery ' . $r['battery_level'] . '%' : '';
                $net    = !empty($r['network_type'])  ? ' · Network: ' . $r['network_type']      : '';
                $timeline[] = [
                    'type'     => 'activity',
                    'subtitle' => ucfirst($atype),
                    'title'    => 'Activity: ' . ucfirst($atype),
                    'body'     => $screen . $bat . $net . ' · Confidence: ' . $r['confidence'] . '%'
                                  . (!empty($r['info']) ? ' · ' . $r['info'] : ''),
                    'time'     => (int) ($r['activity_time'] ?? 0),
                    'icon'     => $actIcons[$atype] ?? 'fas fa-running',
                    'color'    => 'bg-info',
                ];
            }
        } catch (\Throwable $e) { log_message('error', 'timeline Activity: ' . $e->getMessage()); }

        // ── 4. Location check-ins ─────────────────────────────────────────────
        try {
            $rows = $this->db->table('tbl_location')
                ->select('latitude, longitude, provider, accuracy, location_time')
                ->where('owner_id', $userId)
                ->orderBy('location_time', 'DESC')
                ->limit((int) ceil($src / 2))
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $acc = !empty($r['accuracy']) ? ' · Accuracy: ' . round((float)$r['accuracy'], 1) . 'm' : '';
                $timeline[] = [
                    'type'     => 'location',
                    'subtitle' => $r['provider'] ?? 'gps',
                    'title'    => 'Location Update via ' . strtoupper($r['provider'] ?? 'GPS'),
                    'body'     => 'Lat: ' . $r['latitude'] . '  Lng: ' . $r['longitude'] . $acc,
                    'time'     => (int) ($r['location_time'] ?? 0),
                    'icon'     => 'fas fa-map-marker-alt',
                    'color'    => 'bg-warning',
                ];
            }
        } catch (\Throwable $e) { log_message('error', 'timeline Location: ' . $e->getMessage()); }

        // ── 5. App usage / screen sessions ───────────────────────────────────
        try {
            $rows = $this->db->table('tbl_app_usage')
                ->select('package_name, app_name, foreground_time_ms, last_time_used')
                ->where('owner_id', $userId)
                ->where('last_time_used >', 0)
                ->orderBy('last_time_used', 'DESC')
                ->limit($src)
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $appLabel = !empty($r['app_name']) ? $r['app_name'] : $r['package_name'];
                $mins     = $r['foreground_time_ms'] > 0
                    ? round($r['foreground_time_ms'] / 60000, 1) . ' min'
                    : 'brief session';
                $timeline[] = [
                    'type'     => 'app_usage',
                    'subtitle' => $r['package_name'] ?? '',
                    'title'    => 'App Opened: ' . $appLabel,
                    'body'     => 'Package: ' . ($r['package_name'] ?? '?') . ' · Session: ' . $mins,
                    'time'     => (int) ($r['last_time_used'] ?? 0),
                    'icon'     => 'fas fa-mobile-alt',
                    'color'    => 'bg-indigo',
                ];
            }
        } catch (\Throwable $e) { log_message('error', 'timeline AppUsage: ' . $e->getMessage()); }

        // ── 6. App installs ───────────────────────────────────────────────────
        try {
            $rows = $this->db->table('tbl_apps')
                ->select('app_name, package_name, first_install_time, last_update_time, is_system_app, version_name')
                ->where('owner_id', $userId)
                ->where('first_install_time >', 0)
                ->orderBy('first_install_time', 'DESC')
                ->limit($src)
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $label    = !empty($r['app_name']) ? $r['app_name'] : $r['package_name'];
                $isSystem = ($r['is_system_app'] ?? 0) ? ' [System App]' : '';
                $ver      = !empty($r['version_name']) ? ' v' . $r['version_name'] : '';
                $timeline[] = [
                    'type'     => 'upload',
                    'subtitle' => 'app',
                    'title'    => 'App Installed: ' . $label . $ver,
                    'body'     => 'Package: ' . ($r['package_name'] ?? '?') . $isSystem,
                    'time'     => (int) ($r['first_install_time'] ?? 0),
                    'icon'     => 'fas fa-mobile-alt',
                    'color'    => 'bg-indigo',
                ];
                // Also emit an update event if update time differs
                $upd = (int) ($r['last_update_time'] ?? 0);
                $ins = (int) ($r['first_install_time'] ?? 0);
                if ($upd > 0 && $upd !== $ins) {
                    $timeline[] = [
                        'type'     => 'upload',
                        'subtitle' => 'app',
                        'title'    => 'App Updated: ' . $label . $ver,
                        'body'     => 'Package: ' . ($r['package_name'] ?? '?'),
                        'time'     => $upd,
                        'icon'     => 'fas fa-sync-alt',
                        'color'    => 'bg-teal',
                    ];
                }
            }
        } catch (\Throwable $e) { log_message('error', 'timeline Apps: ' . $e->getMessage()); }

        // ── 7. Device files ───────────────────────────────────────────────────
        try {
            $rows = $this->db->table('tbl_device_files')
                ->select('name, path, size_bytes, last_modified, mime_type')
                ->where('owner_id', $userId)
                ->where('last_modified >', 0)
                ->orderBy('last_modified', 'DESC')
                ->limit($src)
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $size  = $r['size_bytes'] > 0 ? round($r['size_bytes'] / 1024, 1) . ' KB' : 'unknown size';
                $mime  = !empty($r['mime_type']) ? ' · ' . $r['mime_type'] : '';
                $timeline[] = [
                    'type'     => 'file',
                    'subtitle' => 'file',
                    'title'    => 'File: ' . ($r['name'] ?? 'Unknown'),
                    'body'     => 'Path: ' . ($r['path'] ?? '?') . ' · Size: ' . $size . $mime,
                    'time'     => (int) ($r['last_modified'] ?? 0),
                    'icon'     => 'fas fa-file-alt',
                    'color'    => 'bg-secondary',
                ];
            }
        } catch (\Throwable $e) { log_message('error', 'timeline Files: ' . $e->getMessage()); }

        // ── 8. Data upload/receive events (tbl_receive) ───────────────────────
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM tbl_receive")->getResultArray();
            if (!empty($cols)) {
                $rows = $this->db->table('tbl_receive')
                    ->where('owner_id', $userId)
                    ->orderBy('received_at', 'DESC')
                    ->limit($src)
                    ->get()->getResultArray();

                foreach ($rows as $r) {
                    $dtype = $r['data_type'] ?? ($r['type'] ?? 'data');
                    $timeline[] = [
                        'type'     => 'upload',
                        'subtitle' => strtolower($dtype),
                        'title'    => 'Data Upload: ' . ucfirst($dtype),
                        'body'     => 'Records received from device · Source: ' . ($r['source'] ?? 'device'),
                        'time'     => strtotime($r['received_at'] ?? '') ?: 0,
                        'icon'     => 'fas fa-upload',
                        'color'    => 'bg-teal',
                    ];
                }
            }
        } catch (\Throwable $e) { log_message('error', 'timeline Receive: ' . $e->getMessage()); }

        // ── Sort all events DESC by time and slice ────────────────────────────
        usort($timeline, fn($a, $b) => $b['time'] <=> $a['time']);
        return array_slice($timeline, 0, $limit);
    }
    /**
     * Privacy Audit: Analyze permissions for risk scoring.
     */
    public function get_app_privacy_audit(int $userId): array
    {
        $apps = $this->db->table('tbl_apps')
            ->select('app_name, package_name, permissions, app_icon, version_name, version_code, app_size, permission_count, target_sdk, min_sdk, first_install_time, last_update_time, is_system_app')
            ->where('owner_id', $userId)
            ->get()
            ->getResultArray();

        $audit = [];

        foreach ($apps as $app) {
            $perms = explode(',', $app['permissions']);
            $score = 0;
            $risks = [];

            $hasInternet = in_array('android.permission.INTERNET', $perms);
            $hasSms = in_array('android.permission.READ_SMS', $perms) || in_array('android.permission.RECEIVE_SMS', $perms) || in_array('android.permission.SEND_SMS', $perms);
            $hasAudio = in_array('android.permission.RECORD_AUDIO', $perms);
            $hasCamera = in_array('android.permission.CAMERA', $perms);
            $hasLocation = in_array('android.permission.ACCESS_FINE_LOCATION', $perms) || in_array('android.permission.ACCESS_COARSE_LOCATION', $perms) || in_array('android.permission.ACCESS_BACKGROUND_LOCATION', $perms);

            if ($hasSms) {
                $score += 3;
                $risks[] = 'Reads/Sends Private Messages';
            }
            if ($hasLocation) {
                $score += 2;
            }
            if ($hasAudio || $hasCamera) {
                $score += 2;
            }

            // Dangerous Combos
            if ($hasSms && $hasInternet) {
                $score += 5;
                $risks[] = 'Data Exfiltration Risk (SMS + Internet)';
            }
            if ($hasAudio && $hasCamera) {
                $score += 4;
                $risks[] = 'Privacy Intrusion (Microphone + Camera)';
            }
            if (in_array('android.permission.ACCESS_FINE_LOCATION', $perms)) {
                $score += 3;
                $risks[] = 'Movement Tracking (Fine Location)';
            }

            // Suspicious App Check
            if (preg_match('/spy|tracker|hack|cheat|monitor|stealth/i', $app['package_name'])) {
                $score += 8;
                $risks[] = 'Suspicious App Signature (Spyware/Tracker)';
            }

            if ($score > 0) {
                $audit[] = [
                    'name' => $app['app_name'],
                    'package' => $app['package_name'],
                    'score' => $score,
                    'risks' => $risks,
                    'app_data' => $app // Pass the full app data for the modal
                ];
            }
        }

        usort($audit, fn($a, $b) => $b['score'] <=> $a['score']);
        return $audit;
    }

    /**
     * Scam SMS Audit: Identify suspicious/scam messages.
     */
    public function get_scam_sms_audit(int $userId): array
    {
        $sms = $this->db->table('tbl_sms')
            ->select('address, body, sms_date')
            ->where('owner_id', $userId)
            ->orderBy('sms_date', 'DESC')
            ->get()
            ->getResultArray();

        $scams = [];
        $scamKeywords = ['win', 'won', 'lottery', 'prize', 'urgent', 'verify', 'password', 'click here', 'congratulations', 'blocked', 'suspend'];

        foreach ($sms as $s) {
            $body = strtolower($this->decode_sms_body($s['body']));
            $isScam = false;
            foreach ($scamKeywords as $kw) {
                if (strpos($body, $kw) !== false) {
                    $isScam = true;
                    break;
                }
            }

            if ($isScam && !isset($scams[$s['address']])) {
                $scams[$s['address']] = [
                    'address' => $s['address'],
                    'body' => $body,
                    'date' => $s['sms_date']
                ];
            }
        }

        return array_values($scams);
    }

    /**
     * Subscription Forecast: Detect recurring bills in SMS.
     */
    public function get_subscription_forecast(int $userId): array
    {
        $sms = $this->db->table('tbl_sms')
            ->select('address, body, sms_date')
            ->where('owner_id', $userId)
            ->orderBy('sms_date', 'DESC')
            ->get()
            ->getResultArray();

        $forecast = [];
        $keywords = [
            'subscription',
            'renew',
            'renewal',
            'token',
            'postpaid',
            'prepaid',
            'monthly bill',
            'utility',
            'netflix',
            'spotify',
            'dstv',
            'zuku',
            'gotv',
            'kplc',
            'water',
            'internet',
            'premium',
            'membership'
        ];

        foreach ($sms as $s) {
            $body = strtolower($this->decode_sms_body($s['body']));
            $is_sub = false;
            foreach ($keywords as $kw) {
                if (strpos($body, $kw) !== false) {
                    $is_sub = true;
                    break;
                }
            }

            if ($is_sub) {
                if (preg_match('/(?:ksh|kes)[\\s]?([\\d,]+(?:\\.\\d{2})?)/i', $body, $matches)) {
                    $amount = (float) str_replace(',', '', $matches[1]);
                    $sender = strtoupper($s['address']);

                    if (!isset($forecast[$sender])) {
                        $forecast[$sender] = [
                            'amount' => $amount,
                            'count' => 0,
                            'last_date' => $s['sms_date']
                        ];
                    }
                    $forecast[$sender]['count']++;
                }
            }
        }

        // Filter out one-time payments to keep only recurring subscriptions/bills
        $recurring_forecast = array_filter($forecast, fn($f) => $f['count'] > 1);

        return count($recurring_forecast) > 0 ? $recurring_forecast : $forecast;
    }

    /**
     * App Category Distribution based on package name.
     */
    public function get_app_category_dist(int $userId): array
    {
        $apps = $this->db->table('tbl_apps')
            ->select('package_name, is_system_app')
            ->where('owner_id', $userId)
            ->get()
            ->getResultArray();

        $dist = [
            'Social & Communication' => 0,
            'Finance & Banking' => 0,
            'Entertainment & Media' => 0,
            'Productivity & Work' => 0,
            'Tools & Utilities' => 0,
            'System & Core' => 0,
            'Shopping & Lifestyle' => 0,
            'Other' => 0
        ];

        foreach ($apps as $app) {
            if ($app['is_system_app']) {
                $dist['System & Core']++;
                continue;
            }

            $pkg = strtolower($app['package_name']);
            if (preg_match('/whatsapp|facebook|instagram|tiktok|twitter|linkedin|snapchat|telegram|messenger|discord|viber|skype/i', $pkg)) {
                $dist['Social & Communication']++;
            } else if (preg_match('/bank|kcb|equity|mcoop|pay|binance|stripe|paypal|wallet|crypto|mpesa|ncba|stanchart|absa/i', $pkg)) {
                $dist['Finance & Banking']++;
            } else if (preg_match('/netflix|youtube|spotify|music|player|video|games|sport|bet|tv|media/i', $pkg)) {
                $dist['Entertainment & Media']++;
            } else if (preg_match('/office|mail|calendar|slack|note|drive|zoom|teams|meet|docs|pdf|word|excel/i', $pkg)) {
                $dist['Productivity & Work']++;
            } else if (preg_match('/cleaner|antivirus|browser|launcher|tool|vpn|keyboard|filemanager|share/i', $pkg)) {
                $dist['Tools & Utilities']++;
            } else if (preg_match('/shop|amazon|jumia|alibaba|glovo|uber|bolt|food|health|fitness/i', $pkg)) {
                $dist['Shopping & Lifestyle']++;
            } else {
                $dist['Other']++;
            }
        }

        // Clean up empty categories to make charts look better
        return array_filter($dist, fn($val) => $val > 0);
    }

    /**
     * Storage Forensics: Deep file distribution analysis.
     */
    public function get_storage_forensics(int $userId): array
    {
        $files = $this->db->table('tbl_device_files')
            ->where('owner_id', $userId)
            ->get()
            ->getResultArray();

        $stats = [
            'total_size' => 0,
            'count' => count($files),
            'top_files' => [],
            'large_hogs' => [],
            'by_source' => [
                'WhatsApp' => 0,
                'Camera/DCIM' => 0,
                'Downloads' => 0,
                'Other' => 0
            ],
            'by_age' => [
                'Old (>1yr)' => 0,
                'Mid (6mo-1yr)' => 0,
                'Recent' => 0
            ]
        ];

        $now = time() * 1000;
        $sixMonths = 15552000000; // 6 months in ms
        $oneYear = 31104000000;   // 1 year in ms

        foreach ($files as $f) {
            $size = (int) $f['size_bytes'];
            $stats['total_size'] += $size;

            // By Source
            $path = $f['path'];
            if (stripos($path, 'WhatsApp') !== false)
                $stats['by_source']['WhatsApp'] += $size;
            else if (stripos($path, 'DCIM') !== false)
                $stats['by_source']['Camera/DCIM'] += $size;
            else if (stripos($path, 'Download') !== false)
                $stats['by_source']['Downloads'] += $size;
            else
                $stats['by_source']['Other'] += $size;

            // By Age
            $age = $now - (int) $f['last_modified'];
            if ($age > $oneYear)
                $stats['by_age']['Old (>1yr)']++;
            else if ($age > $sixMonths)
                $stats['by_age']['Mid (6mo-1yr)']++;
            else
                $stats['by_age']['Recent']++;

            // Large Space Hogs (>50MB)
            if ($size > 52428800) {
                $stats['large_hogs'][] = [
                    'name' => $f['name'],
                    'size' => $size,
                    'path' => $path,
                    'extension' => $f['extension'] ?? null,
                    'formatted_size' => $f['formatted_size'] ?? null,
                    'formatted_date' => $f['formatted_date'] ?? null,
                    'last_modified' => $f['last_modified'] ?? null,
                    'category' => $f['category'] ?? null,
                ];
            }

            // For Top Files
            $stats['top_files'][] = [
                'name' => $f['name'],
                'size' => $size,
                'path' => $path,
                'extension' => $f['extension'] ?? null,
                'formatted_size' => $f['formatted_size'] ?? null,
                'formatted_date' => $f['formatted_date'] ?? null,
                'last_modified' => $f['last_modified'] ?? null,
                'category' => $f['category'] ?? null,
            ];
        }

        usort($stats['top_files'], fn($a, $b) => $b['size'] <=> $a['size']);
        $stats['top_files'] = array_slice($stats['top_files'], 0, 50);

        usort($stats['large_hogs'], fn($a, $b) => $b['size'] <=> $a['size']);

        return $stats;
    }
    /**
     * Sentiment & Social Tone: Keyword-based relationship health.
     */
    public function get_sentiment_profile(int $userId): array
    {
        $sms = $this->db->table('tbl_sms')
            ->select('address, body, sms_type')
            ->where('owner_id', $userId)
            ->where('type_code !=', 1)
            ->orderBy('sms_date', 'DESC')
            ->limit(1000)
            ->get()
            ->getResultArray();

        $totalMessages = count($sms);
        if ($totalMessages < 5) {
            return [];
        }

        $posWords = ['love', 'good', 'great', 'happy', 'thanks', 'thank', 'awesome', 'best', 'well', 'congrats', 'nice'];
        $negWords = ['hate', 'bad', 'sorry', 'sad', 'angry', 'worst', 'fail', 'stop', 'late', 'wrong', 'issue', 'problem'];

        [$vectors, $vectorizer] = Mod_ML_Analyzer::vectorizeSms($sms, 300);
        $vocab = $vectorizer->getVocabulary();

        $k = $totalMessages < 30 ? 2 : 3;
        $clusters = Mod_ML_Analyzer::kmeans($vectors, $k);

        $clusterLabels = Mod_ML_Analyzer::labelClustersByKeywords($clusters, $sms, $posWords, $negWords);
        if (count(array_unique($clusterLabels)) < 2) {
            $clusterLabels = Mod_ML_Analyzer::labelClustersByCentroid(
                $clusters, $vectors, $posWords, $negWords, $vocab
            );
        }

        $msgSentiment = [];
        foreach ($clusters as $ci => $points) {
            $label = $clusterLabels[$ci] ?? 'neutral';
            foreach (array_keys($points) as $idx) {
                $msgSentiment[$idx] = $label;
            }
        }

        $sentiment = [];
        foreach ($sms as $i => $s) {
            $addr = $s['address'];
            if (!isset($sentiment[$addr])) {
                $sentiment[$addr] = [
                    'positive' => 0,
                    'negative' => 0,
                    'total' => 0,
                    'name' => $addr,
                ];
            }
            $label = $msgSentiment[$i] ?? 'neutral';
            if ($label === 'positive') {
                $sentiment[$addr]['positive']++;
            } elseif ($label === 'negative') {
                $sentiment[$addr]['negative']++;
            }
            $sentiment[$addr]['total']++;
        }

        $contacts = $this->db->table('tbl_contacts')
            ->select('phone_numbers, display_name')
            ->where('owner_id', $userId)
            ->get()
            ->getResultArray();

        foreach ($contacts as $contact) {
            $nums = json_decode($contact['phone_numbers'], true);
            if (is_array($nums)) {
                foreach ($nums as $num) {
                    if (isset($sentiment[$num])) {
                        $sentiment[$num]['name'] = $contact['display_name'];
                    }
                }
            }
        }

        $sentiment = array_filter($sentiment, fn($v) => $v['total'] > 3);
        uasort($sentiment, fn($a, $b) => $b['total'] <=> $a['total']);

        return array_slice($sentiment, 0, 15, true);
    }

    /**
     * Behavioral Anomaly Detection: Identifying out-of-character events.
     */
    public function get_behavioral_anomalies(int $userId): array
    {
        $anomalies = [];

        // 1. Time Anomaly (Activity after midnight)
        $midnightActivity = $this->db->table('tbl_activity')
            ->where('owner_id', $userId)
            ->where('activity_type !=', 'still')
            ->where('HOUR(FROM_UNIXTIME(activity_time/1000)) >=', 0)
            ->where('HOUR(FROM_UNIXTIME(activity_time/1000)) <=', 4)
            ->orderBy('activity_time', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        foreach ($midnightActivity as $a) {
            $anomalies[] = [
                'type' => 'Unusual Hours',
                'severity' => 'Medium',
                'desc' => 'Significant movement detected between 12 AM and 4 AM.',
                'time' => (int) $a['activity_time']
            ];
        }

        // 2. High Frequency SMS (Burst detection)
        $last24h = (time() - 86400) * 1000;
        $burstSms = $this->db->table('tbl_sms')
            ->select('address, COUNT(*) as count')
            ->where('owner_id', $userId)
            ->where('sms_date >', $last24h)
            ->groupBy('address')
            ->having('count >', 50)
            ->get()
            ->getResultArray();

        foreach ($burstSms as $b) {
            $anomalies[] = [
                'type' => 'Communication Burst',
                'severity' => 'High',
                'desc' => 'Unusually high volume of messages (>50) to ' . $b['address'] . ' in 24h.',
                'time' => (int) $last24h
            ];
        }

        return $anomalies;
    }

    /**
     * Communication Quality Metrics: Response latency and initiation.
     */
    public function get_communication_quality(int $userId): array
    {
        $quality = [];
        $sms = $this->db->table('tbl_sms')
            ->select('address, sms_type, sms_date')
            ->where('owner_id', $userId)
            ->orderBy('sms_date', 'ASC') // ASC to calculate latency easily
            ->limit(2000)
            ->get()
            ->getResultArray();

        $interaction = [];
        foreach ($sms as $s) {
            $addr = $s['address'];
            if (!isset($interaction[$addr])) {
                $interaction[$addr] = [
                    'sent' => 0,
                    'inbox' => 0,
                    'last_msg' => null,
                    'total_latency' => 0,
                    'responses' => 0,
                    'name' => $addr // Default to address
                ];
            }

            if ($s['sms_type'] === 'sent')
                $interaction[$addr]['sent']++;
            else
                $interaction[$addr]['inbox']++;

            if ($interaction[$addr]['last_msg'] && $interaction[$addr]['last_msg']['type'] !== $s['sms_type']) {
                $latency = (int) $s['sms_date'] - (int) $interaction[$addr]['last_msg']['time'];
                if ($latency < 86400000) { // Only count if within 24h to avoid outlier days
                    $interaction[$addr]['total_latency'] += $latency;
                    $interaction[$addr]['responses']++;
                }
            }
            $interaction[$addr]['last_msg'] = ['type' => $s['sms_type'], 'time' => $s['sms_date']];
        }

        // Resolve names from contacts
        $contacts = $this->db->table('tbl_contacts')
            ->select('phone_numbers, display_name')
            ->where('owner_id', $userId)
            ->get()
            ->getResultArray();

        foreach ($contacts as $contact) {
            $nums = json_decode($contact['phone_numbers'], true);
            if (is_array($nums)) {
                foreach ($nums as $num) {
                    if (isset($interaction[$num])) {
                        $interaction[$num]['name'] = $contact['display_name'];
                    }
                }
            }
        }

        foreach ($interaction as $addr => $data) {
            if ($data['sent'] + $data['inbox'] < 10)
                continue;

            $quality[] = [
                'address' => $addr,
                'name' => $data['name'],
                'initiation_sent' => round(($data['sent'] / max(1, $data['sent'] + $data['inbox'])) * 100),
                'avg_latency_min' => $data['responses'] > 0 ? round($data['total_latency'] / ($data['responses'] * 60000)) : 0,
                'total' => $data['sent'] + $data['inbox']
            ];
        }

        usort($quality, fn($a, $b) => $b['total'] <=> $a['total']);
        return array_slice($quality, 0, 10);
    }

    /**
     * Geographical Hotspot Clustering: Base of Operations.
     */
    public function get_geospatial_clusters(int $userId): array
    {
        $locations = $this->db->table('tbl_location')
            ->select('latitude, longitude, location_time')
            ->where('owner_id', $userId)
            ->where('latitude !=', 0)
            ->where('longitude !=', 0)
            ->orderBy('location_time', 'DESC')
            ->limit(1000)
            ->get()
            ->getResultArray();

        $count = count($locations);
        if ($count < 5) {
            return [];
        }

        $vectors = [];
        $lookup = [];
        foreach ($locations as $i => $l) {
            $vectors[] = [(float) $l['latitude'], (float) $l['longitude']];
            $lookup[$i] = $l;
        }

        $epsilon = 0.002;
        $clusters = Mod_ML_Analyzer::dbscan($vectors, $epsilon, 3);

        $results = [];
        foreach ($clusters as $points) {
            if (count($points) < 2) {
                continue;
            }
            $latSum = 0;
            $lngSum = 0;
            $pings = 0;
            $lastSeen = 0;
            foreach ($points as $idx => $coords) {
                $latSum += $coords[0];
                $lngSum += $coords[1];
                $pings++;
                $locTime = $lookup[$idx]['location_time'] ?? 0;
                if ($locTime > $lastSeen) {
                    $lastSeen = $locTime;
                }
            }
            $results[] = [
                'lat' => round($latSum / $pings, 6),
                'lng' => round($lngSum / $pings, 6),
                'pings' => $pings,
                'last_seen' => $lastSeen,
                'label' => 'Unknown',
            ];
        }

        usort($results, fn($a, $b) => $b['pings'] <=> $a['pings']);
        $results = array_slice($results, 0, 5);

        foreach ($results as $index => &$cluster) {
            if ($index === 0) {
                $cluster['label'] = 'Home / Primary Base';
            } elseif ($index === 1) {
                $cluster['label'] = 'Work / Secondary Base';
            } else {
                $cluster['label'] = 'Frequent Social Base';
            }
        }

        return $results;
    }

    /**
     * Helper to decode SMS body if base64 encoded.
     *
     * @param string $body
     * @return string
     */
    protected function decode_sms_body(string $body): string
    {
        $text = $body;
        // Only decode if it looks like base64 or if it's long enough to be an encoded msg
        if (strlen($body) > 4 && preg_match('/^[a-zA-Z0-9\/\+=]+$/', $body)) {
            $decoded = base64_decode($body, true);
            if ($decoded !== false && mb_check_encoding($decoded, 'UTF-8')) {
                $text = $decoded;
            }
        }
        return $text;
    }

    /**
     * Get Captured Media (Images and Audio)
     */
    public function get_captured_media(int $userId): array
    {
        return $this->db->table('tbl_captured_media')
            ->where('owner_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function get_captured_media_by_id(int $id, int $userId): ?array
    {
        return $this->db->table('tbl_captured_media')
            ->where('id', $id)
            ->where('owner_id', $userId)
            ->get()
            ->getRowArray();
    }

    public function delete_captured_media(int $id, int $userId): bool
    {
        return $this->db->table('tbl_captured_media')
            ->where('id', $id)
            ->where('owner_id', $userId)
            ->delete();
    }

    public function get_count_timeline_events(int $userId): int
    {
        try {
            $b_calls = $this->getBlockedIdentifiers($userId, 'call');
            $b_sms = $this->getBlockedIdentifiers($userId, 'sms');
            $b_apps = $this->getBlockedIdentifiers($userId, 'app_usage');
            $b_notif = $this->getBlockedIdentifiers($userId, 'notification');

            $callWhere = empty($b_calls) ? "" : " AND phone_number NOT IN ('" . implode("','", array_map('addslashes', $b_calls)) . "')";
            $smsWhere = empty($b_sms) ? "" : " AND address NOT IN ('" . implode("','", array_map('addslashes', $b_sms)) . "')";
            $notifWhere = empty($b_notif) ? "" : " AND package_name NOT IN ('" . implode("','", array_map('addslashes', $b_notif)) . "')";
            $appWhere = empty($b_apps) ? "" : " AND package_name NOT IN ('" . implode("','", array_map('addslashes', $b_apps)) . "')";

            $sql = "
                SELECT SUM(c) AS total FROM (
                    SELECT COUNT(*) AS c FROM tbl_logs WHERE owner_id = ?$callWhere
                    UNION ALL
                    SELECT COUNT(*) AS c FROM tbl_sms WHERE owner_id = ?$smsWhere
                    UNION ALL
                    SELECT COUNT(*) AS c FROM tbl_notifications WHERE owner_id = ?$notifWhere
                    UNION ALL
                    SELECT COUNT(*) AS c FROM tbl_app_usage WHERE owner_id = ?$appWhere
                    UNION ALL
                    SELECT COUNT(*) AS c FROM tbl_location WHERE owner_id = ?
                    UNION ALL
                    SELECT COUNT(*) AS c FROM tbl_activity WHERE owner_id = ?
                    UNION ALL
                    SELECT COUNT(*) AS c FROM uploaded_files WHERE token_owner_id = ?
                ) t
            ";
            $row = $this->db->query($sql, [$userId, $userId, $userId, $userId, $userId, $userId, $userId])->getRowArray();
            return (int) ($row['total'] ?? 0);
        } catch (\Exception $e) {
            log_message('error', 'get_count_timeline_events error: ' . $e->getMessage());
            return 0;
        }
    }

    public function get_timeline_events(int $userId, int $perPage = 50): array
    {
        try {
            $total = $this->get_count_timeline_events($userId);
            $page = (int) (service('request')->getGet('page') ?? 1);
            $offset = ($page - 1) * $perPage;

            $b_calls = $this->getBlockedIdentifiers($userId, 'call');
            $b_sms = $this->getBlockedIdentifiers($userId, 'sms');
            $b_apps = $this->getBlockedIdentifiers($userId, 'app_usage');
            $b_notif = $this->getBlockedIdentifiers($userId, 'notification');

            $callWhere = empty($b_calls) ? "" : " AND phone_number NOT IN ('" . implode("','", array_map('addslashes', $b_calls)) . "')";
            $smsWhere = empty($b_sms) ? "" : " AND address NOT IN ('" . implode("','", array_map('addslashes', $b_sms)) . "')";
            $notifWhere = empty($b_notif) ? "" : " AND package_name NOT IN ('" . implode("','", array_map('addslashes', $b_notif)) . "')";
            $appWhere = empty($b_apps) ? "" : " AND package_name NOT IN ('" . implode("','", array_map('addslashes', $b_apps)) . "')";

            $sql = "
                SELECT 
                    'call' AS event_type, counter AS event_id, call_date AS timestamp_ms, contact_name AS title, phone_number AS subtitle, call_type AS meta1, CAST(duration_seconds AS CHAR) AS meta2
                FROM tbl_logs WHERE owner_id = ?$callWhere
                UNION ALL
                SELECT 
                    'sms' AS event_type, counter AS event_id, sms_date AS timestamp_ms, address AS title, NULL AS subtitle, sms_type AS meta1, body AS meta2
                FROM tbl_sms WHERE owner_id = ?$smsWhere
                UNION ALL
                SELECT 
                    'notification' AS event_type, id AS event_id, notification_timestamp AS timestamp_ms, app_name AS title, package_name AS subtitle, title AS meta1, text AS meta2
                FROM tbl_notifications WHERE owner_id = ?$notifWhere
                UNION ALL
                SELECT 
                    'app_usage' AS event_type, id AS event_id, last_time_used AS timestamp_ms, app_name AS title, package_name AS subtitle, NULL AS meta1, CAST(foreground_time_ms AS CHAR) AS meta2
                FROM tbl_app_usage WHERE owner_id = ?$appWhere
                UNION ALL
                SELECT 
                    'location' AS event_type, counter AS event_id, location_time AS timestamp_ms, provider AS title, NULL AS subtitle, CAST(latitude AS CHAR) AS meta1, CAST(longitude AS CHAR) AS meta2
                FROM tbl_location WHERE owner_id = ?
                UNION ALL
                SELECT 
                    'activity' AS event_type, counter AS event_id, activity_time AS timestamp_ms, activity_type AS title, info AS subtitle, CAST(confidence AS CHAR) AS meta1, status AS meta2
                FROM tbl_activity WHERE owner_id = ?
                UNION ALL
                SELECT 
                    'upload' AS event_type, file_id AS event_id, (UNIX_TIMESTAMP(uploaded_at) * 1000) AS timestamp_ms, original_filename AS title, file_category AS subtitle, CAST(file_size_bytes AS CHAR) AS meta1, mime_type AS meta2
                FROM uploaded_files WHERE token_owner_id = ?
                ORDER BY timestamp_ms DESC
                LIMIT ? OFFSET ?
            ";

            $results = $this->db->query($sql, [$userId, $userId, $userId, $userId, $userId, $userId, $userId, $perPage, $offset])->getResultArray();

            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_timeline_events error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_daily_usage_heatmap(int $userId): array
    {
        try {
            $sql = "
                SELECT 
                    DATE(FROM_UNIXTIME(extracted_at / 1000)) as date,
                    SUM(foreground_time_ms) as total_time_ms
                FROM tbl_app_usage
                WHERE owner_id = ? AND extracted_at IS NOT NULL AND extracted_at > 0
                GROUP BY date
                ORDER BY date ASC
            ";
            return $this->db->query($sql, [$userId])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_daily_usage_heatmap error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_dopamine_vs_productivity(int $userId): array
    {
        try {
            $socialPackages = [
                'com.whatsapp', 'com.facebook.katana', 'com.instagram.android', 
                'com.zhiliaoapp.musically', 'com.snapchat.android', 'com.twitter.android',
                'com.ss.android.ugc.trill', 'com.tencent.ig', 'com.whatsapp.w4b'
            ];
            $productivityPackages = [
                'com.slack', 'com.google.android.gm', 'com.microsoft.teams',
                'com.google.android.apps.docs', 'com.microsoft.office.word',
                'com.google.android.calendar', 'com.google.android.keep'
            ];

            $socialIn = "'" . implode("','", $socialPackages) . "'";
            $prodIn = "'" . implode("','", $productivityPackages) . "'";

            $sql = "
                SELECT 
                    SUM(CASE WHEN package_name IN ($socialIn) THEN foreground_time_ms ELSE 0 END) as dopamine_ms,
                    SUM(CASE WHEN package_name IN ($prodIn) THEN foreground_time_ms ELSE 0 END) as productivity_ms,
                    SUM(CASE WHEN package_name NOT IN ($socialIn) AND package_name NOT IN ($prodIn) THEN foreground_time_ms ELSE 0 END) as other_ms
                FROM tbl_app_usage
                WHERE owner_id = ?
            ";
            $row = $this->db->query($sql, [$userId])->getRowArray();
            return $row ?: ['dopamine_ms' => 0, 'productivity_ms' => 0, 'other_ms' => 0];
        } catch (\Exception $e) {
            log_message('error', 'get_dopamine_vs_productivity error: ' . $e->getMessage());
            return ['dopamine_ms' => 0, 'productivity_ms' => 0, 'other_ms' => 0];
        }
    }

    public function get_top_time_sink_apps(int $userId, int $limit = 5): array
    {
        try {
            $sql = "
                SELECT package_name, app_name, SUM(foreground_time_ms) as total_time_ms
                FROM tbl_app_usage
                WHERE owner_id = ?
                GROUP BY package_name, app_name
                ORDER BY total_time_ms DESC
                LIMIT ?
            ";
            return $this->db->query($sql, [$userId, $limit])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_top_time_sink_apps error: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_call_log(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_logs')
                ->where('counter', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_call_log error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_sms(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_sms')
                ->where('counter', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_sms error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_contact(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_contacts')
                ->where('counter', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_contact error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_file(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_device_files')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_file error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_location(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_location')
                ->where('counter', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_location error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_activity(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_activity')
                ->where('counter', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_activity error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_app_usage(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_app_usage')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_app_usage error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_notification(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_notifications')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_notification error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_app(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_apps')
                ->where('counter', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_app error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_device_context_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_device_context')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_device_context_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_network_info_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_network_info')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_network_info_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_accounts_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_accounts')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_accounts_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_calendar_event(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_calendar_events')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_calendar_event error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_sensor_profile(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_sensor_profile')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_sensor_profile error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_bluetooth_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_bluetooth')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_bluetooth_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_proc_info_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_proc_info')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_proc_info_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_security_audit_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_security_audit')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_security_audit_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_app_usage_by_package(int $userId, string $packageName): bool
    {
        try {
            return (bool) $this->db->table('tbl_app_usage')
                ->where('owner_id', $userId)
                ->where('package_name', $packageName)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_app_usage_by_package error: ' . $e->getMessage());
            return false;
        }
    }

}
