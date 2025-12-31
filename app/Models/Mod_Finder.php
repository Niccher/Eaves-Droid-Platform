<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Finder extends Model
{
    /**
     * Gets basic user data if logged in.
     *
     * @return array|false
     */
    public function basic_user()
    {
        try {
            if (auth()->loggedIn()) {
                return json_decode(json_encode(auth()->user()), true);
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
            $builder->where('meta_Owner', $user_id);
            if (!empty($extraWhere)) {
                $builder->where($extraWhere);
            }
            return $builder->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'getCount error for ' . $table . ': ' . $e->getMessage());
            return 0;
        }
    }

    public function get_count_Sms(int $user_id): int
    {
        return $this->getCount('tbl_Sms', $user_id);
    }

    public function get_count_Sms_category(int $user_id, string $category): int
    {
        return $this->getCount('tbl_Sms', $user_id, ['sms_type' => $category]);
    }

    public function get_count_Apps(int $user_id): int
    {
        return $this->getCount('tbl_Apps', $user_id);
    }

    public function get_count_Contacts(int $user_id): int
    {
        return $this->getCount('tbl_Contacts', $user_id);
    }

    public function get_count_Calls(int $user_id): int
    {
        return $this->getCount('tbl_Logs', $user_id);
    }

    public function get_contact_info($contactNumber1){
        $user_id = json_decode(json_encode(auth()->user()), true)['id'];
        $builder = $this->db->table('tbl_Contacts');
        $query_sent = $builder->select('*')
            ->where('meta_Owner', $user_id)
            ->like('Number', $contactNumber1)
            ->limit(1)
            ->get();
        return $query_sent->getRowArray();
    }

    /**
     * Gets Contacts.
     *
     * @param int $user_id
     * @return array
     */
    public function get_contacts(int $userId, int $perPage = 25): array
    {
        try {
            // Set the table explicitly
            $this->table = 'tbl_Contacts';

            // Reset the model state
            $this->resetQuery();

            // Get paginated results
            $results = $this->asArray()
                ->where('meta_Owner', $userId)
                ->orderBy('Name', 'ASC')
                ->paginate($perPage, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_contacts error: ' . $e->getMessage());
            return ['error', 'get_contacts error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets SMS sent.
     *
     * @param int $user_id
     * @return array
     */
    public function get_sms_sent(int $user_id, int $perPage = 25): array
    {
        try {
            $this->table = 'tbl_SMSsent';
            return $this->asArray()
                ->where('meta_Owner', $user_id)
                ->orderBy('Name', 'ASC')
                ->paginate($perPage, 'bootstrap5_full');

        } catch (\Exception $e) {
            log_message('error', 'get_sms_sent error: ' . $e->getMessage());
            return ['error', 'get_sms_sent error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets SMS Received.
     *
     * @param int $user_id
     * @return array
     */
    public function get_sms_received(int $user_id, int $perPage = 25): array
    {
        try {
            $this->table = 'tbl_SMSsent';
            return $this->asArray()
                ->where('meta_Owner', $user_id)
                ->orderBy('Name', 'ASC')
                ->paginate($perPage, 'bootstrap5_full');

        } catch (\Exception $e) {
            log_message('error', 'get_sms_sent error: ' . $e->getMessage());
            return ['error', 'get_sms_sent error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets SMS by type.
     *
     * @param int $user_id
     * @param string $sms_type
     * @return array
     */
    public function get_sms_type(int $user_id, string $sms_type, int $perPage = 25): array
    {
        try {
            $this->table = 'tbl_Sms';

            // Reset the model state
            $this->resetQuery();

            return $this->asArray()
                ->where('meta_Owner', $user_id)
                ->where('sms_type', $sms_type)
                ->orderBy('sms_time', 'DESC')
                ->paginate($perPage, 'bootstrap5_full');

        } catch (\Exception $e) {
            log_message('error', 'get_sms_type error: ' . $e->getMessage());
            return ['error', 'get_sms_type error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets all SMS.
     *
     * @param int $user_id
     * @return array
     */
    public function get_sms(int $user_id, int $perPage = 25): array
    {
        try {
            // Set the table explicitly
            $this->table = 'tbl_Sms';

            // Reset the model state
            $this->resetQuery();

            // Get paginated results
            $results = $this->asArray()
                ->where('meta_Owner', $user_id)
                ->orderBy('sms_time', 'DESC')
                ->paginate($perPage, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_sms error: ' . $e->getMessage());
            return ['error', 'get_sms error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets limited SMS.
     *
     * @param int $user_id
     * @param int $limit
     * @param int $start
     * @return array
     */
    public function get_sms_limited(int $user_id, int $limit, int $start): array
    {
        try {
            $this->table = 'tbl_Sms';
            return $this->asArray()
                ->where('meta_Owner', $user_id)
                ->orderBy('sms_time', 'DESC')
                ->paginate($limit, 'bootstrap5');

        } catch (\Exception $e) {
            log_message('error', 'get_sms_limited error: ' . $e->getMessage());
            return ['error', 'get_sms_limited error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets active SMS stats.
     *
     * @param int $user_id
     * @return array
     */
    public function get_sms_active(int $user_id, int $perPage = 25): array
    {
        try {
            return $this->db->table('tbl_Sms')
                ->select('sms_number, sms_thread_id, count(*) AS Totals')
                ->where('meta_Owner', $user_id)
                ->groupBy('sms_number')
                ->orderBy('Totals', 'DESC')
                ->limit($perPage)
                ->get()
                ->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'get_sms_active error: ' . $e->getMessage());
            return ['error', 'get_sms_active error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets SMS between numbers.
     *
     * @param int $user_id
     * @param string $contactNumber1
     * @param string $contactNumber2
     * @return array
     */
    public function get_sms_between(int $user_id, string $contactNumber1, string $contactNumber2, int $perPage = 25): array
    {
        try {
            $this->table = 'tbl_Sms';
            return $this->asArray()
                ->select('sms_number, sms_thread_id, count(*) AS Totals')
                ->where('meta_Owner', $user_id)
                ->where('sms_number', $contactNumber1)
                ->orWhere('sms_number', $contactNumber2)
                ->groupBy('sms_number')
                ->paginate($perPage, 'bootstrap5');

        } catch (\Exception $e) {
            log_message('error', 'get_sms_between error: ' . $e->getMessage());
            return ['error', 'get_sms_between error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets all call logs.
     *
     * @param int $user_id
     * @return array
     */
    public function get_call_logs(int $user_id, int $perPage = 25): array
    {
        try {
            // Set the table explicitly
            $this->table = 'tbl_Logs';

            // Reset the model state
            $this->resetQuery();

            // Get paginated results
            $results = $this->asArray()
                ->where('meta_Owner', $user_id)
                ->orderBy('Timestamp', 'DESC')
                ->paginate($perPage, 'bootstrap5_full');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_call_logs error: ' . $e->getMessage());
            return ['error', 'get_call_logs error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets limited calls by type.
     *
     * @param int $user_id
     * @param string $category
     * @return array
     */
    public function get_calls_limited(int $user_id, string $category, int $perPage = 25): array
    {
        try {
            $this->table = 'tbl_Logs';
            $results = $this->asArray()
                ->where('meta_Owner', $user_id)
                ->where('Type', $category)
                ->orderBy('Timestamp', 'DESC')
                ->paginate($perPage, 'bootstrap5');

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'get_calls_limited error: ' . $e->getMessage());
            return ['error', 'get_calls_limited error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets active calls stats.
     *
     * @param int $user_id
     * @return array
     */
    public function get_calls_active(int $user_id, int $perPage = 20): array
    {
        try {
            return $this->db->table('tbl_Logs')
                ->select('Caller, Saved, count(*) AS Totals')
                ->where('meta_Owner', $user_id)
                ->groupBy('Caller')
                ->orderBy('Totals', 'DESC')
                ->limit($perPage)
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_calls_active error: ' . $e->getMessage());
            return ['error', 'get_calls_active error: ' . $e->getMessage()];
        }


    }

    /**
     * Gets logs between contacts.
     *
     * @param string $contactNumber1
     * @param string $contactNumber2
     * @param int $user_id
     * @return array
     */
    public function get_logs_between(string $contactNumber1, string $contactNumber2, int $user_id, int $perPage = 20): array
    {
        try {
            $this->table = 'tbl_Logs';
            return $this->asArray()
                ->where('meta_Owner', $user_id)
                ->where('Caller', $contactNumber1)
                ->orWhere('Caller', $contactNumber2)
                ->orderBy('Timestamp', 'DESC')
                ->paginate($perPage, 'bootstrap5');

        } catch (\Exception $e) {
            log_message('error', 'get_logs_between error: ' . $e->getMessage());
            return ['error', 'get_logs_between error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets apps with limit.
     *
     * @param int $user_id
     * @param int $limit
     * @param int $start
     * @return array
     */
    public function get_apps_with_limit(int $user_id, int $limit, int $start): array
    {
        try {
            $this->table = 'tbl_Apps';
            return $this->asArray()
                ->where('meta_Owner', $user_id)
                ->paginate($limit, 'bootstrap5');

        } catch (\Exception $e) {
            log_message('error', 'get_apps_with_limit error: ' . $e->getMessage());
            return ['error', 'get_apps_with_limit error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets all apps.
     *
     * @param int $user_id
     * @return array
     */
    public function get_apps(int $user_id, int $perPage = 20): array
    {
        try {
            $this->table = 'tbl_Apps';
            return $this->asArray()
                ->where('meta_Owner', $user_id)
                ->paginate($perPage, 'bootstrap5');

        } catch (\Exception $e) {
            log_message('error', 'get_apps error: ' . $e->getMessage());
            return ['error', 'get_apps error: ' . $e->getMessage()];
        }
    }

    /**
     * Gets SMS finance points.
     *
     * @param int $user_id
     * @return array
     */
    public function get_points_sms_finance(int $user_id, int $perPage = 20): array
    {
        try {
//        $this->table = 'tbl_Points_Finance'; //tbl_Points_Finance
//        return $this->asArray()
//            ->where('point_Owner', $user_id)
//            ->orderBy('point_Inserted', 'DESC')
//            ->paginate($perPage, 'bootstrap5');

        $db      = \Config\Database::connect();
        $builder = $db->table('tbl_Points_Finance'); // Explicitly define table

        return $builder->where('point_Owner', (string)$user_id) // Cast to string since DB is varchar
        ->orderBy('point_Inserted', 'DESC')
            ->get() // Use get() or paginate
            ->getResultArray();

    } catch (\Exception $e) {
        log_message('error', 'get_points_sms_finance error: ' . $e->getMessage());
        return ['error', 'get_points_sms_finance error: ' . $e->getMessage()];
    }
    }



    /**
     * Gets SMS from sender(s).
     *
     * @param int $user_id
     * @param array|string $sender
     * @return array
     */
    public function get_sms_from_sender(int $user_id, $sender, int $perPage = 20): array
    {
        try {
            // Set the table directly (clears any previous state)
            $this->table  = 'tbl_Sms';

            // Start building the query
            $query = $this->asArray()
                ->where('meta_Owner', $user_id)
                ->orderBy('sms_time', 'DESC');

            // Apply sender filter
            if (is_array($sender)) {
                $query->whereIn('sms_number', $sender);
            } else {
                $query->where('sms_number', $sender);
            }

            // Apply pagination using the correct Model method
            return $query->paginate($perPage, 'bootstrap5');

        } catch (\Exception $e) {
            log_message('error', 'get_sms_from_sender error: ' . $e->getMessage());
            return ['error', 'get_sms_from_sender error: ' . $e->getMessage()];
        }
    }

    /**
     * Sets SMS points for finance analysis.
     *
     * @param array $data
     * @return bool
     */
    public function set_sms_points_to_analyze_finance(array $data): bool
    {
        try {
            $builder = $this->db->table('tbl_Points_Finance');
            if ($builder->insert($data)) {
                log_message('info', 'SMS points set for user ' . ($data['point_Owner'] ?? 'unknown'));
                return true;
            }
            log_message('error', 'Failed to insert SMS points');
            return false;
        } catch (\Exception $e) {
            log_message('error', 'set_sms_points_to_analyze_finance error: ' . $e->getMessage());
            return false;
        }
    }

    public function getPager()
    {
        if (!$this->pager) {
            $this->pager = \Config\Services::pager();
        }
        return $this->pager;
    }
}