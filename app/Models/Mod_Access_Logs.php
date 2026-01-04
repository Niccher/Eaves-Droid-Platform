<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Access_Logs extends Model
{
    protected $table = 'tbl_user_actions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'platform', 'action', 'status', 'ip_address',
        'user_agent', 'browser', 'device', 'os_version', 'location',
        'details', 'timestamp'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get access logs for user
     */
    public function get_access_logs(int $user_id, int $limit = 100): array
    {
        try {
            return $this->asArray()
                ->where('user_id', $user_id)
                ->orderBy('created_date', 'DESC')
                ->limit($limit)
                ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'get_access_logs error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get access stats for user
     */
    public function get_access_stats(int $user_id): array
    {
        try {
            $builder = $this->db->table($this->table);

            // Get total count
            $total = $builder->where('user_id', $user_id)->countAllResults();

            // Get counts by status
            $statusCounts = $builder->select('status, COUNT(*) as count')
                ->where('user_id', $user_id)
                ->groupBy('status')
                ->get()
                ->getResultArray();

            // Get counts by platform
            $platformCounts = $builder->select('platform, COUNT(*) as count')
                ->where('user_id', $user_id)
                ->groupBy('platform')
                ->get()
                ->getResultArray();

            // Format results
            $stats = ['total' => $total, 'by_status' => [], 'by_platform' => []];

            foreach ($statusCounts as $row) {
                $stats['by_status'][$row['status']] = (int)$row['count'];
            }

            foreach ($platformCounts as $row) {
                $stats['by_platform'][$row['platform']] = (int)$row['count'];
            }

            return $stats;
        } catch (\Exception $e) {
            log_message('error', 'get_access_stats error: ' . $e->getMessage());
            return ['total' => 0, 'by_status' => [], 'by_platform' => []];
        }
    }
}