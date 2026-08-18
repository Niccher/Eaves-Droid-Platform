<?php

namespace App\Models\Finder;

use CodeIgniter\Model;
use App\Models\MLAnalyzerModel;

class FinderComms extends Model
{
    protected $parent;
    protected $db;

    public function __construct($parent)
    {
        parent::__construct();
        $this->parent = $parent;
        $this->db = $parent->db;
    }

    public function __get($name)
    {
        if ($name === 'deviceId') {
            return $this->parent->deviceId;
        }
        if ($name === 'pager') {
            return $this->parent->pager;
        }
        return null;
    }

    public function __set($name, $value)
    {
        if ($name === 'pager') {
            $this->parent->pager = $value;
        }
        if ($name === 'total_timeline') {
            $this->parent->total_timeline = $value;
        }
    }

    // Helper wrappers
    protected function applyOwnerDeviceFilter($builder, int $user_id)
    {
        return $this->parent->applyOwnerDeviceFilter($builder, $user_id);
    }

    protected function fq(string $table, int $userId)
    {
        return $this->parent->fq($table, $userId);
    }

    protected function cq(string $table, int $userId): int
    {
        return $this->parent->cq($table, $userId);
    }

    protected function getCount(string $table, int $user_id, array $extraWhere = [], ?string $blockColumn = null, array $blockedValues = []): int
    {
        return $this->parent->getCount($table, $user_id, $extraWhere, $blockColumn, $blockedValues);
    }

    protected function getBlockedIdentifiers(int $userId, string $category): array
    {
        return $this->parent->getBlockedIdentifiers($userId, $category);
    }

    protected function decode_sms_body(string $body): string
    {
        return $this->parent->decode_sms_body($body);
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
            $builder = $this->db->table('tbl_extracted_call_logs');
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
            $builder = $this->db->table('tbl_extracted_contacts');
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
            $builder = $this->db->table('tbl_extracted_sms');
            return $this->applyOwnerDeviceFilter($builder, $user_id)->delete();
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
        $blocked = $this->getBlockedIdentifiers($user_id, 'sms');
        return $this->getCount('tbl_extracted_sms', $user_id, [], 'address', $blocked);
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
        return $this->getCount('tbl_extracted_sms', $user_id, ['sms_type' => $category], 'address', $blocked);
    }

    /**
     * Gets count of contacts for user.
     *
     * @param int $user_id
     * @return int
     */
    public function get_count_Contacts(int $user_id): int
    {
        return $this->getCount('tbl_extracted_contacts', $user_id);
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
         return $this->getCount('tbl_extracted_call_logs', $user_id, [], 'phone_number', $blocked);
     }

