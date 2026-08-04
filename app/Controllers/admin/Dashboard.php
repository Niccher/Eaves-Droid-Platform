<?php

namespace App\Controllers\admin;

class Dashboard extends BaseAdminController
{
    public function index()
    {
        $db = $this->getDb();

        $totalUsersBuilder = $db->table('users')->where('deleted_at IS NULL');
        if (!auth()->user()->inGroup('superadmin')) {
            $superAdminIds = $db->table('auth_groups_users')
                ->select('user_id')
                ->where('group', 'superadmin')
                ->get()
                ->getResultArray();
            $ids = array_column($superAdminIds, 'user_id');
            if ($ids !== []) {
                $totalUsersBuilder->whereNotIn('users.id', $ids);
            }
        }
        $totalUsers = $totalUsersBuilder->countAllResults();
        $totalDevices = $db->table('tbl_devices')->countAllResults();
        $totalUploads = $db->table('uploaded_files')->countAllResults();
        $storageUsed = $db->table('uploaded_files')
            ->selectSum('file_size_bytes')
            ->get()
            ->getRow()
            ->file_size_bytes ?? 0;

        $recentActivity = $db->table('tbl_user_actions')
            ->select('tbl_user_actions.*, users.username')
            ->join('users', 'users.id = tbl_user_actions.user_id', 'left')
            ->where('tbl_user_actions.action_severity !=', 'critical')
            ->where('tbl_user_actions.action_type !=', 'admin_role_change')
            ->orderBy('tbl_user_actions.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        $uploadsPerDay = $db->table('uploaded_files')
            ->select("DATE(uploaded_at) as date, COUNT(*) as count")
            ->where('uploaded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')
            ->groupBy('DATE(uploaded_at)')
            ->orderBy('date', 'ASC')
            ->get()
            ->getResultArray();

        $usersPerDay = $db->table('users')
            ->select("DATE(created_at) as date, COUNT(*) as count")
            ->where('created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->get()
            ->getResultArray();

        $totalUsersWithData = $db->table('uploaded_files')
            ->select('token_owner_id')
            ->groupBy('token_owner_id')
            ->countAllResults();

        $latestBackup = null;

        $recentBuilder = $db->table('users');
        if (!auth()->user()->inGroup('superadmin')) {
            $superAdminIds = $db->table('auth_groups_users')
                ->select('user_id')
                ->where('group', 'superadmin')
                ->get()
                ->getResultArray();
            $ids = array_column($superAdminIds, 'user_id');
            if ($ids !== []) {
                $recentBuilder->whereNotIn('users.id', $ids);
            }
        }
        $recentRegistrations = $recentBuilder->orderBy('users.created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        return $this->renderView('admin/dashboard', [
            'pag' => 'admin-dashboard',
            'total_users' => $totalUsers,
            'total_devices' => $totalDevices,
            'total_uploads' => $totalUploads,
            'storage_used' => $storageUsed,
            'recent_activity' => $recentActivity,
            'uploads_per_day' => $uploadsPerDay,
            'users_per_day' => $usersPerDay,
            'total_users_with_data' => $totalUsersWithData,
            'latest_backup' => $latestBackup,
            'recent_registrations' => $recentRegistrations,
        ]);
    }

    public function overview()
    {
        return $this->index();
    }

    public function getStats()
    {
        $db = $this->getDb();

        $totalUsers = $db->table('users')->where('deleted_at IS NULL')->countAllResults();
        $activeToday = $db->table('users')
            ->where('last_active >= DATE_SUB(NOW(), INTERVAL 24 HOUR)')
            ->countAllResults();
        $newToday = $db->table('users')
            ->where('created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)')
            ->countAllResults();

        $uploadsToday = $db->table('uploaded_files')
            ->where('uploaded_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)')
            ->countAllResults();
        $pendingParsing = $db->table('uploaded_files')
            ->where('upload_status', 'uploaded')
            ->countAllResults();

        $dbStatus = 'connected';

        $statusCode = 200;
        try {
            $db->initialize();
            if (!$db->connID) $statusCode = 503;
        } catch (\Exception $e) {
            $dbStatus = 'error: ' . $e->getMessage();
            $statusCode = 503;
        }

        return $this->response->setJSON([
            'status' => $statusCode === 200 ? 'online' : 'degraded',
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'db_status' => $dbStatus,
            'stats' => [
                'total_users' => $totalUsers,
                'active_today' => $activeToday,
                'new_today' => $newToday,
                'uploads_today' => $uploadsToday,
                'pending_parsing' => $pendingParsing,
            ],
        ]);
    }
}
