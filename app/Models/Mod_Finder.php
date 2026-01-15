<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Finder extends Model
{
    protected $table = '';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [];
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    public $pager; // Changed from protected to public

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
     * @return int
     */
    protected function getCount(string $table, int $user_id, array $extraWhere = []): int
    {
        try {
            $builder = $this->db->table($table);

            // Handle different owner column names
            $ownerColumn = 'owner_id';
            $builder->where($ownerColumn, $user_id);

            if (!empty($extraWhere)) {
                $builder->where($extraWhere);
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
            return $builder->where('owner_id', $user_id)->delete();
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
            return $builder->where('owner_id', $user_id)->delete();
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
            return $builder->where('owner_id', $user_id)->delete();
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
            return $builder->where('owner_id', $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteSmsByUser error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets count of SMS messages for user.
     *
     * @param int $user_id
     * @return int
     */
    public function get_count_Sms(int $user_id): int
    {
        return $this->getCount('tbl_sms', $user_id);
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
        return $this->getCount('tbl_sms', $user_id, ['sms_type' => $category]);
    }

    /**
     * Gets count of apps for user.
     *
     * @param int $user_id
     * @return int
     */
    public function get_count_Apps(int $user_id): int
    {
        try {
            return $this->db->table('tbl_apps')
                ->where('owner_id', $user_id)
                ->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'get_count_Apps error: ' . $e->getMessage());
            return 0;
        }
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
        return $this->getCount('tbl_logs', $user_id);
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
                ->where('owner_id', $user_id)
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
            return ['error' => 'get_contacts error: ' . $e->getMessage()];
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
            return ['error' => 'get_contacts error: ' . $e->getMessage()];
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

            $results = $builder->select('
                counter as id,
                android_sms_id,
                thread_id as sms_thread_id,
                address as sms_number,
                body as sms_body,
                sms_date as sms_time,
                sms_type
            ')
                ->where('owner_id', $user_id)
                ->where('sms_type', $sms_type)
                ->orderBy('sms_date', 'DESC')
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

            $results = $builder->select('
                counter as id,
                android_sms_id,
                thread_id as sms_thread_id,
                address as sms_number,
                body as sms_body,
                sms_date as sms_time,
                sms_type
            ')
                ->where('owner_id', $user_id)
                ->orderBy('sms_date', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Set up pagination
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_sms error: ' . $e->getMessage());
            return ['error' => 'get_sms error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets active SMS stats with new schema mapping.
     *
     * @param int $user_id
     * @param int $perPage
     * @return array
     */
    public function get_sms_active(int $user_id, int $perPage = 20): array
    {
        try {
            return $this->db->table('tbl_sms')
                ->select('address as sms_number, thread_id as sms_thread_id, count(*) AS Totals')
                ->where('owner_id', $user_id)
                ->groupBy('address')
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

            $results = $builder->select('
                counter,
                contact_name as Saved,
                phone_number as Caller,
                call_date as Timestamp,
                duration_seconds as Durations,
                call_type as Type
            ')
                ->where('owner_id', $user_id)
                ->orderBy('call_date', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Set up pagination
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_call_logs error: ' . $e->getMessage());
            return ['error' => 'get_call_logs error: ' . $e->getMessage()];
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
            $total = $builder->where('owner_id', $user_id)
                ->where('call_type', $category)
                ->countAllResults();

            // Get page number from request
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $results = $builder->select('
                counter,
                contact_name as Saved,
                phone_number as Caller,
                call_date as Timestamp,
                duration_seconds as Durations,
                call_type as Type
            ')
                ->where('owner_id', $user_id)
                ->where('call_type', $category)
                ->orderBy('call_date', 'DESC')
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
    public function get_calls_active(int $user_id, int $perPage = 20): array
    {
        try {
            return $this->db->table('tbl_logs')
                ->select('phone_number as Caller, contact_name as Saved, count(*) AS Totals')
                ->where('owner_id', $user_id)
                ->groupBy('phone_number')
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

            $results = $builder->select('
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
            ')
                ->where('owner_id', $user_id)
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Set up pagination
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_apps error: ' . $e->getMessage());
            return ['error' => 'get_apps error: ' . $e->getMessage()];
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
                $total = $builder->where('owner_id', $user_id)
                    ->whereIn('address', $sender)
                    ->countAllResults();
            } else {
                $total = $builder->where('owner_id', $user_id)
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
                ->where('owner_id', $user_id)
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
}