     /**
      * Gets count of call logs by type.
      *
      * @param int $user_id
      * @param string $callType
      * @return int
      */
     public function get_count_Calls_by_type(int $user_id, string $callType): int
     {
         $blocked = $this->getBlockedIdentifiers($user_id, 'call');
         return $this->getCount('tbl_extracted_call_logs', $user_id, ['call_type' => $callType], 'phone_number', $blocked);
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
            return $this->db->table('tbl_extracted_contacts')
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
            $builder = $this->db->table('tbl_extracted_contacts');
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
            $builder = $this->fq('tbl_extracted_contacts', $userId);

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
                ->orderBy('display_name', 'ASC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Process phone numbers from JSON
            foreach ($results as &$row) {
                $phoneNumbers = json_decode($row['phone_numbers'], true);
                $row['Number'] = '';
                if (!empty($phoneNumbers) && is_array($phoneNumbers)) {
                    $firstPhone = $phoneNumbers[0];
                    if (is_array($firstPhone) && isset($firstPhone['number'])) {
                        $row['Number'] = $firstPhone['number'];
                    } elseif (is_string($firstPhone)) {
                        $row['Number'] = $firstPhone;
                    }
                }
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
            $builder = $this->fq('tbl_extracted_contacts', $userId);

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
            emails,
            email_count,
            companies,
            addresses,
            notes,
            nickname,
            website,
            social_profiles,
            raw_contact_account_type,
            raw_contact_account_name,
            photo_thumbnail_base64,
            relation,
            created_at
        ')
                ->orderBy('display_name', 'ASC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Process phone numbers from JSON
            foreach ($results as &$row) {
                $phoneNumbers = json_decode($row['phone_numbers'], true);
                $row['Number'] = '';
                if (!empty($phoneNumbers) && is_array($phoneNumbers)) {
                    $firstPhone = $phoneNumbers[0];
                    if (is_array($firstPhone) && isset($firstPhone['number'])) {
                        $row['Number'] = $firstPhone['number'];
                    } elseif (is_string($firstPhone)) {
                        $row['Number'] = $firstPhone;
                    }
                }
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
            $builder = $this->db->table('tbl_extracted_sms');

            // Get total count for pagination
            $total = $this->get_count_Sms_category($user_id, $sms_type);

            // Scope to this user (and active device) — never show another user's SMS.
            $this->applyOwnerDeviceFilter($builder, $user_id);

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
            $builder = $this->db->table('tbl_extracted_sms');

            // Get total count for pagination
            $total = $this->get_count_Sms($user_id);

            // Scope to this user (and active device) — never show another user's SMS.
            $this->applyOwnerDeviceFilter($builder, $user_id);

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
            $builder = $this->db->table('tbl_extracted_sms')
                ->select('address as sms_number, thread_id as sms_thread_id, count(*) AS Totals')
                ->where('owner_id', $user_id);

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
            $builder = $this->db->table('tbl_extracted_call_logs');

            // Get total count for pagination
            $total = $this->get_count_Calls($user_id);

            // Scope to this user (and active device) — the list MUST never show
            // another user's call logs.
            $this->applyOwnerDeviceFilter($builder, $user_id);

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
            $builder = $this->db->table('tbl_extracted_call_logs');

            // Get total count for this category using a SEPARATE builder
            // (countAllResults() would reset the shared builder and wipe the
            // owner filter below).
            $total = $this->get_count_Calls_by_type($user_id, $category);

            // Scope to this user (and active device) — never show another user's calls.
            $this->applyOwnerDeviceFilter($builder, $user_id);

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
            $builder = $this->db->table('tbl_extracted_call_logs')
                ->select('phone_number as Caller, contact_name as Saved, count(*) AS Totals')
                ->where('owner_id', $user_id);

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
            $builder = $this->db->table('tbl_extracted_sms');

            // Get total count with a SEPARATE builder (countAllResults() would
            // reset the shared builder and wipe the owner filter below).
            $countBuilder = $this->db->table('tbl_extracted_sms');
            $this->applyOwnerDeviceFilter($countBuilder, $user_id);
            if (is_array($sender)) {
                $countBuilder->whereIn('address', $sender);
            } else {
                $countBuilder->where('address', $sender);
            }
            $total = $countBuilder->countAllResults();

            // Scope to this user (and active device) — never show another user's SMS.
            $this->applyOwnerDeviceFilter($builder, $user_id);

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

    public function get_categorized_sms_counts(int $userId): array
    {
        $total = $this->db->table('tbl_extracted_sms')->where('owner_id', $userId)->countAllResults();

        $all_sms = $this->db->table('tbl_extracted_sms')
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
        $fin_senders = ['kcb', 'kcb_mobile', 'equitybank', 'equity', 'coopbank', 'mcoopcash', 'ncba', 'ncba_loop', 'absa', 'absabank', 'stanbic', 'stanbic_ke', 'familybank', 'stanchart', 'dtb', 'im_bank', 'postbank', 'mpesa'];
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

        $autoLabels = MLAnalyzerModel::autoLabelSms($rawBodies, $keywordMap);

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
            [$vectors, $vectorizer] = MLAnalyzerModel::vectorize($mlTexts, 200);

            $trainSamples = [];
            $trainLabels = [];
            foreach ($mlIndices as $pos => $origIdx) {
                $trainSamples[] = $vectors[$pos];
                $trainLabels[] = $autoLabels[$origIdx];
            }

            $classifier = MLAnalyzerModel::trainNaiveBayes($trainSamples, $trainLabels);
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
        $builder = $this->db->table('tbl_extracted_call_logs');
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
        $all_sms = $this->db->table('tbl_extracted_sms')
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
        $all_logs = $this->db->table('tbl_extracted_call_logs')
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
    public function search_sms(int $userId, string $query, int $limit = 0, int $offset = 0): array
    {
        $builder = $this->db->table('tbl_extracted_sms')
            ->select('address as Number, body as Message, sms_date as Date, sms_type as Type')
            ->where('owner_id', $userId)
            ->groupStart()
            ->like('address', $query)
            ->orLike('body', $query)
            ->groupEnd()
            ->orderBy('sms_date', 'DESC');

        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function search_sms_count(int $userId, string $query): int
    {
        return (int) $this->db->table('tbl_extracted_sms')
            ->where('owner_id', $userId)
            ->groupStart()
            ->like('address', $query)
            ->orLike('body', $query)
            ->groupEnd()
            ->countAllResults(false);
    }

    /**
     * Search Call LogsController by keyword.
     */
    public function search_calls(int $userId, string $query, int $limit = 0, int $offset = 0): array
    {
        $builder = $this->db->table('tbl_extracted_call_logs')
            ->select('contact_name as Name, phone_number as Number, call_date as Date, call_type as Type, duration_seconds as Duration')
            ->where('owner_id', $userId)
            ->groupStart()
            ->like('contact_name', $query)
            ->orLike('phone_number', $query)
            ->groupEnd()
            ->orderBy('call_date', 'DESC');

        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function search_calls_count(int $userId, string $query): int
    {
        return (int) $this->db->table('tbl_extracted_call_logs')
            ->where('owner_id', $userId)
            ->groupStart()
            ->like('contact_name', $query)
            ->orLike('phone_number', $query)
            ->groupEnd()
            ->countAllResults(false);
    }

    /**
     * Search ContactsController by keyword.
     */
    public function search_contacts(int $userId, string $query, int $limit = 0, int $offset = 0): array
    {
        $builder = $this->db->table('tbl_extracted_contacts')
            ->select('display_name as Name, phone_numbers as Number, last_contacted, contact_id')
            ->where('owner_id', $userId)
            ->groupStart()
            ->like('display_name', $query)
            ->orLike('phone_numbers', $query)
            ->groupEnd()
            ->orderBy('display_name', 'ASC');

        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    public function search_contacts_count(int $userId, string $query): int
    {
        return (int) $this->db->table('tbl_extracted_contacts')
            ->where('owner_id', $userId)
            ->groupStart()
            ->like('display_name', $query)
            ->orLike('phone_numbers', $query)
            ->groupEnd()
            ->countAllResults(false);
    }

    /**
     * Extracts financial transactions from SMS for AdvancedController Analysis.
     */
    public function get_financial_transactions(int $userId): array
    {
        $all_sms = $this->db->table('tbl_extracted_sms')
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
     * Get social graph data (top contacts by interaction).
     */
    public function get_social_graph(int $userId, int $limit = 20): array
    {
        // 1. Get SMS counts
        $sms_data = $this->db->table('tbl_extracted_sms')
            ->select('address, COUNT(*) as count')
            ->where('owner_id', $userId)
            ->groupBy('address')
            ->get()
            ->getResultArray();

        // 2. Get Call counts
        $call_data = $this->db->table('tbl_extracted_call_logs')
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

        // 4. Resolve Names from ContactsController table for remaining Unknowns
        $unknowns = array_keys(array_filter($social_map, fn($c) => $c['name'] === 'Unknown'));
        if (!empty($unknowns)) {
            // Processing unknowns in chunks to avoid query limits if needed, but for top 20 it's fine.
            // Actually querying all potential matches.
            $contacts = $this->db->table('tbl_extracted_contacts')
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
     * Communication Quality Metrics: Response latency and initiation.
     */
    public function get_communication_quality(int $userId): array
    {
        $quality = [];
        $sms = $this->db->table('tbl_extracted_sms')
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
        $contacts = $this->db->table('tbl_extracted_contacts')
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
     * Scam SMS Audit: Identify suspicious/scam messages.
     */
    public function get_scam_sms_audit(int $userId): array
    {
        $sms = $this->db->table('tbl_extracted_sms')
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
        $sms = $this->db->table('tbl_extracted_sms')
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
     * Sentiment & Social Tone: Keyword-based relationship health.
     */
    public function get_sentiment_profile(int $userId): array
    {
        $sms = $this->db->table('tbl_extracted_sms')
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

        [$vectors, $vectorizer] = MLAnalyzerModel::vectorizeSms($sms, 300);
        $vocab = $vectorizer->getVocabulary();

        $k = $totalMessages < 30 ? 2 : 3;
        $clusters = MLAnalyzerModel::kmeans($vectors, $k);

        $clusterLabels = MLAnalyzerModel::labelClustersByKeywords($clusters, $sms, $posWords, $negWords);
        if (count(array_unique($clusterLabels)) < 2) {
            $clusterLabels = MLAnalyzerModel::labelClustersByCentroid(
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

        $contacts = $this->db->table('tbl_extracted_contacts')
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

    public function delete_call_log(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_extracted_call_logs')
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
            return (bool) $this->db->table('tbl_extracted_sms')
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
            return (bool) $this->db->table('tbl_extracted_contacts')
                ->where('counter', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_contact error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Basic timeline: SMS + Call events only (for the Basic Timeline pill).
     *
     * @param int $userId
     * @param int $limit   Max rows per data source (total can be up to 2× limit)
     * @return array       Chronologically sorted event array
     */
    public function get_basic_timeline(int $userId, int $limit = 200, int $sinceDays = 0): array
    {
        $timeline = [];

        // SMS events
        try {
            $builder = $this->db->table('tbl_extracted_sms')
                ->select('address, body, sms_date, sms_type')
                ->where('owner_id', $userId);
            if ($sinceDays > 0) {
                $builder->where('sms_date >=', (time() - $sinceDays * 86400) * 1000);
            }
            $sms = $builder->orderBy('sms_date', 'DESC')
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
            $callsBuilder = $this->db->table('tbl_extracted_call_logs')
                ->select('phone_number, contact_name, call_type, call_date, duration_seconds')
                ->where('owner_id', $userId);
            if ($sinceDays > 0) {
                $callsBuilder->where('call_date >=', (time() - $sinceDays * 86400) * 1000);
            }
            $calls = $callsBuilder->orderBy('call_date', 'DESC')
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
            log_message('error', 'get_basic_timeline CallsController: ' . $e->getMessage());
        }

        usort($timeline, fn($a, $b) => $b['time'] <=> $a['time']);
        return $timeline;
    }

    public function deleteNotificationsByUser(int $user_id): bool
    {
        return $this->fq('tbl_extracted_notifications', $user_id)->delete();
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
            $builder = $this->db->table('tbl_extracted_notifications');
            return $this->applyOwnerDeviceFilter($builder, $user_id)
                           ->where('package_name', $packageName)
                           ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_notifications_by_app error: ' . $e->getMessage());
            return false;
        }
    }

    public function get_count_Notifications(int $user_id): int
    {
        $blocked = $this->getBlockedIdentifiers($user_id, 'notification');
        return $this->getCount('tbl_extracted_notifications', $user_id, [], 'package_name', $blocked);
    }

    public function export_notifications(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_extracted_notifications', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function get_notifications(int $user_id, int $perPage = 50): array
    {
        try {
            $total = $this->get_count_Notifications($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_extracted_notifications', $user_id)
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
        if ($this->db->fieldExists('is_screen_notification', 'tbl_extracted_notifications')) {
            return 'SUM(is_screen_notification) AS screen_count';
        }

        return '0 AS screen_count';
    }

    public function get_count_notification_groups(int $user_id): int
    {
        try {
            $groupSql = $this->notificationGroupKeySql();
            $builder = $this->fq('tbl_extracted_notifications', $user_id);
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

            $results = $this->db->table('tbl_extracted_notifications')
                ->where('owner_id', $user_id)
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
            $builder = $this->fq('tbl_extracted_notifications', $user_id);
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

            $builder = $this->fq('tbl_extracted_notifications', $user_id);
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
            $builder = $this->db->table('tbl_extracted_notifications')
                ->where('owner_id', $user_id)
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

    public function delete_notification(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_extracted_notifications')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_notification error: ' . $e->getMessage());
            return false;
        }
    }
}
