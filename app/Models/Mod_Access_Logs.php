<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Access_Logs extends Model
{
    protected $table = 'tbl_user_actions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'session_id', 'action_category', 'action_type', 'action_severity',
        'ip_address', 'user_agent', 'device_type', 'device_name', 'operating_system',
        'browser', 'country_code', 'city', 'request_url', 'request_method', 'response_code',
        'execution_time_ms', 'resource_id', 'old_values', 'new_values', 'success',
        'error_code', 'error_message'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null;

    /**
     * Gets access logs for user.
     *
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public function get_access_logs(int $user_id, int $limit = 100): array
    {
        try {
            return $this->asArray()
                ->where('user_id', $user_id)
                ->orWhere('user_id', null) // Include anonymous logs
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'get_access_logs error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets access stats for user.
     *
     * @param int $user_id
     * @return array
     */
    public function get_access_stats(int $user_id): array
    {
        try {
            $builder = $this->db->table($this->table);

            // Get total count
            $total = $builder->where('user_id', $user_id)->countAllResults();

            // Get counts by success status
            $statusCounts = $builder->select('success, COUNT(*) as count')
                ->where('user_id', $user_id)
                ->groupBy('success')
                ->get()
                ->getResultArray();

            // Get counts by action category
            $categoryCounts = $builder->select('action_category, COUNT(*) as count')
                ->where('user_id', $user_id)
                ->groupBy('action_category')
                ->get()
                ->getResultArray();

            // Get counts by device type
            $deviceCounts = $builder->select('device_type, COUNT(*) as count')
                ->where('user_id', $user_id)
                ->groupBy('device_type')
                ->get()
                ->getResultArray();

            // Format results
            $stats = [
                'total' => $total,
                'by_status' => [],
                'by_category' => [],
                'by_device' => []
            ];

            foreach ($statusCounts as $row) {
                $status = $row['success'] ? 'success' : 'failed';
                $stats['by_status'][$status] = (int)$row['count'];
            }

            foreach ($categoryCounts as $row) {
                $stats['by_category'][$row['action_category']] = (int)$row['count'];
            }

            foreach ($deviceCounts as $row) {
                $stats['by_device'][$row['device_type'] ?? 'unknown'] = (int)$row['count'];
            }

            return $stats;
        } catch (\Exception $e) {
            log_message('error', 'get_access_stats error: ' . $e->getMessage());
            return ['total' => 0, 'by_status' => [], 'by_category' => [], 'by_device' => []];
        }
    }

    /**
     * Gets access logs for authentication actions.
     *
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public function get_auth_logs(int $user_id, int $limit = 50): array
    {
        try {
            return $this->asArray()
                ->where('user_id', $user_id)
                ->where('action_category', 'authentication')
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'get_auth_logs error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets grouped access logs for the "All Activities" summary view.
     * Groups by action_type + action_category with a frequency counter.
     *
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public function get_grouped_access_logs(int $user_id, int $limit = 50): array
    {
        try {
            return $this->db->table($this->table)
                ->select("action_type, action_category, COUNT(*) as frequency, MAX(created_at) as last_occurrence")
                ->where('user_id', $user_id)
                ->groupBy('action_type, action_category')
                ->orderBy('frequency', 'DESC')
                ->limit($limit)
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_grouped_access_logs error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets failed login attempts.
     *
     * @param int $user_id
     * @param int $hours
     * @return int
     */
    public function get_failed_attempts(int $user_id, int $hours = 24): int
    {
        try {
            $timeThreshold = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

            return $this->where('user_id', $user_id)
                ->where('action_category', 'authentication')
                ->where('success', 0)
                ->where('created_at >=', $timeThreshold)
                ->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'get_failed_attempts error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Gets recent activities by category.
     *
     * @param int $user_id
     * @param string $category
     * @param int $limit
     * @return array
     */
    public function get_recent_by_category(int $user_id, string $category, int $limit = 20): array
    {
        try {
            return $this->asArray()
                ->where('user_id', $user_id)
                ->where('action_category', $category)
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'get_recent_by_category error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Logs user actions with automatic field population.
     *
     * @param array $data
     * @return mixed
     */
    public function logAction($data)
    {
        $request = service('request');
        $agent = $request->getUserAgent();

        // Add IP address if not provided
        if (!isset($data['ip_address'])) {
            $data['ip_address'] = $request->getIPAddress();
        }

        // Add user agent if not provided
        if (!isset($data['user_agent'])) {
            $data['user_agent'] = $agent->getAgentString();
        }

        // Parse user agent to populate device fields if not explicitly provided
        $uaString = $data['user_agent'];
        
        if (!isset($data['device_type'])) {
            if ($agent->isMobile()) {
                $data['device_type'] = 'mobile';
            } elseif ($agent->isRobot()) {
                $data['device_type'] = 'bot';
            } else {
                if (stripos($uaString, 'okhttp') !== false || stripos($uaString, 'android') !== false) {
                    $data['device_type'] = 'mobile';
                } else {
                    $data['device_type'] = 'desktop';
                }
            }
        }

        if (!isset($data['operating_system'])) {
            $data['operating_system'] = $agent->getPlatform();
            if (empty($data['operating_system']) || $data['operating_system'] === 'Unknown Platform') {
                if (stripos($uaString, 'android') !== false) {
                    $data['operating_system'] = 'Android';
                } elseif (stripos($uaString, 'windows') !== false) {
                    $data['operating_system'] = 'Windows';
                } elseif (stripos($uaString, 'macintosh') !== false || stripos($uaString, 'mac os') !== false) {
                    $data['operating_system'] = 'macOS';
                } elseif (stripos($uaString, 'linux') !== false) {
                    $data['operating_system'] = 'Linux';
                } else {
                    $data['operating_system'] = 'Unknown OS';
                }
            }
        }

        if (!isset($data['browser'])) {
            if ($agent->isBrowser()) {
                $data['browser'] = $agent->getBrowser() . ' ' . $agent->getVersion();
            } else {
                if (stripos($uaString, 'okhttp') !== false) {
                    $data['browser'] = 'OkHttp Client';
                } elseif (stripos($uaString, 'postman') !== false) {
                    $data['browser'] = 'Postman';
                } else {
                    $data['browser'] = 'API Client';
                }
            }
        }

        if (!isset($data['device_name'])) {
            if ($data['device_type'] === 'mobile') {
                $matches = [];
                if (preg_match('/\b(android\s+\d+;\s+)?([^;\/]+)\s+build\b/i', $uaString, $matches)) {
                    $data['device_name'] = trim($matches[2]);
                } elseif (preg_match('/\(([^;]+);\s+[^;]+;\s+Android\s+[^;]+;\s+([^)]+)\)/i', $uaString, $matches)) {
                    $data['device_name'] = trim($matches[2]);
                } else {
                    $data['device_name'] = $agent->isMobile() ? ($agent->getMobile() ?: 'Android Mobile') : 'Desktop PC';
                }
            } else {
                $data['device_name'] = 'Desktop PC';
            }
        }

        // Add user ID from session if not provided
        if (!isset($data['user_id']) && session()->has('user_id')) {
            $data['user_id'] = session()->get('user_id');
        }

        // Add session ID
        if (!isset($data['session_id'])) {
            $data['session_id'] = session_id();
        }

        return $this->insert($data);
    }
}