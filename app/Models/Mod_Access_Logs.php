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
        // Add IP address if not provided
        if (!isset($data['ip_address'])) {
            $data['ip_address'] = service('request')->getIPAddress();
        }

        // Add user agent if not provided
        if (!isset($data['user_agent'])) {
            $data['user_agent'] = service('request')->getUserAgent()->getAgentString();
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