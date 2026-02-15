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
     * Gets count of locations for user.
     *
     * @param int $user_id
     * @return int
     */
    public function get_count_Location(int $user_id): int
    {
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
                ->where('owner_id', $user_id)
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
    public function get_calls_active(int $user_id, int $perPage = 15): array
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

    /**
     * Gets all locations with pagination.
     *
     * @param int $user_id
     * @param int $perPage
     * @return array
     */
    public function get_locations(int $user_id, int $perPage = 25): array
    {
        try {
            $builder = $this->db->table('tbl_location');
            $total = $this->get_count_Location($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;

            $results = $builder->where('owner_id', $user_id)
                ->orderBy('extracted_at', 'DESC')
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

            $results = $builder->where('owner_id', $user_id)
                ->orderBy('extracted_at', 'DESC')
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

    /**
     * Get categorized SMS counts for dashboard.
     */
    public function get_categorized_sms_counts(int $userId): array
    {
        $fin_senders = ['kcb', 'kcb_mobile', 'equitybank', 'equity', 'coopbank', 'mcoopcash', 'ncba', 'ncba_loop', 'absa', 'absabank', 'stanbic', 'stanbic_ke', 'familybank', 'stanchart', 'dtb', 'im_bank', 'postbank', 'mpesa'];
        
        // Basic keywords for SQL LIKE - simple ones that don't depend on decoding if possible
        // But since many might be encoded, we still might need to fetch some.
        // Let's try to count by sender ID first in SQL as it\'s 100% reliable and fast.
        
        $builder = $this->db->table('tbl_sms');
        $total = $builder->where('owner_id', $userId)->countAllResults();

        // Optimized counting: Fetch everything but avoid heavy processing if we can
        // For truly high performance, we'd need a categorized column in the DB.
        // For now, let's optimize the loop and base64 check.
        
        $all_sms = $this->db->table('tbl_sms')
            ->select('address, body')
            ->where('owner_id', $userId)
            ->get()
            ->getResultArray();

        $counts = [
            'financial' => 0,
            'otp'       => 0,
            'promo'     => 0,
            'utility'   => 0,
            'service'   => 0,
            'malicious' => 0,
            'personal'  => 0,
            'total'     => $total
        ];

        $fin_keys = ['bank', 'mpesa', 'equity', 'kcb', 'transaction', 'kes', 'paid', 'received', 'balance', 'credited', 'debited', 'reversal'];
        $otp_keys = ['code', 'otp', 'verification', 'login', 'password reset'];
        $promo_keys = ['offer', 'discount', '% off', 'sale', 'win', 'subscribe', 'buy', 'promo', 'exclusive', 'betting', 'bet', 'jackpot'];
        $util_keys = ['kplc', 'water', 'token', 'zuku', 'fiber', 'safaricom home', 'bill', 'due date'];
        $serv_keys = ['uber', 'bolt', 'jumia', 'dhl', 'courier', 'delivery', 'ride', 'food'];
        $mal_keys = ['won lottery', 'congratulations you have won', 'prize', 'kshs 50,000'];

        foreach ($all_sms as $sms) {
            $addr = strtolower($sms['address']);
            
            // Fast Path: Financial Sender ID
            if (in_array($addr, $fin_senders)) {
                $counts['financial']++;
                continue;
            }

            $raw_body = $sms['body'];
            $body_text = $raw_body;

            // Only decode if it looks like base64 or if it's long enough to be an encoded msg
            if (strlen($raw_body) > 4 && preg_match('/^[a-zA-Z0-9\/\+=]+$/', $raw_body)) {
                $decoded = base64_decode($raw_body, true);
                if ($decoded !== false && mb_check_encoding($decoded, 'UTF-8')) {
                    $body_text = $decoded;
                }
            }
            
            $body = strtolower($body_text);
            $addr_len = strlen($addr);
            $categorized = false;

            // Malicious
            foreach ($mal_keys as $key) {
                if (strpos($body, $key) !== false) {
                    $counts['malicious']++;
                    $categorized = true;
                    break;
                }
            }
            if ($categorized) continue;

            // OTP
            foreach ($otp_keys as $key) {
                if (strpos($body, $key) !== false) {
                    $counts['otp']++;
                    $categorized = true;
                    break;
                }
            }
            if ($categorized) continue;

            // Financial Keywords
            if ($addr_len < 10) {
                foreach ($fin_keys as $key) {
                    if (strpos($body, $key) !== false) {
                        $counts['financial']++;
                        $categorized = true;
                        break;
                    }
                }
            }
            if ($categorized) continue;

            // Utility
            foreach ($util_keys as $key) {
                if (strpos($body, $key) !== false) {
                    $counts['utility']++;
                    $categorized = true;
                    break;
                }
            }
            if ($categorized) continue;

            // Service
            foreach ($serv_keys as $key) {
                if (strpos($body, $key) !== false) {
                    $counts['service']++;
                    $categorized = true;
                    break;
                }
            }
            if ($categorized) continue;

            // Promo
            if ($addr_len < 10) {
                $counts['promo']++;
            } else {
                foreach ($promo_keys as $key) {
                    if (strpos($body, $key) !== false) {
                        $counts['promo']++;
                        $categorized = true;
                        break;
                    }
                }
                if (!$categorized) {
                    $counts['personal']++;
                }
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
            'family'    => 0,
            'business'  => 0,
            'intl'      => 0,
            'urgent'    => 0,
            'spam'      => 0,
            'new'       => 0,
            'total'     => count($all_logs)
        ];

        if (empty($all_logs)) return $counts;

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
            $raw_body = $sms['body'];
            $decoded_body = base64_decode($raw_body, true);
            if ($decoded_body !== false && mb_check_encoding($decoded_body, 'UTF-8')) {
                $body_text = $decoded_body;
            } else {
                $body_text = $raw_body;
            }
            
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
            if (!isset($freq[$num])) $freq[$num] = ['count' => 0, 'recent' => 0];
            $freq[$num]['count']++;
            if ($log['call_date'] > $recent_threshold) $freq[$num]['recent']++;
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
}